<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Tipos de Crédito</h1>
    <a href="{{ route('tipo_credito.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Agregar Tipo de Crédito</a>

    <x-errores>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
    </x-errores>

    <div class="overflow-x-auto">
        <table class="table-auto w-full">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2">ID</th>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">Código</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tipos as $tipo_credito)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $tipo_credito->id }}</td>
                    <td class="px-4 py-2">{{ $tipo_credito->nombre }}</td>
                    <td class="px-4 py-2">{{ $tipo_credito->codigo }}</td>
                    <td class="px-4 py-2 space-x-2">
                        <a href="{{ route('tipo_credito.show', $tipo_credito) }}" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Ver</a>
                        <a href="{{ route('tipo_credito.edit', $tipo_credito) }}" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">Editar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>