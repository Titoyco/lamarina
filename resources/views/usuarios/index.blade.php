<x-app-layout>
    <h1 class="text-2xl font-bold mb-4">Usuarios</h1>

    <a href="{{ route('usuarios.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Agregar Usuario</a>

    <x-errores>
        <!-- ACA SE MUESTRAN LOS ERRORES -->
       
    </x-errores>

    <table class="min-w-full bg-white mt-4">
        <thead>
            <tr>
                <th class="border px-4 py-2">ID</th>
                <th class="border px-4 py-2">Nombre</th>
                <th class="border px-4 py-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $usuario)
                <tr>
                    <td class="border px-4 py-2">{{ $usuario->id }}</td>
                    <td class="border px-4 py-2">{{ $usuario->username }}</td>
                    <td class="border px-4 py-2">
                        <a href="{{ route('usuarios.edit-password', $usuario) }}" class="bg-yellow-500 text-white px-2 py-1 rounded">Cambiar Clave</a>
                        <form action="{{ route('usuarios.destroy', $usuario) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-2 py-1 rounded" onclick="return confirm('¿Está seguro de eliminar este usuario?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-app-layout>