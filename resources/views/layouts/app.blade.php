<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'LaMarina Créditos') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/favicon.ico') }}">
    
    {{-- Prevención de FOUC para Modo Oscuro --}}
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // Helper global para filas con Menú Contextual interactivo
        function contextMenuRow() {
            return {
                open: false,
                x: 0,
                y: 0,
                openMenu(e) {
                    if (e.target.closest('a') || e.target.closest('button') || e.target.closest('input') || e.target.closest('select')) {
                        return;
                    }
                    window.dispatchEvent(new CustomEvent('close-context-menus'));
                    const menuWidth = 240;
                    const menuHeight = 320;
                    const winW = window.innerWidth;
                    const winH = window.innerHeight;
                    this.x = (e.clientX + menuWidth > winW - 10) ? Math.max(10, winW - menuWidth - 10) : e.clientX;
                    this.y = (e.clientY + menuHeight > winH - 10) ? Math.max(10, e.clientY - menuHeight) : e.clientY;
                    this.open = true;
                },
                closeMenu() {
                    this.open = false;
                }
            };
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-sans antialiased transition-colors duration-200">
    <div class="min-h-screen">
        @if(!request()->boolean('embed') && !request()->boolean('popup'))
        <nav class="bg-blue-800 dark:bg-slate-950 border-b border-blue-900 dark:border-slate-800 shadow-lg">
            {{-- Fila 1: Div Superior (Identidad de Marca + Usuario, Sucursal, Punto de Venta y Control) --}}
            <div class="max-w-7xl mx-auto px-4 sm:px-4 lg:px-8">
                <div class="flex justify-between h-15 items-center"> 
                    {{-- Logo y Nombre del Sistema --}}
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ url('/') }}" class="inline-flex items-center bg-white dark:bg-slate-800 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700/80 transition-all px-3.5 py-1.5 shadow-sm border border-slate-100 dark:border-slate-700">
                            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="h-8 w-auto object-contain"> 
                        </a>

                        <div class="hidden sm:flex items-center">
                            <div class="h-6 w-px bg-blue-700/80 dark:bg-slate-700 mr-3"></div>
                            <div class="flex flex-col">
                                <span class="text-blue-100 dark:text-slate-200 text-xs sm:text-sm font-black italic tracking-widest uppercase leading-none drop-shadow-xs">
                                    SISTEMA DE CRÉDITOS
                                </span>
                                <span class="text-blue-300 dark:text-slate-400 text-[10px] font-semibold tracking-wider uppercase mt-0.5">
                                    Panel de Gestión
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Panel Operativo Derecho: Reloj, Perfil, Sucursal, Punto de Venta, Tema y Salir --}}
                    <div class="flex items-center text-white text-right z-10 shrink-0 gap-1 sm:gap-2">
                        {{-- ⏱ Reloj Operativo --}}
                        <div class="hidden xl:flex flex-col items-end pr-3 border-r border-blue-700/60 dark:border-slate-800 leading-tight select-none mr-1"
                             x-data="{
                                 hora: '',
                                 fecha: '',
                                 actualizar() {
                                     const now = new Date();
                                     this.hora = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                                     const opcionesFecha = { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' };
                                     this.fecha = now.toLocaleDateString('es-AR', opcionesFecha).replace('.', '');
                                 },
                                 init() {
                                     this.actualizar();
                                     setInterval(() => this.actualizar(), 1000);
                                 }
                             }">
                            <span class="text-[10px] text-blue-200 dark:text-slate-400 font-bold uppercase tracking-wider font-mono" x-text="fecha">
                                {{ now()->locale('es')->isoFormat('ddd, DD/MM/YYYY') }}
                            </span>
                            <span class="text-xs font-mono font-bold tabular-nums text-white" x-text="hora">
                                {{ now()->format('H:i:s') }}
                            </span>
                        </div>

                        <a href="{{ route('profile.edit') }}" title="Editar mi perfil y clave" class="group hidden md:block border-r border-blue-700 dark:border-slate-800 pr-3 mr-1 hover:opacity-95 transition text-left">
                            <p class="text-[10px] uppercase text-blue-300 dark:text-slate-400 font-black leading-none mb-1 tracking-tighter flex items-center gap-1">
                                <span>Mi Perfil</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-blue-300 opacity-60 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </p>
                            <p class="text-xs font-bold italic text-white group-hover:text-amber-300 transition truncate max-w-[120px]">{{ Auth::user()->name }}</p>
                        </a>

                        @if(Auth::user()->is_admin)
                        <div class="border-r border-blue-700 dark:border-slate-800 pr-3 mr-1 text-left">
                            <p class="text-[9px] uppercase text-amber-300 font-black leading-none mb-1 tracking-tighter flex items-center gap-1">
                                <svg class="w-3 h-3 text-amber-400 inline" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Sucursal Activa (Admin)
                            </p>

                            <form action="{{ route('sucursal.update.global') }}" method="POST" id="formCambioSucursal" autocomplete="off">
                                @csrf
                                <select name="id_sucursal" onchange="this.form.submit()" 
                                    class="bg-blue-900/90 dark:bg-slate-800 hover:bg-blue-900 dark:hover:bg-slate-700 text-white text-xs font-black border border-blue-500 dark:border-slate-600 rounded-lg py-1 px-2 focus:ring-2 focus:ring-amber-400 cursor-pointer transition-all shadow-sm">
                                    @foreach(\App\Models\Sucursal::all() as $suc)
                                        <option value="{{ $suc->id }}" 
                                            {{ (int)config('app.sucursal_activa.id') === (int)$suc->id ? 'selected' : '' }}>
                                            {{ $suc->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        @else
                        <div class="border-r border-blue-700 dark:border-slate-800 pr-3 mr-1 text-left">
                            <p class="text-[9px] uppercase text-blue-300 dark:text-slate-400 font-black leading-none mb-1 tracking-tighter">Sucursal Asignada</p>
                            <div class="flex items-center gap-1.5 text-xs font-black text-white bg-blue-900/70 dark:bg-slate-800 border border-blue-600 dark:border-slate-700 rounded-lg py-1 px-2 shadow-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span>{{ config('app.sucursal_activa.nombre') ?? Auth::user()->sucursal?->nombre ?? 'Sin sucursal' }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- 🖨️ Punto de Venta de la Terminal / Puesto Local --}}
                        <div class="pl-1">
                            <x-terminal-punto-venta-modal />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fila 2: Sub-barra de Accesos Rápidos Principales y Controles de Sesión --}}
            <div class="bg-blue-900/80 dark:bg-slate-900/90 border-t border-blue-700/50 dark:border-slate-800 shadow-inner">
                <div class="max-w-7xl mx-auto px-4 sm:px-4 lg:px-8">
                    <div class="flex items-center justify-between py-1.5 gap-2">
                        {{-- Accesos Directos Principales --}}
                        <div class="flex items-center gap-1.5 overflow-x-auto py-0.5">
                            <a href="{{ route('ventas.create') }}" 
                               class="px-3 py-1 rounded-md text-xs font-bold uppercase transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('ventas.create') ? 'bg-blue-950 text-amber-300 ring-1 ring-blue-500 shadow-inner' : 'text-blue-100 hover:bg-blue-800/80 dark:hover:bg-slate-800' }}">
                                <span>🛒 POS Mostrador</span>
                            </a>
                            <a href="{{ route('ventas.index') }}" 
                               class="px-3 py-1 rounded-md text-xs font-bold uppercase transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('ventas.index') || request()->routeIs('ventas.show') ? 'bg-blue-950 text-amber-300 ring-1 ring-blue-500 shadow-inner' : 'text-blue-100 hover:bg-blue-800/80 dark:hover:bg-slate-800' }}">
                                <span>🧾 Ventas</span>
                            </a>
                            <a href="{{ route('cajas.index') }}" 
                               class="px-3 py-1 rounded-md text-xs font-bold uppercase transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('cajas.*') ? 'bg-blue-950 text-amber-300 ring-1 ring-blue-500 shadow-inner' : 'text-blue-100 hover:bg-blue-800/80 dark:hover:bg-slate-800' }}">
                                <span>💵 Cajas & Turnos</span>
                            </a>
                            <a href="{{ route('clientes.index') }}" 
                               class="px-3 py-1 rounded-md text-xs font-bold uppercase transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('clientes.*') ? 'bg-blue-950 text-amber-300 ring-1 ring-blue-500 shadow-inner' : 'text-blue-100 hover:bg-blue-800/80 dark:hover:bg-slate-800' }}">
                                <span>👥 Clientes</span>
                            </a>
                            <a href="{{ route('creditos.index') }}" 
                               class="px-3 py-1 rounded-md text-xs font-bold uppercase transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('creditos.*') ? 'bg-blue-950 text-amber-300 ring-1 ring-blue-500 shadow-inner' : 'text-blue-100 hover:bg-blue-800/80 dark:hover:bg-slate-800' }}">
                                <span>💳 Créditos</span>
                            </a>
                            <a href="{{ route('productos.index') }}" 
                               class="px-3 py-1 rounded-md text-xs font-bold uppercase transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('productos.*') ? 'bg-blue-950 text-amber-300 ring-1 ring-blue-500 shadow-inner' : 'text-blue-100 hover:bg-blue-800/80 dark:hover:bg-slate-800' }}">
                                <span>📦 Stock & Catálogo</span>
                            </a>
                            <a href="{{ route('remitos.index') }}" 
                               class="px-3 py-1 rounded-md text-xs font-bold uppercase transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('remitos.*') ? 'bg-blue-950 text-amber-300 ring-1 ring-blue-500 shadow-inner' : 'text-blue-100 hover:bg-blue-800/80 dark:hover:bg-slate-800' }}">
                                <span>📄 Compras & Remitos</span>
                            </a>
                            <a href="{{ route('metricas.index') }}" 
                               class="px-3 py-1 rounded-md text-xs font-bold uppercase transition flex items-center gap-1.5 whitespace-nowrap {{ request()->routeIs('metricas.*') ? 'bg-amber-400 text-slate-950 font-black ring-2 ring-amber-300 shadow-md' : 'text-amber-300 hover:bg-blue-800/80 dark:hover:bg-slate-800 font-extrabold' }}">
                                <span>📊 Métricas BI</span>
                            </a>
                        </div>

                        {{-- Controles Secundarios: Modo Oscuro y Cerrar Sesión --}}
                        <div class="flex items-center gap-1.5 shrink-0 pl-2">
                            {{-- 🌓 Conmutador de Modo Claro / Oscuro --}}
                            <div x-data="{
                                    isDark: document.documentElement.classList.contains('dark'),
                                    toggle() {
                                        this.isDark = !this.isDark;
                                        if (this.isDark) {
                                            document.documentElement.classList.add('dark');
                                            localStorage.setItem('color-theme', 'dark');
                                        } else {
                                            document.documentElement.classList.remove('dark');
                                            localStorage.setItem('color-theme', 'light');
                                        }
                                    }
                                }" 
                                class="flex items-center">
                                <button @click="toggle()" 
                                        type="button" 
                                        class="px-2.5 py-1 rounded-md text-xs font-bold text-blue-200 hover:text-white dark:text-amber-300 hover:bg-blue-800/80 dark:hover:bg-slate-800 border border-blue-700/60 dark:border-slate-700 transition flex items-center gap-1.5 cursor-pointer shadow-xs"
                                        :title="isDark ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'">
                                    <svg x-show="isDark" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <svg x-show="!isDark" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                    <span class="text-[10px] uppercase font-black tracking-wider hidden sm:inline" x-text="isDark ? 'Modo Claro' : 'Modo Oscuro'"></span>
                                </button>
                            </div>

                            <div class="h-4 w-px bg-blue-700/60 dark:bg-slate-700"></div>

                            {{-- Cerrar Sesión --}}
                            <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                                @csrf
                                <button type="submit" title="Cerrar Sesión" class="px-2.5 py-1 rounded-md text-xs font-bold uppercase text-rose-200 hover:text-white hover:bg-rose-900/60 border border-transparent hover:border-rose-700/60 transition flex items-center gap-1.5 cursor-pointer">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span class="text-[10px] uppercase font-black tracking-wider hidden sm:inline">Salir</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        @endif

        <main class="{{ (request()->boolean('embed') || request()->boolean('popup')) ? 'p-2 sm:p-4 w-full' : 'py-2 max-w-[92%] mx-auto px-4' }}">
            <div class="{{ (request()->boolean('embed') || request()->boolean('popup')) ? 'w-full' : 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6' }}">
            {{ $slot }}
            </div>
        </main>
    </div>

    <x-notification-modal />

</body>
</html>