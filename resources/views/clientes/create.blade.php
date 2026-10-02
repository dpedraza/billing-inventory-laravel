@extends('layouts.app')
@section('title', 'Nuevo Cliente')
@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-bold text-gray-900 mb-6">Nuevo Cliente</h1>
    @include('clientes.form')
</div>
@endsection
