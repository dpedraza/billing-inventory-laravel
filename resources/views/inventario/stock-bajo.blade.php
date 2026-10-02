@extends('layouts.app')

@section('title', 'Productos con Stock Bajo')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Productos con Stock Bajo</h1>
        </div>

        <div class="border-b border-gray-200">
            <nav class="flex gap-6">
                <a href="{{ route('inventario.index') }}" class="pb-3 text-sm font-medium border-b-2 {{ request()->routeIs('inventario.index') ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">
                    Stock General
                </a>
                <a href="{{ route('inventario.stock-bajo') }}" class="pb-3 text-sm font-medium border-b-2 {{ request()->routeIs('inventario.stock-bajo') ? 'text-indigo-600 border-indigo-600' : 'text-gray-500 border-transparent hover:text-gray-700 hover:border-gray-300' }}">
                    Stock Bajo
                </a>
            </nav>
        </div>

        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <span>Hay <strong>{{ $productos->total() }}</strong> productos con stock por debajo del mínimo</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                            <th class="px-6 py-3">SKU</th>
                            <th class="px-6 py-3">Nombre</th>
                            <th class="px-6 py-3">Categoría</th>
                            <th class="px-6 py-3 text-right">Stock Actual</th>
                            <th class="px-6 py-3 text-right">Stock Mínimo</th>
                            <th class="px-6 py-3 text-right">Precio Costo</th>
                            <th class="px-6 py-3 text-right">Precio Venta</th>
                            <th class="px-6 py-3 text-right">Valor Total</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($productos as $producto)
                            <tr class="hover:bg-gray-50 transition-colors border-l-4 border-l-red-500 bg-red-50/30">
                                <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $producto->sku }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $producto->nombre }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $producto->categoria?->nombre ?? '—' }}</td>
                                <td class="px-6 py-4 text-right text-red-600 font-bold">{{ number_format($producto->stock_actual) }}</td>
                                <td class="px-6 py-4 text-right text-gray-500">{{ number_format($producto->stock_minimo) }}</td>
                                <td class="px-6 py-4 text-right text-gray-900">$ {{ number_format($producto->precio_costo, 2) }}</td>
                                <td class="px-6 py-4 text-right text-gray-900">$ {{ number_format($producto->precio_venta, 2) }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-900">$ {{ number_format($producto->stock_actual * $producto->precio_costo, 2) }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('inventario.kardex', $producto) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition-colors">
                                        Kardex
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    <p class="text-sm">No hay productos con stock bajo.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($productos->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $productos->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
