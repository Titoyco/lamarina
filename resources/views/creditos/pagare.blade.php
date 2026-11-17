<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagaré - Crédito #{{ $credito->full_identificador }}</title>
    <script type="text/javascript">
        function imprimir() { 
            setTimeout(function(){ 
                window.print(); 
            }, 400);
        }
    </script>
    <style type="text/css">
        @media print {
            @page {
                size: 210mm 297mm;
                margin: 10mm 15mm;
            }
            .no-print {
                display: none !important;
            }
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 10px;
            background-color: #f8fafc;
        }
        .contenedor {
            width: 780px;
            margin: 0 auto;
            background-color: #fff;
            padding: 18px 24px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .contenedor {
                box-shadow: none;
                padding: 0;
                width: 100%;
            }
        }
        .barra-acciones {
            width: 780px;
            margin: 0 auto 10px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-accion {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 6px;
            cursor: pointer;
            text-transform: uppercase;
            border: 1px solid transparent;
        }
        .btn-imprimir {
            background-color: #0284c7;
            color: #fff;
        }
        .btn-imprimir:hover { background-color: #0369a1; }
        .btn-volver {
            background-color: #e2e8f0;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-volver:hover { background-color: #cbd5e1; }
        .tabla-border {
            width: 100%;
            border-collapse: collapse;
        }
        .tabla-border td, .tabla-border th {
            border: 1px solid #ccc;
            padding: 4px;
        }
        .bg-gris {
            background-color: #E2E2E2;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .texto-manuscrito {
            font-family: "Brush Script MT", cursive, serif;
            font-size: 13.5px;
            line-height: 20px;
            text-align: justify;
        }
        .box-firma {
            border: 2px solid #E2E2E2;
            text-align: center;
            vertical-align: bottom;
            height: 70px;
            font-size: 10px;
            padding: 4px;
        }
        hr.linea-punteada {
            border: none;
            border-top: 2px dashed #000;
            margin: 12px 0;
        }
    </style>
</head>
<body @if(!request('embed')) onload="imprimir();" @endif style="{{ request('embed') ? 'padding: 4px;' : '' }}">

@if(!request('embed'))
<div class="barra-acciones no-print">
    <a href="javascript:window.history.back()" class="btn-accion btn-volver">
        ← Volver
    </a>
    <button type="button" onclick="window.print()" class="btn-accion btn-imprimir">
        🖨️ Imprimir Pagaré
    </button>
</div>
@endif

<div class="contenedor">

    <!-- Encabezado -->
    <table style="width: 100%;">
        <tr>
            <td rowspan="2" style="width: 58%;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" height="48" onerror="this.style.display='none'" />
                    <div>
                        <strong style="font-size: 17px; letter-spacing: 1px;">LA MARINA</strong><br/>
                        <span style="font-size: 10px; color: #475569;">{{ $credito->sucursal?->nombre ?? 'Sucursal' }} • {{ $credito->sucursal?->direccion ?? '' }}</span>
                    </div>
                </div>
            </td>
            <td class="bg-gris" style="width: 18%;">Id Venta / Crédito:</td>
            <td style="border: 1px solid #ccc; padding: 4px; width: 24%;">&nbsp;<strong>{{ $credito->full_identificador }}</strong></td>
        </tr>
        <tr>
            <td class="bg-gris">Fecha/Hora:</td>
            <td style="border: 1px solid #ccc; padding: 4px;">&nbsp;{{ $credito->fecha_credito ? \Carbon\Carbon::parse($credito->fecha_credito)->format('d/m/Y') : now()->format('d/m/Y') }}</td>
        </tr>
    </table>

    <p style="font-size: 10.5px; margin-top: 8px; line-height: 1.35; margin-bottom: 2px;">
        Autorizo a GUILLERMO BARZINI, “LA MARINA”, y/o “TIENDAS LA MARINA SAS”, a debitar de mi cuenta bancaria el importe que surge de la siguiente compra. Si el débito no fuese posible por alguna causa ajena a la EMPRESA, me comprometo a realizar el pago en el local comercial de {{ $credito->sucursal?->direccion ?? 'RIVADAVIA 249' }} – {{ $credito->sucursal?->nombre ?? 'Trelew' }}.
    </p>
    <p style="font-size: 9.5px; font-style: italic; color: #b91c1c; margin-top: 0; margin-bottom: 8px;">
        * El incumplimiento de los pagos pactados, generará intereses mensuales por mora y dará vía al COBRO JUDICIAL.
    </p>

    <!-- Datos Cliente -->
    <table class="tabla-border" style="margin-top: 4px;">
        <tr>
            <td class="bg-gris" style="width: 35%;">Nombres y Apellidos</td>
            <td class="bg-gris" style="width: 15%;">DNI</td>
            <td class="bg-gris" style="width: 20%;">Teléfono</td>
            <td class="bg-gris" style="width: 30%;">Dirección</td>
        </tr>
        <tr>
            <td><strong>{{ $credito->cliente?->nombre }}</strong></td>
            <td class="text-center font-bold">{{ $credito->cliente?->dni }}</td>
            <td class="text-center">{{ $credito->cliente?->tel1 ?: ($credito->cliente?->tel2 ?: '-') }}</td>
            <td>{{ $credito->cliente?->direccion ?: '-' }}{{ $credito->cliente?->localidad ? ' (' . $credito->cliente->localidad->nombre . ')' : '' }}</td>
        </tr>
    </table>

    <!-- Plan y Firma Autorizado -->
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 60%; vertical-align: top; font-size: 11px; line-height: 1.4;">
                Son <strong>{{ $credito->cantidad_cuotas }}</strong> cuotas mensuales consecutivas de <strong>$ {{ number_format((float)$credito->valor_cuota_ref, 2, ',', '.') }}</strong><br/><br/>
                {{ $concepto }}
            </td>
            <td style="width: 40%;" class="box-firma">
                <strong>FIRMA DEL TITULAR</strong>
                @if($credito->autorizado && $credito->autorizado->cliente)
                    <p style="margin: 3px 0 0 0; font-size: 9px;">
                        AUTORIZADO: {{ $credito->autorizado->cliente->nombre }}<br>
                        DNI: {{ $credito->autorizado->cliente->dni }}
                    </p>
                @endif
            </td>
        </tr>
    </table>

    <hr style="border: none; border-top: 1px solid #ccc; margin: 10px 0;" />

    <!-- Datos del Pagaré -->
    <table style="width: 100%;">
        <tr>
            <td class="bg-gris" style="width: 20%;">SELLADO</td>
            <td class="bg-gris" style="width: 20%;">NUMERO</td>
            <td class="bg-gris" style="width: 20%;">VENCIMIENTO</td>
            <td class="text-right" style="width: 15%; font-size: 16px; font-weight: bold;">Por:</td>
            <td style="border: 2px solid #94a3b8; font-size: 15px; font-weight: bold; padding: 4px; width: 25%;" class="text-center">
                $ {{ number_format($totalMonto, 2, ',', '.') }}.-
            </td>
        </tr>
        <tr>
            <td style="border: 1px solid #ccc; height: 20px;"></td>
            <td style="border: 1px solid #ccc; height: 20px;"></td>
            <td style="border: 1px solid #ccc; height: 20px;"></td>
            <td colspan="2"></td>
        </tr>
    </table>

    <p class="texto-manuscrito" style="margin-top: 10px; margin-bottom: 4px;">
        El _____________ de ______________________ de ___________________ pagaré / mes a ________________
    </p>
    <p class="texto-manuscrito" style="margin-top: 0; margin-bottom: 4px;">
        _________________________________________________________________________________ a su orden 
    </p>
    
    <table style="width: 100%;">
        <tr>
            <td class="texto-manuscrito" style="width: 150px;">la cantidad de pesos:</td>
            <td style="background-color: #E2E2E2; padding: 4px; font-weight: bold; font-size: 12px;">
                &nbsp;{{ $montoEnLetras }} PESOS.-
            </td>
        </tr>
    </table>

    <p class="texto-manuscrito" style="margin-top: 6px; margin-bottom: 6px;">
        por igual valor recibido en ___________________________________________ a _____________________ entera satisfacción pagadero sin protesto (art. 50 Ley 5965/63) en {{ $credito->sucursal?->direccion ?? 'Rivadavia 249' }} de {{ $credito->sucursal?->nombre ?? 'Trelew' }}.
    </p>

    <!-- Firmante y Cuadro de Firma -->
    <table style="width: 100%; margin-top: 6px;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <table style="width: 100%; font-size: 10.5px;">
                    <tr>
                        <td style="width: 75px; padding: 2px 0;"><strong>Firmante:</strong></td>
                        <td style="border-bottom: 1px solid #ccc;">{{ $credito->cliente?->nombre }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;"><strong>Documento:</strong></td>
                        <td style="border-bottom: 1px solid #ccc;">{{ $credito->cliente?->dni }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 0;"><strong>Domicilio:</strong></td>
                        <td style="border-bottom: 1px solid #ccc;">{{ $credito->cliente?->direccion ?: '' }}{{ $credito->cliente?->localidad ? ' (' . $credito->cliente->localidad->nombre . ')' : '' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%;" class="box-firma">
                <strong>FIRMA DEL FIRMANTE</strong>
                @if($credito->autorizado && $credito->autorizado->cliente)
                    <p style="margin: 2px 0 0 0; font-size: 9px;">
                        AUTORIZADO: {{ $credito->autorizado->cliente->nombre }}<br>
                        DNI: {{ $credito->autorizado->cliente->dni }}
                    </p>
                @endif
            </td>
        </tr>
    </table>

    <hr class="linea-punteada" />

    <!-- Talón Cortable / Copia Inferior -->
    <table style="width: 100%;">
        <tr>
            <td style="width: 65%; border: 1px solid #ccc; padding: 6px; font-size: 10.5px; vertical-align: top;">
                Son <strong>{{ $credito->cantidad_cuotas }}</strong> cuotas mensuales consecutivas de <strong>$ {{ number_format((float)$credito->valor_cuota_ref, 2, ',', '.') }}</strong>.<br />
                {{ $concepto }}
            </td>
            <td style="width: 35%; padding-left: 8px; vertical-align: top;">
                <table style="width: 100%; font-size: 10.5px;">
                    <tr>
                        <td class="bg-gris" style="width: 40%;">Id venta:</td>
                        <td style="border: 1px solid #ccc; padding: 2px 4px;">&nbsp;<strong>{{ $credito->full_identificador }}</strong></td>
                    </tr>
                    <tr>
                        <td class="bg-gris">Fecha/hora:</td>
                        <td style="border: 1px solid #ccc; padding: 2px 4px;">&nbsp;{{ $credito->fecha_credito ? \Carbon\Carbon::parse($credito->fecha_credito)->format('d/m/Y') : now()->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Detalle de Cuenta Corriente y Próximos Vencimientos (Referencia Histórica La Marina) -->
    @if(!empty($estadoCrediticio))
        <div style="margin-top: 10px; font-size: 10.5px; border-top: 1px solid #e2e8f0; padding-top: 6px;">
            <p style="font-size: 11px; margin: 2px 0; font-weight: bold;"><u>DETALLE DE CUENTA CORRIENTE:</u></p>
            <div style="display: flex; gap: 24px; font-size: 10.5px; margin-top: 2px;">
                <div>TOTAL ADEUDADO: <strong>$ {{ number_format($estadoCrediticio['deuda_acumulada'], 2, ',', '.') }}</strong></div>
                <div style="color: {{ $estadoCrediticio['tiene_mora'] ? '#dc2626' : '#334155' }};">
                    MORA (VENCIDO): <strong>$ {{ number_format($estadoCrediticio['total_mora'], 2, ',', '.') }}</strong>
                </div>
                <div>LÍMITE DISPONIBLE: <strong>$ {{ number_format($estadoCrediticio['limite_disponible'], 2, ',', '.') }}</strong></div>
            </div>
        </div>
    @endif

    @if(!empty($proximosMeses))
        <div style="margin-top: 8px;">
            <p style="font-size: 11px; margin: 2px 0; font-weight: bold;"><u>PRÓXIMOS VENCIMIENTOS:</u></p>
            <table class="tabla-border" style="width: 100%; font-size: 9.5px; margin-top: 2px;">
                <thead>
                    <tr class="bg-gris">
                        <th style="width: 25%;">1° Mes (Lote {{ $proximosMeses[1]['lote'] ?? '-' }})</th>
                        <th style="width: 25%;">2° Mes (Lote {{ $proximosMeses[2]['lote'] ?? '-' }})</th>
                        <th style="width: 25%;">3° Mes (Lote {{ $proximosMeses[3]['lote'] ?? '-' }})</th>
                        <th style="width: 25%;">4° Mes (Lote {{ $proximosMeses[4]['lote'] ?? '-' }})</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @for($i = 1; $i <= 4; $i++)
                            <td style="vertical-align: top; height: 36px; padding: 2px;">
                                @if(!empty($proximosMeses[$i]['cuotas']) && $proximosMeses[$i]['cuotas']->isNotEmpty())
                                    <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                                        @foreach($proximosMeses[$i]['cuotas'] as $c)
                                            <tr>
                                                <td>{{ $c->credito?->full_identificador }} C/{{ $c->num_cuota }}</td>
                                                <td class="text-right">$ {{ number_format($c->saldo_actual, 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @else
                                    <div class="text-center" style="color: #94a3b8; padding-top: 8px;">-</div>
                                @endif
                            </td>
                        @endfor
                    </tr>
                    <tr class="bg-gris">
                        @for($i = 1; $i <= 4; $i++)
                            <td class="text-center font-bold">
                                Total: $ {{ number_format($proximosMeses[$i]['total'] ?? 0, 2, ',', '.') }}
                            </td>
                        @endfor
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

</div>
</body>
</html>