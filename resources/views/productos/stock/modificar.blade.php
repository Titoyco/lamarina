@php
$botones = [
    ['url' => 'javascript:history.back()', 'texto' => ' < Volver', 'color' => 'bg-gray-500'],
    ['url' => '#', 'texto' => 'Agregar Stock', 'color' => 'bg-blue-500'], // Botón para agregar una nueva categoría
]; 
@endphp

<x-app-layout :botones="$botones">
<div class="container max-w-lg mx-auto mt-8">
    <h1 class="text-2xl font-bold mb-6">Modificar Stock</h1>
    <form method="POST" action="{{ route('productos.stock.modificar', $producto->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label class="block text-gray-700">ID del Producto</label>
            <input type="text" class="form-control bg-gray-200" value="{{ $producto->codigo }}" readonly>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Descripción</label>
            <input type="text" class="form-control bg-gray-200" value="{{ $producto->descripcion }}" readonly>
        </div>
        <div class="mb-4">
            <label for="stock" class="block text-gray-700">Stock actual (Sucursal: {{ $sucursal->nombre }})</label>
            <input
                type="number"
                name="stock"
                id="stock"
                class="form-control"
                value="{{ old('stock', $stock_actual) }}"
                min="0"
                step="1"
                required
            >
        </div>
        <button type="submit" class="btn btn-primary w-full">Guardar</button>
    </form>
</div>
</x-app-layout>