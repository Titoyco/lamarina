<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Etiqueta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: 50mm 25mm;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="w-[50mm] h-[25mm] border border-black p-1 flex flex-col justify-between">
        <!-- Encabezado -->
        <div class="flex justify-between items-center">
            <img src="{{ asset('images/logo_ticket.png') }}" alt="Logo" class="h-[6mm] max-w-[20mm]">
            <div class="text-lg font-bold text-right">{{ $precio_venta !== null ? '$'.number_format($precio_venta, 2) : 'No disponible' }}</div> 
        </div>

        <!-- Contenido principal -->
        <div class="text-center">
            <div class="text-[8px]">{{ $producto->descripcion }}</div>
            <div class="mt-1">{!! $barcode !!}</div>
        </div>

        <!-- Pie de etiqueta -->
        <div class="text-center text-[8px]" style="letter-spacing: 0.5em;">
           <strong>*{{ $producto->codigo }}*</strong>
        </div>
    </div>
    
    <div class="mt-4">
        <button id="btnImprimir" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Imprimir Etiqueta
        </button>
    </div>

    <script>
    document.getElementById('btnImprimir').addEventListener('click', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const datos = {
            producto: '{{ $producto->descripcion }}',
            barcode: '{{ $producto->codigo }}',
            precio_venta: {{ $precio_venta ?? 0 }}
        };
        
        console.log('Enviando:', datos);
        console.log('URL:', '{{ route("ticket.imprimir") }}');
        
        fetch('{{ route("ticket.imprimir") }}', {
            method: 'POST',
            credentials: 'same-origin', // <- enviar cookies de sesión
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify(datos)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta:', data);
            if(data.success) {
                alert('✓ ' + data.message);
            } else {
                alert('✗ ' + (data.message || 'Error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error en la solicitud: ' + error.message);
        });
    });
    </script>
</body>
</html>