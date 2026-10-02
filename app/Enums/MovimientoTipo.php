<?php

declare(strict_types=1);

namespace App\Enums;

enum MovimientoTipo: string
{
    case Compra = 'compra';
    case Venta = 'venta';
    case AjusteEntrada = 'ajuste_entrada';
    case AjusteSalida = 'ajuste_salida';
    case AnulacionVenta = 'anulacion_venta';
    case AnulacionCompra = 'anulacion_compra';
}
