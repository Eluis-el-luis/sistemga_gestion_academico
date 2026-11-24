<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-stone-50 dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 min-h-screen flex flex-col justify-between p-6 transition-colors">
    <header class="w-full max-w-5xl mx-auto flex items-center justify-between py-2">
        <div class="flex items-center gap-3">
            <x-application-logo class="h-9 w-auto object-contain" />
            <span class="font-bold text-sm sm:text-base text-neutral-900 dark:text-neutral-100">Colegio Cristiano En Nicaragua</span>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.theme-toggle />
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 text-sm font-semibold transition-colors cursor-pointer">
                        Panel Principal
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-neutral-900 dark:bg-neutral-800 hover:bg-neutral-800 dark:hover:bg-neutral-700 text-white text-sm font-semibold transition-colors cursor-pointer">
                        Iniciar Sesión
                    </a>
                @endauth
            @endif
        </div>
    </header>

    <main class="w-full max-w-3xl mx-auto my-auto py-12">
        <div class="bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 rounded-3xl p-8 sm:p-10 shadow-xs text-center space-y-4">
            <span class="inline-flex px-3 py-1 rounded-full bg-amber-50 dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] text-xs font-semibold uppercase tracking-wider">
                Sistema de Gestión Académica
            </span>
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100">
                Bienvenido al portal institucional
            </h1>
            <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 max-w-xl mx-auto">
                Accede con tu cuenta asignada para consultar y gestionar los módulos académicos correspondientes a tu rol.
            </p>
        </div>
    </main>

    <footer class="w-full max-w-5xl mx-auto text-center text-xs text-neutral-500 dark:text-neutral-400 py-3">
        Colegio Cristiano En Nicaragua · SGA
    </footer>
</body>
</html>