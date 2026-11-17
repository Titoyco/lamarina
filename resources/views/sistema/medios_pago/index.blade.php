<x-app-layout>
    <div class="max-w-7xl mx-auto py-5 px-3 sm:px-6 space-y-6">

        <!-- Header con Botón de Creación -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sistema.index') }}" 
                   class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-2 px-3 rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <span>⬅️ Panel de Sistema</span>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <span>💳 Medios de Pago</span>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            {{ $medios->total() }} registrados
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Administración de formas de pago consolidadas para facturación en mostrador, cobranza de cuotas y recibos.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('sistema.medios-pago.create') }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2.5 px-4 rounded-xl transition uppercase flex items-center gap-1.5 shadow-sm">
                    <span class="text-sm font-black">+</span>
                    <span>Nuevo Medio de Pago</span>
                </a>
            </div>
        </div>

        <!-- Alertas de Sesión -->
        @if(session('success'))
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs rounded-xl flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="p-3 bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs rounded-xl flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-base">⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Filtros y Búsqueda -->
        <div class="bg-white dark:bg-slate-800 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
            <form method="GET" action="{{ route('sistema.medios-pago.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
                <!-- Búsqueda texto -->
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Buscar
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o código identificador..."
                           class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>

                <!-- Tipo Base -->
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Categoría Base
                    </label>
                    <select name="tipo_base" class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="all" {{ request('tipo_base') === 'all' ? 'selected' : '' }}>Todas las categorías</option>
                        <option value="efectivo" {{ request('tipo_base') === 'efectivo' ? 'selected' : '' }}>💵 Efectivo Físico</option>
                        <option value="tarjeta" {{ request('tipo_base') === 'tarjeta' ? 'selected' : '' }}>💳 Tarjeta (Débito/Crédito)</option>
                        <option value="credito" {{ request('tipo_base') === 'credito' ? 'selected' : '' }}>📑 Crédito Personal / Cta Cte</option>
                        <option value="banco" {{ request('tipo_base') === 'banco' ? 'selected' : '' }}>📱 Transferencia / QR</option>
                        <option value="otro" {{ request('tipo_base') === 'otro' ? 'selected' : '' }}>📦 Otro Medio</option>
                    </select>
                </div>

                <!-- Estado -->
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Estado
                    </label>
                    <select name="estado" class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="all" {{ request('estado') === 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Habilitados (Activos)</option>
                        <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Deshabilitados (Inactivos)</option>
                    </select>
                </div>

                <!-- Botones -->
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="w-full bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white font-bold text-xs py-2 px-4 rounded-xl transition uppercase flex items-center justify-center gap-1">
                        <span>🔍 Filtrar</span>
                    </button>
                    @if(request()->hasAny(['search', 'tipo_base', 'estado']))
                        <a href="{{ route('sistema.medios-pago.index') }}" 
                           class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs py-2 px-3 rounded-xl transition uppercase">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @php
            $sort = $sort ?? request()->get('sort', 'orden');
            $direction = $direction ?? request()->get('direction', 'asc');
            $nextDirection = function($col) use ($sort, $direction) {
                return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
            };
            $sortUrl = function($col) use ($nextDirection) {
                return route('sistema.medios-pago.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
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

        <!-- Tabla Data-Grid de Medios de Pago -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-900/80 text-slate-600 dark:text-slate-400 uppercase text-[10px] font-black border-b border-slate-200 dark:border-slate-700 select-none">
                        <tr>
                            <th class="py-3 px-4">
                                <a href="{{ $sortUrl('orden') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Orden">
                                    <span>Orden</span>
                                    {!! $sortIndicator('orden') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4">
                                <a href="{{ $sortUrl('nombre') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Nombre">
                                    <span>Medio de Pago</span>
                                    {!! $sortIndicator('nombre') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4">
                                <a href="{{ $sortUrl('codigo') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Código">
                                    <span>Código / Slug</span>
                                    {!! $sortIndicator('codigo') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4">
                                <a href="{{ $sortUrl('tipo_base') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Categoría Base">
                                    <span>Categoría Base</span>
                                    {!! $sortIndicator('tipo_base') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4 text-center">
                                <a href="{{ $sortUrl('recargo_porcentaje') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Recargo/Descuento">
                                    <span>Recargo / Descuento</span>
                                    {!! $sortIndicator('recargo_porcentaje') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4 text-center">Configuración</th>
                            <th class="py-3 px-4 text-center">
                                <a href="{{ $sortUrl('movimientos') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Movimientos Asociados">
                                    <span>Movimientos Asociados</span>
                                    {!! $sortIndicator('movimientos') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4 text-center">
                                <a href="{{ $sortUrl('activo') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Estado">
                                    <span>Estado</span>
                                    {!! $sortIndicator('activo') !!}
                                </a>
                            </th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($medios as $medio)
                            @php
                                $movsTotal = ($medio->ventas_count ?? 0) + ($medio->cuotas_count ?? 0) + ($medio->recibos_count ?? 0);
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-400">
                                    {{ $medio->orden }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-xl shrink-0 p-1 rounded-lg bg-slate-100 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600">
                                            {{ $medio->icono ?: '💳' }}
                                        </span>
                                        <div>
                                            <a href="{{ route('sistema.medios-pago.edit', $medio->id) }}" 
                                               class="font-black text-slate-900 dark:text-white text-xs hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                                                {{ $medio->nombre }}
                                            </a>
                                            @if($medio->requiere_referencia)
                                                <span class="block text-[9px] text-slate-400 font-normal">Pide cupón/comprobante</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-600 dark:text-slate-300">
                                    <code>{{ $medio->codigo }}</code>
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $badgeColor = match($medio->tipo_base) {
                                            'efectivo' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-300',
                                            'tarjeta'  => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 border-sky-300',
                                            'credito'  => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-300',
                                            'banco'    => 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300 border-teal-300',
                                            default    => 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-200 border-slate-300',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $badgeColor }}">
                                        {{ $medio->tipo_base_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-[11px]">
                                    @if($medio->recargo_porcentaje > 0)
                                        <span class="text-rose-600 font-bold" title="Recargo al cobrar">+{{ $medio->recargo_porcentaje }}%</span>
                                    @elseif($medio->descuento_porcentaje > 0)
                                        <span class="text-emerald-600 font-bold" title="Descuento al cobrar">-{{ $medio->descuento_porcentaje }}%</span>
                                    @else
                                        <span class="text-slate-400">0%</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5 text-[10px]">
                                        @if($medio->permite_vuelto)
                                            <span class="px-1.5 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800" title="Calcula vuelto">
                                                Vuelto
                                            </span>
                                        @endif
                                        @if($medio->requiere_referencia)
                                            <span class="px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800" title="Exige número de comprobante o cupón">
                                                Ref/Cupón
                                            </span>
                                        @endif
                                        @if(!$medio->permite_vuelto && !$medio->requiere_referencia)
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($movsTotal > 0)
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200"
                                              title="{{ $medio->ventas_count }} ventas, {{ $medio->cuotas_count }} pagos cuotas, {{ $medio->recibos_count }} recibos">
                                            {{ number_format($movsTotal, 0, ',', '.') }} movs
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">Sin movimientos</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <form method="POST" action="{{ route('sistema.medios-pago.toggle-activo', $medio->id) }}">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase transition cursor-pointer {{ $medio->activo ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}"
                                                title="Haga clic para {{ $medio->activo ? 'desactivar' : 'activar' }}">
                                            {{ $medio->activo ? 'Activo' : 'Inactivo' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('sistema.medios-pago.edit', $medio->id) }}" 
                                           class="bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 font-bold text-[10px] py-1 px-2.5 rounded-lg transition"
                                           title="Editar configuración">
                                            ✏️ Editar
                                        </a>

                                        @if($movsTotal === 0)
                                            <form method="POST" action="{{ route('sistema.medios-pago.destroy', $medio->id) }}" 
                                                  onsubmit="return confirm('¿Confirma eliminar definitivamente el medio de pago \'{{ $medio->nombre }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 font-bold text-[10px] py-1 px-2 rounded-lg transition cursor-pointer"
                                                        title="Eliminar (sin movimientos)">
                                                    🗑️
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" disabled
                                                    class="opacity-30 cursor-not-allowed bg-slate-100 text-slate-400 border border-slate-200 font-bold text-[10px] py-1 px-2 rounded-lg"
                                                    title="Bloqueado: posee {{ $movsTotal }} movimientos asociados. Para dejar de usarlo, desactívelo.">
                                                🔒
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-500">
                                    No se encontraron medios de pago que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($medios->hasPages())
                <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    {{ $medios->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
