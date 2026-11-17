<x-app-layout>
    <!-- Header Compacto -->
    <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('sistema.sucursales.index') }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                <span>Editar Sucursal</span>
                <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700 font-mono tabular-nums">
                    #{{ $sucursal->id }} · {{ $sucursal->codigo_sucursal }}
                </span>
            </h1>
        </div>
    </div>

    <form action="{{ route('sistema.sucursales.update', $sucursal->id) }}" method="POST" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="w-full lg:w-4/5 space-y-4">

            <!-- PANEL 1: Datos de la Sucursal -->
            <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="bg-slate-900 text-white px-3.5 py-2 flex items-center justify-between">
                    <h2 class="font-bold text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                        <span>🏢</span>
                        <span>1. Identificación de la Sucursal</span>
                    </h2>
                    <span class="text-[9px] text-slate-400 font-mono">ID: #{{ $sucursal->id }}</span>
                </div>

                <div class="p-3.5 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-6">
                            <label for="nombre" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Nombre de la Sucursal *</label>
                            <input id="nombre" name="nombre" type="text" maxlength="100" required
                                   value="{{ old('nombre', $sucursal->nombre) }}"
                                   class="w-full text-xs font-semibold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('nombre')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="codigo_sucursal" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Código *</label>
                            <input id="codigo_sucursal" name="codigo_sucursal" type="text" maxlength="10" required
                                   value="{{ old('codigo_sucursal', $sucursal->codigo_sucursal) }}"
                                   class="w-full text-xs font-mono font-black uppercase rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm text-center">
                            <x-input-error :messages="$errors->get('codigo_sucursal')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="id_provincia" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Provincia</label>
                            <select id="id_provincia" name="id_provincia"
                                    class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                                <option value="">-- Seleccionar --</option>
                                @foreach($provincias ?? [] as $prov)
                                    <option value="{{ $prov->id }}" {{ old('id_provincia', $sucursal->id_provincia) == $prov->id ? 'selected' : '' }}>
                                        {{ $prov->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('id_provincia')" class="mt-1"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 2: Ubicación Física y Canales de Contacto (Impresión en Factura) -->
            <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="bg-slate-800 text-white px-3.5 py-2 flex items-center justify-between">
                    <h2 class="font-bold text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                        <span>📍</span>
                        <span>2. Ubicación Física y Canales de Contacto (Salen en la Factura)</span>
                    </h2>
                    <span class="text-[9px] text-slate-300">Dirección y contacto de mostrador</span>
                </div>

                <div class="p-3.5 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-6">
                            <label for="direccion" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Dirección Física de la Sucursal *</label>
                            <input id="direccion" name="direccion" type="text" maxlength="191" required
                                   value="{{ old('direccion', $sucursal->direccion) }}"
                                   placeholder="Ej: Rivadavia 249, Trelew, Chubut"
                                   class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('direccion')" class="mt-1"/>
                            <p class="text-[9px] text-slate-400 mt-0.5">Esta es la dirección física que se imprime en los comprobantes y facturas.</p>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="telefono" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Teléfono / Celular de Contacto</label>
                            <input id="telefono" name="telefono" type="text" maxlength="50"
                                   value="{{ old('telefono', $sucursal->telefono) }}"
                                   placeholder="Ej: 0280 442-0000 / 280 4123456"
                                   class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('telefono')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="email" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Email de Contacto</label>
                            <input id="email" name="email" type="email" maxlength="100"
                                   value="{{ old('email', $sucursal->email) }}"
                                   placeholder="contacto@tiendaslamarina.com.ar"
                                   class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('email')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-6">
                            <label for="pagina_web" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Página Web</label>
                            <input id="pagina_web" name="pagina_web" type="text" maxlength="150"
                                   value="{{ old('pagina_web', $sucursal->pagina_web ?? 'www.tiendaslamarina.com.ar') }}"
                                   placeholder="www.tiendaslamarina.com.ar"
                                   class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('pagina_web')" class="mt-1"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 3: Datos Fiscales de Facturación (Membrete Fiscal) -->
            <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="bg-indigo-900 text-white px-3.5 py-2 flex items-center justify-between">
                    <h2 class="font-bold text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                        <span>🧾</span>
                        <span>3. Datos Fiscales de Facturación (Responsable Fiscal)</span>
                    </h2>
                    <span class="text-[9px] text-indigo-200">Emisor legal de comprobantes</span>
                </div>

                <div class="p-3.5 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-4">
                            <label for="nombre_fantasia" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Nombre de Fantasía *</label>
                            <input id="nombre_fantasia" name="nombre_fantasia" type="text" maxlength="100" required
                                   value="{{ old('nombre_fantasia', $sucursal->nombre_fantasia ?? 'La Marina') }}"
                                   class="w-full text-xs font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('nombre_fantasia')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-8">
                            <label for="razon_social" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Razón Social / Responsable Fiscal *</label>
                            <input id="razon_social" name="razon_social" type="text" maxlength="150" required
                                   value="{{ old('razon_social', $sucursal->razon_social) }}"
                                   placeholder="Ej: Tiendas la Marina SAS o BARZINI GUILLERMO ENRIQUE"
                                   class="w-full text-xs font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('razon_social')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="cuit" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">CUIT *</label>
                            <input id="cuit" name="cuit" type="text" maxlength="25" required
                                   value="{{ old('cuit', $sucursal->cuit) }}"
                                   placeholder="30-71699602-2"
                                   class="w-full text-xs font-mono font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('cuit')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="condicion_iva" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Condición de IVA *</label>
                            <input id="condicion_iva" name="condicion_iva" type="text" maxlength="100" required
                                   value="{{ old('condicion_iva', $sucursal->condicion_iva ?? 'IVA Responsable Inscripto') }}"
                                   class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('condicion_iva')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="ingresos_brutos" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Ingresos Brutos (IIBB)</label>
                            <input id="ingresos_brutos" name="ingresos_brutos" type="text" maxlength="100"
                                   value="{{ old('ingresos_brutos', $sucursal->ingresos_brutos) }}"
                                   placeholder="Ej: 30-71699602-2"
                                   class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('ingresos_brutos')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-8">
                            <label for="domicilio_fiscal" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Domicilio Fiscal</label>
                            <input id="domicilio_fiscal" name="domicilio_fiscal" type="text" maxlength="191"
                                   value="{{ old('domicilio_fiscal', $sucursal->domicilio_fiscal) }}"
                                   placeholder="Ej: Rivadavia 249 - Trelew - Chubut"
                                   class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('domicilio_fiscal')" class="mt-1"/>
                        </div>

                        <div class="sm:col-span-4">
                            <label for="inicio_actividades" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Inicio de Actividades</label>
                            <input id="inicio_actividades" name="inicio_actividades" type="text" maxlength="30"
                                   value="{{ old('inicio_actividades', $sucursal->inicio_actividades) }}"
                                   placeholder="DD/MM/AAAA"
                                   class="w-full text-xs font-mono rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('inicio_actividades')" class="mt-1"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end space-x-2 pt-2">
                <a href="{{ route('sistema.sucursales.index') }}"
                   class="bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-2 px-4 rounded-xl uppercase tracking-wider border border-slate-300 dark:border-slate-600 transition-all shadow-sm">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2 px-6 rounded-xl uppercase tracking-wider transition-all shadow-sm">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
