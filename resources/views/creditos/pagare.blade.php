<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="es-ar">
    <title>Imprimir Pagaré</title>

    <script>
        function imprimir() {
            setTimeout(() => {
                window.print();
                window.location.href = "{{ 'javascript:history.back()' }}"; // Redirigir después de imprimir
            }, 500);
        }
    </script>

    <style>
        @media print {
            @page {
                size: A4;
                margin: 20px;
            }

            body {
                background: white;
            }
        }
    </style>
    @vite('resources/css/app.css') <!-- Incluye Tailwind -->
</head>

<body onload="imprimir()" class="bg-gray-100 font-sans text-sm text-gray-800">
    <div class="max-w-4xl mx-auto bg-white border border-gray-300 p-4">
        <!-- Encabezado -->
        <div class="flex justify-between items-center border-b pb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-14">
            <div>
                <p><strong>Id Venta:</strong> {{ str_pad($credito->credito, 8, '0', STR_PAD_LEFT) }} {{ $credito->terminal }}</p>
                <p><strong>Fecha/Hora:</strong> {{ $credito->fecha_credito }}</p>
            </div>
        </div>

        <!-- Detalle -->
        <p class="mt-6 text-justify">
            Autorizo a GUILLERMO BARZINI, “LA MARINA”, y/o “TIENDAS LA MARINA SAS”, a debitar de mi cuenta bancaria el importe que surge de la siguiente compra.
            Si el débito no fuese posible por alguna causa ajena a la EMPRESA, me comprometo a realizar el pago en el local comercial de RIVADAVIA 249 – Trelew.
        </p>
        <p class="text-sm text-red-600 mt-2">* El incumplimiento de los pagos pactados generará intereses mensuales por mora y dará vía al COBRO JUDICIAL.</p>

        <!-- Datos del cliente -->
        <table class="w-full mt-6 table-auto border-collapse border border-gray-300 text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="border border-gray-300 p-2 text-left">Nombres y Apellidos</th>
                    <th class="border border-gray-300 p-2 text-left">DNI</th>
                    <th class="border border-gray-300 p-2 text-left">Teléfono</th>
                    <th class="border border-gray-300 p-2 text-left">Dirección</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border border-gray-300 p-2">{{ $credito->cliente->nombre }}</td>
                    <td class="border border-gray-300 p-2">{{ $credito->cliente->dni }}</td>
                    <td class="border border-gray-300 p-2">{{ $credito->cliente->tel1 }}</td>
                    <td class="border border-gray-300 p-2">{{ $credito->cliente->direccion }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Detalle de cuotas -->
        <div class="mt-6">
            <p><strong>Son cuotas:</strong> {{ $credito->cantidad_cuotas }} mensuales consecutivas de ${{ number_format($credito->valor_cuota, 2) }}</p>
            <p><strong>Concepto:</strong> </p>
        </div>

        <div class="mt-6 flex justify-between items-end">
            <div>
                @if($credito->id_autorizado)
                    <p>Autorizado: {{ $credito->id_autorizado }} (DNI: {{ $credito->id_autorizado }})</p>
                @endif
            </div>
            <div class="border-t border-gray-300 pt-2 text-center">
                <strong>FIRMA</strong>
            </div>
        </div>

        @php
            $totalAdeudado = 0;
            $pendienteDeCobro = 0;
            $mora = 0;
        @endphp


        <!-- Resumen financiero -->
        <div class="mt-6">
            <p><strong>Total Adeudado:</strong> ${{ number_format($totalAdeudado, 2) }}</p>
            <p><strong>Pendiente de Cobro:</strong> ${{ number_format($pendienteDeCobro, 2) }}</p>
            <p class="text-red-600"><strong>Mora (Vencido):</strong> ${{ number_format($mora, 2) }}</p>
        </div>
    </div>
</body>

</html>