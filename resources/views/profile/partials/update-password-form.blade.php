<section>
    <header>
        <h2 class="text-lg font-black text-slate-900 dark:text-white uppercase tracking-tight">
            Actualizar Clave de Acceso
        </h2>

        <p class="mt-1 text-xs text-slate-500 font-medium">
            Asegúrese de utilizar una contraseña segura. La clave se almacena encriptada en la base de datos con algoritmo Bcrypt.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Clave Actual" class="text-xs uppercase font-bold" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-2xl font-mono" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nueva Clave" class="text-xs uppercase font-bold" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-2xl font-mono" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar Nueva Clave" class="text-xs uppercase font-bold" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-2xl font-mono" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button class="rounded-xl px-5 py-2.5 uppercase font-black text-xs tracking-wider">
                Actualizar Clave
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400"
                >Clave actualizada correctamente.</p>
            @endif
        </div>
    </form>
</section>
