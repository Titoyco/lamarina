<x-app-layout>
    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-tight">Mi Perfil de Usuario</h1>
                    <p class="text-xs text-slate-500 font-medium">Gestione su nombre, nombre de usuario y clave de acceso al sistema</p>
                </div>
            </div>

            @if(session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/60 border-l-4 border-rose-500 rounded-r-2xl text-rose-800 dark:text-rose-200 text-xs font-bold shadow-xs">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Datos de Usuario (Nombre y Username) -->
            <div class="p-6 sm:p-8 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Cambio de Clave de Acceso -->
            <div class="p-6 sm:p-8 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Eliminación de Cuenta (solo para usuarios no-superusuarios) -->
            @if(auth()->user()->username !== 'superuser')
                <div class="p-6 sm:p-8 bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
