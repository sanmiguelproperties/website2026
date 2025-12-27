<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
                'code' => 'USER_NOT_AUTHENTICATED',
                'data' => null,
                'errors' => [
                    'auth' => ['El usuario no está autenticado']
                ]
            ], 401);
        }

        if (!$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. Se requiere rol de administrador.',
                'code' => 'ACCESS_DENIED',
                'data' => null,
                'errors' => [
                    'auth' => ['No tienes permisos para realizar esta acción']
                ]
            ], 403);
        }

        return $next($request);
    }
}