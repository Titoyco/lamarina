<x-app-layout>
    <!-- Script y Configuración Alpine.js del Remito -->
    <script>
        window.remitoConfig = {
            sucursalId: '{{ $sucursalActivaId }}',
            proveedorId: '{{ $proveedorSeleccionadoId ?? '' }}',
            remitoId: '{{ $remitoCargar->id ?? '' }}',
            numeroRemito: '{{ $remitoCargar->numero_remito ?? '' }}',
            fechaHoy: '{{ $remitoCargar ? \Carbon\Carbon::parse($remitoCargar->fecha_recepcion)->format('Y-m-d') : date('Y-m-d') }}',
            estado: '{{ $remitoCargar->estado ?? 'completado' }}',
            observaciones: {!! json_encode($remitoCargar->observaciones ?? '') !!},
            itemsIniciales: {!! json_encode($itemsPrecargados ?? []) !!},
            searchUrl: '{{ route('remitos.buscar-productos') }}',
            crearProductoUrl: '{{ route('remitos.crear-producto-rapido') }}',
            proveedoresUrl: '{{ route('proveedores.store') }}',
            siguienteLegajo: '{{ $siguienteLegajoProveedor ?? '' }}',
            proveedores: {!! json_encode($proveedoresList ?? []) !!}
        };

        function remitoForm(cfg) {
            const config = cfg || window.remitoConfig || {};
            return {
                remitoId: config.remitoId || '',
                sucursalId: config.sucursalId,
                proveedorId: config.proveedorId,
                listaProveedores: config.proveedores || [],
                fechaRecepcion: config.fechaHoy,
                numeroRemito: config.numeroRemito || '',
                observaciones: config.observaciones || '',
                busqueda: '',
                resultados: [],
                buscando: false,
                busquedaRealizada: false,
                items: config.itemsIniciales || [],
                modalNuevo: false,
                guardandoNuevo: false,
                nuevoProdError: '',
                nuevoProd: {
                    descripcion: '',
                    codigo: '',
                    codigo_barra: '',
                    codigo_proveedor: '',
                    talle: '',
                    color: '',
                    categoria_id: '',
                    subcategoria_id: '',
                    precio_costo: 0,
                    precio_venta: 0
                },
                modalProveedorAbierto: false,
                guardandoProv: false,
                errorProv: '',
                nuevoProv: {
                    nombre: '',
                    legajo: config.siguienteLegajo || '',
                    cuit: '',
                    tel1: '',
                    direccion: ''
                },

                cargarRemitoParcialSeleccionado(id) {
                    if (!id) return;
                    window.location.href = "{{ route('remitos.create') }}?remito_id=" + id;
                },

                onCambioProveedor() {
                    this.busqueda = '';
                    this.resultados = [];
                    this.busquedaRealizada = false;
                    if (this.proveedorId) {
                        this.buscarProductos();
                    }
                },

                buscarProductos() {
                    if (!this.proveedorId) {
                        this.resultados = [];
                        this.busquedaRealizada = false;
                        return;
                    }
                    const term = this.busqueda.trim();
                    this.buscando = true;
                    this.busquedaRealizada = true;

                    const url = `${config.searchUrl}?q=${encodeURIComponent(term)}&sucursal_id=${this.sucursalId}&proveedor_id=${this.proveedorId}`;

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            this.resultados = Array.isArray(data) ? data : [];
                            this.buscando = false;
                        })
                        .catch(() => {
                            this.resultados = [];
                            this.buscando = false;
                        });
                },

                onEnterBusqueda() {
                    if (!this.proveedorId) {
                        alert('Selecciona primero un proveedor en el Paso 1 para cargar o buscar artículos.');
                        return;
                    }
                    const term = this.busqueda.trim();
                    if (term.length === 0) return;

                    // Si ya hay un único resultado visible, agregarlo de inmediato
                    if (this.resultados.length === 1) {
                        this.agregarItem(this.resultados[0]);
                        return;
                    }

                    // Si hay varios resultados, buscar si alguno coincide exactamente con código o barra
                    if (this.resultados.length > 1) {
                        const cleanTerm = term.toUpperCase().replace(/[^A-Z0-9]/g, '');
                        const exact = this.resultados.find(r => 
                            String(r.codigo || '').toUpperCase().replace(/[^A-Z0-9]/g, '') === cleanTerm ||
                            String(r.codigo_barra || '').toUpperCase().replace(/[^A-Z0-9]/g, '') === cleanTerm ||
                            String(r.codigo_proveedor || '').toUpperCase() === term.toUpperCase()
                        );
                        if (exact) {
                            this.agregarItem(exact);
                            return;
                        }
                        // De lo contrario, tomar el primer resultado
                        this.agregarItem(this.resultados[0]);
                        return;
                    }

                    // Si no había resultados aún (por debounce), buscar de inmediato
                    this.buscando = true;
                    fetch(`${config.searchUrl}?q=${encodeURIComponent(term)}&sucursal_id=${this.sucursalId}&proveedor_id=${this.proveedorId}`)
                        .then(res => res.json())
                        .then(data => {
                            this.buscando = false;
                            this.resultados = Array.isArray(data) ? data : [];
                            if (this.resultados.length >= 1) {
                                this.agregarItem(this.resultados[0]);
                            } else {
                                this.busquedaRealizada = true;
                            }
                        })
                        .catch(() => {
                            this.buscando = false;
                        });
                },

                agregarItem(producto) {
                    if (!producto) return;

                    // Si no había proveedor seleccionado en el encabezado, auto-seleccionarlo del producto
                    if (!this.proveedorId && producto.proveedor_id) {
                        this.proveedorId = String(producto.proveedor_id);
                    }

                    // Si ya está en la lista, incrementa cantidad
                    const exist = this.items.find(i => i.producto_id === producto.id);
                    if (exist) {
                        exist.cantidad = Number((Number(exist.cantidad) + 1).toFixed(2));
                    } else {
                        const costo = Number(producto.precio_costo) || 0;
                        const sugerido = Math.round(costo * 2 * 100) / 100;
                        const venta = Number(producto.precio_venta) > 0 ? Number(producto.precio_venta) : sugerido;

                        this.items.push({
                            producto_id: producto.id,
                            codigo: producto.codigo,
                            codigo_barra: producto.codigo_barra,
                            codigo_proveedor: producto.codigo_proveedor,
                            talle: producto.talle,
                            color: producto.color,
                            descripcion: producto.descripcion,
                            stock_actual: producto.stock_actual,
                            cantidad: 1,
                            precio_compra: costo,
                            precio_venta: venta,
                            precio_venta_original: producto.precio_venta,
                            precio_venta_sugerido: sugerido
                        });
                    }
                    this.busqueda = '';
                    this.resultados = [];
                    this.busquedaRealizada = false;

                    this.$nextTick(() => {
                        const input = document.getElementById('input_busqueda_articulo');
                        if (input) {
                            input.focus();
                        }
                    });
                },

                actualizarSugerido(row) {
                    row.precio_venta_sugerido = Math.round(Number(row.precio_compra) * 2 * 100) / 100;
                },

                quitarItem(index) {
                    this.items.splice(index, 1);
                },

                calcularTotalUnidades() {
                    return this.items.reduce((acc, i) => acc + (Number(i.cantidad) || 0), 0);
                },

                calcularTotalCosto() {
                    const sum = this.items.reduce((acc, i) => acc + ((Number(i.cantidad) || 0) * (Number(i.precio_compra) || 0)), 0);
                    return sum.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                },

                calcularTotalVenta() {
                    const sum = this.items.reduce((acc, i) => acc + ((Number(i.cantidad) || 0) * (Number(i.precio_venta) || 0)), 0);
                    return sum.toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                },

                abrirModalNuevo() {
                    if (!this.proveedorId) {
                        alert('Selecciona primero el proveedor en el encabezado del remito.');
                        return;
                    }
                    this.nuevoProd = {
                        descripcion: '',
                        codigo: '',
                        codigo_barra: '',
                        codigo_proveedor: '',
                        talle: '',
                        color: '',
                        categoria_id: '',
                        subcategoria_id: '',
                        precio_costo: 0,
                        precio_venta: 0
                    };
                    this.nuevoProdError = '';
                    this.modalNuevo = true;
                },

                abrirModalNuevoConBusqueda() {
                    const txt = (this.busqueda || '').trim();
                    this.abrirModalNuevo();
                    if (txt) {
                        if (/^\d{8,14}$/.test(txt)) {
                            this.nuevoProd.codigo = txt;
                            this.nuevoProd.codigo_barra = txt;
                        } else if (/^[A-Za-z0-9\-]{1,8}$/.test(txt) && !txt.includes(' ')) {
                            this.nuevoProd.codigo_proveedor = txt.toUpperCase();
                        } else {
                            this.nuevoProd.descripcion = txt.toUpperCase();
                        }
                    }
                },

                guardarNuevoProducto() {
                    if (!this.nuevoProd.descripcion.trim()) {
                        this.nuevoProdError = 'Ingresa la descripción del artículo.';
                        return;
                    }
                    if (!this.proveedorId) {
                        this.nuevoProdError = 'Selecciona un proveedor para asociar el artículo.';
                        return;
                    }

                    this.guardandoNuevo = true;
                    this.nuevoProdError = '';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                    fetch(config.crearProductoUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            descripcion: this.nuevoProd.descripcion,
                            codigo: this.nuevoProd.codigo,
                            codigo_barra: this.nuevoProd.codigo_barra,
                            codigo_proveedor: this.nuevoProd.codigo_proveedor,
                            talle: this.nuevoProd.talle,
                            color: this.nuevoProd.color,
                            categoria_id: this.nuevoProd.categoria_id || null,
                            subcategoria_id: this.nuevoProd.subcategoria_id || null,
                            proveedor_id: this.proveedorId,
                            precio_costo: this.nuevoProd.precio_costo,
                            precio_venta: this.nuevoProd.precio_venta
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.guardandoNuevo = false;
                        if (data.success && data.producto) {
                            this.agregarItem(data.producto);
                            this.modalNuevo = false;
                        } else {
                            this.nuevoProdError = data.message || 'Error al guardar el producto.';
                        }
                    })
                    .catch(err => {
                        this.guardandoNuevo = false;
                        this.nuevoProdError = 'Error de conexión con el servidor.';
                    });
                },

                guardarNuevoProveedor() {
                    if (!this.nuevoProv.nombre.trim()) {
                        this.errorProv = 'El nombre o razón social es obligatorio.';
                        return;
                    }
                    this.guardandoProv = true;
                    this.errorProv = '';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

                    fetch(config.proveedoresUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            nombre: this.nuevoProv.nombre,
                            legajo: this.nuevoProv.legajo,
                            cuit: this.nuevoProv.cuit,
                            tel1: this.nuevoProv.tel1,
                            direccion: this.nuevoProv.direccion
                        })
                    })
                    .then(async res => {
                        const data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.message || 'Error al registrar proveedor');
                        }
                        return data;
                    })
                    .then(res => {
                        this.guardandoProv = false;
                        if (res.success && res.proveedor) {
                            const p = res.proveedor;
                            const provItem = {
                                id: p.id,
                                nombre: p.nombre,
                                legajo: p.legajo_formateado || String(p.legajo).padStart(3, '0')
                            };
                            this.listaProveedores.push(provItem);
                            const sel = document.getElementById('select_proveedor_id');
                            if (sel) {
                                const opt = document.createElement('option');
                                opt.value = p.id;
                                opt.textContent = (provItem.legajo ? '[' + provItem.legajo + '] ' : '') + provItem.nombre;
                                opt.selected = true;
                                sel.appendChild(opt);
                            }
                            this.proveedorId = p.id;
                            this.modalProveedorAbierto = false;
                            this.nuevoProv = { nombre: '', legajo: '', cuit: '', tel1: '', direccion: '' };
                        }
                    })
                    .catch(err => {
                        this.guardandoProv = false;
                        this.errorProv = err.message || 'Error al conectar con el servidor';
                    });
                },

                validarEnvio(e) {
                    if (this.items.length === 0) {
                        e.preventDefault();
                        alert('Debes agregar al menos un artículo al remito.');
                    }
                }
            };
        }
    </script>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8" x-data="remitoForm()">

        <!-- Encabezado -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700 mb-6 gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('remitos.index') }}" 
                   class="bg-white dark:bg-slate-800 p-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        Cargar Factura / Remito de Mercadería
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        Carga por factura o remito de proveedor: agregue artículos por código o descripción, continúe cargas parciales en etapas y actualice stock por diferencia.
                    </p>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('productos.create') }}" 
                   class="inline-flex items-center gap-1.5 text-xs font-black text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 dark:hover:bg-blue-900/60 border border-blue-200 dark:border-blue-800 px-3.5 py-1.5 rounded-lg shadow-2xs transition-all">
                    <span>🏷️ Carga Prenda a Prenda (Unitario CODE128)</span> &rarr;
                </a>
                <button type="button" @click="modalProveedorAbierto = true"
                   class="text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 px-3 py-1.5 rounded-lg shadow-2xs hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center gap-1">
                    <span>+ Alta Proveedor Nuevo</span>
                </button>
            </div>
        </div>

        {{-- SECCIÓN DE CARGA EN ETAPAS (TRAER FACTURA CON CARGA PARCIAL O COMPROBANTES ANTERIORES) --}}
        <div class="mb-6 p-4 rounded-3xl border transition-all shadow-sm"
             :class="remitoId ? 'bg-amber-50/90 dark:bg-amber-950/40 border-amber-300 dark:border-amber-700 text-amber-950 dark:text-amber-100' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200'">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-2xl flex items-center justify-center font-bold text-lg shrink-0 shadow-2xs"
                          :class="remitoId ? 'bg-amber-200 text-amber-900 dark:bg-amber-800 dark:text-amber-100' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'">
                        ⚡
                    </span>
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider flex items-center gap-2">
                            <span x-show="!remitoId">Carga en Etapas: Traer Factura / Remito Anterior</span>
                            <span x-show="remitoId">Continuando Carga en Etapas de Remito #<span x-text="numeroRemito"></span></span>
                            <span x-show="remitoId" class="text-[10px] font-mono bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100 px-2 py-0.5 rounded-md font-bold">
                                Editando comprobante previo
                            </span>
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                            <span x-show="!remitoId">Seleccione un comprobante previo para seguir agregándole prendas. Al guardar, el sistema actualizará el stock por la diferencia de unidades automáticamente.</span>
                            <span x-show="remitoId">Al guardar, el sistema calculará la diferencia (delta) entre las cantidades previas y las nuevas para ajustar el stock físico con exactitud.</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 flex-wrap">
                    @if(isset($remitosAnteriores) && $remitosAnteriores->count() > 0)
                        <div x-show="!remitoId" class="flex items-center gap-2">
                            <select @change="cargarRemitoParcialSeleccionado($event.target.value)"
                                    class="text-xs font-bold py-1.5 px-3 rounded-xl border-amber-300 dark:border-amber-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white shadow-2xs">
                                <option value="">-- Seleccionar comprobante para continuar carga --</option>
                                @foreach($remitosAnteriores as $ra)
                                    <option value="{{ $ra->id }}">
                                        {{ $ra->estado === 'parcial' ? '🟡 [PARCIAL]' : '🟢 [COMPLETO]' }} Remito #{{ $ra->numero_remito }} ({{ Str::limit($ra->proveedor?->nombre, 20) }}) — {{ $ra->items->count() }} ítems [{{ \Carbon\Carbon::parse($ra->fecha_recepcion)->format('d/m/Y') }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div x-show="!remitoId" class="text-xs text-slate-500 italic bg-slate-50 dark:bg-slate-900/60 px-3 py-1.5 rounded-xl border border-dashed border-slate-300 dark:border-slate-700">
                            Aún no hay comprobantes guardados (aparecerán aquí al cargar el primero).
                        </div>
                    @endif

                    <div x-show="remitoId">
                        <a href="{{ route('remitos.create') }}" 
                           class="text-xs font-bold text-rose-700 dark:text-rose-300 hover:text-rose-900 bg-white dark:bg-slate-900 border border-rose-300 dark:border-rose-800 px-3 py-1.5 rounded-xl shadow-2xs transition-all">
                            ✕ Iniciar Factura Nueva en Blanco
                        </a>
                    </div>

                    <a href="{{ route('remitos.index') }}" 
                       class="text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 px-3 py-1.5 rounded-xl shadow-2xs transition-all flex items-center gap-1">
                        <span>Ver Todos los Remitos</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('remitos.store') }}" method="POST" @submit="validarEnvio($event)">
            @csrf
            <input type="hidden" name="remito_id" :value="remitoId">

            <!-- 1. ENCABEZADO DEL REMITO -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-sm mb-6">
                <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-100 dark:border-slate-700/60">
                    <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 flex items-center justify-center font-black text-xs">1</span>
                    <h2 class="text-xs font-black uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                        Datos del Comprobante y Proveedor
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Fecha Recepción -->
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">
                            Fecha de Recepción <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="fecha_recepcion" x-model="fechaRecepcion" required
                               class="w-full text-xs font-mono font-bold rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3 shadow-sm">
                    </div>

                    <!-- Número de Remito / Factura -->
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">
                            N° de Factura / Remito <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="numero_remito" x-model="numeroRemito" placeholder="Ej: FC-0001-00028475" required
                               class="w-full text-xs font-mono font-bold uppercase rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3 shadow-sm">
                    </div>

                    <!-- Proveedor -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[10px] font-black uppercase text-slate-500">
                                Proveedor Emisor <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" @click="modalProveedorAbierto = true"
                                    class="text-[10px] font-bold text-blue-600 dark:text-blue-400 hover:underline">
                                + Alta Rápida
                            </button>
                        </div>
                        <select name="proveedor_id" id="select_proveedor_id" x-model="proveedorId" @change="onCambioProveedor()" required
                                class="w-full text-xs font-bold rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3 shadow-sm">
                            <option value="">-- Seleccione el proveedor --</option>
                            @foreach($proveedores as $prov)
                                <option value="{{ $prov->id }}" {{ (string)$prov->id === (string)($proveedorSeleccionadoId ?? '') ? 'selected' : '' }}>
                                    {{ $prov->legajo ? "[{$prov->legajo}] " : '' }}{{ $prov->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sede de Stock Destino -->
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">
                            Sucursal de Ingreso <span class="text-rose-500">*</span>
                        </label>
                        <select name="sucursal_id" x-model="sucursalId" required
                                class="w-full text-xs font-bold rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3 shadow-sm">
                            @foreach($sucursales as $suc)
                                <option value="{{ $suc->id }}">
                                    {{ $suc->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Observaciones -->
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">
                            Observaciones de la Recepción (Opcional)
                        </label>
                        <input type="text" name="observaciones" x-model="observaciones" placeholder="Detalles de bultos, precintos, transportista o entrega parcial..."
                               class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3">
                    </div>
                </div>
            </div>

            <!-- 2. BÚSQUEDA Y AGREGADO DE ARTÍCULOS -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-sm mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-2 border-b border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 flex items-center justify-center font-black text-xs">2</span>
                        <h2 class="text-xs font-black uppercase text-slate-800 dark:text-slate-200 tracking-wider">
                            Renglones de Mercadería a Ingresar
                        </h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="abrirModalNuevo()"
                                class="px-3.5 py-1.5 text-xs font-black uppercase text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 rounded-xl hover:bg-indigo-100 transition shadow-sm flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Nuevo Artículo en Catálogo</span>
                        </button>
                    </div>
                </div>

                <!-- Buscador de Artículos Existentes -->
                <div class="relative mb-5" @click.away="resultados = []; busquedaRealizada = false">
                    <div class="relative">
                        <input type="text" id="input_busqueda_articulo"
                               x-model="busqueda" 
                               @input.debounce.200ms="buscarProductos()"
                               @focus="if (proveedorId && resultados.length === 0) buscarProductos()"
                               @keydown.enter.prevent="onEnterBusqueda()"
                               @keydown.escape="resultados = []; busquedaRealizada = false"
                               :disabled="!proveedorId"
                               :placeholder="proveedorId ? 'Buscar por Código de Barra, Código de Fábrica (ej: 755), Talle o Descripción de este proveedor...' : '⚠️ Seleccione primero un proveedor arriba para buscar o cargar artículos...'"
                               class="w-full text-xs py-2.5 pl-10 pr-10 rounded-2xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-medium shadow-inner focus:ring-2 focus:ring-blue-500 disabled:opacity-60 disabled:cursor-not-allowed">

                        <!-- Icono Búsqueda o Spinner de Carga -->
                        <div class="absolute left-3.5 top-3 text-slate-400">
                            <template x-if="!buscando">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </template>
                            <template x-if="buscando">
                                <svg class="animate-spin w-4 h-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </template>
                        </div>

                        <!-- Botón Limpiar Búsqueda -->
                        <button type="button" x-show="busqueda.length > 0" 
                                @click="busqueda = ''; if(proveedorId) { buscarProductos(); } else { resultados = []; busquedaRealizada = false; } document.getElementById('input_busqueda_articulo')?.focus()"
                                class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 p-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Mensaje indicativo si no seleccionó proveedor -->
                    <template x-if="!proveedorId">
                        <p class="text-[11px] text-amber-600 dark:text-amber-400 font-semibold mt-1.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Selecciona el proveedor en el Paso 1 para habilitar la búsqueda y ver únicamente sus artículos.
                        </p>
                    </template>

                    <!-- Dropdown de Resultados Encontrados -->
                    <div x-show="resultados.length > 0" 
                         class="absolute z-50 left-0 right-0 mt-1.5 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-2xl max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700">
                        <div class="px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between text-[11px] text-slate-500 font-bold">
                            <span>Resultados encontrados (<span x-text="resultados.length"></span>) - Clic o presione ENTER para agregar:</span>
                            <span class="text-[10px] text-blue-600 uppercase font-mono">[ENTER = Agregar primer resultado]</span>
                        </div>
                        <template x-for="item in resultados" :key="item.id">
                            <div @click="agregarItem(item)" 
                                 class="p-3 hover:bg-blue-50 dark:hover:bg-slate-700/60 cursor-pointer flex items-center justify-between transition group">
                                <div class="flex-1 min-w-0 pr-3">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono font-black text-xs text-blue-700 dark:text-blue-400 group-hover:underline" x-text="item.codigo"></span>
                                        <span class="text-xs font-bold text-slate-900 dark:text-white" x-text="item.descripcion"></span>
                                        <span x-show="item.proveedor && !proveedorId" class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 font-bold" x-text="item.proveedor"></span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400 flex-wrap">
                                        <template x-if="item.talle">
                                            <span class="px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold font-mono">T: <span x-text="item.talle"></span></span>
                                        </template>
                                        <template x-if="item.color">
                                            <span class="px-1.5 py-0.2 rounded bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 font-bold">C: <span x-text="item.color"></span></span>
                                        </template>
                                        <template x-if="item.codigo_proveedor">
                                            <span class="px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono">Fab: <span x-text="item.codigo_proveedor"></span></span>
                                        </template>
                                        <span x-text="'Barra: ' + (item.codigo_barra || item.codigo)"></span>
                                        <span>•</span>
                                        <span x-text="item.categoria"></span>
                                        <span>•</span>
                                        <span class="font-bold text-slate-600 dark:text-slate-300" x-text="'Stock actual: ' + item.stock_actual + ' u.'"></span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-[10px] text-slate-400 block">Costo / Venta actual:</span>
                                    <span class="text-xs font-mono font-black text-slate-800 dark:text-slate-200" 
                                          x-text="'$' + Number(item.precio_costo).toFixed(2) + ' / $' + Number(item.precio_venta).toFixed(2)"></span>
                                    <span class="inline-block mt-0.5 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.2 rounded">
                                        + Agregar al remito
                                    </span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Mensaje cuando no hay resultados -->
                    <div x-show="busqueda.trim().length > 0 && !buscando && resultados.length === 0 && busquedaRealizada"
                         class="absolute z-50 left-0 right-0 mt-1.5 p-3.5 bg-amber-50 dark:bg-amber-950/80 border border-amber-200 dark:border-amber-800 rounded-2xl text-xs text-amber-900 dark:text-amber-200 shadow-xl flex items-center justify-between gap-3">
                        <div>
                            <div class="font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>No se encontraron artículos para "<span x-text="busqueda" class="font-mono underline"></span>"</span>
                            </div>
                            <p class="text-[11px] text-amber-700 dark:text-amber-300 mt-0.5">
                                Puede ingresar el código de fábrica, código de barra o parte de la descripción, o dar de alta la prenda nueva de inmediato.
                            </p>
                        </div>
                        <button type="button" @click="abrirModalNuevoConBusqueda()"
                                class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-sm shrink-0 flex items-center gap-1">
                            <span>+ Alta de Prenda Nueva</span>
                        </button>
                    </div>
                </div>

                <!-- Tabla de Renglones Cargados -->
                <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-700">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/80 dark:bg-slate-700/60 text-slate-500 dark:text-slate-400 font-black uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                                <th class="py-2.5 px-3">Artículo</th>
                                <th class="py-2.5 px-2 text-center w-28">Cantidad</th>
                                <th class="py-2.5 px-2 text-right w-36">Precio Compra ($)</th>
                                <th class="py-2.5 px-2 text-right w-44">P. Venta Sugerido (x2)</th>
                                <th class="py-2.5 px-2 text-right w-40">Precio Venta ($)</th>
                                <th class="py-2.5 px-3 text-right w-32">Subtotal Costo</th>
                                <th class="py-2.5 px-2 text-center w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700 font-medium">
                            <template x-for="(row, idx) in items" :key="row.producto_id">
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30">
                                    <!-- Artículo -->
                                    <td class="py-2.5 px-3">
                                        <input type="hidden" :name="'items[' + idx + '][producto_id]'" :value="row.producto_id">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-black text-blue-700 dark:text-blue-400 text-xs" x-text="row.codigo"></span>
                                            <span class="font-bold text-slate-900 dark:text-white" x-text="row.descripcion"></span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400 font-mono flex-wrap">
                                            <template x-if="row.talle">
                                                <span class="px-1.5 py-0.2 rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold">T: <span x-text="row.talle"></span></span>
                                            </template>
                                            <template x-if="row.color">
                                                <span class="px-1.5 py-0.2 rounded bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 font-bold">C: <span x-text="row.color"></span></span>
                                            </template>
                                            <template x-if="row.codigo_proveedor">
                                                <span class="px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">Fab: <span x-text="row.codigo_proveedor"></span></span>
                                            </template>
                                            <span x-text="'Stock: ' + row.stock_actual + ' unid.'"></span>
                                        </div>
                                    </td>

                                    <!-- Cantidad -->
                                    <td class="py-2.5 px-2 text-center">
                                        <input type="number" min="0.01" step="any" required
                                               :name="'items[' + idx + '][cantidad]'"
                                               x-model.number="row.cantidad"
                                               class="w-24 text-center font-mono font-bold text-xs py-1.5 px-2 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm focus:border-blue-500">

                                        <template x-if="row.cantidad_guardada_previa !== undefined">
                                            <div class="mt-1 text-[9px] font-mono leading-tight">
                                                <span class="text-slate-400 block">Previa: <strong x-text="row.cantidad_guardada_previa"></strong></span>
                                                <template x-if="Number(row.cantidad) !== Number(row.cantidad_guardada_previa)">
                                                    <span class="inline-block px-1 rounded font-black mt-0.5"
                                                          :class="(Number(row.cantidad) - Number(row.cantidad_guardada_previa)) > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'"
                                                          x-text="((Number(row.cantidad) - Number(row.cantidad_guardada_previa)) > 0 ? '+' : '') + (Number(row.cantidad) - Number(row.cantidad_guardada_previa)).toFixed(2) + ' dif'">
                                                    </span>
                                                </template>
                                            </div>
                                        </template>
                                    </td>

                                    <!-- Precio de Compra (Costo) -->
                                    <td class="py-2.5 px-2 text-right">
                                        <input type="number" min="0" step="0.01" required
                                               :name="'items[' + idx + '][precio_compra]'"
                                               x-model.number="row.precio_compra"
                                               @input="actualizarSugerido(row)"
                                               class="w-32 text-right font-mono font-bold text-xs py-1.5 px-2 rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm focus:border-blue-500">
                                    </td>

                                    <!-- Sugerido x2 con botón rápido -->
                                    <td class="py-2.5 px-2 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <span class="font-mono text-xs font-bold text-indigo-600 dark:text-indigo-400"
                                                  x-text="'$' + (row.precio_compra * 2).toFixed(2)"></span>
                                            <button type="button" @click="row.precio_venta = Math.round(row.precio_compra * 2 * 100) / 100"
                                                    title="Aplicar sugerido (x2)"
                                                    class="px-1.5 py-0.5 text-[9px] font-black uppercase rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100">
                                                Usar x2
                                            </button>
                                        </div>
                                        <span class="text-[9px] text-slate-400 block" x-text="'Actual: $' + Number(row.precio_venta_original || 0).toFixed(2)"></span>
                                    </td>

                                    <!-- Precio Venta Final Editable -->
                                    <td class="py-2.5 px-2 text-right">
                                        <input type="number" min="0" step="0.01" required
                                               :name="'items[' + idx + '][precio_venta]'"
                                               x-model.number="row.precio_venta"
                                               class="w-32 text-right font-mono font-black text-xs py-1.5 px-2 rounded-lg border-emerald-300 dark:border-emerald-700 bg-emerald-50/30 dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 shadow-sm focus:border-emerald-500">
                                    </td>

                                    <!-- Subtotal Costo -->
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-white">
                                        <span x-text="'$' + (row.cantidad * row.precio_compra).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                                    </td>

                                    <!-- Quitar Renglón -->
                                    <td class="py-2.5 px-2 text-center">
                                        <button type="button" @click="quitarItem(idx)" 
                                                class="text-slate-400 hover:text-rose-600 transition p-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="items.length === 0">
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 italic text-xs">
                                        No hay artículos agregados aún. Usa el buscador de arriba o el botón "+ Nuevo Artículo en Catálogo".
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 3. BARRA INFERIOR DE TOTALES Y BOTONES DE CONFIRMACIÓN -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700/80 p-5 shadow-lg flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <!-- Métricas acumuladas -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <span class="text-[10px] font-black uppercase text-slate-400 block">Artículos</span>
                        <span class="text-xl font-black text-slate-900 dark:text-white" x-text="items.length + ' renglones'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase text-slate-400 block">Total Unidades</span>
                        <span class="text-xl font-black text-slate-900 dark:text-white font-mono" x-text="calcularTotalUnidades()"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase text-blue-700 dark:text-blue-400 block">Total Costo Compra</span>
                        <span class="text-xl font-black text-blue-900 dark:text-blue-300 font-mono" x-text="'$' + calcularTotalCosto()"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-black uppercase text-emerald-600 block">Total Venta Estimado</span>
                        <span class="text-xl font-black text-emerald-600 font-mono" x-text="'$' + calcularTotalVenta()"></span>
                    </div>
                </div>

                <!-- Botones de Acción (Parcial vs Completo) -->
                <div class="flex items-center gap-3 justify-end">
                    <!-- Guardar Parcial -->
                    <button type="submit" name="estado" value="parcial"
                            :disabled="items.length === 0"
                            class="px-4 py-2.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 font-black text-xs uppercase tracking-wide transition shadow-sm disabled:opacity-50">
                        Guardar Entrega Parcial
                    </button>

                    <!-- Guardar Completo -->
                    <button type="submit" name="estado" value="completado"
                            :disabled="items.length === 0"
                            class="px-5 py-2.5 rounded-xl bg-blue-700 hover:bg-blue-800 text-white font-black text-xs uppercase tracking-wider transition shadow-md disabled:opacity-50 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Recepcionar y Finalizar Remito</span>
                    </button>
                </div>
            </div>

        </form>

        <!-- MODAL PARA CREAR NUEVO ARTÍCULO ON-THE-FLY -->
        <div x-show="modalNuevo" style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full p-6 text-left"
                 @click.away="modalNuevo = false">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700 mb-4">
                    <div>
                        <h3 class="text-base font-black uppercase text-slate-900 dark:text-white">
                            Alta Rápida de Artículo
                        </h3>
                        <p class="text-xs text-slate-500">Crea el producto para incorporarlo directamente a este remito.</p>
                    </div>
                    <button type="button" @click="modalNuevo = false" class="text-slate-400 hover:text-slate-600 text-lg font-black">&times;</button>
                </div>

                <div class="space-y-3.5">
                    <!-- Descripción -->
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">
                            Descripción / Nombre del Artículo <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="nuevoProd.descripcion" placeholder="Ej: REMERA ESTAMPADA ALGODÓN TALLE M"
                               class="w-full text-xs font-bold uppercase rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <!-- Código Fábrica -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Cód. Fábrica / Proveedor</label>
                            <input type="text" x-model="nuevoProd.codigo_proveedor" placeholder="Ej: ART-99"
                                   class="w-full text-xs font-mono font-bold uppercase rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3">
                        </div>

                        <!-- Talle -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Talle (1-70 / XS-8XL)</label>
                            <input type="text" x-model="nuevoProd.talle" list="remito_talles_list" placeholder="Ej: 42, M, XL"
                                   class="w-full text-xs font-mono font-bold uppercase rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3">
                            <datalist id="remito_talles_list">
                                @foreach(App\Models\Producto::listaTallesSugeridos() as $t)
                                    <option value="{{ $t }}"></option>
                                @endforeach
                            </datalist>
                        </div>

                        <!-- Color -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Color</label>
                            <input type="text" x-model="nuevoProd.color" list="remito_colores_list" placeholder="Ej: Negro, Azul"
                                   class="w-full text-xs font-bold uppercase rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3">
                            <datalist id="remito_colores_list">
                                <option value="NEGRO"></option>
                                <option value="BLANCO"></option>
                                <option value="AZUL"></option>
                                <option value="ROJO"></option>
                                <option value="VERDE"></option>
                                <option value="AMARILLO"></option>
                                <option value="GRIS"></option>
                                <option value="ROSA"></option>
                                <option value="BEIGE"></option>
                                <option value="MARRON"></option>
                            </datalist>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Código Interno -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Código Interno / SKU</label>
                            <input type="text" x-model="nuevoProd.codigo" placeholder="Vacío para generar automático"
                                   class="w-full text-xs font-mono font-bold uppercase rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3">
                        </div>

                        <!-- Código de Barras -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Código de Barras (CODE128)</label>
                            <input type="text" x-model="nuevoProd.codigo_barra" placeholder="Vacío para estructurado CODE128..."
                                   class="w-full text-xs font-mono font-bold rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <!-- Categoría -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Categoría</label>
                            <select x-model="nuevoProd.categoria_id"
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-2.5">
                                <option value="">General</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subcategoría -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">Subcategoría</label>
                            <select x-model="nuevoProd.subcategoria_id"
                                    class="w-full text-xs rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-2.5">
                                <option value="">Sin Subcategoría</option>
                                @foreach($subcategorias as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <!-- Precio Costo -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-500 mb-1">
                                Precio Compra ($) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" min="0" step="0.01" x-model.number="nuevoProd.precio_costo"
                                   @input="nuevoProd.precio_venta = Math.round(nuevoProd.precio_costo * 2 * 100) / 100"
                                   placeholder="0.00"
                                   class="w-full text-xs font-mono font-bold text-right rounded-xl border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white py-2 px-3">
                        </div>

                        <!-- Precio Venta Sugerido (x2) y editable -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-emerald-600 mb-1">
                                Precio Venta Público ($) (Sugerido x2)
                            </label>
                            <input type="number" min="0" step="0.01" x-model.number="nuevoProd.precio_venta"
                                   placeholder="0.00"
                                   class="w-full text-xs font-mono font-black text-right rounded-xl border-emerald-300 dark:border-emerald-700 bg-emerald-50/40 dark:bg-slate-900 text-emerald-800 dark:text-emerald-300 py-2 px-3">
                        </div>
                    </div>

                    <template x-if="nuevoProdError">
                        <p class="text-xs text-rose-600 font-bold" x-text="nuevoProdError"></p>
                    </template>
                </div>

                <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-700 mt-5">
                    <button type="button" @click="modalNuevo = false"
                            class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800">
                        Cancelar
                    </button>
                    <button type="button" @click="guardarNuevoProducto()"
                            :disabled="guardandoNuevo"
                            class="px-4 py-2 text-xs font-black uppercase text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md transition disabled:opacity-50">
                        <span x-text="guardandoNuevo ? 'Guardando...' : 'Crear y Agregar al Remito'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL RÁPIDO: ALTA DE NUEVO PROVEEDOR -->
        <div x-show="modalProveedorAbierto" 
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 w-full max-w-lg p-5 shadow-2xl space-y-4"
                 @click.away="modalProveedorAbierto = false">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>+ Registrar Nuevo Proveedor</span>
                    </h3>
                    <button type="button" @click="modalProveedorAbierto = false" class="text-slate-400 hover:text-slate-600 text-lg font-black">&times;</button>
                </div>

                <div x-show="errorProv" class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 text-rose-800 dark:text-rose-300 text-xs font-bold" x-text="errorProv"></div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nombre / Razón Social <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" x-model="nuevoProv.nombre" required placeholder="Ej: TEXTIL PATAGONIA S.A."
                               class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 uppercase font-bold focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Legajo (3 dígitos) <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="nuevoProv.legajo" required maxlength="10" placeholder="Ej: 015"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono font-bold text-center">
                            <span class="text-[9px] text-slate-400">Prefijo para etiquetas</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                CUIT
                            </label>
                            <input type="text" x-model="nuevoProv.cuit" placeholder="XX-XXXXXXXX-X"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Teléfono / WhatsApp
                            </label>
                            <input type="text" x-model="nuevoProv.tel1" placeholder="Ej: 280-4123456"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                Dirección / Localidad
                            </label>
                            <input type="text" x-model="nuevoProv.direccion" placeholder="Ej: Av. Mitre 540"
                                   class="w-full text-xs py-2 px-3 rounded-xl border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="modalProveedorAbierto = false" 
                            class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 bg-slate-100 dark:bg-slate-700 rounded-xl">
                        Cancelar
                    </button>
                    <button type="button" @click="guardarNuevoProveedor()" :disabled="guardandoProv"
                            class="px-5 py-2 text-xs font-black text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md flex items-center gap-1.5 disabled:opacity-50">
                        <template x-if="guardandoProv">
                            <svg class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </template>
                        <span>Guardar y Seleccionar</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
