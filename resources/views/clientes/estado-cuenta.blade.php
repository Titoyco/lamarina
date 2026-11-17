<x-app-layout>
    <!-- Encabezado y Pestañas -->
    <div class="mb-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3 border-b border-slate-200 pb-2">
        <div class="flex items-center space-x-3">
            <a href="{{  url()->previous()  }}" class="bg-white p-1.5 rounded-md border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight flex items-center space-x-2 flex-wrap gap-1">
                    <a href="{{ route('clientes.show', array_merge(['cliente' => $cliente], request()->boolean('embed') ? ['embed' => 1] : [])) }}" 
                       class="uppercase hover:text-blue-700 hover:underline transition-colors" 
                       title="Ver legajo de {{ $cliente->nombre }}">
                        {{ $cliente->nombre }} ↗
                    </a>
                    <span class="bg-blue-100 text-blue-900 text-[10px] font-black px-2 py-0.5 rounded border border-blue-200 uppercase font-mono">DNI: {{ $cliente->dni }}</span>
                    @if($cliente->cuit)
                        <span class="bg-slate-100 text-slate-700 text-[10px] font-bold px-2 py-0.5 rounded border border-slate-200 uppercase font-mono">CUIT: {{ $cliente->cuit }}</span>
                    @endif
                    @if($cliente->nro_cuenta)
                        <span class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-200 uppercase font-mono">Cta: {{ $cliente->nro_cuenta }}</span>
                    @endif
                    <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded border border-slate-200">
                        {{ $cliente->sucursal?->nombre }} ({{ $cliente->sucursal?->codigo_sucursal }})
                    </span>
                </h1>
            </div>
        </div>

        <div class="flex bg-slate-100 p-0.5 rounded-md border border-slate-200">
            <a href="{{ route('clientes.show', array_merge(['cliente' => $cliente], request()->boolean('embed') ? ['embed' => 1] : [])) }}" class="px-3 py-1 rounded text-[11px] font-bold text-slate-600 hover:text-slate-900 transition-all">
                📄 Legajo Personal
            </a>
            <a href="{{ route('clientes.estado-cuenta', array_merge(['cliente' => $cliente], request()->boolean('embed') ? ['embed' => 1] : [])) }}" class="px-3 py-1 rounded text-[11px] font-black bg-white text-blue-900 shadow-sm border border-slate-200 transition-all">
                💳 Estado de Cuenta
            </a>
        </div>
    </div>

    <!-- Cinta KPI Financiera -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-2 mb-4">
        <div class="bg-white p-2 rounded-md border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[9px] font-bold text-slate-500 uppercase">Total Deuda Base</span>
            <span class="font-mono font-black text-slate-900 text-sm tabular-nums">$ {{ number_format($totalValorBase, 2, ',', '.') }}</span>
        </div>
        <div class="bg-white p-2 rounded-md border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[9px] font-bold text-slate-500 uppercase">Total Intereses</span>
            <span class="font-mono font-black text-red-800 text-sm tabular-nums">$ {{ number_format($totalIntereses, 2, ',', '.') }}</span>
        </div>
        <div class="bg-white p-2 rounded-md border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-[9px] font-bold text-slate-500 uppercase">Total Entregado</span>
            <span class="font-mono font-black text-slate-900 text-sm tabular-nums">$ {{ number_format($totalEntregado, 2, ',', '.') }}</span>
        </div>
        <div class="bg-emerald-50 p-2 rounded-md border border-emerald-200 shadow-sm flex flex-col justify-between">
            <span class="text-[9px] font-bold text-emerald-800 uppercase">Total a Pagar</span>
            <span class="font-mono font-black text-emerald-700 text-sm tabular-nums">$ {{ number_format($totalAPagar, 2, ',', '.') }}</span>
        </div>
        <div class="bg-blue-900 p-2 rounded-md border border-blue-950 shadow-sm flex flex-col justify-between">
            <span class="text-[9px] font-bold text-blue-200 uppercase">A Vencer</span>
            <span class="font-mono font-black text-white text-sm tabular-nums">$ {{ number_format($totalRestante, 2, ',', '.') }}</span>
        </div>
    </div>



    <form action="{{ route('recibos.create') }}" method="GET">
        <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">
        
        <!-- Toolbar Tabla -->
        <div class="bg-slate-50 p-2 rounded-t-md border border-slate-300 border-b-0 flex items-center justify-between shadow-sm">
            
            <!-- Izquierda: Título y Contador -->
            <div class="flex items-center space-x-2 pl-1">
                <span class="text-xs font-bold text-slate-800 uppercase">Créditos Activos</span>
                <span class="text-[9px] font-black bg-slate-200 border border-slate-300 text-slate-600 px-1.5 py-0.5 rounded">{{ $creditosAbiertos->count() }}</span>
            </div>
            
            <!-- Derecha: Botones y Acciones -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white border border-emerald-700 px-3 py-1 rounded text-[10px] font-bold uppercase transition-all shadow-sm flex items-center space-x-1">
                    <span>💵 Emitir Recibo de Cobranza</span>
                </button>
                <button type="button" class="bg-amber-100 hover:bg-amber-500 hover:text-white text-amber-900 border border-amber-300 px-3 py-1 rounded text-[10px] font-bold uppercase transition-all shadow-sm">
                    🔄 Refinanciar Sel.
                </button>

                <!-- Separador vertical -->
                <div class="w-px h-5 bg-slate-300 mx-1"></div>

                <!-- Botón Condicional -->
                @if(isset($cliente->suspendido) && $cliente->suspendido)
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 px-3 py-1 rounded shadow-sm flex items-center">
                        <span class="font-black text-[10px] uppercase">⛔ CLIENTE SUSPENDIDO</span>
                    </div>
                    <a href="{{ route('ventas.create', ['id_cliente' => $cliente->id, 'medio_pago' => 'credito_personal']) }}" 
                       target="_top"
                       class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] py-1 px-3 rounded uppercase transition-all flex items-center justify-center space-x-1 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>NUEVO CRÉDITO (FACTURADOR)</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-b-md border border-slate-300 shadow-sm overflow-hidden mb-6">
            <table class="w-full text-left border-collapse font-sans text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300">
                        <th class="py-1.5 px-2 text-center border-r border-slate-200 w-10">
                            <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 h-3 w-3"
                                   onclick="document.querySelectorAll('input[name=\'creditos_ids[]\']').forEach(c => c.checked = this.checked);">
                        </th>
                        <th class="py-1.5 px-3 border-r border-slate-200 w-32">Identificador</th>
                        <th class="py-1.5 px-3 border-r border-slate-200 w-24 text-center">Fecha</th>
                        
                        <!-- Columna de Plan -->
                        <th class="py-1.5 px-3 border-r border-slate-200 text-center w-36">Plan (Cuotas)</th>
                        
                        <!-- Nueva Columna: Intereses -->
                        <th class="py-1.5 px-3 border-r border-slate-200 text-right w-24">Intereses</th>
                        
                        <th class="py-1.5 px-3 border-r border-slate-200 text-right w-24">Pagado</th>
                        <th class="py-1.5 px-3 border-r border-slate-200 text-right w-28">Saldo Actual</th>
                        <th class="py-1.5 px-3 text-center w-36">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($creditosAbiertos as $credito)
                    @php
                        $pagadoCredito = $credito->cuotas->sum('entrego');
                        $saldoCredito  = $credito->cuotas->sum('saldo_actual');
                        $interesesCredito = $credito->cuotas->sum('intereses');
                        
                        $cantCuotas = $credito->cantidad_cuotas ?? 1;
                        $valorCuotaDisplay = $credito->valor_cuota_ref ?? $credito->valor_cuota ?? ($cantCuotas > 0 ? $credito->importe_original / $cantCuotas : $credito->importe_original);
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="py-1.5 px-2 text-center border-r border-slate-200/60">
                            <input type="checkbox" name="creditos_ids[]" value="{{ $credito->id }}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 h-3 w-3">
                        </td>
                        <td class="py-1.5 px-3 border-r border-slate-200/60">
                            <a href="{{ route('creditos.show', $credito) }}" class="font-mono font-bold text-blue-700 hover:text-blue-500 hover:underline transition-colors text-[11px]">
                                {{ $credito->full_identificador }}
                            </a>
                        </td>
                        <td class="py-1.5 px-3 text-center text-[11px] font-mono text-slate-600 border-r border-slate-200/60">
                            {{ $credito->fecha_credito ? \Carbon\Carbon::parse($credito->fecha_credito)->format('d/m/Y') : '-' }}
                        </td>
                        
                        <!-- Plan (Cuotas) -->
                        <td class="py-1 px-3 text-center border-r border-slate-200/60 leading-tight">
                            <span class="text-[10px] text-slate-500"><strong class="text-slate-800">{{ $cantCuotas }}</strong> cuotas de</span><br>
                            <span class="font-mono font-bold text-slate-900 text-[11px] tabular-nums">$ {{ number_format($valorCuotaDisplay, 2, ',', '.') }}</span>
                        </td>

                        <!-- Intereses (Bordó) -->
                        <td class="py-1.5 px-3 text-right font-mono text-red-800 font-bold text-[11px] border-r border-slate-200/60 tabular-nums">
                            $ {{ number_format($interesesCredito, 2, ',', '.') }}
                        </td>
                        
                        <!-- Pagado (Negro) -->
                        <td class="py-1.5 px-3 text-right font-mono text-slate-900 font-bold text-[11px] border-r border-slate-200/60 tabular-nums">
                            $ {{ number_format($pagadoCredito, 2, ',', '.') }}
                        </td>
                        
                        <!-- Saldo Actual (Verde Esmeralda) -->
                        <td class="py-1.5 px-3 text-right font-mono font-black text-emerald-700 text-[11px] border-r border-slate-200/60 tabular-nums">
                            $ {{ number_format($saldoCredito, 2, ',', '.') }}
                        </td>
                        
                        <td class="py-1 px-2 text-center space-x-1">
                            <a href="{{ route('recibos.create', ['cliente_id' => $cliente->id]) }}" 
                               class="inline-flex items-center bg-emerald-50 border border-emerald-200 hover:bg-emerald-600 hover:text-white text-emerald-800 px-2 py-0.5 rounded text-[10px] font-bold transition-all uppercase shadow-xs">
                                💵 Cobrar
                            </a>
                            <a href="{{ route('creditos.show', $credito) }}" class="inline-flex items-center bg-slate-100 border border-slate-200 hover:bg-blue-600 hover:text-white text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold transition-all uppercase shadow-xs">
                                Detalle →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400 font-medium text-xs italic bg-slate-50">
                            El cliente no tiene créditos abiertos pendientes.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <!-- HISTORIAL DE RECIBOS DE COBRANZA (CUENTA CORRIENTE) -->
    <div class="mb-6">
        <div class="bg-slate-50 p-2 rounded-t-md border border-slate-300 border-b-0 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-2 pl-1">
                <span class="text-xs font-bold text-slate-800 uppercase">Historial de Recibos de Cobranza (Ingresos a Cuenta Corriente)</span>
                <span class="text-[9px] font-black bg-blue-100 border border-blue-200 text-blue-800 px-1.5 py-0.5 rounded">
                    {{ $recibos->count() }}
                </span>
            </div>
            <div>
                <a href="{{ route('recibos.create', ['cliente_id' => $cliente->id]) }}" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] py-1 px-2.5 rounded uppercase transition-all shadow-xs">
                    + Emitir Nuevo Recibo
                </a>
            </div>
        </div>

        <div class="bg-white rounded-b-md border border-slate-300 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse font-sans text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300">
                        <th class="py-1.5 px-3 border-r border-slate-200 w-32">N° Recibo</th>
                        <th class="py-1.5 px-3 border-r border-slate-200 w-28 text-center">Fecha</th>
                        <th class="py-1.5 px-3 border-r border-slate-200">Sucursal</th>
                        <th class="py-1.5 px-3 border-r border-slate-200">Medio de Pago</th>
                        <th class="py-1.5 px-3 border-r border-slate-200">Operador</th>
                        <th class="py-1.5 px-3 border-r border-slate-200 text-right w-28">Importe Cobrado</th>
                        <th class="py-1.5 px-3 border-r border-slate-200 text-center w-24">Estado</th>
                        <th class="py-1.5 px-3 text-center w-28">Comprobante</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($recibos as $recibo)
                        <tr class="hover:bg-blue-50/40 transition-colors {{ $recibo->anulado ? 'opacity-60 bg-rose-50/20' : '' }}">
                            <td class="py-1.5 px-3 font-mono font-bold border-r border-slate-200/60">
                                <a href="{{ route('recibos.show', $recibo->id) }}" class="text-blue-700 hover:underline">
                                    {{ $recibo->numero_formateado }}
                                </a>
                            </td>
                            <td class="py-1.5 px-3 text-center font-mono text-[11px] text-slate-600 border-r border-slate-200/60">
                                {{ \Carbon\Carbon::parse($recibo->fecha)->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-1.5 px-3 text-slate-700 border-r border-slate-200/60">
                                {{ $recibo->sucursal?->nombre }}
                            </td>
                            <td class="py-1.5 px-3 text-slate-700 font-medium border-r border-slate-200/60">
                                {{ $recibo->medio_pago }}
                            </td>
                            <td class="py-1.5 px-3 text-slate-600 text-[11px] border-r border-slate-200/60">
                                {{ $recibo->user?->name ?? 'Operador' }}
                            </td>
                            <td class="py-1.5 px-3 text-right font-mono font-black text-emerald-700 border-r border-slate-200/60 tabular-nums">
                                $ {{ number_format($recibo->importe_total, 2, ',', '.') }}
                            </td>
                            <td class="py-1.5 px-3 text-center border-r border-slate-200/60">
                                @if($recibo->anulado)
                                    <span class="bg-rose-100 text-rose-800 text-[9px] font-black px-1.5 py-0.5 rounded border border-rose-300 uppercase">
                                        Anulado
                                    </span>
                                @else
                                    <span class="bg-emerald-100 text-emerald-800 text-[9px] font-black px-1.5 py-0.5 rounded border border-emerald-300 uppercase">
                                        Activo
                                    </span>
                                @endif
                            </td>
                            <td class="py-1 px-2 text-center space-x-1">
                                <a href="{{ route('recibos.show', $recibo->id) }}" 
                                   class="inline-flex items-center bg-slate-100 hover:bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded text-[10px] font-bold transition shadow-2xs uppercase">
                                    Ver
                                </a>
                                <a href="{{ route('recibos.imprimir', $recibo->id) }}" target="_blank"
                                   class="inline-flex items-center bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 border border-blue-200 px-1.5 py-0.5 rounded text-[10px] font-bold transition shadow-2xs uppercase">
                                    🖨️
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400 font-medium text-xs italic bg-slate-50">
                                No se registran recibos emitidos para este cliente en el sistema.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- CRÉDITOS CANCELADOS HISTÓRICOS (COLAPSABLE / SECUNDARIO) -->
    @if($creditosCancelados->isNotEmpty())
        <div class="mb-4" x-data="{ abierto: false }">
            <button type="button" @click="abierto = !abierto" 
                    class="w-full bg-slate-100 hover:bg-slate-200 p-2 rounded-md border border-slate-300 flex items-center justify-between text-xs font-bold text-slate-700 uppercase transition">
                <span class="flex items-center space-x-2">
                    <span>📁 Créditos Históricos Cancelados / Saldados</span>
                    <span class="bg-slate-200 text-slate-600 text-[9px] font-black px-1.5 py-0.5 rounded">{{ $creditosCancelados->count() }}</span>
                </span>
                <span x-text="abierto ? '▲ Ocultar' : '▼ Mostrar'"></span>
            </button>

            <div x-show="abierto" x-cloak class="mt-2 bg-white rounded-md border border-slate-300 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse font-sans text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-slate-300">
                            <th class="py-1.5 px-3 border-r border-slate-200">Identificador</th>
                            <th class="py-1.5 px-3 border-r border-slate-200 text-center">Fecha Otorgamiento</th>
                            <th class="py-1.5 px-3 border-r border-slate-200 text-center">Fecha Cancelación</th>
                            <th class="py-1.5 px-3 border-r border-slate-200 text-right">Importe Base</th>
                            <th class="py-1.5 px-3 border-r border-slate-200 text-right">Total Pagado</th>
                            <th class="py-1.5 px-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($creditosCancelados as $cred)
                            <tr class="hover:bg-slate-50">
                                <td class="py-1.5 px-3 font-mono font-bold text-slate-700 border-r border-slate-200/60">
                                    {{ $cred->full_identificador }}
                                </td>
                                <td class="py-1.5 px-3 text-center font-mono text-[11px] text-slate-600 border-r border-slate-200/60">
                                    {{ $cred->fecha_credito ? \Carbon\Carbon::parse($cred->fecha_credito)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-1.5 px-3 text-center font-mono text-[11px] text-emerald-700 font-bold border-r border-slate-200/60">
                                    {{ $cred->fecha_cancelacion ? \Carbon\Carbon::parse($cred->fecha_cancelacion)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-700 border-r border-slate-200/60 tabular-nums">
                                    $ {{ number_format($cred->importe_original, 2, ',', '.') }}
                                </td>
                                <td class="py-1.5 px-3 text-right font-mono text-slate-900 font-bold border-r border-slate-200/60 tabular-nums">
                                    $ {{ number_format($cred->cuotas->sum('entrego'), 2, ',', '.') }}
                                </td>
                                <td class="py-1 px-3 text-center">
                                    <a href="{{ route('creditos.show', $cred) }}" class="inline-flex items-center bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase transition">
                                        Ver →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>