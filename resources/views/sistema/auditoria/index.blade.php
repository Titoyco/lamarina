<x-app-layout>
    <!-- Header Compacto -->
    <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-700 mb-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Auditoría del Sistema</span>
                <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">Trazabilidad y Control de Cambios</span>
            </h1>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('sistema.auditoria.index', array_merge(request()->query(), ['export' => 'excel'])) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white p-1.5 px-3 rounded-md transition-all shadow-sm flex items-center space-x-1.5 text-xs font-bold"
               title="Exportar logs de auditoría filtrados a Excel / CSV">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>EXPORTAR EXCEL</span>
            </a>

            <a href="{{ route('sistema.index') }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm flex items-center space-x-1 text-xs font-semibold">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Volver al Sistema</span>
            </a>
        </div>
    </div>

    <!-- Panel de Filtros (Ribbon Denso) -->
    <div class="bg-slate-50 dark:bg-slate-800/80 p-2.5 rounded-md border border-slate-200 dark:border-slate-700 mb-4 shadow-sm">
        <form action="{{ route('sistema.auditoria.index') }}" method="GET" class="space-y-2">
            <!-- Buscador Principal -->
            <div class="flex gap-2">
                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por ID de registro o ID de auditoría..." 
                           class="w-full text-xs py-1.5 px-3 rounded-md border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-4 rounded-md shadow-sm transition">
                    Buscar
                </button>
                @if(request()->anyFilled(['search', 'auditable_type', 'event', 'fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('sistema.auditoria.index') }}" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs py-1.5 px-3 rounded-md transition flex items-center">
                        Limpiar
                    </a>
                @endif
            </div>

            <!-- Filtros Secundarios -->
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 pt-1.5 border-t border-slate-200/70 dark:border-slate-700/70">
                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-0.5">Entidad</label>
                    <select name="auditable_type" onchange="this.form.submit()" class="w-full text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-600 dark:text-slate-200">
                        <option value="">Todas las Entidades</option>
                        @foreach($entidades as $fqcn => $label)
                            <option value="{{ $fqcn }}" {{ request('auditable_type') === $fqcn ? 'selected' : '' }}>
                                {{ $label }} ({{ class_basename($fqcn) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-0.5">Evento</label>
                    <select name="event" onchange="this.form.submit()" class="w-full text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-600 dark:text-slate-200">
                        <option value="">Todos los Eventos</option>
                        <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Creado (created)</option>
                        <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Modificado (updated)</option>
                        <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Eliminado (deleted)</option>
                        <option value="restored" {{ request('event') === 'restored' ? 'selected' : '' }}>Restaurado (restored)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-0.5">Fecha Desde</label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" onchange="this.form.submit()"
                           class="w-full text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-600 dark:text-slate-200">
                </div>

                <div>
                    <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-0.5">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" onchange="this.form.submit()"
                           class="w-full text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-600 dark:text-slate-200">
                </div>
            </div>
        </form>
    </div>

    @php
        $sort = $sort ?? request()->get('sort', 'id');
        $direction = $direction ?? request()->get('direction', 'desc');
        $nextDirection = function($col) use ($sort, $direction) {
            return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
        };
        $sortUrl = function($col) use ($nextDirection) {
            return route('sistema.auditoria.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
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

    <!-- Data Grid de Auditoría -->
    <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-300 uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700 select-none">
                        <th class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-semibold w-16">
                            <a href="{{ $sortUrl('id') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por ID">
                                <span>ID</span>
                                {!! $sortIndicator('id') !!}
                            </a>
                        </th>
                        <th class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-semibold w-36">
                            <a href="{{ $sortUrl('created_at') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Fecha/Hora">
                                <span>Fecha / Hora</span>
                                {!! $sortIndicator('created_at') !!}
                            </a>
                        </th>
                        <th class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-semibold w-32">
                            <a href="{{ $sortUrl('user') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Usuario">
                                <span>Usuario</span>
                                {!! $sortIndicator('user') !!}
                            </a>
                        </th>
                        <th class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-semibold w-24 text-center">
                            <a href="{{ $sortUrl('event') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Evento">
                                <span>Evento</span>
                                {!! $sortIndicator('event') !!}
                            </a>
                        </th>
                        <th class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-semibold w-40">
                            <a href="{{ $sortUrl('auditable_type') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Entidad">
                                <span>Entidad</span>
                                {!! $sortIndicator('auditable_type') !!}
                            </a>
                        </th>
                        <th class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-semibold">Cambios</th>
                        <th class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-semibold w-24">
                            <a href="{{ $sortUrl('ip_address') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por IP">
                                <span>IP</span>
                                {!! $sortIndicator('ip_address') !!}
                            </a>
                        </th>
                        <th class="py-1.5 px-3 font-semibold text-center w-24">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                    @forelse($audits as $audit)
                        <tr class="bg-white dark:bg-slate-800 hover:bg-blue-50/40 dark:hover:bg-slate-700/50 text-slate-800 dark:text-slate-200 transition-colors">
                            <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-mono text-[11px] text-slate-500 dark:text-slate-400 tabular-nums">
                                #{{ $audit->id }}
                            </td>
                            <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-mono text-[11px] text-slate-600 dark:text-slate-300 tabular-nums whitespace-nowrap">
                                {{ $audit->created_at ? $audit->created_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-medium text-slate-700 dark:text-slate-200 whitespace-nowrap">
                                {{ $audit->user ? $audit->user->name : 'Sistema / CLI' }}
                            </td>
                            <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-center whitespace-nowrap">
                                @if($audit->event === 'created')
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                        CREATED
                                    </span>
                                @elseif($audit->event === 'updated')
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        UPDATED
                                    </span>
                                @elseif($audit->event === 'deleted')
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        DELETED
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                                        {{ strtoupper($audit->event) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-medium whitespace-nowrap">
                                <span class="text-blue-700 dark:text-blue-400 font-bold">{{ class_basename($audit->auditable_type) }}</span>
                                <span class="font-mono text-[11px] text-slate-500 dark:text-slate-400 tabular-nums">#{{ $audit->auditable_id }}</span>
                            </td>
                            <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-[11px]">
                                @php
                                    $oldValues = $audit->old_values ?? [];
                                    $newValues = $audit->new_values ?? [];
                                    $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));
                                @endphp
                                @if(empty($allKeys))
                                    <span class="text-slate-400 dark:text-slate-500 italic">Sin datos</span>
                                @else
                                    <div class="space-y-0.5 max-w-xl">
                                        @foreach($allKeys as $key)
                                            <div class="font-mono text-[10px] leading-tight">
                                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $key }}:</span>
                                                @if(array_key_exists($key, $oldValues))
                                                    <span class="text-rose-600 dark:text-rose-400 line-through bg-rose-50 dark:bg-rose-950/60 px-1 rounded">{{ is_array($oldValues[$key]) ? json_encode($oldValues[$key]) : ($oldValues[$key] ?? 'null') }}</span>
                                                @endif
                                                @if(array_key_exists($key, $oldValues) && array_key_exists($key, $newValues))
                                                    <span class="text-slate-400 dark:text-slate-500 font-sans">&rarr;</span>
                                                @endif
                                                @if(array_key_exists($key, $newValues))
                                                    <span class="text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1 rounded font-semibold">{{ is_array($newValues[$key]) ? json_encode($newValues[$key]) : ($newValues[$key] ?? 'null') }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-mono text-[10px] text-slate-500 dark:text-slate-400 tabular-nums whitespace-nowrap">
                                {{ $audit->ip_address ?: '-' }}
                            </td>
                            <td class="py-1.5 px-3 text-center whitespace-nowrap">
                                @if($audit->event === 'updated' && $audit->auditable)
                                    <form action="{{ route('sistema.auditoria.rollback', $audit) }}" method="POST"
                                          onsubmit="return confirm('¿Revertir cambios del registro #{{ $audit->auditable_id }}?');"
                                          class="inline-block">
                                        @csrf
                                        <button type="submit"
                                                class="bg-amber-500 hover:bg-amber-600 text-white font-bold text-[10px] py-1 px-2 rounded shadow-sm transition">
                                            Deshacer
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600 text-[10px] italic">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400 dark:text-slate-500 text-xs bg-white dark:bg-slate-800">
                                No se encontraron registros de auditoría.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($audits->hasPages())
            <div class="px-3 py-2 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80">
                {{ $audits->links() }}
            </div>
        @endif
    </div>
</x-app-layout>