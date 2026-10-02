<div class="space-y-6">

    <!-- VENDEDOR FOCUS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Ventas Hoy -->
        @php
            $formattedHoy = number_format($resumenVentas['total_ventas_hoy'] ?? 0, 2, ',', '.');
            $partsHoy = explode(',', $formattedHoy);
        @endphp
        <x-card-kpi 
            title="Mis Ventas de Hoy" 
            :value="$partsHoy[0]" 
            :decimals="$partsHoy[1] ?? '00'" 
            :subtitle="($resumenVentas['cantidad_ventas_hoy'] ?? 0) . ' facturas cerradas'" 
            badgeText="Mostrador" 
            badgeType="success" 
            type="financial" 
        />

        <!-- Ventas Pendientes -->
        <x-card-kpi 
            title="Facturas Pendientes de Cobro" 
            :value="(string) count($ventasPendientes ?? [])" 
            :decimals="null" 
            subtitle="Requieren confirmación de pago" 
            badgeText="En Espera" 
            badgeType="warning" 
            type="alert" 
        />

        <!-- Quick Stock Lookup Widget -->
        <div class="bg-white rounded-xl p-5 border border-slate-200 card-signature-inventory shadow-xs">
            <span class="text-xs text-slate-500 font-medium">Buscador Rápido de Stock</span>
            <div class="mt-2">
                <a href="{{ route('productos.index') }}" class="flex items-center gap-2 bg-slate-50 border border-slate-200 hover:border-teal-500 rounded text-xs px-3 py-2 text-slate-500 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Buscar disponibilidad por SKU...</span>
                </a>
            </div>
            <p class="text-[10px] text-slate-400 mt-3">Consulta inmediata para clientes en mostrador</p>
        </div>
    </div>

    <!-- PENDING SALES TABLE FOR VENDEDOR -->
    <x-card-resumen title="Comprobantes Pendientes de Emitir" subtitle="Ventas iniciadas en mostrador listos para facturar" :actionUrl="route('ventas.index')">
        @if(count($ventasPendientes ?? []))
            <div class="space-y-3">
                @foreach($ventasPendientes as $venta)
                    <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 bg-slate-50/50">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-bold text-slate-900">{{ $venta->numero_comprobante ?? 'BORRADOR' }}</span>
                                <x-badge-estado :status="$venta->estado" />
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">Cliente: {{ $venta->cliente->razon_social ?? 'Consumidor Final' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-mono font-bold text-slate-900">$ {{ number_format($venta->total, 2, ',', '.') }}</p>
                            <a href="{{ route('ventas.show', $venta) }}" class="inline-block mt-1 bg-teal-600 hover:bg-teal-700 text-white text-[10px] font-semibold px-2.5 py-1 rounded transition-colors">Confirmar / Ver</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-400 py-6 text-center">No tenés comprobantes pendientes de emisión.</p>
        @endif
    </x-card-resumen>

    <!-- TOP SELLING PRODUCTS -->
    <x-card-resumen title="Productos Más Vendidos en Mostrador" subtitle="Artículos de alta rotación comercial">
        @if(count($masVendidos ?? []))
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-mono uppercase text-[10px] border-b border-slate-100">
                            <th class="pb-2">SKU</th>
                            <th class="pb-2">Producto</th>
                            <th class="pb-2 text-right">Cantidad Vendida</th>
                            <th class="pb-2 text-right">Total Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($masVendidos as $item)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-2 font-mono text-slate-500">{{ $item->producto->sku ?? 'N/A' }}</td>
                                <td class="py-2 font-medium text-slate-900">{{ $item->producto->nombre ?? 'S/N' }}</td>
                                <td class="py-2 text-right font-mono font-bold text-slate-900">{{ number_format($item->total_cantidad) }}</td>
                                <td class="py-2 text-right font-mono font-bold text-emerald-600">$ {{ number_format($item->total_ingresos, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-xs text-slate-400 py-6 text-center">No hay registros de productos vendidos en este período.</p>
        @endif
    </x-card-resumen>

</div>
