<x-app-layout>
    @php
        $isEdit = isset($producto);
    @endphp

    <div class="max-w-4xl mx-auto py-2">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>{{ $isEdit ? 'Editar Producto' : 'Nuevo Producto / Artículo' }}</span>
                    @if($isEdit)
                        <span class="text-xs font-mono font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 px-2 py-0.5 rounded border border-blue-200 dark:border-blue-700">
                            {{ $producto->codigo }}
                        </span>
                    @endif
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Definición de datos maestros, precios, códigos de barra y stock.
                </p>
            </div>

            <a href="{{ $isEdit ? route('productos.show', $producto->id) : route('productos.index') }}" 
               class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-2xs transition-all">
                &larr; Volver
            </a>
        </div>

        <form action="{{ $isEdit ? route('productos.update', $producto->id) : route('productos.store') }}" 
              method="POST" 
              class="space-y-6" 
              x-data="{
                  controlaStock: {{ old('controla_stock', isset($producto) ? (int)$producto->controla_stock : 1) ? 'true' : 'false' }}
              }">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            {{-- Bloque 1: Identificación y Códigos --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-2xs space-y-4"
                 x-data="{
                     codigo: '{{ old('codigo', $producto->codigo ?? '') }}',
                     codigoBarra: '{{ old('codigo_barra', $producto->codigo_barra ?? '') }}',
                     codigoProveedor: '{{ old('codigo_proveedor', $producto->codigo_proveedor ?? '') }}',
                     talle: '{{ old('talle', $producto->talle ?? '') }}',
                     color: '{{ old('color', $producto->color ?? '') }}',
                     proveedorId: '{{ old('proveedor_id', $producto->proveedor_id ?? '') }}',
                     proveedoresMap: @json($proveedores->pluck('legajo', 'id')),

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
                             'NEGRO': 'NE', 'BLANCO': 'BL', 'AZUL': 'AZ', 'ROJO': 'RO',
                             'VERDE': 'VE', 'AMARILLO': 'AM', 'GRIS': 'GR', 'ROSA': 'RS',
                             'ROSADO': 'RS', 'CELESTE': 'CE', 'BEIGE': 'BE', 'MARRON': 'MA',
                             'CAFE': 'CF', 'BORDO': 'BO', 'VIOLETA': 'VI', 'LILA': 'LI',
                             'NARANJA': 'NA', 'FUCSIA': 'FU', 'TURQUESA': 'TU', 'ESTAMPADO': 'ES',
                             'SURTIDO': 'SU', 'VARIOS': 'VA', 'MULTICOLOR': 'MC'
                         };
                         if (map[c]) return map[c];
                         const clean = c.replace(/[^A-Z0-9]/g, '');
                         if (clean.length >= 2) return clean.substring(0, 2);
                         if (clean.length === 1) return '0' + clean;
                         return '00';
                     },

                     generarBarcodeSugerido() {
                         const leg = this.proveedoresMap[this.proveedorId] || '0';
                         const codProv = String(leg).padStart(3, '0');
                         const talle2 = this.normalizarTalle2Char(this.talle);
                         const color2 = this.normalizarColor2Char(this.color);
                         let art = this.codigoProveedor ? this.codigoProveedor.trim().replace(/[^A-Za-z0-9]/g, '').toUpperCase() : '';
                         if (!art) {
                             art = this.codigo ? this.codigo.trim().replace(/[^A-Za-z0-9]/g, '').toUpperCase() : '00001';
                         }
                         this.codigoBarra = (codProv + talle2 + color2 + art).toUpperCase();
                     }
                 }">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-2">
                    <h3 class="text-xs font-black uppercase tracking-wider text-blue-700 dark:text-blue-400">
                        1. Identificación, Talle, Color y Códigos
                    </h3>
                    <span class="text-[10px] text-slate-400 font-mono">
                        Estándar de etiqueta: CODE128 (cod.prov/talle/color/codigo)
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    {{-- Código Interno SKU --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Código Interno / SKU <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="codigo" required 
                               x-model="codigo"
                               value="{{ old('codigo', $producto->codigo ?? '') }}" 
                               placeholder="Ej: ART-001045"
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-mono font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('codigo') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Código de Fabricante / Proveedor --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Código de Fábrica / Proveedor
                        </label>
                        <input type="text" name="codigo_proveedor" 
                               x-model="codigoProveedor"
                               value="{{ old('codigo_proveedor', $producto->codigo_proveedor ?? '') }}" 
                               placeholder="Ej: FAB-9921 / ART-402"
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-mono font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('codigo_proveedor') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Código de Barras con botón Sugerir --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Código de Barras (CODE128)
                            </label>
                            <button type="button" @click="generarBarcodeSugerido()"
                                    class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1">
                                <span>⚡ Sugerir estructurado</span>
                            </button>
                        </div>
                        <div class="relative">
                            <input type="text" name="codigo_barra" 
                                   x-model="codigoBarra"
                                   value="{{ old('codigo_barra', $producto->codigo_barra ?? '') }}" 
                                   placeholder="###,##,##,##### (CODE128)"
                                   class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono uppercase focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        @error('codigo_barra') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Descripción del Artículo --}}
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Descripción del Artículo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="descripcion" required 
                               value="{{ old('descripcion', $producto->descripcion ?? '') }}" 
                               placeholder="Ej: PANTALON CORDEROY CLASICO TULY"
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('descripcion') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Talle --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Talle (1 al 70 / XS a 8XL)
                            </label>
                            <span class="text-[9px] text-slate-400 font-mono">Calzado, Bebé, Adulto</span>
                        </div>
                        <input type="text" name="talle" list="lista_talles"
                               x-model="talle"
                               value="{{ old('talle', $producto->talle ?? '') }}" 
                               placeholder="Ej: 42 o XL o 2XL"
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-mono font-bold focus:ring-blue-500 focus:border-blue-500">
                        <datalist id="lista_talles">
                            @foreach(App\Models\Producto::listaTallesSugeridos() as $t)
                                <option value="{{ $t }}"></option>
                            @endforeach
                        </datalist>
                        @error('talle') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Color --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Color del Artículo
                            </label>
                            <span class="text-[9px] text-slate-400">Nombre / Tono</span>
                        </div>
                        <input type="text" name="color" list="lista_colores"
                               x-model="color"
                               value="{{ old('color', $producto->color ?? '') }}" 
                               placeholder="Ej: Negro, Azul Marino, Blanco..."
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-bold focus:ring-blue-500 focus:border-blue-500">
                        <datalist id="lista_colores">
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
                            <option value="ESTAMPADO"></option>
                            <option value="SURTIDO"></option>
                        </datalist>
                        @error('color') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Proveedor Habitual --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">Proveedor Habitual</label>
                            <span class="text-[9px] text-slate-400 font-mono">[Cód. Prov 3c]</span>
                        </div>
                        <select name="proveedor_id" 
                                x-model="proveedorId"
                                class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-bold">
                            <option value="">-- Sin Proveedor Asignado --</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id }}" {{ old('proveedor_id', $producto->proveedor_id ?? '') == $prov->id ? 'selected' : '' }}>
                                    [{{ $prov->legajo_formateado ?? '000' }}] {{ $prov->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Clasificación: Categoría y Subcategoría --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Categoría</label>
                        <select name="categoria_id" 
                                class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            <option value="">-- Sin Categoría --</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Subcategoría / Segmento</label>
                        <select name="subcategoria_id" 
                                class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            <option value="">-- Sin Subcategoría --</option>
                            @foreach($subcategorias as $sub)
                                <option value="{{ $sub->id }}" {{ old('subcategoria_id', $producto->subcategoria_id ?? '') == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Bloque 2: Precios y Configuración de Stock --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-2xs space-y-4">
                <h3 class="text-xs font-black uppercase tracking-wider text-emerald-700 dark:text-emerald-400 border-b border-slate-100 dark:border-slate-700/60 pb-2">
                    2. Precios y Configuración de Stock
                </h3>

                {{-- Toggle de Control de Stock Físico --}}
                <label class="relative flex items-start gap-3 p-3.5 rounded-xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/50 dark:bg-blue-950/20 cursor-pointer select-none hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors">
                    <input type="checkbox" name="controla_stock" value="1" x-model="controlaStock" 
                           class="mt-0.5 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500 w-4 h-4">
                    <div>
                        <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-1.5">
                            Controlar Stock Físico (Artículo Inventariable)
                            <span x-show="!controlaStock" class="text-[9px] font-black uppercase px-1.5 py-0.2 rounded bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                Modo Genérico / Varios
                            </span>
                        </span>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                            Si está desmarcado, el producto se considera <strong>Genérico / Varios / Servicio</strong>: no requiere stock previo en sucursales, tiene disponibilidad libre y <strong>no suma unidades ni altera la valuación de existencias en los informes</strong>.
                        </p>
                    </div>
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Precio de Costo ($)
                        </label>
                        <input type="number" step="0.01" name="precio_costo" 
                               value="{{ old('precio_costo', $producto->precio_costo ?? 0) }}" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono font-bold text-right focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Precio de Venta ($) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="precio_venta" required 
                               value="{{ old('precio_venta', $producto->precio_venta ?? 0) }}" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-emerald-700 dark:text-emerald-400 font-mono font-black text-right text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        @error('precio_venta') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="controlaStock">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Stock Mínimo (Alerta)
                        </label>
                        <input type="number" step="1" name="stock_minimo" 
                               value="{{ old('stock_minimo', $producto->stock_minimo ?? 5) }}" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono text-center">
                    </div>

                    <div :class="controlaStock ? '' : 'sm:col-span-2'">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Unidad de Medida
                        </label>
                        <select name="unidad_medida" class="w-full text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            <option value="UNIDAD" {{ old('unidad_medida', $producto->unidad_medida ?? 'UNIDAD') == 'UNIDAD' ? 'selected' : '' }}>UNIDAD</option>
                            <option value="PAR" {{ old('unidad_medida', $producto->unidad_medida ?? '') == 'PAR' ? 'selected' : '' }}>PAR</option>
                            <option value="METRO" {{ old('unidad_medida', $producto->unidad_medida ?? '') == 'METRO' ? 'selected' : '' }}>METRO</option>
                            <option value="KG" {{ old('unidad_medida', $producto->unidad_medida ?? '') == 'KG' ? 'selected' : '' }}>KILOGRAMO (KG)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Observaciones / Detalles Técnicos</label>
                    <textarea name="observaciones" rows="2" 
                              placeholder="Talles, colores, materiales o notas internas..." 
                              class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-blue-500 focus:border-blue-500">{{ old('observaciones', $producto->observaciones ?? '') }}</textarea>
                </div>
            </div>

            {{-- Bloque 3: Stock Inicial por Sucursal (Solo en creación y si controla stock) --}}
            @if(!$isEdit)
                <div x-show="controlaStock" class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-2xs space-y-4">
                    <h3 class="text-xs font-black uppercase tracking-wider text-amber-700 dark:text-amber-400 border-b border-slate-100 dark:border-slate-700/60 pb-2">
                        3. Carga Inicial de Stock por Sucursal
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Indique la cantidad física existente en cada sucursal al dar de alta el artículo:
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach($sucursales as $suc)
                            <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
                                <span class="text-[10px] font-black uppercase text-slate-600 dark:text-slate-300 block truncate" title="{{ $suc->nombre }}">
                                    {{ $suc->nombre }}
                                </span>
                                <span class="text-[9px] text-slate-400 block mb-1">Cod: {{ $suc->codigo_sucursal ?? '-' }}</span>
                                <input type="number" step="1" min="0" 
                                       name="stock_inicial[{{ $suc->id }}]" 
                                       value="{{ old("stock_inicial.{$suc->id}", 0) }}" 
                                       class="w-full text-xs py-1 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono font-bold text-center">
                            </div>
                        @endforeach
                    </div>

                    {{-- Checkbox y advertencia de control de inventario para impresión tras carga inicial --}}
                    <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 space-y-2 mt-2">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="imprimir_etiquetas" value="1" 
                                   class="rounded border-slate-300 text-blue-600 shadow-2xs focus:ring-blue-500">
                            <span class="text-xs font-bold text-blue-900 dark:text-blue-200">
                                Imprimir etiquetas térmicas del stock inicial cargado (50×25mm)
                            </span>
                        </label>
                        <div class="flex items-start gap-1.5 text-[10px] text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/60 p-2 rounded-lg border border-amber-300 dark:border-amber-800/60 leading-relaxed">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <strong>¡Atención al control de inventario!</strong>
                                No imprima etiquetas de más. La cantidad impresa debe coincidir estrictamente con las prendas físicas existentes para evitar descuadres entre las existencias en góndola y el stock del sistema.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Botones de Acción --}}
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ $isEdit ? route('productos.show', $producto->id) : route('productos.index') }}" 
                   class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $isEdit ? 'Guardar Cambios' : 'Registrar Producto' }}</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
