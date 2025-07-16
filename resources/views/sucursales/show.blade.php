<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Detalle de Sucursal</h1>

    <x-errores>
        <!-- Errores no aplican en show, pero se mantiene el slot por consistencia -->
    </x-errores>

    <div class="bg-white rounded shadow p-6 max-w-md">
        <p><strong>ID:</strong> {{ $sucursal->id }}</p>
        <p><strong>Nombre:</strong> {{ $sucursal->nombre }}</p>
        <p><strong>Código:</strong> {{ $sucursal->codigo }}</p>
    </div>

    <div class="mt-4 flex space-x-2">
        <a href="{{ route('sucursales.edit', $sucursal) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">Editar</a>
        <a href="{{ route('sucursales.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded">Volver</a>
    </div>
</x-app-layout>