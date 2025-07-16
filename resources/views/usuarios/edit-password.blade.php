<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Cambiar Clave - {{ $user->name }}</h1>

    <x-errores>
        <!-- ACA SE MUESTRAN LOS ERRORES -->
       
    </x-errores>

    <form action="{{ route('usuarios.update-password', $user) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label for="password" class="block font-medium text-gray-700">Nueva Clave</label>
            <input type="password" name="password" id="password" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm" required>
        </div>
        <div>
            <label for="password_confirmation" class="block font-medium text-gray-700">Confirmar Clave</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm" required>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Actualizar</button>
    </form>
</x-app-layout>