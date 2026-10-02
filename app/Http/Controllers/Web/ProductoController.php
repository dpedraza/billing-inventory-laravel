<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\ProductoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function __construct(
        private readonly ProductoService $productoService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Producto::class);

        $productos = Producto::with('categoria')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->categoria_id, fn($q, $v) => $q->where('categoria_id', $v))
            ->orderByDesc('id')
            ->paginate(15);

        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('productos.index', compact('productos', 'categorias'));
    }

    public function create(): View
    {
        $this->authorize('create', Producto::class);

        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('productos.create', compact('categorias'));
    }

    public function store(StoreProductoRequest $request): RedirectResponse
    {
        $producto = $this->productoService->crear($request->validated());

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $producto): View
    {
        $this->authorize('view', $producto);

        $producto->load(['categoria', 'stockMovements' => function ($query) {
            $query->orderByDesc('created_at')->limit(50);
        }]);

        return view('productos.show', compact('producto'));
    }

    public function edit(Producto $producto): View
    {
        $this->authorize('update', $producto);

        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('productos.edit', compact('producto', 'categorias'));
    }

    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        $this->productoService->actualizar($producto, $request->validated());

        return redirect()->route('productos.show', $producto)
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $this->authorize('delete', $producto);

        $producto->delete();

        return redirect()->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
