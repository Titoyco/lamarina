<x-app-layout>
    <!-- Header Compacto -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-4 gap-2">
        <div class="flex items-center space-x-3">
            <a href="{{ route('sistema.index') }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm" title="Volver al Panel de Sistema">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                    <span>Puntos de Venta y Facturación Híbrida</span>
                    <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">Hasar 2G & ARCA WSFE</span>
                </h1>
            </div>
        </div>

        <a href="{{ route('sistema.puntos-venta.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-3 rounded-lg uppercase tracking-wider transition-all shadow-sm flex items-center justify-center space-x-1.5 self-start sm:self-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>+ Nuevo Punto de Venta</span>
        </a>
    </div>

    <!-- Cinta de Filtros (Ribbon) -->
    <div class="bg-white dark:bg-slate-800/90 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm p-3 mb-4">
        <form method="GET" action="{{ route('sistema.puntos-venta.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center space-x-2">
                <label for="id_sucursal" class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Sucursal:</label>
                <select id="id_sucursal" name="id_sucursal" onchange="this.form.submit()"
                        class="text-xs font-medium rounded-md border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1 px-2.5 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todas las Sucursales</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ request('id_sucursal') == $suc->id ? 'selected' : '' }}>
                            {{ $suc->nombre }} ({{ $suc->codigo_sucursal }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <label for="tipo_emision" class="text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Modalidad:</label>
                <select id="tipo_emision" name="tipo_emision" onchange="this.form.submit()"
                        class="text-xs font-medium rounded-md border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 py-1 px-2.5 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos los Tipos</option>
                    <option value="fiscal_hasar" {{ request('tipo_emision') === 'fiscal_hasar' ? 'selected' : '' }}>Controlador Fiscal Hasar 2G</option>
                    <option value="arca_wsfe" {{ request('tipo_emision') === 'arca_wsfe' ? 'selected' : '' }}>Factura Digital ARCA WSFE</option>
                </select>
            </div>

            @if(request('id_sucursal') || request('tipo_emision'))
                <a href="{{ route('sistema.puntos-venta.index') }}"
                   class="text-xs text-rose-600 hover:text-rose-700 font-semibold underline ml-auto">
                    Limpiar Filtros
                </a>
            @endif
        </form>
    </div>

    <!-- Data-Grid Principal -->
    <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="bg-slate-100 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 px-3.5 py-2 flex justify-between items-center">
            <h2 class="font-bold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                <span>Puntos de Emisión Fiscal Habilitados</span>
            </h2>
            <span class="bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[10px] px-2 py-0.5 rounded-full font-mono tabular-nums">
                {{ $puntosVenta->total() }} registros
            </span>
        </div>

        @php
            $sort = $sort ?? request()->get('sort', 'numero');
            $direction = $direction ?? request()->get('direction', 'asc');
            $nextDirection = function($col) use ($sort, $direction) {
                return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
            };
            $sortUrl = function($col) use ($nextDirection) {
                return route('sistema.puntos-venta.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
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
                    <tr class="bg-slate-50 dark:bg-slate-900/60 text-slate-600 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700 select-none">
                        <th class="py-2 px-3 text-center w-28">
                            <a href="{{ $sortUrl('numero') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Pto. Venta">
                                <span>Pto. Venta</span>
                                {!! $sortIndicator('numero') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3">
                            <a href="{{ $sortUrl('nombre') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Nombre">
                                <span>Nombre / Identificación</span>
                                {!! $sortIndicator('nombre') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3">
                            <a href="{{ $sortUrl('sucursal') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Sucursal">
                                <span>Sucursal Asignada</span>
                                {!! $sortIndicator('sucursal') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3">
                            <a href="{{ $sortUrl('tipo_emision') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Modalidad">
                                <span>Modalidad Fiscal</span>
                                {!! $sortIndicator('tipo_emision') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3">Configuración Técnica / IP</th>
                        <th class="py-2 px-3 text-center w-24">
                            <a href="{{ $sortUrl('activo') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Estado">
                                <span>Estado</span>
                                {!! $sortIndicator('activo') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3 text-center w-48">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($puntosVenta as $pv)
                        <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-700/50 transition-colors">
                            <!-- Pto. Venta -->
                            <td class="py-2 px-3 text-center">
                                <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600 tabular-nums">
                                    {{ str_pad($pv->numero, 5, '0', STR_PAD_LEFT) }}
                                </span>
                            </td>

                            <!-- Nombre -->
                            <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-100">
                                <div class="font-bold text-xs text-slate-900 dark:text-white">{{ $pv->nombre }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">ID Int: #{{ $pv->id }}</div>
                            </td>

                            <!-- Sucursal -->
                            <td class="py-2 px-3 text-slate-700 dark:text-slate-300">
                                <div class="font-bold text-xs flex items-center gap-1.5">
                                    <span>{{ $pv->sucursal->nombre ?? 'Sin sucursal' }}</span>
                                    @if($pv->sucursal)
                                        <span class="bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-mono px-1.5 py-0.2 rounded font-bold">
                                            {{ $pv->sucursal->codigo_sucursal }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400">{{ $pv->sucursal?->direccion ?? '' }}</div>
                            </td>

                            <!-- Modalidad Fiscal -->
                            <td class="py-2 px-3">
                                @if($pv->tipo_emision === 'fiscal_hasar')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                        Fiscal Hasar 2G
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 border border-blue-300 dark:border-blue-700">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                                        </svg>
                                        Digital ARCA
                                    </span>
                                @endif
                            </td>

                            <!-- Configuración / IP -->
                            <td class="py-2 px-3 text-slate-700 dark:text-slate-300">
                                @if($pv->tipo_emision === 'fiscal_hasar')
                                    <div class="flex items-center gap-1.5 font-mono tabular-nums text-xs font-semibold text-slate-800 dark:text-slate-100">
                                        <span class="text-slate-500 font-normal">IP:</span>
                                        <span>{{ $pv->configuracion['hasar_ip'] ?? '—' }}:{{ $pv->configuracion['hasar_puerto'] ?? 2000 }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono tabular-nums">
                                        Puente Local: :{{ $pv->configuracion['puerto_local'] ?? 8585 }} | {{ $pv->configuracion['modelo'] ?? 'SMH/PT-250F' }}
                                    </div>
                                @else
                                    <div class="text-xs font-semibold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                        <span class="text-slate-500 font-normal">Ambiente:</span>
                                        <span class="uppercase tracking-wider text-[10px] px-1.5 py-0.2 rounded font-mono font-bold {{ ($pv->configuracion['ambiente'] ?? 'produccion') === 'produccion' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-700' : 'bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700' }}">
                                            {{ $pv->configuracion['ambiente'] ?? 'produccion' }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        Concepto: {{ ($pv->configuracion['concepto'] ?? 1) == 1 ? '1 - Productos' : (($pv->configuracion['concepto'] ?? 1) == 2 ? '2 - Servicios' : '3 - Prod. y Serv.') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Estado -->
                            <td class="py-2 px-3 text-center">
                                @if($pv->activo)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-2 px-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    @if($pv->tipo_emision === 'fiscal_hasar')
                                        <a href="{{ route('sistema.puntos-venta.descargar-config', $pv) }}"
                                           title="Descargar config.json para el puente Windows local"
                                           class="bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900 border border-emerald-300 dark:border-emerald-700 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider transition-colors inline-flex items-center gap-1 shadow-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span>config.json</span>
                                        </a>
                                    @endif

                                    <a href="{{ route('sistema.puntos-venta.edit', $pv) }}"
                                       class="bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-blue-600 hover:text-white border border-slate-300 dark:border-slate-600 px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider transition-colors inline-flex items-center gap-1 shadow-sm"
                                       title="Editar punto de venta">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Editar</span>
                                    </a>

                                    <form action="{{ route('sistema.puntos-venta.destroy', $pv) }}" method="POST" class="inline"
                                          onsubmit="return confirm('¿Está seguro de eliminar el Punto de Venta {{ str_pad($pv->numero, 5, '0', STR_PAD_LEFT) }} ({{ $pv->nombre }})?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-slate-100 dark:bg-slate-700 text-rose-600 hover:bg-rose-600 hover:text-white border border-slate-300 dark:border-slate-600 p-1 rounded transition-colors shadow-sm"
                                                title="Eliminar punto de venta">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 dark:text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-400">No se encontraron puntos de venta</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Haga clic en "+ Nuevo Punto de Venta" para configurar uno nuevo.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($puntosVenta->hasPages())
            <div class="p-3 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-200 dark:border-slate-700">
                {{ $puntosVenta->links() }}
            </div>
        @endif
    </div>
</x-app-layout>

