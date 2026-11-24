<x-app-layout>
    <!-- Header Compacto -->
    <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('sistema.puntos-venta.index') }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm" title="Volver al Listado">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                <span>Nuevo Punto de Venta</span>
                <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">Alta de Emisor Fiscal</span>
            </h1>
        </div>
    </div>

    <form action="{{ route('sistema.puntos-venta.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off"
          x-data="{ tipo: '{{ old('tipo_emision', 'fiscal_hasar') }}' }">
        @csrf

        <div class="w-full lg:w-4/5 space-y-4">

            <!-- PANEL 1: Datos Principales -->
            <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="bg-slate-900 text-white px-3.5 py-2 flex items-center justify-between">
                    <h2 class="font-bold text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                        <span>1. Identificación y Asignación de Sucursal</span>
                    </h2>
                </div>

                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <!-- Sucursal -->
                        <div class="sm:col-span-5">
                            <label for="id_sucursal" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Sucursal Emisora *</label>
                            <select id="id_sucursal" name="id_sucursal" required
                                    class="w-full text-xs font-semibold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2.5 shadow-sm">
                                <option value="">-- Seleccionar Sucursal --</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}" {{ old('id_sucursal') == $suc->id ? 'selected' : '' }}>
                                        {{ $suc->nombre }} ({{ $suc->codigo_sucursal }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('id_sucursal')" class="mt-1"/>
                        </div>

                        <!-- Número Punto de Venta -->
                        <div class="sm:col-span-3">
                            <label for="numero" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Pto. Venta ARCA (N°)*</label>
                            <input id="numero" name="numero" type="number" min="1" max="99999" required
                                   value="{{ old('numero') }}"
                                   placeholder="Ej: 3"
                                   class="w-full text-xs font-mono font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2.5 shadow-sm tabular-nums">
                            <span class="text-[9px] text-slate-400">1 a 5 dígitos (ej: 3 = 00003)</span>
                            <x-input-error :messages="$errors->get('numero')" class="mt-1"/>
                        </div>

                        <!-- Nombre Descriptivo -->
                        <div class="sm:col-span-4">
                            <label for="nombre" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Nombre Descriptivo *</label>
                            <input id="nombre" name="nombre" type="text" maxlength="100" required
                                   value="{{ old('nombre') }}"
                                   placeholder="Ej: Caja 1 Trelew"
                                   class="w-full text-xs font-semibold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('nombre')" class="mt-1"/>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                        <!-- Tipo de Emisión -->
                        <div class="sm:col-span-7">
                            <label for="tipo_emision" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Tipo de Emisión / Tecnología *</label>
                            <select id="tipo_emision" name="tipo_emision" x-model="tipo" required
                                    class="w-full text-xs font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2.5 shadow-sm">
                                <option value="fiscal_hasar">Controlador Fiscal Hasar 2G (Hardware Local en Red)</option>
                                <option value="arca_wsfe">Factura Electrónica ARCA (Web Service WSFE)</option>
                            </select>
                            <x-input-error :messages="$errors->get('tipo_emision')" class="mt-1"/>
                        </div>

                        <!-- Estado Activo -->
                        <div class="sm:col-span-5 flex items-center pt-4">
                            <label class="relative flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="activo" value="1" {{ old('activo', '1') == '1' ? 'checked' : '' }}
                                       class="rounded border-slate-300 dark:border-slate-600 text-blue-600 shadow-sm focus:ring-blue-500 h-4 w-4">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Punto de Venta Habilitado / Activo</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 2A: Configuración Hasar 2G (Condicional) -->
            <div x-show="tipo === 'fiscal_hasar'" x-cloak
                 class="bg-white dark:bg-slate-800/90 rounded-xl border border-amber-300 dark:border-amber-700 shadow-sm overflow-hidden">
                <div class="bg-amber-600 text-white px-3.5 py-2 flex items-center justify-between">
                    <h2 class="font-bold text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>2. Parámetros de Red Controlador Fiscal Hasar 2G</span>
                    </h2>
                    <span class="text-[10px] font-mono uppercase bg-amber-700 px-2 py-0.5 rounded">Puente Local Windows</span>
                </div>

                <div class="p-4 space-y-3">
                    <div class="p-2.5 rounded-lg bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs">
                        Este punto de venta se comunica con la impresora mediante un micro-servicio local (puente Windows). Desde el listado podrá descargar el archivo <strong>config.json</strong> para sincronizar la PC de caja.
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <!-- IP de la Impresora -->
                        <div class="sm:col-span-4">
                            <label for="hasar_ip" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">IP Fija de la Impresora *</label>
                            <input id="hasar_ip" name="hasar_ip" type="text"
                                   value="{{ old('hasar_ip', '192.168.1.150') }}"
                                   placeholder="192.168.1.150"
                                   class="w-full text-xs font-mono font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-amber-500 focus:ring-amber-500 py-1.5 px-2.5 shadow-sm tabular-nums">
                            <x-input-error :messages="$errors->get('hasar_ip')" class="mt-1"/>
                        </div>

                        <!-- Puerto TCP Hasar -->
                        <div class="sm:col-span-3">
                            <label for="hasar_puerto" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Puerto TCP Hasar *</label>
                            <input id="hasar_puerto" name="hasar_puerto" type="number" min="1" max="65535"
                                   value="{{ old('hasar_puerto', 20000) }}"
                                   placeholder="20000"
                                   class="w-full text-xs font-mono font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-amber-500 focus:ring-amber-500 py-1.5 px-2.5 shadow-sm tabular-nums">
                            <x-input-error :messages="$errors->get('hasar_puerto')" class="mt-1"/>
                        </div>

                        <!-- Puerto Local Puente Windows -->
                        <div class="sm:col-span-3">
                            <label for="puerto_local" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Puerto Local Puente *</label>
                            <input id="puerto_local" name="puerto_local" type="number" min="1024" max="65535"
                                   value="{{ old('puerto_local', 8585) }}"
                                   placeholder="8585"
                                   class="w-full text-xs font-mono font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-amber-500 focus:ring-amber-500 py-1.5 px-2.5 shadow-sm tabular-nums">
                            <x-input-error :messages="$errors->get('puerto_local')" class="mt-1"/>
                        </div>

                        <!-- Modelo Hasar -->
                        <div class="sm:col-span-2">
                            <label for="modelo" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Modelo Hasar</label>
                            <input id="modelo" name="modelo" type="text"
                                   value="{{ old('modelo', 'SMH/PT-250F') }}"
                                   class="w-full text-xs font-mono font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-amber-500 focus:ring-amber-500 py-1.5 px-2.5 shadow-sm">
                            <x-input-error :messages="$errors->get('modelo')" class="mt-1"/>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 2B: Configuración ARCA WSFE (Condicional) -->
            <div x-show="tipo === 'arca_wsfe'" x-cloak
                 class="bg-white dark:bg-slate-800/90 rounded-xl border border-blue-300 dark:border-blue-700 shadow-sm overflow-hidden">
                <div class="bg-blue-600 text-white px-3.5 py-2 flex items-center justify-between">
                    <h2 class="font-bold text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                        </svg>
                        <span>2. Parámetros de Factura Digital ARCA (WSFE)</span>
                    </h2>
                    <span class="text-[10px] font-mono uppercase bg-blue-700 px-2 py-0.5 rounded">AFIP / ARCA Web Service</span>
                </div>

                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <!-- Ambiente -->
                        <div class="sm:col-span-4">
                            <label for="ambiente" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Ambiente ARCA *</label>
                            <select id="ambiente" name="ambiente"
                                    class="w-full text-xs font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2.5 shadow-sm">
                                <option value="produccion" {{ old('ambiente', 'produccion') === 'produccion' ? 'selected' : '' }}>Producción Oficial (Validez Fiscal)</option>
                                <option value="homologacion" {{ old('ambiente') === 'homologacion' ? 'selected' : '' }}>Homologación / Testing (Pruebas)</option>
                            </select>
                            <x-input-error :messages="$errors->get('ambiente')" class="mt-1"/>
                        </div>

                        <!-- CUIT Emisor -->
                        <div class="sm:col-span-4">
                            <label for="cuit" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">CUIT Emisor ante ARCA *</label>
                            <input id="cuit" name="cuit" type="text" maxlength="14"
                                   value="{{ old('cuit', '30-71234567-8') }}"
                                   placeholder="30712345678"
                                   class="w-full text-xs font-mono font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2.5 shadow-sm tabular-nums">
                            <x-input-error :messages="$errors->get('cuit')" class="mt-1"/>
                        </div>

                        <!-- Concepto -->
                        <div class="sm:col-span-4">
                            <label for="concepto" class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-0.5">Concepto Predeterminado</label>
                            <select id="concepto" name="concepto"
                                    class="w-full text-xs font-semibold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2.5 shadow-sm">
                                <option value="1" {{ old('concepto', 1) == 1 ? 'selected' : '' }}>1 - Productos / Mercaderías</option>
                                <option value="2" {{ old('concepto') == 2 ? 'selected' : '' }}>2 - Servicios</option>
                                <option value="3" {{ old('concepto') == 3 ? 'selected' : '' }}>3 - Productos y Servicios</option>
                            </select>
                            <x-input-error :messages="$errors->get('concepto')" class="mt-1"/>
                        </div>
                    </div>

                    <!-- Claves Digitales y Certificado X.509 -->
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700/80">
                        <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Certificado Digital y Clave Privada (WSAA)</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3 leading-tight">
                            Suba el archivo de certificado otorgado por ARCA (<code>.crt</code> o <code>.pem</code>) y la clave privada generada con OpenSSL (<code>.key</code>). Se almacenarán de forma segura en el servidor.
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Archivo Certificado -->
                            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                                <label for="certificado" class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                                    Certificado Digital (.crt / .pem)
                                </label>
                                <input id="certificado" name="certificado" type="file" accept=".crt,.pem,.cert"
                                       class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                <x-input-error :messages="$errors->get('certificado')" class="mt-1"/>
                            </div>

                            <!-- Archivo Clave Privada -->
                            <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                                <label for="clave_privada" class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                                    Clave Privada (.key)
                                </label>
                                <input id="clave_privada" name="clave_privada" type="file" accept=".key,.pem,.txt"
                                       class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                                <x-input-error :messages="$errors->get('clave_privada')" class="mt-1"/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end space-x-3 pt-3">
                <a href="{{ route('sistema.puntos-venta.index') }}"
                   class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 font-bold text-xs py-2 px-4 rounded-lg uppercase tracking-wider transition-all shadow-sm">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2 px-5 rounded-lg uppercase tracking-wider transition-all shadow-sm flex items-center space-x-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Guardar Punto de Venta</span>
                </button>
            </div>

        </div>
    </form>
</x-app-layout>

