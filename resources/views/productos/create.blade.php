@extends('layouts.app')
@section('title', 'Nuevo Producto')
@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Nuevo Producto</h1>
    @include('productos.form')
</div>
@endsection
