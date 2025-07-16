@php
$botones = [
]; 
@endphp

<x-app-layout :botones="$botones">
    <div class="container">

        <x-errores>
            <!-- ACA SE MUESTRAN LOS ERRORES -->
           
        </x-errores>

       <!--     -->

         @include('clientes.buscar')
    </div>
    </x-app-layout>