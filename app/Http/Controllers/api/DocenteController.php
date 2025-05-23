<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Docente;

/**
 * @OA\Tag(
 *     name="Docentes",
 *     description="API Endpoints para la gestión de docentes"
 * )
 */
class DocenteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/docentes",
     *     summary="Obtener lista de docentes activos",
     *     tags={"Docentes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de docentes obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Docentes obtenidos correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id_docente", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Juan"),
     *                     @OA\Property(property="apellido", type="string", example="Pérez"),
     *                     @OA\Property(property="dni", type="string", example="12345678"),
     *                     @OA\Property(property="titulo_academico", type="string", example="Licenciado en Matemáticas"),
     *                     @OA\Property(property="estado", type="string", example="A")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron docentes"
     *     )
     * )
     */
    public function index()
    {
        // Obtener todos los docentes activos ordenados por apellido y nombre
        $docentes = Docente::select(
                'id_docente', 
                'nombre', 
                'apellido', 
                'dni', 
                'titulo_academico', 
                'estado'
            )
            ->orderBy('apellido', 'asc')
            ->orderBy('nombre', 'asc')
            ->where('estado', 'A')
            ->get();
        
        if ($docentes->isEmpty()) {
            $respuesta = [
                'status'  => 404,
                'message' => 'No se encontraron docentes activos',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Retornar los docentes obtenidos
        $respuesta = [
            'status'  => 200,
            'message' => 'Docentes obtenidos correctamente',
            'errors'  => null,
            'data'    => $docentes
        ];
        
        return response()->json($respuesta, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/docentes",
     *     summary="Crear un nuevo docente",
     *     tags={"Docentes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "apellido", "dni", "titulo_academico", "estado"},
     *             @OA\Property(property="nombre", type="string", example="Juan"),
     *             @OA\Property(property="apellido", type="string", example="Pérez"),
     *             @OA\Property(property="dni", type="string", example="12345678"),
     *             @OA\Property(property="titulo_academico", type="string", example="Licenciado en Matemáticas"),
     *             @OA\Property(property="estado", type="string", example="A", enum={"A", "I"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Docente creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Docente creado correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="nombre", type="string", example="Juan"),
     *                 @OA\Property(property="apellido", type="string", example="Pérez"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Obtener y validar los datos del request
        $validator = Validator::make($request->all(), [
            'nombre'           => 'required|string|max:80',
            'apellido'        => 'required|string|max:80',
            'dni'             => 'required|string|max:15|unique:docentes',
            'titulo_academico' => 'required|string|max:80',
            'estado'          => 'required|in:A,I'
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
        
        // Crear el docente en la base de datos
        try {
            $docente = Docente::create($request->only(['nombre', 'apellido', 'dni', 'titulo_academico', 'estado']));
            
            $respuesta = [
                'status'  => 201,
                'message' => 'Docente creado correctamente',
                'errors'  => null,
                'data'    => [
                    'nombre'     => $docente->nombre, 
                    'apellido'   => $docente->apellido, 
                    'created_at' => $docente->created_at
                ]
            ];
            
            return response()->json($respuesta, 201);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al crear el docente en la base de datos',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al crear el docente',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/api/docentes/{id}",
     *     summary="Obtener un docente específico",
     *     tags={"Docentes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del docente",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Docente encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Docente obtenido correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id_docente", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Juan"),
     *                 @OA\Property(property="apellido", type="string", example="Pérez"),
     *                 @OA\Property(property="dni", type="string", example="12345678"),
     *                 @OA\Property(property="titulo_academico", type="string", example="Licenciado en Matemáticas"),
     *                 @OA\Property(property="estado", type="string", example="A")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Docente no encontrado"
     *     )
     * )
     */
    public function show(string $id)
    {
        // Obtener el docente activo por su ID
        $docente = Docente::select(
                'id_docente', 
                'nombre', 
                'apellido', 
                'dni', 
                'titulo_academico', 
                'estado'
            )
            ->where('id_docente', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$docente) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Docente no encontrado o inactivo',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Retornar el docente obtenido
        $respuesta = [
            'status'  => 200,
            'message' => 'Docente obtenido correctamente',
            'errors'  => null,
            'data'    => $docente
        ];
        
        return response()->json($respuesta, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/docentes/{id}",
     *     summary="Actualizar un docente existente",
     *     tags={"Docentes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del docente",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Juan"),
     *             @OA\Property(property="apellido", type="string", example="Pérez"),
     *             @OA\Property(property="dni", type="string", example="12345678"),
     *             @OA\Property(property="titulo_academico", type="string", example="Licenciado en Matemáticas"),
     *             @OA\Property(property="estado", type="string", example="A", enum={"A", "I"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Docente actualizado exitosamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Docente no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
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
        
        // Obtener el docente activo por su ID
        $docente = Docente::where('id_docente', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$docente) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Docente no encontrado o inactivo',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Obtener y validar los datos del request
        $validator = Validator::make($request->all(), [
            'nombre'           => 'string|max:80',
            'apellido'        => 'string|max:80',
            'dni'             => 'string|max:15|unique:docentes,dni,'.$id.',id_docente',
            'titulo_academico' => 'string|max:80',
            'estado'          => 'in:A,I'
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
        
        // Actualizar los datos del docente
        try {
            $docente->fill($request->only(['nombre', 'apellido', 'dni', 'titulo_academico', 'estado']))->save();
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Docente actualizado correctamente',
                'errors'  => null,
                'data'    => $docente
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al actualizar el docente',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al actualizar el docente',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/docentes/{id}",
     *     summary="Eliminar un docente (borrado lógico)",
     *     tags={"Docentes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del docente",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Docente eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Docente eliminado correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="nombre", type="string", example="Juan"),
     *                 @OA\Property(property="apellido", type="string", example="Pérez"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Docente no encontrado"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Obtener el docente activo por su ID
        $docente = Docente::where('id_docente', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$docente) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Docente no encontrado o ya inactivo',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Eliminar de forma lógica el docente (cambiar estado a I)
        try {
            $docente->estado = 'I';
            $docente->save();
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Docente eliminado correctamente',
                'errors'  => null,
                'data'    => [
                    'nombre'     => $docente->nombre,
                    'apellido'   => $docente->apellido,
                    'updated_at' => $docente->updated_at
                ]
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al eliminar el docente',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al eliminar el docente',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }
}
