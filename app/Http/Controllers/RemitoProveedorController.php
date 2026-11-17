<?php

namespace App\Http\Controllers;

use App\Models\RemitoProveedor;
use App\Models\RemitoProveedorItem;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\ProductoStock;
use App\Models\ProductoMovimiento;
use App\Models\ProductoCategoria;
use App\Models\ProductoSubcategoria;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class RemitoProveedorController extends Controller
{
    /**
     * Listado general de remitos recibidos.
     */
    public function index(Request $request)
    {
        $query = RemitoProveedor::with(['proveedor', 'sucursal', 'usuario', 'items']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_remito', 'like', "%{$search}%")
                  ->orWhereHas('proveedor', function ($pq) use ($search) {
                      $pq->where('nombre', 'like', "%{$search}%")
                         ->orWhere('cuit', 'like', "%{$search}%")
                         ->orWhere('legajo', 'like', "%{$search}%");
                  });
            });
        }

        if ($proveedorId = $request->input('proveedor_id')) {
            $query->where('proveedor_id', $proveedorId);
        }

        if ($sucursalId = $request->input('sucursal_id')) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($fechaDesde = $request->input('fecha_desde')) {
            $query->whereDate('fecha_recepcion', '>=', $fechaDesde);
        }

        if ($fechaHasta = $request->input('fecha_hasta')) {
            $query->whereDate('fecha_recepcion', '<=', $fechaHasta);
        }

        // Click-to-Sort (conforme a .agents/rules/table_sorting.md)
        $sortableColumns = [
            'numero_remito'        => 'remitos_proveedores.numero_remito',
            'fecha_recepcion'      => 'remitos_proveedores.fecha_recepcion',
            'proveedor'            => 'proveedor',
            'sucursal'             => 'sucursal',
            'total_costo'          => 'remitos_proveedores.total_costo',
            'total_venta_estimado' => 'remitos_proveedores.total_venta_estimado',
            'estado'               => 'remitos_proveedores.estado',
        ];

        $sort = $request->get('sort', 'fecha_recepcion');
        $direction = strtolower($request->get('direction', 'desc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'fecha_recepcion';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if ($sort === 'proveedor') {
            $query->leftJoin('proveedores', 'remitos_proveedores.proveedor_id', '=', 'proveedores.id')
                ->select('remitos_proveedores.*')
                ->orderBy('proveedores.nombre', $direction);
        } elseif ($sort === 'sucursal') {
            $query->leftJoin('sucursales', 'remitos_proveedores.sucursal_id', '=', 'sucursales.id')
                ->select('remitos_proveedores.*')
                ->orderBy('sucursales.nombre', $direction);
        } else {
            $query->orderBy($sortableColumns[$sort], $direction);
        }

        if ($sort !== 'id' && $sort !== 'numero_remito') {
            $query->orderBy('remitos_proveedores.id', 'desc');
        }

        // Exportación a Excel / CSV con todos los filtros aplicados
        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            $exportQuery = clone $query;
            $filename = 'remitos_compras_' . date('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($exportQuery) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

                fputcsv($handle, [
                    'Nro Remito',
                    'Fecha Recepcion',
                    'Proveedor',
                    'Legajo Proveedor',
                    'CUIT Proveedor',
                    'Sucursal Ingreso',
                    'Cantidad Renglones',
                    'Total Costo',
                    'Total Venta Estimado',
                    'Estado',
                    'Operador',
                ], ';');

                $exportQuery->chunk(500, function ($chunk) use ($handle) {
                    foreach ($chunk as $rem) {
                        fputcsv($handle, [
                            $rem->numero_remito,
                            $rem->fecha_recepcion ? \Carbon\Carbon::parse($rem->fecha_recepcion)->format('d/m/Y') : '-',
                            $rem->proveedor?->nombre ?? '-',
                            $rem->proveedor?->legajo ?? '-',
                            $rem->proveedor?->cuit ?? '-',
                            $rem->sucursal?->nombre ?? '-',
                            $rem->items ? $rem->items->count() : 0,
                            number_format((float)$rem->total_costo, 2, ',', ''),
                            number_format((float)$rem->total_venta_estimado, 2, ',', ''),
                            ucfirst($rem->estado),
                            $rem->usuario?->name ?? '-',
                        ], ';');
                    }
                });

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $remitos = $query->paginate(20)->withQueryString();
        $proveedores = Proveedor::orderBy('nombre')->get();
        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('proveedores.remitos.index', compact('remitos', 'proveedores', 'sucursales', 'sort', 'direction'));
    }

    /**
     * Formulario interactivo de carga de remito de mercadería.
     */
    public function create(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre')->get();
        $categorias = ProductoCategoria::orderBy('nombre')->get();
        $subcategorias = ProductoSubcategoria::orderBy('nombre')->get();

        $sucursalActivaId = session('sucursal_activa_id') 
            ?? config('app.sucursal_activa')->id 
            ?? Auth::user()->id_sucursal 
            ?? 1;

        $proveedorSeleccionadoId = $request->input('proveedor_id');
        $siguienteLegajoProveedor = Proveedor::sugerirSiguienteLegajo();

        // Remitos y Facturas anteriores disponibles para continuar la carga en etapas
        $remitosAnteriores = RemitoProveedor::with(['proveedor', 'sucursal', 'items.producto.stocks'])
            ->latest('id')
            ->take(50)
            ->get();

        $remitoCargar = null;
        if ($remitoId = $request->input('remito_id')) {
            $remitoCargar = $remitosAnteriores->firstWhere('id', (int)$remitoId) 
                ?? RemitoProveedor::with(['proveedor', 'sucursal', 'items.producto.stocks'])->find($remitoId);
        }

        $proveedoresList = $proveedores->map(fn($p) => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'legajo' => $p->legajo_formateado ?? (is_numeric($p->legajo) ? str_pad((string)$p->legajo, 3, '0', STR_PAD_LEFT) : $p->legajo)
        ])->values()->all();

        // Formatear items del remito a cargar si se seleccionó uno con carga parcial
        $itemsPrecargados = [];
        if ($remitoCargar) {
            $proveedorSeleccionadoId = $remitoCargar->proveedor_id;
            $sucursalActivaId = $remitoCargar->sucursal_id;

            foreach ($remitoCargar->items as $it) {
                $stockSede = (float) ($it->producto?->stocks->where('sucursal_id', $remitoCargar->sucursal_id)->first()?->stock ?? 0);
                $costo = (float) $it->precio_compra;
                $venta = (float) $it->precio_venta;

                $itemsPrecargados[] = [
                    'producto_id' => $it->producto_id,
                    'codigo' => $it->producto?->codigo ?? '',
                    'codigo_barra' => $it->producto?->codigo_barra ?? $it->producto?->codigo,
                    'codigo_proveedor' => $it->producto?->codigo_proveedor ?? '',
                    'talle' => $it->producto?->talle ?? '',
                    'color' => $it->producto?->color ?? '',
                    'descripcion' => $it->producto?->descripcion ?? ('Producto #' . $it->producto_id),
                    'stock_actual' => $stockSede,
                    'cantidad' => (float) $it->cantidad,
                    'cantidad_guardada_previa' => (float) $it->cantidad,
                    'precio_compra' => $costo,
                    'precio_venta' => $venta,
                    'precio_venta_original' => (float) ($it->producto?->precio_venta ?? 0),
                    'precio_venta_sugerido' => round($costo * 2, 2)
                ];
            }
        }

        return view('proveedores.remitos.create', compact(
            'sucursales',
            'proveedores',
            'proveedoresList',
            'categorias',
            'subcategorias',
            'sucursalActivaId',
            'proveedorSeleccionadoId',
            'siguienteLegajoProveedor',
            'remitosAnteriores',
            'remitoCargar',
            'itemsPrecargados'
        ));
    }

    /**
     * Procesar y guardar el remito de mercadería (completo o parcial, nuevo o en etapas).
     * Al actualizar una carga parcial calcula la diferencia (delta) para actualizar el stock.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'remito_id' => 'nullable|exists:remitos_proveedores,id',
            'numero_remito' => 'required|string|max:50',
            'proveedor_id' => 'required|exists:proveedores,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'fecha_recepcion' => 'required|date',
            'estado' => 'required|in:completado,parcial',
            'observaciones' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_compra' => 'required|numeric|min:0',
            'items.*.precio_venta' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Debes ingresar al menos un artículo en el remito.',
            'items.min' => 'Debes ingresar al menos un artículo en el remito.',
        ]);

        try {
            DB::beginTransaction();

            $sucursal = Sucursal::findOrFail($validated['sucursal_id']);
            $proveedor = Proveedor::findOrFail($validated['proveedor_id']);
            $isUpdate = !empty($validated['remito_id']);

            if ($isUpdate) {
                $remito = RemitoProveedor::with('items')->findOrFail($validated['remito_id']);
                $remito->update([
                    'numero_remito' => strtoupper(trim($validated['numero_remito'])),
                    'proveedor_id' => $validated['proveedor_id'],
                    'sucursal_id' => $validated['sucursal_id'],
                    'fecha_recepcion' => $validated['fecha_recepcion'],
                    'estado' => $validated['estado'],
                    'observaciones' => $validated['observaciones'] ?? null,
                ]);

                // 1. Mapear cantidades guardadas previamente por producto
                $cantidadesPrevias = [];
                foreach ($remito->items as $itemPrevio) {
                    $pid = $itemPrevio->producto_id;
                    $cantidadesPrevias[$pid] = ($cantidadesPrevias[$pid] ?? 0) + (float) $itemPrevio->cantidad;
                }
            } else {
                $remito = RemitoProveedor::create([
                    'numero_remito' => strtoupper(trim($validated['numero_remito'])),
                    'proveedor_id' => $validated['proveedor_id'],
                    'sucursal_id' => $validated['sucursal_id'],
                    'fecha_recepcion' => $validated['fecha_recepcion'],
                    'usuario_id' => Auth::id(),
                    'estado' => $validated['estado'],
                    'total_costo' => 0,
                    'total_venta_estimado' => 0,
                    'observaciones' => $validated['observaciones'] ?? null,
                ]);

                $cantidadesPrevias = [];
            }

            // 2. Mapear cantidades nuevas ingresadas por producto
            $cantidadesNuevas = [];
            foreach ($validated['items'] as $itemData) {
                $pid = (int) $itemData['producto_id'];
                $cantidadesNuevas[$pid] = ($cantidadesNuevas[$pid] ?? 0) + (float) $itemData['cantidad'];
            }

            // 3. Calcular diferencia (delta) para cada producto y actualizar stock físico
            $todosLosProductos = array_unique(array_merge(array_keys($cantidadesPrevias), array_keys($cantidadesNuevas)));

            foreach ($todosLosProductos as $pid) {
                $qPrev = (float) ($cantidadesPrevias[$pid] ?? 0);
                $qNew = (float) ($cantidadesNuevas[$pid] ?? 0);
                $delta = $qNew - $qPrev;

                if (abs($delta) > 0.0001) {
                    $stockRow = ProductoStock::firstOrCreate(
                        [
                            'producto_id' => $pid,
                            'sucursal_id' => $sucursal->id,
                        ],
                        [
                            'stock' => 0,
                            'stock_minimo' => 0,
                            'stock_maximo' => 100,
                        ]
                    );

                    $stockAnterior = (float) $stockRow->stock;
                    $stockNuevo = $stockAnterior + $delta;
                    $stockRow->update(['stock' => $stockNuevo]);

                    $producto = Producto::find($pid);

                    // Registrar auditoría del movimiento de inventario por el delta
                    ProductoMovimiento::create([
                        'producto_id' => $pid,
                        'sucursal_id' => $sucursal->id,
                        'tipo' => 'ingreso_remito',
                        'cantidad' => abs($delta),
                        'stock_anterior' => $stockAnterior,
                        'stock_nuevo' => $stockNuevo,
                        'precio_costo' => (float) ($producto?->precio_costo ?? 0),
                        'precio_venta' => (float) ($producto?->precio_venta ?? 0),
                        'usuario_id' => Auth::id(),
                        'comprobante' => "REM-" . $remito->numero_remito,
                        'motivo' => $isUpdate 
                            ? "Ajuste por carga en etapas - Remito N° {$remito->numero_remito} (Diferencia: " . ($delta > 0 ? "+{$delta}" : "{$delta}") . ")"
                            : "Recepción de mercadería - Remito N° {$remito->numero_remito} ({$proveedor->nombre})",
                        'observaciones' => $isUpdate
                            ? ($delta > 0 ? 'Incremento por ampliación de carga parcial' : 'Reducción por corrección en carga parcial')
                            : ($remito->estado === 'parcial' ? 'Ingreso por entrega parcial' : 'Ingreso por recepción completa'),
                        'referencia_type' => RemitoProveedor::class,
                        'referencia_id' => $remito->id,
                    ]);
                }
            }

            // 4. Si fue actualización, limpiar renglones anteriores para persistir el detalle actual consolidado
            if ($isUpdate) {
                $remito->items()->delete();
            }

            // 5. Guardar renglones actuales y actualizar precios de costo y venta
            $totalCosto = 0;
            $totalVenta = 0;

            foreach ($validated['items'] as $itemData) {
                $producto = Producto::findOrFail($itemData['producto_id']);
                $cantidad = (float) $itemData['cantidad'];
                $precioCompra = (float) $itemData['precio_compra'];
                $precioVenta = (float) $itemData['precio_venta'];
                $precioVentaAnterior = (float) ($producto->precio_venta ?? 0);

                $stockRow = ProductoStock::where('producto_id', $producto->id)
                    ->where('sucursal_id', $sucursal->id)
                    ->first();

                $stockActual = (float) ($stockRow?->stock ?? 0);

                // Actualizar precios de costo y venta del producto
                $producto->update([
                    'precio_costo' => $precioCompra,
                    'precio_venta' => $precioVenta,
                    'proveedor_id' => $producto->proveedor_id ?? $proveedor->id,
                ]);

                // Crear renglón del remito
                RemitoProveedorItem::create([
                    'remito_id' => $remito->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_compra' => $precioCompra,
                    'precio_venta' => $precioVenta,
                    'precio_venta_anterior' => $precioVentaAnterior,
                    'stock_anterior' => $stockActual - $cantidad,
                    'stock_posterior' => $stockActual,
                ]);

                $totalCosto += ($cantidad * $precioCompra);
                $totalVenta += ($cantidad * $precioVenta);
            }

            // 6. Actualizar totales del remito
            $remito->update([
                'total_costo' => $totalCosto,
                'total_venta_estimado' => $totalVenta,
            ]);

            DB::commit();

            if ($isUpdate) {
                $msg = $remito->estado === 'parcial'
                    ? "Factura/Remito #{$remito->numero_remito} ACTUALIZADO (Carga Parcial). Se procesó la diferencia de stock con éxito ({$remito->items()->count()} artículos cargados)."
                    : "Factura/Remito #{$remito->numero_remito} RECEPCIONADO Y COMPLETADO. Stock final actualizado en {$sucursal->nombre}.";
            } else {
                $msg = $remito->estado === 'parcial' 
                    ? "Factura/Remito #{$remito->numero_remito} guardado como CARGA PARCIAL. Puede continuar agregando artículos en cualquier momento."
                    : "Factura/Remito #{$remito->numero_remito} RECEPCIONADO Y COMPLETADO. Stock físico actualizado en {$sucursal->nombre}.";
            }

            return redirect()->route('remitos.show', $remito->id)->with('success', $msg);

        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al procesar el remito: ' . $e->getMessage());
        }
    }

    /**
     * Ver comprobante y detalle de recepción del remito.
     */
    public function show(RemitoProveedor $remito)
    {
        $remito->load(['proveedor', 'sucursal', 'usuario', 'items.producto.categoria']);

        return view('proveedores.remitos.show', compact('remito'));
    }

    /**
     * Agregar más artículos a un remito guardado previamente como parcial.
     */
    public function agregarItems(Request $request, RemitoProveedor $remito)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_compra' => 'required|numeric|min:0',
            'items.*.precio_venta' => 'required|numeric|min:0',
            'completar_remito' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $sucursal = $remito->sucursal;
            $proveedor = $remito->proveedor;
            $totalCostoAdicional = 0;
            $totalVentaAdicional = 0;

            foreach ($validated['items'] as $itemData) {
                $producto = Producto::findOrFail($itemData['producto_id']);
                $cantidad = (float) $itemData['cantidad'];
                $precioCompra = (float) $itemData['precio_compra'];
                $precioVenta = (float) $itemData['precio_venta'];
                $precioVentaAnterior = (float) ($producto->precio_venta ?? 0);

                $stockRow = ProductoStock::firstOrCreate(
                    [
                        'producto_id' => $producto->id,
                        'sucursal_id' => $sucursal->id,
                    ],
                    [
                        'stock' => 0,
                        'stock_minimo' => 0,
                        'stock_maximo' => 100,
                    ]
                );

                $stockAnterior = (float) $stockRow->stock;
                $stockNuevo = $stockAnterior + $cantidad;
                $stockRow->update(['stock' => $stockNuevo]);

                $producto->update([
                    'precio_costo' => $precioCompra,
                    'precio_venta' => $precioVenta,
                ]);

                RemitoProveedorItem::create([
                    'remito_id' => $remito->id,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_compra' => $precioCompra,
                    'precio_venta' => $precioVenta,
                    'precio_venta_anterior' => $precioVentaAnterior,
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockNuevo,
                ]);

                ProductoMovimiento::create([
                    'producto_id' => $producto->id,
                    'sucursal_id' => $sucursal->id,
                    'tipo' => 'ingreso_remito',
                    'cantidad' => $cantidad,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockNuevo,
                    'precio_costo' => $precioCompra,
                    'precio_venta' => $precioVenta,
                    'usuario_id' => Auth::id(),
                    'comprobante' => "REM-" . $remito->numero_remito,
                    'motivo' => "Recepción de mercadería adicional - Remito N° {$remito->numero_remito} ({$proveedor->nombre})",
                    'observaciones' => 'Entrega complementaria en remito parcial',
                    'referencia_type' => RemitoProveedor::class,
                    'referencia_id' => $remito->id,
                ]);

                $totalCostoAdicional += ($cantidad * $precioCompra);
                $totalVentaAdicional += ($cantidad * $precioVenta);
            }

            $nuevoEstado = !empty($validated['completar_remito']) ? 'completado' : $remito->estado;

            $remito->update([
                'total_costo' => $remito->total_costo + $totalCostoAdicional,
                'total_venta_estimado' => $remito->total_venta_estimado + $totalVentaAdicional,
                'estado' => $nuevoEstado,
            ]);

            DB::commit();

            return redirect()->route('remitos.show', $remito->id)
                             ->with('success', "Se agregaron nuevos artículos al Remito #{$remito->numero_remito} y se actualizó el stock.");

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al agregar artículos: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint API para buscar productos dinámicamente en el formulario de remito.
     * Filtra estrictamente por el proveedor seleccionado.
     */
    public function buscarProductos(Request $request)
    {
        $term = trim((string)$request->input('q', ''));
        $proveedorId = $request->filled('proveedor_id') ? (int)$request->input('proveedor_id') : null;

        // En remitos de proveedor, el proveedor es obligatorio para filtrar y mostrar artículos
        if (!$proveedorId) {
            return response()->json([]);
        }

        $sucursalId = (int) ($request->input('sucursal_id') 
            ?? session('sucursal_activa_id') 
            ?? Auth::user()?->id_sucursal 
            ?? 1);

        $query = Producto::with(['stocks' => function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            }, 'proveedor', 'categoria'])
            ->where('activo', true);

        // Filtrado ESTRICTO por Proveedor: únicamente artículos cargados a nombre de este proveedor
        $query->where('proveedor_id', $proveedorId);

        // Búsqueda inteligente por términos dentro de este proveedor
        if (strlen($term) > 0) {
            $tokens = array_filter(preg_split('/\s+/', $term), fn($w) => strlen($w) > 0);

            foreach ($tokens as $token) {
                $cleanToken = preg_replace('/[^A-Za-z0-9]/', '', $token);

                $query->where(function ($sub) use ($token, $cleanToken) {
                    $sub->where('descripcion', 'like', "%{$token}%")
                        ->orWhere('codigo', 'like', "%{$token}%")
                        ->orWhere('codigo_barra', 'like', "%{$token}%")
                        ->orWhere('codigo_proveedor', 'like', "%{$token}%")
                        ->orWhere('talle', 'like', "%{$token}%")
                        ->orWhere('color', 'like', "%{$token}%");

                    // Si el token es numérico (código de artículo o fábrica), buscar también con ceros a la izquierda (ej: '755' -> '00755')
                    if (!empty($cleanToken) && is_numeric($cleanToken)) {
                        $padded5 = str_pad($cleanToken, 5, '0', STR_PAD_LEFT);
                        $sub->orWhere('codigo', 'like', "%{$padded5}%")
                            ->orWhere('codigo_barra', 'like', "%{$padded5}%");
                    }
                });
            }
        }

        $limite = strlen($term) > 0 ? 30 : 60;

        $productos = $query->orderBy('descripcion', 'asc')
            ->take($limite)
            ->get()
            ->map(function ($prod) {
                $stockSede = (float) ($prod->stocks->first()?->stock ?? 0);
                $costo = (float) ($prod->precio_costo ?? 0);
                $venta = (float) ($prod->precio_venta ?? ($costo * 2));
                $desglose = Producto::desglosarCodigoBarra($prod->codigo);

                return [
                    'id' => $prod->id,
                    'codigo' => $prod->codigo,
                    'codigo_barra' => $prod->codigo_barra ?: $prod->codigo,
                    'codigo_proveedor' => $prod->codigo_proveedor ?: $desglose['codigo_proveedor'],
                    'talle' => $prod->talle ?: $desglose['talle_sugerido'],
                    'color' => $prod->color ?: $desglose['color_sugerido'],
                    'descripcion' => $prod->descripcion,
                    'categoria' => $prod->categoria?->nombre ?? 'Sin Categoría',
                    'proveedor' => $prod->proveedor?->nombre ?? 'N/A',
                    'proveedor_id' => $prod->proveedor_id,
                    'stock_actual' => $stockSede,
                    'precio_costo' => $costo,
                    'precio_venta' => $venta,
                    'precio_venta_sugerido' => round($costo * 2, 2),
                ];
            });

        return response()->json($productos);
    }

    /**
     * Crear producto nuevo en el catálogo directamente desde la pantalla de remito.
     */
    public function crearProductoRapido(Request $request)
    {
        $validated = $request->validate([
            'descripcion' => 'required|string|max:200',
            'codigo' => 'nullable|string|max:50',
            'codigo_barra' => 'nullable|string|max:50',
            'codigo_proveedor' => 'nullable|string|max:100',
            'talle' => 'nullable|string|max:20',
            'color' => 'nullable|string|max:50',
            'proveedor_id' => 'required|exists:proveedores,id',
            'categoria_id' => 'nullable|exists:productos_categorias,id',
            'subcategoria_id' => 'nullable|exists:productos_subcategorias,id',
            'precio_costo' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
        ]);

        $proveedor = Proveedor::findOrFail($validated['proveedor_id']);
        $talle = !empty($validated['talle']) ? strtoupper(trim($validated['talle'])) : null;
        $color = !empty($validated['color']) ? strtoupper(trim($validated['color'])) : null;
        $codigoProveedor = !empty($validated['codigo_proveedor']) ? strtoupper(trim($validated['codigo_proveedor'])) : null;

        // Si no viene código, generar código estructurado CODE128: Prov(3) + Talle(2) + Color(2) + Fabrica
        $codigoInput = trim((string)($validated['codigo'] ?? ''));
        if (empty($codigoInput)) {
            $codigo = Producto::armarCodigoEstructurado($proveedor, $talle, $color, $codigoProveedor);
        } else {
            $codigo = strtoupper($codigoInput);
        }

        // Si ya existiese, añadir sufijo para unicidad
        if (Producto::where('codigo', $codigo)->exists()) {
            $codigo = $codigo . '-' . rand(10, 99);
        }

        $codigoBarra = !empty($validated['codigo_barra']) ? trim($validated['codigo_barra']) : $codigo;

        $producto = Producto::create([
            'codigo' => $codigo,
            'codigo_barra' => $codigoBarra,
            'codigo_proveedor' => $codigoProveedor,
            'talle' => $talle,
            'color' => $color,
            'descripcion' => strtoupper(trim($validated['descripcion'])),
            'proveedor_id' => $proveedor->id,
            'categoria_id' => $validated['categoria_id'] ?? null,
            'subcategoria_id' => $validated['subcategoria_id'] ?? null,
            'precio_costo' => $validated['precio_costo'],
            'precio_venta' => $validated['precio_venta'],
            'controla_stock' => true,
            'activo' => true,
            'fecha_alta' => now(),
        ]);

        return response()->json([
            'success' => true,
            'producto' => [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'codigo_barra' => $producto->codigo_barra,
                'codigo_proveedor' => $producto->codigo_proveedor,
                'talle' => $producto->talle,
                'color' => $producto->color,
                'descripcion' => $producto->descripcion,
                'categoria' => $producto->categoria?->nombre ?? 'General',
                'proveedor' => $proveedor->nombre,
                'proveedor_id' => $proveedor->id,
                'stock_actual' => 0,
                'precio_costo' => (float) $producto->precio_costo,
                'precio_venta' => (float) $producto->precio_venta,
                'precio_venta_sugerido' => round($producto->precio_costo * 2, 2),
            ],
            'message' => "Producto '{$producto->descripcion}' dado de alta en catálogo.",
        ]);
    }
}
