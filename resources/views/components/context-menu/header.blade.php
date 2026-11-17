@props([
    'title' => '',
    'subtitle' => null,
    'badge' => null,
    'badgeColor' => 'blue',
])

@php
    $badgeColors = [
        'blue' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-300 border-blue-200 dark:border-blue-700',
        'rose' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300 border-rose-200 dark:border-rose-700',
        'emerald' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-700',
        'amber' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 border-amber-200 dark:border-amber-700',
        'slate' => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600',
    ][$badgeColor] ?? 'bg-slate-100 text-slate-700 border-slate-200';
@endphp

<div class="px-3.5 py-2.5 bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-100 dark:border-slate-700/60">
    <div class="flex items-start justify-between gap-1.5">
        <div class="min-w-0 flex-1">
            <h4 class="font-bold text-slate-900 dark:text-white text-xs truncate leading-snug uppercase tracking-tight" title="{{ $title }}">
                {{ $title }}
            </h4>
            @if($subtitle)
                <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono truncate mt-0.5">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
        @if($badge)
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border shrink-0 {{ $badgeColors }}">
                {{ $badge }}
            </span>
        @endif
    </div>
</div>
