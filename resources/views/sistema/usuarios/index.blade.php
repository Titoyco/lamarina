<x-app-layout>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Operadores del Sistema</h1>
            <p class="text-slate-500 dark:text-slate-400 font-medium text-sm">Administración de perfiles, nombres de usuario, claves y sucursales</p>
        </div>
        
        <a href="{{ route('usuarios.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-2xl font-black shadow-lg hover:shadow-xl transition-all flex items-center space-x-2 active:scale-95 text-xs uppercase tracking-wider">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nuevo Operador</span>
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/60 border-l-4 border-emerald-500 rounded-r-2xl text-emerald-800 dark:text-emerald-200 text-xs font-bold shadow-xs">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/60 border-l-4 border-rose-500 rounded-r-2xl text-rose-800 dark:text-rose-200 text-xs font-bold shadow-xs">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($usuarios as $usuario)
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden">
            <div>
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-2xl {{ $usuario->is_admin ? 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300' }} flex items-center justify-center font-black text-base uppercase shrink-0">
                        {{ mb_substr($usuario->name, 0, 2) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-black text-slate-900 dark:text-white uppercase leading-tight truncate">{{ $usuario->name }}</h3>
                        <p class="text-blue-600 dark:text-blue-400 font-mono text-xs font-bold mt-0.5">{{ '@' . $usuario->username }}</p>
                    </div>
                </div>

                <div class="bg-slate-50 dark:bg-slate-900/60 rounded-2xl p-4 mb-4 flex justify-between items-center border border-slate-100 dark:border-slate-700/60">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase leading-none mb-1">Sucursal Asignada</p>
                        <p class="text-slate-800 dark:text-slate-200 font-bold text-xs truncate">
                            {{ $usuario->sucursal->nombre ?? 'Sin sucursal fija' }}
                        </p>
                    </div>
                    @if($usuario->is_admin)
                        <span class="bg-purple-100 dark:bg-purple-950/70 text-purple-800 dark:text-purple-300 border border-purple-200 dark:border-purple-800 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-wider">
                            Admin
                        </span>
                    @else
                        <span class="bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase tracking-wider">
                            Operador
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-2 border-t border-slate-100 dark:border-slate-700/60">
                <a href="{{ route('usuarios.edit', $usuario->id) }}" class="flex-1 bg-slate-100 dark:bg-slate-700 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 text-slate-700 dark:text-slate-200 text-center py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider transition-all">
                    Editar Perfil
                </a>

                @if($usuario->id !== auth()->id())
                    <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar al operador {{ $usuario->name }} ({{ '@' . $usuario->username }})?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Eliminar operador" class="bg-slate-100 dark:bg-slate-700 hover:bg-rose-600 hover:text-white text-slate-500 dark:text-slate-400 p-2.5 rounded-xl transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</x-app-layout>