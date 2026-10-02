@extends('layouts.app')

@section('title', isset($proveedor) ? 'Editar Proveedor' : 'Nuevo Proveedor')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ isset($proveedor) ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h1>
            <a href="{{ route('proveedores.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form method="POST" action="{{ isset($proveedor) ? route('proveedores.update', $proveedor) : route('proveedores.store') }}">
                @csrf
                @isset($proveedor)
                    @method('PUT')
                @endisset

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="razon_social" class="block text-sm font-medium text-gray-700 mb-1">Razón Social <span class="text-red-500">*</span></label>
                        <input type="text" name="razon_social" id="razon_social" value="{{ old('razon_social', $proveedor->razon_social ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('razon_social') border-red-400 @enderror">
                        @error('razon_social')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="cuit_dni" class="block text-sm font-medium text-gray-700 mb-1">CUIT/DNI <span class="text-red-500">*</span></label>
                        <input type="text" name="cuit_dni" id="cuit_dni" value="{{ old('cuit_dni', $proveedor->cuit_dni ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('cuit_dni') border-red-400 @enderror">
                        @error('cuit_dni')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="condicion_iva_id" class="block text-sm font-medium text-gray-700 mb-1">Condición IVA <span class="text-red-500">*</span></label>
                        <select name="condicion_iva_id" id="condicion_iva_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('condicion_iva_id') border-red-400 @enderror">
                            <option value="">Seleccione una condición</option>
                            @foreach($condicionesIva as $condicion)
                                <option value="{{ $condicion->id }}" {{ old('condicion_iva_id', $proveedor->condicion_iva_id ?? '') == $condicion->id ? 'selected' : '' }}>{{ $condicion->nombre }}</option>
                            @endforeach
                        </select>
                        @error('condicion_iva_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $proveedor->telefono ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('telefono') border-red-400 @enderror">
                        @error('telefono')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $proveedor->email ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('email') border-red-400 @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <textarea name="direccion" id="direccion" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('direccion') border-red-400 @enderror">{{ old('direccion', $proveedor->direccion ?? '') }}</textarea>
                        @error('direccion')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-gray-100">
                    <a href="{{ route('proveedores.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancelar</a>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        {{ isset($proveedor) ? 'Actualizar Proveedor' : 'Guardar Proveedor' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
