<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Menú del Sistema</h1>

    <x-errores>
        <!-- ACA SE MUESTRAN LOS ERRORES -->
    </x-errores>

    @php
        $sucursalId = session('sucursal_activa');
    @endphp

    <div class="mb-4">
        @if ($sucursalId)
            <p class="text-green-600 font-medium">Sucursal activa: {{ $sucursalId }}</p>
        @else
            <p class="text-red-600 font-medium">No hay una sucursal activa seleccionada.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <p class="text-lg font-semibold mb-4">Seleccione una opción:</p>
        <p class="text-gray-600">id de usuario: {{ auth()->id() }}</p>
        <!-- Botón Ver Usuarios -->
        <x-button-submit class="w-full sm:w-80" onclick="window.location.href='{{ route('usuarios.index') }}'">
            Ver Usuarios
        </x-button-submit>

        <!-- Botón Ver Permisos de Usuarios -->
        <x-button-submit class="w-full sm:w-80" onclick="window.location.href='{{ route('permisos.index') }}'">
            Ver Permisos de Usuarios
        </x-button-submit>

        <!-- Botón Ver Categorías de Productos -->
        <x-button-submit class="w-full sm:w-80" onclick="window.location.href='{{ route('productos.categorias.index') }}'">
            Ver Categorías de Productos
        </x-button-submit>

        <!-- Botón Ver Subcategorías de Productos -->
        <x-button-submit class="w-full sm:w-80" onclick="window.location.href='{{ route('productos.subcategorias.index') }}'">
            Ver Subcategorías de Productos
        </x-button-submit>

        <!-- Botón Ver Sucursales de Productos -->
        <x-button-submit class="w-full sm:w-80" onclick="window.location.href='{{ route('sucursales.index') }}'">
            Ver Sucursales
        </x-button-submit>
        <x-button-submit class="w-full sm:w-80" onclick="window.location.href='{{ route('tipo_credito.index') }}'">
            Ver Tipos de Crédito
        </x-button-submit>


    </div>
</x-app-layout>