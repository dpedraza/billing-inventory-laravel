<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'FacturaPro ERP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F6F9] font-sans text-slate-800 antialiased min-h-screen selection:bg-teal-500 selection:text-white"
      x-data="{ sidebarOpen: true, searchOpen: false }">

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside :class="sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-20'"
               class="fixed inset-y-0 left-0 z-40 bg-[#0B132B] text-slate-300 transition-all duration-300 flex flex-col border-r border-slate-800 lg:static">
            
            <!-- Sidebar Brand Header -->
            <div class="h-16 px-4 flex items-center justify-between border-b border-slate-800/80 bg-[#080E21]">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-teal-500 to-sky-600 flex items-center justify-center text-white font-mono font-bold text-lg shadow-sm tracking-tighter shrink-0">
                        Fx
                    </div>
                    <div x-show="sidebarOpen" class="flex flex-col transition-opacity duration-200">
                        <span class="font-semibold text-sm text-white tracking-tight leading-none">{{ config('app.name', 'FacturaPro') }}</span>
                        <span class="text-[10px] font-mono text-teal-400 mt-1 uppercase tracking-widest">PyME ERP</span>
                    </div>
                </a>
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-400 hover:text-white p-1 rounded-md transition-colors hidden lg:block" title="Alternar menú">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
                
                <!-- Main Operations -->
                <div>
                    <div x-show="sidebarOpen" class="px-3 mb-2 text-[10px] font-mono text-slate-400 uppercase tracking-widest font-semibold">Operaciones</div>
                    <div class="space-y-1">
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-slate-800/90 text-white border-l-2 border-teal-400 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span x-show="sidebarOpen" class="truncate">Dashboard</span>
                        </a>

                        @can('viewAny', App\Models\Venta::class)
                        <a href="{{ route('ventas.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-medium transition-all {{ request()->routeIs('ventas.*') ? 'bg-slate-800/90 text-white border-l-2 border-teal-400 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('ventas.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            <span x-show="sidebarOpen" class="truncate">Ventas</span>
                        </a>
                        @endcan

                        @can('viewAny', App\Models\Compra::class)
                        <a href="{{ route('compras.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-medium transition-all {{ request()->routeIs('compras.*') ? 'bg-slate-800/90 text-white border-l-2 border-teal-400 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('compras.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                            <span x-show="sidebarOpen" class="truncate">Compras</span>
                        </a>
                        @endcan
                    </div>
                </div>

                <!-- Catalog & Stock -->
                <div>
                    <div x-show="sidebarOpen" class="px-3 mb-2 text-[10px] font-mono text-slate-400 uppercase tracking-widest font-semibold">Inventario & Catálogo</div>
                    <div class="space-y-1">
                        @can('viewAny', App\Models\Producto::class)
                        <a href="{{ route('productos.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-medium transition-all {{ request()->routeIs('productos.*') ? 'bg-slate-800/90 text-white border-l-2 border-teal-400 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('productos.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span x-show="sidebarOpen" class="truncate">Productos</span>
                        </a>
                        <a href="{{ route('inventario.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-medium transition-all {{ request()->routeIs('inventario.*') ? 'bg-slate-800/90 text-white border-l-2 border-teal-400 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('inventario.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span x-show="sidebarOpen" class="truncate">Kardex Inventario</span>
                        </a>
                        @endcan
                    </div>
                </div>

                <!-- Stakeholders & Reports -->
                <div>
                    <div x-show="sidebarOpen" class="px-3 mb-2 text-[10px] font-mono text-slate-400 uppercase tracking-widest font-semibold">Gestión & Reportes</div>
                    <div class="space-y-1">
                        @can('viewAny', App\Models\Cliente::class)
                        <a href="{{ route('clientes.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-medium transition-all {{ request()->routeIs('clientes.*') ? 'bg-slate-800/90 text-white border-l-2 border-teal-400 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('clientes.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span x-show="sidebarOpen" class="truncate">Clientes</span>
                        </a>
                        @endcan

                        @can('viewAny', App\Models\Proveedor::class)
                        <a href="{{ route('proveedores.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-medium transition-all {{ request()->routeIs('proveedores.*') ? 'bg-slate-800/90 text-white border-l-2 border-teal-400 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('proveedores.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span x-show="sidebarOpen" class="truncate">Proveedores</span>
                        </a>
                        @endcan

                        <a href="{{ route('reportes.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-md text-xs font-medium transition-all {{ request()->routeIs('reportes.*') ? 'bg-slate-800/90 text-white border-l-2 border-teal-400 shadow-inner' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                            <svg class="w-4 h-4 {{ request()->routeIs('reportes.*') ? 'text-teal-400' : 'text-slate-400' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span x-show="sidebarOpen" class="truncate">Reportes</span>
                        </a>
                    </div>
                </div>

            </nav>

            <!-- User Footer Card -->
            <div class="p-3 border-t border-slate-800 bg-[#080E21]">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-700 text-teal-300 font-mono text-xs font-semibold flex items-center justify-center shrink-0 border border-slate-600">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div x-show="sidebarOpen" class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-white truncate">{{ auth()->user()->name ?? 'Usuario' }}</p>
                        <span class="inline-block text-[10px] font-mono text-teal-400 uppercase">{{ auth()->user()->roles->first()?->name ?? 'Operador' }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline" x-show="sidebarOpen">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-red-400 transition-colors p-1" title="Cerrar Sesión">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- MAIN WORKSPACE -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden">

            <!-- TOPBAR -->
            <header class="h-16 bg-white border-b border-slate-200 px-4 lg:px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-800 p-1.5 rounded-md hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <nav class="flex items-center text-xs text-slate-500 gap-2 font-medium">
                        <span>Sistema</span>
                        <span class="text-slate-300">/</span>
                        <span class="text-slate-900 font-semibold">@yield('title', 'Dashboard')</span>
                    </nav>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Role Pill Badge -->
                    @php
                        $roleName = auth()->user()->roles->first()?->name ?? 'Admin';
                    @endphp
                    <div class="px-2.5 py-1 rounded-full text-[11px] font-mono font-medium border
                         @if($roleName === 'Admin') bg-teal-50 text-teal-800 border-teal-200
                         @elseif($roleName === 'Vendedor') bg-sky-50 text-sky-800 border-sky-200
                         @else bg-amber-50 text-amber-800 border-amber-200 @endif">
                        <span class="w-1.5 h-1.5 rounded-full inline-block mr-1.5
                             @if($roleName === 'Admin') bg-teal-500
                             @elseif($roleName === 'Vendedor') bg-sky-500
                             @else bg-amber-500 @endif"></span>
                        <span>{{ strtoupper($roleName) }}</span>
                    </div>

                    @can('create', App\Models\Venta::class)
                    <a href="{{ route('ventas.create') }}" class="bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold px-3 py-1.5 rounded-md shadow-xs flex items-center gap-1.5 transition-all active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Nueva Venta</span>
                    </a>
                    @endcan
                </div>
            </header>

            <!-- MAIN CONTENT CANVAS -->
            <main class="flex-1 p-4 lg:p-6 space-y-6">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" class="px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex justify-between items-center shadow-xs">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 text-base">&times;</button>
                    </div>
                @endif
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" class="px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-xs flex justify-between items-center shadow-xs">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-red-600 hover:text-red-800 text-base">&times;</button>
                    </div>
                @endif

                @yield('content')
            </main>

        </div>

    </div>

    @stack('scripts')
</body>
</html>

