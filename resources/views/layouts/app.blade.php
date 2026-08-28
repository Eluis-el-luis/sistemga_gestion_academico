<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SGA Institucional') }}</title>

        <!-- Fonts & Scripts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </head>
    
    @php
        // Validamos si el usuario tiene permiso para ver el menú lateral
        $mostrarSidebar = auth()->check() && auth()->user()->hasAnyRole(['Director', 'Subdirector', 'Gestor de Usuarios', 'Coordinador']);
    @endphp

    <body class="font-sans antialiased text-[#3d2c1d] bg-slate-50" 
          x-data="{ 
              sidebarOpen: localStorage.getItem('sidebarOpen') !== null ? localStorage.getItem('sidebarOpen') === 'true' : window.innerWidth >= 1024,
              showTopBtnGlobal: false 
          }" 
          x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val))"
          @resize.window="if(window.innerWidth < 1024) sidebarOpen = false" 
          @scroll.window="showTopBtnGlobal = (window.pageYOffset > 150)">
        
        <!-- SIDEBAR (Solo visible para administradores y coordinadores) -->
        @if($mostrarSidebar)
            @include('layouts.navigation')
        @endif

        <!-- CONTENEDOR DINÁMICO -->
        <div class="flex flex-col min-h-screen transition-all duration-300 {{ !$mostrarSidebar ? 'w-full' : '' }}" 
             @if($mostrarSidebar) :class="sidebarOpen ? 'lg:pl-64' : 'lg:pl-20'" @endif>
            
            <!-- TOPBAR GLOBAL (Logo, Menú Hamburguesa y Perfil) -->
            <nav class="sticky top-0 z-30 bg-white border-b border-slate-200/60 shadow-sm h-16 flex items-center justify-between px-4 sm:px-6 transition-all">
                
                <div class="flex items-center gap-4 lg:gap-6">
                    <!-- Botón Hamburguesa -->
                    @if($mostrarSidebar)
                        <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-[#e6ac27] bg-slate-50 hover:bg-amber-50 p-2 rounded-lg transition-colors focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                    @endif

                    <!-- LOGO INSTITUCIONAL (Siempre visible, libre y sin círculo) -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <x-application-logo class="block h-10 w-auto object-contain shrink-0 group-hover:scale-105 transition-transform drop-shadow-sm" />
                        <span class="font-black text-[#3d2c1d] text-xl tracking-tight group-hover:text-[#e6ac27] transition-colors hidden sm:block">
                            Colegio Cristiano En Nicaragua 
                        </span>
                    </a>
                </div>

                <!-- Menú de Usuario a la derecha -->
                <div class="flex items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @php
                                $nombreCorto = explode(' ', Auth::user()->nombre_completo ?? Auth::user()->name ?? 'Usuario')[0];
                                $inicial = substr($nombreCorto, 0, 1);
                            @endphp
                            <button class="inline-flex items-center p-1.5 pr-3 border border-[#e6ac27]/30 text-sm font-bold rounded-full text-amber-900 bg-amber-50 hover:bg-amber-100 transition shadow-sm gap-2">
                                <div class="w-7 h-7 rounded-full bg-[#e6ac27] text-white flex items-center justify-center text-xs">
                                    {{ $inicial }}
                                </div>
                                <span class="hidden sm:block">{{ $nombreCorto }}</span>
                                <svg class="fill-current h-4 w-4 text-[#e6ac27]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                                <p class="text-sm font-black text-slate-800">{{ Auth::user()->nombre_completo ?? Auth::user()->name }}</p>
                                <p class="text-xs text-slate-500 font-medium truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')" class="hover:bg-amber-50 hover:text-amber-900 font-bold flex items-center gap-2 mt-1">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ __('Mi Perfil') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 font-bold hover:bg-red-50 flex items-center gap-2 mt-1 border-t border-slate-100 pt-2">
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    {{ __('Cerrar Sesión') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </nav>

            <!-- ENCABEZADO DE PÁGINA -->
            @isset($header)
                <header class="bg-transparent pt-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
                    {{ $header }}
                </header>
            @endisset

            <!-- CONTENIDO PRINCIPAL -->
            <main class="flex-1 w-full">
                {{ $slot }}
            </main>

            <!-- Botón flotante -->
            <button x-show="showTopBtnGlobal" x-transition @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-8 right-8 z-50 p-3.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-full shadow-lg transition-all transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-[#e6ac27]" title="Volver arriba">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
            </button>
        </div>
    </body>
</html>