<x-app-layout>
    <div class="max-w-7xl mx-auto py-3">
        <!-- Header Compacto -->
        <div class="flex items-center justify-between py-2 border-b border-slate-200 dark:border-slate-800 mb-3">
            <div class="flex items-center space-x-3">
                <a href="{{ url()->previous() }}" class="bg-white dark:bg-slate-800 p-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center space-x-2">
                        <span>Emitir Recibo de Cobranza</span>
                        <span class="text-xs font-normal text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                            Ingreso a Cuenta Corriente / Cancelación de Deuda
                        </span>
                    </h1>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <a href="{{ route('recibos.index') }}" class="bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs py-1.5 px-3 rounded-md transition flex items-center space-x-1">
                    <span>← Listado Recibos</span>
                </a>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-4 bg-rose-50 dark:bg-rose-950/60 border-l-4 border-rose-500 p-3 rounded text-rose-800 dark:text-rose-200 text-xs font-bold shadow-xs">
                {{ session('error') }}
            </div>
        @endif

        @if(!$cliente)
            <!-- Panel de Búsqueda y Selección de Cliente si no fue preseleccionado -->
            <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 mb-6">
                <div class="max-w-xl mx-auto text-center space-y-4">
                    <div class="w-12 h-12 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-300 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
                        👤
                    </div>
                    <div>
                        <h2 class="text-lg font-black text-slate-800 dark:text-white uppercase">Seleccione el Cliente para Cobrar</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Busque por DNI, Nro. de Cuenta o Nombre y Apellido para visualizar sus cuotas adeudadas.</p>
                    </div>

                    <form action="{{ route('recibos.create') }}" method="GET" class="flex gap-2">
                        <div class="relative flex-grow">
                            <input type="text" name="cliente_search" id="cliente_search_input"
                                   placeholder="Ingrese DNI o Apellido del cliente..." 
                                   class="w-full text-xs py-2 px-3 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                                   autocomplete="off">
                            <div id="cliente_search_results" class="hidden absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl z-50 max-h-60 overflow-y-auto text-left text-xs divide-y divide-slate-100 dark:divide-slate-800"></div>
                        </div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2 rounded-lg transition shadow-sm uppercase">
                            Buscar
                        </button>
                    </form>

                    @php
                        $clientesConDeuda = \App\Models\Cliente::where('saldo', '>', 0)->orderBy('nombre')->limit(8)->get();
                    @endphp
                    @if($clientesConDeuda->isNotEmpty())
                        <div class="pt-4 border-t border-slate-100 dark:border-slate-700/60 text-left">
                            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase block mb-2">Clientes con saldo pendiente reciente:</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($clientesConDeuda as $c)
                                    <a href="{{ route('recibos.create', ['cliente_id' => $c->id]) }}" 
                                       class="inline-flex items-center gap-1.5 bg-slate-50 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-slate-700 hover:border-blue-300 dark:hover:border-slate-600 border border-slate-200 dark:border-slate-700 px-2.5 py-1 rounded text-xs font-medium text-slate-700 dark:text-slate-200 transition">
                                        <span class="font-bold">{{ $c->nombre }}</span>
                                        <span class="font-mono text-[10px] text-slate-500 dark:text-slate-400">(DNI: {{ $c->dni }})</span>
                                        <span class="font-mono font-bold text-rose-700 dark:text-rose-400 text-[11px]">$ {{ number_format($c->saldo, 2, ',', '.') }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <script>
                const searchInput = document.getElementById('cliente_search_input');
                const resultsBox = document.getElementById('cliente_search_results');
                let debounceTimer;

                searchInput?.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const q = this.value.trim();
                    if (q.length < 2) {
                        resultsBox.classList.add('hidden');
                        resultsBox.innerHTML = '';
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`{{ route('clientes.buscar') }}?q=${encodeURIComponent(q)}`)
                            .then(res => res.json())
                            .then(data => {
                                resultsBox.innerHTML = '';
                                if (!data || data.length === 0) {
                                    resultsBox.innerHTML = '<div class="p-3 text-slate-400 italic text-center">No se encontraron clientes coincidentes.</div>';
                                    resultsBox.classList.remove('hidden');
                                    return;
                                }

                                data.forEach(cli => {
                                    const a = document.createElement('a');
                                    a.href = `{{ route('recibos.create') }}?cliente_id=${cli.id}`;
                                    a.className = 'block p-2.5 hover:bg-blue-50 dark:hover:bg-slate-800 transition flex items-center justify-between text-slate-800 dark:text-slate-100';
                                    a.innerHTML = `
                                        <div>
                                            <span class="font-bold uppercase block">${cli.nombre}</span>
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">DNI: ${cli.dni} ${cli.nro_cuenta ? '| Cta: ' + cli.nro_cuenta : ''}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Saldo</span>
                                            <span class="font-mono font-bold text-xs ${parseFloat(cli.saldo) > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-emerald-700 dark:text-emerald-400'}">
                                                $ ${parseFloat(cli.saldo || 0).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                            </span>
                                        </div>
                                    `;
                                    resultsBox.appendChild(a);
                                });
                                resultsBox.classList.remove('hidden');
                            })
                            .catch(() => {
                                resultsBox.classList.add('hidden');
                            });
                    }, 250);
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput?.contains(e.target) && !resultsBox?.contains(e.target)) {
                        resultsBox?.classList.add('hidden');
                    }
                });
            </script>
        @else
            <!-- Formulario de Emisión con Cliente Seleccionado -->
            @php
                $sucursalDefecto = $sucursalActiva ?? $cliente->sucursal ?? $sucursales->first();
                $saldoTotalDeudor = $cuotasPendientes->sum('saldo_actual');
                $saldoFavorActual = (float)($cliente->saldo_a_favor ?? 0);
            @endphp

            <div x-data="{
                items: [
                    @foreach($cuotasPendientes as $cuota)
                    {
                        cuota_id: {{ $cuota->id }},
                        credito_id: {{ $cuota->id_credito }},
                        identificador: '{{ $cuota->credito->full_identificador ?? ('CR-' . $cuota->id_credito) }}',
                        num_cuota: {{ $cuota->num_cuota }},
                        vencimiento: '{{ $cuota->fecha_vencimiento ? \Carbon\Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y') : 'Sin fecha' }}',
                        is_vencida: {{ ($cuota->fecha_vencimiento && \Carbon\Carbon::parse($cuota->fecha_vencimiento)->isPast()) ? 'true' : 'false' }},
                        saldo_actual: {{ (float)$cuota->saldo_actual }},
                        seleccionado: {{ (empty($cuotasSeleccionadasIds) || in_array($cuota->id, $cuotasSeleccionadasIds)) ? 'true' : 'false' }},
                        monto: {{ (float)$cuota->saldo_actual }}
                    },
                    @endforeach
                ],
                medio_pago: '{{ $mediosPago->firstWhere("codigo", "efectivo")?->nombre ?? $mediosPago->first()?->nombre ?? "Efectivo" }}',
                montoEntrega: '',
                montoAFavor: 0,
                aplicarExcedenteACuotas: true,

                get totalCuotas() {
                    return this.items
                        .filter(i => i.seleccionado)
                        .reduce((acc, i) => acc + (parseFloat(i.monto) || 0), 0);
                },

                get totalRecibo() {
                    return Math.round((this.totalCuotas + (parseFloat(this.montoAFavor) || 0)) * 100) / 100;
                },

                get nuevoSaldoCliente() {
                    const inicial = {{ (float)$saldoTotalDeudor }};
                    return Math.max(0, Math.round((inicial - this.totalCuotas) * 100) / 100);
                },

                get nuevoSaldoAFavorCliente() {
                    const inicialFavor = {{ (float)$saldoFavorActual }};
                    return Math.round((inicialFavor + (parseFloat(this.montoAFavor) || 0)) * 100) / 100;
                },

                seleccionarTodos(estado) {
                    this.items.forEach(i => {
                        i.seleccionado = estado;
                        if (estado && (parseFloat(i.monto) <= 0 || isNaN(parseFloat(i.monto)))) {
                            i.monto = i.saldo_actual;
                        }
                    });
                },

                distribuirEntrega() {
                    let disponible = parseFloat(this.montoEntrega) || 0;
                    if (disponible <= 0) return;

                    // Si hay cuotas seleccionadas, tomamos esas prioritarias. Si no hay ninguna marcada, tomamos todas.
                    let seleccionadas = this.items.filter(i => i.seleccionado);
                    if (seleccionadas.length === 0) {
                        seleccionadas = this.items;
                    }

                    let restante = disponible;

                    // Deseleccionar las que no fueron elegidas inicialmente
                    this.items.forEach(i => {
                        if (!seleccionadas.includes(i)) {
                            i.seleccionado = false;
                            i.monto = 0;
                        }
                    });

                    // Paso 1: Imputar a las cuotas prioritarias
                    seleccionadas.forEach(i => {
                        if (restante > 0) {
                            i.seleccionado = true;
                            if (restante >= i.saldo_actual) {
                                i.monto = i.saldo_actual;
                                restante = Math.round((restante - i.saldo_actual) * 100) / 100;
                            } else {
                                i.monto = Math.round(restante * 100) / 100;
                            }
                        } else {
                            i.seleccionado = false;
                            i.monto = 0;
                        }
                    });

                    // Paso 2: Si sobrepasa el valor de la cuota seleccionada y se opta por cancelar otras cuotas pendientes
                    if (restante > 0 && this.aplicarExcedenteACuotas) {
                        let cuotasRestantes = this.items.filter(i => !i.seleccionado || i.monto < i.saldo_actual);
                        cuotasRestantes.forEach(i => {
                            if (restante > 0) {
                                i.seleccionado = true;
                                let pendiente = Math.round((i.saldo_actual - (parseFloat(i.monto) || 0)) * 100) / 100;
                                if (pendiente > 0) {
                                    if (restante >= pendiente) {
                                        i.monto = i.saldo_actual;
                                        restante = Math.round((restante - pendiente) * 100) / 100;
                                    } else {
                                        i.monto = Math.round(((parseFloat(i.monto) || 0) + restante) * 100) / 100;
                                        restante = 0;
                                    }
                                }
                            }
                        });
                    }

                    // Paso 3: Cualquier excedente restante queda como Saldo a Favor del cliente
                    this.montoAFavor = Math.round(restante * 100) / 100;
                }
            }">
                <!-- Tarjeta Datos Cliente + KPIs Financieros -->
                <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 mb-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-blue-900 dark:bg-blue-800 text-white rounded-xl flex items-center justify-center font-black text-xl shadow-xs">
                                {{ strtoupper(substr($cliente->nombre, 0, 1)) }}
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h2 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-tight">{{ $cliente->nombre }}</h2>
                                    <span class="bg-blue-100 dark:bg-blue-950/80 text-blue-900 dark:text-blue-300 font-mono font-bold text-[10px] px-2 py-0.5 rounded border border-blue-200 dark:border-blue-800">
                                        DNI: {{ $cliente->dni }}
                                    </span>
                                    @if($cliente->nro_cuenta)
                                        <span class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono font-bold text-[10px] px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                                            Cta: {{ $cliente->nro_cuenta }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                    Sucursal: <strong class="text-slate-700 dark:text-slate-200">{{ $cliente->sucursal?->nombre ?? 'Sin sucursal' }}</strong> 
                                    | Domicilio: {{ $cliente->domicilio ?? 'S/D' }} ({{ $cliente->localidad?->nombre ?? 'S/L' }})
                                </p>
                            </div>
                        </div>

                        <!-- Indicadores Financieros -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <div class="bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 px-3 py-1.5 rounded-lg text-right">
                                <span class="text-[9px] font-bold text-rose-700 dark:text-rose-400 uppercase block">Deuda Exigible</span>
                                <span class="font-mono font-black text-rose-700 dark:text-rose-400 text-sm tabular-nums">
                                    $ {{ number_format($saldoTotalDeudor, 2, ',', '.') }}
                                </span>
                            </div>

                            <div class="bg-emerald-50/70 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900 px-3 py-1.5 rounded-lg text-right">
                                <span class="text-[9px] font-bold text-emerald-700 dark:text-emerald-400 uppercase block">Saldo a Favor Actual</span>
                                <span class="font-mono font-black text-emerald-700 dark:text-emerald-400 text-sm tabular-nums">
                                    $ {{ number_format($saldoFavorActual, 2, ',', '.') }}
                                </span>
                            </div>

                            <div class="bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-900 px-3 py-1.5 rounded-lg text-right">
                                <span class="text-[9px] font-bold text-blue-800 dark:text-blue-300 uppercase block">Total a Cobrar</span>
                                <span class="font-mono font-black text-blue-900 dark:text-blue-200 text-base tabular-nums">
                                    $ <span x-text="Number(totalRecibo).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                </span>
                            </div>

                            <a href="{{ route('recibos.create') }}" class="text-[11px] text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 underline ml-1">
                                Cambiar
                            </a>
                        </div>
                    </div>
                </div>

                <form action="{{ route('recibos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
                        <!-- TABLA DE CUOTAS Y PANEL DE ENTREGA (2 COLUMNAS) -->
                        <div class="lg:col-span-2 space-y-3">
                            <!-- PANEL DE IMPUTACIÓN INTELIGENTE DE ENTREGA -->
                            <div class="bg-gradient-to-r from-blue-900 to-indigo-900 dark:from-slate-900 dark:to-indigo-950 text-white rounded-xl p-4 shadow-sm space-y-3 border border-blue-800 dark:border-slate-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg">💵</span>
                                        <div>
                                            <h3 class="text-xs font-black uppercase tracking-wider text-blue-100 dark:text-slate-200">
                                                Cobro por Entrega de Dinero
                                            </h3>
                                            <p class="text-[11px] text-blue-200 dark:text-slate-300">
                                                Ingrese el monto entregado ($X) para amortizar cuotas seleccionadas y procesar excedentes.
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-mono bg-blue-800/80 dark:bg-slate-800 text-blue-200 dark:text-slate-300 px-2 py-0.5 rounded border border-blue-700 dark:border-slate-600">
                                        Orden por Vencimiento
                                    </span>
                                </div>

                                <div class="bg-white/10 dark:bg-black/30 backdrop-blur-xs p-3 rounded-lg border border-white/15 dark:border-slate-700/60 space-y-2.5">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex-grow flex items-center space-x-2">
                                            <label class="text-xs font-bold text-blue-100 dark:text-slate-200 shrink-0">Monto Entregado:</label>
                                            <div class="relative flex-grow max-w-xs">
                                                <span class="absolute left-3 top-2 text-slate-400 font-mono text-sm font-bold">$</span>
                                                <input type="number" step="0.01" min="0" x-model="montoEntrega" 
                                                       @keydown.enter.prevent="distribuirEntrega()"
                                                       placeholder="Ej: 100000" 
                                                       class="w-full text-sm font-mono font-black py-1.5 pl-7 pr-3 rounded-lg border-0 focus:ring-2 focus:ring-emerald-400 bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-inner">
                                            </div>
                                        </div>

                                        <button type="button" @click="distribuirEntrega()" 
                                                class="bg-emerald-500 hover:bg-emerald-600 text-white font-black text-xs py-2 px-4 rounded-lg transition uppercase shadow-md flex items-center justify-center space-x-1.5 shrink-0">
                                            <span>⚡ Aplicar Entrega</span>
                                        </button>
                                    </div>

                                    <!-- Selector de Destino de Excedente -->
                                    <div class="pt-2 border-t border-white/10 dark:border-slate-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                        <label class="flex items-center space-x-2 cursor-pointer select-none">
                                            <input type="checkbox" x-model="aplicarExcedenteACuotas" 
                                                   class="rounded border-blue-400 text-blue-600 focus:ring-blue-400 h-4 w-4 bg-white/90 dark:bg-slate-800">
                                            <span class="text-blue-100 dark:text-slate-200 font-medium text-[11px]">
                                                Si sobra dinero, aplicar a siguientes cuotas pendientes por vencimiento más próximo
                                            </span>
                                        </label>

                                        <span class="text-[10px] text-blue-300 dark:text-slate-400 italic">
                                            (Cualquier remanente final quedará acreditado a Saldo a Favor)
                                        </span>
                                    </div>
                                </div>

                                <!-- Resumen Dinámico de Imputación de Entrega -->
                                <template x-if="parseFloat(montoEntrega) > 0">
                                    <div class="bg-black/20 dark:bg-slate-950/60 p-2.5 rounded-lg border border-white/10 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 text-xs font-mono">
                                        <div>
                                            <span class="text-blue-300 dark:text-slate-400 text-[10px] block">Abonado a Cuotas:</span>
                                            <span class="text-emerald-300 font-bold">$ <span x-text="Number(totalCuotas).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></span>
                                        </div>
                                        <div>
                                            <span class="text-blue-300 dark:text-slate-400 text-[10px] block">Sobrante a Saldo a Favor:</span>
                                            <span class="text-amber-300 font-bold">$ <span x-text="Number(montoAFavor).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></span>
                                        </div>
                                        <div>
                                            <span class="text-blue-300 dark:text-slate-400 text-[10px] block">Total Comprobante:</span>
                                            <span class="text-white font-black text-sm">$ <span x-text="Number(totalRecibo).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            @if($cuotasPendientes->isEmpty())
                                <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6 text-center">
                                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-950/80 text-emerald-700 dark:text-emerald-300 rounded-full flex items-center justify-center mx-auto text-xl font-bold mb-2">
                                        ✓
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-white uppercase">Sin cuotas exigibles pendientes</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">El cliente se encuentra al día. Puede emitir un recibo para ingresar dinero directamente a su Saldo a Favor.</p>
                                </div>
                            @else
                                <!-- Tabla de Cuotas -->
                                <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                                    <!-- Toolbar superior de la tabla -->
                                    <div class="bg-slate-50 dark:bg-slate-900/80 px-3 py-2 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-xs font-bold text-slate-800 dark:text-white uppercase">Cuotas Exigibles</span>
                                            <span class="text-[10px] font-black bg-blue-100 dark:bg-blue-950/80 text-blue-900 dark:text-blue-300 px-1.5 py-0.5 rounded border border-blue-200 dark:border-blue-800">
                                                {{ $cuotasPendientes->count() }} cuotas (ordenadas por vencimiento)
                                            </span>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <button type="button" @click="seleccionarTodos(true)" class="bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 px-2 py-1 rounded text-[10px] font-bold uppercase transition">
                                                ✓ Marcar Todas
                                            </button>
                                            <button type="button" @click="seleccionarTodos(false)" class="bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 px-2 py-1 rounded text-[10px] font-bold uppercase transition">
                                                ✕ Desmarcar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Tabla de Cuotas -->
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-left border-collapse text-xs">
                                            <thead>
                                                <tr class="bg-slate-100 dark:bg-slate-900/80 text-slate-700 dark:text-slate-300 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200 dark:border-slate-700">
                                                    <th class="py-2 px-2 text-center w-10">Cobrar</th>
                                                    <th class="py-2 px-3">Crédito</th>
                                                    <th class="py-2 px-2 text-center">Cuota</th>
                                                    <th class="py-2 px-3 text-center">Vencimiento</th>
                                                    <th class="py-2 px-3 text-right">Saldo Actual</th>
                                                    <th class="py-2 px-3 text-right w-44">Monto a Cobrar ($)</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-200 dark:divide-slate-700/60">
                                                <template x-for="(item, index) in items" :key="item.cuota_id">
                                                    <tr class="transition-colors" :class="item.seleccionado ? 'bg-blue-50/40 dark:bg-blue-950/30 hover:bg-blue-50/70 dark:hover:bg-blue-900/40' : 'bg-white dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-700/40 opacity-60'">
                                                        <td class="py-2 px-2 text-center">
                                                            <input type="checkbox" x-model="item.seleccionado" 
                                                                   @change="if (item.seleccionado && (!item.monto || item.monto <= 0)) item.monto = item.saldo_actual;"
                                                                   class="rounded border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-blue-600 focus:ring-blue-500 h-4 w-4 cursor-pointer">
                                                        </td>
                                                        <td class="py-2 px-3">
                                                            <span class="font-mono font-bold text-blue-900 dark:text-blue-400" x-text="item.identificador"></span>
                                                        </td>
                                                        <td class="py-2 px-2 text-center">
                                                            <span class="font-mono font-bold text-slate-700 dark:text-slate-300 text-[11px]" x-text="'#' + item.num_cuota"></span>
                                                        </td>
                                                        <td class="py-2 px-3 text-center font-mono text-[11px]">
                                                            <span x-text="item.vencimiento" :class="item.is_vencida ? 'text-rose-700 dark:text-rose-400 font-bold' : 'text-slate-600 dark:text-slate-300'"></span>
                                                            <template x-if="item.is_vencida">
                                                                <span class="ml-1 text-[9px] bg-rose-100 dark:bg-rose-950/80 text-rose-800 dark:text-rose-300 px-1 py-0.2 rounded font-bold uppercase border border-rose-200 dark:border-rose-900">Vencida</span>
                                                            </template>
                                                        </td>
                                                        <td class="py-2 px-3 text-right font-mono font-bold text-rose-700 dark:text-rose-400 tabular-nums">
                                                            $ <span x-text="Number(item.saldo_actual).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                                        </td>
                                                        <td class="py-2 px-3 text-right">
                                                            <template x-if="item.seleccionado">
                                                                <div class="space-y-1">
                                                                    <input type="hidden" :name="'items[' + index + '][cuota_id]'" :value="item.cuota_id">
                                                                    <div class="relative">
                                                                        <span class="absolute left-2 top-1 text-slate-400 font-mono text-xs">$</span>
                                                                        <input type="number" step="0.01" min="0.01" :max="item.saldo_actual"
                                                                               :name="'items[' + index + '][monto]'" 
                                                                               x-model="item.monto" 
                                                                               class="w-full text-right font-mono font-black text-xs py-1 pl-5 pr-2 rounded border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs">
                                                                    </div>
                                                                    <div class="text-[9px] font-mono">
                                                                        <template x-if="parseFloat(item.monto) < item.saldo_actual">
                                                                            <span class="text-amber-700 dark:text-amber-300 font-bold bg-amber-50 dark:bg-amber-950/60 px-1 py-0.2 rounded border border-amber-200 dark:border-amber-800">
                                                                                Pago Parcial (Resta $ <span x-text="Number(item.saldo_actual - item.monto).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>)
                                                                            </span>
                                                                        </template>
                                                                        <template x-if="parseFloat(item.monto) >= item.saldo_actual">
                                                                            <span class="text-emerald-700 dark:text-emerald-300 font-bold bg-emerald-50 dark:bg-emerald-950/60 px-1 py-0.2 rounded border border-emerald-200 dark:border-emerald-800">
                                                                                ✓ Cancela 100%
                                                                            </span>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                            <template x-if="!item.seleccionado">
                                                                <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase">— Excluido —</span>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            <!-- SECCIÓN DE SALDO A FAVOR ADICIONAL / EXCEDENTE -->
                            <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm">💎</span>
                                        <h4 class="text-xs font-black uppercase text-slate-800 dark:text-white tracking-tight">
                                            Acreditación a Saldo a Favor del Cliente
                                        </h4>
                                    </div>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        Dinero ingresado que queda depositado en su cuenta corriente para saldar futuras cuotas o compras.
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2 shrink-0">
                                    <span class="text-slate-400 dark:text-slate-500 font-mono text-xs font-bold">$</span>
                                    <input type="number" step="0.01" min="0" name="monto_a_favor" x-model="montoAFavor"
                                           class="w-36 text-right font-mono font-black text-xs py-1.5 px-2.5 rounded-lg border-slate-300 dark:border-slate-600 focus:border-emerald-500 focus:ring-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-300 shadow-xs">
                                </div>
                            </div>
                        </div>

                        <!-- PARÁMETROS DEL RECIBO Y CONFIRMACIÓN (1 COLUMNA) -->
                        <div class="space-y-4">
                            <div class="bg-white dark:bg-slate-800/90 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-4 space-y-4">
                                <h3 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider border-b border-slate-100 dark:border-slate-700/60 pb-2">
                                    Parámetros del Comprobante
                                </h3>

                                <!-- Sucursal de Cobro -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Sucursal Emisora</label>
                                    <select name="sucursal_id" required class="w-full text-xs font-bold rounded-lg border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-2 px-2.5 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-xs">
                                        @foreach($sucursales as $s)
                                            <option value="{{ $s->id }}" {{ $s->id == $sucursalDefecto->id ? 'selected' : '' }}>
                                                {{ $s->nombre }} ({{ $s->codigo_sucursal }}) - Próx. #{{ ($s->ultimo_recibo ?? 0) + 1 }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Fecha de Cobro -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Fecha de Operación</label>
                                    <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required
                                           class="w-full text-xs font-bold rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2.5 shadow-xs">
                                </div>

                                <!-- Medio de Pago -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Medio de Pago</label>
                                    <select name="medio_pago" x-model="medio_pago" required class="w-full text-xs font-bold rounded-lg border-slate-300 dark:border-slate-600 focus:border-blue-500 focus:ring-blue-500 py-2 px-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 shadow-xs">
                                        @foreach($mediosPago as $mp)
                                            <option value="{{ $mp->nombre }}">
                                                {{ $mp->icono ?: '💳' }} {{ $mp->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Observaciones -->
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Observaciones / Leyenda (Opcional)</label>
                                    <textarea name="observaciones" rows="2" placeholder="Detalle bancario, nro de cupón o transacción..."
                                              class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-blue-500 py-1.5 px-2.5 shadow-xs"></textarea>
                                </div>

                                <!-- Resumen Económico -->
                                <div class="bg-slate-50 dark:bg-slate-900/60 rounded-lg p-3 border border-slate-200 dark:border-slate-700 space-y-2">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-500 dark:text-slate-400 font-medium">Deuda Exigible Inicial:</span>
                                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">$ {{ number_format($saldoTotalDeudor, 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-500 dark:text-slate-400 font-medium">Cobro en Cuotas:</span>
                                        <span class="font-mono font-bold text-blue-900 dark:text-blue-300">
                                            $ <span x-text="Number(totalCuotas).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                        </span>
                                    </div>
                                    <template x-if="parseFloat(montoAFavor) > 0">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-emerald-700 dark:text-emerald-400 font-bold">Acreditado Saldo a Favor:</span>
                                            <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                                $ <span x-text="Number(montoAFavor).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                            </span>
                                        </div>
                                    </template>
                                    <div class="flex justify-between items-center text-xs border-t border-b border-slate-200 dark:border-slate-700 py-2">
                                        <span class="text-slate-900 dark:text-white font-black uppercase text-[11px]">Total del Recibo:</span>
                                        <span class="font-mono font-black text-emerald-700 dark:text-emerald-400 text-base">
                                            $ <span x-text="Number(totalRecibo).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs pt-1">
                                        <span class="text-slate-600 dark:text-slate-400 font-medium">Deuda Restante:</span>
                                        <span class="font-mono font-bold text-rose-700 dark:text-rose-400">
                                            $ <span x-text="Number(nuevoSaldoCliente).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-slate-600 dark:text-slate-400 font-medium">Saldo a Favor Resultante:</span>
                                        <span class="font-mono font-bold text-emerald-700 dark:text-emerald-400">
                                            $ <span x-text="Number(nuevoSaldoAFavorCliente).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Botón Principal de Emisión -->
                                <button type="submit" 
                                        :disabled="totalRecibo <= 0"
                                        :class="totalRecibo <= 0 ? 'bg-slate-300 dark:bg-slate-700 cursor-not-allowed text-slate-500 dark:text-slate-400' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md hover:shadow-lg'"
                                        class="w-full font-black text-xs uppercase py-3 px-4 rounded-xl transition-all flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span>Confirmar y Emitir Recibo</span>
                                </button>

                                <p class="text-[10px] text-slate-400 dark:text-slate-500 text-center">
                                    Se emitirá el comprobante oficial, actualizando cuotas exigibles y saldo a favor en cuenta corriente.
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
