<div class="space-y-6">

    <!-- KPI CARDS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Ventas Período -->
        @php
            $formattedTotal = number_format($resumenVentas['total_ventas'] ?? 0, 2, ',', '.');
            $partsTotal = explode(',', $formattedTotal);
        @endphp
        <x-card-kpi 
            title="Ventas del Período" 
            :value="$partsTotal[0]" 
            :decimals="$partsTotal[1] ?? '00'" 
            :subtitle="($resumenVentas['cantidad_ventas'] ?? 0) . ' transacciones emitidas'" 
            badgeText="En curso" 
            badgeType="success" 
            type="financial" 
        />

        <!-- Ventas Hoy -->
        @php
            $formattedHoy = number_format($resumenVentas['total_ventas_hoy'] ?? 0, 2, ',', '.');
            $partsHoy = explode(',', $formattedHoy);
        @endphp
        <x-card-kpi 
            title="Ventas de Hoy" 
            :value="$partsHoy[0]" 
            :decimals="$partsHoy[1] ?? '00'" 
            :subtitle="($resumenVentas['cantidad_ventas_hoy'] ?? 0) . ' facturas hoy'" 
            badgeText="Hoy" 
            badgeType="neutral" 
            type="financial" 
        />

        <!-- Utilidad Estimada -->
        @php
            $formattedUtilidad = number_format($resumenVentas['utilidad_estimada'] ?? 0, 2, ',', '.');
            $partsUtilidad = explode(',', $formattedUtilidad);
        @endphp
        <x-card-kpi 
            title="Utilidad Estimada" 
            :value="$partsUtilidad[0]" 
            :decimals="$partsUtilidad[1] ?? '00'" 
            subtitle="Margen directo s/costo" 
            badgeText="Margen Bruto" 
            badgeType="neutral" 
            type="inventory" 
        />

        <!-- Valorización Inventario -->
        @php
            $formattedValor = number_format($resumenInventario['valor_inventario'] ?? 0, 2, ',', '.');
            $partsValor = explode(',', $formattedValor);
        @endphp
        <x-card-kpi 
            title="Valor del Inventario" 
            :value="$partsValor[0]" 
            :decimals="$partsValor[1] ?? '00'" 
            :subtitle="($resumenInventario['total_productos'] ?? 0) . ' SKUs totales'" 
            :badgeText="($resumenInventario['stock_bajo'] ?? 0) . ' Stock Crítico'" 
            :badgeType="($resumenInventario['stock_bajo'] ?? 0) > 0 ? 'warning' : 'neutral'" 
            type="inventory" 
        />
    </div>

    <!-- MIDDLE SECTION: TOP CLIENTES & TOP PROVEEDORES -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Clientes -->
        <x-card-resumen title="Top 5 Clientes por Facturación" subtitle="Mayor concentración de ventas en el mes" :actionUrl="route('clientes.index')">
            @if(count($topClientes ?? []))
                <div class="space-y-2.5">
                    @foreach($topClientes as $item)
                        <div class="flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-200">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded bg-slate-100 text-slate-700 font-mono text-xs font-bold flex items-center justify-center">{{ $loop->iteration }}</div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-900">{{ $item->razon_social }}</p>
                                    <p class="text-[10px] font-mono text-slate-400">CUIT: {{ $item->cuit_dni }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-mono font-bold text-slate-900">$ {{ number_format($item->total_facturado, 2, ',', '.') }}</p>
                                <p class="text-[10px] text-slate-400">{{ $item->cantidad_compras }} comprobantes</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 py-6 text-center">No hay clientes con facturación registrada en este período.</p>
            @endif
        </x-card-resumen>

        <!-- Top Proveedores -->
        <x-card-resumen title="Top 5 Proveedores por Compras" subtitle="Volumen de compras confirmadas" :actionUrl="route('proveedores.index')">
            @if(count($topProveedores ?? []))
                <div class="space-y-2.5">
                    @foreach($topProveedores as $item)
                        <div class="flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-200">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded bg-slate-100 text-slate-700 font-mono text-xs font-bold flex items-center justify-center">{{ $loop->iteration }}</div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-900">{{ $item->razon_social }}</p>
                                    <p class="text-[10px] font-mono text-slate-400">CUIT: {{ $item->cuit_dni }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-mono font-bold text-slate-900">$ {{ number_format($item->total_comprado, 2, ',', '.') }}</p>
                                <p class="text-[10px] text-slate-400">{{ $item->cantidad_ordenes }} órdenes</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 py-6 text-center">No hay compras a proveedores registradas en este período.</p>
            @endif
        </x-card-resumen>
    </div>

    <!-- PENDING TRANSACTIONS GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ventas Pendientes -->
        <x-card-resumen title="Ventas Pendientes de Cobro/Emisión" subtitle="Facturas en estado borrador o pendiente" :actionUrl="route('ventas.index')">
            @if(count($ventasPendientes ?? []))
                <div class="space-y-2">
                    @foreach($ventasPendientes as $venta)
                        <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200 bg-slate-50/50">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-900">{{ $venta->numero_comprobante ?? 'N/A' }}</span>
                                    <x-badge-estado :status="$venta->estado" />
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">Cliente: {{ $venta->cliente->razon_social ?? 'Consumidor Final' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-mono font-bold text-slate-900">$ {{ number_format($venta->total, 2, ',', '.') }}</p>
                                <a href="{{ route('ventas.show', $venta) }}" class="inline-block mt-1 text-[10px] font-semibold text-teal-600 hover:underline">Ver detalle</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 py-4 text-center">No hay ventas pendientes.</p>
            @endif
        </x-card-resumen>

        <!-- Compras Pendientes -->
        <x-card-resumen title="Compras Pendientes de Recepción" subtitle="Órdenes de compra abiertas a confirmar" :actionUrl="route('compras.index')">
            @if(count($comprasPendientes ?? []))
                <div class="space-y-2">
                    @foreach($comprasPendientes as $compra)
                        <div class="flex items-center justify-between p-2.5 rounded-lg border border-slate-200 bg-slate-50/50">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-900">OC #{{ $compra->numero_orden ?? $compra->id }}</span>
                                    <x-badge-estado :status="$compra->estado" />
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">Proveedor: {{ $compra->proveedor->razon_social ?? 'S/D' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-mono font-bold text-slate-900">$ {{ number_format($compra->total, 2, ',', '.') }}</p>
                                <a href="{{ route('compras.show', $compra) }}" class="inline-block mt-1 text-[10px] font-semibold text-teal-600 hover:underline">Ver detalle</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400 py-4 text-center">No hay compras pendientes.</p>
            @endif
        </x-card-resumen>
    </div>

    <!-- KARDEX STREAM CARD -->
    <x-card-actividad title="Últimos Movimientos de Kardex" subtitle="Ingresos, salidas y ajustes de stock en tiempo real">
        @if(count($movimientosRecientes ?? []))
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-mono uppercase text-[10px] border-b border-slate-100">
                            <th class="pb-2">SKU / Producto</th>
                            <th class="pb-2">Tipo</th>
                            <th class="pb-2 text-right">Cantidad</th>
                            <th class="pb-2 text-right">Stock Ant.</th>
                            <th class="pb-2 text-right">Stock Nuevo</th>
                            <th class="pb-2 text-right">Fecha / Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($movimientosRecientes as $mov)
                            <tr class="hover:bg-slate-50/80 transition-colors">
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
                                <td class="py-2.5 text-right font-mono text-slate-500">{{ number_format($mov->saldo_anterior, 2) }}</td>
                                <td class="py-2.5 text-right font-mono font-bold text-slate-900">{{ number_format($mov->saldo_posterior, 2) }}</td>
                                <td class="py-2.5 text-right font-mono text-slate-400">{{ $mov->created_at->format('d/m H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-xs text-slate-400 py-6 text-center">No hay movimientos registrados recientemente.</p>
        @endif
    </x-card-actividad>

</div>
