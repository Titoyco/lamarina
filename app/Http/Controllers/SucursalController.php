<?php

namespace App\Http\Controllers;

use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SucursalController extends Controller
{
    // Listar sucursales
    public function index()
    {
        $sucursales = Sucursal::all();
        return view('sucursales.index', compact('sucursales'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        return view('sucursales.create');
    }

    // Guardar nueva sucursal
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'size:1',
                'unique:sucursales,codigo',
                'regex:/^[A-Z]$/'
            ],
        ]);

        Sucursal::create($request->only('nombre', 'codigo'));

        return redirect()->route('sucursales.index')->with('success', 'Sucursal creada correctamente');
    }

    // Mostrar una sucursal
    public function show(Sucursal $sucursal)
    {
        return view('sucursales.show', compact('sucursal'));
    }

    // Mostrar formulario de edición (solo nombre)
    public function edit(Sucursal $sucursal)
    {
        return view('sucursales.edit', compact('sucursal'));
    }

    // Actualizar sucursal (solo nombre)
    public function update(Request $request, Sucursal $sucursal)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $sucursal->update($request->only('nombre'));

        return redirect()->route('sucursales.index')->with('success', 'Sucursal actualizada correctamente');
    }

    // Eliminar sucursal - NO IMPLEMENTADO
    // public function destroy(Sucursal $sucursal) {}

    // Mantengo tu método personalizado
    public function cambiarSucursal(Request $request)
    {
        $user = Auth::user();
        $sucursales = $user->sucursales;
        $sucursalId = $request->input('sucursal_id');

        if ($sucursales->contains('id', $sucursalId)) {
            Session::put('sucursal_activa', $sucursalId);
            return redirect()->back()->with('success', 'Sucursal cambiada con éxito.');
        }

        return redirect()->back()->with('error', 'No tienes permiso para acceder a esta sucursal.');
    }
}