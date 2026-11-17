<x-app-layout>
    <div class="max-w-3xl mx-auto py-5 px-3 sm:px-6 space-y-6">

        <!-- Header Superior -->
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('sistema.medios-pago.index') }}" 
                   class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-2 px-3 rounded-xl transition flex items-center gap-1.5 shadow-2xs">
                    <span>⬅️ Volver al Listado</span>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        {{ $isEdit ? 'Editar Medio de Pago' : 'Nuevo Medio de Pago' }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $isEdit ? "Modificación de parámetros y reglas para '{$medio->nombre}'" : 'Alta de una nueva forma de cobro en el sistema' }}
                    </p>
                </div>
            </div>
        </div>

        @if($isEdit && ($movsCount ?? 0) > 0)
            <div class="p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 rounded-2xl flex items-start gap-3 text-xs text-amber-900 dark:text-amber-200">
                <span class="text-xl">🔒</span>
                <div>
                    <strong class="font-black uppercase block tracking-tight">Medio de Pago con Historial Registrado</strong>
                    <p class="mt-0.5 text-amber-800 dark:text-amber-300 leading-relaxed">
                        Este medio de pago cuenta con <strong>{{ number_format($movsCount, 0, ',', '.') }} movimientos vinculados</strong> (ventas, cuotas o recibos). Para proteger la integridad contable y tributaria del histórico, la eliminación directa se encuentra deshabilitada. Puede modificar su nombre, recargos o desactivarlo cuando desee dejar de utilizarlo.
                    </p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-300 dark:border-rose-800 rounded-2xl text-xs text-rose-800 dark:text-rose-200 space-y-1">
                <strong class="font-black uppercase block">Corrija los siguientes errores:</strong>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" 
              action="{{ $isEdit ? route('sistema.medios-pago.update', $medio->id) : route('sistema.medios-pago.store') }}" 
              class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-5"
              x-data="{
                  nombre: '{{ old('nombre', $medio->nombre ?? '') }}',
                  codigo: '{{ old('codigo', $medio->codigo ?? '') }}',
                  tipoBase: '{{ old('tipo_base', $medio->tipo_base ?? 'otro') }}',
                  icono: '{{ old('icono', $medio->icono ?? '💳') }}',
                  color: '{{ old('color', $medio->color ?? 'indigo') }}',
                  generarCodigo() {
                      if (!this.codigo || '{{ !$isEdit }}' === '1') {
                          this.codigo = this.nombre.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
                      }
                  }
              }">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nombre del Medio de Pago -->
                <div>
                    <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                        Nombre Visible <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nombre" x-model="nombre" @input="generarCodigo()" required maxlength="100"
                           placeholder="Ej: Tarjeta Naranja, Mercado Pago QR..."
                           class="w-full text-xs py-2.5 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Nombre con el que figurará en el facturador, tickets y reportes.</span>
                </div>

                <!-- Código Identificador / Slug -->
                <div>
                    <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                        Código / Slug Único <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="codigo" x-model="codigo" required maxlength="50"
                           placeholder="ej: tarjeta_naranja"
                           class="w-full font-mono text-xs py-2.5 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <span class="text-[10px] text-slate-400 mt-1 block">Identificador interno en minúsculas sin espacios (ej: <code>mp_qr</code>).</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Categoría / Tipo Base -->
                <div>
                    <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                        Categoría Base Operativa <span class="text-rose-500">*</span>
                    </label>
                    <select name="tipo_base" x-model="tipoBase" required
                            class="w-full text-xs py-2.5 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="efectivo">💵 Efectivo Físico (Afecta Arqueo en Caja)</option>
                        <option value="tarjeta">💳 Tarjeta (Terminal POSNET / Débito / Crédito)</option>
                        <option value="credito">📑 Crédito Personal / Cuenta Corriente (Valida cupo)</option>
                        <option value="banco">📱 Transferencia Bancaria / Billetera Virtual / QR</option>
                        <option value="otro">📦 Otro Medio (Cheque, Vale, etc.)</option>
                    </select>
                    <span class="text-[10px] text-slate-400 mt-1 block">Define si suma al arqueo físico de caja o si genera cuotas automáticas.</span>
                </div>

                <!-- Orden de Visualización -->
                <div>
                    <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                        Orden en Pantalla
                    </label>
                    <input type="number" name="orden" value="{{ old('orden', $medio->orden ?? 0) }}" min="0" step="1"
                           class="w-full text-xs py-2.5 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right">
                    <span class="text-[10px] text-slate-400 mt-1 block">Número de posición en el facturador (menor número aparece primero).</span>
                </div>
            </div>

            <!-- Recargo y Descuento % -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                <div>
                    <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                        Recargo Financiero (%)
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" name="recargo_porcentaje" 
                               value="{{ old('recargo_porcentaje', $medio->recargo_porcentaje ?? 0) }}"
                               class="w-full font-mono text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right pr-7"
                               placeholder="0.00">
                        <span class="absolute right-3 top-2 text-slate-400 text-xs font-bold">%</span>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Porcentaje adicional que se suma automáticamente al facturar.</span>
                </div>

                <div>
                    <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                        Descuento (%)
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" name="descuento_porcentaje" 
                               value="{{ old('descuento_porcentaje', $medio->descuento_porcentaje ?? 0) }}"
                               class="w-full font-mono text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right pr-7"
                               placeholder="0.00">
                        <span class="absolute right-3 top-2 text-slate-400 text-xs font-bold">%</span>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Bonificación automática al elegir este medio de pago.</span>
                </div>
            </div>

            <!-- Icono y Color Visual -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                            Ícono Representativo
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="icono" x-model="icono" maxlength="50"
                                   class="w-20 text-center text-xl py-1 px-2 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            <div class="flex flex-wrap gap-1">
                                <template x-for="ic in ['💵', '💳', '📱', '📑', '🏦', '🏷️', '📦', '🪙']" :key="ic">
                                    <button type="button" @click="icono = ic" 
                                            class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center hover:bg-slate-200 text-sm transition"
                                            x-text="ic">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                            Color de Botón
                        </label>
                        <select name="color" x-model="color"
                                class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            <option value="emerald">Verde Esmeralda (Efectivo)</option>
                            <option value="cyan">Celeste / Cian (Débito)</option>
                            <option value="blue">Azul Intenso (Crédito)</option>
                            <option value="indigo">Índigo (Personal)</option>
                            <option value="teal">Verde Azulado (QR / MP)</option>
                            <option value="amber">Ámbar / Naranja</option>
                            <option value="purple">Púrpura / Violeta</option>
                            <option value="slate">Gris Neutro</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Opciones Avanzadas / Checkboxes -->
            <div class="pt-2 border-t border-slate-100 dark:border-slate-700/60 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 cursor-pointer">
                        <input type="checkbox" name="requiere_referencia" value="1" 
                               {{ old('requiere_referencia', $medio->requiere_referencia ?? false) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="text-xs font-black text-slate-800 dark:text-slate-200 block">Pide Comprobante</span>
                            <span class="text-[10px] text-slate-400 block">Exige número de cupón</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 cursor-pointer">
                        <input type="checkbox" name="permite_vuelto" value="1" 
                               {{ old('permite_vuelto', $medio->permite_vuelto ?? false) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="text-xs font-black text-slate-800 dark:text-slate-200 block">Permite Vuelto</span>
                            <span class="text-[10px] text-slate-400 block">Calculador de cambio</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 cursor-pointer">
                        <input type="checkbox" name="activo" value="1" 
                               {{ old('activo', $medio->activo ?? true) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <span class="text-xs font-black text-slate-800 dark:text-slate-200 block">Habilitado</span>
                            <span class="text-[10px] text-slate-400 block">Visible para cobrar</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('sistema.medios-pago.index') }}" 
                   class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-2.5 px-4 rounded-xl transition uppercase">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs py-2.5 px-6 rounded-xl transition uppercase shadow-sm">
                    {{ $isEdit ? 'Guardar Cambios' : 'Crear Medio de Pago' }}
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
