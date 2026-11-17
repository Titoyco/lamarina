<x-app-layout>
    @php
        $sort = $sort ?? request()->get('sort');
        $direction = $direction ?? request()->get('direction', 'desc');
        $nextDirection = function($col) use ($sort, $direction) {
            return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
        };
        $sortUrl = function($col, $tabParam = null) use ($nextDirection) {
            $params = ['sort' => $col, 'direction' => $nextDirection($col)];
            if ($tabParam) {
                $params['tab'] = $tabParam;
            }
            return route('metricas.index', array_merge(request()->query(), $params));
        };
        $sortIndicator = function($col) use ($sort, $direction) {
            if ($sort === $col) {
                return $direction === 'asc' 
                    ? '<span class="inline-block text-blue-600 dark:text-blue-400 font-black ml-1 text-[11px] leading-none">▲</span>' 
                    : '<span class="inline-block text-blue-600 dark:text-blue-400 font-black ml-1 text-[11px] leading-none">▼</span>';
            }
            return '<span class="inline-block text-slate-300 dark:text-slate-600 group-hover:text-slate-500 font-normal ml-1 text-[10px] leading-none">⇅</span>';
        };
    @endphp
    <div class="py-5 px-3 sm:px-6 max-w-7xl mx-auto space-y-5" 
         x-data="{
             tabActiva: '{{ $tab }}',
             filtroTexto: '',
             busquedaTabla: '',
             cambiarTab(nueva) {
                 this.tabActiva = nueva;
                 const url = new URL(window.location.href);
                 url.searchParams.set('tab', nueva);
                 window.history.pushState({}, '', url);
             },
             aplicarPreset(preset) {
                 document.getElementById('inputPreset').value = preset;
                 document.getElementById('formFiltrosBI').submit();
             }
         }">

        <!-- =================================================================== -->
        <!-- ENCABEZADO ESTILO POWERBI / DASHBOARD CORPORATIVO                   -->
        <!-- =================================================================== -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-300 text-slate-950 flex items-center justify-center text-2xl font-black shadow-lg shadow-amber-500/20 shrink-0">
                    📊
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                            Tablero de Métricas & Business Intelligence
                        </h1>
                        <span class="px-2 py-0.5 text-[10px] font-black uppercase rounded bg-amber-100 text-amber-900 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                            PowerBI Style
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Consolidado de ventas, prendas por categoría/fabricante y cartera de créditos en tiempo real.
                    </p>
                </div>
            </div>

            <!-- Botones de Acción Superior (Exportar y Pestañas) -->
            <div class="flex flex-wrap items-center gap-2">
                <a :href="'{{ route('metricas.exportar') }}?tab=' + tabActiva + '&export=excel&fecha_desde={{ $fechaDesde }}&fecha_hasta={{ $fechaHasta }}&id_sucursal={{ $idSucursal ?? '' }}&dimension={{ $dimensionVentas }}&credito_dimension={{ $dimensionCreditos }}&sort={{ $sort ?? '' }}&direction={{ $direction ?? '' }}'"
                   class="px-3.5 py-2 rounded-xl text-xs font-black uppercase bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition flex items-center gap-1.5 cursor-pointer"
                   title="Descargar datos actuales filtrados para Microsoft Excel">
                    <span>📥</span>
                    <span>Exportar a Excel</span>
                </a>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BARRA SLICER DE FILTROS GLOBALES (FECHAS, PRESETS Y SUCURSAL)       -->
        <!-- =================================================================== -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-sm space-y-3">
            <form id="formFiltrosBI" method="GET" action="{{ route('metricas.index') }}" class="space-y-3">
                <input type="hidden" name="tab" :value="tabActiva">
                <input type="hidden" name="preset" id="inputPreset" value="{{ $rangoPreset }}">
                <input type="hidden" name="dimension" value="{{ $dimensionVentas }}">
                <input type="hidden" name="credito_dimension" value="{{ $dimensionCreditos }}">

                <!-- Fila 1: Presets rápidos estilo PowerBI Slicers -->
                <div class="flex flex-wrap items-center gap-1.5 pb-2 border-b border-slate-100 dark:border-slate-700/60">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-400 mr-1 flex items-center gap-1">
                        <span>⚡</span>
                        <span>Período Rápido:</span>
                    </span>

                    @php
                        $presets = [
                            'hoy'          => 'Hoy',
                            'ayer'         => 'Ayer',
                            'semana'       => 'Esta Semana',
                            'mes'          => 'Este Mes',
                            'mes_anterior' => 'Mes Anterior',
                            'trimestre'    => 'Últimos 3 Meses',
                            'anio'         => 'Año 2026',
                            'todo'         => 'Histórico Todo',
                        ];
                    @endphp

                    @foreach($presets as $key => $lbl)
                        <button type="button" 
                                @click="aplicarPreset('{{ $key }}')"
                                class="px-2.5 py-1 text-xs font-bold rounded-lg transition cursor-pointer {{ $rangoPreset === $key ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600' }}">
                            {{ $lbl }}
                        </button>
                    @endforeach
                </div>

                <!-- Fila 2: Rango manual de fechas + Selector de Sucursal + Botón Aplicar -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <!-- Fecha Desde -->
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-black uppercase text-slate-600 dark:text-slate-400 mb-1">
                            Fecha de Venta Desde:
                        </label>
                        <input type="date" name="fecha_desde" value="{{ $fechaDesde }}"
                               class="w-full text-xs font-mono font-bold py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 shadow-2xs">
                    </div>

                    <!-- Fecha Hasta -->
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-black uppercase text-slate-600 dark:text-slate-400 mb-1">
                            Fecha de Venta Hasta:
                        </label>
                        <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}"
                               class="w-full text-xs font-mono font-bold py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 shadow-2xs">
                    </div>

                    <!-- Sucursal -->
                    <div class="sm:col-span-4">
                        <label class="block text-[11px] font-black uppercase text-slate-600 dark:text-slate-400 mb-1">
                            Sucursal / Terminal:
                        </label>
                        <select name="id_sucursal" 
                                class="w-full text-xs font-bold py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 shadow-2xs">
                            <option value="todas" {{ empty($idSucursal) ? 'selected' : '' }}>
                                🌐 Todas las Sucursales Consolidadas
                            </option>
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}" {{ (int)$idSucursal === (int)$suc->id ? 'selected' : '' }}>
                                    🏢 {{ $suc->nombre }} ({{ $suc->provincia?->nombre ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botón Aplicar -->
                    <div class="sm:col-span-2">
                        <button type="submit" 
                                class="w-full py-2 px-4 rounded-xl text-xs font-black uppercase bg-slate-900 hover:bg-black dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white shadow-md transition cursor-pointer flex items-center justify-center gap-1.5">
                            <span>🔍 Filtrar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- =================================================================== -->
        <!-- SELECTOR DE PESTAÑAS PRINCIPALES (VENTAS VS CRÉDITOS)               -->
        <!-- =================================================================== -->
        <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800">
            <button type="button" 
                    @click="cambiarTab('ventas')"
                    :class="tabActiva === 'ventas' 
                        ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 font-black border-b-2' 
                        : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-bold'"
                    class="py-3 px-4 text-xs uppercase tracking-wider transition flex items-center gap-2 cursor-pointer">
                <span>🛍️ Métricas de Ventas & Prendas</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300">
                    {{ number_format($metricasVentas['kpis']['total_prendas'], 0, ',', '.') }} prendas
                </span>
            </button>

            <button type="button" 
                    @click="cambiarTab('creditos')"
                    :class="tabActiva === 'creditos' 
                        ? 'border-amber-500 text-amber-600 dark:text-amber-400 font-black border-b-2' 
                        : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-bold'"
                    class="py-3 px-4 text-xs uppercase tracking-wider transition flex items-center gap-2 cursor-pointer">
                <span>💳 Métricas de Créditos & Cobranzas</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                    {{ number_format($metricasCreditos['kpis']['total_creditos'], 0, ',', '.') }} créditos
                </span>
            </button>
        </div>

        <!-- =================================================================== -->
        <!-- MÓDULO 1: VENTAS & PRENDAS                                          -->
        <!-- =================================================================== -->
        <div x-show="tabActiva === 'ventas'" class="space-y-5" x-cloak>
            
            <!-- 1. TARJETAS KPI POWERBI (VENTAS) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Total Cobrado / Facturado -->
                <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-4 rounded-2xl shadow-md space-y-1 relative overflow-hidden">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-100 block">Total Facturado</span>
                    <span class="text-xl sm:text-2xl font-mono font-black block tracking-tight">
                        $ {{ number_format($metricasVentas['kpis']['total_cobrado'], 2, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-emerald-200 block">Facturación Neta Total</span>
                </div>

                <!-- Cantidad de Prendas -->
                <div class="bg-gradient-to-br from-indigo-600 to-blue-700 text-white p-4 rounded-2xl shadow-md space-y-1 relative overflow-hidden">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-100 block">Cantidad Prendas</span>
                    <span class="text-xl sm:text-2xl font-mono font-black block tracking-tight">
                        {{ number_format($metricasVentas['kpis']['total_prendas'], 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-indigo-200 block">Unidades Vendidas</span>
                </div>

                <!-- Subtotal Bruto -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Subtotal Bruto</span>
                    <span class="text-lg sm:text-xl font-mono font-black text-slate-900 dark:text-white block">
                        $ {{ number_format($metricasVentas['kpis']['total_subtotal'], 2, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 block">Importe de Lista</span>
                </div>

                <!-- Descuentos Aplicados -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 block">Descuentos</span>
                    <span class="text-lg sm:text-xl font-mono font-black text-rose-600 dark:text-rose-400 block">
                        -$ {{ number_format($metricasVentas['kpis']['total_descuento'], 2, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 block">Promociones e Ítems Bonificados</span>
                </div>

                <!-- Total Tickets -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Operaciones</span>
                    <span class="text-lg sm:text-xl font-mono font-black text-slate-900 dark:text-white block">
                        {{ number_format($metricasVentas['kpis']['total_tickets'], 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 block">Comprobantes Emitidos</span>
                </div>

                <!-- Precio Promedio por Prenda -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 block">Precio Promedio</span>
                    <span class="text-lg sm:text-xl font-mono font-black text-amber-600 dark:text-amber-400 block">
                        $ {{ number_format($metricasVentas['kpis']['precio_promedio_prenda'], 2, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 block">Por Prenda Vendida</span>
                </div>
            </div>

            <!-- 2. SELECTOR DE DIMENSIÓN DE VENTAS (TIPO POWERBI SLICER MATRIX) -->
            <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <span>🏷️</span>
                        <span>Agrupar Resumen Por:</span>
                    </span>

                    @php
                        $dimensiones = [
                            'categoria'        => ['label' => 'Categoría', 'icon' => '📁'],
                            'fabricante'       => ['label' => 'Fabricante / Proveedor', 'icon' => '🏭'],
                            'subcategoria'     => ['label' => 'Subcategoría', 'icon' => '📂'],
                            'sucursal'         => ['label' => 'Sucursal', 'icon' => '🏢'],
                            'medio_pago'       => ['label' => 'Medio de Pago', 'icon' => '💳'],
                            'tipo_comprobante' => ['label' => 'Tipo Comprobante', 'icon' => '📄'],
                        ];
                    @endphp

                    <div class="flex flex-wrap items-center gap-1">
                        @foreach($dimensiones as $dKey => $dCfg)
                            <a href="{{ request()->fullUrlWithQuery(['dimension' => $dKey, 'tab' => 'ventas']) }}"
                               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer {{ $dimensionVentas === $dKey ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200' }}">
                                <span>{{ $dCfg['icon'] }}</span>
                                <span>{{ $dCfg['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Buscador de Texto en Tiempo Real de la Tabla -->
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400">🔍</span>
                    <input type="text" x-model="busquedaTabla" placeholder="Buscar en resultados..."
                           class="w-full pl-8 pr-3 py-1.5 rounded-xl text-xs border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 shadow-2xs">
                </div>
            </div>

            <!-- 3. TABLA RESUMEN POWERBI (PRENDAS Y VALOR COBRADO) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-3 bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <span>📋</span>
                        <span>Tabla Resumen: Rendimiento por {{ $dimensiones[$dimensionVentas]['label'] ?? 'Categoría' }} ({{ count($metricasVentas['filas']) }} filas)</span>
                    </span>
                    <span class="text-[11px] text-slate-500 font-mono">
                        Rango: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
                    </span>
                </div>

                <div class="overflow-x-auto max-h-[560px] overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-900/90 text-slate-600 dark:text-slate-400 text-[10px] uppercase font-black tracking-wider border-b border-slate-200 dark:border-slate-700 sticky top-0 z-10 select-none">
                            <tr>
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">
                                    <a href="{{ $sortUrl('dimension', 'ventas') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Dimensión">
                                        <span>{{ $dimensiones[$dimensionVentas]['label'] ?? 'Dimensión' }}</span>
                                        {!! $sortIndicator('dimension') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-center w-36">
                                    <a href="{{ $sortUrl('prendas', 'ventas') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Prendas">
                                        <span>Prendas / Cantidad</span>
                                        {!! $sortIndicator('prendas') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-center w-24">
                                    <a href="{{ $sortUrl('porcentaje_prendas', 'ventas') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por % Prendas">
                                        <span>% Prendas</span>
                                        {!! $sortIndicator('porcentaje_prendas') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-right w-36">
                                    <a href="{{ $sortUrl('total_descuento', 'ventas') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Descuentos">
                                        <span>Descuentos ($)</span>
                                        {!! $sortIndicator('total_descuento') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-right w-44">
                                    <a href="{{ $sortUrl('total_monto', 'ventas') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Total Facturado">
                                        <span>Total Cobrado / Facturado</span>
                                        {!! $sortIndicator('total_monto') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-center w-24">
                                    <a href="{{ $sortUrl('porcentaje_monto', 'ventas') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por % Facturado">
                                        <span>% Facturado</span>
                                        {!! $sortIndicator('porcentaje_monto') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-right w-32">
                                    <a href="{{ $sortUrl('precio_promedio', 'ventas') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Precio Promedio">
                                        <span>Precio Promedio</span>
                                        {!! $sortIndicator('precio_promedio') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-center w-24">
                                    <a href="{{ $sortUrl('tickets', 'ventas') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Tickets">
                                        <span>Tickets</span>
                                        {!! $sortIndicator('tickets') !!}
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-sans">
                            @forelse($metricasVentas['filas'] as $i => $row)
                                <tr class="hover:bg-indigo-50/50 dark:hover:bg-slate-700/40 transition"
                                    x-show="!busquedaTabla || '{{ addslashes(mb_strtolower($row['dimension'])) }}'.includes(busquedaTabla.toLowerCase())">
                                    <td class="py-2.5 px-3 font-mono text-[10px] text-slate-400">{{ $i + 1 }}</td>
                                    <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-white text-xs">
                                        {{ $row['dimension'] }}
                                    </td>
                                    
                                    <!-- Prendas con Barra de Progreso Visual -->
                                    <td class="py-2.5 px-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="font-mono font-black text-slate-900 dark:text-white text-xs">
                                                {{ number_format($row['prendas'], 0, ',', '.') }}
                                            </span>
                                            <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-indigo-600 rounded-full" style="width: {{ min(100, $row['porcentaje_prendas']) }}%"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- % Prendas -->
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-indigo-700 dark:text-indigo-400">
                                        {{ number_format($row['porcentaje_prendas'], 1) }}%
                                    </td>

                                    <!-- Descuento $ -->
                                    <td class="py-2.5 px-3 text-right">
                                        @if(($row['total_descuento'] ?? 0) > 0)
                                            <span class="font-mono font-black text-rose-600 dark:text-rose-400 text-xs">
                                                -$ {{ number_format($row['total_descuento'], 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="font-mono text-slate-400 text-xs">$ 0,00</span>
                                        @endif
                                    </td>

                                    <!-- Total Facturado $ con Barra Visual -->
                                    <td class="py-2.5 px-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-emerald-600 rounded-full" style="width: {{ min(100, $row['porcentaje_monto']) }}%"></div>
                                            </div>
                                            <span class="font-mono font-black text-emerald-700 dark:text-emerald-400 text-xs">
                                                $ {{ number_format($row['total_monto'], 2, ',', '.') }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- % Facturado -->
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                        {{ number_format($row['porcentaje_monto'], 1) }}%
                                    </td>

                                    <!-- Precio Promedio por Prenda -->
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-700 dark:text-slate-300 text-xs">
                                        $ {{ number_format($row['precio_promedio'], 2, ',', '.') }}
                                    </td>

                                    <!-- Tickets -->
                                    <td class="py-2.5 px-3 text-center font-mono text-slate-500">
                                        {{ number_format($row['tickets'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400 text-xs">
                                        No se encontraron registros de ventas para el período seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-100 dark:bg-slate-900 border-t-2 border-slate-300 dark:border-slate-700 font-bold sticky bottom-0 z-10 text-xs">
                            <tr>
                                <td colspan="2" class="py-2.5 px-3 uppercase font-black text-slate-800 dark:text-slate-100">
                                    TOTAL CONSOLIDADO ({{ count($metricasVentas['filas']) }} grupos)
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-black text-indigo-700 dark:text-indigo-400">
                                    {{ number_format($metricasVentas['totales_filas']['total_prendas'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-black text-indigo-700 dark:text-indigo-400">
                                    100.0%
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-rose-600 dark:text-rose-400">
                                    -$ {{ number_format($metricasVentas['totales_filas']['total_descuento'] ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-700 dark:text-emerald-400">
                                    $ {{ number_format($metricasVentas['totales_filas']['total_monto'], 2, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-black text-emerald-700 dark:text-emerald-400">
                                    100.0%
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-slate-800 dark:text-slate-200">
                                    $ {{ number_format($metricasVentas['totales_filas']['total_prendas'] > 0 ? ($metricasVentas['totales_filas']['total_monto'] / $metricasVentas['totales_filas']['total_prendas']) : 0, 2, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-black text-slate-500">
                                    {{ number_format($metricasVentas['kpis']['total_tickets'], 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MÓDULO 2: CRÉDITOS, CARTERA & COBRANZAS                             -->
        <!-- =================================================================== -->
        <div x-show="tabActiva === 'creditos'" class="space-y-5" x-cloak>
            
            <!-- 1. TARJETAS KPI POWERBI (CRÉDITOS) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Total Créditos Otorgados -->
                <div class="bg-gradient-to-br from-indigo-700 to-indigo-900 text-white p-4 rounded-2xl shadow-md space-y-1 relative overflow-hidden">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-200 block">Créditos Otorgados</span>
                    <span class="text-xl sm:text-2xl font-mono font-black block tracking-tight">
                        {{ number_format($metricasCreditos['kpis']['total_creditos'], 0, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-indigo-200 block">Operaciones en Período</span>
                </div>

                <!-- Valor Base (Capital Otorgado) -->
                <div class="bg-gradient-to-br from-blue-600 to-cyan-700 text-white p-4 rounded-2xl shadow-md space-y-1 relative overflow-hidden">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-blue-100 block">Valor Base (Capital)</span>
                    <span class="text-lg sm:text-xl font-mono font-black block tracking-tight">
                        $ {{ number_format($metricasCreditos['kpis']['valor_base'], 2, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-blue-200 block">Monto Original Vendido</span>
                </div>

                <!-- Total en Cuotas (Financiado) -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400 block">Total en Cuotas</span>
                    <span class="text-lg sm:text-xl font-mono font-black text-slate-900 dark:text-white block">
                        $ {{ number_format($metricasCreditos['kpis']['total_en_cuotas'], 2, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 block">Capital + Intereses</span>
                </div>

                <!-- Total Cobrado -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 block">Total Cobrado</span>
                    <span class="text-lg sm:text-xl font-mono font-black text-emerald-600 dark:text-emerald-400 block">
                        $ {{ number_format($metricasCreditos['kpis']['total_cobrado'], 2, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 block">Recupero de Cartera</span>
                </div>

                <!-- Deudas Pendientes (Saldo Restante) -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 block">Deudas Pendientes</span>
                    <span class="text-lg sm:text-xl font-mono font-black text-rose-600 dark:text-rose-400 block">
                        $ {{ number_format($metricasCreditos['kpis']['total_deudas'], 2, ',', '.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 block">Saldo en la Calle</span>
                </div>

                <!-- Tasa de Recupero -->
                <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 block">Tasa de Cobranza</span>
                    <span class="text-xl sm:text-2xl font-mono font-black text-amber-600 dark:text-amber-400 block">
                        {{ number_format($metricasCreditos['kpis']['tasa_cobranza'], 1) }}%
                    </span>
                    <span class="text-[10px] text-slate-400 block">% Cobrado s/ Total</span>
                </div>
            </div>

            <!-- 2. SELECTOR DE DIMENSIÓN DE CRÉDITOS -->
            <div class="bg-white dark:bg-slate-800 p-3 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <span>💳</span>
                        <span>Agrupar Créditos Por:</span>
                    </span>

                    @php
                        $dimsCred = [
                            'sucursal'    => ['label' => 'Por Sucursal', 'icon' => '🏢'],
                            'plan_cuotas' => ['label' => 'Por Plan de Cuotas', 'icon' => '🔢'],
                            'mes'         => ['label' => 'Por Mes / Período', 'icon' => '📅'],
                        ];
                    @endphp

                    <div class="flex flex-wrap items-center gap-1">
                        @foreach($dimsCred as $cdKey => $cdCfg)
                            <a href="{{ request()->fullUrlWithQuery(['credito_dimension' => $cdKey, 'tab' => 'creditos']) }}"
                               class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer {{ $dimensionCreditos === $cdKey ? 'bg-amber-600 text-white font-black shadow-xs' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200' }}">
                                <span>{{ $cdCfg['icon'] }}</span>
                                <span>{{ $cdCfg['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Buscador de Texto en Tiempo Real de la Tabla de Créditos -->
                <div class="relative w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400">🔍</span>
                    <input type="text" x-model="busquedaTabla" placeholder="Buscar en créditos..."
                           class="w-full pl-8 pr-3 py-1.5 rounded-xl text-xs border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-amber-500 shadow-2xs">
                </div>
            </div>

            <!-- 3. TABLA RESUMEN CRÉDITOS POWERBI -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-3 bg-slate-50 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <span>📋</span>
                        <span>Resumen Financiero de Cartera ({{ count($metricasCreditos['filas']) }} registros)</span>
                    </span>
                    <span class="text-[11px] text-slate-500 font-mono">
                        Filtro: {{ $dimsCred[$dimensionCreditos]['label'] ?? 'Sucursal' }}
                    </span>
                </div>

                <div class="overflow-x-auto max-h-[560px] overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-900/90 text-slate-600 dark:text-slate-400 text-[10px] uppercase font-black tracking-wider border-b border-slate-200 dark:border-slate-700 sticky top-0 z-10 select-none">
                            <tr>
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">
                                    <a href="{{ $sortUrl('dimension', 'creditos') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Dimensión">
                                        <span>{{ $dimsCred[$dimensionCreditos]['label'] ?? 'Dimensión' }}</span>
                                        {!! $sortIndicator('dimension') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-center w-28">
                                    <a href="{{ $sortUrl('cant_creditos', 'creditos') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Cantidad de Créditos">
                                        <span>Créditos</span>
                                        {!! $sortIndicator('cant_creditos') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-right w-36">
                                    <a href="{{ $sortUrl('valor_base', 'creditos') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Valor Base">
                                        <span>Valor Base ($)</span>
                                        {!! $sortIndicator('valor_base') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-right w-36">
                                    <a href="{{ $sortUrl('total_cuotas', 'creditos') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Total en Cuotas">
                                        <span>Total en Cuotas ($)</span>
                                        {!! $sortIndicator('total_cuotas') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-right w-36">
                                    <a href="{{ $sortUrl('total_cobrado', 'creditos') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Total Cobrado">
                                        <span>Total Cobrado ($)</span>
                                        {!! $sortIndicator('total_cobrado') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-right w-36">
                                    <a href="{{ $sortUrl('deudas', 'creditos') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Deudas Pendientes">
                                        <span>Deudas Pendientes ($)</span>
                                        {!! $sortIndicator('deudas') !!}
                                    </a>
                                </th>
                                <th class="py-2.5 px-3 text-center w-28">
                                    <a href="{{ $sortUrl('tasa_recupero', 'creditos') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por % Recupero">
                                        <span>% Recupero</span>
                                        {!! $sortIndicator('tasa_recupero') !!}
                                    </a>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-sans">
                            @forelse($metricasCreditos['filas'] as $i => $crow)
                                <tr class="hover:bg-amber-50/50 dark:hover:bg-slate-700/40 transition"
                                    x-show="!busquedaTabla || '{{ addslashes(mb_strtolower($crow['dimension'])) }}'.includes(busquedaTabla.toLowerCase())">
                                    <td class="py-2.5 px-3 font-mono text-[10px] text-slate-400">{{ $i + 1 }}</td>
                                    <td class="py-2.5 px-3 font-bold text-slate-900 dark:text-white text-xs">
                                        {{ $crow['dimension'] }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-800 dark:text-slate-200">
                                        {{ number_format($crow['cant_creditos'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-700 dark:text-slate-300">
                                        $ {{ number_format($crow['valor_base'], 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        $ {{ number_format($crow['total_cuotas'], 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                        $ {{ number_format($crow['total_cobrado'], 2, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-700 dark:text-rose-400">
                                        $ {{ number_format($crow['deudas'], 2, ',', '.') }}
                                    </td>
                                    
                                    <!-- % Recupero con Badge -->
                                    <td class="py-2.5 px-3 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-black"
                                              :class="{{ $crow['tasa_recupero'] }} >= 80 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : ({{ $crow['tasa_recupero'] }} >= 50 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300')">
                                            {{ number_format($crow['tasa_recupero'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400 text-xs">
                                        No se encontraron registros de créditos para los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-100 dark:bg-slate-900 border-t-2 border-slate-300 dark:border-slate-700 font-bold sticky bottom-0 z-10 text-xs">
                            <tr>
                                <td colspan="2" class="py-2.5 px-3 uppercase font-black text-slate-800 dark:text-slate-100">
                                    TOTAL CARTERA CONSOLIDADA
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-black text-slate-900 dark:text-white">
                                    {{ number_format($metricasCreditos['kpis']['total_creditos'], 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-slate-800 dark:text-slate-200">
                                    $ {{ number_format($metricasCreditos['kpis']['valor_base'], 2, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-slate-900 dark:text-white">
                                    $ {{ number_format($metricasCreditos['kpis']['total_en_cuotas'], 2, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-emerald-700 dark:text-emerald-400">
                                    $ {{ number_format($metricasCreditos['kpis']['total_cobrado'], 2, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-black text-rose-700 dark:text-rose-400">
                                    $ {{ number_format($metricasCreditos['kpis']['total_deudas'], 2, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-mono font-black text-amber-700 dark:text-amber-400">
                                    {{ number_format($metricasCreditos['kpis']['tasa_cobranza'], 1) }}%
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

