<x-app-layout>
    <div class="max-w-4xl mx-auto py-2 space-y-6">
        
        {{-- Encabezado --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        {{ $proveedor->nombre }}
                    </h1>
                    @if($proveedor->legajo)
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-700" title="Legajo de 3 dígitos para códigos de barra">
                            LEGAJO: {{ $proveedor->legajo_formateado }}
                        </span>
                    @endif
                    @if($proveedor->cuit)
                        <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            CUIT: {{ $proveedor->cuit }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ $proveedor->condicionIva->nombre ?? 'Condición IVA no especificada' }} • {{ $proveedor->localidad->nombre ?? 'Sin localidad' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('remitos.create', ['proveedor_id' => $proveedor->id]) }}" 
                   class="px-3.5 py-1.5 text-xs font-black uppercase text-white bg-blue-700 hover:bg-blue-800 rounded-lg shadow-sm transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    <span>Cargar Remito</span>
                </a>
                <a href="{{ route('proveedores.edit', $proveedor->id) }}" 
                   class="px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg shadow-2xs transition-all flex items-center gap-1">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Editar</span>
                </a>
                <a href="{{ route('proveedores.index') }}" 
                   class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-lg transition-all">
                    &larr; Volver
                </a>
            </div>
        </div>

        {{-- Tarjetas de Datos y Contacto --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-800/90 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Contacto Directo</span>
                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">{{ $proveedor->contacto ?? 'Sin asignar' }}</p>
                <p class="text-xs font-mono text-slate-500 mt-0.5">{{ $proveedor->tel1 ?? 'Sin teléfono' }}</p>
                @if($proveedor->email)
                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 truncate">{{ $proveedor->email }}</p>
                @endif
            </div>

            <div class="bg-white dark:bg-slate-800/90 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Dirección / Radicación</span>
                <p class="text-sm font-bold text-slate-800 dark:text-white mt-1">{{ $proveedor->direccion ?? 'Sin dirección' }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $proveedor->localidad->nombre ?? '' }} ({{ $proveedor->localidad->provincia->nombre ?? '' }})</p>
            </div>

            <div class="bg-white dark:bg-slate-800/90 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-wider">Saldo en Cuenta</span>
                <p class="text-2xl font-black text-slate-900 dark:text-white mt-1 font-mono">
                    $ {{ number_format($proveedor->saldo, 2, ',', '.') }}
                </p>
                <span class="text-[10px] text-slate-400">Estado de cuenta comercial</span>
            </div>
        </div>

        {{-- Artículos que Suministra este Proveedor --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xs p-5">
            <h2 class="text-sm font-black uppercase text-slate-800 dark:text-white tracking-wider flex items-center justify-between border-b border-slate-100 dark:border-slate-700/60 pb-3 mb-4">
                <span>Artículos Vinculados al Proveedor</span>
                <a href="{{ route('productos.index', ['proveedor_id' => $proveedor->id]) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                    Ver en Catálogo General &rarr;
                </a>
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-sans border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/50 text-[10px] font-bold uppercase text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                            <th class="py-2 px-3">Código</th>
                            <th class="py-2 px-3">Descripción</th>
                            <th class="py-2 px-3 text-right">Costo</th>
                            <th class="py-2 px-3 text-right">Venta</th>
                            <th class="py-2 px-3 text-right">Stock Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($proveedor->productos as $prod)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                <td class="py-2 px-3 font-mono font-bold text-blue-600 dark:text-blue-400">
                                    <a href="{{ route('productos.show', $prod->id) }}">{{ $prod->codigo }}</a>
                                </td>
                                <td class="py-2 px-3 font-bold text-slate-800 dark:text-slate-200 uppercase">
                                    {{ $prod->descripcion }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-slate-600 dark:text-slate-400">
                                    $ {{ number_format($prod->precio_costo, 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    $ {{ number_format($prod->precio_venta, 2, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-slate-800 dark:text-white tabular-nums">
                                    {{ (int)$prod->getStockTotal() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 italic">
                                    Este proveedor aún no tiene artículos asociados en el catálogo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
