<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: 50mm 25mm; /* Tamaño exacto del papel de la etiquetadora */
            margin: 0; /* Sin márgenes */
        }

        body {
            margin: 0;
            padding: 0;
            width: 50mm;
            height: 25mm;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .barcode-container svg {
            max-width: 48mm; /* Ancho máximo del código de barras */
            height: auto; /* Escala proporcional */
        }
    </style>
</head>
<body>
    <div class="w-[50mm] h-[25mm] border border-black p-1 flex flex-col justify-between">
        <!-- Encabezado -->
        <div class="flex justify-between items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-[8mm] max-w-[20mm]">
            <div class="text-lg font-bold text-right">${{ number_format($producto->precio_venta, 2) }}</div>
        </div>

        <!-- Contenido principal -->
        <div class="text-center">
            <div class="text-[8px]">{{ $producto->descripcion }}</div>
            <div class="mt-1 barcode-container">{!! $barcode !!}</div>
        </div>

        <!-- Pie de etiqueta -->
        <div class="text-center text-[10px]" style="letter-spacing: 0.5em;">
           <strong> *{{ $producto->codigo }}* </strong>
        </div>
    </div>
</body>
</html>