@extends('layouts.app')

@section('title', 'Dashboard Operativo')

@section('content')
<div class="space-y-6">

    <!-- HEADER TITLE BAR & PERIOD FILTER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Centro de Control Operativo</h1>
                <span class="text-[11px] font-mono bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200">AR-ARS</span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Visión consolidada en tiempo real. Fecha: <span class="font-mono text-slate-700 font-semibold">{{ now()->format('d/m/Y') }}</span>
            </p>
        </div>

        <div class="inline-flex bg-slate-100 p-1 rounded-lg border border-slate-200 text-xs font-medium">
            <span class="px-3 py-1 bg-white text-slate-900 rounded-md shadow-xs font-semibold">Este Mes</span>
        </div>
    </div>

    <!-- GLOBAL ACTION ALERT BANNER -->
    <x-card-alerta 
        title="Alertas del Sistema" 
        :stockCritico="$alertasAtencion['stock_critico'] ?? 0" 
        :comprasPendientes="$alertasAtencion['compras_pendientes'] ?? 0" 
        :ventasPendientes="$alertasAtencion['ventas_pendientes'] ?? 0" 
    />

    <!-- DYNAMIC ROLE PARTIAL INCLUSION -->
    @if(($userRole ?? 'Admin') === 'Admin')
        @include('dashboard.partials.admin')
    @elseif(($userRole ?? '') === 'Vendedor')
        @include('dashboard.partials.vendedor')
    @elseif(($userRole ?? '') === 'Deposito')
        @include('dashboard.partials.deposito')
    @else
        @include('dashboard.partials.admin')
    @endif

</div>
@endsection
