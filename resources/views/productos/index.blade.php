@php
$botones = [
    ['url' => 'javascript:history.back()', 'texto' => ' < Volver', 'color' => 'bg-gray-500'],
    ['url' => route('productos.create'), 'texto' => 'Agregar Producto', 'color' => 'bg-blue-500'],
    ['url' => route('productos.categorias.index'), 'texto' => 'Listar Categorias', 'color' => 'bg-green-500'],
    ['url' => route('productos.subcategorias.index'), 'texto' => 'Listar Subcategorias', 'color' => 'bg-yellow-500'],
];
@endphp

<x-app-layout :botones="$botones">
    <div class="container">

        <x-errores>
            <!-- ACA SE MUESTRAN LOS ERRORES -->
        </x-errores>

       <!--  incluyo el la busqueda   -->

       @include('productos.buscar')


    </div>
</x-app-layout>