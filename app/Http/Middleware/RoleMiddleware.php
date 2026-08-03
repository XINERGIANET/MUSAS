<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, $roles)
    {
        if (!Auth::check()) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        $user = Auth::user();

        // Soporte para múltiples roles separados por |
        $roleList = explode('|', $roles);

        // Verifica si el usuario tiene al menos uno de los roles
        foreach ($roleList as $role) {
            if ($user->hasRole(trim($role))) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para acceder a esta página.');
    }
}
