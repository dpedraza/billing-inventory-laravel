@props([
    'title',
    'value',
    'decimals' => '00',
    'subtitle' => null,
    'badgeText' => null,
    'badgeType' => 'neutral',
    'type' => 'financial',
])

@php
    $signatureClass = match($type) {
        'financial' => 'card-signature-financial',
        'inventory' => 'card-signature-inventory',
        'alert' => 'card-signature-alert',
        default => 'card-signature-activity',
    };

    $badgeClass = match($badgeType) {
        'success' => 'bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200',
        'warning' => 'bg-amber-50 text-amber-800 font-semibold border border-amber-200',
        'danger' => 'bg-red-50 text-red-700 font-semibold border border-red-200',
        default => 'bg-slate-100 text-slate-600 border border-slate-200',
    };
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-xl p-5 border border-slate-200 {$signatureClass} shadow-xs relative overflow-hidden group hover:border-slate-300 transition-all"]) }}>
    <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-2">
        <span>{{ $title }}</span>
        @if($badgeText)
            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded {{ $badgeClass }}">{{ $badgeText }}</span>
        @endif
    </div>

    <div class="flex items-baseline gap-1 my-1">
        @if($type === 'financial')
            <span class="text-xs text-slate-400 font-mono font-medium">$</span>
        @endif
        <span class="text-2xl font-mono font-bold text-slate-900 tracking-tight tabular-nums">{{ $value }}</span>
        @if($type === 'financial' && $decimals !== null)
            <span class="text-xs font-mono text-slate-400">,{{ $decimals }}</span>
        @endif
    </div>

    @if($subtitle)
        <div class="text-[11px] text-slate-500 pt-2 border-t border-slate-100 mt-3 flex items-center justify-between">
            <span>{{ $subtitle }}</span>
        </div>
    @endif
</div>
