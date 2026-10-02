<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\CondicionIva;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Proveedor::class);

        $proveedores = Proveedor::with('condicionIva')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('razon_social', 'like', "%{$search}%")
                      ->orWhere('cuit_dni', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15);

        return view('proveedores.index', compact('proveedores'));
    }

    public function create(): View
    {
        $this->authorize('create', Proveedor::class);

        $condicionesIva = CondicionIva::where('activo', true)->orderBy('nombre')->get();

        return view('proveedores.create', compact('condicionesIva'));
    }

    public function store(StoreProveedorRequest $request): RedirectResponse
    {
        $proveedor = Proveedor::create($request->validated() + [
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('proveedores.show', $proveedor)
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function show(Proveedor $proveedor): View
    {
        $this->authorize('view', $proveedor);

        $proveedor->load('condicionIva');

        return view('proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor): View
    {
        $this->authorize('update', $proveedor);

        $condicionesIva = CondicionIva::where('activo', true)->orderBy('nombre')->get();

        return view('proveedores.edit', compact('proveedor', 'condicionesIva'));
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        $proveedor->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('proveedores.show', $proveedor)
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $this->authorize('delete', $proveedor);

        $proveedor->delete();

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }
}
