<x-app-layout>
    <!-- Script del Cierre Z y Arqueo de Caja -->
    <script>
        window.controladoresHasarEstado = @json($resumenFiscal['hasar_controladores'] ?? []);

        function actualizarInputResumenFiscal() {
            const input = document.getElementById('input_resumen_fiscal');
            if (input) {
                input.value = JSON.stringify({
                    arca: @json($resumenFiscal['arca'] ?? []),
                    hasar_controladores: window.controladoresHasarEstado,
                    hasar_total_general: window.controladoresHasarEstado.reduce((acc, c) => acc + Number(c.total || 0), 0),
                    hasar_cant_total: window.controladoresHasarEstado.reduce((acc, c) => acc + Number(c.cant_tickets || 0), 0)
                });
            }
        }

        window.ejecutarCierreZ = async function(key, hasarIp, hasarPuerto, puertoLocal, modelo, ctrlId) {
            console.group(`⚡ [CIERRE Z HASAR] - Controlador ${modelo} (${key})`);
            console.log('1. [INICIO] Ejecutando Cierre Z:', { key, hasarIp, hasarPuerto, puertoLocal, modelo });

            const btn = document.getElementById(ctrlId + '_btn');
            const btnText = document.getElementById(ctrlId + '_btn_text');
            const badge = document.getElementById(ctrlId + '_badge');
            const icon = document.getElementById(ctrlId + '_icon');
            const card = document.getElementById(ctrlId);
            const errP = document.getElementById(ctrlId + '_error');

            if (btn) btn.disabled = true;
            if (btnText) btnText.textContent = '⏳ Emitiendo Z...';
            if (errP) { errP.textContent = ''; errP.classList.add('hidden'); }

            let payloadZ = {
                accion: "cierre_z",
                hasar_ip: hasarIp,
                hasar_puerto: hasarPuerto,
                modelo: modelo,
                caja_id: {{ $caja->id }},
                sucursal: {!! json_encode($caja->sucursal?->nombre ?? '') !!}
            };

            console.log('2. [PAYLOAD ENVIADO]:', payloadZ);

            let dataBridge = null;
            const endpointsATestear = [
                `http://127.0.0.1:${puertoLocal}/imprimir`,
                `https://127.0.0.1:8586/imprimir`,
                `http://127.0.0.1:${puertoLocal}/cierre-z`,
                `https://127.0.0.1:8586/cierre-z`,
                `http://localhost:${puertoLocal}/imprimir`
            ];
            for (let url of endpointsATestear) {
                try {
                    console.log(`3. [INTENTO FETCH PUENTE]: ${url}`);
                    let resBridge = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json; charset=utf-8' },
                        body: JSON.stringify(payloadZ)
                    });
                    if (resBridge && resBridge.ok) {
                        dataBridge = await resBridge.json();
                        console.log(`✓ [PUENTE RESPONDIÓ EN ${url}]:`, dataBridge);
                        break;
                    }
                } catch (err) {
                    console.warn(`Fetch a ${url} no disponible:`, err.message);
                }
            }

            try {
                console.log('4. [ENVIANDO A LARAVEL]:', '{{ route("cajas.cierre-z-hasar", $caja->id) }}');
                let resServidor = await fetch('{{ route("cajas.cierre-z-hasar", $caja->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        hasar_ip: hasarIp,
                        hasar_puerto: hasarPuerto,
                        puerto_local: puertoLocal,
                        modelo: modelo,
                        numero_z: dataBridge ? (dataBridge.numero_comprobante || dataBridge.numero_z || dataBridge.numero || dataBridge.nro_z) : null,
                        respuesta: dataBridge
                    })
                });

                let dataServidor = await resServidor.json();
                console.log('5. [RESPUESTA DE LARAVEL]:', dataServidor);

                if (!resServidor.ok || !dataServidor.success) {
                    throw new Error(dataServidor.message || 'No se pudo contactar el controlador fiscal Hasar.');
                }

                let nroZ = dataServidor.numero_z || (dataBridge ? dataBridge.numero_comprobante : 'Z-OK');
                
                // Actualizar UI
                if (badge) {
                    badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900 dark:bg-emerald-900 dark:text-emerald-200 flex items-center gap-1';
                    badge.textContent = `✓ Cierre Z Emitido (#${nroZ})`;
                }
                if (icon) {
                    icon.className = 'w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg shrink-0 shadow-sm bg-emerald-600 text-white';
                }
                if (card) {
                    card.className = 'p-3.5 rounded-xl border transition-all bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800';
                }
                if (btn) {
                    btn.className = 'px-3.5 py-2 rounded-xl text-xs font-black uppercase transition flex items-center gap-1.5 shadow-sm cursor-pointer bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-300';
                    btn.disabled = false;
                }
                if (btnText) {
                    btnText.textContent = '🔄 Re-emitir Cierre Z';
                }

                const banner = document.getElementById('banner-alerta-z');
                if (banner) banner.classList.add('hidden');

                // Actualizar estado persistido
                let idx = window.controladoresHasarEstado.findIndex(c => c.key === key);
                if (idx !== -1) {
                    window.controladoresHasarEstado[idx].cierre_z_emitido = true;
                    window.controladoresHasarEstado[idx].cierre_z_numero = nroZ;
                    window.controladoresHasarEstado[idx].cierre_z_fecha = new Date().toLocaleTimeString('es-AR');
                }
                actualizarInputResumenFiscal();

                console.log(`✅ [ÉXITO] Cierre Z registrado: #${nroZ}`);
                console.groupEnd();
                alert(`✓ Cierre Z emitido y registrado exitosamente.\n\nNúmero de Z: #${nroZ}\nControlador: ${modelo} (${hasarIp})`);

            } catch (e) {
                console.error('❌ [ERROR AL EMITIR CIERRE Z]:', e);
                console.groupEnd();

                let nroManual = prompt('No se pudo contactar automáticamente el controlador fiscal Hasar en el puerto ' + puertoLocal + '.\n\nSi emitió el Cierre Z manualmente desde la impresora física (o el simulador ya lo emitió), ingrese el Número de Z para registrarlo en el sistema (ej: 0010):');
                if (nroManual && nroManual.trim()) {
                    let nroZ = nroManual.trim();
                    await fetch('{{ route("cajas.cierre-z-hasar", $caja->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            hasar_ip: hasarIp,
                            hasar_puerto: hasarPuerto,
                            puerto_local: puertoLocal,
                            modelo: modelo,
                            numero_z: nroZ,
                            respuesta: { manual: true }
                        })
                    });

                    if (badge) {
                        badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900 dark:bg-emerald-900 dark:text-emerald-200 flex items-center gap-1';
                        badge.textContent = `✓ Cierre Z Emitido (#${nroZ})`;
                    }
                    if (icon) {
                        icon.className = 'w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg shrink-0 shadow-sm bg-emerald-600 text-white';
                    }
                    if (card) {
                        card.className = 'p-3.5 rounded-xl border transition-all bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800';
                    }
                    if (btn) {
                        btn.className = 'px-3.5 py-2 rounded-xl text-xs font-black uppercase transition flex items-center gap-1.5 shadow-sm cursor-pointer bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-300';
                        btn.disabled = false;
                    }
                    if (btnText) {
                        btnText.textContent = '🔄 Re-emitir Cierre Z';
                    }

                    const banner = document.getElementById('banner-alerta-z');
                    if (banner) banner.classList.add('hidden');

                    let idx = window.controladoresHasarEstado.findIndex(c => c.key === key);
                    if (idx !== -1) {
                        window.controladoresHasarEstado[idx].cierre_z_emitido = true;
                        window.controladoresHasarEstado[idx].cierre_z_numero = nroZ;
                        window.controladoresHasarEstado[idx].cierre_z_fecha = new Date().toLocaleTimeString('es-AR');
                    }
                    actualizarInputResumenFiscal();
                    alert(`✓ Cierre Z #${nroZ} registrado manualmente.`);
                } else {
                    if (errP) {
                        errP.textContent = 'No se pudo contactar el controlador fiscal. Verifique que el puente Hasar esté activo en localhost:' + puertoLocal;
                        errP.classList.remove('hidden');
                    }
                    if (btn) btn.disabled = false;
                    if (btnText) btnText.textContent = '⚡ Emitir Cierre Z';
                }
            }
        };

        window.consultarEstadoFiscal = async function(hasarIp, puertoLocal, ctrlId) {
            const endpoints = [
                `http://127.0.0.1:${puertoLocal}/test`,
                `https://127.0.0.1:8586/test`,
                `http://127.0.0.1:8585/test`
            ];
            let data = null;
            for (let u of endpoints) {
                try {
                    let r = await fetch(u, { method: 'GET', headers: { 'Accept': 'application/json' } });
                    if (r.ok) {
                        data = await r.json();
                        break;
                    }
                } catch(e) {}
            }
            if (!data || !data.exito) {
                alert('⚠️ No se pudo comunicar con el puente fiscal en localhost:' + puertoLocal + '.\nVerifique que "puente-hasar" esté ejecutándose.');
                return;
            }

            let xml = (data.respuestas_fiscal && data.respuestas_fiscal[0]) || '';
            let matchEstado = xml.match(/<EstadoInterno>(.*?)<\/EstadoInterno>/);
            let matchUltimo = xml.match(/<NumeroUltimoComprobante>(.*?)<\/NumeroUltimoComprobante>/);
            let matchEmitidos = xml.match(/<CantidadEmitidos>(.*?)<\/CantidadEmitidos>/);
            let matchCancelados = xml.match(/<CantidadCancelados>(.*?)<\/CantidadCancelados>/);

            let estado = matchEstado ? matchEstado[1] : 'Desconocido';
            let ultimo = matchUltimo ? matchUltimo[1] : '-';
            let emitidos = matchEmitidos ? matchEmitidos[1] : '0';
            let cancelados = matchCancelados ? matchCancelados[1] : '0';

            let msg = `🟢 [ESTADO OFICIAL DE LA REGISTRADORA HASAR 2G]\n\n`;
            msg += `• IP Fiscal: ${hasarIp}\n`;
            msg += `• Punto de Venta: #${data.punto_venta || 7}\n`;
            msg += `• Estado Interno: ${estado}\n`;
            msg += `• Último Comprobante emitido: #${ultimo}\n`;
            msg += `• Comprobantes emitidos en la jornada: ${emitidos}\n`;
            if (cancelados > 0) msg += `• Cancelados: ${cancelados}\n`;
            msg += `\n`;

            if (estado === 'EnJornadaFiscal') {
                msg += `✅ LA REGISTRADORA ESTÁ EN JORNADA FISCAL ABIERTA.\nEstá operativa y lista para facturar hoy normalmente.`;
            } else {
                msg += `ℹ️ Estado: ${estado}.\nSi ya se realizó el Cierre Z hoy, la registradora iniciará la nueva jornada automáticamente al emitir la primera factura.`;
            }

            alert(msg);
        };

        window.marcarZYaEmitido = async function(key, hasarIp, hasarPuerto, puertoLocal, modelo, ctrlId) {
            let nroZ = prompt(
                'Indique el Número de Cierre Z realizado previamente en la registradora (o deje "PREVIO" si no tiene el número a mano):',
                'PREVIO'
            );
            if (nroZ === null) return; // Canceló
            nroZ = nroZ.trim() || 'PREVIO';

            try {
                await fetch('{{ route("cajas.cierre-z-hasar", $caja->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        hasar_ip: hasarIp,
                        hasar_puerto: hasarPuerto,
                        puerto_local: puertoLocal,
                        modelo: modelo,
                        numero_z: nroZ,
                        respuesta: { previo_sistema_anterior: true, manual: true }
                    })
                });
            } catch (err) {
                console.warn('Registro Z previo:', err);
            }

            const badge = document.getElementById(ctrlId + '_badge');
            const icon = document.getElementById(ctrlId + '_icon');
            const card = document.getElementById(ctrlId);
            const btn = document.getElementById(ctrlId + '_btn');
            const btnText = document.getElementById(ctrlId + '_btn_text');

            if (badge) {
                badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900 dark:bg-emerald-900 dark:text-emerald-200 flex items-center gap-1';
                badge.textContent = `✓ Z Ya Emitido (#${nroZ})`;
            }
            if (icon) {
                icon.className = 'w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg shrink-0 shadow-sm bg-emerald-600 text-white';
            }
            if (card) {
                card.className = 'p-3.5 rounded-xl border transition-all bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800';
            }
            if (btnText) {
                btnText.textContent = '🔄 Re-emitir Cierre Z';
            }

            const banner = document.getElementById('banner-alerta-z');
            if (banner) banner.classList.add('hidden');

            let idx = window.controladoresHasarEstado.findIndex(c => c.key === key);
            if (idx !== -1) {
                window.controladoresHasarEstado[idx].cierre_z_emitido = true;
                window.controladoresHasarEstado[idx].cierre_z_numero = nroZ;
                window.controladoresHasarEstado[idx].cierre_z_fecha = new Date().toLocaleTimeString('es-AR');
            }
            actualizarInputResumenFiscal();
            alert(`✓ Cierre Z registrado como emitido previamente.\n\nYa puede confirmar el cierre de caja del sistema sin re-imprimir.`);
        };

        window.validarCierreForm = function(e) {
            console.log('⚡ [FORM SUBMIT] validarCierreForm invocado');
            let pend = (window.controladoresHasarEstado || []).filter(c => Number(c.cant_tickets || 0) > 0 && !c.cierre_z_emitido);
            if (pend.length > 0) {
                let nombres = pend.map(c => `• ${c.modelo} (${c.hasar_ip}:${c.hasar_puerto}) - ${c.cant_tickets} ticket(s)`).join('\n');
                let respuesta = confirm(
                    `Controladores con Cierre Z no registrado en esta sesión:\n${nombres}\n\n` +
                    `¿El Cierre Z ya fue emitido previamente (ej: desde el sistema anterior o por teclado)?\n\n` +
                    `• ACEPTAR: Confirmar cierre de caja considerando el Z como YA REALIZADO (sin mandar a imprimir).\n` +
                    `• CANCELAR: Volver a la pantalla para emitir el Z en la impresora física.`
                );
                if (respuesta) {
                    for (let c of pend) {
                        c.cierre_z_emitido = true;
                        c.cierre_z_numero = c.cierre_z_numero || 'PREVIO';
                    }
                    actualizarInputResumenFiscal();
                    return true;
                } else {
                    if (e) e.preventDefault();
                    return false;
                }
            } else {
                if (!confirm('¿Está seguro de cerrar definitivamente la caja? Se asentará el cierre de turno y el arqueo de efectivo.')) {
                    if (e) e.preventDefault();
                    return false;
                }
            }
            return true;
        };

        function cierreCajaApp() {
            return {
                arca: @json($resumenFiscal['arca'] ?? []),
                teoricoEfectivo: {{ (float) $totales['efectivo_esperado'] }},
                realContado: {{ max(0, (float) $totales['efectivo_esperado']) }},
                formatDinero(val) {
                    if (val === undefined || val === null || isNaN(val)) return '0,00';
                    return Number(val).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                diferencia() {
                    return Math.round((Number(this.realContado || 0) - this.teoricoEfectivo) * 100) / 100;
                },
                validarAntesDeCerrar(e) {
                    return window.validarCierreForm(e);
                }
            };
        }

        window.cierreCajaApp = cierreCajaApp;
        if (window.Alpine) {
            window.Alpine.data('cierreCajaApp', cierreCajaApp);
        } else {
            document.addEventListener('alpine:init', () => {
                Alpine.data('cierreCajaApp', cierreCajaApp);
            });
        }
    </script>

    <div class="max-w-4xl mx-auto py-5 px-3 space-y-5" x-data="cierreCajaApp()">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('cajas.index') }}" 
                   class="bg-white dark:bg-slate-800 p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 transition shadow-2xs">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <span>🔒 Arqueo & Cierre Z de Caja</span>
                        <span class="text-xs font-mono font-black px-2 py-0.5 rounded-full bg-slate-900 text-white dark:bg-white dark:text-slate-900">
                            #{{ $caja->id }}
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Sucursal: <strong>{{ $caja->sucursal?->nombre }}</strong> • Apertura: <strong>{{ $caja->fecha_apertura ? $caja->fecha_apertura->format('d/m/Y H:i') : '-' }} hs</strong> por <strong>{{ $caja->usuarioApertura?->name }}</strong>
                    </p>
                </div>
            </div>

            <a href="{{ route('ventas.create') }}" 
               class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs py-2 px-3 rounded-xl transition uppercase">
                Volver al POS
            </a>
        </div>

        @if(session('error'))
            <div class="p-3 bg-rose-50 dark:bg-rose-950/50 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('cajas.cerrar.store', $caja->id) }}" class="space-y-5"
              id="formCierreCaja"
              onsubmit="return window.validarCierreForm(event)">
            @csrf

            <!-- 1. RESUMEN POR MEDIOS DE PAGO (SIMILAR AL CIERRE Z DE REGISTRADORA) -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <span>📊</span>
                        <span>Resumen de Ventas por Medio de Pago (Cierre Z)</span>
                    </span>
                    <span class="text-xs font-bold text-slate-500">
                        {{ $totales['ventas_count'] }} comprobante(s) emitido(s)
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    <!-- Efectivo -->
                    <div class="p-3 rounded-xl border border-emerald-200 dark:border-emerald-900 bg-emerald-50/50 dark:bg-emerald-950/20">
                        <div class="flex items-center justify-between text-xs text-emerald-800 dark:text-emerald-300 font-bold">
                            <span>💵 Efectivo</span>
                            <span class="text-[10px] font-mono bg-emerald-100 dark:bg-emerald-900 px-1.5 py-0.5 rounded">
                                {{ $totales['efectivo_count'] }} op.
                            </span>
                        </div>
                        <div class="text-lg font-black font-mono text-emerald-900 dark:text-emerald-200 mt-1">
                            ${{ number_format($totales['efectivo_total'], 2, ',', '.') }}
                        </div>
                    </div>

                    <!-- Débito -->
                    <div class="p-3 rounded-xl border border-sky-200 dark:border-sky-900 bg-sky-50/50 dark:bg-sky-950/20">
                        <div class="flex items-center justify-between text-xs text-sky-800 dark:text-sky-300 font-bold">
                            <span>💳 Tarjeta Débito</span>
                            <span class="text-[10px] font-mono bg-sky-100 dark:bg-sky-900 px-1.5 py-0.5 rounded">
                                {{ $totales['debito_count'] }} op.
                            </span>
                        </div>
                        <div class="text-lg font-black font-mono text-sky-900 dark:text-sky-200 mt-1">
                            ${{ number_format($totales['debito_total'], 2, ',', '.') }}
                        </div>
                    </div>

                    <!-- Crédito Bancario -->
                    <div class="p-3 rounded-xl border border-indigo-200 dark:border-indigo-900 bg-indigo-50/50 dark:bg-indigo-950/20">
                        <div class="flex items-center justify-between text-xs text-indigo-800 dark:text-indigo-300 font-bold">
                            <span>💳 Tarjeta Crédito</span>
                            <span class="text-[10px] font-mono bg-indigo-100 dark:bg-indigo-900 px-1.5 py-0.5 rounded">
                                {{ $totales['credito_count'] }} op.
                            </span>
                        </div>
                        <div class="text-lg font-black font-mono text-indigo-900 dark:text-indigo-200 mt-1">
                            ${{ number_format($totales['credito_total'], 2, ',', '.') }}
                        </div>
                    </div>

                    <!-- Crédito Personal -->
                    <div class="p-3 rounded-xl border border-purple-200 dark:border-purple-900 bg-purple-50/50 dark:bg-purple-950/20">
                        <div class="flex items-center justify-between text-xs text-purple-800 dark:text-purple-300 font-bold">
                            <span>📋 Crédito Personal</span>
                            <span class="text-[10px] font-mono bg-purple-100 dark:bg-purple-900 px-1.5 py-0.5 rounded">
                                {{ $totales['credito_personal_count'] }} op.
                            </span>
                        </div>
                        <div class="text-lg font-black font-mono text-purple-900 dark:text-purple-200 mt-1">
                            ${{ number_format($totales['credito_personal_total'], 2, ',', '.') }}
                        </div>
                    </div>

                    <!-- Transferencias -->
                    <div class="p-3 rounded-xl border border-teal-200 dark:border-teal-900 bg-teal-50/50 dark:bg-teal-950/20">
                        <div class="flex items-center justify-between text-xs text-teal-800 dark:text-teal-300 font-bold">
                            <span>📱 Transferencia / MP</span>
                            <span class="text-[10px] font-mono bg-teal-100 dark:bg-teal-900 px-1.5 py-0.5 rounded">
                                {{ $totales['transferencia_count'] + $totales['otros_count'] }} op.
                            </span>
                        </div>
                        <div class="text-lg font-black font-mono text-teal-900 dark:text-teal-200 mt-1">
                            ${{ number_format($totales['transferencia_total'] + $totales['otros_total'], 2, ',', '.') }}
                        </div>
                    </div>

                    <!-- TOTAL GENERAL FACTURADO -->
                    <div class="p-3 rounded-xl border-2 border-slate-900 dark:border-white bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-sm">
                        <div class="flex items-center justify-between text-xs font-bold opacity-80 uppercase tracking-wider">
                            <span>Total Facturado</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-white/20 dark:bg-slate-900/20">
                                {{ $totales['ventas_count'] }} total
                            </span>
                        </div>
                        <div class="text-xl font-black font-mono mt-1">
                            ${{ number_format($totales['total_general'], 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. DISCRIMINACIÓN FISCAL HÍBRIDA & CIERRES Z DE CONTROLADORES FISCALES -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-sm">🖨️</span>
                        <div>
                            <span class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 block">
                                Discriminación Fiscal & Cierres Z de Controladores
                            </span>
                            <span class="text-[10px] text-slate-500">
                                Factura Digital ARCA vs. Controladores Fiscales Hasar 2G (Unificados por IP)
                            </span>
                        </div>
                    </div>
                </div>

                {{-- A. Facturación Digital ARCA --}}
                <div class="p-3 rounded-xl border border-sky-200 dark:border-sky-900 bg-sky-50/50 dark:bg-sky-950/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                            🌐
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-black uppercase text-sky-950 dark:text-sky-200">Facturación Digital ARCA (Web Service)</span>
                                <span class="text-[9px] font-mono bg-sky-200 text-sky-900 dark:bg-sky-900 dark:text-sky-200 font-bold px-1.5 py-0.5 rounded uppercase">
                                    Digital en Línea
                                </span>
                            </div>
                            <p class="text-[11px] text-sky-800 dark:text-sky-300 mt-0.5">
                                Comprobantes autorizados con CAE electrónico. No requiere cierre Z de hardware físico.
                            </p>
                            <div class="flex items-center gap-3 text-[10px] text-sky-700 dark:text-sky-400 mt-1 font-mono">
                                <span>Facturas B: <strong>{{ $resumenFiscal['arca']['facturas_b_cant'] ?? 0 }}</strong> (${{ number_format($resumenFiscal['arca']['facturas_b_total'] ?? 0, 2, ',', '.') }})</span>
                                <span>•</span>
                                <span>Facturas A: <strong>{{ $resumenFiscal['arca']['facturas_a_cant'] ?? 0 }}</strong> (${{ number_format($resumenFiscal['arca']['facturas_a_total'] ?? 0, 2, ',', '.') }})</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right sm:border-l sm:border-sky-200 dark:sm:border-sky-900 sm:pl-4 shrink-0">
                        <span class="text-[10px] font-bold text-sky-600 dark:text-sky-400 uppercase block">Total Digital ARCA</span>
                        <span class="text-lg font-black font-mono text-sky-950 dark:text-sky-200">$ {{ number_format($resumenFiscal['arca']['total'] ?? 0, 2, ',', '.') }}</span>
                        <span class="text-[10px] font-mono text-sky-600 dark:text-sky-400 block">{{ $resumenFiscal['arca']['cant_comprobantes'] ?? 0 }} comprobantes</span>
                    </div>
                </div>

                {{-- B. Controladores Fiscales Hasar 2G Físicos --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <span>🖨️</span>
                            <span>Controladores Fiscales Físicos en la Sucursal:</span>
                        </span>
                    </div>

                    @php
                        $hasPendientesZ = false;
                        foreach ($resumenFiscal['hasar_controladores'] ?? [] as $c) {
                            if (!empty($c['cant_tickets']) && empty($c['cierre_z_emitido'])) {
                                $hasPendientesZ = true;
                                break;
                            }
                        }
                    @endphp

                    {{-- Banner de advertencia si hay Z pendientes con tickets --}}
                    <div id="banner-alerta-z" class="p-3 bg-amber-50 dark:bg-amber-950/60 border-2 border-amber-400 dark:border-amber-700 rounded-xl text-xs text-amber-950 dark:text-amber-200 flex items-start gap-2.5 shadow-xs {{ $hasPendientesZ ? '' : 'hidden' }}">
                        <span class="text-base shrink-0">⚠️</span>
                        <div>
                            <strong class="font-black uppercase tracking-wider block">Emisión de Cierre Z</strong>
                            <span class="text-[11px] leading-tight block mt-0.5">
                                Existen controladores fiscales con tickets emitidos. Puede presionar <strong>"⚡ Emitir Cierre Z"</strong> para imprimirlo en la registradora física, o si <u>ya fue emitido previamente</u> (desde el sistema anterior o registradora), presione <strong>"✓ Z Ya Realizado (Omitir)"</strong> para cerrar solo la caja del sistema sin re-imprimir.
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        @forelse($resumenFiscal['hasar_controladores'] ?? [] as $ctrl)
                            @php
                                $ctrlId = 'ctrl_' . md5($ctrl['key']);
                                $tieneTickets = ($ctrl['cant_tickets'] ?? 0) > 0;
                                $zEmitido = !empty($ctrl['cierre_z_emitido']);
                            @endphp
                            <div id="{{ $ctrlId }}" class="p-3.5 rounded-xl border transition-all {{ $zEmitido ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-300 dark:border-emerald-800' : ($tieneTickets ? 'bg-amber-50/70 dark:bg-amber-950/30 border-amber-300 dark:border-amber-800' : 'bg-slate-50 dark:bg-slate-900/60 border-slate-200 dark:border-slate-700') }}">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-start gap-3">
                                        <div id="{{ $ctrlId }}_icon" class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg shrink-0 shadow-sm {{ $zEmitido ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white' }}">
                                            🖨️
                                        </div>
                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="text-xs font-black uppercase text-slate-900 dark:text-white">
                                                    {{ $ctrl['modelo'] }} • IP: {{ $ctrl['hasar_ip'] }}:{{ $ctrl['hasar_puerto'] }}
                                                </span>
                                                <span id="{{ $ctrlId }}_badge" class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $zEmitido ? 'bg-emerald-200 text-emerald-900 dark:bg-emerald-900 dark:text-emerald-200' : ($tieneTickets ? 'bg-amber-200 text-amber-900 dark:bg-amber-900 dark:text-amber-200 animate-pulse' : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300') }}">
                                                    {{ $zEmitido ? '✓ Cierre Z Emitido (' . ($ctrl['cierre_z_numero'] ?? 'OK') . ')' : ($tieneTickets ? '⚠️ Cierre Z Pendiente' : 'Sin Ventas en este Turno') }}
                                                </span>
                                            </div>

                                            <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-0.5">
                                                Puntos de venta asociados: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $ctrl['puntos_venta_info'] }}</strong>
                                            </p>

                                            <div class="flex items-center gap-3 text-[11px] font-mono text-slate-600 dark:text-slate-400 mt-1">
                                                <span>Tickets Emitidos: <strong class="text-slate-900 dark:text-white">{{ $ctrl['cant_tickets'] }}</strong></span>
                                                <span>•</span>
                                                <span>Total Facturado: <strong class="text-slate-900 dark:text-white">$ {{ number_format($ctrl['total'], 2, ',', '.') }}</strong></span>
                                            </div>

                                            <p id="{{ $ctrlId }}_error" class="text-[11px] text-rose-600 font-bold mt-1 hidden"></p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 self-end sm:self-center shrink-0">
                                        <button type="button" 
                                                onclick="consultarEstadoFiscal('{{ $ctrl['hasar_ip'] }}', {{ $ctrl['puerto_local'] }}, '{{ $ctrlId }}')"
                                                class="px-2.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer shadow-2xs"
                                                title="Consultar si la registradora está en jornada abierta y puede facturar hoy">
                                            <span>🔍 Ver Estado Registradora</span>
                                        </button>

                                        @if(!$zEmitido)
                                        <button type="button" 
                                                onclick="marcarZYaEmitido('{{ $ctrl['key'] }}', '{{ $ctrl['hasar_ip'] }}', {{ $ctrl['hasar_puerto'] }}, {{ $ctrl['puerto_local'] }}, '{{ $ctrl['modelo'] }}', '{{ $ctrlId }}')"
                                                class="px-2.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1 border border-emerald-400 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100 cursor-pointer shadow-2xs"
                                                title="Marcar como ya emitido en el sistema anterior sin mandar a imprimir">
                                            <span>✓ Z Ya Realizado (Omitir)</span>
                                        </button>
                                        @endif

                                        <button type="button" 
                                                id="{{ $ctrlId }}_btn"
                                                onclick="ejecutarCierreZ('{{ $ctrl['key'] }}', '{{ $ctrl['hasar_ip'] }}', {{ $ctrl['hasar_puerto'] }}, {{ $ctrl['puerto_local'] }}, '{{ $ctrl['modelo'] }}', '{{ $ctrlId }}')"
                                                class="px-3.5 py-2 rounded-xl text-xs font-black uppercase transition flex items-center gap-1.5 shadow-sm cursor-pointer {{ $zEmitido ? 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-300' : 'bg-amber-600 hover:bg-amber-700 text-white' }}">
                                            <span id="{{ $ctrlId }}_btn_text">{{ $zEmitido ? '🔄 Re-emitir Cierre Z' : '⚡ Emitir Cierre Z' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-xs text-slate-500 text-center">
                                No hay controladores fiscales Hasar activos registrados para esta sucursal.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Campo oculto para persistir el resumen fiscal consolidado en el POST --}}
                <input type="hidden" name="resumen_fiscal" id="input_resumen_fiscal" value="{{ json_encode($resumenFiscal) }}">
            </div>

            <!-- 3. ARQUEO DE EFECTIVO FÍSICO Y FALLO DE CAJA -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <span>💰</span>
                        <span>Arqueo de Efectivo Físico & Fallo de Caja</span>
                    </span>
                    <span class="text-[11px] text-slate-500 font-mono">
                        Apertura: ${{ number_format($caja->monto_inicial_efectivo, 2, ',', '.') }} + Ventas Ef.: ${{ number_format($totales['efectivo_total'], 2, ',', '.') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Detalle Teórico -->
                    <div class="space-y-2 p-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                            <span>Fondo inicial de apertura:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">
                                +${{ number_format($caja->monto_inicial_efectivo, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400">
                            <span>Ventas cobradas en efectivo:</span>
                            <span class="font-mono font-bold text-slate-900 dark:text-white">
                                +${{ number_format($totales['efectivo_total'], 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-emerald-700 dark:text-emerald-400 font-bold">
                            <span>Ingresos justificados de efectivo:</span>
                            <span class="font-mono">
                                +${{ number_format($totales['total_ingresos'] ?? 0, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-rose-700 dark:text-rose-400 font-bold">
                            <span>Retiros justificados de efectivo:</span>
                            <span class="font-mono">
                                -${{ number_format($totales['total_retiros'] ?? 0, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-slate-200 dark:border-slate-700 font-black text-sm">
                            <span class="text-slate-900 dark:text-white">Efectivo Teórico Esperado:</span>
                            <span class="font-mono text-emerald-700 dark:text-emerald-400">
                                ${{ number_format($totales['efectivo_esperado'], 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Input Efectivo Real Contado -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black uppercase text-slate-800 dark:text-slate-200">
                            Efectivo Físico Contado en Cajón ($ ARS):
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-base">$</span>
                            <input type="number" step="0.01" min="0" required
                                   name="monto_final_efectivo_real"
                                   x-model.number="realContado"
                                   class="w-full pl-9 pr-4 py-2.5 rounded-xl font-mono font-black text-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right focus:ring-2 focus:ring-indigo-500 shadow-inner"
                                   placeholder="0.00">
                        </div>
                        <p class="text-[10px] text-slate-500">
                            Ingrese el dinero en billetes y monedas contado al momento del cierre.
                        </p>
                    </div>
                </div>

                <!-- Tarjeta Fallo de Caja en Tiempo Real -->
                <div class="p-3.5 rounded-xl border flex items-center justify-between text-xs transition"
                     :class="{
                         'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200': diferencia() === 0,
                         'bg-sky-50 dark:bg-sky-950/40 border-sky-300 dark:border-sky-800 text-sky-900 dark:text-sky-200': diferencia() > 0,
                         'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-800 text-rose-900 dark:text-rose-200': diferencia() < 0
                     }">
                    <div>
                        <div class="font-black text-sm flex items-center gap-1.5 uppercase">
                            <span x-show="diferencia() === 0">✓ Arqueo Exacto (Sin Diferencias)</span>
                            <span x-show="diferencia() > 0">▲ Sobrante de Caja</span>
                            <span x-show="diferencia() < 0">▼ Fallo de Caja (Faltante)</span>
                        </div>
                        <span class="text-[11px] opacity-80" x-show="diferencia() === 0">
                            El dinero contado coincide con el esperado por el sistema.
                        </span>
                        <span class="text-[11px] opacity-80" x-show="diferencia() > 0">
                            Hay más efectivo físico que el registrado por las ventas.
                        </span>
                        <span class="text-[11px] opacity-80" x-show="diferencia() < 0">
                            Falta efectivo en el cajón respecto al esperado por el sistema.
                        </span>
                    </div>

                    <div class="text-right">
                        <span class="text-xs uppercase font-bold block opacity-75">Diferencia:</span>
                        <span class="font-mono font-black text-xl" x-text="(diferencia() > 0 ? '+$ ' : (diferencia() < 0 ? '-$ ' : '$ ')) + formatDinero(Math.abs(diferencia()))"></span>
                    </div>
                </div>

                @if($caja->movimientos->isNotEmpty())
                <!-- TABLA DE MOVIMIENTOS REGISTRADOS EN ESTA CAJA -->
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700 space-y-2">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                        <span>📋</span>
                        <span>Detalle de Movimientos Justificados en este Turno ({{ $caja->movimientos->count() }})</span>
                    </span>

                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="bg-slate-50 dark:bg-slate-900/80 text-slate-600 dark:text-slate-400 uppercase text-[10px] font-black border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="py-2 px-3">Hora</th>
                                    <th class="py-2 px-3">Tipo</th>
                                    <th class="py-2 px-3">Motivo / Justificación</th>
                                    <th class="py-2 px-3">Comprobante</th>
                                    <th class="py-2 px-3">Operador</th>
                                    <th class="py-2 px-3 text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-900/40">
                                @foreach($caja->movimientos as $mov)
                                    <tr>
                                        <td class="py-2 px-3 font-mono text-slate-500">{{ $mov->created_at->format('H:i') }} hs</td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $mov->tipo === 'ingreso' ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300' : 'bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300' }}">
                                                {{ $mov->tipo === 'ingreso' ? '+ Ingreso' : '- Retiro' }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 font-bold text-slate-800 dark:text-slate-200">{{ $mov->motivo }}</td>
                                        <td class="py-2 px-3 text-slate-500 font-mono">{{ $mov->comprobante ?? '-' }}</td>
                                        <td class="py-2 px-3 text-slate-600 dark:text-slate-400">{{ $mov->usuario?->name ?? 'Usuario' }}</td>
                                        <td class="py-2 px-3 text-right font-mono font-black {{ $mov->tipo === 'ingreso' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                            {{ ($mov->tipo === 'ingreso' ? '+$ ' : '-$ ') . number_format($mov->monto, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Observaciones -->
                <div>
                    <label class="block text-[11px] font-bold uppercase text-slate-600 dark:text-slate-400 mb-1">
                        Observaciones / Justificación de Cierre (Opcional):
                    </label>
                    <textarea name="observaciones" rows="2"
                              class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200"
                              placeholder="Ej: Cierre de jornada sin novedades, cambio retirado..."></textarea>
                </div>
            </div>

            @if(!$caja->esDeHoy())
            <!-- 4. APERTURA AUTOMÁTICA DE LA CAJA DE HOY -->
            <div class="bg-indigo-50/70 dark:bg-indigo-950/40 p-4 rounded-2xl border-2 border-indigo-400 dark:border-indigo-600 shadow-xs space-y-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-sm">
                        🌅
                    </span>
                    <div>
                        <h3 class="text-xs font-black uppercase text-indigo-950 dark:text-indigo-200 tracking-wider">
                            Apertura Automática de la Caja de HOY ({{ now()->format('d/m/Y') }})
                        </h3>
                        <p class="text-[11px] text-indigo-800 dark:text-indigo-300">
                            Al confirmar este Cierre Z de la jornada previa, se abrirá automáticamente el turno de hoy para <strong>{{ $caja->sucursal?->nombre }}</strong> y volverá al POS listo para facturar.
                        </p>
                    </div>
                </div>

                <div class="pt-1">
                    <label class="block text-[11px] font-black uppercase text-indigo-950 dark:text-indigo-200 mb-1">
                        Fondo Inicial en Efectivo para Abrir la Caja de HOY ($ ARS):
                    </label>
                    <div class="relative max-w-xs">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">$</span>
                        <input type="number" step="0.01" min="0" required name="monto_inicial_hoy"
                               class="w-full pl-8 pr-3 font-mono font-black text-base py-2 rounded-xl border-indigo-300 dark:border-indigo-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-right focus:ring-2 focus:ring-indigo-500 shadow-xs"
                               placeholder="0.00" value="{{ old('monto_inicial_hoy', '') }}">
                    </div>
                </div>
            </div>
            @endif

            <!-- Botones de Acción -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('cajas.index') }}" 
                   class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs py-2.5 px-5 rounded-xl transition uppercase">
                    Cancelar
                </a>
                <button type="submit" 
                        class="bg-slate-900 hover:bg-black dark:bg-white dark:hover:bg-slate-100 text-white dark:text-slate-900 font-black text-xs py-2.5 px-6 rounded-xl shadow-lg transition uppercase flex items-center gap-2 cursor-pointer">
                    <span>🔒 {{ !$caja->esDeHoy() ? 'Confirmar Cierre Z y Abrir Caja de Hoy' : 'Confirmar Cierre Z de Caja' }}</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
