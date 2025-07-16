<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckRoutePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Obtener la URI actual
        $currentRoute = $request->route()->uri();

        // Verificar si la ruta está permitida para el usuario
        $hasPermission = DB::table('user_rutas')
            ->where('user_id', $user->id)
            ->where('ruta', $currentRoute)
            ->exists();

        if (!$hasPermission) {
            // Si no tiene permiso, devolver error 403
            abort(403, 'Permiso denegado');
        }

        // Continuar con la solicitud
        return $next($request);
    }
}