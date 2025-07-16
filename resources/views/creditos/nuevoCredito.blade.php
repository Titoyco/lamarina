@php
$botones = [
    ['url' => 'javascript:history.back()', 'texto' => ' < Volver', 'color' => 'bg-gray-500'],
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
    
<!-- Modal -->
<div id="myModal" class="fixed inset-0 bg-black  opacity-75 items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 w-96">
        <span class="close cursor-pointer text-gray-900 float-right" onclick="cerrarModal()">&times;</span>
        <p id="mensajeConfirmacion" class="mt-4 text-black"></p>
        <div class="mt-4 flex justify-end">
            <button onclick="aceptar()" class="bg-green-500 text-white px-4 py-2 rounded mr-2">Aceptar</button>
            <button onclick="cerrarModal()" class="bg-red-500 text-white px-4 py-2 rounded">Cancelar</button>
        </div>
    </div>
</div>

    <script>
        function calcularCuotas() {
            const monto = parseFloat(document.getElementById('monto').value) || 0;
            const interes = parseFloat(document.getElementById('interes').value) || 0.04; // Asegúrate de que el interés esté definido
            const cuotas = parseInt(document.getElementById('numeroCuotas').value) || 1; // Obtener el número de cuotas
            const resultados = document.getElementById('resultados');
            resultados.innerHTML = ''; // Limpiar resultados anteriores
    
            // Validar el número de cuotas
            if (cuotas < 1 || cuotas > 4) {
                resultados.innerHTML = '<p class="text-red-500">Por favor, ingrese un número de cuotas entre 1 y 4.</p>';
                return;
            }
    
            // Calcular el valor de la cuota para el número de cuotas ingresado
            const valorCuota = (monto * (1 + interes * cuotas)) / cuotas;
            resultados.innerHTML = `<p>Se cargaran $${monto} en ${cuotas} cuota(s) de: $${valorCuota.toFixed(2)}</p><br><p>Opciones de cuotas:</p>`;
    
            // Mostrar el valor de las cuotas para 1 a 4 cuotas
            for (let i = 1; i <= 4; i++) {
                const valorCuota = (monto * (1 + interes * i)) / i;
                resultados.innerHTML += `<p>${i} cuota(s) de: $${valorCuota.toFixed(2)}</p>`;
            }
        }

        function mostrarConfirmacion() {
            const nombre = document.getElementById('nombreCliente').value;
            const monto = document.getElementById('monto').value;
            const cuotas = document.getElementById('numeroCuotas').value;
    
            const mensaje = `Nombre del Cliente: ${nombre}\nMonto: $${monto}\nCantidad de Cuotas: ${cuotas}`;
            document.getElementById('mensajeConfirmacion').innerText = mensaje;
    
            const modal = document.getElementById("myModal");
            modal.classList.remove("hidden");
            modal.classList.add("flex");
        }
    
        function cerrarModal() {
            const modal = document.getElementById("myModal");
            modal.classList.remove("flex");
            modal.classList.add("hidden");
        }
    
        function aceptar() {
            alert("Pago aceptado.");
            cerrarModal();
            const form = document.getElementById('creditoForm');
            form.submit(); // Envía el formulario
        }


        
    </script>

    <div class="container mx-auto mt-5">
        <h1 class="text-xl font-bold mb-4">Nuevo Crédito para {{ $cliente->nombre }}</h1>
        <form id="creditoForm" action="{{ route('creditos.store', $cliente) }}" method="POST"  onsubmit="event.preventDefault(); mostrarConfirmacion();">
            @csrf <!-- token CSRF -->
            <div class="mb-4">
                <label for="monto" class="block text-gray-700">Monto del Crédito:</label>
                <input type="number" id="monto" class="border rounded p-2 w-full" oninput="calcularCuotas()" placeholder="Ingrese el monto" required>
            </div>
            <div class="mb-4">
                <label for="numeroCuotas" class="block text-gray-700">Número de Cuotas (1-4):</label>
                <input type="number" id="numeroCuotas" class="border rounded p-2 w-full" min="1" max="4" oninput="calcularCuotas()" placeholder="Ingrese el número de cuotas" required>
            </div>
            <div class="mb-4">
                <label for="terminal">Selecciona una terminal:</label>
                <select name="terminal" id="terminal">
                    @foreach($terminales as $terminal)
                        <option value="{{ $terminal->codigo }}">{{ $terminal->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" id="interes" value="{{ $interes }}">
            <input type="hidden" id="nombreCliente" value="{{ $cliente->nombre }}">
            <div id="resultados" class="mt-4">
                <!-- Resultados de las cuotas se mostrarán aquí -->
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded mt-4">Guardar Crédito</button>
        </form>
    </div>

</x-app-layout>