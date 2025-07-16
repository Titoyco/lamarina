<h1 class="text-3xl font-bold mb-4">Buscar productos:</h1>
<div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-4 sm:space-y-0 mb-4">

    <!-- Formulario de búsqueda -->
    <form action="{{ route('productos.index') }}" method="GET" class="flex flex-col sm:flex-row w-full space-y-2 sm:space-y-0 sm:space-x-2">
        <input 
            type="text" 
            name="query" 
            placeholder="Buscar por código, descripción, categoría, subcategoría o precio" 
            class="border border-gray-300 rounded-md p-2 w-full sm:w-80" 
            required
        >
        <x-button-submit>
            BUSCAR
        </x-button-submit>
        <label class="flex items-center">
            <input type="checkbox" name="in_stock" class="mr-2" {{ $in_stock ? 'checked' : '' }}>
            Sólo productos con stock
        </label>
    </form>

    <!-- Botón de nuevo producto -->
    <x-button-submit class="w-full sm:w-80">
        <x-slot name='onclick'>
            onclick="window.location.href='{{route('productos.create')}}'"   
        </x-slot>
        Nuevo Producto
    </x-button-submit>
</div>

<!-- Listado de productos -->
@if($productos->isEmpty())
    <p>No hay productos para mostrar.</p>
@else
    <div class="overflow-x-auto min-h-screen">
        <table class="border table-fixed border-gray-500 min-w-full text-sm">
            <thead>
                <tr class="bg-slate-400">
                    @php
                        $columns = [
                            'codigo' => 'Código',
                            'descripcion' => 'Descripción',
                            'categoria' => 'Categoría',
                            'subcategoria' => 'Subcategoría',
                            'precio_venta' => 'Precio',
                            'stock' => 'Stk'
                        ];
                        $sort_order = $sort_order === 'asc' ? 'desc' : 'asc';
                    @endphp

                    @foreach ($columns as $column => $label)
                        <th class="border border-gray-200 px-2 py-2 w-15">
                            <a href="{{ route('productos.index', ['sort_by' => $column, 'sort_order' => $sort_order, 'query' => $query, 'in_stock' => $in_stock]) }}">
                                {{ $label }}
                            </a>
                        </th>
                    @endforeach
                    <th class="border border-gray-200 px-4 py-2 w-32">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos as $producto)
                    
                    <tr class="hover:border hover:border-gray-500 hover:bg-slate-400 transition duration-200 text-xs">
                        <td class="border border-gray-200 px-2 py-2 w-28"><a href="{{ route('productos.show', $producto) }}">{{ $producto->codigo }}</a></td>
                        <td class="border border-gray-200 px-2 py-2 w-96">{{ $producto->descripcion }}</td>
                        <td class="border border-gray-200 px-2 py-2 w-48">{{ $producto->categoria->nombre }}</td>
                        <td class="border border-gray-200 px-2 py-2 w-48">{{ $producto->subcategoria->nombre }}</td>
                        <td class="border border-gray-200 px-2 py-2 w-20">$ {{ $producto->precio_venta }}</td>
                        <td class="border border-gray-200 px-2 py-2 w-14">{{ $producto->stock }}</td>
                        <td class="border border-gray-200 px-4 py-2 w-24 justify-center items-center relative">
                            <div class="flex justify-center items-center">
                            <button 
                                onclick="toggleMenu(event, '{{ $producto->id }}')" 
                                class="bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-700"
                            >
                                Opciones
                            </button>
                            </div>
                            <!-- Menú desplegable -->
                            <div id="menu-{{ $producto->id }}" class="hidden absolute top-full left-0 bg-white border border-gray-300 rounded-md shadow-lg z-10 w-40">
                                <ul>
                                    @php
                                        $botones = [
                                            ['url' => route('productos.edit', $producto), 'texto' => 'Editar'],
                                            ['url' => route('productos.barcode', $producto), 'texto' => 'Imprimir Etiqueta'], // Cambia '#' por la ruta correspondiente
                                            ['url' => '#', 'texto' => 'Modificar Stock'], // Cambia '#' por la ruta correspondiente
                                        ];
                                    @endphp
                                    @foreach ($botones as $boton)
                                        <li>
                                            <a 
                                                href="{{ $boton['url'] }}" 
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 transition"
                                                                @if ($boton['texto'] === 'Imprimir Etiqueta')
                                                                onclick="abrirImpresionEtiqueta('{{ $boton['url'] }}'); return false;"
                                                                @endif
                                            >
                                                {{ $boton['texto'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                    <li>
                                        <form action="{{ route('productos.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-200 transition">
                                                Eliminar
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Enlaces de paginación -->
    <div class="text-xs mt-4">
        {{ $productos->links() }}
    </div>

@endif

<script>
    function abrirImpresionEtiqueta(url) {
        // Abre una nueva ventana para la impresión
        const nuevaVentana = window.open(url, '_blank', 'width=800,height=600');

        // Espera a que la nueva ventana cargue completamente antes de ejecutar la impresión
        nuevaVentana.onload = function () {
            nuevaVentana.print();
        };
    }
</script>