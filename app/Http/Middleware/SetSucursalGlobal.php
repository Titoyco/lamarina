<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sucursal;

class SetSucursalGlobal
{
    public function handle(Request $request, Closure $next)
    {
        // Solo actuamos si el usuario está logueado
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->is_admin) {
                // Administrador: toma la sucursal de la sesión, o la asignada, o la primera existente
                $id = session('sucursal_activa_id') ?? $user->id_sucursal ?? 1;
            } else {
                // Vendedor / Operador: SIEMPRE anclado a su id_sucursal asignada
                $id = $user->id_sucursal;
                session(['sucursal_activa_id' => $id]);
            }
            
            $sucursal = Sucursal::find($id) ?? Sucursal::first();

            if ($sucursal) {
                // Guardamos como OBJETO para acceder con config('app.sucursal_activa')->nombre o ->id
                config(['app.sucursal_activa' => $sucursal]);
            }
        }

        return $next($request);
    }
}