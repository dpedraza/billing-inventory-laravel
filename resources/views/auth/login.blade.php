@extends('layouts.guest')

@section('title', 'Iniciar Sesión')

@section('content')
    <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
        <div class="text-center mb-8">
            <div class="mx-auto w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Iniciar Sesión</h1>
            <p class="text-sm text-gray-500 mt-1">Ingresa tus credenciales para acceder al sistema</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200">
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm text-sm placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-400 @enderror"
                    placeholder="admin@demo.test">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="block w-full px-4 py-2.5 rounded-lg border border-gray-300 shadow-sm text-sm placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-400 @enderror"
                    placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 shadow-sm">
                    <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                </label>
            </div>

            <button type="submit"
                class="w-full flex justify-center py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-sm font-semibold text-white shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Ingresar
            </button>
        </form>
    </div>
@endsection
