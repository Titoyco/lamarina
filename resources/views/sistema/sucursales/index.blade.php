<x-app-layout>
    <!-- Header Compacto -->
    <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('sistema.index') }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                    <span>Gestión de Sucursales & Datos Fiscales</span>
                    <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">Puntos de Venta y Facturación</span>
                </h1>
            </div>
        </div>

        <a href="{{ route('sistema.sucursales.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-3 rounded-lg uppercase tracking-wider transition-all shadow-sm flex items-center space-x-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nueva Sucursal</span>
        </a>
    </div>

    <!-- Data-Grid -->
    <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="bg-slate-100 dark:bg-slate-900/80 border-b border-slate-200 dark:border-slate-700 px-3.5 py-2 flex justify-between items-center">
            <h2 class="font-bold text-xs text-slate-700 dark:text-slate-300 uppercase tracking-wider">Sucursales Oficiales y Emisores Fiscales</h2>
            <span class="bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[10px] px-2 py-0.5 rounded-full font-mono tabular-nums">
                {{ method_exists($sucursales, 'total') ? $sucursales->total() : $sucursales->count() }} sucursales
            </span>
        </div>

        @php
            $sort = $sort ?? request()->get('sort', 'nombre');
            $direction = $direction ?? request()->get('direction', 'asc');
            $nextDirection = function($col) use ($sort, $direction) {
                return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
            };
            $sortUrl = function($col) use ($nextDirection) {
                return route('sucursales.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
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
                        <th class="py-2 px-3 w-14 text-center">
                            <a href="{{ $sortUrl('id') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por ID">
                                <span>ID</span>
                                {!! $sortIndicator('id') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3 w-20 text-center">
                            <a href="{{ $sortUrl('codigo_sucursal') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Código">
                                <span>Cód.</span>
                                {!! $sortIndicator('codigo_sucursal') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3">
                            <a href="{{ $sortUrl('nombre') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Nombre">
                                <span>Sucursal / Nombre</span>
                                {!! $sortIndicator('nombre') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3">
                            <a href="{{ $sortUrl('direccion') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Dirección">
                                <span>Dirección Física (Factura)</span>
                                {!! $sortIndicator('direccion') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3">
                            <a href="{{ $sortUrl('cuit') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por CUIT">
                                <span>Responsable Fiscal / CUIT</span>
                                {!! $sortIndicator('cuit') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3">
                            <a href="{{ $sortUrl('telefono') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Contacto">
                                <span>Contacto / Web</span>
                                {!! $sortIndicator('telefono') !!}
                            </a>
                        </th>
                        <th class="py-2 px-2 text-center w-24">
                            <a href="{{ $sortUrl('clientes_count') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Cantidad de Clientes">
                                <span>Clientes</span>
                                {!! $sortIndicator('clientes_count') !!}
                            </a>
                        </th>
                        <th class="py-2 px-3 text-center w-28">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($sucursales as $suc)
                        <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-700/50 transition-colors">
                            <td class="py-2.5 px-3 text-center font-mono tabular-nums text-slate-400">
                                #{{ $suc->id }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <span class="inline-block bg-slate-800 dark:bg-slate-700 text-white font-mono font-bold text-[10px] px-1.5 py-0.5 rounded uppercase border border-slate-600">
                                    {{ $suc->codigo_sucursal }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-100">
                                <span class="font-bold text-xs block text-slate-900 dark:text-white">{{ $suc->nombre }}</span>
                                <span class="text-[10px] text-slate-400 font-normal">
                                    {{ $suc->provincia?->nombre ?? ($suc->isTdf() ? 'Tierra del Fuego' : 'Chubut') }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 dark:text-slate-300">
                                <span class="font-medium block text-xs">{{ $suc->direccion ?? '—' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">P.V.: {{ str_pad($suc->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300">
                                <span class="font-bold text-xs block text-indigo-950 dark:text-indigo-300">{{ $suc->razon_social ?? 'Sin Razón Social' }}</span>
                                <div class="flex items-center gap-1.5 text-[10px] font-mono text-slate-500 dark:text-slate-400">
                                    <span>CUIT: {{ $suc->cuit ?? '—' }}</span>
                                    <span>•</span>
                                    <span>{{ $suc->condicion_iva ?? 'Resp. Inscripto' }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 dark:text-slate-400 text-[11px]">
                                <div>{{ $suc->telefono ?: 'Sin teléfono' }}</div>
                                <div class="text-[10px] font-mono text-indigo-600 dark:text-indigo-400">{{ $suc->pagina_web ?? 'www.tiendaslamarina.com.ar' }}</div>
                            </td>
                            <td class="py-2.5 px-2 text-center font-mono tabular-nums text-slate-600 dark:text-slate-300">
                                {{ $suc->clientes_count }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <a href="{{ route('sistema.sucursales.edit', $suc->id) }}"
                                       title="Editar sucursal y datos fiscales"
                                       class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] py-1 px-2.5 rounded uppercase transition-all shadow-xs">
                                        Editar
                                    </a>
                                    <form action="{{ route('sistema.sucursales.destroy', $suc->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('ATENCIÓN: Se eliminará la sucursal «{{ $suc->nombre }}». Esta acción no se puede deshacer. ¿Desea continuar?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Eliminar sucursal"
                                                class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] py-1 px-2.5 rounded uppercase transition-all shadow-xs">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 px-3 text-center bg-slate-50 dark:bg-slate-900/40">
                                <span class="text-xs text-slate-400 dark:text-slate-500 font-medium italic">No hay sucursales registradas.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($sucursales, 'hasPages') && $sucursales->hasPages())
            <div class="px-3.5 py-2 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
                {{ $sucursales->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
