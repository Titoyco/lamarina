<nav class=" text-black bg-gray-400 dark:text-white/50 p-1 space-x-1 relative flex items-center justify-between w-full h-10 border-b border-gray-500 mb-4">
    <x-boton :url="'javascript:history.back()'" :color="'bg-gray-500'" :texto="'< Volver'" />
    @if (!empty($botones) && count($botones) > 0)
    <div class="relative">
        <!-- Botón para abrir el menú -->
        <button 
            onclick="toggleMenu(event, 'principal')" 
            class="bg-blue-500 text-white px-2 py-1 rounded-md hover:bg-blue-700  left-0  w-40"
        >
            Menú
        </button>

        <!-- Menú desplegable -->
        <div id="menu-principal" class="hidden absolute top-full left-0 bg-white border border-gray-300 rounded-md shadow-lg z-10 w-40">
            <ul>
                @foreach ($botones as $boton)
                    <li>
                        <a 
                            href="{{ $boton['url'] }}" 
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-200 transition"
                        >
                            {{ $boton['texto'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</nav>


<script>
    function toggleMenu(event, id) {
        event.stopPropagation(); // Evita que el evento se propague al documento
        const menu = document.getElementById('menu-' + id);
        const isVisible = menu.classList.contains('hidden');
        
        // Oculta todos los menús
        document.querySelectorAll('.absolute').forEach(function(el) {
            el.classList.add('hidden');
        });
        
        // Muestra u oculta el menú correspondiente
        if (isVisible) {
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }
    }

    // Cierra el menú al hacer clic fuera de él
    document.addEventListener('click', function() {
        document.querySelectorAll('.absolute').forEach(function(el) {
            el.classList.add('hidden');
        });
    });
</script>