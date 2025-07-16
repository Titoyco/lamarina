<h1 class="text-3xl font-bold mb-4">Buscar clientes:</h1>
<div class="bg-white flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-4 sm:space-y-0 mb-4 p-4 rounded-md shadow-md">

    <!-- Formulario de búsqueda -->
    <form action="{{ route('clientes.index') }}" method="GET" class="flex flex-col sm:flex-row w-full space-y-2 sm:space-y-0 sm:space-x-2">
        <input 
            type="text" 
            name="query" 
            placeholder="Buscar por nombre o DNI" 
            class="border border-gray-300 rounded-md p-2 w-full sm:w-80" 
            required
        >
        <x-button-submit>
            BUSCAR
        </x-button-submit>
    </form>

    <!-- Botón de nuevo cliente -->
    <x-button-submit class="w-full sm:w-80">
        <x-slot name='onclick'>
            onclick="window.location.href='{{ route('clientes.create') }}'"   
        </x-slot>
        Nuevo Cliente
    </x-button-submit>
</div>

<!-- Listado de clientes -->
@if($clientes->isEmpty())
    <p class="bg-white p-4 rounded-md shadow-md">No hay clientes para mostrar.</p>
@else
    <div class="overflow-x-auto relative bg-white p-4 rounded-md shadow-md min-h-[500px]">
        <table class="border table-fixed bg-white border-gray-500 min-w-full text-sm">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                    <th class="border border-gray-200 px-4 py-2 w-24"> </th>
                    <th class="border border-gray-200 px-4 py-2 w-32">DNI</th>
                    <th class="border border-gray-200 px-4 py-2 w-max">Nombre</th>
                    <th class="border border-gray-200 px-4 py-2 w-48">Acciones</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-xs font-light">
                @foreach($clientes as $cliente)
                    <tr class="border-b border-gray-200 hover:bg-gray-100 transition duration-200">
                        <td class="border border-gray-200 px-4 py-2">Titular</td>
                        <td class="border border-gray-200 px-4 py-2">{{ $cliente->dni }}</td>
                        <td class="border border-gray-200 px-4 py-2">{{ $cliente->nombre }}</td>
                        <td class="border border-gray-200 px-4 py-2 w-48 justify-center items-center relative">
                            <div class="flex justify-center items-center">
                                <button 
                                    onclick="toggleMenu(event, '{{ $cliente->id }}')" 
                                    class="bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-700"
                                >
                                    Opciones
                                </button>
                            </div>
                            <!-- Menú desplegable -->
                            <div id="menu-{{ $cliente->id }}" class="hidden absolute top-full left-0 bg-white border border-gray-300 rounded-md shadow-lg z-50 w-40">
                                <ul>
                                    @php
                                        $botones = [
                                            ['url' => route('clientes.edit', $cliente), 'texto' => 'Editar'],
                                            ['url' => '#', 'texto' => 'Bloquear'], // Cambia '#' por la ruta correspondiente
                                            ['url' => '#', 'texto' => 'Autorizados'], // Cambia '#' por la ruta correspondiente
                                            ['url' => route('clientes.movimientos', $cliente), 'texto' => 'Movimientos'],
                                            ['url' => route('clientes.nuevoCredito', $cliente), 'texto' => 'Nuevo Crédito'], // Cambia '#' por la ruta correspondiente
                                        ];
                                    @endphp
                                    @foreach ($botones as $boton)
                                        <li>
                                            <a 
                                                href="{{ $boton['url'] }}" 
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 transition"
                                            >
                                                {{ $boton['texto'] }}
                                            </a>
                                        </li>
                                    @endforeach
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
        {{ $clientes->links() }}
    </div>
@endif