<?php

namespace App\Http\Controllers;

use App\Models\Tipo_credito;
use Illuminate\Http\Request;

class Tipo_creditoController extends Controller
{
    public function index()
    {
        $tipos = Tipo_credito::all();
        return view('tipo_creditos.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipo_creditos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'size:1',
                'unique:tipo_credito,codigo',
                'regex:/^[A-Z]$/'
            ],
        ]);

        Tipo_credito::create($request->only('nombre', 'codigo'));

        return redirect()->route('tipo_credito.index')->with('success', 'Tipo de crédito creado correctamente');
    }

    public function show(Tipo_credito $tipo_credito)
    {
        return view('tipo_creditos.show', compact('tipo_credito'));
    }

    public function edit(Tipo_credito $tipo_credito)
    {
        return view('tipo_creditos.edit', compact('tipo_credito'));
    }

    public function update(Request $request, Tipo_credito $tipo_credito)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);
        $tipo_credito->update($request->only('nombre'));
        return redirect()->route('tipo_credito.index')->with('success', 'Tipo de crédito actualizado correctamente');
    }

    // No implementar destroy para no permitir eliminar
}