<?php

namespace App\Http\Controllers;

use App\Models\CajaSesion;
use App\Models\CajaMovimiento;
use App\Models\Sucursal;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    /**
     * Listado e Historial de Sesiones de Caja y Cierres Z.
     */
    public function index(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $sucursalActiva = config('app.sucursal_activa') ?? Sucursal::first();
        $sucursalFiltro = $request->get('sucursal', (string)($sucursalActiva?->id ?? 1));
        $sucursalId = $sucursalFiltro === 'all' ? (int)($sucursalActiva?->id ?? 1) : (int)$sucursalFiltro;

        $query = CajaSesion::with(['sucursal', 'usuarioApertura', 'usuarioCierre']);

        // Filtro por sucursal
        if ($sucursalFiltro !== 'all') {
            $query->where('id_sucursal', $sucursalId);
        }

        // Filtro por estado
        if ($request->filled('estado') && $request->estado !== 'all') {
            $query->where('estado', $request->estado);
        }

        // Filtro por fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }

        // Click-to-Sort (conforme a .agents/rules/table_sorting.md)
        $sortableColumns = [
            'id'                     => 'cajas_sesiones.id',
            'sucursal'               => 'sucursales.nombre',
            'fecha_apertura'         => 'cajas_sesiones.fecha_apertura',
            'monto_inicial_efectivo' => 'cajas_sesiones.monto_inicial_efectivo',
            'monto_final_efectivo'   => 'cajas_sesiones.monto_final_efectivo_real',
            'diferencia_efectivo'    => 'cajas_sesiones.diferencia_efectivo',
            'total_general'          => 'cajas_sesiones.total_general',
            'estado'                 => 'cajas_sesiones.estado',
        ];

        $sort = $request->get('sort', 'fecha_apertura');
        $direction = strtolower($request->get('direction', 'desc'));

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'fecha_apertura';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        if ($sort === 'sucursal') {
            $query->leftJoin('sucursales', 'cajas_sesiones.id_sucursal', '=', 'sucursales.id')
                ->select('cajas_sesiones.*')
                ->orderBy('sucursales.nombre', $direction);
        } else {
            $query->orderBy($sortableColumns[$sort], $direction);
        }

        if ($sort !== 'id') {
            $query->orderBy('cajas_sesiones.id', 'desc');
        }

        // Totales agregados de la consulta filtrada (por medio de pago, ventas e ingresos/egresos)
        $totalesFiltro = (clone $query)->selectRaw('
            COALESCE(SUM(total_general), 0) as total_facturado,
            COALESCE(SUM(total_efectivo), 0) as total_efectivo,
            COALESCE(SUM(total_tarjeta_debito), 0) as total_debito,
            COALESCE(SUM(total_tarjeta_credito), 0) as total_credito,
            COALESCE(SUM(total_credito_personal), 0) as total_credito_personal,
            COALESCE(SUM(total_transferencia), 0) as total_transferencia,
            COALESCE(SUM(total_otros), 0) as total_otros,
            COALESCE(SUM(total_ingresos_efectivo), 0) as total_ingresos,
            COALESCE(SUM(total_retiros_efectivo), 0) as total_retiros,
            COALESCE(SUM(cant_ventas_total), 0) as cant_ventas
        ')->first();

        // Exportación a Excel / CSV con todos los filtros aplicados
        if ($request->get('export') === 'excel' || $request->get('export') === 'csv') {
            $exportQuery = clone $query;
            $filename = 'cajas_turnos_' . date('Ymd_His') . '.csv';

            return response()->streamDownload(function () use ($exportQuery) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8

                fputcsv($handle, [
                    'Turno ID',
                    'Sucursal',
                    'Fecha Apertura',
                    'Hora Apertura',
                    'Fecha Cierre',
                    'Hora Cierre',
                    'Fondo Apertura',
                    'Efectivo Cierre Real',
                    'Efectivo Sistema',
                    'Diferencia Efectivo',
                    'Total Facturado',
                    'Total Efectivo',
                    'Total Tarjeta Debito',
                    'Total Tarjeta Credito',
                    'Total Credito Personal',
                    'Total Transferencia',
                    'Total Otros',
                    'Ingresos Manuales',
                    'Retiros Manuales',
                    'Cantidad Ventas',
                    'Estado',
                ], ';');

                $exportQuery->chunk(500, function ($chunk) use ($handle) {
                    foreach ($chunk as $c) {
                        fputcsv($handle, [
                            $c->id,
                            $c->sucursal?->nombre ?? '-',
                            $c->fecha_apertura ? $c->fecha_apertura->format('d/m/Y') : '-',
                            $c->fecha_apertura ? $c->fecha_apertura->format('H:i') : '-',
                            $c->fecha_cierre ? $c->fecha_cierre->format('d/m/Y') : 'En curso',
                            $c->fecha_cierre ? $c->fecha_cierre->format('H:i') : '-',
                            number_format((float)$c->monto_inicial_efectivo, 2, ',', ''),
                            number_format((float)$c->monto_final_efectivo_real, 2, ',', ''),
                            number_format((float)$c->monto_final_efectivo_sistema, 2, ',', ''),
                            number_format((float)$c->diferencia_efectivo, 2, ',', ''),
                            number_format((float)$c->total_general, 2, ',', ''),
                            number_format((float)$c->total_efectivo, 2, ',', ''),
                            number_format((float)$c->total_tarjeta_debito, 2, ',', ''),
                            number_format((float)$c->total_tarjeta_credito, 2, ',', ''),
                            number_format((float)$c->total_credito_personal, 2, ',', ''),
                            number_format((float)$c->total_transferencia, 2, ',', ''),
                            number_format((float)$c->total_otros, 2, ',', ''),
                            number_format((float)$c->total_ingresos_efectivo, 2, ',', ''),
                            number_format((float)$c->total_retiros_efectivo, 2, ',', ''),
                            (int)$c->cant_ventas_total,
                            ucfirst($c->estado),
                        ], ';');
                    }
                });

                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $cajas = $query->paginate(20)->withQueryString();

        // Sesión de caja abierta actual de la sucursal activa
        $cajaActiva = CajaSesion::activaParaSucursal($sucursalId);
        $totalesActiva = $cajaActiva ? $cajaActiva->calcularTotales() : null;
        $totalesCajaActiva = $totalesActiva;
        $requiereCierreAnterior = $cajaActiva && !$cajaActiva->esDeHoy();

        return view('cajas.index', compact(
            'cajas',
            'sucursales',
            'sucursalActiva',
            'sucursalId',
            'sucursalFiltro',
            'cajaActiva',
            'totalesActiva',
            'totalesCajaActiva',
            'requiereCierreAnterior',
            'totalesFiltro',
            'sort',
            'direction'
        ));
    }

    /**
     * API JSON: Consulta de estado actual de la caja para la sucursal activa (usado por el POS).
     */
    public function estadoActual(Request $request)
    {
        $sucursalId = (int) ($request->get('sucursal_id') ?? config('app.sucursal_activa.id') ?? auth()->user()->id_sucursal ?? 1);
        $caja = CajaSesion::activaParaSucursal($sucursalId);

        if (!$caja) {
            return response()->json([
                'abierta'       => false,
                'caja'          => null,
                'es_de_hoy'     => false,
                'requiere_cierre_anterior' => false,
            ]);
        }

        $esDeHoy = $caja->esDeHoy();
        $totales = $caja->calcularTotales();

        return response()->json([
            'abierta'       => true,
            'caja'          => [
                'id'                     => $caja->id,
                'sucursal'               => $caja->sucursal?->nombre,
                'id_sucursal'            => $caja->id_sucursal,
                'fecha_apertura'         => $caja->fecha_apertura?->format('d/m/Y H:i'),
                'fecha_apertura_raw'     => $caja->fecha_apertura?->toIso8601String(),
                'monto_inicial_efectivo' => (float) $caja->monto_inicial_efectivo,
                'usuario_apertura'       => $caja->usuarioApertura?->name,
                'estado'                 => $caja->estado,
            ],
            'es_de_hoy'                 => $esDeHoy,
            'requiere_cierre_anterior'  => !$esDeHoy,
            'totales'                   => $totales,
        ]);
    }

    /**
     * Apertura de una nueva sesión de caja.
     */
    public function abrir(Request $request)
    {
        $validated = $request->validate([
            'id_sucursal'            => 'nullable|exists:sucursales,id',
            'monto_inicial_efectivo' => 'required|numeric|min:0',
            'observaciones'          => 'nullable|string|max:500',
        ]);

        $sucursalId = (int) ($validated['id_sucursal'] ?? config('app.sucursal_activa.id') ?? auth()->user()->id_sucursal ?? 1);
        $sucursal = Sucursal::findOrFail($sucursalId);

        // Verificar si ya existe una caja abierta para esta sucursal
        $existente = CajaSesion::activaParaSucursal($sucursalId);
        if ($existente) {
            if (!$existente->esDeHoy()) {
                $msg = "La sucursal {$sucursal->nombre} posee una caja de la jornada anterior (#{$existente->id}) sin cerrar. Debe realizar el Cierre Z de los controladores fiscales y asentar el arqueo antes de abrir una nueva caja.";
                return $request->wantsJson() 
                    ? response()->json(['error' => $msg, 'requiere_cierre_anterior' => true, 'redirect_url' => route('cajas.cerrar', $existente->id)], 422) 
                    : redirect()->route('cajas.cerrar', $existente->id)->with('error', $msg);
            }

            $msg = "La sucursal {$sucursal->nombre} ya posee una caja abierta (Sesión #{$existente->id}) desde el {$existente->fecha_apertura?->format('d/m/Y H:i')}.";
            return $request->wantsJson() ? response()->json(['error' => $msg], 422) : back()->with('error', $msg);
        }

        $caja = CajaSesion::create([
            'id_sucursal'            => $sucursalId,
            'id_usuario_apertura'    => auth()->id() ?? 1,
            'fecha_apertura'         => now(),
            'monto_inicial_efectivo' => (float) $validated['monto_inicial_efectivo'],
            'estado'                 => 'abierta',
            'observaciones_apertura' => $validated['observaciones'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Caja abierta exitosamente con un fondo inicial de $ " . number_format($caja->monto_inicial_efectivo, 2, ',', '.'),
                'caja'    => $caja,
            ]);
        }

        return redirect()->route('cajas.index', ['sucursal' => $sucursalId])
            ->with('success', "Caja #{$caja->id} abierta exitosamente para {$sucursal->nombre}.");
    }

    /**
     * Cierre de caja de jornada anterior y apertura inmediata de la jornada de hoy (Cierre Z automático / transición).
     */
    public function cerrarAnteriorYAbrir(Request $request)
    {
        if ($request->has('efectivo_real_contado') && !$request->has('monto_final_efectivo_real')) {
            $request->merge(['monto_final_efectivo_real' => $request->get('efectivo_real_contado')]);
        }

        $validated = $request->validate([
            'id_caja_anterior'         => 'required|exists:cajas_sesiones,id',
            'monto_final_efectivo_real'=> 'required|numeric|min:0',
            'observaciones_cierre'     => 'nullable|string|max:500',
            'monto_inicial_efectivo'   => 'required|numeric|min:0',
            'observaciones_apertura'   => 'nullable|string|max:500',
        ]);

        $cajaAnterior = CajaSesion::findOrFail($validated['id_caja_anterior']);
        if ($cajaAnterior->estado === 'cerrada') {
            return back()->with('error', 'La caja anterior ya se encuentra cerrada.');
        }

        // Validar que la caja anterior no tenga controladores Hasar con tickets pendientes de Cierre Z
        $resumenFiscal = $cajaAnterior->calcularResumenFiscal();
        foreach ($resumenFiscal['hasar_controladores'] ?? [] as $ctrl) {
            if (!empty($ctrl['cant_tickets']) && empty($ctrl['cierre_z_emitido'])) {
                $nombreCtrl = ($ctrl['modelo'] ?? 'Hasar') . ' (' . ($ctrl['hasar_ip'] ?? '') . ')';
                $msg = "No se puede cerrar la caja: El controlador fiscal {$nombreCtrl} tiene {$ctrl['cant_tickets']} tickets emitidos sin Cierre Z. Debe realizar el Cierre Z completo desde la pantalla oficial de Cierre Z antes de abrir hoy.";
                return $request->wantsJson()
                    ? response()->json(['error' => $msg, 'redirect_url' => route('cajas.cerrar', $cajaAnterior->id)], 422)
                    : redirect()->route('cajas.cerrar', $cajaAnterior->id)->with('error', $msg);
            }
        }

        $user = auth()->user();
        $sucursalId = $cajaAnterior->id_sucursal;

        $resultado = DB::transaction(function () use ($cajaAnterior, $validated, $user, $sucursalId, $resumenFiscal) {
            // 1. Cerrar caja anterior y computar arqueo
            $cajaAnterior->cerrar(
                (float) $validated['monto_final_efectivo_real'],
                $validated['observaciones_cierre'] ?? 'Cierre automático al inicio de la jornada siguiente',
                $user?->id,
                $resumenFiscal
            );

            // 2. Abrir la nueva caja de hoy
            $nuevaCaja = CajaSesion::create([
                'id_sucursal'            => $sucursalId,
                'id_usuario_apertura'    => $user?->id ?? 1,
                'fecha_apertura'         => now(),
                'monto_inicial_efectivo' => (float) $validated['monto_inicial_efectivo'],
                'estado'                 => 'abierta',
                'observaciones_apertura' => $validated['observaciones_apertura'] ?? null,
            ]);

            return ['caja_anterior' => $cajaAnterior, 'nueva_caja' => $nuevaCaja];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Caja anterior #{$cajaAnterior->id} cerrada exitosamente y nueva caja #{$resultado['nueva_caja']->id} abierta.",
                'caja_anterior_id' => $cajaAnterior->id,
                'nueva_caja'       => $resultado['nueva_caja'],
            ]);
        }

        return redirect()->route('cajas.comprobante', $cajaAnterior->id)
            ->with('success', "Cierre Z de la caja anterior #{$cajaAnterior->id} registrado y nueva caja abierta para hoy.");
    }

    /**
     * Formulario interactivo de Cierre Z y Arqueo de Caja.
     */
    public function cerrarForm(CajaSesion $caja)
    {
        if ($caja->estado === 'cerrada') {
            return redirect()->route('cajas.comprobante', $caja->id);
        }

        $caja->load(['sucursal', 'usuarioApertura', 'movimientos.usuario']);
        $totales = $caja->calcularTotales();
        $resumenFiscal = $caja->calcularResumenFiscal();

        return view('cajas.cerrar', compact('caja', 'totales', 'resumenFiscal'));
    }

    /**
     * Procesa y confirma el Cierre Z de una caja.
     */
    public function cerrar(Request $request, CajaSesion $caja)
    {
        if ($caja->estado === 'cerrada') {
            $msg = 'Esta caja ya se encuentra cerrada.';
            return $request->wantsJson() ? response()->json(['error' => $msg], 422) : redirect()->route('cajas.comprobante', $caja->id);
        }

        $validated = $request->validate([
            'monto_final_efectivo_real' => 'required|numeric|min:0',
            'observaciones'             => 'nullable|string|max:500',
            'resumen_fiscal'            => 'nullable',
            'monto_inicial_hoy'         => 'nullable|numeric|min:0',
        ]);

        $resumenFiscalInput = $request->input('resumen_fiscal');
        if (is_string($resumenFiscalInput)) {
            $resumenFiscalInput = json_decode($resumenFiscalInput, true);
        }
        $resumenFiscal = is_array($resumenFiscalInput) ? $resumenFiscalInput : $caja->calcularResumenFiscal();

        // 1. Si en la base de datos ya se registraron cierres Z previos en $caja->resumen_fiscal, conservarlos para no sobreescribir con un payload stale
        $dbFiscal = $caja->resumen_fiscal ?? [];
        if (!empty($dbFiscal['hasar_controladores']) && is_array($dbFiscal['hasar_controladores'])) {
            foreach ($dbFiscal['hasar_controladores'] as $dbH) {
                if (!empty($dbH['cierre_z_emitido'])) {
                    foreach ($resumenFiscal['hasar_controladores'] as $rfIdx => $rfH) {
                        if (($rfH['key'] ?? '') === ($dbH['key'] ?? '') || ($rfH['hasar_ip'] ?? '') === ($dbH['hasar_ip'] ?? '')) {
                            $resumenFiscal['hasar_controladores'][$rfIdx]['cierre_z_emitido'] = true;
                            $resumenFiscal['hasar_controladores'][$rfIdx]['cierre_z_numero'] = $dbH['cierre_z_numero'] ?? ($rfH['cierre_z_numero'] ?? null);
                            $resumenFiscal['hasar_controladores'][$rfIdx]['cierre_z_fecha'] = $dbH['cierre_z_fecha'] ?? ($rfH['cierre_z_fecha'] ?? null);
                            if (!empty($dbH['cierre_z_respuesta'])) {
                                $resumenFiscal['hasar_controladores'][$rfIdx]['cierre_z_respuesta'] = $dbH['cierre_z_respuesta'];
                            }
                        }
                    }
                }
            }
        }

        // 2. Si todavía existen controladores con tickets pendientes de Cierre Z, intentar emitirlo automáticamente por backend con el puente local
        if (!empty($resumenFiscal['hasar_controladores']) && is_array($resumenFiscal['hasar_controladores'])) {
            foreach ($resumenFiscal['hasar_controladores'] as $idx => $ctrl) {
                if (!empty($ctrl['cant_tickets']) && empty($ctrl['cierre_z_emitido'])) {
                    $puertoLocal = (int) ($ctrl['puerto_local'] ?? 8585);
                    $hasarIp = $ctrl['hasar_ip'] ?? '127.0.0.1';
                    $hasarPuerto = (int) ($ctrl['hasar_puerto'] ?? 2000);
                    $modelo = $ctrl['modelo'] ?? 'SMH/PT-250F';

                    $payload = [
                        'accion'       => 'cierre_z',
                        'hasar_ip'     => $hasarIp,
                        'hasar_puerto' => $hasarPuerto,
                        'modelo'       => $modelo,
                        'caja_id'      => $caja->id,
                        'sucursal'     => $caja->sucursal?->nombre ?? '',
                    ];

                    $ch = curl_init("http://127.0.0.1:{$puertoLocal}/imprimir");
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                    $bridgeRes = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($httpCode === 200 && $bridgeRes) {
                        $dataBridge = json_decode($bridgeRes, true);
                        $numeroZ = $dataBridge['numero_comprobante'] 
                            ?? $dataBridge['numero_z'] 
                            ?? $dataBridge['numero'] 
                            ?? $dataBridge['nro_z'] 
                            ?? ('Z-' . date('His'));
                        $resumenFiscal['hasar_controladores'][$idx]['cierre_z_emitido'] = true;
                        $resumenFiscal['hasar_controladores'][$idx]['cierre_z_numero'] = $numeroZ;
                        $resumenFiscal['hasar_controladores'][$idx]['cierre_z_fecha'] = now()->toDateTimeString();
                        $resumenFiscal['hasar_controladores'][$idx]['cierre_z_respuesta'] = $dataBridge;
                    }
                }
            }
        }

        // 3. Validar obligatoriedad de Cierre Z en controladores Hasar que registraron tickets
        foreach ($resumenFiscal['hasar_controladores'] ?? [] as $ctrl) {
            if (!empty($ctrl['cant_tickets']) && empty($ctrl['cierre_z_emitido'])) {
                $nombreCtrl = ($ctrl['modelo'] ?? 'Hasar 2G') . ' (' . ($ctrl['hasar_ip'] ?? 'IP') . ')';
                $cantTickets = $ctrl['cant_tickets'] ?? 0;
                $msg = "No se puede cerrar la caja: El controlador fiscal {$nombreCtrl} registró {$cantTickets} ticket(s) en este turno y aún NO ha emitido su Cierre Z. Debe presionar '⚡ Emitir Cierre Z' (o registrar el número manual de Z) en cada equipo antes de confirmar el cierre.";
                return $request->wantsJson() 
                    ? response()->json(['error' => $msg], 422) 
                    : back()->with('error', $msg)->withInput();
            }
        }

        $caja->cerrar(
            (float) $validated['monto_final_efectivo_real'],
            $validated['observaciones'] ?? null,
            auth()->id(),
            $resumenFiscal
        );

        // Si la caja cerrada era de una jornada anterior y se ingresó fondo para hoy, abrir la caja de hoy automáticamente
        if (!$caja->esDeHoy() && $request->filled('monto_inicial_hoy')) {
            $nuevaCaja = CajaSesion::create([
                'id_sucursal'            => $caja->id_sucursal,
                'id_usuario_apertura'    => auth()->id() ?? 1,
                'fecha_apertura'         => now(),
                'monto_inicial_efectivo' => (float) $validated['monto_inicial_hoy'],
                'estado'                 => 'abierta',
                'observaciones_apertura' => 'Apertura automática tras Cierre Z de jornada previa #' . $caja->id,
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success'       => true,
                    'message'       => "Cierre Z de la sesión anterior #{$caja->id} registrado correctamente y caja de hoy #{$nuevaCaja->id} abierta.",
                    'caja_id'       => $caja->id,
                    'nueva_caja_id' => $nuevaCaja->id,
                    'redirect_url'  => route('ventas.create'),
                ]);
            }

            return redirect()->route('ventas.create')
                ->with('success', "Cierre Z de la sesión previa #{$caja->id} asentado correctamente y Caja de hoy #{$nuevaCaja->id} abierta ($ " . number_format($nuevaCaja->monto_inicial_efectivo, 2, ',', '.') . "). ¡Listo para facturar!");
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => "Cierre Z ejecutado correctamente.",
                'caja_id'      => $caja->id,
                'redirect_url' => route('cajas.comprobante', $caja->id),
            ]);
        }

        return redirect()->route('cajas.comprobante', ['caja' => $caja->id, 'formato' => 'ticket'])
            ->with('success', "Cierre Z de la sesión #{$caja->id} registrado correctamente.");
    }

    /**
     * Registra el resultado de la emisión de un Cierre Z en un controlador Hasar específico.
     * Si no se envía numero_z, el servidor intenta disparar el Cierre Z directamente en el puente Hasar.
     */
    public function registrarCierreZHasar(Request $request, CajaSesion $caja)
    {
        $validated = $request->validate([
            'hasar_ip'     => 'required|string',
            'hasar_puerto' => 'nullable|integer',
            'puerto_local' => 'nullable|integer',
            'modelo'       => 'nullable|string',
            'numero_z'     => 'nullable|string',
            'respuesta'    => 'nullable|array',
        ]);

        $puertoLocal = (int) ($validated['puerto_local'] ?? 8585);
        $hasarIp = $validated['hasar_ip'];
        $hasarPuerto = (int) ($validated['hasar_puerto'] ?? 2000);
        $modelo = $validated['modelo'] ?? 'SMH/PT-250F';
        $key = "{$hasarIp}:{$hasarPuerto}";

        // Si no se proveyó número de Z desde el frontend, intentar emitirlo llamando directamente al puente Hasar local desde PHP
        if (empty($validated['numero_z'])) {
            $payload = [
                'accion'       => 'cierre_z',
                'hasar_ip'     => $hasarIp,
                'hasar_puerto' => $hasarPuerto,
                'modelo'       => $modelo,
                'caja_id'      => $caja->id,
                'sucursal'     => $caja->sucursal?->nombre ?? '',
            ];

            $ch = curl_init("http://127.0.0.1:{$puertoLocal}/imprimir");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=utf-8']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            $bridgeRes = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && $bridgeRes) {
                $dataBridge = json_decode($bridgeRes, true);
                $numeroZ = $dataBridge['numero_comprobante'] 
                    ?? $dataBridge['numero_z'] 
                    ?? $dataBridge['numero'] 
                    ?? $dataBridge['nro_z'] 
                    ?? ('Z-' . date('His'));
                $validated['numero_z'] = $numeroZ;
                $validated['respuesta'] = $dataBridge;
            } else {
                return response()->json([
                    'success' => false,
                    'error'   => 'bridge_offline',
                    'message' => "No se pudo contactar el simulador/puente Hasar en http://127.0.0.1:{$puertoLocal}/imprimir. " . ($curlError ? "Error de conexión: {$curlError}" : "Código HTTP: {$httpCode}"),
                ], 503);
            }
        }

        $resumen = $caja->calcularResumenFiscal();

        $actualizado = false;
        if (!empty($resumen['hasar_controladores']) && is_array($resumen['hasar_controladores'])) {
            foreach ($resumen['hasar_controladores'] as $idx => $ctrl) {
                if (($ctrl['key'] ?? '') === $key || ($ctrl['hasar_ip'] ?? '') === $hasarIp) {
                    $resumen['hasar_controladores'][$idx]['cierre_z_emitido'] = true;
                    $resumen['hasar_controladores'][$idx]['cierre_z_numero'] = $validated['numero_z'] ?? ($ctrl['cierre_z_numero'] ?: 'Z-' . date('His'));
                    $resumen['hasar_controladores'][$idx]['cierre_z_fecha'] = now()->toDateTimeString();
                    if (!empty($validated['respuesta'])) {
                        $resumen['hasar_controladores'][$idx]['cierre_z_respuesta'] = $validated['respuesta'];
                    }
                    $actualizado = true;
                }
            }
        }

        $caja->update(['resumen_fiscal' => $resumen]);

        return response()->json([
            'success'        => true,
            'message'        => "Cierre Z registrado exitosamente para controlador Hasar {$key}.",
            'numero_z'       => $validated['numero_z'],
            'respuesta'      => $validated['respuesta'] ?? null,
            'resumen_fiscal' => $resumen,
        ]);
    }

    /**
     * Vista de detalle de una sesión de caja específica: totales por medio de pago,
     * desglose de efectivo (apertura, ingresos, egresos, cierre) y listado de movimientos y ventas.
     */
    public function show(CajaSesion $caja)
    {
        $caja->load(['sucursal.provincia', 'usuarioApertura', 'usuarioCierre', 'movimientos.usuario']);
        
        $ventas = $caja->ventas()
            ->with(['cliente', 'usuario', 'medioPago'])
            ->orderBy('id', 'desc')
            ->paginate(50);

        $totales = $caja->calcularTotales();
        $resumenFiscal = $caja->resumen_fiscal ?: $caja->calcularResumenFiscal();
        $movimientos = $caja->movimientos()->orderBy('id', 'desc')->get();

        return view('cajas.show', compact('caja', 'totales', 'resumenFiscal', 'ventas', 'movimientos'));
    }

    /**
     * Vista de impresión oficial de Cierre Z (Ticket térmico 80mm y A4).
     */
    public function comprobante(Request $request, CajaSesion $caja)
    {
        $caja->load(['sucursal.provincia', 'usuarioApertura', 'usuarioCierre', 'ventas', 'movimientos.usuario']);
        $totales = $caja->calcularTotales();
        $resumenFiscal = $caja->resumen_fiscal ?: $caja->calcularResumenFiscal();

        $formato = $request->get('formato', 'ticket');

        // Primer y último comprobante emitido en la sesión
        $primerComprobante = $caja->ventas()->orderBy('id', 'asc')->value('comprobante');
        $ultimoComprobante = $caja->ventas()->orderBy('id', 'desc')->value('comprobante');

        return view('cajas.comprobante', compact('caja', 'totales', 'resumenFiscal', 'primerComprobante', 'ultimoComprobante', 'formato'));
    }

    /**
     * Registra un ingreso o retiro manual de efectivo en la caja activa con su motivo justificado.
     */
    public function storeMovimiento(Request $request, CajaSesion $caja)
    {
        if ($caja->estado !== 'abierta') {
            $msg = 'No se pueden registrar movimientos en una caja que no esté abierta.';
            return $request->wantsJson() ? response()->json(['error' => $msg], 422) : back()->with('error', $msg);
        }

        $validated = $request->validate([
            'tipo'        => 'required|in:ingreso,retiro',
            'monto'       => 'required|numeric|min:0.01',
            'motivo'      => 'required|string|max:255',
            'comprobante' => 'nullable|string|max:100',
        ]);

        $movimiento = $caja->movimientos()->create([
            'id_usuario'  => auth()->id() ?? 1,
            'tipo'        => $validated['tipo'],
            'monto'       => (float) $validated['monto'],
            'motivo'      => trim($validated['motivo']),
            'comprobante' => !empty($validated['comprobante']) ? trim($validated['comprobante']) : null,
        ]);

        $totales = $caja->calcularTotales();

        if ($request->wantsJson()) {
            return response()->json([
                'success'           => true,
                'message'           => ($movimiento->tipo === 'ingreso' ? 'Ingreso' : 'Retiro') . " de efectivo registrado correctamente ($ " . number_format($movimiento->monto, 2, ',', '.') . ").",
                'movimiento'        => $movimiento,
                'efectivo_esperado' => $totales['efectivo_esperado'],
                'totales'           => $totales,
            ]);
        }

        $tipoStr = $movimiento->tipo === 'ingreso' ? 'Ingreso' : 'Retiro';
        return back()->with('success', "{$tipoStr} de efectivo por $ " . number_format($movimiento->monto, 2, ',', '.') . " asentado correctamente ({$movimiento->motivo}).");
    }

    /**
     * Retorna la lista de movimientos de efectivo de una caja en formato JSON.
     */
    public function movimientosJson(CajaSesion $caja)
    {
        $movimientos = $caja->movimientos()
            ->with('usuario')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'movimientos' => $movimientos,
            'totales'     => $caja->calcularTotales(),
        ]);
    }

    /**
     * Reporte Histórico de Facturación por Medios de Pago.
     * Consulta directamente sobre la tabla ventas donde residen los 137.000+ registros históricos.
     */
    public function historicoMedios(Request $request)
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $sucursalId = $request->get('sucursal', 'all');
        $periodo = $request->get('periodo', 'mes'); // 'dia', 'mes', 'anio'
        $fechaDesde = $request->get('fecha_desde');
        $fechaHasta = $request->get('fecha_hasta');

        $query = Venta::where('estado', '!=', 'anulada');

        if ($sucursalId !== 'all' && is_numeric($sucursalId)) {
            $query->where('id_sucursal', (int) $sucursalId);
        }

        if ($fechaDesde) {
            $query->whereDate('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate('fecha', '<=', $fechaHasta);
        }

        // Resumen total por medio de pago consolidado
        $porMedio = (clone $query)
            ->leftJoin('medios_pago', 'ventas.id_medio_pago', '=', 'medios_pago.id')
            ->selectRaw('
                COALESCE(medios_pago.codigo, ventas.medio_pago) as medio_pago,
                COALESCE(medios_pago.nombre, ventas.medio_pago) as medio_pago_nombre,
                medios_pago.icono,
                medios_pago.color,
                count(*) as cant_ventas,
                sum(ventas.total) as total_monto
            ')
            ->groupBy(DB::raw('COALESCE(medios_pago.codigo, ventas.medio_pago)'), DB::raw('COALESCE(medios_pago.nombre, ventas.medio_pago)'), 'medios_pago.icono', 'medios_pago.color')
            ->orderByDesc('total_monto')
            ->get();

        $totalGeneral = (float) $porMedio->sum('total_monto');
        $cantidadGeneral = (int) $porMedio->sum('cant_ventas');

        // Desglose cruzado por sucursal y medio de pago
        $porSucursalMedio = (clone $query)
            ->join('sucursales', 'ventas.id_sucursal', '=', 'sucursales.id')
            ->leftJoin('medios_pago', 'ventas.id_medio_pago', '=', 'medios_pago.id')
            ->selectRaw('ventas.id_sucursal, sucursales.nombre as sucursal_nombre, COALESCE(medios_pago.nombre, ventas.medio_pago) as medio_pago, count(*) as cant_ventas, sum(ventas.total) as total_monto')
            ->groupBy('ventas.id_sucursal', 'sucursales.nombre', DB::raw('COALESCE(medios_pago.nombre, ventas.medio_pago)'))
            ->orderBy('sucursales.nombre')
            ->orderByDesc('total_monto')
            ->get();

        // Exportación CSV / Excel (con filtros aplicados)
        if ($request->get('export') === 'csv' || $request->get('export') === 'excel') {
            return response()->streamDownload(function () use ($porMedio, $totalGeneral, $cantidadGeneral) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF"); // BOM UTF-8
                fputcsv($handle, ['Medio de Pago', 'Cantidad Ventas', 'Total Facturado ($ ARS)', 'Participacion (%)'], ';');
                foreach ($porMedio as $fila) {
                    $pct = $totalGeneral > 0 ? round(($fila->total_monto / $totalGeneral) * 100, 2) : 0;
                    fputcsv($handle, [$fila->medio_pago_nombre ?? $fila->medio_pago, $fila->cant_ventas, number_format((float)$fila->total_monto, 2, ',', ''), $pct . '%'], ';');
                }
                fputcsv($handle, ['TOTAL GENERAL', $cantidadGeneral, number_format((float)$totalGeneral, 2, ',', ''), '100.0%'], ';');
                fclose($handle);
            }, 'reporte_historico_medios_' . date('Ymd_His') . '.csv', [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="reporte_historico_medios_' . date('Ymd_His') . '.csv"',
            ]);
        }

        // Evolución temporal agrupada (compatible con MySQL y SQLite de tests)
        $evolucionQuery = clone $query;
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            $sqliteFmt = match($periodo) {
                'dia' => '%Y-%m-%d',
                'anio' => '%Y',
                default => '%Y-%m',
            };
            $dateExpr = "strftime('{$sqliteFmt}', fecha)";
        } else {
            $formatoFecha = match($periodo) {
                'dia' => '%Y-%m-%d',
                'anio' => '%Y',
                default => '%Y-%m',
            };
            $dateExpr = "DATE_FORMAT(fecha, '{$formatoFecha}')";
        }

        $evolucion = $evolucionQuery
            ->selectRaw("{$dateExpr} as periodo_label, medio_pago, sum(total) as total, count(*) as cantidad")
            ->groupBy('periodo_label', 'medio_pago')
            ->orderBy('periodo_label', 'desc')
            ->limit(100)
            ->get();

        $filtroSucursal = $sucursalId;

        return view('cajas.historico_medios', compact(
            'sucursales',
            'filtroSucursal',
            'sucursalId',
            'periodo',
            'fechaDesde',
            'fechaHasta',
            'porMedio',
            'porSucursalMedio',
            'totalGeneral',
            'cantidadGeneral',
            'evolucion'
        ));
    }
}
