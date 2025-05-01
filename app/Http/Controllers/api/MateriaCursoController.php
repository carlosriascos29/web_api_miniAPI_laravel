<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\MateriaCurso;
use App\Models\Materia;
use App\Models\Curso;

class MateriaCursoController extends Controller
{
    /*----------- LISTAR ASIGNACIONES : GET -----------*/
    public function index()
    {
        // Obtener todas las asignaciones con sus materias y cursos
        $asignaciones = MateriaCurso::with([
                'materia:id_materia,nombre,estado',
                'curso:id_curso,nombre,estado'
            ])
            ->whereHas('materia', function($query) {
                $query->where('estado', 'A');
            })
            ->whereHas('curso', function($query) {
                $query->where('estado', 'A');
            })
            ->get();

        if ($asignaciones->isEmpty()) {
            $respuesta = [
                'status'  => 404,
                'message' => 'No se encontraron asignaciones de materias a cursos',
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

    /*----------- CREAR ASIGNACIÓN : POST -----------*/
    public function store(Request $request)
    {
        // Obtener y validar los datos del request
        $validator = Validator::make($request->all(), [
            'materias_id_materias' => 'required|exists:materias,id_materia',
            'cursos_id_cursos'     => 'required|exists:cursos,id_curso'
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
        $asignacionExistente = MateriaCurso::where('materias_id_materias', $request->materias_id_materias)
            ->where('cursos_id_cursos', $request->cursos_id_cursos)
            ->first();

        if ($asignacionExistente) {
            $respuesta = [
                'status'  => 409,
                'message' => 'La materia ya está asignada a este curso',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 409);
        }

        // Crear la asignación en la base de datos
        try {
            $asignacion = MateriaCurso::create($request->only(['materias_id_materias', 'cursos_id_cursos']));
            
            $respuesta = [
                'status'  => 201,
                'message' => 'Asignación creada correctamente',
                'errors'  => null,
                'data'    => [
                    'materia_id' => $asignacion->materias_id_materias,
                    'curso_id'   => $asignacion->cursos_id_cursos,
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

    /*----------- MOSTRAR ASIGNACIONES POR CURSO : GET -----------*/
    public function show(string $cursoId)
    {
        // Obtener el curso y validar que exista y esté activo
        $curso = Curso::where('id_curso', $cursoId)
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

        // Obtener las materias asignadas al curso
        $materias = MateriaCurso::with(['materia:id_materia,nombre'])
            ->where('cursos_id_cursos', $cursoId)
            ->whereHas('materia', function($query) {
                $query->where('estado', 'A');
            })
            ->get();

        if ($materias->isEmpty()) {
            $respuesta = [
                'status'  => 404,
                'message' => 'No se encontraron materias asignadas a este curso',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 404);
        }

        $respuesta = [
            'status'  => 200,
            'message' => 'Materias del curso obtenidas correctamente',
            'errors'  => null,
            'data'    => [
                'curso'    => $curso->nombre,
                'materias' => $materias
            ]
        ];
        
        return response()->json($respuesta, 200);
    }

    /*----------- ACTUALIZAR ASIGNACIÓN : PUT -----------*/
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
        $asignacion = MateriaCurso::find($id);
        
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
            'materias_id_materias' => 'exists:materias,id_materia',
            'cursos_id_cursos'     => 'exists:cursos,id_curso'
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
        if ($request->has('materias_id_materias') && $request->has('cursos_id_cursos')) {
            $existe = MateriaCurso::where('materias_id_materias', $request->materias_id_materias)
                ->where('cursos_id_cursos', $request->cursos_id_cursos)
                ->where('id', '!=', $id)
                ->first();

            if ($existe) {
                $respuesta = [
                    'status'  => 409,
                    'message' => 'La materia ya está asignada a este curso',
                    'errors'  => null,
                    'data'    => null
                ];
                
                return response()->json($respuesta, 409);
            }
        }
        
        // Actualizar la asignación en la base de datos
        try {
            $asignacion->update($request->only(['materias_id_materias', 'cursos_id_cursos']));
            
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

    /*----------- ELIMINAR ASIGNACIÓN : DELETE -----------*/
    public function destroy(string $id)
    {
        // Obtener la asignación y validar que exista
        $asignacion = MateriaCurso::find($id);
        
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
