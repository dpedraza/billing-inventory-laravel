<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class VentaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('Vendedor');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('Vendedor');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function confirmar(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('Vendedor');
    }

    public function anular(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    public function exportar(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('Vendedor');
    }
}
