<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Login con email y password -> devuelve token
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required','email'],
            'password' => ['required'],
            // opcional: 'device_name' => ['required','string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        $user = $request->user();

        // Abilities del token (puedes granular: ['read'], ['read','write'], etc.)
        $abilities = ['*'];
        $deviceName = $request->input('device_name', 'mobile');

        // Si quieres caducidad, configúralo en config/sanctum.php o usa tokens expiring via job/cron
        $token = $user->createToken($deviceName, $abilities)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ]);
    }

    // Cerrar sesión del dispositivo actual (revoca el token usado en el request)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada']);
    }

    // Ejemplo de ruta protegida
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
