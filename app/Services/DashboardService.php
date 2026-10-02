<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CompraEstado;
use App\Enums\VentaEstado;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getResumenVentas(?string $fechaDesde = null, ?string $fechaHasta = null): array
    {
        $fechaDesde ??= now()->startOfMonth()->toDateString();
        $fechaHasta ??= now()->endOfMonth()->toDateString();

        $resumen = Venta::where('estado', VentaEstado::Pagada)
            ->whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
            ->selectRaw('COALESCE(SUM(total), 0) as total_ventas')
            ->selectRaw('COALESCE(SUM(impuesto), 0) as total_impuesto')
            ->selectRaw('COUNT(*) as cantidad_ventas')
            ->first();

        $ventasHoy = Venta::where('estado', VentaEstado::Pagada)
            ->whereDate('fecha_emision', now()->toDateString())
            ->selectRaw('COALESCE(SUM(total), 0) as total_hoy')
            ->selectRaw('COUNT(*) as cantidad_hoy')
            ->first();

        $utilidadEstimada = VentaItem::join('ventas', 'ventas.id', '=', 'venta_items.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_items.producto_id')
            ->where('ventas.estado', VentaEstado::Pagada)
            ->whereBetween('ventas.fecha_emision', [$fechaDesde, $fechaHasta])
            ->selectRaw('COALESCE(SUM(venta_items.subtotal - (venta_items.cantidad * productos.precio_costo)), 0) as total_utilidad')
            ->value('total_utilidad');

        return [
            'total_ventas' => (float) ($resumen->total_ventas ?? 0),
            'total_impuesto' => (float) ($resumen->total_impuesto ?? 0),
            'cantidad_ventas' => (int) ($resumen->cantidad_ventas ?? 0),
            'total_ventas_hoy' => (float) ($ventasHoy->total_hoy ?? 0),
            'cantidad_ventas_hoy' => (int) ($ventasHoy->cantidad_hoy ?? 0),
            'utilidad_estimada' => (float) ($utilidadEstimada ?? 0),
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
        ];
    }

    public function getProductosMasVendidos(int $limit = 10): Collection
    {
        return VentaItem::select(
            'producto_id',
            DB::raw('SUM(venta_items.cantidad) as total_cantidad'),
            DB::raw('SUM(venta_items.subtotal) as total_ingresos'),
        )
            ->join('ventas', 'ventas.id', '=', 'venta_items.venta_id')
            ->where('ventas.estado', VentaEstado::Pagada)
            ->groupBy('producto_id')
            ->orderByDesc('total_cantidad')
            ->limit($limit)
            ->get()
            ->load('producto');
    }

    public function getResumenInventario(): array
    {
        $totalProductos = Producto::count();
        $productosActivos = Producto::where('activo', true)->count();
        $stockBajo = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        $valorInventario = Producto::where('activo', true)
            ->selectRaw('COALESCE(SUM(stock_actual * precio_costo), 0) as valor_total')
            ->value('valor_total');

        return [
            'total_productos' => $totalProductos,
            'productos_activos' => $productosActivos,
            'stock_bajo' => $stockBajo,
            'valor_inventario' => (float) ($valorInventario ?? 0),
        ];
    }

    public function getVentasPorDia(?string $fechaDesde = null, ?string $fechaHasta = null): Collection
    {
        $fechaDesde ??= now()->startOfMonth()->toDateString();
        $fechaHasta ??= now()->endOfMonth()->toDateString();

        return Venta::where('estado', VentaEstado::Pagada)
            ->whereBetween('fecha_emision', [$fechaDesde, $fechaHasta])
            ->selectRaw('DATE(fecha_emision) as dia')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->selectRaw('COUNT(*) as cantidad')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();
    }

    public function getProductosStockCritico(int $limit = 5): Collection
    {
        return Producto::where('activo', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getProductosSinMovimiento(int $days = 30, int $limit = 5): Collection
    {
        $haceNDias = now()->subDays($days);

        return Producto::where('activo', true)
            ->whereDoesntHave('stockMovements', function ($query) use ($haceNDias) {
                $query->where('created_at', '>=', $haceNDias);
            })
            ->orderBy('updated_at', 'asc')
            ->limit($limit)
            ->get();
    }

    public function getVentasPendientes(int $limit = 5): Collection
    {
        return Venta::with(['cliente', 'tipoComprobante'])
            ->where('estado', VentaEstado::Pendiente)
            ->orderBy('fecha_emision', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getComprasPendientes(int $limit = 5): Collection
    {
        return Compra::with('proveedor')
            ->where('estado', CompraEstado::Pendiente)
            ->orderBy('fecha_emision', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTopClientes(int $limit = 5): Collection
    {
        return Cliente::select('clientes.*')
            ->selectRaw('COALESCE(SUM(ventas.total), 0) as total_facturado')
            ->selectRaw('COUNT(ventas.id) as cantidad_compras')
            ->join('ventas', 'ventas.cliente_id', '=', 'clientes.id')
            ->where('ventas.estado', VentaEstado::Pagada)
            ->whereBetween('ventas.fecha_emision', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->groupBy('clientes.id')
            ->orderByDesc('total_facturado')
            ->limit($limit)
            ->get();
    }

    public function getTopProveedores(int $limit = 5): Collection
    {
        return Proveedor::select('proveedores.*')
            ->selectRaw('COALESCE(SUM(compras.total), 0) as total_comprado')
            ->selectRaw('COUNT(compras.id) as cantidad_ordenes')
            ->join('compras', 'compras.proveedor_id', '=', 'proveedores.id')
            ->where('compras.estado', CompraEstado::Completada)
            ->whereBetween('compras.fecha_emision', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->groupBy('proveedores.id')
            ->orderByDesc('total_comprado')
            ->limit($limit)
            ->get();
    }

    public function getMovimientosRecientes(int $limit = 8): Collection
    {
        return StockMovement::with(['producto', 'creador'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getAlertasAtencion(): array
    {
        return [
            'stock_critico' => Producto::where('activo', true)->whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'ventas_pendientes' => Venta::where('estado', VentaEstado::Pendiente)->count(),
            'compras_pendientes' => Compra::where('estado', CompraEstado::Pendiente)->count(),
            'sin_movimiento' => Producto::where('activo', true)->whereDoesntHave('stockMovements', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })->count(),
        ];
    }

    public function getDashboardDataForUser(User $user): array
    {
        $role = $user->roles->first()?->name ?? 'Admin';

        $resumenVentas = $this->getResumenVentas();
        $resumenInventario = $this->getResumenInventario();
        $masVendidos = $this->getProductosMasVendidos(5);
        $stockCritico = $this->getProductosStockCritico(5);
        $sinMovimiento = $this->getProductosSinMovimiento(30, 5);
        $ventasPendientes = $this->getVentasPendientes(5);
        $comprasPendientes = $this->getComprasPendientes(5);
        $topClientes = $this->getTopClientes(5);
        $topProveedores = $this->getTopProveedores(5);
        $movimientosRecientes = $this->getMovimientosRecientes(8);
        $alertasAtencion = $this->getAlertasAtencion();

        return [
            'userRole' => $role,
            'resumenVentas' => $resumenVentas,
            'resumenInventario' => $resumenInventario,
            'masVendidos' => $masVendidos,
            'stockCritico' => $stockCritico,
            'sinMovimiento' => $sinMovimiento,
            'ventasPendientes' => $ventasPendientes,
            'comprasPendientes' => $comprasPendientes,
            'topClientes' => $topClientes,
            'topProveedores' => $topProveedores,
            'movimientosRecientes' => $movimientosRecientes,
            'alertasAtencion' => $alertasAtencion,
        ];
    }
}

