<?php

namespace App\Http\Controllers;

use App\Models\ProductosSubcategoria;
use Illuminate\Http\Request;

class SubcategoriaController extends Controller
{
    /**
     * Display a listing of the subcategorias.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subcategorias = ProductosSubcategoria::orderBy('nombre')->get();
        return view('productos.subcategorias', compact('subcategorias')); // resources/views/productos/subcategorias.blade.php
    }

    /**
     * Show the form for creating a new subcategoria.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('productos.create-subcategoria'); // resources/views/productos/create-subcategoria.blade.php
    }

    /**
     * Store a newly created subcategoria in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:productos_subcategorias,nombre',
        ]);

        ProductosSubcategoria::create($request->only('nombre'));

        return redirect()->route('subcategorias.index')->with('success', 'Subcategoría creada exitosamente.');
    }

    /**
     * Show the form for editing the specified subcategoria.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $subcategoria = ProductosSubcategoria::findOrFail($id);
        return view('productos.edit-subcategoria', compact('subcategoria')); // resources/views/productos/edit-subcategoria.blade.php
    }

    /**
     * Update the specified subcategoria in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:productos_subcategorias,nombre,' . $id,
        ]);

        $subcategoria = ProductosSubcategoria::findOrFail($id);
        $subcategoria->update($request->only('nombre'));

        return redirect()->route('subcategorias.index')->with('success', 'Subcategoría actualizada exitosamente.');
    }

    /**
     * Remove the specified subcategoria from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $subcategoria = ProductosSubcategoria::findOrFail($id);
        $subcategoria->delete();

        return redirect()->route('subcategorias.index')->with('success', 'Subcategoría eliminada exitosamente.');
    }
}