<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sucursal;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MetricasController extends Controller
{
    /**
     * Tablero principal de métricas BI (Ventas y Créditos).
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'ventas');
        if (!in_array($tab, ['ventas', 'creditos'])) {
            $tab = 'ventas';
        }

        // Sucursales para el filtro
        $sucursales = Sucursal::orderBy('id')->get();
        $sucursalActivaId = (int) (config('app.sucursal_activa.id') ?? 1);

        // Rango de fechas por defecto
        $hoy = Carbon::today();
        $rangoPreset = $request->input('preset', '');

        // Calcular fechas si viene un preset
        if ($rangoPreset) {
            [$calcDesde, $calcHasta] = $this->resolverFechasPreset($rangoPreset);
            $fechaDesde = $calcDesde;
            $fechaHasta = $calcHasta;
        } else {
            // Por defecto: desde el 1° de este mes hasta hoy (o últimos 30 días si el mes recién empieza)
            $fechaDesde = $request->input('fecha_desde', $hoy->copy()->startOfMonth()->toDateString());
            $fechaHasta = $request->input('fecha_hasta', $hoy->toDateString());
        }

        // Sucursal filtrada (null = todas)
        $idSucursal = $request->filled('id_sucursal') && $request->input('id_sucursal') !== 'todas'
            ? (int) $request->input('id_sucursal')
            : null;

        // Dimensión de agrupación de ventas
        $dimensionVentas = $request->input('dimension', 'categoria');
        $dimensionesValidasVentas = ['categoria', 'fabricante', 'subcategoria', 'sucursal', 'medio_pago', 'tipo_comprobante'];
        if (!in_array($dimensionVentas, $dimensionesValidasVentas)) {
            $dimensionVentas = 'categoria';
        }

        // Dimensión de agrupación de créditos
        $dimensionCreditos = $request->input('credito_dimension', 'sucursal');
        $dimensionesValidasCreditos = ['sucursal', 'plan_cuotas', 'mes'];
        if (!in_array($dimensionCreditos, $dimensionesValidasCreditos)) {
            $dimensionCreditos = 'sucursal';
        }

        // Click-to-Sort (conforme a .agents/rules/table_sorting.md)
        $sort = $request->input('sort');
        $direction = strtolower($request->input('direction', 'desc'));
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        // Si se solicita exportación a CSV / Excel (con todos los filtros y orden aplicados)
        if ($request->input('export') === 'csv' || $request->input('export') === 'excel') {
            return $this->exportarCsv($tab, $fechaDesde, $fechaHasta, $idSucursal, $dimensionVentas, $dimensionCreditos, $sort, $direction);
        }

        // 1. OBTENER MÉTRICAS DE VENTAS
        $metricasVentas = $this->obtenerMetricasVentas($fechaDesde, $fechaHasta, $idSucursal, $dimensionVentas, $sort, $direction);

        // 2. OBTENER MÉTRICAS DE CRÉDITOS
        $metricasCreditos = $this->obtenerMetricasCreditos($fechaDesde, $fechaHasta, $idSucursal, $dimensionCreditos, $sort, $direction);

        // Si es petición AJAX, devolver solo JSON
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'          => true,
                'tab'              => $tab,
                'ventas'           => $metricasVentas,
                'creditos'         => $metricasCreditos,
            ]);
        }

        return view('metricas.index', compact(
            'tab',
            'sucursales',
            'sucursalActivaId',
            'fechaDesde',
            'fechaHasta',
            'rangoPreset',
            'idSucursal',
            'dimensionVentas',
            'dimensionCreditos',
            'metricasVentas',
            'metricasCreditos',
            'sort',
            'direction'
        ));
    }

    /**
     * Cálculo de métricas y resumen dimensional de ventas.
     */
    protected function obtenerMetricasVentas(?string $fechaDesde, ?string $fechaHasta, ?int $idSucursal, string $dimension, ?string $sort = null, string $direction = 'desc'): array
    {
        // 1. KPIs Generales de Ventas
        $qVentas = DB::table('ventas')->where('estado', '!=', 'anulada');
        if ($fechaDesde) $qVentas->where('fecha', '>=', $fechaDesde . ' 00:00:00');
        if ($fechaHasta) $qVentas->where('fecha', '<=', $fechaHasta . ' 23:59:59');
        if ($idSucursal) $qVentas->where('id_sucursal', $idSucursal);

        $kpis = (clone $qVentas)->selectRaw('
            COUNT(id) as total_tickets,
            COALESCE(SUM(subtotal), 0) as total_subtotal,
            COALESCE(SUM(descuento), 0) as total_descuento_cabecera,
            COALESCE(SUM(recargo), 0) as total_recargo,
            COALESCE(SUM(total), 0) as total_cobrado
        ')->first();

        // 2. Base agregada a nivel de ítems (prendas, descuentos de MOVIMIENTO_DETALLE y valor de lista)
        $baseItems = DB::table('venta_items as vi')
            ->join('ventas as v', 'vi.id_venta', '=', 'v.id')
            ->where('v.estado', '!=', 'anulada');
        if ($fechaDesde) $baseItems->where('v.fecha', '>=', $fechaDesde . ' 00:00:00');
        if ($fechaHasta) $baseItems->where('v.fecha', '<=', $fechaHasta . ' 23:59:59');
        if ($idSucursal) $baseItems->where('v.id_sucursal', $idSucursal);

        $itemsAgg = (clone $baseItems)->selectRaw('
            COALESCE(SUM(vi.cantidad), 0) as total_prendas,
            COALESCE(SUM(vi.descuento), 0) as total_descuento_items,
            COALESCE(SUM(vi.cantidad * vi.precio_unitario), 0) as total_bruto
        ')->first();

        $totalPrendas = (float) ($itemsAgg->total_prendas ?? 0);
        $totalDescuentoItems = (float) ($itemsAgg->total_descuento_items ?? 0);
        $totalBrutoItems = (float) ($itemsAgg->total_bruto ?? 0);

        $totalTickets = (int) ($kpis->total_tickets ?? 0);
        $totalCobrado = (float) ($kpis->total_cobrado ?? 0);
        $totalDescuentoCabecera = (float) ($kpis->total_descuento_cabecera ?? 0);

        // Descuento total consolidado = descuentos en ítems (MOVIMIENTO_DETALLE) + descuentos en factura (MOVIMIENTO)
        $totalDescuento = round($totalDescuentoItems + $totalDescuentoCabecera, 2);

        // Subtotal Bruto: valor de lista total antes de descuentos
        $totalSubtotal = $totalBrutoItems > 0 ? round($totalBrutoItems, 2) : (float) ($kpis->total_subtotal ?? 0);

        $ticketPromedio = $totalTickets > 0 ? round($totalCobrado / $totalTickets, 2) : 0;
        $precioPromedioPrenda = $totalPrendas > 0 ? round($totalCobrado / $totalPrendas, 2) : 0;

        // 3. Agrupación por Dimensión
        $filas = [];
        switch ($dimension) {
            case 'categoria':
                $filas = (clone $baseItems)
                    ->leftJoin('productos as p', 'vi.id_producto', '=', 'p.id')
                    ->leftJoin('productos_categorias as c', 'p.categoria_id', '=', 'c.id')
                    ->groupBy('c.id', 'c.nombre')
                    ->selectRaw('
                        COALESCE(c.nombre, "Sin Categoría") as dimension,
                        SUM(vi.cantidad) as prendas,
                        SUM(vi.descuento) as total_descuento,
                        SUM(vi.subtotal) as total_monto,
                        COUNT(DISTINCT v.id) as tickets
                    ')
                    ->orderByDesc('total_monto')
                    ->get();
                break;

            case 'fabricante':
                $filas = (clone $baseItems)
                    ->leftJoin('productos as p', 'vi.id_producto', '=', 'p.id')
                    ->leftJoin('proveedores as prov', 'p.proveedor_id', '=', 'prov.id')
                    ->groupBy('prov.id', 'prov.nombre')
                    ->selectRaw('
                        COALESCE(prov.nombre, "Sin Proveedor / Varios") as dimension,
                        SUM(vi.cantidad) as prendas,
                        SUM(vi.descuento) as total_descuento,
                        SUM(vi.subtotal) as total_monto,
                        COUNT(DISTINCT v.id) as tickets
                    ')
                    ->orderByDesc('total_monto')
                    ->get();
                break;

            case 'subcategoria':
                $filas = (clone $baseItems)
                    ->leftJoin('productos as p', 'vi.id_producto', '=', 'p.id')
                    ->leftJoin('productos_subcategorias as subc', 'p.subcategoria_id', '=', 'subc.id')
                    ->groupBy('subc.id', 'subc.nombre')
                    ->selectRaw('
                        COALESCE(subc.nombre, "Sin Subcategoría") as dimension,
                        SUM(vi.cantidad) as prendas,
                        SUM(vi.descuento) as total_descuento,
                        SUM(vi.subtotal) as total_monto,
                        COUNT(DISTINCT v.id) as tickets
                    ')
                    ->orderByDesc('total_monto')
                    ->get();
                break;

            case 'sucursal':
                $filas = (clone $baseItems)
                    ->leftJoin('sucursales as s', 'v.id_sucursal', '=', 's.id')
                    ->groupBy('s.id', 's.nombre')
                    ->selectRaw('
                        COALESCE(s.nombre, "Sin Sucursal") as dimension,
                        SUM(vi.cantidad) as prendas,
                        SUM(vi.descuento) as total_descuento,
                        SUM(vi.subtotal) as total_monto,
                        COUNT(DISTINCT v.id) as tickets
                    ')
                    ->orderByDesc('total_monto')
                    ->get();
                break;

            case 'medio_pago':
                $filas = (clone $baseItems)
                    ->groupBy('v.medio_pago')
                    ->selectRaw('
                        UPPER(REPLACE(COALESCE(v.medio_pago, "EFECTIVO"), "_", " ")) as dimension,
                        SUM(vi.cantidad) as prendas,
                        SUM(vi.descuento) as total_descuento,
                        SUM(vi.subtotal) as total_monto,
                        COUNT(DISTINCT v.id) as tickets
                    ')
                    ->orderByDesc('total_monto')
                    ->get();
                break;

            case 'tipo_comprobante':
                $filas = (clone $baseItems)
                    ->groupBy('v.tipo_comprobante')
                    ->selectRaw('
                        COALESCE(v.tipo_comprobante, "FACTURA_B") as dimension,
                        SUM(vi.cantidad) as prendas,
                        SUM(vi.descuento) as total_descuento,
                        SUM(vi.subtotal) as total_monto,
                        COUNT(DISTINCT v.id) as tickets
                    ')
                    ->orderByDesc('total_monto')
                    ->get();
                break;
        }

        // Calcular porcentajes y promedios para cada fila
        $sumMontoFilas = (float) $filas->sum('total_monto');
        $sumPrendasFilas = (float) $filas->sum('prendas');
        $sumDescuentoFilas = (float) $filas->sum('total_descuento');

        $filasFormateadas = $filas->map(function ($f) use ($sumMontoFilas, $sumPrendasFilas) {
            $prendas = (float) $f->prendas;
            $monto = (float) $f->total_monto;
            $descuentoFila = (float) ($f->total_descuento ?? 0);
            $pctPrendas = $sumPrendasFilas > 0 ? round(($prendas / $sumPrendasFilas) * 100, 1) : 0;
            $pctMonto = $sumMontoFilas > 0 ? round(($monto / $sumMontoFilas) * 100, 1) : 0;
            $promPrenda = $prendas > 0 ? round($monto / $prendas, 2) : 0;

            return [
                'dimension'          => $f->dimension,
                'prendas'            => $prendas,
                'porcentaje_prendas' => $pctPrendas,
                'total_descuento'    => $descuentoFila,
                'total_monto'        => $monto,
                'porcentaje_monto'   => $pctMonto,
                'precio_promedio'    => $promPrenda,
                'tickets'            => (int) $f->tickets,
            ];
        });

        // Ordenamiento interactivo por encabezado
        if ($sort && in_array($sort, ['dimension', 'prendas', 'porcentaje_prendas', 'total_descuento', 'total_monto', 'porcentaje_monto', 'precio_promedio', 'tickets'])) {
            if ($direction === 'asc') {
                $filasFormateadas = $filasFormateadas->sortBy($sort)->values();
            } else {
                $filasFormateadas = $filasFormateadas->sortByDesc($sort)->values();
            }
        }

        // Top 8 para gráfico de torta/barras
        $labelsChart = $filasFormateadas->take(8)->pluck('dimension')->toArray();
        $montosChart = $filasFormateadas->take(8)->pluck('total_monto')->toArray();
        $prendasChart = $filasFormateadas->take(8)->pluck('prendas')->toArray();

        return [
            'kpis' => [
                'total_tickets'          => $totalTickets,
                'total_prendas'          => $totalPrendas,
                'total_cobrado'          => $totalCobrado,
                'total_subtotal'         => $totalSubtotal,
                'total_descuento'        => $totalDescuento,
                'total_descuento_items'  => $totalDescuentoItems,
                'total_descuento_factura'=> $totalDescuentoCabecera,
                'ticket_promedio'        => $ticketPromedio,
                'precio_promedio_prenda' => $precioPromedioPrenda,
            ],
            'dimension'     => $dimension,
            'filas'         => $filasFormateadas,
            'totales_filas' => [
                'total_prendas'   => $sumPrendasFilas,
                'total_descuento' => $sumDescuentoFilas,
                'total_monto'     => $sumMontoFilas,
            ],
            'chart' => [
                'labels'  => $labelsChart,
                'montos'  => $montosChart,
                'prendas' => $prendasChart,
            ]
        ];
    }

    /**
     * Cálculo de métricas y resumen dimensional de créditos y cobranzas.
     */
    protected function obtenerMetricasCreditos(?string $fechaDesde, ?string $fechaHasta, ?int $idSucursal, string $dimension, ?string $sort = null, string $direction = 'desc'): array
    {
        // 1. KPIs Generales de Créditos
        $qCred = DB::table('creditos');
        if ($fechaDesde) $qCred->where('fecha_credito', '>=', $fechaDesde);
        if ($fechaHasta) $qCred->where('fecha_credito', '<=', $fechaHasta);
        if ($idSucursal) $qCred->where('id_sucursal', $idSucursal);

        $kpisCred = (clone $qCred)->selectRaw('
            COUNT(id) as total_creditos,
            COALESCE(SUM(importe_original), 0) as valor_base
        ')->first();

        // Cuotas vinculadas a los créditos seleccionados
        $qCuotas = DB::table('cuotas as cu')
            ->join('creditos as cr', 'cu.id_credito', '=', 'cr.id');
        if ($fechaDesde) $qCuotas->where('cr.fecha_credito', '>=', $fechaDesde);
        if ($fechaHasta) $qCuotas->where('cr.fecha_credito', '<=', $fechaHasta);
        if ($idSucursal) $qCuotas->where('cr.id_sucursal', $idSucursal);

        $kpisCuotas = (clone $qCuotas)->selectRaw('
            COALESCE(SUM(cu.valor_base + cu.intereses), 0) as total_en_cuotas,
            COALESCE(SUM(cu.entrego), 0) as total_cobrado,
            COALESCE(SUM(cu.saldo_actual), 0) as total_deudas
        ')->first();

        $totalCreditos = (int) ($kpisCred->total_creditos ?? 0);
        $valorBase = (float) ($kpisCred->valor_base ?? 0);
        $totalEnCuotas = (float) ($kpisCuotas->total_en_cuotas ?? 0);
        $totalCobrado = (float) ($kpisCuotas->total_cobrado ?? 0);
        $totalDeudas = (float) ($kpisCuotas->total_deudas ?? 0);
        $tasaCobranza = $totalEnCuotas > 0 ? round(($totalCobrado / $totalEnCuotas) * 100, 2) : 0;

        // 2. Agrupación por Dimensión
        $filas = [];
        switch ($dimension) {
            case 'sucursal':
                $filas = (clone $qCuotas)
                    ->leftJoin('sucursales as s', 'cr.id_sucursal', '=', 's.id')
                    ->groupBy('s.id', 's.nombre')
                    ->selectRaw('
                        COALESCE(s.nombre, "Sin Sucursal") as dimension,
                        COUNT(DISTINCT cr.id) as cant_creditos,
                        COALESCE(SUM(cu.valor_base + cu.intereses), 0) as total_cuotas,
                        COALESCE(SUM(cu.entrego), 0) as total_cobrado,
                        COALESCE(SUM(cu.saldo_actual), 0) as deudas
                    ')
                    ->orderByDesc('total_cuotas')
                    ->get();
                break;

            case 'plan_cuotas':
                $filas = (clone $qCuotas)
                    ->groupBy('cr.cantidad_cuotas')
                    ->selectRaw('
                        CONCAT(cr.cantidad_cuotas, " Cuota(s)") as dimension,
                        COUNT(DISTINCT cr.id) as cant_creditos,
                        COALESCE(SUM(cu.valor_base + cu.intereses), 0) as total_cuotas,
                        COALESCE(SUM(cu.entrego), 0) as total_cobrado,
                        COALESCE(SUM(cu.saldo_actual), 0) as deudas
                    ')
                    ->orderBy('cr.cantidad_cuotas')
                    ->get();
                break;

            case 'mes':
                $filas = (clone $qCuotas)
                    ->groupBy(DB::raw('DATE_FORMAT(cr.fecha_credito, "%Y-%m")'))
                    ->selectRaw('
                        DATE_FORMAT(cr.fecha_credito, "%m/%Y") as dimension,
                        COUNT(DISTINCT cr.id) as cant_creditos,
                        COALESCE(SUM(cu.valor_base + cu.intereses), 0) as total_cuotas,
                        COALESCE(SUM(cu.entrego), 0) as total_cobrado,
                        COALESCE(SUM(cu.saldo_actual), 0) as deudas
                    ')
                    ->orderBy(DB::raw('DATE_FORMAT(cr.fecha_credito, "%Y-%m")'), 'desc')
                    ->limit(24)
                    ->get();
                break;
        }

        // Obtener valor base exacto para cada grupo sin duplicar
        $valoresBaseMap = [];
        $qBaseGroup = DB::table('creditos as cr');
        if ($fechaDesde) $qBaseGroup->where('cr.fecha_credito', '>=', $fechaDesde);
        if ($fechaHasta) $qBaseGroup->where('cr.fecha_credito', '<=', $fechaHasta);
        if ($idSucursal) $qBaseGroup->where('cr.id_sucursal', $idSucursal);

        if ($dimension === 'sucursal') {
            $valoresBaseMap = (clone $qBaseGroup)
                ->leftJoin('sucursales as s', 'cr.id_sucursal', '=', 's.id')
                ->groupBy('s.nombre')
                ->selectRaw('COALESCE(s.nombre, "Sin Sucursal") as k, SUM(cr.importe_original) as v')
                ->pluck('v', 'k')
                ->toArray();
        } elseif ($dimension === 'plan_cuotas') {
            $valoresBaseMap = (clone $qBaseGroup)
                ->groupBy('cr.cantidad_cuotas')
                ->selectRaw('CONCAT(cr.cantidad_cuotas, " Cuota(s)") as k, SUM(cr.importe_original) as v')
                ->pluck('v', 'k')
                ->toArray();
        } elseif ($dimension === 'mes') {
            $valoresBaseMap = (clone $qBaseGroup)
                ->groupBy(DB::raw('DATE_FORMAT(cr.fecha_credito, "%m/%Y")'))
                ->selectRaw('DATE_FORMAT(cr.fecha_credito, "%m/%Y") as k, SUM(cr.importe_original) as v')
                ->pluck('v', 'k')
                ->toArray();
        }

        $filasFormateadas = $filas->map(function ($f) use ($valoresBaseMap) {
            $dim = $f->dimension;
            $cuotas = (float) $f->total_cuotas;
            $cobrado = (float) $f->total_cobrado;
            $deudas = (float) $f->deudas;
            $base = (float) ($valoresBaseMap[$dim] ?? 0);
            $recupero = $cuotas > 0 ? round(($cobrado / $cuotas) * 100, 1) : 0;

            return [
                'dimension'     => $dim,
                'cant_creditos' => (int) $f->cant_creditos,
                'valor_base'    => $base,
                'total_cuotas'  => $cuotas,
                'total_cobrado' => $cobrado,
                'deudas'        => $deudas,
                'tasa_recupero' => $recupero,
            ];
        });

        // Ordenamiento interactivo por encabezado
        if ($sort && in_array($sort, ['dimension', 'cant_creditos', 'valor_base', 'total_cuotas', 'total_cobrado', 'deudas', 'tasa_recupero'])) {
            if ($direction === 'asc') {
                $filasFormateadas = $filasFormateadas->sortBy($sort)->values();
            } else {
                $filasFormateadas = $filasFormateadas->sortByDesc($sort)->values();
            }
        }

        return [
            'kpis' => [
                'total_creditos'  => $totalCreditos,
                'valor_base'      => $valorBase,
                'total_en_cuotas' => $totalEnCuotas,
                'total_cobrado'   => $totalCobrado,
                'total_deudas'    => $totalDeudas,
                'tasa_cobranza'   => $tasaCobranza,
            ],
            'dimension' => $dimension,
            'filas'     => $filasFormateadas,
            'chart'     => [
                'labels'   => $filasFormateadas->take(8)->pluck('dimension')->toArray(),
                'cuotas'   => $filasFormateadas->take(8)->pluck('total_cuotas')->toArray(),
                'cobrado'  => $filasFormateadas->take(8)->pluck('total_cobrado')->toArray(),
                'deudas'   => $filasFormateadas->take(8)->pluck('deudas')->toArray(),
            ]
        ];
    }

    /**
     * Resuelve rango de fechas según presets rápidos estilo PowerBI.
     */
    protected function resolverFechasPreset(string $preset): array
    {
        $hoy = Carbon::today();
        switch ($preset) {
            case 'hoy':
                return [$hoy->toDateString(), $hoy->toDateString()];
            case 'ayer':
                $ayer = $hoy->copy()->subDay();
                return [$ayer->toDateString(), $ayer->toDateString()];
            case 'semana':
                return [$hoy->copy()->startOfWeek()->toDateString(), $hoy->toDateString()];
            case 'mes':
                return [$hoy->copy()->startOfMonth()->toDateString(), $hoy->toDateString()];
            case 'mes_anterior':
                $mesAnt = $hoy->copy()->subMonth();
                return [$mesAnt->copy()->startOfMonth()->toDateString(), $mesAnt->copy()->endOfMonth()->toDateString()];
            case 'trimestre':
                return [$hoy->copy()->subMonths(3)->startOfMonth()->toDateString(), $hoy->toDateString()];
            case 'anio':
                return [$hoy->copy()->startOfYear()->toDateString(), $hoy->toDateString()];
            case 'todo':
            default:
                return ['2000-01-01', $hoy->toDateString()];
        }
    }

    /**
     * Exportación de la tabla actual a formato CSV/Excel descargable.
     */
    protected function exportarCsv(string $tab, ?string $fechaDesde, ?string $fechaHasta, ?int $idSucursal, string $dimVentas, string $dimCreditos, ?string $sort = null, string $direction = 'desc'): StreamedResponse
    {
        $fileName = "metricas_{$tab}_" . date('Ymd_His') . ".csv";

        $response = new StreamedResponse(function () use ($tab, $fechaDesde, $fechaHasta, $idSucursal, $dimVentas, $dimCreditos, $sort, $direction) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para visualización correcta de tildes y caracteres en Excel en español
            fputs($handle, "\xEF\xBB\xBF");

            if ($tab === 'ventas') {
                $data = $this->obtenerMetricasVentas($fechaDesde, $fechaHasta, $idSucursal, $dimVentas, $sort, $direction);
                fputcsv($handle, ['TABLERO DE METRICAS DE VENTAS - LA MARINA', 'Rango:', "{$fechaDesde} a {$fechaHasta}"], ';');
                fputcsv($handle, [], ';');
                fputcsv($handle, ['Total Tickets', 'Total Prendas', 'Subtotal Bruto ($)', 'Descuentos ($)', 'Total Facturado ($)', 'Ticket Promedio ($)'], ';');
                fputcsv($handle, [
                    $data['kpis']['total_tickets'],
                    $data['kpis']['total_prendas'],
                    number_format($data['kpis']['total_subtotal'], 2, ',', ''),
                    number_format($data['kpis']['total_descuento'], 2, ',', ''),
                    number_format($data['kpis']['total_cobrado'], 2, ',', ''),
                    number_format($data['kpis']['ticket_promedio'], 2, ',', ''),
                ], ';');
                fputcsv($handle, [], ';');
                fputcsv($handle, [strtoupper($dimVentas), 'Prendas (Cant)', '% Prendas', 'Descuentos ($)', 'Total Facturado ($)', '% Facturado', 'Precio Prom. Prenda ($)', 'Tickets'], ';');

                foreach ($data['filas'] as $f) {
                    fputcsv($handle, [
                        $f['dimension'],
                        $f['prendas'],
                        $f['porcentaje_prendas'] . '%',
                        number_format($f['total_descuento'] ?? 0, 2, ',', ''),
                        number_format($f['total_monto'], 2, ',', ''),
                        $f['porcentaje_monto'] . '%',
                        number_format($f['precio_promedio'], 2, ',', ''),
                        $f['tickets'],
                    ], ';');
                }
            } else {
                $data = $this->obtenerMetricasCreditos($fechaDesde, $fechaHasta, $idSucursal, $dimCreditos, $sort, $direction);
                fputcsv($handle, ['TABLERO DE METRICAS DE CREDITOS Y COBRANZAS - LA MARINA', 'Rango:', "{$fechaDesde} a {$fechaHasta}"], ';');
                fputcsv($handle, [], ';');
                fputcsv($handle, ['Total Créditos', 'Valor Base ($)', 'Total en Cuotas ($)', 'Total Cobrado ($)', 'Deudas Pendientes ($)', '% Cobranza'], ';');
                fputcsv($handle, [
                    $data['kpis']['total_creditos'],
                    number_format($data['kpis']['valor_base'], 2, ',', ''),
                    number_format($data['kpis']['total_en_cuotas'], 2, ',', ''),
                    number_format($data['kpis']['total_cobrado'], 2, ',', ''),
                    number_format($data['kpis']['total_deudas'], 2, ',', ''),
                    $data['kpis']['tasa_cobranza'] . '%',
                ], ';');
                fputcsv($handle, [], ';');
                fputcsv($handle, [strtoupper($dimCreditos), 'Cant. Créditos', 'Valor Base ($)', 'Total en Cuotas ($)', 'Total Cobrado ($)', 'Deudas Pendientes ($)', '% Recupero'], ';');

                foreach ($data['filas'] as $f) {
                    fputcsv($handle, [
                        $f['dimension'],
                        $f['cant_creditos'],
                        number_format($f['valor_base'], 2, ',', ''),
                        number_format($f['total_cuotas'], 2, ',', ''),
                        number_format($f['total_cobrado'], 2, ',', ''),
                        number_format($f['deudas'], 2, ',', ''),
                        $f['tasa_recupero'] . '%',
                    ], ';');
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        return $response;
    }
}

