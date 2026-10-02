@extends('layouts.app')

@section('title', "Kardex: {$producto->nombre}")

@section('content')
    <div class="space-y-6">
        <div>
            <a href="{{ route('inventario.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al inventario
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Kardex: {{ $producto->nombre }}</h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="grid grid-cols-3 gap-6">
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</span>
                    <p class="mt-1 text-sm font-mono text-gray-900">{{ $producto->sku }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Actual</span>
                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ number_format($producto->stock_actual, 2) }}</p>
                </div>
                <div>
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Mínimo</span>
                    <p class="mt-1 text-sm text-gray-900">{{ number_format($producto->stock_minimo, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-4 border-b border-gray-100">
                <form method="GET" action="{{ route('inventario.kardex', $producto) }}" class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                        <input type="date" name="desde" value="{{ request('desde') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                        <input type="date" name="hasta" value="{{ request('hasta') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tipo Movimiento</label>
                        <select name="tipo_movimiento" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Todos</option>
                            @foreach(\App\Enums\MovimientoTipo::cases() as $tipo)
                                <option value="{{ $tipo->value }}" {{ request('tipo_movimiento') == $tipo->value ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $tipo->value)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2 items-end">
                        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Filtrar</button>
                        @if(request()->anyFilled(['desde', 'hasta', 'tipo_movimiento']))
                            <a href="{{ route('inventario.kardex', $producto) }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Limpiar</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                            <th class="px-6 py-3">Fecha</th>
                            <th class="px-6 py-3">Tipo Movimiento</th>
                            <th class="px-6 py-3">Referencia</th>
                            <th class="px-6 py-3 text-right">Cantidad</th>
                            <th class="px-6 py-3 text-right">Costo Unitario</th>
                            <th class="px-6 py-3 text-right">Saldo Anterior</th>
                            <th class="px-6 py-3 text-right">Saldo Posterior</th>
                            <th class="px-6 py-3">Usuario</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($movimientos as $movimiento)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-gray-600 whitespace-nowrap">{{ $movimiento->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $tipoClass = match($movimiento->tipo_movimiento->value) {
                                            'compra', 'ajuste_entrada', 'anulacion_venta' => 'text-green-700 bg-green-50',
                                            'venta', 'ajuste_salida', 'anulacion_compra' => 'text-red-700 bg-red-50',
                                            default => 'text-gray-700 bg-gray-50',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tipoClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $movimiento->tipo_movimiento->value)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-500">
                                    {{ $movimiento->referencia_id ? '#' . $movimiento->referencia_id : '—' }}
                                </td>
                                <td class="px-6 py-4 text-right font-semibold {{ $movimiento->cantidad > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movimiento->cantidad > 0 ? '+' : '' }}{{ number_format($movimiento->cantidad, 2) }}
                                </td>
                                <td class="px-6 py-4 text-right text-gray-900">$ {{ number_format($movimiento->costo_unitario, 2) }}</td>
                                <td class="px-6 py-4 text-right text-gray-600">{{ number_format($movimiento->saldo_anterior, 2) }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">{{ number_format($movimiento->saldo_posterior, 2) }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $movimiento->creador?->name ?? 'Sistema' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    <p class="text-sm">No se encontraron movimientos para este producto.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($movimientos->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $movimientos->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
