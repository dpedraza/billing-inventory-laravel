<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CompraEstado;
use App\Enums\MovimientoTipo;
use App\Models\Compra;
use App\Models\CompraItem;
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CompraService
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    public function crear(array $data, array $items): Compra
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['cantidad'] * $item['costo_unitario'];
        }

        $impuesto = $subtotal * 0.21;
        $total = $subtotal + $impuesto;

        $compra = Compra::create([
            'proveedor_id' => $data['proveedor_id'],
            'numero_orden' => $data['numero_orden'] ?? null,
            'subtotal' => $subtotal,
            'impuesto' => $impuesto,
            'total' => $total,
            'estado' => CompraEstado::Pendiente,
            'fecha_emision' => $data['fecha_emision'] ?? now()->toDateString(),
            'notas' => $data['notas'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $compraItems = [];
        foreach ($items as $item) {
            $compraItems[] = new CompraItem([
                'compra_id' => $compra->id,
                'producto_id' => $item['producto_id'],
                'cantidad' => $item['cantidad'],
                'costo_unitario' => $item['costo_unitario'],
                'subtotal' => $item['cantidad'] * $item['costo_unitario'],
            ]);
        }

        $compra->items()->saveMany($compraItems);

        return $compra->load('items');
    }

    public function confirmar(Compra $compra): Compra
    {
        if ($compra->estado !== CompraEstado::Pendiente) {
            throw new RuntimeException('Solo se pueden confirmar compras en estado pendiente.');
        }

        DB::transaction(function () use ($compra) {
            $compra->load('items.producto');

            foreach ($compra->items as $item) {
                $producto = $item->producto;

                $this->stockService->registrarMovimiento(
                    producto: $producto,
                    tipo: MovimientoTipo::Compra,
                    cantidad: (float) $item->cantidad,
                    costoUnitario: (float) $item->costo_unitario,
                    referencia: $compra,
                    metadata: ['compra_item_id' => $item->id],
                );

                $producto->update([
                    'precio_costo' => $item->costo_unitario,
                ]);
            }

            $compra->update(['estado' => CompraEstado::Completada]);
        });

        return $compra->fresh()->load('items');
    }

    public function anular(Compra $compra): Compra
    {
        if ($compra->estado !== CompraEstado::Completada) {
            throw new RuntimeException('Solo se pueden anular compras completadas.');
        }

        DB::transaction(function () use ($compra) {
            $this->stockService->anularMovimientos($compra);

            $compra->update(['estado' => CompraEstado::Anulada]);
        });

        return $compra->fresh()->load('items');
    }
}
