<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductoCategoria;
use App\Models\ProductoSubcategoria;

class ProductoCategoriaController extends Controller
{
    public function index()
    {
        $categorias = ProductoCategoria::withCount('productos')->orderBy('nombre')->get();
        $subcategorias = ProductoSubcategoria::withCount('productos')->orderBy('nombre')->get();

        return view('categorias.index', compact('categorias', 'subcategorias'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150|unique:productos_categorias,nombre',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        ProductoCategoria::create([
            'nombre' => strtoupper(trim($validated['nombre'])),
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        return back()->with('success', "Categoría '" . strtoupper(trim($validated['nombre'])) . "' creada exitosamente.");
    }

    public function update(Request $request, ProductoCategoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150|unique:productos_categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique' => 'Ya existe una categoría con ese nombre.',
        ]);

        $categoria->update([
            'nombre' => strtoupper(trim($validated['nombre'])),
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        return back()->with('success', "Categoría '{$categoria->nombre}' actualizada exitosamente.");
    }

    public function destroy(ProductoCategoria $categoria)
    {
        $productosCount = $categoria->productos()->count();
        if ($productosCount > 0) {
            return back()->with('error', "No se puede eliminar la categoría '{$categoria->nombre}' porque tiene {$productosCount} producto(s) asociado(s).");
        }

        $nombre = $categoria->nombre;
        $categoria->delete();

        return back()->with('success', "Categoría '{$nombre}' eliminada exitosamente.");
    }

    /**
     * Alta de subcategoría global (combinable con cualquier categoría).
     */
    public function storeSubcategoriaGlobal(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150|unique:productos_subcategorias,nombre',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la subcategoría es obligatorio.',
            'nombre.unique' => 'Ya existe una subcategoría con ese nombre.',
        ]);

        ProductoSubcategoria::create([
            'nombre' => strtoupper(trim($validated['nombre'])),
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        return back()->with('success', "Subcategoría '" . strtoupper(trim($validated['nombre'])) . "' creada exitosamente.");
    }

    /**
     * Edición de subcategoría global.
     */
    public function updateSubcategoriaGlobal(Request $request, ProductoSubcategoria $subcategoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150|unique:productos_subcategorias,nombre,' . $subcategoria->id,
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la subcategoría es obligatorio.',
            'nombre.unique' => 'Ya existe una subcategoría con ese nombre.',
        ]);

        $subcategoria->update([
            'nombre' => strtoupper(trim($validated['nombre'])),
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        return back()->with('success', "Subcategoría '{$subcategoria->nombre}' actualizada exitosamente.");
    }

    /**
     * Eliminación de subcategoría.
     */
    public function destroySubcategoria(ProductoSubcategoria $subcategoria)
    {
        $productosCount = $subcategoria->productos()->count();
        if ($productosCount > 0) {
            return back()->with('error', "No se puede eliminar la subcategoría '{$subcategoria->nombre}' porque tiene {$productosCount} producto(s) asociado(s).");
        }

        $nombre = $subcategoria->nombre;
        $subcategoria->delete();

        return back()->with('success', "Subcategoría '{$nombre}' eliminada exitosamente.");
    }

    /**
     * Compatibilidad con formularios o llamadas previas pasando {categoria}
     */
    public function storeSubcategoria(Request $request, ProductoCategoria $categoria = null)
    {
        return $this->storeSubcategoriaGlobal($request);
    }

    /**
     * Endpoint API/JSON para select de subcategorías globales.
     */
    public function getAllSubcategoriasJson()
    {
        $subcategorias = ProductoSubcategoria::orderBy('nombre')->get(['id', 'nombre']);
        return response()->json($subcategorias);
    }

    public function getSubcategorias(ProductoCategoria $categoria = null)
    {
        return $this->getAllSubcategoriasJson();
    }
}
