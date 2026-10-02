@props([
    'title' => 'Atención requerida',
    'stockCritico' => 0,
    'comprasPendientes' => 0,
    'ventasPendientes' => 0,
])

@php
    $totalAlertas = $stockCritico + $comprasPendientes + $ventasPendientes;
@endphp

@if($totalAlertas > 0)
    <div class="bg-amber-50/90 border border-amber-200 rounded-lg p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900 shadow-xs">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-md bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <div>
                <span class="font-semibold text-amber-950">{{ $title }}:</span>
                <span>
                    @if($comprasPendientes > 0) Existen <strong>{{ $comprasPendientes }} compras pendientes</strong> de recepción. @endif
                    @if($ventasPendientes > 0) Hay <strong>{{ $ventasPendientes }} ventas borrador</strong> por confirmar. @endif
                    @if($stockCritico > 0) Contamos con <strong>{{ $stockCritico }} productos en stock crítico</strong>. @endif
                </span>
            </div>
        </div>
    </div>
@endif
