<x-app-layout>
    <div x-data="{
        modalEtiquetas: false,
        etiquetaUrl: '',
        etiquetaCodigo: '',
        etiquetaDesc: '',
        etiquetaCantidad: 1,
        etiquetaMotivo: 'Etiqueta rota / dañada en salón',
        abrirModalEtiquetas(url, cod, desc, cant) {
            this.etiquetaUrl = url;
            this.etiquetaCodigo = cod;
            this.etiquetaDesc = desc;
            this.etiquetaCantidad = 1;
            this.etiquetaMotivo = 'Etiqueta rota / dañada en salón';
            this.modalEtiquetas = true;
        },
        imprimir() {
            const targetUrl = this.etiquetaUrl + '?cantidad=' + this.etiquetaCantidad + '&motivo=' + encodeURIComponent(this.etiquetaMotivo) + '&autoprint=1';
            window.open(targetUrl, '_blank');
            this.modalEtiquetas = false;
        }
    }">
    {{-- Encabezado Principal y Acciones --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-4 gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                <span>Catálogo y Stock de Productos</span>
                <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                    Multi-Sucursal
                </span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Control de inventario centralizado en Ushuaia y Trelew, códigos de barras y traslados.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('productos.traslado') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-300 dark:border-amber-700 rounded-lg shadow-2xs transition-all">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>Traslado entre Sucursales</span>
            </a>

            <a href="{{ route('stock.movimientos') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Movimientos / Auditoría</span>
            </a>

            <a href="{{ route('categorias.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span>Categorías</span>
            </a>

            <a href="{{ route('proveedores.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>Proveedores</span>
            </a>

            <a href="{{ route('productos.index', array_merge(request()->query(), ['export' => 'excel'])) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1.5 shadow-sm"
               title="Exportar todos los productos filtrados a Excel / CSV">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>EXPORTAR EXCEL</span>
            </a>

            <a href="{{ route('remitos.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg text-xs font-black transition-all flex items-center space-x-1.5 shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>INGRESO DE MERCADERÍA</span>
            </a>

            <a href="{{ route('productos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1 shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                <span>NUEVO ARTÍCULO</span>
            </a>
        </div>
    </div>

    {{-- Barra Resumen Compacta (Una Sola Línea) --}}
    <div class="bg-white dark:bg-slate-800/90 px-3.5 py-2 rounded-xl border {{ !empty($metricas['is_filtered']) ? 'border-blue-300 dark:border-blue-700/80 bg-blue-50/20 dark:bg-blue-950/20' : 'border-slate-200 dark:border-slate-700/80' }} shadow-2xs mb-3 flex flex-wrap lg:flex-nowrap items-center justify-between gap-y-2 gap-x-4 text-xs">
        {{-- Item 1: Cantidad de Artículos --}}
        <div class="flex items-center space-x-2 shrink-0">
            <span class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-[11px] font-bold">
                🏷️
            </span>
            <div class="flex items-baseline space-x-1.5">
                <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Artículos:</span>
                <span class="font-black text-slate-900 dark:text-white tabular-nums text-sm">{{ number_format($metricas['total_articulos'], 0, ',', '.') }}</span>
                <span class="text-[10px] text-slate-400">({{ $metricas['page_articulos'] ?? count($productos) }} en pág.)</span>
            </div>
        </div>

        <div class="hidden lg:block w-px h-4 bg-slate-200 dark:bg-slate-700"></div>

        {{-- Item 2: Existencias Físicas --}}
        <div class="flex items-center space-x-2 shrink-0">
            <span class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[11px] font-bold">
                📦
            </span>
            <div class="flex items-baseline space-x-1.5">
                <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Existencias Físicas:</span>
                <span class="font-black text-emerald-600 dark:text-emerald-400 tabular-nums text-sm">{{ number_format($metricas['total_stock_unidades'], 0, ',', '.') }}</span>
                <span class="text-[10px] font-bold text-slate-400">unid.</span>
                <span class="text-[10px] text-slate-400">({{ number_format($metricas['page_stock_unidades'] ?? 0, 0, ',', '.') }} en pág.)</span>
            </div>
        </div>

        <div class="hidden lg:block w-px h-4 bg-slate-200 dark:bg-slate-700"></div>

        {{-- Item 3: Valuación de Venta --}}
        <div class="flex items-center space-x-2 shrink-0">
            <span class="w-6 h-6 rounded-md bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[11px] font-bold">
                💲
            </span>
            <div class="flex items-baseline space-x-1.5">
                <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Valuación Venta:</span>
                <span class="font-black text-blue-800 dark:text-blue-300 tabular-nums text-sm font-mono" title="{{ !empty($metricas['is_filtered']) ? 'Capital posible de venta filtrado' : 'Capital en venta' }}">
                    $ {{ number_format($metricas['valor_stock_venta'] ?? 0, 2, ',', '.') }}
                </span>
                @if(!empty($metricas['is_filtered']))
                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300">
                        {{ request('search') ? 'Búsqueda' : 'Filtro Activo' }}
                    </span>
                    <span class="hidden">Capital posible de venta filtrado</span>
                @endif
            </div>
        </div>

        <div class="hidden lg:block w-px h-4 bg-slate-200 dark:bg-slate-700"></div>

        {{-- Item 4: Sede Filtrada --}}
        <div class="flex items-center space-x-2 shrink-0">
            <span class="w-6 h-6 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-[11px] font-bold">
                🏢
            </span>
            <div class="flex items-center space-x-1.5">
                <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Sede:</span>
                @if(!empty($metricas['sucursal_id_filtrada']))
                    <span class="font-bold text-slate-900 dark:text-white truncate max-w-[150px]" title="{{ $metricas['sucursal_filtrada']->nombre ?? 'Sede' }}">
                        {{ $metricas['sucursal_filtrada']->nombre ?? 'Sede' }}
                    </span>
                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded {{ !empty($metricas['ver_otras']) ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300' : 'bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300' }}">
                        {{ !empty($metricas['ver_otras']) ? 'Otras visibles' : 'Solo activa' }}
                    </span>
                @else
                    <span class="font-bold text-indigo-700 dark:text-indigo-300">
                        Consolidado
                    </span>
                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-950/80 text-indigo-800 dark:text-indigo-300">
                        {{ $metricas['sucursales_con_stock'] }} sedes
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Panel de Filtros Inteligentes y Toolbar de Sucursales --}}
    <div class="bg-white dark:bg-slate-800/95 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80 mb-4 shadow-xs space-y-3">
        <form method="GET" action="{{ route('productos.index') }}" id="formFiltrosProductos" class="space-y-3">
            <input type="hidden" name="ver_otras" id="input_ver_otras" value="{{ !empty($metricas['ver_otras']) ? '1' : '0' }}">
            @if(request()->filled('sort'))
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
            @endif

            {{-- Fila 1: Barra de Búsqueda Principal + Botones de Acción --}}
            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Buscar por SKU, Código de Barras (EAN), Proveedor o Descripción..." 
                           class="w-full text-xs py-2 pl-9 pr-8 rounded-xl border border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 bg-slate-50/70 dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 font-medium transition-all">
                    @if(request('search'))
                        <button type="button" onclick="this.form.search.value=''; this.form.submit();" 
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200" title="Borrar búsqueda">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold text-xs py-2 px-4 rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span>Buscar</span>
                    </button>

                    @if(request()->anyFilled(['search', 'categoria_id', 'subcategoria_id', 'proveedor_id', 'stock_status', 'rotacion', 'orden', 'sort', 'fecha_alta_desde', 'fecha_alta_hasta']) || (request()->has('sucursal_id') && request('sucursal_id') !== (string)($metricas['sucursal_activa']->id ?? '1')) || request('ver_otras') == '1' || (request('sort') && request('sort') !== 'descripcion') || (request('direction') && request('direction') !== 'asc'))
                        <a href="{{ route('productos.index') }}" 
                           class="bg-slate-100 dark:bg-slate-700/80 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 font-bold text-xs py-2 px-3 rounded-xl transition-all flex items-center gap-1"
                           title="Restablecer filtros a la sucursal activa por defecto">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Limpiar</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Fila 2: Control Prominente de Sucursal y Botón Mostrar Demás Sucursales --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200/80 dark:border-slate-700/60">
                {{-- Selector Rápido de Alcance (Segmented control) --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Alcance:</span>
                    </span>

                    @php
                        $isActivaSelected = !empty($metricas['sucursal_id_filtrada']) && (int)$metricas['sucursal_id_filtrada'] === (int)($metricas['sucursal_activa']->id ?? 1);
                        $isTodasSelected = empty($metricas['sucursal_id_filtrada']);
                    @endphp

                    {{-- Botón 1: Sucursal Activa --}}
                    <button type="button" 
                            onclick="document.getElementById('select_sucursal_id').value = '{{ $metricas['sucursal_activa']->id ?? 1 }}'; document.getElementById('formFiltrosProductos').submit();"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-2xs {{ $isActivaSelected ? 'bg-blue-600 text-white shadow-blue-500/20 ring-1 ring-blue-500' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
                        <span class="w-2 h-2 rounded-full {{ $isActivaSelected ? 'bg-white' : 'bg-blue-500' }}"></span>
                        <span>{{ $metricas['sucursal_activa']->nombre ?? 'Sucursal Activa' }}</span>
                        <span class="text-[9px] uppercase px-1 py-0.2 rounded {{ $isActivaSelected ? 'bg-blue-700 text-blue-100' : 'bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400' }}">
                            Activa
                        </span>
                    </button>

                    {{-- Botón 2: Todas las Sucursales --}}
                    <button type="button" 
                            onclick="document.getElementById('select_sucursal_id').value = 'todas'; document.getElementById('formFiltrosProductos').submit();"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-2xs {{ $isTodasSelected ? 'bg-indigo-600 text-white shadow-indigo-500/20 ring-1 ring-indigo-500' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700' }}">
                        <svg class="w-3.5 h-3.5 {{ $isTodasSelected ? 'text-white' : 'text-indigo-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Todas las Sucursales</span>
                        <span class="text-[9px] uppercase px-1 py-0.2 rounded {{ $isTodasSelected ? 'bg-indigo-700 text-indigo-100' : 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400' }}">
                            Consolidado
                        </span>
                    </button>
                </div>

                {{-- Botón Switch: Mostrar / Ocultar Stock de Demás Sucursales --}}
                <div class="flex items-center gap-2">
                    @if(!empty($metricas['sucursal_id_filtrada']))
                        <button type="button" 
                                onclick="document.getElementById('input_ver_otras').value = '{{ !empty($metricas['ver_otras']) ? '0' : '1' }}'; document.getElementById('formFiltrosProductos').submit();"
                                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-2xs {{ !empty($metricas['ver_otras']) ? 'bg-emerald-600 text-white hover:bg-emerald-700 ring-2 ring-emerald-500/20' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-600' }}"
                                title="{{ !empty($metricas['ver_otras']) ? 'Clic para ocultar el stock de las demás sucursales y ver solo la activa' : 'Clic para mostrar también las existencias de las demás sucursales en la tabla' }}">
                            @if(!empty($metricas['ver_otras']))
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Stock Demás Sucursales: <strong>Visible</strong></span>
                                <span class="bg-emerald-700 text-white text-[9px] px-1.5 py-0.5 rounded font-black">ON</span>
                            @else
                                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                                <span>Mostrar stock demás sucursales</span>
                                <span class="bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-[9px] px-1.5 py-0.5 rounded font-bold">OFF</span>
                            @endif
                        </button>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium bg-white dark:bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Mostrando todas las sucursales (Consolidado)</span>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Fila 3: Grilla de Filtros Avanzados (Organizada en grupos con labels) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2.5 pt-1">
                {{-- 1. Categoría --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                        Categoría
                    </label>
                    <select name="categoria_id" onchange="this.form.submit()" 
                            class="w-full text-xs py-1.5 px-2.5 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 {{ request('categoria_id') ? 'border-blue-500 ring-1 ring-blue-500 text-blue-700 dark:text-blue-400 font-bold' : '' }}">
                        <option value="">Todas las Categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Subcategoría --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                        Subcategoría
                    </label>
                    <select name="subcategoria_id" onchange="this.form.submit()" 
                            class="w-full text-xs py-1.5 px-2.5 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 {{ request('subcategoria_id') ? 'border-blue-500 ring-1 ring-blue-500 font-bold text-blue-700 dark:text-blue-400' : '' }}">
                        <option value="">Todas las Subcategorías</option>
                        @foreach($subcategorias as $sub)
                            <option value="{{ $sub->id }}" {{ request('subcategoria_id') == $sub->id ? 'selected' : '' }}>{{ $sub->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Proveedor --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                        Proveedor
                    </label>
                    <select name="proveedor_id" onchange="this.form.submit()" 
                            class="w-full text-xs py-1.5 px-2.5 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 {{ request('proveedor_id') ? 'border-blue-500 ring-1 ring-blue-500 font-bold text-blue-700 dark:text-blue-400' : '' }}">
                        <option value="">Todos los Proveedores</option>
                        @foreach($proveedores as $prov)
                            <option value="{{ $prov->id }}" {{ request('proveedor_id') == $prov->id ? 'selected' : '' }}>
                                {{ $prov->legajo ? '[' . $prov->legajo_formateado . '] ' : '' }}{{ Str::limit($prov->nombre, 24) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Sucursal Puntual (Dropdown) --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                        Sede de Stock
                    </label>
                    <select name="sucursal_id" id="select_sucursal_id" onchange="this.form.submit()" 
                            class="w-full text-xs py-1.5 px-2.5 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 {{ !empty($metricas['sucursal_id_filtrada']) ? 'border-blue-500 ring-1 ring-blue-500 font-bold text-blue-700 dark:text-blue-400' : '' }}">
                        @foreach($sucursales as $suc)
                            @php
                                $isThisActiva = (int)$suc->id === (int)($metricas['sucursal_activa']->id ?? 1);
                                $isSelected = (int)($metricas['sucursal_id_filtrada'] ?? 0) === (int)$suc->id;
                            @endphp
                            <option value="{{ $suc->id }}" {{ $isSelected ? 'selected' : '' }}>
                                {{ $suc->nombre }}{{ $isThisActiva ? ' (Activa)' : '' }}
                            </option>
                        @endforeach
                        <option value="todas" {{ empty($metricas['sucursal_id_filtrada']) ? 'selected' : '' }}>
                            🌐 Todas las Sucursales (Consolidado)
                        </option>
                    </select>
                </div>

                {{-- 5. Estado de Stock --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                        Nivel de Stock
                    </label>
                    <select name="stock_status" onchange="this.form.submit()" 
                            class="w-full text-xs py-1.5 px-2.5 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 {{ request('stock_status') ? 'border-blue-500 ring-1 ring-blue-500 font-bold text-blue-700 dark:text-blue-400' : '' }}">
                        <option value="" {{ empty(request('stock_status')) ? 'selected' : '' }}>Solo con Existencias (> 0)</option>
                        <option value="agotado" {{ request('stock_status') == 'agotado' ? 'selected' : '' }}>Agotados (Stock 0)</option>
                        <option value="todos" {{ request('stock_status') == 'todos' ? 'selected' : '' }}>Todos los Niveles</option>
                    </select>
                </div>

                {{-- 6. Rotación / Ventas --}}
                <div>
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                        Rotación / Ventas
                    </label>
                    <select name="rotacion" onchange="this.form.submit()" 
                            class="w-full text-xs py-1.5 px-2.5 border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 {{ request('rotacion') ? 'border-amber-500 ring-1 ring-amber-500 font-bold text-amber-700 dark:text-amber-400' : '' }}">
                        <option value="">Todas las Rotaciones</option>
                        <option value="mas_vendidos" {{ request('rotacion') == 'mas_vendidos' ? 'selected' : '' }}>🔥 Más vendidos (+20)</option>
                        <option value="menos_vendidos" {{ request('rotacion') == 'menos_vendidos' ? 'selected' : '' }}>❄️ Menos vendidos (&le; 2)</option>
                        <option value="sin_venta_2_anos" {{ request('rotacion') == 'sin_venta_2_anos' ? 'selected' : '' }}>⚠️ Sin venta (+2 años)</option>
                        <option value="sin_venta_1_ano" {{ request('rotacion') == 'sin_venta_1_ano' ? 'selected' : '' }}>⏳ Sin venta (+1 año)</option>
                        <option value="sin_venta_6_meses" {{ request('rotacion') == 'sin_venta_6_meses' ? 'selected' : '' }}>⏱️ Sin venta (+6 meses)</option>
                        <option value="nunca_vendido" {{ request('rotacion') == 'nunca_vendido' ? 'selected' : '' }}>🚫 Nunca vendido (0)</option>
                        <option value="venta_reciente" {{ request('rotacion') == 'venta_reciente' ? 'selected' : '' }}>⚡ Venta reciente (&lt; 90d)</option>
                    </select>
                </div>
            </div>

            {{-- Fila 4: Ordenamiento + Chips de Filtros Activos --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-slate-200/70 dark:border-slate-700/60">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <span class="text-[10px] font-bold uppercase text-slate-400 dark:text-slate-500">
                        Filtros aplicados:
                    </span>
                    @if(!empty($metricas['sucursal_id_filtrada']))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            <span>Sede: {{ $metricas['sucursal_filtrada']->nombre ?? 'Filtrada' }}</span>
                            <button type="button" onclick="document.getElementById('select_sucursal_id').value='todas'; document.getElementById('formFiltrosProductos').submit();" class="text-blue-400 hover:text-blue-700">&times;</button>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            <span>Consolidado Todas las Sedes</span>
                        </span>
                    @endif

                    @if(!empty($metricas['ver_otras']) && !empty($metricas['sucursal_id_filtrada']))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            <span>Demás sucursales visibles</span>
                            <button type="button" onclick="document.getElementById('input_ver_otras').value='0'; document.getElementById('formFiltrosProductos').submit();" class="text-emerald-400 hover:text-emerald-700">&times;</button>
                        </span>
                    @endif

                    @if(request('categoria_id'))
                        @php $catItem = $categorias->firstWhere('id', request('categoria_id')); @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                            <span>Cat: {{ $catItem->nombre ?? request('categoria_id') }}</span>
                            <button type="button" onclick="this.form.categoria_id.value=''; this.form.submit();" class="text-slate-400 hover:text-slate-600">&times;</button>
                        </span>
                    @endif

                    @if(request('subcategoria_id'))
                        @php $subItem = $subcategorias->firstWhere('id', request('subcategoria_id')); @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                            <span>Sub: {{ $subItem->nombre ?? request('subcategoria_id') }}</span>
                            <button type="button" onclick="this.form.subcategoria_id.value=''; this.form.submit();" class="text-slate-400 hover:text-slate-600">&times;</button>
                        </span>
                    @endif

                    @if(request('proveedor_id'))
                        @php $prvItem = $proveedores->firstWhere('id', request('proveedor_id')); @endphp
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                            <span>Prov: {{ Str::limit($prvItem->nombre ?? request('proveedor_id'), 14) }}</span>
                            <button type="button" onclick="this.form.proveedor_id.value=''; this.form.submit();" class="text-slate-400 hover:text-slate-600">&times;</button>
                        </span>
                    @endif

                    @if(request('rotacion'))
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                            <span>Rotación activa</span>
                            <button type="button" onclick="this.form.rotacion.value=''; this.form.submit();" class="text-amber-400 hover:text-amber-600">&times;</button>
                        </span>
                    @endif

                    @if(request('stock_status') && request('stock_status') !== 'con_stock')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                            <span>{{ request('stock_status') === 'agotado' ? 'Agotados' : 'Todos los niveles' }}</span>
                            <button type="button" onclick="this.form.stock_status.value=''; this.form.submit();" class="text-slate-400 hover:text-slate-600">&times;</button>
                        </span>
                    @endif
                </div>

                {{-- Estado de ordenamiento o sugerencia interactiva --}}
                <div class="flex items-center gap-2 shrink-0">
                    @if($sort && ($sort !== 'descripcion' || $direction !== 'asc'))
                        @php
                            $sortLabels = [
                                'codigo'             => 'Código',
                                'descripcion'        => 'Descripción',
                                'precio'             => 'Precio',
                                'fecha_alta'         => 'Fecha Alta',
                                'fecha_modificacion' => 'Modificación',
                                'ultima_venta'       => 'Última Venta',
                                'ventas'             => 'Ventas',
                                'stock'              => 'Stock',
                            ];
                            $sortNombre = $sortLabels[$sort] ?? ucfirst($sort);
                            $dirNombre = $direction === 'asc' ? 'Ascendente (▲)' : 'Descendente (▼)';
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shadow-2xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            <span>Orden: {{ $sortNombre }} {{ $dirNombre }}</span>
                            <a href="{{ route('productos.index', array_merge(request()->except(['sort', 'direction', 'orden']), ['page' => 1])) }}" 
                               class="text-blue-400 hover:text-blue-700 dark:hover:text-blue-200 font-black ml-1 text-xs leading-none" 
                               title="Quitar orden personalizado y restablecer al orden por defecto">&times;</a>
                        </span>
                    @else
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium hidden sm:inline-flex items-center gap-1">
                            <span>💡 Clic en los encabezados para alternar ASC / DESC</span>
                        </span>
                    @endif
                </div>
            </div>
        </form>
    </div>

    @php
        $defaultDirections = [
            'codigo'       => 'asc',
            'descripcion'  => 'asc',
            'precio'       => 'desc',
            'fecha_alta'   => 'desc',
            'ultima_venta' => 'desc',
            'stock'        => 'desc',
        ];

        $nextDirection = function($col) use ($sort, $direction, $defaultDirections) {
            if ($sort === $col) {
                return $direction === 'asc' ? 'desc' : 'asc';
            }
            return $defaultDirections[$col] ?? 'asc';
        };

        $sortUrl = function($col) use ($nextDirection) {
            $params = request()->query();
            unset($params['orden']);
            return route('productos.index', array_merge($params, [
                'sort' => $col,
                'direction' => $nextDirection($col),
                'page' => 1
            ]));
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

    {{-- Tabla de Productos con Iluminación y Menú Contextual Integrado --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-300 dark:border-slate-700 shadow-2xs mb-4">
        <table class="w-full text-left border-collapse font-sans text-xs">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-900/80 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300 dark:border-slate-700 select-none">
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-44">
                        <a href="{{ $sortUrl('codigo') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Código">
                            <span>Código / Barras</span>
                            {!! $sortIndicator('codigo') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60">
                        <a href="{{ $sortUrl('descripcion') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Descripción">
                            <span>Descripción / Categoría</span>
                            {!! $sortIndicator('descripcion') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-32 text-right">
                        <a href="{{ $sortUrl('precio') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Precio">
                            <span>Precios</span>
                            {!! $sortIndicator('precio') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-28">
                        <a href="{{ $sortUrl('fecha_alta') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Fecha de Alta">
                            <span>Alta / Modif.</span>
                            {!! $sortIndicator('fecha_alta') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-36">
                        <a href="{{ $sortUrl('ultima_venta') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Última Venta">
                            <span>Última Venta</span>
                            {!! $sortIndicator('ultima_venta') !!}
                        </a>
                    </th>
                    <th class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 w-64">
                        @if(!empty($metricas['sucursal_id_filtrada']) && empty($metricas['ver_otras']))
                            Stock en {{ $metricas['sucursal_filtrada']->nombre ?? 'Sede Activa' }}
                            <span class="block text-[8px] font-normal lowercase tracking-normal text-blue-600 dark:text-blue-400 font-sans">solo sede activa</span>
                        @else
                            Stock por Sucursal
                            @if(!empty($metricas['sucursal_id_filtrada']) && !empty($metricas['ver_otras']))
                                <span class="block text-[8px] font-normal lowercase tracking-normal text-emerald-600 dark:text-emerald-400 font-sans">demás sedes visibles</span>
                            @endif
                        @endif
                    </th>
                    <th class="py-2.5 px-3 w-32 text-right">
                        <a href="{{ $sortUrl('stock') }}" class="group flex flex-col items-end text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Stock">
                            <div class="flex items-center justify-end space-x-1">
                                @if(!empty($metricas['sucursal_id_filtrada']))
                                    <span>Stock {{ $metricas['sucursal_filtrada']->codigo_sucursal ?? 'Sede' }}</span>
                                @else
                                    <span>Total Stock</span>
                                @endif
                                {!! $sortIndicator('stock') !!}
                            </div>
                            <span class="block text-[8px] font-normal lowercase tracking-normal text-slate-400 font-sans">
                                {{ !empty($metricas['sucursal_id_filtrada']) ? Str::limit($metricas['sucursal_filtrada']->nombre ?? 'filtrada', 16) : 'consolidado' }}
                            </span>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                @forelse ($productos as $producto)
                    @php
                        $esGenerico = !$producto->controla_stock;
                        $stockTotal = $producto->getStockTotal();
                        $esAgotado = !$esGenerico && $stockTotal <= 0;
                        $esBajo = !$esGenerico && $stockTotal > 0 && $stockTotal <= (float)$producto->stock_minimo;
                        
                        $rowHover = $esGenerico
                            ? 'bg-blue-50/20 dark:bg-blue-950/10 hover:bg-blue-50/70 dark:hover:bg-slate-700/60 border-l-4 border-l-blue-400 hover:border-l-blue-600'
                            : ($esAgotado 
                                ? 'bg-slate-50/50 dark:bg-slate-900/20 hover:bg-slate-100/70 dark:hover:bg-slate-700/60 border-l-4 border-l-transparent hover:border-l-slate-400'
                                : ($esBajo 
                                    ? 'bg-amber-50/40 dark:bg-amber-950/20 hover:bg-amber-100/60 dark:hover:bg-amber-900/40 border-l-4 border-l-amber-400 hover:border-l-amber-500'
                                    : 'bg-white dark:bg-slate-800/90 hover:bg-blue-50/90 dark:hover:bg-slate-700/80 border-l-4 border-l-transparent hover:border-l-blue-500'));
                    @endphp

                    <tr class="{{ $rowHover }} transition-all duration-150 cursor-pointer hover:shadow-xs group"
                        x-data="contextMenuRow()"
                        @click="openMenu($event)"
                        @contextmenu.prevent="openMenu($event)">
                        
                        {{-- Código y Código de Barras --}}
                        <td class="py-2 px-3 font-mono font-bold text-slate-900 dark:text-slate-100 border-r border-slate-200/60 dark:border-slate-700/60">
                            <div class="flex flex-col">
                                <div class="flex items-center justify-between gap-1">
                                    <a href="{{ route('productos.show', $producto->id) }}" class="text-blue-700 dark:text-blue-400 hover:underline tracking-tight">
                                        {{ $producto->codigo }}
                                    </a>
                                    <button type="button" 
                                            @click.stop="abrirModalEtiquetas('{{ route('productos.etiqueta', $producto->id) }}', '{{ addslashes($producto->codigo) }}', '{{ addslashes($producto->descripcion) }}', 1)" 
                                            title="Reimprimir etiquetas (Reposición por daño o falla - No modifica stock)" 
                                            class="p-1 rounded text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>
                                </div>
                                @if($producto->codigo_barra)
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                        {{ $producto->codigo_barra }}
                                    </span>
                                @endif
                            </div>

                            {{-- Menú Contextual Modular --}}
                            <x-context-menu>
                                <x-context-menu.header 
                                    :title="$producto->descripcion" 
                                    :subtitle="'SKU: ' . $producto->codigo . ($producto->codigo_barra ? ' • EAN: ' . $producto->codigo_barra : '')"
                                    :badge="$esGenerico ? 'Genérico' : ($esAgotado ? 'Agotado' : ($esBajo ? 'Stock Bajo' : 'Normal'))"
                                    :badgeColor="$esGenerico ? 'blue' : ($esAgotado ? 'rose' : ($esBajo ? 'amber' : 'emerald'))" />

                                <x-context-menu.group label="Etiquetas y Barcode">
                                    <x-context-menu.item href="{{ route('productos.etiqueta', $producto->id) }}" icon="printer" color="amber" target="_blank">
                                        Reimprimir Etiquetas (Por daño / falla)
                                    </x-context-menu.item>
                                </x-context-menu.group>

                                <x-context-menu.group label="Operaciones de Stock">
                                    <x-context-menu.item href="{{ route('productos.show', $producto->id) }}" icon="document" color="blue">
                                        Ver Ficha y Stock Multi-Sede
                                    </x-context-menu.item>

                                    @if($producto->controla_stock)
                                        <x-context-menu.item href="{{ route('productos.traslado', ['producto_id' => $producto->id]) }}" icon="history" color="amber">
                                            Trasladar entre Sucursales
                                        </x-context-menu.item>
                                    @endif
                                </x-context-menu.group>

                                <x-context-menu.group label="Catálogo y Precios">
                                    <x-context-menu.item href="{{ route('productos.edit', $producto->id) }}" icon="pencil">
                                        Editar Producto
                                    </x-context-menu.item>
                                </x-context-menu.group>

                                <x-context-menu.group label="Gestión">
                                    <x-context-menu.item 
                                        action="{{ route('productos.destroy', $producto->id) }}" 
                                        method="DELETE" 
                                        icon="trash" 
                                        color="rose"
                                        confirm="¿Deseas dar de baja este producto del catálogo activo?">
                                        Dar de Baja
                                    </x-context-menu.item>
                                </x-context-menu.group>
                            </x-context-menu>
                        </td>

                        {{-- Descripción, Talle, Color y Categoría --}}
                        <td class="py-2 px-3 border-r border-slate-200/60 dark:border-slate-700/60">
                            <div class="flex items-center space-x-1.5 flex-wrap">
                                <a href="{{ route('productos.show', $producto->id) }}" 
                                   class="font-bold text-slate-900 dark:text-slate-100 uppercase hover:text-blue-600 dark:hover:text-blue-400 transition-colors truncate">
                                    {{ $producto->descripcion }}
                                </a>
                                @if($esGenerico)
                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        Genérico / Sin control de stock
                                    </span>
                                @endif
                            </div>

                            {{-- Fila de Talle, Color y Cód. Fabricante --}}
                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                @if($producto->talle)
                                    <span class="text-[10px] font-bold font-mono px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/80 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800" title="Talle">
                                        Talle: <strong>{{ $producto->talle }}</strong>
                                    </span>
                                @endif

                                @if($producto->color)
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-950/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800" title="Color">
                                        Color: <strong>{{ $producto->color }}</strong>
                                    </span>
                                @endif

                                @if($producto->codigo_proveedor)
                                    <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600" title="Código de Fábrica / Proveedor">
                                        Fab: <strong>{{ $producto->codigo_proveedor }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                @if($producto->categoria)
                                    <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                        {{ $producto->categoria->nombre }}
                                    </span>
                                @endif
                                @if($producto->subcategoria)
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400">
                                        / {{ $producto->subcategoria->nombre }}
                                    </span>
                                @endif
                                @if($producto->proveedor)
                                    <span class="text-[9px] text-slate-400 dark:text-slate-500 font-mono truncate max-w-[140px]" title="Proveedor: {{ $producto->proveedor->nombre }}">
                                        • Prov: {{ $producto->proveedor->nombre }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Precios --}}
                        <td class="py-2 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-right font-mono">
                            <p class="font-bold text-slate-900 dark:text-white text-xs">$ {{ number_format($producto->precio_venta, 2, ',', '.') }}</p>
                            @if($producto->precio_costo > 0)
                                <p class="text-[10px] text-slate-400 dark:text-slate-500">Costo: $ {{ number_format($producto->precio_costo, 2, ',', '.') }}</p>
                            @endif
                        </td>

                        {{-- Fechas de Alta y Modificación --}}
                        <td class="py-2 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-mono text-[11px]">
                            @if($producto->fecha_alta)
                                <div class="text-slate-800 dark:text-slate-200 font-semibold" title="Fecha de Alta / Ingreso">
                                    <span class="text-[8px] uppercase text-slate-400 font-sans block leading-none">Alta:</span>
                                    <span class="mt-0.5 inline-block">{{ $producto->fecha_alta->format('d/m/Y') }}</span>
                                </div>
                            @else
                                <span class="text-slate-400 italic text-[10px]">Sin alta</span>
                            @endif
                            @if($producto->fecha_modificacion && (!$producto->fecha_alta || $producto->fecha_modificacion->format('Y-m-d') !== $producto->fecha_alta->format('Y-m-d')))
                                <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-1" title="Última modificación">
                                    <span class="text-[8px] uppercase font-sans">Mod:</span> {{ $producto->fecha_modificacion->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>

                        {{-- Última Venta / Rotación --}}
                        <td class="py-2 px-3 border-r border-slate-200/60 dark:border-slate-700/60">
                            @php
                                $rotStatus = $producto->getRotacionStatus();
                            @endphp
                            <div class="flex flex-col gap-1 items-start">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold leading-tight {{ $rotStatus['class'] }}">
                                    {{ $rotStatus['label'] }}
                                </span>
                                @if($producto->fecha_ultima_venta)
                                    <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400" title="Fecha última venta/factura">
                                        {{ $producto->fecha_ultima_venta->format('d/m/Y') }}
                                    </span>
                                @elseif($producto->controla_stock)
                                    <span class="text-[9px] text-slate-400 italic">Nunca vendido</span>
                                @endif
                                @if($producto->controla_stock)
                                    <span class="text-[9px] font-mono text-slate-400 dark:text-slate-500 mt-0.5" title="Unidades totales vendidas acumuladas en kardex">
                                        Ventas: <strong class="text-slate-700 dark:text-slate-300 font-bold">{{ number_format($producto->total_ventas_unidades, 0, ',', '.') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </td>

                        {{-- Stock por Sucursal (Pills por sede o indicador de genérico) --}}
                        <td class="py-2 px-3 border-r border-slate-200/60 dark:border-slate-700/60">
                            @if($esGenerico)
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50/70 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200/70 dark:border-blue-800 text-[11px] font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    <span>Disponible en todas las sedes (Sin control físico)</span>
                                </div>
                            @else
                                @if(!empty($metricas['sucursal_id_filtrada']) && empty($metricas['ver_otras']))
                                    {{-- Modo por defecto: Solo se muestra el stock de la sucursal activa / filtrada --}}
                                    @php
                                        $sucFiltradaId = (int)$metricas['sucursal_id_filtrada'];
                                        $stkSuc = $producto->stocks->firstWhere('sucursal_id', $sucFiltradaId);
                                        $stockQty = (float)($stkSuc?->stock ?? 0);
                                        $esUsh = str_contains(strtoupper($metricas['sucursal_filtrada']->nombre ?? ''), 'USHUAIA');
                                        $badgeBg = $stockQty > 0 
                                            ? ($esUsh ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800')
                                            : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700';
                                    @endphp
                                    <div class="flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-mono border {{ $badgeBg }}"
                                              title="{{ $metricas['sucursal_filtrada']->nombre ?? 'Sucursal' }} (Existencia física en salón)">
                                            <span class="font-bold uppercase text-[9px] opacity-75">{{ $metricas['sucursal_filtrada']->codigo_sucursal ?? 'SEDE' }}:</span>
                                            <strong class="tabular-nums text-xs font-black">{{ number_format($stockQty, 0, ',', '.') }}</strong>
                                            <span class="text-[9px] font-normal text-slate-400">unid.</span>
                                        </span>
                                    </div>
                                @else
                                    {{-- Modo multi-sede o botón de demás sucursales habilitado --}}
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        @forelse($producto->stocks as $stk)
                                            @php
                                                $esFiltrada = !empty($metricas['sucursal_id_filtrada']) && (int)$stk->sucursal_id === (int)$metricas['sucursal_id_filtrada'];
                                                $esUsh = str_contains(strtoupper($stk->sucursal->nombre), 'USHUAIA');
                                                $badgeBg = (float)$stk->stock > 0 
                                                    ? ($esUsh ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800')
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border-slate-200 dark:border-slate-700';
                                            @endphp
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-mono border {{ $badgeBg }} {{ $esFiltrada ? 'ring-2 ring-blue-500 font-bold' : '' }}"
                                                  title="{{ $stk->sucursal->nombre }}{{ $esFiltrada ? ' (Sede seleccionada)' : '' }}">
                                                <span class="font-bold uppercase text-[8px] opacity-80">{{ $stk->sucursal->codigo_sucursal ?? substr($stk->sucursal->nombre, 0, 7) }}:</span>
                                                <strong class="tabular-nums">{{ (float)$stk->stock }}</strong>
                                            </span>
                                        @empty
                                            <span class="text-[10px] text-slate-400 italic">Sin inventario registrado</span>
                                        @endforelse
                                    </div>
                                @endif
                            @endif
                        </td>

                        {{-- Stock Total / Sede Filtrada --}}
                        <td class="py-2 px-3 text-right font-mono">
                            @if($esGenerico)
                                <span class="text-xs font-black text-blue-700 dark:text-blue-400">Libre</span>
                                <span class="text-[9px] text-slate-400 block font-sans">No inventariable</span>
                            @else
                                @php
                                    $sucFiltroId = $metricas['sucursal_id_filtrada'] ?? null;
                                    $stockDisplay = $sucFiltroId ? (float)($producto->stocks->firstWhere('sucursal_id', $sucFiltroId)?->stock ?? 0) : $stockTotal;
                                    $esAgotadoDisplay = $stockDisplay <= 0;
                                    $esBajoDisplay = $stockDisplay > 0 && $stockDisplay <= (float)$producto->stock_minimo;
                                @endphp
                                <span class="text-sm font-black tabular-nums {{ $esAgotadoDisplay ? 'text-rose-500 dark:text-rose-400' : ($esBajoDisplay ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                    {{ number_format($stockDisplay, 0, ',', '.') }}
                                </span>
                                <span class="text-[9px] text-slate-400 block">{{ $producto->unidad_medida }}</span>
                                @if($sucFiltroId && !empty($metricas['ver_otras']) && $stockDisplay != $stockTotal)
                                    <span class="text-[9px] text-slate-400 block font-sans" title="Total consolidado en todas las sucursales">
                                        Total: {{ number_format($stockTotal, 0, ',', '.') }}
                                    </span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500 font-medium text-xs italic bg-slate-50 dark:bg-slate-900/30">
                            No se encontraron artículos en el catálogo con los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($productos) > 0)
                <tfoot class="border-t-2 border-slate-300 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-900/80 font-mono text-xs">
                    {{-- Fila 1: Subtotales de la página actual visible --}}
                    <tr class="text-slate-700 dark:text-slate-300">
                        <td colspan="2" class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 font-sans">
                            <div class="flex items-center gap-1.5 font-bold uppercase text-[10px] text-slate-500 dark:text-slate-400">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span>Subtotal en esta Página ({{ $metricas['page_articulos'] }} artículos visibles):</span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right font-sans text-[10px] text-slate-500">
                            Val. Venta Pág:
                        </td>
                        <td colspan="2" class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 font-bold text-slate-800 dark:text-slate-200 text-xs">
                            $ {{ number_format($metricas['page_valor_venta'], 2, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right font-sans text-[10px] text-slate-500">
                            Unidades en Pág:
                        </td>
                        <td class="py-2.5 px-3 text-right font-black text-slate-900 dark:text-white tabular-nums">
                            {{ number_format($metricas['page_stock_unidades'], 0, ',', '.') }}
                        </td>
                    </tr>

                    {{-- Fila 2: Totales Generales del Filtro / Catálogo --}}
                    <tr class="bg-blue-50/50 dark:bg-blue-950/20 text-slate-800 dark:text-slate-100 font-bold border-t border-slate-200 dark:border-slate-700">
                        <td colspan="2" class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 font-sans">
                            <div class="flex items-center gap-1.5 uppercase text-[10px] text-blue-700 dark:text-blue-300">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>
                                    @if(!empty($metricas['sucursal_id_filtrada']))
                                        Total en {{ $metricas['sucursal_filtrada']->nombre ?? 'Sede' }} ({{ number_format($metricas['total_articulos'], 0, ',', '.') }} artículos):
                                    @else
                                        {{ !empty($metricas['is_filtered']) ? 'Total General Filtrado' : 'Total Catálogo Completo' }} ({{ number_format($metricas['total_articulos'], 0, ',', '.') }} artículos):
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right font-sans text-[10px] text-blue-600 dark:text-blue-400">
                            Val. Venta Total:
                        </td>
                        <td colspan="2" class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 font-black text-blue-900 dark:text-blue-300 text-xs">
                            $ {{ number_format($metricas['valor_stock_venta'], 2, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 border-r border-slate-200 dark:border-slate-700/60 text-right font-sans text-[10px] text-emerald-600 dark:text-emerald-400">
                            {{ !empty($metricas['sucursal_id_filtrada']) ? 'Stock Sede:' : 'Stock Total:' }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-black text-emerald-700 dark:text-emerald-400 tabular-nums">
                            {{ number_format($metricas['total_stock_unidades'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Paginación --}}
    <div class="px-1 text-xs">
        {{ $productos->appends(request()->query())->links() }}
    </div>

    {{-- MODAL FLOTANTE: REIMPRESIÓN DE ETIQUETAS (REPOSICIÓN - SIN AFECTAR STOCK) --}}
    <div x-show="modalEtiquetas" style="display: none;" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
         @keydown.escape.window="modalEtiquetas = false">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 dark:border-slate-700"
             @click.outside="modalEtiquetas = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4">
                <div>
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <span>Reimpresión de Etiquetas</span>
                    </h3>
                    <p class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-0.5">
                        Reposición por rotura, despegue o falla de impresión
                    </p>
                </div>
                <button @click="modalEtiquetas = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 text-lg">&times;</button>
            </div>

            <div class="space-y-4">
                {{-- Advertencia explícita de NO modificación de stock --}}
                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200">
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="text-[11px] leading-relaxed">
                            <strong class="font-bold block text-amber-800 dark:text-amber-300">Esta acción NO modifica el stock ni registra movimientos.</strong>
                            Utilice esta reimpresión <u>únicamente</u> para reponer etiquetas dañadas en salón o fallas de la impresora térmica. <strong>No la use para mercadería nueva</strong> (para eso ingrese stock por el módulo de ajuste o compras).
                        </div>
                    </div>
                </div>

                {{-- Ficha resumida del artículo --}}
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Artículo a Reimprimir</p>
                    <p class="text-xs font-bold text-slate-900 dark:text-white mt-0.5" x-text="etiquetaDesc"></p>
                    <div class="flex items-center gap-2 mt-1.5 font-mono text-[11px] text-blue-600 dark:text-blue-400 font-bold">
                        <span>Código: <span x-text="etiquetaCodigo"></span></span>
                        <span>• Formato: 50mm × 25mm</span>
                    </div>
                </div>

                {{-- Motivo de la reimpresión --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Motivo de la Reimpresión <span class="text-rose-500">*</span>
                    </label>
                    <select x-model="etiquetaMotivo" class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white font-medium">
                        <option value="Etiqueta rota / dañada en salón">Etiqueta rota / dañada en salón</option>
                        <option value="Falla de impresora térmica / atasco">Falla de impresora térmica / atasco de papel</option>
                        <option value="Etiqueta despegada / perdida">Etiqueta despegada / perdida</option>
                        <option value="Código de barras ilegible">Código de barras ilegible o manchado</option>
                        <option value="Actualización de precio de exhibición">Actualización de precio de exhibición</option>
                    </select>
                </div>

                {{-- Cantidad de reposición --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Cantidad de Etiquetas de Reposición
                    </label>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="etiquetaCantidad = Math.max(1, etiquetaCantidad - 1)" 
                                class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-200">-</button>
                        <input type="number" min="1" max="50" x-model.number="etiquetaCantidad" 
                               class="w-full text-center text-sm py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold">
                        <button type="button" @click="etiquetaCantidad = Math.min(50, etiquetaCantidad + 1)" 
                                class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-200">+</button>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Reimprima únicamente los stickers necesarios para reponer.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="modalEtiquetas = false" class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                        Cancelar
                    </button>
                    <button type="button" @click="imprimir()" class="px-4 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        <span>Reimprimir <span x-text="etiquetaCantidad"></span> <span x-text="etiquetaCantidad == 1 ? 'Etiqueta' : 'Etiquetas'"></span> (Sin alterar stock)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
