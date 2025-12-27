<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si hay un token en el header Authorization
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token de acceso requerido',
                'code' => 'TOKEN_MISSING',
                'data' => null,
                'errors' => [
                    'auth' => ['No se proporcionó un token de acceso en el header Authorization']
                ]
            ], 401);
        }

        // Intentar autenticar con el token
        try {
            if (!Auth::guard('api')->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido o expirado',
                    'code' => 'TOKEN_INVALID',
                    'data' => null,
                    'errors' => [
                        'auth' => ['El token proporcionado no es válido o ha expirado']
                    ]
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación',
                'code' => 'AUTH_ERROR',
                'data' => null,
                'errors' => [
                    'auth' => ['Error interno al validar el token']
                ]
            ], 401);
        }

        // Verificar si el usuario está activo (opcional, dependiendo de tu lógica)
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
                'code' => 'USER_NOT_FOUND',
                'data' => null,
                'errors' => [
                    'auth' => ['El usuario asociado al token no existe']
                ]
            ], 401);
        }

        return $next($request);
    }
}