<x-app-layout>
    <div class="max-w-5xl mx-auto py-2 space-y-6" x-data="{ modalAjuste: false, modalTraslado: false, modalEtiquetas: false, tipoAjuste: 'alta', etiquetaCantidad: 1, etiquetaMotivo: 'Etiqueta rota / dañada en salón', sucursalSeleccionada: {{ config('app.sucursal_activa.id') ?? 1 }} }">
        
        {{-- Encabezado del Producto --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        {{ $producto->descripcion }}
                    </h1>
                    <span class="text-xs font-bold font-mono px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-700">
                        {{ $producto->codigo }}
                    </span>
                    @if($producto->talle)
                        <span class="text-xs font-bold font-mono px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-700">
                            Talle: {{ $producto->talle }}
                        </span>
                    @endif
                    @if($producto->color)
                        <span class="text-xs font-bold px-2 py-0.5 rounded bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-300 border border-rose-200 dark:border-rose-700">
                            Color: {{ $producto->color }}
                        </span>
                    @endif
                    @if(!$producto->controla_stock)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-100 dark:bg-blue-950 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            Genérico / Sin control de stock
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1 flex-wrap">
                    @if($producto->codigo_barra)
                        <span class="font-mono flex items-center gap-1 text-slate-700 dark:text-slate-300">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            CODE128: <strong>{{ $producto->codigo_barra }}</strong>
                        </span>
                    @endif
                    @if($producto->codigo_proveedor)
                        <span class="font-mono flex items-center gap-1 text-slate-700 dark:text-slate-300">
                            • Cód. Fábrica: <strong>{{ $producto->codigo_proveedor }}</strong>
                        </span>
                    @endif
                    @if($producto->categoria)
                        <span>• Categoría: <strong class="text-slate-700 dark:text-slate-200">{{ $producto->categoria->nombre }}</strong></span>
                    @endif
                    @if($producto->subcategoria)
                        <span>/ <strong class="text-slate-700 dark:text-slate-200">{{ $producto->subcategoria->nombre }}</strong></span>
                    @endif
                    @if($producto->proveedor)
                        <span>• Proveedor: <strong class="text-slate-700 dark:text-slate-200">{{ $producto->proveedor->nombre }}</strong></span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                @if($producto->controla_stock)
                    <button @click="modalAjuste = true" type="button" 
                            class="px-3 py-1.5 text-xs font-bold text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-300 dark:border-emerald-700 rounded-lg shadow-2xs transition-all flex items-center gap-1">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m8-8H4" />
                        </svg>
                        <span>Ajustar Stock</span>
                    </button>

                    <button @click="modalTraslado = true" type="button" 
                            class="px-3 py-1.5 text-xs font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-300 dark:border-amber-700 rounded-lg shadow-2xs transition-all flex items-center gap-1">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span>Trasladar</span>
                    </button>
                @endif

                <button @click="modalEtiquetas = true" type="button" 
                        title="Reimprimir etiquetas (Reposición por daño o falla - No modifica stock)"
                        class="px-3 py-1.5 text-xs font-bold text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-300 dark:border-amber-700 rounded-lg shadow-2xs transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Reimprimir Etiqueta</span>
                </button>

                <a href="{{ route('productos.edit', $producto->id) }}" 
                   class="px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs transition-all flex items-center gap-1">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Editar</span>
                </a>

                <a href="{{ route('productos.index') }}" 
                   class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg transition-all">
                    &larr; Catálogo
                </a>
            </div>
        </div>

        {{-- Bloque de Precios y Métricas Rápidas --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white dark:bg-slate-800/90 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Precio de Venta</span>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 font-mono">
                    $ {{ number_format($producto->precio_venta, 2, ',', '.') }}
                </p>
                <span class="text-[10px] text-slate-400">Público / Mostrador</span>
            </div>

            <div class="bg-white dark:bg-slate-800/90 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Precio de Costo</span>
                <p class="text-2xl font-black text-slate-800 dark:text-slate-200 mt-1 font-mono">
                    $ {{ number_format($producto->precio_costo, 2, ',', '.') }}
                </p>
                <span class="text-[10px] text-slate-400">Reposición Proveedor</span>
            </div>

            <div class="bg-white dark:bg-slate-800/90 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                @php
                    $margen = ($producto->precio_costo > 0) 
                        ? (($producto->precio_venta - $producto->precio_costo) / $producto->precio_costo) * 100 
                        : 0;
                @endphp
                <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Margen Bruto</span>
                <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 font-mono">
                    {{ number_format($margen, 1, ',', '.') }} %
                </p>
                <span class="text-[10px] text-slate-400">Utilidad estimada</span>
            </div>

            <div class="bg-white dark:bg-slate-800/90 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                <span class="text-[10px] font-black uppercase text-slate-400 dark:text-slate-500 tracking-wider">Stock Consolidado</span>
                @if($producto->controla_stock)
                    <p class="text-2xl font-black text-slate-900 dark:text-white mt-1 font-mono">
                        {{ number_format($producto->getStockTotal(), 0, ',', '.') }}
                        <span class="text-xs font-normal text-slate-400 uppercase">{{ $producto->unidad_medida }}</span>
                    </p>
                    <span class="text-[10px] text-slate-400">En todas las sedes</span>
                @else
                    <p class="text-2xl font-black text-blue-600 dark:text-blue-400 mt-1 font-mono">
                        Libre
                    </p>
                    <span class="text-[10px] text-blue-500 font-medium">No inventariable</span>
                @endif
            </div>
        </div>

        {{-- AUDITORÍA DE FECHAS HISTÓRICAS Y ROTACIÓN --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs p-4">
            <h2 class="text-xs font-black uppercase text-slate-700 dark:text-slate-300 tracking-wider flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-2.5 mb-3">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Ciclo de Vida y Rotación en Catálogo</span>
                </span>
                @php
                    $rotStatus = $producto->getRotacionStatus();
                @endphp
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold {{ $rotStatus['class'] }}">
                    Estado: {{ $rotStatus['label'] }}
                </span>
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wide">Alta en Sistema</span>
                    <p class="font-mono font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ $producto->fecha_alta ? $producto->fecha_alta->format('d/m/Y H:i') : 'Sin registro' }}
                    </p>
                    <span class="text-[10px] text-slate-400">Ingreso original</span>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-700/60">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wide">Última Venta</span>
                        @if($producto->controla_stock)
                            <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 font-mono" title="Total unidades vendidas históricas">{{ number_format($producto->total_ventas_unidades, 0, ',', '.') }} unid.</span>
                        @endif
                    </div>
                    <p class="font-mono font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ $producto->fecha_ultima_venta ? $producto->fecha_ultima_venta->format('d/m/Y H:i') : 'Sin ventas' }}
                    </p>
                    <span class="text-[10px] text-slate-400">
                        @if($producto->fecha_ultima_venta)
                            Hace {{ $producto->diasSinVenta() }} días ({{ $producto->total_ventas_operaciones }} ventas registradas)
                        @else
                            Sin salidas en facturación
                        @endif
                    </span>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wide">Última Compra</span>
                    <p class="font-mono font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ $producto->fecha_ultima_compra ? $producto->fecha_ultima_compra->format('d/m/Y H:i') : 'Sin registro' }}
                    </p>
                    <span class="text-[10px] text-slate-400">Reposición proveedor</span>
                </div>

                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wide">Última Modificación</span>
                    <p class="font-mono font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                        {{ $producto->fecha_modificacion ? $producto->fecha_modificacion->format('d/m/Y H:i') : ($producto->updated_at ? $producto->updated_at->format('d/m/Y H:i') : '-') }}
                    </p>
                    <span class="text-[10px] text-slate-400">Edición o ajuste</span>
                </div>
            </div>
        </div>

        {{-- MATRIZ DE STOCK POR SUCURSAL --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs p-5">
            <h2 class="text-sm font-black uppercase text-slate-800 dark:text-white tracking-wider flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 mb-4">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Existencias Físicas por Sucursal</span>
                </span>
                @if($producto->controla_stock)
                    <span class="text-xs font-normal text-slate-400 lowercase">
                        stock mínimo configurado: {{ (int)$producto->stock_minimo }} unid.
                    </span>
                @else
                    <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">
                        Artículo Genérico / Varios
                    </span>
                @endif
            </h2>

            @if(!$producto->controla_stock)
                <div class="p-6 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 text-center space-y-2">
                    <div class="w-12 h-12 mx-auto rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-blue-600 dark:text-blue-300">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-tight">Artículo sin control de existencias físicas</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-300 max-w-xl mx-auto leading-relaxed">
                        Este producto está configurado como <strong>concepto genérico / venta libre</strong>. No mantiene stock físico por sucursal, no altera las unidades en inventario ni la valuación de stock de la empresa, y puede emitirse en ventas y facturas desde cualquiera de las 4 sedes de forma directa.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($stocksPorSucursal as $item)
                    @php
                        $suc = $item['sucursal'];
                        $stock = $item['stock'];
                        $min = $item['stock_minimo'];
                        $esCritico = $stock > 0 && $stock <= $min;
                        $esAgotado = $stock <= 0;
                        
                        $cardBg = $esAgotado 
                            ? 'bg-slate-50 dark:bg-slate-900/40 border-slate-200 dark:border-slate-700' 
                            : ($esCritico 
                                ? 'bg-amber-50/50 dark:bg-amber-950/30 border-amber-300 dark:border-amber-700' 
                                : 'bg-emerald-50/40 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-700');
                    @endphp

                    <div class="p-4 rounded-xl border {{ $cardBg }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-black uppercase text-slate-800 dark:text-white truncate" title="{{ $suc->nombre }}">
                                    {{ $suc->nombre }}
                                </span>
                                <span class="text-[9px] font-mono font-bold px-1.5 py-0.2 rounded bg-white/80 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                    {{ $suc->codigo_sucursal ?? '-' }}
                                </span>
                            </div>
                            <span class="text-[10px] text-slate-400 block mb-2">{{ $suc->direccion ?? 'Sin dirección' }}</span>

                            <div class="flex items-baseline gap-1 my-2">
                                <span class="text-3xl font-black font-mono tabular-nums {{ $esAgotado ? 'text-slate-400 dark:text-slate-500' : ($esCritico ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-300') }}">
                                    {{ (int)$stock }}
                                </span>
                                <span class="text-xs font-bold text-slate-400 uppercase">{{ $producto->unidad_medida }}</span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between text-[10px]">
                            @if($esAgotado)
                                <span class="font-bold text-slate-400 uppercase">Sin Stock</span>
                            @elseif($esCritico)
                                <span class="font-bold text-amber-600 dark:text-amber-400 uppercase flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Stock Crítico
                                </span>
                            @else
                                <span class="font-bold text-emerald-700 dark:text-emerald-400 uppercase flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Abastecido
                                </span>
                            @endif

                            <button @click="modalAjuste = true; sucursalSeleccionada = {{ $suc->id }}" 
                                    class="text-blue-600 dark:text-blue-400 hover:underline font-bold">
                                Ajustar &rarr;
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- HISTORIAL DE MOVIMIENTOS RECIENTES --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs p-5">
            <h2 class="text-sm font-black uppercase text-slate-800 dark:text-white tracking-wider flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 mb-4">
                <span>Últimos Movimientos de Stock</span>
                <a href="{{ route('stock.movimientos', ['producto_id' => $producto->id]) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                    Ver Auditoría Completa &rarr;
                </a>
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sans border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <th class="py-2 px-3">Fecha / Hora</th>
                            <th class="py-2 px-3">Sucursal</th>
                            <th class="py-2 px-3">Tipo Operación</th>
                            <th class="py-2 px-3 text-right">Cantidad</th>
                            <th class="py-2 px-3 text-right">Stock Resultante</th>
                            <th class="py-2 px-3">Comprobante / Motivo</th>
                            <th class="py-2 px-3">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($movimientos as $mov)
                            @php
                                $badgeClass = match($mov->tipo) {
                                    'alta' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
                                    'baja' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
                                    'traslado_salida' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                                    'traslado_entrada' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                                    'venta' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                <td class="py-2 px-3 font-mono text-slate-500 dark:text-slate-400">
                                    {{ $mov->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-2 px-3 font-bold text-slate-700 dark:text-slate-300">
                                    {{ $mov->sucursal->nombre ?? '-' }}
                                </td>
                                <td class="py-2 px-3">
                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-black uppercase {{ $badgeClass }}">
                                        {{ str_replace('_', ' ', $mov->tipo) }}
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-bold tabular-nums {{ (float)$mov->cantidad < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ (float)$mov->cantidad > 0 ? '+' : '' }}{{ (float)$mov->cantidad }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-slate-700 dark:text-slate-300 tabular-nums">
                                    {{ (float)$mov->stock_nuevo }}
                                </td>
                                <td class="py-2 px-3 text-slate-600 dark:text-slate-400">
                                    <span class="font-bold font-mono text-slate-800 dark:text-slate-200">{{ $mov->comprobante ?? '-' }}</span>
                                    @if($mov->motivo)
                                        <span class="text-[10px] text-slate-400 block truncate max-w-xs">{{ $mov->motivo }}</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-slate-500 font-mono text-[11px]">
                                    {{ $mov->usuario->username ?? $mov->usuario->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-400 italic">
                                    No hay movimientos de stock registrados todavía para este artículo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MODAL FLOTANTE: AJUSTE DE STOCK --}}
        <div x-show="modalAjuste" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             @keydown.escape.window="modalAjuste = false">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 dark:border-slate-700"
                 @click.outside="modalAjuste = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Ajustar Stock Manual</span>
                    </h3>
                    <button @click="modalAjuste = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form action="{{ route('productos.ajuste', $producto->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Sucursal a Impactar</label>
                        <select name="sucursal_id" x-model="sucursalSeleccionada" required class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white font-medium">
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}">{{ $suc->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Tipo de Movimiento</label>
                            <select name="tipo" x-model="tipoAjuste" required class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white font-medium">
                                <option value="alta">Ingreso / Alta (+)</option>
                                <option value="baja">Egreso / Baja (-)</option>
                                <option value="correccion">Ajuste de Conteo</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cantidad (Unid.)</label>
                            <input type="number" step="1" min="0.01" name="cantidad" required placeholder="Ej: 5" 
                                   class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold text-right">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Motivo del Ajuste <span class="text-rose-500">*</span></label>
                        <input type="text" name="motivo" required placeholder="Ej: Recuento físico de inventario, rotura, etc." 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Comprobante de Referencia</label>
                        <input type="text" name="comprobante" placeholder="Ej: AJUSTE-OCT-2026, REM-001" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono">
                    </div>

                    {{-- Checkbox y advertencia estricta para imprimir etiquetas tras ingreso de mercadería --}}
                    <div x-show="tipoAjuste === 'alta'" class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 space-y-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="imprimir_etiquetas" value="1" checked 
                                   class="rounded border-slate-300 text-blue-600 shadow-2xs focus:ring-blue-500">
                            <span class="text-xs font-bold text-blue-900 dark:text-blue-200">
                                Imprimir etiquetas de los artículos ingresados (50×25mm)
                            </span>
                        </label>
                        <div class="flex items-start gap-1.5 text-[10px] text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 p-2 rounded-lg border border-amber-300 dark:border-amber-800/60 leading-relaxed">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <strong>¡Atención al control de inventario!</strong>
                                No imprima etiquetas de más. La cantidad de etiquetas debe coincidir estrictamente con las prendas físicas recibidas para evitar descuadres entre las existencias en góndola y el stock del sistema.
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="modalAjuste = false" class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-700">Cancelar</button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm">Aplicar Ajuste</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL FLOTANTE: TRASLADO INTER-SUCURSAL --}}
        <div x-show="modalTraslado" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             @keydown.escape.window="modalTraslado = false">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-5 border border-slate-200 dark:border-slate-700"
                 @click.outside="modalTraslado = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span>Traslado Inter-Sucursales</span>
                    </h3>
                    <button @click="modalTraslado = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form action="{{ route('productos.trasladar', $producto->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Sucursal Origen (Emisora)</label>
                        <select name="sucursal_origen_id" required class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white font-medium">
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}" {{ (int)config('app.sucursal_activa.id') === $suc->id ? 'selected' : '' }}>
                                    {{ $suc->nombre }} (Stock: {{ $producto->getStockSucursal($suc->id) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Sucursal Destino (Receptora)</label>
                        <select name="sucursal_destino_id" required class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white font-medium">
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}" {{ (int)config('app.sucursal_activa.id') !== $suc->id ? 'selected' : '' }}>
                                    {{ $suc->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Cantidad a Enviar</label>
                        <input type="number" step="1" min="1" name="cantidad" required placeholder="Ej: 5" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold text-right">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nro. de Remito o Guía</label>
                        <input type="text" name="comprobante" placeholder="Ej: REM-0001-00045" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="modalTraslado = false" class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-700">Cancelar</button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm">Confirmar Traslado</button>
                    </div>
                </form>
            </div>
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
                                Utilice esta reimpresión <u>únicamente</u> para reponer etiquetas dañadas en salón o por fallas de la impresora térmica. <strong>No la use para mercadería nueva</strong> (para eso ingrese stock por el módulo de ajuste o compras).
                            </div>
                        </div>
                    </div>

                    {{-- Ficha resumida del artículo --}}
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                        <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Artículo a Reimprimir</p>
                        <p class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">{{ $producto->descripcion }}</p>
                        <div class="flex items-center gap-2 mt-1.5 font-mono text-[11px] text-blue-600 dark:text-blue-400 font-bold">
                            <span>Código: {{ $producto->codigo }}</span>
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
                        <button type="button" 
                                @click="window.open('{{ route('productos.etiqueta', $producto->id) }}?cantidad=' + etiquetaCantidad + '&motivo=' + encodeURIComponent(etiquetaMotivo) + '&autoprint=1', '_blank'); modalEtiquetas = false;" 
                                class="px-4 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            <span>Reimprimir <span x-text="etiquetaCantidad"></span> <span x-text="etiquetaCantidad == 1 ? 'Etiqueta' : 'Etiquetas'"></span> (Sin alterar stock)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
