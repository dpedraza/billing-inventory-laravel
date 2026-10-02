<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\CondicionIva;
use App\Models\Configuracion;
use App\Models\TipoComprobante;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Responsable Inscripto', 'Monotributista', 'Exento', 'Consumidor Final', 'Sujeto No Categorizado'] as $nombre) {
            CondicionIva::firstOrCreate(['nombre' => $nombre]);
        }

        TipoComprobante::firstOrCreate(['codigo' => 'FC-A'], ['nombre' => 'Factura A']);
        TipoComprobante::firstOrCreate(['codigo' => 'FC-B'], ['nombre' => 'Factura B']);
        TipoComprobante::firstOrCreate(['codigo' => 'REM'], ['nombre' => 'Remito']);
        TipoComprobante::firstOrCreate(['codigo' => 'PRES'], ['nombre' => 'Presupuesto']);

        Categoria::firstOrCreate(['nombre' => 'Electrónica'], ['descripcion' => 'Productos electrónicos y tecnológicos']);
        Categoria::firstOrCreate(['nombre' => 'Indumentaria'], ['descripcion' => 'Ropa y accesorios']);
        Categoria::firstOrCreate(['nombre' => 'Alimentos'], ['descripcion' => 'Productos alimenticios']);
        Categoria::firstOrCreate(['nombre' => 'Limpieza'], ['descripcion' => 'Artículos de limpieza']);
        Categoria::firstOrCreate(['nombre' => 'Herramientas'], ['descripcion' => 'Herramientas manuales y eléctricas']);

        Configuracion::firstOrCreate(['clave' => 'allow_negative_stock'], ['valor' => 'false']);
        Configuracion::firstOrCreate(['clave' => 'iva_porcentaje'], ['valor' => '21']);
    }
}
