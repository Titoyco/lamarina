<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante Factura {{ $venta->comprobante }} - La Marina</title>
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
            @if($formato === 'ticket')
                size: 80mm auto;
                margin: 3mm;
            @else
                size: A4 portrait;
                margin: 1cm;
            @endif
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans antialiased text-xs {{ request('embed') ? 'p-1' : 'p-3 sm:p-6' }} print:p-0">

    @if(!request('embed'))
    <!-- Barra Superior de Control de Impresión (No visible al imprimir ni en modo embebido) -->
    <div class="no-print max-w-3xl mx-auto mb-4 bg-slate-900 text-white p-3 rounded-xl shadow-lg flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center space-x-2">
            <span class="text-sm font-bold">Factura #{{ $venta->comprobante }}</span>
            <span class="text-xs text-slate-400 font-mono">({{ $venta->fecha ? $venta->fecha->format('d/m/Y H:i') : '' }})</span>
            @if($venta->estado === 'anulada')
                <span class="bg-rose-600 text-white font-black text-[10px] px-2 py-0.5 rounded uppercase">Anulada</span>
            @endif
        </div>

        <div class="flex items-center space-x-2">
            <!-- Selector de formato -->
            <a href="{{ route('ventas.imprimir', ['venta' => $venta->id, 'formato' => 'ticket']) }}" 
               class="text-xs font-bold py-1 px-2.5 rounded-lg {{ $formato === 'ticket' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                🖨️ Ticket 80mm
            </a>
            <a href="{{ route('ventas.imprimir', ['venta' => $venta->id, 'formato' => 'a4']) }}" 
               class="text-xs font-bold py-1 px-2.5 rounded-lg {{ $formato === 'a4' ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                📄 Hoja A4
            </a>

            <button onclick="window.print()" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs py-1.5 px-4 rounded-lg shadow transition uppercase flex items-center space-x-1">
                <span>🖨️ Imprimir</span>
            </button>
            <button onclick="window.close()" class="bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs py-1.5 px-3 rounded-lg transition uppercase">
                ✕ Cerrar
            </button>
        </div>
    </div>
    @endif

    @if($formato === 'ticket')
        <!-- ============================================================== -->
        <!-- FORMATO TICKET TÉRMICO (80mm)                                  -->
        <!-- ============================================================== -->
        <div class="max-w-[340px] mx-auto bg-white p-4 font-mono text-[11px] leading-tight text-slate-900 border border-slate-300 print:border-0 shadow-sm print:shadow-none">
            <!-- Encabezado de Empresa -->
            <div class="text-center space-y-0.5 pb-2">
                <h1 class="text-sm font-black uppercase tracking-wider">{{ $venta->sucursal?->nombre_fantasia ?: 'LA MARINA' }}</h1>
                <p class="text-[10px] uppercase font-bold text-slate-800">{{ $venta->sucursal?->razon_social ?: 'Tiendas la Marina SAS' }}</p>
                <p class="text-[9px] text-slate-600">CUIT: {{ $venta->sucursal?->cuit ?: '30-71699602-2' }} • {{ $venta->sucursal?->condicion_iva ?: 'IVA Resp. Inscripto' }}</p>
                @if($venta->sucursal?->ingresos_brutos)
                    <p class="text-[9px] text-slate-600">IIBB: {{ $venta->sucursal->ingresos_brutos }}</p>
                @endif
                @if($venta->sucursal?->domicilio_fiscal)
                    <p class="text-[9px] text-slate-600">Dom. Fiscal: {{ $venta->sucursal->domicilio_fiscal }}</p>
                @endif
                <p class="text-[9px] font-bold text-slate-800">Sucursal: {{ $venta->sucursal?->nombre }}</p>
                @if($venta->sucursal?->direccion)
                    <p class="text-[9px] text-slate-700 font-semibold">{{ $venta->sucursal->direccion }}</p>
                @endif
                @if($venta->sucursal?->telefono || $venta->sucursal?->email)
                    <p class="text-[8px] text-slate-500">
                        @if($venta->sucursal?->telefono) Tel: {{ $venta->sucursal->telefono }} @endif
                        @if($venta->sucursal?->telefono && $venta->sucursal?->email) • @endif
                        @if($venta->sucursal?->email) {{ $venta->sucursal->email }} @endif
                    </p>
                @endif
                <p class="text-[9px] font-mono text-slate-600">{{ $venta->sucursal?->pagina_web ?: 'www.tiendaslamarina.com.ar' }}</p>
            </div>

            <div class="border-t border-dashed border-slate-400 my-2"></div>

            <!-- Datos del Comprobante -->
            <div class="space-y-0.5 text-[10px]">
                <div class="flex justify-between font-bold">
                    <span>FACTURA {{ $venta->isFacturaA() ? 'A' : 'B' }}</span>
                    <span>N° {{ $venta->comprobante }}</span>
                </div>
                @if($venta->esExentoIva())
                    <div class="text-[9px] font-bold text-slate-700 bg-slate-100 p-0.5 text-center rounded">
                        Operación Exenta de IVA - Ley 19.640 (Área Aduanera Especial)
                    </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>Fecha: {{ $venta->fecha ? $venta->fecha->format('d/m/Y') : '-' }}</span>
                    <span>Hora: {{ $venta->fecha ? $venta->fecha->format('H:i:s') : '-' }}</span>
                </div>
                <div class="text-slate-600">
                    P.V.: {{ str_pad($venta->punto_venta, 4, '0', STR_PAD_LEFT) }} • Cajero: {{ $venta->usuario?->name ?? 'Operador' }}
                </div>
            </div>

            <div class="border-t border-dashed border-slate-400 my-2"></div>

            <!-- Datos del Cliente -->
            <div class="text-[10px] space-y-0.5">
                <div class="font-bold uppercase">
                    Cliente: {{ $venta->cliente_nombre }}
                </div>
                @if($venta->cliente_documento)
                    <div class="text-slate-600">{{ $venta->isFacturaA() ? 'CUIT' : 'DNI/CUIT' }}: {{ $venta->cliente_documento }}</div>
                @endif
                <div class="text-slate-600">Cond. IVA: {{ $venta->isFacturaA() ? 'Responsable Inscripto' : 'Consumidor Final' }}</div>
            </div>

            <div class="border-t border-dashed border-slate-400 my-2"></div>

            <!-- Detalle de Artículos -->
            <div class="space-y-1 text-[10px]">
                <div class="flex justify-between font-bold border-b border-slate-300 pb-1">
                    <span>DESCRIPCIÓN</span>
                    <span>IMPORTE</span>
                </div>

                @foreach($venta->items as $it)
                    <div class="py-0.5">
                        <div class="font-bold truncate">{{ $it->descripcion }}</div>
                        <div class="flex justify-between text-[9px] text-slate-600 pl-1">
                            <span>
                                {{ number_format((float)$it->cantidad, 0) }} x ${{ number_format((float)$it->precio_unitario, 2, ',', '.') }}
                                @if((float)$it->descuento > 0 || (float)$it->descuento_porcentaje > 0)
                                    <span class="text-emerald-700 font-bold">
                                        @if((float)$it->descuento_porcentaje >= 100 || (float)$it->subtotal <= 0)
                                            (2x1 100% BONIF.)
                                        @else
                                            (-{{ number_format((float)$it->descuento_porcentaje, 0) }}%)
                                        @endif
                                    </span>
                                @endif
                            </span>
                            <span class="font-bold text-slate-900">${{ number_format((float)$it->subtotal, 2, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-dashed border-slate-400 my-2"></div>

            <!-- Totales -->
            <div class="space-y-0.5 text-[11px]">
                <div class="flex justify-between text-slate-700">
                    <span>Subtotal:</span>
                    <span>${{ number_format((float)$venta->subtotal, 2, ',', '.') }}</span>
                </div>
                @if((float)$venta->descuento > 0)
                    <div class="flex justify-between text-emerald-800 font-bold">
                        <span>Descuento:</span>
                        <span>-${{ number_format((float)$venta->descuento, 2, ',', '.') }}</span>
                    </div>
                @endif
                @if((float)$venta->recargo > 0)
                    <div class="flex justify-between text-slate-700">
                        <span>Recargo Financiación:</span>
                        <span>${{ number_format((float)$venta->recargo, 2, ',', '.') }}</span>
                    </div>
                @endif
                @if($venta->esExentoIva())
                    <div class="flex justify-between text-[10px] text-teal-800 font-bold">
                        <span>IVA (0% - Ley 19.640):</span>
                        <span>$0,00</span>
                    </div>
                @elseif($venta->isFacturaA())
                    <div class="flex justify-between text-[10px] text-slate-600">
                        <span>Neto Gravado:</span>
                        <span>${{ number_format($venta->netoGravado(), 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[10px] text-indigo-700 font-bold">
                        <span>IVA (21%):</span>
                        <span>${{ number_format($venta->ivaLiquidado(), 2, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm font-black border-t border-slate-400 pt-1 mt-1">
                    <span>TOTAL:</span>
                    <span>${{ number_format((float)$venta->total, 2, ',', '.') }}</span>
                </div>
            </div>

            <div class="border-t border-dashed border-slate-400 my-2"></div>

            <!-- Medio de Pago -->
            <div class="text-[10px] space-y-1">
                <div class="flex justify-between">
                    <span class="font-bold">FORMA DE PAGO:</span>
                    <span class="uppercase font-bold">{{ $venta->medio_pago_formateado }}</span>
                </div>

                @if($venta->credito)
                    <div class="p-1.5 bg-slate-50 border border-slate-300 rounded text-[9px] space-y-0.5">
                        <div class="font-black text-slate-900">CRÉDITO #{{ $venta->credito->full_identificador }}</div>
                        <div>Financiado: {{ $venta->cuotas_credito }} cuota(s) de ${{ number_format((float)$venta->valor_cuota_credito, 2, ',', '.') }}</div>
                        <div>Lote Presentación: #{{ $venta->credito->presentacion?->numero ?? '-' }}</div>
                    </div>

                    <!-- Talón de Firma para Crédito -->
                    <div class="pt-6 pb-2 text-center text-[9px] space-y-1">
                        <div class="border-b border-dotted border-slate-500 w-3/4 mx-auto mb-1"></div>
                        <div class="font-bold uppercase">Firma del Titular</div>
                        <div class="text-[8px] text-slate-500">Acepto y pagaré conforme a las condiciones de crédito.</div>
                    </div>
                @endif
            </div>

            <div class="border-t border-dashed border-slate-400 my-2"></div>

            @if($venta->cae)
                <div class="border-b border-dashed border-slate-400 pb-2 mb-2 text-center">
                    @if($venta->afip_qr)
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($venta->afip_qr) }}"
                             alt="QR ARCA" class="w-24 h-24 mx-auto my-1 border border-slate-300 p-1">
                    @endif
                    <div class="font-mono text-[9px] font-bold">CAE: {{ $venta->cae }}</div>
                    <div class="font-mono text-[8px] text-slate-600">Vto. CAE: {{ $venta->cae_vencimiento ? $venta->cae_vencimiento->format('d/m/Y') : '' }}</div>
                    <div class="text-[8px] uppercase font-bold text-slate-700 mt-0.5">Comprobante Autorizado por ARCA</div>
                </div>
            @endif

            <div class="text-center text-[8px] text-slate-500 uppercase space-y-0.5 pt-1">
                <p>Comprobante de Control Comercial Interno</p>
                <p>¡Gracias por elegir {{ $venta->sucursal?->nombre_fantasia ?: 'La Marina' }}!</p>
                <p class="font-mono lowercase">{{ $venta->sucursal?->pagina_web ?: 'www.tiendaslamarina.com.ar' }}</p>
            </div>
        </div>

    @else
        <!-- ============================================================== -->
        <!-- FORMATO HOJA A4 COMERCIAL                                     -->
        <!-- ============================================================== -->
        <div class="max-w-4xl mx-auto space-y-6 print:space-y-4">
            @php
                $ejemplares = ['ORIGINAL (Cliente)', 'DUPLICADO (Comercio)'];
            @endphp

            @foreach($ejemplares as $index => $ejemplar)
                <div class="bg-white border-2 border-slate-300 rounded-lg p-5 shadow-sm print:border-slate-400 print:shadow-none {{ $index > 0 ? 'border-dashed pt-4' : '' }}">
                    
                    <!-- Encabezado de Factura A4 -->
                    <div class="flex justify-between items-start border-b-2 border-slate-800 pb-3">
                        <div class="w-1/2 space-y-0.5">
                            <h1 class="text-2xl font-black tracking-wider uppercase text-slate-900">{{ $venta->sucursal?->nombre_fantasia ?: 'LA MARINA' }}</h1>
                            <p class="text-xs font-bold text-slate-800 uppercase">{{ $venta->sucursal?->razon_social ?: 'Tiendas la Marina SAS' }}</p>
                            <p class="text-[11px] text-slate-600">CUIT: <span class="font-mono font-bold">{{ $venta->sucursal?->cuit ?: '30-71699602-2' }}</span> • {{ $venta->sucursal?->condicion_iva ?: 'IVA Responsable Inscripto' }}</p>
                            @if($venta->sucursal?->ingresos_brutos)
                                <p class="text-[10px] text-slate-500">Ingresos Brutos: {{ $venta->sucursal->ingresos_brutos }}</p>
                            @endif
                            @if($venta->sucursal?->domicilio_fiscal)
                                <p class="text-[10px] text-slate-500">Domicilio Fiscal: {{ $venta->sucursal->domicilio_fiscal }}</p>
                            @endif
                            <div class="pt-1 text-[11px] text-slate-700">
                                <span class="font-bold">Sucursal de Emisión:</span> {{ $venta->sucursal?->nombre }}
                                @if($venta->sucursal?->direccion)
                                    — <span class="font-semibold">{{ $venta->sucursal->direccion }}</span>
                                @endif
                            </div>
                            <div class="text-[10px] text-slate-500 flex flex-wrap gap-x-2">
                                @if($venta->sucursal?->telefono)
                                    <span>Tel: {{ $venta->sucursal->telefono }}</span>
                                @endif
                                @if($venta->sucursal?->email)
                                    <span>Email: {{ $venta->sucursal->email }}</span>
                                @endif
                                <span>Web: <strong class="text-slate-700">{{ $venta->sucursal?->pagina_web ?: 'www.tiendaslamarina.com.ar' }}</strong></span>
                            </div>
                        </div>

                        <!-- Letra Comprobante Centrada -->
                        <div class="w-16 h-16 border-2 border-slate-800 rounded flex flex-col items-center justify-center bg-slate-50">
                            <span class="text-2xl font-black text-slate-900 leading-none">{{ $venta->isFacturaA() ? 'A' : 'B' }}</span>
                            <span class="text-[8px] font-bold text-slate-600 uppercase">{{ $venta->isFacturaA() ? 'Cód. 01' : 'Cód. 06' }}</span>
                        </div>

                        <!-- Número y Ejemplar -->
                        <div class="w-1/2 text-right">
                            <span class="text-[10px] font-black uppercase text-slate-500 block">{{ $ejemplar }}</span>
                            <h2 class="text-lg font-black font-mono text-slate-900">FACTURA {{ $venta->isFacturaA() ? 'A' : 'B' }}</h2>
                            <p class="text-xs font-mono font-bold text-indigo-700">N° {{ $venta->comprobante }}</p>
                            @if($venta->esExentoIva())
                                <span class="inline-block text-[9px] font-bold text-teal-800 bg-teal-50 border border-teal-300 px-1.5 py-0.5 rounded mt-0.5">
                                    Operación Exenta de IVA - Ley 19.640 (AAE)
                                </span>
                            @endif
                            <p class="text-[10px] text-slate-600">Fecha: {{ $venta->fecha ? $venta->fecha->format('d/m/Y H:i') : '-' }} hs</p>
                            <p class="text-[10px] text-slate-500">P.Venta: {{ str_pad($venta->punto_venta, 4, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>

                    <!-- Datos del Cliente -->
                    <div class="my-3 p-2.5 bg-slate-50 rounded border border-slate-200 grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold block uppercase">Cliente:</span>
                            <span class="font-bold uppercase text-slate-900">{{ $venta->cliente_nombre }}</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold block uppercase">DNI / Documento:</span>
                            <span class="font-mono font-bold">{{ $venta->cliente_documento ?: 'Sin Documento' }}</span>
                        </div>
                    </div>

                    <!-- Tabla de Artículos A4 -->
                    <table class="w-full text-left text-xs my-3 border border-slate-300">
                        <thead class="bg-slate-100 text-[10px] uppercase font-bold text-slate-700 border-b border-slate-300">
                            <tr>
                                <th class="py-1.5 px-2 w-12 text-center">#</th>
                                <th class="py-1.5 px-2 w-28">Código</th>
                                <th class="py-1.5 px-2">Descripción</th>
                                <th class="py-1.5 px-2 text-center w-20">Cantidad</th>
                                <th class="py-1.5 px-2 text-right w-24">P. Unitario</th>
                                <th class="py-1.5 px-2 text-right w-24">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($venta->items as $i => $item)
                                <tr>
                                    <td class="py-1 px-2 text-center font-mono text-slate-400 text-[10px]">{{ $i + 1 }}</td>
                                    <td class="py-1 px-2 font-mono text-slate-600">{{ $item->codigo_producto ?? '-' }}</td>
                                    <td class="py-1 px-2 font-bold">
                                        {{ $item->descripcion }}
                                        @if((float)$item->descuento > 0 || (float)$item->descuento_porcentaje > 0)
                                            <span class="text-[9px] font-bold text-emerald-800 bg-emerald-50 border border-emerald-300 px-1 py-0.2 rounded ml-1">
                                                @if((float)$item->descuento_porcentaje >= 100 || (float)$item->subtotal <= 0)
                                                    PROMO 2x1 (100% BONIF.)
                                                @else
                                                    DESC -{{ number_format((float)$item->descuento_porcentaje, 0) }}% (-${{ number_format((float)$item->descuento, 2, ',', '.') }})
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-1 px-2 text-center font-mono font-bold">{{ number_format((float)$item->cantidad, 2, ',', '.') }}</td>
                                    <td class="py-1 px-2 text-right font-mono">${{ number_format((float)$item->precio_unitario, 2, ',', '.') }}</td>
                                    <td class="py-1 px-2 text-right font-mono font-bold">
                                        @if((float)$item->subtotal <= 0 && ((float)$item->descuento > 0 || (float)$item->descuento_porcentaje >= 100))
                                            <span class="text-emerald-700">$ 0,00</span>
                                        @else
                                            ${{ number_format((float)$item->subtotal, 2, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pie y Totales A4 -->
                    <div class="flex justify-between items-start pt-2 border-t border-slate-300 text-xs">
                        <div class="space-y-1 w-1/2">
                            <div><strong>Medio de Pago:</strong> {{ $venta->medio_pago_formateado }}</div>
                            @if($venta->credito)
                                <div class="p-2 bg-indigo-50 border border-indigo-200 rounded text-[11px] space-y-0.5">
                                    <div><strong>Crédito Asignado:</strong> {{ $venta->credito->full_identificador }}</div>
                                    <div><strong>Plan:</strong> {{ $venta->cuotas_credito }} cuota(s) de ${{ number_format((float)$venta->valor_cuota_credito, 2, ',', '.') }}</div>
                                </div>
                            @endif
                        </div>

                        <div class="w-1/2 space-y-1 text-right font-mono text-xs">
                            @if($venta->esExentoIva())
                                <div class="flex justify-between text-slate-600">
                                    <span>Op. Exentas (Ley 19.640):</span>
                                    <span>${{ number_format((float)$venta->total, 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-teal-700 font-bold">
                                    <span>IVA Liquidado (0%):</span>
                                    <span>$0,00</span>
                                </div>
                            @elseif($venta->isFacturaA())
                                <div class="flex justify-between text-slate-600">
                                    <span>Subtotal Neto Gravado:</span>
                                    <span>${{ number_format($venta->netoGravado(), 2, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-indigo-700 font-bold">
                                    <span>IVA Liquidado (21%):</span>
                                    <span>${{ number_format($venta->ivaLiquidado(), 2, ',', '.') }}</span>
                                </div>
                            @else
                                <div class="flex justify-between text-slate-600">
                                    <span>Subtotal:</span>
                                    <span>${{ number_format((float)$venta->subtotal, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            @if((float)$venta->descuento > 0)
                                <div class="flex justify-between text-emerald-700 font-bold">
                                    <span>Descuento / Bonificación:</span>
                                    <span>-${{ number_format((float)$venta->descuento, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            @if((float)$venta->recargo > 0)
                                <div class="flex justify-between text-indigo-700">
                                    <span>Recargo:</span>
                                    <span>${{ number_format((float)$venta->recargo, 2, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-base font-black border-t border-slate-800 pt-1 text-slate-900">
                                <span>TOTAL:</span>
                                <span>${{ number_format((float)$venta->total, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    @if($venta->credito)
                        <div class="mt-8 pt-4 border-t border-dashed border-slate-400 flex justify-end">
                            <div class="w-64 text-center">
                                <div class="border-b border-slate-600 mb-1"></div>
                                <span class="text-[10px] font-bold uppercase text-slate-600">Firma del Titular / Deudor</span>
                            </div>
                        </div>
                    @endif

                    @if($venta->cae)
                        <div class="mt-4 pt-3 border-t-2 border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if($venta->afip_qr)
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data={{ urlencode($venta->afip_qr) }}"
                                         alt="QR ARCA" class="w-24 h-24 border border-slate-300 p-1 rounded">
                                @endif
                                <div class="text-xs space-y-0.5">
                                    <div class="font-black text-slate-900 tracking-wider">ARCA / AFIP - Factura Electrónica</div>
                                    <div class="text-[10px] text-slate-500">Comprobante Autorizado con Validez Fiscal Oficial</div>
                                    <div class="text-[10px] font-mono text-slate-700">Régimen de Facturación RG 5003</div>
                                </div>
                            </div>
                            <div class="text-right font-mono space-y-1">
                                <div class="text-xs font-bold text-slate-800">
                                    <span class="text-slate-500 uppercase font-sans text-[10px]">CAE N°:</span> {{ $venta->cae }}
                                </div>
                                <div class="text-xs font-semibold text-slate-700">
                                    <span class="text-slate-500 uppercase font-sans text-[10px]">Fecha Vto. CAE:</span> {{ $venta->cae_vencimiento ? $venta->cae_vencimiento->format('d/m/Y') : '' }}
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>
    @endif

</body>
</html>
