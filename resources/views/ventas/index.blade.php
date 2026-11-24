<x-app-layout>
    <div class="max-w-7xl mx-auto py-3 space-y-4">
        <!-- Header de Sección -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 py-2 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/60 flex items-center justify-center text-indigo-700 dark:text-indigo-400 font-black">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-800 dark:text-white tracking-tight uppercase flex items-center space-x-2">
                        <span>Historial de Ventas & Facturación</span>
                        <span class="text-[10px] font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/40 px-2 py-0.5 rounded border border-indigo-200 dark:border-indigo-800 uppercase">
                            Terminal: {{ $sucursalActiva->nombre ?? 'N/A' }}
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Registro de facturas emitidas, movimientos de mercadería y créditos automáticos.
                    </p>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('ventas.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-4 rounded-lg shadow-sm transition flex items-center space-x-1.5 uppercase">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Nueva Venta (POS)</span>
                </a>

                <a href="{{ route('ventas.index', array_merge(request()->query(), ['export' => 'excel'])) }}" 
                   class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/70 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-3 rounded-lg shadow-xs transition flex items-center space-x-1.5 uppercase"
                   title="Exportar todas las ventas filtradas a Excel / CSV">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>EXPORTAR EXCEL</span>
                </a>
            </div>
        </div>

        <!-- Cinta Resumen de KPIs (Compacta en 1 sola línea) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            <div class="bg-white dark:bg-slate-800 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
                <div>
                    <span class="block text-[9px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Hoy ({{ $sucursalActiva->codigo_sucursal ?? 'Sede' }})</span>
                    <span class="text-sm font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                        $ {{ number_format($facturadoHoy, 2, ',', '.') }}
                    </span>
                </div>
                <div class="w-7 h-7 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center text-xs">
                    📅
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
                <div>
                    <span class="block text-[9px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Mes en Curso</span>
                    <span class="text-sm font-black text-slate-800 dark:text-white tabular-nums">
                        $ {{ number_format($facturadoMes, 2, ',', '.') }}
                    </span>
                </div>
                <div class="w-7 h-7 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-600 flex items-center justify-center text-xs">
                    📈
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
                <div>
                    <span class="block text-[9px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Operaciones Filtradas</span>
                    <span class="text-sm font-black text-indigo-600 dark:text-indigo-400 tabular-nums">
                        {{ number_format($cantidadFiltrada, 0, ',', '.') }} ventas
                    </span>
                </div>
                <div class="w-7 h-7 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 flex items-center justify-center text-xs">
                    🧾
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 shadow-xs flex items-center justify-between">
                <div>
                    <span class="block text-[9px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Total en Consulta</span>
                    <span class="text-sm font-black text-slate-900 dark:text-white tabular-nums">
                        $ {{ number_format($totalFiltrado, 2, ',', '.') }}
                    </span>
                </div>
                <div class="w-7 h-7 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 flex items-center justify-center text-xs">
                    💰
                </div>
            </div>
        </div>

        <!-- Barra de Filtros -->
        <div class="bg-white dark:bg-slate-800 p-3 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs">
            <form action="{{ route('ventas.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-2">
                {{-- Preservar ordenamiento activo --}}
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">

                {{-- Buscador general --}}
                <div class="lg:col-span-2">
                    <label for="search" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Comprobante / Cliente / DNI</label>
                    <div class="relative">
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                               placeholder="Ej: 0002-00000001, Pérez, 32145678..."
                               class="w-full text-xs py-1.5 pl-8 pr-2 rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                            🔍
                        </div>
                    </div>
                </div>

                {{-- Selector de Sucursal --}}
                <div>
                    <label for="sucursal" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Sucursal / Región</label>
                    <select name="sucursal" id="sucursal" class="w-full text-xs py-1.5 px-2 rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                        <option value="all" {{ request('sucursal') === 'all' ? 'selected' : '' }}>-- Todas las Sucursales --</option>
                        <optgroup label="Por Jurisdicción">
                            <option value="provincia_2" {{ request('sucursal') === 'provincia_2' || (!request()->has('sucursal') && $provinciaActivaId === 2) ? 'selected' : '' }}>Tierra del Fuego (Ushuaia)</option>
                            <option value="provincia_1" {{ request('sucursal') === 'provincia_1' || (!request()->has('sucursal') && $provinciaActivaId === 1) ? 'selected' : '' }}>Chubut (Trelew)</option>
                        </optgroup>
                        <optgroup label="Sucursal Específica">
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ request('sucursal') == (string)$s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }} ({{ $s->codigo_sucursal }})
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                {{-- Medio de Pago --}}
                <div>
                    <label for="medio_pago" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Medio de Pago</label>
                    <select name="medio_pago" id="medio_pago" class="w-full text-xs py-1.5 px-2 rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                        <option value="all">Todos los medios</option>
                        <option value="efectivo" {{ request('medio_pago') === 'efectivo' ? 'selected' : '' }}>💵 Efectivo</option>
                        <option value="tarjeta_debito" {{ request('medio_pago') === 'tarjeta_debito' ? 'selected' : '' }}>💳 Débito</option>
                        <option value="tarjeta_credito" {{ request('medio_pago') === 'tarjeta_credito' ? 'selected' : '' }}>💳 Tarjeta Crédito</option>
                        <option value="credito_personal" {{ request('medio_pago') === 'credito_personal' ? 'selected' : '' }}>📄 Crédito Personal</option>
                    </select>
                </div>

                {{-- Tipo de Comprobante --}}
                <div>
                    <label for="tipo_comprobante" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Tipo Cbte</label>
                    <select name="tipo_comprobante" id="tipo_comprobante" class="w-full text-xs py-1.5 px-2 rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                        <option value="all">Todos</option>
                        <option value="FACTURA_B" {{ request('tipo_comprobante') === 'FACTURA_B' ? 'selected' : '' }}>Factura B</option>
                        <option value="FACTURA_A" {{ request('tipo_comprobante') === 'FACTURA_A' ? 'selected' : '' }}>Factura A</option>
                        <option value="FACTURA_C" {{ request('tipo_comprobante') === 'FACTURA_C' ? 'selected' : '' }}>Factura C</option>
                        <option value="REMITO" {{ request('tipo_comprobante') === 'REMITO' ? 'selected' : '' }}>Remito</option>
                        <option value="PRESUPUESTO" {{ request('tipo_comprobante') === 'PRESUPUESTO' ? 'selected' : '' }}>Presupuesto</option>
                        <option value="NOTA_CREDITO_B" {{ request('tipo_comprobante') === 'NOTA_CREDITO_B' ? 'selected' : '' }}>Nota de Crédito</option>
                    </select>
                </div>

                {{-- Fecha Desde --}}
                <div>
                    <label for="fecha_desde" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Fecha Desde</label>
                    <input type="date" name="fecha_desde" id="fecha_desde" value="{{ request('fecha_desde') }}"
                           class="w-full text-xs py-1.5 px-2 rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                </div>

                {{-- Fecha Hasta + Botones --}}
                <div class="flex items-end gap-1">
                    <div class="flex-grow">
                        <label for="fecha_hasta" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" id="fecha_hasta" value="{{ request('fecha_hasta') }}"
                               class="w-full text-xs py-1.5 px-2 rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                    </div>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 px-3 rounded-lg transition shadow-xs">
                        Filtrar
                    </button>
                    @if(request()->anyFilled(['search', 'sucursal', 'medio_pago', 'tipo_comprobante', 'fecha_desde', 'fecha_hasta', 'estado']))
                        <a href="{{ route('ventas.index') }}" title="Limpiar Filtros"
                           class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold text-xs py-2 px-2.5 rounded-lg transition">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Pastilla de Ordenamiento Activo (conforme a .agents/rules/table_sorting.md) --}}
        @if(($sort && $sort !== 'fecha') || ($direction && $direction !== 'desc'))
            <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800/80 px-3 py-1.5 rounded-lg">
                <span class="font-bold">Ordenando por:</span>
                <span class="font-mono uppercase font-black text-indigo-700 dark:text-indigo-400">
                    {{ match($sort) {
                        'comprobante' => 'Comprobante',
                        'cliente'     => 'Cliente',
                        'sucursal'    => 'Sucursal',
                        'medio_pago'  => 'Medio de Pago',
                        'total'       => 'Total Facturado',
                        'estado'      => 'Estado',
                        default       => 'Fecha y Hora'
                    } }} ({{ $direction === 'asc' ? 'Ascendente ▲' : 'Descendente ▼' }})
                </span>
                <a href="{{ route('ventas.index', array_merge(request()->except(['sort', 'direction', 'page']))) }}"
                   class="ml-auto text-[10px] font-black uppercase text-indigo-600 dark:text-indigo-400 hover:underline">
                    Restablecer orden predeterminado (Fecha Reciente) ✕
                </a>
            </div>
        @endif

        <!-- Tabla Operativa de Ventas -->
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100 dark:bg-slate-900/90 text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-700 text-[10px] uppercase font-black tracking-wider">
                            {{-- 1. Comprobante --}}
                            <th scope="col" class="py-2.5 px-3">
                                <a href="{{ route('ventas.index', array_merge(request()->query(), [
                                    'sort' => 'comprobante',
                                    'direction' => ($sort === 'comprobante' && $direction === 'asc') ? 'desc' : 'asc',
                                    'page' => 1
                                ])) }}" class="group flex items-center space-x-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>Comprobante</span>
                                    <span class="font-mono text-xs {{ $sort === 'comprobante' ? 'text-indigo-600 dark:text-indigo-400 font-black' : 'text-slate-400 dark:text-slate-600 group-hover:text-slate-600' }}">
                                        {{ $sort === 'comprobante' ? ($direction === 'asc' ? '▲' : '▼') : '⇅' }}
                                    </span>
                                </a>
                            </th>

                            {{-- 2. Fecha --}}
                            <th scope="col" class="py-2.5 px-3">
                                <a href="{{ route('ventas.index', array_merge(request()->query(), [
                                    'sort' => 'fecha',
                                    'direction' => ($sort === 'fecha' && $direction === 'asc') ? 'desc' : 'asc',
                                    'page' => 1
                                ])) }}" class="group flex items-center space-x-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>Fecha / Hora</span>
                                    <span class="font-mono text-xs {{ $sort === 'fecha' ? 'text-indigo-600 dark:text-indigo-400 font-black' : 'text-slate-400 dark:text-slate-600 group-hover:text-slate-600' }}">
                                        {{ $sort === 'fecha' ? ($direction === 'asc' ? '▲' : '▼') : '⇅' }}
                                    </span>
                                </a>
                            </th>

                            {{-- 3. Sucursal --}}
                            <th scope="col" class="py-2.5 px-3">
                                <a href="{{ route('ventas.index', array_merge(request()->query(), [
                                    'sort' => 'sucursal',
                                    'direction' => ($sort === 'sucursal' && $direction === 'asc') ? 'desc' : 'asc',
                                    'page' => 1
                                ])) }}" class="group flex items-center space-x-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>Sucursal</span>
                                    <span class="font-mono text-xs {{ $sort === 'sucursal' ? 'text-indigo-600 dark:text-indigo-400 font-black' : 'text-slate-400 dark:text-slate-600 group-hover:text-slate-600' }}">
                                        {{ $sort === 'sucursal' ? ($direction === 'asc' ? '▲' : '▼') : '⇅' }}
                                    </span>
                                </a>
                            </th>

                            {{-- 4. Cliente --}}
                            <th scope="col" class="py-2.5 px-3">
                                <a href="{{ route('ventas.index', array_merge(request()->query(), [
                                    'sort' => 'cliente',
                                    'direction' => ($sort === 'cliente' && $direction === 'asc') ? 'desc' : 'asc',
                                    'page' => 1
                                ])) }}" class="group flex items-center space-x-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>Cliente / DNI</span>
                                    <span class="font-mono text-xs {{ $sort === 'cliente' ? 'text-indigo-600 dark:text-indigo-400 font-black' : 'text-slate-400 dark:text-slate-600 group-hover:text-slate-600' }}">
                                        {{ $sort === 'cliente' ? ($direction === 'asc' ? '▲' : '▼') : '⇅' }}
                                    </span>
                                </a>
                            </th>

                            {{-- 5. Medio de Pago --}}
                            <th scope="col" class="py-2.5 px-3 text-center">
                                <a href="{{ route('ventas.index', array_merge(request()->query(), [
                                    'sort' => 'medio_pago',
                                    'direction' => ($sort === 'medio_pago' && $direction === 'asc') ? 'desc' : 'asc',
                                    'page' => 1
                                ])) }}" class="group inline-flex items-center space-x-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>Medio de Pago</span>
                                    <span class="font-mono text-xs {{ $sort === 'medio_pago' ? 'text-indigo-600 dark:text-indigo-400 font-black' : 'text-slate-400 dark:text-slate-600 group-hover:text-slate-600' }}">
                                        {{ $sort === 'medio_pago' ? ($direction === 'asc' ? '▲' : '▼') : '⇅' }}
                                    </span>
                                </a>
                            </th>

                            {{-- 6. Crédito / Cuotas --}}
                            <th scope="col" class="py-2.5 px-3 text-center">Crédito Asociado</th>

                            {{-- 7. Total --}}
                            <th scope="col" class="py-2.5 px-3 text-right">
                                <a href="{{ route('ventas.index', array_merge(request()->query(), [
                                    'sort' => 'total',
                                    'direction' => ($sort === 'total' && $direction === 'asc') ? 'desc' : 'asc',
                                    'page' => 1
                                ])) }}" class="group inline-flex items-center space-x-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>Total</span>
                                    <span class="font-mono text-xs {{ $sort === 'total' ? 'text-indigo-600 dark:text-indigo-400 font-black' : 'text-slate-400 dark:text-slate-600 group-hover:text-slate-600' }}">
                                        {{ $sort === 'total' ? ($direction === 'asc' ? '▲' : '▼') : '⇅' }}
                                    </span>
                                </a>
                            </th>

                            {{-- 8. Estado --}}
                            <th scope="col" class="py-2.5 px-3 text-center">
                                <a href="{{ route('ventas.index', array_merge(request()->query(), [
                                    'sort' => 'estado',
                                    'direction' => ($sort === 'estado' && $direction === 'asc') ? 'desc' : 'asc',
                                    'page' => 1
                                ])) }}" class="group inline-flex items-center space-x-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                    <span>Estado</span>
                                    <span class="font-mono text-xs {{ $sort === 'estado' ? 'text-indigo-600 dark:text-indigo-400 font-black' : 'text-slate-400 dark:text-slate-600 group-hover:text-slate-600' }}">
                                        {{ $sort === 'estado' ? ($direction === 'asc' ? '▲' : '▼') : '⇅' }}
                                    </span>
                                </a>
                            </th>

                            {{-- 9. Acciones --}}
                            <th scope="col" class="py-2.5 px-3 text-right">Comprobante</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse($ventas as $v)
                            <tr class="hover:bg-indigo-50/40 dark:hover:bg-slate-700/40 transition-colors {{ $v->estado === 'anulada' ? 'bg-rose-50/30 dark:bg-rose-950/20 text-rose-700 dark:text-rose-400' : '' }}">
                                {{-- Comprobante --}}
                                <td class="py-2 px-3 font-mono font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('ventas.show', $v->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ $v->comprobante }}
                                        </a>
                                        <span class="text-[9px] font-bold px-1 rounded font-mono 
                                            @if($v->isFacturaA()) bg-indigo-100 text-indigo-800 dark:bg-indigo-900/60 dark:text-indigo-200
                                            @elseif($v->isFacturaC()) bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200
                                            @elseif($v->isRemito()) bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200
                                            @elseif($v->isPresupuesto()) bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200
                                            @elseif($v->isNotaCredito()) bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-200
                                            @else bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200
                                            @endif" 
                                            title="{{ $v->tipo_comprobante_nombre }}">
                                            {{ $v->letra_comprobante }}
                                        </span>
                                        @if($v->esExentoIva())
                                            <span class="text-[9px] font-bold px-1 rounded font-mono bg-teal-100 text-teal-800 dark:bg-teal-950/60 dark:text-teal-300 border border-teal-300 dark:border-teal-800" title="Operación Exenta de IVA (Ley 19.640 - Tierra del Fuego)">
                                                EX
                                            </span>
                                        @endif
                                        @if($v->cae)
                                            <span class="text-[9px] bg-sky-100 text-sky-800 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800 font-mono font-bold px-1 rounded" title="Factura Digital ARCA - CAE: {{ $v->cae }}">
                                                ARCA
                                            </span>
                                        @elseif($v->tipo_emision === 'fiscal_hasar')
                                            <span class="text-[9px] bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800 font-mono font-bold px-1 rounded" title="Controlador Fiscal Hasar 2G Local">
                                                HASAR
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Fecha / Hora --}}
                                <td class="py-2 px-3 whitespace-nowrap text-slate-600 dark:text-slate-400">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $v->fecha ? $v->fecha->format('d/m/Y') : '-' }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono ml-1">{{ $v->fecha ? $v->fecha->format('H:i') : '' }}</span>
                                </td>

                                {{-- Sucursal --}}
                                <td class="py-2 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-black uppercase {{ $v->sucursal?->isTdf() ? 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-800' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' }}">
                                        {{ $v->sucursal?->codigo_sucursal ?? 'N/A' }}
                                    </span>
                                    <span class="text-[11px] text-slate-600 dark:text-slate-400 ml-1">
                                        {{ $v->sucursal?->nombre }}
                                    </span>
                                </td>

                                {{-- Cliente --}}
                                <td class="py-2 px-3">
                                    @if($v->es_consumidor_final)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                            Consumidor Final
                                        </span>
                                    @else
                                        <a href="{{ route('clientes.show', $v->id_cliente) }}" class="font-bold text-indigo-700 dark:text-indigo-400 hover:underline block truncate max-w-[200px]" title="{{ $v->cliente_nombre }}">
                                            {{ $v->cliente_nombre }}
                                        </a>
                                        @if($v->cliente_documento)
                                            <span class="text-[10px] text-slate-400 font-mono">DNI: {{ $v->cliente_documento }}</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Medio de Pago --}}
                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                    @if($v->medio_pago === 'credito_personal')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-200 border border-indigo-300 dark:border-indigo-700">
                                            📄 Crédito Personal
                                        </span>
                                    @elseif($v->medio_pago === 'efectivo')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700">
                                            💵 Efectivo
                                        </span>
                                    @elseif($v->medio_pago === 'tarjeta_debito')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-cyan-100 dark:bg-cyan-900/60 text-cyan-800 dark:text-cyan-200 border border-cyan-300 dark:border-cyan-700">
                                            💳 Débito
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 border border-blue-300 dark:border-blue-700">
                                            💳 Tarjeta Crédito
                                        </span>
                                    @endif
                                </td>

                                {{-- Crédito Asociado / Cuotas --}}
                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                    @if($v->credito)
                                        <a href="{{ route('creditos.show', $v->credito->id) }}" class="inline-flex flex-col items-center hover:opacity-80">
                                            <span class="font-mono font-black text-indigo-700 dark:text-indigo-400 text-xs">
                                                {{ $v->credito->full_identificador }}
                                            </span>
                                            <span class="text-[9px] text-slate-500 font-bold">
                                                {{ $v->cuotas_credito }} cuota(s) de ${{ number_format((float)$v->valor_cuota_credito, 2, ',', '.') }}
                                            </span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-[10px]">-</span>
                                    @endif
                                </td>

                                {{-- Total --}}
                                <td class="py-2 px-3 text-right font-mono font-black text-slate-900 dark:text-white tabular-nums whitespace-nowrap">
                                    $ {{ number_format((float)$v->total, 2, ',', '.') }}
                                </td>

                                {{-- Estado --}}
                                <td class="py-2 px-3 text-center whitespace-nowrap">
                                    @if($v->estado === 'completada')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            Completada
                                        </span>
                                    @elseif($v->estado === 'remito')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800" title="Remito pendiente de facturar">
                                            Remito
                                        </span>
                                    @elseif($v->estado === 'presupuesto')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800" title="Cotización / Presupuesto">
                                            Presupuesto
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            Anulada
                                        </span>
                                    @endif
                                </td>

                                {{-- Acciones --}}
                                <td class="py-2 px-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-1">
                                        <a href="{{ route('ventas.show', $v->id) }}" title="Ver Detalle de Venta"
                                           class="p-1.5 text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition">
                                            👁️
                                        </a>
                                        <a href="{{ route('ventas.imprimir', ['venta' => $v->id, 'formato' => 'ticket']) }}" target="_blank" title="Imprimir Ticket Térmico (80mm)"
                                           class="p-1.5 text-slate-600 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition font-mono text-[10px] font-black uppercase">
                                            🖨️ Ticket
                                        </a>
                                        <a href="{{ route('ventas.imprimir', ['venta' => $v->id, 'formato' => 'a4']) }}" target="_blank" title="Imprimir Factura A4"
                                           class="p-1.5 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-md transition font-mono text-[10px] font-black uppercase">
                                            📄 A4
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="text-3xl mb-2">🧾</div>
                                    <p class="font-bold text-sm">No se encontraron ventas para los criterios seleccionados.</p>
                                    <p class="text-xs mt-1">Pruebe ajustando los filtros de fecha, sucursal o buscador.</p>
                                    <div class="mt-4">
                                        <a href="{{ route('ventas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg transition uppercase">
                                            ➕ Emitir Primera Venta en Mostrador
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if($ventas->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    {{ $ventas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
