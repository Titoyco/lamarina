<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Detalle de Tipo de Crédito</h1>

    <x-errores>
        <!-- Errores no aplican en show, pero se mantiene el slot por consistencia -->
    </x-errores>

    <div class="bg-white rounded shadow p-6 max-w-md">
        <p><strong>ID:</strong> {{ $tipo_credito->id }}</p>
        <p><strong>Nombre:</strong> {{ $tipo_credito->nombre }}</p>
        <p><strong>Código:</strong> {{ $tipo_credito->codigo }}</p>
    </div>

    <div class="mt-4 flex space-x-2">
        <a href="{{ route('tipo_credito.edit', $tipo_credito) }}" class="bg-yellow-500 text-white px-4 py-2 rounded">Editar</a>
        <a href="{{ route('tipo_credito.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded">Volver</a>
    </div>
</x-app-layout>