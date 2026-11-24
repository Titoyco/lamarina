<x-app-layout>
    <div class="max-w-7xl mx-auto py-2" x-data="posApp()" x-init="initPos()">
        
        <!-- Header Superior del POS -->
        <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-3">
            <div class="flex items-center space-x-3">
                <a href="{{ route('ventas.index') }}" title="Volver al Historial de Ventas"
                   class="bg-white dark:bg-slate-800 p-2 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <span>Punto de Venta (POS) & Facturación</span>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $esTdf ? 'bg-sky-100 dark:bg-sky-950/70 text-sky-800 dark:text-sky-300 border border-sky-300 dark:border-sky-800' : 'bg-emerald-100 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' }}">
                            {{ $sucursalActiva->nombre ?? 'Sucursal' }} ({{ $sucursalActiva->codigo_sucursal ?? 'N/A' }})
                        </span>
                    </h1>
                    <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        <span>Lote / Presentación: <strong>#{{ $presentacion->numero ?? 'Sin Lote' }}</strong></span>
                        <span>•</span>
                        <span>Tasa Financiación Lote: <strong>{{ number_format($tasaInteresCredito, 1) }}%</strong></span>
                        @if($esTdf)
                            <span>•</span>
                            <span class="text-sky-600 dark:text-sky-400 font-bold">Modo Escáner Activo (Rebaja de Stock)</span>
                        @else
                            <span>•</span>
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold">Modo Mostrador Trelew (Venta Directa / Varios)</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <button type="button" @click="limpiarVenta()" 
                        class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-1.5 px-3 rounded-lg transition flex items-center gap-1 uppercase">
                    <span>🗑️ Limpiar Factura</span>
                </button>
                <a href="{{ route('ventas.index') }}" 
                   class="bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 font-bold text-xs py-1.5 px-3 rounded-lg transition flex items-center gap-1 uppercase">
                    <span>Historial Ventas</span>
                </a>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- BANNER DE CONTROL DE CAJA / ESTADO DEL TURNO                        -->
        <!-- =================================================================== -->
        @if($requiereCierreAnterior && $cajaActiva)
            <div class="mb-3 p-3.5 bg-amber-50 dark:bg-amber-950/70 border-2 border-amber-500 rounded-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-sm animate-pulse">
                        ⚠️
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-wider text-amber-950 dark:text-amber-200">
                                Cierre de Jornada Anterior Pendiente (Caja #{{ $cajaActiva->id }})
                            </span>
                            <span class="text-[10px] bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100 font-bold px-2 py-0.5 rounded-full">
                                Apertura: {{ $cajaActiva->fecha_apertura->format('d/m/Y H:i') }} hs
                            </span>
                        </div>
                        <p class="text-xs text-amber-800 dark:text-amber-300 mt-0.5">
                            La caja de la jornada anterior no fue cerrada. Para abrir la caja de hoy y poder facturar, debe emitir el Cierre Z de los controladores fiscales que registraron ventas y realizar el arqueo de caja.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 justify-end">
                    <a href="{{ route('cajas.cerrar', $cajaActiva->id) }}" 
                       class="bg-amber-600 hover:bg-amber-700 text-white font-black text-xs py-2 px-4 rounded-xl shadow transition uppercase flex items-center gap-1.5 cursor-pointer">
                        <span>🔒 Emitir Cierre Z Anterior y Abrir Hoy</span>
                    </a>
                    <a href="{{ route('cajas.index') }}" 
                       class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 font-bold text-xs py-2 px-3 rounded-xl transition hover:bg-slate-50">
                        <span>📋 Historial Cajas</span>
                    </a>
                </div>
            </div>
        @elseif($cajaActiva && $cajaActiva->esDeHoy())
            <div class="mb-3 p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 rounded-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-sm font-black shrink-0 shadow-sm">
                        ✓
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-900 dark:text-emerald-200 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                <span>Caja Abierta (Turno #{{ $cajaActiva->id }})</span>
                            </span>
                            <span class="text-[10px] bg-emerald-200 dark:bg-emerald-900 text-emerald-900 dark:text-emerald-200 font-bold px-2 py-0.5 rounded-full">
                                Hoy {{ $cajaActiva->fecha_apertura->format('H:i') }} hs
                            </span>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                • Operador: <strong>{{ $cajaActiva->usuarioApertura?->name ?? 'Usuario' }}</strong>
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            <span>Fondo Inicial: <strong class="text-slate-900 dark:text-white font-mono">${{ number_format($cajaActiva->monto_inicial_efectivo, 2, ',', '.') }}</strong></span>
                            <span>•</span>
                            <span>Ingresos (+): <strong class="text-emerald-700 dark:text-emerald-400 font-mono">+$<span x-text="formatDinero(totalIngresosCaja)">{{ number_format($totalesCajaActiva['total_ingresos'] ?? 0, 2, ',', '.') }}</span></strong></span>
                            <span>•</span>
                            <span>Retiros (-): <strong class="text-rose-700 dark:text-rose-400 font-mono">-$<span x-text="formatDinero(totalRetirosCaja)">{{ number_format($totalesCajaActiva['total_retiros'] ?? 0, 2, ',', '.') }}</span></strong></span>
                            <span>•</span>
                            <span>Efectivo en Caja: <strong class="text-emerald-700 dark:text-emerald-400 font-mono text-sm">$<span x-text="formatDinero(efectivoEsperadoCaja)">{{ number_format($totalesCajaActiva['efectivo_esperado'] ?? 0, 2, ',', '.') }}</span></strong></span>
                            <span>•</span>
                            <span>Total Facturado: <strong class="text-indigo-700 dark:text-indigo-400 font-mono">${{ number_format($totalesCajaActiva['total_general'] ?? 0, 2, ',', '.') }}</strong> ({{ $totalesCajaActiva['ventas_count'] ?? 0 }} ventas)</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 justify-end">
                    <button type="button" @click="abrirModalMovimiento()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-black text-xs py-2 px-3.5 rounded-xl shadow transition uppercase flex items-center gap-1.5 cursor-pointer">
                        <span>💸 Ingreso / Retiro</span>
                    </button>
                    <a href="{{ route('cajas.cerrar', $cajaActiva->id) }}" 
                       class="bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-white dark:text-slate-900 text-white font-black text-xs py-2 px-3.5 rounded-xl shadow transition uppercase flex items-center gap-1.5">
                        <span>🔒 Cierre Z</span>
                    </a>
                    <a href="{{ route('cajas.index') }}" 
                       class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 font-bold text-xs py-2 px-3 rounded-xl transition hover:bg-slate-50">
                        <span>📋 Control Cajas</span>
                    </a>
                </div>
            </div>
        @else
            <div class="mb-3 p-3.5 bg-rose-50 dark:bg-rose-950/60 border-2 border-rose-400 dark:border-rose-700 rounded-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-lg font-black shrink-0 shadow-sm animate-pulse">
                        🔒
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black uppercase tracking-wider text-rose-950 dark:text-rose-200">
                                Caja No Iniciada — Apertura Obligatoria
                            </span>
                            <span class="text-[10px] bg-rose-200 dark:bg-rose-900/80 text-rose-900 dark:text-rose-200 font-bold px-2 py-0.5 rounded-full uppercase">
                                Facturación Bloqueada
                            </span>
                        </div>
                        <p class="text-xs text-rose-800 dark:text-rose-300 mt-0.5">
                            Al inicio de la jornada se debe abrir la caja ingresando el dinero en efectivo disponible para habilitar la facturación de mostrador.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0 justify-end">
                    <button type="button" @click="modalAbrirCaja = true"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs py-2 px-4 rounded-xl shadow transition uppercase flex items-center gap-1.5 cursor-pointer">
                        <span>🟢 Abrir Caja de Hoy</span>
                    </button>
                    <a href="{{ route('cajas.index') }}" 
                       class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600 font-bold text-xs py-2 px-3 rounded-xl transition hover:bg-slate-50">
                        <span>📋 Historial Cajas</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- =================================================================== -->
        <!-- BARRA GUÍA DE FLUJO DINÁMICO POR TECLADO (MODO CRÉDITO / TRELEW)    -->
        <!-- =================================================================== -->
        <div x-show="esTrelew || esCredito" x-cloak
             class="mb-3 p-2.5 bg-indigo-950 text-white rounded-xl border border-indigo-700/80 shadow-md flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 text-xs">
                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded bg-indigo-800 text-indigo-200 flex items-center gap-1">
                    <span>⚡</span>
                    <span>Flujo Rápido</span>
                </span>

                <!-- Paso 1: Cliente -->
                <button type="button" @click="irAPaso(1)"
                        class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1.5 transition cursor-pointer text-[11px]"
                        :class="pasoActualFlujo === 1 
                            ? 'bg-amber-400 text-slate-950 font-black ring-2 ring-amber-200 shadow-xs' 
                            : (clienteSeleccionado ? 'bg-emerald-800/80 text-emerald-100 border border-emerald-600/60' : 'bg-indigo-900/70 text-indigo-200')">
                    <span x-text="clienteSeleccionado ? '✓ 1. Cliente' : '1. Cliente'"></span>
                    <kbd class="text-[9px] px-1 py-0.2 rounded bg-black/25 font-mono">F2</kbd>
                </button>

                <span class="text-indigo-400 font-black">→</span>

                <!-- Paso 2: COD: BARRA -->
                <button type="button" @click="irAPaso(2)"
                        class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1.5 transition cursor-pointer text-[11px]"
                        :class="pasoActualFlujo === 2 
                            ? 'bg-amber-400 text-slate-950 font-black ring-2 ring-amber-200 shadow-xs' 
                            : (items.length > 0 ? 'bg-emerald-800/80 text-emerald-100 border border-emerald-600/60' : 'bg-indigo-900/70 text-indigo-200')">
                    <span>2. COD: BARRA</span>
                    <kbd class="text-[9px] px-1 py-0.2 rounded bg-black/25 font-mono">F3</kbd>
                </button>

                <span class="text-indigo-400 font-black">→</span>

                <!-- Paso 3: Monto -->
                <button type="button" @click="irAPaso(3)"
                        class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1.5 transition cursor-pointer text-[11px]"
                        :class="pasoActualFlujo === 3 
                            ? 'bg-amber-400 text-slate-950 font-black ring-2 ring-amber-200 shadow-xs' 
                            : (items.length > 0 ? 'bg-emerald-800/80 text-emerald-100 border border-emerald-600/60' : 'bg-indigo-900/70 text-indigo-200')">
                    <span x-text="items.length > 0 ? '✓ 3. Monto ($' + formatDinero(calcularSubtotal()) + ')' : '3. Monto'"></span>
                    <kbd class="text-[9px] px-1 py-0.2 rounded bg-black/25 font-mono">F4</kbd>
                </button>

                <span class="text-indigo-400 font-black">→</span>

                <!-- Paso 4: Crédito Personal -->
                <button type="button" @click="irAPaso(4)"
                        class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1.5 transition cursor-pointer text-[11px]"
                        :class="pasoActualFlujo === 4 
                            ? 'bg-amber-400 text-slate-950 font-black ring-2 ring-amber-200 shadow-xs' 
                            : (creditoAutorizado ? 'bg-emerald-800/80 text-emerald-100 border border-emerald-600/60' : 'bg-indigo-900/70 text-indigo-200')">
                    <span x-text="creditoAutorizado ? '✓ 4. Crédito Aprobado' : '4. Crédito Personal'"></span>
                    <kbd class="text-[9px] px-1 py-0.2 rounded bg-black/25 font-mono">F5</kbd>
                </button>

                <span class="text-indigo-400 font-black">→</span>

                <!-- Paso 5: Facturar y Generar Pagaré -->
                <button type="button" @click="irAPaso(5)"
                        class="px-2.5 py-1 rounded-lg font-bold flex items-center gap-1.5 transition cursor-pointer text-[11px]"
                        :class="pasoActualFlujo === 5 
                            ? 'bg-amber-400 text-slate-950 font-black ring-2 ring-amber-200 shadow-xs animate-pulse' 
                            : (modalExito ? 'bg-emerald-800/80 text-emerald-100 border border-emerald-600/60' : 'bg-indigo-900/70 text-indigo-200')">
                    <span>5. Facturar</span>
                    <kbd class="text-[9px] px-1 py-0.2 rounded bg-black/25 font-mono">F8</kbd>
                </button>
            </div>

            <div class="text-[11px] font-mono text-indigo-200 flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-black">ENTER</span>
                <span>Avanzar siguiente paso</span>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- POS PRINCIPAL: LAYOUT A ANCHO COMPLETO (COMPACTO Y DESATURADO)     -->
        <!-- =================================================================== -->
        <div class="space-y-3">
            
            <!-- 1. BARRA SUPERIOR UNIFICADA: COMPROBANTE + CLIENTE + ENTRADAS RÁPIDAS -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-3 space-y-2.5">
                
                <!-- Fila 1: Selector de Comprobante y Selector de Cliente -->
                <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-2.5 pb-2 border-b border-slate-100 dark:border-slate-700/60">
                    
                    <!-- Selector de Comprobante (Pills compactas) -->
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mr-1">Comprobante:</span>
                        
                        <button type="button" 
                                @click="seleccionarTipoComprobante('FACTURA_B')"
                                :class="tipoComprobante === 'FACTURA_B' ? 'bg-indigo-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600'"
                                class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                            Factura B
                        </button>

                        <button type="button" 
                                @click="seleccionarTipoComprobante('FACTURA_A')"
                                :class="tipoComprobante === 'FACTURA_A' ? 'bg-indigo-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600'"
                                class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                            Factura A
                        </button>

                        <button type="button" 
                                @click="seleccionarTipoComprobante('FACTURA_C')"
                                :class="tipoComprobante === 'FACTURA_C' ? 'bg-indigo-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600'"
                                class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer">
                            Factura C
                        </button>

                        <button type="button" 
                                @click="seleccionarTipoComprobante('REMITO')"
                                :class="tipoComprobante === 'REMITO' ? 'bg-amber-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600'"
                                class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer"
                                title="Descuenta stock. Facturación posterior.">
                            Remito
                        </button>

                        <button type="button" 
                                @click="seleccionarTipoComprobante('PRESUPUESTO')"
                                :class="tipoComprobante === 'PRESUPUESTO' ? 'bg-purple-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600'"
                                class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer"
                                title="Cotización previa. No afecta stock ni se factura.">
                            Presupuesto
                        </button>

                        <button type="button" 
                                @click="seleccionarTipoComprobante('NOTA_CREDITO')"
                                :class="tipoComprobante.includes('NOTA_CREDITO') ? 'bg-rose-600 text-white shadow-xs font-black' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600'"
                                class="px-2.5 py-1 rounded-lg text-xs transition cursor-pointer"
                                title="Reingreso de mercadería / Devolución.">
                            Nota de Crédito
                        </button>

                        <span class="text-[10px] text-slate-400 font-mono hidden sm:inline ml-1" x-text="nombreComprobanteActual"></span>
                    </div>

                    <!-- Selector / Ficha de Cliente (Paso 1) -->
                    <div class="flex items-center gap-2">
                        <template x-if="!clienteSeleccionado">
                            <div class="relative flex-grow sm:w-80">
                                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                                    <span>👤</span>
                                </div>
                                <input type="text" 
                                       x-ref="clienteQueryInput"
                                       x-model="clienteQuery"
                                       @input.debounce.250ms="buscarClientes()"
                                       @keydown.enter.prevent="confirmarClienteEnter()"
                                       @keydown.arrow-down.prevent="moverSugerenciaCliente(1)"
                                       @keydown.arrow-up.prevent="moverSugerenciaCliente(-1)"
                                       placeholder="Buscar cliente (DNI, Nombre, CUIT)... [F2]"
                                       class="w-full text-xs py-1.5 pl-8 pr-12 rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 shadow-2xs">
                                <span class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-[9px] font-mono font-bold text-slate-400">
                                    F2
                                </span>

                                <!-- Dropdown de sugerencias de clientes -->
                                <div x-show="sugerenciasClientes.length > 0"
                                     @click.away="sugerenciasClientes = []"
                                     class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800"
                                     x-cloak>
                                    <template x-for="(cl, idx) in sugerenciasClientes" :key="cl.id">
                                        <button type="button" 
                                                @click="seleccionarCliente(cl)"
                                                :class="{'bg-indigo-50 dark:bg-slate-800 font-bold': idx === indiceSugerenciaCliente}"
                                                class="w-full text-left p-2.5 hover:bg-indigo-50 dark:hover:bg-slate-800 transition flex items-center justify-between text-xs">
                                            <div>
                                                <span class="font-bold text-slate-900 dark:text-white block" x-text="cl.nombre || cl.razon_social"></span>
                                                <span class="text-[10px] font-mono text-slate-400" x-text="'DNI/CUIT: ' + (cl.dni || cl.cuit || '-')"></span>
                                            </div>
                                            <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold">Seleccionar ↵</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <template x-if="clienteSeleccionado">
                            <div class="flex items-center gap-2 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 px-3 py-1 rounded-lg">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-black text-indigo-950 dark:text-indigo-200" x-text="clienteSeleccionado.nombre"></span>
                                        <span class="text-[10px] font-mono bg-indigo-200 dark:bg-indigo-900 text-indigo-900 dark:text-indigo-200 px-1 py-0.2 rounded font-bold" x-text="clienteSeleccionado.dni || 'Sin DNI'"></span>
                                    </div>
                                    <template x-if="infoCredito">
                                        <div class="text-[10px] text-indigo-700 dark:text-indigo-300 flex items-center gap-2">
                                            <span>Disponible: <strong>$<span x-text="formatDinero(infoCredito.estado_crediticio.limite_disponible)"></span></strong></span>
                                            <span>•</span>
                                            <span>Hasta <strong x-text="infoCredito.estado_crediticio.max_cuotas"></strong> cuotas</span>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" @click="deseleccionarCliente()" 
                                        class="text-xs text-rose-600 hover:text-rose-800 font-bold p-1 hover:bg-rose-100 rounded transition cursor-pointer"
                                        title="Cambiar cliente (F2)">
                                    ✕
                                </button>
                            </div>
                        </template>
                    </div>

                </div>

                <!-- Fila 2: Inputs de Carga Rápida (Monto Genérico VARIOS F4 + Escáner Barcode F3) -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5 items-center">
                    
                    <!-- Input 1: Carga Rápida de Monto VARIOS (F4) - 4 columnas -->
                    <div class="md:col-span-4 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-slate-800 dark:to-slate-800/90 p-1.5 rounded-xl border border-amber-200 dark:border-amber-800/60 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 shrink-0 pl-1">
                            <span class="w-6 h-6 rounded-md bg-amber-500 text-white flex items-center justify-center font-black text-[11px] shadow-2xs">
                                ⚡
                            </span>
                            <div class="leading-none">
                                <span class="text-[11px] font-black uppercase text-amber-900 dark:text-amber-200 block">Monto Varios</span>
                                <span class="text-[9px] font-mono text-amber-700 dark:text-amber-400 font-bold">[F4]</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 flex-grow justify-end">
                            <div class="relative w-full max-w-[130px]">
                                <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-xs font-black text-slate-400">$</span>
                                <input type="number" step="0.01" min="1" 
                                       x-ref="montoGenericoInput"
                                       x-model="montoGenerico" 
                                       @keydown.enter.prevent="procesarMontoGenericoEnter()"
                                       placeholder="0.00"
                                       class="w-full text-xs font-mono font-black py-1 pl-5 pr-1 rounded-lg border-amber-300 dark:border-amber-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-amber-500">
                            </div>
                            <button type="button" @click="procesarMontoGenericoEnter()"
                                    class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-1 px-2.5 rounded-lg shadow-2xs transition uppercase shrink-0 cursor-pointer">
                                <span>+ Varios</span>
                            </button>
                        </div>
                    </div>

                    <!-- Input 2: Escáner de Código de Barras / Búsqueda de Productos (F3) - 8 columnas -->
                    <div class="md:col-span-8 relative">
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-indigo-600 dark:text-indigo-400">
                                <svg class="w-4 h-4 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>
                            <input type="text" 
                                   x-ref="barcodeInput"
                                   x-model="barcodeQuery"
                                   @keydown.enter.prevent="procesarCodigoBarra()"
                                   @input.debounce.250ms="buscarProductosSugeridos()"
                                   placeholder="Escanee código de barra o escriba descripción del producto... [F3]"
                                   class="w-full text-xs font-mono py-1.5 pl-9 pr-24 rounded-lg border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 shadow-inner"
                                   autocomplete="off">
                            
                            <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center space-x-1">
                                <span class="text-[9px] bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-mono px-1.5 py-0.5 rounded font-bold">
                                    F3
                                </span>
                                <button type="button" @click="procesarCodigoBarra()"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-1 px-2.5 rounded-md shadow-2xs transition uppercase shrink-0">
                                    + Agregar
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown de sugerencias de productos -->
                        <div x-show="sugerenciasProductos.length > 0"
                             @click.away="sugerenciasProductos = []"
                             class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800"
                             x-cloak>
                            <template x-for="prod in sugerenciasProductos" :key="prod.id">
                                <button type="button" @click="intentarAgregarProducto(prod); sugerenciasProductos = []; barcodeQuery = ''; focusBarcode();"
                                        class="w-full text-left p-2.5 hover:bg-indigo-50 dark:hover:bg-slate-800 transition flex items-center justify-between">
                                    <div>
                                        <span class="font-bold text-slate-800 dark:text-white text-xs block" x-text="prod.descripcion"></span>
                                        <span class="text-[10px] font-mono text-slate-400" x-text="'Cód: ' + prod.codigo + ' | EAN: ' + (prod.codigo_barra || '-')"></span>
                                    </div>
                                    <div class="text-right flex flex-col items-end">
                                        <span class="text-xs font-mono font-black text-indigo-700 dark:text-indigo-400 block" x-text="'$ ' + formatDinero(prod.precio_venta)"></span>
                                        <template x-if="prod.controla_stock">
                                            <span class="text-[10px] font-black px-1.5 py-0.5 rounded tracking-tight mt-0.5 inline-block"
                                                  :class="prod.stock > 0 ? 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' : 'bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-800'"
                                                  x-text="prod.stock > 0 ? 'Stock: ' + prod.stock : '⚠️ SIN STOCK (0)'">
                                            </span>
                                        </template>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

            <!-- 2. TABLA DE ARTÍCULOS A ANCHO COMPLETO (COMPACT DATA-GRID) -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-2.5 bg-slate-100 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-xs font-black uppercase text-slate-700 dark:text-slate-300 tracking-wider flex items-center gap-1.5">
                        <span>🛒</span>
                        <span>Detalle de Factura (<span x-text="items.length"></span> artículos cargados)</span>
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-bold" x-text="items.length > 0 ? 'Subtotal parcial: $ ' + formatDinero(calcularSubtotal()) : ''"></span>
                </div>

                <div class="overflow-x-auto min-h-[220px] max-h-[380px] overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 text-[10px] uppercase font-bold sticky top-0 z-10">
                                <th class="py-1.5 px-2.5 w-8 text-center">#</th>
                                <th class="py-1.5 px-3 w-32 font-mono">Código</th>
                                <th class="py-1.5 px-3">Artículo / Descripción</th>
                                <th class="py-1.5 px-2 text-center w-28">Cantidad</th>
                                <th class="py-1.5 px-3 text-right w-28">P. Unitario</th>
                                <th class="py-1.5 px-2 text-center w-24">% Desc.</th>
                                <th class="py-1.5 px-3 text-right w-32">Subtotal</th>
                                <th class="py-1.5 px-2 text-center w-12">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-sans">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/40 transition">
                                    {{-- # --}}
                                    <td class="py-1.5 px-2.5 text-center font-mono text-[10px] text-slate-400" x-text="index + 1"></td>

                                    {{-- Código --}}
                                    <td class="py-1.5 px-3 font-mono text-[11px] text-slate-500 dark:text-slate-400" x-text="item.codigo || item.codigo_producto || '-'"></td>

                                    {{-- Descripción --}}
                                    <td class="py-1.5 px-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 dark:text-white text-xs" x-text="item.descripcion"></span>
                                            <template x-if="item.controla_stock">
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 rounded bg-sky-100 dark:bg-sky-950 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800" x-text="'Stock: ' + item.stock"></span>
                                            </template>
                                        </div>
                                    </td>

                                    {{-- Cantidad (+ / -) --}}
                                    <td class="py-1.5 px-2 text-center">
                                        <div class="inline-flex items-center border border-slate-300 dark:border-slate-600 rounded-lg overflow-hidden bg-white dark:bg-slate-900 shadow-2xs">
                                            <button type="button" @click="cambiarCantidad(index, -1)" 
                                                    class="px-2 py-0.5 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 font-black text-xs transition">
                                                -
                                            </button>
                                            <input type="number" min="0.01" step="1" 
                                                   x-model.number="item.cantidad"
                                                   @change="validarItem(index)"
                                                   @keydown.enter.prevent="focusBarcode()"
                                                   class="w-10 text-center text-xs font-bold font-mono py-0.5 px-0 border-0 bg-transparent text-slate-900 dark:text-white focus:ring-0">
                                            <button type="button" @click="cambiarCantidad(index, 1)" 
                                                    class="px-2 py-0.5 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 font-black text-xs transition">
                                                +
                                            </button>
                                        </div>
                                    </td>

                                    {{-- Precio Unitario --}}
                                    <td class="py-1.5 px-3 text-right">
                                        <input type="number" step="0.01" min="0" 
                                               x-model.number="item.precio_unitario"
                                               @change="validarItem(index)"
                                               class="w-24 text-right text-xs font-mono font-bold py-0.5 px-1.5 rounded border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-indigo-500">
                                    </td>

                                    {{-- % Descuento --}}
                                    <td class="py-1.5 px-2 text-center">
                                        <div class="inline-flex items-center gap-1 justify-center">
                                            <input type="number" min="0" max="100" step="1" 
                                                   x-model.number="item.descuento_porcentaje"
                                                   @input="validarItem(index)"
                                                   placeholder="0"
                                                   class="w-12 text-center text-xs font-mono font-bold py-0.5 px-0.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-indigo-500">
                                            <span class="text-[10px] font-bold text-slate-400">%</span>
                                        </div>
                                    </td>

                                    {{-- Subtotal --}}
                                    <td class="py-1.5 px-3 text-right font-mono tabular-nums text-xs">
                                        <template x-if="item.descuento_porcentaje > 0">
                                            <div>
                                                <span class="font-black text-emerald-600 dark:text-emerald-400 block"
                                                      x-text="'$ ' + formatDinero(calcularSubtotalItem(item))"></span>
                                                <span class="text-[10px] text-slate-400 line-through block"
                                                      x-text="'$ ' + formatDinero(item.cantidad * item.precio_unitario)"></span>
                                            </div>
                                        </template>
                                        <template x-if="!item.descuento_porcentaje || item.descuento_porcentaje == 0">
                                            <span class="font-black text-slate-900 dark:text-white"
                                                  x-text="'$ ' + formatDinero(item.cantidad * item.precio_unitario)"></span>
                                        </template>
                                    </td>

                                    {{-- Quitar fila --}}
                                    <td class="py-1.5 px-2 text-center">
                                        <button type="button" @click="quitarItem(index)" 
                                                class="text-rose-500 hover:text-rose-700 p-1 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded transition text-xs font-bold cursor-pointer" 
                                                title="Eliminar fila">
                                            ✕
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="items.length === 0">
                                <tr>
                                    <td colspan="8" class="py-10 text-center text-slate-400 dark:text-slate-500">
                                        <div class="text-2xl mb-1">🛒</div>
                                        <p class="font-bold text-xs">Factura vacía</p>
                                        <p class="text-[11px] mt-0.5 text-slate-400">Escanee un código de barras (F3) o cargue un monto rápido en VARIOS (F4).</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. BARRA INFERIOR DE TOTALES Y FACTURACIÓN (FULL WIDTH SUMMARY BAR) -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-3">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    
                    <!-- Lado Izquierdo: Descuento General Manual y Promos -->
                    <div class="flex flex-wrap items-center gap-3 text-xs w-full md:w-auto">
                        <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-900 p-1.5 rounded-lg border border-slate-200 dark:border-slate-700">
                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase">🏷️ Desc. Factura:</span>
                            
                            <div class="flex rounded border border-slate-300 dark:border-slate-600 overflow-hidden text-[10px]">
                                <button type="button" 
                                        @click="tipoDescuentoManual = 'monto'"
                                        :class="tipoDescuentoManual === 'monto' ? 'bg-indigo-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                                        class="px-2 py-0.5">
                                    $
                                </button>
                                <button type="button" 
                                        @click="tipoDescuentoManual = 'porcentaje'"
                                        :class="tipoDescuentoManual === 'porcentaje' ? 'bg-indigo-600 text-white font-bold' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                                        class="px-2 py-0.5">
                                    %
                                </button>
                            </div>

                            <input type="number" step="0.01" min="0"
                                   x-model.number="descuentoManual"
                                   placeholder="0.00"
                                   class="w-20 text-right text-xs font-mono font-bold py-0.5 px-1.5 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">

                            <button type="button" @click="descuentoManual = 0" x-show="descuentoManual > 0" class="text-rose-500 font-bold px-1 cursor-pointer" title="Quitar descuento">✕</button>
                        </div>

                        <template x-if="calcularDescuentoTotalItems() > 0">
                            <span class="text-amber-600 dark:text-amber-400 font-bold text-xs font-mono">
                                Promos ítems: -$ <span x-text="formatDinero(calcularDescuentoTotalItems())"></span>
                            </span>
                        </template>

                        <span class="text-slate-400 font-mono text-[11px]">
                            [F8] Facturar | [ENTER] Continuar
                        </span>
                    </div>

                    <!-- Lado Derecho: Gran Total y Botón Principal Facturar -->
                    <div class="flex items-center gap-4 w-full md:w-auto justify-end">
                        <div class="text-right">
                            <span class="text-[10px] text-slate-500 uppercase font-black tracking-wider block">TOTAL A FACTURAR</span>
                            <span class="text-3xl sm:text-4xl font-mono font-black text-slate-900 dark:text-emerald-400 tabular-nums leading-none"
                                  x-text="'$ ' + formatDinero(calcularTotal())">
                            </span>
                        </div>

                        <button type="button" 
                                x-ref="btnFacturarPrincipal"
                                @click="abrirModalPago()"
                                :disabled="items.length === 0 || procesando"
                                :class="items.length === 0 ? 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg active:scale-98 cursor-pointer'"
                                class="py-3 px-6 rounded-xl font-black text-sm uppercase tracking-wide transition flex items-center gap-2 shrink-0">
                            <span x-text="procesando ? 'Procesando...' : (esPresupuesto ? 'EMITIR PRESUPUESTO' : (esRemito ? 'EMITIR REMITO' : (esNotaCredito ? 'EMITIR NOTA CRÉDITO' : 'FACTURAR (F8 / ENTER)')))"></span>
                            <span class="text-[10px] bg-black/20 text-white px-1.5 py-0.5 rounded font-mono">↵</span>
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <!-- =================================================================== -->
        <!-- MODAL 1: ESTADO DE CUENTA Y AUTORIZACIÓN DE CRÉDITO PERSONAL        -->
        <!-- =================================================================== -->
        <div x-show="modalCredito" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-xs flex items-start justify-center p-3 sm:p-5 pt-8 sm:pt-12 pb-8"
             @keydown.window.escape="cerrarModalCredito()"
             x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-5xl w-full p-3.5 sm:p-5 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-3 max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150 my-auto"
                 @click.away="cerrarModalCredito()">
                
                <!-- Encabezado del Modal -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3 shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            📄
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                    3. Estado de Cuenta y Autorización de Crédito
                                </h3>
                                <template x-if="clienteSeleccionado">
                                    <button type="button" @click="abrirFichaClienteModal()"
                                            class="px-2 py-0.5 rounded text-xs font-black bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 hover:underline border border-indigo-200 dark:border-indigo-800 flex items-center gap-1 cursor-pointer"
                                            title="Ver legajo completo en modal">
                                        <span x-text="clienteSeleccionado.nombre"></span>
                                        <span>↗</span>
                                    </button>
                                </template>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                DNI: <span class="font-mono font-bold" x-text="clienteSeleccionado ? clienteSeleccionado.dni : '-'"></span> • 
                                Cuenta: <span class="font-mono font-bold" x-text="clienteSeleccionado?.nro_cuenta || '-'"></span> • 
                                Sucursal: <span class="font-bold" x-text="infoCredito?.cliente?.sucursal || 'N/A'"></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="consultarInfoCredito(clienteSeleccionado?.id)" 
                                title="Actualizar datos en vivo"
                                class="px-2 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 transition cursor-pointer">
                            🔄 Actualizar
                        </button>
                        <button type="button" @click="cerrarModalCredito()" 
                                class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center text-lg font-bold">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Contenido con scroll -->
                <div class="overflow-y-auto flex-1 pr-1 space-y-4">
                    <template x-if="infoCredito">
                        <div class="space-y-4">
                            <!-- FILA SUPERIOR: DETALLE DE COMPRAS (IZQ) vs COMENTARIOS (DER) -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Columna Izquierda: DETALLE DE COMPRAS -->
                                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                                    <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight border-b border-slate-200 dark:border-slate-700 pb-1.5">
                                        DETALLE DE COMPRAS:
                                    </h4>
                                    <div class="space-y-1.5 text-xs font-mono">
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-600 dark:text-slate-400 font-sans font-bold">TOTAL ADEUDADO:</span>
                                            <span class="text-sm font-black text-slate-900 dark:text-white" 
                                                  x-text="'$ ' + formatDinero(infoCredito.detalle_compras?.total_adeudado || infoCredito.estado_crediticio?.deuda_acumulada || 0)"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-600 dark:text-slate-400 font-sans font-bold">PENDIENTE DE COBRO (A PAGAR):</span>
                                            <span class="text-sm font-black text-indigo-700 dark:text-indigo-300" 
                                                  x-text="'$ ' + formatDinero(infoCredito.detalle_compras?.pendiente_cobro || 0)"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-600 dark:text-slate-400 font-sans font-bold">MORA (VENCIDO):</span>
                                            <span class="text-sm font-black" 
                                                  :class="(infoCredito.detalle_compras?.mora_vencido || infoCredito.estado_crediticio?.total_mora || 0) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200'"
                                                  x-text="'$ ' + formatDinero(infoCredito.detalle_compras?.mora_vencido || infoCredito.estado_crediticio?.total_mora || 0)"></span>
                                        </div>
                                        <div class="flex justify-between items-center pt-1 border-t border-slate-200 dark:border-slate-700">
                                            <span class="text-slate-600 dark:text-slate-400 font-sans font-bold">MONTO DISPONIBLE:</span>
                                            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400" 
                                                  x-text="'$ ' + formatDinero(infoCredito.detalle_compras?.limite_disponible || infoCredito.estado_crediticio?.limite_disponible || 0)"></span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-slate-600 dark:text-slate-400 font-sans font-bold">LÍMITE TOTAL:</span>
                                            <span class="font-bold text-slate-700 dark:text-slate-300" 
                                                  x-text="'$ ' + formatDinero(infoCredito.detalle_compras?.limite_total || infoCredito.estado_crediticio?.limite_total || 0)"></span>
                                        </div>
                                    </div>

                                    <!-- Modificación de Límite en Vivo -->
                                    <div class="mt-2.5 pt-2 border-t border-slate-200 dark:border-slate-700">
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase block mb-1">
                                            Modificar Límite de Crédito:
                                        </span>
                                        <div class="flex items-center gap-2">
                                            <div class="relative flex-1">
                                                <span class="absolute left-2.5 top-1.5 text-xs text-slate-400 font-bold">$</span>
                                                <input type="number" step="0.01" min="0" x-model="nuevoLimiteInput" 
                                                       @keydown.enter.prevent="guardarNuevoLimite()"
                                                       placeholder="Nuevo límite"
                                                       class="w-full pl-6 pr-2 py-1 text-xs font-mono font-bold bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg">
                                            </div>
                                            <button type="button" @click="guardarNuevoLimite()" :disabled="guardandoLimite"
                                                    class="px-3 py-1 text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition disabled:opacity-50 shrink-0 cursor-pointer">
                                                <span x-text="guardandoLimite ? 'Guardando...' : 'Modificar Límite'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna Derecha: COMENTARIOS / NOVEDADES (fondo verde menta con scroll) -->
                                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-1.5 mb-2">
                                            <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                                COMENTARIOS / NOVEDADES:
                                            </h4>
                                            <span class="text-[10px] text-slate-500 font-bold" x-text="(infoCredito.novedades?.length || 0) + ' nota(s)'"></span>
                                        </div>

                                        <!-- Caja Verde con Scroll (estilo réplica de captura) -->
                                        <div class="bg-[#dcfce7] dark:bg-emerald-950/40 border-2 border-[#86efac] dark:border-emerald-700/80 rounded-xl p-2 h-36 overflow-y-auto space-y-1.5 shadow-inner">
                                            <template x-if="!infoCredito.novedades || infoCredito.novedades.length === 0">
                                                <p class="text-xs text-emerald-800 dark:text-emerald-300 italic p-3 text-center">
                                                    Sin comentarios registrados en el legajo del cliente.
                                                </p>
                                            </template>
                                            <template x-for="com in (infoCredito.novedades || [])" :key="com.id">
                                                <div class="p-1.5 bg-white/75 dark:bg-slate-900/70 rounded-lg border border-emerald-200/90 dark:border-emerald-800/80 text-xs flex items-start gap-2 shadow-2xs">
                                                    <span class="font-mono font-black text-emerald-900 dark:text-emerald-300 shrink-0 text-[11px]" x-text="com.fecha"></span>
                                                    <div class="text-slate-900 dark:text-slate-100 flex-grow leading-tight">
                                                        <span class="font-bold text-indigo-900 dark:text-indigo-300" x-show="com.usuario_nombre" x-text="com.usuario_nombre + ': '"></span>
                                                        <span x-text="com.mensaje"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Campo Rápido para Añadir Comentario -->
                                    <div class="mt-2 flex items-center gap-1.5">
                                        <input type="text" x-model="nuevoComentarioInput" placeholder="Añadir nota al legajo..." 
                                               @keydown.enter.prevent="guardarNuevoComentario()"
                                               class="flex-grow px-2.5 py-1 text-xs bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg">
                                        <button type="button" @click="guardarNuevoComentario()" :disabled="guardandoComentario || !nuevoComentarioInput.trim()"
                                                class="px-2.5 py-1 text-xs font-bold bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg transition disabled:opacity-40 cursor-pointer shrink-0">
                                            <span x-text="guardandoComentario ? '...' : '+ Agregar'"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- FILA CENTRAL: PRÓXIMOS VENCIMIENTOS (TABLA 4 MESES) -->
                            <div class="space-y-1.5">
                                <h4 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight underline">
                                    PROXIMOS VENCIMIENTOS:
                                </h4>
                                <div class="border border-slate-300 dark:border-slate-700 rounded-xl overflow-hidden bg-white dark:bg-slate-900 shadow-sm">
                                    <!-- Cabecera de 4 Meses -->
                                    <div class="grid grid-cols-4 bg-slate-100 dark:bg-slate-800 border-b border-slate-300 dark:border-slate-700 text-center font-black text-xs py-2 text-slate-800 dark:text-slate-200">
                                        <template x-for="mes in (infoCredito.proximos_vencimientos || [])" :key="mes.numero_mes">
                                            <div class="border-r last:border-r-0 border-slate-300 dark:border-slate-700 px-1">
                                                <span x-text="mes.numero_mes + '° Mes (Lote ' + mes.lote + ')'"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <!-- Celdas de 4 Meses con Cuotas y Totales -->
                                    <div class="grid grid-cols-4 min-h-[85px] divide-x divide-slate-300 dark:divide-slate-700 text-xs">
                                        <template x-for="mes in (infoCredito.proximos_vencimientos || [])" :key="'col-' + mes.numero_mes">
                                            <div class="p-2 flex flex-col justify-between bg-white dark:bg-slate-900">
                                                <div class="space-y-1 max-h-28 overflow-y-auto">
                                                    <template x-for="c in mes.cuotas" :key="c.id">
                                                        <div class="p-1 rounded bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-mono text-[11px] flex items-center justify-between">
                                                            <span class="font-bold text-indigo-900 dark:text-indigo-300 truncate" x-text="c.identificador"></span>
                                                            <span class="px-1 text-slate-500 font-bold" x-text="c.num_cuota"></span>
                                                            <span class="font-black text-slate-900 dark:text-slate-100" x-text="'$' + formatDinero(c.saldo)"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!mes.cuotas || mes.cuotas.length === 0">
                                                        <span class="text-[10px] text-slate-400 italic block text-center py-4">- Sin cuotas -</span>
                                                    </template>
                                                </div>
                                                <div class="mt-2 pt-1.5 border-t border-slate-200 dark:border-slate-700 text-center font-mono font-bold text-xs text-slate-900 dark:text-slate-100 bg-slate-100 dark:bg-slate-800/60 py-1 rounded">
                                                    <span x-text="'Total: $ ' + formatDinero(mes.total)"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- FILA INFERIOR: CONDICIONES DE LA VENTA ACTUAL Y HABILITACIÓN POR VENDEDOR -->
                            <div class="p-3.5 bg-indigo-50/70 dark:bg-indigo-950/40 rounded-xl border border-indigo-200 dark:border-indigo-800 space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-indigo-200 dark:border-indigo-800/80 pb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black uppercase text-indigo-950 dark:text-indigo-200">
                                            Compra Actual a Facturar:
                                        </span>
                                        <span class="text-base font-black font-mono text-indigo-700 dark:text-indigo-300"
                                              x-text="'$ ' + formatDinero(calcularTotal())"></span>
                                    </div>

                                    <!-- Selector de Cuotas en el Modal -->
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300">Plan (Teclas 1-4):</span>
                                        <div class="flex items-center gap-1">
                                            @for($i = 1; $i <= 4; $i++)
                                                <button type="button" @click="seleccionarCuotaPlan({{ $i }})"
                                                        class="px-2.5 py-1 text-xs font-bold rounded-lg border transition cursor-pointer"
                                                        :class="cuotasPlan === {{ $i }} 
                                                            ? 'bg-indigo-600 text-white border-indigo-600 font-black shadow-xs' 
                                                            : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:bg-slate-100'">
                                                    {{ $i }} {{ $i === 1 ? 'Pago' : 'Pagos' }}
                                                </button>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-between text-xs gap-2">
                                    <div class="text-slate-700 dark:text-slate-300">
                                        Plan seleccionado: <span class="font-black text-slate-900 dark:text-white" x-text="cuotasPlan + (cuotasPlan === 1 ? ' cuota' : ' cuotas')"></span> de 
                                        <span class="font-mono font-black text-indigo-700 dark:text-indigo-400 text-sm" x-text="'$ ' + formatDinero(calcularValorCuota(cuotasPlan))"></span>
                                        <span class="text-[11px] opacity-80" x-text="' (Int. ' + porcentajeInteresCuotas(cuotasPlan) + '%)'"></span>
                                    </div>
                                </div>

                                <!-- SECCIÓN DE ADVERTENCIA / HABILITACIÓN DEL VENDEDOR -->
                                <template x-if="infoCredito.estado_crediticio.tiene_mora || (calcularTotal() > (infoCredito.detalle_compras?.limite_disponible || infoCredito.estado_crediticio.limite_disponible)) || infoCredito.estado_crediticio.suspendido">
                                    <div class="p-3 bg-amber-100/90 dark:bg-amber-950/80 border-2 border-amber-400 dark:border-amber-600 rounded-xl space-y-2">
                                        <div class="flex items-center gap-2 text-amber-900 dark:text-amber-200 text-xs font-black uppercase">
                                            <span>⚠️ ATENCIÓN: El cliente posee restricciones de crédito</span>
                                        </div>
                                        <ul class="text-[11px] list-disc list-inside text-amber-900 dark:text-amber-300 space-y-0.5">
                                            <template x-if="infoCredito.estado_crediticio.suspendido">
                                                <li>Cliente suspendido para créditos.</li>
                                            </template>
                                            <template x-if="infoCredito.estado_crediticio.tiene_mora">
                                                <li x-text="'Registra mora por $ ' + formatDinero(infoCredito.detalle_compras?.mora_vencido || infoCredito.estado_crediticio.total_mora) + '.'"></li>
                                            </template>
                                            <template x-if="calcularTotal() > (infoCredito.detalle_compras?.limite_disponible || infoCredito.estado_crediticio.limite_disponible)">
                                                <li x-text="'El total ($ ' + formatDinero(calcularTotal()) + ') supera el disponible ($ ' + formatDinero(infoCredito.detalle_compras?.limite_disponible || infoCredito.estado_crediticio.limite_disponible) + ').'"></li>
                                            </template>
                                        </ul>

                                        <!-- CHECKBOX EXCLUSIVO DE HABILITACIÓN POR VENDEDOR -->
                                        <div class="pt-1.5 border-t border-amber-300 dark:border-amber-700">
                                            <label class="inline-flex items-center gap-2.5 p-2 bg-white dark:bg-slate-900 rounded-lg border-2 border-amber-500 cursor-pointer shadow-sm hover:bg-amber-50 transition">
                                                <input type="checkbox" x-ref="chkAutorizarVendedor" x-model="autorizadoPorVendedor" @change="if (autorizadoPorVendedor) focusBtnAutorizarCredito()" class="w-4 h-4 text-indigo-600 rounded cursor-pointer">
                                                <span class="text-xs font-black text-amber-950 dark:text-white uppercase tracking-tight">
                                                    HABILITAR PARA ESTA COMPRA (Autoriza el vendedor)
                                                </span>
                                            </label>
                                            <p class="text-[10px] text-amber-800 dark:text-amber-400 mt-1">
                                                Al marcar esta casilla, el vendedor asume la autorización excepcional para emitir la factura a crédito y quedará asentado en el legajo.
                                            </p>
                                        </div>
                                    </div>
                                </template>

                                <!-- ESTADO REGULAR APROBADO -->
                                <template x-if="!infoCredito.estado_crediticio.tiene_mora && !infoCredito.estado_crediticio.suspendido && (calcularTotal() <= (infoCredito.detalle_compras?.limite_disponible || infoCredito.estado_crediticio.limite_disponible))">
                                    <div class="p-2.5 bg-emerald-100/90 dark:bg-emerald-950/70 border border-emerald-300 dark:border-emerald-700 rounded-lg text-emerald-900 dark:text-emerald-200 text-xs flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base">✓</span>
                                            <span class="font-bold">Cliente en regla y con margen de crédito disponible. Presione ENTER para autorizar y continuar.</span>
                                        </div>
                                        <span class="text-[10px] font-mono font-black bg-emerald-700 text-white px-2 py-0.5 rounded shrink-0">APROBADO</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer del Modal de Crédito -->
                <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-slate-200 dark:border-slate-800 shrink-0">
                    <button type="button" @click="cerrarModalCredito()"
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 uppercase transition cursor-pointer">
                        Cancelar / Volver (ESC)
                    </button>

                    <div class="flex items-center gap-2">
                        <!-- Botón Secundario Rápido: Autorizar y Facturar Directo (F8) -->
                        <button type="button" @click="autorizarYFacturarDirecto()"
                                x-show="items.length > 0"
                                :disabled="!puedeAutorizarCredito || procesando"
                                class="px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-tight shadow-sm flex items-center gap-1.5 transition cursor-pointer"
                                :class="puedeAutorizarCredito 
                                    ? 'bg-emerald-600 hover:bg-emerald-700 text-white active:scale-98' 
                                    : 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed'">
                            <span>⚡</span>
                            <span>Autorizar y Facturar Directo (F8)</span>
                        </button>

                        <!-- Botón Principal: AUTORIZAR CRÉDITO (ENTER) -->
                        <button type="button" 
                                x-ref="btnAutorizarCreditoModal"
                                @click="confirmarAutorizacionCredito()"
                                :disabled="!puedeAutorizarCredito"
                                class="px-6 py-2.5 rounded-xl font-black text-xs uppercase tracking-tight shadow-md flex items-center gap-2 transition cursor-pointer focus:ring-4 focus:ring-amber-400"
                                :class="puedeAutorizarCredito 
                                    ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-indigo-500/20 active:scale-98' 
                                    : 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed'">
                            <span class="text-sm">✓</span>
                            <span>AUTORIZAR CRÉDITO (ENTER)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MODAL 2: LIQUIDACIÓN DE COBRO MULTIPAGO & CUPONES DE TARJETA        -->
        <!-- =================================================================== -->
        <div x-show="modalCobro" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4"
             @keydown.window.escape="modalCobro = false"
             x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-2xl w-full p-4 sm:p-5 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-3 animate-in fade-in zoom-in-95 duration-150 my-auto"
                 @click.away="modalCobro = false">
                
                <!-- Encabezado del Modal -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-xs">
                            💳
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                Liquidación y Medios de Pago
                            </h3>
                            <div class="flex items-center gap-2 text-[10px] text-slate-500 font-mono mt-0.5">
                                <span class="font-bold text-indigo-600 dark:text-indigo-400" x-text="nombreComprobanteActual"></span>
                                <span>•</span>
                                <span x-text="clienteSeleccionado ? clienteSeleccionado.nombre : 'CONSUMIDOR FINAL'"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="modalCobro = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-lg p-1 cursor-pointer">✕</button>
                </div>

                <!-- Visor Métrico de Cobro (3 Tarjetas Compactas) -->
                <div class="grid grid-cols-3 gap-2">
                    <div class="bg-slate-50 dark:bg-slate-800/80 p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-center">
                        <span class="text-[9px] font-black uppercase text-slate-500 block">Total a Pagar</span>
                        <span class="text-base sm:text-lg font-mono font-black text-slate-900 dark:text-white tabular-nums block"
                              x-text="'$ ' + formatDinero(calcularTotal())"></span>
                    </div>

                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-2 rounded-xl border border-indigo-200 dark:border-indigo-800 text-center">
                        <span class="text-[9px] font-black uppercase text-indigo-700 dark:text-indigo-300 block">Total Cubierto</span>
                        <span class="text-base sm:text-lg font-mono font-black text-indigo-700 dark:text-indigo-400 tabular-nums block"
                              x-text="'$ ' + formatDinero(calcularTotalCubierto())"></span>
                    </div>

                    <div class="p-2 rounded-xl border text-center"
                         :class="{
                             'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800': calcularSaldoRestante() === 0 && calcularVueltoModal() === 0,
                             'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800': calcularSaldoRestante() > 0,
                             'bg-amber-50 dark:bg-amber-950/40 border-amber-300 dark:border-amber-800': calcularVueltoModal() > 0
                         }">
                        <template x-if="calcularSaldoRestante() > 0">
                            <div>
                                <span class="text-[9px] font-black uppercase text-rose-700 dark:text-rose-300 block">Faltan Cubrir</span>
                                <span class="text-base sm:text-lg font-mono font-black text-rose-600 dark:text-rose-400 tabular-nums block"
                                      x-text="'$ ' + formatDinero(calcularSaldoRestante())"></span>
                            </div>
                        </template>
                        <template x-if="calcularSaldoRestante() === 0 && calcularVueltoModal() === 0">
                            <div>
                                <span class="text-[9px] font-black uppercase text-emerald-700 dark:text-emerald-300 block">Estado</span>
                                <span class="text-xs sm:text-sm font-black text-emerald-600 dark:text-emerald-400 mt-1 block">✓ Cubierto 100%</span>
                            </div>
                        </template>
                        <template x-if="calcularVueltoModal() > 0">
                            <div>
                                <span class="text-[9px] font-black uppercase text-amber-700 dark:text-amber-300 block">Vuelto a entregar</span>
                                <span class="text-base sm:text-lg font-mono font-black text-amber-600 dark:text-amber-400 tabular-nums block"
                                      x-text="'$ ' + formatDinero(calcularVueltoModal())"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Botones de Acceso Rápido (1 Clic para fijar el medio) -->
                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                    <span class="text-[10px] font-bold text-slate-400 uppercase mr-1">Rápido:</span>
                    <template x-for="mp in mediosPagoLista" :key="mp.codigo">
                        <button type="button" 
                                @click="establecerPagoUnico(mp.codigo)"
                                class="px-2 py-0.5 rounded text-[11px] font-bold border transition cursor-pointer flex items-center gap-1"
                                :class="pagos.length === 1 && pagos[0].medio_pago === mp.codigo 
                                    ? 'bg-slate-900 dark:bg-indigo-600 text-white border-slate-900 dark:border-indigo-600 shadow-xs' 
                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200'">
                            <span x-text="mp.icono || '💳'"></span>
                            <span x-text="mp.nombre"></span>
                        </button>
                    </template>
                </div>

                <!-- Tabla de Pagos Detallados (Soporte Multipago) -->
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <div class="bg-slate-50 dark:bg-slate-800/80 px-3 py-1.5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between text-[11px] font-black uppercase text-slate-600 dark:text-slate-400">
                        <span>Medios de Pago Aplicados</span>
                        <button type="button" @click="agregarPago()" 
                                class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1 font-bold lowercase first-letter:uppercase cursor-pointer">
                            <span>+ agregar otro medio</span>
                        </button>
                    </div>

                    <div class="p-2 space-y-2 max-h-[220px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="(pago, pIndex) in pagos" :key="pIndex">
                            <div class="pt-2 first:pt-0 grid grid-cols-12 gap-2 items-center">
                                
                                <!-- Col 1: Selector de Medio de Pago (4 columnas) -->
                                <div class="col-span-4">
                                    <select :value="pago.medio_pago"
                                            @change="cambiarMedioPagoFila(pIndex, $event.target.value)"
                                            class="w-full text-xs font-bold py-1.5 px-2 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                                        <template x-for="mpOpt in mediosPagoLista" :key="mpOpt.codigo">
                                            <option :value="mpOpt.codigo" x-text="(mpOpt.icono || '💳') + ' ' + mpOpt.nombre"></option>
                                        </template>
                                    </select>
                                </div>

                                <!-- Col 2: Monto a Pagar con este medio (3 columnas) -->
                                <div class="col-span-3 relative">
                                    <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-xs font-bold text-slate-400">$</span>
                                    <input type="number" step="0.01" min="0.01" 
                                           x-model.number="pago.monto"
                                           placeholder="0.00"
                                           class="w-full text-xs font-mono font-bold py-1.5 pl-5 pr-1.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right">
                                </div>

                                <!-- Col 3: N° Cupón / Referencia (4 columnas) -->
                                <div class="col-span-4">
                                    <input type="text" 
                                           x-model="pago.cupon"
                                           :placeholder="pago.tipo_base === 'debito' || pago.tipo_base === 'tarjeta_credito' ? 'N° Cupón tarjeta' : 'Ref. / Obs opcional'"
                                           :class="(pago.tipo_base === 'debito' || pago.tipo_base === 'tarjeta_credito') ? 'border-sky-400 bg-sky-50/50 dark:bg-sky-950/20' : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900'"
                                           class="w-full text-xs font-mono py-1.5 px-2 rounded-lg text-slate-900 dark:text-white placeholder-slate-400 focus:ring-1 focus:ring-indigo-500">
                                </div>

                                <!-- Col 4: Botón Quitar Fila (1 columna) -->
                                <div class="col-span-1 text-center">
                                    <button type="button" @click="quitarPago(pIndex)" 
                                            :disabled="pagos.length === 1"
                                            :class="pagos.length === 1 ? 'opacity-20 cursor-not-allowed text-slate-400' : 'text-rose-500 hover:text-rose-700 cursor-pointer'"
                                            class="p-1 font-bold text-sm">
                                        ✕
                                    </button>
                                </div>

                            </div>
                        </template>
                    </div>
                </div>

                <!-- Panel Condicional si hay Pago en Efectivo: Calculador de Vuelto -->
                <template x-if="tienePagoEfectivo()">
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700 dark:text-slate-300 uppercase text-[10px]">💵 Calculador de Vuelto en Efectivo:</span>
                            <button type="button" @click="pagaCon = calcularTotal()" class="text-[11px] font-bold text-emerald-600 hover:underline cursor-pointer">
                                Importe Exacto
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="relative w-40">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-xs font-bold text-slate-400">$</span>
                                <input type="number" step="100" 
                                       x-model.number="pagaCon" 
                                       placeholder="Paga con..."
                                       class="w-full text-xs font-mono font-bold py-1 pl-6 pr-2 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            </div>

                            <div class="flex flex-wrap gap-1">
                                <button type="button" @click="pagaCon = 2000" class="px-1.5 py-0.5 bg-white dark:bg-slate-700 border rounded text-[10px] font-bold font-mono cursor-pointer">$2k</button>
                                <button type="button" @click="pagaCon = 5000" class="px-1.5 py-0.5 bg-white dark:bg-slate-700 border rounded text-[10px] font-bold font-mono cursor-pointer">$5k</button>
                                <button type="button" @click="pagaCon = 10000" class="px-1.5 py-0.5 bg-white dark:bg-slate-700 border rounded text-[10px] font-bold font-mono cursor-pointer">$10k</button>
                                <button type="button" @click="pagaCon = 20000" class="px-1.5 py-0.5 bg-white dark:bg-slate-700 border rounded text-[10px] font-bold font-mono cursor-pointer">$20k</button>
                            </div>

                            <div class="ml-auto text-right">
                                <span class="text-[9px] text-slate-400 block uppercase font-bold">Vuelto</span>
                                <span class="font-mono font-black text-sm text-emerald-600 dark:text-emerald-400"
                                      x-text="'$ ' + formatDinero(calcularVueltoModal())"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Panel Condicional si hay Pago a Crédito Personal: Selector de Cuotas -->
                <template x-if="tienePagoCreditoPersonal()">
                    <div class="p-2.5 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-black text-indigo-950 dark:text-indigo-200 uppercase text-[10px]">📋 Plan de Cuotas a Crédito Personal:</span>
                            <span class="text-[10px] font-bold text-indigo-700 dark:text-indigo-300" x-text="'Interés lote: ' + tasaInteres + '%'"></span>
                        </div>

                        <div class="grid grid-cols-4 gap-1.5">
                            <template x-for="n in [1, 2, 3, 4]" :key="n">
                                <button type="button" @click="seleccionarCuotaPlan(n)"
                                        :class="cuotasPlan === n ? 'bg-indigo-600 text-white font-black shadow-xs' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'"
                                        class="py-1.5 px-2 rounded-lg text-center transition cursor-pointer">
                                    <span class="text-xs font-bold block" x-text="n + (n === 1 ? ' Pago' : ' Cuotas')"></span>
                                    <span class="text-[10px] font-mono block opacity-90" x-text="'$' + formatDinero(calcularValorCuota(n))"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Footer del Modal: Cancelar / Confirmar y Emitir -->
                <div class="flex items-center justify-between pt-2.5 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="modalCobro = false" 
                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs py-2 px-4 rounded-xl transition uppercase cursor-pointer">
                        Cancelar (ESC)
                    </button>

                    <button type="button" @click="enviarVenta()"
                            :disabled="procesando || calcularSaldoRestante() > 0"
                            :class="procesando || calcularSaldoRestante() > 0 ? 'bg-slate-300 dark:bg-slate-700 text-slate-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg active:scale-98 cursor-pointer'"
                            class="py-2.5 px-6 rounded-xl font-black text-xs uppercase tracking-wide transition flex items-center gap-2">
                        <span x-text="procesando ? 'Procesando Emisión...' : (esPresupuesto ? 'Confirmar Presupuesto' : (esRemito ? 'Confirmar Remito' : (esNotaCredito ? 'Confirmar Nota Crédito' : 'Confirmar y Facturar (ENTER)')))"></span>
                        <span class="text-[10px] bg-black/20 text-white px-1.5 py-0.5 rounded font-mono">↵</span>
                    </button>
                </div>

            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MODAL 3: VENTA EXITOSA & IMPRESIÓN DIRECTA CON VISTA PREVIA        -->
        <!-- =================================================================== -->
        <div x-show="modalExito" 
             class="fixed inset-0 z-50 overflow-hidden bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4"
             @keydown.window.escape="cerrarModalExito()"
             x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-6xl shadow-2xl border border-slate-200 dark:border-slate-800 flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
                 style="height: 94vh; max-height: 96vh; min-height: 720px; max-width: 1200px;">
                
                <!-- Encabezado del Modal -->
                <div class="flex flex-wrap items-center justify-between px-4 py-2.5 bg-slate-100 dark:bg-slate-800/90 border-b border-slate-200 dark:border-slate-700/80 shrink-0 gap-2">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/70 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-black shadow-inner shrink-0">
                            ✓
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">¡Venta y Pagaré Generados Exitosamente!</span>
                                <span class="font-mono font-black text-sm text-slate-900 dark:text-white" x-text="'#' + ventaExitosa.comprobante"></span>
                                <template x-if="ventaExitosa.cae">
                                    <span class="inline-flex items-center gap-1 bg-sky-100 text-sky-800 text-[10px] font-mono font-bold px-2 py-0.5 rounded border border-sky-300 dark:bg-sky-950/60 dark:text-sky-300">
                                        <span>CAE:</span>
                                        <span x-text="ventaExitosa.cae"></span>
                                        <span class="text-[9px] text-sky-600 dark:text-sky-400" x-text="'(Vto: ' + ventaExitosa.cae_vencimiento + ')'"></span>
                                    </span>
                                </template>
                                <template x-if="ventaExitosa.tipo_emision === 'fiscal_hasar'">
                                    <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-[10px] font-mono font-bold px-2 py-0.5 rounded border border-amber-300 dark:bg-amber-950/60 dark:text-amber-300">
                                        <span>🖨️ Fiscal Hasar 2G</span>
                                    </span>
                                </template>
                            </div>
                            <span class="text-xs text-slate-600 dark:text-slate-300 font-bold" x-text="'Total Facturado: $ ' + formatDinero(ventaExitosa.total)"></span>
                        </div>
                    </div>

                    <!-- Botones de Acción de Cabecera -->
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                x-ref="btnImprimirExito"
                                @click="imprimirComprobanteExito(esTrelew)"
                                class="px-4 py-2 rounded-xl text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white shadow-md flex items-center gap-1.5 transition active:scale-98 cursor-pointer focus:ring-4 focus:ring-amber-400">
                            <span>🖨️</span>
                            <span x-text="esTrelew ? '5. Imprimir y Volver a Comenzar (ENTER / F9)' : 'Mandar a Imprimir (ENTER / F9)'"></span>
                        </button>
                        <button type="button" 
                                x-ref="btnSiguienteVentaExito"
                                @click="cerrarModalExito()"
                                class="px-3.5 py-2 rounded-xl text-xs font-black bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm flex items-center gap-1.5 transition active:scale-98 cursor-pointer focus:ring-4 focus:ring-amber-400"
                                title="Iniciar siguiente venta">
                            <span>➕</span>
                            <span class="hidden sm:inline">Siguiente Venta (F2 / ESC)</span>
                        </button>
                        <button type="button" 
                                @click="cerrarModalExito()" 
                                class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-2 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer" 
                                title="Cerrar modal (Esc)">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Barra de Selección de Comprobantes (Botones Horizontales) -->
                <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-700/60 shrink-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider mr-1">Comprobante:</span>

                        <!-- Botón Pagaré si es crédito -->
                        <template x-if="ventaExitosa.pagare_url">
                            <button type="button" 
                                    @click="seleccionarDocumentoExito('pagare')"
                                    :class="documentoExitoTipo === 'pagare' 
                                        ? 'bg-amber-600 text-white font-black shadow-sm ring-2 ring-amber-400/50' 
                                        : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-amber-50 dark:hover:bg-slate-700'"
                                    class="py-1.5 px-3 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                <span>📋</span>
                                <span>Pagaré de Crédito</span>
                            </button>
                        </template>

                        <!-- Botón Ticket 80mm -->
                        <button type="button" 
                                @click="seleccionarDocumentoExito('ticket')"
                                :class="documentoExitoTipo === 'ticket' 
                                    ? 'bg-emerald-600 text-white font-black shadow-sm ring-2 ring-emerald-400/50' 
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-emerald-50 dark:hover:bg-slate-700'"
                                class="py-1.5 px-3 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <span>🖨️</span>
                            <span>Ticket Térmico (80mm)</span>
                        </button>

                        <!-- Botón Factura A4 -->
                        <button type="button" 
                                @click="seleccionarDocumentoExito('a4')"
                                :class="documentoExitoTipo === 'a4' 
                                    ? 'bg-indigo-600 text-white font-black shadow-sm ring-2 ring-indigo-400/50' 
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-indigo-50 dark:hover:bg-slate-700'"
                                class="py-1.5 px-3 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <span>📄</span>
                            <span>Factura A4</span>
                        </button>

                        <!-- Botón Ficha de la Factura -->
                        <button type="button" 
                                @click="seleccionarDocumentoExito('ficha')"
                                :class="documentoExitoTipo === 'ficha' 
                                    ? 'bg-slate-800 dark:bg-slate-200 text-white dark:text-slate-900 font-black shadow-sm' 
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700'"
                                class="py-1.5 px-3 rounded-lg text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <span>👁️</span>
                            <span>Ver Ficha de Factura</span>
                        </button>
                    </div>
                </div>

                <!-- Vista Previa Directa Debajo de los Botones (Mismo Modal con Altura Completa 94vh) -->
                <div class="flex-1 min-h-0 w-full bg-slate-100 dark:bg-slate-950 p-2 sm:p-3 relative overflow-hidden flex flex-col"
                     style="flex: 1 1 auto; height: calc(94vh - 120px); min-height: 600px;">
                    <iframe id="iframeComprobanteExito"
                            :src="comprobanteActivoUrl"
                            @load="onIframeExitoLoad()"
                            class="w-full flex-1 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-xl shadow-inner block"
                            style="width: 100%; height: 100%; min-height: 600px; border: 0;"
                            loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MODAL 4: CORRECCIÓN POR RECUENTO FÍSICO / SIN STOCK                 -->
        <!-- =================================================================== -->
        <div x-show="modalRecuento" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border-2 border-amber-500/80 dark:border-amber-500/60 space-y-4"
                 @click.away="modalRecuento = false; focusBarcode();">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold text-base shrink-0 border border-amber-300 dark:border-amber-700">
                            ⚠️
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                Artículo sin Stock Disponible
                            </h3>
                            <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold uppercase tracking-wider block">
                                Sucursal: {{ $sucursalActiva->nombre ?? 'Trelew' }}
                            </span>
                        </div>
                    </div>
                    <button type="button" @click="modalRecuento = false; focusBarcode();" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                </div>

                <!-- Detalle del Producto -->
                <div class="bg-slate-50 dark:bg-slate-900/70 p-3 rounded-xl border border-slate-200 dark:border-slate-700 text-xs space-y-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-bold text-slate-900 dark:text-white block text-sm" x-text="productoSinStock?.descripcion"></span>
                            <span class="font-mono text-[10px] text-slate-500" x-text="'Cód: ' + (productoSinStock?.codigo || '-') + ' | EAN: ' + (productoSinStock?.codigo_barra || '-')"></span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-black text-indigo-700 dark:text-indigo-400 text-sm block" x-text="'$ ' + formatDinero(productoSinStock?.precio_venta)"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200/70 dark:border-slate-800">
                        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-300">Stock actual en sistema:</span>
                        <span class="text-xs font-mono font-black px-2 py-0.5 rounded"
                              :class="(productoSinStock?.stock || 0) <= 0 ? 'bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-800' : 'bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300'"
                              x-text="(productoSinStock?.stock || 0) + ' unidades'">
                        </span>
                    </div>
                </div>

                <!-- Explicación operativa -->
                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800/60 text-xs text-amber-900 dark:text-amber-200 space-y-1">
                    <p class="font-bold">
                        ¿Desea registrar una corrección de stock por recuento físico en mostrador?
                    </p>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300 leading-tight">
                        Al autorizar el recuento, se registrará el ingreso por ajuste físico de la cantidad indicada para habilitar la venta y que, al emitir la factura, el stock se descuente y vuelva a quedar en cero (evitando generar stock negativo).
                    </p>
                </div>

                <!-- Input Cantidad a ingresar por recuento -->
                <div class="space-y-3 bg-white dark:bg-slate-900/50 p-3 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                            Cantidad a ingresar por recuento físico:
                        </label>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="if (recuentoCantidad > 1) recuentoCantidad--"
                                    class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 font-black text-slate-700 dark:text-slate-200 hover:bg-slate-200 transition">
                                -
                            </button>
                            <input type="number" min="0.01" step="1" 
                                   x-model.number="recuentoCantidad"
                                   @keydown.enter.prevent="confirmarRecuento()"
                                   class="w-24 text-center font-mono font-black text-base py-1.5 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                            <button type="button" @click="recuentoCantidad++"
                                    class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 font-black text-slate-700 dark:text-slate-200 hover:bg-slate-200 transition">
                                +
                            </button>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400 font-semibold ml-2">
                                unidad(es) físicas encontradas
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                            Motivo de la corrección:
                        </label>
                        <input type="text" x-model="recuentoMotivo" 
                               class="w-full text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                               placeholder="Recuento físico previo a facturación en mostrador">
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" @click="modalRecuento = false; focusBarcode();"
                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-xl transition uppercase">
                        Cancelar (No Cargar)
                    </button>
                    <button type="button" @click="confirmarRecuento()"
                            :disabled="procesandoRecuento"
                            class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black text-xs py-2 px-5 rounded-xl shadow-md transition uppercase flex items-center gap-1.5">
                        <span x-show="!procesandoRecuento">✓ Autorizar Recuento y Cargar (+<span x-text="recuentoCantidad"></span>)</span>
                        <span x-show="procesandoRecuento">Procesando ajuste...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MODAL 5: APERTURA DE CAJA AL INICIO DE LA JORNADA                   -->
        <!-- =================================================================== -->
        <div x-show="modalAbrirCaja" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border-2 border-emerald-500/80 space-y-4"
                 @click.away="modalAbrirCaja = false">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-black text-base shrink-0 border border-emerald-300 dark:border-emerald-700">
                            🟢
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                Apertura de Caja (Inicio de Jornada)
                            </h3>
                            <span class="text-[10px] text-emerald-700 dark:text-emerald-400 font-bold uppercase tracking-wider block">
                                Sucursal: {{ $sucursalActiva->nombre ?? 'Sucursal' }}
                            </span>
                        </div>
                    </div>
                    <button type="button" @click="modalAbrirCaja = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                </div>

                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-800/60 text-xs text-emerald-900 dark:text-emerald-200 space-y-1">
                    <p class="font-bold">
                        Ingrese el monto de dinero en efectivo con el que se inicia la caja.
                    </p>
                    <p class="text-[11px] text-emerald-800 dark:text-emerald-300 leading-tight">
                        Este monto corresponde al cambio o fondo de caja disponible en el cajón físico al abrir la sucursal.
                    </p>
                </div>

                <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                            Monto Inicial en Efectivo ($ ARS):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">$</span>
                            <input type="number" step="0.01" min="0" 
                                   x-model.number="montoApertura"
                                   @keydown.enter.prevent="enviarAperturaCaja()"
                                   class="w-full pl-8 pr-3 font-mono font-black text-lg py-2 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right focus:ring-2 focus:ring-emerald-500"
                                   placeholder="0.00" autofocus>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                            Observaciones de Apertura (Opcional):
                        </label>
                        <input type="text" x-model="obsApertura" 
                               class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                               placeholder="Ej: Cambio inicial de mostrador...">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" @click="modalAbrirCaja = false"
                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-xl transition uppercase">
                        Cancelar
                    </button>
                    <button type="button" @click="enviarAperturaCaja()"
                            :disabled="procesandoApertura"
                            class="bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black text-xs py-2 px-5 rounded-xl shadow-md transition uppercase flex items-center gap-1.5 cursor-pointer">
                        <span x-show="!procesandoApertura">✓ Confirmar Apertura de Caja</span>
                        <span x-show="procesandoApertura">Abriendo caja...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MODAL 6: CIERRE DE JORNADA ANTERIOR + APERTURA DE HOY              -->
        <!-- =================================================================== -->
        <div x-show="modalCierreAnterior" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border-2 border-amber-500 space-y-4"
                 @click.away="modalCierreAnterior = false">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-950/80 text-amber-700 dark:text-amber-400 flex items-center justify-center font-black text-base shrink-0 border border-amber-300 dark:border-amber-700">
                            ⚠️
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                Arqueo Jornada Anterior y Apertura de Hoy
                            </h3>
                            <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold uppercase tracking-wider block">
                                Cierre pendiente de Caja #<span x-text="cajaAnteriorId"></span>
                            </span>
                        </div>
                    </div>
                    <button type="button" @click="modalCierreAnterior = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                </div>

                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800/60 text-xs text-amber-900 dark:text-amber-200 space-y-1">
                    <p class="font-bold">
                        La caja anterior no fue cerrada al terminar el día.
                    </p>
                    <p class="text-[11px] text-amber-800 dark:text-amber-300 leading-tight">
                        Ingrese el dinero en efectivo que había en el cajón para asentar el Cierre Z y la revisión de fallo de caja de la jornada anterior, y el fondo inicial para hoy.
                    </p>
                </div>

                <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                    <!-- Efectivo Esperado del Sistema -->
                    <div class="flex items-center justify-between p-2.5 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Efectivo Teórico Esperado (Caja Anterior):</span>
                        <span class="font-mono font-black text-slate-900 dark:text-white text-sm" x-text="'$ ' + formatDinero(efectivoEsperadoAnterior)"></span>
                    </div>

                    <!-- Input Efectivo Real Contado -->
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                            Efectivo Real Contado en el Cajón (Cierre Anterior):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">$</span>
                            <input type="number" step="0.01" min="0" 
                                   x-model.number="contadoAnterior"
                                   class="w-full pl-8 pr-3 font-mono font-black text-base py-2 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right focus:ring-2 focus:ring-amber-500"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <!-- Fallo de caja en vivo -->
                    <div class="p-2.5 rounded-lg border flex items-center justify-between text-xs"
                         :class="{
                             'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300': (contadoAnterior - efectivoEsperadoAnterior) === 0,
                             'bg-sky-50 dark:bg-sky-950/40 border-sky-200 dark:border-sky-800 text-sky-800 dark:text-sky-300': (contadoAnterior - efectivoEsperadoAnterior) > 0,
                             'bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300': (contadoAnterior - efectivoEsperadoAnterior) < 0
                         }">
                        <span class="font-bold">
                            <span x-show="(contadoAnterior - efectivoEsperadoAnterior) === 0">✓ Arqueo Exacto (Sin diferencias)</span>
                            <span x-show="(contadoAnterior - efectivoEsperadoAnterior) > 0">▲ Sobrante de Caja:</span>
                            <span x-show="(contadoAnterior - efectivoEsperadoAnterior) < 0">▼ Faltante de Caja (Fallo):</span>
                        </span>
                        <span class="font-mono font-black text-sm" 
                              x-text="'$ ' + formatDinero(Math.abs(contadoAnterior - efectivoEsperadoAnterior))">
                        </span>
                    </div>

                    <!-- Fondo Inicial para la Caja de Hoy -->
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                        <label class="block text-[11px] font-black uppercase text-emerald-700 dark:text-emerald-400 mb-1">
                            Fondo Inicial en Efectivo para la Caja de HOY ($ ARS):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">$</span>
                            <input type="number" step="0.01" min="0" 
                                   x-model.number="fondoHoyAnterior"
                                   class="w-full pl-8 pr-3 font-mono font-black text-base py-2 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right focus:ring-2 focus:ring-emerald-500"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                            Observaciones / Justificación de Cierre:
                        </label>
                        <input type="text" x-model="obsCierreAnterior" 
                               class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                               placeholder="Ej: Cierre automático al inicio de la siguiente jornada...">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" @click="modalCierreAnterior = false"
                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-xl transition uppercase">
                        Cancelar
                    </button>
                    <button type="button" @click="enviarCierreAnteriorYAbrir()"
                            :disabled="procesandoCierreAnterior"
                            class="bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white font-black text-xs py-2 px-5 rounded-xl shadow-md transition uppercase flex items-center gap-1.5 cursor-pointer">
                        <span x-show="!procesandoCierreAnterior">✓ Asentar Cierre Z y Abrir Hoy</span>
                        <span x-show="procesandoCierreAnterior">Procesando cierre y apertura...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MODAL 7: INGRESO O RETIRO MANUAL DE EFECTIVO (MOVIMIENTO DE CAJA)   -->
        <!-- =================================================================== -->
        <div x-show="modalMovimientoCaja" 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/75 backdrop-blur-xs flex items-center justify-center p-4"
             x-cloak>
            <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4"
                 @click.away="modalMovimientoCaja = false">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950/80 text-blue-700 dark:text-blue-400 flex items-center justify-center font-black text-base shrink-0 border border-blue-300 dark:border-blue-700">
                            💸
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                Movimiento Manual de Efectivo
                            </h3>
                            <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">
                                Turno de Caja #{{ $cajaActiva->id ?? '' }} • {{ $sucursalActiva->nombre ?? 'Sucursal' }}
                            </span>
                        </div>
                    </div>
                    <button type="button" @click="modalMovimientoCaja = false" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
                </div>

                <!-- Selector de Tipo: Ingreso (+) vs Retiro (-) -->
                <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 dark:bg-slate-900 rounded-xl">
                    <button type="button" 
                            @click="movimientoTipo = 'retiro'"
                            :class="movimientoTipo === 'retiro' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                            class="py-2 px-3 rounded-lg font-black text-xs uppercase transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>📤 Retiro de Efectivo (-)</span>
                    </button>
                    <button type="button" 
                            @click="movimientoTipo = 'ingreso'"
                            :class="movimientoTipo === 'ingreso' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                            class="py-2 px-3 rounded-lg font-black text-xs uppercase transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>📥 Ingreso de Efectivo (+)</span>
                    </button>
                </div>

                <!-- Explicación -->
                <div class="p-2.5 rounded-xl border text-xs"
                     :class="movimientoTipo === 'retiro' ? 'bg-rose-50 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900 text-rose-900 dark:text-rose-200' : 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-900 text-emerald-900 dark:text-emerald-200'">
                    <span x-show="movimientoTipo === 'retiro'" class="font-bold">
                        ⚠️ Retiro / Salida: Para pagar compras, fletes, adelantos o gastos menores. Se restará del efectivo esperado al cierre.
                    </span>
                    <span x-show="movimientoTipo === 'ingreso'" class="font-bold">
                        ✓ Ingreso / Entrada: Para cobrar un pendiente manual, inyección de cambio o corrección positiva. Se sumará al efectivo esperado.
                    </span>
                </div>

                <div class="space-y-3 bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                            Monto en Efectivo ($ ARS):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">$</span>
                            <input type="number" step="0.01" min="0.01" 
                                   x-model.number="movimientoMonto"
                                   class="w-full pl-8 pr-3 font-mono font-black text-lg py-2 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right focus:ring-2 focus:ring-blue-500"
                                   placeholder="0.00" autofocus>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1">
                            Motivo / Justificación del Movimiento (Obligatorio):
                        </label>
                        <input type="text" x-model="movimientoMotivo" required
                               class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                               :placeholder="movimientoTipo === 'retiro' ? 'Ej: Pago de flete mercadería / Compra de insumos...' : 'Ej: Cobro de saldo pendiente / Aporte de cambio...'">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 mb-1">
                            Nro Comprobante / Recibo Adjunto (Opcional):
                        </label>
                        <input type="text" x-model="movimientoComprobante" 
                               class="w-full text-xs py-1.5 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                               placeholder="Ej: Ticket #1234 / Factura B-0012">
                    </div>
                </div>

                <!-- Historial reciente de movimientos en el turno actual -->
                <template x-if="movimientosCaja && movimientosCaja.length > 0">
                    <div class="space-y-1">
                        <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Movimientos Registrados en este Turno:</span>
                        <div class="max-h-28 overflow-y-auto space-y-1 pr-1">
                            <template x-for="m in movimientosCaja" :key="m.id">
                                <div class="p-2 rounded-lg bg-slate-100 dark:bg-slate-900 flex items-center justify-between text-[11px]">
                                    <div class="flex items-center gap-2">
                                        <span class="font-black px-1.5 py-0.5 rounded text-[9px] uppercase"
                                              :class="m.tipo === 'ingreso' ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300'"
                                              x-text="m.tipo === 'ingreso' ? '+ INGRESO' : '- RETIRO'">
                                        </span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200" x-text="m.motivo"></span>
                                        <span x-show="m.comprobante" class="text-slate-400 text-[10px]" x-text="'(' + m.comprobante + ')'"></span>
                                    </div>
                                    <span class="font-mono font-black"
                                          :class="m.tipo === 'ingreso' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400'"
                                          x-text="(m.tipo === 'ingreso' ? '+$ ' : '-$ ') + formatDinero(m.monto)">
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" @click="modalMovimientoCaja = false"
                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs py-2 px-4 rounded-xl transition uppercase">
                        Cerrar
                    </button>
                    <button type="button" @click="enviarMovimientoCaja()"
                            :disabled="procesandoMovimiento"
                            :class="movimientoTipo === 'retiro' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                            class="text-white font-black text-xs py-2 px-5 rounded-xl shadow-md transition uppercase flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                        <span x-show="!procesandoMovimiento" x-text="movimientoTipo === 'retiro' ? '✓ Asentar Retiro de Dinero' : '✓ Asentar Ingreso de Dinero'"></span>
                        <span x-show="procesandoMovimiento">Registrando...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- MODAL FICHA Y LEGAJO DEL CLIENTE (SIN SALIR DE FACTURACIÓN)        -->
        <!-- =================================================================== -->
        <div x-show="modalFichaCliente" 
             class="fixed inset-0 z-50 overflow-hidden bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-2 sm:p-4"
             @keydown.window.escape="cerrarFichaClienteModal()"
             x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-7xl shadow-2xl border border-slate-200 dark:border-slate-700 flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150"
                 style="height: 94vh; max-height: 96vh; min-height: 720px;">
                
                <!-- Encabezado del Modal -->
                <div class="flex items-center justify-between px-4 py-3 bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                            📄
                        </span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                    Ficha y Legajo del Cliente
                                </h3>
                                <template x-if="clienteSeleccionado">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-indigo-100 dark:bg-indigo-950 text-indigo-800 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-800"
                                          x-text="clienteSeleccionado.nombre + ' (DNI: ' + clienteSeleccionado.dni + ')'">
                                    </span>
                                </template>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Ver y editar el legajo del cliente directamente sin perder los artículos ni el progreso de la factura
                            </p>
                        </div>
                    </div>

                    <!-- Botones de Acción del Modal -->
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="irAlInicioFichaCliente()"
                                title="Volver a la portada de la ficha del cliente"
                                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900 flex items-center gap-1 shadow-xs transition cursor-pointer">
                            <span>🏠</span>
                            <span class="hidden sm:inline">Inicio</span>
                        </button>
                        <button type="button" 
                                @click="recargarFichaCliente()"
                                title="Actualizar datos de la ficha"
                                class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600 flex items-center gap-1 shadow-xs transition cursor-pointer">
                            <span>🔄</span>
                            <span class="hidden sm:inline">Recargar</span>
                        </button>
                        <button type="button" 
                                @click="cerrarFichaClienteModal()"
                                class="px-3 py-1.5 rounded-lg text-xs font-black bg-rose-600 hover:bg-rose-700 text-white shadow-sm flex items-center gap-1 transition cursor-pointer">
                            <span>✕</span>
                            <span>Volver a Facturar</span>
                        </button>
                    </div>
                </div>

                <!-- Contenido con iframe optimizado embebido a altura completa -->
                <div class="flex-1 min-h-0 w-full bg-slate-50 dark:bg-slate-900 relative overflow-hidden"
                     style="flex: 1 1 auto; height: calc(94vh - 65px); min-height: 640px;">
                    <iframe id="iframeFichaCliente"
                            :src="fichaClienteUrl"
                            class="w-full h-full border-0 bg-transparent block"
                            style="width: 100%; height: 100%; min-height: 640px; border: 0;"
                            loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>

    </div>

    <!-- Script de Reactividad Alpine.js del POS -->
    <script>
        function posApp() {
            return {
                esTrelew: {{ !$esTdf ? 'true' : 'false' }},
                esTdf: {{ $esTdf ? 'true' : 'false' }},
                esChubut: {{ $esChubut ? 'true' : 'false' }},
                tipoComprobante: 'FACTURA_B',
                get esFacturaA() { return this.tipoComprobante === 'FACTURA_A'; },
                get esFacturaB() { return this.tipoComprobante === 'FACTURA_B'; },
                get esFacturaC() { return this.tipoComprobante === 'FACTURA_C'; },
                get esRemito() { return this.tipoComprobante === 'REMITO'; },
                get esPresupuesto() { return this.tipoComprobante === 'PRESUPUESTO'; },
                get esNotaCredito() { return this.tipoComprobante.includes('NOTA_CREDITO'); },

                get nombreComprobanteActual() {
                    switch(this.tipoComprobante) {
                        case 'FACTURA_A': return 'FACTURA A (IVA 21% Discriminado)';
                        case 'FACTURA_B': return this.esTdf ? 'FACTURA B (Exento Ley 19.640)' : 'FACTURA B (Consumidor Final)';
                        case 'FACTURA_C': return 'FACTURA C (Régimen Simplificado)';
                        case 'REMITO': return 'REMITO DE SALIDA (Descuenta Stock - Sin Facturar)';
                        case 'PRESUPUESTO': return 'PRESUPUESTO (Cotización - No Afecta Stock)';
                        case 'NOTA_CREDITO_B': return 'NOTA DE CRÉDITO B';
                        case 'NOTA_CREDITO_A': return 'NOTA DE CRÉDITO A';
                        case 'NOTA_CREDITO': return 'NOTA DE CRÉDITO';
                        default: return this.tipoComprobante;
                    }
                },

                get textoBotonEmitir() {
                    if (this.esPresupuesto) return 'Emitir Presupuesto';
                    if (this.esRemito) return 'Emitir Remito de Salida';
                    if (this.esNotaCredito) return 'Emitir Nota de Crédito';
                    if (this.esFacturaA) return 'Emitir Factura A';
                    if (this.esFacturaC) return 'Emitir Factura C';
                    return 'Emitir Factura B';
                },

                get clienteTieneCuit() {
                    if (!this.clienteSeleccionado) return false;
                    let doc = String(this.clienteSeleccionado.dni || '').replace(/\D/g, '');
                    return doc.length === 11;
                },
                seleccionarTipoComprobante(tipo) {
                    if (tipo === 'FACTURA_A' || tipo === 'NOTA_CREDITO_A') {
                        if (!this.clienteSeleccionado) {
                            alert('Para comprobantes tipo A primero debe buscar y seleccionar un cliente con CUIT (11 dígitos).');
                            this.focusCliente();
                            return;
                        }
                        if (!this.clienteTieneCuit) {
                            alert('El cliente seleccionado tiene DNI (' + (this.clienteSeleccionado.dni || 'Sin DNI') + '). Para comprobantes tipo A ante ARCA es obligatorio registrar su CUIT de 11 dígitos.');
                            return;
                        }
                    }
                    this.tipoComprobante = tipo;
                },
                presupuestoCargado: @json($presupuestoInicial ?? null),
                items: [],
                barcodeQuery: '',
                sugerenciasProductos: [],
                clienteSeleccionado: @json($clienteInicial ?? null),
                clienteQuery: '',
                sugerenciasClientes: [],
                indiceSugerenciaCliente: 0,
                medioPago: '{{ $medioPagoInicial ?? ($mediosPago->firstWhere("codigo", "efectivo")?->codigo ?? $mediosPago->first()?->codigo ?? "efectivo") }}',
                mediosPagoLista: @json($mediosPago),
                referenciaPago: '',
                pagos: [],

                inicializarPagosPorDefecto() {
                    let tot = this.calcularTotal();
                    let mp = this.medioPagoActual || this.mediosPagoLista.find(m => m.codigo === 'efectivo') || this.mediosPagoLista[0];
                    this.pagos = [
                        {
                            id_medio_pago: mp?.id || null,
                            medio_pago: mp?.codigo || 'efectivo',
                            nombre: mp?.nombre || 'Efectivo',
                            tipo_base: mp?.tipo_base || 'efectivo',
                            monto: tot,
                            cupon: this.referenciaPago || '',
                            codigo_forma: this.mapearCodigoFormaHasar(mp?.tipo_base || 'efectivo')
                        }
                    ];
                    if (mp?.tipo_base === 'efectivo') {
                        this.pagaCon = tot;
                    }
                },
                mapearCodigoFormaHasar(tipoBase) {
                    switch (tipoBase) {
                        case 'debito': return 'TarjetaDeDebito';
                        case 'tarjeta_credito': return 'TarjetaDeCredito';
                        case 'transferencia': return 'TransferenciaBancaria';
                        case 'cuenta_corriente': return 'CuentaCorriente';
                        case 'cheque': return 'Cheque';
                        default: return 'Efectivo';
                    }
                },
                agregarPago() {
                    let saldo = this.calcularSaldoRestante();
                    let mp = this.mediosPagoLista.find(m => m.codigo === 'efectivo') || this.mediosPagoLista[0];
                    this.pagos.push({
                        id_medio_pago: mp?.id || null,
                        medio_pago: mp?.codigo || 'efectivo',
                        nombre: mp?.nombre || 'Efectivo',
                        tipo_base: mp?.tipo_base || 'efectivo',
                        monto: saldo > 0 ? saldo : 0,
                        cupon: '',
                        codigo_forma: 'Efectivo'
                    });
                },
                quitarPago(idx) {
                    if (this.pagos.length > 1) {
                        this.pagos.splice(idx, 1);
                    }
                },
                cambiarMedioPagoFila(idx, codigo) {
                    let mp = this.mediosPagoLista.find(m => m.codigo === codigo);
                    if (!mp) return;
                    if (mp.tipo_base === 'credito' && !this.clienteSeleccionado) {
                        alert('Para abonar con Crédito Personal debe seleccionar un cliente registrado.');
                        return;
                    }
                    this.pagos[idx].id_medio_pago = mp.id;
                    this.pagos[idx].medio_pago = mp.codigo;
                    this.pagos[idx].nombre = mp.nombre;
                    this.pagos[idx].tipo_base = mp.tipo_base;
                    this.pagos[idx].codigo_forma = this.mapearCodigoFormaHasar(mp.tipo_base);
                    if (this.pagos.length === 1) {
                        this.medioPago = mp.codigo;
                    }
                },
                establecerPagoUnico(codigo) {
                    let mp = this.mediosPagoLista.find(m => m.codigo === codigo);
                    if (!mp) return;
                    if (mp.tipo_base === 'credito' && !this.clienteSeleccionado) {
                        alert('Para abonar con Crédito Personal debe seleccionar un cliente registrado.');
                        this.focusCliente();
                        return;
                    }
                    let tot = this.calcularTotal();
                    this.medioPago = mp.codigo;
                    this.pagos = [
                        {
                            id_medio_pago: mp.id,
                            medio_pago: mp.codigo,
                            nombre: mp.nombre,
                            tipo_base: mp.tipo_base,
                            monto: tot,
                            cupon: '',
                            codigo_forma: this.mapearCodigoFormaHasar(mp.tipo_base)
                        }
                    ];
                    if (mp.tipo_base === 'efectivo') {
                        this.pagaCon = tot;
                    }
                },
                calcularTotalCubierto() {
                    return Math.round((this.pagos || []).reduce((acc, p) => acc + (parseFloat(p.monto) || 0), 0) * 100) / 100;
                },
                calcularSaldoRestante() {
                    return Math.max(0, Math.round((this.calcularTotal() - this.calcularTotalCubierto()) * 100) / 100);
                },
                calcularVueltoModal() {
                    let efectivo = (this.pagos || []).filter(p => p.tipo_base === 'efectivo').reduce((acc, p) => acc + (parseFloat(p.monto) || 0), 0);
                    let noEfectivo = (this.pagos || []).filter(p => p.tipo_base !== 'efectivo').reduce((acc, p) => acc + (parseFloat(p.monto) || 0), 0);
                    let restoParaEfectivo = Math.max(0, this.calcularTotal() - noEfectivo);
                    let pagando = (this.pagaCon && this.pagaCon > 0) ? this.pagaCon : efectivo;
                    return Math.max(0, Math.round((pagando - restoParaEfectivo) * 100) / 100);
                },
                obtenerCuponesStr() {
                    let cupones = (this.pagos || []).map(p => (p.cupon || '').trim()).filter(c => c.length > 0);
                    return cupones.length > 0 ? cupones.join(', ') : (this.referenciaPago || null);
                },
                tienePagoTarjeta() {
                    return (this.pagos || []).some(p => p.tipo_base === 'debito' || p.tipo_base === 'tarjeta_credito');
                },
                tienePagoEfectivo() {
                    return (this.pagos || []).some(p => p.tipo_base === 'efectivo');
                },
                tienePagoCreditoPersonal() {
                    return (this.pagos || []).some(p => p.tipo_base === 'credito' || p.medio_pago === 'credito_personal');
                },
                get medioPagoActual() {
                    return this.mediosPagoLista.find(m => m.codigo === this.medioPago) || null;
                },
                get esCredito() {
                    let mp = this.medioPagoActual;
                    return (mp && (mp.tipo_base === 'credito' || mp.codigo === 'credito_personal')) || this.medioPago === 'credito_personal';
                },
                get esEfectivo() {
                    let mp = this.medioPagoActual;
                    return (mp && (mp.tipo_base === 'efectivo' || mp.permite_vuelto)) || this.medioPago === 'efectivo';
                },
                get pasoActualFlujo() {
                    if (this.modalExito) return 6;
                    if (this.modalCobro) return 5;
                    if (this.modalCredito) return 4;
                    if (!this.clienteSeleccionado) return 1;
                    if (this.items.length === 0) {
                        return (this.montoGenerico && String(this.montoGenerico).trim() !== '') ? 3 : 2;
                    }
                    if (this.esCredito && !this.creditoAutorizado) return 4;
                    return 5;
                },
                irAPaso(paso) {
                    if (paso === 1) {
                        this.modalCredito = false;
                        this.modalCobro = false;
                        if (this.clienteSeleccionado) {
                            this.deseleccionarCliente();
                        } else {
                            this.focusCliente();
                        }
                        return;
                    }
                    if (paso === 2) {
                        this.modalCredito = false;
                        this.modalCobro = false;
                        this.focusBarcode();
                        return;
                    }
                    if (paso === 3) {
                        this.modalCredito = false;
                        this.modalCobro = false;
                        this.focusMontoGenerico();
                        return;
                    }
                    if (paso === 4) {
                        if (!this.clienteSeleccionado) {
                            this.focusCliente();
                            return;
                        }
                        if (parseFloat(this.montoGenerico) > 0) {
                            this.agregarArticuloGenerico(false);
                        }
                        this.abrirModalCredito();
                        return;
                    }
                    if (paso === 5) {
                        if (!this.clienteSeleccionado && this.esCredito) {
                            this.focusCliente();
                            return;
                        }
                        if (parseFloat(this.montoGenerico) > 0) {
                            this.agregarArticuloGenerico(false);
                        }
                        if (this.items.length === 0) {
                            this.focusBarcode();
                            return;
                        }
                        this.abrirModalPago();
                        return;
                    }
                    if (paso === 6 && this.modalExito) {
                        this.imprimirComprobanteExito(this.esTrelew);
                    }
                },
                avanzarPasoFlujo() {
                    let paso = this.pasoActualFlujo;
                    if (paso === 1) {
                        if (this.clienteQuery.trim().length >= 2) {
                            this.confirmarClienteEnter();
                        } else {
                            this.focusCliente();
                        }
                    } else if (paso === 2) {
                        if (this.barcodeQuery.trim().length > 0) {
                            this.procesarCodigoBarra();
                        } else if (parseFloat(this.montoGenerico) > 0) {
                            this.procesarMontoGenericoEnter();
                        } else {
                            this.focusBarcode();
                        }
                    } else if (paso === 3) {
                        if (parseFloat(this.montoGenerico) > 0) {
                            this.procesarMontoGenericoEnter();
                        } else {
                            this.focusMontoGenerico();
                        }
                    } else if (paso === 4) {
                        if (this.modalCredito) {
                            if (this.puedeAutorizarCredito) {
                                this.confirmarAutorizacionCredito();
                            }
                        } else {
                            this.abrirModalCredito();
                        }
                    } else if (paso === 5) {
                        this.abrirModalPago();
                    } else if (paso === 6) {
                        if (!this.yaImprimioExito) {
                            this.imprimirComprobanteExito(this.esTrelew);
                        } else {
                            this.cerrarModalExito();
                        }
                    }
                },
                seleccionarMedioPago(mp) {
                    if (mp.tipo_base === 'credito' || mp.codigo === 'credito_personal') {
                        if (!this.clienteSeleccionado) {
                            alert('Para operar con ' + mp.nombre + ' debe seleccionar un cliente primero.');
                            this.focusCliente();
                            return;
                        }
                        this.abrirModalCredito();
                        return;
                    }
                    this.medioPago = mp.codigo;
                    if (this.esEfectivo) {
                        if (!this.pagaCon || this.pagaCon < this.calcularTotal()) {
                            this.pagaCon = this.calcularTotal();
                        }
                    }
                },
                cuotasPlan: 1,
                tasaInteres: {{ (float)$tasaInteresCredito }},
                primeraSinInteres: {{ ($presentacion?->primera_cuota_sin_interes ?? true) ? 'true' : 'false' }},
                montoGenerico: '',
                infoCredito: null,
                modalCredito: false,
                modalCobro: false,
                modalExito: false,
                modalRecuento: false,
                productoSinStock: null,
                recuentoCantidad: 1,
                recuentoMotivo: 'Recuento físico previo a facturación en mostrador',
                procesandoRecuento: false,

                // Estado de Autorización de Crédito Personal y Vendedor
                autorizadoPorVendedor: false,
                creditoAutorizado: false,
                nuevoLimiteInput: '',
                guardandoLimite: false,
                nuevoComentarioInput: '',
                guardandoComentario: false,

                // Modal Ficha / Legajo del Cliente
                modalFichaCliente: false,
                fichaClienteUrl: '',
                abrirFichaClienteModal(clienteId = null) {
                    let id = clienteId || this.clienteSeleccionado?.id;
                    if (!id) return;
                    let targetUrl = '{{ url('/clientes') }}/' + id + '?embed=1';
                    let iframe = document.getElementById('iframeFichaCliente');
                    if (iframe) {
                        iframe.src = 'about:blank';
                        setTimeout(() => {
                            iframe.src = targetUrl;
                        }, 40);
                    }
                    this.fichaClienteUrl = targetUrl;
                    this.modalFichaCliente = true;
                },
                irAlInicioFichaCliente() {
                    let id = this.clienteSeleccionado?.id;
                    if (!id) return;
                    let targetUrl = '{{ url('/clientes') }}/' + id + '?embed=1';
                    let iframe = document.getElementById('iframeFichaCliente');
                    if (iframe) {
                        iframe.src = targetUrl;
                    }
                    this.fichaClienteUrl = targetUrl;
                },
                recargarFichaCliente() {
                    let iframe = document.getElementById('iframeFichaCliente');
                    if (iframe && iframe.contentWindow) {
                        iframe.contentWindow.location.reload();
                    }
                },
                async cerrarFichaClienteModal() {
                    this.modalFichaCliente = false;
                    let iframe = document.getElementById('iframeFichaCliente');
                    if (iframe) {
                        iframe.src = 'about:blank';
                    }
                    this.fichaClienteUrl = '';
                    if (this.clienteSeleccionado) {
                        await this.consultarInfoCredito(this.clienteSeleccionado.id);
                    }
                    if (this.modalCredito) {
                        this.focusBtnAutorizarCredito();
                    } else {
                        this.focusInicialSegunModo();
                    }
                },

                // Control de Caja / Turnos
                cajaEstado: '{{ $cajaActiva ? ($requiereCierreAnterior ? 'cierre_anterior' : 'abierta') : 'cerrada' }}',
                cajaId: {{ $cajaActiva->id ?? 'null' }},
                modalAbrirCaja: false,
                montoApertura: 0,
                obsApertura: '',
                procesandoApertura: false,
                modalCierreAnterior: false,
                cajaAnteriorId: {{ $cajaActiva->id ?? 'null' }},
                efectivoEsperadoAnterior: {{ (float)($totalesCajaActiva['efectivo_esperado'] ?? 0) }},
                contadoAnterior: 0,
                fondoHoyAnterior: 0,
                obsCierreAnterior: '',
                procesandoCierreAnterior: false,

                // Movimientos manuales de caja (Ingreso / Retiro)
                modalMovimientoCaja: false,
                movimientoTipo: 'retiro',
                movimientoMonto: '',
                movimientoMotivo: '',
                movimientoComprobante: '',
                procesandoMovimiento: false,
                movimientosCaja: [],
                efectivoEsperadoCaja: {{ (float)($totalesCajaActiva['efectivo_esperado'] ?? 0) }},
                totalIngresosCaja: {{ (float)($totalesCajaActiva['total_ingresos'] ?? 0) }},
                totalRetirosCaja: {{ (float)($totalesCajaActiva['total_retiros'] ?? 0) }},

                pagaCon: 0,
                procesando: false,
                ventaExitosa: {
                    id: null,
                    comprobante: '',
                    total: 0,
                    ticket_url: '#',
                    a4_url: '#',
                    redirect_url: '#',
                    id_credito: null,
                    pagare_url: null
                },

                // Modal de Venta Exitosa con Impresión y Vista Previa Integrada
                documentoExitoTipo: 'ticket',
                yaImprimioExito: false,
                iframeExitoCargado: false,
                impresionPendienteExito: false,
                autoCerrarTrasImprimir: false,
                get comprobanteActivoUrl() {
                    if (!this.modalExito || !this.ventaExitosa || !this.ventaExitosa.id) return 'about:blank';
                    let url = '';
                    if (this.documentoExitoTipo === 'pagare') {
                        url = this.ventaExitosa.pagare_url || '';
                    } else if (this.documentoExitoTipo === 'ticket') {
                        url = this.ventaExitosa.ticket_url || '';
                    } else if (this.documentoExitoTipo === 'a4') {
                        url = this.ventaExitosa.a4_url || '';
                    } else if (this.documentoExitoTipo === 'ficha') {
                        url = this.ventaExitosa.redirect_url || '';
                    }
                    if (!url || url === '#' || url === 'null') return 'about:blank';
                    if (url.indexOf('embed=1') === -1) {
                        url += (url.indexOf('?') === -1 ? '?embed=1' : '&embed=1');
                    }
                    return url;
                },
                seleccionarDocumentoExito(tipo) {
                    this.iframeExitoCargado = false;
                    this.documentoExitoTipo = tipo;
                },
                onIframeExitoLoad() {
                    if (!this.modalExito) return;
                    let iframe = document.getElementById('iframeComprobanteExito');
                    if (!iframe || !iframe.contentWindow) return;
                    let currentUrl = this.comprobanteActivoUrl;
                    if (!currentUrl || currentUrl === 'about:blank') return;

                    this.iframeExitoCargado = true;

                    try {
                        iframe.contentWindow.addEventListener('keydown', (ev) => {
                            this.manejarAtajoGlobal(ev);
                        });
                    } catch (err) {
                        // Ignore cross-origin if any
                    }

                    if (this.impresionPendienteExito) {
                        this.impresionPendienteExito = false;
                        this.ejecutarImpresionIframe(iframe, this.autoCerrarTrasImprimir);
                    } else {
                        this.focusBtnImprimirExito();
                    }
                },
                imprimirComprobanteExito(volverAComenzar = false) {
                    if (this.ventaExitosa && this.ventaExitosa.tipo_emision === 'fiscal_hasar') {
                        if (this.ventaExitosa.hasar_payload) {
                            this.enviarAHasarBridge(this.ventaExitosa.hasar_payload, true);
                        }
                        if (volverAComenzar) {
                            this.cerrarModalExito();
                        }
                        return;
                    }

                    let iframe = document.getElementById('iframeComprobanteExito');
                    if (!iframe || !iframe.contentWindow) return;

                    if (!this.iframeExitoCargado) {
                        this.impresionPendienteExito = true;
                        this.autoCerrarTrasImprimir = volverAComenzar;
                        return;
                    }

                    this.ejecutarImpresionIframe(iframe, volverAComenzar);
                },
                ejecutarImpresionIframe(iframe, autoCerrar = false) {
                    this.yaImprimioExito = true;
                    let win = iframe.contentWindow;
                    let finalizo = false;
                    let alTerminar = () => {
                        if (finalizo) return;
                        finalizo = true;
                        win.onafterprint = null;
                        window.focus();
                        if (autoCerrar) {
                            setTimeout(() => {
                                if (this.modalExito) {
                                    this.cerrarModalExito();
                                }
                            }, 120);
                        } else {
                            this.$nextTick(() => {
                                if (this.$refs.btnSiguienteVentaExito) {
                                    this.$refs.btnSiguienteVentaExito.focus();
                                }
                            });
                        }
                    };

                    win.onafterprint = alTerminar;
                    win.focus();
                    win.print();

                    // En navegadores de escritorio donde print() es síncrono, continúa aquí al cerrar el diálogo
                    setTimeout(() => {
                        alTerminar();
                    }, 250);
                },
                async enviarAHasarBridge(payload, esReintento = false) {
                    let puertoHTTP = 8585;
                    let puertoHTTPS = 8586;
                    let urls = [
                        `http://127.0.0.1:${puertoHTTP}/imprimir`,
                        `https://127.0.0.1:${puertoHTTPS}/imprimir`
                    ];
                    if (window.location.protocol === 'https:') {
                        urls = [
                            `https://127.0.0.1:${puertoHTTPS}/imprimir`,
                            `http://127.0.0.1:${puertoHTTP}/imprimir`
                        ];
                    }

                    let exito = false;
                    let ultimoError = null;
                    let resData = null;

                    for (let u of urls) {
                        try {
                            let res = await fetch(u, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json; charset=utf-8' },
                                body: JSON.stringify(payload)
                            });
                            if (res.ok) {
                                resData = await res.json();
                                exito = true;
                                break;
                            } else {
                                let errText = await res.text();
                                ultimoError = `Error HTTP ${res.status}: ${errText}`;
                            }
                        } catch (err) {
                            ultimoError = err;
                        }
                    }

                    if (exito) {
                        console.log('✅ Hasar Fiscal impreso exitosamente:', resData);
                        if (esReintento) {
                            alert('✅ Ticket enviado e impreso correctamente en la Impresora Fiscal Hasar.');
                        }
                        return true;
                    } else {
                        console.warn('⚠️ No se pudo comunicar con el Puente Fiscal Hasar:', ultimoError);
                        alert('⚠️ ATENCIÓN: No se pudo comunicar con la Impresora Fiscal Hasar.\n\n' +
                              '1. Verifique que el programa "Puente Fiscal Hasar" esté abierto en la barra de tareas de Windows.\n' +
                              '2. Si está accediendo por HTTPS en Brave o Chrome, configure el sitio para "Permitir contenido no seguro" (haga clic en el icono a la izquierda de la barra de direcciones).\n' +
                              '3. Verifique que la impresora fiscal esté encendida con luz verde y papel.');
                        return false;
                    }
                },
                async consultarProximoNumeroHasar() {
                    let urls = [
                        'http://127.0.0.1:8585/proximo-numero',
                        'https://127.0.0.1:8586/proximo-numero'
                    ];
                    if (window.location.protocol === 'https:') {
                        urls = [
                            'https://127.0.0.1:8586/proximo-numero',
                            'http://127.0.0.1:8585/proximo-numero'
                        ];
                    }
                    for (let u of urls) {
                        try {
                            let res = await fetch(u, { method: 'GET', headers: { 'Accept': 'application/json' } });
                            if (res.ok) {
                                let json = await res.json();
                                if (json && json.exito) return json;
                            }
                        } catch (e) {
                            // continuar
                        }
                    }
                    return null;
                },
                cerrarModalExito() {
                    this.modalExito = false;
                    this.iniciarNuevaVenta();
                },

                initPos() {
                    if (this.clienteSeleccionado) {
                        this.consultarInfoCredito(this.clienteSeleccionado.id);
                    }

                    if (this.presupuestoCargado && this.presupuestoCargado.items && this.presupuestoCargado.items.length > 0) {
                        this.items = JSON.parse(JSON.stringify(this.presupuestoCargado.items));
                    }

                    this.focusInicialSegunModo();

                    window.addEventListener('keydown', (e) => {
                        this.manejarAtajoGlobal(e);
                    });
                },

                manejarAtajoGlobal(e) {
                    // 1. Escape: cerrar modales o reiniciar venta tras éxito
                    if (e.key === 'Escape') {
                        if (this.modalExito) {
                            e.preventDefault();
                            this.cerrarModalExito();
                            return;
                        }
                        if (this.modalCredito) {
                            e.preventDefault();
                            this.cerrarModalCredito();
                            return;
                        }
                        if (this.modalCobro || this.modalRecuento || this.modalFichaCliente) {
                            e.preventDefault();
                            this.modalCobro = false;
                            this.modalRecuento = false;
                            this.modalFichaCliente = false;
                            this.focusInicialSegunModo();
                            return;
                        }
                        this.focusInicialSegunModo();
                        return;
                    }

                    // 2. Teclas de Función F2, F3, F4, F5, F8, F9
                    if (e.key === 'F2') {
                        e.preventDefault();
                        if (this.modalExito) {
                            this.cerrarModalExito();
                        } else {
                            this.irAPaso(1);
                        }
                        return;
                    }
                    if (e.key === 'F3') {
                        e.preventDefault();
                        if (!this.modalExito) {
                            this.irAPaso(2);
                        }
                        return;
                    }
                    if (e.key === 'F4') {
                        e.preventDefault();
                        if (!this.modalExito) {
                            this.irAPaso(3);
                        }
                        return;
                    }
                    if (e.key === 'F5') {
                        e.preventDefault();
                        if (this.modalExito) {
                            this.imprimirComprobanteExito(this.esTrelew);
                            return;
                        }
                        if (this.modalCredito) {
                            if (this.puedeAutorizarCredito) {
                                this.confirmarAutorizacionCredito();
                            }
                            return;
                        }
                        this.irAPaso(4);
                        return;
                    }
                    if (e.key === 'F8') {
                        e.preventDefault();
                        if (this.modalCredito && this.puedeAutorizarCredito && this.items.length > 0) {
                            this.autorizarYFacturarDirecto();
                            return;
                        }
                        if (!this.modalExito) {
                            this.irAPaso(5);
                        }
                        return;
                    }
                    if (e.key === 'F9') {
                        e.preventDefault();
                        if (this.modalExito) {
                            this.imprimirComprobanteExito(this.esTrelew);
                        }
                        return;
                    }

                    let tag = document.activeElement?.tagName || '';
                    let enInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag) && document.activeElement?.type !== 'checkbox';

                    // 3. Selección rápida de cuotas 1..4 cuando está en modal de crédito o con crédito autorizado (sin estar escribiendo en un input)
                    if (!enInput && ['1', '2', '3', '4'].includes(e.key)) {
                        if (this.modalCredito || (this.esCredito && this.clienteSeleccionado && !this.modalExito && !this.modalCobro)) {
                            e.preventDefault();
                            this.seleccionarCuotaPlan(parseInt(e.key, 10));
                            return;
                        }
                    }

                    // 4. Enter en Modal de Éxito (Paso 5: Imprimir y volver a comenzar)
                    if (this.modalExito && e.key === 'Enter') {
                        e.preventDefault();
                        if (!this.yaImprimioExito) {
                            this.imprimirComprobanteExito(this.esTrelew);
                        } else {
                            this.cerrarModalExito();
                        }
                        return;
                    }

                    // 5. Enter en Modal de Crédito Personal (Paso 3: Autorizar si está aprobado)
                    if (this.modalCredito && e.key === 'Enter' && !enInput) {
                        e.preventDefault();
                        if (this.puedeAutorizarCredito) {
                            this.confirmarAutorizacionCredito();
                        }
                        return;
                    }

                    // 6. Enter en pantalla principal cuando no hay foco en un input ni botón específico
                    if (!this.modalCredito && !this.modalCobro && !this.modalExito && !this.modalRecuento && !this.modalFichaCliente) {
                        if (e.key === 'Enter' && !enInput && tag !== 'BUTTON' && tag !== 'A') {
                            e.preventDefault();
                            this.avanzarPasoFlujo();
                            return;
                        }
                    }
                },

                focusInicialSegunModo() {
                    if (this.esTrelew) {
                        if (!this.clienteSeleccionado) {
                            this.focusCliente();
                        } else if (this.items.length === 0) {
                            this.focusMontoGenerico();
                        } else if (!this.creditoAutorizado) {
                            this.abrirModalCredito();
                        } else {
                            this.focusBtnFacturar();
                        }
                    } else {
                        this.focusBarcode();
                    }
                },

                focusCliente() {
                    this.$nextTick(() => {
                        if (this.$refs.clienteInput) {
                            this.$refs.clienteInput.focus();
                            this.$refs.clienteInput.select?.();
                        }
                        setTimeout(() => {
                            if (this.$refs.clienteInput && document.activeElement !== this.$refs.clienteInput) {
                                this.$refs.clienteInput.focus();
                            }
                        }, 60);
                    });
                },

                focusMontoGenerico() {
                    this.$nextTick(() => {
                        if (this.$refs.montoGenericoInput && document.activeElement !== this.$refs.montoGenericoInput) {
                            this.$refs.montoGenericoInput.focus();
                            this.$refs.montoGenericoInput.select?.();
                        }
                        setTimeout(() => {
                            if (this.$refs.montoGenericoInput && document.activeElement !== this.$refs.montoGenericoInput) {
                                this.$refs.montoGenericoInput.focus();
                            }
                        }, 50);
                    });
                },

                focusBtnAutorizarCredito() {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (!this.modalCredito) return;
                            if (this.puedeAutorizarCredito && this.$refs.btnAutorizarCreditoModal) {
                                this.$refs.btnAutorizarCreditoModal.focus();
                            } else if (!this.puedeAutorizarCredito && this.$refs.chkAutorizarVendedor) {
                                this.$refs.chkAutorizarVendedor.focus();
                            }
                        }, 50);
                    });
                },

                focusBtnFacturar() {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (this.$refs.btnFacturarPrincipal) {
                                this.$refs.btnFacturarPrincipal.focus();
                            }
                        }, 50);
                    });
                },

                focusBtnImprimirExito() {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            if (this.modalExito && this.$refs.btnImprimirExito) {
                                this.$refs.btnImprimirExito.focus();
                            }
                        }, 60);
                    });
                },

                focusBarcode() {
                    this.$nextTick(() => {
                        if (this.$refs.barcodeInput) {
                            this.$refs.barcodeInput.focus();
                        }
                    });
                },

                calcularSubtotalItem(item) {
                    let cant = Number(item.cantidad) || 0;
                    let pu = Number(item.precio_unitario) || 0;
                    let pct = Math.max(0, Math.min(100, Number(item.descuento_porcentaje) || 0));
                    let bruto = cant * pu;
                    let desc = bruto * (pct / 100);
                    return Math.max(0, Math.round((bruto - desc) * 100) / 100);
                },

                calcularSubtotal() {
                    let sum = 0;
                    for (let it of this.items) {
                        sum += this.calcularSubtotalItem(it);
                    }
                    return Math.round(sum * 100) / 100;
                },

                calcularSubtotalBruto() {
                    let sum = 0;
                    for (let it of this.items) {
                        sum += (Number(it.cantidad) || 0) * (Number(it.precio_unitario) || 0);
                    }
                    return Math.round(sum * 100) / 100;
                },

                calcularDescuentoTotalItems() {
                    let sum = 0;
                    for (let it of this.items) {
                        let cant = Number(it.cantidad) || 0;
                        let pu = Number(it.precio_unitario) || 0;
                        let pct = Math.max(0, Math.min(100, Number(it.descuento_porcentaje) || 0));
                        sum += (cant * pu) * (pct / 100);
                    }
                    return Math.round(sum * 100) / 100;
                },

                // % de interés total según cantidad de cuotas (ej: 6% -> 1:0%, 2:6%, 3:12%, 4:18%)
                porcentajeInteresCuotas(cuotas) {
                    let n = Math.max(1, parseInt(cuotas) || 1);
                    let tramos = this.primeraSinInteres ? n - 1 : n;
                    return Math.round(this.tasaInteres * tramos * 100) / 100;
                },

                calcularRecargo(cuotas = null) {
                    let sub = this.calcularSubtotal();
                    if (this.esCredito) {
                        let pct = this.porcentajeInteresCuotas(cuotas ?? this.cuotasPlan);
                        return Math.round(sub * (pct / 100) * 100) / 100;
                    }
                    let mp = this.medioPagoActual;
                    if (mp && Number(mp.recargo_porcentaje) > 0) {
                        return Math.round(sub * (Number(mp.recargo_porcentaje) / 100) * 100) / 100;
                    }
                    return 0;
                },

                descuentoManual: 0,
                tipoDescuentoManual: 'monto',

                calcularDescuentoManual() {
                    let sub = this.calcularSubtotal();
                    let val = Number(this.descuentoManual) || 0;
                    if (val <= 0) return 0;
                    if (this.tipoDescuentoManual === 'porcentaje') {
                        return Math.round(sub * (val / 100) * 100) / 100;
                    }
                    return Math.min(sub, Math.round(val * 100) / 100);
                },

                calcularDescuento() {
                    let sub = this.calcularSubtotal();
                    let descMedio = 0;
                    let mp = this.medioPagoActual;
                    if (mp && Number(mp.descuento_porcentaje) > 0) {
                        descMedio = Math.round(sub * (Number(mp.descuento_porcentaje) / 100) * 100) / 100;
                    }
                    let totalDesc = descMedio + this.calcularDescuentoManual();
                    return Math.min(sub, Math.round(totalDesc * 100) / 100);
                },

                calcularTotal(cuotas = null) {
                    return Math.max(0, Math.round((this.calcularSubtotal() + this.calcularRecargo(cuotas) - this.calcularDescuento()) * 100) / 100);
                },

                // valor_cuota = subtotal * (1 + tasa * tramos) / cuotas
                calcularValorCuota(cuotas) {
                    if (!cuotas || cuotas < 1) cuotas = 1;
                    let totalFinanciado = this.calcularTotal(cuotas);
                    return Math.round((totalFinanciado / cuotas) * 100) / 100;
                },

                formatDinero(val) {
                    if (val === undefined || val === null || isNaN(val)) return '0,00';
                    return Number(val).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                // Procesamiento de código de barras
                async procesarCodigoBarra() {
                    let q = this.barcodeQuery.trim();
                    if (!q) return;

                    try {
                        let res = await fetch(`{{ route('ventas.buscar-producto') }}?q=${encodeURIComponent(q)}&sucursal_id={{ $sucursalActiva->id ?? 1 }}`);
                        let data = await res.json();

                        if (data.exact && data.producto) {
                            this.intentarAgregarProducto(data.producto);
                            this.barcodeQuery = '';
                            this.sugerenciasProductos = [];
                            this.focusBarcode();
                        } else if (data.productos && data.productos.length === 1) {
                            this.intentarAgregarProducto(data.productos[0]);
                            this.barcodeQuery = '';
                            this.sugerenciasProductos = [];
                            this.focusBarcode();
                        } else if (data.productos && data.productos.length > 1) {
                            this.sugerenciasProductos = data.productos;
                        } else {
                            alert(`No se encontró ningún artículo con el código "${q}".`);
                            this.barcodeQuery = '';
                        }
                    } catch (e) {
                        console.error('Error buscando producto:', e);
                    }
                },

                async buscarProductosSugeridos() {
                    let q = this.barcodeQuery.trim();
                    if (q.length < 2) {
                        this.sugerenciasProductos = [];
                        return;
                    }

                    try {
                        let res = await fetch(`{{ route('ventas.buscar-producto') }}?q=${encodeURIComponent(q)}&sucursal_id={{ $sucursalActiva->id ?? 1 }}`);
                        let data = await res.json();
                        this.sugerenciasProductos = data.productos || [];
                    } catch (e) {
                        console.error('Error en sugerencias:', e);
                    }
                },

                intentarAgregarProducto(prod, cantidadDeseada = 1) {
                    if (!prod) return false;
                    let exist = this.items.find(it => it.id_producto === prod.id);
                    let cantActual = exist ? exist.cantidad : 0;
                    let cantFinal = cantActual + cantidadDeseada;

                    // Si controla stock y la cantidad supera el stock disponible en la sucursal
                    if (prod.controla_stock && cantFinal > (Number(prod.stock) || 0)) {
                        let faltante = Math.max(1, cantFinal - Math.max(0, Number(prod.stock) || 0));
                        this.abrirModalRecuento(prod, cantFinal, faltante);
                        return false;
                    }

                    this.agregarItem(prod, cantidadDeseada);
                    return true;
                },

                abrirModalRecuento(prod, cantDeseada, faltante) {
                    this.productoSinStock = {
                        id: prod.id,
                        codigo: prod.codigo,
                        codigo_barra: prod.codigo_barra,
                        descripcion: prod.descripcion,
                        precio_venta: prod.precio_venta,
                        controla_stock: prod.controla_stock,
                        stock: Number(prod.stock) || 0,
                        cantDeseada: cantDeseada
                    };
                    this.recuentoCantidad = Math.max(1, Math.round(faltante));
                    this.recuentoMotivo = 'Recuento físico previo a facturación en mostrador';
                    this.modalRecuento = true;
                },

                async confirmarRecuento() {
                    if (!this.productoSinStock || this.procesandoRecuento) return;

                    let cantAjuste = parseFloat(this.recuentoCantidad);
                    if (isNaN(cantAjuste) || cantAjuste <= 0) {
                        alert('Por favor ingrese una cantidad válida mayor a cero.');
                        return;
                    }

                    this.procesandoRecuento = true;

                    try {
                        let res = await fetch(`{{ route('ventas.ajuste-recuento-rapido') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id_producto: this.productoSinStock.id,
                                id_sucursal: {{ $sucursalActiva->id ?? 1 }},
                                cantidad: cantAjuste,
                                motivo: this.recuentoMotivo || 'Recuento físico previo a facturación en mostrador'
                            })
                        });

                        let data = await res.json();

                        if (!res.ok || !data.success) {
                            alert(data.error || data.message || 'Error al registrar el recuento.');
                            this.procesandoRecuento = false;
                            return;
                        }

                        // Actualizar datos de stock del producto retornado
                        let prodActualizado = data.producto;

                        // Actualizar stock en lista de sugerencias si está abierta
                        let enSugerencias = this.sugerenciasProductos.find(p => p.id === prodActualizado.id);
                        if (enSugerencias) {
                            enSugerencias.stock = prodActualizado.stock;
                        }

                        // Cantidad requerida a agregar a la factura
                        let cantAAgregar = 1;
                        if (this.productoSinStock.cantDeseada) {
                            let yaEnCarrito = this.items.find(it => it.id_producto === prodActualizado.id)?.cantidad || 0;
                            cantAAgregar = Math.max(1, this.productoSinStock.cantDeseada - yaEnCarrito);
                        }

                        this.agregarItem(prodActualizado, cantAAgregar);

                        this.modalRecuento = false;
                        this.productoSinStock = null;
                        this.barcodeQuery = '';
                        this.sugerenciasProductos = [];
                        this.focusBarcode();

                    } catch (e) {
                        console.error('Error al registrar recuento:', e);
                        alert('Error de conexión al registrar el recuento físico.');
                    } finally {
                        this.procesandoRecuento = false;
                    }
                },

                agregarItem(prod, cantidad = 1) {
                    this.creditoAutorizado = false;
                    let exist = this.items.find(it => it.id_producto === prod.id && (!it.descuento_porcentaje || it.descuento_porcentaje == 0));
                    if (exist) {
                        exist.cantidad += cantidad;
                        exist.stock = prod.stock;
                    } else {
                        this.items.push({
                            id_producto: prod.id,
                            codigo_producto: prod.codigo,
                            codigo: prod.codigo,
                            codigo_barra: prod.codigo_barra,
                            descripcion: prod.descripcion,
                            cantidad: cantidad,
                            precio_unitario: Number(prod.precio_venta) || 0,
                            descuento_porcentaje: 0,
                            controla_stock: prod.controla_stock,
                            stock: prod.stock
                        });
                    }
                },

                agregarArticuloGenerico(enfocar = true) {
                    let monto = parseFloat(this.montoGenerico);
                    if (isNaN(monto) || monto <= 0) {
                        alert('Ingrese un monto válido para el artículo varios.');
                        return false;
                    }

                    this.creditoAutorizado = false;
                    this.items.push({
                        id_producto: {{ $productoGenerico->id ?? 'null' }},
                        codigo_producto: '{{ $productoGenerico->codigo ?? '1' }}',
                        codigo: '{{ $productoGenerico->codigo ?? '1' }}',
                        codigo_barra: '{{ $productoGenerico->codigo_barra ?? '1' }}',
                        descripcion: 'VARIOS - ARTICULO GENERAL',
                        cantidad: 1,
                        precio_unitario: monto,
                        descuento_porcentaje: 0,
                        controla_stock: false,
                        stock: 9999
                    });

                    this.montoGenerico = '';
                    if (enfocar) {
                        this.focusBarcode();
                    }
                    return true;
                },

                async procesarMontoGenericoEnter() {
                    let monto = parseFloat(this.montoGenerico);
                    if (!isNaN(monto) && monto > 0) {
                        this.agregarArticuloGenerico(false);
                        if (this.esTrelew || this.esCredito) {
                            if (!this.clienteSeleccionado) {
                                this.focusCliente();
                            } else {
                                await this.abrirModalCredito();
                            }
                        } else {
                            this.focusBarcode();
                        }
                        return;
                    }

                    if (this.items.length > 0) {
                        if (this.esTrelew || this.esCredito) {
                            if (!this.clienteSeleccionado) {
                                this.focusCliente();
                            } else {
                                await this.abrirModalCredito();
                            }
                        } else {
                            this.abrirModalPago();
                        }
                        return;
                    }

                    alert('Ingrese un monto válido para continuar.');
                    this.focusMontoGenerico();
                },

                cambiarCantidad(index, delta) {
                    if (!this.items[index]) return;
                    let item = this.items[index];
                    let nueva = item.cantidad + delta;
                    if (nueva <= 0) {
                        this.quitarItem(index);
                        return;
                    }
                    if (delta > 0 && item.controla_stock && nueva > (Number(item.stock) || 0)) {
                        let faltante = nueva - Math.max(0, Number(item.stock) || 0);
                        this.abrirModalRecuento(item, nueva, faltante);
                        return;
                    }
                    this.creditoAutorizado = false;
                    item.cantidad = nueva;
                },

                validarItem(index) {
                    if (!this.items[index]) return;
                    let item = this.items[index];
                    if (item.cantidad <= 0) item.cantidad = 1;
                    if (item.precio_unitario < 0) item.precio_unitario = 0;
                    if (item.descuento_porcentaje === undefined || item.descuento_porcentaje === null || isNaN(item.descuento_porcentaje) || item.descuento_porcentaje < 0) {
                        item.descuento_porcentaje = 0;
                    } else if (item.descuento_porcentaje > 100) {
                        item.descuento_porcentaje = 100;
                    }
                    this.creditoAutorizado = false;
                    if (item.controla_stock && item.cantidad > (Number(item.stock) || 0)) {
                        let solicitada = item.cantidad;
                        let faltante = solicitada - Math.max(0, Number(item.stock) || 0);
                        item.cantidad = Math.max(1, Math.min(solicitada - 1, Number(item.stock) || 0));
                        this.abrirModalRecuento(item, solicitada, faltante);
                    }
                },

                quitarItem(index) {
                    this.items.splice(index, 1);
                    this.creditoAutorizado = false;
                    this.focusInicialSegunModo();
                },

                limpiarVenta() {
                    if (this.items.length > 0 && !confirm('¿Desea vaciar todos los artículos cargados?')) {
                        return;
                    }
                    this.items = [];
                    this.pagos = [];
                    this.referenciaPago = '';
                    this.creditoAutorizado = false;
                    this.focusInicialSegunModo();
                },

                // Búsqueda y selección de clientes
                async buscarClientes() {
                    let q = this.clienteQuery.trim();
                    if (q.length < 2) {
                        this.sugerenciasClientes = [];
                        this.indiceSugerenciaCliente = 0;
                        return [];
                    }

                    try {
                        let res = await fetch(`{{ route('clientes.buscar') }}?q=${encodeURIComponent(q)}`);
                        let data = await res.json();
                        this.sugerenciasClientes = data || [];
                        this.indiceSugerenciaCliente = 0;
                        return this.sugerenciasClientes;
                    } catch (e) {
                        console.error('Error buscando clientes:', e);
                        return [];
                    }
                },

                buscarClientesSugeridos() {
                    return this.buscarClientes();
                },

                moverSugerenciaCliente(delta) {
                    if (!this.sugerenciasClientes || this.sugerenciasClientes.length === 0) return;
                    let max = this.sugerenciasClientes.length - 1;
                    let next = this.indiceSugerenciaCliente + delta;
                    if (next < 0) next = max;
                    if (next > max) next = 0;
                    this.indiceSugerenciaCliente = next;
                },

                async confirmarClienteEnter() {
                    if (this.sugerenciasClientes && this.sugerenciasClientes.length > 0) {
                        let elegido = this.sugerenciasClientes[this.indiceSugerenciaCliente] || this.sugerenciasClientes[0];
                        await this.seleccionarCliente(elegido);
                        return;
                    }

                    let q = this.clienteQuery.trim();
                    if (q.length >= 2) {
                        let lista = await this.buscarClientes();
                        if (lista && lista.length > 0) {
                            await this.seleccionarCliente(lista[0]);
                            return;
                        }
                        alert(`No se encontró ningún cliente con "${q}".`);
                    }
                },

                async seleccionarCliente(cli) {
                    this.clienteSeleccionado = cli;
                    this.sugerenciasClientes = [];
                    this.clienteQuery = '';
                    this.indiceSugerenciaCliente = 0;
                    this.creditoAutorizado = false;
                    this.autorizadoPorVendedor = false;

                    if (this.esTrelew) {
                        this.medioPago = 'credito_personal';
                        this.focusMontoGenerico();
                    }

                    await this.consultarInfoCredito(cli.id);

                    if (this.esTrelew && this.items.length === 0) {
                        this.focusMontoGenerico();
                    } else if (!this.esTrelew) {
                        this.focusBarcode();
                    }
                },

                deseleccionarCliente() {
                    this.clienteSeleccionado = null;
                    this.infoCredito = null;
                    this.creditoAutorizado = false;
                    this.autorizadoPorVendedor = false;
                    if (!this.esTrelew && this.esCredito) {
                        this.medioPago = 'efectivo';
                    }
                    this.focusInicialSegunModo();
                },

                seleccionarCuotaPlan(n) {
                    if (this.infoCredito && this.infoCredito.estado_crediticio && n > this.infoCredito.estado_crediticio.max_cuotas) {
                        return;
                    }
                    this.cuotasPlan = n;
                    if (this.modalCredito) {
                        this.focusBtnAutorizarCredito();
                    } else if (this.creditoAutorizado) {
                        this.focusBtnFacturar();
                    }
                },

                async abrirModalCredito() {
                    if (!this.clienteSeleccionado) {
                        alert('Para operar con Crédito Personal debe seleccionar un cliente primero.');
                        this.focusCliente();
                        return;
                    }
                    this.medioPago = 'credito_personal';
                    await this.consultarInfoCredito(this.clienteSeleccionado.id);
                    if (this.infoCredito && this.infoCredito.detalle_compras) {
                        this.nuevoLimiteInput = this.infoCredito.detalle_compras.limite_total;
                    } else if (this.clienteSeleccionado) {
                        this.nuevoLimiteInput = this.clienteSeleccionado.limite_efectivo || this.clienteSeleccionado.limite || 0;
                    }
                    if (!this.creditoAutorizado) {
                        this.autorizadoPorVendedor = false;
                    }
                    this.modalCredito = true;
                    this.focusBtnAutorizarCredito();
                },

                cerrarModalCredito() {
                    this.modalCredito = false;
                    if (this.esTrelew) {
                        if (this.items.length === 0) {
                            this.focusMontoGenerico();
                        } else {
                            this.focusBtnFacturar();
                        }
                    } else {
                        this.focusBarcode();
                    }
                },

                async guardarNuevoLimite() {
                    if (!this.clienteSeleccionado) return;
                    let nuevo = parseFloat(this.nuevoLimiteInput);
                    if (isNaN(nuevo) || nuevo < 0) {
                        alert('Ingrese un límite numérico válido mayor o igual a 0.');
                        return;
                    }
                    this.guardandoLimite = true;
                    try {
                        let res = await fetch(`{{ url('/ventas/clientes') }}/${this.clienteSeleccionado.id}/actualizar-limite`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ limite: nuevo })
                        });
                        let data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.message || data.error || 'Error al actualizar el límite');
                        }
                        await this.consultarInfoCredito(this.clienteSeleccionado.id);
                        alert('✓ Límite de crédito actualizado correctamente a $ ' + this.formatDinero(nuevo));
                        this.focusBtnAutorizarCredito();
                    } catch (e) {
                        alert('Error: ' + e.message);
                    } finally {
                        this.guardandoLimite = false;
                    }
                },

                async guardarNuevoComentario() {
                    if (!this.clienteSeleccionado) return;
                    let txt = (this.nuevoComentarioInput || '').trim();
                    if (!txt) return;

                    this.guardandoComentario = true;
                    try {
                        let res = await fetch(`{{ url('/ventas/clientes') }}/${this.clienteSeleccionado.id}/comentarios`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ mensaje: txt })
                        });
                        let data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.message || data.error || 'Error al guardar comentario');
                        }
                        if (!this.infoCredito.novedades) {
                            this.infoCredito.novedades = [];
                        }
                        this.infoCredito.novedades.unshift(data.novedad);
                        this.nuevoComentarioInput = '';
                        this.focusBtnAutorizarCredito();
                    } catch (e) {
                        alert('Error: ' + e.message);
                    } finally {
                        this.guardandoComentario = false;
                    }
                },

                get puedeAutorizarCredito() {
                    if (!this.infoCredito || !this.infoCredito.estado_crediticio) return false;
                    let limiteDisp = parseFloat(this.infoCredito.detalle_compras?.limite_disponible ?? this.infoCredito.estado_crediticio.limite_disponible ?? 0);
                    let regularHabilitado = this.infoCredito.estado_crediticio.habilitado && (this.calcularTotal() <= limiteDisp);
                    return regularHabilitado || this.autorizadoPorVendedor;
                },

                confirmarAutorizacionCredito() {
                    if (!this.puedeAutorizarCredito) {
                        alert('No es posible autorizar el crédito sin la habilitación correspondiente.');
                        return;
                    }
                    this.medioPago = 'credito_personal';
                    this.creditoAutorizado = true;
                    this.modalCredito = false;
                    if (this.items.length > 0) {
                        this.focusBtnFacturar();
                    } else if (this.esTrelew) {
                        this.focusMontoGenerico();
                    } else {
                        this.focusBarcode();
                    }
                },

                autorizarYFacturarDirecto() {
                    if (!this.puedeAutorizarCredito) {
                        alert('No es posible autorizar el crédito sin la habilitación correspondiente.');
                        return;
                    }
                    this.medioPago = 'credito_personal';
                    this.creditoAutorizado = true;
                    this.modalCredito = false;
                    this.abrirModalPago();
                },

                async consultarInfoCredito(clienteId) {
                    try {
                        let monto = this.calcularSubtotal();
                        let res = await fetch(`{{ url('/ventas/clientes') }}/${clienteId}/info-credito?monto=${monto}&sucursal_id={{ $sucursalActiva->id ?? 1 }}`);
                        let data = await res.json();
                        this.infoCredito = data;
                        if (data && data.cliente && this.clienteSeleccionado && this.clienteSeleccionado.id == clienteId) {
                            this.clienteSeleccionado.nombre = data.cliente.nombre;
                            this.clienteSeleccionado.dni = data.cliente.dni;
                            this.clienteSeleccionado.nro_cuenta = data.cliente.nro_cuenta;
                            this.clienteSeleccionado.limite = data.cliente.limite;
                            this.clienteSeleccionado.limite_efectivo = data.cliente.limite_efectivo;
                        }
                    } catch (e) {
                        console.error('Error consultando crédito:', e);
                    }
                },

                abrirModalPago() {
                    if (this.items.length === 0) {
                        alert('Cargue al menos un artículo o monto en la factura para continuar.');
                        if (this.esTrelew) {
                            this.focusMontoGenerico();
                        } else {
                            this.focusBarcode();
                        }
                        return;
                    }

                    // Guardia de Control de Caja
                    if (this.cajaEstado === 'cerrada') {
                        this.modalAbrirCaja = true;
                        return;
                    }
                    if (this.cajaEstado === 'cierre_anterior') {
                        this.modalCierreAnterior = true;
                        return;
                    }

                    // Inicializar o sincronizar medios de pago
                    if (!this.pagos || this.pagos.length === 0 || this.calcularTotalCubierto() === 0) {
                        this.inicializarPagosPorDefecto();
                    } else if (this.pagos.length === 1) {
                        this.pagos[0].monto = this.calcularTotal();
                    }

                    if (this.tienePagoEfectivo() && (!this.pagaCon || this.pagaCon < this.calcularTotal())) {
                        this.pagaCon = this.calcularTotal();
                    }

                    this.modalCobro = true;
                },

                async enviarAperturaCaja() {
                    if (this.procesandoApertura) return;
                    this.procesandoApertura = true;

                    try {
                        let res = await fetch(`{{ route('cajas.abrir') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id_sucursal: {{ $sucursalActiva->id ?? 1 }},
                                monto_inicial_efectivo: this.montoApertura || 0,
                                observaciones: this.obsApertura || null
                            })
                        });

                        let data = await res.json();
                        if (!res.ok || !data.success) {
                            alert(data.error || 'Error al realizar la apertura de caja.');
                            this.procesandoApertura = false;
                            return;
                        }

                        alert('Caja abierta correctamente. Ya puede facturar.');
                        window.location.reload();
                    } catch (e) {
                        console.error('Error abriendo caja:', e);
                        alert('Error de conexión al abrir la caja.');
                        this.procesandoApertura = false;
                    }
                },

                async enviarCierreAnteriorYAbrir() {
                    if (this.procesandoCierreAnterior) return;
                    this.procesandoCierreAnterior = true;

                    try {
                        let res = await fetch(`{{ route('cajas.cerrar-anterior-y-abrir') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id_sucursal: {{ $sucursalActiva->id ?? 1 }},
                                id_caja_anterior: this.cajaAnteriorId,
                                efectivo_real_contado: this.contadoAnterior || 0,
                                monto_inicial_efectivo: this.fondoHoyAnterior || 0,
                                observaciones: this.obsCierreAnterior || null
                            })
                        });

                        let data = await res.json();
                        if (!res.ok || !data.success) {
                            alert(data.error || 'Error al asentar el cierre anterior y apertura.');
                            this.procesandoCierreAnterior = false;
                            return;
                        }

                        alert('Cierre de jornada anterior asentado y nueva caja abierta con éxito.');
                        window.location.reload();
                    } catch (e) {
                        console.error('Error cerrando anterior y abriendo:', e);
                        alert('Error de conexión al procesar el cierre.');
                        this.procesandoCierreAnterior = false;
                    }
                },

                async abrirModalMovimiento() {
                    if (!this.cajaId) {
                        alert('Debe abrir la caja antes de registrar movimientos.');
                        return;
                    }
                    this.modalMovimientoCaja = true;
                    await this.cargarMovimientosCaja();
                },

                async cargarMovimientosCaja() {
                    if (!this.cajaId) return;
                    try {
                        let res = await fetch(`{{ url('/cajas') }}/${this.cajaId}/movimientos`);
                        let data = await res.json();
                        this.movimientosCaja = data.movimientos || [];
                        if (data.totales) {
                            this.efectivoEsperadoCaja = data.totales.efectivo_esperado || 0;
                            this.totalIngresosCaja = data.totales.total_ingresos || 0;
                            this.totalRetirosCaja = data.totales.total_retiros || 0;
                        }
                    } catch (e) {
                        console.error('Error cargando movimientos de caja:', e);
                    }
                },

                async enviarMovimientoCaja() {
                    if (!this.cajaId) return;
                    let monto = parseFloat(this.movimientoMonto);
                    if (isNaN(monto) || monto <= 0) {
                        alert('Ingrese un monto válido mayor a cero.');
                        return;
                    }
                    if (!this.movimientoMotivo || !this.movimientoMotivo.trim()) {
                        alert('Debe ingresar el motivo o justificación del movimiento.');
                        return;
                    }

                    this.procesandoMovimiento = true;

                    try {
                        let res = await fetch(`{{ url('/cajas') }}/${this.cajaId}/movimientos`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                tipo: this.movimientoTipo,
                                monto: monto,
                                motivo: this.movimientoMotivo.trim(),
                                comprobante: this.movimientoComprobante ? this.movimientoComprobante.trim() : null
                            })
                        });

                        let data = await res.json();
                        if (!res.ok || !data.success) {
                            alert(data.error || 'Error al registrar el movimiento.');
                            this.procesandoMovimiento = false;
                            return;
                        }

                        alert(data.message || 'Movimiento registrado correctamente.');
                        this.movimientoMonto = '';
                        this.movimientoMotivo = '';
                        this.movimientoComprobante = '';
                        this.procesandoMovimiento = false;
                        await this.cargarMovimientosCaja();
                    } catch (e) {
                        console.error('Error registrando movimiento:', e);
                        alert('Error de conexión al asentar el movimiento.');
                        this.procesandoMovimiento = false;
                    }
                },

                cambiarMedioYFacturar(nuevoMedio) {
                    this.medioPago = nuevoMedio;
                    this.modalCredito = false;
                    this.modalCobro = true;
                    this.pagaCon = this.calcularTotal();
                },

                async enviarVenta() {
                    if (this.procesando) return;
                    this.procesando = true;

                    let listPagos = (this.pagos && this.pagos.length > 0) ? this.pagos : [
                        {
                            id_medio_pago: this.medioPagoActual?.id || null,
                            medio_pago: this.medioPago || 'efectivo',
                            nombre: this.medioPagoActual?.nombre || 'Efectivo',
                            monto: this.calcularTotal(),
                            cupon: this.referenciaPago || null,
                            codigo_forma: this.mapearCodigoFormaHasar(this.medioPagoActual?.tipo_base || 'efectivo')
                        }
                    ];

                    let payload = {
                        _token: '{{ csrf_token() }}',
                        id_sucursal: {{ $sucursalActiva->id ?? 1 }},
                        id_punto_venta: localStorage.getItem('lamarina_terminal_pv_id') || null,
                        tipo_comprobante: this.tipoComprobante,
                        id_cliente: this.clienteSeleccionado ? this.clienteSeleccionado.id : null,
                        cliente_nombre: this.clienteSeleccionado ? String(this.clienteSeleccionado.nombre) : 'CONSUMIDOR FINAL',
                        cliente_documento: (this.clienteSeleccionado && this.clienteSeleccionado.dni) ? String(this.clienteSeleccionado.dni) : null,
                        medio_pago: (listPagos.length > 1) ? 'multipago' : (listPagos[0]?.medio_pago || this.medioPago),
                        pagos: listPagos.map(p => ({
                            id_medio_pago: p.id_medio_pago || null,
                            medio_pago: p.medio_pago || 'efectivo',
                            nombre: p.nombre || 'Efectivo',
                            monto: parseFloat(p.monto) || 0,
                            cupon: p.cupon ? String(p.cupon).trim() : null,
                            tipo_base: p.tipo_base || 'efectivo',
                            codigo_forma: p.codigo_forma || null
                        })),
                        numero_cupon: this.obtenerCuponesStr(),
                        descuento: this.calcularDescuento(),
                        cuotas: this.tienePagoCreditoPersonal() ? parseInt(this.cuotasPlan || 1) : null,
                        autorizado_vendedor: this.tienePagoCreditoPersonal() ? (this.autorizadoPorVendedor ? true : false) : false,
                        observaciones: this.obtenerCuponesStr() ? ('[Ref: ' + this.obtenerCuponesStr() + ']') : null,
                        items: this.items.map(it => ({
                            id_producto: it.id_producto || null,
                            codigo_producto: it.codigo_producto ? String(it.codigo_producto) : (it.codigo ? String(it.codigo) : null),
                            descripcion: String(it.descripcion || 'ARTICULO GENERAL'),
                            cantidad: parseFloat(it.cantidad) || 1,
                            precio_unitario: parseFloat(it.precio_unitario) || 0,
                            descuento_porcentaje: Math.max(0, Math.min(100, parseFloat(it.descuento_porcentaje) || 0))
                        }))
                    };

                    // Consultar número oficial previo a la controladora fiscal Hasar
                    let hasarInfo = await this.consultarProximoNumeroHasar();
                    if (hasarInfo && hasarInfo.exito) {
                        payload.hasar_ticket_numero = hasarInfo.proximo_comprobante;
                        payload.hasar_pv_numero = hasarInfo.punto_venta;
                    }

                    try {
                        let res = await fetch(`{{ route('ventas.store') }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        let data = await res.json();

                        if (!res.ok) {
                            if (data.requiere_apertura) {
                                this.modalAbrirCaja = true;
                            } else if (data.requiere_cierre_anterior) {
                                let targetId = data.caja_anterior_id || {{ $cajaActiva->id ?? 'null' }};
                                alert((data.error || 'La caja anterior no fue cerrada.') + '\n\nSerá redirigido a la pantalla oficial para emitir el Cierre Z de los controladores fiscales y asentar el arqueo.');
                                if (targetId) {
                                    window.location.href = "{{ url('cajas') }}/" + targetId + "/cerrar";
                                    return;
                                }
                            }
                            let errorMsg = data.error || data.message;
                            if (data.errors) {
                                let detalles = Object.values(data.errors).flat().join('\n');
                                if (detalles && detalles !== errorMsg) {
                                    errorMsg = errorMsg ? (errorMsg + '\n\n' + detalles) : detalles;
                                }
                            }
                            alert(errorMsg || 'Ocurrió un error al procesar la venta.');
                            this.procesando = false;
                            return;
                        }

                        // Si el punto de venta es Controlador Fiscal Hasar, enviar comando al puente local
                        if (data.tipo_emision === 'fiscal_hasar') {
                            let hasarIp = (data.hasar_config && data.hasar_config.hasar_ip) ? data.hasar_config.hasar_ip : undefined;
                            let hasarPuerto = (data.hasar_config && data.hasar_config.hasar_puerto) ? parseInt(data.hasar_config.hasar_puerto) : 80;
                            let letraComprobante = (this.tipoComprobante === 'FACTURA_A') ? 'A' : 'B';
                            let descMedio = this.medioPagoActual ? this.medioPagoActual.nombre : (this.medioPago === 'efectivo' ? 'Efectivo' : (this.medioPago === 'tarjeta_debito' ? 'Tarjeta Débito' : (this.medioPago === 'tarjeta_credito' ? 'Tarjeta Crédito' : 'Transferencia')));

                            let bodyHasar = {
                                accion: "factura",
                                hasar_ip: hasarIp,
                                hasar_puerto: hasarPuerto,
                                tipo_comprobante: letraComprobante,
                                cliente: {
                                    nombre: this.clienteSeleccionado ? (this.clienteSeleccionado.nombre || this.clienteSeleccionado.razon_social) : 'CONSUMIDOR FINAL',
                                    tipo_documento: (this.clienteSeleccionado && this.clienteSeleccionado.cuit && this.clienteSeleccionado.cuit.length > 8) ? 'CUIT' : 'DNI',
                                    numero_documento: this.clienteSeleccionado ? String(this.clienteSeleccionado.cuit || this.clienteSeleccionado.dni || '0').trim() : '0',
                                    responsabilidad: (this.tipoComprobante === 'FACTURA_A') ? 'I' : 'C',
                                    domicilio: (this.clienteSeleccionado && this.clienteSeleccionado.domicilio) ? this.clienteSeleccionado.domicilio : 'Av. Fontana 123'
                                },
                                items: (this.items || []).map((it, idx) => ({
                                    id: String(it.id || (idx + 1)),
                                    codigo: String(it.codigo_barra || it.id || ('VAR' + (idx + 1))),
                                    descripcion: it.descripcion,
                                    cantidad: parseFloat(it.cantidad) || 1,
                                    precio_unitario: parseFloat(it.precio_unitario) || 0,
                                    alicuota_iva: this.esTdf ? 0.0 : 21.0
                                })),
                                pagos: listPagos.map(p => ({
                                    descripcion: p.nombre || p.medio_pago || 'Efectivo',
                                    monto: parseFloat(p.monto) || 0,
                                    codigo_forma: p.codigo_forma || this.mapearCodigoFormaHasar(p.tipo_base || 'efectivo'),
                                    cupon: p.cupon ? String(p.cupon).trim() : null
                                }))
                            };

                            data.hasar_payload = bodyHasar;
                            await this.enviarAHasarBridge(bodyHasar, false);
                        }

                        // Éxito: cerrar modales de cobro y mostrar modal final
                        this.modalCredito = false;
                        this.modalCobro = false;
                        this.creditoAutorizado = false;
                        this.autorizadoPorVendedor = false;
                        this.yaImprimioExito = false;
                        this.iframeExitoCargado = false;
                        this.impresionPendienteExito = false;
                        this.ventaExitosa = {
                            id: data.venta_id,
                            comprobante: (hasarInfo && hasarInfo.exito && data.tipo_emision === 'fiscal_hasar') ? hasarInfo.comprobante_formateado : data.comprobante,
                            total: data.total,
                            tipo_emision: data.tipo_emision,
                            hasar_payload: (data.tipo_emision === 'fiscal_hasar') ? data.hasar_payload : null,
                            cae: data.cae,
                            cae_vencimiento: data.cae_vencimiento,
                            afip_qr: data.afip_qr,
                            ticket_url: data.ticket_url,
                            a4_url: data.a4_url,
                            redirect_url: data.redirect_url,
                            id_credito: data.id_credito || null,
                            pagare_url: data.pagare_url || null
                        };
                        this.documentoExitoTipo = (data.id_credito && data.pagare_url) ? 'pagare' : 'ticket';
                        this.modalExito = true;
                        this.focusBtnImprimirExito();
                    } catch (e) {
                        console.error('Error al enviar venta:', e);
                        alert('Error de conexión al emitir la factura.');
                    } finally {
                        this.procesando = false;
                    }
                },

                iniciarNuevaVenta() {
                    this.items = [];
                    this.descuentoManual = 0;
                    this.tipoDescuentoManual = 'monto';
                    this.clienteSeleccionado = null;
                    this.clienteQuery = '';
                    this.sugerenciasClientes = [];
                    this.indiceSugerenciaCliente = 0;
                    this.infoCredito = null;
                    this.medioPago = this.esTrelew ? 'credito_personal' : 'efectivo';
                    this.referenciaPago = '';
                    this.cuotasPlan = 1;
                    this.creditoAutorizado = false;
                    this.autorizadoPorVendedor = false;
                    this.modalExito = false;
                    this.yaImprimioExito = false;
                    this.iframeExitoCargado = false;
                    this.impresionPendienteExito = false;
                    this.documentoExitoTipo = 'ticket';
                    this.ventaExitosa = {
                        id: null,
                        comprobante: '',
                        total: 0,
                        ticket_url: '#',
                        a4_url: '#',
                        redirect_url: '#',
                        id_credito: null,
                        pagare_url: null
                    };
                    this.focusInicialSegunModo();
                }
            };
        }
    </script>
</x-app-layout>
