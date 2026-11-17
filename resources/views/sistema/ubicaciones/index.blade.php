<x-app-layout>
    <!-- Header Compacto -->
    <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-700 mb-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('sistema.index') }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                <span>Zonas Geográficas</span>
                <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">Provincias y Localidades</span>
            </h1>
        </div>
    </div>

    {{-- Alertas de validación: los formularios de edición viven en modales, por lo que sin
         este bloque los errores de validación no serían visibles al volver del redirect --}}
    @if ($errors->any())
        <div class="bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 px-3 py-2 rounded-md shadow-sm mb-3 flex items-start space-x-2">
            <span class="font-black text-[10px] uppercase block mt-0.5">⛔ Error:</span>
            <ul class="text-[10px] font-bold space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-4 items-start">

        <!-- ══════════ COLUMNA IZQUIERDA (40%): PROVINCIAS ══════════ -->
        <div class="w-full lg:w-[40%]">

            <!-- Formulario rápido de alta -->
            <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm overflow-hidden mb-4">
                <div class="bg-blue-950 dark:bg-slate-900 px-3 py-2 flex justify-between items-center border-b border-blue-900 dark:border-slate-700">
                    <h2 class="font-bold text-[10px] text-blue-200 dark:text-blue-300 uppercase tracking-wider">Alta Rápida de Provincia</h2>
                    <span class="bg-blue-800 dark:bg-blue-900 text-blue-100 dark:text-blue-200 font-bold text-[9px] px-1.5 py-0.5 rounded font-mono tabular-nums">
                        {{ $provincias->count() }}
                    </span>
                </div>

                <form action="{{ route('sistema.ubicaciones.provincias.store') }}" method="POST" autocomplete="off"
                      class="p-3 border-b border-slate-200 dark:border-slate-700 flex items-start gap-2 bg-slate-50/50 dark:bg-slate-800/50">
                    @csrf
                    <div class="flex-1">
                        <label for="nombre_provincia" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Nombre de la Provincia *</label>
                        <input id="nombre_provincia" name="nombre" type="text" maxlength="100" required
                               value="{{ old('nombre') }}"
                               placeholder="Ej: Buenos Aires"
                               class="w-full text-xs rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm">
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1"/>
                    </div>
                    <button type="submit" title="Registrar provincia"
                            class="mt-[18px] bg-blue-600 hover:bg-blue-700 text-white font-bold text-[9px] py-1.5 px-3 rounded uppercase tracking-wider transition-all shadow-sm shrink-0">
                        Agregar
                    </button>
                </form>

                <!-- Grilla de provincias -->
                <div class="max-h-[560px] overflow-y-auto">
                    <table class="w-full text-left border-collapse font-sans text-xs">
                        <thead class="sticky top-0 z-10">
                            <tr class="bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300 dark:border-slate-700">
                                <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700 w-14 text-center">ID</th>
                                <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700">Provincia</th>
                                <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700 text-center w-16">Loc.</th>
                                <th class="py-1.5 px-3 text-center w-24">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                            @forelse($provincias as $prov)
                                <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-700/40 bg-white dark:bg-slate-800 transition-colors">
                                    <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-center font-mono tabular-nums text-slate-500 dark:text-slate-400">
                                        #{{ $prov->id }}
                                    </td>
                                    <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-semibold text-slate-800 dark:text-slate-200" title="{{ $prov->nombre }}">
                                        <span class="block truncate max-w-[180px]">{{ $prov->nombre }}</span>
                                    </td>
                                    <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-center font-mono tabular-nums text-slate-600 dark:text-slate-300">
                                        {{ $prov->localidades_count }}
                                    </td>
                                    <td class="py-1.5 px-3">
                                        <div class="flex items-center justify-center space-x-1">
                                            <button type="button" title="Editar provincia"
                                                    @click="$dispatch('open-modal', 'modal-provincia-{{ $prov->id }}')"
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[9px] py-1 px-2 rounded uppercase transition-all shadow-sm">
                                                Editar
                                            </button>
                                            <form action="{{ route('sistema.ubicaciones.provincias.destroy', $prov->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('ATENCIÓN: Se eliminará la provincia «{{ $prov->nombre }}». ¿Desea continuar?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar provincia"
                                                        class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-[9px] py-1 px-2 rounded uppercase transition-all shadow-sm">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 px-3 text-center bg-white dark:bg-slate-800">
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium italic">No hay provincias cargadas.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ══════════ COLUMNA DERECHA (60%): LOCALIDADES ══════════ -->
        <div class="w-full lg:w-[60%]">

            <!-- Filtros + Alta -->
            <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm overflow-hidden mb-4">
                <div class="bg-slate-100 dark:bg-slate-900 border-b border-slate-300 dark:border-slate-700 px-3 py-2 flex justify-between items-center">
                    <h2 class="font-bold text-[10px] text-slate-700 dark:text-slate-300 uppercase tracking-wider">Catálogo de Localidades</h2>
                    <span class="bg-slate-300 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[9px] px-1.5 py-0.5 rounded font-mono tabular-nums">
                        {{ $localidades->total() }}
                    </span>
                </div>

                <form action="{{ route('sistema.ubicaciones.index') }}" method="GET" autocomplete="off"
                      class="p-2.5 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-end gap-2">
                    <div class="flex-1 min-w-[150px]">
                        <label for="search" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Buscar</label>
                        <input id="search" name="search" type="text" value="{{ request('search') }}"
                               placeholder="Nombre o código postal..."
                               class="w-full text-xs rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm">
                    </div>
                    <div class="w-full sm:w-56">
                        <label for="id_provincia" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Provincia</label>
                        <select id="id_provincia" name="id_provincia"
                                class="w-full text-xs rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm">
                            <option value="">Todas las provincias</option>
                            @foreach($provincias as $prov)
                                <option value="{{ $prov->id }}" @selected((string) request('id_provincia') === (string) $prov->id)>
                                    {{ $prov->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[9px] py-1.5 px-3 rounded uppercase tracking-wider transition-all shadow-sm">
                        Filtrar
                    </button>
                    @if(request()->filled('search') || request()->filled('id_provincia'))
                        <a href="{{ route('sistema.ubicaciones.index') }}" title="Quitar filtros"
                           class="text-[10px] text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-200 font-black uppercase px-1 py-1.5">✕ Quitar filtro</a>
                    @endif
                </form>

                <form action="{{ route('sistema.ubicaciones.localidades.store') }}" method="POST" autocomplete="off"
                      class="p-2.5 bg-slate-50/70 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-end gap-2">
                    @csrf
                    <div class="flex-1 min-w-[150px]">
                        <label for="loc_nombre" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Nueva Localidad *</label>
                        <input id="loc_nombre" name="nombre" type="text" maxlength="191" required
                               value="{{ old('nombre') }}"
                               placeholder="Ej: La Plata"
                               class="w-full text-xs rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm">
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1"/>
                    </div>
                    <div class="w-full sm:w-48">
                        <label for="loc_provincia" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Provincia *</label>
                        <select id="loc_provincia" name="id_provincia" required
                                class="w-full text-xs rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm">
                            <option value="">Seleccione...</option>
                            @foreach($provincias as $prov)
                                <option value="{{ $prov->id }}" @selected((string) old('id_provincia') === (string) $prov->id)>
                                    {{ $prov->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id_provincia')" class="mt-1"/>
                    </div>
                    <div class="w-full sm:w-32">
                        <label for="loc_cp" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">C. Postal</label>
                        <input id="loc_cp" name="codigo_postal" type="text" maxlength="10"
                               value="{{ old('codigo_postal') }}"
                               placeholder="1900"
                               class="w-full text-xs font-mono tabular-nums rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 shadow-sm">
                        <x-input-error :messages="$errors->get('codigo_postal')" class="mt-1"/>
                    </div>
                    <button type="submit" title="Registrar localidad"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[9px] py-1.5 px-3 rounded uppercase tracking-wider transition-all shadow-sm shrink-0">
                        Agregar
                    </button>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse font-sans text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300 dark:border-slate-700">
                                <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700 w-14 text-center">ID</th>
                                <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700">Localidad</th>
                                <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700 w-40">Provincia</th>
                                <th class="py-1.5 px-3 border-r border-slate-200 dark:border-slate-700 text-center w-28">C. Postal</th>
                                <th class="py-1.5 px-3 text-center w-32">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                            @forelse($localidades as $loc)
                                <tr class="hover:bg-blue-50/40 dark:hover:bg-slate-700/40 bg-white dark:bg-slate-800 transition-colors">
                                    <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-center font-mono tabular-nums text-slate-500 dark:text-slate-400">
                                        #{{ $loc->id }}
                                    </td>
                                    <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 font-semibold text-slate-800 dark:text-slate-200" title="{{ $loc->nombre }}">
                                        <span class="block truncate max-w-[200px]">{{ $loc->nombre }}</span>
                                    </td>
                                    <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-slate-600 dark:text-slate-300" title="{{ $loc->provincia->nombre ?? '—' }}">
                                        <span class="block truncate max-w-[150px]">{{ $loc->provincia->nombre ?? '—' }}</span>
                                    </td>
                                    <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60 text-center font-mono tabular-nums text-slate-600 dark:text-slate-300">
                                        {{ $loc->codigo_postal ?? '—' }}
                                    </td>
                                    <td class="py-1.5 px-3">
                                        <div class="flex items-center justify-center space-x-1.5">
                                            <button type="button" title="Editar localidad"
                                                    @click="$dispatch('open-modal', 'modal-localidad-{{ $loc->id }}')"
                                                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[9px] py-1 px-2 rounded uppercase transition-all shadow-sm">
                                                Editar
                                            </button>
                                            <form action="{{ route('sistema.ubicaciones.localidades.destroy', $loc->id) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('ATENCIÓN: Se eliminará la localidad «{{ $loc->nombre }}». ¿Desea continuar?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar localidad"
                                                        class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-[9px] py-1 px-2 rounded uppercase transition-all shadow-sm">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 px-3 text-center bg-white dark:bg-slate-800">
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-medium italic">No hay localidades para los filtros aplicados.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-2 py-1 bg-slate-50 dark:bg-slate-800/80 border-t border-slate-200 dark:border-slate-700 text-xs">
                    {{ $localidades->links() }}
                </div>

                @foreach($provincias as $prov)
                    <x-modal name="modal-provincia-{{ $prov->id }}" maxWidth="sm">
                        <form action="{{ route('sistema.ubicaciones.provincias.update', $prov->id) }}" method="POST" autocomplete="off" class="bg-white dark:bg-slate-800">
                            @csrf
                            @method('PUT')
                            <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900">
                                <h3 class="font-bold text-[10px] text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                    Editar Provincia <span class="font-mono text-slate-500 dark:text-slate-400">#{{ $prov->id }}</span>
                                </h3>
                            </div>
                            <div class="px-5 py-4 space-y-3 bg-white dark:bg-slate-800">
                                <div>
                                    <label for="prov_nombre_{{ $prov->id }}" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Nombre *</label>
                                    <input id="prov_nombre_{{ $prov->id }}" name="nombre" type="text" maxlength="100" required
                                           value="{{ old('nombre', $prov->nombre) }}"
                                           class="w-full text-xs rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm">
                                    <x-input-error :messages="$errors->get('nombre')" class="mt-1"/>
                                </div>
                                <p class="text-[9px] text-slate-400 dark:text-slate-500 leading-tight">
                                    Localidades asociadas: <span class="font-mono tabular-nums font-bold text-slate-600 dark:text-slate-300">{{ $prov->localidades_count }}</span>
                                </p>
                            </div>
                            <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-2">
                                <button type="button" @click="$dispatch('close')"
                                        class="bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-[9px] py-1.5 px-3 rounded uppercase border border-slate-300 dark:border-slate-600 transition-all shadow-sm">
                                    Cancelar
                                </button>
                                <button type="submit"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[9px] py-1.5 px-3 rounded uppercase transition-all shadow-sm">
                                    Guardar
                                </button>
                            </div>
                        </form>
                    </x-modal>
                @endforeach

                @foreach($localidades as $loc)
                    <x-modal name="modal-localidad-{{ $loc->id }}" maxWidth="sm">
                        <form action="{{ route('sistema.ubicaciones.localidades.update', $loc->id) }}" method="POST" autocomplete="off" class="bg-white dark:bg-slate-800">
                            @csrf
                            @method('PUT')
                            <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-900">
                                <h3 class="font-bold text-[10px] text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                                    Editar Localidad <span class="font-mono text-slate-500 dark:text-slate-400">#{{ $loc->id }}</span>
                                </h3>
                            </div>
                            <div class="px-5 py-4 space-y-3 bg-white dark:bg-slate-800">
                                <div>
                                    <label for="loc_edit_nombre_{{ $loc->id }}" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Nombre *</label>
                                    <input id="loc_edit_nombre_{{ $loc->id }}" name="nombre" type="text" maxlength="191" required
                                           value="{{ old('nombre', $loc->nombre) }}"
                                           class="w-full text-xs rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm">
                                    <x-input-error :messages="$errors->get('nombre')" class="mt-1"/>
                                </div>
                                <div>
                                    <label for="loc_edit_prov_{{ $loc->id }}" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Provincia *</label>
                                    <select id="loc_edit_prov_{{ $loc->id }}" name="id_provincia" required
                                            class="w-full text-xs rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm">
                                        @foreach($provincias as $prov)
                                            <option value="{{ $prov->id }}" @selected((string) old('id_provincia', $loc->id_provincia) === (string) $prov->id)>
                                                {{ $prov->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('id_provincia')" class="mt-1"/>
                                </div>
                                <div>
                                    <label for="loc_edit_cp_{{ $loc->id }}" class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5">Código Postal</label>
                                    <input id="loc_edit_cp_{{ $loc->id }}" name="codigo_postal" type="text" maxlength="10"
                                           value="{{ old('codigo_postal', $loc->codigo_postal) }}"
                                           class="w-full text-xs font-mono tabular-nums rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm">
                                    <x-input-error :messages="$errors->get('codigo_postal')" class="mt-1"/>
                                </div>
                            </div>
                            <div class="px-5 py-3 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-2">
                                <button type="button" @click="$dispatch('close')"
                                        class="bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-[9px] py-1.5 px-3 rounded uppercase border border-slate-300 dark:border-slate-600 transition-all shadow-sm">
                                    Cancelar
                                </button>
                                <button type="submit"
                                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[9px] py-1.5 px-3 rounded uppercase transition-all shadow-sm">
                                    Guardar
                                </button>
                            </div>
                        </form>
                    </x-modal>
                @endforeach
            </div>
        </div>

    </div>
</x-app-layout>
