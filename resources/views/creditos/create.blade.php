<x-app-layout>
    <div class="max-w-7xl mx-auto py-4" 
         x-data="{ 
            valorVenta: 0, 
            aFavor: 0, 
            cuotasSeleccionadas: 1,
            limiteDisponible: {{ $limiteDisponible }},
            tasaInteres: {{ \App\Http\Controllers\VentaController::normalizarPorcentajeInteres($presentacionActivaModel ?? null) }},
            primeraSinInteres: {{ (($presentacionActivaModel?->primera_cuota_sin_interes ?? true)) ? 'true' : 'false' }},
            get subtotal() { 
                return Math.max(0, (parseFloat(this.valorVenta) || 0) - (parseFloat(this.aFavor) || 0)); 
            },
            calcularCuota(cant) {
                if (this.subtotal <= 0) return 0;
                let tramos = this.primeraSinInteres ? cant - 1 : cant;
                let total = this.subtotal * (1 + (this.tasaInteres / 100) * tramos);
                return (total / cant).toFixed(2);
            }
         }">

        <!-- Header Compacto -->
        <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-3">
            <div class="flex items-center space-x-3">
                <a href="{{  url()->previous()  }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                        <span class="uppercase">Generar Crédito: {{ $cliente->nombre }}</span>
                        <span class="bg-blue-100 dark:bg-blue-950/80 text-blue-900 dark:text-blue-300 font-mono font-bold text-[10px] px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800">DNI: {{ $cliente->dni }}</span>
                    </h1>
                </div>
            </div>
            <div class="flex items-center space-x-2 bg-green-100 dark:bg-emerald-950/60 px-3 py-1.5 rounded-md border border-slate-200 dark:border-emerald-800">
                <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Saldo a Favor:</span>
                <span class="font-mono font-black text-sm tabular-nums" :class="subtotal > limiteDisponible ? 'text-red-700 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400'">
                    $ {{ number_format($aFavor, 2, ',', '.') }}
                </span>
            </div>
            
            <div class="flex items-center space-x-2 bg-slate-50 dark:bg-slate-800 px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700">
                <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Disponible:</span>
                <span class="font-mono font-black text-sm tabular-nums" :class="subtotal > limiteDisponible ? 'text-red-700 dark:text-red-400' : 'text-emerald-700 dark:text-emerald-400'">
                    $ {{ number_format($limiteDisponible, 2, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-4 items-start">
            
            <!-- COLUMNA IZQUIERDA: PUNTO DE VENTA (66%) -->
            <div class="w-full lg:w-[66.6%] space-y-4">
                
                <form action="{{ route('creditos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id_cliente" value="{{ $cliente->id }}">
                    @if($autorizado)
                        <input type="hidden" name="id_autorizado" value="{{ $autorizado->id }}">
                    @endif

                    <!-- Panel de Carga (Caja) -->
                    <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm overflow-hidden">
                        <div class="bg-slate-100 dark:bg-slate-900/80 border-b border-slate-300 dark:border-slate-700 px-3 py-2 flex items-center justify-between">
                            <h2 class="font-bold text-[10px] text-slate-700 dark:text-slate-300 uppercase tracking-wider">1. Parámetros de Venta</h2>
                            @if($autorizado)
                                <span class="bg-amber-100 dark:bg-amber-950/80 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-bold text-[9px] px-1.5 py-0.5 rounded uppercase">
                                    Firma: {{ $autorizado->cliente->nombre ?? 'S/D' }}
                                </span>
                            @endif
                        </div>

                        <div class="p-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Inputs -->
                            <div class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Valor Venta ($)</label>
                                        <input type="number" step="0.1" name="importe_original" x-model="valorVenta" required
                                               class="w-full text-sm font-mono font-black rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm" placeholder="0.00">
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Entrega / A Favor ($)</label>
                                        <input type="number" step="0.1" name="a_favor" x-model="aFavor" value="{{ $aFavor }}" required
                                               class="w-full text-sm font-mono font-bold rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-slate-50 dark:bg-slate-900/80 text-slate-800 dark:text-slate-100 shadow-sm" placeholder="0.00">
                                    </div>
                                </div>

                                <div class="bg-slate-100/70 dark:bg-slate-900/60 p-2 rounded border border-slate-200 dark:border-slate-700 flex justify-between items-center">
                                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase">Subtotal a Financiar</span>
                                    <span class="font-mono font-black text-slate-900 dark:text-white text-base tabular-nums">
                                        $ <span x-text="Number(subtotal).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                    </span>
                                </div>

                                <div>
                                    <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Cuotas Seleccionadas</label>
                                    <select name="cuotas" x-model="cuotasSeleccionadas" class="w-full text-xs font-bold rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 shadow-sm">
                                        <option value="1">1 Cuota</option>
                                        <option value="2">2 Cuotas</option>
                                        <option value="3">3 Cuotas</option>
                                        <option value="4">4 Cuotas</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Panel Simulación y Submit -->
                            <div class="bg-slate-800 dark:bg-slate-950 text-white rounded-md p-3 flex flex-col justify-between shadow-sm border border-slate-700">
                                <div>
                                    <span class="text-[9px] font-bold text-blue-300 uppercase tracking-wider block mb-2">Simulador de Cuotas</span>
                                    <div class="space-y-1">
                                        <template x-for="i in 4" :key="i">
                                            <div class="flex justify-between items-center p-1.5 rounded border transition-all" 
                                                 :class="cuotasSeleccionadas == i ? 'bg-blue-600 border-blue-400' : 'bg-slate-700/50 dark:bg-slate-800/60 border-transparent'">
                                                <span class="text-[10px] font-bold text-slate-200" x-text="i + (i==1 ? ' cuota de:' : ' cuotas de:')"></span>
                                                <span class="font-mono font-black text-xs text-white tabular-nums">$ <span x-text="Number(calcularCuota(i)).toLocaleString('es-AR', {minimumFractionDigits: 2})"></span></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2 border-t border-slate-600 dark:border-slate-800">
                                    <template x-if="subtotal > limiteDisponible">
                                        <p class="text-[9px] text-red-400 font-bold mb-1.5 uppercase">⚠️ Supera límite disponible</p>
                                    </template>
                                    <button type="submit" :disabled="subtotal <= 0 || subtotal > limiteDisponible"
                                            class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-600 text-white font-bold text-[10px] py-2 px-3 rounded uppercase transition-all shadow-sm">
                                        Confirmar y Generar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Grilla Vencimientos Cartera Activa (Estilo PowerBI) -->
                <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm overflow-hidden mb-4">
                    <div class="bg-slate-100 dark:bg-slate-900/80 border-b border-slate-300 dark:border-slate-700 px-3 py-1.5 flex justify-between items-center">
                        <h2 class="font-bold text-[10px] text-slate-700 dark:text-slate-300 uppercase tracking-wider">Próximos Vencimientos (Presentación: {{ $presentacionActual }})</h2>
                    </div>
                    
                    <div class="grid grid-cols-4 divide-x divide-slate-200 dark:divide-slate-700">
                        @for ($mes = 1; $mes <= 4; $mes++)
                            <div class="flex flex-col justify-between">
                                <!-- Header Columna -->
                                <div class="bg-slate-50 dark:bg-slate-900/60 py-1 px-2 text-center border-b border-slate-200 dark:border-slate-700">
                                    <span class="font-bold text-[10px] text-slate-800 dark:text-slate-200 uppercase block leading-tight">{{ $mes }}° Mes</span>
                                    <span class="text-[9px] font-mono text-slate-500 dark:text-slate-400">Pres. {{ $vencimientos[$mes]['presentacion'] }}</span>
                                </div>

                                <!-- Cuotas -->
                                <div class="p-1 space-y-0.5 min-h-[90px] max-h-[160px] overflow-y-auto bg-white dark:bg-slate-800/40">
                                    @forelse($vencimientos[$mes]['cuotas'] as $cuota)
                                        <div class="flex items-center justify-between px-1.5 py-1 rounded bg-white dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700/60 border border-slate-200 dark:border-slate-700 transition-colors group">
                                            <a href="{{ route('creditos.show', $cuota->credito->id) }}" class="font-mono font-bold text-[10px] text-blue-700 dark:text-blue-400 group-hover:underline">
                                                {{ $cuota->credito->full_identificador }}
                                            </a>
                                            <span class="bg-slate-100 dark:bg-slate-700 px-1 rounded text-[9px] font-mono font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                                C{{ $cuota->numero_display }}
                                            </span>
                                            <!-- Verde porque es el saldo exigible / A Pagar -->
                                            <span class="font-mono font-black text-[10px] text-emerald-700 dark:text-emerald-400 tabular-nums">
                                                ${{ number_format($cuota->saldo_neto_base, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="h-full min-h-[70px] flex items-center justify-center">
                                            <span class="text-[9px] text-slate-400 dark:text-slate-500 italic">Sin cuotas</span>
                                        </div>
                                    @endforelse
                                </div>

                                <!-- Total Columna -->
                                <div class="bg-slate-100 dark:bg-slate-900/80 border-t border-slate-200 dark:border-slate-700 px-2 py-1 flex justify-between text-[10px]">
                                    <span class="font-bold text-slate-500 dark:text-slate-400 uppercase">Total:</span>
                                    <span class="font-mono font-black text-slate-900 dark:text-white tabular-nums">${{ number_format($vencimientos[$mes]['total'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: KPIs Y NOVEDADES (33%) -->
            <div class="w-full lg:w-[33.3%] space-y-4">
                
                <!-- KPIs Globales Cartera Cliente -->
                <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm p-3 grid grid-cols-2 gap-2">
                    <div class="col-span-2 mb-1">
                        <span class="font-bold text-[10px] text-slate-700 dark:text-slate-300 uppercase tracking-wider">Estado Financiero (Cartera Activa)</span>
                    </div>
                    <div class="col-span-2 bg-amber-50 dark:bg-amber-950/40 p-2 rounded border border-amber-200 dark:border-amber-800 flex justify-between items-center">
                        <span class="text-[10px] font-bold text-amber-800 dark:text-amber-300 uppercase block">Total deuda</span>
                        <span class="text-base font-black text-amber-700 dark:text-amber-400 font-mono tabular-nums">$ {{ number_format($totalCreditosActivos, 2, ',', '.') }}</span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-900/60 p-2 rounded border border-slate-200 dark:border-slate-700 flex flex-col justify-center">
                        <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Total Entregado</span>
                        <span class="text-sm font-black text-slate-900 dark:text-white font-mono tabular-nums">$ {{ number_format($totalEntregado, 2, ',', '.') }}</span>
                    </div>
                    <div class="bg-red-50 dark:bg-rose-950/40 p-2 rounded border border-red-200 dark:border-rose-900 flex flex-col justify-center">
                        <span class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Intereses Acum.</span>
                        <span class="text-sm font-black text-red-800 dark:text-rose-400 font-mono tabular-nums">$ {{ number_format($totalIntereses, 2, ',', '.') }}</span>
                    </div>
                    <div class="col-span-2 bg-emerald-100 dark:bg-emerald-950/60 p-2 rounded border border-emerald-200 dark:border-emerald-800 flex justify-between items-center">
                        <span class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 uppercase block">Total a Pagar (Saldo)</span>
                        <span class="text-base font-black text-emerald-700 dark:text-emerald-400 font-mono tabular-nums">$ {{ number_format($totalAPagar, 2, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Chat / Historial Novedades -->
                <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm flex flex-col h-[380px]">
                    <div class="bg-slate-100 dark:bg-slate-900/80 border-b border-slate-300 dark:border-slate-700 px-3 py-1.5 flex justify-between items-center">
                        <h2 class="font-bold text-[10px] text-slate-700 dark:text-slate-300 uppercase tracking-wider">Novedades ({{ $cliente->novedades ? $cliente->novedades->count() : 0 }})</h2>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-2 space-y-1.5 bg-slate-50/50 dark:bg-slate-900/50">
                        @if($cliente->novedades && $cliente->novedades->count() > 0)
                            @foreach($cliente->novedades as $novedad)
                                <div class="p-2 rounded text-[10px] border-l-2 shadow-sm bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 {{ $novedad->tipo == 0 ? 'border-l-rose-500' : 'border-l-emerald-500' }}">
                                    <div class="flex justify-between items-center mb-1">
                                        <div class="flex items-center space-x-1.5">
                                            <span class="font-bold text-slate-800 dark:text-slate-200 uppercase">{{ $novedad->user?->name ?? $novedad->usuario_nombre ?? 'Admin' }}</span>
                                            @if($novedad->metodo)
                                                <span class="text-[8px] font-bold px-1 rounded uppercase bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 border border-slate-200 dark:border-slate-600">{{ $novedad->metodo }}</span>
                                            @endif
                                        </div>
                                        <span class="font-mono text-slate-400 dark:text-slate-500">{{ \Carbon\Carbon::parse($novedad->created_at)->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <p class="text-slate-600 dark:text-slate-300 leading-snug">{{ $novedad->mensaje }}</p>
                                </div>
                            @endforeach
                        @else
                            <div class="h-full flex items-center justify-center">
                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium italic">Sin novedades registradas.</span>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>