@php
$botones = [
    ['url' => 'javascript:history.back()', 'texto' => ' < Volver', 'color' => 'bg-gray-500'],
];
@endphp

<x-app-layout :botones="$botones">
    <div class="container">
        <h1 class="text-3xl font-bold mb-4">Nueva Factura</h1>
        
        <form id="ventaForm" action="{{ route('ventas.store') }}" method="POST">
            @csrf

            <!-- Selección de Cliente -->
            <div id="cliente" class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Seleccione Cliente</label>
                <div class="flex mb-2">
                    <input type="text" id="dniCliente" class="mt-1 block w-1/2 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="DNI del Cliente">
                    <button type="button" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-blue-500 text-white ml-2" onclick="mostrarModal('modalBuscarCliente')">Buscar Clientes</button>
                </div>
                <div id="clienteSeleccionado" class="mt-2">
                    <!-- Aquí se mostrará el cliente seleccionado -->
                </div>
            </div>

            <!-- Selección de Productos -->
            <!-- Campo para Código de Barras -->
            <div id="productos" class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Seleccione Productos</label>
                <div class="flex mb-2">
                    <!-- Campo de texto para Código de Barras -->
                    <input 
                        type="text" 
                        id="codigoBarras" 
                        class="mt-1 block w-1/2 py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                        placeholder="Código de Barras"
                        autofocus
                    >
                    <!-- Botón para abrir el Modal de Buscar Productos -->
                    <button 
                        type="button" 
                        class="py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-blue-500 text-white ml-2" 
                        onclick="mostrarModal('modalAgregarProducto')"
                    >
                        Buscar Productos
                    </button>
                </div>
            </div>

            <!-- Lista de Productos Seleccionados -->
            <div id="lista-productos" class="mb-4">
                <table class="min-w-full border-collapse block md:table">
                    <thead class="block md:table-header-group">
                        <tr class="border border-gray-300 md:border-none block md:table-row absolute -top-full md:top-auto -left-full md:left-auto md:relative">
                            <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Producto</th>
                            <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Cantidad</th>
                            <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Precio</th>
                            <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Subtotal</th>
                            <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="block md:table-row-group">
                        <!-- Aquí se añadirán los productos seleccionados -->
                    </tbody>
                </table>
            </div>

            <!-- Total de la Factura -->
            <div class="mb-4">
                <label for="total" class="block text-sm font-medium text-gray-700">Total de la Factura</label>
                <input type="text" name="total" id="total" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:outline-none sm:text-sm" readonly>
            </div>

            <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Crear Factura
            </button>
        </form>
    </div>

    <!-- Modal para Buscar Clientes -->
    <div id="modalBuscarCliente" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" onclick="cerrarModalExterior(event)">
        <div class="relative top-20 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white" onclick="event.stopPropagation()">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Buscar Cliente</h3>
                <button type="button" class="absolute top-0 right-0 mt-3 mr-3 text-gray-500 border-2 rounded-md bg-red-400" onclick="ocultarModal('modalBuscarCliente')">
                    <strong>&nbsp; &times; &nbsp;</strong>
                </button>

                <div class="mt-2 px-7 py-3">
                    <div class="mb-4">
                        <input type="text" id="buscarCliente" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Buscar cliente por DNI o Nombre...">
                    </div>
                    <div id="listaClientes">
                        <table class="min-w-full border-collapse block md:table">
                            <thead class="block md:table-header-group">
                                <tr class="border border-gray-300 md:border-none block md:table-row absolute -top-full md:top-auto -left-full md:left-auto md:relative">
                                    <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">DNI</th>
                                    <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Nombre</th>
                                    <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="block md:table-row-group">
                                <!-- Aquí se mostrarán los clientes encontrados -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Buscar y Agregar Productos -->
    <div id="modalAgregarProducto" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" onclick="cerrarModalExterior(event)">
        <div class="relative top-20 mx-auto p-5 border w-[800px] shadow-lg rounded-md bg-white" onclick="event.stopPropagation()">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Buscar Producto</h3>
                <button type="button" class="absolute top-0 right-0 mt-3 mr-3 text-gray-500 border-2 rounded-md bg-red-400" onclick="ocultarModal('modalAgregarProducto')">
                    <strong>&nbsp; &times; &nbsp;</strong>
                </button>

                <div class="mt-2 px-7 py-3">
                    <div class="mb-4">
                        <input type="text" id="buscarProducto" class="mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Buscar producto...">
                    </div>
                    <div id="listaProductos">
                        <table class="min-w-full border-collapse block md:table">
                            <thead class="block md:table-header-group">
                                <tr class="border border-gray-300 md:border-none block md:table-row absolute -top-full md:top-auto -left-full md:left-auto md:relative">
                                    <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Producto</th>
                                    <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Stock</th>
                                    <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Precio</th>
                                    <th class="bg-gray-200 p-2 text-gray-600 font-bold md:border md:border-gray-300 text-left block md:table-cell">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="block md:table-row-group">
                                <!-- Aquí se mostrarán los productos encontrados -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('buscarProducto').addEventListener('input', function() {
            const query = this.value;
            fetch(`{{ route('productos.search') }}?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    const listaProductos = document.querySelector('#listaProductos tbody');
                    listaProductos.innerHTML = '';
                    data.forEach(producto => {
                        const productRow = document.createElement('tr');
                        productRow.classList.add('border', 'border-gray-300', 'md:border-none', 'block', 'md:table-row');
                        productRow.innerHTML = `
                            <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">${producto.descripcion}</td>
                            <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">${producto.stock}</td>
                            <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">${producto.precio_venta}</td>
                            <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">
                                <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-md ml-2 focus:outline-none" onclick="agregarProducto(${producto.id}, '${producto.descripcion}', ${producto.precio_venta}, ${producto.stock})">Agregar</button>
                            </td>
                        `;
                        listaProductos.appendChild(productRow);
                    });
                });
        });

        document.getElementById('codigoBarras').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Evitar el comportamiento predeterminado del Enter
                const barcode = this.value.trim(); // Capturar el valor del código de barras
                if (barcode) {
                    // Buscar el producto por código de barras
                    fetch(`{{ route('productos.searchcod') }}?query=${barcode}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                // Producto encontrado: agregarlo a la lista
                                const producto = data[0];
                                agregarProducto(producto.id, producto.descripcion, producto.precio_venta, producto.stock);
                                this.value = ''; // Limpiar el campo de entrada
                                this.focus(); // Mantener el foco en el campo de código de barras
                            } else {
                                // Producto no encontrado: mostrar alerta
                                alert('Producto no encontrado');
                                this.focus(); // Mantener el foco en el campo de código de barras
                            }
                        });
                }
            }
        });

        document.getElementById('dniCliente').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                const dni = this.value.trim();
                if (dni) {
                    fetch(`{{ route('clientes.searchdni') }}?dni=${dni}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length > 0) {
                                const cliente = data[0];
                                seleccionarCliente(cliente.id, cliente.nombre, cliente.dni);
                                this.value = ''; // Limpiar el campo de DNI del cliente
                                document.getElementById('codigoBarras').focus(); // Poner el foco al input de codico de barras del cliente
                            } else {
                                alert('Cliente no encontrado');
                                this.focus(); // Volver el foco al input de DNI del cliente
                            }
                        });
                }
            }
        });

        function seleccionarCliente(id, nombre, dni) {
            const clienteSeleccionadoDiv = document.getElementById('clienteSeleccionado');
            clienteSeleccionadoDiv.innerHTML = `
                <input type="hidden" name="cliente_id" value="${id}">
                <div class="p-2 border border-gray-300 rounded-md">
                    <span class="font-bold">DNI:</span> ${dni} <span class="font-bold">Cliente:</span> ${nombre}
                </div>
            `;
            ocultarModal('modalBuscarCliente');
            document.getElementById('codigoBarras').focus(); // Poner el foco al input de código de barras
            
        }

        function agregarProducto(id, descripcion, precio, stock) {
            const productosDiv = document.querySelector('#lista-productos tbody');
            const productRow = document.createElement('tr');
            productRow.classList.add('border', 'border-gray-300', 'md:border-none', 'block', 'md:table-row');
            productRow.innerHTML = `
                <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">
                    <input type="hidden" name="productos_id[]" value="${id}">
                    <input type="hidden" name="cantidades[]" class="cantidad-hidden" value="1">
                    <input type="text" class="product-description mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:outline-none sm:text-sm" value="${descripcion}" readonly>
                </td>
                <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">
                    <input type="number" min="1" max="${stock}" class="product-quantity mt-1 block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none sm:text-sm" value="1" onchange="actualizarSubtotal(this, ${precio})">
                </td>
                <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">
                    <input type="text" class="product-precio mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:outline-none sm:text-sm" value="${precio.toFixed(2)}" readonly>
                </td>
                <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">
                    <input type="text" class="product-subtotal mt-1 block w-full py-2 px-3 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:outline-none sm:text-sm" value="${precio.toFixed(2)}" readonly>
                </td>
                <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">
                    <button type="button" class="remove-product ml-2 mt-1 py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-red-500 text-white" onclick="removerProducto(this)">Eliminar</button>
                </td>
            `;
            productosDiv.appendChild(productRow);
            actualizarTotal();
            ocultarModal('modalAgregarProducto');
        }

        function actualizarSubtotal(input, precio) {
            const cantidad = input.value;
            const subtotalInput = input.closest('tr').querySelector('.product-subtotal');
            const hiddenCantidadInput = input.closest('tr').querySelector('.cantidad-hidden');
            const subtotal = cantidad * precio;
            hiddenCantidadInput.value = cantidad;
            subtotalInput.value = subtotal.toFixed(2);
            actualizarTotal();
        }

        function actualizarTotal() {
            const productosDiv = document.querySelector('#lista-productos tbody');
            const productRows = productosDiv.querySelectorAll('tr');
            let total = 0;
            productRows.forEach(row => {
                const subtotal = parseFloat(row.querySelector('.product-subtotal').value);
                total += subtotal;
            });
            document.getElementById('total').value = total.toFixed(2);
        }

        function removerProducto(button) {
            const productRow = button.closest('tr');
            productRow.remove();
            actualizarTotal();
        }

        function mostrarModal(idModal) {
    const modal = document.getElementById(idModal);
    if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden'); // Mostrar el modal
    }
}

        function ocultarModal(idModal) {
            const modal = document.getElementById(idModal);
            if (!modal.classList.contains('hidden')) {
                modal.classList.add('hidden'); // Ocultar el modal
            }
        }

        function cerrarModalExterior(event) {
            const modalBuscarCliente = document.getElementById('modalBuscarCliente');
            const modalAgregarProducto = document.getElementById('modalAgregarProducto');

            if (event.target === modalBuscarCliente) {
                ocultarModal('modalBuscarCliente');
            } else if (event.target === modalAgregarProducto) {
                ocultarModal('modalAgregarProducto');
            }
        }

        document.getElementById('buscarCliente').addEventListener('input', function() {
            const query = this.value;
            fetch(`{{ route('clientes.search') }}?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    const listaClientes = document.querySelector('#listaClientes tbody');
                    listaClientes.innerHTML = '';
                    data.forEach(cliente => {
                        const clienteRow = document.createElement('tr');
                        clienteRow.classList.add('border', 'border-gray-300', 'md:border-none', 'block', 'md:table-row');
                        clienteRow.innerHTML = `
                            <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">${cliente.dni}</td>
                            <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">${cliente.nombre}</td>
                            <td class="p-2 md:border md:border-gray-300 text-left block md:table-cell">
                                <button type="button" class="bg-blue-500 text-white px-4 py-2 rounded-md ml-2 focus:outline-none" onclick="seleccionarCliente(${cliente.id}, '${cliente.nombre}', '${cliente.dni}')">Seleccionar</button>
                            </td>
                        `;
                        listaClientes.appendChild(clienteRow);
                    });
                });
        });
    </script>
</x-app-layout>