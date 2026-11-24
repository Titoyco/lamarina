<!DOCTYPE html>
@php
    $formato = $formato ?? request('formato', 'ticket');
@endphp
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cierre Z #{{ $caja->id }} - {{ $caja->sucursal?->nombre }}</title>
    <style>
        @page {
            @if($formato === 'ticket')
                size: 80mm auto;
                margin: 2mm;
            @else
                size: A4 portrait;
                margin: 15mm;
            @endif
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body {
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .no-print {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .btn {
            background: #0f172a;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-alt {
            background: #e2e8f0;
            color: #1e293b;
        }

        @if($formato === 'ticket')
            .receipt-container {
                width: 76mm;
                background: #fff;
                padding: 10px 6px;
                border: 1px dashed #cbd5e1;
                font-size: 11px;
                line-height: 1.25;
            }
            .center { text-align: center; }
            .right { text-align: right; }
            .left { text-align: left; }
            .bold { font-weight: bold; }
            .border-top { border-top: 1px dashed #64748b; margin-top: 5px; padding-top: 5px; }
            .border-bottom { border-bottom: 1px dashed #64748b; margin-bottom: 5px; padding-bottom: 5px; }
            .double-bottom { border-bottom: 2px double #0f172a; margin-bottom: 6px; padding-bottom: 6px; }
            .row { display: flex; justify-content: space-between; margin-bottom: 2px; }
            .h1 { font-size: 14px; font-weight: 900; }
            .h2 { font-size: 12px; font-weight: 800; }
            .tag { display: inline-block; padding: 1px 4px; background: #0f172a; color: #fff; font-size: 9px; font-weight: bold; border-radius: 3px; }
        @else
            .receipt-container {
                width: 190mm;
                background: #fff;
                padding: 25px;
                border: 1px solid #cbd5e1;
                border-radius: 12px;
                font-size: 13px;
                line-height: 1.4;
            }
            .center { text-align: center; }
            .right { text-align: right; }
            .left { text-align: left; }
            .bold { font-weight: bold; }
            .border-top { border-top: 1px solid #94a3b8; margin-top: 10px; padding-top: 10px; }
            .border-bottom { border-bottom: 1px solid #94a3b8; margin-bottom: 10px; padding-bottom: 10px; }
            .double-bottom { border-bottom: 2px solid #0f172a; margin-bottom: 12px; padding-bottom: 12px; }
            .row { display: flex; justify-content: space-between; margin-bottom: 4px; }
            .h1 { font-size: 20px; font-weight: 900; }
            .h2 { font-size: 15px; font-weight: 800; }
            .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .table th, .table td { padding: 6px 10px; border: 1px solid #e2e8f0; }
            .table th { background: #f8fafc; font-weight: bold; text-align: left; }
        @endif

        @media print {
            body { background: none; padding: 0; }
            .no-print { display: none !important; }
            .receipt-container { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn">🖨️ Imprimir</button>
        @if($formato === 'ticket')
            <a href="{{ route('cajas.comprobante', ['caja' => $caja->id, 'formato' => 'a4']) }}" class="btn btn-alt">📄 Ver en A4</a>
        @else
            <a href="{{ route('cajas.comprobante', ['caja' => $caja->id, 'formato' => 'ticket']) }}" class="btn btn-alt">🖨️ Ver en Ticket 80mm</a>
        @endif
        <a href="{{ route('cajas.index') }}" class="btn btn-alt">✕ Volver a Cajas</a>
    </div>

    <div class="receipt-container">
        <!-- Encabezado de la Sucursal -->
        <div class="center double-bottom">
            <div class="h1">{{ $caja->sucursal?->razon_social ?? 'TIENDAS LA MARINA' }}</div>
            <div class="bold">{{ $caja->sucursal?->nombre_fantasia ?? 'LA MARINA' }}</div>
            <div>CUIT: {{ $caja->sucursal?->cuit ?? '30-71699602-2' }}</div>
            <div>{{ $caja->sucursal?->direccion ?? 'Rivadavia 249' }} - {{ $caja->sucursal?->provincia?->nombre ?? 'Chubut' }}</div>
            <div>Sucursal: <strong>{{ $caja->sucursal?->nombre }}</strong></div>
        </div>

        <!-- Título del Documento -->
        <div class="center border-bottom">
            <div class="h2">CIERRE DE CAJA (CIERRE Z) #{{ $caja->id }}</div>
            <div>Fecha Cierre: <strong>{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i:s') : 'EN CURSO' }}</strong></div>
            <div>Apertura: {{ $caja->fecha_apertura ? $caja->fecha_apertura->format('d/m/Y H:i:s') : '-' }}</div>
        </div>

        <!-- Operadores -->
        <div class="border-bottom" style="font-size: 0.9em;">
            <div class="row">
                <span>Operador Apertura:</span>
                <span class="bold">{{ $caja->usuarioApertura?->name ?? 'Usuario' }}</span>
            </div>
            <div class="row">
                <span>Operador Cierre:</span>
                <span class="bold">{{ $caja->usuarioCierre?->name ?? 'Usuario' }}</span>
            </div>
            @if($caja->observaciones_apertura)
                <div style="font-size: 0.85em; color: #475569;">Obs. Apertura: {{ $caja->observaciones_apertura }}</div>
            @endif
            @if($caja->observaciones_cierre)
                <div style="font-size: 0.85em; color: #475569;">Obs. Cierre: {{ $caja->observaciones_cierre }}</div>
            @endif
        </div>

        <!-- RESUMEN POR MEDIOS DE PAGO -->
        <div class="border-bottom">
            <div class="bold center" style="margin-bottom: 5px;">VENTAS POR MEDIO DE PAGO</div>
            
            <div class="row">
                <span>Efectivo ({{ $caja->cantidad_ventas_efectivo }} op.):</span>
                <span class="bold">${{ number_format($caja->total_efectivo, 2, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Tarjeta Débito ({{ $caja->cantidad_ventas_debito }} op.):</span>
                <span class="bold">${{ number_format($caja->total_debito, 2, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Tarjeta Crédito ({{ $caja->cantidad_ventas_credito }} op.):</span>
                <span class="bold">${{ number_format($caja->total_credito, 2, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>Crédito Personal ({{ $caja->cantidad_ventas_credito_personal }} op.):</span>
                <span class="bold">${{ number_format($caja->total_credito_personal, 2, ',', '.') }}</span>
            </div>
            @if($caja->total_transferencia > 0 || $caja->total_otros > 0)
                <div class="row">
                    <span>Transferencia / Otros ({{ $caja->cantidad_ventas_transferencia + $caja->cantidad_ventas_otros }} op.):</span>
                    <span class="bold">${{ number_format($caja->total_transferencia + $caja->total_otros, 2, ',', '.') }}</span>
                </div>
            @endif

            <div class="row border-top bold" style="font-size: 1.1em; margin-top: 6px; padding-top: 4px;">
                <span>TOTAL FACTURADO (Z):</span>
                <span>${{ number_format($caja->total_ventas, 2, ',', '.') }}</span>
            </div>
            <div class="center" style="font-size: 0.9em; margin-top: 2px;">
                Total Comprobantes Emitidos: <strong>{{ $caja->cantidad_ventas }}</strong>
            </div>
        </div>

        <!-- DESGLOSE FISCAL HÍBRIDO (ARCA DIGITAL VS CONTROLADOR FISCAL HASAR) -->
        @if(!empty($resumenFiscal))
        <div class="border-bottom">
            <div class="bold center" style="margin-bottom: 5px;">DISCRIMINACIÓN FISCAL & CIERRES Z</div>
            
            {{-- ARCA Digital --}}
            <div class="row">
                <span>Factura Digital ARCA ({{ $resumenFiscal['arca']['cant_comprobantes'] ?? 0 }} cbte.):</span>
                <span class="bold">${{ number_format($resumenFiscal['arca']['total'] ?? 0, 2, ',', '.') }}</span>
            </div>
            @if(!empty($resumenFiscal['arca']['facturas_a_cant']))
                <div style="font-size: 0.85em; color: #475569; padding-left: 8px;">
                    • Facturas A: {{ $resumenFiscal['arca']['facturas_a_cant'] }} (${{ number_format($resumenFiscal['arca']['facturas_a_total'] ?? 0, 2, ',', '.') }})
                </div>
            @endif
            @if(!empty($resumenFiscal['arca']['facturas_b_cant']))
                <div style="font-size: 0.85em; color: #475569; padding-left: 8px;">
                    • Facturas B: {{ $resumenFiscal['arca']['facturas_b_cant'] }} (${{ number_format($resumenFiscal['arca']['facturas_b_total'] ?? 0, 2, ',', '.') }})
                </div>
            @endif

            {{-- Controladores Hasar --}}
            @if(!empty($resumenFiscal['hasar_controladores']))
                <div class="bold" style="margin-top: 6px; font-size: 0.9em;">Controladores Fiscales Hasar:</div>
                @foreach($resumenFiscal['hasar_controladores'] as $ctrl)
                    <div class="row" style="margin-top: 2px;">
                        <span>{{ $ctrl['modelo'] }} ({{ $ctrl['hasar_ip'] }}):</span>
                        <span class="bold">${{ number_format($ctrl['total'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div style="font-size: 0.82em; color: #334155; padding-left: 8px;">
                        • P.Venta: {{ $ctrl['puntos_venta_info'] ?? '-' }} • Tickets: {{ $ctrl['cant_tickets'] ?? 0 }}
                    </div>
                    <div style="font-size: 0.82em; padding-left: 8px; font-weight: bold; color: {{ !empty($ctrl['cierre_z_emitido']) ? '#059669' : '#d97706' }};">
                        • Estado Z: {{ !empty($ctrl['cierre_z_emitido']) ? 'Z Emitido (' . ($ctrl['cierre_z_numero'] ?? 'OK') . ')' : 'Pendiente' }}
                    </div>
                @endforeach
            @endif
        </div>
        @endif

        <!-- ARQUEO DE EFECTIVO FÍSICO / FALLO DE CAJA -->
        <div class="border-bottom">
            <div class="bold center" style="margin-bottom: 5px;">ARQUEO DE EFECTIVO / FALLO DE CAJA</div>
            
            <div class="row">
                <span>(+) Fondo Inicial Apertura:</span>
                <span>${{ number_format($caja->monto_inicial_efectivo, 2, ',', '.') }}</span>
            </div>
            <div class="row">
                <span>(+) Ventas en Efectivo:</span>
                <span>${{ number_format($caja->total_efectivo, 2, ',', '.') }}</span>
            </div>
            @if($caja->movimientos && $caja->movimientos->where('tipo', 'ingreso')->isNotEmpty())
                <div class="row" style="color: #065f46;">
                    <span>(+) Ingresos Manuales de Efectivo:</span>
                    <span class="bold">+${{ number_format($caja->movimientos->where('tipo', 'ingreso')->sum('monto'), 2, ',', '.') }}</span>
                </div>
                @foreach($caja->movimientos->where('tipo', 'ingreso') as $ing)
                    <div style="font-size: 0.82em; color: #047857; padding-left: 10px;">
                        • {{ $ing->motivo }}: +${{ number_format($ing->monto, 2, ',', '.') }}
                    </div>
                @endforeach
            @endif
            @if($caja->movimientos && $caja->movimientos->where('tipo', 'retiro')->isNotEmpty())
                <div class="row" style="color: #991b1b;">
                    <span>(-) Retiros / Pagos de Efectivo:</span>
                    <span class="bold">-${{ number_format($caja->movimientos->where('tipo', 'retiro')->sum('monto'), 2, ',', '.') }}</span>
                </div>
                @foreach($caja->movimientos->where('tipo', 'retiro') as $ret)
                    <div style="font-size: 0.82em; color: #b91c1c; padding-left: 10px;">
                        • {{ $ret->motivo }} {{ $ret->comprobante ? "({$ret->comprobante})" : '' }}: -${{ number_format($ret->monto, 2, ',', '.') }}
                    </div>
                @endforeach
            @endif
            <div class="row bold border-top" style="margin-top: 4px; padding-top: 4px;">
                <span>(=) Efectivo Teórico Esperado:</span>
                <span>${{ number_format($caja->monto_final_efectivo_sistema, 2, ',', '.') }}</span>
            </div>
            <div class="row bold border-top" style="margin-top: 4px; padding-top: 4px;">
                <span>EFECTIVO REAL CONTADO:</span>
                <span>${{ number_format($caja->monto_final_efectivo_real, 2, ',', '.') }}</span>
            </div>

            @php
                $dif = (float) $caja->diferencia_efectivo;
            @endphp
            <div class="row bold" style="font-size: 1.1em; margin-top: 4px;">
                <span>DIFERENCIA / FALLO DE CAJA:</span>
                @if(abs($dif) < 0.01)
                    <span>$0,00 (EXACTO)</span>
                @elseif($dif > 0)
                    <span>+${{ number_format($dif, 2, ',', '.') }} (SOBRANTE)</span>
                @else
                    <span>-${{ number_format(abs($dif), 2, ',', '.') }} (FALTANTE)</span>
                @endif
            </div>
        </div>

        <!-- Firma -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px dotted #94a3b8; text-align: center;">
            <div style="width: 60%; margin: 0 auto; border-top: 1px solid #000; padding-top: 4px;">
                Firma y Aclaración de Responsable
            </div>
            <div style="font-size: 0.8em; color: #64748b; margin-top: 10px;">
                Comprobante oficial no fiscal de Arqueo y Control Interno de Caja
            </div>
        </div>
    </div>

</body>
</html>
