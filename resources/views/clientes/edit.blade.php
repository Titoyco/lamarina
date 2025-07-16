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

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="max-w-4xl mx-auto bg-slate-100 font-sans dark:text-black"> 
        <h1 class="text-3xl font-bold m-5 dark:text-black">Editar Cliente</h1>
        <form action="{{ route('clientes.update', $cliente->id) }}" method="POST" class="m-5">
            @csrf
            @method('PUT')
            <div class="space-y-2 p-5">
                <div class="flex items-end justify-end space-x-4">
                    <label for="suspendido" class="block text-sm font-medium text-gray-700">Suspendido</label>
                    <input type="checkbox" name="suspendido" id="suspendido" class="mt-1" value="1" {{ $cliente->suspendido ? 'checked' : '' }}>
                </div>
                <div class="flex flex-col sm:flex-row  items-center space-x-4">
                    <label for="dni" class="text-sm font-medium text-gray-700 mr-5">DNI:</label>
                    <input type="number" name="dni" id="dni" value="{{ $cliente->dni }}" required class="mr-5 mt-1 w-32 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <label for="nombre" class="text-sm font-medium text-gray-700 mr-5">Nombre</label>
                    <input type="text" name="nombre" id="nombre" value="{{ $cliente->nombre }}" required class="mr-5 mt-1 flex-grow border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col sm:flex-row  items-center space-x-4">
                    <label for="banco" class="text-sm font-medium text-gray-700">Banco: </label>
                    <input type="text" name="banco" id="banco" value="{{ $cliente->banco }}" disabled class="block w-1/4 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"> 
                    <label for="cbu" class="text-sm font-medium text-gray-700">CBU: </label>
                    <input type="text" name="cbu" id="cbu" value="{{ $cliente->cbu }}" class="flex-grow border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"> 
                    <label class="text-sm font-medium text-gray-700"> Vacío o 22 dígitos válidos </label>
                </div>
                <div class="flex flex-col sm:flex-row  items-center space-x-4">
                    <label for="tel1" class="block text-sm font-medium text-gray-700 w-32">Teléfono 1: </label>
                    <input type="text" name="tel1" id="tel1" value="{{ $cliente->tel1 }}" required class="mt-1 block w-48 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    
                    <label for="tel2" class="block text-sm font-medium text-gray-700 w-32">Teléfono 2: </label>
                    <input type="text" name="tel2" id="tel2" value="{{ $cliente->tel2 }}" class="mt-1 block w-48 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    
                    <label for="email" class="block text-sm font-medium text-gray-700 w-32">Email: </label>
                    <input type="email" name="email" id="email" value="{{ $cliente->email }}" class="mt-1 block w-48 flex-grow border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col sm:flex-row  items-center space-x-4">
                    <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" id="direccion" value="{{ $cliente->direccion }}" required class="mt-1 block w-full border-gray-300 rounded -md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col sm:flex-row  items-center space-x-4">
                    <label for="id_provincia" class="block text-sm font-medium text-gray-700">Provincia: </label>
                    <select name="id_provincia" id="id_provincia" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Seleccione una provincia</option>
                        @foreach($provincias as $provincia)
                            <option value="{{ $provincia->id }}" {{ optional($cliente->localidad)->id_provincia == $provincia->id ? 'selected' : '' }}>{{ $provincia->nombre }}</option>
                        @endforeach
                    </select>
                    <label for="id_localidad" class="block text-sm font-medium text-gray-700">Localidad: </label>
                    <select name="id_localidad" id="id_localidad" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>
                <div class="flex flex-col sm:flex-row  items-center space-x-4">
                    <label for="tipo_haber" class="block text-sm font-medium text-gray-700">Tipo de Haber</label>
                    <select name="tipo_haber" id="tipo_haber" required class="mt-1 block border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($tipos_haber as $tipo)
                            <option value="{{ $tipo['id'] }}" {{ $cliente->tipo_haber == $tipo['id'] ? 'selected' : '' }}>{{ $tipo['nombre'] }}</option>
                        @endforeach
                    </select>
                    <label for="saldo" class="block text-sm font-medium text-gray-700">Saldo:</label>
                    <input type="number" name="saldo" id="saldo" value="{{ $cliente->saldo }}" class="text-center mt-1 block border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <label for="limite" class="block text-sm font-medium text-gray-700">Límite:</label>
                    <input type="number" name="limite" id="limite" value="{{ $cliente->limite }}" class="text-center mt-1 block w-48 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex flex-col sm:flex-row  items-center space-x-4">
                    <label for="sucursal" class="block text-sm font-medium text-gray-700">Sucursal: </label>
                    <select name="sucursal" id="sucursal" required class="mt-1 w-32 block border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal['id'] }}" {{ $cliente->sucursal == $sucursal['id'] ? 'selected' : '' }}>{{ $sucursal['nombre'] }}</option>
                        @endforeach
                    </select>
                    <label for="max_cuotas" class="block text-sm font-medium text-gray-700">Máximo de Cuotas</label>
                    <input type="number" name="max_cuotas" id="max_cuotas" value="{{ $cliente->max_cuotas }}" class="text-center mt-1 block w-20 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="obs" class="block text-sm font-medium text-gray-700">Observaciones:</label>
                    <textarea name="obs" id="obs" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $cliente->obs }}</textarea>
                </div>
                <div class="m-2 p-2 border-2 border-black">
                    <h1>Familiar de Contacto:</h1>
                    <div class="flex items-center space-x-4">
                        <select id="tipo_familiar" name="tipo_familiar">
                            <option value="" disabled selected>Tipo:</option>
                            <option value="madre" {{ $cliente->tipo_familiar == 'madre' ? 'selected' : '' }}>Madre</option>
                            <option value="padre" {{ $cliente->tipo_familiar == 'padre' ? 'selected' : '' }}>Padre</option>
                            <option value="hijo" {{ $cliente->tipo_familiar == 'hijo' ? 'selected' : '' }}>Hijo/a</option>
                            <option value="hermano" {{ $cliente->tipo_familiar == 'hermano' ? 'selected' : '' }}>Hermano/a</option>
                            <option value="tio" {{ $cliente->tipo_familiar == 'tio' ? 'selected' : '' }}>Tío/a</option>
                            <option value="primo" {{ $cliente->tipo_familiar == 'primo' ? 'selected' : '' }}>Primo/a</option>
                            <option value="otro" {{ $cliente->tipo_familiar == 'otro' ? 'selected' : '' }}>Otro/a</option>
                        </select>
                        <label for="familiar" class="block text-sm font-medium text-gray-700">Nombre: </label>
                        <input type="text" name="familiar" id="familiar" value="{{ $cliente->familiar }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="direccion_familiar" class="block text-sm font-medium text-gray-700">Dirección Familiar</label>
                        <input type="text" name="direccion_familiar" id="direccion_familiar" value="{{ $cliente->direccion_familiar }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-center space-x-4">
                        <label for="id_provincia_familiar" class="block text-sm font-medium text-gray-700">Provincia</label>
                        <select name="id_provincia_familiar" id="id_provincia_familiar" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Seleccione una provincia</option>
                            @foreach($provincias as $provincia)
                                <option value="{{ $provincia->id }}" {{ optional($cliente->localidadFamiliar)->id_provincia == $provincia->id ? 'selected' : '' }}>{{ $provincia->nombre }}</option>
                            @endforeach
                        </select>
                        <label for="id_localidad_familiar" class="block text-sm font-medium text-gray-700">Localidad Familiar</label>
                        <select name="id_localidad_familiar" id="id_localidad_familiar" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="flex items-center space-x-4">
                        <label for="email_familiar" class="block text-sm font-medium text-gray-700">Email Familiar</label>
                        <input type="email" name="email_familiar" id="email_familiar" value="{{ $cliente->email_familiar }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="tel_familiar" class="block text-sm font-medium text-gray-700">Teléfono Familiar</label>
                        <input type="text" name="tel_familiar" id="tel_familiar" value="{{ $cliente->tel_familiar }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="m-2 p-2 border-2 border-black">
                    <h1>Lugar de trabajo:</h1>
                    <div class="flex items-center space-x-4">
                        <label for="trabajo" class="block text-sm font-medium text-gray-700">Trabajo</label>
                        <input type="text" name="trabajo" id="trabajo" value="{{ $cliente->trabajo }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <label for="tel_trabajo" class="block text-sm font-medium text-gray-700">Teléfono Trabajo</label>
                        <input type="text" name="tel_trabajo" id="tel_trabajo" value="{{ $cliente->tel_trabajo }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="direccion_trabajo" class="block text-sm font-medium text-gray-700">Dirección Trabajo</label>
                        <input type="text" name="direccion_trabajo" id="direccion_trabajo" value="{{ $cliente->direccion_trabajo }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-center space-x-4">
                        <label for="id_provincia_trabajo" class="block text-sm font-medium text-gray-700">Provincia</label>
                        <select name="id_provincia_trabajo" id="id_provincia_trabajo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Seleccione una provincia</option>
                            @foreach($provincias as $provincia)
                                <option value="{{ $provincia->id }}" {{ optional($cliente->localidadTrabajo)->id_provincia == $provincia->id ? 'selected' : '' }}>{{ $provincia->nombre }}</option>
                            @endforeach
                        </select>
                        <label for="id_localidad_trabajo" class="block text-sm font-medium text-gray-700">Localidad Trabajo</label>
                        <select name="id_localidad_trabajo" id="id_localidad_trabajo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>

                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2 rounded-md hover:bg-indigo-700">Actualizar Cliente</button>
            </div>
        </form>
    </div>
</x-app-layout>

<script>
    // Código JavaScript para manejar la carga dinámica de localidades y otras interacciones
// Función para cargar localidades
// Función para cargar localidades
function cargarLocalidades(idLocalidad,provinciaSelectId, localidadSelectId) {
    const provinciaSelect = document.getElementById(provinciaSelectId);
    const localidadSelect = document.getElementById(localidadSelectId);
    const provinciaId = provinciaSelect.value;

    // Limpiar las localidades
    localidadSelect.innerHTML = '<option value="">Seleccione una localidad</option>';

    if (provinciaId) {
        const localidadesUrl = '{{ url("/localidades/") }}'; // Importante la barra diagonal final
        fetch(`${localidadesUrl}/${provinciaId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(localidad => {
                    const option = document.createElement('option');
                    option.value = localidad.id;
                    option.textContent = localidad.nombre;
                    localidadSelect.appendChild(option);
                });
                if (idLocalidad) {
                    localidadSelect.value = idLocalidad;
                }
            })
            .catch(error => {
                console.error('Error al cargar localidades:', error);
            });
    }
}





// Agregar los event listeners
document.getElementById('id_provincia').addEventListener('change', function() {
    cargarLocalidades(0,'id_provincia', 'id_localidad');
});

document.getElementById('id_provincia_familiar').addEventListener('change', function() {
    cargarLocalidades(0,'id_provincia_familiar', 'id_localidad_familiar');
});

document.getElementById('id_provincia_trabajo').addEventListener('change', function() {
    cargarLocalidades(0,'id_provincia_trabajo', 'id_localidad_trabajo');
});
   
    const inputCBU = document.getElementById('cbu');
    const banco = document.getElementById('banco');

    inputCBU.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 22) {
            this.value = this.value.slice(0, 22); // Limita a 22 dígitos
        } 
    });

    inputCBU.addEventListener('blur', function() {
        if (this.value.length === 0) {
            // Permitir que el campo esté vacío
            inputCBU.classList.remove('error', 'success');
            banco.value = ''; // Limpiar el campo del banco si el CBU está vacío
        } else if (this.value.length < 22) {
            inputCBU.classList.add('error');
            inputCBU.classList.remove('success');
            alert('El CBU debe tener 22 dígitos numéricos.');
            setTimeout(() => {
                inputCBU.focus();
                }, 0);
            } else if (this.value.length === 22){
                obtenerNombreBanco(this.value);                    
            }
    });

       

    function obtenerNombreBanco(valor) {
    // Hacer la solicitud AJAX para obtener el nombre del banco
    const bancoUrl = '{{ url("/bancos/") }}'; // Importante la barra diagonal final
    fetch(`${bancoUrl}/${valor}`)
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.error); // Captura el mensaje de error del servidor
                });
            }
            return response.json();
        })
        .then(data => {
            banco.value = data.nombre; // Asignar el nombre del banco
            inputCBU.classList.add('success');
            inputCBU.classList.remove('error');
        })
        .catch(error => {
            inputCBU.classList.add('error');
            inputCBU.classList.remove('success');
            banco.value = ''; // Limpiar el campo del banco en caso de error
            alert(error.message);
            setTimeout(() => {
                inputCBU.focus();
            }, 0);
        });
}

// Ejecutar la función al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // Cargar localidades para el cliente
    const idLocalidadCliente = {{ $cliente->id_localidad ?? 0 }};
    cargarLocalidades(idLocalidadCliente, 'id_provincia', 'id_localidad');

    // Cargar localidades para el familiar
    const idLocalidadFamiliar = {{ $cliente->id_localidad_familiar ?? 0}};
    cargarLocalidades(idLocalidadFamiliar, 'id_provincia_familiar', 'id_localidad_familiar');
    
    // Cargar localidades para el trabajo
    const idLocalidadTrabajo = {{ $cliente->id_localidad_trabajo ?? 0}};
    cargarLocalidades(idLocalidadTrabajo, 'id_provincia_trabajo', 'id_localidad_trabajo');
});


</script>
