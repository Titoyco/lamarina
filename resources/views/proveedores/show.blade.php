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


            <h1><strong>Detalles del Proveedor:</strong></h1><br>
            <p><strong>Nombre:</strong> {{ $proveedor->nombre }}</p>
            <p><strong>Dirección:</strong> {{ $proveedor->direccion }}</p>
            <p><strong>Teléfono 1:</strong> {{ $proveedor->tel1 }}</p>
            <p><strong>Teléfono 2:</strong> {{ $proveedor->tel2 }}</p>
            <p><strong>CUIT:</strong> {{ $proveedor->cuit }}</p>
            <p><strong>Email:</strong> {{ $proveedor->email }}</p>
            <p><strong>Saldo:</strong> {{ $proveedor->Saldo }}</p>
            <p><strong>Web:</strong> {{ $proveedor->web }}</p>
            <p><strong>Comentario:</strong> {{ $proveedor->comentario }}</p>
    </div>
</x-app-layout>