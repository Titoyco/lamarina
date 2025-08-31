<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Editar Tipo de Crédito</h1>

    <x-errores>
        @if ($errors->any())
            <ul class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </x-errores>

    <form action="{{ route('tipo_credito.update', $tipo_credito) }}" method="POST" class="space-y-4 max-w-md">
        @csrf
        @method('PUT')
        <div>
            <label for="nombre" class="block font-semibold">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="w-full border rounded px-3 py-2" required value="{{ old('nombre', $tipo_credito->nombre) }}">
        </div>
        <div>
            <label for="codigo" class="block font-semibold">Código</label>
            <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100" value="{{ $tipo_credito->codigo }}" disabled>
            <small class="text-gray-600">El código no puede ser modificado.</small>
        </div>
        <div class="flex space-x-2">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Actualizar</button>
            <a href="{{ route('tipo_credito.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded">Volver</a>
        </div>
    </form>
</x-app-layout>