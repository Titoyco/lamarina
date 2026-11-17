@props([
    'label' => null,
])

<div class="py-1">
    @if($label)
        <div class="px-3 py-1 text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
            {{ $label }}
        </div>
    @endif
    {{ $slot }}
</div>
