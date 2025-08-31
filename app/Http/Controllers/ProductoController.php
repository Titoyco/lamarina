<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoCategoria;
use App\Models\ProductoSubcategoria;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\ProductoStock;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Picqer\Barcode\BarcodeGeneratorSVG;

class ProductoController extends Controller
{
    /**
     * Display a listing of the productos.
     *
     * @return \Illuminate\Http\Response
     */
     // Mostrar todos los productos con stock por sucursal
    public function index(Request $request)
    {
        $query = $request->input('query');
        $sort_by = $request->input('sort_by', 'codigo');
        $sort_order = $request->input('sort_order', 'asc');
        $in_stock = $request->input('in_stock', false);

        // Obtener la sucursal activa (desde la sesión)
        $sucursalId = session('sucursal_activa');
        if (!$sucursalId) {
            $sucursalId = Sucursal::first()?->id;
            if (!$sucursalId) {
                return redirect()->back()->with('error', 'No hay sucursales disponibles');
            }
            session(['sucursal_activa' => $sucursalId]);
        }

        $sucursalActiva = Sucursal::find($sucursalId);
        $sucursales = Sucursal::all();

        // Construir la consulta base
        $productosQuery = Producto::with([
            'categoria',
            'subcategoria',
            'proveedor',
            'sucursales' => function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            }
        ])
        // Left join para traer los datos de stock de la sucursal activa
        ->leftJoin('productos_stock', function($join) use ($sucursalId) {
            $join->on('productos.id', '=', 'productos_stock.producto_id')
                ->where('productos_stock.sucursal_id', '=', $sucursalId);
        })
        // Filtro de búsqueda
        ->when($query, function ($queryBuilder) use ($query) {
            $keywords = explode(' ', trim($query));
            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (!empty($keyword)) {
                    $queryBuilder->where(function ($queryBuilder) use ($keyword) {
                        $queryBuilder->where('productos.codigo', 'like', "%$keyword%")
                            ->orWhere('productos.descripcion', 'like', "%$keyword%")
                            ->orWhereHas('categoria', function ($categoryQuery) use ($keyword) {
                                $categoryQuery->where('nombre', 'like', "%$keyword%");
                            })
                            ->orWhereHas('subcategoria', function ($subcategoryQuery) use ($keyword) {
                                $subcategoryQuery->where('nombre', 'like', "%$keyword%");
                            })
                            ->orWhereHas('proveedor', function ($providerQuery) use ($keyword) {
                                $providerQuery->where('nombre', 'like', "%$keyword%");
                            });
                    });
                }
            }
        });

        // Solo filtrar productos con stock si in_stock está activo
        if ($in_stock) {
            $productosQuery->where('productos_stock.stock', '>', 0);
        }

        // Ordenamiento
        switch ($sort_by) {
            case 'categoria':
                $productosQuery->join('productos_categorias', 'productos.categoria_id', '=', 'productos_categorias.id')
                            ->orderBy('productos_categorias.nombre', $sort_order)
                            ->select('productos.*');
                break;

            case 'subcategoria':
                $productosQuery->join('productos_subcategorias', 'productos.subcategoria_id', '=', 'productos_subcategorias.id')
                            ->orderBy('productos_subcategorias.nombre', $sort_order)
                            ->select('productos.*');
                break;

            case 'proveedor':
                $productosQuery->join('proveedores', 'productos.proveedor_id', '=', 'proveedores.id')
                            ->orderBy('proveedores.nombre', $sort_order)
                            ->select('productos.*');
                break;

            case 'stock':
                $productosQuery->orderBy('productos_stock.stock', $sort_order)
                            ->select('productos.*');
                break;

            case 'precio_venta':
                $productosQuery->orderBy('productos_stock.precio_venta', $sort_order)
                            ->select('productos.*');
                break;

            default:
                $productosQuery->orderBy("productos.$sort_by", $sort_order);
                break;
        }

        // Evitar conflicto en select
        if (!in_array($sort_by, ['categoria', 'subcategoria', 'proveedor', 'stock', 'precio_venta'])) {
            $productosQuery->select('productos.*');
        }

        // Ejecutar la consulta
        $productos = $productosQuery->paginate(20);

        // Asignar info de stock a cada producto (si existe para la sucursal)
        foreach ($productos as $producto) {
            $stockSucursal = $producto->sucursales->first();
            $producto->stock_actual = $stockSucursal ? $stockSucursal->pivot->stock : null;
            $producto->precio_venta_actual = $stockSucursal ? $stockSucursal->pivot->precio_venta : null;
            $producto->punto_compra_actual = $stockSucursal ? $stockSucursal->pivot->punto_compra : null;
            $producto->stock_bajo = $producto->stock_actual !== null && $producto->punto_compra_actual !== null
                ? $producto->stock_actual <= $producto->punto_compra_actual && $producto->punto_compra_actual > 0
                : false;
        }

        return view('productos.index', compact(
            'productos',
            'sucursales',
            'sucursalActiva',
            'sort_by',
            'sort_order',
            'query',
            'in_stock'
        ));
    }


    public function generateBarcode($id)
    {
        $producto = Producto::findOrFail($id);
        $code = $producto->codigo;
        $sucursalId = session('sucursal_activa');

        // Obtener la relación de stock para la sucursal activa
        $stockSucursal = $producto->sucursales()->where('sucursal_id', $sucursalId)->first();

        // Si el producto no tiene relación con la sucursal activa, retorna null
        $precio_venta = $stockSucursal ? $stockSucursal->pivot->precio_venta : null;

        // Generar el código de barras
        $generator = new BarcodeGeneratorSVG();
        $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_39, 1, 30);

        return view('productos.barcode', compact('producto', 'barcode', 'precio_venta'));
    }

    // Mostrar un producto específico con su stock por sucursales
    public function show($id)
    {
        $producto = Producto::with(['categoria', 'subcategoria', 'proveedor', 'sucursales', 'ingresos'])
                            ->findOrFail($id);
        
        $stockPorSucursales = $producto->getStockPorSucursales();
        
        return view('productos.show', compact('producto', 'stockPorSucursales'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $sucursalId = session('sucursal_id', 3);

        $productos = Producto::whereHas('sucursales', function ($q) use ($sucursalId) {
                                $q->where('sucursal_id', $sucursalId);
                            })
                            ->where(function($q) use ($query) {
                                $q->where('descripcion', 'like', "%$query%")
                                  ->orWhere('codigo', 'like', "%$query%");
                            })
                            ->with(['sucursales' => function($q) use ($sucursalId) {
                                $q->where('sucursal_id', $sucursalId);
                            }])
                            ->get();
        return response()->json($productos);
    }

    public function searchcod(Request $request)
    {
        $query = $request->input('query');
        $sucursalId = session('sucursal_id', 3);

        $productos = Producto::whereHas('sucursales', function ($q) use ($sucursalId) {
                                $q->where('sucursal_id', $sucursalId);
                            })
                            ->where('codigo', 'like', "$query")
                            ->with(['sucursales' => function($q) use ($sucursalId) {
                                $q->where('sucursal_id', $sucursalId);
                            }])
                            ->get();
        return response()->json($productos);
    }

    public function create()
    {
        $categorias = ProductoCategoria::orderBy('nombre')->get();
        $subcategorias = ProductoSubcategoria::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();

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
            'iva' => 'required|numeric|min:0|max:100',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'ganancia' => 'required|numeric|min:0|max:100',
            'Imagen' => 'nullable|image|max:2048',
            'stock' => 'required|numeric|min:0',
            'punto_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_compra' => 'required|numeric|min:0',
        ]);

        $data = $request->except(['stock', 'punto_compra', 'precio_venta', 'precio_compra']);
        if ($request->hasFile('Imagen')) {
            $data['Imagen'] = $request->file('Imagen')->store('productos', 'public');
        }
        $producto = Producto::create($data);

        // Guardar datos en la sucursal activa
        $sucursalId = session('sucursal_id', 3);
        $producto->sucursales()->attach($sucursalId, [
            'stock' => $request->input('stock'),
            'punto_compra' => $request->input('punto_compra'),
            'precio_venta' => $request->input('precio_venta'),
        ]);

        /* Guardar ingreso inicial
        if($request->input('stock') > 0) {
            \App\Models\ProductosIngresos::create([
                'producto_id' => $producto->id,
                'sucursal_id' => $sucursalId,
                'cantidad' => $request->input('stock'),
                'precio_compra' => $request->input('precio_compra'),
                'fecha' => now(),
            ]);
        }*/

        return redirect()->route('productos.index')->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Show the form for editing the specified producto.
     */
    public function edit($id)
    {
        $producto = Producto::findOrFail($id);
        $categorias = ProductoCategoria::orderBy('nombre')->get();
        $subcategorias = ProductoSubcategoria::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();

        // Traer datos de la sucursal activa
        $sucursalId = session('sucursal_id', 3);
        $pivot = $producto->sucursales()->where('sucursal_id', $sucursalId)->first();

        return view('productos.edit', compact('producto', 'categorias', 'subcategorias', 'proveedores', 'pivot', 'sucursalId'));
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
            'iva' => 'required|numeric|min:0|max:100',
            'descuento' => 'nullable|numeric|min:0|max:100',
            'ganancia' => 'required|numeric|min:0|max:100',
            'Imagen' => 'nullable|image|max:2048',
            'stock' => 'required|numeric|min:0',
            'punto_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_compra' => 'required|numeric|min:0',
        ]);

        $data = $request->except(['stock', 'punto_compra', 'precio_venta', 'precio_compra']);

        if ($request->hasFile('Imagen')) {
            $data['Imagen'] = $request->file('Imagen')->store('productos', 'public');
        }

        $producto->update($data);

        // Actualizar datos en la sucursal activa
        $sucursalId = session('sucursal_id', 3);
        $pivot = $producto->sucursales()->where('sucursal_id', $sucursalId)->first();

        if ($pivot) {
            $producto->sucursales()->updateExistingPivot($sucursalId, [
                'stock' => $request->input('stock'),
                'punto_compra' => $request->input('punto_compra'),
                'precio_venta' => $request->input('precio_venta'),
            ]);
        } else {
            $producto->sucursales()->attach($sucursalId, [
                'stock' => $request->input('stock'),
                'punto_compra' => $request->input('punto_compra'),
                'precio_venta' => $request->input('precio_venta'),
            ]);
        }

        /* Guardar ingreso de stock si se incrementa stock
        if ($request->input('stock') > 0 && $request->input('precio_compra') > 0) {
            \App\Models\ProductosIngresos::create([
                'producto_id' => $producto->id,
                'sucursal_id' => $sucursalId,
                'cantidad' => $request->input('stock'),
                'precio_compra' => $request->input('precio_compra'),
                'fecha' => now(),
            ]);
        } */

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