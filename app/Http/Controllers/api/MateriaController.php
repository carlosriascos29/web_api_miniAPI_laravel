<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Materia;

/**
 * @OA\Tag(
 *     name="Materias",
 *     description="API Endpoints para la gestión de materias"
 * )
 */
class MateriaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/materias",
     *     summary="Obtener lista de materias activas",
     *     tags={"Materias"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de materias obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Materias obtenidas correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id_materia", type="integer", example=1),
     *                     @OA\Property(property="nombre", type="string", example="Matemáticas"),
     *                     @OA\Property(property="estado", type="string", example="A")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron materias"
     *     )
     * )
     */
    public function index()
    {
        // Obtener todas las materias activas ordenadas por nombre
        $materias = Materia::select(
                'id_materia', 
                'nombre', 
                'estado'
            )
            ->orderBy('nombre', 'asc')
            ->where('estado', 'A')
            ->get();
        
        if ($materias->isEmpty()) {
            $respuesta = [
                'status'  => 404,
                'message' => 'No se encontraron materias activas',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Retornar las materias obtenidas
        $respuesta = [
            'status'  => 200,
            'message' => 'Materias obtenidas correctamente',
            'errors'  => null,
            'data'    => $materias
        ];
        
        return response()->json($respuesta, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/materias",
     *     summary="Crear una nueva materia",
     *     tags={"Materias"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre", "estado"},
     *             @OA\Property(property="nombre", type="string", example="Matemáticas"),
     *             @OA\Property(property="estado", type="string", example="A", enum={"A", "I"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Materia creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Materia creada correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="nombre", type="string", example="Matemáticas"),
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
            'nombre' => 'required|string|max:80|unique:materias',
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
        
        // Crear la materia en la base de datos
        try {
            $materia = Materia::create($request->only(['nombre', 'estado']));
            
            $respuesta = [
                'status'  => 201,
                'message' => 'Materia creada correctamente',
                'errors'  => null,
                'data'    => [
                    'nombre'     => $materia->nombre, 
                    'created_at' => $materia->created_at
                ]
            ];
            
            return response()->json($respuesta, 201);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al crear la materia en la base de datos',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al crear la materia',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/api/materias/{id}",
     *     summary="Obtener una materia específica",
     *     tags={"Materias"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la materia",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Materia encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Materia obtenida correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id_materia", type="integer", example=1),
     *                 @OA\Property(property="nombre", type="string", example="Matemáticas"),
     *                 @OA\Property(property="estado", type="string", example="A")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Materia no encontrada"
     *     )
     * )
     */
    public function show(string $id)
    {
        // Obtener la materia activa por su ID
        $materia = Materia::select(
                'id_materia', 
                'nombre', 
                'estado'
            )
            ->where('id_materia', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$materia) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Materia no encontrada o inactiva',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Retornar la materia obtenida
        $respuesta = [
            'status'  => 200,
            'message' => 'Materia obtenida correctamente',
            'errors'  => null,
            'data'    => $materia
        ];
        
        return response()->json($respuesta, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/materias/{id}",
     *     summary="Actualizar una materia existente",
     *     tags={"Materias"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la materia",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Matemáticas"),
     *             @OA\Property(property="estado", type="string", example="A", enum={"A", "I"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Materia actualizada exitosamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Materia no encontrada"
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
        
        // Obtener la materia activa por su ID
        $materia = Materia::where('id_materia', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$materia) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Materia no encontrada o inactiva',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Obtener y validar los datos del request
        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:80|unique:materias,nombre,'.$id.',id_materia',
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
        
        // Actualizar los datos de la materia
        try {
            $materia->fill($request->only(['nombre', 'estado']))->save();
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Materia actualizada correctamente',
                'errors'  => null,
                'data'    => $materia
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al actualizar la materia',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al actualizar la materia',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/materias/{id}",
     *     summary="Eliminar una materia (borrado lógico)",
     *     tags={"Materias"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la materia",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Materia eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Materia eliminada correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="nombre", type="string", example="Matemáticas"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Materia no encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Obtener la materia activa por su ID
        $materia = Materia::where('id_materia', $id)
            ->where('estado', 'A')
            ->first();
        
        if (!$materia) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Materia no encontrada o ya inactiva',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Eliminar de forma lógica la materia (cambiar estado a I)
        try {
            $materia->estado = 'I';
            $materia->save();
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Materia eliminada correctamente',
                'errors'  => null,
                'data'    => [
                    'nombre'     => $materia->nombre,
                    'updated_at' => $materia->updated_at
                ]
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al eliminar la materia',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al eliminar la materia',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }
}
