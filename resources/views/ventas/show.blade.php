<x-app-layout>
    <div class="max-w-6xl mx-auto py-3 space-y-4" x-data="{
        modalAnular: false,
        motivoAnulacion: '',
        modalFacturarRemito: false,
        tipoFacturaRemito: 'FACTURA_B',
        async enviarAHasar() {
            if (!confirm('¿Desea enviar la impresión de este comprobante a la Impresora Fiscal Hasar?')) return;
            try {
                let resPayload = await fetch('{{ route("ventas.hasar-payload", $venta->id) }}');
                if (!resPayload.ok) throw new Error('No se pudo generar el payload fiscal de la venta.');
                let payload = await resPayload.json();

                let urls = [
                    'http://127.0.0.1:8585/imprimir',
                    'https://127.0.0.1:8586/imprimir'
                ];
                if (window.location.protocol === 'https:') {
                    urls = [
                        'https://127.0.0.1:8586/imprimir',
                        'http://127.0.0.1:8585/imprimir'
                    ];
                }

                let exito = false;
                let resData = null;
                let lastErr = null;

                for (let u of urls) {
                    try {
                        let r = await fetch(u, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json; charset=utf-8' },
                            body: JSON.stringify(payload)
                        });
                        if (r.ok) {
                            resData = await r.json();
                            exito = true;
                            break;
                        }
                    } catch (err) {
                        lastErr = err;
                    }
                }

                if (exito) {
                    alert('✅ Comprobante enviado e impreso exitosamente en la Hasar 2G. Ticket Oficial N° ' + (resData.numero_comprobante || ''));
                } else {
                    alert('⚠️ No se pudo comunicar con el Puente Fiscal Hasar (127.0.0.1:8585).\n\n1. Verifique que puente-hasar prog.exe esté abierto.\n2. En Brave / Chrome, permita Contenido no seguro en la configuración del sitio.');
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }
    }">
        
        <!-- Header con Acciones -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center space-x-3">
                <a href="{{ route('ventas.index') }}" title="Volver al Historial"
                   class="bg-white dark:bg-slate-800 p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <span>{{ $venta->tipo_comprobante_nombre }} #{{ $venta->comprobante }}</span>
                        <span class="text-[10px] font-mono font-bold px-1.5 py-0.5 rounded
                            @if($venta->isFacturaA()) bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-200 border border-indigo-300
                            @elseif($venta->isFacturaC()) bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200 border border-blue-300
                            @elseif($venta->isRemito()) bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200 border border-amber-300
                            @elseif($venta->isPresupuesto()) bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-200 border border-purple-300
                            @elseif($venta->isNotaCredito()) bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200 border border-rose-300
                            @else bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200
                            @endif">
                            {{ $venta->tipo_comprobante_nombre }}
                        </span>
                        @if($venta->esExentoIva())
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 border border-teal-300 dark:bg-teal-950 dark:text-teal-300" title="Operación Exenta por Ley 19.640">
                                🌴 Exento Ley 19.640
                            </span>
                        @endif
                        @if($venta->estado === 'completada')
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300">
                                Completada
                            </span>
                        @elseif($venta->estado === 'remito')
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950 dark:text-amber-300" title="Mercadería entregada sin comprobante fiscal. Debe facturarse posteriormente.">
                                Remito Pendiente
                            </span>
                        @elseif($venta->estado === 'presupuesto')
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 border border-purple-300 dark:bg-purple-950 dark:text-purple-300" title="Cotización previa. No afecta stock ni es fiscal.">
                                Presupuesto
                            </span>
                        @else
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 border border-rose-300 dark:bg-rose-950 dark:text-rose-300">
                                Anulada
                            </span>
                        @endif

                        @if($venta->cae)
                            <span class="text-[10px] font-mono font-black uppercase px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 border border-sky-300 dark:bg-sky-950 dark:text-sky-300">
                                CAE: {{ $venta->cae }} (Vto: {{ $venta->cae_vencimiento?->format('d/m/Y') }})
                            </span>
                        @elseif($venta->tipo_emision === 'fiscal_hasar')
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950 dark:text-amber-300">
                                🖨️ Hasar 2G
                            </span>
                        @endif
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Emitida el {{ $venta->fecha ? $venta->fecha->format('d/m/Y \a \l\a\s H:i') : '-' }} hs • Terminal: {{ $venta->sucursal?->nombre }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($venta->credito)
                    <a href="{{ route('creditos.pagare', $venta->credito->id) }}" target="_blank"
                       class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-2 px-3 rounded-lg shadow-sm transition flex items-center gap-1.5 uppercase">
                        <span>📋 Pagaré de Crédito</span>
                    </a>
                @endif

                @if($venta->isPresupuesto())
                    <a href="{{ route('ventas.create', ['presupuesto_id' => $venta->id]) }}"
                       class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs py-2 px-3 rounded-lg shadow-sm transition flex items-center gap-1.5 uppercase">
                        <span>🛒 Cargar en Facturador</span>
                    </a>
                @endif

                @if($venta->isRemito() && $venta->estado === 'remito')
                    <button type="button" @click="modalFacturarRemito = true"
                            class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-2 px-3 rounded-lg shadow-sm transition flex items-center gap-1.5 uppercase">
                        <span>🧾 Facturar Remito</span>
                    </button>
                @endif

                <button type="button" @click="enviarAHasar()"
                        class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-2 px-3 rounded-lg shadow-sm transition flex items-center gap-1.5 uppercase"
                        title="Mandar impresión física a la controladora fiscal Hasar">
                    <span>🖨️ Mandar a Hasar</span>
                </button>

                <a href="{{ route('ventas.imprimir', ['venta' => $venta->id, 'formato' => 'ticket']) }}" target="_blank"
                   class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-3 rounded-lg shadow-sm transition flex items-center gap-1.5 uppercase">
                    <span>🖨️ Ticket (80mm)</span>
                </a>

                <a href="{{ route('ventas.imprimir', ['venta' => $venta->id, 'formato' => 'a4']) }}" target="_blank"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 px-3 rounded-lg shadow-sm transition flex items-center gap-1.5 uppercase">
                    <span>📄 Imprimir A4</span>
                </a>

                @if($venta->estado === 'completada' || $venta->estado === 'remito' || $venta->estado === 'presupuesto')
                    <button type="button" @click="modalAnular = true"
                            class="bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 font-bold text-xs py-2 px-3 rounded-lg transition uppercase flex items-center gap-1">
                        <span>🚫 Anular</span>
                    </button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/60 border-l-4 border-emerald-500 rounded-r-lg text-emerald-800 dark:text-emerald-200 text-xs font-bold shadow-xs">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 bg-rose-50 dark:bg-rose-950/60 border-l-4 border-rose-500 rounded-r-lg text-rose-800 dark:text-rose-200 text-xs font-bold shadow-xs">
                {{ session('error') }}
            </div>
        @endif

        @if($venta->estado === 'anulada')
            <div class="p-3 bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 rounded-xl text-rose-800 dark:text-rose-200 text-xs">
                <span class="font-black uppercase block mb-1">Comprobante Anulado</span>
                <p>{{ $venta->observaciones }}</p>
            </div>
        @endif

        <!-- Tarjetas de Información Consolidada -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            
            {{-- Tarjeta 1: Cliente --}}
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-2">
                <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">Cliente de la Operación</span>
                @if($venta->es_consumidor_final)
                    <div>
                        <span class="text-sm font-black text-slate-800 dark:text-white uppercase block">CONSUMIDOR FINAL</span>
                        <span class="text-xs text-slate-500">Venta anónima de mostrador</span>
                    </div>
                @else
                    <div>
                        <a href="{{ route('clientes.show', $venta->id_cliente) }}" class="text-sm font-black text-indigo-700 dark:text-indigo-400 hover:underline uppercase block">
                            {{ $venta->cliente_nombre }}
                        </a>
                        <div class="text-xs text-slate-500 font-mono mt-0.5 space-y-0.5">
                            <div>DNI: {{ $venta->cliente_documento ?? $venta->cliente?->dni ?? '-' }}</div>
                            @if($venta->cliente?->nro_cuenta)
                                <div>Cuenta Corriente: {{ $venta->cliente->nro_cuenta }}</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- Tarjeta 2: Sucursal y Operador --}}
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-2">
                <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">Terminal de Emisión</span>
                <div>
                    <span class="text-sm font-black text-slate-800 dark:text-white uppercase block">
                        {{ $venta->sucursal?->nombre }}
                    </span>
                    <p class="text-xs text-slate-500">
                        {{ $venta->sucursal?->direccion }} ({{ $venta->sucursal?->provincia?->nombre ?? 'N/A' }})
                    </p>
                    <div class="text-[11px] text-slate-600 dark:text-slate-400 mt-1 space-y-0.5">
                        <div><strong class="text-slate-700 dark:text-slate-300">Fiscal:</strong> {{ $venta->sucursal?->razon_social }}</div>
                        <div><strong class="text-slate-700 dark:text-slate-300">CUIT:</strong> <span class="font-mono">{{ $venta->sucursal?->cuit }}</span> • {{ $venta->sucursal?->condicion_iva }}</div>
                    </div>
                    <div class="text-[11px] text-slate-600 dark:text-slate-400 font-bold mt-2 pt-1 border-t border-slate-100 dark:border-slate-700">
                        Cajero: <span class="text-slate-800 dark:text-slate-200">{{ $venta->usuario?->name ?? 'Operador' }}</span>
                    </div>
                </div>
            </div>

            {{-- Tarjeta 3: Resumen Financiero --}}
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs space-y-2">
                <span class="text-[10px] font-black uppercase text-slate-400 block tracking-wider">Forma de Pago & Totales</span>
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-400">Medio:</span>
                        <span class="font-black text-xs uppercase px-2 py-0.5 rounded {{ $venta->medio_pago === 'credito_personal' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $venta->medio_pago_formateado }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs mt-1">
                        <span class="text-slate-500">Subtotal:</span>
                        <span class="font-mono tabular-nums text-slate-700 dark:text-slate-300">$ {{ number_format((float)$venta->subtotal, 2, ',', '.') }}</span>
                    </div>

                    @if((float)$venta->descuento > 0)
                        <div class="flex items-center justify-between text-xs text-emerald-600 dark:text-emerald-400 font-bold">
                            <span>Descuento / Bonificación:</span>
                            <span class="font-mono tabular-nums">-$ {{ number_format((float)$venta->descuento, 2, ',', '.') }}</span>
                        </div>
                    @endif

                    @if((float)$venta->recargo > 0)
                        <div class="flex items-center justify-between text-xs text-indigo-600">
                            <span>Recargo Financiación:</span>
                            <span class="font-mono tabular-nums font-bold">$ {{ number_format((float)$venta->recargo, 2, ',', '.') }}</span>
                        </div>
                    @endif

                    @if($venta->esExentoIva())
                        <div class="flex items-center justify-between text-xs text-teal-700 dark:text-teal-400">
                            <span>Tratamiento Fiscal:</span>
                            <span class="font-bold">Exento de IVA (Ley 19.640)</span>
                        </div>
                    @elseif($venta->isFacturaA())
                        <div class="flex items-center justify-between text-xs text-slate-600 dark:text-slate-400">
                            <span>Neto Gravado:</span>
                            <span class="font-mono tabular-nums font-bold">$ {{ number_format($venta->netoGravado(), 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-indigo-700 dark:text-indigo-400">
                            <span>IVA Liquidado (21%):</span>
                            <span class="font-mono tabular-nums font-bold">$ {{ number_format($venta->ivaLiquidado(), 2, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-base font-black pt-1.5 border-t border-slate-200 dark:border-slate-700 mt-1">
                        <span class="text-slate-900 dark:text-white uppercase">Total:</span>
                        <span class="font-mono tabular-nums text-emerald-700 dark:text-emerald-400 text-lg">
                            $ {{ number_format((float)$venta->total, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        {{-- Tarjeta de Crédito Personal Asociado (Si aplica) --}}
        @if($venta->credito)
            <div class="bg-indigo-50/70 dark:bg-indigo-950/40 p-4 rounded-xl border border-indigo-200 dark:border-indigo-800 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-indigo-200 dark:border-indigo-800/80 pb-2.5 mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📄</span>
                        <div>
                            <span class="text-xs font-black uppercase text-indigo-950 dark:text-indigo-200 block">
                                Crédito Personal Generado: {{ $venta->credito->full_identificador }}
                            </span>
                            <span class="text-[11px] text-indigo-700 dark:text-indigo-400">
                                Liquidado en {{ $venta->cuotas_credito }} cuota(s) de ${{ number_format((float)$venta->valor_cuota_credito, 2, ',', '.') }} • Lote #{{ $venta->credito->presentacion?->numero ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('creditos.pagare', $venta->credito->id) }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-lg transition uppercase">
                            Imprimir Pagaré 📋
                        </a>
                        <a href="{{ route('creditos.show', $venta->credito->id) }}" class="inline-flex items-center px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition uppercase">
                            Ver Ficha del Crédito →
                        </a>
                    </div>
                </div>

                {{-- Tabla de Cuotas --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-[10px] uppercase font-bold text-slate-500 border-b border-indigo-200 dark:border-indigo-800">
                                <th class="py-1 px-2">Cuota</th>
                                <th class="py-1 px-2 text-right">Valor Base</th>
                                <th class="py-1 px-2 text-right">Intereses</th>
                                <th class="py-1 px-2 text-right">Cobrado (Entregó)</th>
                                <th class="py-1 px-2 text-right">Saldo Actual</th>
                                <th class="py-1 px-2 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-indigo-100 dark:divide-indigo-900/60 font-mono">
                            @foreach($venta->credito->cuotas as $cuota)
                                <tr>
                                    <td class="py-1.5 px-2 font-bold font-sans">Cuota {{ $cuota->num_cuota }} / {{ $venta->credito->cantidad_cuotas }}</td>
                                    <td class="py-1.5 px-2 text-right">$ {{ number_format((float)$cuota->valor_base, 2, ',', '.') }}</td>
                                    <td class="py-1.5 px-2 text-right text-amber-600">$ {{ number_format((float)$cuota->intereses, 2, ',', '.') }}</td>
                                    <td class="py-1.5 px-2 text-right text-emerald-600 font-bold">$ {{ number_format((float)$cuota->entrego, 2, ',', '.') }}</td>
                                    <td class="py-1.5 px-2 text-right font-black {{ (float)$cuota->saldo_actual > 0 ? 'text-slate-900 dark:text-white' : 'text-slate-400' }}">
                                        $ {{ number_format((float)$cuota->saldo_actual, 2, ',', '.') }}
                                    </td>
                                    <td class="py-1.5 px-2 text-center font-sans">
                                        @if((float)$cuota->saldo_actual <= 0)
                                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded">Saldada</span>
                                        @else
                                            <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Tabla de Renglones / Artículos Facturados -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-3 bg-slate-100 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-xs font-black uppercase text-slate-700 dark:text-slate-200 tracking-wider">
                    Detalle de Mercaderías / Ítems Facturados ({{ $venta->items->count() }})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 text-[10px] uppercase font-bold">
                            <th class="py-2.5 px-3 w-12">#</th>
                            <th class="py-2.5 px-3">Código</th>
                            <th class="py-2.5 px-3">Descripción</th>
                            <th class="py-2.5 px-3 text-center">Cantidad</th>
                            <th class="py-2.5 px-3 text-right">P. Unitario</th>
                            <th class="py-2.5 px-3 text-center">Desc / Promo</th>
                            <th class="py-2.5 px-3 text-right">Subtotal</th>
                            <th class="py-2.5 px-3 text-center">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($venta->items as $idx => $it)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition">
                                <td class="py-2 px-3 font-mono text-slate-400 text-[10px]">{{ $idx + 1 }}</td>
                                <td class="py-2 px-3 font-mono text-slate-600 dark:text-slate-300 font-bold">
                                    {{ $it->codigo_producto ?? '-' }}
                                </td>
                                <td class="py-2 px-3">
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $it->descripcion }}</span>
                                    @if($it->producto)
                                        <a href="{{ route('productos.show', $it->producto->id) }}" class="text-[10px] text-indigo-600 dark:text-indigo-400 hover:underline">
                                            Ver Ficha de Stock →
                                        </a>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-center font-mono font-bold text-slate-900 dark:text-white">
                                    {{ number_format((float)$it->cantidad, 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-slate-700 dark:text-slate-300">
                                    $ {{ number_format((float)$it->precio_unitario, 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-center">
                                    @if((float)$it->descuento > 0 || (float)$it->descuento_porcentaje > 0)
                                        @if((float)$it->descuento_porcentaje >= 100 || (float)$it->subtotal <= 0)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300">
                                                🎁 2x1 / 100%
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300">
                                                -{{ number_format((float)$it->descuento_porcentaje, 0) }}% (-${{ number_format((float)$it->descuento, 2, ',', '.') }})
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-[10px]">-</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-black tabular-nums">
                                    @if((float)$it->subtotal <= 0 && ((float)$it->descuento > 0 || (float)$it->descuento_porcentaje >= 100))
                                        <span class="text-emerald-600 dark:text-emerald-400">$ 0,00</span>
                                        <span class="block text-[10px] text-slate-400 line-through">$ {{ number_format((float)$it->cantidad * (float)$it->precio_unitario, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-slate-900 dark:text-white">$ {{ number_format((float)$it->subtotal, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-center">
                                    @if($it->descuenta_stock)
                                        <span class="inline-flex items-center text-[10px] font-bold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-300" title="Descontado del stock de Ushuaia">
                                            ✓ Descontado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[10px] font-bold text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">
                                            No controlado
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL DE ANULACIÓN DE VENTA -->
        <div x-show="modalAnular" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4"
                 @click.away="modalAnular = false">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                    <h3 class="text-base font-black text-rose-600 uppercase flex items-center gap-2">
                        <span>🚫 Anular Factura #{{ $venta->comprobante }}</span>
                    </h3>
                    <button type="button" @click="modalAnular = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300">
                    Al anular la factura se revertirá el stock de las mercaderías (en Ushuaia) y se dará de baja el crédito asociado si no registra cobros.
                </p>

                <form action="{{ route('ventas.anular', $venta->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label for="motivo" class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase mb-1">
                            Motivo de la anulación (Obligatorio):
                        </label>
                        <textarea name="motivo" id="motivo" rows="3" required
                                  placeholder="Indique el motivo de la anulación o devolución..."
                                  class="w-full text-xs p-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-rose-500 focus:border-rose-500"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="modalAnular = false"
                                class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-lg uppercase">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="bg-rose-600 hover:bg-rose-700 text-white font-black text-xs py-2 px-5 rounded-lg shadow uppercase">
                            Confirmar Anulación
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Facturar Remito -->
        @if($venta->isRemito() && $venta->estado === 'remito')
        <div x-show="modalFacturarRemito" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4"
                 @click.away="modalFacturarRemito = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                    <h3 class="text-base font-black text-amber-600 uppercase flex items-center gap-2">
                        <span>🧾 Facturar Remito #{{ $venta->comprobante }}</span>
                    </h3>
                    <button type="button" @click="modalFacturarRemito = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-300">
                    Se generará la factura fiscal correspondiente. La mercadería ya fue descontada de inventario, por lo que este paso <strong>NO alterará el stock</strong> nuevamente.
                </p>

                <form action="{{ route('ventas.facturar-remito', $venta->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-200 uppercase mb-1">
                            Tipo de Factura a emitir:
                        </label>
                        <select name="tipo_comprobante" x-model="tipoFacturaRemito" 
                                class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-bold focus:ring-amber-500 focus:border-amber-500">
                            <option value="FACTURA_B">Factura B (Consumidor Final)</option>
                            <option value="FACTURA_A">Factura A (Responsable Inscripto con CUIT)</option>
                            <option value="FACTURA_C">Factura C (Régimen Simplificado)</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" @click="modalFacturarRemito = false"
                                class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-lg uppercase">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="bg-amber-600 hover:bg-amber-700 text-white font-black text-xs py-2 px-5 rounded-lg shadow uppercase">
                            Confirmar Emisión
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
