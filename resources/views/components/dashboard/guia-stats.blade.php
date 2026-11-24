@php
    $usuarioGuia = Auth::user();
    $docenteGuia = $usuarioGuia?->docente;
    $aulaGuiaStats = $docenteGuia
        ? \App\Models\Aula::with('grado')->where('docente_guia_id', $docenteGuia->id)->first()
        : null;
    $totalAlumnosGuia = $aulaGuiaStats
        ? \App\Models\Matricula::where('aula_id', $aulaGuiaStats->id)->where('estado', 'activo')->count()
        : 0;
@endphp

<div class="space-y-6 mt-8">
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
        <h3 class="text-lg font-black text-[#3d2c1d] dark:text-white">Resumen de Tutoría</h3>
    </div>

    @if ($aulaGuiaStats)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-emerald-100 dark:border-emerald-900/60 p-6 flex items-center gap-4">
                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wide">Mi Aula Asignada</p>
                    <h4 class="text-2xl font-black text-[#3d2c1d] dark:text-white">{{ $aulaGuiaStats->nombre }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Turno: {{ $aulaGuiaStats->turno }} · Cupo: {{ $aulaGuiaStats->cupo }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-emerald-100 dark:border-emerald-900/60 p-6 flex items-center gap-4">
                <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wide">Mis Alumnos</p>
                    <h4 class="text-3xl font-black text-[#3d2c1d] dark:text-white">{{ $totalAlumnosGuia }}</h4>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-bold mt-1">Estudiantes activos a cargo</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 p-4 rounded-xl">
            <p class="text-amber-800 dark:text-amber-200 text-sm font-bold">Aún no tienes un aula asignada como Docente Guía para este ciclo escolar.</p>
        </div>
    @endif
</div>