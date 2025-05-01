<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Curso;

class CursoController extends Controller
{
    /*----------- LISTAR CURSOS : GET -----------*/
    public function index()
    {
        // Obtener todos los cursos activos ordenados por nombre
        $cursos = Curso::select(
                'id_curso', 
                'nombre', 
                'estado'
            )
            ->orderBy('nombre', 'asc')
            ->where('estado', 'A')
            ->get();
        
        if ($cursos->isEmpty()) {
            $respuesta = [
                'status'  => 404,
                'message' => 'No se encontraron cursos activos',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Retornar los cursos obtenidos
        $respuesta = [
            'status'  => 200,
            'message' => 'Cursos obtenidos correctamente',
            'errors'  => null,
            'data'    => $cursos
        ];
        
        return response()->json($respuesta, 200);
    }

    /*----------- CREAR CURSO : POST -----------*/
    public function store(Request $request)
    {
        // Obtener y validar los datos del request
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100|unique:cursos',
            'estado' => 'required|in:A,I'
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
        
        // Crear el curso en la base de datos
        try {
            $curso = Curso::create($request->only(['nombre', 'estado']));
            
            $respuesta = [
                'status'  => 201,
                'message' => 'Curso creado correctamente',
                'errors'  => null,
                'data'    => [
                    'nombre'     => $curso->nombre, 
                    'created_at' => $curso->created_at
                ]
            ];
            
            return response()->json($respuesta, 201);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al crear el curso en la base de datos',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al crear el curso',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }

    /*----------- MOSTRAR CURSO : GET -----------*/
    public function show(string $id)
    {
        // Obtener el curso activo por su ID
        $curso = Curso::select(
                'id_curso', 
                'nombre', 
                'estado'
            )
            ->where('id_curso', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$curso) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Curso no encontrado o inactivo',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Retornar el curso obtenido
        $respuesta = [
            'status'  => 200,
            'message' => 'Curso obtenido correctamente',
            'errors'  => null,
            'data'    => $curso
        ];
        
        return response()->json($respuesta, 200);
    }

    /*----------- ACTUALIZAR CURSO : PUT -----------*/
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
        
        // Obtener el curso activo por su ID
        $curso = Curso::where('id_curso', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$curso) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Curso no encontrado o inactivo',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Obtener y validar los datos del request
        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:100|unique:cursos,nombre,'.$id.',id_curso',
            'estado' => 'in:A,I'
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
        
        // Actualizar los datos del curso
        try {
            $curso->fill($request->only(['nombre', 'estado']))->save();
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Curso actualizado correctamente',
                'errors'  => null,
                'data'    => $curso
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al actualizar el curso',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al actualizar el curso',
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
        // Obtener el curso activo por su ID
        $curso = Curso::where('id_curso', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$curso) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Curso no encontrado o ya inactivo',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Eliminar de forma lógica el curso (cambiar estado a I)
        try {
            $curso->estado = 'I';
            $curso->save();
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Curso eliminado correctamente',
                'errors'  => null,
                'data'    => [
                    'nombre'     => $curso->nombre,
                    'updated_at' => $curso->updated_at
                ]
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al eliminar el curso',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al eliminar el curso',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }
}
