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

       <!--  incluyo el la busqueda   -->

       @include('creditos.buscar')


    </div>
</x-app-layout>