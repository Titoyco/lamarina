<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Crear Tipo de Crédito</h1>

    <x-errores>
        @if ($errors->any())
            <ul class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </x-errores>

    <form action="{{ route('tipo_credito.store') }}" method="POST" class="space-y-4 max-w-md">
        @csrf
        <div>
            <label for="nombre" class="block font-semibold">Nombre</label>
            <input type="text" name="nombre" id="nombre" class="w-full border rounded px-3 py-2" required value="{{ old('nombre') }}">
        </div>
        <div>
            <label for="codigo" class="block font-semibold">Código (una sola letra mayúscula)</label>
            <input type="text" name="codigo" id="codigo" class="w-full border rounded px-3 py-2" required maxlength="1" pattern="[A-Z]" value="{{ old('codigo') }}">
            <small class="text-gray-600">Ejemplo: T, I, U</small>
        </div>
        <div class="flex space-x-2">
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Guardar</button>
            <a href="{{ route('tipo_credito.index') }}" class="bg-gray-400 text-white px-4 py-2 rounded">Volver</a>
        </div>
    </form>
</x-app-layout>