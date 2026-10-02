@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Ventas</h1>
            <a href="{{ route('ventas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nueva Venta
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100">
                <form method="GET" action="{{ route('ventas.index') }}" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" name="search" placeholder="Buscar por cliente o N° comprobante..." value="{{ request('search') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="w-44">
                        <select name="estado" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                            <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                        </select>
                    </div>
                    <div class="w-44">
                        <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Desde">
                    </div>
                    <div class="w-44">
                        <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Hasta">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Filtrar</button>
                        @if(request()->anyFilled(['search', 'estado', 'fecha_desde', 'fecha_hasta']))
                            <a href="{{ route('ventas.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Limpiar</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                            <th class="px-6 py-3">N° Comprobante</th>
                            <th class="px-6 py-3">Tipo</th>
                            <th class="px-6 py-3">Cliente</th>
                            <th class="px-6 py-3">Fecha</th>
                            <th class="px-6 py-3 text-right">Subtotal</th>
                            <th class="px-6 py-3 text-right">IVA</th>
                            <th class="px-6 py-3 text-right">Total</th>
                            <th class="px-6 py-3">Estado</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($ventas as $venta)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $venta->numero_comprobante }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $venta->tipoComprobante?->nombre ?? '—' }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $venta->cliente?->razon_social ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $venta->fecha_emision?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-6 py-4 text-right text-gray-900">$ {{ number_format($venta->subtotal, 2) }}</td>
                                <td class="px-6 py-4 text-right text-gray-600">$ {{ number_format($venta->iva, 2) }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">$ {{ number_format($venta->total, 2) }}</td>
                                <td class="px-6 py-4">
                                    <x-badge-estado :status="$venta->estado" />
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('ventas.show', $venta) }}" class="p-1.5 text-gray-400 hover:text-blue-600 transition-colors" title="Ver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        @php
                                            $estadoVal = $venta->estado->value ?? (string) $venta->estado;
                                        @endphp
                                        @if($estadoVal === 'pendiente')
                                            <form method="POST" action="{{ route('ventas.confirmar', $venta) }}" x-data @submit.prevent="if(confirm('¿Confirmar la venta {{ $venta->numero_comprobante }}? Se actualizará el stock.')) $el.submit()">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-green-600 transition-colors" title="Confirmar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                        @if($estadoVal === 'pagada')
                                            <form method="POST" action="{{ route('ventas.anular', $venta) }}" x-data @submit.prevent="if(confirm('¿Anular la venta {{ $venta->numero_comprobante }}? Se revertirá el stock.')) $el.submit()">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors" title="Anular">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                    <p class="text-sm">No hay ventas registradas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ventas->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $ventas->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
