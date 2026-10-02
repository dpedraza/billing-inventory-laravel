@extends('layouts.app')
@section('title', 'Nueva Venta')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Nueva Venta</h1>
    @include('ventas.form')
</div>
@endsection
