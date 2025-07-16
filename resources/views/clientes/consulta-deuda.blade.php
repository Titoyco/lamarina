<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="icon" href="{{ url('images/favicon.png') }}" type="image/png">
    <title>La Marina V3</title>
    <style>
        .error {
            border: 2px solid red;
        }
        .success {
            border: 2px solid green;
        }
    </style>
</head>
<body class="min-h-screen min-w-[400px] flex flex-col items-center font-sans antialiased bg-slate-500 text-gray-900 dark:text-white/50">

    <!-- Header -->
    <header class="bg-slate-500 w-full sm:h-20">
        <div class="flex flex-col sm:flex-row items-center justify-between bg-slate-300 h-auto sm:h-20 w-full max-w-[800px] mx-auto px-4">
            <img class="h-16 sm:h-20 min-w-min p-2" src="{{ asset('images/logo.png') }}" alt="Logo de La Marina">
            <h1 class="text-xl sm:text-2xl font-bold text-center sm:ml-4">SISTEMA DE VENTAS V3.0</h1>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container w-full max-w-[800px] bg-amber-50 p-6 mt-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-4">Consulta de Saldo</h1>
        <form id="consulta-form" class="space-y-4">
            @csrf
            <div class="form-group">
                <label for="dni" class="block font-medium text-gray-700">DNI del Cliente:</label>
                <input type="text" id="dni" name="dni" class="form-control w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-500 focus:border-blue-500" placeholder="Ingrese el DNI" required>
            </div>
            <button type="submit" class="btn btn-primary bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">Consultar</button>
        </form>

        <!-- Result Section -->
        <div id="resultado" class="mt-6 p-4 bg-gray-100 rounded-lg shadow-md" style="display: none;">
            <h3 class="text-xl font-bold mb-2">Resultado</h3>
            <p><strong>Nombre:</strong> <span id="nombre"></span></p>
            <p><strong>Deuda Actual:</strong>$ <span id="deuda"></span></p>
        </div>
    </main>

    <script>
        document.getElementById('consulta-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const dni = document.getElementById('dni').value;

            fetch(`/consultadeuda/${dni}`)
                .then(response => {
                    if (response.ok) {
                        return response.json();
                    } else {
                        throw new Error('Cliente no encontrado');
                    }
                })
                .then(data => {
                    document.getElementById('resultado').style.display = 'block';
                    document.getElementById('nombre').textContent = data.nombre;
                    document.getElementById('deuda').textContent = data.deudaTotal;
                })
                .catch(error => {
                    alert(error.message);
                    document.getElementById('resultado').style.display = 'none';
                });
        });
    </script>
</body>
</html>