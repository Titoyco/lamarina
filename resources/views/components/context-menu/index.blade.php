@props([
    'width' => 'w-60',
])

{{-- Menú Contextual Flotante Reutilizable --}}
<div x-show="open"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="transform opacity-0 scale-95"
     x-transition:enter-end="transform opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="transform opacity-100 scale-100"
     x-transition:leave-end="transform opacity-0 scale-95"
     @click.outside="closeMenu()"
     @keydown.escape.window="closeMenu()"
     :style="`top: ${y}px; left: ${x}px;`"
     style="display: none;"
     class="fixed z-50 {{ $width }} bg-white/95 dark:bg-slate-800/95 backdrop-blur-md rounded-xl shadow-2xl border border-slate-200/90 dark:border-slate-700/80 ring-1 ring-black/5 dark:ring-white/10 text-left text-xs divide-y divide-slate-100 dark:divide-slate-700/60 font-sans focus:outline-none select-none overflow-hidden animate-in fade-in zoom-in-95 duration-100">
    
    {{ $slot }}

</div>
