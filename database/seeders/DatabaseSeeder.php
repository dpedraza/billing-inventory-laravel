<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Datos demo listos para probar. Todos los seeders son idempotentes:
     * re-ejecutar `php artisan db:seed` no duplica registros.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CatalogoSeeder::class,
            UserSeeder::class,
            ProductoSeeder::class,
            ClienteSeeder::class,
            ProveedorSeeder::class,
            OperacionesDemoSeeder::class,
        ]);
    }
}
