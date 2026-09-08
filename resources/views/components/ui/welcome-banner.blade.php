@php
    $hora = now()->timezone('America/Managua')->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $nombreLimpio = explode(' ', trim(Auth::user()->nombre_completo ?? Auth::user()->name))[0];
    $rolPrincipal = Auth::user()->roles->first()->name ?? 'Usuario';
@endphp

<div class="flex justify-between items-center w-full min-h-[2rem] mb-8">
    <!-- Izquierda: Saludo Minimalista y Rol -->
    <div class="flex items-center gap-3">
        <h2 class="text-xl font-black text-[#3d2c1d] dark:text-white tracking-tight transition-colors">
            {{ $saludo }}, <span class="text-[#e6ac27]">{{ $nombreLimpio }}</span>
        </h2>
        <span class="hidden sm:inline-flex px-2.5 py-1 rounded-md bg-slate-200/50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-black text-[10px] uppercase tracking-widest border border-slate-300/50 dark:border-slate-700 transition-colors">
            {{ $rolPrincipal }}
        </span>
    </div>
    
    <!-- Derecha: Espacio dinámico para notificaciones (Slot) -->
    <div class="flex items-center gap-3">
        {{ $slot }}
    </div>
</div>