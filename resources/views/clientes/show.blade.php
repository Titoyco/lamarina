<x-app-layout>
    <!-- Encabezado y Navegación de Pestañas -->
    <div class="mb-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-slate-200 pb-2">
        <div class="flex items-center space-x-3">
            @if(!request()->boolean('embed'))
            <a href="{{ url()->previous() }}" class="bg-white p-1.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            @endif
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight flex items-center space-x-2">
                    <span class="uppercase">{{ $cliente->nombre }}</span>
                    @if($cliente->suspendido)
                        <span class="bg-rose-100 text-rose-800 text-[9px] font-black px-2 py-0.5 rounded border border-rose-200 uppercase">SUSPENDIDO</span>
                    @else
                        <span class="bg-emerald-100 text-emerald-800 text-[9px] font-black px-2 py-0.5 rounded border border-emerald-200 uppercase">HABILITADO</span>
                    @endif
                </h1>
                <p class="text-xs text-slate-500 font-medium">
                    Sucursal: <span class="font-bold text-slate-700">{{ $cliente->sucursal?->nombre }} ({{ $cliente->sucursal?->codigo_sucursal }})</span> &bull; 
                    Alta: {{ $cliente->created_at ? $cliente->created_at->format('d/m/Y') : '-' }}
                </p>
            </div>
        </div>

        <div class="flex bg-slate-100 p-0.5 rounded-md border border-slate-200">
            <a href="{{ route('clientes.show', array_merge(['cliente' => $cliente], request()->boolean('embed') ? ['embed' => 1] : [])) }}" class="px-3 py-1 rounded text-[11px] font-black bg-white text-blue-900 shadow-sm border border-slate-200 transition-all">
                📄 Legajo Personal
            </a>
            <a href="{{ route('clientes.estado-cuenta', array_merge(['cliente' => $cliente], request()->boolean('embed') ? ['embed' => 1] : [])) }}" class="px-3 py-1 rounded text-[11px] font-bold text-slate-600 hover:text-slate-900 transition-all">
                💳 Estado de Cuenta
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-3 p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-md font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-3 items-start">
        
        <!-- Columna Izquierda: Información Detallada -->
        <div class="w-full lg:w-[66.6%] space-y-3">
            
            <!-- 1. Datos Personales y Sucursal -->
            <div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5 flex justify-between items-center">
                    <h2 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">1. Identificación y Sucursal</h2>
                    <a href="{{ route('clientes.edit', array_merge(['cliente' => $cliente], request()->boolean('embed') ? ['embed' => 1] : [])) }}" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 uppercase flex items-center space-x-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span>Editar</span>
                    </a>
                </div>
                <div class="p-3 grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Nombre Completo / Razón Social</span>
                        <span class="font-bold text-slate-800 text-xs uppercase">{{ $cliente->nombre }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">DNI</span>
                        <span class="font-mono font-black text-blue-900 text-xs tabular-nums">{{ $cliente->dni }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">CUIT / CUIL</span>
                        <span class="font-mono font-bold text-slate-800 text-xs tabular-nums">{{ $cliente->cuit ?? 'No registrado' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Nro. Cuenta / Legajo</span>
                        <span class="font-mono font-bold text-blue-700 text-xs">{{ $cliente->nro_cuenta ?? 'Sin Asignar' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Sucursal de Alta</span>
                        <span class="font-bold text-slate-700 text-xs">{{ $cliente->sucursal?->nombre ?? 'Sucursal' }} ({{ $cliente->sucursal?->codigo_sucursal }})</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Medio Preferido</span>
                        <span class="font-semibold text-slate-700 text-xs">
                            {{ $cliente->medio_pago_preferido == 1 ? 'Débito Bancario en CBU' : 'Cobranza en Sucursal (Cta. Cte.)' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Ubicación y Contacto -->
            <div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5">
                    <h2 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">2. Ubicación y Contacto</h2>
                </div>
                <div class="p-3 grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Dirección Particular</span>
                        <span class="font-bold text-slate-800 text-xs uppercase">{{ $cliente->direccion ?? 'Sin dirección' }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Localidad y Provincia</span>
                        <span class="font-bold text-slate-700 text-xs uppercase">
                            {{ $cliente->localidad?->nombre ?? 'S/D' }} 
                            @if($cliente->localidad?->provincia)
                                ({{ $cliente->localidad->provincia->nombre }})
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Tel. Principal (Celular)</span>
                        <span class="font-mono font-bold text-slate-800 text-xs">{{ $cliente->tel1 ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Tel. Alternativo</span>
                        <span class="font-mono font-medium text-slate-700 text-xs">{{ $cliente->tel2 ?? '-' }}</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Email</span>
                        <span class="font-medium text-slate-700 text-xs truncate">{{ $cliente->email ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. Información Laboral y Bancaria -->
            <div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5">
                    <h2 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">3. Laboral y Bancaria</h2>
                </div>
                <div class="p-3 grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Lugar de Trabajo / Empleador</span>
                        <span class="font-bold text-slate-800 text-xs uppercase">{{ $cliente->trabajo ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Tipo Haber (ID)</span>
                        <span class="font-bold text-slate-700 text-xs font-mono">{{ $cliente->tipo_haber ?? '1' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Máx Cuotas</span>
                        <span class="font-bold text-slate-700 text-xs">{{ $cliente->max_cuotas ?? 4 }} Cuotas</span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Dirección Laboral</span>
                        <span class="font-medium text-slate-700 text-xs uppercase">
                            {{ $cliente->direccion_trabajo ?? '-' }} 
                            @if($cliente->localidadTrabajo)
                                ({{ $cliente->localidadTrabajo->nombre }})
                            @endif
                        </span>
                    </div>
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Tel. Laboral</span>
                        <span class="font-mono font-medium text-slate-700 text-xs">{{ $cliente->tel_trabajo ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- 4. Familiar de Contacto / Referencia -->
            <div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5">
                    <h2 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">4. Referencia Familiar / Contacto</h2>
                </div>
                <div class="p-3 grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="col-span-2">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Familiar</span>
                        <span class="font-bold text-slate-800 text-xs uppercase">
                            {{ $cliente->familiar ?? '-' }} 
                            @if($cliente->tipo_familiar)
                                <span class="text-[10px] font-normal text-slate-500">({{ $cliente->tipo_familiar }})</span>
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Teléfono Familiar</span>
                        <span class="font-mono font-bold text-slate-700 text-xs">{{ $cliente->tel_familiar ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-1">Localidad Familiar</span>
                        <span class="font-medium text-slate-700 text-xs uppercase">{{ $cliente->localidadFamiliar?->nombre ?? '-' }}</span>
                    </div>
                    @if($cliente->direccion_familiar)
                        <div class="col-span-4 border-t border-slate-100 pt-1.5">
                            <span class="text-[9px] font-bold text-slate-400 uppercase block leading-none mb-0.5">Dirección Familiar</span>
                            <span class="font-medium text-slate-700 text-xs uppercase">{{ $cliente->direccion_familiar }}</span>
                        </div>
                    @endif
                </div>
                @if(!empty($cliente->obs))
                    <div class="px-3 pb-3">
                        <span class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Observaciones Generales</span>
                        <p class="text-[11px] text-slate-700 bg-amber-50 p-2.5 rounded border border-amber-200 italic leading-snug">
                            {{ $cliente->obs }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Firmas Autorizadas -->
            <div class="bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden">
                <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5 flex justify-between items-center">
                    <h2 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">Firmas Autorizadas ({{ $cliente->autorizados ? $cliente->autorizados->count() : 0 }})</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[9px] uppercase text-slate-500">
                            <tr>
                                <th class="py-1.5 px-3">Nombre</th>
                                <th class="py-1.5 px-3">DNI</th>
                                <th class="py-1.5 px-3">Relación</th>
                                <th class="py-1.5 px-3 text-right">Límite ($)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($cliente->autorizados as $autorizado)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-1.5 px-3 font-bold text-slate-800 uppercase">{{ $autorizado->nombre }}</td>
                                    <td class="py-1.5 px-3 font-mono font-bold">{{ $autorizado->dni }}</td>
                                    <td class="py-1.5 px-3">
                                        <span class="bg-blue-100 text-blue-800 text-[9px] font-bold px-1.5 py-0.5 rounded">{{ $autorizado->pivot->relacion_aut ?? 'ADICIONAL' }}</span>
                                    </td>
                                    <td class="py-1.5 px-3 text-right font-mono font-semibold tabular-nums">{{ number_format($autorizado->pivot->limite_aut ?? 0, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-3 px-3 text-center text-slate-400 italic text-[10px]">No hay adicionales autorizados vinculados a este titular.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Tarjeta Financiera, Acciones y Novedades -->
        <div class="w-full lg:w-[33.3%] space-y-3">
            
            <!-- Resumen Financiero -->
            <div class="bg-blue-950 text-white p-4 rounded-xl shadow-md border border-blue-900 relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="text-[9px] font-bold text-blue-300 uppercase tracking-widest block">Límite de Crédito</span>
                            <div class="text-2xl font-black text-white font-mono tabular-nums tracking-tight">
                                $ {{ number_format($cliente->limite ?? 0, 2, ',', '.') }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[9px] font-bold text-amber-300 uppercase tracking-widest block">Saldo Deudor</span>
                            <div class="text-lg font-black text-amber-400 font-mono tabular-nums">
                                $ {{ number_format($cliente->saldo ?? 0, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-blue-800/80 mt-3 pt-2.5 space-y-1.5">
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="text-blue-300 font-medium">Nro. de Cuenta:</span>
                            <span class="font-mono font-bold text-white">{{ $cliente->nro_cuenta ?? 'Sin asignar' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="text-blue-300 font-medium">CBU Bancario:</span>
                            <span class="font-mono text-[10px] text-blue-100 truncate max-w-[180px]" title="{{ $cliente->cbu }}">{{ $cliente->cbu ?? 'No especificado' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-[11px]">
                            <span class="text-blue-300 font-medium">Medio de Pago:</span>
                            <span class="font-bold text-emerald-400 uppercase text-[10px]">
                                {{ $cliente->medio_pago_preferido == 1 ? 'Débito CBU' : 'Sucursal / Cta Cte' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Directas -->
            <div class="bg-white rounded-md border border-slate-300 shadow-sm p-3 space-y-2">
                @if($cliente->suspendido)
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 p-2.5 rounded text-center">
                        <span class="font-black text-xs uppercase block">⛔ CLIENTE SUSPENDIDO</span>
                        <span class="text-[9px] text-rose-600 block mt-0.5">Operaciones de crédito bloqueadas</span>
                    </div>
                @else
                    <a href="{{ route('ventas.create', ['id_cliente' => $cliente->id, 'medio_pago' => 'credito_personal']) }}" 
                       target="_top"
                       class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs py-2 px-3 rounded-md uppercase transition-all flex items-center justify-center space-x-1.5 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        <span>+ INICIAR VENTA / CRÉDITO</span>
                    </a>
                @endif
                <a href="{{ route('clientes.estado-cuenta', array_merge(['cliente' => $cliente->id], request()->boolean('embed') ? ['embed' => 1] : [])) }}" class="w-full bg-slate-700 hover:bg-slate-800 text-white font-bold text-xs py-2 px-3 rounded-md uppercase transition-all flex items-center justify-center space-x-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span>Ver Estado de Cuenta</span>
                </a>
            </div>

            <!-- Historial / Novedades -->
            <div class="bg-white rounded-md border border-slate-300 shadow-sm flex flex-col h-[380px]">
                <div class="bg-slate-100 border-b border-slate-300 px-3 py-1.5 flex justify-between items-center">
                    <h2 class="font-bold text-[10px] text-slate-700 uppercase tracking-wider">Historial / Novedades</h2>
                    <span class="bg-slate-300 text-slate-700 font-bold text-[9px] px-1.5 py-0.5 rounded">{{ $cliente->novedades ? $cliente->novedades->count() : 0 }}</span>
                </div>
                
                <div class="flex-1 overflow-y-auto p-2.5 space-y-2 bg-slate-50/60">
                    @if($cliente->novedades && $cliente->novedades->count() > 0)
                        @foreach($cliente->novedades as $novedad)
                            <div class="p-2.5 rounded-md text-xs shadow-xs bg-white border border-slate-200 border-l-4 border-l-blue-600">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-bold text-slate-800 uppercase text-[10px]">{{ $novedad->user?->name ?? $novedad->usuario_nombre ?? 'Operador' }}</span>
                                    <span class="text-[9px] font-mono text-slate-400">{{ \Carbon\Carbon::parse($novedad->created_at)->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="text-slate-600 text-[11px] leading-relaxed">{{ $novedad->mensaje }}</p>
                            </div>
                        @endforeach
                    @else
                        <div class="h-full flex items-center justify-center">
                            <span class="text-xs text-slate-400 font-medium italic">Sin novedades registradas.</span>
                        </div>
                    @endif
                </div>

                <div class="border-t border-slate-200 p-2 bg-white">
                    <form action="{{ route('clientes.novedades.store', $cliente->id) }}" method="POST" class="flex flex-col space-y-1.5">
                        @csrf
                        @if(request()->boolean('embed'))
                            <input type="hidden" name="embed" value="1">
                        @endif
                        <textarea name="mensaje" rows="2" placeholder="Agregar nota, llamada o acuerdo..." required class="w-full text-xs rounded border-slate-300 focus:border-blue-500 focus:ring-blue-500 p-1.5 resize-none"></textarea>
                        <button type="submit" class="bg-blue-800 hover:bg-blue-900 text-white font-bold text-[10px] py-1.5 px-3 rounded uppercase transition-all shadow-sm w-full">
                            + Registrar Novedad
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>