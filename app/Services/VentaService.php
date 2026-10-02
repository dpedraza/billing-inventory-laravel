<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MovimientoTipo;
use App\Enums\VentaEstado;
use App\Models\TipoComprobante;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VentaService
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    public function crear(array $data, array $items): Venta
    {
        $tipoComprobante = TipoComprobante::findOrFail($data['tipo_comprobante_id']);

        $numeroComprobante = $this->generarNumeroComprobante($tipoComprobante);

        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['cantidad'] * $item['precio_unitario'];
        }

        $impuesto = $subtotal * 0.21;
        $total = $subtotal + $impuesto;

        $venta = Venta::create([
            'cliente_id' => $data['cliente_id'],
            'tipo_comprobante_id' => $tipoComprobante->id,
            'numero_comprobante' => $numeroComprobante,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'total' => $total,
            'estado' => VentaEstado::Pendiente,
            'fecha_emision' => $data['fecha_emision'] ?? now()->toDateString(),
            'notas' => $data['notas'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $ventaItems = [];
        foreach ($items as $item) {
            $ventaItems[] = new VentaItem([
                'venta_id' => $venta->id,
                'producto_id' => $item['producto_id'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'subtotal' => $item['cantidad'] * $item['precio_unitario'],
            ]);
        }

        $venta->items()->saveMany($ventaItems);

        return $venta->load('items');
    }

    public function confirmar(Venta $venta): Venta
    {
        if ($venta->estado !== VentaEstado::Pendiente) {
            throw new RuntimeException('Solo se pueden confirmar ventas en estado pendiente.');
        }

        DB::transaction(function () use ($venta) {
            $venta->load('items.producto');

            foreach ($venta->items as $item) {
                $producto = $item->producto;

                $this->stockService->registrarMovimiento(
                    producto: $producto,
                    tipo: MovimientoTipo::Venta,
                    cantidad: -(float) $item->cantidad,
                    costoUnitario: (float) $producto->precio_costo,
                    referencia: $venta,
                    metadata: ['venta_item_id' => $item->id],
                );
            }

            $venta->update(['estado' => VentaEstado::Pagada]);
        });

        return $venta->fresh()->load('items');
    }

    public function anular(Venta $venta): Venta
    {
        if ($venta->estado !== VentaEstado::Pagada) {
            throw new RuntimeException('Solo se pueden anular ventas pagadas.');
        }

        DB::transaction(function () use ($venta) {
            $this->stockService->anularMovimientos($venta);

            $venta->update(['estado' => VentaEstado::Anulada]);
        });

        return $venta->fresh()->load('items');
    }

    private function generarNumeroComprobante(TipoComprobante $tipoComprobante): string
    {
        $lastVenta = Venta::where('tipo_comprobante_id', $tipoComprobante->id)
            ->where('numero_comprobante', 'like', $tipoComprobante->codigo . '-%')
            ->orderByDesc('id')
            ->first();

        if ($lastVenta) {
            $parts = explode('-', $lastVenta->numero_comprobante);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $tipoComprobante->codigo . '-' . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
