@php
$botones = [
    ['url' => route('clientes.index'), 'texto' => 'Clientes', 'color' => 'bg-gray-500'],
    ['url' => route('clientes.exportarFicha', $cliente), 'texto' => 'Exportar a PDF', 'color' => 'bg-gray-500'],
    ['url' => route('clientes.edit', $cliente), 'texto' => 'Editar', 'color' => 'bg-gray-500'],
    ['url' => '#', 'texto' => 'Bloquear', 'color' => 'bg-gray-500'], // Cambia '#' por la ruta correspondiente
    ['url' => '#', 'texto' => 'Autorizados', 'color' => 'bg-gray-500'], // Cambia '#' por la ruta correspondiente
    ['url' => route('clientes.movimientos', $cliente), 'texto' => 'Movimientos', 'color' => 'bg-gray-500'],
    ['url' => '#', 'texto' => 'Nuevo Credito', 'color' => 'bg-gray-500'] // Cambia '#' por la ruta correspondiente
]; 
@endphp
<x-app-layout :botones="$botones">

    <x-errores>
        <!-- ACA SE MUESTRAN LOS ERRORES -->
    </x-errores>

    <div class="bg-white p-2 min-h-screen w-full">
        <h1 class="text-xl font-bold text-cyan-950 mb-4">Movimientos de {{ $cliente->nombre }} Dni: {{ $cliente->dni }}</h1>

        <h2 class="text-xl font-bold text-cyan-950 mb-4">
            Saldo a favor: $ {{ number_format($cliente->saldo, 2, ',', '.') }}
        </h2>

        <h2 class="text-xl font-bold text-cyan-950 mb-4">
            Total adeudado: $ {{ number_format($deudaTotal, 2, ',', '.') }}
        </h2>
        <br>

        <h2 class="text-xl font-bold mb-4">Créditos Activos:</h2>

        @if($creditos_pendientes->isEmpty())
            <p>No hay créditos activos disponibles.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                            <th class="py-3 px-6 text-left">Crédito</th>
                            <th class="py-3 px-6 text-left">Fecha</th>
                            <th class="py-3 px-6 text-left">Cuota</th>
                            <th class="py-3 px-6 text-left">Val. de Cuota</th>
                            <th class="py-3 px-6 text-left">Intereses</th>
                            <th class="py-3 px-6 text-left">Entrego</th>
                            <th class="py-3 px-6 text-left">A Pagar</th>
                            <th class="py-3 px-6 text-left">Fecha de Cobro</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-xs font-light">
                        @foreach($creditos_pendientes as $credito)
                            @foreach($credito->cuotas as $cuota)
                                @php
                                    // Condición de interés y sin fecha de cobro
                                    $resaltar = $cuota->intereses > 0 && is_null($cuota->fecha_cobro);
                                @endphp
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    @if ($loop->first)
                                        <td class="py-2 px-3" rowspan="{{ $credito->cuotas->count() }}">{{ $credito->credito }} {{$credito->codigo_sucursal}}{{$credito->tipo_credito}}</td>
                                        <td class="py-2 px-3" rowspan="{{ $credito->cuotas->count() }}">{{ $credito->fecha_credito }} {{$resaltar}}</td>
                                    @endif

                                    <td class="py-2 px-3 {{ $resaltar ? 'bg-orange-200' : '' }}">{{ $cuota->cuota }} de {{ $credito->cantidad_cuotas }}</td>
                                    <td class="py-2 px-3 {{ $resaltar ? 'bg-orange-200' : '' }}">
                                        $ {{ number_format($credito->valor_cuota, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3 {{ $resaltar ? 'bg-orange-200' : '' }}">
                                        $ {{ number_format($cuota->intereses, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3 {{ $resaltar ? 'bg-orange-200' : '' }}">
                                        $ {{ number_format($cuota->entrego, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3 {{ $resaltar ? 'bg-orange-200' : '' }}">
                                        $ {{ number_format($cuota->a_pagar, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3 {{ $resaltar ? 'bg-orange-200' : '' }}">{{ $cuota->fecha_cobro }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <hr class="border-t-2 border-red-800 my-6">

        <h2 class="text-xl font-bold mb-4">Créditos Pagados:</h2>

        @if($creditos_pagados->isEmpty())
            <p>No hay créditos activos disponibles.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-xs leading-normal">
                            <th class="py-3 px-6 text-left">Crédito</th>
                            <th class="py-3 px-6 text-left">Fecha</th>
                            <th class="py-3 px-6 text-left">Cuota</th>
                            <th class="py-3 px-6 text-left">Val. de Cuota</th>
                            <th class="py-3 px-6 text-left">Intereses</th>
                            <th class="py-3 px-6 text-left">Entrego</th>
                            <th class="py-3 px-6 text-left">A Pagar</th>
                            <th class="py-3 px-6 text-left">Fecha de Cobro</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-xs font-light">
                        @foreach($creditos_pagados as $credito)
                            @foreach($credito->cuotas as $cuota)
                                <tr class="border-b border-gray-200 hover:bg-gray-100">
                                    @if ($loop->first) <!-- Solo mostrar los datos del crédito en la primera fila -->
                                        <td class="py-2 px-3" rowspan="{{ $credito->cuotas->count() }}">{{ $credito->credito }}  {{$credito->codigo_sucursal}}{{$credito->tipo_credito}}</td>
                                        <td class="py-2 px-3" rowspan="{{ $credito->cuotas->count() }}">{{ $credito->fecha_credito }}</td>
                                    @endif
                                    <td class="py-2 px-3">{{ $cuota->cuota }} de {{ $credito->cantidad_cuotas }}</td>
                                    <td class="py-2 px-3">
                                        $ {{ number_format($credito->valor_cuota, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3">
                                        $ {{ number_format($cuota->intereses, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3">
                                        $ {{ number_format($cuota->entrego, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3">
                                        $ {{ number_format($cuota->a_pagar, 2, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3">{{ $cuota->fecha_cobro }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif  
    </div>
</x-app-layout>

<script>
    window.onload = function() {
        @if (isset($imprimir) && $imprimir)
            window.print(); // Abre el diálogo de impresión si $imprimir es true
        @endif
    }
</script>