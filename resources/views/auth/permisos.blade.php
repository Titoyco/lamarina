@php
$botones = [];
@endphp

<x-app-layout :botones="$botones">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold text-gray-800 mb-6">Gestión de Permisos</h1>

        {{-- Mensajes de éxito o error --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Listado de usuarios con sus permisos --}}
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <ul class="divide-y divide-gray-200">
                @foreach ($usuarios as $usuario)
                    <li class="p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-xl font-medium text-gray-800">{{ $usuario->username }}</h2>

                                {{-- Sucursales habilitadas --}}
                                <div class="mt-2">
                                    <h3 class="text-sm font-semibold text-gray-600">Sucursales Habilitadas:</h3>
                                    @if ($usuario->sucursales->isEmpty())
                                        <p class="text-sm text-gray-500">No tiene sucursales habilitadas.</p>
                                    @else
                                        <div class="flex flex-wrap mt-1">
                                            @foreach ($usuario->sucursales as $sucursal)
                                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">
                                                    {{ $sucursal->nombre }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- Rutas habilitadas --}}
                                <div class="mt-4">
                                    <h3 class="text-sm font-semibold text-gray-600">Rutas Habilitadas:</h3>
                                    @if ($usuario->rutas->isEmpty())
                                        <p class="text-sm text-gray-500">No tiene rutas habilitadas.</p>
                                    @else
                                        <div class="flex flex-wrap mt-1">
                                            @foreach ($usuario->rutas as $ruta)
                                                <span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">
                                                    {{ $ruta->ruta }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Botón para editar permisos --}}
                            <a href="{{ route('permisos.edit', $usuario->id) }}" 
                                class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400">
                                Editar
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>