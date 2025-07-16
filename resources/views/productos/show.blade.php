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
                <p><strong>Stock:</strong> {{ $producto->stock }}</p>
                <p><strong>Punto de Compra:</strong> {{ $producto->punto_compra }}</p>
                <p><strong>Precio de Compra:</strong> {{ $producto->precio_compra }}</p>
                <p><strong>IVA:</strong> {{ $producto->iva }}</p>
                <p><strong>Descuento:</strong> {{ $producto->descuento }}</p>
                <p><strong>Ganancia:</strong> {{ $producto->ganancia }}</p>
                <p><strong>Precio de Venta:</strong> $ {{ $producto->precio_venta }}</p>
            </div>
    </div>
</x-app-layout>