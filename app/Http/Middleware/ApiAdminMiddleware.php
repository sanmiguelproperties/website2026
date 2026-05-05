<?php

namespace App\Http\Middleware;

use App\Support\Rbac;
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
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
                'code' => 'USER_NOT_AUTHENTICATED',
                'data' => null,
                'errors' => [
                    'auth' => ['El usuario no esta autenticado'],
                ],
            ], 401);
        }

        $requiredPermissions = $permissions === [] ? ['dashboard.view'] : $permissions;

        if (!Rbac::canAny($user, $requiredPermissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Acceso denegado. No tienes permisos para realizar esta accion.',
                'code' => 'ACCESS_DENIED',
                'data' => null,
                'errors' => [
                    'auth' => ['No tienes permisos para realizar esta accion'],
                    'permissions' => Rbac::normalizePermissions($requiredPermissions),
                ],
            ], 403);
        }

        return $next($request);
    }
}
