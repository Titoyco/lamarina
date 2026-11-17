<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recibo;
use App\Models\ReciboDetalle;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReciboController extends Controller
{
    /**
     * Listado general de recibos con filtros de búsqueda, fechas y provincias.
     */
    public function index(Request $request)
    {
        $query = Recibo::with(['cliente', 'sucursal', 'user', 'detalles']);

        // Determinar sucursal activa y su provincia
        $sucursalActiva = config('app.sucursal_activa') ?? Sucursal::first();
        $provinciaActivaId = $sucursalActiva?->id_provincia ?? ($sucursalActiva?->isTdf() ? 2 : 1);
        $provinciaActivaNombre = ($provinciaActivaId === 2) ? 'Tierra del Fuego' : 'Chubut';

        $filtroSucursal = $request->get('sucursal');

        // Filtro por Sucursal / Provincia
        if ($filtroSucursal === 'all') {
            // Sin filtro provincial ni de sucursal
        } elseif ($filtroSucursal && str_starts_with($filtroSucursal, 'provincia_')) {
            $provId = (int) str_replace('provincia_', '', $filtroSucursal);
            $query->porProvincia($provId);
        } elseif ($filtroSucursal && is_numeric($filtroSucursal)) {
            $query->porSucursal((int) $filtroSucursal);
        } else {
            // Por defecto: provincia de la sucursal activa
            $query->porProvincia($provinciaActivaId);
        }

        // Búsqueda por número de recibo o cliente
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('numero_recibo', 'LIKE', "%{$search}%")
                  ->orWhereHas('cliente', function ($qCli) use ($search) {
                      $qCli->where('nombre', 'LIKE', "%{$search}%")
                           ->orWhere('dni', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Filtro por fecha desde / hasta
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        // Filtro por estado
        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->activos();
            } elseif ($request->estado === 'anulados') {
                $query->anulados();
            }
        }

        // Métricas rápidas del día y del mes
        $hoy = Carbon::today();
        $totalCobradoHoy = Recibo::activos()
            ->when($filtroSucursal && is_numeric($filtroSucursal), fn($q) => $q->porSucursal($filtroSucursal))
            ->when(!$filtroSucursal || str_starts_with($filtroSucursal, 'provincia_'), function($q) use ($provinciaActivaId, $filtroSucursal) {
                $pId = ($filtroSucursal && str_starts_with($filtroSucursal, 'provincia_')) ? (int)str_replace('provincia_', '', $filtroSucursal) : $provinciaActivaId;
                $q->porProvincia($pId);
            })
            ->whereDate('fecha', $hoy)
            ->sum('importe_total');

        $recibosHoyCount = Recibo::activos()
            ->when($filtroSucursal && is_numeric($filtroSucursal), fn($q) => $q->porSucursal($filtroSucursal))
            ->when(!$filtroSucursal || str_starts_with($filtroSucursal, 'provincia_'), function($q) use ($provinciaActivaId, $filtroSucursal) {
                $pId = ($filtroSucursal && str_starts_with($filtroSucursal, 'provincia_')) ? (int)str_replace('provincia_', '', $filtroSucursal) : $provinciaActivaId;
                $q->porProvincia($pId);
            })
            ->whereDate('fecha', $hoy)
            ->count();

        // Click-to-Sort (conforme a .agents/rules/table_sorting.md)
        $sortableColumns = [
            'numero_recibo' => 'recibos.numero_recibo',
            'fecha'         => 'recibos.fecha',
            'sucursal'      => 'sucursal',
            'cliente'       => 'cliente',
            'medio_pago'    => 'recibos.medio_pago',
            'importe_total' => 'recibos.importe_total',
            'estado'        => 'recibos.anulado',
        ];

        $sort = $request->get('sort', 'fecha');
        $direction = strtolower($request->get('direction', 'desc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'fecha';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if ($sort === 'sucursal') {
            $query->leftJoin('sucursales', 'recibos.id_sucursal', '=', 'sucursales.id')
                ->select('recibos.*')
                ->orderBy('sucursales.nombre', $direction);
        } elseif ($sort === 'cliente') {
            $query->leftJoin('clientes', 'recibos.id_cliente', '=', 'clientes.id')
                ->select('recibos.*')
                ->orderBy('clientes.nombre', $direction);
        } else {
            $query->orderBy($sortableColumns[$sort], $direction);
        }

        if ($sort !== 'id' && $sort !== 'numero_recibo') {
            $query->orderBy('recibos.numero_recibo', 'desc');
        }

        // Exportación a Excel / CSV con todos los filtros aplicados
        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            $exportQuery = clone $query;
            $filename = 'recibos_cobranzas_' . date('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($exportQuery) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

                fputcsv($handle, [
                    'Numero Recibo',
                    'Fecha y Hora',
                    'Sucursal',
                    'Cliente',
                    'DNI',
                    'Medio de Pago',
                    'Importe Total',
                    'Estado',
                    'Operador',
                ], ';');

                $exportQuery->chunk(1000, function ($chunk) use ($handle) {
                    foreach ($chunk as $r) {
                        fputcsv($handle, [
                            $r->numero_recibo,
                            $r->fecha ? \Carbon\Carbon::parse($r->fecha)->format('d/m/Y H:i') : '-',
                            $r->sucursal?->nombre ?? '-',
                            $r->cliente?->nombre ?? '-',
                            $r->cliente?->dni ?? '-',
                            $r->medio_pago,
                            number_format((float)$r->importe_total, 2, ',', ''),
                            $r->anulado ? 'Anulado' : 'Valido',
                            $r->user?->name ?? '-',
                        ], ';');
                    }
                });

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $recibos = $query->paginate(15)->withQueryString();

        $sucursales = Sucursal::all();

        return view('recibos.index', compact(
            'recibos',
            'sucursales',
            'provinciaActivaId',
            'provinciaActivaNombre',
            'filtroSucursal',
            'totalCobradoHoy',
            'recibosHoyCount',
            'sort',
            'direction'
        ));
    }

    /**
     * Formulario para emitir un nuevo recibo.
     */
    public function create(Request $request)
    {
        $cliente = null;
        $cuotasPendientes = collect();
        $cuotasSeleccionadasIds = (array) $request->get('cuotas_ids', []);

        if ($request->filled('cliente_id')) {
            $cliente = Cliente::with(['sucursal', 'creditos.cuotas' => function ($q) {
                $q->where('saldo_actual', '>', 0)
                  ->orderBy('num_cuota', 'asc');
            }])->findOrFail($request->cliente_id);

            // Obtener todas las cuotas con saldo exigible ordenadas por vencimiento más próximo
            $cuotasPendientes = $cliente->creditos
                ->whereNull('fecha_cancelacion')
                ->flatMap->cuotas
                ->where('saldo_actual', '>', 0)
                ->sortBy(function ($cuota) {
                    $venc = $cuota->fecha_vencimiento ? Carbon::parse($cuota->fecha_vencimiento)->timestamp : 9999999999;
                    return [$venc, $cuota->id_credito, $cuota->num_cuota];
                })
                ->values();
        }

        $sucursales = Sucursal::all();
        $sucursalActiva = config('app.sucursal_activa') ?? Sucursal::first();
        $mediosPago = \App\Models\MedioPago::activos()->get();

        return view('recibos.create', compact(
            'cliente',
            'cuotasPendientes',
            'cuotasSeleccionadasIds',
            'sucursales',
            'sucursalActiva',
            'mediosPago'
        ));
    }

    /**
     * Guarda el recibo, actualiza las cuotas, saldos y cancela créditos saldados.
     * Soporta entregas parciales y excedentes acreditados como saldo a favor en cuenta corriente.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'sucursal_id' => 'required|exists:sucursales,id',
            'medio_pago' => 'required|string|max:50',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.cuota_id' => 'required_with:items|exists:cuotas,id',
            'items.*.monto' => 'required_with:items|numeric|min:0.01',
            'monto_a_favor' => 'nullable|numeric|min:0',
        ]);

        $cliente = Cliente::findOrFail($validated['cliente_id']);
        $sucursal = Sucursal::findOrFail($validated['sucursal_id']);
        $fechaCobro = Carbon::parse($validated['fecha'])->setTimeFrom(now());
        $user = auth()->user();

        $itemsCuotas = $validated['items'] ?? [];
        $montoAFavor = round((float) ($validated['monto_a_favor'] ?? 0), 2);

        // Calcular total en cuotas
        $totalCuotas = 0;
        foreach ($itemsCuotas as $item) {
            $totalCuotas += (float) $item['monto'];
        }
        $totalCuotas = round($totalCuotas, 2);

        $importeTotal = round($totalCuotas + $montoAFavor, 2);

        if ($importeTotal <= 0) {
            return back()->withInput()->with('error', 'El recibo debe tener un importe mayor a cero (imputado a cuotas o saldo a favor).');
        }

        // Resolución de medio de pago consolidado
        $medioPagoInput = $validated['medio_pago'];
        $mpModel = \App\Models\MedioPago::where('codigo', $medioPagoInput)->orWhere('nombre', $medioPagoInput)->first();
        $idMedioPago = $mpModel?->id;
        $medioPagoNombre = $mpModel?->nombre ?? $medioPagoInput;

        $recibo = DB::transaction(function () use ($validated, $itemsCuotas, $totalCuotas, $montoAFavor, $importeTotal, $cliente, $sucursal, $fechaCobro, $user, $idMedioPago, $medioPagoNombre) {
            // 1. Obtener siguiente número correlativo de recibo
            $numeroRecibo = $sucursal->siguienteNumeroRecibo();

            // 2. Crear encabezado del recibo
            $recibo = Recibo::create([
                'id_sucursal'            => $sucursal->id,
                'id_cliente'             => $cliente->id,
                'id_user'                => $user?->id,
                'numero_recibo'          => $numeroRecibo,
                'fecha'                  => $fechaCobro,
                'importe_total'          => $importeTotal,
                'saldo_a_favor_generado' => $montoAFavor,
                'medio_pago'             => $medioPagoNombre,
                'id_medio_pago'          => $idMedioPago,
                'observaciones'          => $validated['observaciones'] ?? null,
                'anulado'                => false,
            ]);

            // 3. Imputar a cada cuota seleccionada
            $creditosAfectadosIds = [];

            foreach ($itemsCuotas as $item) {
                $montoPago = round((float) $item['monto'], 2);
                if ($montoPago <= 0) continue;

                $cuota = Cuota::with('credito')->lockForUpdate()->findOrFail($item['cuota_id']);
                $credito = $cuota->credito;
                $creditosAfectadosIds[$credito->id] = $credito;

                $saldoAnterior = (float) $cuota->saldo_actual;
                $saldoNuevo = max(0, round($saldoAnterior - $montoPago, 2));

                // Concepto descriptivo indicando si es cancelación total o pago a cuenta
                if ($saldoNuevo <= 0) {
                    $concepto = "Cancelación Cuota {$cuota->num_cuota}/{$credito->cantidad_cuotas} - Crédito #{$credito->full_identificador}";
                } else {
                    $concepto = "Pago parcial Cuota {$cuota->num_cuota}/{$credito->cantidad_cuotas} - Crédito #{$credito->full_identificador} (Resta $ " . number_format($saldoNuevo, 2, ',', '.') . ")";
                }

                // Renglón detalle del recibo
                ReciboDetalle::create([
                    'id_recibo'      => $recibo->id,
                    'id_credito'     => $credito->id,
                    'id_cuota'       => $cuota->id,
                    'num_cuota'      => $cuota->num_cuota,
                    'concepto'       => $concepto,
                    'importe'        => $montoPago,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_nuevo'    => $saldoNuevo,
                ]);

                // Actualizar la cuota
                $cuota->update([
                    'entrego'       => (float)$cuota->entrego + $montoPago,
                    'saldo_actual'  => $saldoNuevo,
                    'fecha_cobro'   => $fechaCobro,
                    'medio_pago'    => $medioPagoNombre,
                    'id_medio_pago' => $idMedioPago,
                ]);
            }

            // 4. Si hay remanente o entrega que genera Saldo a Favor
            if ($montoAFavor > 0) {
                $saldoFavorAnterior = (float) $cliente->saldo_a_favor;
                $saldoFavorNuevo = round($saldoFavorAnterior + $montoAFavor, 2);

                ReciboDetalle::create([
                    'id_recibo'      => $recibo->id,
                    'id_credito'     => null,
                    'id_cuota'       => null,
                    'num_cuota'      => null,
                    'concepto'       => "Acreditación en Cuenta Corriente - Saldo a Favor / Excedente",
                    'importe'        => $montoAFavor,
                    'saldo_anterior' => $saldoFavorAnterior,
                    'saldo_nuevo'    => $saldoFavorNuevo,
                ]);

                $cliente->update([
                    'saldo_a_favor' => $saldoFavorNuevo,
                ]);
            }

            // 5. Verificar cancelación de créditos si no tienen más saldo exigible
            foreach ($creditosAfectadosIds as $cred) {
                if (!$cred->cuotas()->where('saldo_actual', '>', 0)->exists()) {
                    $cred->update(['fecha_cancelacion' => $fechaCobro]);
                }
            }

            // 6. Actualizar saldo deudor del cliente
            $nuevoSaldoCliente = $cliente->creditos()
                ->whereNull('fecha_cancelacion')
                ->withSum('cuotas as saldo_pendiente', 'saldo_actual')
                ->get()
                ->sum('saldo_pendiente');

            $cliente->update(['saldo' => $nuevoSaldoCliente]);

            // 7. Asentar novedad en el legajo del cliente
            $detalleNovedad = "Emisión de Recibo #{$recibo->numero_recibo} por $ " . number_format($importeTotal, 2, ',', '.') . " ({$validated['medio_pago']}).";
            if ($montoAFavor > 0) {
                $detalleNovedad .= " Incluye $ " . number_format($montoAFavor, 2, ',', '.') . " acreditados como Saldo a Favor.";
            }

            $cliente->novedades()->create([
                'user_id' => $user?->id,
                'usuario_nombre' => $user?->name ?? 'Operador',
                'mensaje' => $detalleNovedad,
                'origen_accion' => 'cobranza',
            ]);

            return $recibo;
        });

        return redirect()->route('recibos.show', $recibo->id)
            ->with('success', "Recibo #{$recibo->numero_recibo} emitido exitosamente.");
    }

    /**
     * Muestra el detalle del recibo emitido.
     */
    public function show(Recibo $recibo)
    {
        $recibo->load(['cliente.localidad.provincia', 'sucursal.provincia', 'user', 'userAnulacion', 'detalles.credito', 'detalles.cuota']);

        return view('recibos.show', compact('recibo'));
    }

    /**
     * Vista de impresión limpia (Comprobante Original / Duplicado).
     */
    public function imprimir(Recibo $recibo)
    {
        $recibo->load(['cliente.localidad.provincia', 'sucursal.provincia', 'user', 'detalles.credito', 'detalles.cuota']);

        return view('recibos.imprimir', compact('recibo'));
    }

    /**
     * Anulación de recibo con reversión de cobranza en cuotas y saldos.
     */
    public function anular(Request $request, Recibo $recibo)
    {
        $request->validate([
            'motivo' => 'required|string|min:5|max:255',
        ]);

        if ($recibo->anulado) {
            return back()->with('error', 'El recibo ya se encuentra anulado.');
        }

        DB::transaction(function () use ($request, $recibo) {
            $user = auth()->user();
            $cliente = $recibo->cliente;

            // Revertir entregas en cuotas
            foreach ($recibo->detalles as $det) {
                if ($det->id_cuota) {
                    $cuota = Cuota::with('credito')->lockForUpdate()->find($det->id_cuota);
                    if ($cuota) {
                        $cuota->update([
                            'saldo_actual' => (float)$cuota->saldo_actual + (float)$det->importe,
                            'entrego'      => max(0, (float)$cuota->entrego - (float)$det->importe),
                        ]);

                        if ($cuota->saldo_actual > 0 && (float)$cuota->entrego <= 0) {
                            $cuota->update([
                                'fecha_cobro' => null,
                                'medio_pago'  => null,
                            ]);
                        }

                        // Reabrir crédito si estaba cancelado
                        if ($cuota->credito && $cuota->credito->fecha_cancelacion !== null) {
                            $cuota->credito->update(['fecha_cancelacion' => null]);
                        }
                    }
                }
            }

            // Marcar recibo como anulado
            $recibo->update([
                'anulado'           => true,
                'fecha_anulacion'   => now(),
                'motivo_anulacion'  => trim($request->motivo),
                'user_anulacion_id' => $user?->id,
            ]);

            // Revertir saldo a favor generado si corresponde
            if ((float)$recibo->saldo_a_favor_generado > 0) {
                $cliente->update([
                    'saldo_a_favor' => max(0, round((float)$cliente->saldo_a_favor - (float)$recibo->saldo_a_favor_generado, 2)),
                ]);
            }

            // Reajustar saldo del cliente
            $nuevoSaldo = $cliente->creditos()
                ->whereNull('fecha_cancelacion')
                ->withSum('cuotas as saldo_pendiente', 'saldo_actual')
                ->get()
                ->sum('saldo_pendiente');

            $cliente->update(['saldo' => $nuevoSaldo]);

            // Asentar novedad en el legajo
            $mensajeAnulacion = "ANULACIÓN de Recibo #{$recibo->numero_recibo} por $ " . number_format($recibo->importe_total, 2, ',', '.') . ". Motivo: " . trim($request->motivo);
            if ((float)$recibo->saldo_a_favor_generado > 0) {
                $mensajeAnulacion .= " (Se descontaron $ " . number_format($recibo->saldo_a_favor_generado, 2, ',', '.') . " de Saldo a Favor en cuenta corriente).";
            }

            $cliente->novedades()->create([
                'user_id' => $user?->id,
                'usuario_nombre' => $user?->name ?? 'Operador',
                'mensaje' => $mensajeAnulacion,
                'origen_accion' => 'anulacion_recibo',
            ]);
        });

        return back()->with('success', "Recibo #{$recibo->numero_recibo} anulado correctamente y saldos compensados.");
    }
}
