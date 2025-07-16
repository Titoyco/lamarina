<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductosCategoria;
use App\Models\ProductosSubcategoria;
use App\Models\Proveedor;
use Illuminate\Http\Request;

use Picqer\Barcode\BarcodeGeneratorSVG;


class ProductoController extends Controller
{
    /**
     * Display a listing of the productos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $productos = collect();

        $query = $request->input('query');
        $sort_by = $request->input('sort_by', 'codigo');
        $sort_order = $request->input('sort_order', 'asc');
        $in_stock = $request->input('in_stock', false);
           
        
        $productos = Producto::with(['categoria', 'subcategoria']) // Cargar las relaciones
            ->when($query, function ($queryBuilder) use ($query) {
                $keywords = explode(' ', $query);
                foreach ($keywords as $keyword) {
                    $queryBuilder->where(function ($queryBuilder) use ($keyword) {
                        $queryBuilder->where('codigo', 'like', "%$keyword%")
                            ->orWhere('descripcion', 'like', "%$keyword%")
                            ->orWhereHas('categoria', function ($queryBuilder) use ($keyword) {
                                $queryBuilder->where('nombre', 'like', "%$keyword%");
                            })
                            ->orWhereHas('subcategoria', function ($queryBuilder) use ($keyword) {
                                $queryBuilder->where('nombre', 'like', "%$keyword%");
                            })
                            ->orWhere('precio_venta', 'like', "%$keyword%");
                    });
                }
            })
            ->when($sort_by == 'categoria', function ($queryBuilder) use ($sort_order) {
                $queryBuilder->join('productos_categorias', 'productos.categoria_id', '=', 'productos_categorias.id')
                             ->orderBy('productos_categorias.nombre', $sort_order);
            })
            ->when($sort_by == 'subcategoria', function ($queryBuilder) use ($sort_order) {
                $queryBuilder->join('productos_subcategorias', 'productos.subcategoria_id', '=', 'productos_subcategorias.id')
                             ->orderBy('productos_subcategorias.nombre', $sort_order);
            })
            ->when($sort_by != 'categoria' && $sort_by != 'subcategoria', function ($queryBuilder) use ($sort_by, $sort_order) {
                $queryBuilder->orderBy($sort_by, $sort_order);
            })
            ->when($in_stock, function ($queryBuilder) {
                $queryBuilder->where('stock', '>', 0);
            })
            ->select('productos.*') // Asegurarse de seleccionar solo columnas de la tabla productos para evitar conflictos
            ->paginate(20);

        return view('productos.index', compact('productos', 'sort_by', 'sort_order', 'query', 'in_stock'));
    }


    public function generateBarcode($id)
    {
        $producto = Producto::findOrFail($id);
        $code = $producto->codigo;

        // Generar el código de barras
        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_39,1,30); //TYPE_CODE_39   o   TYPE_EAN_13

        return view('productos.barcode', compact('producto', 'barcode'));
    }



    public function show($id)
    {
        $producto = Producto::with(['categoria', 'subcategoria', 'proveedor'])->findOrFail($id);
        return view('productos.show', compact('producto'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $productos = Producto::where('descripcion', 'like', "%$query%")
                            ->orWhere('codigo', 'like', "%$query%")
                            ->get();
        return response()->json($productos);
    }
    public function searchcod(Request $request)
    {
        $query = $request->input('query');
        $productos = Producto::where('codigo', 'like', "$query")
                            ->get();
        return response()->json($productos);
    }

  /**
     * Show the form for creating a new producto.
     */
    public function create()
    {
        $categorias = ProductosCategoria::orderBy('nombre')->get(); // Recuperar categorías
        $subcategorias = ProductosSubcategoria::orderBy('nombre')->get(); // Recuperar subcategorías
        $proveedores = Proveedor::orderBy('nombre')->get(); // Recuperar proveedores

        return view('productos.create', compact('categorias', 'subcategorias', 'proveedores'));
    }

    /**
     * Store a newly created producto in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:255|unique:productos,codigo',
            'descripcion' => 'required|string|max:255',
            'categoria_id' => 'required|exists:productos_categorias,id',
            'subcategoria_id' => 'nullable|exists:productos_subcategorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'stock' => 'required|integer|min:0',
            'punto_compra' => 'required|integer|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'iva' => 'required|numeric|min:0|max:100',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'ganancia' => 'required|numeric|min:0|max:100',
            'precio_venta' => 'required|numeric|min:0',
            'Imagen' => 'nullable|image|max:2048'
        ]);

        $data = $request->all();

        if ($request->hasFile('Imagen')) {
            $data['Imagen'] = $request->file('Imagen')->store('productos', 'public');
        }

        Producto::create($data);

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Show the form for editing the specified producto.
     */
    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        $categorias = ProductosCategoria::orderBy('nombre')->get(); // Recuperar categorías
        $subcategorias = ProductosSubcategoria::orderBy('nombre')->get(); // Recuperar subcategorías
        $proveedores = Proveedor::orderBy('nombre')->get(); // Recuperar proveedores

        return view('productos.edit', compact('producto', 'categorias', 'subcategorias', 'proveedores'));
    }

    /**
     * Update the specified producto in storage.
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'codigo' => 'required|string|max:255|unique:productos,codigo,' . $id,
            'descripcion' => 'required|string|max:255',
            'categoria_id' => 'required|exists:productos_categorias,id',
            'subcategoria_id' => 'nullable|exists:productos_subcategorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'stock' => 'required|integer|min:0',
            'punto_compra' => 'required|integer|min:0',
            'precio_compra' => 'required|numeric|min:0',
            'iva' => 'required|numeric|min:0|max:100',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'ganancia' => 'required|numeric|min:0|max:100',
            'precio_venta' => 'required|numeric|min:0',
            'Imagen' => 'nullable|image|max:2048'
        ]);

        $data = $request->all();

        if ($request->hasFile('Imagen')) {
            $data['Imagen'] = $request->file('Imagen')->store('productos', 'public');
        }

        $producto->update($data);

        return redirect()->route('productos.index')->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Remove the specified producto from storage.
     */
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();

        return redirect()->route('productos.index')->with('success', 'Producto eliminado exitosamente.');
    }
}


