@extends('layouts.app')

@section('title', 'Venta N° ' . $venta->numero_comprobante)

@section('content')
    @php
        $estado = $venta->estado->value ?? (string) $venta->estado;
    @endphp
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Venta N° {{ $venta->numero_comprobante }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $venta->tipoComprobante?->nombre ?? 'Comprobante' }} — {{ $venta->fecha_emision?->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($estado === 'pendiente')
                    <form method="POST" action="{{ route('ventas.confirmar', $venta) }}" x-data @submit.prevent="if(confirm('¿Confirmar la venta? Se actualizará el stock.')) $el.submit()">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Confirmar
                        </button>
                    </form>
                @endif
                @if($estado === 'pagada')
                    <form method="POST" action="{{ route('ventas.anular', $venta) }}" x-data @submit.prevent="if(confirm('¿Anular la venta? Se revertirá el stock.')) $el.submit()">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Anular
                        </button>
                    </form>
                @endif
                <a href="{{ route('ventas.pdf', $venta) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF
                </a>
                <a href="{{ route('ventas.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Volver
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Información de la Venta</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">N° Comprobante</dt>
                            <dd class="mt-1 text-sm font-mono text-gray-900">{{ $venta->numero_comprobante }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $venta->tipoComprobante?->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $venta->cliente?->razon_social ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha de Emisión</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $venta->fecha_emision?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</dt>
                            <dd class="mt-1">
                                <x-badge-estado :status="$venta->estado" />
                            </dd>
                        </div>
                        @if($venta->notas)
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $venta->notas }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Productos</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                                    <th class="px-4 py-3">Producto</th>
                                    <th class="px-4 py-3 text-right">Cantidad</th>
                                    <th class="px-4 py-3 text-right">Precio Unitario</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($venta->items as $detalle)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $detalle->producto?->nombre ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right text-gray-600">{{ number_format($detalle->cantidad, 2) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-600">$ {{ number_format($detalle->precio_unitario, 2) }}</td>
                                        <td class="px-4 py-3 text-right font-medium text-gray-900">$ {{ number_format($detalle->subtotal, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Sin productos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Totales</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">Subtotal</dt>
                            <dd class="font-medium text-gray-900">$ {{ number_format($venta->subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">IVA</dt>
                            <dd class="font-medium text-gray-900">$ {{ number_format($venta->impuesto, 2) }}</dd>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex justify-between text-base">
                            <dt class="font-semibold text-gray-900">Total</dt>
                            <dd class="font-bold text-indigo-600">$ {{ number_format($venta->total, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Cliente</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Razón Social</dt>
                            <dd class="mt-1 text-gray-900">{{ $venta->cliente?->razon_social ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">CUIT/DNI</dt>
                            <dd class="mt-1 font-mono text-gray-900">{{ $venta->cliente?->cuit_dni ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Condición IVA</dt>
                            <dd class="mt-1 text-gray-900">{{ $venta->cliente?->condicionIva?->nombre ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
