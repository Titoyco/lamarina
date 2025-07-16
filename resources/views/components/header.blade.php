<header>
    <div class="bg-slate-500 h-auto sm:h-20">
        <div class="flex flex-col sm:flex-row items-center justify-between bg-slate-300 h-auto sm:h-20">
            <!-- Logo -->
            <a href="{{ url('/') }}" class="flex-shrink-0">
                <img class="h-16 sm:h-20 min-w-min mr-0 sm:mr-4 p-2" src="{{ asset('images/logo.png') }}" alt="La Marina">
            </a>
            <!-- Texto central -->
            <div class="flex-grow text-center text-lg sm:text-2xl font-bold mt-2 sm:mt-0">
                SISTEMA DE VENTAS V3.0
            </div>
            <!-- Recuadro: Usuario y Sucursal -->
            <div class="flex-shrink-0 flex flex-col items-center justify-center sm:justify-end mt-2 sm:mt-0 w-full sm:w-auto min-w-[150px]">
                @auth
                <!-- Contenedor Unificado -->
                <div class="bg-gray-700 text-white p-2 rounded-lg w-full sm:w-auto mr-1">
                    <!-- Usuario -->
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm sm:text-base">Usuario: {{ auth()->user()->username }}</span>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="ml-2">
                            @csrf
                            <button type="submit" class="w-20 ml-2 bg-blue-500 text-white border border-transparent hover:bg-blue-700 transition duration-200 px-1 py-0 rounded-md text-sm sm:text-base">
                                SALIR
                            </button>
                        </form>
                    </div>

                    <!-- Sucursal Activa -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm sm:text-base">Sucursal: {{ \App\Models\Sucursal::find(session('sucursal_activa'))->nombre ?? 'N/A' }}</span>
                        <button class=" w-20 ml-2 bg-blue-500 text-white border border-transparent hover:bg-blue-700 transition duration-200 px-1 py-0 rounded-md text-sm sm:text-base"
                            onclick="document.getElementById('cambiar-sucursal-modal').classList.toggle('hidden')">
                            Cambiar
                        </button>
                    </div>
                </div>

                <!-- Modal para cambiar sucursal -->
                <div id="cambiar-sucursal-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
                    <!-- Fondo oscuro semitransparente -->
                    <div class="absolute inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm"></div>

                    <!-- Contenido del modal -->
                    <div class="relative bg-white p-6 rounded-lg shadow-lg max-w-sm w-full z-50">
                        <h2 class="text-lg font-bold mb-4 text-gray-800">Cambiar Sucursal</h2>
                        <form action="{{ route('cambiar.sucursal') }}" method="POST">
                            @csrf
                            <select name="sucursal_id" class="border border-gray-300 rounded-md p-2 w-full mb-4">
                                @foreach (auth()->user()->sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}" @if ($sucursal->id == session('sucursal_activa')) selected @endif>
                                        {{ $sucursal->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition">
                                    Cambiar
                                </button>
                                <button type="button" onclick="document.getElementById('cambiar-sucursal-modal').classList.toggle('hidden')" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @else
                <!-- Contenedor vacío que mantiene el diseño -->
                <div class="min-w-[150px]"></div>
                @endauth
            </div>
        </div>
    </div>
</header>