<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\CompraService;
use App\Services\ReporteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CompraController extends Controller
{
    public function __construct(
        private readonly CompraService $compraService,
        private readonly ReporteService $reporteService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Compra::class);

        $compras = Compra::with('proveedor')
            ->when($request->estado, fn($query, $estado) => $query->where('estado', $estado))
            ->when($request->proveedor_id, fn($query, $id) => $query->where('proveedor_id', $id))
            ->when($request->fecha_desde, fn($query, $fecha) => $query->whereDate('fecha_emision', '>=', $fecha))
            ->when($request->fecha_hasta, fn($query, $fecha) => $query->whereDate('fecha_emision', '<=', $fecha))
            ->orderByDesc('id')
            ->paginate(15);

        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();

        return view('compras.index', compact('compras', 'proveedores'));
    }

    public function create(): View
    {
        $this->authorize('create', Compra::class);

        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        $productos = Producto::where('activo', true)->orderBy('nombre')->get();

        return view('compras.create', compact('proveedores', 'productos'));
    }

    public function store(StoreCompraRequest $request): RedirectResponse
    {
        $compra = $this->compraService->crear(
            $request->safe()->except('items'),
            $request->validated('items'),
        );

        return redirect()->route('compras.show', $compra)
            ->with('success', 'Compra creada correctamente.');
    }

    public function show(Compra $compra): View
    {
        $this->authorize('view', $compra);

        $compra->load(['proveedor', 'items.producto', 'creador']);

        return view('compras.show', compact('compra'));
    }

    public function pdf(Compra $compra): Response
    {
        $this->authorize('view', $compra);

        $pdf = $this->reporteService->exportarCompraIndividualPDF($compra);

        $filename = 'Compra_' . ($compra->numero_orden ?? (string)$compra->id) . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function confirmar(Compra $compra): RedirectResponse
    {
        $this->authorize('confirmar', $compra);

        try {
            $this->compraService->confirmar($compra);

            return back()->with('success', 'Compra confirmada correctamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function anular(Compra $compra): RedirectResponse
    {
        $this->authorize('anular', $compra);

        try {
            $this->compraService->anular($compra);

            return back()->with('success', 'Compra anulada correctamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
