@php
    $hora = now()->timezone('America/Managua')->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $nombreLimpio = explode(' ', trim(Auth::user()->nombre_completo ?? Auth::user()->name))[0];
    $rolPrincipal = Auth::user()->roles->first()->name ?? 'Usuario';
@endphp

<div class="bg-white rounded-[2rem] p-8 md:p-10 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6 transition-all mb-8">
    <div class="absolute top-0 right-0 -mt-16 -mr-16 w-80 h-80 bg-gradient-to-br from-rose-950/15 via-rose-900/5 to-transparent rounded-full blur-3xl pointer-events-none"></div>
    <div class="relative z-10">
        <center>
            <h2 class="text-3xl font-black text-[#3d2c1d] tracking-tight mb-2">
                {{ $saludo }}, <span class="text-[#e6ac27]">{{ $nombreLimpio }}</span> 
            </h2>
        </center>
        <p class="text-slate-500 text-sm md:text-base font-medium max-w-xl leading-relaxed">
            {{ $slot }}
        </p>
    </div>
    <div class="relative z-10 shrink-0 mt-4 md:mt-0">
        <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 border border-slate-200 font-black text-xs uppercase tracking-widest shadow-sm">
            Módulo: {{ $rolPrincipal }}
        </span>
    </div>
</div>