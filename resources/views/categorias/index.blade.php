<x-app-layout>
    <div class="max-w-7xl mx-auto py-2 space-y-6" 
         x-data="{ 
             modalNuevaCat: false, 
             modalEditarCat: false,
             catId: null,
             catNombre: '',
             catDesc: '',
             modalNuevaSub: false, 
             modalEditarSub: false,
             subId: null,
             subNombre: '',
             subDesc: '',
             abrirEditarCat(id, nombre, desc) {
                 this.catId = id;
                 this.catNombre = nombre;
                 this.catDesc = desc || '';
                 this.modalEditarCat = true;
             },
             abrirEditarSub(id, nombre, desc) {
                 this.subId = id;
                 this.subNombre = nombre;
                 this.subDesc = desc || '';
                 this.modalEditarSub = true;
             }
         }">
        
        {{-- Encabezado Principal --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span>Clasificación del Catálogo: Categorías y Subcategorías</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Estructura multidimensional: cualquier categoría (ej: <em>Pantalón, Remera, Calzado</em>) se combina libremente con cualquier subcategoría (ej: <em>Caballero, Dama, Niño, Bebé</em>).
                </p>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('productos.index') }}" class="px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs transition-all">
                    &larr; Catálogo
                </a>
                <button @click="modalNuevaCat = true" type="button" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1 shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>NUEVA CATEGORÍA</span>
                </button>
                <button @click="modalNuevaSub = true" type="button" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center space-x-1 shadow-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                    <span>NUEVA SUBCATEGORÍA</span>
                </button>
            </div>
        </div>

        {{-- Mensajes de Notificación y Alertas --}}
        @if(session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs space-y-1 shadow-2xs">
                <div class="font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>Por favor revise los siguientes avisos:</span>
                </div>
                <ul class="list-disc list-inside pl-6 space-y-0.5 font-medium">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Panel de 2 Columnas: Categorías y Subcategorías Independientes --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            
            {{-- COLUMNA 1: CATEGORÍAS PRINCIPALES --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white tracking-wide">
                                Categorías Principales
                            </h2>
                            <p class="text-[11px] text-slate-400">Tipo de prenda, producto o rubro</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300">
                        {{ $categorias->count() }} registradas
                    </span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-700/60 max-h-[600px] overflow-y-auto pr-1">
                    @forelse($categorias as $cat)
                        <div class="py-3 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/30 px-2 rounded-xl transition-colors group">
                            <div class="flex-grow pr-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-slate-100 text-xs uppercase tracking-tight">
                                        {{ $cat->nombre }}
                                    </span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold" title="Artículos asociados a esta categoría">
                                        {{ $cat->productos_count }} art.
                                    </span>
                                </div>
                                @if($cat->descripcion)
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $cat->descripcion }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100">
                                <button type="button" 
                                        @click="abrirEditarCat({{ $cat->id }}, '{{ addslashes($cat->nombre) }}', '{{ addslashes($cat->descripcion ?? '') }}')"
                                        class="p-1.5 text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-lg transition" title="Editar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                @if($cat->productos_count === 0)
                                    <form action="{{ route('categorias.destroy', $cat->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Deseas eliminar la categoría {{ $cat->nombre }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition" title="Eliminar categoría">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <span class="p-1.5 text-slate-300 dark:text-slate-600 cursor-not-allowed inline-flex items-center" 
                                          title="Bloqueado: posee {{ $cat->productos_count }} producto(s) asociado(s)">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            No hay categorías principales registradas.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- COLUMNA 2: SUBCATEGORÍAS GLOBALES --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs p-5 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-black uppercase text-slate-900 dark:text-white tracking-wide">
                                Subcategorías / Segmentos
                            </h2>
                            <p class="text-[11px] text-slate-400">Transversales (Caballero, Dama, Niño, Bebé, etc.)</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-300">
                        {{ $subcategorias->count() }} registradas
                    </span>
                </div>

                {{-- Explicación funcional --}}
                <div class="p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40 text-xs text-indigo-900 dark:text-indigo-300 flex items-start gap-2">
                    <svg class="w-4 h-4 text-indigo-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="leading-relaxed">
                        Estas subcategorías no dependen de una sola categoría; están disponibles para combinarse libremente con cualquier rubro (ejemplo: <em>Pantalón + Niño</em>, <em>Remera + Bebé</em> o <em>Calzado + Caballero</em>).
                    </p>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-700/60 max-h-[600px] overflow-y-auto pr-1">
                    @forelse($subcategorias as $sub)
                        <div class="py-3 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-700/30 px-2 rounded-xl transition-colors group">
                            <div class="flex-grow pr-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 dark:text-slate-100 text-xs uppercase tracking-tight">
                                        {{ $sub->nombre }}
                                    </span>
                                    <span class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 font-bold border border-indigo-100 dark:border-indigo-800" title="Artículos asociados a esta subcategoría">
                                        {{ $sub->productos_count }} art.
                                    </span>
                                </div>
                                @if($sub->descripcion)
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $sub->descripcion }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100">
                                <button type="button" 
                                        @click="abrirEditarSub({{ $sub->id }}, '{{ addslashes($sub->nombre) }}', '{{ addslashes($sub->descripcion ?? '') }}')"
                                        class="p-1.5 text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg transition" title="Editar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                @if($sub->productos_count === 0)
                                    <form action="{{ route('subcategorias.destroy', $sub->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Deseas eliminar la subcategoría {{ $sub->nombre }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition" title="Eliminar subcategoría">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <span class="p-1.5 text-slate-300 dark:text-slate-600 cursor-not-allowed inline-flex items-center" 
                                          title="Bloqueado: posee {{ $sub->productos_count }} producto(s) asociado(s)">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">
                            No hay subcategorías registradas.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Modal Nueva Categoría --}}
        <div x-show="modalNuevaCat" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full p-5 border border-slate-200 dark:border-slate-700"
                 @click.outside="modalNuevaCat = false">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase mb-3">Nueva Categoría Principal</h3>
                <form action="{{ route('categorias.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre de Categoría <span class="text-rose-500">*</span></label>
                        <input type="text" name="nombre" required placeholder="Ej: PANTALON / REMERA / CALZADO" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white uppercase font-bold focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Descripción</label>
                        <input type="text" name="descripcion" placeholder="Opcional..." 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="modalNuevaCat = false" class="px-3 py-1.5 text-xs font-bold text-slate-500">Cancelar</button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Crear Categoría</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Editar Categoría --}}
        <div x-show="modalEditarCat" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full p-5 border border-slate-200 dark:border-slate-700"
                 @click.outside="modalEditarCat = false">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase mb-3">Editar Categoría Principal</h3>
                <form :action="'{{ url('categorias') }}/' + catId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre</label>
                        <input type="text" name="nombre" x-model="catNombre" required 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white uppercase font-bold focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Descripción</label>
                        <input type="text" name="descripcion" x-model="catDesc" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="modalEditarCat = false" class="px-3 py-1.5 text-xs font-bold text-slate-500">Cancelar</button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Nueva Subcategoría --}}
        <div x-show="modalNuevaSub" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full p-5 border border-slate-200 dark:border-slate-700"
                 @click.outside="modalNuevaSub = false">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase mb-1">Nueva Subcategoría Global</h3>
                <p class="text-[11px] text-slate-400 mb-3">Aplicable a todas las categorías del sistema.</p>
                <form action="{{ route('subcategorias.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre de Subcategoría <span class="text-rose-500">*</span></label>
                        <input type="text" name="nombre" required placeholder="Ej: CABALLERO / DAMA / NIÑO / BEBE" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white uppercase font-bold focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Descripción</label>
                        <input type="text" name="descripcion" placeholder="Opcional..." 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="modalNuevaSub = false" class="px-3 py-1.5 text-xs font-bold text-slate-500">Cancelar</button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Crear Subcategoría</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Editar Subcategoría --}}
        <div x-show="modalEditarSub" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-sm w-full p-5 border border-slate-200 dark:border-slate-700"
                 @click.outside="modalEditarSub = false">
                <h3 class="font-bold text-sm text-slate-900 dark:text-white uppercase mb-3">Editar Subcategoría Global</h3>
                <form :action="'{{ url('categorias/subcategorias') }}/' + subId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre</label>
                        <input type="text" name="nombre" x-model="subNombre" required 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white uppercase font-bold focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Descripción</label>
                        <input type="text" name="descripcion" x-model="subDesc" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" @click="modalEditarSub = false" class="px-3 py-1.5 text-xs font-bold text-slate-500">Cancelar</button>
                        <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
