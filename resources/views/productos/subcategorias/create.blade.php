@php
$botones = [
    ['url' => 'javascript:history.back()', 'texto' => ' < Volver', 'color' => 'bg-gray-500'],
    ['url' => route('productos.subcategorias.create'), 'texto' => 'Agregar Subcategoría', 'color' => 'bg-blue-500'], // Botón para agregar una nueva subcategoría
]; 
@endphp

<x-app-layout :botones="$botones">
    <div class="container mx-auto p-6">
        <x-errores>
            <!-- ACA SE MUESTRAN LOS ERRORES -->
        </x-errores>

        <h1 class="text-3xl font-semibold text-gray-800 mb-6">Agregar Nueva Subcategoría</h1>

        <form action="{{ route('productos.subcategorias.store') }}" method="POST">
            @csrf

            <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="mt-1 block w-full border border-gray-300 rounded py-2 px-3" required>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Guardar
                </button>
                <a href="{{ route('productos.subcategorias.index') }}" class="ml-2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>