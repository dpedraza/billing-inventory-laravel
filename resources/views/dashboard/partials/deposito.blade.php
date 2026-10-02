<div class="space-y-6">

    <!-- DEPÓSITO FOCUS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Stock Crítico -->
        <x-card-kpi 
            title="Productos en Stock Crítico" 
            :value="(string) count($stockCritico ?? [])" 
            :decimals="null" 
            subtitle="Por debajo del punto de reabastecimiento" 
            badgeText="Urgente" 
            badgeType="danger" 
            type="alert" 
        />

        <!-- Compras Esperando Recepción -->
        <x-card-kpi 
            title="Compras Esperando Ingreso" 
            :value="(string) count($comprasPendientes ?? [])" 
            :decimals="null" 
            subtitle="Mercadería pendiente de recepción física" 
            badgeText="En camino" 
            badgeType="warning" 
            type="inventory" 
        />

        <!-- Sin Movimiento -->
        <x-card-kpi 
            title="Productos Sin Movimiento (+30d)" 
            :value="(string) count($sinMovimiento ?? [])" 
            :decimals="null" 
            subtitle="Inactivos en estantería" 
            badgeText="Inmovilizado" 
            badgeType="neutral" 
            type="activity" 
        />
    </div>

    <!-- CRITICAL STOCK REPLACEMENT TABLE -->
    <x-card-resumen title="Artículos con Necesidad de Reposición Urgente" subtitle="Productos donde el stock actual es menor o igual al mínimo definido" :actionUrl="route('inventario.index')">
        @if(count($stockCritico ?? []))
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-mono uppercase text-[10px] border-b border-slate-100">
                            <th class="pb-2">SKU</th>
                            <th class="pb-2">Producto</th>
                            <th class="pb-2 text-right">Stock Actual</th>
                            <th class="pb-2 text-right">Stock Mínimo</th>
                            <th class="pb-2 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($stockCritico as $prod)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-2.5 font-mono text-slate-500">{{ $prod->sku }}</td>
                                <td class="py-2.5 font-medium text-slate-900">{{ $prod->nombre }}</td>
                                <td class="py-2.5 text-right font-mono font-bold text-red-600">{{ number_format($prod->stock_actual, 2) }}</td>
                                <td class="py-2.5 text-right font-mono text-slate-500">{{ number_format($prod->stock_minimo, 2) }}</td>
                                <td class="py-2.5 text-center">
                                    @can('create', App\Models\Compra::class)
                                    <a href="{{ route('compras.create') }}" class="inline-block bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-semibold px-2 py-1 rounded transition-colors">Generar Compra</a>
                                    @else
                                    <a href="{{ route('productos.index') }}" class="inline-block bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-semibold px-2 py-1 rounded transition-colors">Ver Producto</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-xs text-emerald-600 font-medium py-6 text-center">No hay productos en nivel de stock crítico.</p>
        @endif
    </x-card-resumen>

    <!-- RECENT KARDEX MOVEMENTS FOR DEPÓSITO -->
    <x-card-actividad title="Últimos Movimientos de Almacén" subtitle="Historial de entradas, salidas y transferencias">
        @if(count($movimientosRecientes ?? []))
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-mono uppercase text-[10px] border-b border-slate-100">
                            <th class="pb-2">SKU / Producto</th>
                            <th class="pb-2">Tipo</th>
                            <th class="pb-2 text-right">Cantidad</th>
                            <th class="pb-2 text-right">Stock Resultante</th>
                            <th class="pb-2 text-right">Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($movimientosRecientes as $mov)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-2.5 font-medium text-slate-900">
                                    <span class="font-mono text-slate-500 mr-2">{{ $mov->producto->sku ?? 'SKU' }}</span> {{ $mov->producto->nombre ?? 'Producto' }}
                                </td>
                                <td class="py-2.5">
                                    @php
                                        $tipoVal = is_object($mov->tipo_movimiento) ? $mov->tipo_movimiento->value : $mov->tipo_movimiento;
                                        $isEntrada = in_array($tipoVal, ['compra', 'ajuste_entrada', 'anulacion_venta']);
                                        $isSalida = in_array($tipoVal, ['venta', 'ajuste_salida', 'anulacion_compra']);
                                    @endphp
                                    <span class="px-2 py-0.5 text-[10px] font-mono rounded font-semibold border
                                        @if($isEntrada) bg-emerald-50 text-emerald-700 border-emerald-200
                                        @elseif($isSalida) bg-red-50 text-red-700 border-red-200
                                        @else bg-amber-50 text-amber-700 border-amber-200 @endif">
                                        {{ strtoupper(str_replace('_', ' ', $tipoVal)) }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right font-mono font-bold @if($mov->cantidad > 0) text-emerald-600 @else text-red-600 @endif">
                                    {{ $mov->cantidad > 0 ? '+' : '' }}{{ number_format($mov->cantidad, 2) }}
                                </td>
                                <td class="py-2.5 text-right font-mono font-bold text-slate-900">{{ number_format($mov->saldo_posterior, 2) }}</td>
                                <td class="py-2.5 text-right font-mono text-slate-400">{{ $mov->created_at->format('d/m H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-xs text-slate-400 py-6 text-center">No hay movimientos recientes en almacén.</p>
        @endif
    </x-card-actividad>

</div>
