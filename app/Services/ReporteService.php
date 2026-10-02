<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Illuminate\Database\Eloquent\Builder;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteService
{
    public function exportarStockPDF(): DomPDF
    {
        $productos = Producto::with('categoria')->orderBy('nombre')->get();

        return Pdf::loadView('pdfs.stock', compact('productos'));
    }

    public function exportarVentaIndividualPDF(Venta $venta): DomPDF
    {
        $venta->load(['cliente.condicionIva', 'tipoComprobante', 'items.producto', 'creador']);

        return Pdf::loadView('pdfs.venta_detalle', compact('venta'));
    }

    public function exportarCompraIndividualPDF(Compra $compra): DomPDF
    {
        $compra->load(['proveedor', 'items.producto', 'creador']);

        return Pdf::loadView('pdfs.compra_detalle', compact('compra'));
    }

    public function exportarVentasPDF(?string $fechaDesde, ?string $fechaHasta): DomPDF
    {
        $ventas = $this->ventasQuery($fechaDesde, $fechaHasta)->with('items.producto')->get();

        return Pdf::loadView('pdfs.ventas', compact('ventas', 'fechaDesde', 'fechaHasta'));
    }

    public function exportarComprasPDF(?string $fechaDesde, ?string $fechaHasta): DomPDF
    {
        $compras = $this->comprasQuery($fechaDesde, $fechaHasta)->with('items.producto')->get();

        return Pdf::loadView('pdfs.compras', compact('compras', 'fechaDesde', 'fechaHasta'));
    }

    public function exportarStockExcel(): StreamedResponse
    {
        $filas = Producto::with('categoria')->orderBy('nombre')->lazy()->map(fn (Producto $producto): array => [
            $producto->sku,
            $producto->nombre,
            $producto->categoria?->nombre ?? '',
            (float) $producto->stock_actual,
            (float) $producto->stock_minimo,
            (float) $producto->precio_costo,
            (float) $producto->precio_venta,
            $producto->activo ? 'Sí' : 'No',
        ]);

        return $this->descargarExcel('reporte_stock.xlsx', [
            'SKU', 'Nombre', 'Categoría', 'Stock Actual', 'Stock Mínimo',
            'Precio Costo', 'Precio Venta', 'Activo',
        ], $filas);
    }

    public function exportarVentasExcel(?string $fechaDesde, ?string $fechaHasta): StreamedResponse
    {
        $filas = $this->ventasQuery($fechaDesde, $fechaHasta)->with('tipoComprobante')->lazy()->map(fn (Venta $venta): array => [
            $venta->numero_comprobante ?? '',
            $venta->tipoComprobante?->nombre ?? '',
            $venta->fecha_emision->format('d/m/Y'),
            $venta->cliente?->razon_social ?? 'Consumidor Final',
            $venta->cliente?->cuit_dni ?? '',
            ucfirst($venta->estado->value),
            (float) $venta->subtotal,
            (float) $venta->impuesto,
            (float) $venta->total,
        ]);

        return $this->descargarExcel('reporte_ventas.xlsx', [
            'N° Comprobante', 'Tipo', 'Fecha', 'Cliente', 'CUIT/DNI', 'Estado',
            'Subtotal', 'IVA', 'Total',
        ], $filas);
    }

    public function exportarComprasExcel(?string $fechaDesde, ?string $fechaHasta): StreamedResponse
    {
        $filas = $this->comprasQuery($fechaDesde, $fechaHasta)->lazy()->map(fn (Compra $compra): array => [
            $compra->numero_orden ?? '',
            $compra->fecha_emision->format('d/m/Y'),
            $compra->proveedor?->razon_social ?? '',
            $compra->proveedor?->cuit_dni ?? '',
            ucfirst($compra->estado->value),
            (float) $compra->subtotal,
            (float) $compra->impuesto,
            (float) $compra->total,
        ]);

        return $this->descargarExcel('reporte_compras.xlsx', [
            'N° Orden', 'Fecha', 'Proveedor', 'CUIT', 'Estado',
            'Subtotal', 'IVA', 'Total',
        ], $filas);
    }

    private function ventasQuery(?string $fechaDesde, ?string $fechaHasta): Builder
    {
        return $this->filtrarPorFecha(Venta::with('cliente'), $fechaDesde, $fechaHasta)
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id');
    }

    private function comprasQuery(?string $fechaDesde, ?string $fechaHasta): Builder
    {
        return $this->filtrarPorFecha(Compra::with('proveedor'), $fechaDesde, $fechaHasta)
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id');
    }

    private function filtrarPorFecha(Builder $query, ?string $fechaDesde, ?string $fechaHasta): Builder
    {
        if ($fechaDesde && $fechaHasta) {
            $query->whereBetween('fecha_emision', [$fechaDesde, $fechaHasta]);
        } elseif ($fechaDesde) {
            $query->whereDate('fecha_emision', '>=', $fechaDesde);
        } elseif ($fechaHasta) {
            $query->whereDate('fecha_emision', '<=', $fechaHasta);
        }

        return $query;
    }

    /**
     * Escribe el XLSX directamente en la respuesta (sin archivo temporal propio),
     * leyendo los registros por lotes con lazy() para acotar el uso de memoria.
     *
     * @param list<string> $encabezados
     * @param iterable<array<int, mixed>> $filas
     */
    private function descargarExcel(string $nombreArchivo, array $encabezados, iterable $filas): StreamedResponse
    {
        return response()->streamDownload(function () use ($encabezados, $filas): void {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($encabezados));

            foreach ($filas as $fila) {
                $writer->addRow(Row::fromValues($fila));
            }

            $writer->close();
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
