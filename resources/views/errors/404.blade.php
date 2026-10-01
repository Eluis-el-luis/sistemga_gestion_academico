<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada | Colegio Cristiano</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#FFFDF5] min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden text-center p-10">
        
        <!-- Ícono de Lupa (404) -->
        <div class="w-24 h-24 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-6 border border-amber-100">
            <svg class="w-12 h-12 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 17h.01"></path>
            </svg>
        </div>

        <h1 class="text-3xl font-black text-[#3d2c1d] mb-2">Error 404</h1>
        <p class="text-slate-500 font-medium mb-8">
            Lo sentimos, no pudimos encontrar la página que buscas. Es posible que el enlace esté roto o que la dirección haya cambiado.
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