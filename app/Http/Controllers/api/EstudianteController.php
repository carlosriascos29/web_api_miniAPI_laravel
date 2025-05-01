<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Estudiante;

class EstudianteController extends Controller
{
    /*----------- LISTAR ESTUDIANTES : GET -----------*/
    public function index()
    {
        // Obtener todos los estudiantes activos ordenados por apellido y nombre
        $estudiantes = Estudiante::select(
                'id_estudiante', 
                'nombre', 
                'apellido', 
                'dni', 
                'estado'
            )
            ->orderBy('apellido', 'asc')
            ->orderBy('nombre', 'asc')
            ->where('estado', 'A')
            ->get();
        
        if ($estudiantes->isEmpty()) {
            $respuesta = [
                'status'  => 404,
                'message' => 'No se encontraron estudiantes activos',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Retornar los estudiantes obtenidos
        $respuesta = [
            'status'  => 200,
            'message' => 'Estudiantes obtenidos correctamente',
            'errors'  => null,
            'data'    => $estudiantes
        ];
        
        return response()->json($respuesta, 200);
    }

    /*----------- CREAR ESTUDIANTE : POST -----------*/
    public function store(Request $request)
    {
        // Obtener y validar los datos del request
        $validator = Validator::make($request->all(), [
            'nombre'   => 'required|string|max:80',
            'apellido' => 'required|string|max:80',
            'dni'      => 'required|string|max:15|unique:estudiantes',
            'estado'   => 'required|in:A,I'
        ]);
        
        if ($validator->fails()) {
            $respuesta = [
                'status'  => 400,
                'message' => 'Error en la validación de los datos',
                'errors'  => $validator->errors(),
                'data'    => null
            ];
            
            return response()->json($respuesta, 400);
        }
        
        // Crear el estudiante en la base de datos
        try {
            $estudiante = Estudiante::create($request->only(['nombre', 'apellido', 'dni', 'estado']));
            
            $respuesta = [
                'status'  => 201,
                'message' => 'Estudiante creado correctamente',
                'errors'  => null,
                'data'    => [
                    'nombre'     => $estudiante->nombre, 
                    'apellido'   => $estudiante->apellido, 
                    'created_at' => $estudiante->created_at
                ]
            ];
            
            return response()->json($respuesta, 201);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al crear el estudiante en la base de datos',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al crear el estudiante',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }

    /*----------- MOSTRAR ESTUDIANTE : GET -----------*/
    public function show(string $id)
    {
        // Obtener el estudiante activo por su ID
        $estudiante = Estudiante::select(
                'id_estudiante', 
                'nombre', 
                'apellido', 
                'dni', 
                'estado'
            )
            ->where('id_estudiante', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$estudiante) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Estudiante no encontrado o inactivo',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Retornar el estudiante obtenido
        $respuesta = [
            'status'  => 200,
            'message' => 'Estudiante obtenido correctamente',
            'errors'  => null,
            'data'    => $estudiante
        ];
        
        return response()->json($respuesta, 200);
    }

    /*----------- ACTUALIZAR ESTUDIANTE : PUT -----------*/
    public function update(Request $request, string $id)
    {
        // Este controlador no permite el uso de PATCH
        if ($request->method() === 'PATCH') {
            $respuesta = [
                'status'  => 405,
                'message' => 'Método PATCH no permitido. Usa PUT.',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 405);
        }
        
        // Obtener el estudiante activo por su ID
        $estudiante = Estudiante::where('id_estudiante', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$estudiante) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Estudiante no encontrado o inactivo',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Obtener y validar los datos del request
        $validator = Validator::make($request->all(), [
            'nombre'   => 'string|max:80',
            'apellido' => 'string|max:80',
            'dni'      => 'string|max:15|unique:estudiantes,dni,'.$id.',id_estudiante',
            'estado'   => 'in:A,I'
        ]);
        
        if ($validator->fails()) {
            $respuesta = [
                'status'  => 400,
                'message' => 'Error en la validación de los datos',
                'errors'  => $validator->errors(),
                'data'    => null
            ];
            
            return response()->json($respuesta, 400);
        }
        
        // Actualizar los datos del estudiante
        try {
            $estudiante->fill($request->only(['nombre', 'apellido', 'dni', 'estado']))->save();
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Estudiante actualizado correctamente',
                'errors'  => null,
                'data'    => $estudiante
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al actualizar el estudiante',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al actualizar el estudiante',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }

    /*----------- BORRADO LÓGICO : DELETE -----------*/
    public function destroy(string $id)
    {
        // Obtener el estudiante activo por su ID
        $estudiante = Estudiante::where('id_estudiante', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$estudiante) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Estudiante no encontrado o ya inactivo',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Eliminar de forma lógica el estudiante (cambiar estado a I)
        try {
            $estudiante->estado = 'I';
            $estudiante->save();
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Estudiante eliminado correctamente',
                'errors'  => null,
                'data'    => [
                    'nombre'     => $estudiante->nombre,
                    'apellido'   => $estudiante->apellido,
                    'updated_at' => $estudiante->updated_at
                ]
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al eliminar el estudiante',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al eliminar el estudiante',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }
}
