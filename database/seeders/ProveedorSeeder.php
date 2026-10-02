<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CondicionIva;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Datos ficticios (ver ClienteSeeder). El CUIT identifica al proveedor en
     * OperacionesDemoSeeder para asignarle los productos que abastece.
     *
     * [razón social, CUIT, teléfono, email, dirección]
     */
    public const PROVEEDORES = [
        ['Distribuidora Andina de Tecnología SA', '30983144272', '+54 11 0000-2001', 'ventas@andinatec.example.com', 'Av. Cordillera 2500, Parque Industrial Norte, Buenos Aires'],
        ['Textil Río Claro SRL', '30983947219', '+54 351 000-2002', 'pedidos@textilrioclaro.example.com', 'Calle Hilanderos 340, Villa Algodonera, Córdoba'],
        ['Alimentos La Pradera SA', '30995935607', '+54 341 000-2003', 'comercial@lapradera.example.com', 'Ruta Provincial 99 km 12, Colonia Trigales, Santa Fe'],
        ['Química del Valle SRL', '30992565558', '+54 261 000-2004', 'ventas@quimicadelvalle.example.com', 'Calle Alambiques 77, Parque Industrial Valle Escondido, Mendoza'],
        ['Herramientas Cordillera SA', '33995375627', '+54 11 0000-2005', 'distribuidores@hcordillera.example.com', 'Av. de los Talleres 1180, Villa Forja, Buenos Aires'],
    ];

    public function run(): void
    {
        $adminId = User::where('email', 'admin@demo.test')->value('id');
        $responsableInscripto = CondicionIva::where('nombre', 'Responsable Inscripto')->value('id');

        foreach (self::PROVEEDORES as [$razonSocial, $cuit, $telefono, $email, $direccion]) {
            Proveedor::firstOrCreate(['cuit_dni' => $cuit], [
                'razon_social' => $razonSocial,
                'condicion_iva_id' => $responsableInscripto,
                'telefono' => $telefono,
                'email' => $email,
                'direccion' => $direccion,
                'activo' => true,
                'created_by' => $adminId,
            ]);
        }
    }
}
