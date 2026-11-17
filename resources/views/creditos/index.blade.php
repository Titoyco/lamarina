<x-app-layout>
    <!-- Header Compacto -->
    <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                <span>Cartera de Créditos</span>
                <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">Monitoreo y Estados de Cuenta</span>
            </h1>
        </div>
        
        @php
            $filtroSucursal = request('sucursal');
            $valorPorDefecto = 'provincia_' . ($provinciaActivaId ?? 2);
            $valorSeleccionado = $filtroSucursal !== null ? $filtroSucursal : $valorPorDefecto;
        @endphp

        <div class="flex items-center space-x-2">
            @if($valorSeleccionado === 'all')
                <span class="inline-flex items-center text-[10px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 px-2 py-0.5 rounded shadow-xs">
                    🌐 Todas las Sucursales y Provincias
                </span>
            @elseif(str_starts_with($valorSeleccionado, 'provincia_'))
                @php
                    $pId = (int) str_replace('provincia_', '', $valorSeleccionado);
                    $pNombre = ($pId === 2) ? 'Tierra del Fuego' : 'Chubut';
                @endphp
                <span class="inline-flex items-center text-[10px] font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 px-2 py-0.5 rounded shadow-xs">
                    📍 Ámbito Provincial: {{ $pNombre }}
                </span>
            @else
                @php
                    $sucActualObj = $sucursales->firstWhere('id', $valorSeleccionado);
                @endphp
                <span class="inline-flex items-center text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 px-2 py-0.5 rounded shadow-xs">
                    🏢 Sucursal: {{ $sucActualObj?->nombre ?? 'Sucursal #' . $valorSeleccionado }}
                </span>
            @endif

            <a href="{{ route('creditos.index', array_merge(request()->query(), ['export' => 'excel'])) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5 no-print"
               title="Exportar toda la consulta a Excel (CSV)">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>EXPORTAR EXCEL</span>
            </a>

            <button type="button" onclick="window.print()" 
                    class="bg-slate-700 hover:bg-slate-800 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-all shadow-sm flex items-center space-x-1.5 no-print"
                    title="Imprimir tabla de créditos de la consulta">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>IMPRIMIR</span>
            </button>
        </div>
    </div>

    <!-- Panel de Búsqueda y Filtros (Ribbon Denso) -->
    <div class="bg-slate-50 dark:bg-slate-800/70 p-2.5 rounded-md border border-slate-200 dark:border-slate-700/80 mb-4 shadow-sm no-print">
        <form action="{{ route('creditos.index') }}" method="GET" class="space-y-2">
            @if(request('cliente_id'))
                <input type="hidden" name="cliente_id" value="{{ request('cliente_id') }}">
            @endif
            
            <!-- Buscador Principal -->
            <div class="flex gap-2">
                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nro crédito, cliente o autorizado..." 
                           class="w-full text-xs py-1.5 px-3 rounded-md border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-4 rounded-md shadow-sm transition">
                    Buscar
                </button>
                @if(request()->anyFilled(['search', 'sucursal', 'tipo', 'estado', 'has_interes', 'has_entrego', 'has_saldo']))
                    <a href="{{ route('creditos.index') }}" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs py-1.5 px-3 rounded-md transition flex items-center">
                        Limpiar
                    </a>
                @endif
            </div>
            
            <!-- Filtros Secundarios -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 pt-1.5 border-t border-slate-200/70 dark:border-slate-700/60">
                <select name="sucursal" onchange="this.form.submit()" class="text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-semibold text-slate-700 dark:text-slate-200">
                    <option value="provincia_{{ $provinciaActivaId ?? 2 }}" {{ $valorSeleccionado === ('provincia_' . ($provinciaActivaId ?? 2)) ? 'selected' : '' }}>
                        📍 Toda la Provincia: {{ $provinciaActivaNombre ?? 'TDF' }} (Sucursal Activa)
                    </option>

                    <option value="all" {{ $valorSeleccionado === 'all' ? 'selected' : '' }}>
                        🌐 Todas las Sucursales (Todas las Provincias)
                    </option>

                    @if(($provinciaActivaId ?? 2) === 2)
                        <option value="provincia_1" {{ $valorSeleccionado === 'provincia_1' ? 'selected' : '' }}>
                            📍 Toda la Provincia: Chubut
                        </option>
                    @else
                        <option value="provincia_2" {{ $valorSeleccionado === 'provincia_2' ? 'selected' : '' }}>
                            📍 Toda la Provincia: Tierra del Fuego
                        </option>
                    @endif

                    <optgroup label="── Sucursales Tierra del Fuego ──">
                        @foreach($sucursales->filter(fn($s) => $s->isTdf()) as $suc)
                            <option value="{{ $suc->id }}" {{ (string)$valorSeleccionado === (string)$suc->id ? 'selected' : '' }}>
                                {{ $suc->nombre }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="── Sucursales Chubut ──">
                        @foreach($sucursales->filter(fn($s) => $s->isChubut()) as $suc)
                            <option value="{{ $suc->id }}" {{ (string)$valorSeleccionado === (string)$suc->id ? 'selected' : '' }}>
                                {{ $suc->nombre }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>

                <select name="tipo" onchange="this.form.submit()" class="text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-600 dark:text-slate-300">
                    <option value="all">Todos los Tipos</option>
                    <option value="C" {{ request('tipo') === 'C' ? 'selected' : '' }}>C - Crédito</option>
                    <option value="D" {{ request('tipo') === 'D' ? 'selected' : '' }}>D - Débito</option>
                    <option value="R" {{ request('tipo') === 'R' ? 'selected' : '' }}>R - Refinanciación</option>
                </select>

                <select name="estado" onchange="this.form.submit()" class="text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-600 dark:text-slate-300">
                    <option value="all" {{ request('estado') === 'all' ? 'selected' : '' }}>Todos los Estados</option>
                    <option value="abiertos" {{ request('estado', 'abiertos') === 'abiertos' ? 'selected' : '' }}>Abiertos</option>
                    <option value="cancelados" {{ request('estado') === 'cancelados' ? 'selected' : '' }}>Cancelados</option>
                </select>
            </div>

            <!-- Filtros Financieros Rápidos (Checkboxes) -->
            <div class="flex items-center space-x-5 pt-2 mt-2 border-t border-slate-200/70 dark:border-slate-700/60 text-[10px] text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider">
                <label class="flex items-center space-x-1.5 cursor-pointer hover:text-blue-700 dark:hover:text-blue-400 transition-colors">
                    <input type="checkbox" name="has_interes" value="1" {{ request('has_interes') ? 'checked' : '' }} 
                           class="rounded border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-blue-600 focus:ring-blue-500 h-3 w-3" onchange="this.form.submit()">
                    <span>Interés > 0</span>
                </label>
                
                <label class="flex items-center space-x-1.5 cursor-pointer hover:text-blue-700 dark:hover:text-blue-400 transition-colors">
                    <input type="checkbox" name="has_entrego" value="1" {{ request('has_entrego') ? 'checked' : '' }} 
                           class="rounded border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-blue-600 focus:ring-blue-500 h-3 w-3" onchange="this.form.submit()">
                    <span>Entregó > 0</span>
                </label>

                <label class="flex items-center space-x-1.5 cursor-pointer hover:text-blue-700 dark:hover:text-blue-400 transition-colors">
                    <input type="checkbox" name="has_saldo" value="1" {{ request('has_saldo') ? 'checked' : '' }} 
                           class="rounded border-slate-400 dark:border-slate-600 bg-white dark:bg-slate-900 text-blue-600 focus:ring-blue-500 h-3 w-3" onchange="this.form.submit()">
                    <span>A Pagar > 0</span>
                </label>
            </div>
        </form>
    </div>

    <!-- Resumen Superior de Créditos Listados (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
        <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-200 dark:border-slate-700/80 p-2.5 px-3 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 flex items-center justify-center font-black text-sm border border-blue-200 dark:border-blue-800/80">
                    #
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Cantidad de Créditos
                    </div>
                    <div class="text-base font-black text-slate-800 dark:text-slate-100 font-mono">
                        {{ number_format($totalCreditos ?? $creditos->total(), 0, ',', '.') }}
                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 lowercase">créditos</span>
                    </div>
                </div>
            </div>
            @if(request('search') || request('cliente_id'))
                <span class="text-[9px] bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 px-1.5 py-0.5 rounded font-bold border border-blue-200 dark:border-blue-800">
                    Filtrados
                </span>
            @endif
        </div>

        <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-200 dark:border-slate-700/80 p-2.5 px-3 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 flex items-center justify-center font-black text-sm border border-emerald-200 dark:border-emerald-800/80">
                    $
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Total a Pagar
                    </div>
                    <div class="text-base font-black text-emerald-700 dark:text-emerald-400 font-mono tabular-nums">
                        $ {{ number_format($totalAPagar ?? 0, 2, ',', '.') }}
                    </div>
                </div>
            </div>
            <span class="text-[9px] bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 px-1.5 py-0.5 rounded font-bold border border-emerald-200 dark:border-emerald-800">
                Saldo Exigible
            </span>
        </div>
    </div>

    <!-- Data-Grid Estilo Excel -->
    <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm overflow-hidden mb-4 print:border-none print:shadow-none">
        
        <!-- Encabezado exclusivo para Impresión en Papel / PDF -->
        <div class="hidden print:block p-3 border-b border-slate-400 mb-2">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-base font-black uppercase tracking-tight text-slate-900">La Marina - Cartera de Créditos</h1>
                    <div class="text-[11px] text-slate-700">
                        @if($valorSeleccionado === 'all')
                            Ámbito: Todas las Sucursales y Provincias
                        @elseif(str_starts_with($valorSeleccionado, 'provincia_'))
                            Ámbito: Provincia {{ $pNombre ?? ($provinciaActivaNombre ?? 'TDF') }}
                        @else
                            Ámbito: {{ $sucActualObj?->nombre ?? 'Sucursal #' . $valorSeleccionado }}
                        @endif
                    </div>
                </div>
                <div class="text-right text-[11px]">
                    <div class="font-bold">Total Créditos: {{ number_format($totalCreditos ?? $creditos->total(), 0, ',', '.') }}</div>
                    <div class="font-black text-emerald-800">Total A Pagar: $ {{ number_format($totalAPagar ?? 0, 2, ',', '.') }}</div>
                    <div class="text-[9px] text-slate-500">Fecha impresión: {{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>

        <!-- Indicador de Ámbito Activo / Filtro por Cliente (Limpio sin duplicar totales) -->
        @if(request('cliente_id') && isset($creditos->first()->cliente))
            <div class="bg-blue-50 dark:bg-blue-950/50 border-b border-blue-200 dark:border-blue-800 px-3 py-1.5 flex justify-between items-center no-print">
                <span class="text-[10px] text-blue-800 dark:text-blue-300 font-bold uppercase tracking-wider">Filtrando por cliente: {{ $creditos->first()->cliente->nombre }}</span>
                <a href="{{ route('creditos.index') }}" class="text-[10px] text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-200 font-black">✕ Quitar filtro</a>
            </div>
        @else
            <div class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 px-3 py-1 flex justify-between items-center text-[10px] no-print">
                <div class="flex items-center space-x-1.5">
                    @if($valorSeleccionado === 'all')
                        <span class="text-indigo-800 dark:text-indigo-300 font-bold">🌐 Mostrando créditos de todas las sucursales y provincias.</span>
                    @elseif(str_starts_with($valorSeleccionado, 'provincia_'))
                        <span class="text-blue-800 dark:text-blue-300 font-bold">📍 Mostrando créditos de clientes de {{ $pNombre ?? ($provinciaActivaNombre ?? 'la provincia') }}.</span>
                        <span class="text-slate-400 dark:text-slate-500 hidden sm:inline">• (Seleccione "Todas las Sucursales" para ver todo el país).</span>
                    @else
                        @php $sucActualObj = $sucursales->firstWhere('id', $valorSeleccionado); @endphp
                        <span class="text-emerald-800 dark:text-emerald-300 font-bold">🏢 Mostrando créditos asociados a sucursal: {{ $sucActualObj?->nombre }}.</span>
                    @endif
                </div>
            </div>
        @endif

        @php
            $nextDirection = function($col) use ($sort, $direction) {
                return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
            };
            $sortUrl = function($col) use ($nextDirection) {
                return route('creditos.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
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

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse font-sans text-xs">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-900/80 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300 dark:border-slate-700 select-none">
                        
                        <!-- ID Gestión -->
                        <th class="py-1.5 px-2.5 border-r border-slate-200 dark:border-slate-700/60 w-24 min-w-[95px]">
                            <a href="{{ $sortUrl('credito') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por ID Gestión">
                                <span>ID Gestión</span>
                                {!! $sortIndicator('credito') !!}
                            </a>
                        </th>

                        <!-- Fecha -->
                        <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700/60 w-20 min-w-[85px] text-center">
                            <a href="{{ $sortUrl('fecha') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Fecha">
                                <span>Fecha</span>
                                {!! $sortIndicator('fecha') !!}
                            </a>
                        </th>

                        <!-- Cliente -->
                        <th class="py-1.5 px-2.5 border-r border-slate-200 dark:border-slate-700/60 w-36 max-w-[140px]">
                            <a href="{{ $sortUrl('cliente') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Cliente">
                                <span>Cliente</span>
                                {!! $sortIndicator('cliente') !!}
                            </a>
                        </th>

                        <!-- Cuota -->
                        <th class="py-1.5 px-2 border-r border-slate-200 dark:border-slate-700/60 text-center w-14 min-w-[60px]">
                            <a href="{{ $sortUrl('cuota') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Cantidad de Cuotas">
                                <span>Cuota</span>
                                {!! $sortIndicator('cuota') !!}
                            </a>
                        </th>

                        <!-- Valor Cuota (NUEVA COLUMNA) -->
                        <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-24 min-w-[95px] whitespace-nowrap">
                            <a href="{{ $sortUrl('valor_cuota') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Valor Cuota">
                                <span>Valor Cuota</span>
                                {!! $sortIndicator('valor_cuota') !!}
                            </a>
                        </th>

                        <!-- Total (Antes Valor Base) -->
                        <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-28 min-w-[110px] whitespace-nowrap">
                            <a href="{{ $sortUrl('total') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Total Financiado">
                                <span>Total</span>
                                {!! $sortIndicator('total') !!}
                            </a>
                        </th>

                        <!-- Intereses -->
                        <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-24 min-w-[95px] whitespace-nowrap">
                            <a href="{{ $sortUrl('intereses') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Intereses">
                                <span>Intereses</span>
                                {!! $sortIndicator('intereses') !!}
                            </a>
                        </th>

                        <!-- Entregó -->
                        <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-24 min-w-[95px] whitespace-nowrap">
                            <a href="{{ $sortUrl('entrego') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Entregó">
                                <span>Entregó</span>
                                {!! $sortIndicator('entrego') !!}
                            </a>
                        </th>

                        <!-- A Pagar (Reducido a w-28 min-w-[110px]) -->
                        <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-28 min-w-[110px] whitespace-nowrap">
                            <a href="{{ $sortUrl('a_pagar') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Saldo A Pagar">
                                <span>A Pagar</span>
                                {!! $sortIndicator('a_pagar') !!}
                            </a>
                        </th>

                        <!-- Acciones -->
                        <th class="py-1.5 px-2 text-center w-16 min-w-[70px] no-print">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse($creditos as $credito)
                        @php
                            $esCancelado = !is_null($credito->fecha_cancelacion);
                            $bgFila = $esCancelado 
                                ? 'bg-slate-50/60 dark:bg-slate-900/40 text-slate-500 dark:text-slate-400' 
                                : 'bg-white dark:bg-slate-800/50 hover:bg-blue-50/40 dark:hover:bg-slate-700/60 text-slate-800 dark:text-slate-200';
                        @endphp
                        <tr class="{{ $bgFila }} transition-colors">
                            
                            <!-- Columna ID -->
                            <td class="py-1.5 px-2.5 border-r border-slate-200/60 dark:border-slate-700/50 whitespace-nowrap">
                                <div class="flex flex-col space-y-0.5">
                                    <a href="{{ route('creditos.show', $credito) }}" class="font-mono font-bold {{ $esCancelado ? 'text-slate-500 dark:text-slate-400 line-through' : 'text-blue-700 dark:text-blue-400 hover:underline' }} text-[11px]">
                                        {{ $credito->full_identificador }}
                                    </a>
                                    @if($esCancelado)
                                        <span class="bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-300 text-[8px] font-black px-1 py-0.5 rounded uppercase w-max">Cancelado</span>
                                    @else
                                        <span class="bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/80 text-[8px] font-black px-1 py-0.5 rounded uppercase w-max">Abierto</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Columna Fecha -->
                            <td class="py-1.5 px-2 text-center font-mono border-r border-slate-200/60 dark:border-slate-700/50 whitespace-nowrap">
                                <div class="text-[10px] font-bold text-slate-700 dark:text-slate-300">
                                    {{ $credito->fecha_credito ? \Carbon\Carbon::parse($credito->fecha_credito)->format('d/m/Y') : '-' }}
                                </div>
                                <div class="text-[9px] text-slate-400 dark:text-slate-500">
                                    {{ $credito->fecha_credito ? \Carbon\Carbon::parse($credito->fecha_credito)->format('H:i') : '' }}
                                </div>
                            </td>

                            <!-- Columna Cliente (Achicada un 25%, con truncate) -->
                            <td class="py-1.5 px-2.5 border-r border-slate-200/60 dark:border-slate-700/50 w-36 max-w-[140px] truncate">
                                <a href="{{ route('clientes.show', $credito->cliente->id) }}" 
                                   class="font-bold uppercase text-[11px] truncate text-slate-800 dark:text-slate-100 hover:text-blue-600 dark:hover:text-blue-400 hover:underline block" 
                                   title="Ver legajo de {{ $credito->cliente->nombre }}">
                                    {{ $credito->cliente->nombre }}
                                </a>
                                <div class="text-[9px] font-bold text-slate-400 dark:text-slate-500 flex items-center space-x-1 truncate">
                                    <a href="{{ route('clientes.estado-cuenta', $credito->cliente->id) }}" 
                                       class="hover:text-blue-600 dark:hover:text-blue-400 hover:underline"
                                       title="Ver estado de cuenta de {{ $credito->cliente->nombre }}">
                                        DNI: {{ $credito->cliente->dni }}
                                    </a>
                                    <span>•</span>
                                    <span class="text-slate-500 dark:text-slate-400 font-semibold" title="Sucursal del cliente: {{ $credito->cliente->sucursal?->nombre ?? '-' }}">
                                        {{ $credito->cliente->sucursal?->codigo_sucursal ?? $credito->sucursal?->codigo_sucursal ?? 'SUC' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Columna Cuota que va -->
                            <td class="py-1.5 px-2 text-center font-mono border-r border-slate-200/60 dark:border-slate-700/50 whitespace-nowrap">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-black {{ $esCancelado ? 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500' : 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/80' }}"
                                      title="Lote origen: #{{ $credito->presentacion?->numero ?? '-' }} | Cuotas totales: {{ $credito->cantidad_cuotas }}">
                                    {{ $credito->cuota_progreso }}
                                </span>
                            </td>

                            <!-- Columna Valor Cuota -->
                            <td class="py-1.5 px-3 text-right font-mono font-bold border-r border-slate-200/60 dark:border-slate-700/50 tabular-nums whitespace-nowrap text-slate-800 dark:text-slate-200">
                                $ {{ number_format($credito->valor_cuota, 2, ',', '.') }}
                            </td>

                            <!-- Columna Total (Antes Valor Base) -->
                            <td class="py-1.5 px-3 text-right font-mono font-bold border-r border-slate-200/60 dark:border-slate-700/50 tabular-nums whitespace-nowrap text-slate-900 dark:text-slate-100">
                                $ {{ number_format($credito->total_valor_base ?? 0, 2, ',', '.') }}
                            </td>

                            <!-- Intereses: Bordó / Rojo Oscuro -->
                            <td class="py-1.5 px-3 text-right font-mono font-bold border-r border-slate-200/60 dark:border-slate-700/50 tabular-nums whitespace-nowrap {{ $esCancelado ? 'text-slate-400 dark:text-slate-500' : 'text-red-800 dark:text-red-400' }}">
                                $ {{ number_format($credito->total_intereses ?? 0, 2, ',', '.') }}
                            </td>

                            <!-- Entregó -->
                            <td class="py-1.5 px-3 text-right font-mono font-bold border-r border-slate-200/60 dark:border-slate-700/50 tabular-nums whitespace-nowrap text-slate-900 dark:text-slate-100">
                                $ {{ number_format($credito->total_entrego ?? 0, 2, ',', '.') }}
                            </td>

                            <!-- A Pagar: Verde Esmeralda (Ancho ajustado) -->
                            <td class="py-1.5 px-3 text-right font-mono font-black border-r border-slate-200/60 dark:border-slate-700/50 tabular-nums whitespace-nowrap {{ $esCancelado ? 'line-through text-slate-400 dark:text-slate-500' : 'text-emerald-700 dark:text-emerald-400' }}">
                                $ {{ number_format($credito->total_saldo_actual ?? 0, 2, ',', '.') }}
                            </td>

                            <!-- Acciones -->
                            <td class="py-1 px-2 text-center no-print">
                                <a href="{{ route('creditos.show', $credito) }}" class="inline-flex items-center bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 hover:bg-blue-600 dark:hover:bg-blue-600 text-slate-700 dark:text-slate-200 hover:text-white px-2 py-0.5 rounded text-[10px] font-bold transition-all uppercase shadow-xs">
                                    Detalle →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400 dark:text-slate-500 font-medium text-xs italic bg-slate-50 dark:bg-slate-900/40">
                                No se encontraron créditos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginador -->
        @if($creditos->hasPages())
            <div class="px-2 py-1 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-700 text-xs no-print">
                {{ $creditos->links() }}
            </div>
        @endif
    </div>

    <!-- Estilos de Impresión en Papel / PDF -->
    <style>
        @media print {
            header, nav, aside, .no-print, form {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print\:block {
                display: block !important;
            }
            .print\:border-none {
                border: none !important;
            }
            .print\:shadow-none {
                box-shadow: none !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }
            th, td {
                border: 1px solid #cbd5e1 !important;
                font-size: 8pt !important;
                padding: 3px 4px !important;
            }
            th {
                background-color: #f1f5f9 !important;
                color: #0f172a !important;
            }
            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</x-app-layout>