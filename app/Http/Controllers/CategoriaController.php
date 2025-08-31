<?php

namespace App\Http\Controllers;

use App\Models\ProductosCategoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the categorias.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorias = ProductosCategoria::orderBy('nombre')->get();
        return view('productos.categorias.index', compact('categorias')); // resources/views/productos/categorias/index.blade.php
    }

    public function show($id)
    {
        $categoria = ProductosCategoria::findOrFail($id);
        return view('productos.categorias.show', compact('categoria'));
    }

    /**
     * Show the form for creating a new categoria.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('productos.categorias.create'); // resources/views/productos/categorias/create.blade.php
    }

    /**
     * Store a newly created categoria in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:productos_categorias,nombre',
        ]);

        ProductosCategoria::create($request->only('nombre'));

        return redirect()->route('productos.categorias.index')->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Show the form for editing the specified categoria.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $categoria = ProductosCategoria::findOrFail($id);

        return view('productos.categorias.edit', compact('categoria')); // resources/views/productos/categorias/edit.blade.php
    }

    /**
     * Update the specified categoria in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:productos_categorias,nombre,' . $id,
        ]);

        $categoria = ProductosCategoria::findOrFail($id);
        $categoria->update($request->only('nombre'));

        return redirect()->route('productos.categorias.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Remove the specified categoria from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $categoria = ProductosCategoria::findOrFail($id);
        $categoria->delete();

        return redirect()->route('productos.categorias.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}