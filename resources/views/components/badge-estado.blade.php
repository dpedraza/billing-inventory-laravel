@props([
    'status',
])

@php
    $val = is_object($status) && property_exists($status, 'value') ? $status->value : (string) $status;

    $text = match($val) {
        'pagada' => 'PAGADA',
        'completada' => 'COMPLETADA',
        'pendiente' => 'PENDIENTE',
        'anulada' => 'ANULADA',
        'borrador' => 'BORRADOR',
        default => strtoupper($val),
    };

    $classes = match($val) {
        'pagada', 'completada' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
        'pendiente', 'borrador' => 'bg-amber-50 text-amber-800 border-amber-200',
        'anulada' => 'bg-red-50 text-red-800 border-red-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "px-2 py-0.5 text-[10px] font-mono rounded font-semibold border {$classes}"]) }}>
    {{ $text }}
</span>
