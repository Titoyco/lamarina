<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo {{ $recibo->numero_formateado }} - La Marina</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { 
                background: white !important; 
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .page-break { page-break-after: always; }
        }
        @page {
            size: A4 portrait;
            margin: 1cm;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased text-xs p-4 sm:p-6 print:p-0">

    <!-- Barra Superior de Control (Solo visible en pantalla) -->
    <div class="no-print max-w-4xl mx-auto mb-4 bg-slate-900 text-white p-3 rounded-xl shadow-lg flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span class="text-sm font-bold">Vista Previa de Impresión: Recibo #{{ $recibo->numero_recibo }}</span>
            @if($recibo->anulado)
                <span class="bg-rose-600 text-white font-black text-[10px] px-2 py-0.5 rounded uppercase">Anulado</span>
            @endif
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs py-1.5 px-4 rounded-lg shadow transition uppercase flex items-center space-x-1">
                <span>🖨️ Mandar a Imprimir</span>
            </button>
            <button onclick="window.close()" class="bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs py-1.5 px-3 rounded-lg transition uppercase">
                ✕ Cerrar
            </button>
        </div>
    </div>

    <!-- Contenedor de Hoja A4 -->
    <div class="max-w-4xl mx-auto space-y-6 print:space-y-4">
        
        {{-- Macro-componente de bloque de recibo (Original y Duplicado) --}}
        @php
            $tiposEjemplar = ['ORIGINAL (Cliente)', 'DUPLICADO (Comercio)'];
        @endphp

        @foreach($tiposEjemplar as $index => $ejemplar)
            <div class="bg-white border-2 border-slate-300 rounded-lg p-5 shadow-sm print:border-slate-400 print:shadow-none {{ $index > 0 ? 'border-dashed pt-4' : '' }}">
                
                <!-- Encabezado de Empresa y Comprobante -->
                <div class="flex justify-between items-start border-b-2 border-slate-800 pb-3">
                    <div class="space-y-0.5">
                        <h1 class="text-2xl font-black tracking-wider uppercase text-slate-900">{{ $recibo->sucursal?->nombre_fantasia ?: 'LA MARINA' }}</h1>
                        <p class="text-[11px] font-bold text-slate-700 uppercase">{{ $recibo->sucursal?->razon_social ?: 'Tiendas la Marina SAS' }}</p>
                        <p class="text-[10px] text-slate-500">CUIT: {{ $recibo->sucursal?->cuit ?: '30-71699602-2' }} • {{ $recibo->sucursal?->condicion_iva ?: 'IVA Resp. Inscripto' }}</p>
                        <p class="text-[10px] text-slate-600">
                            Sucursal: <strong>{{ $recibo->sucursal?->nombre }}</strong> ({{ $recibo->sucursal?->codigo_sucursal }})
                        </p>
                        <p class="text-[10px] text-slate-500">
                            {{ $recibo->sucursal?->direccion ?? 'Casa Central' }} — {{ $recibo->sucursal?->provincia?->nombre ?? 'Chubut' }}
                        </p>
                        @if($recibo->sucursal?->telefono || $recibo->sucursal?->email)
                            <p class="text-[9px] text-slate-400">
                                @if($recibo->sucursal?->telefono) Tel: {{ $recibo->sucursal->telefono }} @endif
                                @if($recibo->sucursal?->telefono && $recibo->sucursal?->email) • @endif
                                @if($recibo->sucursal?->email) {{ $recibo->sucursal->email }} @endif
                            </p>
                        @endif
                        <p class="text-[9px] font-mono text-slate-400">{{ $recibo->sucursal?->pagina_web ?: 'www.tiendaslamarina.com.ar' }}</p>
                    </div>

                    <!-- Cuadro Central Tipo Comprobante -->
                    <div class="border-2 border-slate-800 rounded px-3 py-1 text-center bg-slate-50">
                        <span class="text-xl font-black">X</span>
                        <span class="block text-[8px] font-bold uppercase tracking-tight text-slate-600">Doc. No Válido<br>como Factura</span>
                    </div>

                    <!-- Datos del Comprobante -->
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block">{{ $ejemplar }}</span>
                        <h2 class="text-lg font-black font-mono tracking-tight text-slate-900">
                            RECIBO: {{ $recibo->numero_formateado }}
                        </h2>
                        <p class="text-[11px] font-mono font-bold text-slate-700">
                            Fecha: {{ \Carbon\Carbon::parse($recibo->fecha)->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-[10px] text-slate-500">
                            Operador: {{ $recibo->user?->name ?? 'Operador' }}
                        </p>
                    </div>
                </div>

                @if($recibo->anulado)
                    <div class="my-2 bg-rose-50 border border-rose-300 text-rose-800 p-2 rounded text-center font-black tracking-widest uppercase">
                        *** COMPROBANTE ANULADO EL {{ \Carbon\Carbon::parse($recibo->fecha_anulacion)->format('d/m/Y') }} ***
                    </div>
                @endif

                <!-- Datos del Cliente -->
                <div class="my-3 bg-slate-50 border border-slate-200 rounded p-2.5 grid grid-cols-2 sm:grid-cols-4 gap-2 text-[11px]">
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-500 uppercase block">Recibimos de:</span>
                        <strong class="text-slate-900 uppercase text-xs">{{ $recibo->cliente?->nombre }}</strong>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-500 uppercase block">Documento:</span>
                        <span class="font-mono font-bold">DNI {{ $recibo->cliente?->dni }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-500 uppercase block">Cuenta / CUIT:</span>
                        <span class="font-mono">{{ $recibo->cliente?->nro_cuenta ?? $recibo->cliente?->cuit ?? '-' }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-500 uppercase block">Domicilio:</span>
                        <span>{{ $recibo->cliente?->domicilio ?? 'S/D' }} ({{ $recibo->cliente?->localidad?->nombre ?? 'S/L' }})</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-500 uppercase block">Medio de Pago:</span>
                        <strong class="text-slate-800 uppercase">{{ $recibo->medio_pago }}</strong>
                    </div>
                </div>

                <!-- Detalle de Conceptos e Imputaciones -->
                <table class="w-full text-left border-collapse border border-slate-300 text-[10px] my-2">
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 font-bold uppercase border-b border-slate-300">
                            <th class="py-1 px-2 border-r border-slate-300">Crédito</th>
                            <th class="py-1 px-1.5 text-center border-r border-slate-300">Cuota</th>
                            <th class="py-1 px-2 border-r border-slate-300">Concepto</th>
                            <th class="py-1 px-2 text-right border-r border-slate-300">Saldo Anterior</th>
                            <th class="py-1 px-2 text-right border-r border-slate-300">Importe Abonado</th>
                            <th class="py-1 px-2 text-right">Saldo Actual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($recibo->detalles as $det)
                            <tr>
                                <td class="py-1 px-2 font-mono font-bold border-r border-slate-200">
                                    {{ $det->credito?->full_identificador ?? ($det->id_credito ? ('CR-' . $det->id_credito) : 'CTA CTE') }}
                                </td>
                                <td class="py-1 px-1.5 text-center font-mono border-r border-slate-200">
                                    {{ $det->num_cuota ? ('#' . $det->num_cuota) : '—' }}
                                </td>
                                <td class="py-1 px-2 border-r border-slate-200">
                                    {{ $det->concepto }}
                                </td>
                                <td class="py-1 px-2 text-right font-mono border-r border-slate-200 tabular-nums">
                                    $ {{ number_format($det->saldo_anterior, 2, ',', '.') }}
                                </td>
                                <td class="py-1 px-2 text-right font-mono font-bold text-slate-900 border-r border-slate-200 tabular-nums">
                                    $ {{ number_format($det->importe, 2, ',', '.') }}
                                </td>
                                <td class="py-1 px-2 text-right font-mono tabular-nums">
                                    $ {{ number_format($det->saldo_nuevo, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-100 font-bold border-t-2 border-slate-400">
                            <td colspan="4" class="py-1.5 px-2 text-right uppercase text-[10px]">
                                TOTAL COBRADO:
                            </td>
                            <td class="py-1.5 px-2 text-right font-mono text-xs font-black text-slate-900 tabular-nums border-r border-slate-200">
                                $ {{ number_format($recibo->importe_total, 2, ',', '.') }}
                            </td>
                            <td class="text-right px-2 font-mono text-[9px] text-slate-500">
                                @if((float)$recibo->saldo_a_favor_generado > 0)
                                    (A favor: ${{ number_format($recibo->saldo_a_favor_generado, 2, ',', '.') }})
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>

                @if($recibo->observaciones)
                    <p class="text-[9px] text-slate-600 italic mt-1">
                        Obs: {{ $recibo->observaciones }}
                    </p>
                @endif

                <!-- Firmas y Pie -->
                <div class="mt-8 pt-2 grid grid-cols-2 gap-8 text-center text-[10px]">
                    <div>
                        <div class="border-t border-slate-400 w-48 mx-auto mb-1"></div>
                        <span class="text-slate-500 uppercase">Firma y Aclaración Titular</span>
                    </div>
                    <div>
                        <div class="border-t border-slate-400 w-48 mx-auto mb-1"></div>
                        <span class="text-slate-500 uppercase">Por La Marina (Cajero / Operador)</span>
                    </div>
                </div>
            </div>

            @if($index === 0)
                <div class="text-center text-[9px] text-slate-400 uppercase tracking-widest no-print">
                    - - - - - - - - - - - - - - - - - - - - - - - - - CORTE AQUÍ - - - - - - - - - - - - - - - - - - - - - - - - -
                </div>
            @endif
        @endforeach

    </div>

</body>
</html>
