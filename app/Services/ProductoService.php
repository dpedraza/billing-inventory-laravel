<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductoService
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * El producto se crea con stock 0 y el stock inicial entra al kardex como ajuste de entrada.
     */
    public function crear(array $data): Producto
    {
        return DB::transaction(function () use ($data) {
            $stockInicial = (float) ($data['stock_actual'] ?? 0);

            $producto = Producto::create([
                ...$data,
                'stock_actual' => 0,
                'created_by' => Auth::id(),
            ]);

            $this->stockService->ajustarStock($producto, $stockInicial, 'stock_inicial');

            return $producto->refresh();
        });
    }

    /**
     * El stock ingresado en el formulario se toma como conteo físico: la diferencia
     * con el stock actual se registra en el kardex como ajuste de entrada o de salida.
     */
    public function actualizar(Producto $producto, array $data): Producto
    {
        return DB::transaction(function () use ($producto, $data) {
            $stockContado = array_key_exists('stock_actual', $data) ? (float) $data['stock_actual'] : null;
            unset($data['stock_actual']);

            $producto->update($data + ['updated_by' => Auth::id()]);

            if ($stockContado !== null) {
                $this->stockService->ajustarStock($producto, $stockContado, 'ajuste_manual');
            }

            return $producto->refresh();
        });
    }
}
