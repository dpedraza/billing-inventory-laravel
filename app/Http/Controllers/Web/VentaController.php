<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\TipoComprobante;
use App\Models\Venta;
use App\Services\ReporteService;
use App\Services\VentaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function __construct(
        private readonly VentaService $ventaService,
        private readonly ReporteService $reporteService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Venta::class);

        $ventas = Venta::with('cliente')
            ->when($request->estado, fn($query, $estado) => $query->where('estado', $estado))
            ->when($request->cliente_id, fn($query, $id) => $query->where('cliente_id', $id))
            ->when($request->fecha_desde, fn($query, $fecha) => $query->whereDate('fecha_emision', '>=', $fecha))
            ->when($request->fecha_hasta, fn($query, $fecha) => $query->whereDate('fecha_emision', '<=', $fecha))
            ->orderByDesc('id')
            ->paginate(15);

        $clientes = Cliente::where('activo', true)->orderBy('razon_social')->get();

        return view('ventas.index', compact('ventas', 'clientes'));
    }

    public function create(): View
    {
        $this->authorize('create', Venta::class);

        $clientes = Cliente::where('activo', true)->orderBy('razon_social')->get();
        $tiposComprobante = TipoComprobante::where('activo', true)->orderBy('nombre')->get();
        $productos = Producto::where('activo', true)
            ->with('categoria')
            ->orderBy('nombre')
            ->get();

        return view('ventas.create', compact('clientes', 'tiposComprobante', 'productos'));
    }

    public function store(StoreVentaRequest $request): RedirectResponse
    {
        $venta = $this->ventaService->crear(
            $request->safe()->except('items'),
            $request->validated('items'),
        );

        return redirect()->route('ventas.show', $venta)
            ->with('success', 'Venta creada correctamente.');
    }

    public function show(Venta $venta): View
    {
        $this->authorize('view', $venta);

        $venta->load(['cliente', 'tipoComprobante', 'items.producto', 'creador']);

        return view('ventas.show', compact('venta'));
    }

    public function pdf(Venta $venta): Response
    {
        $this->authorize('view', $venta);

        $pdf = $this->reporteService->exportarVentaIndividualPDF($venta);

        $filename = 'Venta_' . ($venta->numero_comprobante ?? (string)$venta->id) . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function confirmar(Venta $venta): RedirectResponse
    {
        $this->authorize('confirmar', $venta);

        try {
            $this->ventaService->confirmar($venta);

            return back()->with('success', 'Venta confirmada correctamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function anular(Venta $venta): RedirectResponse
    {
        $this->authorize('anular', $venta);

        try {
            $this->ventaService->anular($venta);

            return back()->with('success', 'Venta anulada correctamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
