<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public const PASSWORD = 'password';

    /** @var array<string, array{name: string, role: string}> */
    public const USUARIOS = [
        'admin@demo.test' => ['name' => 'Administrador Demo', 'role' => 'Admin'],
        'vendedor@demo.test' => ['name' => 'Vendedor Demo', 'role' => 'Vendedor'],
        'deposito@demo.test' => ['name' => 'Depósito Demo', 'role' => 'Deposito'],
    ];

    public function run(): void
    {
        foreach (self::USUARIOS as $email => $datos) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $datos['name'],
                    'password' => Hash::make(self::PASSWORD),
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$datos['role']]);
        }
    }
}
