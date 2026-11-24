<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Algo salió mal | Colegio Cristiano</title>
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
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = { darkMode: 'class' };
        </script>
    @endif
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-[#FFFDF8] dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 min-h-screen flex items-center justify-center p-6 transition-colors">
    <div class="max-w-md w-full bg-white dark:bg-neutral-900 rounded-3xl shadow-sm border border-stone-200 dark:border-neutral-800 overflow-hidden text-center p-8 sm:p-10">
        <div class="w-20 h-20 bg-rose-50 dark:bg-neutral-800 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-rose-200/70 dark:border-neutral-700">
            <svg class="w-10 h-10 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-[#3d2c1d] dark:text-white mb-2">Interrupción temporal</h1>
        <p class="text-sm text-neutral-600 dark:text-neutral-300 font-normal leading-relaxed mb-8">
            Ocurrió un inconveniente inesperado al cargar esta pantalla. El evento quedó registrado para revisión técnica.
        </p>

        <div class="flex flex-col gap-3">
            <button type="button" onclick="window.history.back()" class="w-full py-3.5 px-4 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl font-semibold text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#e6ac27] cursor-pointer">
                Volver a la página anterior
            </button>
            <a href="{{ url('/dashboard') }}" class="w-full py-3.5 px-4 bg-stone-100 dark:bg-neutral-800 hover:bg-stone-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-100 rounded-xl border border-stone-200 dark:border-neutral-700 font-semibold text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-[#e6ac27] cursor-pointer">
                Ir al Panel Principal
            </a>
        </div>
    </div>
</body>
</html>