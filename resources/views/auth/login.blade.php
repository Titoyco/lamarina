<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso - LaMarina</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-200 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md">
        <div class="flex flex-col items-center mb-8">
            <img src="{{ asset('img/logo_letras.png') }}" alt="LaMarina Logo" class="h-20 w-auto mb-2">
            <h1 class="text-2xl font-black text-blue-950 uppercase tracking-tight">LA MARINA</h1>
            <p class="text-blue-700 text-xs font-bold uppercase tracking-widest">Sistema Comercial, Stock y Créditos</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-xl border-t-4 border-blue-700">
            <h2 class="text-xl font-bold text-center text-slate-800 mb-6 uppercase tracking-tight">Iniciar Sesión</h2>

            @if (session('warning'))
                <div class="mb-5 p-3.5 bg-amber-50 border border-amber-300 text-amber-900 rounded-xl text-xs font-semibold flex items-start gap-2.5 shadow-2xs">
                    <span class="text-base leading-none">⚠️</span>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-5 p-3.5 bg-blue-50 border border-blue-300 text-blue-900 rounded-xl text-xs font-semibold flex items-start gap-2.5 shadow-2xs">
                    <span class="text-base leading-none">ℹ️</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 p-3.5 bg-rose-50 border border-rose-300 text-rose-900 rounded-xl text-xs font-semibold flex items-start gap-2.5 shadow-2xs">
                    <span class="text-base leading-none">✕</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="mb-5">
                    <label for="username" class="block text-xs font-black text-slate-700 uppercase mb-2 ml-1">Usuario</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus 
                        class="w-full px-4 py-3 rounded-xl border-slate-300 focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all shadow-sm font-semibold"
                        placeholder="Ingrese su nombre de usuario">
                    @error('username')
                        <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-xs font-black text-slate-700 uppercase mb-2 ml-1">Clave de Acceso</label>
                    
                    <div class="relative flex items-center">
                        <input type="password" id="password" name="password" required 
                            class="w-full px-4 py-3 pr-10 rounded-xl border-slate-300 focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all shadow-sm font-semibold"
                            placeholder="••••••••">
                        
                        <button type="button" onclick="togglePassword()" class="absolute right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 focus:outline-none" tabindex="-1">
                            <svg id="eye-icon" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path id="eye-path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path id="eye-path-2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    @error('password')
                        <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="inline-flex items-center text-xs font-semibold text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2">Recordar sesión</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-black py-3.5 px-4 rounded-xl transition duration-200 shadow-lg uppercase tracking-wider text-sm">
                    INGRESAR AL SISTEMA
                </button>
            </form>
        </div>

        <p class="text-center text-gray-400 text-xs mt-6">
            &copy; {{ date('Y') }} La Marina — Ushuaia & Trelew
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyePath = document.getElementById('eye-path');
            const eyePath2 = document.getElementById('eye-path-2');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyePath.setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18');
                eyePath2.setAttribute('d', '');
            } else {
                passwordInput.type = 'password';
                eyePath.setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
                eyePath2.setAttribute('d', 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z');
            }
        }

        // Si la página se recupera de la caché del navegador (bfcache / botón volver), recargar para obtener token CSRF fresco
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        // Heartbeat para mantener activa la sesión si la pestaña de login queda abierta en el mostrador
        setInterval(function () {
            fetch('{{ route("login") }}', { method: 'HEAD', cache: 'no-store' }).catch(function () {});
        }, 15 * 60 * 1000); // Cada 15 minutos
    </script>
</body>
</html>