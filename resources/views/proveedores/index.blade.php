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

        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Listado de Proveedores</h1>
        
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="table-auto w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">Legajo</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Nombre</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Teléfono 1</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Teléfono 2</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
                            <th class="border border-gray-300 px-4 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($proveedores as $proveedor)
                            <tr class="hover:bg-gray-50">
                                <td class="border border-gray-300 px-4 py-2">{{ $proveedor->legajo }}</td>
                                <td class="border border-gray-300 px-4 py-2">
                                    <a href="{{ route('proveedores.show', $proveedor->id) }}" class="text-blue-600 hover:underline">
                                        {{ $proveedor->nombre }}
                                    </a>
                                </td>
                                <td class="border border-gray-300 px-4 py-2">{{ $proveedor->tel1 }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $proveedor->tel2 }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $proveedor->email }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-center">
                                    <a href="{{ route('proveedores.edit', $proveedor->id) }}" class="text-yellow-500 hover:underline">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        
            <div class="mt-4">
                {{ $proveedores->links() }} <!-- Paginación -->
            </div>
        </div>


    </div>
</x-app-layout>
