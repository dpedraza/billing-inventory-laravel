<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MovimientoTipo;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use RuntimeException;

class StockService
{
    public function registrarMovimiento(
        Producto $producto,
        MovimientoTipo $tipo,
        float $cantidad,
        float $costoUnitario,
        ?Model $referencia = null,
        ?array $metadata = [],
    ): StockMovement {
        return DB::transaction(function () use ($producto, $tipo, $cantidad, $costoUnitario, $referencia, $metadata) {
            $producto = Producto::where('id', $producto->id)->lockForUpdate()->firstOrFail();

            $saldoAnterior = (float) $producto->stock_actual;
            $saldoPosterior = $saldoAnterior + $cantidad;

            if ($saldoPosterior < 0 && !$this->allowNegativeStock()) {
                throw new RuntimeException(
                    "Stock insuficiente para el producto {$producto->nombre}. " .
                    "Actual: {$saldoAnterior}, requerido: " . abs($cantidad)
                );
            }

            $movimiento = StockMovement::create([
                'producto_id' => $producto->id,
                'tipo_movimiento' => $tipo,
                'referencia_type' => $referencia ? $referencia->getMorphClass() : null,
                'referencia_id' => $referencia?->id,
                'cantidad' => $cantidad,
                'costo_unitario' => $costoUnitario,
                'saldo_anterior' => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
                'created_by' => Auth::id(),
                'metadata' => array_merge($metadata ?? [], [
                    'usuario' => Auth::user()?->name ?? 'system',
                    'ip' => Request::ip() ?? 'CLI',
                    'timestamp' => now()->toIso8601String(),
                ]),
            ]);

            $producto->update(['stock_actual' => $saldoPosterior]);

            return $movimiento;
        });
    }

    /**
     * Lleva el stock del producto al valor indicado (por ejemplo, un conteo físico)
     * registrando la diferencia en el kardex como ajuste de entrada o de salida.
     * Devuelve null si el stock ya coincide.
     */
    public function ajustarStock(Producto $producto, float $stockObjetivo, string $motivo = 'ajuste_manual'): ?StockMovement
    {
        return DB::transaction(function () use ($producto, $stockObjetivo, $motivo) {
            $stockActual = (float) Producto::where('id', $producto->id)->lockForUpdate()->value('stock_actual');
            $diferencia = round($stockObjetivo - $stockActual, 2);

            if ($diferencia == 0.0) {
                return null;
            }

            return $this->registrarMovimiento(
                producto: $producto,
                tipo: $diferencia > 0 ? MovimientoTipo::AjusteEntrada : MovimientoTipo::AjusteSalida,
                cantidad: $diferencia,
                costoUnitario: (float) $producto->precio_costo,
                metadata: ['motivo' => $motivo, 'stock_objetivo' => $stockObjetivo],
            );
        });
    }

    public function anularMovimientos(Model $referencia): void
    {
        $movements = StockMovement::where('referencia_type', $referencia->getMorphClass())
            ->where('referencia_id', $referencia->id)
            ->whereIn('tipo_movimiento', [MovimientoTipo::Compra, MovimientoTipo::Venta])
            ->get();

        if ($movements->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($movements) {
            foreach ($movements as $mov) {
                $inverseTipo = match ($mov->tipo_movimiento) {
                    MovimientoTipo::Compra => MovimientoTipo::AnulacionCompra,
                    MovimientoTipo::Venta => MovimientoTipo::AnulacionVenta,
                };

                $producto = Producto::where('id', $mov->producto_id)->lockForUpdate()->firstOrFail();

                $cantidadInversa = -$mov->cantidad;
                $saldoAnterior = (float) $producto->stock_actual;
                $saldoPosterior = $saldoAnterior + $cantidadInversa;

                StockMovement::create([
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => $inverseTipo,
                    'referencia_type' => $mov->getMorphClass(),
                    'referencia_id' => $mov->id,
                    'cantidad' => $cantidadInversa,
                    'costo_unitario' => $mov->costo_unitario,
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_posterior' => $saldoPosterior,
                    'created_by' => Auth::id(),
                    'metadata' => [
                        'anulacion_de_movimiento_id' => $mov->id,
                        'usuario' => Auth::user()?->name ?? 'system',
                        'ip' => Request::ip() ?? 'CLI',
                        'timestamp' => now()->toIso8601String(),
                    ],
                ]);

                $producto->update(['stock_actual' => $saldoPosterior]);
            }
        });
    }

    public function getKardex(Producto $producto, ?array $filters = []): LengthAwarePaginator
    {
        $query = StockMovement::with('creador')->where('producto_id', $producto->id);

        if ($filters !== null) {
            $tipo = $filters['tipo_movimiento'] ?? $filters['tipo'] ?? null;
            $query
                ->when($tipo, fn ($q, $t) => $q->where('tipo_movimiento', $t))
                ->when($filters['desde'] ?? null, fn ($q, $desde) => $q->whereDate('created_at', '>=', $desde))
                ->when($filters['hasta'] ?? null, fn ($q, $hasta) => $q->whereDate('created_at', '<=', $hasta));
        }

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 15);
    }

    private function allowNegativeStock(): bool
    {
        $value = Configuracion::where('clave', 'allow_negative_stock')->value('valor');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
