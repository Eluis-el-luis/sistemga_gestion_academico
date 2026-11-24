<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGA Institucional') }}</title>

    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
@php
    $usuarioLayout = Auth::user();
    $mostrarSidebar = Auth::check() &&$usuarioLayout && $usuarioLayout->hasAnyRole([ 'directora', 'subdirectora', 'gestor_it', 'coordinador', 'Director', 'Subdirector', 'Gestor de Usuarios', 'Coordinador', ]);$nombreCortoLayout = explode(' ', trim((string) ($usuarioLayout?->nombre_completo ?? $usuarioLayout?->name ?? 'Usuario')))[0] ?: 'Usuario';
    $inicialLayout = mb_substr($nombreCortoLayout, 0, 1, 'UTF-8');
    $avatarNav = ($usuarioLayout && !empty($usuarioLayout->avatar)) ? asset('storage/' . $usuarioLayout->avatar) : null;
@endphp
<body
    class="antialiased text-neutral-800 dark:text-neutral-100 bg-stone-50 dark:bg-neutral-950 transition-colors duration-200"
    x-data="{
        sidebarOpen: false,
        showTopBtnGlobal: false
    }"
    @resize.window="if (window.innerWidth < 1024) sidebarOpen = false"
    @scroll.window="showTopBtnGlobal = (window.pageYOffset > 150)"
>
    @if ($mostrarSidebar)
        @include('layouts.navigation')
    @endif

    <div
        class="contenedor-principal-layout flex flex-col min-h-screen bg-stone-50 dark:bg-neutral-950 transition-all duration-200 {{ !$mostrarSidebar ? 'w-full' : '' }}"
        @if ($mostrarSidebar) :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-20'" @endif
    >
        <nav class="sticky top-0 z-40 bg-white/95 dark:bg-neutral-900/95 backdrop-blur border-b border-stone-200/80 dark:border-neutral-800 h-16 flex items-center justify-between px-4 sm:px-6 transition-colors duration-200 print:hidden">
            <div class="flex items-center gap-3 sm:gap-5">
                @if ($mostrarSidebar)
                    <button
                        type="button"
                        @click="sidebarOpen = !sidebarOpen"
                        aria-label="Alternar menú lateral"
                        class="text-neutral-600 dark:text-neutral-300 hover:text-[#d49b1a] bg-stone-100 dark:bg-neutral-800 hover:bg-amber-50 dark:hover:bg-neutral-700 p-2 rounded-xl transition-colors focus:outline-none focus:ring-2 focus:ring-[#e6ac27] cursor-pointer"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                @endif

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <x-application-logo class="block h-9 w-auto object-contain shrink-0 group-hover:scale-105 transition-transform" />
                    <span class="font-bold text-neutral-900 dark:text-neutral-100 text-base sm:text-lg tracking-tight group-hover:text-[#d49b1a] transition-colors hidden sm:block">
                        Colegio Cristiano En Nicaragua
                    </span>
                </a>
            </div>

            <div class="flex items-center gap-3">
                <x-ui.theme-toggle />

                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center p-1.5 pr-3 border border-neutral-200 dark:border-neutral-700 text-sm font-medium rounded-full text-neutral-800 dark:text-neutral-100 bg-white dark:bg-neutral-800 hover:bg-stone-100 dark:hover:bg-neutral-700 transition gap-2 cursor-pointer">
                                @if ($avatarNav)
                                    <img src="{{ $avatarNav }}" class="w-7 h-7 rounded-full object-cover border border-[#e6ac27]/50" alt="Avatar">
                                @else
                                    <div class="w-7 h-7 rounded-full bg-[#e6ac27] text-neutral-950 flex items-center justify-center text-xs font-bold">
                                        {{ $inicialLayout }}
                                    </div>
                                @endif
                                <span class="hidden sm:block">{{ $nombreCortoLayout }}</span>
                                <svg class="fill-current h-4 w-4 text-neutral-500 dark:text-neutral-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-stone-100 dark:border-neutral-800 bg-stone-50 dark:bg-neutral-900">
                                <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">{{ $usuarioLayout?->nombre_completo ?? $usuarioLayout?->name }}</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ $usuarioLayout?->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')" class="hover:bg-stone-100 dark:hover:bg-neutral-800 font-medium flex items-center gap-2 mt-1">
                                {{ __('Mi Perfil') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-rose-600 dark:text-rose-400 font-medium hover:bg-rose-50 dark:hover:bg-rose-950/30 flex items-center gap-2 mt-1 border-t border-stone-100 dark:border-neutral-800 pt-2">
                                    {{ __('Cerrar Sesión') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>
        </nav>

        @isset($header)
            <header class="bg-transparent pt-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full print:hidden">
                {{ $header }}
            </header>
        @endisset

        <main class="flex-1 w-full bg-stone-50 dark:bg-neutral-950 transition-colors duration-200">
            @isset($slot)
                {{ $slot }}
            @else
                @yield('content')
            @endisset

            <div class="hidden print:flex justify-between items-center mt-4 px-2 text-[10px] text-neutral-500 font-medium uppercase tracking-wider">
                <span>Documento generado el {{ now()->timezone('America/Managua')->format('d/m/Y h:i A') }}</span>
                <span>Colegio Cristiano En Nicaragua · Sistema de Gestión Académica</span>
            </div>
        </main>

        <button
            type="button"
            x-show="showTopBtnGlobal"
            x-cloak
            x-transition
            @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
            aria-label="Volver arriba"
            title="Volver arriba"
            class="fixed bottom-8 right-8 z-50 p-3 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-full shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-[#e6ac27] print:hidden cursor-pointer"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
            </svg>
        </button>
    </div>

    <x-ui.alerts />
</body>
</html>