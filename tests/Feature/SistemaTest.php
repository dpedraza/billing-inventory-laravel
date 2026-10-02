<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CondicionIva;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\TipoComprobante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SistemaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $vendedor;
    private User $deposito;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin']);
        $this->admin->assignRole('Admin');

        $this->vendedor = User::factory()->create(['name' => 'Vendedor']);
        $this->vendedor->assignRole('Vendedor');

        $this->deposito = User::factory()->create(['name' => 'Deposito']);
        $this->deposito->assignRole('Deposito');

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
    }

    public function test_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertOk();
    }

    public function test_admin_can_list_products(): void
    {
        $response = $this->actingAs($this->admin)->get(route('productos.index'));
        $response->assertOk();
    }

    public function test_admin_can_create_product(): void
    {
        $response = $this->actingAs($this->admin)->get(route('productos.create'));
        $response->assertOk();

        $data = [
            'sku' => 'TEST-001',
            'nombre' => 'Producto Test',
            'unidad_medida' => 'unidad',
            'precio_costo' => 100,
            'precio_venta' => 200,
            'stock_minimo' => 5,
            'stock_actual' => 50,
        ];

        $response = $this->actingAs($this->admin)->post(route('productos.store'), $data);
        $response->assertRedirect();
        $this->assertDatabaseHas('productos', ['sku' => 'TEST-001']);
    }

    public function test_product_initial_stock_is_recorded_as_kardex_adjustment(): void
    {
        $this->actingAs($this->admin)->post(route('productos.store'), $this->datosProducto('ADJ-001', 50))
            ->assertRedirect();

        $producto = Producto::where('sku', 'ADJ-001')->firstOrFail();
        $this->assertEquals(50, $producto->stock_actual);

        $movimientos = $producto->stockMovements()->get();
        $this->assertCount(1, $movimientos);
        $this->assertSame('ajuste_entrada', $movimientos[0]->tipo_movimiento->value);
        $this->assertEquals(50, $movimientos[0]->cantidad);
        $this->assertSame('stock_inicial', $movimientos[0]->metadata['motivo']);
    }

    public function test_editing_product_stock_records_adjustment_in_kardex(): void
    {
        $this->actingAs($this->admin)->post(route('productos.store'), $this->datosProducto('ADJ-002', 10));
        $producto = Producto::where('sku', 'ADJ-002')->firstOrFail();

        $this->actingAs($this->deposito)
            ->put(route('productos.update', $producto), $this->datosProducto('ADJ-002', 4))
            ->assertRedirect();

        $producto->refresh();
        $this->assertEquals(4, $producto->stock_actual);
        $ultimo = $producto->stockMovements()->latest('id')->first();
        $this->assertSame('ajuste_salida', $ultimo->tipo_movimiento->value);
        $this->assertEquals(-6, $ultimo->cantidad);
        $this->assertEquals(10, $ultimo->saldo_anterior);
        $this->assertEquals(4, $ultimo->saldo_posterior);

        // Editar sin cambiar el stock no genera movimientos nuevos.
        $datos = $this->datosProducto('ADJ-002', 4);
        $datos['nombre'] = 'Producto Ajuste renombrado';
        $this->actingAs($this->deposito)->put(route('productos.update', $producto), $datos)->assertRedirect();

        $this->assertSame(2, $producto->stockMovements()->count());
        $this->assertEquals(4, (float) $producto->stockMovements()->sum('cantidad'));

        $this->actingAs($this->admin)->get(route('inventario.kardex', $producto))
            ->assertOk()
            ->assertSee('Ajuste entrada')
            ->assertSee('Ajuste salida');
        $this->actingAs($this->admin)->get(route('productos.show', $producto))->assertOk();
    }

    public function test_admin_can_list_clients(): void
    {
        $response = $this->actingAs($this->admin)->get(route('clientes.index'));
        $response->assertOk();
    }

    public function test_admin_can_create_client(): void
    {
        $condicion = CondicionIva::firstOrCreate(['nombre' => 'Consumidor Final']);

        $data = [
            'razon_social' => 'Cliente Test',
            'cuit_dni' => '20111111110',
            'condicion_iva_id' => $condicion->id,
            'telefono' => '3511111111',
            'email' => 'test@test.com',
            'direccion' => 'Calle Falsa 123',
        ];

        $response = $this->actingAs($this->admin)->post(route('clientes.store'), $data);
        $response->assertRedirect();
        $this->assertDatabaseHas('clientes', ['cuit_dni' => '20111111110']);
    }

    public function test_admin_can_list_suppliers(): void
    {
        $response = $this->actingAs($this->admin)->get(route('proveedores.index'));
        $response->assertOk();
    }

    public function test_admin_can_show_and_edit_supplier(): void
    {
        $proveedor = Proveedor::create([
            'razon_social' => 'Proveedor Test Show',
            'cuit_dni' => '30999999990',
        ]);

        $responseShow = $this->actingAs($this->admin)->get(route('proveedores.show', $proveedor));
        $responseShow->assertOk();

        $responseEdit = $this->actingAs($this->admin)->get(route('proveedores.edit', $proveedor));
        $responseEdit->assertOk();
    }

    public function test_full_purchase_flow(): void
    {
        CondicionIva::create(['nombre' => 'Consumidor Final']);

        $proveedor = Proveedor::create([
            'razon_social' => 'Proveedor Test',
            'cuit_dni' => '30111111110',
        ]);

        $producto = Producto::create([
            'sku' => 'PUR-001',
            'nombre' => 'Producto Compra',
            'unidad_medida' => 'unidad',
            'precio_costo' => 50,
            'precio_venta' => 100,
            'stock_minimo' => 5,
            'stock_actual' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('compras.create'));
        $response->assertOk();

        $response = $this->actingAs($this->admin)->post(route('compras.store'), [
            'proveedor_id' => $proveedor->id,
            'fecha_emision' => now()->toDateString(),
            'items' => [
                ['producto_id' => $producto->id, 'cantidad' => 10, 'costo_unitario' => 50],
            ],
        ]);
        $response->assertRedirect();

        $compra = Compra::latest('id')->first();
        $this->assertNotNull($compra);
        $this->assertEquals('pendiente', $compra->estado->value);

        $response = $this->actingAs($this->admin)->get(route('compras.show', $compra));
        $response->assertOk();

        $response = $this->actingAs($this->admin)->post(route('compras.confirmar', $compra));
        $response->assertRedirect();

        $compra->refresh();
        $this->assertEquals('completada', $compra->estado->value);

        $producto->refresh();
        $this->assertEquals(10, $producto->stock_actual);
    }

    public function test_full_sale_flow(): void
    {
        CondicionIva::create(['nombre' => 'Consumidor Final']);
        TipoComprobante::create(['codigo' => 'FC-B', 'nombre' => 'Factura B']);

        $cliente = Cliente::create([
            'razon_social' => 'Cliente Venta',
            'cuit_dni' => '20222222220',
        ]);

        $producto = Producto::create([
            'sku' => 'SAL-001',
            'nombre' => 'Producto Venta',
            'unidad_medida' => 'unidad',
            'precio_costo' => 50,
            'precio_venta' => 100,
            'stock_minimo' => 2,
            'stock_actual' => 20,
        ]);

        $response = $this->actingAs($this->admin)->get(route('ventas.create'));
        $response->assertOk();

        $tipoComprobante = TipoComprobante::first();

        $response = $this->actingAs($this->admin)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'tipo_comprobante_id' => $tipoComprobante->id,
            'fecha_emision' => now()->toDateString(),
            'items' => [
                ['producto_id' => $producto->id, 'cantidad' => 5, 'precio_unitario' => 100],
            ],
        ]);
        $response->assertRedirect();

        $venta = \App\Models\Venta::latest('id')->first();
        $this->assertNotNull($venta);
        $this->assertEquals('pendiente', $venta->estado->value);
        $this->assertNotNull($venta->numero_comprobante);

        $response = $this->actingAs($this->admin)->get(route('ventas.show', $venta));
        $response->assertOk();

        $response = $this->actingAs($this->admin)->post(route('ventas.confirmar', $venta));
        $response->assertRedirect();

        $venta->refresh();
        $this->assertEquals('pagada', $venta->estado->value);

        $producto->refresh();
        $this->assertEquals(15, $producto->stock_actual);
    }

    public function test_insufficient_stock_blocks_sale(): void
    {
        CondicionIva::firstOrCreate(['nombre' => 'Consumidor Final']);
        TipoComprobante::firstOrCreate(['codigo' => 'FC-B'], ['nombre' => 'Factura B']);

        $cliente = Cliente::create([
            'razon_social' => 'Cliente Test Block',
            'cuit_dni' => '20333333330',
        ]);

        $producto = Producto::create([
            'sku' => 'LOW-001',
            'nombre' => 'Stock Bajo',
            'unidad_medida' => 'unidad',
            'precio_costo' => 10,
            'precio_venta' => 20,
            'stock_minimo' => 1,
            'stock_actual' => 2,
        ]);

        $tipoComprobante = TipoComprobante::first();

        $response = $this->actingAs($this->admin)->post(route('ventas.store'), [
            'cliente_id' => $cliente->id,
            'tipo_comprobante_id' => $tipoComprobante->id,
            'fecha_emision' => now()->toDateString(),
            'items' => [
                ['producto_id' => $producto->id, 'cantidad' => 5, 'precio_unitario' => 20],
            ],
        ]);

        $venta = \App\Models\Venta::latest('id')->first();
        $this->assertNotNull($venta);

        $response = $this->actingAs($this->admin)->post(route('ventas.confirmar', $venta));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $venta->refresh();
        $this->assertEquals('pendiente', $venta->estado->value);
    }

    public function test_inventory_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('inventario.index'));
        $response->assertOk();
    }

    public function test_kardex_page(): void
    {
        $producto = Producto::create([
            'sku' => 'KDX-001',
            'nombre' => 'Producto Kardex',
            'unidad_medida' => 'unidad',
            'precio_costo' => 10,
            'precio_venta' => 20,
            'stock_minimo' => 1,
            'stock_actual' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get(route('inventario.kardex', $producto));
        $response->assertOk();
    }

    public function test_reports_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reportes.index'));
        $response->assertOk();
    }

    public function test_vendedor_can_access_sales(): void
    {
        $response = $this->actingAs($this->vendedor)->get(route('ventas.index'));
        $response->assertOk();

        $response = $this->actingAs($this->vendedor)->get(route('ventas.create'));
        $response->assertOk();
    }

    public function test_vendedor_cannot_access_purchases(): void
    {
        $response = $this->actingAs($this->vendedor)->get(route('compras.index'));
        $response->assertForbidden();
    }

    public function test_deposito_can_access_purchases(): void
    {
        $response = $this->actingAs($this->deposito)->get(route('compras.index'));
        $response->assertOk();
    }

    public function test_deposito_cannot_access_clients(): void
    {
        $response = $this->actingAs($this->deposito)->get(route('clientes.create'));
        $response->assertForbidden();
    }

    public function test_user_can_download_sale_pdf(): void
    {
        $condicion = CondicionIva::firstOrCreate(['nombre' => 'Consumidor Final']);
        $cliente = Cliente::create([
            'razon_social' => 'Cliente PDF',
            'cuit_dni' => '20999999990',
            'condicion_iva_id' => $condicion->id,
        ]);
        $tipo = TipoComprobante::firstOrCreate(['codigo' => 'FC-B'], ['nombre' => 'Factura B']);
        $venta = \App\Models\Venta::create([
            'cliente_id' => $cliente->id,
            'tipo_comprobante_id' => $tipo->id,
            'numero_comprobante' => 'FC-B-999',
            'subtotal' => 1000,
            'impuesto' => 210,
            'total' => 1210,
            'estado' => 'pendiente',
            'fecha_emision' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('ventas.pdf', $venta));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_user_can_download_purchase_pdf(): void
    {
        $proveedor = Proveedor::create([
            'razon_social' => 'Proveedor PDF',
            'cuit_dni' => '30888888880',
        ]);
        $compra = \App\Models\Compra::create([
            'proveedor_id' => $proveedor->id,
            'numero_orden' => 'ORD-999',
            'subtotal' => 5000,
            'impuesto' => 1050,
            'total' => 6050,
            'estado' => 'completada',
            'fecha_emision' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('compras.pdf', $compra));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_public_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    public function test_sales_and_purchase_reports_are_restricted_by_role(): void
    {
        $this->actingAs($this->admin)->get(route('reportes.ventas', 'pdf'))->assertOk();
        $this->actingAs($this->admin)->get(route('reportes.compras', 'pdf'))->assertOk();

        $this->actingAs($this->vendedor)->get(route('reportes.ventas', 'pdf'))->assertOk();
        $this->actingAs($this->vendedor)->get(route('reportes.compras', 'pdf'))->assertForbidden();

        $this->actingAs($this->deposito)->get(route('reportes.compras', 'pdf'))->assertOk();
        $this->actingAs($this->deposito)->get(route('reportes.ventas', 'pdf'))->assertForbidden();
    }

    public function test_excel_exports_are_real_xlsx_files(): void
    {
        $proveedor = Proveedor::create(['razon_social' => 'Proveedor Excel', 'cuit_dni' => '30777777770']);
        $cliente = Cliente::create(['razon_social' => 'Cliente Excel', 'cuit_dni' => '20777777770']);
        $tipo = TipoComprobante::firstOrCreate(['codigo' => 'FC-B'], ['nombre' => 'Factura B']);
        Producto::create([
            'sku' => 'XLS-001', 'nombre' => 'Producto Excel', 'unidad_medida' => 'unidad',
            'precio_costo' => 10, 'precio_venta' => 20, 'stock_minimo' => 1, 'stock_actual' => 5,
        ]);
        Compra::create([
            'proveedor_id' => $proveedor->id, 'numero_orden' => 'OC-XLS', 'subtotal' => 100,
            'impuesto' => 21, 'total' => 121, 'estado' => 'completada', 'fecha_emision' => now()->toDateString(),
        ]);
        \App\Models\Venta::create([
            'cliente_id' => $cliente->id, 'tipo_comprobante_id' => $tipo->id, 'numero_comprobante' => 'FC-B-XLS',
            'subtotal' => 200, 'impuesto' => 42, 'total' => 242, 'estado' => 'pagada', 'fecha_emision' => now()->toDateString(),
        ]);

        $casos = [
            'reportes.stock' => ['SKU', 'XLS-001'],
            'reportes.ventas' => ['N° Comprobante', 'FC-B-XLS'],
            'reportes.compras' => ['N° Orden', 'OC-XLS'],
        ];

        foreach ($casos as $ruta => [$encabezado, $valor]) {
            $response = $this->actingAs($this->admin)->get(route($ruta, 'excel'));
            $response->assertOk();
            $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            $filas = $this->leerXlsx($response->streamedContent());
            $this->assertSame($encabezado, $filas[0][0], $ruta);
            $this->assertSame($valor, $filas[1][0], $ruta);
        }
    }

    /** @return list<list<mixed>> */
    private function leerXlsx(string $contenido): array
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx_test_') . '.xlsx';
        file_put_contents($path, $contenido);

        $reader = new \OpenSpout\Reader\XLSX\Reader();
        $reader->open($path);

        $filas = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $filas[] = $row->toArray();
            }
        }

        $reader->close();
        unlink($path);

        return $filas;
    }

    /** @return array<string, mixed> */
    private function datosProducto(string $sku, float $stock): array
    {
        return [
            'sku' => $sku,
            'nombre' => 'Producto Ajuste',
            'unidad_medida' => 'unidad',
            'precio_costo' => 100,
            'precio_venta' => 200,
            'stock_minimo' => 5,
            'stock_actual' => $stock,
        ];
    }
}
