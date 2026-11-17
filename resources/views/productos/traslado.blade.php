<x-app-layout>
    {{-- Script del Componente Alpine definido ANTES del DOM para garantizar inicialización inmediata --}}
    <script>
        const TRASLADO_CONFIG = {
            sucursalOrigenId: @json((int)$sucursalActivaId),
            sucursalDestinoId: @json(old('sucursal_destino_id', '')),
            comprobante: @json(old('comprobante', $comprobanteSugerido)),
            motivo: @json(old('motivo', 'Traslado inter-sucursal de mercadería')),
            buscarUrl: @json(route('productos.buscar.traslado')),
            verificarStockUrl: @json(route('productos.verificar.stock.traslado')),
            csrfToken: @json(csrf_token()),
            initialItems: @json(old('items', $productoSeleccionado ? [$productoSeleccionado] : []))
        };

        function trasladoLoteComponent() {
            return {
                sucursalOrigenId: TRASLADO_CONFIG.sucursalOrigenId,
                sucursalDestinoId: TRASLADO_CONFIG.sucursalDestinoId,
                comprobante: TRASLADO_CONFIG.comprobante,
                motivo: TRASLADO_CONFIG.motivo,

                scanQuery: '',
                isLoading: false,
                errorMessage: '',
                searchResults: [],
                showResultsDropdown: false,

                // Modal de ingreso de cantidad y rectificación de talle / color
                modalCantidad: false,
                itemPendiente: null,
                cantidadAEnviar: 1,
                errorModal: '',

                desgloseActual: {
                    prov: '000',
                    talle: '00',
                    color: '00',
                    resto: '00001',
                    codigo_completo: ''
                },
                talleOriginal: '',
                colorOriginal: '',
                talleNuevo: '',
                colorNuevo: '',
                nuevoCodigoGenerado: '',
                esRectificado: false,

                items: (TRASLADO_CONFIG.initialItems || []).map(it => ({
                    ...it,
                    cantidad: parseFloat(it.cantidad) || 1,
                    stock_origen: parseFloat(it.stock_origen) || 0,
                    codigo_proveedor: it.codigo_proveedor || '',
                    talle: it.talle || '',
                    color: it.color || '',
                    talle_original: it.talle_original || it.talle || '',
                    color_original: it.color_original || it.color || '',
                    proveedor: it.proveedor || '',
                    proveedor_legajo: it.proveedor_legajo || '',
                    rectificado: it.rectificado || false,
                    nuevo_codigo_barra: it.nuevo_codigo_barra || '',
                    mostrar_edicion: false
                })),

                normalizarTalle2Char(talle) {
                    if (!talle || !talle.trim()) return '00';
                    const t = talle.trim().toUpperCase();
                    if (!isNaN(t)) {
                        return String(parseInt(t, 10)).padStart(2, '0');
                    }
                    const map = {
                        'XS': 'XS', 'S': '0S', 'M': '0M', 'L': '0L', 'XL': 'XL',
                        'XXL': '2X', '2XL': '2X', 'XXXL': '3X', '3XL': '3X',
                        '4XL': '4X', '5XL': '5X', '6XL': '6X', '7XL': '7X', '8XL': '8X',
                        'U': '0U', 'UNICO': 'UN', 'ESP': 'ES'
                    };
                    if (map[t]) return map[t];
                    if (t.length === 2) return t;
                    if (t.length === 1) return '0' + t;
                    const clean = t.replace(/[^A-Z0-9]/g, '');
                    return (clean.substring(0, 2) || '00').padEnd(2, '0');
                },

                normalizarColor2Char(color) {
                    if (!color || !color.trim()) return '00';
                    const c = color.trim().toUpperCase();
                    const map = {
                        'NEGRO': 'NE', 'BLANCO': 'BL', 'AZUL': 'AZ', 'ROJO': 'RO',
                        'VERDE': 'VE', 'AMARILLO': 'AM', 'GRIS': 'GR', 'ROSA': 'RS',
                        'ROSADO': 'RS', 'CELESTE': 'CE', 'BEIGE': 'BE', 'MARRON': 'MA',
                        'CAFE': 'CF', 'BORDO': 'BO', 'VIOLETA': 'VI', 'LILA': 'LI',
                        'NARANJA': 'NA', 'FUCSIA': 'FU', 'TURQUESA': 'TU', 'ESTAMPADO': 'ES',
                        'SURTIDO': 'SU', 'VARIOS': 'VA', 'MULTICOLOR': 'MC'
                    };
                    if (map[c]) return map[c];
                    const clean = c.replace(/[^A-Z0-9]/g, '');
                    if (clean.length >= 2) return clean.substring(0, 2);
                    if (clean.length === 1) return '0' + clean;
                    return '00';
                },

                // Descompone el código según la regla canónica:
                // Primeros 3: Proveedor | Siguientes 2: Talle | Siguientes 2: Color | Resto: Producto
                descomponerCodigo(prod) {
                    let cod = (prod.codigo_barra || prod.codigo || '').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                    if (cod.length === 11) {
                        cod = '0' + cod;
                    }

                    let prov = '000';
                    let talle = '00';
                    let color = '00';
                    let resto = '00001';

                    if (cod.length >= 12) {
                        prov = cod.substring(0, 3);
                        talle = cod.substring(3, 5);
                        color = cod.substring(5, 7);
                        resto = cod.substring(7);
                    } else if (cod.length >= 8) {
                        prov = cod.substring(0, 3);
                        talle = '00';
                        color = '00';
                        resto = cod.substring(3);
                    } else {
                        prov = String(prod.proveedor_legajo || '0').padStart(3, '0');
                        talle = this.normalizarTalle2Char(prod.talle);
                        color = this.normalizarColor2Char(prod.color);
                        resto = (prod.codigo_proveedor || prod.codigo || '00001').replace(/[^A-Za-z0-9]/g, '').toUpperCase();
                    }

                    return {
                        prov,
                        talle,
                        color,
                        resto,
                        codigo_completo: cod
                    };
                },

                init() {
                    this.focoScanner();
                },

                focoScanner() {
                    this.$nextTick(() => {
                        if (this.$refs.inputScanner) {
                            this.$refs.inputScanner.focus();
                        }
                    });
                },

                // Búsqueda en vivo mientras se escribe (sugerencias)
                async onInputSearch() {
                    const query = this.scanQuery.trim();
                    if (query.length < 2) {
                        this.searchResults = [];
                        this.showResultsDropdown = false;
                        return;
                    }
                    await this.ejecutarBusqueda(query, false);
                },

                // Búsqueda al presionar Enter (pistola barcode o búsqueda forzada)
                async buscarYProcesar() {
                    const query = this.scanQuery.trim();
                    if (!query) return;
                    await this.ejecutarBusqueda(query, true);
                },

                async ejecutarBusqueda(query, esEnter) {
                    this.isLoading = true;
                    this.errorMessage = '';

                    try {
                        const url = `${TRASLADO_CONFIG.buscarUrl}?q=${encodeURIComponent(query)}&sucursal_id=${this.sucursalOrigenId}`;
                        const res = await fetch(url);
                        const data = await res.json();
                        this.isLoading = false;

                        if (!data || data.length === 0) {
                            this.searchResults = [];
                            this.showResultsDropdown = false;
                            if (esEnter) {
                                this.errorMessage = `No se encontró ningún artículo activo con "${query}".`;
                                this.reproducirAlerta();
                                this.focoScanner();
                            }
                            return;
                        }

                        // Si se presionó Enter y es coincidencia exacta de código de barras o código único:
                        if (esEnter) {
                            const exacto = data.find(p => p.exact_match);
                            if (exacto) {
                                this.scanQuery = '';
                                this.showResultsDropdown = false;
                                this.abrirModalCantidad(exacto);
                                return;
                            } else if (data.length === 1) {
                                this.scanQuery = '';
                                this.showResultsDropdown = false;
                                this.abrirModalCantidad(data[0]);
                                return;
                            }
                        }

                        // Mostrar dropdown de sugerencias interactivas
                        this.searchResults = data;
                        this.showResultsDropdown = true;
                    } catch (e) {
                        this.isLoading = false;
                        this.errorMessage = 'Error de comunicación al consultar el catálogo.';
                    }
                },

                seleccionarResultado(prod) {
                    this.showResultsDropdown = false;
                    this.scanQuery = '';
                    this.abrirModalCantidad(prod);
                },

                abrirModalCantidad(prod) {
                    if (prod.stock_origen <= 0) {
                        this.errorMessage = `El artículo [${prod.codigo}] ${prod.descripcion} no posee stock en la sucursal emisora seleccionada (Stock: 0).`;
                        this.reproducirAlerta();
                        this.focoScanner();
                        return;
                    }

                    const itemsMismoProd = this.items.filter(i => i.id === prod.id);
                    const yaAsignado = itemsMismoProd.reduce((acc, i) => acc + (parseFloat(i.cantidad) || 0), 0);
                    const disponibleRestante = prod.stock_origen - yaAsignado;

                    if (disponibleRestante <= 0) {
                        this.errorMessage = `Ya se cargó todo el stock disponible (${prod.stock_origen} un.) de "${prod.descripcion}" en este lote.`;
                        this.reproducirAlerta();
                        this.focoScanner();
                        return;
                    }

                    this.itemPendiente = prod;
                    this.cantidadAEnviar = 1;
                    this.errorModal = '';

                    // Descomposición del código en los 4 componentes (3 Prov, 2 Talle, 2 Color, Resto Producto)
                    this.desgloseActual = this.descomponerCodigo(prod);

                    // Inicializar Talle y Color actuales de la prenda
                    this.talleOriginal = (prod.talle && String(prod.talle).trim()) ? String(prod.talle).trim().toUpperCase() : this.desgloseActual.talle;
                    this.colorOriginal = (prod.color && String(prod.color).trim()) ? String(prod.color).trim().toUpperCase() : this.desgloseActual.color;
                    this.talleNuevo = this.talleOriginal;
                    this.colorNuevo = this.colorOriginal;
                    this.nuevoCodigoGenerado = this.desgloseActual.codigo_completo;
                    this.esRectificado = false;

                    this.modalCantidad = true;

                    this.$nextTick(() => {
                        if (this.$refs.inputCantidadModal) {
                            this.$refs.inputCantidadModal.focus();
                            this.$refs.inputCantidadModal.select();
                        }
                    });
                },

                recalcularEnModal() {
                    const tNuevo = (this.talleNuevo || '').trim().toUpperCase();
                    const cNuevo = (this.colorNuevo || '').trim().toUpperCase();
                    const tOrig = (this.talleOriginal || '').trim().toUpperCase();
                    const cOrig = (this.colorOriginal || '').trim().toUpperCase();

                    const cambioTalle = tNuevo !== tOrig;
                    const cambioColor = cNuevo !== cOrig;
                    this.esRectificado = cambioTalle || cambioColor;

                    if (this.esRectificado) {
                        const talle2 = this.normalizarTalle2Char(tNuevo);
                        const color2 = this.normalizarColor2Char(cNuevo);
                        this.nuevoCodigoGenerado = (this.desgloseActual.prov + talle2 + color2 + this.desgloseActual.resto).toUpperCase();
                    } else {
                        this.nuevoCodigoGenerado = this.desgloseActual.codigo_completo;
                    }
                },

                confirmarAgregarAlLote() {
                    if (!this.itemPendiente) return;
                    const cant = parseFloat(this.cantidadAEnviar);

                    if (isNaN(cant) || cant <= 0) {
                        this.errorModal = 'Por favor indique una cantidad mayor a cero.';
                        return;
                    }

                    const tNuevo = (this.talleNuevo || '').trim().toUpperCase();
                    const cNuevo = (this.colorNuevo || '').trim().toUpperCase();
                    const tOrig = (this.talleOriginal || '').trim().toUpperCase();
                    const cOrig = (this.colorOriginal || '').trim().toUpperCase();
                    const esRect = this.esRectificado;
                    const codNuevo = esRect ? this.nuevoCodigoGenerado : this.desgloseActual.codigo_completo;

                    // Validar límite de stock en origen
                    const itemsMismoProd = this.items.filter(i => i.id === this.itemPendiente.id);
                    const yaAsignado = itemsMismoProd.reduce((acc, i) => acc + (parseFloat(i.cantidad) || 0), 0);
                    const nuevoTotal = yaAsignado + cant;

                    if (nuevoTotal > this.itemPendiente.stock_origen) {
                        this.errorModal = `La cantidad total (${nuevoTotal} un.) supera el stock disponible en origen (${this.itemPendiente.stock_origen} un., ya en lote: ${yaAsignado}).`;
                        return;
                    }

                    // Buscar si ya existe una fila idéntica (mismo producto y misma rectificación)
                    const itemExistente = this.items.find(i => 
                        i.id === this.itemPendiente.id && 
                        i.rectificado === esRect && 
                        i.nuevo_codigo_barra === codNuevo
                    );

                    if (itemExistente) {
                        itemExistente.cantidad += cant;
                    } else {
                        this.items.unshift({
                            id: this.itemPendiente.id,
                            codigo: this.itemPendiente.codigo,
                            codigo_barra: this.itemPendiente.codigo_barra || this.desgloseActual.codigo_completo,
                            codigo_proveedor: this.desgloseActual.resto,
                            proveedor: this.itemPendiente.proveedor || '',
                            proveedor_legajo: this.desgloseActual.prov,
                            descripcion: this.itemPendiente.descripcion,
                            categoria: this.itemPendiente.categoria,
                            subcategoria: this.itemPendiente.subcategoria,
                            unidad_medida: this.itemPendiente.unidad_medida,
                            stock_origen: this.itemPendiente.stock_origen,
                            cantidad: cant,
                            // Datos de rectificación
                            talle_original: tOrig,
                            color_original: cOrig,
                            talle: tNuevo,
                            color: cNuevo,
                            rectificado: esRect,
                            nuevo_codigo_barra: codNuevo,
                            desglose_prov: this.desgloseActual.prov,
                            desglose_talle: this.normalizarTalle2Char(tNuevo),
                            desglose_color: this.normalizarColor2Char(cNuevo),
                            desglose_resto: this.desgloseActual.resto,
                            mostrar_edicion: false
                        });
                    }

                    this.modalCantidad = false;
                    this.itemPendiente = null;
                    this.focoScanner();
                },

                quitarItem(index) {
                    this.items.splice(index, 1);
                    this.focoScanner();
                },

                modificarCantidad(index, delta) {
                    const item = this.items[index];
                    const nueva = (parseFloat(item.cantidad) || 0) + delta;
                    if (nueva <= 0) {
                        this.quitarItem(index);
                    } else if (nueva > item.stock_origen) {
                        alert(`No es posible superar el stock disponible en origen (${item.stock_origen} un.)`);
                    } else {
                        item.cantidad = nueva;
                    }
                },

                actualizarCantidadManual(index, valor) {
                    const item = this.items[index];
                    const val = parseFloat(valor);
                    if (isNaN(val) || val <= 0) {
                        item.cantidad = 1;
                    } else if (val > item.stock_origen) {
                        alert(`La cantidad no puede superar el stock disponible en origen (${item.stock_origen} un.)`);
                        item.cantidad = item.stock_origen;
                    } else {
                        item.cantidad = val;
                    }
                },

                async cambioSucursalOrigen() {
                    if (this.sucursalDestinoId == this.sucursalOrigenId) {
                        this.sucursalDestinoId = '';
                    }

                    if (this.items.length === 0) return;
                    const ids = this.items.map(i => i.id);
                    try {
                        const res = await fetch(TRASLADO_CONFIG.verificarStockUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': TRASLADO_CONFIG.csrfToken
                            },
                            body: JSON.stringify({ sucursal_id: this.sucursalOrigenId, producto_ids: ids })
                        });
                        const data = await res.json();
                        this.items.forEach(it => {
                            it.stock_origen = parseFloat(data[it.id]) || 0;
                        });
                    } catch (e) {}
                },

                totalArticulos() {
                    return this.items.length;
                },

                totalUnidades() {
                    return this.items.reduce((acc, it) => acc + (parseFloat(it.cantidad) || 0), 0);
                },

                totalRectificados() {
                    return this.items.filter(it => it.rectificado).reduce((acc, it) => acc + (parseFloat(it.cantidad) || 0), 0);
                },

                hayStockInvalido() {
                    return this.items.some(it => it.cantidad > it.stock_origen || it.stock_origen <= 0);
                },

                puedeConfirmar() {
                    return this.items.length > 0 && 
                           this.sucursalDestinoId && 
                           this.sucursalDestinoId != this.sucursalOrigenId && 
                           !this.hayStockInvalido();
                },

                confirmarEnvio(e) {
                    if (!this.puedeConfirmar()) {
                        e.preventDefault();
                        return false;
                    }
                    const dest = this.$refs.selectDestino.options[this.$refs.selectDestino.selectedIndex]?.text || 'Sucursal Destino';
                    let msg = `¿Confirma el traslado de ${this.totalArticulos()} artículo(s) (${this.totalUnidades()} unidades) hacia "${dest}"?`;
                    if (this.totalRectificados() > 0) {
                        msg += `\n\nATENCIÓN: ${this.totalRectificados()} unidad(es) poseen rectificación de talle/color. Se generarán nuevos códigos y se habilitará la impresión de etiquetas térmicas.`;
                    }
                    if (!confirm(msg)) {
                        e.preventDefault();
                        return false;
                    }
                },

                reproducirAlerta() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        osc.type = 'sawtooth';
                        osc.frequency.setValueAtTime(220, ctx.currentTime);
                        osc.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.15);
                    } catch (e) {}
                }
            };
        }
    </script>

    {{-- Datalists globales de sugerencias de talles y colores --}}
    <datalist id="lista-talles-sugeridos">
        @foreach($tallesSugeridos as $ts)
            <option value="{{ $ts }}"></option>
        @endforeach
    </datalist>

    <datalist id="lista-colores-sugeridos">
        @foreach($coloresSugeridos as $cs)
            <option value="{{ $cs }}"></option>
        @endforeach
    </datalist>

    <div class="max-w-7xl mx-auto py-2 space-y-5" x-data="trasladoLoteComponent()">

        {{-- Encabezado Principal --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800 gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </span>
                    <h1 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                        Carga y Traslado de Mercadería por Lote
                    </h1>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Escanee prendas con el lector. Puede rectificar Talle y Color en el acto: si cambian, se descuenta del original, se genera el nuevo código estructurado y se habilitan etiquetas térmicas.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('stock.movimientos') }}" 
                   class="px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl transition-all flex items-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>Historial de Movimientos</span>
                </a>
                <a href="{{ route('productos.index') }}" 
                   class="px-3 py-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl transition-all shadow-sm">
                    &larr; Catálogo
                </a>
            </div>
        </div>

        {{-- BANNER ESPECIAL: BOTÓN DIRECTO PARA IMPRIMIR ETIQUETAS DE RECTIFICACIÓN --}}
        @if(session('etiquetas_rectificadas'))
            <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-500/15 via-indigo-500/15 to-blue-500/15 border-2 border-amber-400 dark:border-amber-600 flex flex-col md:flex-row items-center justify-between gap-3 shadow-md">
                <div class="flex items-center gap-3">
                    <span class="text-3xl p-2.5 rounded-xl bg-amber-500/20 text-amber-600">🏷️</span>
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                            ¡Traslado completado con {{ count(session('etiquetas_rectificadas')) }} prenda(s) rectificada(s)!
                        </h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                            Se generaron nuevos códigos de barra CODE128. Imprima las etiquetas térmicas (50mm x 25mm) para pegarlas en las prendas físicas.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('productos.etiquetas.lote', ['desde_sesion' => 1]) }}" target="_blank"
                       class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs uppercase tracking-wide shadow-lg transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>Imprimir Etiquetas ({{ array_sum(array_column(session('etiquetas_rectificadas'), 'cantidad')) }} unid.)</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Alertas Globales --}}
        @if(session('success') && !session('etiquetas_rectificadas'))
            <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-semibold flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-semibold flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- FORMULARIO PRINCIPAL --}}
        <form action="{{ route('productos.traslado.store') }}" method="POST" @submit="confirmarEnvio($event)" class="space-y-5">
            @csrf

            {{-- 1. BLOQUE DE ENCABEZADO DEL ENVÍO / REMITO --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-700/60">
                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 flex items-center justify-center text-[11px] font-bold">1</span>
                        Datos del Envío Inter-Sucursal
                    </span>
                    <span class="text-[11px] text-slate-400 font-mono">
                        Configuración de sede emisora y receptora
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    {{-- Sucursal Origen --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Sucursal Origen (Emisora) <span class="text-rose-500">*</span>
                        </label>
                        <select name="sucursal_origen_id" x-model="sucursalOrigenId" @change="cambioSucursalOrigen()" required
                                class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-bold focus:ring-blue-500 focus:border-blue-500">
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}">
                                    {{ $suc->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sucursal Destino --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Sucursal Destino (Receptora) <span class="text-rose-500">*</span>
                        </label>
                        <select name="sucursal_destino_id" x-model="sucursalDestinoId" x-ref="selectDestino" required
                                class="w-full text-xs py-2 px-3 rounded-xl border-amber-300 dark:border-amber-600/80 bg-amber-50/30 dark:bg-slate-900 text-slate-900 dark:text-white font-bold focus:ring-amber-500 focus:border-amber-500">
                            <option value="">-- Seleccionar Sucursal Destino --</option>
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}" :disabled="sucursalOrigenId == {{ $suc->id }}">
                                    {{ $suc->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nro. de Comprobante / Remito --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nro. de Remito / Guía
                        </label>
                        <input type="text" name="comprobante" x-model="comprobante" required
                               placeholder="Ej: REM-20261017-001"
                               class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold">
                    </div>

                    {{-- Motivo / Observaciones --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Motivo / Observaciones del Lote
                        </label>
                        <input type="text" name="motivo" x-model="motivo"
                               placeholder="Ej: Reposición de stock semanal, Caja N° 1..."
                               class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white">
                    </div>
                </div>
            </div>

            {{-- 2. BARRA DE ESCANEO RÁPIDO Y BUSCADOR INTELIGENTE --}}
            <div class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl p-5 shadow-md text-white relative">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-400 text-blue-950 flex items-center justify-center text-[11px] font-black">2</span>
                        <span class="text-xs font-black uppercase tracking-wider text-blue-100 flex items-center gap-2">
                            Punto de Escaneo de Artículos
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-800 text-blue-200 border border-blue-700">
                                Lector Barcode Activo
                            </span>
                        </span>
                    </div>

                    <div class="text-[11px] text-blue-200 flex items-center gap-3">
                        <span>Escriba para ver sugerencias o escanee con la pistola y presione <kbd class="px-1.5 py-0.5 rounded bg-blue-950 border border-blue-700 font-mono text-[10px]">Enter</kbd>.</span>
                        <button type="button" @click="focoScanner()" class="underline hover:text-white text-xs font-bold">
                            Reenfocar Lector
                        </button>
                    </div>
                </div>

                {{-- Campo de entrada con icono de pistola de código de barras y buscador --}}
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>

                    <input type="text" 
                           x-ref="inputScanner"
                           x-model="scanQuery"
                           @input.debounce.250ms="onInputSearch()"
                           @keydown.enter.prevent="buscarYProcesar()"
                           @keydown.escape="showResultsDropdown = false; scanQuery = ''"
                           placeholder="Escriba código, descripción o pase el lector de código de barras..."
                           class="w-full text-sm py-3 pl-11 pr-28 rounded-xl bg-white/10 dark:bg-black/30 border border-blue-400/40 focus:border-amber-400 focus:bg-white focus:text-slate-900 dark:focus:bg-slate-900 dark:focus:text-white placeholder:text-blue-200/70 transition-all font-mono font-medium shadow-inner">

                    <div class="absolute inset-y-0 right-1.5 flex items-center gap-1.5">
                        <span x-show="isLoading" class="text-xs text-amber-300 animate-pulse font-bold px-2">Buscando...</span>
                        <button type="button" 
                                @click="buscarYProcesar()"
                                class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs px-3 py-1.5 rounded-lg shadow-sm transition-all flex items-center gap-1">
                            <span>BUSCAR</span>
                            <span class="text-[10px]">&crarr;</span>
                        </button>
                    </div>

                    {{-- Dropdown de sugerencias interactivas cuando hay resultados --}}
                    <div x-show="showResultsDropdown" 
                         @click.outside="showResultsDropdown = false"
                         style="display: none;"
                         class="absolute z-50 left-0 right-0 mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden max-h-72 overflow-y-auto text-slate-900 dark:text-white">
                        <div class="p-2 bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs font-bold text-slate-500">
                            <span>Sugerencias de artículos (<span x-text="searchResults.length"></span>):</span>
                            <span class="text-[10px] text-blue-600 dark:text-blue-400">Haga clic en una prenda para indicar cantidad y rectificar</span>
                        </div>
                        <template x-for="item in searchResults" :key="item.id">
                            <button type="button" 
                                    @click="seleccionarResultado(item)"
                                    class="w-full text-left px-3.5 py-2.5 hover:bg-blue-50 dark:hover:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between transition-colors">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-bold text-xs text-blue-600 dark:text-blue-400" x-text="item.codigo"></span>
                                        <span class="text-xs font-bold uppercase" x-text="item.descripcion"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-2 flex-wrap">
                                        <span x-show="item.codigo_barra" class="font-mono">Barras: <span x-text="item.codigo_barra"></span></span>
                                        <span x-show="item.talle" class="px-1 py-0.2 bg-indigo-50 dark:bg-indigo-950 text-indigo-500 rounded font-mono">T: <span x-text="item.talle"></span></span>
                                        <span x-show="item.color" class="px-1 py-0.2 bg-rose-50 dark:bg-rose-950 text-rose-500 rounded">C: <span x-text="item.color"></span></span>
                                        <span class="px-1 py-0.2 bg-slate-100 dark:bg-slate-800 rounded text-slate-500" x-text="item.categoria"></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block text-xs font-bold font-mono px-2 py-0.5 rounded-full"
                                          :class="item.stock_origen > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'"
                                          x-text="item.stock_origen + ' en origen'"></span>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Aviso de error en escaneo --}}
                <div x-show="errorMessage" style="display: none;" 
                     class="mt-2 text-xs text-rose-200 bg-rose-950/80 border border-rose-700/80 px-3 py-1.5 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>
                    <button type="button" @click="errorMessage = ''" class="text-rose-300 hover:text-white">&times;</button>
                </div>
            </div>

            {{-- 3. LISTADO DEL LOTE / CAJA DE ENVÍO (TABLA EN TIEMPO REAL) --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 flex items-center justify-center text-[11px] font-bold">3</span>
                        <h2 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Prendas en el Lote a Trasladar
                        </h2>
                    </div>

                    <div class="flex items-center gap-4 text-xs font-mono font-bold">
                        <span class="text-slate-500 dark:text-slate-400">
                            Artículos distintos: <strong class="text-slate-900 dark:text-white" x-text="totalArticulos()"></strong>
                        </span>
                        <span x-show="totalRectificados() > 0" class="px-2 py-0.5 rounded-lg bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[11px]">
                            Rectificados: <strong x-text="totalRectificados()"></strong> un.
                        </span>
                        <span class="px-2.5 py-1 rounded-lg bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                            Total Unidades a Mover: <strong class="text-sm" x-text="totalUnidades()"></strong>
                        </span>
                    </div>
                </div>

                {{-- Tabla de Artículos --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-700 text-[10px] font-black uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                                <th class="py-2.5 px-4 w-12 text-center">#</th>
                                <th class="py-2.5 px-4">Código / Barras</th>
                                <th class="py-2.5 px-4">Descripción & Rubro</th>
                                <th class="py-2.5 px-4">Talle & Color</th>
                                <th class="py-2.5 px-4 text-right">Stock en Origen</th>
                                <th class="py-2.5 px-4 text-center w-48">Cantidad a Mover</th>
                                <th class="py-2.5 px-4 text-right">Remanente Origen</th>
                                <th class="py-2.5 px-4 text-center w-16">Quitar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition-colors"
                                    :class="item.rectificado ? 'bg-amber-50/40 dark:bg-amber-950/20' : (item.cantidad > item.stock_origen ? 'bg-rose-50/50 dark:bg-rose-950/30' : '')">
                                    {{-- Nro --}}
                                    <td class="py-3 px-4 text-center font-mono text-slate-400 text-[11px]" x-text="index + 1"></td>

                                    {{-- Código y Barras --}}
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <template x-if="!item.rectificado">
                                            <div>
                                                <div class="font-mono font-bold text-slate-900 dark:text-slate-100" x-text="item.codigo"></div>
                                                <div class="text-[10px] font-mono text-slate-400" x-text="item.codigo_barra || 'Sin barras'"></div>
                                            </div>
                                        </template>

                                        <template x-if="item.rectificado">
                                            <div>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-500 text-white mb-0.5">
                                                    RECTIFICADO
                                                </span>
                                                <div class="font-mono font-bold text-amber-700 dark:text-amber-300" x-text="item.nuevo_codigo_barra"></div>
                                                <div class="text-[10px] font-mono text-slate-400 line-through" x-text="'Orig: ' + item.codigo"></div>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- Descripción y Clasificación --}}
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900 dark:text-white uppercase tracking-tight" x-text="item.descripcion"></div>
                                        <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5">
                                            <span class="px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 font-semibold" x-text="item.categoria"></span>
                                            <span x-show="item.subcategoria" class="px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950/80 text-indigo-600 dark:text-indigo-400 font-semibold" x-text="item.subcategoria"></span>
                                        </div>
                                    </td>

                                    {{-- Talle / Color (con indicador si fue rectificado) --}}
                                    <td class="py-3 px-4">
                                        <template x-if="!item.rectificado">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold font-mono text-[10px]"
                                                      x-text="'T: ' + (item.talle || 'S/T')"></span>
                                                <span class="px-1.5 py-0.5 rounded bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 font-bold text-[10px]"
                                                      x-text="'C: ' + (item.color || 'S/C')"></span>
                                            </div>
                                        </template>

                                        <template x-if="item.rectificado">
                                            <div class="space-y-0.5">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 text-indigo-800 dark:text-indigo-200 font-black font-mono text-[10px]"
                                                          x-text="'T: ' + item.talle"></span>
                                                    <span class="px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/60 text-rose-800 dark:text-rose-200 font-black text-[10px]"
                                                          x-text="'C: ' + item.color"></span>
                                                </div>
                                                <div class="text-[9px] text-slate-400 font-mono">
                                                    Orig: T: <span x-text="item.talle_original || 'S/T'"></span> | C: <span x-text="item.color_original || 'S/C'"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- Stock actual en origen --}}
                                    <td class="py-3 px-4 text-right whitespace-nowrap font-mono">
                                        <span class="font-bold" x-text="item.stock_origen"></span>
                                        <span class="text-[10px] text-slate-400" x-text="item.unidad_medida"></span>
                                    </td>

                                    {{-- Controles de Cantidad (+/- y número editable) --}}
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center border border-slate-300 dark:border-slate-600 rounded-xl overflow-hidden shadow-sm bg-white dark:bg-slate-900">
                                            <button type="button" 
                                                    @click="modificarCantidad(index, -1)"
                                                    class="px-2.5 py-1 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition font-bold text-sm select-none">
                                                &minus;
                                            </button>
                                            <input type="number" 
                                                   step="1" 
                                                   min="1" 
                                                   :max="item.stock_origen"
                                                   :value="item.cantidad"
                                                   @change="actualizarCantidadManual(index, $event.target.value)"
                                                   class="w-16 py-1 px-1 text-center font-mono font-black text-xs border-0 bg-transparent text-slate-900 dark:text-white focus:ring-0">
                                            <button type="button" 
                                                    @click="modificarCantidad(index, 1)"
                                                    class="px-2.5 py-1 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition font-bold text-sm select-none">
                                                &plus;
                                            </button>
                                        </div>
                                    </td>

                                    {{-- Remanente estimado en origen --}}
                                    <td class="py-3 px-4 text-right whitespace-nowrap font-mono font-bold">
                                        <span :class="(item.stock_origen - item.cantidad) < 0 ? 'text-rose-500 font-black' : 'text-slate-600 dark:text-slate-300'"
                                              x-text="(item.stock_origen - item.cantidad)"></span>
                                        <span class="text-[10px] text-slate-400">un.</span>
                                    </td>

                                    {{-- Botón Quitar --}}
                                    <td class="py-3 px-4 text-center">
                                        <button type="button" 
                                                @click="quitarItem(index)"
                                                class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg transition" 
                                                title="Quitar del lote">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            {{-- Estado Vacío --}}
                            <tr x-show="items.length === 0">
                                <td colspan="8" class="py-12 text-center text-slate-400 dark:text-slate-500">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 stroke-[1.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                        <p class="font-bold text-xs uppercase tracking-wide">El lote de traslado está vacío</p>
                                        <p class="text-[11px] max-w-sm text-slate-400">
                                            Escriba o use el lector de código de barras arriba para agregar las prendas que trasladará en la caja o lote.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Inputs ocultos para enviar el array de items en el formulario HTTP POST --}}
                <template x-for="(it, idx) in items" :key="idx">
                    <div>
                        <input type="hidden" :name="'items[' + idx + '][producto_id]'" :value="it.id">
                        <input type="hidden" :name="'items[' + idx + '][cantidad]'" :value="it.cantidad">
                        <input type="hidden" :name="'items[' + idx + '][codigo_proveedor]'" :value="it.codigo_proveedor || ''">
                        <input type="hidden" :name="'items[' + idx + '][talle_original]'" :value="it.talle_original || ''">
                        <input type="hidden" :name="'items[' + idx + '][color_original]'" :value="it.color_original || ''">
                        <input type="hidden" :name="'items[' + idx + '][talle]'" :value="it.talle || ''">
                        <input type="hidden" :name="'items[' + idx + '][color]'" :value="it.color || ''">
                        <input type="hidden" :name="'items[' + idx + '][rectificado]'" :value="it.rectificado ? '1' : '0'">
                        <input type="hidden" :name="'items[' + idx + '][nuevo_codigo_barra]'" :value="it.nuevo_codigo_barra || ''">
                    </div>
                </template>
            </div>

            {{-- 4. PIE DE ACCIONES Y CONFIRMACIÓN --}}
            <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    <p class="font-bold text-slate-700 dark:text-slate-200">Resumen de la Operación:</p>
                    <p class="mt-0.5">
                        Al confirmar, se descontarán las unidades en la sede emisora y se acreditarán automáticamente en la sede receptora con auditoría. Las prendas con rectificación crearán nuevo código habilitando la impresión de etiquetas.
                    </p>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="{{ route('productos.index') }}" 
                       class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:white bg-slate-100 dark:bg-slate-800 rounded-xl transition-all">
                        Cancelar
                    </a>

                    <button type="submit" 
                            :disabled="!puedeConfirmar()"
                            :class="puedeConfirmar() ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-lg cursor-pointer' : 'bg-slate-200 dark:bg-slate-700 text-slate-400 cursor-not-allowed'"
                            class="px-6 py-2.5 text-xs font-black rounded-xl transition-all flex items-center space-x-2 uppercase tracking-wide">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>CONFIRMAR Y MOVER LOTE (<span x-text="totalUnidades()"></span> UN.)</span>
                    </button>
                </div>
            </div>

        </form>

        {{-- MODAL INTERACTIVO: PREGUNTAR CANTIDAD Y RECTIFICAR TALLE & COLOR --}}
        <div x-show="modalCantidad" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             @keydown.escape.window="modalCantidad = false; focoScanner()">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-lg w-full p-6 border border-slate-200 dark:border-slate-700 space-y-4"
                 @click.outside="modalCantidad = false; focoScanner()">
                
                {{-- Encabezado Modal --}}
                <div class="flex items-start justify-between pb-3 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">
                            Artículo Seleccionado
                        </span>
                        <h3 class="text-sm font-black uppercase text-slate-900 dark:text-white tracking-tight mt-0.5"
                            x-text="itemPendiente ? itemPendiente.descripcion : ''"></h3>
                    </div>
                    <button type="button" @click="modalCantidad = false; focoScanner()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>

                {{-- Detalles rápidos del producto y stock disponible --}}
                <div class="flex items-center justify-between text-xs p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                    <div>
                        <span class="text-slate-400 block text-[10px]">CÓDIGO ACTUAL:</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="itemPendiente ? itemPendiente.codigo : ''"></span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 block text-[10px]">DISPONIBLE EN ORIGEN:</span>
                        <span class="font-mono font-black text-emerald-600 dark:text-emerald-400 text-sm"
                              x-text="itemPendiente ? (itemPendiente.stock_origen + ' ' + itemPendiente.unidad_medida) : ''"></span>
                    </div>
                </div>

                {{-- PANEL DE DESCOMPOSICIÓN DEL CÓDIGO (Regla: 3 Proveedor, 2 Talle, 2 Color, Resto Producto) --}}
                <div class="p-3 bg-blue-50/60 dark:bg-slate-900/90 rounded-xl border border-blue-200 dark:border-blue-800/80 space-y-2">
                    <div class="flex items-center justify-between text-[11px] font-bold text-blue-900 dark:text-blue-300">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            Descomposición del Código de Barras (Regla 3-2-2-X):
                        </span>
                        <span class="font-mono text-xs font-black text-blue-700 dark:text-blue-300" x-text="desgloseActual.codigo_completo"></span>
                    </div>

                    <div class="grid grid-cols-4 gap-1.5 text-center">
                        <div class="p-1.5 bg-white dark:bg-slate-800 rounded-lg border border-blue-200 dark:border-slate-700 shadow-xs">
                            <span class="block text-[9px] font-bold uppercase text-slate-400">1. Prov (3c)</span>
                            <span class="font-mono font-black text-xs text-blue-600 dark:text-blue-400" x-text="desgloseActual.prov"></span>
                        </div>
                        <div class="p-1.5 bg-white dark:bg-slate-800 rounded-lg border border-indigo-200 dark:border-slate-700 shadow-xs">
                            <span class="block text-[9px] font-bold uppercase text-slate-400">2. Talle (2c)</span>
                            <span class="font-mono font-black text-xs text-indigo-600 dark:text-indigo-400" x-text="desgloseActual.talle"></span>
                        </div>
                        <div class="p-1.5 bg-white dark:bg-slate-800 rounded-lg border border-rose-200 dark:border-slate-700 shadow-xs">
                            <span class="block text-[9px] font-bold uppercase text-slate-400">3. Color (2c)</span>
                            <span class="font-mono font-black text-xs text-rose-600 dark:text-rose-400" x-text="desgloseActual.color"></span>
                        </div>
                        <div class="p-1.5 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-xs">
                            <span class="block text-[9px] font-bold uppercase text-slate-400">4. Producto</span>
                            <span class="font-mono font-black text-xs text-slate-800 dark:text-slate-200 truncate block" x-text="desgloseActual.resto" :title="desgloseActual.resto"></span>
                        </div>
                    </div>
                </div>

                {{-- Input de Cantidad a mover --}}
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                        ¿Cuántas unidades desea agregar al envío? <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               x-ref="inputCantidadModal"
                               x-model="cantidadAEnviar"
                               step="1" 
                               min="1"
                               :max="itemPendiente ? itemPendiente.stock_origen : 9999"
                               @keydown.enter.prevent="confirmarAgregarAlLote()"
                               class="w-full text-base py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-black text-center focus:ring-2 focus:ring-amber-500">
                    </div>

                    {{-- Atajos rápidos de cantidad --}}
                    <div class="flex items-center justify-center gap-1.5 mt-1.5">
                        <button type="button" @click="cantidadAEnviar = 1" class="px-2.5 py-0.5 text-[11px] font-bold rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200">1 un.</button>
                        <button type="button" @click="cantidadAEnviar = 5" class="px-2.5 py-0.5 text-[11px] font-bold rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200">+5 un.</button>
                        <button type="button" @click="cantidadAEnviar = 10" class="px-2.5 py-0.5 text-[11px] font-bold rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200">+10 un.</button>
                        <button type="button" @click="cantidadAEnviar = itemPendiente ? itemPendiente.stock_origen : 1" class="px-2.5 py-0.5 text-[11px] font-bold rounded-lg bg-amber-100 dark:bg-amber-950 text-amber-900 dark:text-amber-300 hover:bg-amber-200">Todo el stock</button>
                    </div>
                </div>

                {{-- Campos Talle y Color: sugerencias y libre edición --}}
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                            <span>Talle</span>
                            <span class="text-[10px] font-mono text-slate-400" x-show="talleOriginal">Actual: <strong x-text="talleOriginal"></strong></span>
                        </label>
                        <input type="text" 
                               x-model="talleNuevo" 
                               @input="recalcularEnModal()"
                               list="lista-talles-sugeridos"
                               placeholder="Ej: 34, 42, M..."
                               class="w-full text-xs py-2 px-3 rounded-xl font-mono uppercase font-bold border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Sugerido o nuevo libre</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-slate-700 dark:text-slate-300 mb-1 flex items-center justify-between">
                            <span>Color</span>
                            <span class="text-[10px] font-mono text-slate-400" x-show="colorOriginal">Actual: <strong x-text="colorOriginal"></strong></span>
                        </label>
                        <input type="text" 
                               x-model="colorNuevo" 
                               @input="recalcularEnModal()"
                               list="lista-colores-sugeridos"
                               placeholder="Ej: NEGRO, AZUL, 46..."
                               class="w-full text-xs py-2 px-3 rounded-xl uppercase font-bold border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Sugerido o nuevo libre</span>
                    </div>
                </div>

                {{-- Previsualización reactiva del código resultante y aviso de rectificación --}}
                <div class="p-2.5 rounded-xl text-xs transition-colors"
                     :class="esRectificado 
                        ? 'bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700 text-amber-900 dark:text-amber-200' 
                        : 'bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200'">
                    
                    <template x-if="!esRectificado">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            <div>
                                <span class="font-bold">Mismo Código de Barra: </span>
                                <span class="font-mono font-black" x-text="desgloseActual.codigo_completo"></span>
                                <span class="block text-[10px] text-emerald-700 dark:text-emerald-300/80">Sin modificación de talle ni color; se trasladará normalmente con el mismo SKU.</span>
                            </div>
                        </div>
                    </template>

                    <template x-if="esRectificado">
                        <div class="space-y-1">
                            <div class="flex items-center gap-1.5 font-black text-amber-800 dark:text-amber-300">
                                <span class="text-sm">⚡</span>
                                <span>Rectificación detectada: Se generará un nuevo código de barra</span>
                            </div>
                            <div class="flex items-center justify-between font-mono bg-white/70 dark:bg-black/30 p-1.5 rounded-lg border border-amber-200 dark:border-amber-800">
                                <span class="text-slate-400 text-[10px]">Orig: <strong x-text="desgloseActual.codigo_completo"></strong> &rarr;</span>
                                <span class="text-xs font-black text-amber-700 dark:text-amber-300" x-text="nuevoCodigoGenerado"></span>
                            </div>
                            <p class="text-[10px] text-amber-800 dark:text-amber-300/90 leading-tight">
                                Se dará de baja el stock original en origen y se creará/acreditará como nuevo producto en destino con habilitación para imprimir etiquetas térmicas.
                            </p>
                        </div>
                    </template>
                </div>

                {{-- Error Modal --}}
                <p x-show="errorModal" class="text-rose-500 text-xs font-bold text-center" x-text="errorModal"></p>

                {{-- Botones de Acción Modal --}}
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" 
                            @click="modalCantidad = false; focoScanner()" 
                            class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700">
                        Cancelar (Esc)
                    </button>
                    <button type="button" 
                            @click="confirmarAgregarAlLote()" 
                            class="px-5 py-2 text-xs font-black text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md transition-all flex items-center gap-1.5 uppercase">
                        <span>Agregar al Lote</span>
                        <span class="text-[10px] font-mono opacity-80">(Enter)</span>
                    </button>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>