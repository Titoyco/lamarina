<x-app-layout>
    <div class="max-w-5xl mx-auto py-3" x-data="{ modalAnular: false }">
        <!-- Header y Navegación -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-3 gap-2">
            <div class="flex items-center space-x-3">
                <a href="{{ route('recibos.index') }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center space-x-2">
                        <span>Recibo {{ $recibo->numero_formateado }}</span>
                        @if($recibo->anulado)
                            <span class="bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 font-black text-[10px] px-2 py-0.5 rounded border border-rose-300 dark:border-rose-800 uppercase tracking-wider">
                                ⛔ ANULADO
                            </span>
                        @else
                            <span class="bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 font-black text-[10px] px-2 py-0.5 rounded border border-emerald-300 dark:border-emerald-800 uppercase tracking-wider">
                                ✓ ACTIVO
                            </span>
                        @endif
                    </h1>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('recibos.imprimir', $recibo->id) }}" target="_blank"
                   class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-3.5 rounded-md shadow-sm transition flex items-center space-x-1 uppercase">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Imprimir Comprobante</span>
                </a>

                @if(!$recibo->anulado)
                    <button type="button" @click="modalAnular = true"
                            class="bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-600 hover:text-white text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900 font-bold text-xs py-1.5 px-3 rounded-md transition shadow-xs flex items-center space-x-1 uppercase">
                        <span>✕ Anular Recibo</span>
                    </button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-emerald-50 dark:bg-emerald-950/60 border-l-4 border-emerald-500 p-3 rounded text-emerald-800 dark:text-emerald-200 text-xs font-bold shadow-xs">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-rose-50 dark:bg-rose-950/60 border-l-4 border-rose-500 p-3 rounded text-rose-800 dark:text-rose-200 text-xs font-bold shadow-xs">
                {{ session('error') }}
            </div>
        @endif

        @if($recibo->anulado)
            <!-- Banner de Alerta de Anulación -->
            <div class="mb-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 rounded-xl p-3.5 text-xs text-rose-900 dark:text-rose-200 shadow-sm flex items-start gap-3">
                <span class="text-xl">⚠️</span>
                <div>
                    <span class="font-black uppercase tracking-wider block">Este recibo fue anulado</span>
                    <p class="mt-0.5">
                        Fecha: <strong>{{ \Carbon\Carbon::parse($recibo->fecha_anulacion)->format('d/m/Y H:i') }}</strong> 
                        | Usuario: <strong>{{ $recibo->userAnulacion?->name ?? 'Operador' }}</strong>
                    </p>
                    <p class="mt-1 text-rose-800 dark:text-rose-300 font-mono italic bg-white/70 dark:bg-slate-900/80 p-1.5 rounded border border-rose-200 dark:border-rose-900">
                        Motivo: "{{ $recibo->motivo_anulacion }}"
                    </p>
                    <p class="text-[10px] text-rose-600 dark:text-rose-400 mt-1">
                        Las cuotas y saldos vinculados fueron compensados y reabiertos automáticamente en el estado de cuenta.
                    </p>
                </div>
            </div>
        @endif

        <!-- Tarjeta Principal del Recibo -->
        <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-4">
            <!-- Encabezado con Membrete Comercial -->
            <div class="bg-slate-900 dark:bg-slate-950 text-white p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-800">
                <div>
                    <span class="text-[10px] font-black uppercase text-blue-300 tracking-wider block">Comprobante Oficial de Cobranza</span>
                    <h2 class="text-2xl font-black font-mono tracking-tight">{{ $recibo->numero_formateado }}</h2>
                    <p class="text-xs text-slate-300 mt-0.5">
                        {{ $recibo->sucursal?->nombre }} ({{ $recibo->sucursal?->codigo_sucursal }}) 
                        — {{ $recibo->sucursal?->provincia?->nombre ?? 'Chubut' }}
                    </p>
                </div>

                <div class="bg-slate-800/80 dark:bg-slate-900/90 border border-slate-700 p-3 rounded-lg text-right">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Total Cobrado</span>
                    <span class="font-mono font-black text-2xl text-emerald-400 tabular-nums">
                        $ {{ number_format($recibo->importe_total, 2, ',', '.') }}
                    </span>
                    @if((float)$recibo->saldo_a_favor_generado > 0)
                        <div class="text-[10px] text-amber-300 font-bold mt-0.5">
                            (Incluye $ {{ number_format($recibo->saldo_a_favor_generado, 2, ',', '.') }} a Saldo a Favor)
                        </div>
                    @endif
                    <span class="text-[10px] text-slate-300 block mt-0.5">
                        Medio: <strong>{{ $recibo->medio_pago }}</strong>
                    </span>
                </div>
            </div>

            <!-- Fila de Metadatos: Cliente y Operación -->
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200 dark:divide-slate-700 bg-slate-50/50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700 text-xs">
                <!-- Columna Izquierda: Datos del Cliente -->
                <div class="p-4 space-y-1.5">
                    <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider block">Datos del Titular</span>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('clientes.show', $recibo->id_cliente) }}" class="font-black text-slate-900 dark:text-white uppercase hover:text-blue-700 dark:hover:text-blue-400 hover:underline">
                            {{ $recibo->cliente?->nombre }}
                        </a>
                        <span class="bg-blue-100 dark:bg-blue-950/80 text-blue-900 dark:text-blue-300 font-mono font-bold text-[10px] px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800">
                            DNI: {{ $recibo->cliente?->dni }}
                        </span>
                    </div>
                    @if($recibo->cliente?->nro_cuenta)
                        <p class="text-slate-600 dark:text-slate-400">Nro. de Cuenta: <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $recibo->cliente->nro_cuenta }}</span></p>
                    @endif
                    <p class="text-slate-600 dark:text-slate-400">
                        Domicilio: {{ $recibo->cliente?->domicilio ?? 'S/D' }} 
                        ({{ $recibo->cliente?->localidad?->nombre ?? 'S/L' }})
                    </p>
                    <div class="pt-1 flex items-center space-x-3">
                        <a href="{{ route('clientes.estado-cuenta', $recibo->id_cliente) }}" class="inline-flex items-center text-[11px] font-bold text-blue-700 dark:text-blue-400 hover:underline">
                            <span>Ver Estado de Cuenta Completo →</span>
                        </a>
                        @if((float)($recibo->cliente?->saldo_a_favor ?? 0) > 0)
                            <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                Saldo a Favor actual: $ {{ number_format($recibo->cliente->saldo_a_favor, 2, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Columna Derecha: Datos de Emisión y Operador -->
                <div class="p-4 space-y-1.5">
                    <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider block">Datos de Emisión</span>
                    <p class="text-slate-700 dark:text-slate-300">
                        Fecha y Hora: <strong class="font-mono text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($recibo->fecha)->format('d/m/Y H:i') }}</strong>
                    </p>
                    <p class="text-slate-700 dark:text-slate-300">
                        Operador Responsable: <strong class="text-slate-900 dark:text-white">{{ $recibo->user?->name ?? 'Operador General' }}</strong>
                    </p>
                    <p class="text-slate-700 dark:text-slate-300">
                        Puesto de Cobro: <strong class="text-slate-900 dark:text-white">{{ $recibo->sucursal?->nombre }}</strong>
                    </p>

                    @if($recibo->observaciones)
                        <div class="mt-2 bg-white dark:bg-slate-800 p-2 rounded border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-[11px] italic">
                            "{{ $recibo->observaciones }}"
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabla de Renglones Imputados -->
            <div class="p-4">
                <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider block mb-2">
                    Detalle de Imputaciones a Cuotas y Cuenta Corriente
                </span>

                <div class="overflow-x-auto border border-slate-200 dark:border-slate-700 rounded-lg">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 dark:bg-slate-900/80 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700">
                                <th class="py-2 px-3">Crédito / Cuenta</th>
                                <th class="py-2 px-2 text-center">Cuota</th>
                                <th class="py-2 px-3">Concepto Imputado</th>
                                <th class="py-2 px-3 text-right">Saldo Previo</th>
                                <th class="py-2 px-3 text-right">Monto Cobrado</th>
                                <th class="py-2 px-3 text-right">Saldo Restante / Actual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                            @foreach($recibo->detalles as $det)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors {{ !$det->id_credito ? 'bg-emerald-50/30 dark:bg-emerald-950/20' : '' }}">
                                    <td class="py-2.5 px-3">
                                        @if($det->credito)
                                            <a href="{{ route('creditos.show', $det->credito->id) }}" class="font-mono font-bold text-blue-700 dark:text-blue-400 hover:underline">
                                                {{ $det->credito->full_identificador }}
                                            </a>
                                        @elseif($det->id_credito)
                                            <span class="font-mono text-slate-500 dark:text-slate-400">CR-#{{ $det->id_credito }}</span>
                                        @else
                                            <span class="font-mono font-black text-emerald-800 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-950/80 px-1.5 py-0.5 rounded text-[10px] border border-emerald-300 dark:border-emerald-800">
                                                CTA CTE
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-mono font-bold text-slate-700 dark:text-slate-300">
                                        {{ $det->num_cuota ? ('#' . $det->num_cuota) : '—' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-slate-700 dark:text-slate-200 font-medium">
                                        {{ $det->concepto }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-600 dark:text-slate-400 tabular-nums">
                                        $ {{ number_format($det->saldo_anterior, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-700 dark:text-emerald-400 tabular-nums">
                                        $ {{ number_format($det->importe, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white tabular-nums">
                                        $ {{ number_format($det->saldo_nuevo, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-100/80 dark:bg-slate-900/80 font-bold border-t-2 border-slate-300 dark:border-slate-700">
                                <td colspan="4" class="py-2 px-3 text-right uppercase text-[10px] text-slate-600 dark:text-slate-400">
                                    Total del Recibo:
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-black text-emerald-800 dark:text-emerald-400 text-sm tabular-nums">
                                    $ {{ number_format($recibo->importe_total, 2, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL DE ANULACIÓN -->
        <div x-show="modalAnular" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4"
             style="display: none;">
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden" @click.away="modalAnular = false">
                <div class="bg-rose-50 dark:bg-rose-950/60 border-b border-rose-200 dark:border-rose-900 p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-2 text-rose-800 dark:text-rose-200">
                        <span class="text-xl">⚠️</span>
                        <h3 class="font-black text-sm uppercase">Confirmar Anulación de Recibo</h3>
                    </div>
                    <button type="button" @click="modalAnular = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-sm">✕</button>
                </div>

                <form action="{{ route('recibos.anular', $recibo->id) }}" method="POST" class="p-4 space-y-4">
                    @csrf
                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        Está a punto de anular el recibo <strong>#{{ $recibo->numero_recibo }}</strong> por valor de 
                        <strong class="font-mono text-emerald-700 dark:text-emerald-400">$ {{ number_format($recibo->importe_total, 2, ',', '.') }}</strong>.
                    </p>

                    <div class="bg-amber-50 dark:bg-amber-950/50 p-3 rounded-lg border border-amber-200 dark:border-amber-900 text-[11px] text-amber-800 dark:text-amber-300 space-y-1">
                        <p class="font-bold uppercase">Consecuencias de la anulación:</p>
                        <p>• Los importes abonados se restituirán al saldo de las cuotas.</p>
                        <p>• Los créditos cancelados por este recibo serán reabiertos.</p>
                        <p>• El saldo del cliente se incrementará con el monto anulado.</p>
                        @if((float)$recibo->saldo_a_favor_generado > 0)
                            <p class="text-rose-700 dark:text-rose-400 font-bold">• Se descontarán $ {{ number_format($recibo->saldo_a_favor_generado, 2, ',', '.') }} que habían sido acreditados a Saldo a Favor en cuenta corriente.</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">
                            Motivo de la Anulación (Obligatorio)
                        </label>
                        <textarea name="motivo" required rows="3" placeholder="Ej: Error en medio de pago, cobro duplicado, cheque rechazado..."
                                  class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-rose-500 focus:ring-rose-500 py-1.5 px-2.5"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="modalAnular = false"
                                class="bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs py-1.5 px-3 rounded-lg transition uppercase">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="bg-rose-600 hover:bg-rose-700 text-white font-black text-xs py-1.5 px-4 rounded-lg shadow-sm transition uppercase">
                            Confirmar Anulación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
