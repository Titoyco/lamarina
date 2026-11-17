<x-app-layout>
    <div class="max-w-2xl mx-auto py-4">
        <!-- Encabezado y volver -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3">
                <a href="{{ route('usuarios.index') }}" class="p-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-xl transition text-slate-600 dark:text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Editar Perfil de Operador</h1>
                    <p class="text-xs text-slate-500 font-medium">Modifique los datos de acceso, clave y sucursal asignada</p>
                </div>
            </div>

            @if($usuario->id !== auth()->id())
                <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de que desea eliminar a este operador? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:hover:bg-rose-900/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800 text-xs font-bold py-2 px-3 rounded-xl transition uppercase flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Eliminar</span>
                    </button>
                </form>
            @endif
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/60 border-l-4 border-rose-500 rounded-r-xl text-xs text-rose-800 dark:text-rose-200 space-y-1">
                <span class="font-bold uppercase block">Por favor corrija los siguientes errores:</span>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Tarjeta del Formulario -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 sm:p-8">
            <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Nombre Completo / Visual -->
                <div>
                    <label for="name" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase mb-1 tracking-wider">
                        Nombre y Apellido / Descripción
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $usuario->name) }}" required
                           class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-blue-600 focus:bg-white text-sm transition">
                    <p class="text-[11px] text-slate-400 mt-1">Nombre visible en los tickets, comprobantes y terminales de venta.</p>
                </div>

                <!-- Nombre de Usuario (Login) -->
                <div>
                    <label for="username" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase mb-1 tracking-wider">
                        Nombre de Usuario (Para Iniciar Sesión)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">@</span>
                        <input type="text" id="username" name="username" value="{{ old('username', $usuario->username) }}" required
                               class="w-full pl-8 pr-4 py-3 rounded-2xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono font-bold focus:ring-2 focus:ring-blue-600 focus:bg-white text-sm transition">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Identificador único con el que el operador ingresa al sistema.</p>
                </div>

                <!-- Nueva Clave -->
                <div x-data="{ verPass: false }">
                    <label for="password" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase mb-1 tracking-wider">
                        Nueva Clave de Acceso
                    </label>
                    <div class="relative">
                        <input :type="verPass ? 'text' : 'password'" id="password" name="password" autocomplete="new-password"
                               placeholder="Dejar en blanco para mantener la clave actual"
                               class="w-full pr-12 pl-4 py-3 rounded-2xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-mono focus:ring-2 focus:ring-blue-600 focus:bg-white text-sm transition">
                        <button type="button" @click="verPass = !verPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <span x-show="!verPass" class="text-xs uppercase font-bold">Ver</span>
                            <span x-show="verPass" class="text-xs uppercase font-bold" style="display: none;">Ocultar</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Si no desea cambiar la clave, deje este campo vacío. Al modificarla se encripta automáticamente en la base de datos con Bcrypt.
                    </p>
                </div>

                <!-- Sucursal Asignada -->
                <div>
                    <label for="id_sucursal" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase mb-1 tracking-wider">
                        Sucursal Asignada
                    </label>
                    <select id="id_sucursal" name="id_sucursal" required
                            class="w-full px-4 py-3 rounded-2xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-blue-600 focus:bg-white text-sm transition">
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" {{ old('id_sucursal', $usuario->id_sucursal) == $suc->id ? 'selected' : '' }}>
                                {{ $suc->nombre }} ({{ $suc->codigo_sucursal }}) — {{ $suc->provincia?->nombre ?? 'Sin provincia' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Nivel de Acceso (Admin / Operador) -->
                <div class="pt-2 border-t border-slate-100 dark:border-slate-700">
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $usuario->is_admin) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-blue-600 shadow-sm focus:ring-blue-500 mt-1">
                        <div>
                            <span class="text-xs font-black text-slate-900 dark:text-white uppercase block">Privilegios de Administrador</span>
                            <span class="text-[11px] text-slate-500 block">
                                Permite cambiar libremente entre sucursales, ver módulos del sistema, configurar tablas y auditar operaciones.
                            </span>
                        </div>
                    </label>
                </div>

                <!-- Botones de Acción -->
                <div class="pt-4 flex items-center justify-end space-x-3">
                    <a href="{{ route('usuarios.index') }}" class="px-5 py-3 rounded-2xl font-bold text-xs uppercase text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-black px-6 py-3 rounded-2xl shadow-lg hover:shadow-xl transition-all uppercase tracking-wider text-xs active:scale-95">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
