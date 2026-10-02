@extends('layouts.app')

@section('title', isset($producto) ? 'Editar Producto' : 'Nuevo Producto')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ isset($producto) ? 'Editar Producto' : 'Nuevo Producto' }}</h1>
            <a href="{{ route('productos.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ isset($producto) ? route('productos.update', $producto) : route('productos.store') }}">
                @csrf
                @isset($producto)
                    @method('PUT')
                @endisset

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">SKU <span class="text-red-500">*</span></label>
                        <input type="text" name="sku" id="sku" value="{{ old('sku', $producto->sku ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('sku') border-red-400 @enderror">
                        @error('sku')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('nombre') border-red-400 @enderror">
                        @error('nombre')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" id="descripcion" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('descripcion') border-red-400 @enderror">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
                        @error('descripcion')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="categoria_id" class="block text-sm font-medium text-gray-700 mb-1">Categoría <span class="text-red-500">*</span></label>
                        <select name="categoria_id" id="categoria_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('categoria_id') border-red-400 @enderror">
                            <option value="">Seleccione una categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id', $producto->categoria_id ?? '') == $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="unidad_medida" class="block text-sm font-medium text-gray-700 mb-1">Unidad de Medida <span class="text-red-500">*</span></label>
                        <select name="unidad_medida" id="unidad_medida" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('unidad_medida') border-red-400 @enderror">
                            <option value="">Seleccione...</option>
                            <option value="unidad" {{ old('unidad_medida', $producto->unidad_medida ?? '') == 'unidad' ? 'selected' : '' }}>Unidad</option>
                            <option value="kg" {{ old('unidad_medida', $producto->unidad_medida ?? '') == 'kg' ? 'selected' : '' }}>Kilogramo (kg)</option>
                            <option value="litro" {{ old('unidad_medida', $producto->unidad_medida ?? '') == 'litro' ? 'selected' : '' }}>Litro</option>
                            <option value="metro" {{ old('unidad_medida', $producto->unidad_medida ?? '') == 'metro' ? 'selected' : '' }}>Metro</option>
                            <option value="caja" {{ old('unidad_medida', $producto->unidad_medida ?? '') == 'caja' ? 'selected' : '' }}>Caja</option>
                            <option value="pack" {{ old('unidad_medida', $producto->unidad_medida ?? '') == 'pack' ? 'selected' : '' }}>Pack</option>
                        </select>
                        @error('unidad_medida')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="precio_costo" class="block text-sm font-medium text-gray-700 mb-1">Precio Costo <span class="text-red-500">*</span></label>
                        <input type="number" name="precio_costo" id="precio_costo" step="0.01" min="0" value="{{ old('precio_costo', $producto->precio_costo ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('precio_costo') border-red-400 @enderror">
                        @error('precio_costo')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="precio_venta" class="block text-sm font-medium text-gray-700 mb-1">Precio Venta <span class="text-red-500">*</span></label>
                        <input type="number" name="precio_venta" id="precio_venta" step="0.01" min="0" value="{{ old('precio_venta', $producto->precio_venta ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('precio_venta') border-red-400 @enderror">
                        @error('precio_venta')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="stock_minimo" class="block text-sm font-medium text-gray-700 mb-1">Stock Mínimo <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_minimo" id="stock_minimo" step="0.01" min="0" value="{{ old('stock_minimo', $producto->stock_minimo ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('stock_minimo') border-red-400 @enderror">
                        @error('stock_minimo')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="stock_actual" class="block text-sm font-medium text-gray-700 mb-1">Stock Actual <span class="text-red-500">*</span></label>
                        <input type="number" name="stock_actual" id="stock_actual" step="0.01" min="0" value="{{ old('stock_actual', $producto->stock_actual ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('stock_actual') border-red-400 @enderror">
                        <p class="mt-1 text-xs text-gray-500">La diferencia con el stock actual se registra en el kardex como ajuste.</p>
                        @error('stock_actual')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                    <a href="{{ route('productos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancelar</a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        {{ isset($producto) ? 'Actualizar Producto' : 'Guardar Producto' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
