@extends('layouts.app')

@section('title', 'Nueva Compra')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Nueva Compra</h1>
            <a href="{{ route('compras.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </div>

        <form method="POST" action="{{ route('compras.store') }}" x-data="compraForm()">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Información de la Compra</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1">Proveedor <span class="text-red-500">*</span></label>
                                <select name="proveedor_id" id="proveedor_id" x-ref="proveedorSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('proveedor_id') border-red-400 @enderror">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->id }}" {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>{{ $proveedor->razon_social }} ({{ $proveedor->cuit_dni }})</option>
                                    @endforeach
                                </select>
                                @error('proveedor_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="numero_orden" class="block text-sm font-medium text-gray-700 mb-1">N° Orden <span class="text-red-500">*</span></label>
                                <input type="text" name="numero_orden" id="numero_orden" value="{{ old('numero_orden') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('numero_orden') border-red-400 @enderror">
                                @error('numero_orden')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="fecha_emision" class="block text-sm font-medium text-gray-700 mb-1">Fecha de Emisión <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_emision" id="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('fecha_emision') border-red-400 @enderror">
                                @error('fecha_emision')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="notas" class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea name="notas" id="notas" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('notas') border-red-400 @enderror">{{ old('notas') }}</textarea>
                            @error('notas')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">Productos</h2>
                            <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 text-sm font-medium rounded-lg hover:bg-indigo-100 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Añadir Producto
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                                        <th class="px-4 py-3">Producto</th>
                                        <th class="px-4 py-3 text-right w-28">Cantidad</th>
                                        <th class="px-4 py-3 text-right w-36">Costo Unitario</th>
                                        <th class="px-4 py-3 text-right w-36">Subtotal</th>
                                        <th class="px-4 py-3 text-center w-16"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr>
                                            <td class="px-4 py-2">
                                                <select x-model="item.producto_id" :name="'items['+index+'][producto_id]'" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="">Seleccione...</option>
                                                    @foreach($productos as $producto)
                                                        <option value="{{ $producto->id }}" data-precio="{{ $producto->precio_costo }}">{{ $producto->nombre }} ({{ $producto->sku }})</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" x-model="item.cantidad" @input="calcRow(index)" :name="'items['+index+'][cantidad]'" min="0.01" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-right focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" x-model="item.costo_unitario" @input="calcRow(index)" :name="'items['+index+'][costo_unitario]'" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-right focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                            </td>
                                            <td class="px-4 py-2 text-right font-medium text-gray-900" x-text="'$ ' + item.subtotal.toFixed(2)"></td>
                                            <td class="px-4 py-2 text-center">
                                                <button type="button" @click="removeItem(index)" class="p-1.5 text-gray-400 hover:text-red-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="items.length === 0">
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">No hay productos agregados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Totales</h2>
                        <dl class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">Subtotal</dt>
                                <dd class="font-medium text-gray-900" x-text="'$ ' + subtotal.toFixed(2)">$ 0.00</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">IVA (21%)</dt>
                                <dd class="font-medium text-gray-900" x-text="'$ ' + iva.toFixed(2)">$ 0.00</dd>
                            </div>
                            <div class="border-t border-gray-100 pt-3 flex justify-between text-base">
                                <dt class="font-semibold text-gray-900">Total</dt>
                                <dd class="font-bold text-indigo-600" x-text="'$ ' + total.toFixed(2)">$ 0.00</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('compras.index') }}" class="flex-1 text-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancelar</a>
                        <button type="submit" class="flex-1 px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">Guardar Compra</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('compraForm', () => ({
            items: [],
            ivaRate: 0.21,

            get subtotal() {
                return this.items.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0);
            },

            get iva() {
                return this.subtotal * this.ivaRate;
            },

            get total() {
                return this.subtotal + this.iva;
            },

            addItem() {
                this.items.push({
                    producto_id: '',
                    cantidad: 1,
                    costo_unitario: 0,
                    subtotal: 0
                });
                this.$nextTick(() => {
                    const selects = this.$el.querySelectorAll('select');
                    const lastSelect = selects[selects.length - 1];
                    if (lastSelect) lastSelect.focus();
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            calcRow(index) {
                const item = this.items[index];
                const cant = parseFloat(item.cantidad) || 0;
                const costo = parseFloat(item.costo_unitario) || 0;
                item.subtotal = cant * costo;
            }
        }));
    });
</script>
@endpush
