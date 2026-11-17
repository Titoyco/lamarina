@props([
    'href' => null,
    'action' => null,
    'method' => 'POST',
    'icon' => null,
    'color' => 'slate',
    'disabled' => false,
    'confirm' => null,
    'badge' => null,
])

@php
    $colorClasses = [
        'slate' => 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/80 dark:hover:bg-slate-700/60 hover:text-slate-900 dark:hover:text-white',
        'blue' => 'text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 hover:text-blue-900 dark:hover:text-blue-300',
        'emerald' => 'text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-900 dark:hover:text-emerald-300 font-semibold',
        'rose' => 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-700 dark:hover:text-rose-300',
        'amber' => 'text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40 hover:text-amber-900 dark:hover:text-amber-300',
    ][$color] ?? 'text-slate-700 dark:text-slate-200 hover:bg-slate-100/80 dark:hover:bg-slate-700/60';

    $iconColors = [
        'slate' => 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300',
        'blue' => 'text-blue-500 dark:text-blue-400 group-hover:text-blue-600',
        'emerald' => 'text-emerald-500 dark:text-emerald-400 group-hover:text-emerald-600',
        'rose' => 'text-rose-500 dark:text-rose-400 group-hover:text-rose-600',
        'amber' => 'text-amber-500 dark:text-amber-400 group-hover:text-amber-600',
    ][$color] ?? 'text-slate-400';
@endphp

@if($disabled)
    <div class="flex items-center justify-between px-3.5 py-1.5 text-xs text-slate-400 dark:text-slate-500 bg-slate-50/50 dark:bg-slate-900/30 cursor-not-allowed select-none"
         {{ $attributes }}>
        <div class="flex items-center gap-2.5 truncate">
            @if($icon)
                <span class="w-4 h-4 text-slate-300 dark:text-slate-600 shrink-0">
                    @include('components.context-menu.icons.' . $icon, ['class' => 'w-4 h-4'])
                </span>
            @endif
            <span class="truncate">{{ $slot }}</span>
        </div>
        @if($badge)
            <span class="text-[9px] px-1 py-0.2 rounded bg-slate-200/60 dark:bg-slate-800 text-slate-500 font-mono">{{ $badge }}</span>
        @endif
    </div>
@elseif($action)
    <form action="{{ $action }}" method="POST" class="block m-0 p-0" data-context-item>
        @csrf
        @if(in_array(strtoupper($method), ['PUT', 'PATCH', 'DELETE']))
            @method($method)
        @endif
        <button type="submit" 
                @if($confirm) onclick="return confirm('{{ $confirm }}')" @endif
                class="w-full group flex items-center justify-between px-3.5 py-1.5 text-xs transition-colors text-left {{ $colorClasses }}"
                {{ $attributes }}>
            <div class="flex items-center gap-2.5 truncate">
                @if($icon)
                    <span class="w-4 h-4 shrink-0 transition-colors {{ $iconColors }}">
                        @include('components.context-menu.icons.' . $icon, ['class' => 'w-4 h-4'])
                    </span>
                @endif
                <span class="truncate">{{ $slot }}</span>
            </div>
            @if($badge)
                <span class="text-[9px] px-1 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono">{{ $badge }}</span>
            @endif
        </button>
    </form>
@elseif($href)
    <a href="{{ $href }}" 
       data-context-item
       class="group flex items-center justify-between px-3.5 py-1.5 text-xs transition-colors {{ $colorClasses }}"
       {{ $attributes }}>
        <div class="flex items-center gap-2.5 truncate">
            @if($icon)
                <span class="w-4 h-4 shrink-0 transition-colors {{ $iconColors }}">
                    @include('components.context-menu.icons.' . $icon, ['class' => 'w-4 h-4'])
                </span>
            @endif
            <span class="truncate">{{ $slot }}</span>
        </div>
        @if($badge)
            <span class="text-[9px] px-1 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono">{{ $badge }}</span>
        @endif
    </a>
@else
    <button type="button" 
            data-context-item
            @if($confirm) onclick="return confirm('{{ $confirm }}')" @endif
            class="w-full group flex items-center justify-between px-3.5 py-1.5 text-xs transition-colors text-left {{ $colorClasses }}"
            {{ $attributes }}>
        <div class="flex items-center gap-2.5 truncate">
            @if($icon)
                <span class="w-4 h-4 shrink-0 transition-colors {{ $iconColors }}">
                    @include('components.context-menu.icons.' . $icon, ['class' => 'w-4 h-4'])
                </span>
            @endif
            <span class="truncate">{{ $slot }}</span>
        </div>
        @if($badge)
            <span class="text-[9px] px-1 py-0.2 rounded bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-mono">{{ $badge }}</span>
        @endif
    </button>
@endif
