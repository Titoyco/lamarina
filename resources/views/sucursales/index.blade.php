<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Sucursales</h1>

    <a href="{{ route('sucursales.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">Agregar Sucursal</a>

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
                @foreach($sucursales as $sucursal)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $sucursal->id }}</td>
                    <td class="px-4 py-2">{{ $sucursal->nombre }}</td>
                    <td class="px-4 py-2">{{ $sucursal->codigo }}</td>
                    <td class="px-4 py-2 space-x-2">
                        <a href="{{ route('sucursales.show', $sucursal) }}" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Ver</a>
                        <a href="{{ route('sucursales.edit', $sucursal) }}" class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">Editar</a>
                        {{-- No mostrar botón de eliminar --}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>