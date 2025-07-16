@php
$botones = [];
@endphp

<x-app-layout :botones="$botones">
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold text-gray-800 mb-6">Editar Permisos para {{ $usuario->username }}</h1>

        {{-- Errores --}}
        <x-errores>
            <!-- Mensajes de error se mostrarían aquí -->
        </x-errores>

        {{-- Formulario para editar permisos --}}
        <form action="{{ route('permisos.update', $usuario->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Sucursales --}}
            <h6 class="text-lg font-medium text-gray-700 mb-2">Sucursales</h6>
            @foreach ($todasSucursales as $sucursal)
                <div class="flex items-center mb-2">
                    <input type="checkbox" id="sucursal-{{ $sucursal->id }}" class="form-checkbox h-5 w-5 text-blue-600"
                        name="sucursales[]" value="{{ $sucursal->id }}"
                        @if ($usuario->sucursales->contains($sucursal->id)) checked @endif>
                    <label for="sucursal-{{ $sucursal->id }}" class="ml-2 text-gray-600">{{ $sucursal->nombre }}</label>
                </div>
            @endforeach

            {{-- Rutas --}}
            <h6 class="text-lg font-medium text-gray-700 mt-6 mb-2">Rutas</h6>
            @foreach ($authRoutes as $route)
                <div class="flex items-center mb-2">
                    <input type="checkbox" id="ruta-{{ Str::slug($route['uri']) }}" class="form-checkbox h-5 w-5 text-green-600"
                        name="rutas[]" value="{{ $route['uri'] }}"
                        @if ($usuario->rutas->pluck('ruta')->contains($route['uri'])) checked @endif>
                    <label for="ruta-{{ Str::slug($route['uri']) }}" class="ml-2 text-gray-600">
                        {{-- Mostrar nombre o "Sin Nombre" --}}
                        <strong>{{ $route['name'] ?? 'Sin Nombre' }}</strong>
                        {{-- Mostrar la URI de la ruta --}}
                        <span class="text-gray-500">({{ $route['uri'] }})</span>
                    </label>
                </div>
            @endforeach

            {{-- Botones de acción --}}
            <div class="mt-6">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400">
                    Guardar Cambios
                </button>
                <a href="{{ route('permisos.index') }}" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</x-app-layout>