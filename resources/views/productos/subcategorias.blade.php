@php
$botones = [
    ['url' => 'javascript:history.back()', 'texto' => ' < Volver', 'color' => 'bg-gray-500'],
    ['url' => route('subcategorias.create'), 'texto' => 'Agregar Subcategoría', 'color' => 'bg-blue-500'], // Botón para agregar una nueva subcategoría
]; 
@endphp

<x-app-layout :botones="$botones">
    <div class="container mx-auto p-6">
        <x-errores>
            <!-- ACA SE MUESTRAN LOS ERRORES -->
        </x-errores>

        <h1 class="text-3xl font-semibold text-gray-800 mb-6">Listado de Subcategorías</h1>
            
        <a href="{{ route('subcategorias.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded shadow hover:bg-blue-600">
        Agregar SubCategoría
    </a> 
        
       <table class="table-auto w-full mt-6 border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2">#</th>
                    <th class="border border-gray-300 px-4 py-2">Nombre</th>
                    <th class="border border-gray-300 px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subcategorias as $subcategoria)
                <tr>
                    <td class="border border-gray-300 px-4 py-2">{{ $loop->iteration }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $subcategoria->nombre }}</td>
                    <td class="border border-gray-300 px-4 py-2">
                        <div class="flex space-x-2">
                            <a href="{{ route('subcategorias.edit', $subcategoria->id) }}" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                Editar
                            </a>
                            <form action="{{ route('subcategorias.destroy', $subcategoria->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta subcategoría?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>