<?php

namespace App\Http\Middleware;

use App\Support\Rbac;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (isset(auth()->user()->is_active) && !auth()->user()->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        $requiredPermissions = $permissions === [] ? ['dashboard.view'] : $permissions;

        if (in_array('super-admin', $requiredPermissions, true)) {
            if (!Rbac::isSuperAdmin(auth()->user())) {
                abort(403, 'Acceso denegado. Esta seccion es solo para administradores.');
            }

            return $next($request);
        }

        if (!Rbac::canAny(auth()->user(), $requiredPermissions)) {
            abort(403, 'Acceso denegado. No tienes permisos para esta seccion.');
        }

        return $next($request);
    }
}
