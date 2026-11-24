@php
    $usuarioBanner = Auth::user();
    $hora = now()->timezone('America/Managua')->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $nombreCompleto = trim((string) ($usuarioBanner?->nombre_completo ?? $usuarioBanner?->name ?? 'Usuario'));
    $nombreLimpio = explode(' ', $nombreCompleto)[0] ?: 'Usuario';
    $rolPrincipal = $usuarioBanner?->roles->pluck('name')->first() ?? 'Usuario';
@endphp

<div class="bg-white dark:bg-slate-900 rounded-[2rem] p-6 md:p-8 shadow-sm border border-slate-100 dark:border-slate-800 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6 transition-colors mb-8">
    <div class="absolute top-0 right-0 -mt-16 -mr-16 w-80 h-80 bg-gradient-to-br from-[#e6ac27]/15 via-amber-500/5 to-transparent rounded-full blur-3xl pointer-events-none"></div>
    <div class="relative z-10">
        <h2 class="text-2xl md:text-3xl font-black text-[#3d2c1d] dark:text-white tracking-tight mb-2">
            {{ $saludo }}, <span class="text-[#e6ac27]">{{ $nombreLimpio }}</span>
        </h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm md:text-base font-medium max-w-xl leading-relaxed">
            {{ $slot }}
        </p>
    </div>
    <div class="relative z-10 shrink-0">
        <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-200 border border-slate-200 dark:border-slate-700 font-black text-xs uppercase tracking-widest shadow-sm">
            Módulo: {{ $rolPrincipal }}
        </span>
    </div>
</div>