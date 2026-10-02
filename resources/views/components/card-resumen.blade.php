@props([
    'title',
    'subtitle' => null,
    'actionUrl' => null,
    'actionText' => 'Ver todos',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200 shadow-xs p-5']) }}>
    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-900">{{ $title }}</h3>
            @if($subtitle)
                <p class="text-xs text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
        @if($actionUrl)
            <a href="{{ $actionUrl }}" class="text-xs font-semibold text-teal-600 hover:text-teal-800 transition-colors">{{ $actionText }}</a>
        @endif
    </div>

    <div>
        {{ $slot }}
    </div>
</div>
