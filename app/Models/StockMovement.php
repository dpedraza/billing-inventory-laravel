<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MovimientoTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'producto_id',
        'tipo_movimiento',
        'referencia_type',
        'referencia_id',
        'cantidad',
        'costo_unitario',
        'saldo_anterior',
        'saldo_posterior',
        'created_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'tipo_movimiento' => MovimientoTipo::class,
            'cantidad' => 'decimal:2',
            'costo_unitario' => 'decimal:2',
            'saldo_anterior' => 'decimal:2',
            'saldo_posterior' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
