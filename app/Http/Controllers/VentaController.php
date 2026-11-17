<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaItem;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\ProductoStock;
use App\Models\ProductoMovimiento;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Presentacion;
use App\Models\Sucursal;
use App\Models\SucursalIndice;
use App\Models\Provincia;
use App\Models\CajaSesion;
use App\Models\PuntoVenta;
use App\Services\ArcaWsfeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class VentaController extends Controller
{
    /**
     * Normaliza el porcentaje de interés para visualización (ej: 6.0 para 6%).
     * En la BD puede estar guardado como decimal rate (0.06) o porcentaje directo (6.00 / 15.00).
     */
    public static function normalizarPorcentajeInteres(?Presentacion $presentacion): float
    {
        if (!$presentacion) {
            return 0.0;
        }
        $raw = (float) $presentacion->interes_credito;
        if ($raw > 0 && $raw < 1) {
            return round($raw * 100, 2);
        }
        return round($raw, 2);
    }

    /**
     * Normaliza el factor multiplicador de interés (ej: 0.06 para 6%).
     */
    public static function normalizarFactorInteres(?Presentacion $presentacion): float
    {
        if (!$presentacion) {
            return 0.0;
        }
        $raw = (float) $presentacion->interes_credito;
        if ($raw > 0 && $raw < 1) {
            return $raw;
        }
        return round($raw / 100, 4);
    }

    /**
     * Financiación con interés escalonado por cuota.
     * Con "primera cuota sin interés" (default):
     *   1 pago  => 0%
     *   N pagos => tasa * (N - 1)   (ej: 6% -> 2c=6%, 3c=12%, 4c=18%)
     * Sin el beneficio: N pagos => tasa * N
     *   total       = monto * (1 + tasa * tramos)
     *   valor_cuota = total / N
     */
    public static function calcularFinanciacion(float $monto, float $factorInteres, int $cuotas, bool $primeraSinInteres = true): array
    {
        $cuotas = max(1, $cuotas);
        $tramos = $primeraSinInteres ? $cuotas - 1 : $cuotas;
        $factorEfectivo = $factorInteres * $tramos;
        $recargo = round($monto * $factorEfectivo, 2);
        $total = round($monto + $recargo, 2);

        return [
            'factor'      => $factorEfectivo,
            'recargo'     => $recargo,
            'total'       => $total,
            'valor_cuota' => round($total / $cuotas, 2),
        ];
    }

    /**
     * Listado histórico de ventas con click-to-sort, filtros de sucursal, fechas y buscador.
     */
    public function index(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $sucursalActiva = config('app.sucursal_activa') ?? Sucursal::first();
        $provinciaActivaId = $sucursalActiva?->id_provincia ?? 2;
        $provinciaActivaNombre = $provinciaActivaId === 2 ? 'Tierra del Fuego' : 'Chubut';

        $query = Venta::with(['sucursal', 'cliente', 'credito', 'usuario']);

        // 1. Filtro por Sucursal o Jurisdicción Provincial
        $filtroSucursal = $request->get('sucursal');
        if ($filtroSucursal === 'all') {
            // Mostrar todas sin filtrar jurisdicción
        } elseif ($filtroSucursal && str_starts_with($filtroSucursal, 'provincia_')) {
            $provId = (int) str_replace('provincia_', '', $filtroSucursal);
            $query->porProvincia($provId);
        } elseif ($filtroSucursal && is_numeric($filtroSucursal)) {
            $query->porSucursal((int) $filtroSucursal);
        } else {
            // Por defecto: provincia de la sucursal activa
            $query->porProvincia($provinciaActivaId);
        }

        // 2. Buscador por comprobante, cliente o DNI
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('comprobante', 'LIKE', "%{$search}%")
                  ->orWhere('cliente_nombre', 'LIKE', "%{$search}%")
                  ->orWhere('cliente_documento', 'LIKE', "%{$search}%")
                  ->orWhereHas('cliente', function ($qCli) use ($search) {
                      $qCli->where('nombre', 'LIKE', "%{$search}%")
                           ->orWhere('dni', 'LIKE', "%{$search}%");
                  });
            });
        }

        // 3. Filtro por medio de pago
        if ($request->filled('medio_pago') && $request->medio_pago !== 'all') {
            $query->where('medio_pago', $request->medio_pago);
        }

        // 4. Filtro por estado
        if ($request->filled('estado') && $request->estado !== 'all') {
            $query->where('estado', $request->estado);
        }

        // 5. Filtro por fecha desde / hasta
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // 6. Click-to-Sort (conforme a .agents/rules/table_sorting.md)
        $sortableColumns = [
            'fecha'       => 'ventas.fecha',
            'comprobante' => 'ventas.comprobante',
            'cliente'     => 'ventas.cliente_nombre',
            'sucursal'    => 'ventas.id_sucursal',
            'medio_pago'  => 'ventas.medio_pago',
            'total'       => 'ventas.total',
            'estado'      => 'ventas.estado',
        ];

        $sort = $request->get('sort', 'fecha');
        $direction = strtolower($request->get('direction', 'desc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'fecha';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $orderByCol = $sortableColumns[$sort];
        $query->orderBy($orderByCol, $direction);

        // Orden secundario determinista
        if ($orderByCol !== 'ventas.id') {
            $query->orderBy('ventas.id', 'desc');
        }

        // Exportación CSV / Excel (con todos los filtros aplicados)
        if ($request->get('export') === 'csv' || $request->get('export') === 'excel') {
            return $this->exportarCsv(clone $query);
        }

        // 7. KPIs de la consulta y del día
        $hoy = Carbon::today();
        $mesInicio = Carbon::now()->startOfMonth();

        $kpisQuery = clone $query;
        $totalFiltrado = (float) $kpisQuery->sum('total');
        $cantidadFiltrada = $kpisQuery->count();

        // Totales globales de la sucursal activa
        $facturadoHoy = (float) Venta::where('id_sucursal', $sucursalActiva?->id)
            ->whereDate('fecha', $hoy)
            ->where('estado', '!=', 'anulada')
            ->sum('total');

        $facturadoMes = (float) Venta::where('id_sucursal', $sucursalActiva?->id)
            ->whereDate('fecha', '>=', $mesInicio)
            ->where('estado', '!=', 'anulada')
            ->sum('total');

        $ventas = $query->paginate(20)->withQueryString();

        return view('ventas.index', compact(
            'ventas',
            'sucursales',
            'sucursalActiva',
            'provinciaActivaId',
            'provinciaActivaNombre',
            'filtroSucursal',
            'sort',
            'direction',
            'totalFiltrado',
            'cantidadFiltrada',
            'facturadoHoy',
            'facturadoMes'
        ));
    }

    /**
     * Punto de Venta (POS) & Facturación rápida.
     */
    public function create(Request $request)
    {
        $sucursalActiva = config('app.sucursal_activa') ?? Sucursal::first();
        $sucursalId = $sucursalActiva?->id ?? 1;

        $esTdf = $sucursalActiva ? $sucursalActiva->isTdf() : true;
        $esChubut = $sucursalActiva ? $sucursalActiva->isChubut() : false;

        // Cliente preseleccionado si viene redirigido desde el módulo de clientes o créditos
        $clienteInicial = null;
        $clienteId = $request->get('id_cliente') ?? $request->get('cliente_id');
        if ($clienteId) {
            $clienteInicial = Cliente::find($clienteId);
        }
        $medioPagoInicial = $request->get('medio_pago', $esTdf ? 'efectivo' : 'credito_personal');

        // Presentación activa para la sucursal y su tasa de interés vigente normalizada (ej: 6.0%)
        $presentacion = Presentacion::activaParaSucursal($sucursalId);
        $tasaInteresCredito = self::normalizarPorcentajeInteres($presentacion);

        // En Chubut / Trelew (o producto genérico para ventas sin stock físico)
        $productoGenerico = Producto::where('id', 9233)
            ->orWhere('codigo', '1')
            ->orWhere('codigo_barra', '1')
            ->first();

        // En caso de que no exista producto genérico por seed anterior, se crea o se toma el primero disponible
        if (!$productoGenerico) {
            $productoGenerico = Producto::firstOrCreate(
                ['codigo' => 'VARIOS'],
                [
                    'codigo_barra' => '1',
                    'descripcion' => 'VARIOS - ARTICULO GENERICO',
                    'precio_costo' => 0,
                    'precio_venta' => 1,
                    'controla_stock' => false,
                    'activo' => true,
                    'categoria_id' => null,
                    'subcategoria_id' => null,
                ]
            );
        }

        $esTdf = $sucursalActiva ? $sucursalActiva->isTdf() : true;
        $esChubut = $sucursalActiva ? $sucursalActiva->isChubut() : false;

        // Estado del Control de Caja para la sucursal activa
        $cajaActiva = CajaSesion::activaParaSucursal($sucursalId);
        $requiereCierreAnterior = $cajaActiva && !$cajaActiva->esDeHoy();
        $totalesCajaActiva = $cajaActiva ? $cajaActiva->calcularTotales() : [];
        // Medios de pago activos para el mostrador
        $mediosPago = \App\Models\MedioPago::activos()->get();

        return view('ventas.create', compact(
            'sucursalActiva',
            'presentacion',
            'tasaInteresCredito',
            'productoGenerico',
            'esTdf',
            'esChubut',
            'cajaActiva',
            'requiereCierreAnterior',
            'totalesCajaActiva',
            'mediosPago',
            'clienteInicial',
            'medioPagoInicial'
        ));
    }

    /**
     * API JSON: Búsqueda rápida de productos por código de barra, código o nombre con stock de la sucursal.
     */
    public function buscarProducto(Request $request)
    {
        $term = trim($request->get('q', ''));
        if (strlen($term) < 1) {
            return response()->json([]);
        }

        $sucursalId = (int) ($request->get('sucursal_id') ?? config('app.sucursal_activa.id') ?? 1);

        // 1. Coincidencia exacta por código de barra (prioridad máxima para lectores ópticos)
        $exactBarcode = Producto::with(['stocks' => function ($q) use ($sucursalId) {
            $q->where('sucursal_id', $sucursalId);
        }])
        ->where('activo', true)
        ->where(function ($q) use ($term) {
            $q->where('codigo_barra', $term)
              ->orWhere('codigo', $term);
        })
        ->first();

        if ($exactBarcode) {
            $stockVal = (float) ($exactBarcode->stocks->first()?->stock ?? 0);
            return response()->json([
                'exact' => true,
                'producto' => [
                    'id'             => $exactBarcode->id,
                    'codigo'         => $exactBarcode->codigo,
                    'codigo_barra'   => $exactBarcode->codigo_barra,
                    'descripcion'    => $exactBarcode->descripcion,
                    'precio_venta'   => (float) $exactBarcode->precio_venta,
                    'controla_stock' => (bool) $exactBarcode->controla_stock,
                    'stock'          => $stockVal,
                ]
            ]);
        }

        // 2. Búsqueda por similitud (código o descripción)
        $productos = Producto::with(['stocks' => function ($q) use ($sucursalId) {
            $q->where('sucursal_id', $sucursalId);
        }])
        ->where('activo', true)
        ->where(function ($q) use ($term) {
            $q->where('codigo_barra', 'LIKE', "%{$term}%")
              ->orWhere('codigo', 'LIKE', "%{$term}%")
              ->orWhere('descripcion', 'LIKE', "%{$term}%");
        })
        ->orderByRaw("CASE WHEN descripcion LIKE ? THEN 1 WHEN codigo LIKE ? THEN 2 ELSE 3 END", ["{$term}%", "{$term}%"])
        ->limit(20)
        ->get();

        $items = $productos->map(function ($p) {
            return [
                'id'             => $p->id,
                'codigo'         => $p->codigo,
                'codigo_barra'   => $p->codigo_barra,
                'descripcion'    => $p->descripcion,
                'precio_venta'   => (float) $p->precio_venta,
                'controla_stock' => (bool) $p->controla_stock,
                'stock'          => (float) ($p->stocks->first()?->stock ?? 0),
            ];
        });

        return response()->json([
            'exact' => false,
            'productos' => $items,
        ]);
    }

    /**
     * API JSON: Corrección rápida de stock por recuento físico en mostrador previo a facturación.
     */
    public function ajusteRecuentoRapido(Request $request)
    {
        $validated = $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'id_sucursal' => 'nullable|exists:sucursales,id',
            'cantidad'    => 'required|numeric|min:0.01',
            'motivo'      => 'nullable|string|max:255',
        ]);

        $sucursalId = (int) ($validated['id_sucursal'] ?? config('app.sucursal_activa.id') ?? auth()->user()->id_sucursal ?? 1);
        $sucursal = Sucursal::findOrFail($sucursalId);
        $producto = Producto::findOrFail($validated['id_producto']);

        if (!$producto->controla_stock) {
            return response()->json([
                'success'     => true,
                'message'     => 'El artículo no controla stock.',
                'nuevo_stock' => 9999,
                'producto'    => [
                    'id'             => $producto->id,
                    'codigo'         => $producto->codigo,
                    'codigo_barra'   => $producto->codigo_barra,
                    'descripcion'    => $producto->descripcion,
                    'precio_venta'   => (float) $producto->precio_venta,
                    'controla_stock' => false,
                    'stock'          => 9999,
                ],
            ]);
        }

        $cantidad = (float) $validated['cantidad'];
        $motivo = $validated['motivo'] ?? 'Recuento físico previo a facturación en mostrador';

        try {
            // Asentar corrección atómica de stock en la sucursal activa con auditoría
            $producto->ajustarStock(
                $sucursalId,
                'correccion',
                $cantidad,
                $motivo,
                auth()->id(),
                'RECUENTO-POS',
                "Recuento físico de mostrador en {$sucursal->nombre} previo a venta."
            );

            // Obtener stock actualizado
            $stockRow = ProductoStock::where('producto_id', $producto->id)
                ->where('sucursal_id', $sucursalId)
                ->first();

            $nuevoStock = (float) ($stockRow?->stock ?? 0);

            return response()->json([
                'success'     => true,
                'message'     => "Se ingresaron {$cantidad} unidades por recuento físico en {$sucursal->nombre}.",
                'nuevo_stock' => $nuevoStock,
                'producto'    => [
                    'id'             => $producto->id,
                    'codigo'         => $producto->codigo,
                    'codigo_barra'   => $producto->codigo_barra,
                    'descripcion'    => $producto->descripcion,
                    'precio_venta'   => (float) $producto->precio_venta,
                    'controla_stock' => true,
                    'stock'          => $nuevoStock,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => 'Error al registrar el recuento físico: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * API JSON: Consulta y validación de estado crediticio del cliente + simulación de cuotas (1 a 4).
     */
    public function infoCreditoCliente(Request $request, Cliente $cliente)
    {
        $monto = (float) $request->get('monto', 0);
        $sucursalId = (int) ($request->get('sucursal_id') ?? config('app.sucursal_activa.id') ?? $cliente->id_sucursal ?? 1);

        // Estado crediticio calculado (mora, suspensión, deudas y límites)
        $estado = $cliente->estadoCrediticio($monto);

        // Lote/presentación activa de la sucursal para determinar el interés
        $presentacion = Presentacion::activaParaSucursal($sucursalId);
        $tasaInteres = self::normalizarPorcentajeInteres($presentacion);
        $factorInteres = self::normalizarFactorInteres($presentacion);

        // Cuotas (1 a 4 pagos) con interés escalonado según la configuración del lote
        $primeraSinInteres = (bool) ($presentacion?->primera_cuota_sin_interes ?? true);
        $cuotas = [];
        for ($i = 1; $i <= 4; $i++) {
            $calc = self::calcularFinanciacion($monto, $factorInteres, $i, $primeraSinInteres);
            $cuotas[$i] = [
                'cantidad'           => $i,
                'valor_cuota'        => $calc['valor_cuota'],
                'total_financiado'   => $calc['total'],
                'recargo'            => $calc['recargo'],
                'porcentaje_interes' => round($tasaInteres * ($primeraSinInteres ? $i - 1 : $i), 2),
                'habilitada'         => $i <= $estado['max_cuotas'],
            ];
        }

        // Obtener cuotas impagas activas del cliente para el detalle de compras y próximos 4 meses
        $cuotasImpagas = Cuota::whereHas('credito', function ($q) use ($cliente) {
            $q->where('id_cliente', $cliente->id)
              ->whereNull('fecha_cancelacion');
        })
        ->with(['credito.presentacion', 'credito.sucursal'])
        ->where('saldo_actual', '>', 0)
        ->whereNull('fecha_cobro')
        ->get();

        $loteActual = $presentacion ? (int)$presentacion->numero : 0;

        // Estructura de los próximos 4 vencimientos (1° Mes, 2° Mes, 3° Mes, 4° Mes)
        $proximosVencimientos = [];
        for ($m = 1; $m <= 4; $m++) {
            $targetLote = $loteActual > 0 ? ($loteActual + ($m - 1)) : $m;
            $proximosVencimientos[$m] = [
                'numero_mes' => $m,
                'lote'       => $targetLote,
                'cuotas'     => [],
                'total'      => 0.0,
            ];
        }

        $totalAdeudado = 0.0;
        $pendienteCobro = 0.0;

        foreach ($cuotasImpagas as $c) {
            $saldo = (float) $c->saldo_actual;
            $totalAdeudado += $saldo;

            // Formato de código de crédito visible: ej "00326983 T"
            $codNum = preg_replace('/[^0-9]/', '', (string)($c->credito?->credito ?? '0'));
            $codNumPad = str_pad($codNum ?: '0', 7, '0', STR_PAD_LEFT);
            $tipoCred = strtoupper($c->credito?->tipo_credito ?? 'T');
            $identificador = "{$codNumPad} {$tipoCred}";

            $nroCuota = (int) ($c->num_cuota ?? 1);
            $loteCuota = (int) ($c->lote_calculado ?? (($c->credito?->presentacion?->numero ?? 0) + $nroCuota));

            $cuotaItem = [
                'id'            => $c->id,
                'id_credito'    => $c->id_credito,
                'identificador' => $identificador,
                'num_cuota'     => $nroCuota,
                'lote'          => $loteCuota,
                'saldo'         => $saldo,
            ];

            if ($loteActual > 0) {
                if ($loteCuota <= $loteActual) {
                    $proximosVencimientos[1]['cuotas'][] = $cuotaItem;
                    $proximosVencimientos[1]['total'] += $saldo;
                    $pendienteCobro += $saldo;
                } elseif ($loteCuota === $loteActual + 1) {
                    $proximosVencimientos[2]['cuotas'][] = $cuotaItem;
                    $proximosVencimientos[2]['total'] += $saldo;
                } elseif ($loteCuota === $loteActual + 2) {
                    $proximosVencimientos[3]['cuotas'][] = $cuotaItem;
                    $proximosVencimientos[3]['total'] += $saldo;
                } elseif ($loteCuota === $loteActual + 3) {
                    $proximosVencimientos[4]['cuotas'][] = $cuotaItem;
                    $proximosVencimientos[4]['total'] += $saldo;
                }
            } else {
                $proximosVencimientos[1]['cuotas'][] = $cuotaItem;
                $proximosVencimientos[1]['total'] += $saldo;
                $pendienteCobro += $saldo;
            }
        }

        for ($m = 1; $m <= 4; $m++) {
            $proximosVencimientos[$m]['total'] = round($proximosVencimientos[$m]['total'], 2);
        }

        // Novedades ordenadas con la más reciente encima
        $novedades = $cliente->novedades()
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($n) {
                return [
                    'id'             => $n->id,
                    'fecha'          => $n->created_at ? $n->created_at->format('d/m/Y') : '',
                    'fecha_hora'     => $n->created_at ? $n->created_at->format('d/m/Y H:i') : '',
                    'usuario_nombre' => $n->usuario_nombre ?? 'Operador',
                    'mensaje'        => $n->mensaje,
                ];
            });

        $limiteActual = (float) ($cliente->limite_efectivo ?? $cliente->limite ?? 0);

        return response()->json([
            'cliente' => [
                'id'              => $cliente->id,
                'nombre'          => $cliente->nombre,
                'dni'             => $cliente->dni,
                'nro_cuenta'      => $cliente->nro_cuenta,
                'sucursal'        => $cliente->sucursal?->nombre ?? 'N/A',
                'limite'          => $limiteActual,
                'limite_efectivo' => $limiteActual,
            ],
            'estado_crediticio'    => $estado,
            'detalle_compras'      => [
                'total_adeudado'   => round($totalAdeudado, 2),
                'pendiente_cobro'  => round($pendienteCobro, 2),
                'mora_vencido'     => round((float)$estado['total_mora'], 2),
                'limite_disponible'=> (float) $estado['limite_disponible'],
                'limite_total'     => $limiteActual,
            ],
            'proximos_vencimientos'=> array_values($proximosVencimientos),
            'novedades'            => $novedades,
            'tasa_interes'         => $tasaInteres,
            'presentacion'         => [
                'id'      => $presentacion?->id,
                'numero'  => $presentacion?->numero,
                'abierta' => (bool) ($presentacion && is_null($presentacion->fecha_fin)),
            ],
            'simulacion_cuotas'    => $cuotas,
        ]);
    }

    /**
     * Actualiza el límite de crédito del cliente desde el modal de facturación.
     */
    public function actualizarLimiteCliente(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'limite' => 'required|numeric|min:0',
        ]);

        $nuevoLimite = round((float) $validated['limite'], 2);
        $limiteAnterior = (float) ($cliente->limite_efectivo ?? $cliente->limite ?? 0);

        $cliente->update([
            'limite'          => $nuevoLimite,
            'limite_efectivo' => $nuevoLimite,
        ]);

        $user = auth()->user();
        $userName = $user?->name ?? 'Operador';

        $cliente->novedades()->create([
            'user_id'        => $user?->id,
            'usuario_nombre' => $userName,
            'mensaje'        => "Límite modificado de $" . number_format($limiteAnterior, 2, ',', '.') . " a $" . number_format($nuevoLimite, 2, ',', '.') . " por {$userName} en Facturador.",
            'origen_accion'  => 'pos_limite',
        ]);

        return response()->json([
            'success'      => true,
            'mensaje'      => 'Límite de crédito actualizado correctamente.',
            'nuevo_limite' => $nuevoLimite,
        ]);
    }

    /**
     * Agrega un nuevo comentario/novedad al legajo del cliente desde el modal de facturación.
     */
    public function agregarComentarioCliente(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'mensaje' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        $userName = $user?->name ?? 'Operador';

        $novedad = $cliente->novedades()->create([
            'user_id'        => $user?->id,
            'usuario_nombre' => $userName,
            'mensaje'        => trim($validated['mensaje']),
            'origen_accion'  => 'pos_comentario',
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Comentario registrado en el legajo.',
            'novedad' => [
                'id'             => $novedad->id,
                'fecha'          => $novedad->created_at ? $novedad->created_at->format('d/m/Y') : date('d/m/Y'),
                'fecha_hora'     => $novedad->created_at ? $novedad->created_at->format('d/m/Y H:i') : date('d/m/Y H:i'),
                'usuario_nombre' => $userName,
                'mensaje'        => $novedad->mensaje,
            ]
        ]);
    }

    /**
     * Procesamiento transaccional de la Venta (facturación, rebaja de stock y generación de crédito si aplica).
     */
    public function store(Request $request)
    {
        // Normalizar tipos en el request para evitar fallos cuando llegan enteros desde JSON (DNI, códigos de barras, etc.)
        $input = $request->all();
        if (isset($input['cliente_documento']) && $input['cliente_documento'] !== null) {
            $input['cliente_documento'] = (string) $input['cliente_documento'];
        }
        if (isset($input['cliente_nombre']) && $input['cliente_nombre'] !== null) {
            $input['cliente_nombre'] = (string) $input['cliente_nombre'];
        }
        if (isset($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as &$item) {
                if (isset($item['codigo_producto']) && $item['codigo_producto'] !== null) {
                    $item['codigo_producto'] = (string) $item['codigo_producto'];
                }
                if (isset($item['descripcion']) && $item['descripcion'] !== null) {
                    $item['descripcion'] = (string) $item['descripcion'];
                }
            }
            unset($item);
        }
        $request->replace($input);

        try {
            $validated = $request->validate([
                'id_sucursal'             => 'nullable|exists:sucursales,id',
                'id_cliente'              => 'nullable|exists:clientes,id',
                'cliente_nombre'          => 'nullable|string|max:191',
                'cliente_documento'       => 'nullable|string|max:50',
                'tipo_comprobante'        => 'nullable|string|in:FACTURA_A,FACTURA_B,factura_a,factura_b',
                'medio_pago'              => 'required|string|max:50',
                'descuento'               => 'nullable|numeric|min:0',
                'cuotas'                  => 'nullable|integer|min:1|max:4',
                'autorizado_vendedor'     => 'nullable|boolean',
                'observaciones'           => 'nullable|string|max:1000',
                'items'                   => 'required|array|min:1',
                'items.*.descripcion'          => 'required|string|max:255',
                'items.*.cantidad'             => 'required|numeric|min:0.01',
                'items.*.precio_unitario'      => 'required|numeric|min:0',
                'items.*.descuento_porcentaje' => 'nullable|numeric|min:0|max:100',
                'items.*.id_producto'          => 'nullable|exists:productos,id',
                'items.*.codigo_producto'      => 'nullable|string|max:50',
            ]);
        } catch (ValidationException $ve) {
            $firstError = collect($ve->errors())->flatten()->first() ?? 'Datos de la venta inválidos.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error'   => $firstError,
                    'message' => $firstError,
                    'errors'  => $ve->errors(),
                ], 422);
            }
            throw $ve;
        }

        $sucursalId = $validated['id_sucursal'] ?? config('app.sucursal_activa.id') ?? auth()->user()->id_sucursal ?? 1;
        $sucursal = Sucursal::findOrFail($sucursalId);
        $user = auth()->user();

        // Verificación estricta de Apertura y Control de Caja
        $cajaActiva = CajaSesion::activaParaSucursal($sucursalId);
        if (!$cajaActiva) {
            $msg = 'No hay una caja abierta para esta sucursal. Debe realizar la Apertura de Caja antes de emitir la primera factura.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $msg, 'requiere_apertura' => true], 422);
            }
            return back()->with('error', $msg);
        }

        if (!$cajaActiva->esDeHoy()) {
            $fechaAperturaStr = $cajaActiva->fecha_apertura->format('d/m/Y');
            $msg = "La caja abierta pertenece a una jornada anterior ({$fechaAperturaStr}). Debe realizar el Cierre Z de la jornada previa e ingresar el dinero antes de continuar facturando.";
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $msg,
                    'requiere_cierre_anterior' => true,
                    'caja_anterior_id' => $cajaActiva->id
                ], 422);
            }
            return back()->with('error', $msg);
        }

        // Cálculo de subtotal a partir de los renglones (neto de descuentos por ítem / promos)
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $cant = (float) $item['cantidad'];
            $unitario = (float) $item['precio_unitario'];
            $descPorc = max(0, min(100, (float) ($item['descuento_porcentaje'] ?? 0)));
            $descMonto = round($cant * $unitario * ($descPorc / 100), 2);
            $subtotal += max(0, round(($cant * $unitario) - $descMonto, 2));
        }
        $subtotal = round($subtotal, 2);

        if ($subtotal <= 0) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'El total de la venta debe ser mayor a cero.'], 422);
            }
            return back()->with('error', 'El total de la venta debe ser mayor a cero.');
        }

        $medioPago = $validated['medio_pago'];
        $medioPagoModel = \App\Models\MedioPago::where('codigo', $medioPago)->first();
        $idMedioPago = $medioPagoModel?->id;
        $esCreditoPersonal = ($medioPago === 'credito_personal' || $medioPagoModel?->tipo_base === 'credito');

        $cliente = null;
        $credito = null;
        $recargoFinanciacion = 0;
        $cuotasNum = 1;
        $valorCuotaRef = $subtotal;

        // Validaciones estrictas si el medio de pago es Crédito Personal
        if ($esCreditoPersonal) {
            if (empty($validated['id_cliente'])) {
                $msg = 'Para abonar con Crédito Personal debe seleccionar un cliente registrado (no Consumidor Final).';
                return $request->wantsJson() ? response()->json(['error' => $msg], 422) : back()->with('error', $msg);
            }

            $cliente = Cliente::findOrFail($validated['id_cliente']);
            $cuotasNum = (int) ($validated['cuotas'] ?? 1);

            // 1. Verificación de Lote / Presentación abierta
            $presentacion = Presentacion::activaParaSucursal($sucursal->id);
            if (!$presentacion || $presentacion->fecha_fin !== null) {
                $msg = 'No hay una presentación (lote) abierta para la sucursal actual para asentar créditos.';
                return $request->wantsJson() ? response()->json(['error' => $msg], 422) : back()->with('error', $msg);
            }

            // 2. Verificación de salud crediticia (mora, suspensión, límite y cuotas)
            $autorizadoVendedor = filter_var($request->input('autorizado_vendedor', false), FILTER_VALIDATE_BOOLEAN);
            $estadoCred = $cliente->estadoCrediticio($subtotal);
            if (!$estadoCred['habilitado']) {
                if (!$autorizadoVendedor) {
                    $motivo = implode(' ', $estadoCred['motivos_bloqueo']);
                    $msg = "No se puede otorgar el crédito: {$motivo}";
                    return $request->wantsJson() ? response()->json(['error' => $msg], 422) : back()->with('error', $msg);
                }
            }

            if (!$autorizadoVendedor && $cuotasNum > $estadoCred['max_cuotas']) {
                $msg = "La cantidad de cuotas ({$cuotasNum}) supera el máximo permitido para el cliente ({$estadoCred['max_cuotas']}).";
                return $request->wantsJson() ? response()->json(['error' => $msg], 422) : back()->with('error', $msg);
            }

            // Interés escalonado según el lote (ej: 6% -> 1 pago 0%, 2c 6%, 3c 12%, 4c 18%)
            $cuotasNum = max(1, $cuotasNum);
            $calc = self::calcularFinanciacion(
                $subtotal,
                self::normalizarFactorInteres($presentacion),
                $cuotasNum,
                (bool) ($presentacion->primera_cuota_sin_interes ?? true)
            );
            $recargoFinanciacion = $calc['recargo'];
            $valorCuotaRef = $calc['valor_cuota'];
        } else {
            $autorizadoVendedor = false;
            $estadoCred = null;
            if (!empty($validated['id_cliente'])) {
                $cliente = Cliente::find($validated['id_cliente']);
            }
        }

        $descuentoMedio = 0;
        if (!$esCreditoPersonal && $medioPagoModel) {
            if ($medioPagoModel->recargo_porcentaje > 0) {
                $recargoFinanciacion = round($subtotal * ((float)$medioPagoModel->recargo_porcentaje / 100), 2);
            }
            if ($medioPagoModel->descuento_porcentaje > 0) {
                $descuentoMedio = round($subtotal * ((float)$medioPagoModel->descuento_porcentaje / 100), 2);
            }
        }

        $descuentoManual = max(0, round((float) ($request->input('descuento', 0)), 2));
        $descuentoTotal = min($subtotal, round($descuentoMedio + $descuentoManual, 2));

        $totalFinal = max(0, round($subtotal + $recargoFinanciacion - $descuentoTotal, 2));

        // 1. Resolver Punto de Venta asignado a este puesto / terminal (disponible tanto en la transacción como en la respuesta)
        $idPuntoVenta = $validated['id_punto_venta'] ?? session('terminal_punto_venta_id');
        $puntoVenta = $idPuntoVenta 
            ? PuntoVenta::where('id_sucursal', $sucursal->id)->find($idPuntoVenta) 
            : PuntoVenta::where('id_sucursal', $sucursal->id)->where('activo', true)->first();

        // Transacción completa y atómica
        try {
            $venta = DB::transaction(function () use (
                $sucursal,
                $user,
                $cliente,
                $validated,
                $medioPago,
                $idMedioPago,
                $esCreditoPersonal,
                $subtotal,
                $recargoFinanciacion,
                $descuentoTotal,
                $totalFinal,
                $cuotasNum,
                $valorCuotaRef,
                $cajaActiva,
                $autorizadoVendedor,
                $estadoCred,
                $puntoVenta
            ) {
                $tipoEmision = $puntoVenta?->tipo_emision;
                $pvNumero = $puntoVenta ? $puntoVenta->numero : $sucursal->id;

                // Número correlativo de comprobante
                $numeroFactura = !empty($validated['hasar_ticket_numero'])
                    ? (int) $validated['hasar_ticket_numero']
                    : $sucursal->siguienteNumeroComprobante();

                $comprobanteFormateado = sprintf('%05d-%08d', $pvNumero, $numeroFactura);

                // 2. Si es Crédito Personal, dar de alta el Crédito y las Cuotas
                $idCredito = null;
                if ($esCreditoPersonal && $cliente) {
                    $presentacion = Presentacion::activaParaSucursal($sucursal->id);

                    // Incremento de correlativo en sucursal_indices
                    $indice = SucursalIndice::firstOrCreate(
                        ['id_sucursal' => $sucursal->id, 'tipo_credito' => 'C'],
                        ['ultimo_indice' => 0]
                    );
                    $indice->increment('ultimo_indice');
                    $nuevoNumeroIndice = (string) $indice->ultimo_indice;

                    // Alta cabecera de crédito
                    $credito = Credito::create([
                        'id_cliente'       => $cliente->id,
                        'id_sucursal'      => $sucursal->id,
                        'id_autorizado'    => null,
                        'id_presentacion'  => $presentacion->id,
                        'credito'          => $nuevoNumeroIndice,
                        'tipo_credito'     => 'C',
                        'importe_original' => $subtotal,
                        'cantidad_cuotas'  => $cuotasNum,
                        'valor_cuota_ref'  => $valorCuotaRef,
                        'fecha_credito'    => now()->toDateString(),
                    ]);

                    // Cascada de cuotas (1 a N)
                    for ($i = 1; $i <= $cuotasNum; $i++) {
                        Cuota::create([
                            'id_credito'         => $credito->id,
                            'num_cuota'          => $i,
                            'valor_base'         => $valorCuotaRef,
                            'intereses'          => 0,
                            'entrego'            => 0,
                            'saldo_actual'       => $valorCuotaRef,
                            'fecha_ultimo_envio' => null,
                            'estado_bancario'    => null,
                        ]);
                    }

                    $idCredito = $credito->id;

                    // Mensaje para la novedad del legajo
                    $mensajeNovedad = "COMPRA A CRÉDITO: Factura {$comprobanteFormateado} por $" . number_format($totalFinal, 2, ',', '.') . " en {$cuotasNum} cuotas de $" . number_format($valorCuotaRef, 2, ',', '.') . " (Lote {$presentacion->numero}).";
                    if ($autorizadoVendedor && $estadoCred && !$estadoCred['habilitado']) {
                        $mensajeNovedad .= " [AUTORIZACIÓN EXCEPCIONAL POR VENDEDOR: {$user?->name}]";
                    }

                    // Novedad en legajo del cliente
                    $cliente->novedades()->create([
                        'user_id'        => $user?->id,
                        'usuario_nombre' => $user?->name ?? 'Operador',
                        'mensaje'        => $mensajeNovedad,
                        'origen_accion'  => 'pos_venta',
                    ]);
                }

                // 3. Crear cabecera de Venta y determinar tipo de comprobante
                $clienteNombre = $cliente?->nombre ?? ($validated['cliente_nombre'] ?? 'CONSUMIDOR FINAL');
                $clienteDoc = $cliente?->dni ?? ($validated['cliente_documento'] ?? null);

                $tipoComprobante = strtoupper($validated['tipo_comprobante'] ?? $input['tipo_comprobante'] ?? 'FACTURA_B');
                if (!in_array($tipoComprobante, ['FACTURA_A', 'FACTURA_B'])) {
                    $tipoComprobante = 'FACTURA_B';
                }

                // Si se emite Factura A, verificar que el comprador tenga CUIT válido de 11 dígitos
                if ($tipoComprobante === 'FACTURA_A') {
                    $docNumerico = preg_replace('/\D/', '', (string) ($clienteDoc ?? ''));
                    if (strlen($docNumerico) !== 11) {
                        throw new \Exception("Para emitir Factura A es obligatorio seleccionar o ingresar un cliente con CUIT válido de 11 dígitos.");
                    }
                }

                $venta = Venta::create([
                    'id_sucursal'         => $sucursal->id,
                    'id_punto_venta'      => $puntoVenta?->id,
                    'tipo_emision'        => $tipoEmision,
                    'id_usuario'          => $user?->id ?? 1,
                    'id_cliente'          => $cliente?->id,
                    'id_caja_sesion'      => $cajaActiva->id,
                    'cliente_nombre'      => $clienteNombre,
                    'cliente_documento'   => $clienteDoc,
                    'punto_venta'         => $pvNumero,
                    'numero_factura'      => $numeroFactura,
                    'comprobante'         => $comprobanteFormateado,
                    'tipo_comprobante'    => $tipoComprobante,
                    'fecha'               => now(),
                    'medio_pago'          => $medioPago,
                    'id_medio_pago'       => $idMedioPago,
                    'subtotal'            => $subtotal,
                    'recargo'             => $recargoFinanciacion,
                    'descuento'           => $descuentoTotal,
                    'total'               => $totalFinal,
                    'id_credito'          => $idCredito,
                    'cuotas_credito'      => $esCreditoPersonal ? $cuotasNum : null,
                    'valor_cuota_credito' => $esCreditoPersonal ? $valorCuotaRef : null,
                    'estado'              => 'completada',
                    'observaciones'       => $validated['observaciones'] ?? null,
                ]);

                // 4. Crear renglones de detalle (VentaItem) y movimiento de stock en la sucursal activa
                foreach ($validated['items'] as $itemData) {
                    $cant = (float) $itemData['cantidad'];
                    $unitario = (float) $itemData['precio_unitario'];
                    $descPorc = max(0, min(100, (float) ($itemData['descuento_porcentaje'] ?? 0)));
                    $descMonto = round($cant * $unitario * ($descPorc / 100), 2);
                    $itemSubtotal = max(0, round(($cant * $unitario) - $descMonto, 2));
                    $prodId = $itemData['id_producto'] ?? null;
                    $descuentaStock = false;

                    if ($prodId) {
                        $producto = Producto::find($prodId);
                        if ($producto && $producto->controla_stock) {
                            // Descuento en productos_stock de la sucursal activa
                            $stockRow = ProductoStock::firstOrCreate(
                                ['producto_id' => $producto->id, 'sucursal_id' => $sucursal->id],
                                ['stock' => 0, 'precio_venta' => $producto->precio_venta]
                            );

                            $stockAnterior = (float) $stockRow->stock;

                            // Guardia estricta: evitar generar stock negativo
                            if ($stockAnterior < $cant) {
                                throw new \Exception("Stock insuficiente para el artículo '{$producto->descripcion}' en {$sucursal->nombre}. Stock disponible: {$stockAnterior}, solicitado: {$cant}. Realice la corrección por recuento físico antes de facturar.");
                            }

                            $descuentaStock = true;
                            $stockNuevo = round($stockAnterior - $cant, 2);
                            $stockRow->update(['stock' => $stockNuevo]);

                            // Asentar en productos_movimientos (auditoría de inventario)
                            ProductoMovimiento::create([
                                'producto_id'      => $producto->id,
                                'sucursal_id'      => $sucursal->id,
                                'tipo'             => 'venta',
                                'cantidad'         => -$cant,
                                'stock_anterior'   => $stockAnterior,
                                'stock_nuevo'      => $stockNuevo,
                                'precio_costo'     => $producto->precio_costo ?? 0,
                                'precio_venta'     => $unitario,
                                'usuario_id'       => $user?->id,
                                'comprobante'      => $comprobanteFormateado,
                                'motivo'           => "Venta Factura {$comprobanteFormateado}",
                                'referencia_type'  => Venta::class,
                                'referencia_id'    => $venta->id,
                            ]);

                            // Estadísticas de rotación en producto
                            $producto->fecha_ultima_venta = now();
                            $producto->total_ventas_unidades = (float) $producto->total_ventas_unidades + $cant;
                            $producto->total_ventas_operaciones = (int) $producto->total_ventas_operaciones + 1;
                            $producto->save();
                        }
                    }

                    // Guardar ítem de la factura
                    VentaItem::create([
                        'id_venta'             => $venta->id,
                        'id_producto'          => $prodId,
                        'codigo_producto'      => $itemData['codigo_producto'] ?? null,
                        'descripcion'          => $itemData['descripcion'],
                        'cantidad'             => $cant,
                        'precio_unitario'      => $unitario,
                        'descuento_porcentaje' => $descPorc,
                        'descuento'            => $descMonto,
                        'subtotal'             => $itemSubtotal,
                        'descuenta_stock'      => $descuentaStock,
                    ]);
                }

                // 5. Emisión Fiscal según la Modalidad del Punto de Venta
                if ($puntoVenta && $tipoEmision === 'arca_wsfe') {
                    try {
                        $arcaService = app(ArcaWsfeService::class);
                        $tipoCbteAfip = ($tipoComprobante === 'FACTURA_A') ? 1 : 6;
                        $resArca = $arcaService->emitirFactura($puntoVenta, $venta, $tipoCbteAfip);
                        $venta->update([
                            'numero_factura'  => $resArca['numero_factura'],
                            'comprobante'     => sprintf('%05d-%08d', $pvNumero, $resArca['numero_factura']),
                            'cae'             => $resArca['cae'],
                            'cae_vencimiento' => $resArca['cae_vencimiento'],
                            'afip_qr'         => $resArca['afip_qr'],
                            'datos_fiscales'  => $resArca,
                        ]);
                    } catch (\Throwable $eArca) {
                        Log::error("Fallo emisión ARCA en venta #{$venta->id}: " . $eArca->getMessage());
                        throw new \Exception("Fallo en Factura Digital ARCA: " . $eArca->getMessage());
                    }
                } elseif ($puntoVenta && $tipoEmision === 'fiscal_hasar') {
                    $venta->update([
                        'datos_fiscales' => [
                            'modelo'       => $puntoVenta->configuracion['modelo'] ?? 'SMH/PT-250F',
                            'hasar_ip'     => $puntoVenta->configuracion['hasar_ip'] ?? null,
                            'puerto_local' => $puntoVenta->configuracion['puerto_local'] ?? 8585,
                            'letra'        => ($tipoComprobante === 'FACTURA_A') ? 'A' : 'B',
                        ],
                    ]);
                }

                return $venta;
            });
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        }

        $pvConfig = $puntoVenta ?? $venta->puntoVenta;

        if ($request->wantsJson()) {
            return response()->json([
                'success'          => true,
                'venta_id'         => $venta->id,
                'comprobante'      => $venta->comprobante,
                'tipo_comprobante' => $venta->tipo_comprobante,
                'es_exento_iva'    => $venta->esExentoIva(),
                'total'            => (float) $venta->total,
                'tipo_emision'     => $venta->tipo_emision,
                'cae'              => $venta->cae,
                'cae_vencimiento'  => $venta->cae_vencimiento ? $venta->cae_vencimiento->format('d/m/Y') : null,
                'afip_qr'          => $venta->afip_qr,
                'hasar_config'     => ($venta->tipo_emision === 'fiscal_hasar' && $pvConfig) ? [
                    'puerto_local' => $pvConfig->configuracion['puerto_local'] ?? 8585,
                    'hasar_ip'     => $pvConfig->configuracion['hasar_ip'] ?? '',
                    'hasar_puerto' => $pvConfig->configuracion['hasar_puerto'] ?? 2000,
                    'modelo'       => $pvConfig->configuracion['modelo'] ?? 'SMH/PT-250F',
                    'letra'        => ($venta->tipo_comprobante === 'FACTURA_A') ? 'A' : 'B',
                    'es_exento_iva'=> $venta->esExentoIva(),
                ] : null,
                'redirect_url'    => route('ventas.show', $venta->id),
                'ticket_url'      => route('ventas.imprimir', ['venta' => $venta->id, 'formato' => 'ticket']),
                'a4_url'          => route('ventas.imprimir', ['venta' => $venta->id, 'formato' => 'a4']),
                'id_credito'      => $venta->id_credito,
                'pagare_url'      => $venta->id_credito ? route('creditos.pagare', $venta->id_credito) : null,
            ]);
        }

        return redirect()->route('ventas.show', $venta->id)
            ->with('success', "Venta #{$venta->comprobante} registrada exitosamente.");
    }

    /**
     * Muestra el detalle completo de una venta y sus comprobantes asociados.
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'sucursal.provincia',
            'usuario',
            'cliente',
            'credito.cuotas',
            'items.producto'
        ]);

        return view('ventas.show', compact('venta'));
    }

    /**
     * Vista de impresión oficial de Factura / Comprobante (Ticket 80mm o formato A4).
     */
    public function imprimir(Request $request, Venta $venta)
    {
        $venta->load([
            'sucursal.provincia',
            'usuario',
            'cliente',
            'credito.cuotas',
            'items'
        ]);

        $formato = $request->get('formato', 'ticket'); // 'ticket', 'a4' o 'pagare'

        if ($formato === 'pagare') {
            if ($venta->id_credito) {
                return redirect()->route('creditos.pagare', $venta->id_credito);
            }
            return redirect()->route('ventas.imprimir', ['venta' => $venta->id, 'formato' => 'ticket']);
        }

        return view('ventas.imprimir', compact('venta', 'formato'));
    }

    /**
     * Anulación controlada de venta y reincorporación de mercadería.
     */
    public function anular(Request $request, Venta $venta)
    {
        $request->validate([
            'motivo' => 'required|string|min:5|max:255',
        ]);

        if ($venta->estado === 'anulada') {
            return back()->with('error', 'La venta ya se encuentra anulada.');
        }

        // Si fue a crédito personal, verificar que no tenga cuotas pagadas
        if ($venta->credito) {
            if ($venta->credito->cuotas()->where('entrego', '>', 0)->exists()) {
                return back()->with('error', 'No se puede anular la venta: el crédito asociado posee cuotas con pagos registrados. Debe anular los cobros primero.');
            }
        }

        DB::transaction(function () use ($request, $venta) {
            // 1. Revertir stock si fue descontado en Ushuaia
            foreach ($venta->items as $item) {
                if ($item->descuenta_stock && $item->id_producto) {
                    $producto = $item->producto;
                    if ($producto) {
                        $stockRow = ProductoStock::where('producto_id', $producto->id)
                            ->where('sucursal_id', $venta->id_sucursal)
                            ->first();

                        if ($stockRow) {
                            $stockAnt = (float) $stockRow->stock;
                            $stockNue = round($stockAnt + (float) $item->cantidad, 2);
                            $stockRow->update(['stock' => $stockNue]);

                            ProductoMovimiento::create([
                                'producto_id'      => $producto->id,
                                'sucursal_id'      => $venta->id_sucursal,
                                'tipo'             => 'ajuste_alta',
                                'cantidad'         => (float) $item->cantidad,
                                'stock_anterior'   => $stockAnt,
                                'stock_nuevo'      => $stockNue,
                                'precio_costo'     => $producto->precio_costo ?? 0,
                                'precio_venta'     => $item->precio_unitario,
                                'usuario_id'       => auth()->id(),
                                'comprobante'      => $venta->comprobante,
                                'motivo'           => "Anulación de Factura {$venta->comprobante}: {$request->motivo}",
                                'referencia_type'  => Venta::class,
                                'referencia_id'    => $venta->id,
                            ]);
                        }
                    }
                }
            }

            // 2. Anular crédito si existiese
            if ($venta->credito) {
                $credito = $venta->credito;
                $credito->cuotas()->delete();
                $credito->delete();
            }

            // 3. Emisión de Nota de Crédito Fiscal según el canal emisor
            $mensajeFiscal = '';
            if ($venta->tipo_emision === 'arca_wsfe' && !empty($venta->cae) && $venta->puntoVenta) {
                try {
                    $arcaService = app(ArcaWsfeService::class);
                    $resNc = $arcaService->emitirNotaCredito($venta->puntoVenta, $venta, $request->motivo);
                    $datosFiscales = $venta->datos_fiscales ?? [];
                    $datosFiscales['nota_credito'] = $resNc;
                    $venta->datos_fiscales = $datosFiscales;
                    $mensajeFiscal = " Se generó la Nota de Crédito ARCA N° {$resNc['numero_factura']} (CAE: {$resNc['cae']}).";
                } catch (\Throwable $eNc) {
                    Log::error("Fallo emitiendo Nota de Crédito ARCA: " . $eNc->getMessage());
                    throw new \Exception("No se pudo emitir la Nota de Crédito en ARCA: " . $eNc->getMessage());
                }
            } elseif ($venta->tipo_emision === 'fiscal_hasar') {
                $datosFiscales = $venta->datos_fiscales ?? [];
                $datosFiscales['nota_credito'] = [
                    'motivo'                  => $request->motivo,
                    'fecha'                   => now()->toDateTimeString(),
                    'pendiente_emision_hasar' => true,
                ];
                $venta->datos_fiscales = $datosFiscales;
                $mensajeFiscal = " Recuerde emitir el Ticket Nota de Crédito en el Controlador Fiscal Hasar.";
            }

            // 4. Marcar venta como anulada
            $venta->update([
                'estado'        => 'anulada',
                'observaciones' => trim(($venta->observaciones ? $venta->observaciones . " | " : "") . "ANULADA: " . $request->motivo . $mensajeFiscal),
            ]);
        });

        return back()->with('success', "Venta #{$venta->comprobante} anulada correctamente.");
    }

    /**
     * Exportación de ventas a CSV.
     */
    private function exportarCsv($query)
    {
        $filename = "ventas_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

            fputcsv($handle, [
                'Comprobante',
                'Fecha',
                'Sucursal',
                'Cliente',
                'Documento',
                'Medio de Pago',
                'Subtotal',
                'Descuento',
                'Recargo',
                'Total',
                'Crédito Asociado',
                'Cuotas',
                'Estado',
            ], ';');

            $query->chunk(500, function ($chunk) use ($handle) {
                foreach ($chunk as $v) {
                    fputcsv($handle, [
                        $v->comprobante,
                        $v->fecha ? $v->fecha->format('d/m/Y H:i') : '-',
                        $v->sucursal?->nombre ?? '-',
                        $v->cliente_nombre,
                        $v->cliente_documento ?? '-',
                        $v->medio_pago_formateado,
                        number_format((float)$v->subtotal, 2, ',', ''),
                        number_format((float)($v->descuento ?? 0), 2, ',', ''),
                        number_format((float)$v->recargo, 2, ',', ''),
                        number_format((float)$v->total, 2, ',', ''),
                        $v->credito ? $v->credito->full_identificador : '-',
                        $v->cuotas_credito ?? '-',
                        ucfirst($v->estado),
                    ], ';');
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
