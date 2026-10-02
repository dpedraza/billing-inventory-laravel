<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventarioController extends Controller
{
    public function __construct(
        private readonly StockService $stockService
    ) {}

    public function index(Request $request): View
    {
        $productos = Producto::with('categoria')
            ->when($request->categoria_id, fn($query, $id) => $query->where('categoria_id', $id))
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(15);

        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('inventario.index', compact('productos', 'categorias'));
    }

    public function kardex(Request $request, Producto $producto): View
    {
        $movimientos = $this->stockService->getKardex($producto, $request->only(['tipo', 'tipo_movimiento', 'desde', 'hasta']));

        return view('inventario.kardex', compact('producto', 'movimientos'));
    }

    public function stockBajo(Request $request): View
    {
        $productos = Producto::with('categoria')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(15);

        return view('inventario.stock-bajo', compact('productos'));
    }
}
