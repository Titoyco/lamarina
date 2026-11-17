<x-app-layout>
    @php
        $isEdit = isset($proveedor);
    @endphp

    <div class="max-w-3xl mx-auto py-2">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>{{ $isEdit ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</span>
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Datos fiscales, impositivos y vías de contacto del proveedor.
                </p>
            </div>

            <a href="{{ route('proveedores.index') }}" 
               class="inline-flex items-center gap-1 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 shadow-2xs transition-all">
                &larr; Volver
            </a>
        </div>

        <form action="{{ $isEdit ? route('proveedores.update', $proveedor->id) : route('proveedores.store') }}" 
              method="POST" 
              class="space-y-5">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-2xs space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                            <span>Legajo / Código (3 dígitos)</span>
                            <span class="text-[10px] text-blue-600 dark:text-blue-400 font-normal">Base Barcode</span>
                        </label>
                        <input type="text" name="legajo" maxlength="10"
                               value="{{ old('legajo', $proveedor->legajo ?? ($siguienteLegajo ?? '')) }}" 
                               placeholder="Ej: 043"
                               class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold tracking-widest focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-[10px] text-slate-400 mt-1">Identificador para los primeros 3 dígitos del código de barras.</p>
                        @error('legajo') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Razón Social / Nombre <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nombre" required 
                               value="{{ old('nombre', $proveedor->nombre ?? '') }}" 
                               placeholder="Ej: DISTRIBUIDORA PATAGONICA S.A."
                               class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white uppercase font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('nombre') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            CUIT / CUIL (11 dígitos)
                        </label>
                        <input type="text" name="cuit" 
                               value="{{ old('cuit', $proveedor->cuit ?? '') }}" 
                               placeholder="Ej: 30711223344"
                               class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('cuit') <p class="text-rose-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Condición frente al IVA
                        </label>
                        <select name="id_iva" class="w-full text-xs py-2 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            <option value="">-- Seleccionar --</option>
                            @foreach($condicionesIva as $iva)
                                <option value="{{ $iva->id }}" {{ old('id_iva', $proveedor->id_iva ?? '') == $iva->id ? 'selected' : '' }}>
                                    {{ $iva->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nombre de Contacto / Vendedor</label>
                        <input type="text" name="contacto" 
                               value="{{ old('contacto', $proveedor->contacto ?? '') }}" 
                               placeholder="Ej: Juan Pérez"
                               class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Localidad</label>
                        <select name="id_localidad" class="w-full text-xs py-2 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            <option value="">-- Seleccionar Localidad --</option>
                            @foreach($localidades as $loc)
                                <option value="{{ $loc->id }}" {{ old('id_localidad', $proveedor->id_localidad ?? '') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->nombre }} ({{ $loc->provincia->nombre ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Dirección Fiscal / Depósito</label>
                        <input type="text" name="direccion" 
                               value="{{ old('direccion', $proveedor->direccion ?? '') }}" 
                               placeholder="Ej: Av. San Martín 1540"
                               class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Teléfono Principal</label>
                        <input type="text" name="tel1" 
                               value="{{ old('tel1', $proveedor->tel1 ?? '') }}" 
                               placeholder="Ej: 2901-445566"
                               class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email de Pedidos</label>
                        <input type="email" name="email" 
                               value="{{ old('email', $proveedor->email ?? '') }}" 
                               placeholder="ventas@proveedor.com"
                               class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Observaciones / Rubro</label>
                        <textarea name="observaciones" rows="2" 
                                  placeholder="Condiciones comerciales, plazos de entrega o datos adicionales..." 
                                  class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">{{ old('observaciones', $proveedor->observaciones ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('proveedores.index') }}" 
                   class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ $isEdit ? 'Guardar Cambios' : 'Registrar Proveedor' }}</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
