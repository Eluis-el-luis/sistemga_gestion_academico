<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Algo salió mal | Colegio Cristiano</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FFFDF5] min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden text-center p-10">
        
        <div class="w-24 h-24 bg-rose-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-rose-100">
            <svg class="w-12 h-12 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-black text-[#3d2c1d] mb-2">¡Ups! Interrupción temporal</h1>
        <p class="text-slate-500 font-medium mb-8">
            Nuestros sistemas encontraron un error inesperado al cargar esta pantalla. No te preocupes, el problema ya fue registrado para nuestro equipo técnico.
        </p>

        <div class="flex flex-col gap-3">
            <button onclick="window.history.back()" class="w-full py-3.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md transition-all">
                Volver a la página anterior
            </button>
            <a href="{{ url('/dashboard') }}" class="w-full py-3.5 bg-slate-50 text-slate-600 rounded-xl hover:bg-slate-100 border border-slate-200 font-black text-sm transition-all">
                Ir al Panel Principal
            </a>
        </div>
    </div>
</body>
</html>