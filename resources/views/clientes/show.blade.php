@php
$botones = [
    ['url' => route('clientes.exportarFicha', $cliente), 'texto' => 'Exportar a PDF', 'color' => 'bg-blue-500'],
    ['url' => route('clientes.edit', $cliente), 'texto' => 'Editar', 'color' => 'bg-yellow-500'],
    ['url' => '#', 'texto' => 'Bloquear', 'color' => 'bg-red-500'], // Cambia '#' por la ruta correspondiente
    ['url' => '#', 'texto' => 'Autorizados', 'color' => 'bg-amber-950'], // Cambia '#' por la ruta correspondiente
    ['url' => route('clientes.movimientos', $cliente), 'texto' => 'Movimientos', 'color' => 'bg-cyan-600'],
    ['url' => '#', 'texto' => 'Nuevo Credito', 'color' => 'bg-green-600'] // Cambia '#' por la ruta correspondiente
]; 
@endphp

<x-app-layout :botones="$botones">
    <x-errores>
        <!-- ACA SE MUESTRAN LOS ERRORES -->
    </x-errores>



    <div class="max-w-4xl mx-auto mt-10 bg-slate-100 font-sans">
        <h1 class="text-3xl font-bold m-5">Ficha del Cliente</h1>
        <div class="m-5 p-5 border border-gray-300 rounded-md">
    
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Fecha de Creación:</label>
                <span class="text-sm text-gray-900">{{ $cliente->created_at ?? 'No disponible' }}</span>
            </div>
    
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Modificado el:</label>
                <span class="text-sm text-gray-900">{{ $cliente->updated_at ?? 'No disponible' }}</span>
            </div>
    
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">DNI:</label>
                <span class="text-sm text-gray-900">{{ $cliente->dni }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Nombre:</label>
                <span class="text-sm text-gray-900">{{ $cliente->nombre }}</span>
                <span class=" text-sm {{ $cliente->suspendido ? 'text-red-600' : 'text-gray-900' }} ">{{ $cliente->suspendido ? 'CLIENTE SUSPENDIDO' : '' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Banco:</label>
                <span class="text-sm text-gray-900">{{ $cliente->banco ?? 'No disponible'  }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">CBU:</label>
                <span class="text-sm text-gray-900">{{ $cliente->cbu  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Teléfono 1:</label>
                <span class="text-sm text-gray-900">{{ $cliente->tel1  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Teléfono 2:</label>
                <span class="text-sm text-gray-900">{{ $cliente->tel2  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Email:</label>
                <span class="text-sm text-gray-900">{{ $cliente->email  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Dirección:</label>
                <span class="text-sm text-gray-900">{{ $cliente->direccion  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Provincia:</label>
                <span class="text-sm text-gray-900">{{ $cliente->localidad->provincia->nombre ?? 'No disponible'  }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Localidad:</label>
                <span class="text-sm text-gray-900">{{ $cliente->localidad->nombre ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Tipo de Haber:</label>
                <span class="text-sm text-gray-900">{{ $cliente->haber->nombre  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Saldo:</label>
                <span class="text-sm text-gray-900">{{ $cliente->saldo  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Límite:</label>
                <span class="text-sm text-gray-900">{{ $cliente->limite  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Sucursal:</label>
                <span class="text-sm text-gray-900">{{ $cliente->sucursal  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Máximo de Cuotas:</label>
                <span class="text-sm text-gray-900">{{ $cliente->max_cuotas  ?? 'No disponible' }}</span>
            </div>
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Observaciones:</label>
                <p class="text-sm text-gray-900">{{ $cliente->obs  ?? 'No disponible' }}</p>
            </div>
            <br><hr/>
                <div>
                    <h2 class="text-xl font-bold ">Familiar de Contacto:</h1>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Tipo de Familiar:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->tipo_familiar  ?? 'No disponible'  }}</span>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Familiar:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->familiar  ?? 'No disponible' }}</span>
                        </div>
    
                        
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Dirección:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->direccion_familiar  ?? 'No disponible' }}</span>
                        </div>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Localidad:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->localidadFamiliar->nombre ?? 'No disponible'  }} </span>
                        </div>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Provincia:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->localidadFamiliar->provincia->nombre ?? 'No disponible' }} </span>
                        </div>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Email:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->email_familiar  ?? 'No disponible' }}</span>
                        </div>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Teléfono:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->tel_familiar ?? 'No disponible'  }}</span>
                        </div>
                    </div>
                    <br><hr/>
                    <div>
                        <h2 class="text-xl font-bold ">Lugar de Trabajo:</h1>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Trabajo:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->trabajo  ?? 'No disponible' }}</span>
                        </div>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Dirección:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->direccion_trabajo  ?? 'No disponible' }}</span>
                        </div>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Localidad:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->localidadTrabajo->nombre ?? 'No disponible' }}</span>
                        </div>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Provincia:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->localidadTrabajo->provincia->nombre ?? 'No disponible' }}</span>
                        </div>
    
                        <div class="flex items-center space-x-4">
                            <label class="text-sm font-medium text-gray-700">Teléfono Trabajo:</label>
                            <span class="text-sm text-gray-900">{{ $cliente->tel_trabajo ?? 'No disponible'  }}</span>
                        </div>
                </div>
    
    
        </div>
    
    </div>

</x-app-layout>
