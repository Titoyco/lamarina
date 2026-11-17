<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductoMovimiento;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\ProductoStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class StockMovimientoController extends Controller
{
    /**
     * Auditoría general de movimientos de stock.
     */
    public function index(Request $request)
    {
        $query = ProductoMovimiento::with(['producto.categoria', 'sucursal', 'usuario', 'sucursalOrigen', 'sucursalDestino']);

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($sucursalId = $request->input('sucursal_id')) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($productoId = $request->input('producto_id')) {
            $query->where('producto_id', $productoId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('comprobante', 'like', "%{$search}%")
                  ->orWhere('motivo', 'like', "%{$search}%")
                  ->orWhereHas('producto', function ($pq) use ($search) {
                      $pq->where('descripcion', 'like', "%{$search}%")
                         ->orWhere('codigo', 'like', "%{$search}%")
                         ->orWhere('codigo_barra', 'like', "%{$search}%");
                  });
            });
        }

        if ($fechaDesde = $request->input('fecha_desde')) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }

        if ($fechaHasta = $request->input('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        $movimientos = $query->latest('id')->paginate(25);
        $sucursales = Sucursal::orderBy('nombre')->get();

        return view('productos.movimientos', compact('movimientos', 'sucursales'));
    }

    /**
     * Formulario de traslado entre sucursales (tipo punto de venta / carga por escaneo de lote).
     */
    public function trasladoForm(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $sucursalActivaId = config('app.sucursal_activa.id') ?? ($sucursales->first()?->id ?? 1);
        $comprobanteSugerido = 'REM-' . date('Ymd-His');

        $productoSeleccionado = null;
        if ($request->filled('producto_id')) {
            $p = Producto::with(['categoria', 'subcategoria', 'proveedor', 'stocks' => function ($q) use ($sucursalActivaId) {
                $q->where('sucursal_id', $sucursalActivaId);
            }])->find($request->producto_id);

            if ($p && $p->controla_stock) {
                $productoSeleccionado = [
                    'id' => $p->id,
                    'codigo' => $p->codigo,
                    'codigo_barra' => $p->codigo_barra,
                    'codigo_proveedor' => $p->codigo_proveedor,
                    'talle' => $p->talle,
                    'color' => $p->color,
                    'descripcion' => $p->descripcion,
                    'categoria' => $p->categoria?->nombre ?? 'GENERAL',
                    'subcategoria' => $p->subcategoria?->nombre ?? null,
                    'proveedor' => $p->proveedor?->nombre ?? 'N/A',
                    'proveedor_id' => $p->proveedor_id,
                    'proveedor_legajo' => $p->proveedor?->legajo,
                    'unidad_medida' => $p->unidad_medida ?? 'UN',
                    'stock_origen' => (float)($p->stocks->first()?->stock ?? 0),
                    'cantidad' => 1,
                    'actualizar_codigo_barra' => false,
                    'nuevo_codigo_barra' => '',
                ];
            }
        }

        // Talles y colores sugeridos para autocompletado en el modal y la tabla
        $tallesDb = Producto::whereNotNull('talle')->where('talle', '!=', '')->distinct()->pluck('talle')->toArray();
        $tallesSugeridos = array_values(array_unique(array_merge(Producto::listaTallesSugeridos(), $tallesDb)));

        $coloresBase = [
            'NEGRO', 'BLANCO', 'AZUL', 'AZUL MARINO', 'CELESTE', 'ROJO', 'VERDE', 'VERDE MILITAR',
            'AMARILLO', 'GRIS', 'GRIS MELANGE', 'ROSA', 'BEIGE', 'MARRON', 'BORDO', 'VIOLETA',
            'LILA', 'NARANJA', 'FUCSIA', 'TURQUESA', 'ESTAMPADO', 'SURTIDO', 'VARIOS', 'MULTICOLOR',
            'CRUDO', 'CAMEL', 'HABANO', 'TERRACOTA', 'CHOCOLATE', 'MOSTAZA', 'LAVANDA', 'CORAL',
            '00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '20', '30', '40', '50', '60', '70', '80', '90'
        ];
        $coloresDb = Producto::whereNotNull('color')->where('color', '!=', '')->distinct()->pluck('color')->toArray();
        $coloresSugeridos = array_values(array_unique(array_merge($coloresBase, $coloresDb)));

        return view('productos.traslado', compact(
            'sucursales', 
            'sucursalActivaId', 
            'comprobanteSugerido', 
            'productoSeleccionado',
            'tallesSugeridos',
            'coloresSugeridos'
        ));
    }

    /**
     * Endpoint API para búsqueda por código de barras / SKU / descripción para carga en lote.
     */
    public function buscarParaTraslado(Request $request)
    {
        $term = trim($request->input('q', ''));
        $sucursalId = (int)$request->input('sucursal_id', config('app.sucursal_activa.id') ?? 1);

        if (empty($term)) {
            return response()->json([]);
        }

        $productos = Producto::where('activo', true)
            ->where('controla_stock', true)
            ->where(function ($q) use ($term) {
                $q->where('codigo_barra', $term)
                  ->orWhere('codigo', $term)
                  ->orWhere('codigo_proveedor', $term)
                  ->orWhere('talle', $term)
                  ->orWhere('color', $term)
                  ->orWhere('codigo', 'like', "%{$term}%")
                  ->orWhere('codigo_barra', 'like', "%{$term}%")
                  ->orWhere('codigo_proveedor', 'like', "%{$term}%")
                  ->orWhere('talle', 'like', "%{$term}%")
                  ->orWhere('color', 'like', "%{$term}%")
                  ->orWhere('descripcion', 'like', "%{$term}%");
            })
            ->with(['categoria', 'subcategoria', 'proveedor', 'stocks' => function ($sq) use ($sucursalId) {
                $sq->where('sucursal_id', $sucursalId);
            }])
            ->limit(20)
            ->get();

        $resultado = $productos->map(function ($p) use ($term) {
            $stockOrigen = (float)($p->stocks->first()?->stock ?? 0);
            $esExacto = (
                strcasecmp($p->codigo_barra ?? '', $term) === 0 || 
                strcasecmp($p->codigo, $term) === 0 ||
                strcasecmp($p->codigo_proveedor ?? '', $term) === 0
            );

            return [
                'id' => $p->id,
                'codigo' => $p->codigo,
                'codigo_barra' => $p->codigo_barra,
                'codigo_proveedor' => $p->codigo_proveedor,
                'talle' => $p->talle,
                'color' => $p->color,
                'descripcion' => $p->descripcion,
                'categoria' => $p->categoria?->nombre ?? 'GENERAL',
                'subcategoria' => $p->subcategoria?->nombre ?? null,
                'proveedor' => $p->proveedor?->nombre ?? 'N/A',
                'proveedor_id' => $p->proveedor_id,
                'proveedor_legajo' => $p->proveedor?->legajo,
                'unidad_medida' => $p->unidad_medida ?? 'UN',
                'precio_venta' => (float)$p->precio_venta,
                'stock_origen' => $stockOrigen,
                'exact_match' => $esExacto,
            ];
        });

        return response()->json($resultado);
    }

    /**
     * Endpoint API para actualizar stock en origen de los productos en lote al cambiar la sucursal emisora.
     */
    public function verificarStock(Request $request)
    {
        $sucursalId = (int)$request->input('sucursal_id');
        $productoIds = (array)$request->input('producto_ids', []);

        if (empty($productoIds) || !$sucursalId) {
            return response()->json([]);
        }

        $stocks = ProductoStock::where('sucursal_id', $sucursalId)
            ->whereIn('producto_id', $productoIds)
            ->pluck('stock', 'producto_id');

        return response()->json($stocks);
    }

    /**
     * Procesa el traslado inter-sucursal (tanto lote de artículos como artículo individual).
     */
    public function trasladoStore(Request $request)
    {
        $validated = $request->validate([
            'sucursal_origen_id' => 'required|exists:sucursales,id',
            'sucursal_destino_id' => 'required|exists:sucursales,id|different:sucursal_origen_id',
            'comprobante' => 'nullable|string|max:100',
            'motivo' => 'nullable|string|max:255',
            // Soporta lote multi-artículo
            'items' => 'nullable|array',
            'items.*.producto_id' => 'required_with:items|exists:productos,id',
            'items.*.cantidad' => 'required_with:items|numeric|min:0.01',
            'items.*.codigo_proveedor' => 'nullable|string|max:100',
            'items.*.talle' => 'nullable|string|max:50',
            'items.*.color' => 'nullable|string|max:50',
            'items.*.talle_original' => 'nullable|string|max:50',
            'items.*.color_original' => 'nullable|string|max:50',
            'items.*.rectificado' => 'nullable',
            'items.*.actualizar_codigo_barra' => 'nullable|boolean',
            'items.*.nuevo_codigo_barra' => 'nullable|string|max:100',
            // Soporta artículo individual (compatibilidad retroactiva)
            'producto_id' => 'nullable|exists:productos,id',
            'cantidad' => 'nullable|numeric|min:0.01',
            'codigo_proveedor' => 'nullable|string|max:100',
            'talle' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'talle_original' => 'nullable|string|max:50',
            'color_original' => 'nullable|string|max:50',
            'rectificado' => 'nullable',
            'actualizar_codigo_barra' => 'nullable|boolean',
            'nuevo_codigo_barra' => 'nullable|string|max:100',
        ], [
            'sucursal_destino_id.different' => 'La sucursal de destino no puede ser la misma que la sucursal de origen.',
        ]);

        $sucursalOrigenId = (int)$validated['sucursal_origen_id'];
        $sucursalDestinoId = (int)$validated['sucursal_destino_id'];
        $comprobante = trim($validated['comprobante'] ?? '') ?: ('REM-' . date('Ymd-His'));
        $motivo = trim($validated['motivo'] ?? '') ?: 'Traslado inter-sucursal de mercadería';
        $usuarioId = Auth::id();

        // Consolidar lista de items a trasladar con campos de talle/color/código de fábrica y rectificación
        $itemsATrasladar = [];
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $it) {
                $cant = (float)$it['cantidad'];
                if ($cant > 0) {
                    $itemsATrasladar[] = [
                        'producto_id' => (int)$it['producto_id'],
                        'cantidad' => $cant,
                        'codigo_proveedor' => $it['codigo_proveedor'] ?? null,
                        'talle' => isset($it['talle']) ? trim((string)$it['talle']) : null,
                        'color' => isset($it['color']) ? trim((string)$it['color']) : null,
                        'talle_original' => isset($it['talle_original']) ? trim((string)$it['talle_original']) : null,
                        'color_original' => isset($it['color_original']) ? trim((string)$it['color_original']) : null,
                        'rectificado' => !empty($it['rectificado']),
                        'actualizar_codigo_barra' => !empty($it['actualizar_codigo_barra']),
                        'nuevo_codigo_barra' => isset($it['nuevo_codigo_barra']) ? trim((string)$it['nuevo_codigo_barra']) : null,
                    ];
                }
            }
        } elseif (!empty($validated['producto_id']) && !empty($validated['cantidad'])) {
            $itemsATrasladar[] = [
                'producto_id' => (int)$validated['producto_id'],
                'cantidad' => (float)$validated['cantidad'],
                'codigo_proveedor' => $validated['codigo_proveedor'] ?? null,
                'talle' => isset($validated['talle']) ? trim((string)$validated['talle']) : null,
                'color' => isset($validated['color']) ? trim((string)$validated['color']) : null,
                'talle_original' => isset($validated['talle_original']) ? trim((string)$validated['talle_original']) : null,
                'color_original' => isset($validated['color_original']) ? trim((string)$validated['color_original']) : null,
                'rectificado' => !empty($validated['rectificado']),
                'actualizar_codigo_barra' => !empty($validated['actualizar_codigo_barra']),
                'nuevo_codigo_barra' => isset($validated['nuevo_codigo_barra']) ? trim((string)$validated['nuevo_codigo_barra']) : null,
            ];
        }

        if (empty($itemsATrasladar)) {
            return back()->withInput()->with('error', "Debe ingresar al menos un artículo con cantidad válida en el lote a trasladar.");
        }

        $sucursalOrigen = Sucursal::findOrFail($sucursalOrigenId);
        $sucursalDestino = Sucursal::findOrFail($sucursalDestinoId);
        $etiquetasGeneradas = [];

        try {
            DB::transaction(function () use (
                $itemsATrasladar, 
                $sucursalOrigenId, 
                $sucursalDestinoId, 
                $sucursalOrigen, 
                $sucursalDestino, 
                $usuarioId, 
                $comprobante, 
                $motivo, 
                &$etiquetasGeneradas
            ) {
                foreach ($itemsATrasladar as $it) {
                    $productoOriginal = Producto::with(['proveedor', 'stocks'])->lockForUpdate()->findOrFail($it['producto_id']);
                    $cantidad = (float)$it['cantidad'];

                    // Descomposición del código actual para determinar valores de referencia
                    $codigoActual = trim((string)($productoOriginal->codigo_barra ?: $productoOriginal->codigo));
                    $desgloseActual = Producto::desglosarCodigoBarra($codigoActual);

                    // Talles y colores de referencia
                    $talleOrig = trim((string)($it['talle_original'] ?? $productoOriginal->talle ?? $desgloseActual['talle_sugerido'] ?? ''));
                    $colorOrig = trim((string)($it['color_original'] ?? $productoOriginal->color ?? $desgloseActual['color_sugerido'] ?? ''));
                    $talleNuevo = trim((string)($it['talle'] ?? ''));
                    $colorNuevo = trim((string)($it['color'] ?? ''));
                    $rectificado = !empty($it['rectificado']);

                    // Comprobar si hubo cambio efectivo de talle o color
                    $talleCambio = (!empty($talleNuevo) && strcasecmp($talleNuevo, $talleOrig) !== 0);
                    $colorCambio = (!empty($colorNuevo) && strcasecmp($colorNuevo, $colorOrig) !== 0);
                    $huboRectificacion = $rectificado || $talleCambio || $colorCambio;

                    if (!$huboRectificacion) {
                        // ========================================================
                        // CASO 1: TRASLADO NORMAL (Mismo código y producto)
                        // ========================================================
                        $productoOriginal->trasladar(
                            $sucursalOrigenId,
                            $sucursalDestinoId,
                            $cantidad,
                            $usuarioId,
                            $comprobante,
                            $motivo
                        );
                    } else {
                        // ========================================================
                        // CASO 2: TRASLADO CON RECTIFICACIÓN DE TALLE / COLOR
                        // ========================================================
                        // 1. Descontar stock del producto original en sucursal de origen
                        $stockOrigenReg = ProductoStock::firstOrCreate(
                            ['producto_id' => $productoOriginal->id, 'sucursal_id' => $sucursalOrigenId],
                            ['stock' => 0]
                        );

                        $stockOrigenAnt = (float)$stockOrigenReg->stock;
                        if ($stockOrigenAnt < $cantidad) {
                            throw new Exception("Stock insuficiente en origen ({$sucursalOrigen->nombre}) para '{$productoOriginal->descripcion}' (Disponible: {$stockOrigenAnt}, Solicitado: {$cantidad}).");
                        }

                        $stockOrigenReg->stock = $stockOrigenAnt - $cantidad;
                        $stockOrigenReg->save();

                        // Determinar el nuevo código de barras estructurado de 12+ caracteres
                        $nuevoCodigo = trim((string)($it['nuevo_codigo_barra'] ?? ''));
                        if (empty($nuevoCodigo)) {
                            $prov3 = str_pad($desgloseActual['proveedor_legajo'] ?: ($productoOriginal->proveedor?->legajo ?? '000'), 3, '0', STR_PAD_LEFT);
                            $talle2 = Producto::normalizarTalle2Char($talleNuevo ?: $talleOrig);
                            $color2 = Producto::normalizarColor2Char($colorNuevo ?: $colorOrig);
                            $resto = $desgloseActual['codigo_proveedor'] ?: ($productoOriginal->codigo_proveedor ?: $productoOriginal->codigo);
                            $nuevoCodigo = strtoupper($prov3 . $talle2 . $color2 . $resto);
                        }

                        // Registrar movimiento de auditoría de salida en origen
                        ProductoMovimiento::create([
                            'producto_id' => $productoOriginal->id,
                            'sucursal_id' => $sucursalOrigenId,
                            'sucursal_origen_id' => $sucursalOrigenId,
                            'sucursal_destino_id' => $sucursalDestinoId,
                            'tipo' => 'traslado_salida',
                            'cantidad' => -$cantidad,
                            'stock_anterior' => $stockOrigenAnt,
                            'stock_nuevo' => $stockOrigenAnt - $cantidad,
                            'precio_costo' => $productoOriginal->precio_costo ?? 0,
                            'precio_venta' => $productoOriginal->precio_venta ?? 0,
                            'comprobante' => $comprobante,
                            'motivo' => "Traslado a {$sucursalDestino->nombre} con rectificación a T: {$talleNuevo}, C: {$colorNuevo} (Nuevo Cód: {$nuevoCodigo})",
                            'usuario_id' => $usuarioId,
                        ]);

                        // 2. Buscar si ya existe el nuevo producto o crearlo
                        $productoDestino = Producto::where('codigo_barra', $nuevoCodigo)
                            ->orWhere('codigo', $nuevoCodigo)
                            ->first();

                        $esNuevo = false;
                        if (!$productoDestino) {
                            $esNuevo = true;
                            $productoDestino = new Producto();
                            $productoDestino->codigo = $nuevoCodigo;
                            $productoDestino->codigo_barra = $nuevoCodigo;
                            $productoDestino->codigo_proveedor = $productoOriginal->codigo_proveedor ?: (substr($nuevoCodigo, 7) ?: null);
                            $productoDestino->talle = $talleNuevo ?: $talleOrig;
                            $productoDestino->color = $colorNuevo ?: $colorOrig;
                            $productoDestino->descripcion = $productoOriginal->descripcion;
                            $productoDestino->categoria_id = $productoOriginal->categoria_id;
                            $productoDestino->subcategoria_id = $productoOriginal->subcategoria_id;
                            $productoDestino->proveedor_id = $productoOriginal->proveedor_id;
                            $productoDestino->precio_costo = $productoOriginal->precio_costo;
                            $productoDestino->precio_venta = $productoOriginal->precio_venta;
                            $productoDestino->stock_minimo = $productoOriginal->stock_minimo ?: 1;
                            $productoDestino->unidad_medida = $productoOriginal->unidad_medida ?: 'UNIDAD';
                            $productoDestino->observaciones = $productoOriginal->observaciones;
                            $productoDestino->activo = true;
                            $productoDestino->controla_stock = true;
                            $productoDestino->fecha_alta = now();
                            $productoDestino->save();
                        } else {
                            if (empty($productoDestino->talle) && !empty($talleNuevo)) {
                                $productoDestino->talle = $talleNuevo;
                            }
                            if (empty($productoDestino->color) && !empty($colorNuevo)) {
                                $productoDestino->color = $colorNuevo;
                            }
                            $productoDestino->save();
                        }

                        // 3. Acreditar stock en la sucursal receptora (destino)
                        $stockDestinoReg = ProductoStock::firstOrCreate(
                            ['producto_id' => $productoDestino->id, 'sucursal_id' => $sucursalDestinoId],
                            [
                                'stock' => 0,
                                'stock_minimo' => $productoDestino->stock_minimo ?? 0,
                                'precio_venta' => $productoDestino->precio_venta,
                            ]
                        );

                        $stockDestinoAnt = (float)$stockDestinoReg->stock;
                        $stockDestinoReg->stock = $stockDestinoAnt + $cantidad;
                        if (empty($stockDestinoReg->precio_venta) && !empty($productoDestino->precio_venta)) {
                            $stockDestinoReg->precio_venta = $productoDestino->precio_venta;
                        }
                        $stockDestinoReg->save();

                        // Registrar movimiento de auditoría de entrada en destino
                        ProductoMovimiento::create([
                            'producto_id' => $productoDestino->id,
                            'sucursal_id' => $sucursalDestinoId,
                            'sucursal_origen_id' => $sucursalOrigenId,
                            'sucursal_destino_id' => $sucursalDestinoId,
                            'tipo' => 'traslado_entrada',
                            'cantidad' => $cantidad,
                            'stock_anterior' => $stockDestinoAnt,
                            'stock_nuevo' => $stockDestinoAnt + $cantidad,
                            'precio_costo' => $productoDestino->precio_costo ?? 0,
                            'precio_venta' => $stockDestinoReg->precio_venta ?? $productoDestino->precio_venta ?? 0,
                            'comprobante' => $comprobante,
                            'motivo' => "Recepción desde {$sucursalOrigen->nombre} por rectificación de {$productoOriginal->codigo} a T: {$talleNuevo}, C: {$colorNuevo}",
                            'usuario_id' => $usuarioId,
                        ]);

                        // 4. Agregar a la lista para impresión de etiquetas térmicas
                        $etiquetasGeneradas[] = [
                            'producto_id' => $productoDestino->id,
                            'codigo' => $productoDestino->codigo,
                            'codigo_barra' => $productoDestino->codigo_barra,
                            'descripcion' => $productoDestino->descripcion,
                            'talle' => $productoDestino->talle,
                            'color' => $productoDestino->color,
                            'precio_venta' => (float)($stockDestinoReg->precio_venta ?? $productoDestino->precio_venta ?? 0),
                            'cantidad' => (int)$cantidad,
                            'es_nuevo' => $esNuevo,
                            'producto_original_id' => $productoOriginal->id,
                            'codigo_original' => $productoOriginal->codigo,
                            'codigo_barra_original' => $codigoActual,
                            'talle_original' => $talleOrig,
                            'color_original' => $colorOrig,
                        ];
                    }
                }
            });

            $totalArticulos = count($itemsATrasladar);
            $totalUnidades = array_sum(array_column($itemsATrasladar, 'cantidad'));

            $mensajeExito = "Lote de traslado completado exitosamente: {$totalArticulos} artículos ({$totalUnidades} unidades) trasladados de {$sucursalOrigen->nombre} a {$sucursalDestino->nombre} bajo comprobante {$comprobante}.";

            // Si se generaron nuevos códigos por rectificación, persistir en sesión para impresión
            if (!empty($etiquetasGeneradas)) {
                session(['etiquetas_lote_pendientes' => $etiquetasGeneradas]);
                $cantEtiquetas = array_sum(array_column($etiquetasGeneradas, 'cantidad'));
                $mensajeExito .= " Se generaron códigos nuevos para {$cantEtiquetas} prenda(s) con rectificación de talle/color.";
            }

            $redirect = redirect()->route('productos.traslado')->with('success', $mensajeExito);

            if (!empty($etiquetasGeneradas)) {
                $redirect->with('etiquetas_rectificadas', $etiquetasGeneradas);
            }

            return $redirect;
        } catch (Exception $e) {
            return back()->withInput()->with('error', "Error al procesar el traslado: " . $e->getMessage());
        }
    }
}
