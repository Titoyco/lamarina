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

       <!--     -->

      <h1 class="text-2xl font-bold">Ventas</h1>

       <!-- Botón de nuevo cliente -->
       <x-button-submit class="w-full sm:w-80">
        <x-slot name='onclick'>
            onclick="window.location.href='{{ route('ventas.create') }}'"   
        </x-slot>
        Nueva Venta
    </x-button-submit>


       
    </div>
    </x-app-layout>