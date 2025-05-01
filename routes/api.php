<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\EstudianteController;
use App\Http\Controllers\api\DocenteController;
use App\Http\Controllers\api\CursoController;
use App\Http\Controllers\api\MateriaController;
use App\Http\Controllers\api\MateriaCursoController;
USE App\Http\Controllers\api\DocenteMateriaController;
use App\Http\Controllers\api\EstudianteCursoController;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

Route::apiResource('/estudiantes', EstudianteController::class);
Route::apiResource('/docentes', DocenteController::class);
Route::apiResource('/cursos', CursoController::class);
Route::apiResource('/materias', MateriaController::class);
Route::apiResource('/materias-cursos', MateriaCursoController::class);
Route::apiResource('/docentes-materias', DocenteMateriaController::class);
Route::apiResource('/estudiantes-cursos', EstudianteCursoController::class);