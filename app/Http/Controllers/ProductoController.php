<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;
use App\Models\ProductoStock;
use App\Models\ProductoMovimiento;
use App\Models\ProductoCategoria;
use App\Models\ProductoSubcategoria;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Illuminate\Support\Facades\Auth;
use Exception;

class ProductoController extends Controller
{
    /**
     * Muestra el catálogo unificado de artículos y stock.
     */
    public function index(Request $request)
    {
        $sucursales = Sucursal::orderBy('id')->get();
        $sucursalActiva = config('app.sucursal_activa');
        $sucursalActivaId = $sucursalActiva ? $sucursalActiva->id : (session('sucursal_activa_id') ?? Auth::user()?->id_sucursal ?? 1);
        if (!$sucursalActiva) {
            $sucursalActiva = $sucursales->firstWhere('id', $sucursalActivaId) ?? $sucursales->first();
        }

        // Determinación del filtro de sucursal:
        // 'todas' o 'all' -> consolidado multi-sucursal ($sucId = null)
        // ID numérico -> filtra esa sucursal
        // No especificado en request -> de entrada se aplica la sucursal activa
        if ($request->has('sucursal_id')) {
            $rawSucId = $request->input('sucursal_id');
            if ($rawSucId === 'todas' || $rawSucId === 'all' || $rawSucId === '') {
                $sucId = null;
            } elseif (is_numeric($rawSucId) && (int)$rawSucId > 0) {
                $sucId = (int)$rawSucId;
            } else {
                $sucId = null;
            }
        } else {
            // De entrada: aplicado el filtro de stock por la sucursal activa
            $sucId = $sucursalActivaId;
        }

        // Habilitar o no mostrar el stock de las demás sucursales en la tabla:
        // Si no se filtra por sucursal ($sucId === null), siempre se muestran todas.
        // Si se filtra una sucursal, por defecto está en false (oculto) salvo que el botón lo habilite con ver_otras=1
        $verOtras = $sucId ? $request->boolean('ver_otras', false) : true;

        $query = Producto::with(['categoria', 'subcategoria', 'proveedor', 'stocks.sucursal']);

        // Búsqueda por texto (código, código de barras, código proveedor/fábrica, talle, color, descripción o proveedor)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('codigo_barra', 'like', "%{$search}%")
                  ->orWhere('codigo_proveedor', 'like', "%{$search}%")
                  ->orWhere('talle', 'like', "%{$search}%")
                  ->orWhere('color', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%")
                  ->orWhereHas('proveedor', function ($qp) use ($search) {
                      $qp->where('legajo', 'like', "%{$search}%")
                         ->orWhere('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro específico por talle
        if ($talleFiltro = $request->input('talle')) {
            $query->where('talle', $talleFiltro);
        }

        // Filtro específico por color
        if ($colorFiltro = $request->input('color')) {
            $query->where('color', 'like', "%{$colorFiltro}%");
        }

        // Filtro por categoría
        if ($catId = $request->input('categoria_id')) {
            $query->where('categoria_id', $catId);
        }

        // Filtro por subcategoría (global / transversal a cualquier categoría)
        if ($subcatId = $request->input('subcategoria_id')) {
            $query->where('subcategoria_id', $subcatId);
        }

        // Filtro por proveedor
        if ($provId = $request->input('proveedor_id')) {
            $query->where('proveedor_id', $provId);
        }

        // Filtro de estado de stock y sucursal
        $stockStatus = $request->input('stock_status');

        if ($sucId) {
            // Filtro por sucursal específica (por defecto la activa)
            if ($stockStatus === 'agotado') {
                $query->where('controla_stock', true)
                      ->whereDoesntHave('stocks', function ($q) use ($sucId) {
                          $q->where('sucursal_id', $sucId)->where('stock', '>', 0);
                      });
            } elseif ($stockStatus === 'todos' || ($search && $stockStatus !== 'con_stock')) {
                // Muestra todos los artículos del catálogo coincidentes con la búsqueda o filtro todos
            } else {
                // Por defecto o 'con_stock': muestra lo que tiene stock > 0 en la sucursal o artículos genéricos libres
                $query->where(function ($q) use ($sucId) {
                    $q->whereHas('stocks', function ($qs) use ($sucId) {
                        $qs->where('sucursal_id', $sucId)->where('stock', '>', 0);
                    })->orWhere('controla_stock', false);
                });
            }
        } else {
            // Modo consolidado (todas las sucursales)
            if ($stockStatus === 'agotado') {
                $query->where('controla_stock', true)
                      ->whereDoesntHave('stocks', function ($q) {
                          $q->where('stock', '>', 0);
                      });
            } elseif ($stockStatus === 'con_stock') {
                $query->whereHas('stocks', function ($q) {
                    $q->where('stock', '>', 0);
                });
            }
        }

        // 6. Filtro de rotación de mercadería / antigüedad de venta
        if ($rotacion = $request->input('rotacion')) {
            if ($rotacion === 'mas_vendidos') {
                $query->where('controla_stock', true)
                      ->where('total_ventas_unidades', '>=', 20);
            } elseif ($rotacion === 'menos_vendidos') {
                $query->where('controla_stock', true)
                      ->where('total_ventas_unidades', '<=', 2);
            } elseif ($rotacion === 'sin_venta_2_anos') {
                $query->where('controla_stock', true)
                      ->where(function ($q) {
                          $q->where('fecha_ultima_venta', '<', now()->subYears(2))
                            ->orWhereNull('fecha_ultima_venta');
                      });
            } elseif ($rotacion === 'sin_venta_1_ano') {
                $query->where('controla_stock', true)
                      ->where(function ($q) {
                          $q->where('fecha_ultima_venta', '<', now()->subYear())
                            ->orWhereNull('fecha_ultima_venta');
                      });
            } elseif ($rotacion === 'sin_venta_6_meses') {
                $query->where('controla_stock', true)
                      ->where(function ($q) {
                          $q->where('fecha_ultima_venta', '<', now()->subMonths(6))
                            ->orWhereNull('fecha_ultima_venta');
                      });
            } elseif ($rotacion === 'nunca_vendido') {
                $query->where('controla_stock', true)
                      ->whereNull('fecha_ultima_venta')
                      ->where(function ($q) {
                          $q->whereNull('total_ventas_unidades')
                            ->orWhere('total_ventas_unidades', '<=', 0);
                      });
            } elseif ($rotacion === 'venta_reciente') {
                $query->where('controla_stock', true)
                      ->where('fecha_ultima_venta', '>=', now()->subDays(90));
            }
        }

        // 7. Filtro por fecha de alta / incorporación
        if ($fechaAltaDesde = $request->input('fecha_alta_desde')) {
            $query->whereDate('fecha_alta', '>=', $fechaAltaDesde);
        }
        if ($fechaAltaHasta = $request->input('fecha_alta_hasta')) {
            $query->whereDate('fecha_alta', '<=', $fechaAltaHasta);
        }

        $isFiltered = $request->anyFilled([
            'search', 'categoria_id', 'subcategoria_id', 'proveedor_id', 
            'stock_status', 'rotacion', 'fecha_alta_desde', 'fecha_alta_hasta',
            'talle', 'color'
        ]) || ($request->has('sucursal_id') && $request->input('sucursal_id') !== (string)$sucursalActivaId);

        // 8. Ordenamiento interactivo por encabezados de columna (Click-to-Sort)
        $sortableColumns = [
            'codigo'             => 'productos.codigo',
            'descripcion'        => 'productos.descripcion',
            'precio'             => 'productos.precio_venta',
            'fecha_alta'         => 'productos.fecha_alta',
            'fecha_modificacion' => 'productos.fecha_modificacion',
            'ultima_venta'       => 'productos.fecha_ultima_venta',
            'ventas'             => 'productos.total_ventas_unidades',
            'rotacion_cero'      => 'rotacion_cero',
            'stock'              => 'total_stock_sum',
        ];

        // Mapeo retrocompatible si llega parámetro legacy 'orden'
        if ($request->filled('orden') && !$request->has('sort')) {
            $legacyMap = [
                'descripcion_asc'         => ['sort' => 'descripcion', 'direction' => 'asc'],
                'descripcion_desc'        => ['sort' => 'descripcion', 'direction' => 'desc'],
                'stock_desc'              => ['sort' => 'stock', 'direction' => 'desc'],
                'stock_asc'               => ['sort' => 'stock', 'direction' => 'asc'],
                'ventas_desc'             => ['sort' => 'ventas', 'direction' => 'desc'],
                'ventas_asc'              => ['sort' => 'ventas', 'direction' => 'asc'],
                'rotacion_cero'           => ['sort' => 'rotacion_cero', 'direction' => 'asc'],
                'ultima_venta_desc'       => ['sort' => 'ultima_venta', 'direction' => 'desc'],
                'fecha_alta_desc'         => ['sort' => 'fecha_alta', 'direction' => 'desc'],
                'fecha_alta_asc'          => ['sort' => 'fecha_alta', 'direction' => 'asc'],
                'fecha_modificacion_desc' => ['sort' => 'fecha_modificacion', 'direction' => 'desc'],
                'precio_desc'             => ['sort' => 'precio', 'direction' => 'desc'],
                'precio_asc'              => ['sort' => 'precio', 'direction' => 'asc'],
            ];
            $ordenParam = $request->input('orden');
            if (isset($legacyMap[$ordenParam])) {
                $sort = $legacyMap[$ordenParam]['sort'];
                $direction = $legacyMap[$ordenParam]['direction'];
            } else {
                $sort = 'descripcion';
                $direction = 'asc';
            }
        } else {
            $sort = $request->input('sort', 'descripcion');
            $direction = strtolower($request->input('direction', 'asc'));
        }

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'descripcion';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        switch ($sort) {
            case 'codigo':
                $query->orderBy('productos.codigo', $direction);
                break;

            case 'precio':
                $query->orderBy('productos.precio_venta', $direction);
                break;

            case 'fecha_alta':
                if ($direction === 'desc') {
                    $query->orderByRaw('productos.fecha_alta IS NULL ASC, productos.fecha_alta DESC');
                } else {
                    $query->orderByRaw('productos.fecha_alta IS NULL DESC, productos.fecha_alta ASC');
                }
                break;

            case 'fecha_modificacion':
                if ($direction === 'desc') {
                    $query->orderByRaw('productos.fecha_modificacion IS NULL ASC, productos.fecha_modificacion DESC');
                } else {
                    $query->orderByRaw('productos.fecha_modificacion IS NULL DESC, productos.fecha_modificacion ASC');
                }
                break;

            case 'ultima_venta':
                if ($direction === 'desc') {
                    $query->orderByRaw('productos.fecha_ultima_venta IS NULL ASC, productos.fecha_ultima_venta DESC');
                } else {
                    $query->orderByRaw('productos.fecha_ultima_venta IS NULL DESC, productos.fecha_ultima_venta ASC');
                }
                break;

            case 'ventas':
                if ($direction === 'desc') {
                    $query->orderByDesc('productos.total_ventas_unidades')->orderByDesc('productos.fecha_ultima_venta');
                } else {
                    $query->orderBy('productos.total_ventas_unidades', 'asc')->orderByRaw('productos.fecha_ultima_venta IS NULL DESC, productos.fecha_ultima_venta ASC');
                }
                break;

            case 'rotacion_cero':
                $query->orderByRaw('productos.fecha_ultima_venta IS NULL DESC, productos.fecha_ultima_venta ASC');
                break;

            case 'stock':
                if ($sucId) {
                    $query->withSum(['stocks as total_stock_sum' => function ($q) use ($sucId) {
                        $q->where('sucursal_id', $sucId);
                    }], 'stock')->orderBy('total_stock_sum', $direction);
                } else {
                    $query->withSum('stocks as total_stock_sum', 'stock')->orderBy('total_stock_sum', $direction);
                }
                break;

            case 'descripcion':
            default:
                $query->orderBy('productos.descripcion', $direction);
                break;
        }

        if ($sort !== 'codigo') {
            $query->orderBy('productos.id', 'desc');
        }

        // Exportación a Excel / CSV con todos los filtros aplicados
        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            $exportQuery = clone $query;
            $filename = 'catalogo_stock_' . date('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($exportQuery) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

                fputcsv($handle, [
                    'Codigo',
                    'Codigo Barras',
                    'Descripcion',
                    'Categoria',
                    'Subcategoria',
                    'Fabricante / Proveedor',
                    'Precio Costo',
                    'Precio Venta',
                    'Stock Total',
                    'Fecha Alta',
                    'Ultima Venta',
                    'Total Ventas Unidades',
                ], ';');

                $exportQuery->chunk(1000, function ($chunk) use ($handle) {
                    foreach ($chunk as $p) {
                        fputcsv($handle, [
                            $p->codigo,
                            $p->codigo_barras ?? '-',
                            $p->descripcion,
                            $p->categoria?->nombre ?? '-',
                            $p->subcategoria?->nombre ?? '-',
                            $p->proveedor?->nombre ?? '-',
                            number_format((float)($p->precio_costo ?? 0), 2, ',', ''),
                            number_format((float)($p->precio_venta ?? 0), 2, ',', ''),
                            number_format((float)($p->stock_total ?? 0), 2, ',', ''),
                            $p->fecha_alta ? \Carbon\Carbon::parse($p->fecha_alta)->format('d/m/Y') : '-',
                            $p->fecha_ultima_venta ? \Carbon\Carbon::parse($p->fecha_ultima_venta)->format('d/m/Y') : 'Sin ventas',
                            (int)($p->total_ventas_unidades ?? 0),
                        ], ';');
                    }
                });

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Clonamos la query filtrada para paginar
        $productos = (clone $query)->paginate(20)->withQueryString();

        $categorias = ProductoCategoria::orderBy('nombre')->get();
        $subcategorias = ProductoSubcategoria::orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)
            ->orderByRaw('CASE WHEN legajo IS NULL THEN 1 ELSE 0 END, legajo ASC, nombre ASC')
            ->get();

        $sucursalFiltrada = $sucId ? $sucursales->firstWhere('id', $sucId) : null;

        // Métricas reactivas a la búsqueda actual (excluyendo artículos sin control de stock / genéricos)
        $metricsQuery = ProductoStock::query()
            ->join('productos', 'productos_stock.producto_id', '=', 'productos.id')
            ->where('productos.controla_stock', true)
            ->whereNull('productos.deleted_at')
            ->whereIn('productos.id', (clone $query)->reorder()->select('productos.id'));

        if ($sucId) {
            $metricsQuery->where('productos_stock.sucursal_id', $sucId);
        }

        $stats = $metricsQuery->selectRaw('
            SUM(productos_stock.stock) as total_stock_unidades,
            COUNT(DISTINCT CASE WHEN productos_stock.stock > 0 THEN productos_stock.sucursal_id END) as sucursales_con_stock,
            SUM(productos_stock.stock * productos.precio_venta) as valor_stock_venta,
            SUM(productos_stock.stock * productos.precio_costo) as valor_stock_costo
        ')->first();

        // Totales de los artículos visibles en la página actual del listado
        $pageItems = $productos->items();
        $pageArticulos = count($pageItems);
        $pageStockUnidades = 0;
        $pageValorVenta = 0;

        foreach ($pageItems as $item) {
            if ($item->controla_stock) {
                if ($sucId) {
                    $stockVal = (float)($item->stocks->firstWhere('sucursal_id', $sucId)?->stock ?? 0);
                } else {
                    $stockVal = (float)$item->stocks->sum('stock');
                }
                $pageStockUnidades += $stockVal;
                $pageValorVenta += ($stockVal * (float)$item->precio_venta);
            }
        }

        $metricas = [
            'is_filtered' => $isFiltered,
            'total_articulos' => (clone $query)->reorder()->count(),
            'total_stock_unidades' => (float)($stats->total_stock_unidades ?? 0),
            'sucursales_con_stock' => (int)($stats->sucursales_con_stock ?? 0),
            'valor_stock_venta' => (float)($stats->valor_stock_venta ?? 0),
            'valor_stock_costo' => (float)($stats->valor_stock_costo ?? 0),
            'page_articulos' => $pageArticulos,
            'page_stock_unidades' => $pageStockUnidades,
            'page_valor_venta' => $pageValorVenta,
            'sucursal_id_filtrada' => $sucId,
            'sucursal_filtrada' => $sucursalFiltrada,
            'sucursal_activa' => $sucursalActiva,
            'ver_otras' => $verOtras,
        ];

        return view('productos.index', compact('productos', 'categorias', 'subcategorias', 'proveedores', 'sucursales', 'metricas', 'sort', 'direction'));
    }

    /**
     * Formulario de alta de nuevo producto y recepción ágil de mercadería.
     */
    public function create()
    {
        $categorias = ProductoCategoria::orderBy('nombre')->get();
        $subcategorias = ProductoSubcategoria::orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)
            ->orderByRaw('CASE WHEN legajo IS NULL THEN 1 ELSE 0 END, legajo ASC, nombre ASC')
            ->get();
        $sucursales = Sucursal::orderBy('id')->get();
        $sucursalActivaId = session('sucursal_activa_id') ?? Auth::user()?->id_sucursal ?? 1;
        $tallesSugeridos = Producto::listaTallesSugeridos();
        $siguienteLegajoProveedor = Proveedor::sugerirSiguienteLegajo();

        return view('productos.create', compact('categorias', 'subcategorias', 'proveedores', 'sucursales', 'sucursalActivaId', 'tallesSugeridos', 'siguienteLegajoProveedor'));
    }

    /**
     * Búsqueda dinámica de artículos de un proveedor específico para recepción de mercadería.
     */
    public function buscarPorProveedor(Request $request)
    {
        $proveedorId = $request->input('proveedor_id');
        $q = trim((string)$request->input('q'));
        $sucursalId = (int)($request->input('sucursal_id') ?? session('sucursal_activa_id') ?? Auth::user()?->id_sucursal ?? 1);

        $query = Producto::with(['proveedor', 'stocks']);

        if (!empty($proveedorId)) {
            $query->where('proveedor_id', $proveedorId);
        }

        if (!empty($q)) {
            $query->where(function ($sub) use ($q) {
                $sub->where('codigo_proveedor', 'like', "%{$q}%")
                    ->orWhere('descripcion', 'like', "%{$q}%")
                    ->orWhere('codigo', 'like', "%{$q}%")
                    ->orWhere('codigo_barra', 'like', "%{$q}%")
                    ->orWhere('talle', 'like', "%{$q}%")
                    ->orWhere('color', 'like', "%{$q}%");
            });
        }

        $productos = $query->orderBy('descripcion', 'asc')
            ->take(30)
            ->get()
            ->map(function ($p) use ($sucursalId) {
                $stockSuc = (float)($p->stocks->firstWhere('sucursal_id', $sucursalId)?->stock ?? 0);
                $stockTotal = (float)$p->stocks->sum('stock');

                $desglose = Producto::desglosarCodigoBarra($p->codigo);
                $codProveedor = !empty($p->codigo_proveedor) ? $p->codigo_proveedor : $desglose['codigo_proveedor'];
                $talle = !empty($p->talle) ? $p->talle : $desglose['talle_sugerido'];
                $color = !empty($p->color) ? $p->color : $desglose['color_sugerido'];

                return [
                    'id' => $p->id,
                    'codigo' => $p->codigo,
                    'codigo_barra' => $p->codigo_barra ?: $p->codigo,
                    'codigo_proveedor' => $codProveedor,
                    'descripcion' => $p->descripcion,
                    'talle' => $talle,
                    'color' => $color,
                    'proveedor_id' => $p->proveedor_id,
                    'categoria_id' => $p->categoria_id,
                    'subcategoria_id' => $p->subcategoria_id,
                    'proveedor_nombre' => $p->proveedor?->nombre ?? 'Sin Proveedor',
                    'proveedor_legajo' => $p->proveedor?->legajo_formateado ?? $desglose['proveedor_legajo'] ?? '000',
                    'precio_costo' => (float)$p->precio_costo,
                    'precio_venta' => (float)$p->precio_venta,
                    'controla_stock' => (bool)$p->controla_stock,
                    'stock_sucursal' => $stockSuc,
                    'stock_total' => $stockTotal,
                ];
            });

        return response()->json($productos);
    }

    /**
     * Verifica en tiempo real si un código (código / código de barras) ya está asignado a otra prenda.
     */
    public function verificarCodigo(Request $request)
    {
        $codigo = strtoupper(trim((string)$request->input('codigo')));
        $excluirId = $request->input('excluir_id');
        $sucursalId = (int)($request->input('sucursal_id') ?? session('sucursal_activa_id') ?? Auth::user()?->id_sucursal ?? 1);

        if (empty($codigo)) {
            return response()->json(['existe' => false]);
        }

        $query = Producto::with(['proveedor', 'stocks'])
            ->where(function ($q) use ($codigo) {
                $q->where('codigo', $codigo)
                  ->orWhere('codigo_barra', $codigo);
            });

        if (!empty($excluirId)) {
            $query->where('id', '!=', $excluirId);
        }

        $existente = $query->first();

        if ($existente) {
            $stockSuc = (float)($existente->stocks->firstWhere('sucursal_id', $sucursalId)?->stock ?? 0);
            $stockTotal = (float)$existente->stocks->sum('stock');
            $desglose = Producto::desglosarCodigoBarra($existente->codigo);

            return response()->json([
                'existe' => true,
                'producto' => [
                    'id' => $existente->id,
                    'codigo' => $existente->codigo,
                    'codigo_barra' => $existente->codigo_barra,
                    'codigo_proveedor' => $existente->codigo_proveedor ?: $desglose['codigo_proveedor'],
                    'descripcion' => $existente->descripcion,
                    'descripcion_completa' => $existente->descripcion_completa,
                    'talle' => $existente->talle ?: $desglose['talle_sugerido'],
                    'color' => $existente->color ?: $desglose['color_sugerido'],
                    'proveedor_id' => $existente->proveedor_id,
                    'proveedor' => $existente->proveedor?->nombre,
                    'categoria_id' => $existente->categoria_id,
                    'subcategoria_id' => $existente->subcategoria_id,
                    'precio_costo' => (float)$existente->precio_costo,
                    'precio_venta' => (float)$existente->precio_venta,
                    'stock_sucursal' => $stockSuc,
                    'stock_total' => $stockTotal,
                ],
                'message' => "El código [{$codigo}] ya está asignado a: '{$existente->descripcion_completa}'"
            ]);
        }

        return response()->json([
            'existe' => false,
            'message' => "Código disponible para nuevo artículo."
        ]);
    }

    /**
     * Registra el ingreso ágil de mercadería:
     * Si el código/artículo ya existe en el catálogo, suma las unidades al stock existente.
     * Si no existe (nueva combinación de talle/color o artículo nuevo), lo da de alta automáticamente
     * y genera su stock inicial y etiquetas térmicas CODE128.
     */
    public function ingresoMercaderiaRapido(Request $request)
    {
        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'codigo_proveedor' => 'required|string|max:100',
            'talle' => 'required|string|max:50',
            'color' => 'required|string|max:50',
            'descripcion' => 'required|string|max:255',
            'precio_venta' => 'required|numeric|min:0',
            'precio_costo' => 'nullable|numeric|min:0',
            'cantidad' => 'required|numeric|min:1',
            'sucursal_id' => 'required|exists:sucursales,id',
            'categoria_id' => 'nullable|exists:productos_categorias,id',
            'subcategoria_id' => 'nullable|exists:productos_subcategorias,id',
            'codigo' => 'nullable|string|max:100',
            'imprimir_etiquetas' => 'nullable|boolean',
        ]);

        $proveedor = Proveedor::findOrFail($validated['proveedor_id']);
        $talle = strtoupper(trim($validated['talle']));
        $color = strtoupper(trim($validated['color']));
        $codigoProveedor = strtoupper(trim($validated['codigo_proveedor']));
        $cantidad = (float)$validated['cantidad'];
        $sucursalId = (int)$validated['sucursal_id'];
        $precioCosto = isset($validated['precio_costo']) && (float)$validated['precio_costo'] > 0 ? (float)$validated['precio_costo'] : 0;
        $precioVenta = (float)$validated['precio_venta'];

        // Determinar código CODE128 estructurado
        $codigoInput = trim((string)($request->input('codigo') ?? ''));
        if (!empty($codigoInput)) {
            $codigo = strtoupper($codigoInput);
        } else {
            $codigo = Producto::armarCodigoEstructurado($proveedor, $talle, $color, $codigoProveedor);
        }

        // Recordar el proveedor seleccionado en sesión para las siguientes cargas rápidas
        session(['ultimo_proveedor_id' => $proveedor->id]);

        // Buscar si ya existe un producto con este código
        $producto = Producto::where('codigo', $codigo)
            ->orWhere('codigo_barra', $codigo)
            ->first();

        $esNuevo = false;

        if ($producto) {
            // ==========================================
            // CASO 1: VARIANTE EXISTENTE EN CATÁLOGO
            // ==========================================
            if ($precioCosto > 0) {
                $producto->precio_costo = $precioCosto;
            }
            if ($precioVenta > 0) {
                $producto->precio_venta = $precioVenta;
            }
            if (empty($producto->codigo_proveedor)) {
                $producto->codigo_proveedor = $codigoProveedor;
            }
            if (empty($producto->talle)) {
                $producto->talle = $talle;
            }
            if (empty($producto->color)) {
                $producto->color = $color;
            }
            if (empty($producto->categoria_id) && !empty($validated['categoria_id'])) {
                $producto->categoria_id = $validated['categoria_id'];
            }
            if (empty($producto->subcategoria_id) && !empty($validated['subcategoria_id'])) {
                $producto->subcategoria_id = $validated['subcategoria_id'];
            }
            $producto->save();

            // Sumar unidades a stock existente
            if ($producto->controla_stock) {
                $producto->ajustarStock(
                    $sucursalId,
                    'alta',
                    $cantidad,
                    "Ingreso de mercadería / Recepción proveedor ({$proveedor->nombre})",
                    Auth::id(),
                    'RECEPCION-PROV'
                );
            }

            $mensaje = "Variante existente detectada: se sumaron {$cantidad} unid. al stock de '{$producto->descripcion_completa}' (Cód: {$producto->codigo}).";
        } else {
            // ==========================================
            // CASO 2: NUEVO PRODUCTO / NUEVA VARIANTE
            // ==========================================
            $esNuevo = true;
            $producto = Producto::create([
                'codigo' => $codigo,
                'codigo_barra' => $codigo,
                'codigo_proveedor' => $codigoProveedor,
                'talle' => $talle,
                'color' => $color,
                'descripcion' => strtoupper(trim($validated['descripcion'])),
                'categoria_id' => $validated['categoria_id'] ?? null,
                'subcategoria_id' => $validated['subcategoria_id'] ?? null,
                'proveedor_id' => $proveedor->id,
                'precio_costo' => $precioCosto,
                'precio_venta' => $precioVenta,
                'stock_minimo' => 5,
                'unidad_medida' => 'UNIDAD',
                'activo' => true,
                'controla_stock' => true,
                'fecha_alta' => now(),
            ]);

            // Cargar stock inicial
            $producto->ajustarStock(
                $sucursalId,
                'alta',
                $cantidad,
                "Stock inicial por alta de producto ({$proveedor->nombre})",
                Auth::id(),
                'ALTA-PROV'
            );

            $mensaje = "Nuevo producto registrado exitosamente: '{$producto->descripcion_completa}' con código [{$codigo}]. Stock inicial: +{$cantidad} unid.";
        }

        // Impresión directa de etiquetas térmicas si está tildado
        if ($request->boolean('imprimir_etiquetas') && $cantidad > 0) {
            return redirect()->route('productos.etiqueta', [
                'producto' => $producto->id,
                'cantidad' => min(500, (int)$cantidad),
                'sucursal_id' => $sucursalId,
                'es_alta' => $esNuevo ? 1 : 0,
                'autoprint' => 1,
            ])->with('success', $mensaje . " Imprimiendo etiquetas térmicas...");
        }

        return redirect()->route('productos.create')
            ->with('success', $mensaje);
    }

    /**
     * Registra el ingreso de unidades a un producto existente y opcionalmente imprime etiquetas.
     */
    public function ingresoStockRapido(Request $request)
    {
        $validated = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:1',
            'sucursal_id' => 'required|exists:sucursales,id',
            'precio_costo' => 'nullable|numeric|min:0',
            'precio_venta' => 'nullable|numeric|min:0',
            'imprimir_etiquetas' => 'nullable|boolean',
        ]);

        $producto = Producto::findOrFail($validated['producto_id']);
        $cantidad = (float)$validated['cantidad'];
        $sucursalId = (int)$validated['sucursal_id'];

        // Actualizar precios si vienen informados
        if (isset($validated['precio_costo']) && (float)$validated['precio_costo'] > 0) {
            $producto->precio_costo = $validated['precio_costo'];
        }
        if (isset($validated['precio_venta']) && (float)$validated['precio_venta'] > 0) {
            $producto->precio_venta = $validated['precio_venta'];
        }
        $producto->save();

        // Ingreso de stock físico
        if ($producto->controla_stock) {
            $producto->ajustarStock(
                $sucursalId,
                'alta',
                $cantidad,
                'Ingreso de mercadería / Recepción proveedor',
                Auth::id(),
                'RECEPCION-PROV'
            );
        }

        // Si se marcó imprimir etiquetas térmicas
        if ($request->boolean('imprimir_etiquetas') && $cantidad > 0) {
            return redirect()->route('productos.etiqueta', [
                'producto' => $producto->id,
                'cantidad' => min(500, (int)$cantidad),
                'sucursal_id' => $sucursalId,
                'es_alta' => 1,
                'autoprint' => 1,
            ])->with('success', "Stock actualizado (+{$cantidad} unid.) para '{$producto->descripcion}'. Imprimiendo etiquetas térmicas...");
        }

        return redirect()->route('productos.create')
            ->with('success', "Ingreso de {$cantidad} unid. registrado exitosamente para '{$producto->descripcion}'. Stock actualizado.");
    }

    /**
     * Guarda el nuevo producto con su stock inicial por sucursal.
     */
    public function store(Request $request)
    {
        $talle = !empty($request->input('talle')) ? strtoupper(trim($request->input('talle'))) : null;
        $color = !empty($request->input('color')) ? strtoupper(trim($request->input('color'))) : null;
        $codigoProveedor = !empty($request->input('codigo_proveedor')) ? strtoupper(trim($request->input('codigo_proveedor'))) : null;
        $proveedorId = $request->input('proveedor_id');
        $prov = !empty($proveedorId) ? Proveedor::find($proveedorId) : null;

        // Determinar código y código de barra: "no tenemos codigo interno, usamos el codigo de barra como codigo interno"
        $codigoInput = trim((string)$request->input('codigo'));
        if (empty($codigoInput)) {
            $codigo = Producto::armarCodigoEstructurado($prov, $talle, $color, $codigoProveedor);
        } else {
            $codigo = strtoupper($codigoInput);
        }

        $codigoBarra = $codigo;

        // Verificar unicidad en tiempo de guardado
        $duplicado = Producto::where('codigo', $codigo)
            ->orWhere('codigo_barra', $codigo)
            ->first();

        if ($duplicado) {
            return back()->withInput()->withErrors([
                'codigo' => "El código [{$codigo}] ya está asignado a otra prenda: '{$duplicado->descripcion}'. Si es la misma prenda, búsquela para registrar el ingreso de unidades."
            ]);
        }

        $validated = $request->validate([
            'descripcion' => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:productos_categorias,id',
            'subcategoria_id' => 'nullable|exists:productos_subcategorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'precio_costo' => 'nullable|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:30',
            'observaciones' => 'nullable|string',
            'controla_stock' => 'nullable|boolean',
            'cantidad_ingreso' => 'nullable|numeric|min:0',
            'sucursal_id' => 'nullable|exists:sucursales,id',
            'stock_inicial' => 'nullable|array',
            'stock_inicial.*' => 'nullable|numeric|min:0',
        ]);

        $controlaStock = $request->boolean('controla_stock', true);

        $producto = Producto::create([
            'codigo' => $codigo,
            'codigo_barra' => $codigoBarra,
            'codigo_proveedor' => $codigoProveedor,
            'talle' => $talle,
            'color' => $color,
            'descripcion' => strtoupper(trim($validated['descripcion'])),
            'categoria_id' => $validated['categoria_id'] ?? null,
            'subcategoria_id' => $validated['subcategoria_id'] ?? null,
            'proveedor_id' => $validated['proveedor_id'] ?? null,
            'precio_costo' => $validated['precio_costo'] ?? 0,
            'precio_venta' => $validated['precio_venta'],
            'stock_minimo' => $controlaStock ? ($validated['stock_minimo'] ?? 5) : 0,
            'unidad_medida' => strtoupper($validated['unidad_medida'] ?? 'UNIDAD'),
            'observaciones' => $validated['observaciones'] ?? null,
            'activo' => true,
            'controla_stock' => $controlaStock,
            'fecha_alta' => now(),
        ]);

        // Carga de unidades iniciales
        $totalIngreso = 0;
        $sucursalIngresoId = $request->input('sucursal_id') ?? session('sucursal_activa_id') ?? Auth::user()?->id_sucursal ?? 1;

        if ($controlaStock) {
            // Caso A: Ingreso rápido directo en la sucursal activa o seleccionada
            if ($request->filled('cantidad_ingreso') && (float)$request->input('cantidad_ingreso') > 0) {
                $cant = (float)$request->input('cantidad_ingreso');
                $producto->ajustarStock(
                    $sucursalIngresoId,
                    'alta',
                    $cant,
                    'Carga inicial / Recepción proveedor',
                    Auth::id(),
                    'STOCK-INICIAL'
                );
                $totalIngreso += $cant;
            }

            // Caso B: Distribución multi-sucursal (stock_inicial array)
            if (!empty($validated['stock_inicial'])) {
                foreach ($validated['stock_inicial'] as $sucId => $cantidad) {
                    $qty = (float)$cantidad;
                    if ($qty > 0) {
                        $producto->ajustarStock(
                            $sucId,
                            'alta',
                            $qty,
                            'Carga inicial de producto',
                            Auth::id(),
                            'STOCK-INICIAL'
                        );
                        $totalIngreso += $qty;
                    }
                }
            }

            // Inicializar las sucursales restantes en 0
            foreach (Sucursal::all() as $suc) {
                ProductoStock::firstOrCreate(
                    ['producto_id' => $producto->id, 'sucursal_id' => $suc->id],
                    ['stock' => 0, 'stock_minimo' => $producto->stock_minimo, 'precio_venta' => $producto->precio_venta]
                );
            }
        } else {
            foreach (Sucursal::all() as $suc) {
                ProductoStock::firstOrCreate(
                    ['producto_id' => $producto->id, 'sucursal_id' => $suc->id],
                    ['stock' => 0, 'stock_minimo' => 0, 'precio_venta' => $producto->precio_venta]
                );
            }
        }

        // Si se solicitó imprimir etiquetas tras registrar
        if ($controlaStock && $request->boolean('imprimir_etiquetas') && $totalIngreso > 0) {
            return redirect()->route('productos.etiqueta', [
                'producto' => $producto->id,
                'cantidad' => min(500, (int)$totalIngreso),
                'sucursal_id' => $sucursalIngresoId,
                'es_alta' => 1,
                'autoprint' => 1,
            ])->with('success', "Producto '{$producto->descripcion}' registrado con {$totalIngreso} unid. Imprimiendo etiquetas térmicas...");
        }

        return redirect()->route('productos.create')
            ->with('success', "Producto '{$producto->descripcion}' registrado con éxito. Código: [{$producto->codigo}].");
    }

    /**
     * Muestra la ficha del producto, stock multi-sucursal y movimientos.
     */
    public function show(Producto $producto)
    {
        $producto->load(['categoria', 'subcategoria', 'proveedor']);

        $sucursales = Sucursal::all();
        $stocksPorSucursal = [];

        foreach ($sucursales as $suc) {
            $stockReg = ProductoStock::where('producto_id', $producto->id)
                                    ->where('sucursal_id', $suc->id)
                                    ->first();
            $stocksPorSucursal[] = [
                'sucursal' => $suc,
                'stock' => $stockReg ? (float)$stockReg->stock : 0.0,
                'stock_minimo' => $stockReg ? (float)$stockReg->stock_minimo : (float)$producto->stock_minimo,
                'ubicacion' => $stockReg?->ubicacion,
                'precio_especifico' => $stockReg?->precio_venta,
            ];
        }

        // Historial de movimientos recientes
        $movimientos = ProductoMovimiento::with(['sucursal', 'usuario', 'sucursalOrigen', 'sucursalDestino'])
                                         ->where('producto_id', $producto->id)
                                         ->latest('created_at')
                                         ->take(25)
                                         ->get();

        return view('productos.show', compact('producto', 'stocksPorSucursal', 'movimientos', 'sucursales'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Producto $producto)
    {
        $categorias = ProductoCategoria::orderBy('nombre')->get();
        $subcategorias = ProductoSubcategoria::orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre')->get();
        $tallesSugeridos = Producto::listaTallesSugeridos();

        return view('productos.form', compact('producto', 'categorias', 'subcategorias', 'proveedores', 'sucursales', 'tallesSugeridos'));
    }

    /**
     * Actualiza el producto.
     */
    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|max:50|unique:productos,codigo,' . $producto->id,
            'codigo_barra' => 'nullable|string|max:50',
            'codigo_proveedor' => 'nullable|string|max:100',
            'talle' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            'descripcion' => 'required|string|max:255',
            'categoria_id' => 'nullable|exists:productos_categorias,id',
            'subcategoria_id' => 'nullable|exists:productos_subcategorias,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'precio_costo' => 'nullable|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|max:30',
            'observaciones' => 'nullable|string',
            'activo' => 'boolean',
            'controla_stock' => 'nullable|boolean',
        ]);

        $controlaStock = $request->boolean('controla_stock', true);
        $talle = !empty($validated['talle']) ? strtoupper(trim($validated['talle'])) : null;
        $color = !empty($validated['color']) ? strtoupper(trim($validated['color'])) : null;
        $codigoProveedor = !empty($validated['codigo_proveedor']) ? strtoupper(trim($validated['codigo_proveedor'])) : null;
        $codigoBarra = !empty($validated['codigo_barra']) ? trim($validated['codigo_barra']) : null;

        if (empty($codigoBarra) && $request->boolean('generar_codigo_barra')) {
            $prov = !empty($validated['proveedor_id']) ? Proveedor::find($validated['proveedor_id']) : null;
            $codigoBarra = Producto::generarCodigoBarraSugerido($talle, $color, $prov, $validated['codigo']);
        }

        $producto->update([
            'codigo' => strtoupper(trim($validated['codigo'])),
            'codigo_barra' => $codigoBarra,
            'codigo_proveedor' => $codigoProveedor,
            'talle' => $talle,
            'color' => $color,
            'descripcion' => strtoupper(trim($validated['descripcion'])),
            'categoria_id' => $validated['categoria_id'] ?? null,
            'subcategoria_id' => $validated['subcategoria_id'] ?? null,
            'proveedor_id' => $validated['proveedor_id'] ?? null,
            'precio_costo' => $validated['precio_costo'] ?? 0,
            'precio_venta' => $validated['precio_venta'],
            'stock_minimo' => $controlaStock ? ($validated['stock_minimo'] ?? 5) : 0,
            'unidad_medida' => strtoupper($validated['unidad_medida'] ?? 'UNIDAD'),
            'observaciones' => $validated['observaciones'] ?? null,
            'activo' => $request->boolean('activo', true),
            'controla_stock' => $controlaStock,
            'fecha_modificacion' => now(),
        ]);

        // Si se cambia a no controlar stock, limpiar existencias acumuladas a 0
        if (!$controlaStock) {
            $producto->stocks()->update(['stock' => 0, 'stock_minimo' => 0]);
        }

        return redirect()->route('productos.show', $producto->id)
                         ->with('success', "Producto '{$producto->descripcion}' actualizado correctamente.");
    }

    /**
     * Da de baja el producto (Soft Delete).
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()->route('productos.index')
                         ->with('success', "El producto ha sido dado de baja del catálogo.");
    }

    /**
     * Registra un ajuste de stock directo (alta, baja, corrección) en una sucursal.
     */
    public function ajusteStock(Request $request, Producto $producto)
    {
        if (!$producto->controla_stock) {
            return back()->with('error', "El artículo '{$producto->descripcion}' no controla stock físico y no admite ajustes manuales.");
        }

        $validated = $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'tipo' => 'required|in:alta,baja,correccion',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:255',
            'comprobante' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $cantidadReal = ($validated['tipo'] === 'baja') ? -abs((float)$validated['cantidad']) : (float)$validated['cantidad'];

        try {
            $producto->ajustarStock(
                (int)$validated['sucursal_id'],
                $validated['tipo'],
                $cantidadReal,
                $validated['motivo'],
                Auth::id(),
                $validated['comprobante'] ?? 'AJUSTE-MANUAL',
                $validated['observaciones'] ?? null
            );

            // Si el usuario marcó imprimir etiquetas tras el ingreso
            if ($validated['tipo'] === 'alta' && $request->boolean('imprimir_etiquetas')) {
                $cantEtiquetas = max(1, (int)$validated['cantidad']);
                return redirect()->route('productos.etiqueta', [
                    'producto' => $producto->id,
                    'cantidad' => $cantEtiquetas,
                    'sucursal_id' => $validated['sucursal_id'],
                    'es_alta' => 1,
                    'autoprint' => 1
                ])->with('success', "Stock ingresado exitosamente ({$cantEtiquetas} unid.). Generando {$cantEtiquetas} etiquetas térmicas del lote ingresado...");
            }

            return back()->with('success', "Ajuste de stock registrado exitosamente.");
        } catch (Exception $e) {
            return back()->with('error', "Error al ajustar stock: " . $e->getMessage());
        }
    }

    /**
     * Procesa un traslado de stock inter-sucursal.
     */
    public function trasladarStock(Request $request, Producto $producto)
    {
        if (!$producto->controla_stock) {
            return back()->with('error', "El artículo '{$producto->descripcion}' es genérico / sin control de stock y no requiere traslados.");
        }
        $validated = $request->validate([
            'sucursal_origen_id' => 'required|exists:sucursales,id',
            'sucursal_destino_id' => 'required|exists:sucursales,id|different:sucursal_origen_id',
            'cantidad' => 'required|numeric|min:0.01',
            'comprobante' => 'nullable|string|max:100',
            'motivo' => 'nullable|string|max:255',
        ]);

        try {
            $producto->trasladar(
                (int)$validated['sucursal_origen_id'],
                (int)$validated['sucursal_destino_id'],
                (float)$validated['cantidad'],
                Auth::id(),
                $validated['comprobante'] ?? 'REMITO-TRASLADO',
                $validated['motivo'] ?? 'Traslado de mercadería'
            );

            return back()->with('success', "Traslado inter-sucursal de {$validated['cantidad']} unidades realizado correctamente.");
        } catch (Exception $e) {
            return back()->with('error', "Error al trasladar stock: " . $e->getMessage());
        }
    }

    /**
     * Vista de impresión de etiquetas térmicas de código de barras (formato 50mm x 25mm).
     * Soporta reimpresión de etiquetas por reposición (sin modificar stock) y emisión por lote de ingreso.
     */
    public function imprimirEtiqueta(Request $request, Producto $producto)
    {
        $cantidad = max(1, min(500, (int)$request->input('cantidad', 1)));
        $sucursalId = $request->input('sucursal_id') ?? session('sucursal_activa_id');
        $esAlta = $request->boolean('es_alta', false);
        $motivo = $request->input('motivo', $esAlta ? 'Ingreso de mercadería' : 'Reposición de etiqueta');

        // Determinar precio de venta: si hay sucursal específica con precio_venta o el precio_venta general
        $precioVenta = (float)$producto->precio_venta;
        if ($sucursalId) {
            $stockSuc = $producto->stocks()->where('sucursal_id', $sucursalId)->first();
            if ($stockSuc && $stockSuc->precio_venta !== null && (float)$stockSuc->precio_venta > 0) {
                $precioVenta = (float)$stockSuc->precio_venta;
            }
        }

        // Código a codificar (código de barra o código de producto)
        $code = trim((string)($producto->codigo_barra ?: $producto->codigo));

        // Generar SVG de código de barras
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $barcodeSvg = null;
        try {
            // TYPE_CODE_128 es el estándar óptimo y compacto para códigos de 12 dígitos
            $rawSvg = $generator->getBarcode($code, $generator::TYPE_CODE_128, 1.15, 26);
            $barcodeSvg = preg_replace('/<\?xml.*?\?>/i', '', $rawSvg);
            $barcodeSvg = preg_replace('/<!DOCTYPE.*?>/i', '', $barcodeSvg);
        } catch (\Exception $e) {
            try {
                $rawSvg = $generator->getBarcode($code, $generator::TYPE_CODE_39, 1.0, 26);
                $barcodeSvg = preg_replace('/<\?xml.*?\?>/i', '', $rawSvg);
                $barcodeSvg = preg_replace('/<!DOCTYPE.*?>/i', '', $barcodeSvg);
            } catch (\Exception $e2) {
                $barcodeSvg = null;
            }
        }

        return view('productos.etiqueta', compact('producto', 'barcodeSvg', 'precioVenta', 'cantidad', 'code', 'sucursalId', 'esAlta', 'motivo'));
    }

    /**
     * Impresión en lote de etiquetas térmicas (50mm x 25mm CODE128)
     * Utilizado para productos generados o rectificados en traslados o ingresos por lote.
     */
    public function imprimirEtiquetasLote(Request $request)
    {
        $items = [];

        // 1. Probar desde sesión si se solicitó o si existe en sesión
        if ($request->has('desde_sesion') && session()->has('etiquetas_lote_pendientes')) {
            $items = session('etiquetas_lote_pendientes');
        } elseif ($request->filled('items')) {
            $raw = $request->input('items');
            $items = is_string($raw) ? json_decode($raw, true) : $raw;
        } elseif ($request->filled('producto_id')) {
            $items = [[
                'producto_id' => (int)$request->input('producto_id'),
                'cantidad' => max(1, (int)$request->input('cantidad', 1))
            ]];
        } elseif (session()->has('etiquetas_lote_pendientes')) {
            $items = session('etiquetas_lote_pendientes');
        }

        if (empty($items)) {
            return redirect()->route('productos.traslado')->with('error', 'No hay etiquetas pendientes para imprimir.');
        }

        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $etiquetasAImprimir = [];
        $totalEtiquetas = 0;

        foreach ($items as $it) {
            $prodId = $it['producto_id'] ?? null;
            if (!$prodId) continue;

            $producto = Producto::with('proveedor', 'stocks')->find($prodId);
            if (!$producto) continue;

            $cant = max(1, min(500, (int)($it['cantidad'] ?? 1)));
            $code = trim((string)($it['codigo_barra'] ?? $producto->codigo_barra ?: $producto->codigo));

            $barcodeSvg = null;
            try {
                $rawSvg = $generator->getBarcode($code, $generator::TYPE_CODE_128, 1.15, 26);
                $barcodeSvg = preg_replace('/<\?xml.*?\?>/i', '', $rawSvg);
                $barcodeSvg = preg_replace('/<!DOCTYPE.*?>/i', '', $barcodeSvg);
            } catch (\Exception $e) {
                try {
                    $rawSvg = $generator->getBarcode($code, $generator::TYPE_CODE_39, 1.0, 26);
                    $barcodeSvg = preg_replace('/<\?xml.*?\?>/i', '', $rawSvg);
                    $barcodeSvg = preg_replace('/<!DOCTYPE.*?>/i', '', $barcodeSvg);
                } catch (\Exception $e2) {
                    $barcodeSvg = null;
                }
            }

            $precioVenta = isset($it['precio_venta']) && (float)$it['precio_venta'] > 0 
                ? (float)$it['precio_venta'] 
                : (float)($producto->stocks->first()?->precio_venta ?? $producto->precio_venta ?? 0);

            $etiquetasAImprimir[] = [
                'producto' => $producto,
                'code' => $code,
                'barcodeSvg' => $barcodeSvg,
                'precioVenta' => $precioVenta,
                'talle' => $it['talle'] ?? $producto->talle,
                'color' => $it['color'] ?? $producto->color,
                'cantidad' => $cant,
                'es_nuevo' => !empty($it['es_nuevo']),
                'codigo_original' => $it['codigo_original'] ?? null,
            ];

            $totalEtiquetas += $cant;
        }

        return view('productos.etiquetas-lote', compact('etiquetasAImprimir', 'totalEtiquetas'));
    }
}

