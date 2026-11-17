<x-app-layout>
    <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Encabezado Principal y Botón Volver --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('sistema.index') }}" class="p-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-2xl transition text-slate-600 dark:text-slate-300 shadow-xs">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        <span>Copias de Seguridad (Backup)</span>
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        Base de Datos Activa: <strong class="text-slate-700 dark:text-slate-200 font-mono">{{ $databaseName }}</strong> — Resguardo integral en archivo ZIP y restauración con copia preventiva automática.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold px-3 py-1 rounded-full {{ $canRestore ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border border-blue-200 dark:border-blue-800' }}">
                    {{ $canRestore ? 'Privilegios de Administración (Respaldo y Restauración)' : 'Modo Operador (Generación y Descarga de Respaldo)' }}
                </span>
            </div>
        </div>

        {{-- Alertas de Éxito y Error --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/60 border-l-4 border-emerald-500 rounded-r-2xl text-emerald-800 dark:text-emerald-200 text-xs font-bold shadow-xs flex items-start gap-3">
                <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-950/60 border-l-4 border-rose-500 rounded-r-2xl text-rose-800 dark:text-rose-200 text-xs font-bold shadow-xs flex items-start gap-3">
                <svg class="h-5 w-5 text-rose-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-950/60 border-l-4 border-rose-500 rounded-r-2xl text-xs text-rose-800 dark:text-rose-200 space-y-1">
                <span class="font-bold uppercase block">Errores detectados:</span>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Panel de Acciones en 2 Columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- 1. Tarjeta Generar Respaldo (Cualquier Usuario) --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                Crear y Descargar Copia (.ZIP)
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Exporta todas las tablas, relaciones y datos actuales
                            </p>
                        </div>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-900/60 rounded-2xl p-4 mb-6 border border-slate-100 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-300 space-y-2">
                        <div class="flex items-center gap-2 text-slate-800 dark:text-slate-200 font-bold">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>Volcado completo de datos y estructura</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Incluye el padrón de clientes, productos, existencias en todas las sucursales, créditos, ventas históricas, cuotas y registros de auditoría.
                        </p>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            El archivo se comprime con algoritmo ZIP de alta compresión para que la descarga sea liviana y rápida.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('sistema.backup.generate') }}" id="formGenerarBackup"
                      onsubmit="document.getElementById('btnGenerar').disabled = true; document.getElementById('txtBtnGenerar').innerText = 'Generando y Descargando Respaldo...';">
                    @csrf
                    <input type="hidden" name="download" value="1">
                    <button type="submit" id="btnGenerar"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3.5 px-4 rounded-2xl transition duration-200 shadow-md hover:shadow-lg uppercase tracking-wider text-xs flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span id="txtBtnGenerar">GENERAR Y DESCARGAR COPIA (.ZIP)</span>
                    </button>
                </form>
            </div>

            {{-- 2. Tarjeta Restaurar Base de Datos (Solo Admins / Superuser) --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 rounded-2xl {{ $canRestore ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400' : 'bg-slate-100 dark:bg-slate-700 text-slate-400' }} flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">
                                Restaurar Base de Datos
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $canRestore ? 'Sobrescribe los datos actuales a partir de una copia previa' : 'Acceso Restringido a Administradores' }}
                            </p>
                        </div>
                    </div>

                    @if($canRestore)
                        {{-- Garantía de Seguridad Obligatoria --}}
                        <div class="bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/60 rounded-2xl p-4 mb-4 text-xs space-y-2">
                            <div class="flex items-center gap-2 font-black uppercase text-amber-800 dark:text-amber-300">
                                <svg class="w-4 h-4 shrink-0 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span>Garantía de Seguridad Automática</span>
                            </div>
                            <p class="text-amber-900 dark:text-amber-200 leading-snug">
                                <strong>El sistema siempre creará una copia de seguridad automática</strong> de la base de datos actual antes de iniciar la restauración. Si necesita revertir cambios, el estado previo quedará disponible en el historial inferior.
                            </p>
                        </div>

                        <form method="POST" action="{{ route('sistema.backup.restore') }}" enctype="multipart/form-data" id="formRestaurar"
                              onsubmit="return confirm('ATENCIÓN: La restauración reemplazará todas las tablas y datos actuales de la base de datos. Se creará automáticamente un respaldo previo de seguridad. ¿Desea proceder?');">
                            @csrf

                            <div class="mb-4">
                                <label for="backup_file" class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase mb-1">
                                    Seleccionar Archivo de Respaldo (.zip o .sql)
                                </label>
                                <input type="file" id="backup_file" name="backup_file" accept=".zip,.sql" required
                                       class="w-full text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:uppercase file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-slate-700 dark:file:text-slate-200 border border-slate-200 dark:border-slate-700 rounded-2xl bg-slate-50 dark:bg-slate-900 cursor-pointer">
                                <p class="text-[10px] text-slate-400 mt-1">Límite de subida: hasta 512 MB.</p>
                            </div>

                            <div class="mb-5">
                                <label class="flex items-start space-x-2.5 cursor-pointer">
                                    <input type="checkbox" name="confirm_restore" value="1" required
                                           class="rounded border-slate-300 text-rose-600 shadow-sm focus:ring-rose-500 mt-0.5">
                                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold leading-tight">
                                        Confirmo que comprendo que esta acción sobrescribirá la base de datos actual y autorizo la creación previa del resguardo de seguridad.
                                    </span>
                                </label>
                            </div>

                            <button type="submit"
                                    class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-3.5 px-4 rounded-2xl transition duration-200 shadow-md hover:shadow-lg uppercase tracking-wider text-xs flex items-center justify-center gap-2 active:scale-95 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>RESTAURAR BASE DE DATOS</span>
                            </button>
                        </form>
                    @else
                        <div class="p-4 bg-slate-100 dark:bg-slate-900/60 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-xs space-y-2">
                            <p class="font-bold uppercase text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Función Exclusiva para Administradores
                            </p>
                            <p>
                                Su usuario cuenta con permisos para crear y descargar copias de respaldo en cualquier momento, pero la restauración de la base de datos está restringida al Administrador o Superusuario.
                            </p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Tabla de Respaldos Almacenados en el Servidor --}}
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-tight">
                        Historial de Respaldos Disponibles en el Servidor
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Ubicación: <code class="font-mono text-[10px] bg-slate-200 dark:bg-slate-700 px-1.5 py-0.5 rounded text-slate-800 dark:text-slate-200">storage/app/backups</code>
                    </p>
                </div>
                <span class="text-xs font-bold text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-800 px-3 py-1 rounded-full border border-slate-200 dark:border-slate-700">
                    {{ count($backups) }} {{ count($backups) === 1 ? 'archivo' : 'archivos' }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-100/60 dark:bg-slate-800/80 text-slate-600 dark:text-slate-400 font-black uppercase text-[10px] tracking-wider border-b border-slate-200 dark:border-slate-700">
                            <th class="py-3 px-6">Archivo de Respaldo</th>
                            <th class="py-3 px-4">Tipo / Origen</th>
                            <th class="py-3 px-4">Fecha de Creación</th>
                            <th class="py-3 px-4 text-right">Tamaño</th>
                            <th class="py-3 px-6 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($backups as $b)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/30 transition">
                                <td class="py-3.5 px-6 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span class="truncate max-w-xs sm:max-w-md">{{ $b['filename'] }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($b['is_pre_restore'])
                                        <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase bg-amber-100 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800 px-2 py-0.5 rounded-lg">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                            Pre-Restauración
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-[10px] font-black uppercase bg-blue-100 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800 px-2 py-0.5 rounded-lg">
                                            Copia Manual
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-300">
                                    {{ $b['date'] }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $b['size_human'] }}
                                </td>
                                <td class="py-3.5 px-6 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        {{-- Descargar (Todos los usuarios) --}}
                                        <a href="{{ route('sistema.backup.download', $b['filename']) }}"
                                           title="Descargar archivo ZIP"
                                           class="bg-blue-50 dark:bg-slate-700 hover:bg-blue-600 hover:text-white text-blue-700 dark:text-blue-300 p-2 rounded-xl transition font-bold text-xs flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            <span class="hidden sm:inline">Descargar</span>
                                        </a>

                                        {{-- Eliminar (Solo Admins) --}}
                                        @if($canRestore)
                                            <form method="POST" action="{{ route('sistema.backup.destroy', $b['filename']) }}"
                                                  onsubmit="return confirm('¿Está seguro de eliminar el respaldo {{ $b['filename'] }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar respaldo"
                                                        class="bg-slate-100 dark:bg-slate-700 hover:bg-rose-600 hover:text-white text-slate-400 p-2 rounded-xl transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 px-6 text-center text-slate-400 dark:text-slate-500 italic">
                                    Aún no hay copias de seguridad almacenadas en el servidor. Presione el botón "Generar y Descargar Copia" para crear la primera.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
