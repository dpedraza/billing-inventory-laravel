<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Los productos arrancan con stock 0: el stock inicial entra por compras
     * confirmadas en OperacionesDemoSeeder, así el kardex queda completo.
     *
     * [sku, nombre, categoría, unidad, precio_costo, precio_venta, stock_minimo]
     */
    private const PRODUCTOS = [
        ['TEC-001', 'Mouse inalámbrico', 'Electrónica', 'unidad', 9500, 15900, 8],
        ['TEC-002', 'Teclado USB español', 'Electrónica', 'unidad', 12800, 21500, 6],
        ['TEC-003', 'Monitor LED 24"', 'Electrónica', 'unidad', 165000, 259000, 2],
        ['TEC-004', 'Pendrive 64 GB', 'Electrónica', 'unidad', 7200, 12900, 10],
        ['TEC-005', 'Auriculares con micrófono', 'Electrónica', 'unidad', 14500, 24900, 5],
        ['TEC-006', 'Cable HDMI 2 m', 'Electrónica', 'unidad', 3900, 7500, 10],
        ['IND-001', 'Remera de algodón lisa', 'Indumentaria', 'unidad', 6800, 12500, 12],
        ['IND-002', 'Pantalón jean clásico', 'Indumentaria', 'unidad', 18500, 32900, 8],
        ['IND-003', 'Buzo de frisa con capucha', 'Indumentaria', 'unidad', 21000, 37900, 6],
        ['IND-004', 'Medias deportivas x3', 'Indumentaria', 'pack', 4200, 7900, 15],
        ['ALI-001', 'Arroz largo fino 1 kg', 'Alimentos', 'unidad', 1350, 2290, 40],
        ['ALI-002', 'Aceite de girasol 1,5 L', 'Alimentos', 'unidad', 3100, 4990, 30],
        ['ALI-003', 'Yerba mate 1 kg', 'Alimentos', 'unidad', 4800, 7490, 30],
        ['ALI-004', 'Fideos secos 500 g', 'Alimentos', 'unidad', 980, 1690, 40],
        ['ALI-005', 'Café molido 500 g', 'Alimentos', 'unidad', 8900, 14500, 10],
        ['LIM-001', 'Detergente 750 ml', 'Limpieza', 'unidad', 1450, 2490, 24],
        ['LIM-002', 'Lavandina 2 L', 'Limpieza', 'unidad', 1200, 2090, 24],
        ['LIM-003', 'Jabón líquido para ropa 3 L', 'Limpieza', 'unidad', 7800, 12900, 10],
        ['LIM-004', 'Desinfectante en aerosol 360 ml', 'Limpieza', 'unidad', 2900, 4890, 12],
        ['HER-001', 'Martillo de carpintero 500 g', 'Herramientas', 'unidad', 9800, 16900, 4],
        ['HER-002', 'Juego de destornilladores x6', 'Herramientas', 'caja', 11500, 19900, 4],
        ['HER-003', 'Taladro percutor 650 W', 'Herramientas', 'unidad', 68000, 109000, 2],
        ['HER-004', 'Cinta métrica 5 m', 'Herramientas', 'unidad', 3600, 6490, 8],
        ['HER-005', 'Amoladora angular 115 mm', 'Herramientas', 'unidad', 59000, 94900, 2],
    ];

    public function run(): void
    {
        $adminId = User::where('email', 'admin@demo.test')->value('id');
        $categorias = Categoria::pluck('id', 'nombre');

        foreach (self::PRODUCTOS as [$sku, $nombre, $categoria, $unidad, $costo, $venta, $minimo]) {
            Producto::firstOrCreate(['sku' => $sku], [
                'nombre' => $nombre,
                'categoria_id' => $categorias[$categoria] ?? null,
                'unidad_medida' => $unidad,
                'precio_costo' => $costo,
                'precio_venta' => $venta,
                'stock_minimo' => $minimo,
                'stock_actual' => 0,
                'activo' => true,
                'created_by' => $adminId,
            ]);
        }
    }
}
