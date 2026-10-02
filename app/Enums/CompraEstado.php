<?php

declare(strict_types=1);

namespace App\Enums;

enum CompraEstado: string
{
    case Pendiente = 'pendiente';
    case Completada = 'completada';
    case Anulada = 'anulada';
}
