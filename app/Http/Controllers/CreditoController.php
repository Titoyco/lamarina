<?php

namespace App\Http\Controllers;

use App\Models\Credito;
use App\Models\Cuota;
use Illuminate\Http\Request;
use App\Models\Sucursal;
use App\Models\Presentacion;
use App\Models\SucursalIndice;
use App\Models\Cliente;
use App\Models\ClienteAutorizado;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CreditoController extends Controller
{
    public function index(Request $request)
    {
        $query = Credito::select('creditos.*')
            ->join('clientes', 'clientes.id', '=', 'creditos.id_cliente')
            ->with(['cliente.sucursal', 'sucursal', 'autorizado.cliente', 'presentacion'])
            ->withSum('cuotas as total_valor_base', 'valor_base')
            ->withSum('cuotas as total_entrego', 'entrego')
            ->withSum('cuotas as total_intereses', 'intereses')
            ->withSum('cuotas as total_saldo_actual', 'saldo_actual');

        // Determinar sucursal activa y su provincia
        $sucursalActiva = config('app.sucursal_activa') ?? Sucursal::first();
        $provinciaActivaId = $sucursalActiva?->id_provincia ?? ($sucursalActiva?->isTdf() ? 2 : 1);
        $provinciaActivaNombre = ($provinciaActivaId === 2) ? 'Tierra del Fuego' : 'Chubut';

        $filtroSucursal = $request->get('sucursal');

        // 1. FILTRO ESTRICTO POR CLIENTE ID (Si se pasa por URL)
        if ($request->filled('cliente_id')) {
            $query->where('creditos.id_cliente', $request->cliente_id);
        } else {
            // 2. Manejo de Filtro por Sucursal / Provincia
            if ($filtroSucursal === 'all') {
                // "en caso de seleccionar en sucursales todas se quiete el filtro de provincia"
                // No se aplica ningún filtro de sucursal ni de provincia.
            } elseif ($filtroSucursal && str_starts_with($filtroSucursal, 'provincia_')) {
                // Filtro explícito por provincia (ej: provincia_1 o provincia_2)
                $provId = (int) str_replace('provincia_', '', $filtroSucursal);
                $sucursalesProv = Sucursal::where('id_provincia', $provId)->pluck('id')->toArray();
                if (empty($sucursalesProv)) {
                    $sucursalesProv = ($provId === 2) ? [1, 2] : [3, 4];
                }
                $query->whereIn('clientes.id_sucursal', $sucursalesProv);
            } elseif ($filtroSucursal && is_numeric($filtroSucursal)) {
                // "Además se debe poder seguir separando los créditos por sucursal."
                $sucId = (int) $filtroSucursal;
                $sucursalSeleccionada = Sucursal::find($sucId);
                $provIdSuc = $sucursalSeleccionada?->id_provincia ?? (in_array($sucId, [1, 2]) ? 2 : 1);
                $sucursalesProv = Sucursal::where('id_provincia', $provIdSuc)->pluck('id')->toArray();

                $query->where(function ($q) use ($sucId, $sucursalesProv) {
                    $q->where('clientes.id_sucursal', $sucId)
                      ->orWhere(function ($subQ) use ($sucId, $sucursalesProv) {
                          $subQ->where('creditos.id_sucursal', $sucId)
                               ->whereIn('clientes.id_sucursal', $sucursalesProv);
                      });
                });
            } else {
                // "al ingresar se muestren los creditos de los clientes de la provincia de la sucursal activa"
                $sucursalesProv = Sucursal::where('id_provincia', $provinciaActivaId)->pluck('id')->toArray();
                if (empty($sucursalesProv)) {
                    $sucursalesProv = ($provinciaActivaId === 2) ? [1, 2] : [3, 4];
                }
                $query->whereIn('clientes.id_sucursal', $sucursalesProv);
            }
        }

        // 3. Filtro por Búsqueda General (agrupado para no romper el cliente_id)
        if ($request->filled('search')) {
            $search = trim($request->search);
            // Extrae número correlativo si buscan por código compuesto (ej: C0001234TW -> 1234)
            $numericSearch = ltrim(preg_replace('/[^0-9]/', '', $search), '0');

            $query->where(function ($q) use ($search, $numericSearch) {
                $q->where('creditos.credito', 'LIKE', "%{$search}%");
                if (!empty($numericSearch)) {
                    $q->orWhere('creditos.credito', $numericSearch);
                }
                $q->orWhereHas('cliente', function ($qCliente) use ($search) {
                    $qCliente->where('nombre', 'LIKE', "%{$search}%")
                            ->orWhere('dni', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('autorizado.cliente', function ($qAut) use ($search) {
                    $qAut->where('nombre', 'LIKE', "%{$search}%")
                        ->orWhere('dni', 'LIKE', "%{$search}%");
                });
            });
        }

        // 4. Filtro por Tipo de Crédito (C, D, R)
        if ($request->filled('tipo') && $request->tipo !== 'all') {
            $query->where('creditos.tipo_credito', $request->tipo);
        }

        // 5. Filtro por Estado (Abiertos / Cancelados)
        if ($request->filled('estado') && $request->estado !== 'all') {
            if ($request->estado === 'abiertos') {
                $query->whereNull('creditos.fecha_cancelacion');
            } elseif ($request->estado === 'cancelados') {
                $query->whereNotNull('creditos.fecha_cancelacion');
            }
        }

        // ====================================================================
        // 6. FILTROS FINANCIEROS (Corregidos usando whereHas)
        // ====================================================================
        
        // Interés > 0
        if ($request->boolean('has_interes')) {
            $query->whereHas('cuotas', function($q) {
                $q->where('intereses', '>', 0);
            });
        }

        // Entregó > 0
        if ($request->boolean('has_entrego')) {
            $query->whereHas('cuotas', function($q) {
                $q->where('entrego', '>', 0);
            });
        }

        // A Pagar (Saldo) > 0
        if ($request->boolean('has_saldo')) {
            $query->whereHas('cuotas', function($q) {
                $q->where('saldo_actual', '>', 0);
            });
        }

        // ====================================================================
        
        // ====================================================================
        // 7. ORDENAMIENTO DE LA LISTA (Click-to-sort interactivo)
        // ====================================================================
        $sortableColumns = [
            'credito'     => 'creditos.credito',
            'id'          => 'creditos.credito',
            'fecha'       => 'creditos.fecha_credito',
            'cliente'     => 'clientes.nombre',
            'cuota'       => 'creditos.cantidad_cuotas',
            'valor_cuota' => 'creditos.valor_cuota_ref',
            'total'       => 'total_valor_base',
            'intereses'   => 'total_intereses',
            'entrego'     => 'total_entrego',
            'a_pagar'     => 'total_saldo_actual',
            'saldo'       => 'total_saldo_actual',
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
        if ($orderByCol !== 'creditos.id') {
            $query->orderBy('creditos.id', 'desc');
        }

        // ====================================================================
        // 8. EXPORTACIÓN A EXCEL / CSV (si se solicita)
        // ====================================================================
        if ($request->get('export') === 'excel') {
            $exportQuery = clone $query;
            $filename = 'cartera_creditos_' . date('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($exportQuery) {
                $handle = fopen('php://output', 'w');
                // UTF-8 BOM para que Excel en Windows abra directamente con caracteres y acentos correctos
                fputs($handle, "\xEF\xBB\xBF");

                // Encabezados separados por punto y coma (estándar para Excel en español)
                fputcsv($handle, [
                    'ID Gestion',
                    'Fecha',
                    'Cliente',
                    'DNI',
                    'Sucursal',
                    'Cuota',
                    'Valor Cuota',
                    'Total',
                    'Intereses',
                    'Entrego',
                    'A Pagar',
                    'Estado'
                ], ';');

                $exportQuery->chunk(1000, function ($chunk) use ($handle) {
                    foreach ($chunk as $credito) {
                        $esCancelado = !is_null($credito->fecha_cancelacion);
                        fputcsv($handle, [
                            $credito->full_identificador,
                            $credito->fecha_credito ? \Carbon\Carbon::parse($credito->fecha_credito)->format('d/m/Y') : '-',
                            $credito->cliente?->nombre ?? '-',
                            $credito->cliente?->dni ?? '-',
                            $credito->cliente?->sucursal?->nombre ?? ($credito->sucursal?->nombre ?? '-'),
                            $credito->cuota_progreso,
                            number_format((float)$credito->valor_cuota, 2, ',', ''),
                            number_format((float)($credito->total_valor_base ?? 0), 2, ',', ''),
                            number_format((float)($credito->total_intereses ?? 0), 2, ',', ''),
                            number_format((float)($credito->total_entrego ?? 0), 2, ',', ''),
                            number_format((float)($credito->total_saldo_actual ?? 0), 2, ',', ''),
                            $esCancelado ? 'Cancelado' : 'Abierto',
                        ], ';');
                    }
                });

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // ====================================================================
        // 9. TOTALES GENERALES DE LA CONSULTA (Previo a la paginación)
        // ====================================================================
        $totalesSubQuery = (clone $query);
        $totalesSubQuery->getQuery()->columns = ['creditos.id'];
        $totalesSubQuery->getQuery()->orders = null;
        $totalesSubQuery->getQuery()->offset = null;
        $totalesSubQuery->getQuery()->limit = null;

        $totalAPagar = (float) Cuota::joinSub(
            $totalesSubQuery,
            'filtered_creditos',
            'filtered_creditos.id',
            '=',
            'cuotas.id_credito'
        )->sum('cuotas.saldo_actual');

        $creditos = $query->paginate(15)->withQueryString();

        $totalCreditos = $creditos->total();

        $sucursales = Sucursal::all();

        return view('creditos.index', compact(
            'creditos', 
            'sucursales', 
            'provinciaActivaId', 
            'provinciaActivaNombre', 
            'filtroSucursal',
            'totalCreditos',
            'totalAPagar',
            'sort',
            'direction'
        ));
    }

    public function show(Credito $credito)
    {
        // Cargar relaciones necesarias
        $credito->load([
            'cliente',
            'sucursal',
            'autorizado.cliente',
            'cuotas' => function ($q) {
                $q->orderBy('num_cuota', 'asc');
            }
        ]);

        // Totales calculados sobre las cuotas para las tarjetas de resumen
        $totalValorBase = $credito->cuotas->sum('valor_base');
        $totalIntereses = $credito->cuotas->sum('intereses');
        $totalEntrego    = $credito->cuotas->sum('entrego');
        $totalSaldo      = $credito->cuotas->sum('saldo_actual');

        return view('creditos.show', compact(
            'credito', 
            'totalValorBase', 
            'totalIntereses', 
            'totalEntrego', 
            'totalSaldo'
        ));
    }

    public function pagarCuota(Request $request, Cuota $cuota)
    {
        // Validación explícita del cobro: rechaza montos negativos, en cero y sobrepagos
        // que dejarían el saldo exigible en negativo.
        $validated = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01', 'max:' . $cuota->saldo_actual],
            'medio' => ['required', 'string', 'max:50'],
        ]);

        $monto = (float) $validated['monto'];

        // Actualización consistente + cierre automático del crédito (atómico).
        DB::transaction(function () use ($cuota, $monto, $validated) {
            // 1. Acumulamos la entrega (pago parcial acumulativo) y descontamos el saldo exigible.
            $cuota->update([
                'entrego'      => (float) $cuota->entrego + $monto,
                'fecha_cobro'  => now(),
                'medio_pago'   => $validated['medio'],
                'saldo_actual' => max(0, (float) $cuota->saldo_actual - $monto),
            ]);

            // 2. Si el crédito ya no tiene cuotas con saldo exigible, queda cancelado.
            $credito = $cuota->credito;
            if ($credito && ! $credito->cuotas()->where('saldo_actual', '>', 0)->exists()) {
                $credito->update(['fecha_cancelacion' => now()]);
            }
        });

        // Nombre de la variable de sesión 'success' alineado a <x-notification-modal /> (specs/ui-guidelines.md §9).
        return back()->with('success', 'Pago registrado correctamente');
    }

    /**
     * Transacción compensatoria: Anulación de un pago registrado erróneamente.
     */
    public function anularPago(Request $request, Cuota $cuota)
    {
        $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        // Validar que la cuota tenga cobros registrados
        if ((float) $cuota->entrego <= 0 && $cuota->fecha_cobro === null) {
            return back()->with('error', 'La cuota no registra pagos para anular.');
        }

        DB::transaction(function () use ($request, $cuota) {
            $montoRevertido = (float) $cuota->entrego;

            // Reabrir la cuota restaurando el saldo exigible
            $cuota->update([
                'saldo_actual' => (float) $cuota->saldo_actual + $montoRevertido,
                'entrego'      => 0,
                'fecha_cobro'  => null,
                'medio_pago'   => null,
            ]);

            // Si el crédito estaba cerrado por cancelación total, reabrirlo
            $credito = $cuota->credito;
            if ($credito && $credito->fecha_cancelacion !== null) {
                $credito->update(['fecha_cancelacion' => null]);
            }

            // Asentar en el historial humano de novedades del titular ('cliente_novedades')
            $credito?->cliente?->novedades()->create([
                'user_id' => auth()->id(),
                'tipo'    => 0, // Gestión crítica / Reclamo
                'mensaje' => "ANULACIÓN DE PAGO: Cuota #{$cuota->num_cuota} (Crédito {$credito->full_identificador}) por $" . number_format($montoRevertido, 2, ',', '.') . ". Motivo: " . $request->motivo,
            ]);
        });

        return back()->with('success', 'Pago anulado y cuota reabierta correctamente.');
    }

    /**
     * Transacción compensatoria: Anulación y eliminación de un crédito.
     */
    public function anularCredito(Request $request, Credito $credito)
    {
        $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        // Validar que NINGUNA cuota del crédito tenga pagos registrados
        if ($credito->cuotas()->where('entrego', '>', 0)->exists()) {
            return back()->with('error', 'No se puede anular el crédito porque posee cuotas con pagos registrados. Debe anular los pagos primero.');
        }

        DB::transaction(function () use ($request, $credito) {
            // Registrar en 'cliente_novedades' antes de anular/eliminar
            $credito->cliente?->novedades()->create([
                'user_id' => auth()->id(),
                'tipo'    => 0, // Gestión crítica / Reclamo
                'mensaje' => "ANULACIÓN DE CRÉDITO: Se anuló el crédito {$credito->full_identificador} (Importe: $" . number_format($credito->importe_original, 2, ',', '.') . "). Motivo: " . $request->motivo,
            ]);

            // Eliminar el crédito (las cuotas se eliminan en cascada por FK on delete cascade)
            $credito->delete();
        });

        return redirect()->route('creditos.index')->with('success', 'Crédito anulado y eliminado del padrón correctamente.');
    }

    public function create(Request $request)
    {
        $clienteId = $request->get('id_cliente') ?? $request->get('cliente_id');

        return redirect()->route('ventas.create', array_filter([
            'id_cliente' => $clienteId,
            'medio_pago' => 'credito_personal',
        ]));
    }

    public function oldCreateDisabled(Request $request)
    {
            
            // Verificamos si la operación la realiza una firma autorizada.
            // ⚠️ $autorizadoId es el ID del REGISTRO de autorización (cliente_autorizados.id),
            // NO el id de un cliente: 'creditos.id_autorizado' apunta a 'cliente_autorizados'.
            $autorizado = null;
            if ($autorizadoId) {
                $autorizado = ClienteAutorizado::with('cliente')
                    ->where('id', $autorizadoId)
                    ->where('id_titular', $cliente->id) // Solo autorizaciones vigentes de ESTE titular
                    ->first();
            }

            // 1. Obtención de la Presentación Activa según la jurisdicción de la sucursal activa
            $sucursalId = session('sucursal_activa_id') ?? Auth::user()->id_sucursal ?? 1;
            $presentacionActivaModel = Presentacion::activaParaSucursal($sucursalId);
            $presentacionActual = (int) ($presentacionActivaModel?->numero ?? (in_array((int)$sucursalId, [1, 2]) ? 103 : 1));

            // 2. Obtener Créditos Activos y sus Cuotas Pendientes de Pago
            $creditosActivos = $cliente->creditos->whereNull('fecha_cancelacion');
            
            // Obtenemos únicamente las cuotas que aún no fueron saldadas por completo
            $cuotasActivas = $creditosActivos->flatMap->cuotas->filter(function ($cuota) {
                return !isset($cuota->pagada) || $cuota->pagada == false || ($cuota->saldo_actual ?? 1) > 0;
            });

            // 3. Estructura para agrupar los vencimientos de los próximos 4 meses / presentaciones
            $vencimientos = [
                1 => ['cuotas' => collect(), 'total' => 0, 'presentacion' => $presentacionActual],
                2 => ['cuotas' => collect(), 'total' => 0, 'presentacion' => $presentacionActual + 1],
                3 => ['cuotas' => collect(), 'total' => 0, 'presentacion' => $presentacionActual + 2],
                4 => ['cuotas' => collect(), 'total' => 0, 'presentacion' => $presentacionActual + 3],
            ];

            // 4. Iteración y distribución de cuotas por presentación
        foreach ($cuotasActivas as $cuota) {
            // 1. Obtener la presentación
            $idPresentacionCredito = (int) ($cuota->credito->id_presentacion ?? $cuota->id_presentacion ?? 100);
            
            // 2. Número de cuota (evalúa varias opciones de nombres de columna)
            $nroCuota = (int) ($cuota->nro_cuota ?? $cuota->num_cuota ?? $cuota->cuota ?? 1);
            $cuota->numero_display = $nroCuota; // Guardamos explícitamente para el Blade

            // 3. Presentación de Cobro (id_presentacion + num_cuota)
            $presentacionCuota = $idPresentacionCredito + $nroCuota;

            // 4. Mapeo del valor puro de la cuota (Pasa por todas las variantes posibles de nombre)
            $valorPuro = (float) (
                $cuota->valor_base 
                ?? $cuota->importe 
                ?? $cuota->monto 
                ?? $cuota->valor 
                ?? $cuota->credito->valor_cuota 
                ?? $cuota->saldo_actual 
                ?? 0
            );

            $entregado = (float) ($cuota->entrego ?? $cuota->a_favor ?? 0);
            $saldoPendiente = max(0, $valorPuro - $entregado);

            // Si la cuota ya no debe nada, no se incluye en la grilla
            if ($saldoPendiente <= 0) {
                continue;
            }

            // Inyectamos el valor listo para Blade
            $cuota->saldo_neto_base = $saldoPendiente;

            // 5. Agrupamiento por mes
            if ($presentacionCuota <= $presentacionActual) {
                // Presentación actual o vencidas
                $vencimientos[1]['cuotas']->push($cuota);
                $vencimientos[1]['total'] += $saldoPendiente;
            } elseif ($presentacionCuota == $presentacionActual + 1) {
                // 2° Mes
                $vencimientos[2]['cuotas']->push($cuota);
                $vencimientos[2]['total'] += $saldoPendiente;
            } elseif ($presentacionCuota == $presentacionActual + 2) {
                // 3° Mes
                $vencimientos[3]['cuotas']->push($cuota);
                $vencimientos[3]['total'] += $saldoPendiente;
            } elseif ($presentacionCuota == $presentacionActual + 3) {
                // 4° Mes
                $vencimientos[4]['cuotas']->push($cuota);
                $vencimientos[4]['total'] += $saldoPendiente;
            }
        }

            // 5. Cálculo de Límites
            $limiteTotal = (float) ($cliente->limite ?? 800000);
            $totalOtorgadoActivo = (float) $creditosActivos->sum('importe_original');
            $limiteDisponible = max(0, $limiteTotal - $totalOtorgadoActivo);

            // 6. Totales Financieros de la Cartera Activa del Cliente
            $totalCreditosActivos = (float) $cuotasActivas->sum('valor_base');
            $totalEntregado = (float) $cuotasActivas->sum('entrego');
            $totalIntereses = (float) $cuotasActivas->sum('intereses');
            $totalAPagar = (float) $cuotasActivas->sum('saldo_actual');

            $aFavor = (float) $cliente->saldo ?? 0;

            return view('creditos.create', compact(
                'cliente',
                'autorizado',
                'presentacionActual',
                'presentacionActivaModel',
                'vencimientos',
                'limiteTotal',
                'limiteDisponible',
                'totalEntregado',
                'totalCreditosActivos',
                'totalIntereses',
                'totalAPagar',
                'aFavor'
            ));
        }

    public function store(Request $request)
    {
        // Si no vienen sucursal, presentación o tipo_credito, se resuelven por la sucursal activa y su jurisdicción
        $sucursalId = $request->input('id_sucursal') ?? session('sucursal_activa_id') ?? Auth::user()->id_sucursal ?? 1;
        $presentacionModel = Presentacion::activaParaSucursal($sucursalId);

        $request->merge([
            'id_sucursal' => $sucursalId,
            'id_presentacion' => $request->input('id_presentacion') ?? $presentacionModel?->id,
            'tipo_credito' => $request->input('tipo_credito') ?? 'C',
            'cantidad_cuotas' => $request->input('cantidad_cuotas') ?? $request->input('cuotas') ?? 1,
        ]);

        $request->validate([
            'id_cliente' => 'required|exists:clientes,id',
            'id_sucursal' => 'required|exists:sucursales,id',
            'id_presentacion' => 'required|exists:presentaciones,id',
            'tipo_credito' => 'required|in:C,D,R',
            'importe_original' => 'required|numeric|min:1',
            'cantidad_cuotas' => 'required|integer|min:1',
            // ⚠️ 'id_autorizado' es el ID del registro en 'cliente_autorizados' (no un clientes.id) y
            // debe pertenecer al titular que solicita el crédito. Si no viene, se persiste NULL.
            'id_autorizado' => [
                'nullable',
                'integer',
                Rule::exists('cliente_autorizados', 'id')
                    ->where('id_titular', $request->input('id_cliente')),
            ],
        ]);

        // ====================================================================
        // REGLAS DE NEGOCIO DEL ALTA (resueltas y validadas contra la BD
        // ANTES de crear el crédito; cualquier fallo devuelve error de validación)
        // ====================================================================

        // 0. Resolución del cliente titular (fuente de límites y habilitación).
        $cliente = Cliente::findOrFail($request->id_cliente);

        // a) Presentación ABIERTA: sólo se opera contra un lote vigente (fecha_fin IS NULL).
        $presentacion = Presentacion::whereNull('fecha_fin')->find($request->id_presentacion);
        if (! $presentacion) {
            throw ValidationException::withMessages([
                'id_presentacion' => 'La presentación seleccionada no está vigente: el lote ya fue cerrado.',
            ]);
        }

        // b) Cliente HABILITADO: el titular no puede estar suspendido para operar.
        if (! empty($cliente->suspendido)) {
            throw ValidationException::withMessages([
                'id_cliente' => 'El cliente se encuentra suspendido para operar créditos',
            ]);
        }

        // c) LÍMITE DE CUOTAS: el tope es del legajo del cliente (clientes.max_cuotas).
        //    La autorización (cliente_autorizados) sólo aporta `limite_aut`, no un tope de cuotas.
        $maxCuotas = $cliente->max_cuotas;
        if ($maxCuotas !== null && (int) $request->cantidad_cuotas > (int) $maxCuotas) {
            throw ValidationException::withMessages([
                'cantidad_cuotas' => "La cantidad de cuotas ({$request->cantidad_cuotas}) supera el máximo permitido para el cliente ({$maxCuotas}).",
            ]);
        }

        // d) LÍMITE CREDITICIO DISPONIBLE: deuda exigible (saldo_actual de cuotas impagas
        //    en créditos abiertos) + importe solicitado no debe superar el límite efectivo
        //    (cliente.limite_efectivo, que contempla `limite_aut` de la autorización).
        $deudaAcumulada = (float) Cuota::whereHas('credito', function ($q) use ($cliente) {
            $q->where('id_cliente', $cliente->id)
              ->whereNull('fecha_cancelacion');
        })->where('saldo_actual', '>', 0)->sum('saldo_actual');

        $limiteEfectivo = (float) ($cliente->limite_efectivo ?? $cliente->limite ?? 0);
        $disponible = max(0, $limiteEfectivo - $deudaAcumulada);

        if (($deudaAcumulada + (float) $request->importe_original) > $limiteEfectivo) {
            throw ValidationException::withMessages([
                'importe_original' => 'El importe solicitado supera el límite crediticio del cliente. '
                    . 'Límite: $' . number_format($limiteEfectivo, 2)
                    . ' | Deuda exigible: $' . number_format($deudaAcumulada, 2)
                    . ' | Disponible actual: $' . number_format($disponible, 2) . '.',
            ]);
        }

        // Ejecutamos una transacción de base de datos para asegurar consistencia absoluta
        DB::transaction(function () use ($request, $presentacion) {
            
            // 1. Obtener y actualizar el correlativo único de la sucursal
            $indice = SucursalIndice::firstOrCreate(
                ['id_sucursal' => $request->id_sucursal, 'tipo_credito' => $request->tipo_credito],
                ['ultimo_indice' => 0]
            );
            
            $indice->increment('ultimo_indice');
            $nuevoNumeroIndice = $indice->ultimo_indice;

            // 2. Correlativo numérico puro para el campo 'credito' (ej: "125")
            $codigoCredito = (string) $nuevoNumeroIndice;

            // 3. La presentación (lote) ya fue resuelta y validada como ABIERTA antes de la transacción.
            // Cálculo de cuota de referencia con interés escalonado por cuota (mismo criterio que el POS).
            // Tasa real: `presentaciones.interes_credito` (la columna `tasa_cuotas` NO existe).
            $calc = VentaController::calcularFinanciacion(
                (float) $request->importe_original,
                VentaController::normalizarFactorInteres($presentacion),
                (int) $request->cantidad_cuotas,
                (bool) ($presentacion->primera_cuota_sin_interes ?? true)
            );
            $valorCuotaRef = $calc['valor_cuota'];

            // 4. Crear la cabecera del Crédito
            $credito = Credito::create([
                'id_cliente' => $request->id_cliente,
                'id_sucursal' => $request->id_sucursal,
                'id_autorizado' => $request->input('id_autorizado') ?: null, // NULL = crédito del propio titular
                'id_presentacion' => $request->id_presentacion,
                'credito' => $codigoCredito,
                'tipo_credito' => $request->tipo_credito,
                'importe_original' => $request->importe_original,
                'cantidad_cuotas' => $request->cantidad_cuotas,
                'valor_cuota_ref' => $valorCuotaRef,
                'fecha_credito' => now()->toDateString(),
            ]);

            // 5. Crear la cascada de Cuotas asociadas
            for ($i = 1; $i <= $request->cantidad_cuotas; $i++) {
                Cuota::create([
                    'id_credito' => $credito->id,
                    'num_cuota' => $i,
                    'valor_base' => $valorCuotaRef,
                    'intereses' => 0,
                    'entrego' => 0,
                    'saldo_actual' => $valorCuotaRef,
                    // Las fechas de vencimiento y envío bancario las definiremos con tus respuestas
                    'fecha_ultimo_envio' => null,
                    'estado_bancario' => null,
                ]);
            }
        });

        return redirect()->route('creditos.index')
                        ->with('success', 'Crédito y cuotas generados exitosamente.');
    }

    public function imprimirPagare(Credito $credito)
    {
        $credito->load(['cliente.localidad', 'sucursal.provincia', 'autorizado.cliente', 'cuotas', 'presentacion']);
        $cliente = $credito->cliente;

        $totalMonto = ($credito->cantidad_cuotas && (float)$credito->valor_cuota_ref > 0)
            ? round((float)$credito->cantidad_cuotas * (float)$credito->valor_cuota_ref, 2)
            : (float)$credito->importe_original;

        // Concepto
        if ($credito->tipo_credito === 'R') {
            $concepto = "En concepto de REFINANCIACION de creditos.";
        } else {
            $concepto = "En concepto de COMPRAS de mercaderias VARIAS.";
        }

        // Convertidor de números a letras para el Pagaré
        $montoEnLetras = self::convertirMontoEnLetras($totalMonto);

        // Estado crediticio / Deuda acumulada y mora del cliente
        $estadoCrediticio = $cliente ? $cliente->estadoCrediticio() : null;

        // Próximos vencimientos del cliente (4 meses a partir del lote activo de la sucursal)
        $sucursalId = $credito->id_sucursal ?? 1;
        $presentacionActiva = Presentacion::activaParaSucursal($sucursalId) ?? $credito->presentacion;
        $loteBase = $presentacionActiva ? (int)$presentacionActiva->numero : 0;

        $proximosMeses = [];
        if ($cliente && $loteBase > 0) {
            $cuotasPendientes = Cuota::whereHas('credito', function ($q) use ($cliente) {
                $q->where('id_cliente', $cliente->id)
                  ->whereNull('fecha_cancelacion');
            })
            ->where('saldo_actual', '>', 0)
            ->whereNull('fecha_cobro')
            ->with(['credito.presentacion'])
            ->get();

            for ($i = 1; $i <= 4; $i++) {
                $targetLote = $loteBase + $i;
                $cuotasDelMes = $cuotasPendientes->filter(function ($c) use ($targetLote) {
                    return (int)$c->lote_calculado === $targetLote;
                });

                $proximosMeses[$i] = [
                    'numero_mes' => $i,
                    'lote'       => $targetLote,
                    'cuotas'     => $cuotasDelMes,
                    'total'      => (float)$cuotasDelMes->sum('saldo_actual'),
                ];
            }
        }

        return view('creditos.pagare', compact(
            'credito',
            'cliente',
            'totalMonto',
            'concepto',
            'montoEnLetras',
            'estadoCrediticio',
            'proximosMeses'
        ));
    }

    /**
     * Convierte un monto numérico a su expresión en letras (ej: 48190 -> CUARENTA Y OCHO MIL CIENTO NOVENTA).
     */
    public static function convertirMontoEnLetras(float $monto): string
    {
        if (class_exists('\NumberFormatter')) {
            try {
                $formatter = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
                return mb_strtoupper($formatter->format((int)$monto));
            } catch (\Throwable $e) {
                // Continuar a fallback manual
            }
        }

        return mb_strtoupper(self::numeroALetrasBasico($monto));
    }

    private static function numeroALetrasBasico(float $numero): string
    {
        $unidades = ['', 'un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez',
            'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve', 'veinte',
            'veintiuno', 'veintidós', 'veintitrés', 'veinticuatro', 'veinticinco', 'veintiséis', 'veintisiete', 'veintiocho', 'veintinueve'];
        $decenas = ['', 'diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

        $entero = (int) $numero;
        if ($entero === 0) return 'cero';
        if ($entero === 100) return 'cien';

        $texto = '';
        if ($entero >= 1000000) {
            $millones = (int) ($entero / 1000000);
            $texto .= ($millones === 1 ? 'un millón ' : self::numeroALetrasBasico($millones) . ' millones ');
            $entero %= 1000000;
        }

        if ($entero >= 1000) {
            $miles = (int) ($entero / 1000);
            $texto .= ($miles === 1 ? 'mil ' : self::numeroALetrasBasico($miles) . ' mil ');
            $entero %= 1000;
        }

        if ($entero >= 100) {
            if ($entero === 100) {
                $texto .= 'cien ';
                $entero = 0;
            } else {
                $c = (int) ($entero / 100);
                $texto .= $centenas[$c] . ' ';
                $entero %= 100;
            }
        }

        if ($entero > 0) {
            if ($entero < 30) {
                $texto .= $unidades[$entero] . ' ';
            } else {
                $d = (int) ($entero / 10);
                $u = $entero % 10;
                $texto .= $decenas[$d] . ($u > 0 ? ' y ' . $unidades[$u] : '') . ' ';
            }
        }

        return trim($texto);
    }

}