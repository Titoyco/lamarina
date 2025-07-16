<h1 class="text-3xl font-bold mb-4">Buscar créditos:</h1>
<div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-4 sm:space-y-0 mb-4">

    <!-- Formulario de búsqueda -->
    <form action="{{ route('creditos.index') }}" method="GET" class="flex flex-col sm:flex-row w-full space-y-2 sm:space-y-0 sm:space-x-2">
        <input 
            type="text" 
            name="query" 
            placeholder="Buscar por id, dni, nombre" 
            class="border border-gray-300 rounded-md p-2 w-full sm:w-80" 
            value="{{ $query }}"
        >
        <label class="flex items-center">
            <input type="checkbox" name="activo" class="mr-2" {{ $activo ? 'checked' : '' }}>
            Sólo créditos activos
        </label>
        <!-- Select para terminales -->
<!-- Select para terminales/sucursales -->
        <select name="sucursal_id" class="border border-gray-300 rounded-md p-2 w-40 mb-4">
            <option value="">Todas las sucursales</option>
            @foreach (auth()->user()->sucursales as $sucursal)
                <option value="{{ $sucursal->id }}"
                    {{ (old('sucursal_id', request('sucursal_id')) == $sucursal->id) ? 'selected' : '' }}>
                    {{ $sucursal->nombre }}
                </option>
            @endforeach
        </select>
        <x-button-submit>
            BUSCAR
        </x-button-submit>
    </form>

    <!-- Botón de nuevo crédito -->
    <x-button-submit class="w-full sm:w-48">
        <x-slot name='onclick'>
            onclick="window.location.href='{{ route('creditos.create') }}'"
        </x-slot>
        Nuevo Crédito
    </x-button-submit>
</div>

<!-- Listado de créditos -->
@if($creditos->isEmpty())
    <p>No hay créditos para mostrar.</p>
@else
    <div class="overflow-x-auto">
        <table class="border table-fixed border-gray-500 min-w-full text-sm">
            <thead>
                <tr class="bg-slate-400">
                    @php
                        $columns = [
                            'fecha_credito' => 'Fecha',
                            'dni' => 'DNI',
                            'nombre' => 'Nombre',
                            'credito' => 'ID',
                            'importe' => 'Importe',
                            'valor_cuota' => 'Val_Cuotas',
                            'cantidad_cuotas' => 'CC',
                            'fecha_cancelacion' => 'Cancelado'
                        ];
                        $sort_order = $sort_order === 'asc' ? 'desc' : 'asc';
                    @endphp

                    @foreach ($columns as $column => $label)
                        <th class="border border-gray-200 px-2 py-2">
                            <a href="{{ route('creditos.index', ['sort_by' => $column, 'sort_order' => $sort_order, 'query' => $query, 'activo' => $activo, 'sucursal_id'=> request('sucursal_id')]) }}">
                                {{ $label }}
                            </a>
                        </th>
                    @endforeach
                    <th class="border border-gray-200 px-4 py-2 w-32">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creditos as $credito)
                    <tr class="hover:border hover:border-gray-500 hover:bg-slate-400 transition duration-200 text-xs">
                        <td class="border border-gray-200 px-2 py-2 w-20"><a href="{{ route('creditos.show', $credito) }}">{{ $credito->fecha_credito }}</a></td>
                        <td class="border border-gray-200 px-2 py-2">{{ $credito->cliente->dni }}</td>
                        <td class="border border-gray-200 px-2 py-2 w-48">{{ $credito->cliente->nombre }}</td>
                        <td class="border border-gray-200 px-2 py-2">{{ $credito->credito }} {{ $credito->codigo_sucursal }}{{ $credito->tipo_credito }}</td>
                        <td class="border border-gray-200 px-2 py-2">$ {{ $credito->importe }}</td>
                        <td class="border border-gray-200 px-2 py-2">$ {{ $credito->valor_cuota }}</td>
                        <td class="border border-gray-200 px-2 py-2 w-10 text-center">{{ $credito->cantidad_cuotas }}</td>
                        <td class="border border-gray-200 px-2 py-2 w-20">{{ $credito->fecha_cancelacion }}</td>
                        <td class="border border-gray-200 px-2 py-2 relative">
                            <div class="flex justify-center items-center">
                                <!-- Botón para abrir el menú -->
                                <button 
                                    onclick="toggleMenu(event, '{{ $credito->id }}')" 
                                    class="bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-700"
                                >
                                    Opciones
                                </button>

                                <!-- Menú desplegable -->
                                <div id="menu-{{ $credito->id }}" class="hidden absolute top-full left-0 bg-white border border-gray-300 rounded-md shadow-lg z-10 w-40">
                                    <ul>
                                        @php
                                            $botones = [
                                                ['url' => '#', 'texto' => 'Pasar'], // Cambia '#' por la ruta correspondiente
                                                ['url' => '#', 'texto' => 'Cancelar'], // Cambia '#' por la ruta correspondiente
                                                ['url' => route('creditos.imprimirPagare', $credito), 'texto' => 'Imprimir Pagaré'], // Cambia '#' por la ruta correspondiente
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
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Enlaces de paginación -->
    <div class="text-xs mt-4">
        {{ $creditos->links() }}
    </div>
@endif

