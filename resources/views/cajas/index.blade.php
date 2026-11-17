<x-app-layout>
    @php
        $sucursalFiltro = $sucursalFiltro ?? request('sucursal', $sucursalActiva->id ?? 1);
    @endphp
    <div class="max-w-7xl mx-auto py-4 px-2 sm:px-4 space-y-5" x-data="{ modalAbrirCaja: false, montoApertura: 0, obsApertura: '', sucursalApertura: {{ $sucursalActiva->id ?? 1 }}, modalMovimiento: false, tipoMov: 'retiro', montoMov: '', motivoMov: '', compMov: '' }">
        
        <!-- Header Principal -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                    <span>💵 Control de Cajas & Cierres Z</span>
                    <span class="text-xs font-black uppercase px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                        {{ $sucursales->firstWhere('id', $sucursalFiltro)?->nombre ?? 'Todas las Sucursales' }}
                    </span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Historial de turnos, aperturas de jornada, arqueos de efectivo físico, fallos de caja y comprobantes de Cierre Z.
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('cajas.historico-medios') }}" 
                   class="bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 font-bold text-xs py-2 px-3.5 rounded-xl transition uppercase flex items-center gap-1.5 shadow-xs">
                    <span>📊 Histórico por Medios de Pago</span>
                </a>

                <a href="{{ route('ventas.create') }}" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-3.5 rounded-xl transition uppercase flex items-center gap-1.5 shadow-xs">
                    <span>🛒 Ir al Facturador (POS)</span>
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

        <!-- TARJETA: ESTADO DE LA CAJA ACTIVA DE LA SUCURSAL -->
        @if($cajaActiva)
            <div class="p-4 rounded-2xl border {{ $requiereCierreAnterior ? 'bg-amber-50 dark:bg-amber-950/60 border-amber-400 dark:border-amber-700' : 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-400 dark:border-emerald-700' }} shadow-xs">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-xl {{ $requiereCierreAnterior ? 'bg-amber-500' : 'bg-emerald-600' }} text-white flex items-center justify-center text-xl font-black shrink-0 shadow-sm">
                            {{ $requiereCierreAnterior ? '⚠️' : '🟢' }}
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                    {{ $requiereCierreAnterior ? 'Caja de Jornada Anterior sin Cerrar' : 'Caja Abierta en Curso' }} (#{{ $cajaActiva->id }})
                                </h2>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase {{ $requiereCierreAnterior ? 'bg-amber-200 dark:bg-amber-900 text-amber-950 dark:text-amber-200' : 'bg-emerald-200 dark:bg-emerald-900 text-emerald-950 dark:text-emerald-200' }}">
                                    {{ $cajaActiva->sucursal?->nombre }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-300">
                                Apertura: <strong>{{ $cajaActiva->fecha_apertura->format('d/m/Y H:i') }} hs</strong> por <strong>{{ $cajaActiva->usuarioApertura?->name ?? 'Usuario' }}</strong>
                                @if($cajaActiva->observaciones_apertura)
                                    • <span class="italic text-slate-500">"{{ $cajaActiva->observaciones_apertura }}"</span>
                                @endif
                            </p>
                            <div class="flex flex-wrap items-center gap-3 pt-1 text-xs">
                                <span class="bg-white/80 dark:bg-slate-900/60 px-2.5 py-1 rounded-lg border border-slate-200/80 dark:border-slate-700 font-bold">
                                    Fondo Inicial: <strong class="font-mono text-slate-900 dark:text-white">${{ number_format($cajaActiva->monto_inicial_efectivo, 2, ',', '.') }}</strong>
                                </span>
                                <span class="bg-white/80 dark:bg-slate-900/60 px-2.5 py-1 rounded-lg border border-slate-200/80 dark:border-slate-700 font-bold">
                                    Efectivo Teórico: <strong class="font-mono text-emerald-700 dark:text-emerald-400">${{ number_format($totalesCajaActiva['efectivo_esperado'] ?? 0, 2, ',', '.') }}</strong>
                                </span>
                                <span class="bg-white/80 dark:bg-slate-900/60 px-2.5 py-1 rounded-lg border border-slate-200/80 dark:border-slate-700 font-bold">
                                    Facturado Turno: <strong class="font-mono text-indigo-700 dark:text-indigo-400">${{ number_format($totalesCajaActiva['total_general'] ?? 0, 2, ',', '.') }}</strong> ({{ $totalesCajaActiva['ventas_count'] ?? 0 }} ventas)
                                </span>
                                @if(($totalesCajaActiva['total_ingresos'] ?? 0) > 0)
                                    <span class="bg-emerald-100 dark:bg-emerald-950/80 px-2.5 py-1 rounded-lg border border-emerald-300 dark:border-emerald-700 font-bold text-emerald-800 dark:text-emerald-300">
                                        Ingresos (+): <strong class="font-mono">+${{ number_format($totalesCajaActiva['total_ingresos'], 2, ',', '.') }}</strong> ({{ $totalesCajaActiva['cant_ingresos'] ?? 0 }})
                                    </span>
                                @endif
                                @if(($totalesCajaActiva['total_retiros'] ?? 0) > 0)
                                    <span class="bg-rose-100 dark:bg-rose-950/80 px-2.5 py-1 rounded-lg border border-rose-300 dark:border-rose-700 font-bold text-rose-800 dark:text-rose-300">
                                        Retiros (-): <strong class="font-mono">-${{ number_format($totalesCajaActiva['total_retiros'], 2, ',', '.') }}</strong> ({{ $totalesCajaActiva['cant_retiros'] ?? 0 }})
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" @click="modalMovimiento = true"
                                class="bg-amber-600 hover:bg-amber-700 text-white font-black text-xs py-2.5 px-3.5 rounded-xl shadow-md transition uppercase flex items-center gap-1.5 cursor-pointer">
                            <span>💸 Ingreso / Retiro</span>
                        </button>

                        <a href="{{ route('cajas.cerrar', $cajaActiva->id) }}" 
                           class="bg-slate-900 hover:bg-slate-800 dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 font-black text-xs py-2.5 px-4 rounded-xl shadow-md transition uppercase flex items-center gap-1.5 cursor-pointer">
                            <span>🔒 Realizar Cierre Z / Arqueo</span>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="p-4 rounded-2xl border bg-slate-50 dark:bg-slate-900/60 border-slate-300 dark:border-slate-700 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-300 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-lg font-black shrink-0">
                        🔒
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-tight">
                            No hay caja abierta para {{ $sucursales->firstWhere('id', $sucursalFiltro)?->nombre ?? $sucursalActiva->nombre ?? 'la sucursal' }}
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Inicie una apertura de caja con el fondo en efectivo correspondiente para habilitar las ventas.
                        </p>
                    </div>
                </div>
                <button type="button" @click="modalAbrirCaja = true"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-4 rounded-xl transition uppercase flex items-center gap-1.5 cursor-pointer shadow-xs">
                    <span>🟢 Iniciar Apertura de Caja</span>
                </button>
            </div>
        @endif

        <!-- FILTROS Y BUSCADOR -->
        <div class="bg-white dark:bg-slate-800 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
            <form method="GET" action="{{ route('cajas.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 items-end">
                <!-- Filtro Sucursal -->
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Sucursal
                    </label>
                    <select name="sucursal" class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="all" {{ $sucursalFiltro === 'all' ? 'selected' : '' }}>Todas las sucursales</option>
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ (string)$sucursalFiltro === (string)$suc->id ? 'selected' : '' }}>
                                {{ $suc->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Estado -->
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Estado
                    </label>
                    <select name="estado" class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                        <option value="all" {{ request('estado') === 'all' ? 'selected' : '' }}>Todos</option>
                        <option value="abierta" {{ request('estado') === 'abierta' ? 'selected' : '' }}>Abierta</option>
                        <option value="cerrada" {{ request('estado') === 'cerrada' ? 'selected' : '' }}>Cerrada</option>
                    </select>
                </div>

                <!-- Fecha Desde -->
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Fecha Desde
                    </label>
                    <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                           class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>

                <!-- Fecha Hasta -->
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Fecha Hasta
                    </label>
                    <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                           class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                </div>

                <!-- Botones -->
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="flex-1 bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white font-bold text-xs py-2 px-3 rounded-xl transition uppercase flex items-center justify-center gap-1">
                        <span>🔍 Filtrar</span>
                    </button>
                    <a href="{{ route('cajas.index', array_merge(request()->query(), ['export' => 'excel'])) }}" 
                       class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-3 rounded-xl transition uppercase flex items-center gap-1 shadow-xs"
                       title="Exportar todas las cajas filtradas a Excel">
                        <span>📥</span>
                        <span class="hidden sm:inline">Excel</span>
                    </a>
                    @if(request()->hasAny(['sucursal', 'estado', 'fecha_desde', 'fecha_hasta']))
                        <a href="{{ route('cajas.index') }}" 
                           class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs py-2 px-3 rounded-xl transition uppercase"
                           title="Limpiar filtros">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- RESUMEN DE TOTALES POR MEDIO DE PAGO (FILTRO ACTUAL) -->
        @if(isset($totalesFiltro) && $totalesFiltro->total_facturado > 0)
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-2xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-2">
                    <h3 class="text-xs font-black uppercase text-slate-500 tracking-wider flex items-center gap-2">
                        <span>📊 Totales Consolidados por Medio de Pago (Período / Filtro Seleccionado)</span>
                    </h3>
                    <div class="text-xs text-slate-500">
                        Total Facturado: <strong class="font-mono text-indigo-700 dark:text-indigo-400 font-black">${{ number_format($totalesFiltro->total_facturado, 2, ',', '.') }}</strong>
                        ({{ number_format($totalesFiltro->cant_ventas, 0, ',', '.') }} ventas)
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
                    <!-- Efectivo -->
                    <div class="p-2.5 rounded-xl border border-emerald-200 dark:border-emerald-800/80 bg-emerald-50/50 dark:bg-emerald-950/20">
                        <div class="text-[10px] font-bold text-emerald-800 dark:text-emerald-300 uppercase">💵 Efectivo</div>
                        <div class="text-sm font-black font-mono text-emerald-900 dark:text-emerald-200 mt-1">
                            ${{ number_format($totalesFiltro->total_efectivo, 2, ',', '.') }}
                        </div>
                        <div class="text-[10px] text-emerald-700 dark:text-emerald-400">
                            {{ $totalesFiltro->total_facturado > 0 ? round(($totalesFiltro->total_efectivo / $totalesFiltro->total_facturado) * 100, 1) : 0 }}%
                        </div>
                    </div>

                    <!-- Débito -->
                    <div class="p-2.5 rounded-xl border border-sky-200 dark:border-sky-800/80 bg-sky-50/50 dark:bg-sky-950/20">
                        <div class="text-[10px] font-bold text-sky-800 dark:text-sky-300 uppercase">💳 Tarjeta Débito</div>
                        <div class="text-sm font-black font-mono text-sky-900 dark:text-sky-200 mt-1">
                            ${{ number_format($totalesFiltro->total_debito, 2, ',', '.') }}
                        </div>
                        <div class="text-[10px] text-sky-700 dark:text-sky-400">
                            {{ $totalesFiltro->total_facturado > 0 ? round(($totalesFiltro->total_debito / $totalesFiltro->total_facturado) * 100, 1) : 0 }}%
                        </div>
                    </div>

                    <!-- Crédito -->
                    <div class="p-2.5 rounded-xl border border-indigo-200 dark:border-indigo-800/80 bg-indigo-50/50 dark:bg-indigo-950/20">
                        <div class="text-[10px] font-bold text-indigo-800 dark:text-indigo-300 uppercase">💳 Tarjeta Crédito</div>
                        <div class="text-sm font-black font-mono text-indigo-900 dark:text-indigo-200 mt-1">
                            ${{ number_format($totalesFiltro->total_credito, 2, ',', '.') }}
                        </div>
                        <div class="text-[10px] text-indigo-700 dark:text-indigo-400">
                            {{ $totalesFiltro->total_facturado > 0 ? round(($totalesFiltro->total_credito / $totalesFiltro->total_facturado) * 100, 1) : 0 }}%
                        </div>
                    </div>

                    <!-- Crédito Personal -->
                    <div class="p-2.5 rounded-xl border border-amber-200 dark:border-amber-800/80 bg-amber-50/50 dark:bg-amber-950/20">
                        <div class="text-[10px] font-bold text-amber-800 dark:text-amber-300 uppercase">📑 Crédito / Cta Cte</div>
                        <div class="text-sm font-black font-mono text-amber-900 dark:text-amber-200 mt-1">
                            ${{ number_format($totalesFiltro->total_credito_personal, 2, ',', '.') }}
                        </div>
                        <div class="text-[10px] text-amber-700 dark:text-amber-400">
                            {{ $totalesFiltro->total_facturado > 0 ? round(($totalesFiltro->total_credito_personal / $totalesFiltro->total_facturado) * 100, 1) : 0 }}%
                        </div>
                    </div>

                    <!-- Transferencia / MP -->
                    <div class="p-2.5 rounded-xl border border-teal-200 dark:border-teal-800/80 bg-teal-50/50 dark:bg-teal-950/20">
                        <div class="text-[10px] font-bold text-teal-800 dark:text-teal-300 uppercase">📱 Transf / MP</div>
                        <div class="text-sm font-black font-mono text-teal-900 dark:text-teal-200 mt-1">
                            ${{ number_format($totalesFiltro->total_transferencia, 2, ',', '.') }}
                        </div>
                        <div class="text-[10px] text-teal-700 dark:text-teal-400">
                            {{ $totalesFiltro->total_facturado > 0 ? round(($totalesFiltro->total_transferencia / $totalesFiltro->total_facturado) * 100, 1) : 0 }}%
                        </div>
                    </div>

                    <!-- Ingresos / Retiros de Caja -->
                    <div class="p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40">
                        <div class="text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase">💸 Movs. Dinero</div>
                        <div class="text-xs font-mono font-bold mt-1 flex flex-col gap-0.5">
                            <span class="text-emerald-700 dark:text-emerald-400" title="Total Ingresos Manuales">+${{ number_format($totalesFiltro->total_ingresos, 0, ',', '.') }}</span>
                            <span class="text-rose-700 dark:text-rose-400" title="Total Egresos Manuales">-${{ number_format($totalesFiltro->total_retiros, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @php
            $sort = $sort ?? request()->get('sort', 'fecha_apertura');
            $direction = $direction ?? request()->get('direction', 'desc');
            $nextDirection = function($col) use ($sort, $direction) {
                return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
            };
            $sortUrl = function($col) use ($nextDirection) {
                return route('cajas.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
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

        <!-- TABLA DE HISTORIAL DE CAJAS -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-900/80 text-slate-600 dark:text-slate-400 uppercase text-[10px] font-black border-b border-slate-200 dark:border-slate-700 select-none">
                        <tr>
                            <th class="py-3 px-3">
                                <a href="{{ $sortUrl('id') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por # Turno">
                                    <span># Turno</span>
                                    {!! $sortIndicator('id') !!}
                                </a>
                            </th>
                            <th class="py-3 px-3">
                                <a href="{{ $sortUrl('sucursal') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Sucursal">
                                    <span>Sucursal</span>
                                    {!! $sortIndicator('sucursal') !!}
                                </a>
                            </th>
                            <th class="py-3 px-3">
                                <a href="{{ $sortUrl('fecha_apertura') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Fecha">
                                    <span>Fecha</span>
                                    {!! $sortIndicator('fecha_apertura') !!}
                                </a>
                            </th>
                            <th class="py-3 px-3 text-right">
                                <a href="{{ $sortUrl('monto_inicial_efectivo') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Fondo de Apertura">
                                    <span>Fondo Apertura</span>
                                    {!! $sortIndicator('monto_inicial_efectivo') !!}
                                </a>
                            </th>
                            <th class="py-3 px-3 text-right">
                                <a href="{{ $sortUrl('monto_final_efectivo') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Efectivo de Cierre">
                                    <span>Efectivo Cierre</span>
                                    {!! $sortIndicator('monto_final_efectivo') !!}
                                </a>
                            </th>
                            <th class="py-3 px-3 text-right">
                                <a href="{{ $sortUrl('diferencia_efectivo') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Fallo o Diferencia">
                                    <span>Fallo / Dif</span>
                                    {!! $sortIndicator('diferencia_efectivo') !!}
                                </a>
                            </th>
                            <th class="py-3 px-3">Totales por Medio de Pago</th>
                            <th class="py-3 px-3 text-right">
                                <a href="{{ $sortUrl('total_general') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Total Facturado">
                                    <span>Total Facturado</span>
                                    {!! $sortIndicator('total_general') !!}
                                </a>
                            </th>
                            <th class="py-3 px-3 text-center">
                                <a href="{{ $sortUrl('estado') }}" class="group flex items-center justify-center space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Estado">
                                    <span>Estado</span>
                                    {!! $sortIndicator('estado') !!}
                                </a>
                            </th>
                            <th class="py-3 px-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($cajas as $caja)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition">
                                <td class="py-3 px-3 font-mono font-bold text-slate-900 dark:text-white">
                                    <a href="{{ route('cajas.show', $caja->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                        #{{ $caja->id }}
                                    </a>
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-700 dark:text-slate-300">
                                    {{ $caja->sucursal?->nombre ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 dark:text-white">
                                        {{ $caja->fecha_apertura->format('d/m/Y') }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">
                                        {{ $caja->fecha_apertura->format('H:i') }} - {{ $caja->fecha_cierre ? $caja->fecha_cierre->format('H:i') : 'En curso' }}
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-right font-mono">
                                    ${{ number_format($caja->monto_inicial_efectivo, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                    ${{ number_format($caja->monto_final_efectivo_real ?: $caja->monto_final_efectivo_sistema, 2, ',', '.') }}
                                    @if(($caja->total_ingresos_efectivo ?? 0) > 0 || ($caja->total_retiros_efectivo ?? 0) > 0)
                                        <div class="text-[9px] font-normal flex items-center justify-end gap-1">
                                            @if(($caja->total_ingresos_efectivo ?? 0) > 0)
                                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold" title="Total Ingresos Manuales">+${{ number_format($caja->total_ingresos_efectivo, 0, ',', '.') }}</span>
                                            @endif
                                            @if(($caja->total_retiros_efectivo ?? 0) > 0)
                                                <span class="text-rose-600 dark:text-rose-400 font-semibold" title="Total Retiros / Gastos Manuales">-${{ number_format($caja->total_retiros_efectivo, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold">
                                    @if($caja->fecha_cierre)
                                        @php
                                            $dif = (float) $caja->diferencia_efectivo;
                                        @endphp
                                        @if(abs($dif) < 0.01)
                                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">$0,00</span>
                                        @elseif($dif > 0)
                                            <span class="text-sky-600 dark:text-sky-400" title="Sobrante de caja">
                                                +${{ number_format($dif, 2, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-rose-600 dark:text-rose-400" title="Faltante (Fallo de caja)">
                                                -${{ number_format(abs($dif), 2, ',', '.') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <div class="flex flex-wrap items-center gap-1.5 font-mono text-[10px]">
                                        @if(($caja->total_efectivo ?? 0) > 0)
                                            <span class="bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 px-1.5 py-0.5 rounded" title="Efectivo ({{ $caja->cant_ventas_efectivo }} ventas)">
                                                💵 ${{ number_format($caja->total_efectivo, 0, ',', '.') }}
                                            </span>
                                        @endif
                                        @if(($caja->total_tarjeta_debito ?? 0) > 0)
                                            <span class="bg-sky-50 dark:bg-sky-950/60 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800 px-1.5 py-0.5 rounded" title="Tarjeta Débito ({{ $caja->cant_ventas_tarjeta_debito }} ventas)">
                                                💳 Déb: ${{ number_format($caja->total_tarjeta_debito, 0, ',', '.') }}
                                            </span>
                                        @endif
                                        @if(($caja->total_tarjeta_credito ?? 0) > 0)
                                            <span class="bg-indigo-50 dark:bg-indigo-950/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 px-1.5 py-0.5 rounded" title="Tarjeta Crédito ({{ $caja->cant_ventas_tarjeta_credito }} ventas)">
                                                💳 Créd: ${{ number_format($caja->total_tarjeta_credito, 0, ',', '.') }}
                                            </span>
                                        @endif
                                        @if(($caja->total_credito_personal ?? 0) > 0)
                                            <span class="bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 px-1.5 py-0.5 rounded" title="Crédito Personal / Cta Cte ({{ $caja->cant_ventas_credito_personal }} ventas)">
                                                📑 CtaCte: ${{ number_format($caja->total_credito_personal, 0, ',', '.') }}
                                            </span>
                                        @endif
                                        @if(($caja->total_transferencia ?? 0) > 0)
                                            <span class="bg-teal-50 dark:bg-teal-950/60 text-teal-800 dark:text-teal-300 border border-teal-200 dark:border-teal-800 px-1.5 py-0.5 rounded" title="Transferencia / Mercado Pago ({{ $caja->cant_ventas_transferencia }} ventas)">
                                                📱 Transf: ${{ number_format($caja->total_transferencia, 0, ',', '.') }}
                                            </span>
                                        @endif
                                        @if(($caja->total_otros ?? 0) > 0)
                                            <span class="bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 px-1.5 py-0.5 rounded" title="Otros Medios">
                                                📦 Otros: ${{ number_format($caja->total_otros, 0, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-black text-indigo-700 dark:text-indigo-400">
                                    ${{ number_format($caja->total_ventas, 2, ',', '.') }}
                                    <div class="text-[10px] text-slate-500 font-normal">
                                        {{ $caja->cantidad_ventas }} ventas
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($caja->estado === 'abierta')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                            Abierta
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                                            Cerrada
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('cajas.show', $caja->id) }}" 
                                           class="bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 font-bold text-[10px] py-1 px-2 rounded-lg transition flex items-center gap-1"
                                           title="Ver Detalle Completo de la Jornada">
                                            👁️ Detalle
                                        </a>

                                        @if($caja->estado === 'abierta')
                                            <a href="{{ route('cajas.cerrar', $caja->id) }}" 
                                               class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-[10px] py-1 px-2.5 rounded-lg transition uppercase">
                                                Cierre Z
                                            </a>
                                        @else
                                            <a href="{{ route('cajas.comprobante', ['caja' => $caja->id, 'formato' => 'ticket']) }}" target="_blank"
                                               class="bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-bold text-[10px] py-1 px-2 rounded-lg transition"
                                               title="Ticket Cierre Z (80mm)">
                                                🖨️
                                            </a>
                                            <a href="{{ route('cajas.comprobante', ['caja' => $caja->id, 'formato' => 'a4']) }}" target="_blank"
                                               class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-[10px] py-1 px-2 rounded-lg transition"
                                               title="Comprobante Cierre Z A4">
                                                📄
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-8 text-center text-slate-500">
                                    No se encontraron sesiones de caja registradas para los filtros aplicados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($cajas->hasPages())
                <div class="p-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                    {{ $cajas->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL APERTURA DE CAJA DIRECTA -->
        <div x-show="modalAbrirCaja" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border-2 border-emerald-500/80 space-y-4"
                 @click.away="modalAbrirCaja = false">
                
                <form method="POST" action="{{ route('cajas.abrir') }}" class="space-y-4">
                    @csrf
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-black text-base shrink-0 border border-emerald-300 dark:border-emerald-700">
                                🟢
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                    Apertura de Caja
                                </h3>
                                <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase tracking-wider block">
                                    Inicio de Turno
                                </span>
                            </div>
                        </div>
                        <button type="button" @click="modalAbrirCaja = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                                Sucursal:
                            </label>
                            <select name="id_sucursal" class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}" {{ (string)$suc->id === (string)($sucursalActiva->id ?? 1) ? 'selected' : '' }}>
                                        {{ $suc->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                                Monto Inicial en Efectivo ($ ARS):
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">$</span>
                                <input type="number" step="0.01" min="0" name="monto_inicial_efectivo" required
                                       class="w-full pl-8 pr-3 font-mono font-black text-lg py-2 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right focus:ring-2 focus:ring-emerald-500"
                                       placeholder="0.00" autofocus>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                                Observaciones (Opcional):
                            </label>
                            <input type="text" name="observaciones" 
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                                   placeholder="Ej: Cambio inicial...">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" @click="modalAbrirCaja = false"
                                class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-xl transition uppercase">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs py-2 px-5 rounded-xl shadow-md transition uppercase flex items-center gap-1.5 cursor-pointer">
                            <span>✓ Abrir Caja</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($cajaActiva)
        <!-- MODAL: REGISTRAR INGRESO O RETIRO MANUAL DE EFECTIVO -->
        <div x-show="modalMovimiento" x-cloak
             class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
             @keydown.escape.window="modalMovimiento = false">
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-150"
                 @click.outside="modalMovimiento = false">
                
                <form method="POST" action="{{ route('cajas.movimientos.store', $cajaActiva->id) }}" class="p-5 space-y-4">
                    @csrf
                    <input type="hidden" name="tipo" :value="tipoMov">

                    <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-700">
                        <div class="flex items-center gap-2">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-base shrink-0 border"
                                  :class="tipoMov === 'retiro' ? 'bg-rose-100 dark:bg-rose-950/80 text-rose-700 dark:text-rose-400 border-rose-300 dark:border-rose-700' : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-400 border-emerald-300 dark:border-emerald-700'">
                                <span x-text="tipoMov === 'retiro' ? '💸' : '💰'"></span>
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight"
                                    x-text="tipoMov === 'retiro' ? 'Retiro / Gasto de Caja' : 'Ingreso de Efectivo'">
                                </h3>
                                <span class="text-[10px] text-slate-500 uppercase tracking-wider block">
                                    Caja #{{ $cajaActiva->id }} - {{ $cajaActiva->sucursal?->nombre }}
                                </span>
                            </div>
                        </div>
                        <button type="button" @click="modalMovimiento = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                    </div>

                    <!-- Selector de Tipo: Retiro vs Ingreso -->
                    <div class="grid grid-cols-2 gap-2 bg-slate-100 dark:bg-slate-900/60 p-1 rounded-xl">
                        <button type="button" 
                                @click="tipoMov = 'retiro'"
                                :class="tipoMov === 'retiro' ? 'bg-white dark:bg-slate-800 text-rose-700 dark:text-rose-400 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900'"
                                class="py-2 text-xs rounded-lg transition text-center uppercase flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>💸 Retiro / Gasto</span>
                        </button>
                        <button type="button" 
                                @click="tipoMov = 'ingreso'"
                                :class="tipoMov === 'ingreso' ? 'bg-white dark:bg-slate-800 text-emerald-700 dark:text-emerald-400 font-black shadow-xs' : 'text-slate-600 dark:text-slate-400 font-bold hover:text-slate-900'"
                                class="py-2 text-xs rounded-lg transition text-center uppercase flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>💰 Ingreso Extra</span>
                        </button>
                    </div>

                    <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                                Monto ($ ARS): <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">$</span>
                                <input type="number" step="0.01" min="0.01" name="monto" x-model="montoMov" required
                                       class="w-full pl-8 pr-3 font-mono font-black text-lg py-2 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right focus:ring-2 focus:ring-amber-500"
                                       placeholder="0.00" autofocus>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                                Motivo / Justificación: <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="motivo" x-model="motivoMov" required
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500"
                                   :placeholder="tipoMov === 'retiro' ? 'Ej: Pago flete, Compra artículos de limpieza, etc.' : 'Ej: Cobro pendiente cliente, Aporte sencillo/cambio, etc.'">
                            <span class="text-[10px] text-slate-400 mt-0.5 block italic">
                                Este motivo quedará registrado en la auditoría y figurará en el comprobante Cierre Z.
                            </span>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                                N° de Comprobante / Recibo (Opcional):
                            </label>
                            <input type="text" name="comprobante" x-model="compMov"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                                   placeholder="Ej: FAC-A-0001-00004321 / Recibo N° 123">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                        <button type="button" @click="modalMovimiento = false"
                                class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-xl transition uppercase">
                            Cancelar
                        </button>
                        <button type="submit"
                                :class="tipoMov === 'retiro' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                                class="text-white font-black text-xs py-2 px-5 rounded-xl shadow-md transition uppercase flex items-center gap-1.5 cursor-pointer">
                            <span x-text="tipoMov === 'retiro' ? '✓ Registrar Retiro' : '✓ Registrar Ingreso'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
