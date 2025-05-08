<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DocenteMateria;
use App\Models\Docente;
use App\Models\Materia;

/**
 * @OA\Tag(
 *     name="Asignaciones Docente-Materia",
 *     description="API Endpoints para la gestión de asignaciones entre docentes y materias"
 * )
 */
class DocenteMateriaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/docente-materia",
     *     summary="Obtener todas las asignaciones de docentes a materias",
     *     tags={"Asignaciones Docente-Materia"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de asignaciones obtenida correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Asignaciones obtenidas correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="docentes_id_docentes", type="integer", example=1),
     *                     @OA\Property(property="materias_id_materias", type="integer", example=1),
     *                     @OA\Property(property="docente", type="object",
     *                         @OA\Property(property="id_docente", type="integer", example=1),
     *                         @OA\Property(property="nombre", type="string", example="Juan"),
     *                         @OA\Property(property="apellido", type="string", example="Pérez"),
     *                         @OA\Property(property="estado", type="string", example="A")
     *                     ),
     *                     @OA\Property(property="materia", type="object",
     *                         @OA\Property(property="id_materia", type="integer", example=1),
     *                         @OA\Property(property="nombre", type="string", example="Matemáticas"),
     *                         @OA\Property(property="estado", type="string", example="A")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No se encontraron asignaciones"
     *     )
     * )
     */
    public function index()
    {
        // Obtener todas las asignaciones con sus docentes y materias
        $asignaciones = DocenteMateria::with([
                'docente:id_docente,nombre,apellido,estado',
                'materia:id_materia,nombre,estado'
            ])
            ->whereHas('docente', function($query) {
                $query->where('estado', 'A');
            })
            ->whereHas('materia', function($query) {
                $query->where('estado', 'A');
            })
            ->get();

        if ($asignaciones->isEmpty()) {
            $respuesta = [
                'status'  => 404,
                'message' => 'No se encontraron asignaciones de docentes a materias',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }

        $respuesta = [
            'status'  => 200,
            'message' => 'Asignaciones obtenidas correctamente',
            'errors'  => null,
            'data'    => $asignaciones
        ];
        
        return response()->json($respuesta, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/docente-materia",
     *     summary="Crear una nueva asignación de docente a materia",
     *     tags={"Asignaciones Docente-Materia"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"docentes_id_docentes", "materias_id_materias"},
     *             @OA\Property(property="docentes_id_docentes", type="integer", example=1),
     *             @OA\Property(property="materias_id_materias", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Asignación creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Asignación creada correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="docente_id", type="integer", example=1),
     *                 @OA\Property(property="materia_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="El docente ya está asignado a esta materia"
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
            'docentes_id_docentes' => 'required|exists:docentes,id_docente',
            'materias_id_materias' => 'required|exists:materias,id_materia'
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

        // Verificar que no exista ya la asignación
        $asignacionExistente = DocenteMateria::where('docentes_id_docentes', $request->docentes_id_docentes)
            ->where('materias_id_materias', $request->materias_id_materias)
            ->first();

        if ($asignacionExistente) {
            $respuesta = [
                'status'  => 409,
                'message' => 'El docente ya está asignado a esta materia',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 409);
        }

        // Crear la asignación en la base de datos
        try {
            $asignacion = DocenteMateria::create($request->only(['docentes_id_docentes', 'materias_id_materias']));
            
            $respuesta = [
                'status'  => 201,
                'message' => 'Asignación creada correctamente',
                'errors'  => null,
                'data'    => [
                    'docente_id' => $asignacion->docentes_id_docentes,
                    'materia_id' => $asignacion->materias_id_materias,
                    'created_at' => $asignacion->created_at
                ]
            ];
            
            return response()->json($respuesta, 201);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al crear la asignación en la base de datos',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al crear la asignación',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/api/docente-materia/{docenteId}",
     *     summary="Obtener todas las materias asignadas a un docente",
     *     tags={"Asignaciones Docente-Materia"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="docenteId",
     *         in="path",
     *         required=true,
     *         description="ID del docente",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Materias del docente obtenidas correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Materias del docente obtenidas correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="docentes_id_docentes", type="integer", example=1),
     *                     @OA\Property(property="materias_id_materias", type="integer", example=1),
     *                     @OA\Property(property="materia", type="object",
     *                         @OA\Property(property="id_materia", type="integer", example=1),
     *                         @OA\Property(property="nombre", type="string", example="Matemáticas"),
     *                         @OA\Property(property="estado", type="string", example="A")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Docente no encontrado o sin materias asignadas"
     *     )
     * )
     */
    public function show(string $docenteId)
    {
        // Obtener el docente y validar que exista y esté activo
        $docente = Docente::where('id_docente', $docenteId)
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

        // Obtener las materias asignadas al docente
        $materias = DocenteMateria::with(['materia:id_materia,nombre'])
            ->where('docentes_id_docentes', $docenteId)
            ->whereHas('materia', function($query) {
                $query->where('estado', 'A');
            })
            ->get();

        if ($materias->isEmpty()) {
            $respuesta = [
                'status'  => 404,
                'message' => 'No se encontraron materias asignadas a este docente',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }

        $respuesta = [
            'status'  => 200,
            'message' => 'Materias del docente obtenidas correctamente',
            'errors'  => null,
            'data'    => [
                'docente'  => $docente->nombre . ' ' . $docente->apellido,
                'materias' => $materias
            ]
        ];
        
        return response()->json($respuesta, 200);
    }

    /**
     * @OA\Put(
     *     path="/api/docente-materia/{id}",
     *     summary="Actualizar una asignación existente",
     *     tags={"Asignaciones Docente-Materia"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la asignación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="docentes_id_docentes", type="integer", example=1),
     *             @OA\Property(property="materias_id_materias", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asignación actualizada exitosamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asignación no encontrada"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="El docente ya está asignado a esta materia"
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
        
        // Obtener la asignación y validar que exista
        $asignacion = DocenteMateria::find($id);
        
        if (!$asignacion) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Asignación no encontrada',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Obtener y validar los datos del request
        $validator = Validator::make($request->all(), [
            'docentes_id_docentes' => 'exists:docentes,id_docente',
            'materias_id_materias' => 'exists:materias,id_materia'
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
        
        // Validar que no exista otra asignación igual
        if ($request->has('docentes_id_docentes') && $request->has('materias_id_materias')) {
            $existe = DocenteMateria::where('docentes_id_docentes', $request->docentes_id_docentes)
                ->where('materias_id_materias', $request->materias_id_materias)
                ->where('id', '!=', $id)
                ->first();

            if ($existe) {
                $respuesta = [
                    'status'  => 409,
                    'message' => 'El docente ya está asignado a esta materia',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 409);
            }
        }
        
        // Actualizar la asignación en la base de datos
        try {
            $asignacion->update($request->only(['docentes_id_docentes', 'materias_id_materias']));
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Asignación actualizada correctamente',
                'errors'  => null,
                'data'    => $asignacion
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al actualizar la asignación',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al actualizar la asignación',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/docente-materia/{id}",
     *     summary="Eliminar una asignación",
     *     tags={"Asignaciones Docente-Materia"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la asignación",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Asignación eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Asignación eliminada correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="1"),
     *                 @OA\Property(property="deleted_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Asignación no encontrada"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error del servidor"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Obtener la asignación y validar que exista
        $asignacion = DocenteMateria::find($id);
        
        if (!$asignacion) {
            $respuesta = [
                'status'  => 404,
                'message' => 'Asignación no encontrada',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }
        
        // Eliminar la asignación de la base de datos
        try {
            $asignacion->delete();
            
            $respuesta = [
                'status'  => 200,
                'message' => 'Asignación eliminada correctamente',
                'errors'  => null,
                'data'    => [
                    'id'          => $id,
                    'deleted_at'  => now()->toDateTimeString()
                ]
            ];
            
            return response()->json($respuesta, 200);
        
        } catch (\Exception $e) {
            // Si el DEBUG está en true en el archivo .env se mostrarán detalles del error
            if (config('app.debug')) {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error al eliminar la asignación',
                    'errors'  => ['Código de error' => $e->getCode(), 'Mensaje' => $e->getMessage()],
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            
            } else {
                $respuesta = [
                    'status'  => 500,
                    'message' => 'Error inesperado al eliminar la asignación',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 500);
            }
        }
    }
}
