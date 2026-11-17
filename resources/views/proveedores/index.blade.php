<x-app-layout>
    {{-- Encabezado --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-4 gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                <span>Padrón de Proveedores</span>
                <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                    Compras y Suministros
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Administración de datos fiscales (CUIT), contactos y cuentas de proveedores.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('productos.index') }}" class="px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs transition-all">
                &larr; Catálogo
            </a>
            <a href="{{ route('proveedores.index', array_merge(request()->query(), ['export' => 'excel'])) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 shadow-sm"
               title="Exportar proveedores filtrados a Excel / CSV">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>EXPORTAR EXCEL</span>
            </a>
            <a href="{{ route('remitos.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-black uppercase transition-all flex items-center space-x-1.5 shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span>Remitos de Mercadería</span>
            </a>
            <a href="{{ route('proveedores.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1 shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                <span>NUEVO PROVEEDOR</span>
            </a>
        </div>
    </div>

    {{-- Filtro de Búsqueda --}}
    <div class="bg-slate-50 dark:bg-slate-800/70 p-3 rounded-xl border border-slate-200 dark:border-slate-700/80 mb-4 shadow-2xs">
        <form method="GET" action="{{ route('proveedores.index') }}" class="flex gap-2">
            <div class="relative flex-grow">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Buscar por Razón Social, CUIT o Nombre de Contacto..." 
                       class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-2xs placeholder-slate-400 font-medium">
            </div>
            <select name="id_localidad" onchange="this.form.submit()" class="text-xs py-1.5 px-2.5 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200">
                <option value="">Todas las Localidades</option>
                @foreach($localidades as $loc)
                    <option value="{{ $loc->id }}" {{ request('id_localidad') == $loc->id ? 'selected' : '' }}>{{ $loc->nombre }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-4 rounded-lg shadow-2xs transition flex items-center">
                Buscar
            </button>
            @if(request()->anyFilled(['search', 'id_localidad']))
                <a href="{{ route('proveedores.index') }}" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-200 font-bold text-xs py-1.5 px-3 rounded-lg transition flex items-center">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    @php
        $sort = $sort ?? request()->get('sort', 'nombre');
        $direction = $direction ?? request()->get('direction', 'asc');
        $nextDirection = function($col) use ($sort, $direction) {
            return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
        };
        $sortUrl = function($col) use ($nextDirection) {
            return route('proveedores.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
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

    {{-- Tabla de Proveedores con Iluminación y Menú Contextual --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-300 dark:border-slate-700 shadow-2xs mb-4">
        <table class="w-full text-left border-collapse font-sans text-xs">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-900/80 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300 dark:border-slate-700 select-none">
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-20 text-center">
                        <a href="{{ $sortUrl('legajo') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Legajo">
                            <span>Legajo</span>
                            {!! $sortIndicator('legajo') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-44">
                        <a href="{{ $sortUrl('cuit') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por CUIT / Condición IVA">
                            <span>CUIT / Cond. IVA</span>
                            {!! $sortIndicator('cuit') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60">
                        <a href="{{ $sortUrl('nombre') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Razón Social">
                            <span>Proveedor / Razón Social</span>
                            {!! $sortIndicator('nombre') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-48">
                        <a href="{{ $sortUrl('localidad') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Ubicación">
                            <span>Ubicación</span>
                            {!! $sortIndicator('localidad') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-40">
                        <a href="{{ $sortUrl('contacto') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Contacto">
                            <span>Contacto</span>
                            {!! $sortIndicator('contacto') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 w-32 text-right">
                        <a href="{{ $sortUrl('saldo') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Saldo">
                            <span>Saldo Cuenta</span>
                            {!! $sortIndicator('saldo') !!}
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                @forelse($proveedores as $prov)
                    <tr class="bg-white dark:bg-slate-800/90 hover:bg-blue-50/90 dark:hover:bg-slate-700/80 border-l-4 border-l-transparent hover:border-l-blue-500 transition-all duration-150 cursor-pointer hover:shadow-xs group"
                        x-data="contextMenuRow()"
                        @click="openMenu($event)"
                        @contextmenu.prevent="openMenu($event)">
                        
                        {{-- Legajo (3 dígitos para código de barras) --}}
                        <td class="py-2.5 px-2 text-center border-r border-slate-200/60 dark:border-slate-700/60">
                            @if($prov->legajo)
                                <span class="inline-block px-2 py-0.5 rounded font-mono font-black text-xs bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-2xs" title="Legajo de 3 dígitos para código de barras">
                                    {{ $prov->legajo_formateado }}
                                </span>
                            @else
                                <span class="text-[10px] text-slate-400 italic">S/L</span>
                            @endif
                        </td>

                        {{-- CUIT --}}
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-900 dark:text-slate-100 border-r border-slate-200/60 dark:border-slate-700/60">
                            <a href="{{ route('proveedores.show', $prov->id) }}" class="text-blue-700 dark:text-blue-400 hover:underline">
                                {{ $prov->cuit ? substr($prov->cuit, 0, 2) . '-' . substr($prov->cuit, 2, 8) . '-' . substr($prov->cuit, -1) : 'S/CUIT' }}
                            </a>
                            <span class="text-[9px] text-slate-400 block font-sans font-normal mt-0.5">
                                {{ $prov->condicionIva->nombre ?? 'S/IVA' }}
                            </span>

                            {{-- Menú Contextual Reutilizable --}}
                            <x-context-menu>
                                <x-context-menu.header 
                                    :title="$prov->nombre" 
                                    :subtitle="'CUIT: ' . ($prov->cuit ?? 'Sin CUIT')"
                                    badge="PROVEEDOR"
                                    badgeColor="blue" />

                                <x-context-menu.group label="Operaciones">
                                    <x-context-menu.item href="{{ route('proveedores.show', $prov->id) }}" icon="document" color="blue">
                                        Ver Ficha Completa
                                    </x-context-menu.item>

                                    <x-context-menu.item href="{{ route('productos.index', ['proveedor_id' => $prov->id]) }}" icon="history">
                                        Ver Artículos Suministrados
                                    </x-context-menu.item>
                                </x-context-menu.group>

                                <x-context-menu.group label="Edición y Datos">
                                    <x-context-menu.item href="{{ route('proveedores.edit', $prov->id) }}" icon="pencil">
                                        Editar Proveedor
                                    </x-context-menu.item>
                                </x-context-menu.group>

                                <x-context-menu.group label="Gestión">
                                    @if(!$prov->tieneOperaciones())
                                        <x-context-menu.item 
                                            action="{{ route('proveedores.destroy', $prov->id) }}" 
                                            method="DELETE" 
                                            icon="trash" 
                                            color="rose"
                                            confirm="¿Deseas eliminar definitivamente este proveedor sin operaciones?">
                                            Eliminar Proveedor
                                        </x-context-menu.item>
                                    @else
                                        <x-context-menu.item disabled icon="trash" color="slate">
                                            No eliminable (con productos)
                                        </x-context-menu.item>
                                    @endif
                                </x-context-menu.group>
                            </x-context-menu>
                        </td>

                        {{-- Nombre / Contacto --}}
                        <td class="py-2.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60">
                            <a href="{{ route('proveedores.show', $prov->id) }}" class="font-bold text-slate-900 dark:text-slate-100 uppercase hover:text-blue-600 block truncate">
                                {{ $prov->nombre }}
                            </a>
                            @if($prov->contacto)
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                    Contacto: {{ $prov->contacto }}
                                </span>
                            @endif
                        </td>

                        {{-- Ubicación --}}
                        <td class="py-2.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-slate-600 dark:text-slate-300 text-[10px]">
                            <p class="font-bold uppercase text-slate-800 dark:text-slate-200 truncate">{{ $prov->localidad->nombre ?? 'S/D' }}</p>
                            <p class="text-[9px] text-slate-400 truncate">{{ $prov->direccion ?? '' }}</p>
                        </td>

                        {{-- Teléfono / Email --}}
                        <td class="py-2.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-[11px] font-mono text-slate-700 dark:text-slate-300">
                            <p>{{ $prov->tel1 ?? '-' }}</p>
                            @if($prov->email)
                                <p class="text-[9px] text-blue-600 dark:text-blue-400 truncate">{{ $prov->email }}</p>
                            @endif
                        </td>

                        {{-- Saldo --}}
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white tabular-nums">
                            $ {{ number_format($prov->saldo, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-400 italic">
                            No se encontraron proveedores registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-1 text-xs">
        {{ $proveedores->appends(request()->query())->links() }}
    </div>
</x-app-layout>
