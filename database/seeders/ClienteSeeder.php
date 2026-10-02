<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\CondicionIva;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Datos ficticios: CUIT/CUIL inventados con dígito verificador válido (módulo 11),
     * teléfonos con número de abonado que empieza en 0 (no discable) y emails @example.com.
     *
     * [razón social, CUIT/CUIL, condición IVA, teléfono, email, dirección]
     */
    private const CLIENTES = [
        ['Comercial Los Aromos SA', '30988219238', 'Responsable Inscripto', '+54 11 0000-1001', 'compras@losaromos.example.com', 'Av. de los Aromos 1450, Villa Celeste, Buenos Aires'],
        ['Almacén Don Ernesto SRL', '30998680464', 'Responsable Inscripto', '+54 351 000-1002', 'administracion@donernesto.example.com', 'Calle Los Tilos 238, Barrio Puente Azul, Córdoba'],
        ['Oficinas Nordelta Sur SA', '30996709627', 'Responsable Inscripto', '+54 11 0000-1003', 'proveedores@nordeltasur.example.com', 'Paseo del Molino 980, piso 3, Ciudad Arboleda, Buenos Aires'],
        ['Kiosco y Librería El Faro SRL', '30997279308', 'Responsable Inscripto', '+54 223 000-1004', 'elfaro@example.com', 'Calle Gaviotas 712, Puerto Delfín, Buenos Aires'],
        ['Asociación Civil Manos Unidas', '30982602471', 'Exento', '+54 341 000-1005', 'contacto@manosunidas.example.com', 'Pasaje Solidaridad 55, Barrio Las Retamas, Santa Fe'],
        ['Lucía Fernández', '27991095344', 'Monotributista', '+54 261 000-1006', 'lucia.fernandez@example.com', 'Calle Los Membrillos 1820, Villa Viñedos, Mendoza'],
        ['Martín Gutiérrez', '20984980559', 'Monotributista', '+54 381 000-1007', 'martin.gutierrez@example.com', 'Av. Naranjos 455, Barrio Cerro Verde, Tucumán'],
        ['Sofía Romero', '27992131522', 'Consumidor Final', '+54 11 0000-1008', 'sofia.romero@example.com', 'Calle Jacarandá 3021, depto. B, Villa Celeste, Buenos Aires'],
        ['Diego Herrera', '20986723634', 'Consumidor Final', '+54 351 000-1009', 'diego.herrera@example.com', 'Calle Las Calandrias 67, Barrio Puente Azul, Córdoba'],
        ['Valentina Castro', '27994993009', 'Consumidor Final', '+54 342 000-1010', 'valentina.castro@example.com', 'Calle Ceibos 1290, Barrio Las Retamas, Santa Fe'],
    ];

    public function run(): void
    {
        $adminId = User::where('email', 'admin@demo.test')->value('id');
        $condiciones = CondicionIva::pluck('id', 'nombre');

        foreach (self::CLIENTES as [$razonSocial, $cuit, $condicion, $telefono, $email, $direccion]) {
            Cliente::firstOrCreate(['cuit_dni' => $cuit], [
                'razon_social' => $razonSocial,
                'condicion_iva_id' => $condiciones[$condicion] ?? null,
                'telefono' => $telefono,
                'email' => $email,
                'direccion' => $direccion,
                'activo' => true,
                'created_by' => $adminId,
            ]);
        }
    }
}
