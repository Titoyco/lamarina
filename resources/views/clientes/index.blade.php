<x-app-layout>
    {{-- Encabezado Principal --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-3 gap-2">
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                <span>Gestión de Clientes</span>
                <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                    Padrón Unificado
                </span>
            </h1>

            <span class="hidden md:inline-flex items-center gap-1.5 text-[11px] text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800/80 px-2.5 py-1 rounded-lg">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                </svg>
                <span>Clic en cualquier fila para abrir opciones</span>
            </span>
        </div>
        
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('clientes.index', array_merge(request()->query(), ['export' => 'excel'])) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-all flex items-center space-x-1.5 shadow-sm"
               title="Exportar todos los clientes filtrados a Excel / CSV">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>EXPORTAR EXCEL</span>
            </a>

            <a href="{{ route('clientes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-md text-xs font-bold transition-all flex items-center space-x-1 shadow-sm w-fit">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                <span>NUEVO CLIENTE</span>
            </a>
        </div>
    </div>

    {{-- Filtros y Búsqueda --}}
    <div class="bg-slate-50 dark:bg-slate-800/70 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700/80 mb-4 shadow-sm">
        <form method="GET" action="{{ route('clientes.index') }}" class="space-y-2">
            
            <div class="flex gap-2">
                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Buscar por DNI, CUIT, Nro. Cuenta, CBU o Nombre (Ej: 30123456, USM-1045, Perez)..." 
                           class="w-full text-xs py-1.5 px-3 rounded-md border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-sm placeholder-slate-400 dark:placeholder-slate-500">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-4 rounded-md shadow-sm transition flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Buscar
                </button>
                @if(request()->anyFilled(['search', 'id_sucursal', 'id_localidad', 'id_provincia', 'suspendido', 'estado_banco']))
                    <a href="{{ route('clientes.index') }}" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs py-1.5 px-3 rounded-md transition flex items-center">
                        Limpiar
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2 pt-1.5 border-t border-slate-200/70 dark:border-slate-700/60">
                <select name="id_sucursal" onchange="this.form.submit()" class="text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200">
                    <option value="">Todas las Sucursales</option>
                    @foreach($sucursales as $sucursal)
                        <option value="{{ $sucursal->id }}" {{ request('id_sucursal') == $sucursal->id ? 'selected' : '' }}>{{ $sucursal->nombre }}</option>
                    @endforeach
                </select>

                <select name="id_provincia" onchange="this.form.submit()" class="text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200">
                    <option value="">Todas las Provincias</option>
                    @foreach($provincias as $provincia)
                        <option value="{{ $provincia->id }}" {{ request('id_provincia') == $provincia->id ? 'selected' : '' }}>{{ $provincia->nombre }}</option>
                    @endforeach
                </select>

                <select name="id_localidad" onchange="this.form.submit()" class="text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200">
                    <option value="">Todas las Localidades</option>
                    @foreach($localidades as $localidad)
                        <option value="{{ $localidad->id }}" {{ request('id_localidad') == $localidad->id ? 'selected' : '' }}>{{ $localidad->nombre }}</option>
                    @endforeach
                </select>

                <select name="suspendido" onchange="this.form.submit()" class="text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200">
                    <option value="todos" {{ request('suspendido') == 'todos' || !request()->has('suspendido') ? 'selected' : '' }}>Todos los Tipos</option>
                    <option value="0" {{ request('suspendido') === '0' ? 'selected' : '' }}>Normales</option>
                    <option value="1" {{ request('suspendido') === '1' ? 'selected' : '' }}>Suspendidos / Adicionales</option>
                </select>

                <select name="estado_banco" onchange="this.form.submit()" class="text-xs py-1 px-2 border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-900 font-medium text-slate-700 dark:text-slate-200">
                    <option value="">Todos los Estados CBU</option>
                    @foreach($estadosBanco as $est)
                        <option value="{{ $est->codigo }}" {{ request('estado_banco') == $est->codigo ? 'selected' : '' }}>{{ $est->codigo }} - {{ $est->descripcion }}</option>
                    @endforeach
                </select>

                <label class="space-x-1.5 cursor-pointer hover:text-blue-700 dark:hover:text-blue-400 transition-colors flex items-center">
                    <input type="checkbox" name="has_saldo" value="1" {{ request('has_saldo') ? 'checked' : '' }} 
                           class="rounded border-slate-400 dark:border-slate-600 text-blue-600 focus:ring-blue-500 h-3.5 w-3.5 bg-white dark:bg-slate-900" onchange="this.form.submit()">
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">Saldo > 0</span>
                </label>

            </div>
        </form>
    </div>

    @php
        $sort = $sort ?? request()->get('sort', 'nombre');
        $direction = $direction ?? request()->get('direction', 'asc');
        $nextDirection = function($col) use ($sort, $direction) {
            return ($sort === $col && $direction === 'asc') ? 'desc' : 'asc';
        };
        $sortUrl = function($col) use ($nextDirection) {
            return route('clientes.index', array_merge(request()->query(), ['sort' => $col, 'direction' => $nextDirection($col), 'page' => 1]));
        };
        $sortIndicator = function($col) use ($sort, $direction) {
            if ($sort === $col) {
                return $direction === 'asc' 
                    ? '<span class="inline-block text-blue-600 dark:text-blue-400 font-black ml-1 text-[11px] leading-none">▲</span>' 
                    : '<span class="inline-block text-blue-600 dark:text-blue-400 font-black ml-1 text-[11px] leading-none">▼</span>';
            }
            return '<span class="inline-block text-slate-300 dark:text-slate-600 group-hover:text-slate-500 font-normal ml-1 text-[10px] leading-none">⇅</span>';
        };
    @endphp

    {{-- Tabla de Clientes con Menú Contextual Integrado --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-md border border-slate-300 dark:border-slate-700 shadow-sm mb-4">
        <table class="w-full text-left border-collapse font-sans text-xs">
            <thead>
                <tr class="bg-slate-100 dark:bg-slate-900/80 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300 dark:border-slate-700 select-none">
                    <th class="py-2 px-3 border-r border-slate-200 dark:border-slate-700/60 w-44">
                        <a href="{{ $sortUrl('dni') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Documento / Cuenta">
                            <span>Documento / Cuenta</span>
                            {!! $sortIndicator('dni') !!}
                        </a>
                    </th>
                    <th class="py-2 px-3 border-r border-slate-200 dark:border-slate-700/60">
                        <a href="{{ $sortUrl('nombre') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Nombre de Cliente">
                            <span>Cliente / Sucursal</span>
                            {!! $sortIndicator('nombre') !!}
                        </a>
                    </th>
                    <th class="py-2 px-3 border-r border-slate-200 dark:border-slate-700/60 w-48">
                        <a href="{{ $sortUrl('localidad') }}" class="group flex items-center justify-between text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Ubicación">
                            <span>Ubicación</span>
                            {!! $sortIndicator('localidad') !!}
                        </a>
                    </th>
                    <th class="py-2 px-3 border-r border-slate-200 dark:border-slate-700/60 w-36 text-right">
                        <a href="{{ $sortUrl('saldo') }}" class="group flex items-center justify-end space-x-1 text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Ordenar por Saldo Actual">
                            <span>Saldo Actual</span>
                            {!! $sortIndicator('saldo') !!}
                        </a>
                    </th>
                    <th class="py-2 px-3 w-40">Contacto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                @forelse ($clientes as $cliente)
                    @php
                        $estaSuspendido = (int)$cliente->suspendido === 1;
                        $puedeEliminar = !$cliente->tieneHistorial() && $cliente->autorizados->isEmpty();
                        
                        // Estilos de fila con iluminación al pasar el mouse
                        $rowHover = $estaSuspendido 
                            ? 'bg-rose-50/50 dark:bg-rose-950/20 hover:bg-rose-100/70 dark:hover:bg-rose-900/40 border-l-4 border-l-rose-500 hover:border-l-rose-600'
                            : 'bg-white dark:bg-slate-800/90 hover:bg-blue-50/90 dark:hover:bg-slate-700/80 border-l-4 border-l-transparent hover:border-l-blue-500 dark:hover:border-l-blue-400';
                    @endphp

                    {{-- Fila Titular --}}
                    <tr class="{{ $rowHover }} transition-all duration-150 cursor-pointer hover:shadow-xs group"
                        x-data="contextMenuRow()"
                        @click="openMenu($event)"
                        @contextmenu.prevent="openMenu($event)">
                        
                        {{-- Documento / Cuenta --}}
                        <td class="py-2 px-3 font-mono font-bold text-slate-900 dark:text-slate-100 border-r border-slate-200/60 dark:border-slate-700/60">
                            <div class="flex flex-col">
                                <a href="{{ route('clientes.estado-cuenta', $cliente->id) }}" 
                                   title="Ver Estado de Cuenta" 
                                   class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors inline-block w-fit">
                                    DNI: {{ $cliente->dni }}
                                </a>
                                @if($cliente->cuit)
                                    <span class="text-[9px] text-slate-500 dark:text-slate-400 font-mono font-normal">CUIT: {{ $cliente->cuit }}</span>
                                @endif
                                @if($cliente->nro_cuenta)
                                    <span class="text-[9px] text-blue-700 dark:text-blue-300 font-bold bg-blue-50 dark:bg-blue-900/40 px-1 py-0.2 rounded w-fit mt-0.5 border border-blue-200 dark:border-blue-700/50">
                                        Cta: {{ $cliente->nro_cuenta }}
                                    </span>
                                @endif
                            </div>

                            {{-- Menú Contextual Modular --}}
                            <x-context-menu>
                                <x-context-menu.header 
                                    :title="$cliente->nombre" 
                                    :subtitle="'DNI ' . $cliente->dni . ($cliente->nro_cuenta ? ' • Cta ' . $cliente->nro_cuenta : '')"
                                    :badge="$estaSuspendido ? 'Suspendido' : 'Normal'"
                                    :badgeColor="$estaSuspendido ? 'rose' : 'emerald'" />

                                <x-context-menu.group label="Operaciones de Crédito">
                                    @if(!$estaSuspendido)
                                        <x-context-menu.item href="{{ route('ventas.create', ['id_cliente' => $cliente->id, 'medio_pago' => 'credito_personal']) }}" icon="plus" color="emerald">
                                            + Nuevo Crédito (Facturador)
                                        </x-context-menu.item>
                                    @else
                                        <x-context-menu.item disabled icon="ban" color="rose">
                                            Crédito Suspendido
                                        </x-context-menu.item>
                                    @endif

                                    <x-context-menu.item href="{{ route('clientes.estado-cuenta', $cliente->id) }}" icon="money" color="blue">
                                        Estado de Cuenta
                                    </x-context-menu.item>

                                    <x-context-menu.item href="{{ route('creditos.index', ['cliente_id' => $cliente->id, 'estado' => 'all']) }}" icon="history">
                                        Historial de Créditos
                                    </x-context-menu.item>
                                </x-context-menu.group>

                                <x-context-menu.group label="Ficha y Edición">
                                    <x-context-menu.item href="{{ route('clientes.show', $cliente->id) }}" icon="document">
                                        Ver Legajo Completo
                                    </x-context-menu.item>

                                    <x-context-menu.item href="{{ route('clientes.edit', $cliente->id) }}" icon="pencil">
                                        Editar Cliente
                                    </x-context-menu.item>
                                </x-context-menu.group>

                                <x-context-menu.group label="Gestión">
                                    @if($puedeEliminar)
                                        <x-context-menu.item 
                                            action="{{ route('clientes.destroy', $cliente->id) }}" 
                                            method="DELETE" 
                                            icon="trash" 
                                            color="rose"
                                            confirm="¿Seguro que deseas eliminar definitivamente este legajo sin historial?">
                                            Eliminar Cliente
                                        </x-context-menu.item>
                                    @else
                                        <x-context-menu.item disabled icon="trash" color="slate">
                                            No eliminable (con historial)
                                        </x-context-menu.item>
                                    @endif
                                </x-context-menu.group>
                            </x-context-menu>
                        </td>
                        
                        {{-- Cliente / Sucursal --}}
                        <td class="py-2 px-3 border-r border-slate-200/60 dark:border-slate-700/60">
                            <div class="flex items-center space-x-1.5">
                                <a href="{{ route('clientes.show', $cliente->id) }}" 
                                   class="font-bold text-slate-900 dark:text-slate-100 uppercase hover:text-blue-600 dark:hover:text-blue-400 transition-colors truncate">
                                    {{ $cliente->nombre }}
                                </a>
                                @if($estaSuspendido)
                                    <span class="bg-rose-700 text-white text-[9px] font-black px-1.5 py-0.5 rounded uppercase leading-none">SUSPENDIDO</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                                    {{ $cliente->sucursal->nombre ?? 'Sucursal' }} ({{ $cliente->sucursal->codigo_sucursal ?? '-' }})
                                </span>
                                @if($cliente->cbu)
                                    <span class="text-[9px] font-mono text-emerald-800 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 px-1 rounded truncate max-w-[150px]" title="CBU: {{ $cliente->cbu }}">
                                        CBU: {{ substr($cliente->cbu, 0, 4) }}...{{ substr($cliente->cbu, -4) }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        
                        {{-- Ubicación --}}
                        <td class="py-2 px-3 text-slate-600 dark:text-slate-300 border-r border-slate-200/60 dark:border-slate-700/60 text-[10px]">
                            <p class="font-bold uppercase text-slate-800 dark:text-slate-200 truncate">{{ $cliente->localidad->nombre ?? 'S/D' }}</p>
                            <p class="text-[9px] text-slate-400 dark:text-slate-500">{{ $cliente->localidad->provincia->nombre ?? '' }}</p>
                        </td>
                        
                        {{-- Saldo --}}
                        <td class="py-2 px-3 font-mono font-bold text-right text-slate-900 dark:text-slate-100 border-r border-slate-200/60 dark:border-slate-700/60 tabular-nums text-[13px]">
                            $ {{ number_format($cliente->saldo, 2, ',', '.') }}
                        </td>

                        {{-- Contacto --}}
                        <td class="py-2 px-3 font-mono text-slate-700 dark:text-slate-300 text-[11px]">
                            {{ $cliente->tel1 ?? '-' }}
                        </td>
                    </tr>

                    {{-- Subfilas de Firmas Autorizadas --}}
                    @foreach ($cliente->autorizados as $autorizado)
                        @php
                            $padreSuspendido = (int)$cliente->suspendido === 1;
                        @endphp
                        <tr class="bg-slate-50/60 dark:bg-slate-900/30 hover:bg-slate-100/90 dark:hover:bg-slate-800/80 transition-all duration-150 text-[11px] border-b border-slate-200 dark:border-slate-700/60 border-l-2 border-l-blue-400 hover:border-l-blue-600 cursor-pointer group"
                            x-data="contextMenuRow()"
                            @click="openMenu($event)"
                            @contextmenu.prevent="openMenu($event)">
                            
                            {{-- DNI Autorizado con Menú Contextual --}}
                            <td class="py-1.5 px-3 pl-8 font-mono text-slate-500 dark:text-slate-400 border-r border-slate-200/60 dark:border-slate-700/60">
                                └ {{ $autorizado->dni }}

                                <x-context-menu>
                                    <x-context-menu.header 
                                        :title="$autorizado->nombre" 
                                        :subtitle="'Firma Autorizada • DNI ' . $autorizado->dni"
                                        badge="AUTORIZADO"
                                        badgeColor="blue" />

                                    <x-context-menu.group label="Operaciones">
                                        @if(!$padreSuspendido)
                                            <x-context-menu.item href="{{ route('ventas.create', ['id_cliente' => $cliente->id, 'medio_pago' => 'credito_personal']) }}" icon="plus" color="emerald">
                                                + Crédito Autorizado (Facturador)
                                            </x-context-menu.item>
                                        @else
                                            <x-context-menu.item disabled icon="ban" color="rose">
                                                Titular Suspendido
                                            </x-context-menu.item>
                                        @endif
                                    </x-context-menu.group>

                                    <x-context-menu.group label="Ficha">
                                        <x-context-menu.item href="{{ route('clientes.show', $autorizado->id) }}" icon="document">
                                            Ver Legajo Autorizado
                                        </x-context-menu.item>

                                        <x-context-menu.item href="{{ route('clientes.edit', $autorizado->id) }}" icon="pencil">
                                            Editar Autorizado
                                        </x-context-menu.item>
                                    </x-context-menu.group>

                                    <x-context-menu.group label="Vínculo">
                                        <x-context-menu.item 
                                            action="{{ route('clientes.destroy', $autorizado->id) }}" 
                                            method="DELETE" 
                                            icon="unlink" 
                                            color="rose"
                                            confirm="¿Desvincular a esta firma autorizada del cliente?">
                                            Desvincular Firma
                                        </x-context-menu.item>
                                    </x-context-menu.group>
                                </x-context-menu>
                            </td>
                            
                            {{-- Nombre y Relación --}}
                            <td class="py-1.5 px-3 border-r border-slate-200/60 dark:border-slate-700/60">
                                <div class="flex items-center space-x-1.5">
                                    <span class="bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-[8px] font-bold px-1 py-0.5 rounded uppercase leading-none">AUT</span>
                                    <span class="font-medium text-slate-700 dark:text-slate-300 uppercase truncate">{{ $autorizado->nombre }}</span>
                                    <span class="text-[9px] text-slate-400 dark:text-slate-500">({{ $autorizado->pivot->relacion_aut ?? 'Familiar' }})</span>
                                </div>
                            </td>
                            
                            {{-- Ubicación --}}
                            <td class="py-1.5 px-3 text-slate-500 dark:text-slate-400 border-r border-slate-200/60 dark:border-slate-700/60 uppercase text-[10px] italic">
                                {{ $autorizado->localidad->nombre ?? 'Heredada' }}
                            </td>
                            
                            {{-- Saldo (Vacío para autorizados ya que es compartido con titular) --}}
                            <td class="py-1.5 px-3 font-mono text-right text-slate-400 dark:text-slate-500 border-r border-slate-200/60 dark:border-slate-700/60 tabular-nums italic text-[10px]">
                                (Comparte saldo titular)
                            </td>

                            {{-- Teléfono --}}
                            <td class="py-1.5 px-3 font-mono text-slate-500 dark:text-slate-400 text-[11px]">
                                {{ $autorizado->tel1 ?? '-' }}
                            </td>
                        </tr>
                    @endforeach

                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500 font-medium text-xs italic bg-slate-50 dark:bg-slate-900/30">
                            No se encontraron clientes registrados en el padrón con los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse    
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="px-1 text-xs">
        {{ $clientes->appends(request()->query())->links() }}
    </div>
</x-app-layout>