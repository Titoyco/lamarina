<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SetSucursalActiva
{
    public function handle($request, Closure $next)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Si no hay sucursal activa en la sesión, establecer la primera habilitada
        if (!Session::has('sucursal_activa') && $user) {
            // Obtener la primera sucursal habilitada para el usuario
            $primerSucursalId = DB::table('user_sucursales')
                ->where('user_id', $user->id)
                ->value('sucursal_id'); // Obtener directamente el ID de la sucursal

            if ($primerSucursalId) {
                Session::put('sucursal_activa', $primerSucursalId); // Guardar la sucursal activa en la sesión
            }
        }

        return $next($request);
    }
}