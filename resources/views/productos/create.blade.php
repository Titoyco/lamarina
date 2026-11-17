<x-app-layout>
    @php
        $proveedoresMap = $proveedores->mapWithKeys(function ($p) {
            $leg = $p->legajo_formateado ?? str_pad((string)($p->legajo ?? 0), 3, '0', STR_PAD_LEFT);
            return [(string)$p->id => ['nombre' => $p->nombre, 'legajo' => $leg]];
        });
        $ultimoProvId = old('proveedor_id', session('ultimo_proveedor_id', ''));
    @endphp

    <div class="max-w-5xl mx-auto py-2" x-data="ingresoMercaderia()">

        {{-- Cabecera Principal --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-5 gap-3">
            <div>
                <h1 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </span>
                    <span>Ingreso de Mercadería y Alta de Variantes (CODE128)</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Carga ultra-rápida: busque una prenda base, modifique talle y color para crear variantes automáticamente o sumar stock si ya existen.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('remitos.create') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-black text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 border border-indigo-200 dark:border-indigo-800 px-3.5 py-1.5 rounded-lg shadow-2xs transition-all">
                    <span>📄 Carga por Factura / Remito (Múltiples Ítems)</span> &rarr;
                </a>
                <a href="{{ route('productos.index') }}" 
                   class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-2xs transition-all">
                    &larr; Catálogo de Productos
                </a>
            </div>
        </div>

        {{-- Mensajes de Notificación Flash --}}
        @if(session('success'))
            <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold flex items-center gap-2 shadow-2xs">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mb-4 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-bold space-y-1 shadow-2xs">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Por favor corrija los siguientes inconvenientes:</span>
                </div>
                <ul class="list-disc list-inside font-normal pl-2">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-6">

            {{-- ============================================================== --}}
            {{-- PASO 1: SELECCIÓN O ALTA DE PROVEEDOR (PREFIJO 3 DÍGITOS)       --}}
            {{-- ============================================================== --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-2xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-2.5 gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                            1
                        </span>
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-100">
                                Proveedor / Fabricante de la Mercadería
                            </h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Define los 3 primeros dígitos del Código de Barra y Código Interno CODE128.
                            </p>
                        </div>
                    </div>

                    {{-- Indicador dinámico del prefijo generado --}}
                    <div class="flex items-center gap-2" x-show="proveedorId">
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">Prefijo de Código:</span>
                        <span class="font-mono font-black text-xs bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 px-2 py-0.5 rounded border border-blue-200 dark:border-blue-700" 
                              x-text="'[' + legajoProveedor + ']'">
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                    <div class="sm:col-span-9">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Seleccionar Proveedor <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="proveedorId" 
                                class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-bold focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <option value="">-- Seleccione un proveedor para comenzar --</option>
                            <template x-for="(prov, id) in proveedoresMap" :key="id">
                                <option :value="id" :selected="proveedorId == id" 
                                        x-text="'[' + prov.legajo + '] ' + prov.nombre">
                                </option>
                            </template>
                        </select>
                    </div>

                    <div class="sm:col-span-3">
                        <button type="button" @click="modalProveedorAbierto = true"
                                class="w-full py-2 px-3 text-xs font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/50 border border-blue-300 dark:border-blue-800 rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-2xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Nuevo Proveedor</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- ============================================================== --}}
            {{-- PASO 2: BÚSQUEDA RÁPIDA DE ARTÍCULO BASE DEL PROVEEDOR          --}}
            {{-- ============================================================== --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-2xs space-y-4"
                 :class="{ 'opacity-50 pointer-events-none': !proveedorId }">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-slate-700 text-white font-black text-xs flex items-center justify-center shrink-0">
                            2
                        </span>
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-100">
                                Buscar Prenda Base del Proveedor (Opcional - Acelera la Carga)
                            </h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Busque por Código de Fábrica, Descripción, Talle o Color para precargar todos los datos de una prenda existente.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="relative">
                        <input type="text" 
                               x-model="busquedaQuery" 
                               @input.debounce.300ms="buscarArticulosProveedor()"
                               placeholder="🔍 Buscar por Código de Fábrica (ej: 7040), Descripción (ej: REMERA JERSEY), Talle o Color..."
                               class="w-full text-xs py-2.5 pl-9 pr-8 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-bold focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <button type="button" x-show="busquedaQuery.length > 0" @click="busquedaQuery = ''; productosEncontrados = [];"
                                class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Indicador de carga --}}
                    <div x-show="buscando" class="py-2 text-xs text-blue-600 flex items-center gap-1.5">
                        <svg class="animate-spin h-3.5 w-3.5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Buscando artículos en catálogo...</span>
                    </div>

                    {{-- Resultados de la búsqueda --}}
                    <div x-show="productosEncontrados.length > 0" class="mt-3 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shadow-sm divide-y divide-slate-100 dark:divide-slate-700/60 max-h-72 overflow-y-auto">
                        <div class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 text-[10px] font-black uppercase text-slate-500 tracking-wider">
                            Artículos encontrados (<span x-text="productosEncontrados.length"></span>) - Seleccione para precargar datos y editar talle/color:
                        </div>
                        <template x-for="prod in productosEncontrados" :key="prod.id">
                            <div class="p-3 bg-white dark:bg-slate-800 hover:bg-blue-50/60 dark:hover:bg-slate-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-2 transition-colors">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-xs text-slate-900 dark:text-white" x-text="prod.descripcion"></span>
                                        <span class="font-mono text-[10px] bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-300 font-bold" 
                                              x-text="'Cód: ' + prod.codigo"></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-500 dark:text-slate-400 flex-wrap">
                                        <template x-if="prod.codigo_proveedor">
                                            <span class="font-mono font-bold text-blue-600 dark:text-blue-400" x-text="'Fab: ' + prod.codigo_proveedor"></span>
                                        </template>
                                        <template x-if="prod.talle">
                                            <span class="font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 px-1.5 py-0.5 rounded" x-text="'Talle: ' + prod.talle"></span>
                                        </template>
                                        <template x-if="prod.color">
                                            <span class="font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-800 dark:text-purple-300 px-1.5 py-0.5 rounded" x-text="'Color: ' + prod.color"></span>
                                        </template>
                                        <span>•</span>
                                        <span>Stock Sucursal: <strong class="text-slate-700 dark:text-slate-200" x-text="prod.stock_sucursal"></strong></span>
                                        <span>•</span>
                                        <span>Precio: <strong class="text-emerald-600 dark:text-emerald-400" x-text="'$' + prod.precio_venta"></strong></span>
                                    </div>
                                </div>

                                <button type="button" @click="cargarArticuloBase(prod)"
                                        class="shrink-0 px-3 py-1.5 text-xs font-black text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-2xs transition-all flex items-center justify-center gap-1">
                                    <span>Cargar este Artículo &darr;</span>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div x-show="busquedaQuery.length > 1 && productosEncontrados.length === 0 && !buscando" class="mt-2 text-xs text-slate-500 dark:text-slate-400 italic">
                        No se encontró ningún artículo de este proveedor con ese filtro. Puede completar los campos en el Paso 3 para crearlo de cero.
                    </div>
                </div>
            </div>

            {{-- ============================================================== --}}
            {{-- PASO 3: FORMULARIO DINÁMICO DE DETALLE E INGRESO DE STOCK       --}}
            {{-- ============================================================== --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-2xs space-y-5"
                 :class="{ 'opacity-50 pointer-events-none': !proveedorId }">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-2.5 gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                            3
                        </span>
                        <div>
                            <h2 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-100">
                                Detalle de la Prenda, Talle, Color y Unidades
                            </h2>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Al modificar el talle o el color, se recalcula el código CODE128. Si la variante no existe se crea como nueva; si ya existe se sumarán las unidades.
                            </p>
                        </div>
                    </div>

                    <div x-show="articuloBase" class="flex items-center gap-2">
                        <button type="button" @click="limpiarFormularioArticulo()"
                                class="text-[11px] font-bold text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200 px-2 py-1 rounded border border-rose-200 dark:border-rose-800 bg-rose-50/50 dark:bg-rose-950/30 transition-all flex items-center gap-1">
                            <span>✕ Limpiar datos base</span>
                        </button>
                    </div>
                </div>

                {{-- Banner indicativo si está basado en una prenda existente --}}
                <div x-show="articuloBase" 
                     class="p-3 rounded-xl border text-xs flex flex-col sm:flex-row sm:items-center justify-between gap-2 transition-colors"
                     :class="haModificadoVariante ? 'bg-amber-50/70 dark:bg-amber-950/30 border-amber-300 dark:border-amber-800 text-amber-900 dark:text-amber-200' : 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200'">
                    <div class="flex items-center gap-2">
                        <span class="text-base" x-text="haModificadoVariante ? '✏️' : '📋'"></span>
                        <div>
                            <span>Prenda base cargada: </span>
                            <strong x-text="articuloBase ? articuloBase.descripcion : ''"></strong>
                            <span class="text-[11px] opacity-80 ml-1" 
                                  x-text="'(Cód: ' + (articuloBase ? articuloBase.codigo : '') + ' • Talle: ' + (valoresBaseOriginales ? valoresBaseOriginales.talle : '') + ' • Color: ' + (valoresBaseOriginales ? valoresBaseOriginales.color : '') + ' • Fab: ' + (valoresBaseOriginales ? valoresBaseOriginales.codigoProveedor : '') + ')'"></span>
                        </div>
                    </div>
                    <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-md shadow-2xs"
                          :class="haModificadoVariante ? 'bg-amber-200 text-amber-900 dark:bg-amber-900 dark:text-amber-200' : 'bg-emerald-200 text-emerald-900 dark:bg-emerald-900 dark:text-emerald-200'"
                          x-text="haModificadoVariante ? '✨ Modificando hacia nueva variante' : '🟢 Variante original seleccionada'">
                    </span>
                </div>

                {{-- VISOR INTERACTIVO EN TIEMPO REAL DEL CÓDIGO CODE128 ESTRUCTURADO --}}
                <div class="p-4 rounded-xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/40 dark:bg-blue-950/20 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black uppercase tracking-wider text-blue-800 dark:text-blue-300">
                            Estructura del Código CODE128 (Prov + Talle + Color + Fábrica)
                        </span>
                        <div class="flex items-center gap-1.5">
                            <template x-if="articuloBase && !haModificadoVariante">
                                <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Prenda Seleccionada
                                </span>
                            </template>
                            <template x-if="haModificadoVariante">
                                <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    Variante a Verificar
                                </span>
                            </template>
                            <template x-if="!articuloBase">
                                <span class="text-[10px] font-black text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    Nuevo Código
                                </span>
                            </template>
                        </div>
                    </div>

                    {{-- Segmentos visuales CODE128 --}}
                    <div class="flex items-center gap-1.5 font-mono text-sm font-black flex-wrap">
                        <div class="px-2.5 py-1 bg-white dark:bg-slate-900 rounded-lg border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-300 shadow-2xs flex flex-col items-center">
                            <span class="text-[8px] uppercase tracking-wider text-slate-400 font-sans">1. Prov (3c)</span>
                            <span x-text="legajoProveedor"></span>
                        </div>
                        <span class="text-slate-400 font-bold">+</span>
                        <div class="px-2.5 py-1 bg-white dark:bg-slate-900 rounded-lg border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 shadow-2xs flex flex-col items-center">
                            <span class="text-[8px] uppercase tracking-wider text-slate-400 font-sans">2. Talle (2c)</span>
                            <span x-text="talle2Char"></span>
                        </div>
                        <span class="text-slate-400 font-bold">+</span>
                        <div class="px-2.5 py-1 bg-white dark:bg-slate-900 rounded-lg border border-purple-300 dark:border-purple-700 text-purple-700 dark:text-purple-300 shadow-2xs flex flex-col items-center">
                            <span class="text-[8px] uppercase tracking-wider text-slate-400 font-sans">3. Color (2c)</span>
                            <span x-text="color2Char"></span>
                        </div>
                        <span class="text-slate-400 font-bold">+</span>
                        <div class="px-2.5 py-1 bg-white dark:bg-slate-900 rounded-lg border border-emerald-300 dark:border-emerald-700 text-emerald-700 dark:text-emerald-300 shadow-2xs flex flex-col items-center">
                            <span class="text-[8px] uppercase tracking-wider text-slate-400 font-sans">4. Fábrica</span>
                            <span x-text="codigoArticuloLimpio || '••••'"></span>
                        </div>
                        <span class="text-slate-400 font-bold">=</span>
                        <div class="px-3 py-1 bg-slate-900 text-white dark:bg-white dark:text-slate-900 rounded-lg font-black tracking-wider shadow-2xs text-base">
                            <span x-text="codigoGenerado || '---'"></span>
                        </div>
                    </div>

                    {{-- Estado discreto sin disparar peticiones de red mientras se escribe --}}
                    <div class="flex items-center justify-between flex-wrap gap-2 pt-1 text-xs">
                        <template x-if="articuloBase && !haModificadoVariante">
                            <span class="inline-flex items-center gap-1.5 font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 px-3 py-1 rounded-xl">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Prenda original seleccionada (se sumará stock al presionar Continuar)</span>
                            </span>
                        </template>
                        <template x-if="haModificadoVariante">
                            <span class="inline-flex items-center gap-1.5 font-bold text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-800 px-3 py-1 rounded-xl">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>Variante modificada (se verificará disponibilidad al presionar Continuar)</span>
                            </span>
                        </template>
                        <template x-if="!articuloBase">
                            <span class="inline-flex items-center gap-1.5 font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/60 border border-blue-300 dark:border-blue-800 px-3 py-1 rounded-xl">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span>Nuevo artículo (se verificará disponibilidad al presionar Continuar)</span>
                            </span>
                        </template>
                    </div>
                </div>

                {{-- FORMULARIO DE INGRESO ÁGIL / ALTA DE VARIANTES --}}
                <form id="form_ingreso_mercaderia" action="{{ route('productos.ingreso-mercaderia') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="proveedor_id" :value="proveedorId">
                    <input type="hidden" name="codigo" :value="codigoGenerado">

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Código del Proveedor / Fábrica --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Código de Fábrica / Proveedor <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="codigo_proveedor" required 
                                   x-model="codigoProveedor"
                                   placeholder="Ej: 7040 / ART-102"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-mono font-bold focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>

                        {{-- Talle (1 al 70 y XS a 8XL) --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Talle de la Prenda <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[9px] font-mono text-amber-600 dark:text-amber-400 font-bold" 
                                      x-text="'2C: ' + talle2Char"></span>
                            </div>
                            <input type="text" id="input_talle" name="talle" list="lista_talles_create" required
                                   x-model="talle"
                                   placeholder="Ej: 42 o M o 2XL"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-mono font-bold focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <datalist id="lista_talles_create">
                                @foreach($tallesSugeridos as $t)
                                    <option value="{{ $t }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        {{-- Color --}}
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Color de la Prenda <span class="text-rose-500">*</span>
                                </label>
                                <span class="text-[9px] font-mono text-purple-600 dark:text-purple-400 font-bold" 
                                      x-text="'2C: ' + color2Char"></span>
                            </div>
                            <input type="text" id="input_color" name="color" list="lista_colores_create" required
                                   x-model="color"
                                   placeholder="Ej: ROJO, VERDE, NEGRO..."
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-bold focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                            <datalist id="lista_colores_create">
                                <option value="NEGRO"></option>
                                <option value="BLANCO"></option>
                                <option value="AZUL"></option>
                                <option value="AZUL MARINO"></option>
                                <option value="CELESTE"></option>
                                <option value="ROJO"></option>
                                <option value="BORDO"></option>
                                <option value="VERDE"></option>
                                <option value="VERDE MILITAR"></option>
                                <option value="AMARILLO"></option>
                                <option value="GRIS"></option>
                                <option value="GRIS MELANGE"></option>
                                <option value="ROSA"></option>
                                <option value="BEIGE"></option>
                                <option value="MARRON"></option>
                                <option value="VIOLETA"></option>
                                <option value="LILA"></option>
                                <option value="NARANJA"></option>
                                <option value="FUCSIA"></option>
                                <option value="TURQUESA"></option>
                                <option value="ESTAMPADO"></option>
                                <option value="SURTIDO"></option>
                                <option value="VARIOS"></option>
                                <option value="MULTICOLOR"></option>
                            </datalist>
                        </div>

                        {{-- Descripción del Artículo --}}
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Descripción del Artículo / Prenda <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="descripcion" required 
                                   x-model="descripcion"
                                   placeholder="Ej: REMERA MANGA CORTA JERSEY ESTAMPADA"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-bold focus:ring-blue-500 focus:border-blue-500 shadow-2xs">
                        </div>
                    </div>

                    {{-- Precios, Categoría y Subcategoría --}}
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        {{-- Precio de Costo --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Precio Costo ($)
                            </label>
                            <input type="number" step="0.01" min="0" name="precio_costo" 
                                   x-model="precioCosto"
                                   placeholder="0.00"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono font-bold text-right shadow-2xs">
                        </div>

                        {{-- Precio de Venta --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Precio Venta ($) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" name="precio_venta" required 
                                   x-model="precioVenta"
                                   placeholder="0.00"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 font-mono font-black text-right text-sm shadow-2xs focus:ring-emerald-500 focus:border-emerald-500">
                        </div>

                        {{-- Categoría Opcional --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Categoría</label>
                            <select name="categoria_id" x-model="categoriaId"
                                    class="w-full text-xs py-2 px-2.5 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                                <option value="">-- Sin Categoría --</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Subcategoría Opcional --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Subcategoría</label>
                            <select name="subcategoria_id" x-model="subcategoriaId"
                                    class="w-full text-xs py-2 px-2.5 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                                <option value="">-- Sin Subcategoría --</option>
                                @foreach($subcategorias as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Unidades que Ingresan y Sucursal Receptora --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-700/60 items-center">
                        <div>
                            <label class="block text-xs font-black text-slate-800 dark:text-slate-200 mb-1">
                                Cantidad de Unidades a Ingresar <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="1" min="1" name="cantidad" required 
                                   x-model="cantidad"
                                   class="w-full text-base py-2 px-3 rounded-xl border-blue-400 dark:border-blue-600 bg-white dark:bg-slate-900 text-blue-700 dark:text-blue-300 font-mono font-black text-center shadow-2xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Sucursal Receptora <span class="text-rose-500">*</span>
                            </label>
                            <select name="sucursal_id" x-model="sucursalId" required
                                    class="w-full text-xs py-2 px-2.5 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-bold">
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}" {{ $suc->id == $sucursalActivaId ? 'selected' : '' }}>
                                        {{ $suc->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Checkbox Impresión de Etiquetas Térmicas --}}
                    <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/60 flex items-center justify-between">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="imprimir_etiquetas" value="1" x-model="imprimirEtiquetas"
                                   class="rounded border-slate-300 shadow-2xs w-4 h-4 text-blue-600 focus:ring-blue-500">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                Imprimir etiquetas térmicas (50×25mm) para las <span class="font-black text-blue-600 dark:text-blue-400" x-text="cantidad"></span> prendas
                            </span>
                        </label>
                        <span class="text-[10px] text-slate-400 font-mono font-bold">CODE128 Autoprint</span>
                    </div>

                    {{-- Botones de Acción Principal: Verificación al presionar Continuar --}}
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-700/60">
                        <button type="button" @click="limpiarFormularioArticulo()"
                                class="w-full sm:w-auto px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl transition-all">
                            Limpiar Formulario
                        </button>

                        <button type="button" 
                                @click="presionarContinuar()"
                                :disabled="verificandoSubmit || !proveedorId"
                                class="w-full sm:w-auto px-8 py-3 text-xs font-black text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            
                            <span x-show="!verificandoSubmit" class="flex items-center gap-2">
                                <span>Continuar / Ingresar Stock</span>
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </span>

                            <span x-show="verificandoSubmit" class="flex items-center gap-2" style="display: none;">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>Verificando disponibilidad...</span>
                            </span>
                        </button>
                    </div>
                </form>

                {{-- ============================================================== --}}
                {{-- MODAL 1: CONFIRMACIÓN DE ALTA (CÓDIGO LIBRE)                   --}}
                {{-- ============================================================== --}}
                <div x-show="modalConfirmacionAlta" 
                     style="display: none;"
                     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    
                    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-blue-200 dark:border-blue-800 w-full max-w-lg p-6 shadow-2xl space-y-4"
                         @click.away="modalConfirmacionAlta = false">
                        
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                            <span class="w-10 h-10 rounded-2xl bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xl shrink-0">
                                ✨
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
                                    Confirmar Alta de Nuevo Producto
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    El código generado está disponible para registrarse en catálogo.
                                </p>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase text-blue-800 dark:text-blue-300">Código CODE128 Asignado:</span>
                                <span class="font-mono font-black text-sm bg-white dark:bg-slate-900 text-blue-700 dark:text-blue-300 px-2.5 py-0.5 rounded-lg border border-blue-300" x-text="codigoGenerado"></span>
                            </div>
                            <div class="pt-1">
                                <span class="text-[10px] text-slate-500 block uppercase font-bold">Descripción:</span>
                                <span class="font-black text-slate-900 dark:text-white text-xs" x-text="descripcion"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 pt-1 font-mono text-[11px]">
                                <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                    <span class="text-[9px] text-slate-400 block font-sans">Talle</span>
                                    <strong x-text="talle"></strong>
                                </div>
                                <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                    <span class="text-[9px] text-slate-400 block font-sans">Color</span>
                                    <strong x-text="color"></strong>
                                </div>
                                <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                    <span class="text-[9px] text-slate-400 block font-sans">Fábrica</span>
                                    <strong x-text="codigoProveedor"></strong>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-blue-200 dark:border-blue-800 font-bold">
                                <span>Unidades Iniciales a Ingresar:</span>
                                <span class="text-sm font-black text-blue-700 dark:text-blue-300" x-text="cantidad + ' unid.'"></span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            ¿Desea crear este nuevo artículo en el catálogo e ingresar las <strong x-text="cantidad"></strong> prendas?
                        </p>

                        <div class="flex items-center justify-end gap-2.5 pt-2">
                            <button type="button" @click="modalConfirmacionAlta = false"
                                    class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 bg-slate-100 dark:bg-slate-700 rounded-xl">
                                Cancelar / Modificar
                            </button>
                            <button type="button" @click="enviarFormulario()"
                                    class="px-5 py-2 text-xs font-black text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md flex items-center gap-1.5">
                                <span>Confirmar y Dar de Alta &rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ============================================================== --}}
                {{-- MODAL 2: CÓDIGO OCUPADO (PREGUNTAR SI SUMAR A EXISTENTE)       --}}
                {{-- ============================================================== --}}
                <div x-show="modalCodigoOcupado" 
                     style="display: none;"
                     class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    
                    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-amber-300 dark:border-amber-700 w-full max-w-lg p-6 shadow-2xl space-y-4"
                         @click.away="modalCodigoOcupado = false">
                        
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-700">
                            <span class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xl shrink-0">
                                ⚠️
                            </span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">
                                    Código Asignado a Prenda Existente
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    El código generado ya se encuentra registrado en el sistema.
                                </p>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black uppercase text-amber-800 dark:text-amber-300">Código CODE128 Coincidente:</span>
                                <span class="font-mono font-black text-sm bg-white dark:bg-slate-900 text-amber-800 dark:text-amber-300 px-2.5 py-0.5 rounded-lg border border-amber-300" 
                                      x-text="productoOcupadoData ? productoOcupadoData.codigo : ''"></span>
                            </div>
                            <div class="pt-1">
                                <span class="text-[10px] text-slate-500 block uppercase font-bold">Prenda Registrada en Catálogo:</span>
                                <span class="font-black text-slate-900 dark:text-white text-xs" 
                                      x-text="productoOcupadoData ? (productoOcupadoData.descripcion_completa || productoOcupadoData.descripcion) : ''"></span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 pt-1 font-mono text-[11px]">
                                <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                    <span class="text-[9px] text-slate-400 block font-sans">Talle • Color</span>
                                    <strong x-text="(productoOcupadoData ? productoOcupadoData.talle : '') + ' • ' + (productoOcupadoData ? productoOcupadoData.color : '')"></strong>
                                </div>
                                <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700">
                                    <span class="text-[9px] text-slate-400 block font-sans">Stock Actual Sucursal</span>
                                    <strong class="text-emerald-600" x-text="(productoOcupadoData ? productoOcupadoData.stock_sucursal : 0) + ' unid.'"></strong>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-amber-200 dark:border-amber-800 font-bold">
                                <span>Cantidad a Sumar:</span>
                                <span class="text-sm font-black text-emerald-600 dark:text-emerald-400" x-text="'+' + cantidad + ' unid.'"></span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            ¿Desea ingresar y <strong>sumar las <span x-text="cantidad"></span> unidades</strong> al stock de este artículo existente o prefiere cancelar para modificar el talle/color?
                        </p>

                        <div class="flex items-center justify-end gap-2.5 pt-2">
                            <button type="button" @click="modalCodigoOcupado = false"
                                    class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 bg-slate-100 dark:bg-slate-700 rounded-xl">
                                Cancelar / Modificar
                            </button>
                            <button type="button" @click="enviarFormulario()"
                                    class="px-5 py-2 text-xs font-black text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md flex items-center gap-1.5">
                                <span>Sumar Unidades a este Producto &rarr;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL RÁPIDO: ALTA DE NUEVO PROVEEDOR                          --}}
        {{-- ============================================================== --}}
        <div x-show="modalProveedorAbierto" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 w-full max-w-lg p-5 shadow-2xl space-y-4"
                 @click.away="modalProveedorAbierto = false">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>+ Registrar Nuevo Proveedor</span>
                    </h3>
                    <button type="button" @click="modalProveedorAbierto = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <div x-show="errorProv" class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 text-rose-800 dark:text-rose-300 text-xs font-bold" x-text="errorProv"></div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nombre / Razón Social <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="nuevoProv.nombre" required placeholder="Ej: TEXTIL PATAGONIA S.A."
                               class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-bold focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Legajo (3 dígitos) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="nuevoProv.legajo" required maxlength="10" placeholder="Ej: 015"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono font-bold text-center">
                            <span class="text-[9px] text-slate-400">Prefijo para etiquetas</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                CUIT
                            </label>
                            <input type="text" x-model="nuevoProv.cuit" placeholder="XX-XXXXXXXX-X"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Teléfono / WhatsApp
                            </label>
                            <input type="text" x-model="nuevoProv.tel1" placeholder="Ej: 280-4123456"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Dirección / Localidad
                            </label>
                            <input type="text" x-model="nuevoProv.direccion" placeholder="Ej: Av. Mitre 540"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="modalProveedorAbierto = false" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 bg-slate-100 dark:bg-slate-700 rounded-xl">
                        Cancelar
                    </button>
                    <button type="button" @click="guardarNuevoProveedor()" :disabled="guardandoProv"
                            class="px-5 py-2 text-xs font-black text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md flex items-center gap-1.5">
                        <template x-if="guardandoProv">
                            <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </template>
                        <span>Guardar y Seleccionar</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function ingresoMercaderia() {
            return {
                // Estado General
                proveedorId: '{{ $ultimoProvId }}',
                proveedoresMap: @json($proveedoresMap),
                sucursalId: {{ $sucursalActivaId ?? 1 }},

                // Búsqueda de Prenda Base del Proveedor
                busquedaQuery: '',
                buscando: false,
                productosEncontrados: [],
                articuloBase: null,
                valoresBaseOriginales: null,

                // Datos de la Prenda a Ingresar / Dar de Alta
                codigoProveedor: '{{ old('codigo_proveedor', '') }}',
                descripcion: '{{ old('descripcion', '') }}',
                talle: '{{ old('talle', '') }}',
                color: '{{ old('color', '') }}',
                precioCosto: '{{ old('precio_costo', '') }}',
                precioVenta: '{{ old('precio_venta', '') }}',
                categoriaId: '{{ old('categoria_id', '') }}',
                subcategoriaId: '{{ old('subcategoria_id', '') }}',
                cantidad: '{{ old('cantidad', 1) }}',
                imprimirEtiquetas: true,

                // Control de Modales y Verificación al Presionar Continuar
                verificandoSubmit: false,
                modalConfirmacionAlta: false,
                modalCodigoOcupado: false,
                productoOcupadoData: null,

                // Modal de Nuevo Proveedor Rápido
                modalProveedorAbierto: false,
                nuevoProv: {
                    nombre: '',
                    legajo: '{{ $siguienteLegajoProveedor }}',
                    cuit: '',
                    tel1: '',
                    direccion: ''
                },
                guardandoProv: false,
                errorProv: '',

                init() {
                    // Solo limpiamos resultados de búsqueda previa cuando se cambia el proveedor
                    this.$watch('proveedorId', () => { 
                        this.busquedaQuery = ''; 
                        this.productosEncontrados = []; 
                    });
                },

                // Métodos de Normalización para Código CODE128
                normalizarTalle2Char(val) {
                    if (!val || !val.trim()) return '00';
                    const t = val.trim().toUpperCase();
                    if (!isNaN(t)) {
                        return String(parseInt(t, 10)).padStart(2, '0');
                    }
                    const map = {
                        'XS': 'XS', 'S': '0S', 'M': '0M', 'L': '0L', 'XL': 'XL',
                        'XXL': '2X', '2XL': '2X', 'XXXL': '3X', '3XL': '3X',
                        '4XL': '4X', '5XL': '5X', '6XL': '6X', '7XL': '7X', '8XL': '8X',
                        'U': '0U', 'UNICO': 'UN', 'ESP': 'ES'
                    };
                    if (map[t]) return map[t];
                    if (t.length === 2) return t;
                    if (t.length === 1) return '0' + t;
                    const clean = t.replace(/[^A-Z0-9]/g, '');
                    return (clean.substring(0, 2) || '00').padEnd(2, '0');
                },

                normalizarColor2Char(val) {
                    if (!val || !val.trim()) return '00';
                    const c = val.trim().toUpperCase();
                    const map = {
                        'NEGRO': 'NE', 'BLANCO': 'BL', 'AZUL': 'AZ', 'AZUL MARINO': 'AM',
                        'ROJO': 'RO', 'VERDE': 'VE', 'AMARILLO': 'AM', 'GRIS': 'GR',
                        'ROSA': 'RS', 'ROSADO': 'RS', 'CELESTE': 'CE', 'BEIGE': 'BE',
                        'MARRON': 'MA', 'CAFE': 'CF', 'BORDO': 'BO', 'VIOLETA': 'VI',
                        'LILA': 'LI', 'NARANJA': 'NA', 'FUCSIA': 'FU', 'TURQUESA': 'TU',
                        'ESTAMPADO': 'ES', 'SURTIDO': 'SU', 'VARIOS': 'VA', 'MULTICOLOR': 'MC'
                    };
                    if (map[c]) return map[c];
                    const clean = c.replace(/[^A-Z0-9]/g, '');
                    if (clean.length >= 2) return clean.substring(0, 2);
                    if (clean.length === 1) return '0' + clean;
                    return '00';
                },

                get legajoProveedor() {
                    if (!this.proveedorId || !this.proveedoresMap[this.proveedorId]) return '000';
                    return this.proveedoresMap[this.proveedorId].legajo || '000';
                },

                get talle2Char() {
                    return this.normalizarTalle2Char(this.talle);
                },

                get color2Char() {
                    return this.normalizarColor2Char(this.color);
                },

                get codigoArticuloLimpio() {
                    if (!this.codigoProveedor) return '';
                    return this.codigoProveedor.trim().replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                },

                get haModificadoVariante() {
                    if (!this.articuloBase || !this.valoresBaseOriginales) return false;
                    const talleActual = String(this.talle ?? '').trim().toUpperCase();
                    const talleOrig = String(this.valoresBaseOriginales.talle ?? '').trim().toUpperCase();
                    const colorActual = String(this.color ?? '').trim().toUpperCase();
                    const colorOrig = String(this.valoresBaseOriginales.color ?? '').trim().toUpperCase();
                    const artActual = String(this.codigoProveedor ?? '').trim().toUpperCase();
                    const artOrig = String(this.valoresBaseOriginales.codigoProveedor ?? '').trim().toUpperCase();

                    return talleActual !== talleOrig || colorActual !== colorOrig || artActual !== artOrig;
                },

                get codigoGenerado() {
                    if (!this.proveedorId) return '';
                    // Si se seleccionó una prenda base y NO se modificó talle, color ni fábrica, respetar el código original exacto
                    if (this.articuloBase && !this.haModificadoVariante) {
                        return this.articuloBase.codigo;
                    }
                    const art = this.codigoArticuloLimpio || '00001';
                    return (this.legajoProveedor + this.talle2Char + this.color2Char + art).toUpperCase();
                },

                // Extrae talle, color y código de fábrica si están vacíos en productos existentes
                extraerAtributosDeCodigo(codigo, talleExistente, colorExistente, codProvExistente) {
                    let code = String(codigo || '').trim().replace(/[^A-Za-z0-9]/g, '');
                    if (code.length === 11) {
                        code = '0' + code;
                    }

                    let resCodProv = codProvExistente ? String(codProvExistente).trim() : '';
                    let resTalle = talleExistente ? String(talleExistente).trim() : '';
                    let resColor = colorExistente ? String(colorExistente).trim() : '';

                    if (code.length >= 12) {
                        const segTalle = code.substring(3, 5);
                        const segColor = code.substring(5, 7);
                        const fabrica = code.substring(7);

                        if (!resCodProv) resCodProv = fabrica;
                        if (!resTalle) resTalle = segTalle;
                        if (!resColor) resColor = segColor;
                    } else if (code.length >= 8) {
                        if (!resCodProv) resCodProv = code.substring(3);
                        if (!resTalle) resTalle = '00';
                        if (!resColor) resColor = '00';
                    } else {
                        if (!resCodProv) resCodProv = code || '00001';
                        if (!resTalle) resTalle = '00';
                        if (!resColor) resColor = '00';
                    }

                    return {
                        talle: resTalle,
                        color: resColor,
                        codigoProveedor: resCodProv
                    };
                },

                // Búsqueda AJAX de artículos del proveedor
                buscarArticulosProveedor() {
                    if (!this.proveedorId) return;
                    if (this.busquedaQuery.trim().length === 0) {
                        this.productosEncontrados = [];
                        return;
                    }
                    this.buscando = true;
                    fetch(`{{ route('productos.buscar-por-proveedor') }}?proveedor_id=${this.proveedorId}&q=${encodeURIComponent(this.busquedaQuery)}&sucursal_id=${this.sucursalId}`)
                        .then(res => res.json())
                        .then(data => {
                            this.productosEncontrados = data;
                            this.buscando = false;
                        })
                        .catch(() => {
                            this.buscando = false;
                        });
                },

                // Carga los datos de una prenda base para generar una variante o ingresar stock
                cargarArticuloBase(prod) {
                    this.articuloBase = JSON.parse(JSON.stringify(prod));

                    // Extraer talle, color y código de fábrica si venían vacíos desde el código de barra existente
                    const ext = this.extraerAtributosDeCodigo(prod.codigo, prod.talle, prod.color, prod.codigo_proveedor);

                    this.codigoProveedor = ext.codigoProveedor;
                    this.talle = ext.talle;
                    this.color = ext.color;
                    this.descripcion = prod.descripcion || '';
                    this.precioCosto = prod.precio_costo || 0;
                    this.precioVenta = prod.precio_venta || 0;
                    this.categoriaId = prod.categoria_id ? String(prod.categoria_id) : '';
                    this.subcategoriaId = prod.subcategoria_id ? String(prod.subcategoria_id) : '';
                    this.cantidad = 1;
                    this.productosEncontrados = [];
                    this.busquedaQuery = '';

                    // Guardar snapshot de valores originales para detectar modificaciones en tiempo real
                    this.valoresBaseOriginales = {
                        codigo: prod.codigo,
                        talle: String(ext.talle ?? '').trim().toUpperCase(),
                        color: String(ext.color ?? '').trim().toUpperCase(),
                        codigoProveedor: String(ext.codigoProveedor ?? '').trim().toUpperCase(),
                    };

                    this.$nextTick(() => {
                        const inputTalle = document.getElementById('input_talle');
                        if (inputTalle) {
                            inputTalle.focus();
                            inputTalle.select();
                        }
                    });
                },

                limpiarFormularioArticulo() {
                    this.articuloBase = null;
                    this.valoresBaseOriginales = null;
                    this.codigoProveedor = '';
                    this.descripcion = '';
                    this.talle = '';
                    this.color = '';
                    this.precioCosto = '';
                    this.precioVenta = '';
                    this.categoriaId = '';
                    this.subcategoriaId = '';
                    this.cantidad = 1;
                },

                // Verificación ÚNICA al presionar el botón Continuar / Ingresar Stock
                presionarContinuar() {
                    if (!this.proveedorId) {
                        alert('Por favor seleccione un proveedor en el Paso 1.');
                        return;
                    }
                    if (!this.descripcion || !this.descripcion.trim()) {
                        alert('La descripción del artículo es obligatoria.');
                        return;
                    }
                    if (!this.talle || !this.talle.trim()) {
                        alert('El talle de la prenda es obligatorio.');
                        return;
                    }
                    if (!this.color || !this.color.trim()) {
                        alert('El color de la prenda es obligatorio.');
                        return;
                    }
                    if (!this.codigoProveedor || !this.codigoProveedor.trim()) {
                        alert('El código de fábrica o proveedor es obligatorio.');
                        return;
                    }
                    if (!this.cantidad || parseInt(this.cantidad, 10) < 1) {
                        alert('La cantidad de unidades a ingresar debe ser al menos 1.');
                        return;
                    }
                    if (!this.precioVenta || parseFloat(this.precioVenta) <= 0) {
                        alert('El precio de venta debe ser mayor a cero.');
                        return;
                    }

                    // CASO 1: Prenda existente seleccionada SIN cambios en talle, color ni fábrica
                    // Ingreso directo sin demoras ni modales adicionales
                    if (this.articuloBase && !this.haModificadoVariante) {
                        this.enviarFormulario();
                        return;
                    }

                    // CASO 2: Se modificó la variante o es una prenda completamente nueva
                    // Consultamos disponibilidad del código CODE128 generado
                    const cod = this.codigoGenerado;
                    if (!cod || cod.length < 5) {
                        alert('No se pudo generar un código de producto válido.');
                        return;
                    }

                    this.verificandoSubmit = true;
                    const url = `{{ route('productos.verificar-codigo') }}?codigo=${encodeURIComponent(cod)}&sucursal_id=${this.sucursalId}`;

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            this.verificandoSubmit = false;
                            if (data.existe && data.producto) {
                                // Código ocupado: mostrar datos del producto existente y preguntar si sumar unidades
                                this.productoOcupadoData = data.producto;
                                this.modalCodigoOcupado = true;
                            } else {
                                // Código libre: notificar y pedir confirmación de alta
                                this.modalConfirmacionAlta = true;
                            }
                        })
                        .catch(err => {
                            this.verificandoSubmit = false;
                            alert('No se pudo verificar la disponibilidad del código. Intente nuevamente.');
                        });
                },

                enviarFormulario() {
                    this.modalConfirmacionAlta = false;
                    this.modalCodigoOcupado = false;
                    const form = document.getElementById('form_ingreso_mercaderia');
                    if (form) {
                        form.submit();
                    }
                },

                guardarNuevoProveedor() {
                    if (!this.nuevoProv.nombre.trim()) {
                        this.errorProv = 'El nombre o razón social es obligatorio.';
                        return;
                    }
                    this.guardandoProv = true;
                    this.errorProv = '';

                    fetch('{{ route('proveedores.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            nombre: this.nuevoProv.nombre,
                            legajo: this.nuevoProv.legajo,
                            cuit: this.nuevoProv.cuit,
                            tel1: this.nuevoProv.tel1,
                            direccion: this.nuevoProv.direccion
                        })
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.message || 'Error al registrar proveedor');
                        }
                        return data;
                    })
                    .then(res => {
                        this.guardandoProv = false;
                        if (res.success && res.proveedor) {
                            const p = res.proveedor;
                            this.proveedoresMap[p.id] = {
                                nombre: p.nombre,
                                legajo: p.legajo_formateado || String(p.legajo).padStart(3, '0')
                            };
                            this.proveedorId = p.id;
                            this.modalProveedorAbierto = false;
                            this.nuevoProv = { nombre: '', legajo: '', cuit: '', tel1: '', direccion: '' };
                        }
                    })
                    .catch(err => {
                        this.guardandoProv = false;
                        this.errorProv = err.message || 'Error al conectar con el servidor';
                    });
                }
            };
        }
    </script>
</x-app-layout>
