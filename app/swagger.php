<?php

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API de Gestión Académica",
 *     description="API REST para la gestión de cursos, estudiantes, docentes y materias",
 *     @OA\Contact(
 *         email="admin@example.com",
 *         name="Soporte API"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Servidor Local"
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth"
 * )
 */
