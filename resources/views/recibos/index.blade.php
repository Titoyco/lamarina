<x-app-layout>
    <!-- Header Compacto -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-3 gap-2">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                <span>Recibos de Cobranza</span>
                <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                    Ingresos a Cuenta Corriente y Cobro de Cuotas
                </span>
            </h1>
        </div>
        
        <div class="flex items-center space-x-2">
            <a href="{{ route('recibos.index', array_merge(request()->query(), ['export' => 'excel'])) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-1.5 px-3.5 rounded-md shadow-sm transition flex items-center space-x-1.5 uppercase"
               title="Exportar todos los recibos filtrados a Excel / CSV">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>EXPORTAR EXCEL</span>
            </a>

            <a href="{{ route('recibos.create') }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-3.5 rounded-md shadow-sm transition flex items-center space-x-1 uppercase">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>+ Emitir Recibo</span>
            </a>
        </div>
    </div>

    <!-- KPIs Rápidos de Cobranza -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-white dark:bg-slate-800/90 p-3 rounded-md border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Total Cobrado Hoy</span>
                <span class="text-lg font-black text-emerald-700 dark:text-emerald-400 font-mono tabular-nums">
                    $ {{ number_format($totalCobradoHoy, 2, ',', '.') }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center font-bold text-lg">
                💵
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800/90 p-3 rounded-md border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Recibos Emitidos Hoy</span>
                <span class="text-lg font-black text-slate-800 dark:text-white font-mono tabular-nums">
                    {{ $recibosHoyCount }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-lg">
                📄
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800/90 p-3 rounded-md border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between sm:col-span-2">
            <div>
                <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase block">Jurisdicción Activa</span>
                <span class="text-sm font-bold text-slate-800 dark:text-slate-100">
                    {{ config('app.sucursal_activa')->nombre ?? 'Sucursal' }} — {{ $provinciaActivaNombre }}
                </span>
                <p class="text-[10px] text-slate-400 dark:text-slate-500">Los cobros se emiten con numeración correlativa oficial de la sucursal.</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-300 flex items-center justify-center font-bold text-lg">
                📍
            </div>
        </div>
    </div>

    <!-- Panel de Búsqueda y Filtros -->
    <div class="bg-slate-50 dark:bg-slate-800/70 p-2.5 rounded-md border border-slate-200 dark:border-slate-700/80 mb-4 shadow-sm">
        <form action="{{ route('recibos.index') }}" method="GET" class="space-y-2">
            @php
                $valorPorDefecto = 'provincia_' . ($provinciaActivaId ?? 2);
                $valorSeleccionado = $filtroSucursal !== null ? $filtroSucursal : $valorPorDefecto;
            @endphp

            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Buscar por N° de recibo, cliente (nombre o DNI)..." 
                           class="w-full text-xs py-1.5 px-3 rounded-md border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm">
                </div>

                <div class="flex items-center space-x-2">
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" title="Fecha Desde"
                           class="text-xs py-1.5 px-2 rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm">
                    <span class="text-xs text-slate-400 dark:text-slate-500">a</span>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" title="Fecha Hasta"
                           class="text-xs py-1.5 px-2 rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm">
                </div>

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-4 rounded-md shadow-sm transition">
                    Filtrar
                </button>

                @if(request()->anyFilled(['search', 'sucursal', 'fecha_desde', 'fecha_hasta', 'estado']))
                    <a href="{{ route('recibos.index') }}" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs py-1.5 px-3 rounded-md transition flex items-center">
                        Limpiar
                    </a>
                @endif
            </div>

            <!-- Filtros Secundarios -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1.5 border-t border-slate-200/70 dark:border-slate-700/60">
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

                <select name="estado" onchange="this.form.submit()" class="text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-600 dark:text-slate-300">
                    <option value="todos" {{ request('estado') === 'todos' ? 'selected' : '' }}>Todos los Estados</option>
                    <option value="activos" {{ request('estado', 'activos') === 'activos' ? 'selected' : '' }}>Solo Activos / Válidos</option>
                    <option value="anulados" {{ request('estado') === 'anulados' ? 'selected' : '' }}>Solo Anulados</option>
                </select>
            </div>
        </form>
    </div>

    @php
        $sort = $sort ?? request()->get('sort', 'fecha');
        $direction = $direction ?? request()->get('direction', 'desc');
        $nextDirection = function($col) use ($sort, $direction) {
            return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
        };
        $sortUrl = function($col) use ($nextDirection) {
            return route('recibos.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
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

    <!-- Grilla de Recibos -->
    <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm overflow-hidden mb-4">
        <div class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 px-3 py-1 flex justify-between items-center text-[10px]">
            <span class="text-slate-600 dark:text-slate-300 font-bold">Listado de Comprobantes de Cobranza</span>
            <span class="font-mono text-slate-500 dark:text-slate-400">{{ number_format($recibos->total(), 0, ',', '.') }} recibos registrados</span>
        </div>

        <table class="w-full text-left border-collapse font-sans text-xs">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-900/80 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300 dark:border-slate-700 select-none">
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-32">
                        <a href="{{ $sortUrl('numero_recibo') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por N° Recibo">
                            <span>N° Recibo</span>
                            {!! $sortIndicator('numero_recibo') !!}
                        </a>
                    </th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-28 text-center">
                        <a href="{{ $sortUrl('fecha') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Fecha y Hora">
                            <span>Fecha y Hora</span>
                            {!! $sortIndicator('fecha') !!}
                        </a>
                    </th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-36">
                        <a href="{{ $sortUrl('sucursal') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Sucursal">
                            <span>Sucursal</span>
                            {!! $sortIndicator('sucursal') !!}
                        </a>
                    </th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60">
                        <a href="{{ $sortUrl('cliente') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Cliente">
                            <span>Cliente</span>
                            {!! $sortIndicator('cliente') !!}
                        </a>
                    </th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-28 text-center">
                        <a href="{{ $sortUrl('medio_pago') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Medio de Pago">
                            <span>Medio Pago</span>
                            {!! $sortIndicator('medio_pago') !!}
                        </a>
                    </th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right w-28">
                        <a href="{{ $sortUrl('importe_total') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Total Cobrado">
                            <span>Total Cobrado</span>
                            {!! $sortIndicator('importe_total') !!}
                        </a>
                    </th>
                    <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-center w-20">
                        <a href="{{ $sortUrl('estado') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Estado">
                            <span>Estado</span>
                            {!! $sortIndicator('estado') !!}
                        </a>
                    </th>
                    <th class="py-1.5 px-3 text-center w-28">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                @forelse($recibos as $r)
                    @php
                        $bgFila = $r->anulado 
                            ? 'bg-rose-50/40 dark:bg-rose-950/30 text-slate-400 dark:text-slate-400' 
                            : 'bg-white dark:bg-slate-800/50 hover:bg-blue-50/40 dark:hover:bg-slate-700/60 text-slate-800 dark:text-slate-200';
                    @endphp
                    <tr class="{{ $bgFila }} transition-colors">
                        <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/50 font-mono font-bold">
                            <a href="{{ route('recibos.show', $r) }}" class="{{ $r->anulado ? 'line-through text-slate-400 dark:text-slate-500' : 'text-blue-700 dark:text-blue-400 hover:underline' }}">
                                {{ $r->numero_formateado }}
                            </a>
                        </td>

                        <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/50 text-center font-mono text-[11px]">
                            <div class="text-slate-800 dark:text-slate-200 font-bold">{{ $r->fecha ? $r->fecha->format('d/m/Y') : '-' }}</div>
                            <div class="text-[9px] text-slate-400 dark:text-slate-500">{{ $r->fecha ? $r->fecha->format('H:i') : '' }}</div>
                        </td>

                        <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/50 text-[11px] truncate max-w-[140px]" title="{{ $r->sucursal?->nombre }}">
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $r->sucursal?->codigo_sucursal }}</span>
                            <span class="text-slate-500 dark:text-slate-400">• {{ $r->sucursal?->nombre }}</span>
                        </td>

                        <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/50 truncate max-w-[180px]">
                            <div class="font-bold uppercase text-[11px] truncate text-slate-800 dark:text-slate-100" title="{{ $r->cliente?->nombre }}">
                                <a href="{{ route('clientes.show', $r->id_cliente) }}" class="hover:text-blue-700 dark:hover:text-blue-400 hover:underline">
                                    {{ $r->cliente?->nombre ?? 'CLIENTE #' . $r->id_cliente }}
                                </a>
                            </div>
                            <div class="text-[9px] font-bold text-slate-400 dark:text-slate-500">
                                <a href="{{ route('clientes.estado-cuenta', $r->id_cliente) }}" class="hover:underline hover:text-blue-600 dark:hover:text-blue-400">
                                    DNI: {{ $r->cliente?->dni ?? '-' }}
                                </a>
                            </div>
                        </td>

                        <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/50 text-center">
                            <span class="bg-slate-100 dark:bg-slate-700/80 text-slate-700 dark:text-slate-300 text-[10px] font-semibold px-2 py-0.5 rounded border border-slate-200 dark:border-slate-600">
                                {{ $r->medio_pago }}
                            </span>
                        </td>

                        <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/50 text-right font-mono font-black tabular-nums {{ $r->anulado ? 'line-through text-slate-400 dark:text-slate-500' : 'text-emerald-700 dark:text-emerald-400' }} text-xs">
                            $ {{ number_format($r->importe_total, 2, ',', '.') }}
                        </td>

                        <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/50 text-center">
                            @if($r->anulado)
                                <span class="bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800/80 text-[9px] font-black px-1.5 py-0.5 rounded uppercase">Anulado</span>
                            @else
                                <span class="bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/80 text-[9px] font-black px-1.5 py-0.5 rounded uppercase">Válido</span>
                            @endif
                        </td>

                        <td class="py-1.5 px-2 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('recibos.show', $r) }}" 
                                   class="bg-slate-100 dark:bg-slate-700 hover:bg-blue-600 dark:hover:bg-blue-600 hover:text-white text-slate-700 dark:text-slate-200 px-2 py-0.5 rounded text-[10px] font-bold border border-slate-200 dark:border-slate-600 shadow-xs uppercase transition-all">
                                    Ver
                                </a>
                                <a href="{{ route('recibos.imprimir', $r) }}" target="_blank"
                                   class="bg-slate-100 dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 hover:text-white text-slate-700 dark:text-slate-200 px-2 py-0.5 rounded text-[10px] font-bold border border-slate-200 dark:border-slate-600 shadow-xs uppercase transition-all">
                                    🖨️
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 dark:text-slate-500 font-medium text-xs italic bg-slate-50 dark:bg-slate-900/40">
                            No se encontraron recibos de cobranza registrados para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($recibos->hasPages())
            <div class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-700 text-xs">
                {{ $recibos->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
