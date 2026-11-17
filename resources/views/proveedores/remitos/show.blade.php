<x-app-layout>
    <div class="max-w-6xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6"
         x-data="{ 
             modalAgregar: false,
             busqueda: '',
             resultados: [],
             itemsNuevos: [],
             completar: false,
             buscarProductos() {
                 const term = this.busqueda.trim();
                 if (term.length < 1) { this.resultados = []; return; }
                 fetch('{{ route('remitos.buscar-productos') }}?q=' + encodeURIComponent(term) + '&sucursal_id={{ $remito->sucursal_id }}')
                     .then(res => res.json())
                     .then(data => { this.resultados = data; })
                     .catch(() => { this.resultados = []; });
             },
             agregarItem(prod) {
                 const exist = this.itemsNuevos.find(i => i.producto_id === prod.id);
                 if (exist) {
                     exist.cantidad += 1;
                 } else {
                     const costo = Number(prod.precio_costo) || 0;
                     const venta = Number(prod.precio_venta) > 0 ? Number(prod.precio_venta) : Math.round(costo * 2 * 100) / 100;
                     this.itemsNuevos.push({
                         producto_id: prod.id,
                         codigo: prod.codigo,
                         descripcion: prod.descripcion,
                         cantidad: 1,
                         precio_compra: costo,
                         precio_venta: venta
                     });
                 }
                 this.busqueda = '';
                 this.resultados = [];
             }
         }">

        <!-- Header y Navegación -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <a href="{{ route('remitos.index') }}" 
                   class="bg-white dark:bg-slate-800 p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black uppercase px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300">
                            Comprobante de Recepción
                        </span>
                        @if($remito->estado === 'completado')
                            <span class="bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 text-[10px] font-black uppercase px-2 py-0.5 rounded-full border border-emerald-300">
                                Completado
                            </span>
                        @else
                            <span class="bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full border border-amber-300 animate-pulse">
                                Entrega Parcial
                            </span>
                        @endif
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight mt-1">
                        Remito N° {{ $remito->numero_remito }}
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($remito->estado === 'parcial')
                    <a href="{{ route('remitos.create', ['remito_id' => $remito->id]) }}"
                       class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition shadow-md flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>✏️ Continuar Carga en Etapas</span>
                    </a>
                @endif

                <a href="{{ route('stock.movimientos', ['search' => 'REM-' . $remito->numero_remito]) }}"
                   class="px-3.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 transition shadow-sm">
                    Ver Movimientos de Stock
                </a>

                <button type="button" onclick="window.print()"
                        class="px-3.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-50 transition shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir
                </button>
            </div>
        </div>

        <!-- Alertas -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-bold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Ficha de Datos del Remito -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/80 p-6 shadow-sm">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                    <span class="text-[10px] font-black uppercase text-slate-400 block mb-0.5">Proveedor</span>
                    <span class="text-sm font-black text-slate-900 dark:text-white block">
                        {{ $remito->proveedor?->nombre ?? 'N/A' }}
                    </span>
                    @if($remito->proveedor?->cuit)
                        <span class="text-[11px] font-mono text-slate-500">CUIT: {{ $remito->proveedor->cuit }}</span>
                    @endif
                </div>

                <div>
                    <span class="text-[10px] font-black uppercase text-slate-400 block mb-0.5">Fecha Recepción</span>
                    <span class="text-sm font-mono font-bold text-slate-900 dark:text-white block">
                        {{ \Carbon\Carbon::parse($remito->fecha_recepcion)->format('d/m/Y') }}
                    </span>
                    <span class="text-[11px] text-slate-400">Registrado {{ \Carbon\Carbon::parse($remito->created_at)->format('H:i') }} hs</span>
                </div>

                <div>
                    <span class="text-[10px] font-black uppercase text-slate-400 block mb-0.5">Sede Receptora</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white block">
                        {{ $remito->sucursal?->nombre ?? 'N/A' }}
                    </span>
                    <span class="text-[11px] text-blue-700 dark:text-blue-400 font-mono font-bold">Impacto directo en stock</span>
                </div>

                <div>
                    <span class="text-[10px] font-black uppercase text-slate-400 block mb-0.5">Operador Receptor</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white block">
                        {{ $remito->usuario?->name ?? 'Usuario Sistema' }}
                    </span>
                    <span class="text-[11px] text-slate-400">({{ $remito->usuario?->username ?? 'admin' }})</span>
                </div>
            </div>

            @if($remito->observaciones)
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-300">
                    <strong class="font-bold text-slate-400 text-[10px] uppercase">Observaciones:</strong> {{ $remito->observaciones }}
                </div>
            @endif
        </div>

        <!-- Tabla de Artículos Ingresados -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/80 shadow-md overflow-hidden">
            <div class="bg-slate-50 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-700 px-5 py-3.5 flex items-center justify-between">
                <h2 class="font-black text-xs text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                    Artículos Ingresados en este Remito ({{ $remito->items->count() }})
                </h2>
                <span class="text-xs text-slate-400 font-mono font-bold">
                    Total: {{ number_format($remito->items->sum('cantidad'), 0) }} unidades
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/70 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-black uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700">
                            <th class="py-3 px-4">Código</th>
                            <th class="py-3 px-4">Descripción del Artículo</th>
                            <th class="py-3 px-3 text-center">Cantidad Recibida</th>
                            <th class="py-3 px-3 text-center">Stock Antes &rarr; Después</th>
                            <th class="py-3 px-3 text-right">Precio Compra ($)</th>
                            <th class="py-3 px-3 text-right">Precio Venta ($)</th>
                            <th class="py-3 px-4 text-right">Subtotal Costo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-medium">
                        @foreach($remito->items as $item)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30">
                                <td class="py-3 px-4 font-mono font-black text-blue-700 dark:text-blue-400">
                                    <a href="{{ route('productos.show', $item->producto_id) }}" class="hover:underline">
                                        {{ $item->producto?->codigo ?? 'N/A' }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-slate-900 dark:text-white">
                                    <strong>{{ $item->producto?->descripcion ?? 'Artículo Eliminado' }}</strong>
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[10px] text-slate-400 font-mono flex-wrap">
                                        @if($item->producto?->talle)
                                            <span class="px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold">T: {{ $item->producto->talle }}</span>
                                        @endif
                                        @if($item->producto?->color)
                                            <span class="px-1.5 py-0.2 rounded bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 font-bold">C: {{ $item->producto->color }}</span>
                                        @endif
                                        @if($item->producto?->codigo_proveedor)
                                            <span class="px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">Fab: {{ $item->producto->codigo_proveedor }}</span>
                                        @endif
                                        <span>Barra: {{ $item->producto?->codigo_barra ?? 'S/D' }}</span>
                                        <span>• {{ $item->producto?->categoria?->nombre ?? 'General' }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-bold text-slate-900 dark:text-white">
                                    <span class="bg-blue-50 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">
                                        +{{ number_format($item->cantidad, 0) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center font-mono text-[11px] text-slate-500">
                                    {{ number_format($item->stock_anterior, 0) }} &rarr; <strong class="text-slate-800 dark:text-slate-200">{{ number_format($item->stock_posterior, 0) }}</strong>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-700 dark:text-slate-300">
                                    $ {{ number_format($item->precio_compra, 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                    $ {{ number_format($item->precio_venta, 2, ',', '.') }}
                                    @if($item->precio_venta_anterior && $item->precio_venta_anterior != $item->precio_venta)
                                        <span class="text-[9px] text-slate-400 block font-normal">Ant: ${{ number_format($item->precio_venta_anterior, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-black text-slate-900 dark:text-white">
                                    $ {{ number_format($item->cantidad * $item->precio_compra, 2, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 dark:bg-slate-800/80 border-t-2 border-slate-200 dark:border-slate-700 font-mono">
                        <tr>
                            <td colspan="2" class="py-3 px-4 font-bold uppercase text-[11px] text-slate-500">Totales Generales:</td>
                            <td class="py-3 px-3 text-center font-black text-slate-900 dark:text-white">
                                {{ number_format($remito->items->sum('cantidad'), 0) }} u.
                            </td>
                            <td></td>
                            <td></td>
                            <td class="py-3 px-3 text-right text-emerald-600 font-black">
                                $ {{ number_format($remito->total_venta_estimado, 2, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right text-blue-900 dark:text-blue-300 font-black text-sm">
                                $ {{ number_format($remito->total_costo, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- MODAL PARA AGREGAR ÍTEMS A REMITO PARCIAL -->
        <div x-show="modalAgregar" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-2xl w-full p-6 text-left"
                 @click.away="modalAgregar = false">
                
                <form action="{{ route('remitos.agregar-items', $remito->id) }}" method="POST">
                    @csrf

                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4">
                        <div>
                            <h3 class="text-base font-black uppercase text-slate-900 dark:text-white">
                                Agregar Entrega Complementaria
                            </h3>
                            <p class="text-xs text-slate-500">Remito N° {{ $remito->numero_remito }} ({{ $remito->proveedor?->nombre }})</p>
                        </div>
                        <button type="button" @click="modalAgregar = false" class="text-slate-400 hover:text-slate-600 text-lg font-black">&times;</button>
                    </div>

                    <!-- Buscador interno -->
                    <div class="relative mb-4">
                        <input type="text" x-model="busqueda" @input.debounce.250ms="buscarProductos()"
                               placeholder="Buscar artículo por código, barra o descripción..."
                               class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white">

                        <div x-show="resultados.length > 0"
                             class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl max-h-48 overflow-y-auto divide-y divide-slate-100">
                            <template x-for="p in resultados" :key="p.id">
                                <div @click="agregarItem(p)" class="p-2.5 hover:bg-blue-50 cursor-pointer flex justify-between text-xs">
                                    <div>
                                        <span class="font-mono font-bold text-blue-700" x-text="p.codigo"></span>
                                        <span class="font-medium text-slate-800" x-text="p.descripcion"></span>
                                    </div>
                                    <span class="font-mono text-slate-500" x-text="'$' + p.precio_costo"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Lista de Ítems Adicionales -->
                    <div class="max-h-56 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 mb-4 divide-y divide-slate-100">
                        <template x-for="(it, i) in itemsNuevos" :key="it.producto_id">
                            <div class="p-2.5 flex items-center justify-between text-xs">
                                <div class="flex-1">
                                    <input type="hidden" :name="'items[' + i + '][producto_id]'" :value="it.producto_id">
                                    <span class="font-mono font-bold text-blue-700" x-text="it.codigo"></span>
                                    <span class="font-medium" x-text="it.descripcion"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="number" min="0.01" step="any" placeholder="Cant" required
                                           :name="'items[' + i + '][cantidad]'" x-model.number="it.cantidad"
                                           class="w-16 text-center text-xs py-1 px-1.5 rounded border-slate-300">
                                    <input type="number" min="0" step="0.01" placeholder="Costo" required
                                           :name="'items[' + i + '][precio_compra]'" x-model.number="it.precio_compra"
                                           class="w-20 text-right text-xs py-1 px-1.5 rounded border-slate-300 font-mono">
                                    <input type="number" min="0" step="0.01" placeholder="Venta" required
                                           :name="'items[' + i + '][precio_venta]'" x-model.number="it.precio_venta"
                                           class="w-20 text-right text-xs py-1 px-1.5 rounded border-emerald-300 font-mono text-emerald-700">
                                    <button type="button" @click="itemsNuevos.splice(i, 1)" class="text-rose-500 p-1">&times;</button>
                                </div>
                            </div>
                        </template>

                        <template x-if="itemsNuevos.length === 0">
                            <div class="py-6 text-center text-slate-400 text-xs italic">
                                Busca y selecciona los artículos que llegaron en esta entrega adicional.
                            </div>
                        </template>
                    </div>

                    <!-- Opción de Marcar como Completado -->
                    <div class="flex items-center gap-2 mb-4 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <input type="checkbox" id="chk_completar" name="completar_remito" value="1" x-model="completar"
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="chk_completar" class="text-xs font-bold text-slate-700 cursor-pointer">
                            Marcar este remito como COMPLETADO (no se esperan más entregas)
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="modalAgregar = false" class="px-3 py-1.5 text-xs font-bold text-slate-500">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="itemsNuevos.length === 0"
                                class="px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-xl text-xs font-bold transition shadow-sm disabled:opacity-50">
                            Confirmar Entrega Complementaria
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
