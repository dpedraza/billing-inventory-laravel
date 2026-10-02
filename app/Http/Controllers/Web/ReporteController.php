<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Venta;
use App\Services\ReporteService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function __construct(
        private readonly ReporteService $reporteService
    ) {}

    public function index(): View
    {
        return view('reportes.index');
    }

    public function exportStock(string $formato): mixed
    {
        return match ($formato) {
            'pdf' => $this->reporteService->exportarStockPDF()->download('reporte_stock.pdf'),
            'excel' => $this->reporteService->exportarStockExcel(),
            default => abort(404),
        };
    }

    public function exportVentas(Request $request, string $formato): mixed
    {
        $this->authorize('exportar', Venta::class);

        $fechaDesde = $request->get('desde');
        $fechaHasta = $request->get('hasta');

        return match ($formato) {
            'pdf' => $this->reporteService->exportarVentasPDF($fechaDesde, $fechaHasta)->download('reporte_ventas.pdf'),
            'excel' => $this->reporteService->exportarVentasExcel($fechaDesde, $fechaHasta),
            default => abort(404),
        };
    }

    public function exportCompras(Request $request, string $formato): mixed
    {
        $this->authorize('exportar', Compra::class);

        $fechaDesde = $request->get('desde');
        $fechaHasta = $request->get('hasta');

        return match ($formato) {
            'pdf' => $this->reporteService->exportarComprasPDF($fechaDesde, $fechaHasta)->download('reporte_compras.pdf'),
            'excel' => $this->reporteService->exportarComprasExcel($fechaDesde, $fechaHasta),
            default => abort(404),
        };
    }
}
