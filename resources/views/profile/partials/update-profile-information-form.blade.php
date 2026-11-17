<section>
    <header>
        <h2 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">
            Información del Perfil
        </h2>

        <p class="mt-1 text-xs text-slate-500 font-medium">
            Actualice su nombre visible y el nombre de usuario utilizado para iniciar sesión en el sistema.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nombre y Apellido / Descripción" class="text-xs uppercase font-bold" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-2xl" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="username" value="Nombre de Usuario (Login)" class="text-xs uppercase font-bold" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">@</span>
                <x-text-input id="username" name="username" type="text" class="pl-8 block w-full rounded-2xl font-mono font-bold" :value="old('username', $user->username)" required autocomplete="username" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('username')" />
            <p class="text-[11px] text-slate-400 mt-1">Identificador único para iniciar sesión.</p>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="rounded-xl px-5 py-2.5 uppercase font-black text-xs tracking-wider">
                Guardar Cambios
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400"
                >Perfil actualizado correctamente.</p>
            @endif
        </div>
    </form>
</section>
