@php
    $sucursalIdActual = data_get(config('app.sucursal_activa'), 'id') ?? Auth::user()->id_sucursal ?? 1;
    $puntosVentaTerminal = \App\Models\PuntoVenta::where('id_sucursal', $sucursalIdActual)
        ->where('activo', true)
        ->orderBy('numero')
        ->get()
        ->map(fn($p) => [
            'id'                => $p->id,
            'numero'            => $p->numero,
            'numero_formateado' => $p->numero_formateado,
            'nombre'            => $p->nombre,
            'tipo_emision'      => $p->tipo_emision,
            'hasar_ip'          => $p->configuracion['hasar_ip'] ?? null,
            'hasar_puerto'      => $p->configuracion['hasar_puerto'] ?? 2000,
            'puerto_local'      => $p->configuracion['puerto_local'] ?? 8585,
            'ambiente'          => $p->configuracion['ambiente'] ?? 'produccion',
        ]);
@endphp

<div x-data="{
        pvs: {{ json_encode($puntosVentaTerminal) }},
        pvActualId: null,
        pvSeleccionado: null,
        tempId: null,
        modalAbierto: false,
        forzado: false,
        guardando: false,

        init() {
            let saved = localStorage.getItem('lamarina_terminal_pv_id');
            if (saved) {
                this.pvActualId = parseInt(saved);
                let found = this.pvs.find(p => p.id === this.pvActualId);
                if (found) {
                    this.pvSeleccionado = found;
                    this.tempId = found.id;
                    // Sincronizar en segundo plano con la sesión del backend
                    this.sincronizarSesion(found.id);
                } else {
                    // El PV guardado no pertenece a esta sucursal activa o fue desactivado
                    this.forzarSeleccion();
                }
            } else {
                // Primera vez que entra en este navegador
                this.forzarSeleccion();
            }
        },

        forzarSeleccion() {
            if (this.pvs.length > 0) {
                this.tempId = this.pvs[0].id;
                this.modalAbierto = true;
                this.forzado = true;
            }
        },

        abrirCambio() {
            this.tempId = this.pvActualId || (this.pvs.length > 0 ? this.pvs[0].id : null);
            this.forzado = false;
            this.modalAbierto = true;
        },

        async confirmarSeleccion() {
            if (!this.tempId) return;
            let found = this.pvs.find(p => p.id === parseInt(this.tempId));
            if (!found) return;

            this.guardando = true;
            try {
                await this.sincronizarSesion(found.id);
                this.pvActualId = found.id;
                this.pvSeleccionado = found;
                localStorage.setItem('lamarina_terminal_pv_id', found.id);

                window.dispatchEvent(new CustomEvent('terminal-pv-cambiado', {
                    detail: found
                }));

                this.modalAbierto = false;
                this.forzado = false;
            } catch (e) {
                console.error('Error guardando terminal:', e);
                alert('No se pudo guardar el punto de venta en el servidor.');
            } finally {
                this.guardando = false;
            }
        },

        async sincronizarSesion(id) {
            try {
                await fetch('{{ route('sistema.puntos-venta.seleccionar-terminal') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ id_punto_venta: id })
                });
            } catch (e) {
                // Silencioso si falla sincronización secundaria
            }
        }
    }" 
    class="flex items-center">

    <!-- BOTÓN / BADGE EN EL HEADER -->
    <div class="mr-4 border-r border-blue-700 dark:border-slate-800 pr-4">
        <p class="text-[10px] uppercase text-amber-300 font-black leading-none mb-1 tracking-tighter flex items-center gap-1">
            <svg class="w-3 h-3 text-amber-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span>Puesto / Pto. Venta</span>
        </p>

        <template x-if="pvSeleccionado">
            <button type="button" @click="abrirCambio()"
                    class="bg-blue-900/90 hover:bg-blue-900 dark:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-black border border-blue-500 dark:border-slate-600 rounded-lg py-1 px-2.5 flex items-center gap-1.5 focus:ring-2 focus:ring-amber-400 transition-all shadow-sm"
                    title="Haga clic para cambiar el Punto de Venta de este equipo">
                <span class="w-2 h-2 rounded-full" :class="pvSeleccionado.tipo_emision === 'fiscal_hasar' ? 'bg-amber-400' : 'bg-sky-400'"></span>
                <span class="font-mono tabular-nums" x-text="'P.V. ' + pvSeleccionado.numero_formateado"></span>
                <span class="text-[9px] uppercase px-1 py-0.2 rounded font-bold"
                      :class="pvSeleccionado.tipo_emision === 'fiscal_hasar' ? 'bg-amber-500/30 text-amber-200 border border-amber-400/50' : 'bg-sky-500/30 text-sky-200 border border-sky-400/50'"
                      x-text="pvSeleccionado.tipo_emision === 'fiscal_hasar' ? 'Hasar 2G' : 'ARCA WSFE'"></span>
                <svg class="w-3 h-3 text-blue-300 dark:text-slate-400 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
            </button>
        </template>

        <template x-if="!pvSeleccionado">
            <button type="button" @click="abrirCambio()"
                    class="bg-rose-900/90 hover:bg-rose-800 text-white text-xs font-bold border border-rose-500 rounded-lg py-1 px-2.5 flex items-center gap-1.5 focus:ring-2 focus:ring-rose-400 transition-all shadow-sm animate-pulse">
                <span>⚠️ Seleccionar Punto Venta</span>
            </button>
        </template>
    </div>

    <!-- MODAL DE SELECCIÓN Y CONFIGURACIÓN DE TERMINAL -->
    <div x-show="modalAbierto" x-cloak
         class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm"
         @keydown.escape.window="if (!forzado) modalAbierto = false">

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full overflow-hidden"
             @click.away="if (!forzado) modalAbierto = false">

            <!-- Cabecera del Modal -->
            <div class="bg-slate-900 dark:bg-slate-950 text-white px-5 py-3.5 flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center space-x-2.5">
                    <div class="p-2 bg-blue-600 rounded-lg text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider">Punto de Venta de este Puesto</h2>
                        <p class="text-[11px] text-slate-400">Configuración local del navegador / terminal</p>
                    </div>
                </div>

                <template x-if="!forzado">
                    <button type="button" @click="modalAbierto = false" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </template>
            </div>

            <!-- Cuerpo del Modal -->
            <div class="p-5 space-y-4">
                <div class="bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 rounded-xl p-3 text-xs text-blue-900 dark:text-blue-200 leading-snug">
                    <p class="font-bold mb-0.5">💻 Identificación de Estación de Trabajo:</p>
                    <p>Esta configuración queda guardada en este equipo. Todas las facturas y cobros emitidos desde este navegador se registrarán con el Punto de Venta seleccionado.</p>
                </div>

                <template x-if="pvs.length === 0">
                    <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-700 text-amber-900 dark:text-amber-200 text-xs text-center space-y-2">
                        <p class="font-bold">No hay puntos de venta habilitados en esta sucursal.</p>
                        <p class="text-[11px]">Un administrador debe dar de alta un punto de venta en el panel de Sistema.</p>
                        <a href="{{ route('sistema.puntos-venta.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-1.5 px-3 rounded-lg uppercase tracking-wider">
                            + Crear Punto de Venta
                        </a>
                    </div>
                </template>

                <template x-if="pvs.length > 0">
                    <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                        <template x-for="p in pvs" :key="p.id">
                            <label class="flex items-start p-3 rounded-xl border transition-all cursor-pointer select-none"
                                   :class="tempId === p.id 
                                       ? 'border-blue-600 bg-blue-50/70 dark:bg-blue-950/40 ring-2 ring-blue-500/20' 
                                       : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60'">
                                <input type="radio" name="temp_pv" :value="p.id" x-model.number="tempId"
                                       class="mt-1 text-blue-600 focus:ring-blue-500 h-4 w-4 border-slate-300">
                                
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-700 tabular-nums"
                                                  x-text="'P.V. ' + p.numero_formateado"></span>
                                            <span class="font-bold text-xs text-slate-900 dark:text-white" x-text="p.nombre"></span>
                                        </div>

                                        <template x-if="p.tipo_emision === 'fiscal_hasar'">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800 border border-amber-300 dark:bg-amber-950/60 dark:text-amber-300">
                                                Fiscal Hasar 2G
                                            </span>
                                        </template>
                                        <template x-if="p.tipo_emision === 'arca_wsfe'">
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-sky-100 text-sky-800 border border-sky-300 dark:bg-sky-950/60 dark:text-sky-300">
                                                Digital ARCA
                                            </span>
                                        </template>
                                    </div>

                                    <template x-if="p.tipo_emision === 'fiscal_hasar'">
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-mono">
                                            <span>Hardware Local: </span>
                                            <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="p.hasar_ip + ':' + p.hasar_puerto"></span>
                                            <span> | Puente PC: :</span><span x-text="p.puerto_local"></span>
                                        </p>
                                    </template>
                                    <template x-if="p.tipo_emision === 'arca_wsfe'">
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                            <span>Web Service Oficial | Ambiente: </span>
                                            <span class="font-semibold uppercase tracking-wider text-[10px]" x-text="p.ambiente"></span>
                                        </p>
                                    </template>
                                </div>
                            </label>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Pie del Modal -->
            <div class="bg-slate-50 dark:bg-slate-800/80 px-5 py-3.5 flex items-center justify-end space-x-2.5 border-t border-slate-200 dark:border-slate-700">
                <template x-if="!forzado">
                    <button type="button" @click="modalAbierto = false"
                            class="bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 hover:bg-slate-50 font-bold text-xs py-2 px-3.5 rounded-lg uppercase tracking-wider transition-all shadow-sm">
                        Cancelar
                    </button>
                </template>

                <button type="button" @click="confirmarSeleccion()" :disabled="!tempId || guardando || pvs.length === 0"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2 px-5 rounded-lg uppercase tracking-wider transition-all shadow-sm flex items-center space-x-1.5 disabled:opacity-50">
                    <span x-text="guardando ? 'Guardando...' : 'Asignar a este Puesto'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

