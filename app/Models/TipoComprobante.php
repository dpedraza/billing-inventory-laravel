<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoComprobante extends Model
{
    protected $table = 'tipos_comprobante';

    protected $fillable = [
        'codigo',
        'nombre',
        'activo',
    ];

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'tipo_comprobante_id');
    }
}
