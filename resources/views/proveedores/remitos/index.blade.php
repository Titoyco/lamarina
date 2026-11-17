<x-app-layout>
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Encabezado y Acciones -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-700">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black uppercase px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                        Compras & Ingreso a Stock
                    </span>
                    <span class="text-xs text-slate-400">Terminal: <strong>{{ config('app.sucursal_activa')->nombre ?? 'Todas' }}</strong></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight mt-1">
                    Remitos de Proveedores
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                    Historial de recepción de mercadería, control de costo de compra, actualización de precios y remitos parciales.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('proveedores.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Padrón de Proveedores
                </a>

                <a href="{{ route('remitos.index', array_merge(request()->query(), ['export' => 'excel'])) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition"
                   title="Exportar todos los remitos filtrados a Excel / CSV">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>EXPORTAR EXCEL</span>
                </a>

                <a href="{{ route('remitos.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 text-xs font-black uppercase text-white bg-blue-700 hover:bg-blue-800 rounded-xl shadow-md transition transform active:scale-95">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Cargar Nuevo Remito
                </a>
            </div>
        </div>

        <!-- Alertas -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-bold flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs font-bold flex items-center gap-2">
                <span class="font-black">⛔</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Tarjetas Métricas Superiores -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Total Remitos</p>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($remitos->total(), 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Ingresos de mercadería registrados</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-[10px] font-black uppercase text-amber-500 tracking-wider">Recepciones Parciales</p>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                    {{ \App\Models\RemitoProveedor::where('estado', 'parcial')->count() }}
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Pendientes de completar entrega</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Costo Ingresado (Total)</p>
                <p class="text-xl sm:text-2xl font-black text-blue-900 dark:text-blue-300 mt-1 font-mono">
                    $ {{ number_format(\App\Models\RemitoProveedor::sum('total_costo'), 2, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Valorización precio compra</p>
            </div>

            <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <p class="text-[10px] font-black uppercase text-emerald-600 tracking-wider">Valor Venta Estimado</p>
                <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                    $ {{ number_format(\App\Models\RemitoProveedor::sum('total_venta_estimado'), 2, ',', '.') }}
                </p>
                <p class="text-[11px] text-slate-500 mt-0.5">Proyección precio público</p>
            </div>
        </div>

        <!-- Filtros de Búsqueda -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-sm">
            <form method="GET" action="{{ route('remitos.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Buscador texto -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">N° Remito o Proveedor</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Buscar por N° remito, nombre o CUIT..."
                           class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white py-2 px-3">
                </div>

                <!-- Proveedor -->
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Proveedor</label>
                    <select name="proveedor_id" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white py-2 px-2.5">
                        <option value="">Todos los Proveedores</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}" {{ request('proveedor_id') == $prov->id ? 'selected' : '' }}>
                                {{ $prov->legajo ? "[{$prov->legajo}] " : '' }}{{ Str::limit($prov->nombre, 24) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sucursal -->
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Sede de Ingreso</label>
                    <select name="sucursal_id" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white py-2 px-2.5">
                        <option value="">Todas las Sedes</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ request('sucursal_id') == $suc->id ? 'selected' : '' }}>
                                {{ $suc->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-[10px] font-black uppercase text-slate-400 mb-1">Estado</label>
                    <select name="estado" class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white py-2 px-2.5">
                        <option value="">Todos los Estados</option>
                        <option value="completado" {{ request('estado') === 'completado' ? 'selected' : '' }}>Completados</option>
                        <option value="parcial" {{ request('estado') === 'parcial' ? 'selected' : '' }}>Entregas Parciales</option>
                    </select>
                </div>

                <!-- Botones -->
                <div class="lg:col-span-5 flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    <span class="text-xs text-slate-400 font-medium">Resultados encontrados: {{ $remitos->total() }}</span>
                    <div class="flex items-center gap-2">
                        @if(request()->anyFilled(['search', 'proveedor_id', 'sucursal_id', 'estado', 'fecha_desde', 'fecha_hasta']))
                            <a href="{{ route('remitos.index') }}" class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white">
                                Limpiar Filtros
                            </a>
                        @endif
                        <button type="submit" class="px-4 py-1.5 bg-blue-700 hover:bg-blue-800 text-white rounded-xl text-xs font-bold transition shadow-sm">
                            Filtrar Remitos
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @php
            $sort = $sort ?? request()->get('sort', 'fecha_recepcion');
            $direction = $direction ?? request()->get('direction', 'desc');
            $nextDirection = function($col) use ($sort, $direction) {
                return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
            };
            $sortUrl = function($col) use ($nextDirection) {
                return route('remitos.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
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

        <!-- Tabla de Remitos -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/80 shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-black uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700 select-none">
                            <th class="py-3 px-4">
                                <a href="{{ $sortUrl('numero_remito') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por N° Remito">
                                    <span>N° Remito</span>
                                    {!! $sortIndicator('numero_remito') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4">
                                <a href="{{ $sortUrl('fecha_recepcion') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Fecha Recepción">
                                    <span>Fecha Recep.</span>
                                    {!! $sortIndicator('fecha_recepcion') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4">
                                <a href="{{ $sortUrl('proveedor') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Proveedor">
                                    <span>Proveedor</span>
                                    {!! $sortIndicator('proveedor') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4">
                                <a href="{{ $sortUrl('sucursal') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Sede">
                                    <span>Sede Ingreso</span>
                                    {!! $sortIndicator('sucursal') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4 text-center">Ítems</th>
                            <th class="py-3 px-4 text-right">
                                <a href="{{ $sortUrl('total_costo') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Total Costo">
                                    <span>Total Costo</span>
                                    {!! $sortIndicator('total_costo') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4 text-right">
                                <a href="{{ $sortUrl('total_venta_estimado') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Total Venta Estimado">
                                    <span>Total Venta Est.</span>
                                    {!! $sortIndicator('total_venta_estimado') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4 text-center">
                                <a href="{{ $sortUrl('estado') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Estado">
                                    <span>Estado</span>
                                    {!! $sortIndicator('estado') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                        @forelse($remitos as $rem)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/40 transition">
                                <td class="py-3 px-4 font-mono font-black text-slate-900 dark:text-white text-xs">
                                    <a href="{{ route('remitos.show', $rem->id) }}" class="text-blue-700 dark:text-blue-400 hover:underline">
                                        {{ $rem->numero_remito }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                                    {{ \Carbon\Carbon::parse($rem->fecha_recepcion)->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 text-slate-800 dark:text-slate-200">
                                    <strong>{{ $rem->proveedor?->nombre ?? 'N/A' }}</strong>
                                    @if($rem->proveedor?->legajo)
                                        <span class="text-[10px] text-slate-400 block font-mono">Legajo: {{ $rem->proveedor->legajo }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $rem->sucursal?->nombre ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-700 dark:text-slate-300">
                                    <span class="bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full text-[11px]">
                                        {{ $rem->items->count() }} renglones
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-blue-900 dark:text-blue-300">
                                    $ {{ number_format($rem->total_costo, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    $ {{ number_format($rem->total_venta_estimado, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($rem->estado === 'completado')
                                        <span class="bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 text-[10px] font-black uppercase px-2 py-0.5 rounded-full border border-emerald-300">
                                            Completado
                                        </span>
                                    @else
                                        <span class="bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 text-[10px] font-black uppercase px-2 py-0.5 rounded-full border border-amber-300 animate-pulse">
                                            Parcial
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($rem->estado === 'parcial')
                                            <a href="{{ route('remitos.create', ['remito_id' => $rem->id]) }}" 
                                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-black text-amber-900 bg-amber-100 hover:bg-amber-200 dark:bg-amber-950 dark:text-amber-200 border border-amber-300 dark:border-amber-700 rounded-lg shadow-2xs transition-all">
                                                <span>✏️ Continuar Carga</span>
                                            </a>
                                        @endif
                                        <a href="{{ route('remitos.show', $rem->id) }}" 
                                           class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300">
                                            <span>Ver</span>
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400 text-xs italic bg-slate-50/50">
                                    No se encontraron remitos con los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($remitos->hasPages())
                <div class="px-5 py-3.5 bg-slate-50 dark:bg-slate-800/90 border-t border-slate-200 dark:border-slate-700">
                    {{ $remitos->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
