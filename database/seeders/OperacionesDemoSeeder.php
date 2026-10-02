<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\StockMovement;
use App\Models\TipoComprobante;
use App\Models\User;
use App\Models\Venta;
use App\Services\CompraService;
use App\Services\StockService;
use App\Services\VentaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Simula ~5 meses de operación del negocio.
 *
 * Todas las compras y ventas pasan por CompraService / VentaService
 * (crear → confirmar → anular), de modo que stock_actual, el kardex
 * (stock_movements) y la numeración de comprobantes quedan consistentes.
 * Para fechar cada operación en el pasado se "viaja en el tiempo" con
 * Carbon::setTestNow(): así created_at de compras, ventas y movimientos
 * coincide con fecha_emision.
 */
class OperacionesDemoSeeder extends Seeder
{
    private const DIAS_HISTORIA = 150;
    private const DIAS_REPOSICION = [21, 42, 63, 84, 105, 126, 140];
    /** A partir de estos días se anula la primera venta confirmada. */
    private const DIAS_VENTA_ANULADA = [45, 110];
    private const DIA_COMPRA_ANULADA = 60;
    private const DIA_VENTA_MAYORISTA = self::DIAS_HISTORIA - 3;
    private const DIA_CONTEO_FISICO = 90;
    private const INFLACION_MENSUAL_COSTOS = 0.015;

    /**
     * sku => [peso de rotación (0 = no se vende), último día en que se repone].
     */
    private const ROTACION = [
        'TEC-001' => [5, 140], 'TEC-002' => [3, 140], 'TEC-003' => [1, 140],
        'TEC-004' => [5, 140], 'TEC-005' => [3, 140], 'TEC-006' => [3, 140],
        'IND-001' => [5, 140], 'IND-002' => [5, 140], 'IND-003' => [1, 140],
        'IND-004' => [5, 140],
        'ALI-001' => [5, 140], 'ALI-002' => [5, 140], 'ALI-003' => [5, 140],
        'ALI-004' => [5, 140], 'ALI-005' => [1, 140],
        'LIM-001' => [5, 140], 'LIM-002' => [3, 140], 'LIM-003' => [5, 140],
        'LIM-004' => [1, 140],
        'HER-001' => [1, 140], 'HER-002' => [1, 140], 'HER-003' => [1, 140],
        'HER-004' => [3, 140], 'HER-005' => [0, 0],
    ];

    /**
     * Stock final (por debajo del mínimo) que deja la venta mayorista; después
     * esos productos no se venden ni se reponen, así el dashboard muestra
     * alertas de stock crítico sin depender de la fecha de ejecución.
     */
    private const STOCK_FINAL_BAJO_MINIMO = [
        'TEC-004' => 4, 'IND-002' => 3, 'ALI-002' => 12, 'LIM-003' => 3,
    ];

    /** Prefijo de SKU => CUIT del proveedor que abastece el rubro. */
    private const PROVEEDOR_POR_RUBRO = [
        'TEC' => '30983144272',
        'IND' => '30983947219',
        'ALI' => '30995935607',
        'LIM' => '30992565558',
        'HER' => '33995375627',
    ];

    /** CUIT => peso de frecuencia de compra del cliente. */
    private const FRECUENCIA_CLIENTES = [
        '30988219238' => 2, '30998680464' => 2, '30996709627' => 2, '30997279308' => 2,
        '30982602471' => 1, '27991095344' => 2, '20984980559' => 2,
        '27992131522' => 3, '20986723634' => 3, '27994993009' => 3,
    ];

    private CompraService $compraService;
    private StockService $stockService;
    private VentaService $ventaService;

    private User $admin;
    private User $vendedor;
    private User $deposito;

    /** @var Collection<string, Producto> */
    private Collection $productos;
    /** @var Collection<string, Proveedor> */
    private Collection $proveedores;
    /** @var Collection<string, Cliente> */
    private Collection $clientes;

    /** @var array<string, float> Espejo local del stock esperado por SKU. */
    private array $stock = [];
    /** @var array<string, float> Costo inicial por SKU, base para la inflación. */
    private array $costoBase = [];

    private Carbon $ahora;
    private Carbon $inicio;
    private int $numeroOrden = 0;
    private int $ventasAnuladas = 0;
    private int $diaActual = 0;

    public function run(CompraService $compraService, VentaService $ventaService, StockService $stockService): void
    {
        if (Compra::withTrashed()->exists() || Venta::withTrashed()->exists()) {
            $this->command?->warn('  Ya existen compras o ventas: se omiten las operaciones demo.');

            return;
        }

        $this->compraService = $compraService;
        $this->ventaService = $ventaService;
        $this->stockService = $stockService;
        $this->cargarDatosBase();

        mt_srand(2026);

        $this->ahora = Carbon::now();
        $this->inicio = $this->ahora->copy()->startOfDay()->subDays(self::DIAS_HISTORIA);

        try {
            DB::transaction(function (): void {
                for ($dia = 0; $dia <= self::DIAS_HISTORIA; $dia++) {
                    $fecha = $this->inicio->copy()->addDays($dia);
                    $this->diaActual = $dia;

                    if ($dia === 0) {
                        $this->compraInicial($fecha);
                        continue;
                    }

                    if (in_array($dia, self::DIAS_REPOSICION, true)) {
                        $this->reponerStock($fecha, $dia);
                    }

                    if ($dia === self::DIA_COMPRA_ANULADA) {
                        $this->compraAnulada($fecha);
                    }

                    if ($dia === self::DIA_CONTEO_FISICO) {
                        $this->conteoFisico($fecha);
                    }

                    if ($dia === self::DIA_VENTA_MAYORISTA) {
                        $this->ventaMayorista($fecha);
                    }

                    if (! $fecha->isSunday()) {
                        $this->ventasDelDia($fecha, $dia);
                    }
                }

                $this->operacionesPendientes($this->inicio->copy()->addDays(self::DIAS_HISTORIA));
            });
        } finally {
            Carbon::setTestNow();
            Auth::forgetUser();
        }

        $this->verificarStock();
    }

    private function cargarDatosBase(): void
    {
        $this->admin = User::where('email', 'admin@demo.test')->firstOrFail();
        $this->vendedor = User::where('email', 'vendedor@demo.test')->firstOrFail();
        $this->deposito = User::where('email', 'deposito@demo.test')->firstOrFail();

        $this->productos = Producto::whereIn('sku', array_keys(self::ROTACION))->get()->keyBy('sku');
        $this->proveedores = Proveedor::whereIn('cuit_dni', self::PROVEEDOR_POR_RUBRO)->get()->keyBy('cuit_dni');
        $this->clientes = Cliente::with('condicionIva')
            ->whereIn('cuit_dni', array_keys(self::FRECUENCIA_CLIENTES))
            ->get()
            ->keyBy('cuit_dni');

        foreach ($this->productos as $sku => $producto) {
            $this->stock[$sku] = (float) $producto->stock_actual;
            $this->costoBase[$sku] = (float) $producto->precio_costo;
        }
    }

    // ─── Compras ─────────────────────────────────────────────────────────────

    private function compraInicial(Carbon $fecha): void
    {
        $minutos = 0;

        foreach (self::PROVEEDOR_POR_RUBRO as $rubro => $cuit) {
            $items = [];
            foreach ($this->skusDelRubro($rubro) as $sku) {
                $items[$sku] = (float) $this->productos[$sku]->stock_minimo * 5;
            }

            $this->registrarCompra($cuit, $items, $fecha, $minutos, 'Stock inicial de apertura.');
            $minutos += 10;
        }
    }

    private function reponerStock(Carbon $fecha, int $dia): void
    {
        $minutos = 0;

        foreach (self::PROVEEDOR_POR_RUBRO as $rubro => $cuit) {
            $items = [];
            foreach ($this->skusDelRubro($rubro) as $sku) {
                [, $ultimaReposicion] = self::ROTACION[$sku];
                $minimo = (float) $this->productos[$sku]->stock_minimo;

                if ($dia <= $ultimaReposicion && $this->stock[$sku] < $minimo * 2.5) {
                    $items[$sku] = ceil($minimo * 5 - $this->stock[$sku]);
                }
            }

            if ($items !== []) {
                $this->registrarCompra($cuit, $items, $fecha, $minutos, 'Reposición programada de stock.');
                $minutos += 10;
            }
        }
    }

    private function compraAnulada(Carbon $fecha): void
    {
        $compra = $this->registrarCompra(
            self::PROVEEDOR_POR_RUBRO['IND'],
            ['IND-001' => 20, 'IND-003' => 10],
            $fecha,
            30,
            'Lote con fallas de confección: devuelto al proveedor.',
        );

        $this->comoUsuario($this->admin, $this->instante($fecha, 200));
        $this->compraService->anular($compra);

        $this->stock['IND-001'] -= 20;
        $this->stock['IND-003'] -= 10;
    }

    /**
     * @param array<string, float> $items sku => cantidad
     */
    private function registrarCompra(
        string $cuitProveedor,
        array $items,
        Carbon $fecha,
        int $minutos,
        ?string $notas = null,
        bool $confirmar = true,
    ): Compra {
        $this->comoUsuario($this->deposito, $this->instante($fecha, $minutos));

        $lineas = [];
        foreach ($items as $sku => $cantidad) {
            $lineas[] = [
                'producto_id' => $this->productos[$sku]->id,
                'cantidad' => $cantidad,
                'costo_unitario' => $this->costoEnFecha($sku, $fecha),
            ];
        }

        $compra = $this->compraService->crear([
            'proveedor_id' => $this->proveedores[$cuitProveedor]->id,
            'numero_orden' => 'OC-' . str_pad((string) ++$this->numeroOrden, 5, '0', STR_PAD_LEFT),
            'fecha_emision' => $fecha->toDateString(),
            'notas' => $notas,
        ], $lineas);

        if ($confirmar) {
            $compra = $this->compraService->confirmar($compra);

            foreach ($items as $sku => $cantidad) {
                $this->stock[$sku] += $cantidad;
            }
        }

        return $compra;
    }

    /**
     * Conteo físico de depósito: se detectan unidades rotas y el stock se corrige
     * con un ajuste de salida en el kardex (StockService::ajustarStock).
     */
    private function conteoFisico(Carbon $fecha): void
    {
        $sku = 'LIM-002';
        $contado = max(0, $this->stock[$sku] - 3);

        $this->comoUsuario($this->deposito, $this->instante($fecha, 0));
        $this->stockService->ajustarStock($this->productos[$sku], $contado, 'conteo_fisico: 3 bidones rotos');

        $this->stock[$sku] = $contado;
    }

    // ─── Ventas ──────────────────────────────────────────────────────────────

    private function ventasDelDia(Carbon $fecha, int $dia): void
    {
        $esHoy = $dia === self::DIAS_HISTORIA;
        $cantidadVentas = $esHoy ? 3 : [0, 1, 1, 1, 2, 2, 3][mt_rand(0, 6)];

        if ($fecha->isSaturday()) {
            $cantidadVentas = intdiv($cantidadVentas, 2);
        }

        for ($i = 0; $i < $cantidadVentas; $i++) {
            $venta = $this->registrarVenta($fecha, 40 + $i * 35);

            $proximaAnulacion = self::DIAS_VENTA_ANULADA[$this->ventasAnuladas] ?? null;
            if ($venta !== null && $proximaAnulacion !== null && $dia >= $proximaAnulacion) {
                $this->anularVenta($venta, $fecha);
                $this->ventasAnuladas++;
            }
        }
    }

    private function registrarVenta(
        Carbon $fecha,
        int $minutos,
        bool $confirmar = true,
        ?string $cuitCliente = null,
        ?string $codigoComprobante = null,
        ?string $notas = null,
        ?array $items = null,
    ): ?Venta {
        $cliente = $this->clientes[$cuitCliente ?? $this->elegirCliente()];
        $esEmpresa = $cliente->condicionIva?->nombre === 'Responsable Inscripto';

        $items ??= $this->elegirItems($esEmpresa);
        if ($items === []) {
            return null;
        }

        $codigoComprobante ??= $esEmpresa ? 'FC-A' : 'FC-B';

        $this->comoUsuario($this->vendedor, $this->instante($fecha, $minutos));

        $lineas = [];
        foreach ($items as $sku => $cantidad) {
            $lineas[] = [
                'producto_id' => $this->productos[$sku]->id,
                'cantidad' => $cantidad,
                'precio_unitario' => (float) $this->productos[$sku]->precio_venta,
            ];
        }

        $venta = $this->ventaService->crear([
            'cliente_id' => $cliente->id,
            'tipo_comprobante_id' => TipoComprobante::where('codigo', $codigoComprobante)->value('id'),
            'fecha_emision' => $fecha->toDateString(),
            'notas' => $notas,
        ], $lineas);

        if ($confirmar) {
            $venta = $this->ventaService->confirmar($venta);

            foreach ($items as $sku => $cantidad) {
                $this->stock[$sku] -= $cantidad;
            }
        }

        return $venta;
    }

    /**
     * Venta mayorista que deja algunos productos por debajo del mínimo.
     */
    private function ventaMayorista(Carbon $fecha): void
    {
        $items = [];
        foreach (self::STOCK_FINAL_BAJO_MINIMO as $sku => $stockFinal) {
            if ($this->stock[$sku] > $stockFinal) {
                $items[$sku] = $this->stock[$sku] - $stockFinal;
            }
        }

        if ($items !== []) {
            $this->registrarVenta($fecha, 20, cuitCliente: '30998680464', notas: 'Venta mayorista.', items: $items);
        }
    }

    private function anularVenta(Venta $venta, Carbon $fecha): void
    {
        $this->comoUsuario($this->admin, $this->instante($fecha, 200));
        $this->ventaService->anular($venta);

        foreach ($venta->items as $item) {
            $sku = $this->productos->firstWhere('id', $item->producto_id)->sku;
            $this->stock[$sku] += (float) $item->cantidad;
        }
    }

    /**
     * Elige de 1 a 3 productos con stock disponible, ponderados por rotación.
     *
     * @return array<string, float> sku => cantidad
     */
    private function elegirItems(bool $esEmpresa): array
    {
        $candidatos = [];
        foreach (self::ROTACION as $sku => [$peso]) {
            // Tras la venta mayorista, los productos sin reposición conservan su stock final.
            $congelado = $this->diaActual >= self::DIA_VENTA_MAYORISTA
                && isset(self::STOCK_FINAL_BAJO_MINIMO[$sku]);

            if ($peso > 0 && ! $congelado && $this->stock[$sku] >= 1) {
                $candidatos = array_merge($candidatos, array_fill(0, $peso, $sku));
            }
        }

        $items = [];
        $cantidadItems = mt_rand(1, 3);

        for ($i = 0; $i < $cantidadItems && $candidatos !== []; $i++) {
            $sku = $candidatos[mt_rand(0, count($candidatos) - 1)];
            $candidatos = array_values(array_filter($candidatos, fn (string $c): bool => $c !== $sku));

            [$peso] = self::ROTACION[$sku];
            $cantidad = match (true) {
                $peso >= 5 => mt_rand(2, 6),
                $peso >= 3 => mt_rand(1, 3),
                default => 1,
            };

            if ($esEmpresa) {
                $cantidad *= 2;
            }

            $items[$sku] = (float) min($cantidad, floor($this->stock[$sku]));
        }

        return $items;
    }

    private function elegirCliente(): string
    {
        $pool = [];
        foreach (self::FRECUENCIA_CLIENTES as $cuit => $peso) {
            $pool = array_merge($pool, array_fill(0, $peso, (string) $cuit));
        }

        return $pool[mt_rand(0, count($pool) - 1)];
    }

    // ─── Pendientes (sin confirmar) ──────────────────────────────────────────

    private function operacionesPendientes(Carbon $hoy): void
    {
        $this->registrarVenta($hoy, 150, confirmar: false, cuitCliente: '30988219238');
        $this->registrarVenta(
            $hoy,
            160,
            confirmar: false,
            cuitCliente: '30996709627',
            codigoComprobante: 'PRES',
            notas: 'Presupuesto pendiente de aprobación por el cliente.',
        );

        $this->registrarCompra(
            self::PROVEEDOR_POR_RUBRO['ALI'],
            ['ALI-002' => 120],
            $hoy,
            170,
            'Pendiente de recepción en depósito.',
            confirmar: false,
        );
        $this->registrarCompra(
            self::PROVEEDOR_POR_RUBRO['TEC'],
            ['TEC-004' => 40],
            $hoy,
            180,
            'Pendiente de recepción en depósito.',
            confirmar: false,
        );
    }

    // ─── Utilidades ──────────────────────────────────────────────────────────

    private function comoUsuario(User $user, Carbon $momento): void
    {
        Carbon::setTestNow($momento);
        Auth::setUser($user);
    }

    /**
     * Momento del día a partir de las 09:00. Para el día actual se corre la
     * apertura hacia atrás, así ninguna operación queda fechada en el futuro.
     */
    private function instante(Carbon $fecha, int $minutos): Carbon
    {
        $apertura = $fecha->copy()->setTime(9, 0);

        if ($fecha->isSameDay($this->ahora)) {
            $apertura = $apertura->min($this->ahora->copy()->subMinutes(210))->max($fecha->copy()->startOfDay());
        }

        return $apertura->addMinutes($minutos);
    }

    private function costoEnFecha(string $sku, Carbon $fecha): float
    {
        $meses = $this->inicio->diffInDays($fecha) / 30;

        return round($this->costoBase[$sku] * (1 + self::INFLACION_MENSUAL_COSTOS * $meses), -1);
    }

    /** @return list<string> */
    private function skusDelRubro(string $rubro): array
    {
        return array_values(array_filter(
            array_keys(self::ROTACION),
            fn (string $sku): bool => str_starts_with($sku, $rubro . '-'),
        ));
    }

    /**
     * Garantiza que stock_actual coincida con el espejo local y con la suma del kardex.
     */
    private function verificarStock(): void
    {
        $sumaKardex = StockMovement::groupBy('producto_id')
            ->selectRaw('producto_id, SUM(cantidad) as total')
            ->pluck('total', 'producto_id');

        foreach ($this->productos as $sku => $producto) {
            $actual = (float) $producto->fresh()->stock_actual;
            $kardex = (float) ($sumaKardex[$producto->id] ?? 0);

            if (abs($actual - $this->stock[$sku]) > 0.001 || abs($actual - $kardex) > 0.001) {
                throw new RuntimeException(
                    "Stock inconsistente en {$sku}: actual={$actual}, esperado={$this->stock[$sku]}, kardex={$kardex}"
                );
            }
        }
    }
}
