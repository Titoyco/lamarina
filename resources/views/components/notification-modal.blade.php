<!-- resources/views/components/notification-modal.blade.php -->
<div x-data="{ 
        show: false, 
        message: '', 
        type: 'success' 
     }"
     x-init="
        @if(session('success'))
            message = '{{ session('success') }}';
            type = 'success';
            show = true;
        @endif
        @if(session('error'))
            message = '{{ session('error') }}';
            type = 'error';
            show = true;
        @endif
        
        if(show) { 
            setTimeout(() => show = false, 5000); 
        }
     "
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed top-5 right-5 z-[9999] max-w-sm w-full bg-white dark:bg-slate-800 shadow-2xl rounded-2xl pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden border-2"
     :class="type === 'success' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/40' : 'border-red-500 bg-red-50/50 dark:bg-red-950/40'"
     style="display: none;">
    
    <div class="p-4 bg-white dark:bg-slate-800">
        <div class="flex items-start">
            <!-- Icono Condicional -->
            <div class="flex-shrink-0">
                <!-- Éxito -->
                <template x-if="type === 'success'">
                    <svg class="h-6 w-6 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
                <!-- Error / Alerta -->
                <template x-if="type === 'error'">
                    <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </template>
            </div>
            
            <!-- Mensaje -->
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-black uppercase tracking-wider" 
                   :class="type === 'success' ? 'text-emerald-800 dark:text-emerald-300' : 'text-red-800 dark:text-red-300'"
                   x-text="type === 'success' ? 'Operación Exitosa' : 'Atención / Error'"></p>
                <p class="mt-1 text-sm font-medium text-slate-700 dark:text-slate-200" x-text="message"></p>
            </div>
            
            <!-- Botón Cerrar Manual -->
            <div class="ml-4 flex-shrink-0 flex">
                <button @click="show = false" type="button" class="bg-transparent rounded-md inline-flex text-slate-400 dark:text-slate-500 hover:text-slate-500 dark:hover:text-slate-300 focus:outline-none">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>