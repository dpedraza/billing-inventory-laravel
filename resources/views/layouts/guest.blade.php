<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Iniciar Sesión') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 font-sans antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" class="mb-4 px-4 py-3 rounded-lg bg-green-100 border border-green-200 text-green-800 text-sm flex justify-between items-center">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-600 hover:text-green-800">&times;</button>
            </div>
        @endif
        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" class="mb-4 px-4 py-3 rounded-lg bg-red-100 border border-red-200 text-red-800 text-sm flex justify-between items-center">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="text-red-600 hover:text-red-800">&times;</button>
            </div>
        @endif
        @yield('content')
    </div>
    @stack('scripts')
</body>
</html>
