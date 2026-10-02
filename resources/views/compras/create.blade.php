@extends('layouts.app')
@section('title', 'Nueva Compra')
@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Nueva Compra</h1>
    @include('compras.form')
</div>
@endsection
