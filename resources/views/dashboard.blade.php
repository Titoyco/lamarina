<x-app-layout>
    <div class="max-w-7xl mx-auto py-4 sm:py-5 px-4 sm:px-6 lg:px-8">
        {{-- Encabezado Principal Compacto y Estado Operativo --}}
        <div class="mb-5 bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 dark:from-slate-900 dark:via-blue-950 dark:to-slate-900 rounded-2xl p-4 sm:px-6 sm:py-4 text-white shadow-lg relative overflow-hidden border border-blue-800 dark:border-slate-800">
            <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none">
                <svg class="w-48 h-48 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                </svg>
            </div>

            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black tracking-wider uppercase bg-blue-700/60 dark:bg-slate-800/80 border border-blue-500/40 dark:border-slate-700 text-blue-200 dark:text-blue-300 mb-1">
                        <span class="w-1.5 h-1.5 rounded-full {{ Auth::user()->is_admin ? 'bg-amber-400' : 'bg-emerald-400' }} animate-pulse"></span>
                        {{ Auth::user()->is_admin ? 'Modo Administrador' : 'Modo Operador de Sucursal' }}
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white uppercase">
                        Panel de Control — La Marina
                    </h1>
                    <p class="text-blue-200 dark:text-slate-300 text-xs font-medium mt-0.5">
                        Bienvenido, <strong class="text-white">{{ Auth::user()->name }}</strong> ({{ Auth::user()->username }})
                    </p>
                </div>

                {{-- Tarjeta de Sucursal Activa Compacta --}}
                <div class="bg-white/10 dark:bg-slate-800/60 backdrop-blur-md border border-white/20 dark:border-slate-700 rounded-xl px-4 py-2 sm:min-w-[260px] shrink-0">
                    <div class="flex items-center justify-between text-[10px] text-blue-200 dark:text-slate-300 font-bold uppercase tracking-wider mb-0.5">
                        <span>{{ Auth::user()->is_admin ? 'Sucursal Seleccionada' : 'Tu Sucursal Asignada' }}</span>
                        @if(Auth::user()->is_admin)
                            <span class="text-amber-300 font-black text-[9px] bg-amber-500/20 px-1.5 py-0.5 rounded">Editable</span>
                        @else
                            <span class="text-emerald-300 font-black text-[9px] bg-emerald-500/20 px-1.5 py-0.5 rounded">Fija</span>
                        @endif
                    </div>
                    <div class="text-base sm:text-lg font-black text-white flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="truncate">{{ config('app.sucursal_activa')->nombre ?? 'Sin Sucursal' }}</span>
                    </div>
                    <p class="text-[10px] text-blue-200/80 dark:text-slate-400 truncate">
                        {{ config('app.sucursal_activa')->direccion ?? 'Código: ' . (config('app.sucursal_activa')->codigo_sucursal ?? 'N/A') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Cuadrícula de Módulos Operativos --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">

            {{-- 1. Punto de Venta / Facturación (POS) --}}
            <a href="{{ route('ventas.create') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-blue-400 dark:hover:border-blue-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-700 dark:text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            Mostrador
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                        Punto de Venta (POS)
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        Emisión de facturas y ventas con escaneo de código de barras. Efectivo, tarjetas y <strong>Crédito Personal</strong>.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Sucursal: {{ config('app.sucursal_activa')->codigo_sucursal ?? 'N/A' }}</span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 group-hover:underline">Nueva Venta</span>
                        <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 transition-all duration-200 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </div>
            </a>

            {{-- 1.B. Historial de Ventas y Facturación --}}
            <a href="{{ route('ventas.index') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-blue-400 dark:hover:border-blue-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-800/60 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            Comprobantes
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                        Ventas y Facturación
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        Historial completo de ventas y facturas emitidas (A, B, Ticket, Remito), reimpresión oficial y exportación a Excel con filtros.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Histórico de Ventas</span>
                    <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 transition-all duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>

            {{-- 1.B. Control de Caja & Cierres Z --}}
            <a href="{{ route('cajas.index') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-emerald-400 dark:hover:border-emerald-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 flex items-center justify-center text-emerald-700 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            Caja Diaria
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                        Control de Caja (Cierre Z)
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        Apertura de turno, arqueo de efectivo físico, fallos de caja, reportes por medio de pago e impresión de Cierre Z.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Turnos y Arqueos</span>
                    <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-emerald-600 group-hover:text-white dark:group-hover:bg-emerald-500 transition-all duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>

            {{-- 2. Stock y Catálogo de Artículos --}}
            <a href="{{ route('productos.index') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-blue-400 dark:hover:border-blue-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 flex items-center justify-center text-amber-600 dark:text-amber-400 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            Multi-Sede
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                        Catálogo y Stock
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        Existencias en todas las sucursales, códigos de barra (EAN-13 / 12 dígitos) y solicitud de traslados.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Visibilidad Nacional</span>
                    <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 transition-all duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>

            {{-- 2.B. Compras y Remitos (Recepción de Facturas y Stock) --}}
            <a href="{{ route('remitos.index') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-indigo-400 dark:hover:border-indigo-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-800/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                            Proveedores
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-indigo-700 dark:group-hover:text-indigo-400 transition-colors">
                        Compras y Remitos
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        Carga de facturas de proveedores en etapas, altas automáticas CODE128 y actualización de stock por diferencia.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Ingreso de Mercadería</span>
                    <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-indigo-600 group-hover:text-white dark:group-hover:bg-indigo-500 transition-all duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>

            {{-- 3. Padrón de Clientes --}}
            <a href="{{ route('clientes.index') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-blue-400 dark:hover:border-blue-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-100 dark:border-blue-800/60 flex items-center justify-center text-blue-600 dark:text-blue-400 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            Padrón Único
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                        Clientes y Legajos
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        Búsqueda por DNI, CUIT o Cuenta. Límite crediticio disponible, estados bancarios y autorizados.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Base Centralizada</span>
                    <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 transition-all duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>

            {{-- 4. Créditos y Cobranzas --}}
            <a href="{{ route('creditos.index') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-blue-400 dark:hover:border-blue-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-800/60 flex items-center justify-center text-emerald-600 dark:text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            Cta. Cte.
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                        Créditos y Cuotas
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        Cálculo de lotes y cuotas, planes de financiación en cuotas fijas y gestión de débitos banco.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Financiación</span>
                    <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 transition-all duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>

            {{-- 5. Recibos y Cobranzas --}}
            <a href="{{ route('recibos.index') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-blue-400 dark:hover:border-blue-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-100 dark:border-cyan-800/60 flex items-center justify-center text-cyan-700 dark:text-cyan-400 group-hover:bg-cyan-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800">
                            Cobranzas
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                        Recibos y Cobranzas
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        Emisión de recibos de pago oficiales, ingreso a cuenta corriente, cancelación de cuotas e impresión.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Caja y Cta. Cte.</span>
                    <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 transition-all duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>

            {{-- 6. Métricas BI (PowerBI) --}}
            <a href="{{ route('metricas.index') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-amber-400 dark:hover:border-amber-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-100 dark:border-amber-800/60 flex items-center justify-center text-amber-600 dark:text-amber-400 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            PowerBI
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                        Métricas & BI
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        Tableros ejecutivos estilo PowerBI: ventas por categoría, sucursal, medios de pago y evolución de cartera de créditos.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">Business Intelligence</span>
                    <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-amber-500 group-hover:text-white dark:group-hover:bg-amber-400 transition-all duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>

            {{-- 7. Sistema y Auditoría --}}
            <a href="{{ route('sistema.index') }}"
               class="group bg-white dark:bg-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm hover:shadow-lg border border-slate-200/90 dark:border-slate-700/80 hover:border-blue-400 dark:hover:border-blue-500 hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 {{ !Auth::user()->is_admin ? 'opacity-80' : '' }}">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-100 dark:border-purple-800/60 flex items-center justify-center text-purple-700 dark:text-purple-400 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        @if(Auth::user()->is_admin)
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                Administrador
                            </span>
                        @else
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                                Restringido
                            </span>
                        @endif
                    </div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">
                        Sistema y Auditoría
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-1 leading-snug font-medium">
                        ABM de Sucursales, Lotes, Proveedores, usuarios y auditoría con rollback transaccional.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500">{{ Auth::user()->is_admin ? 'Acceso Total' : 'Solo Consulta' }}</span>
                    <span class="w-7 h-7 rounded-full bg-slate-50 dark:bg-slate-700/60 text-slate-400 group-hover:bg-blue-600 group-hover:text-white dark:group-hover:bg-blue-500 transition-all duration-200 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 transform group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </a>

        </div>
    </div>
</x-app-layout>