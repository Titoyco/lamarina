<?php

namespace App\Http\Controllers;

use App\Models\EstadoBanco;
use Illuminate\Http\Request;

class EstadoBancoController extends Controller
{
    /**
     * Muestra el listado de códigos de banco.
     */
    public function index(Request $request)
    {
        $query = EstadoBanco::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'LIKE', "%{$search}%")
                  ->orWhere('descripcion', 'LIKE', "%{$search}%");
            });
        }

        $estadosBanco = $query->orderBy('codigo', 'asc')
                              ->paginate(15)
                              ->withQueryString();

        return view('estados_banco.index', compact('estadosBanco'));
    }

    /**
     * Muestra el formulario para crear un nuevo código.
     */
    public function create()
    {
        return view('estados_banco.create');
    }

    /**
     * Guarda un nuevo código de estado de banco.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:10|unique:estados_banco,codigo',
            'descripcion' => 'required|string|max:191',
        ], [
            'codigo.required' => 'El código de banco es obligatorio.',
            'codigo.unique' => 'Este código de banco ya existe en la base de datos.',
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        // Guardamos el código siempre en mayúsculas
        $validated['codigo'] = strtoupper(trim($validated['codigo']));

        EstadoBanco::create($validated);

        return redirect()->route('estados-banco.index')
                         ->with('success', 'Código de estado de banco creado correctamente.');
    }

    /**
     * Muestra el formulario para editar un código existente.
     */
    public function edit(EstadoBanco $estadoBanco)
    {
        return view('estados_banco.edit', compact('estadoBanco'));
    }

    /**
     * Actualiza un código de estado de banco en la base de datos.
     */
    public function update(Request $request, EstadoBanco $estadoBanco)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:10|unique:estados_banco,codigo,' . $estadoBanco->id,
            'descripcion' => 'required|string|max:191',
        ], [
            'codigo.required' => 'El código de banco es obligatorio.',
            'codigo.unique' => 'Este código de banco ya pertenece a otro registro.',
            'descripcion.required' => 'La descripción es obligatoria.',
        ]);

        $validated['codigo'] = strtoupper(trim($validated['codigo']));

        $estadoBanco->update($validated);

        return redirect()->route('estados-banco.index')
                         ->with('success', 'Código de estado de banco actualizado correctamente.');
    }

    /**
     * Elimina un código de estado de banco.
     */
    public function destroy(EstadoBanco $estadoBanco)
    {
        // Se puede agregar una verificación previa para no borrar códigos que ya estén asignados a algún cliente
        $estadoBanco->delete();

        return redirect()->route('estados-banco.index')
                         ->with('success', 'Código de estado de banco eliminado correctamente.');
    }
}