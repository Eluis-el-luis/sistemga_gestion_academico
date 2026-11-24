@php
    $usuarioActual = Auth::user();
    $docenteActual = $usuarioActual?->docente;
    $asignaciones = $docenteActual
        ? \App\Models\AulaAsignaturaDocente::with(['aula.grado', 'asignatura'])
            ->where('docente_id', $docenteActual->id)
            ->where('activo', true)
            ->get()
        : collect();
    $totalAulas = $asignaciones->pluck('aula_id')->unique()->count();
    $totalMaterias = $asignaciones->count();
@endphp

@if ($totalMaterias > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-stone-200 dark:border-slate-800 p-5 flex items-center gap-4 hover:border-[#e6ac27] transition-colors">
            <div class="w-12 h-12 rounded-xl bg-[#FFFDF5] dark:bg-slate-800 text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/20 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1H4z"></path>
                </svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-stone-500 dark:text-slate-400 uppercase tracking-widest">Aulas que atiendo</span>
                <h4 class="text-2xl font-black text-[#3d2c1d] dark:text-white">{{ $totalAulas }} <span class="text-sm font-bold text-stone-400 dark:text-slate-500">secciones</span></h4>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-stone-200 dark:border-slate-800 p-5 flex items-center gap-4 hover:border-[#e6ac27] transition-colors">
            <div class="w-12 h-12 rounded-xl bg-[#FFFDF5] dark:bg-slate-800 text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/20 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-stone-500 dark:text-slate-400 uppercase tracking-widest">Carga Horaria</span>
                <h4 class="text-2xl font-black text-[#3d2c1d] dark:text-white">{{ $totalMaterias }} <span class="text-sm font-bold text-stone-400 dark:text-slate-500">asignaciones</span></h4>
            </div>
        </div>
    </div>

    <div id="mis-aulas" class="bg-white dark:bg-slate-900 overflow-hidden shadow-sm rounded-2xl border border-stone-200 dark:border-slate-800">
        <div class="p-4 border-b border-stone-200 dark:border-slate-800 bg-[#FFFDF5] dark:bg-slate-800/70">
            <h4 class="font-black text-[#3d2c1d] dark:text-white text-sm uppercase tracking-widest">Mis Aulas Asignadas</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 dark:divide-slate-800 text-sm">
                <thead class="bg-white dark:bg-slate-900 text-stone-500 dark:text-slate-400 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-6 py-3 text-left">Grado y Aula</th>
                        <th class="px-6 py-3 text-left">Asignatura</th>
                        <th class="px-6 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-slate-800">
                    @foreach ($asignaciones as $asignacion)
                        <tr class="hover:bg-stone-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 font-bold text-[#3d2c1d] dark:text-slate-200">
                                {{ $asignacion->aula?->grado?->nombre ?? '' }} - {{ $asignacion->aula?->nombre ?? '' }}
                            </td>
                            <td class="px-6 py-4 text-[#e6ac27] font-black">
                                {{ $asignacion->asignatura?->nombre ?? '' }}
                            </td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="{{ route('academico.notas.create', $asignacion->id) }}" title="Subir Calificaciones" class="inline-flex items-center gap-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 rounded-lg shadow-sm text-xs transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Notas
                                </a>
                                <a href="{{ route('academico.asistencia.asignatura.create', $asignacion->id) }}" title="Reportar Fugas o Retrasos" class="inline-flex items-center gap-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-1.5 px-3 rounded-lg shadow-sm text-xs transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Asistencia
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="bg-stone-50 dark:bg-slate-900 border border-stone-200 dark:border-slate-800 p-6 rounded-2xl text-center mt-6">
        <p class="text-stone-500 dark:text-slate-400 font-bold">No tienes asignaturas asignadas en este momento.</p>
    </div>
@endif