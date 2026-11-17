<!-- 1. DATOS IDENTIFICATORIOS Y SUCURSAL -->
<div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden mb-3">
    <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5 flex justify-between items-center">
        <h3 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">1. Datos Identificatorios y Sucursal</h3>
        <span class="text-[9px] text-blue-700 font-semibold">Tierra del Fuego (Cta. Cte. / CUIT) &bull; Chubut (DNI / CBU)</span>
    </div>
    <div class="p-3 grid grid-cols-1 md:grid-cols-4 gap-3">
        <!-- DNI -->
        <div class="md:col-span-1">
            <label for="dni" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">DNI (Sin puntos) <span class="text-rose-600">*</span></label>
            <input type="number" id="dni" name="dni" value="{{ old('dni', $cliente->dni ?? '') }}" required 
                   placeholder="Ej: 30123456"
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono font-bold" />
            <x-input-error :messages="$errors->get('dni')" class="mt-1 text-[10px]" />
        </div>

        <!-- CUIT / CUIL -->
        <div class="md:col-span-1">
            <label for="cuit" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">CUIT / CUIL (Facturación AFIP)</label>
            <input type="text" id="cuit" name="cuit" value="{{ old('cuit', $cliente->cuit ?? '') }}" 
                   placeholder="Ej: 20-30123456-7"
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono" />
            <x-input-error :messages="$errors->get('cuit')" class="mt-1 text-[10px]" />
        </div>

        <!-- Nro. Cuenta / Legajo Ushuaia -->
        <div class="md:col-span-1">
            <label for="nro_cuenta" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Nro. de Cuenta / Legajo (Cta. Cte.)</label>
            <input type="text" id="nro_cuenta" name="nro_cuenta" value="{{ old('nro_cuenta', $cliente->nro_cuenta ?? '') }}" 
                   placeholder="Ej: USM-1045 o 1045"
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono" />
            <x-input-error :messages="$errors->get('nro_cuenta')" class="mt-1 text-[10px]" />
        </div>

        <!-- Sucursal de Alta -->
        <div class="md:col-span-1">
            <label for="id_sucursal" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Sucursal de Alta <span class="text-rose-600">*</span></label>
            <select name="id_sucursal" id="id_sucursal" required class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-semibold">
                @foreach($sucursales as $sucursal)
                    <option value="{{ $sucursal->id }}" {{ old('id_sucursal', $cliente->id_sucursal ?? config('app.sucursal_activa')->id ?? 1) == $sucursal->id ? 'selected' : '' }}>
                        {{ $sucursal->nombre }} ({{ $sucursal->codigo_sucursal }})
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('id_sucursal')" class="mt-1 text-[10px]" />
        </div>

        <!-- Nombre Completo -->
        <div class="md:col-span-4">
            <label for="nombre" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Nombre y Apellido Completo / Razón Social <span class="text-rose-600">*</span></label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $cliente->nombre ?? '') }}" required 
                   placeholder="Ej: PEREZ JUAN CARLOS"
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm uppercase font-semibold" />
            <x-input-error :messages="$errors->get('nombre')" class="mt-1 text-[10px]" />
        </div>
    </div>
</div>

<!-- 2. UBICACIÓN Y CONTACTO -->
<div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden mb-3">
    <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5">
        <h3 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">2. Ubicación y Contacto Particular</h3>
    </div>
    <div class="p-3 grid grid-cols-1 md:grid-cols-4 gap-3">
        <!-- Localidad agrupada por Provincia -->
        <div class="md:col-span-1">
            <label for="id_localidad" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Localidad <span class="text-rose-600">*</span></label>
            <select name="id_localidad" id="id_localidad" required class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-semibold">
                <option value="">Seleccione localidad...</option>
                @if(isset($provincias) && $provincias->count() > 0)
                    @foreach($provincias as $prov)
                        <optgroup label="Provincia de {{ $prov->nombre }}">
                            @foreach($prov->localidades as $loc)
                                <option value="{{ $loc->id }}" {{ old('id_localidad', $cliente->id_localidad ?? '') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->nombre }} (CP: {{ $loc->codigo_postal ?? 'S/CP' }})
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                @else
                    @foreach($localidades as $localidad)
                        <option value="{{ $localidad->id }}" {{ old('id_localidad', $cliente->id_localidad ?? '') == $localidad->id ? 'selected' : '' }}>
                            {{ $localidad->nombre }}
                        </option>
                    @endforeach
                @endif
            </select>
            <x-input-error :messages="$errors->get('id_localidad')" class="mt-1 text-[10px]" />
        </div>

        <div class="md:col-span-3">
            <label for="direccion" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Dirección Particular <span class="text-rose-600">*</span></label>
            <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $cliente->direccion ?? '') }}" required 
                   placeholder="Calle, Número, Barrio, Dpto."
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm uppercase" />
            <x-input-error :messages="$errors->get('direccion')" class="mt-1 text-[10px]" />
        </div>

        <div class="md:col-span-1">
            <label for="tel1" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Tel. Principal (Celular) <span class="text-rose-600">*</span></label>
            <input type="text" id="tel1" name="tel1" value="{{ old('tel1', $cliente->tel1 ?? '') }}" required 
                   placeholder="Ej: 2964-123456 / 280-123456"
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono" />
            <x-input-error :messages="$errors->get('tel1')" class="mt-1 text-[10px]" />
        </div>

        <div class="md:col-span-1">
            <label for="tel2" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Tel. Alternativo</label>
            <input type="text" id="tel2" name="tel2" value="{{ old('tel2', $cliente->tel2 ?? '') }}" 
                   placeholder="Ej: Fijo o Familiar"
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono" />
            <x-input-error :messages="$errors->get('tel2')" class="mt-1 text-[10px]" />
        </div>

        <div class="md:col-span-2">
            <label for="email" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Email (Opcional)</label>
            <input type="email" id="email" name="email" value="{{ old('email', $cliente->email ?? '') }}" 
                   placeholder="correo@ejemplo.com"
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm" />
            <x-input-error :messages="$errors->get('email')" class="mt-1 text-[10px]" />
        </div>
    </div>
</div>

<!-- 3. INFORMACIÓN COMERCIAL, FINANCIERA Y BANCARIA -->
<div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden mb-3">
    <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5 flex justify-between items-center">
        <h3 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">3. Condiciones Comerciales y Débito Bancario</h3>
        <span class="text-[9px] text-slate-500">Límites, cuotas y modalidad de cobranza</span>
    </div>
    <div class="p-3 grid grid-cols-1 md:grid-cols-5 gap-3">
        <!-- Medio de Pago Preferido -->
        <div class="md:col-span-1">
            <label for="medio_pago_preferido" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Cobranza Preferida <span class="text-rose-600">*</span></label>
            <select name="medio_pago_preferido" id="medio_pago_preferido" class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-semibold">
                <option value="0" {{ old('medio_pago_preferido', $cliente->medio_pago_preferido ?? 0) == 0 ? 'selected' : '' }}>Sucursal (Cta. Cte. / Efectivo)</option>
                <option value="1" {{ old('medio_pago_preferido', $cliente->medio_pago_preferido ?? 1) == 1 ? 'selected' : '' }}>Débito CBU (Banco)</option>
            </select>
        </div>

        <!-- CBU Bancario (Chubut / Banco) -->
        <div class="md:col-span-2">
            <label for="cbu" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">
                CBU Bancario (22 dígitos para Débito)
            </label>
            <input type="text" id="cbu" name="cbu" value="{{ old('cbu', $cliente->cbu ?? '') }}" placeholder="0830001001000012345678" 
                   maxlength="22"
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono font-bold" />
            <p class="text-[8px] text-slate-400 mt-0.5 leading-tight">Obligatorio si se elige Débito en CBU (Banco del Chubut u otros).</p>
            <x-input-error :messages="$errors->get('cbu')" class="mt-1 text-[10px]" />
        </div>

        <!-- Límite Otorgado -->
        <div class="md:col-span-1">
            <label for="limite" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Límite Otorgado ($) <span class="text-rose-600">*</span></label>
            <input type="number" step="0.01" id="limite" name="limite" value="{{ old('limite', $cliente->limite ?? '800000') }}" required 
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono font-black text-blue-900 bg-blue-50/70" />
            <x-input-error :messages="$errors->get('limite')" class="mt-1 text-[10px]" />
        </div>

        <!-- Máx. Cuotas -->
        <div class="md:col-span-1">
            <label for="max_cuotas" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Máx. Cuotas <span class="text-rose-600">*</span></label>
            <select name="max_cuotas" id="max_cuotas" class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-semibold">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ old('max_cuotas', $cliente->max_cuotas ?? 4) == $i ? 'selected' : '' }}>
                        {{ $i }} {{ $i === 1 ? 'Cuota' : 'Cuotas' }}
                    </option>
                @endfor
            </select>
            <x-input-error :messages="$errors->get('max_cuotas')" class="mt-1 text-[10px]" />
        </div>

        <!-- Lugar de Trabajo -->
        <div class="md:col-span-2 border-t border-slate-100 pt-2">
            <label for="trabajo" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Lugar de Trabajo / Empleador</label>
            <input type="text" id="trabajo" name="trabajo" value="{{ old('trabajo', $cliente->trabajo ?? '') }}" 
                   placeholder="Ej: Municipalidad, Gobierno, Comercio, etc."
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm uppercase" />
        </div>

        <!-- Dirección Laboral -->
        <div class="md:col-span-1 border-t border-slate-100 pt-2">
            <label for="direccion_trabajo" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Dirección Laboral</label>
            <input type="text" id="direccion_trabajo" name="direccion_trabajo" value="{{ old('direccion_trabajo', $cliente->direccion_trabajo ?? '') }}" 
                   class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm uppercase" />
        </div>

        <!-- Loc. Laboral -->
        <div class="md:col-span-1 border-t border-slate-100 pt-2">
            <label for="id_localidad_trabajo" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Loc. Laboral</label>
            <select name="id_localidad_trabajo" id="id_localidad_trabajo" class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm">
                <option value="">Seleccione...</option>
                @if(isset($provincias) && $provincias->count() > 0)
                    @foreach($provincias as $prov)
                        <optgroup label="{{ $prov->nombre }}">
                            @foreach($prov->localidades as $loc)
                                <option value="{{ $loc->id }}" {{ old('id_localidad_trabajo', $cliente->id_localidad_trabajo ?? '') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->nombre }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                @else
                    @foreach($localidades as $localidad)
                        <option value="{{ $localidad->id }}" {{ old('id_localidad_trabajo', $cliente->id_localidad_trabajo ?? '') == $localidad->id ? 'selected' : '' }}>
                            {{ $localidad->nombre }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <!-- Tel. Laboral y Tipo Haber -->
        <div class="md:col-span-1 border-t border-slate-100 pt-2 flex gap-2">
            <div class="w-2/3">
                <label for="tel_trabajo" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Tel. Laboral</label>
                <input type="text" id="tel_trabajo" name="tel_trabajo" value="{{ old('tel_trabajo', $cliente->tel_trabajo ?? '') }}" 
                       class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono" />
            </div>
            <div class="w-1/3">
                <label for="tipo_haber" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Haber ID</label>
                <input type="number" id="tipo_haber" name="tipo_haber" min="1" step="1" value="{{ old('tipo_haber', $cliente->tipo_haber ?? 1) }}" 
                       class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm font-mono text-center" />
            </div>
        </div>
    </div>
</div>

<!-- 4. FAMILIAR Y ESTADO DEL CLIENTE -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
    <!-- Referencia Familiar -->
    <div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden">
        <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5">
            <h3 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">4. Referencia Familiar / Contacto</h3>
        </div>
        <div class="p-3 grid grid-cols-2 gap-3">
            <div class="col-span-2 flex space-x-2">
                <div class="flex-grow">
                    <label for="familiar" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Nombre Familiar</label>
                    <input type="text" id="familiar" name="familiar" value="{{ old('familiar', $cliente->familiar ?? '') }}" 
                           placeholder="Ej: Maria Lopez"
                           class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 shadow-sm uppercase" />
                </div>
                <div class="w-28">
                    <label for="tipo_familiar" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Parentesco</label>
                    <input type="text" id="tipo_familiar" name="tipo_familiar" value="{{ old('tipo_familiar', $cliente->tipo_familiar ?? '') }}" 
                           placeholder="Ej: Cónyuge"
                           class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 shadow-sm" />
                </div>
            </div>
            <div>
                <label for="id_localidad_familiar" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Localidad</label>
                <select name="id_localidad_familiar" id="id_localidad_familiar" class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 shadow-sm">
                    <option value="">Seleccione...</option>
                    @foreach($localidades as $localidad)
                        <option value="{{ $localidad->id }}" {{ old('id_localidad_familiar', $cliente->id_localidad_familiar ?? '') == $localidad->id ? 'selected' : '' }}>{{ $localidad->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tel_familiar" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Teléfono</label>
                <input type="text" id="tel_familiar" name="tel_familiar" value="{{ old('tel_familiar', $cliente->tel_familiar ?? '') }}" class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 shadow-sm font-mono" />
            </div>
            <div class="col-span-2">
                <label for="direccion_familiar" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Dirección</label>
                <input type="text" id="direccion_familiar" name="direccion_familiar" value="{{ old('direccion_familiar', $cliente->direccion_familiar ?? '') }}" class="block w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 shadow-sm uppercase" />
            </div>
        </div>
    </div>

    <!-- Observaciones y Estado -->
    <div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden flex flex-col">
        <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5">
            <h3 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">5. Observaciones y Estado Operativo</h3>
        </div>
        <div class="p-3 flex-grow flex flex-col justify-between">
            <div>
                <label for="obs" class="block text-[9px] font-bold text-slate-500 uppercase mb-0.5">Observaciones Generales</label>
                <textarea id="obs" name="obs" rows="3" placeholder="Información crediticia adicional, notas de contacto o acuerdos especiales..." class="w-full text-xs py-1.5 px-2 rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm resize-none">{{ old('obs', $cliente->obs ?? '') }}</textarea>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100">
                <label for="suspendido" class="flex items-center cursor-pointer p-2.5 bg-slate-50 rounded-lg border border-slate-200 hover:bg-slate-100 transition-colors">
                    <input type="hidden" name="suspendido" value="0">
                    <input type="checkbox" id="suspendido" name="suspendido" value="1" 
                        class="rounded border-slate-300 text-rose-600 focus:ring-rose-500 h-4 w-4"
                        {{ old('suspendido', $cliente->suspendido ?? 0) == 1 ? 'checked' : '' }}>
                    <div class="ml-2.5">
                        <span class="text-xs font-black text-rose-900 uppercase block">Suspender para Créditos</span>
                        <span class="text-[9px] text-slate-500 block leading-tight">Si está marcado, no se podrán autorizar nuevas compras a crédito ni emitir pagarés.</span>
                    </div>
                </label>
            </div>
        </div>
    </div>
</div>