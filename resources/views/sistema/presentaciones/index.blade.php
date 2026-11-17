<x-app-layout>
    <!-- Header Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-3 border-b border-slate-200 dark:border-slate-700 mb-6 gap-3">
        <!-- Grupo Izquierdo: Botón Volver + Título -->
        <div class="flex items-center space-x-3">
            <a href="{{ route('sistema.index') }}" class="bg-white dark:bg-slate-800 p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center space-x-2">
                    <span>Cortes y Presentaciones de Créditos</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                    Ciclos comerciales, cálculo de mora y corte independiente para Tierra del Fuego y Chubut.
                </p>
            </div>
        </div>

        <!-- Indicador de Sucursal del Usuario -->
        <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
            <span class="font-bold text-slate-500 dark:text-slate-400">Tu Terminal:</span>
            <span class="font-black text-blue-700 dark:text-blue-400">{{ config('app.sucursal_activa')->nombre ?? 'N/A' }}</span>
            <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded-full {{ Auth::user()->is_admin ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                {{ Auth::user()->is_admin ? 'Administrador Multisede' : 'Operador Local' }}
            </span>
        </div>
    </div>

    <!-- Alertas -->
    @if (session('error'))
        <div class="bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 px-4 py-3 rounded-2xl shadow-sm mb-6 flex items-center space-x-2 text-xs font-bold">
            <span class="font-black uppercase">⛔ ERROR:</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if (session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-4 py-3 rounded-2xl shadow-sm mb-6 flex items-center space-x-2 text-xs font-bold">
            <span class="font-black uppercase">✅ ÉXITO:</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tarjetas Interactivas de Jurisdicción (Clic en todo el div para alternar) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Tarjeta Tierra del Fuego (Ushuaia) -->
        <a href="{{ Auth::user()->is_admin ? route('sistema.presentaciones.index', ['provincia_id' => 2]) : '#' }}"
           class="block text-left p-5 rounded-2xl border transition-all duration-200 relative group {{ $idProvincia === 2 
                ? 'bg-blue-50/80 dark:bg-blue-950/40 border-blue-400 dark:border-blue-500 shadow-md ring-2 ring-blue-500/20' 
                : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 opacity-80 hover:opacity-100 hover:border-blue-300 dark:hover:border-blue-500 hover:shadow-md hover:scale-[1.005] cursor-pointer' }}">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-600"></span>
                    <h3 class="font-black text-sm uppercase text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>🏔️</span>
                        <span>Tierra del Fuego (Ushuaia)</span>
                    </h3>
                </div>
                <div class="flex items-center gap-1.5">
                    @if($idProvincia === 2)
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-blue-600 text-white shadow-xs">
                            ✓ Mostrando ciclo
                        </span>
                    @else
                        <span class="text-[10px] font-bold text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            Clic para ver ciclo →
                        </span>
                    @endif
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                        Patagonia / BTF
                    </span>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-medium">Lote / Presentación Vigente:</span>
                    <span class="text-2xl font-black font-mono text-blue-900 dark:text-blue-300">
                        # {{ $actualTdf?->numero ?? 'Sin apertura' }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold text-slate-500 block">Sedes asociadas:</span>
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300">San Martín & Antártida</span>
                </div>
            </div>
        </a>

        <!-- Tarjeta Chubut (Trelew) -->
        <a href="{{ Auth::user()->is_admin ? route('sistema.presentaciones.index', ['provincia_id' => 1]) : '#' }}"
           class="block text-left p-5 rounded-2xl border transition-all duration-200 relative group {{ $idProvincia === 1 
                ? 'bg-amber-50/80 dark:bg-amber-950/40 border-amber-400 dark:border-amber-500 shadow-md ring-2 ring-amber-500/20' 
                : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 opacity-80 hover:opacity-100 hover:border-amber-300 dark:hover:border-amber-500 hover:shadow-md hover:scale-[1.005] cursor-pointer' }}">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-amber-600"></span>
                    <h3 class="font-black text-sm uppercase text-slate-900 dark:text-white flex items-center gap-1.5">
                        <span>🌾</span>
                        <span>Chubut (Trelew)</span>
                    </h3>
                </div>
                <div class="flex items-center gap-1.5">
                    @if($idProvincia === 1)
                        <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-amber-600 text-white shadow-xs">
                            ✓ Mostrando ciclo
                        </span>
                    @else
                        <span class="text-[10px] font-bold text-slate-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                            Clic para ver ciclo →
                        </span>
                    @endif
                    <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200">
                        Banco Chubut
                    </span>
                </div>
            </div>
            <div class="flex items-baseline justify-between">
                <div>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block font-medium">Lote / Presentación Vigente:</span>
                    <span class="text-2xl font-black font-mono text-amber-900 dark:text-amber-300">
                        # {{ $actualChubut?->numero ?? 'Sin apertura' }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold text-slate-500 block">Sedes asociadas:</span>
                    <span class="text-xs font-medium text-slate-700 dark:text-slate-300">Trelew Centro & Camión</span>
                </div>
            </div>
        </a>
    </div>

    <!-- Contenido Principal: Listado y Panel de Control -->
    <div class="flex flex-col lg:flex-row gap-6 items-start">
        
        <!-- COLUMNA IZQUIERDA: Listado (68%) -->
        <div class="w-full lg:w-[58%]">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/80 shadow-md overflow-hidden mb-6">
                <div class="bg-slate-50 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-700 px-5 py-3.5 flex items-center justify-between">
                    <h2 class="font-black text-xs text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-2">
                        <span>Historial de Presentaciones — {{ $idProvincia === 2 ? 'Tierra del Fuego' : 'Chubut' }}</span>
                    </h2>
                    <span class="text-[11px] font-bold text-slate-400">Total: {{ $presentaciones->total() }} lotes</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-black uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700">
                                <th class="py-2.5 px-4 text-center w-24">N° Lote</th>
                                <th class="py-2.5 px-4 text-center">Fecha Inicio</th>
                                <th class="py-2.5 px-4 text-center">Fecha Fin</th>
                                <th class="py-2.5 px-4 text-right w-24">Int. Crédito</th>
                                <th class="py-2.5 px-4 text-right w-24">Int. Mora</th>
                                <th class="py-2.5 px-4 text-center w-24">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                            @forelse($presentaciones as $pres)
                                @php
                                    $estaAbierta = is_null($pres->fecha_fin);
                                    $bgFila = $estaAbierta ? 'bg-blue-50/40 dark:bg-blue-950/20' : 'bg-white dark:bg-slate-800 hover:bg-slate-50/80 dark:hover:bg-slate-700/40';
                                @endphp
                                <tr class="{{ $bgFila }} transition-colors">
                                    <!-- Número -->
                                    <td class="py-3 px-4 text-center">
                                        <span class="font-mono font-black text-slate-900 dark:text-white text-xs">
                                            # {{ $pres->numero }}
                                        </span>
                                    </td>

                                    <!-- Fechas -->
                                    <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-700 dark:text-slate-300">
                                        <strong>{{ \Carbon\Carbon::parse($pres->fecha_inicio)->format('d/m/Y') }}</strong>
                                        <span class="text-[9px] text-slate-400 block">{{ \Carbon\Carbon::parse($pres->fecha_inicio)->format('H:i') }} hs</span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-700 dark:text-slate-300">
                                        @if($pres->fecha_fin)
                                            <strong>{{ \Carbon\Carbon::parse($pres->fecha_fin)->format('d/m/Y') }}</strong>
                                            <span class="text-[9px] text-slate-400 block">{{ \Carbon\Carbon::parse($pres->fecha_fin)->format('H:i') }} hs</span>
                                        @else
                                            <span class="text-emerald-600 dark:text-emerald-400 font-black text-[10px] uppercase">● En Curso</span>
                                        @endif
                                    </td>

                                    <!-- Intereses -->
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-700 dark:text-slate-300 tabular-nums">
                                        {{ number_format($pres->interes_credito * 100, 1) }} %
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-rose-600 dark:text-rose-400 tabular-nums">
                                        {{ number_format($pres->interes_mora * 100, 1) }} %
                                    </td>

                                    <!-- Estado -->
                                    <td class="py-3 px-4 text-center">
                                        @if($estaAbierta)
                                            <span class="bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 text-[10px] font-black px-2.5 py-1 rounded-full uppercase border border-emerald-300 dark:border-emerald-800">
                                                ACTIVA
                                            </span>
                                        @else
                                            <span class="bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 text-[10px] font-bold px-2 py-0.5 rounded uppercase">
                                                CERRADA
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400 dark:text-slate-500 text-xs italic bg-white dark:bg-slate-800">
                                        No hay presentaciones registradas para esta provincia.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($presentaciones->hasPages())
                    <div class="px-5 py-3 bg-slate-50 dark:bg-slate-800/90 border-t border-slate-200 dark:border-slate-700">
                        {{ $presentaciones->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- COLUMNA DERECHA: Panel de Cierre Operativo (32%) -->
        <div class="w-full lg:w-[42%] sticky top-6">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/80 shadow-md overflow-hidden">
                <div class="bg-gradient-to-r {{ $idProvincia === 2 ? 'from-blue-900 to-indigo-900' : 'from-amber-900 to-orange-950' }} px-5 py-4 text-white">
                    <span class="text-[10px] font-black uppercase tracking-wider block opacity-80">Control de Ciclo Provincial</span>
                    <h2 class="font-black text-base uppercase tracking-tight">
                        Corte {{ $idProvincia === 2 ? 'Tierra del Fuego' : 'Chubut' }}
                    </h2>
                </div>
                
                <div class="p-5">
                    @if($actual)
                        <div class="mb-5 bg-slate-50 dark:bg-slate-900/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700">
                            <span class="text-[10px] font-black text-slate-400 uppercase block mb-1">Presentación Vigente:</span>
                            <div class="flex justify-between items-center mb-1">
                                <span class="font-mono font-black text-2xl text-blue-950 dark:text-white"># {{ $actual->numero }}</span>
                                <span class="bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 text-[10px] font-black px-2 py-0.5 rounded-full border border-emerald-300 uppercase">En Curso</span>
                            </div>
                            <span class="text-[11px] text-slate-600 dark:text-slate-400 font-medium">
                                Abierta hace <strong class="text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($actual->fecha_inicio)->locale('es')->diffForHumans(now(), true) }}</strong>.
                            </span>
                        </div>
                    @endif

                    <form action="{{ route('sistema.presentaciones.cambiar') }}" method="POST"
                          onsubmit="return confirm('ATENCIÓN: Se cerrará la presentación #{{ $actual?->numero }} para {{ $idProvincia === 2 ? 'Tierra del Fuego' : 'Chubut' }}, se aplicará el interés de mora a las cuotas impagas y se volverán exigibles las cuotas del nuevo ciclo en dicha provincia. ¿Deseas confirmar la operación?');">
                        @csrf
                        <input type="hidden" name="id_provincia" value="{{ $idProvincia }}">

                        <div class="space-y-4 mb-5 border-t border-slate-100 dark:border-slate-700/60 pt-4">
                            <div>
                                <span class="text-[11px] font-black text-slate-800 dark:text-slate-200 uppercase block leading-tight">
                                    Abrir Lote Siguiente (N° {{ $actual ? $actual->numero + 1 : 1 }}):
                                </span>
                                <p class="text-[11px] text-slate-500 mt-0.5">Afectará únicamente a créditos originados en sucursales de {{ $idProvincia === 2 ? 'TDF' : 'Chubut' }}.</p>
                            </div>
                            
                            @php
                                $pctCreditoActual = \App\Http\Controllers\VentaController::normalizarPorcentajeInteres($actual) ?: 6;
                                $rawMora = (float) ($actual->interes_mora ?? 0.06);
                                $pctMoraActual = ($rawMora > 0 && $rawMora < 1) ? round($rawMora * 100, 2) : round($rawMora, 2);
                                $primeraSinInteresActual = $actual ? (bool) ($actual->primera_cuota_sin_interes ?? true) : true;
                            @endphp

                            <div x-data="{
                                    tasa: {{ $pctCreditoActual }},
                                    primeraSinInteres: {{ $primeraSinInteresActual ? 'true' : 'false' }},
                                    pct(n) {
                                        let t = parseFloat(String(this.tasa).replace(',', '.')) || 0;
                                        let tramos = this.primeraSinInteres ? n - 1 : n;
                                        return Math.round(t * tramos * 100) / 100;
                                    },
                                    fmt(v) { return Number(v).toLocaleString('es-AR', { maximumFractionDigits: 2 }); }
                                 }"
                                 class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                <!-- Inputs -->
                                <div class="space-y-4">
                                    <div>
                                        <label for="interes_credito" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Interés de Financiación por cuota (%)</label>
                                        <div class="relative">
                                            <input id="interes_credito" type="number" step="0.01" min="0" max="100" name="interes_credito"
                                                   x-model="tasa" value="{{ $pctCreditoActual }}" required
                                                   class="w-full text-xs font-mono font-bold rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:border-blue-500 py-2 pl-3 pr-8 shadow-sm">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">%</span>
                                        </div>
                                        <p class="text-[10px] text-slate-400 mt-1">Ingresá el número entero (ej: 6 para 6%).</p>
                                    </div>

                                    <label for="primera_cuota_sin_interes" class="flex items-start gap-2 cursor-pointer select-none p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60">
                                        <input type="hidden" name="primera_cuota_sin_interes" value="0">
                                        <input id="primera_cuota_sin_interes" type="checkbox" name="primera_cuota_sin_interes" value="1"
                                               x-model="primeraSinInteres" @checked($primeraSinInteresActual)
                                               class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                        <span class="text-[11px] leading-tight">
                                            <strong class="block text-slate-800 dark:text-slate-200 uppercase text-[10px]">Primera cuota sin interés</strong>
                                            <span class="text-slate-500">1 pago = 0%, y se suma la tasa por cada cuota adicional.</span>
                                        </span>
                                    </label>

                                    <div>
                                        <label for="interes_mora" class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Interés de Mora por Atraso (%)</label>
                                        <div class="relative">
                                            <input id="interes_mora" type="number" step="0.01" min="0" max="100" name="interes_mora" value="{{ $pctMoraActual }}" required
                                                   class="w-full text-xs font-mono font-bold rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-rose-700 dark:text-rose-400 focus:border-blue-500 py-2 pl-3 pr-8 shadow-sm">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">%</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabla de interés por cantidad de cuotas -->
                                <div class="rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                                    <table class="w-full text-[11px]">
                                        <thead class="bg-slate-100 dark:bg-slate-900 text-slate-500 uppercase text-[9px]">
                                            <tr>
                                                <th class="py-1.5 px-2 text-left font-black">Cuotas</th>
                                                <th class="py-1.5 px-2 text-right font-black">Interés</th>
                                                <th class="py-1.5 px-2 text-right font-black">Coef.</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-mono">
                                            <template x-for="n in 12" :key="n">
                                                <tr :class="pct(n) === 0 ? 'bg-emerald-50 dark:bg-emerald-950/40' : ''">
                                                    <td class="py-1 px-2 font-bold text-slate-700 dark:text-slate-300" x-text="n + (n === 1 ? ' pago' : ' cuotas')"></td>
                                                    <td class="py-1 px-2 text-right font-black tabular-nums"
                                                        :class="pct(n) === 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-800 dark:text-blue-300'"
                                                        x-text="fmt(pct(n)) + ' %'"></td>
                                                    <td class="py-1 px-2 text-right text-slate-400 tabular-nums" x-text="(1 + pct(n) / 100).toFixed(4)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black text-xs py-3 px-4 rounded-xl uppercase tracking-wider transition-all shadow-md flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            <span>Cerrar Lote y Abrir Siguiente</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>