<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_users_have_one_role_each(): void
    {
        foreach (UserSeeder::USUARIOS as $email => $datos) {
            $user = User::where('email', $email)->firstOrFail();

            $this->assertSame([$datos['role']], $user->getRoleNames()->all());
            $this->assertTrue(Hash::check(UserSeeder::PASSWORD, $user->password));
        }
    }

    public function test_stock_matches_kardex_for_every_product(): void
    {
        $sumaKardex = StockMovement::groupBy('producto_id')
            ->selectRaw('producto_id, SUM(cantidad) as total')
            ->pluck('total', 'producto_id');

        foreach (Producto::all() as $producto) {
            $this->assertGreaterThanOrEqual(0, (float) $producto->stock_actual, $producto->sku);
            $this->assertEquals(
                (float) ($sumaKardex[$producto->id] ?? 0),
                (float) $producto->stock_actual,
                "Stock de {$producto->sku} distinto a la suma del kardex",
            );
        }
    }

    public function test_demo_data_covers_every_document_state(): void
    {
        foreach (['pendiente', 'pagada', 'anulada'] as $estado) {
            $this->assertTrue(Venta::where('estado', $estado)->exists(), "Sin ventas {$estado}");
        }

        foreach (['pendiente', 'completada', 'anulada'] as $estado) {
            $this->assertTrue(Compra::where('estado', $estado)->exists(), "Sin compras {$estado}");
        }

        $this->assertTrue(Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->exists());
    }

    public function test_current_month_always_has_activity_for_the_dashboard(): void
    {
        $mes = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];

        $this->assertTrue(Venta::where('estado', 'pagada')->whereBetween('fecha_emision', $mes)->exists());
        $this->assertTrue(Venta::where('estado', 'pagada')->whereDate('fecha_emision', now()->toDateString())->exists());
        $this->assertSame(
            Proveedor::count(),
            Compra::where('estado', 'completada')->whereBetween('fecha_emision', $mes)->distinct()->count('proveedor_id'),
        );
    }

    public function test_cuits_have_valid_check_digit(): void
    {
        $cuits = Cliente::pluck('cuit_dni')->merge(Proveedor::pluck('cuit_dni'));

        $this->assertNotEmpty($cuits);

        foreach ($cuits as $cuit) {
            $this->assertTrue($this->cuitValido($cuit), "CUIT inválido: {$cuit}");
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        $antes = [Venta::count(), Compra::count(), StockMovement::count(), User::count(), Producto::count()];

        $this->seed(DatabaseSeeder::class);

        $this->assertSame($antes, [Venta::count(), Compra::count(), StockMovement::count(), User::count(), Producto::count()]);
    }

    /** Dígito verificador de CUIT/CUIL (módulo 11). */
    private function cuitValido(string $cuit): bool
    {
        if (! preg_match('/^\d{11}$/', $cuit)) {
            return false;
        }

        $pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
        $suma = 0;
        foreach ($pesos as $i => $peso) {
            $suma += (int) $cuit[$i] * $peso;
        }

        $digito = 11 - ($suma % 11);
        $digito = match ($digito) {
            11 => 0,
            10 => -1,
            default => $digito,
        };

        return $digito === (int) $cuit[10];
    }
}
