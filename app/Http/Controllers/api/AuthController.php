<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class AuthController extends Controller
{
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
