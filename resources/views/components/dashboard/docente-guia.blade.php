<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
        <div>
            @if ($aulaGuia)
                <h3 class="text-xl font-black text-[#3d2c1d] dark:text-white">
                    Tutoría Activa: <span class="text-[#e6ac27]">{{ $aulaGuia->grado->nombre ?? 'Sin Grado' }} Sección "{{ $aulaGuia->nombre ?? 'N/A' }}"</span>
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">Panel de control y seguimiento integral del grupo asignado.</p>
            @else
                <h3 class="text-xl font-black text-[#3d2c1d] dark:text-white">Tutoría Activa: <span class="text-slate-400">Sin aula asignada</span></h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">No tienes un aula asignada como docente guía. Contacta a Dirección o Coordinación.</p>
            @endif
        </div>

        @if ($aulaGuia)
            <a href="{{ route('academico.tutor.horario') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#3d2c1d] hover:bg-stone-800 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-colors self-start sm:self-auto shrink-0 cursor-pointer">
                <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Ver Horario
            </a>
        @endif
    </div>

    @if ($aulaGuia)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            <a href="{{ route('academico.tutor.mis-alumnos') }}" class="group bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px] cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="mt-4">
                    <span class="block font-black text-[#3d2c1d] dark:text-white leading-tight text-sm">Mis Alumnos</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Directorio</span>
                </div>
            </a>

            <a href="{{ route('academico.tutor.detalles-mi-aula') }}" class="group bg-[#FFFDF5] dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-[#e6ac27]/30 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px] cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-950/50 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <div class="mt-4">
                    <span class="block font-black text-[#3d2c1d] dark:text-white leading-tight text-sm">Mi Aula</span>
                    <span class="block text-[9px] font-bold text-[#e6ac27] uppercase tracking-widest mt-1">Materias</span>
                </div>
            </a>

            <a href="{{ route('academico.boletines.index') }}" class="group bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px] cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="mt-4">
                    <span class="block font-black text-[#3d2c1d] dark:text-white leading-tight text-sm">Boletines</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Consolidados</span>
                </div>
            </a>

            <a href="{{ route('academico.apoyo_familiar.index') }}" class="group bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px] cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <div class="mt-4">
                    <span class="block font-black text-[#3d2c1d] dark:text-white leading-tight text-sm">Apoyo Familiar</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Hogar</span>
                </div>
            </a>

            <a href="{{ route('academico.prematricula.index') }}" class="group bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px] cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="mt-4">
                    <span class="block font-black text-[#3d2c1d] dark:text-white leading-tight text-sm">Prematrícula</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Movilidad</span>
                </div>
            </a>

            <a href="{{ route('academico.reportes.mi-aula') }}" class="group bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px] cursor-pointer">
                <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="mt-4">
                    <span class="block font-black text-[#3d2c1d] dark:text-white leading-tight text-sm">Reportes</span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Auditoría</span>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-black text-[#3d2c1d] dark:text-white">Rendimiento por Corte</h3>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $rendimientoAula['grado'] ?? '' }} "{{ $rendimientoAula['seccion'] ?? '' }}"</span>
                </div>

                @if ($rendimientoAula)
                    <div class="grid grid-cols-3 gap-4 mb-5">
                        <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl p-4 text-center border border-slate-100 dark:border-slate-700">
                            <p class="text-2xl font-black text-[#3d2c1d] dark:text-white">{{ ($rendimientoAula['mi_as'] ?? 0) + ($rendimientoAula['mi_f'] ?? 0) }}</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Matrícula</p>
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-950/50 rounded-2xl p-4 text-center border border-emerald-100 dark:border-emerald-900">
                            <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $rendimientoAula['aprobados_todas'] ?? 0 }}</p>
                            <p class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest mt-1">Aprobados</p>
                        </div>
                        <div class="bg-rose-50 dark:bg-rose-950/50 rounded-2xl p-4 text-center border border-rose-100 dark:border-rose-900">
                            <p class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ ($rendimientoAula['aplazados_1'] ?? 0) + ($rendimientoAula['aplazados_2'] ?? 0) + ($rendimientoAula['aplazados_3'] ?? 0) }}</p>
                            <p class="text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-widest mt-1">Aplazados</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">% Aprobados en todas</span>
                            <span class="text-lg font-black text-[#e6ac27]">{{ $rendimientoAula['porcentaje_aprobados'] ?? 0 }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                            <div class="h-full rounded-full bg-[#e6ac27]" style="width: {{ min(100, max(0, (float) ($rendimientoAula['porcentaje_aprobados'] ?? 0))) }}%"></div>
                        </div>
                    </div>
                @else
                    <p class="text-sm font-medium text-slate-400 py-4">Aún no hay datos de rendimiento para esta aula.</p>
                @endif
            </div>

            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                <div class="text-center mb-6">
                    <h3 class="font-black text-[#3d2c1d] dark:text-white mb-4 text-left">Asistencia Semanal</h3>
                    <span class="text-6xl font-black text-[#e6ac27]">{{ $asistenciaSemanal['porcentaje'] ?? 0 }}<span class="text-3xl text-slate-300 dark:text-slate-600">%</span></span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Promedio General</p>
                </div>
                <div class="space-y-2 mt-auto">
                    <a href="{{ route('academico.asistencia.aula.create') }}" class="w-full inline-flex items-center justify-center bg-[#FFFDF5] dark:bg-slate-800 hover:bg-amber-50 dark:hover:bg-slate-700 border border-[#e6ac27]/30 text-[#e6ac27] rounded-xl py-3 text-sm font-black transition-colors cursor-pointer">
                        Pasar Asistencia
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <a href="{{ url('/academico/reportes/mined-detallado?tipo=aprobados&grado_id=' . ($aulaGuia->grado_id ?? '')) }}" class="bg-emerald-50 dark:bg-emerald-950/40 p-5 rounded-2xl border border-emerald-100 dark:border-emerald-900 hover:border-emerald-400 hover:shadow-sm transition-all flex items-center gap-4 group cursor-pointer">
                <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black shrink-0">OK</div>
                <div>
                    <h4 class="font-black text-emerald-900 dark:text-emerald-200">Listado Aprobados</h4>
                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Formato Oficial</span>
                </div>
            </a>

            <a href="{{ url('/academico/reportes/mined-detallado?tipo=1_2&grado_id=' . ($aulaGuia->grado_id ?? '')) }}" class="bg-amber-50 dark:bg-amber-950/40 p-5 rounded-2xl border border-amber-100 dark:border-amber-900 hover:border-amber-400 hover:shadow-sm transition-all flex items-center gap-4 group cursor-pointer">
                <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 flex items-center justify-center font-black shrink-0">1-2</div>
                <div>
                    <h4 class="font-black text-amber-900 dark:text-amber-200">Aplazados (1 a 2)</h4>
                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest">Desglose de Materias</span>
                </div>
            </a>

            <a href="{{ url('/academico/reportes/mined-detallado?tipo=3_mas&grado_id=' . ($aulaGuia->grado_id ?? '')) }}" class="bg-rose-50 dark:bg-rose-950/40 p-5 rounded-2xl border border-rose-100 dark:border-rose-900 hover:border-rose-400 hover:shadow-sm transition-all flex items-center gap-4 group cursor-pointer">
                <div class="w-12 h-12 rounded-xl bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 flex items-center justify-center font-black shrink-0">3+</div>
                <div>
                    <h4 class="font-black text-rose-900 dark:text-rose-200">Aplazados (3 a más)</h4>
                    <span class="text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase tracking-widest">Riesgo Académico</span>
                </div>
            </a>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 p-12 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm text-center">
            <h3 class="text-xl font-black text-[#3d2c1d] dark:text-white mb-2">Sin Aula Asignada</h3>
            <p class="text-slate-500 dark:text-slate-400 mb-6 max-w-md mx-auto">No tienes ningún aula asignada como docente guía para el año escolar actual.</p>
            <a href="{{ route('academico.aulas.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#3d2c1d] hover:bg-stone-800 text-white rounded-2xl text-sm font-black shadow-sm transition-colors cursor-pointer">
                Ver Aulas Disponibles
            </a>
        </div>
    @endif
</div>