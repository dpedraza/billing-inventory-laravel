@extends('layouts.app')
@section('title', 'Editar Proveedor')
@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Editar Proveedor</h1>
    @include('proveedores.form')
</div>
@endsection
