<x-app-layout>
    <!-- Header Compacto -->
    <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-3">
        <div class="flex items-center space-x-3">
            <a href="{{ url()->previous() }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                    <span>Crédito <span class="font-mono text-blue-700 dark:text-blue-400">#{{ $credito->full_identificador }}</span></span>
                    @if($credito->fecha_cancelacion)
                        <span class="bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-black px-2 py-0.5 rounded border border-slate-300 dark:border-slate-600 uppercase">CANCELADO</span>
                    @else
                        <span class="bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 text-[10px] font-black px-2 py-0.5 rounded border border-amber-200 dark:border-amber-800 uppercase">ABIERTO</span>
                    @endif
                </h1>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @php
                $tienePagos = $credito->cuotas->contains(function ($c) {
                    return (float) $c->entrego > 0 || $c->fecha_cobro !== null;
                });
            @endphp

            @if(! $tienePagos)
                <button type="button"
                        x-data=""
                        x-on:click="$dispatch('open-modal', 'modal-anular-credito')"
                        class="bg-rose-50 dark:bg-rose-950/50 hover:bg-rose-600 hover:text-white text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-800 px-3 py-1.5 rounded-md text-xs font-bold shadow-sm transition-all uppercase flex items-center">
                    ⛔ Anular Crédito
                </button>
            @else
                <button type="button"
                        disabled
                        title="No se puede anular un crédito con cobros registrados. Revierta los pagos primero."
                        class="bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-md text-xs font-bold cursor-not-allowed uppercase flex items-center">
                    ⛔ Anular Crédito
                </button>
            @endif

            <a href="{{ route('creditos.pagare', $credito) }}" target="_blank" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 px-3 py-1.5 rounded-md text-xs font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-all uppercase flex items-center">
                📄 Reimprimir Pagaré
            </a>
        </div>
    </div>

    <!-- Panel de Datos Rápidos y Cinta de KPIs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
        
        <!-- Datos del Cliente y Sucursal -->
        <div class="bg-slate-50 dark:bg-slate-800/80 p-3 rounded-md border border-slate-200 dark:border-slate-700/80 shadow-sm flex flex-col justify-between">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block mb-0.5">Cliente Titular</span>
                    <a href="{{ route('clientes.show', $credito->cliente->id) }}" 
                       class="text-xs font-black text-blue-700 dark:text-blue-400 hover:underline uppercase truncate block"
                       title="Ver legajo de {{ $credito->cliente->nombre }}">
                        {{ $credito->cliente->nombre }} ↗
                    </a>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <a href="{{ route('clientes.show', $credito->cliente->id) }}" class="text-[9px] font-bold text-slate-400 dark:text-slate-500 hover:underline">
                            DNI: {{ $credito->cliente->dni }}
                        </a>
                        <span class="text-slate-300 dark:text-slate-600 text-[9px]">•</span>
                        <a href="{{ route('clientes.estado-cuenta', $credito->cliente->id) }}" class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            Estado de Cuenta
                        </a>
                    </div>
                </div>
                <div>
                    <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block mb-0.5">Autorizado</span>
                    @if($credito->autorizado && $credito->autorizado->cliente)
                        <a href="{{ route('clientes.show', $credito->autorizado->cliente->id) }}" 
                           class="text-xs font-bold text-blue-700 dark:text-blue-400 hover:underline uppercase truncate block"
                           title="Ver legajo de {{ $credito->autorizado->cliente->nombre }}">
                            {{ $credito->autorizado->cliente->nombre }} ↗
                        </a>
                    @else
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase truncate">
                            TITULAR
                        </div>
                    @endif
                    <div class="text-[9px] font-bold text-blue-600 dark:text-blue-400 mt-0.5">SUC: {{ $credito->sucursal->nombre ?? 'N/D' }}</div>
                </div>
                <div class="col-span-2 border-t border-slate-200 dark:border-slate-700 pt-1.5 mt-1 flex justify-between items-center text-xs">
                    <div>
                        <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Otorgado:</span>
                        <span class="text-[10px] font-bold text-slate-800 dark:text-slate-200">{{ $credito->fecha_credito ? \Carbon\Carbon::parse($credito->fecha_credito)->format('d/m/Y') : 'N/D' }}</span>
                    </div>
                    @if($credito->venta)
                        <div>
                            <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Factura:</span>
                            <a href="{{ route('ventas.show', $credito->venta->id) }}" class="text-[10px] font-black text-indigo-600 dark:text-indigo-400 hover:underline font-mono">
                                #{{ $credito->venta->comprobante }} ↗
                            </a>
                        </div>
                    @endif
                    @if($credito->fecha_cancelacion)
                        <div>
                            <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Cancelado:</span>
                            <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400">{{ \Carbon\Carbon::parse($credito->fecha_cancelacion)->format('d/m/Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cinta KPI Financiera (Ocupa 2 columnas) -->
        <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-2">
            <div class="bg-white dark:bg-slate-800/90 p-3 rounded-md border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-center">
                <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Importe Original</span>
                <div class="text-lg font-black text-slate-900 dark:text-slate-100 font-mono tabular-nums mt-0.5">$ {{ number_format($credito->importe_original, 2, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800/90 p-3 rounded-md border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-center">
                <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Total Intereses</span>
                <div class="text-lg font-black text-red-800 dark:text-red-400 font-mono tabular-nums mt-0.5">$ {{ number_format($totalIntereses, 2, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-slate-800/90 p-3 rounded-md border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-center">
                <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Total Cobrado</span>
                <div class="text-lg font-black text-slate-900 dark:text-slate-100 font-mono tabular-nums mt-0.5">$ {{ number_format($totalEntrego, 2, ',', '.') }}</div>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-950/40 p-3 rounded-md border border-emerald-200 dark:border-emerald-800/80 shadow-sm flex flex-col justify-center">
                <span class="text-[9px] font-bold text-emerald-800 dark:text-emerald-300 uppercase block">Saldo Pendiente</span>
                <div class="text-lg font-black text-emerald-700 dark:text-emerald-400 font-mono tabular-nums mt-0.5">$ {{ number_format($totalSaldo, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <!-- Data-Grid de Cuotas -->
    <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm overflow-hidden mb-4">
        <div class="bg-slate-100 dark:bg-slate-900/80 border-b border-slate-300 dark:border-slate-700 px-3 py-2 flex justify-between items-center">
            <h2 class="font-bold text-[10px] text-slate-700 dark:text-slate-300 uppercase tracking-wider">Plan de Cuotas ({{ $credito->cuotas->count() }})</h2>
        </div>

        <table class="w-full text-left border-collapse font-sans text-xs">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300 dark:border-slate-700">
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-center w-20">N° Cuota</th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-28">Valor Base</th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-28">Intereses</th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-28">Entregó</th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-32">Saldo Actual</th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-center w-24">Fecha Cobro</th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-center w-24">Medio Pago</th>
                    <th class="py-1.5 px-3 text-center w-28">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                @forelse($credito->cuotas as $cuota)
                    <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-700/50 transition-colors bg-white dark:bg-slate-800/50">
                        <td class="py-1.5 px-3 text-center border-r border-slate-200/60 dark:border-slate-700/50">
                            <span class="font-mono font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700/80 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-600 text-[10px]">
                                {{ $cuota->num_cuota }} / {{ $credito->cantidad_cuotas }}
                            </span>
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono font-bold border-r border-slate-200/60 dark:border-slate-700/50 tabular-nums text-slate-900 dark:text-slate-100">
                            $ {{ number_format($cuota->valor_base, 2, ',', '.') }}
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono font-bold border-r border-slate-200/60 dark:border-slate-700/50 tabular-nums text-red-800 dark:text-red-400">
                            $ {{ number_format($cuota->intereses, 2, ',', '.') }}
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono font-bold border-r border-slate-200/60 dark:border-slate-700/50 tabular-nums text-slate-900 dark:text-slate-100">
                            $ {{ number_format($cuota->entrego, 2, ',', '.') }}
                        </td>
                        <td class="py-1.5 px-3 text-right font-mono font-black border-r border-slate-200/60 dark:border-slate-700/50 tabular-nums text-emerald-700 dark:text-emerald-400">
                            $ {{ number_format($cuota->saldo_actual, 2, ',', '.') }}
                        </td>
                        <td class="py-1.5 px-3 text-center font-mono border-r border-slate-200/60 dark:border-slate-700/50 text-[11px] text-slate-600 dark:text-slate-400">
                            {{ $cuota->fecha_cobro ? \Carbon\Carbon::parse($cuota->fecha_cobro)->format('d/m/Y') : '-' }}
                        </td>
                        <td class="py-1.5 px-3 text-center border-r border-slate-200/60 dark:border-slate-700/50">
                            @if($cuota->medio_pago)
                                <span class="text-blue-700 dark:text-blue-400 font-bold text-[9px] uppercase">{{ $cuota->medio_pago }}</span>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 text-[10px]">-</span>
                            @endif
                        </td>
                        <td class="py-1 px-3 text-center whitespace-nowrap">
                            @if((float) $cuota->entrego > 0 || $cuota->fecha_cobro !== null)
                                <button type="button"
                                        x-data=""
                                        x-on:click="$dispatch('open-modal', 'modal-anular-pago-{{ $cuota->id }}')"
                                        class="bg-amber-50 dark:bg-amber-950/50 hover:bg-amber-600 hover:text-white text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800 px-2 py-0.5 rounded text-[10px] font-bold transition-all uppercase shadow-xs">
                                    ↩ Anular Pago
                                </button>
                            @else
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 italic font-medium">Impaga</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-6 text-center text-slate-400 dark:text-slate-500 font-medium text-xs italic bg-slate-50 dark:bg-slate-900/40">
                            No hay cuotas registradas para este crédito.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modales de Anulación Operativa -->
    {{-- Modal Anular Crédito Completo --}}
    @if(! $tienePagos)
        <x-modal name="modal-anular-credito" maxWidth="md">
            <form method="POST" action="{{ route('creditos.anular', $credito) }}" class="p-4 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                @csrf
                @method('DELETE')

                <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700 mb-3">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase flex items-center space-x-1.5">
                        <span class="text-rose-600 text-sm">⛔</span>
                        <span>Anular Crédito #{{ $credito->full_identificador }}</span>
                    </h3>
                    <button type="button" x-on:click="$dispatch('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm font-bold">&times;</button>
                </div>

                <div class="space-y-3">
                    <div class="bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 rounded p-2.5 text-[11px] text-rose-800 dark:text-rose-200 space-y-1">
                        <p class="font-bold">⚠️ Atención: Acción destructiva irreversible.</p>
                        <p>Se eliminará el crédito de <strong>${{ number_format($credito->importe_original, 2, ',', '.') }}</strong> junto a sus {{ $credito->cantidad_cuotas }} cuotas asociadas. Se registrará una novedad formal en el legajo del cliente.</p>
                    </div>

                    <div>
                        <label for="motivo_credito" class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase block mb-1">
                            Motivo de la anulación <span class="text-rose-600">*</span>
                        </label>
                        <textarea id="motivo_credito"
                                  name="motivo"
                                  rows="2"
                                  required
                                  minlength="5"
                                  placeholder="Ej: Error de carga en mostrador / Solicitud cancelada por el cliente..."
                                  class="w-full text-xs rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-rose-500 focus:ring-rose-500 p-2 shadow-sm resize-none"></textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-2">
                    <button type="button"
                            x-on:click="$dispatch('close')"
                            class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-1.5 px-3 rounded shadow-sm transition uppercase">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs py-1.5 px-3 rounded shadow-sm transition uppercase">
                        Confirmar Anulación
                    </button>
                </div>
            </form>
        </x-modal>
    @endif

    {{-- Modales Anular Pago por Cuota --}}
    @foreach($credito->cuotas as $cuota)
        @if((float) $cuota->entrego > 0 || $cuota->fecha_cobro !== null)
            <x-modal name="modal-anular-pago-{{ $cuota->id }}" maxWidth="md">
                <form method="POST" action="{{ route('creditos.cuotas.anular-pago', $cuota) }}" class="p-4 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    @csrf

                    <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-700 mb-3">
                        <h3 class="text-xs font-bold text-slate-800 dark:text-white uppercase flex items-center space-x-1.5">
                            <span class="text-amber-600 text-sm">↩</span>
                            <span>Anular Pago Cuota #{{ $cuota->num_cuota }}</span>
                        </h3>
                        <button type="button" x-on:click="$dispatch('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-sm font-bold">&times;</button>
                    </div>

                    <div class="space-y-3">
                        <div class="bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800 rounded p-2.5 text-[11px] text-amber-900 dark:text-amber-200 space-y-1">
                            <p class="font-bold">Compensación de cobranza:</p>
                            <p>Se revertirá el pago de <strong class="font-mono text-slate-900 dark:text-white">${{ number_format($cuota->entrego, 2, ',', '.') }}</strong>, restaurando el saldo exigible a <strong class="font-mono text-emerald-800 dark:text-emerald-300">${{ number_format($cuota->saldo_actual + $cuota->entrego, 2, ',', '.') }}</strong>. Si el crédito estaba cancelado, será reabierto automáticamente.</p>
                        </div>

                        <div>
                            <label for="motivo_pago_{{ $cuota->id }}" class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase block mb-1">
                                Motivo de la anulación <span class="text-amber-600">*</span>
                            </label>
                            <textarea id="motivo_pago_{{ $cuota->id }}"
                                      name="motivo"
                                      rows="2"
                                      required
                                      minlength="5"
                                      placeholder="Ej: Cobro duplicado / Imputación errónea..."
                                      class="w-full text-xs rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:border-amber-500 focus:ring-amber-500 p-2 shadow-sm resize-none"></textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-2">
                        <button type="button"
                                x-on:click="$dispatch('close')"
                                class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-1.5 px-3 rounded shadow-sm transition uppercase">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-1.5 px-3 rounded shadow-sm transition uppercase">
                            Revertir Pago
                        </button>
                    </div>
                </form>
            </x-modal>
        @endif
    @endforeach
</x-app-layout>