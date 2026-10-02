@props([
    'title' => 'Últimos Movimientos de Kardex',
    'subtitle' => 'Trazabilidad de entradas, salidas y ajustes de stock',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-xs p-5 card-signature-activity']) }}>
    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-xs text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        <span class="text-xs font-mono text-slate-400">Kardex en tiempo real</span>
    </div>

    <div>
        {{ $slot }}
    </div>
</div>
