<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Autenticación",
 *     description="API Endpoints para la gestión de autenticación y usuarios"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Iniciar sesión de usuario",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="MiContraseña123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Usuario autenticado correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="1|abcdef123456..."),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="usuario@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales inválidas"
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (!Auth::attempt($credentials)) {
            $respuesta = [
                'status'  => 401,
                'message' => 'Credenciales incorrectas',
                'errors'  => null,
                'data'    => null
            ];
            
            return response()->json($respuesta, 401);
        }
        
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;
        
        $respuesta = [
            'status'  => 200,
            'message' => 'Usuario autenticado correctamente',
            'errors'  => null,
            'data'    => ['token' => $token, 'user' => $user]
        ];
        
        return response()->json($respuesta, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Cerrar sesión de usuario",
     *     tags={"Autenticación"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Sesión cerrada correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        $respuesta = [
            'status'  => 200,
            'message' => 'Sesión cerrada correctamente',
            'errors'  => null,
            'data'    => null
        ];
        
        return response()->json($respuesta, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Registrar nuevo usuario",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="MiContraseña123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="MiContraseña123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario registrado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Usuario registrado correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="1|abcdef123456..."),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="usuario@example.com")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[A-Z]/', // Al menos una mayúscula
                'regex:/[0-9]/', // Al menos un número
            ],
        ]);
        
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);
        
        // Opcional: Autenticar al usuario después del registro
        $token = $user->createToken('auth_token')->plainTextToken;

        $respuesta = [
            'status'  => 201,
            'message' => 'Usuario registrado correctamente',
            'errors'  => null,
            'data'    => ['token' => $token, 'user' => $user]
        ];
        
        return response()->json($respuesta, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/password",
     *     summary="Actualizar contraseña del usuario",
     *     tags={"Autenticación"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="ContraseñaActual123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="NuevaContraseña123!"),
     *             @OA\Property(property="new_password_confirmation", type="string", format="password", example="NuevaContraseña123!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contraseña actualizada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Contraseña actualizada correctamente"),
     *             @OA\Property(property="errors", type="null"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="1|abcdef123456...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Contraseña actual incorrecta"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación"
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        // Validación manual para controlar
        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string', 'current_password'],
            'new_password' => [
                'required',
                'string',
                Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),
                'confirmed'
            ],
        ]);
        
        if ($validator->fails()) {
            $respuesta = [
                'status'  => 422,
                'message' => 'Error de validación',
                'errors'  => $validator->errors(),
                'data'    => null
            ];
            return response()->json($respuesta, 422);
        }
        
        // Verificación manual de la contraseña actual
        if (!Hash::check($request->current_password, $request->user()->password)) {
            $respuesta = [
                'status'  => 401,
                'message' => 'La contraseña actual no es válida',
                'errors'  => null,
                'data'    => null
            ];
            return response()->json($respuesta, 401);
        }
        
        // Actualización de la contraseña
        $request->user()->update([
            'password' => Hash::make($request->new_password)
        ]);
        
        // Opcional: Invalidar otros tokens del usuario (corrección de sintaxis)
        $request->user()->tokens()->delete();
        
        // Respuesta (corrección de sintaxis)
        $respuesta = [
            'status'  => 200,
            'message' => 'Contraseña actualizada correctamente',
            'errors'  => null,
            'data'    => [
                'token' => $request->user()->createToken('api-token')->plainTextToken
            ]
        ];
        
        return response()->json($respuesta, 200);
    }
}
