<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Venta;
use App\Policies\ClientePolicy;
use App\Policies\CompraPolicy;
use App\Policies\ProductoPolicy;
use App\Policies\ProveedorPolicy;
use App\Policies\VentaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Producto::class => ProductoPolicy::class,
        Cliente::class => ClientePolicy::class,
        Proveedor::class => ProveedorPolicy::class,
        Compra::class => CompraPolicy::class,
        Venta::class => VentaPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
