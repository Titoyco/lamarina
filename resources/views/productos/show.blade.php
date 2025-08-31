@php
$botones = [
    ['url' => 'javascript:history.back()', 'texto' => ' < Volver', 'color' => 'bg-gray-500'],
]; 
@endphp

<x-app-layout :botones="$botones">
<div class="container">

    <x-errores>
        <!-- ACA SE MUESTRAN LOS ERRORES -->
    </x-errores>

    <h1><strong>Detalles del Producto:</strong></h1><br>
    <div class="mb-4">
        <p><strong>Código:</strong> {{ $producto->codigo }}</p>
        <p><strong>Descripción:</strong> {{ $producto->descripcion }}</p>
        <p><strong>Observaciones:</strong> {{ $producto->observaciones }}</p>
        <p><strong>Categoría:</strong> {{ $producto->categoria->nombre ?? 'N/A' }}</p>
        <p><strong>Subcategoría:</strong> {{ $producto->subcategoria->nombre ?? 'N/A' }}</p>
        <p><strong>Proveedor:</strong> {{ $producto->proveedor->nombre ?? 'N/A' }}</p>
        
        <h5><strong>Stock por Sucursal:</strong></h5>
        <ul>
            @forelse($producto->sucursales as $sucursal)
                <li>
                    <strong>{{ $sucursal->nombre ?? 'Sucursal' }}:</strong>
                    Stock: {{ $sucursal->pivot->stock }},
                    Punto de Compra: {{ $sucursal->pivot->punto_compra }},
                    Precio de Venta: ${{ $sucursal->pivot->precio_venta }}
                </li>
            @empty
                <li>No hay sucursales asociadas a este producto.</li>
            @endforelse
        </ul>
    </div>
</div>
</x-app-layout>