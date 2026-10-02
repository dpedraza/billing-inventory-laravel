<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CondicionIva extends Model
{
    protected $table = 'condiciones_iva';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class, 'condicion_iva_id');
    }

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class, 'condicion_iva_id');
    }
}
