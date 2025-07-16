<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Sucursal;
use Illuminate\Support\Facades\DB;

class PermisosController extends Controller
{
    /**
     * Muestra la lista de usuarios con sus permisos.
     */
    public function index()
    {
        $usuarios = User::with(['sucursales', 'rutas'])->get();
        return view('auth.permisos', compact('usuarios'));
    }

    /**
     * Muestra el formulario para editar permisos de un usuario.
     */
    public function edit($id)
    {
        $usuario = User::with(['sucursales', 'rutas'])->findOrFail($id);
        $todasSucursales = Sucursal::all();
        $todasRutas = DB::table('user_rutas')->distinct()->pluck('ruta');

        // Obtener todas las rutas que usan el middleware "auth"
        $authRoutes = $this->getRoutes();

        return view('auth.editar-permisos', compact('usuario', 'todasSucursales', 'todasRutas', 'authRoutes'));
    }

    /**
     * Actualiza los permisos de un usuario.
     */
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        // Actualizar sucursales asignadas
        $usuario->sucursales()->sync($request->input('sucursales', []));

        // Actualizar rutas asignadas
        DB::table('user_rutas')->where('user_id', $id)->delete();
        if ($request->has('rutas')) {
            foreach ($request->input('rutas') as $ruta) {
                DB::table('user_rutas')->insert([
                    'user_id' => $id,
                    'ruta' => $ruta,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('permisos.index')->with('success', 'Permisos actualizados correctamente.');
    }

    /**
     * Obtiene las rutas que utilizan el middleware "auth".
     */
private function getRoutes()
{
    $routes = collect(Route::getRoutes())->filter(function ($route) {
        return in_array('auth', $route->middleware());
    });

    return $routes->map(function ($route) {
        return [
            'name' => $route->getName(),
            'uri' => $route->uri(),
        ];
    });
}
}