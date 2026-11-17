<x-app-layout>
    <div class="max-w-7xl mx-auto py-5 px-3 space-y-5">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('cajas.index') }}" 
                       class="bg-white dark:bg-slate-800 p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition shadow-2xs">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <span>📊 Histórico de Facturación por Medio de Pago</span>
                    </h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Análisis consolidado de todas las ventas históricas importadas de la base de datos y registradas en el sistema.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('cajas.historico-medios', array_merge(request()->query(), ['export' => 'csv'])) }}" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-3.5 rounded-xl transition uppercase flex items-center gap-1.5 shadow-xs">
                    <span>📥 Exportar CSV</span>
                </a>
                <a href="{{ route('cajas.index') }}" 
                   class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs py-2 px-3 rounded-xl transition uppercase">
                    Control Cajas
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
            <form method="GET" action="{{ route('cajas.historico-medios') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Sucursal
                    </label>
                    <select name="sucursal" class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="all" {{ $filtroSucursal === 'all' ? 'selected' : '' }}>Todas las sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ (string)$filtroSucursal === (string)$suc->id ? 'selected' : '' }}>
                                {{ $suc->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Fecha Desde
                    </label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                           class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Fecha Hasta
                    </label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                           class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="w-full bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 text-white font-bold text-xs py-2 px-4 rounded-xl transition uppercase">
                        🔍 Actualizar Reporte
                    </button>
                    @if(request()->hasAny(['sucursal', 'fecha_desde', 'fecha_hasta']))
                        <a href="{{ route('cajas.historico-medios') }}" 
                           class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs py-2 px-3 rounded-xl transition uppercase">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- KPI GENERAL -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Recaudado Histórico</span>
                <span class="text-2xl font-black font-mono text-emerald-700 dark:text-emerald-400 mt-1 block">
                    ${{ number_format($totalGeneral, 2, ',', '.') }}
                </span>
            </div>

            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Cantidad Total de Ventas</span>
                <span class="text-2xl font-black font-mono text-indigo-700 dark:text-indigo-400 mt-1 block">
                    {{ number_format($cantidadGeneral, 0, ',', '.') }} operaciones
                </span>
            </div>

            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Ticket Promedio Global</span>
                <span class="text-2xl font-black font-mono text-slate-900 dark:text-white mt-1 block">
                    ${{ $cantidadGeneral > 0 ? number_format($totalGeneral / $cantidadGeneral, 2, ',', '.') : '0,00' }}
                </span>
            </div>
        </div>

        <!-- 1. TABLA RESUMEN POR MEDIO DE PAGO -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                    <span>💳 Distribución por Medio de Pago</span>
                </h2>
                <span class="text-xs text-slate-500">
                    {{ $porMedio->count() }} medios registrados
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-900/80 text-slate-600 dark:text-slate-400 uppercase text-[10px] font-black border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="py-3 px-4">Medio de Pago</th>
                            <th class="py-3 px-4 text-right">Cant. Operaciones</th>
                            <th class="py-3 px-4 text-right">% Operaciones</th>
                            <th class="py-3 px-4 text-right">Monto Total</th>
                            <th class="py-3 px-4 text-right">% Participación</th>
                            <th class="py-3 px-4 text-right">Ticket Promedio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @foreach($porMedio as $fila)
                            @php
                                $monto = (float) $fila->total_monto;
                                $cant = (int) $fila->cant_ventas;
                                $pctMonto = $totalGeneral > 0 ? ($monto / $totalGeneral) * 100 : 0;
                                $pctCant = $cantidadGeneral > 0 ? ($cant / $cantidadGeneral) * 100 : 0;
                                $promedio = $cant > 0 ? $monto / $cant : 0;

                                $label = ($fila->icono ? ($fila->icono . ' ') : '') . ($fila->medio_pago_nombre ?? ucwords(str_replace('_', ' ', $fila->medio_pago)));
                                $color = match($fila->color ?? '') {
                                    'emerald' => 'text-emerald-700 dark:text-emerald-400',
                                    'sky', 'cyan' => 'text-sky-700 dark:text-sky-400',
                                    'blue', 'indigo' => 'text-indigo-700 dark:text-indigo-400',
                                    'purple', 'violet' => 'text-purple-700 dark:text-purple-400',
                                    'amber' => 'text-amber-700 dark:text-amber-400',
                                    'teal' => 'text-teal-700 dark:text-teal-400',
                                    default => 'text-slate-800 dark:text-slate-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition">
                                <td class="py-3 px-4 font-bold {{ $color }}">
                                    {{ $label }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold">
                                    {{ number_format($cant, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-500">
                                    {{ number_format($pctCant, 1) }}%
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-black text-slate-900 dark:text-white">
                                    ${{ number_format($monto, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-16 bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                            <div class="bg-indigo-600 h-full rounded-full" style="width: {{ min(100, $pctMonto) }}%"></div>
                                        </div>
                                        <span class="font-mono font-bold text-[11px] text-slate-700 dark:text-slate-300 w-10 text-right">
                                            {{ number_format($pctMonto, 1) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                    ${{ number_format($promedio, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-900/80 font-black border-t border-slate-200 dark:border-slate-700">
                        <tr>
                            <td class="py-3 px-4 uppercase text-slate-900 dark:text-white">TOTAL GENERAL</td>
                            <td class="py-3 px-4 text-right font-mono text-indigo-700 dark:text-indigo-400">{{ number_format($cantidadGeneral, 0, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-mono text-slate-500">100.0%</td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-700 dark:text-emerald-400 text-sm">${{ number_format($totalGeneral, 2, ',', '.') }}</td>
                            <td class="py-3 px-4 text-right font-mono">100.0%</td>
                            <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-white">
                                ${{ $cantidadGeneral > 0 ? number_format($totalGeneral / $cantidadGeneral, 2, ',', '.') : '0,00' }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- 2. DESGLOSE POR SUCURSAL Y MEDIO DE PAGO -->
        @if($porSucursalMedio->isNotEmpty())
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        🏢 Desglose Cruzado por Sucursal y Medio de Pago
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-900/80 text-slate-600 dark:text-slate-400 uppercase text-[10px] font-black border-b border-slate-200 dark:border-slate-700">
                            <tr>
                                <th class="py-3 px-4">Sucursal</th>
                                <th class="py-3 px-4">Medio de Pago</th>
                                <th class="py-3 px-4 text-right">Cantidad Ventas</th>
                                <th class="py-3 px-4 text-right">Total Facturado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                            @foreach($porSucursalMedio as $sm)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition">
                                    <td class="py-2.5 px-4 font-bold text-slate-900 dark:text-white">
                                        {{ $sm->sucursal_nombre ?? 'Sucursal #' . $sm->id_sucursal }}
                                    </td>
                                    <td class="py-2.5 px-4 font-medium text-slate-700 dark:text-slate-300">
                                        {{ ucwords(str_replace('_', ' ', $sm->medio_pago)) }}
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold">
                                        {{ number_format($sm->cant_ventas, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-black text-slate-900 dark:text-white">
                                        ${{ number_format($sm->total_monto, 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
