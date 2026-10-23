@hasanyrole('Docente Guia')
<div x-show="rolActivo === 'Docente Guia'" x-transition.opacity style="display: none;" class="space-y-6">
    
    <!-- Encabezado de Contexto con Botón de Horario -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2 px-2">
        <div>
            <h3 class="text-xl font-black text-[#3d2c1d]">
                Tutoría Activa: <span class="text-[#e6ac27]">{{ $aulaGuia?->grado?->nombre ?? 'Sin Grado' }} Sección "{{ $aulaGuia?->nombre ?? 'N/A' }}"</span>
            </h3>
            <p class="text-sm text-slate-500 font-medium mt-1">Panel de control y seguimiento integral del grupo asignado.</p>
        </div>

        @if($aulaGuia)
            @php
                $rutaHorarioGuia = Route::has('academico.tutor.horario') 
                    ? route('academico.tutor.horario') 
                    : (Route::has('academico.docente-guia.horario') ? route('academico.docente-guia.horario') : '#');
            @endphp
            <a href="{{ $rutaHorarioGuia }}" 
               class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-[#3d2c1d] hover:bg-stone-800 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-sm transition-all transform hover:-translate-y-0.5 self-start sm:self-auto shrink-0">
                <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Ver Horario del Aula
            </a>
        @endif
    </div>

    <!-- 5 Accesos Rápidos Operativos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
        
        <!-- 1. Mis Alumnos -->
        <a href="{{ route('academico.tutor.mis-alumnos') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[130px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d]">Mis Alumnos</span>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Directorio y Promedios</span>
            </div>
        </a>

        <!-- 2. Boletines Oficiales -->
        <a href="{{ route('academico.boletines.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[130px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d]">Boletines Oficiales</span>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Consolidado por Parcial</span>
            </div>
        </a>

        <!-- 3. Apoyo Familiar -->
        <a href="{{ route('academico.apoyo_familiar.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[130px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d]">Apoyo Familiar</span>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Evaluación de Hogar</span>
            </div>
        </a>

        <!-- 4. Prematrícula y Pases -->
        <a href="{{ route('academico.prematricula.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[130px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d]">Prematrícula y Pases</span>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Proyección y Movilidad</span>
            </div>
        </a>

        <!-- 5. Centro de Reportes -->
        <a href="{{ route('academico.reportes.mi-aula') }}" class="group bg-[#FFFDF5] p-5 rounded-2xl border border-[#e6ac27]/30 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[130px]">
            <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-200 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d]">Centro de Reportes</span>
                <span class="block text-[10px] font-bold text-[#e6ac27] uppercase tracking-widest mt-1">Auditoría Global</span>
            </div>
        </a>
    </div>

    <!-- Sección Inferior: Rendimiento por Corte y Asistencia -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Rendimiento por Corte del Aula -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-black text-[#3d2c1d]">Rendimiento por Corte del Aula</h3>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $rendimientoAula['grado'] ?? '' }} "{{ $rendimientoAula['seccion'] ?? '' }}"</span>
            </div>

            @if($rendimientoAula)
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="bg-slate-50 rounded-2xl p-4 text-center">
                    <p class="text-2xl font-black text-[#3d2c1d]">{{ $rendimientoAula['mi_as'] + $rendimientoAula['mi_f'] }}</p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Matrícula</p>
                </div>
                <div class="bg-emerald-50 rounded-2xl p-4 text-center">
                    <p class="text-2xl font-black text-emerald-600">{{ $rendimientoAula['aprobados_todas'] }}</p>
                    <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mt-1">Aprobados en Todas</p>
                </div>
                <div class="bg-rose-50 rounded-2xl p-4 text-center">
                    <p class="text-2xl font-black text-rose-600">{{ $rendimientoAula['aplazados_1'] + $rendimientoAula['aplazados_2'] + $rendimientoAula['aplazados_3'] }}</p>
                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest mt-1">Aplazados</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-600 mb-1">
                        <span>Aplazados de 1 asignatura</span>
                        <span class="font-black">{{ $rendimientoAula['aplazados_1'] }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-slate-600 mb-1">
                        <span>Aplazados de 2 asignaturas</span>
                        <span class="font-black">{{ $rendimientoAula['aplazados_2'] }}</span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-slate-600 mb-1">
                        <span>Aplazados de 3 a más</span>
                        <span class="font-black">{{ $rendimientoAula['aplazados_3'] }}</span>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-bold text-slate-500">% Aprobados en todas</span>
                        <span class="text-lg font-black text-[#e6ac27]">{{ $rendimientoAula['porcentaje_aprobados'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full bg-[#e6ac27]" style="width: {{ $rendimientoAula['porcentaje_aprobados'] }}%"></div>
                    </div>
                </div>
            </div>
            @else
                <p class="text-sm text-slate-400">Aún no hay datos de rendimiento para esta aula.</p>
            @endif
        </div>

        <!-- Widget de Asistencia y Accesos -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col">
            <div>
                <h3 class="font-black text-[#3d2c1d] mb-4">Asistencia Semanal</h3>
                <div class="text-center mb-6">
                    <span class="text-6xl font-black text-[#e6ac27] drop-shadow-sm">{{ $asistenciaSemanal['porcentaje'] ?? 0 }}<span class="text-3xl text-slate-300">%</span></span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Promedio General de la Semana</p>
                </div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('academico.asistencia.aula.create') }}" class="w-full inline-flex items-center justify-center gap-2 bg-[#FFFDF5] hover:bg-slate-50 border border-[#e6ac27]/30 text-[#e6ac27] rounded-xl py-3 text-sm font-black shadow-sm transition-colors">
                    Pasar Asistencia del Aula
                </a>
                <a href="{{ route('academico.tutor.rendimiento') }}" class="w-full inline-flex items-center justify-center gap-2 bg-[#FFFDF5] hover:bg-slate-50 border border-[#e6ac27]/30 text-[#e6ac27] rounded-xl py-3 text-sm font-black shadow-sm transition-colors">
                    Ver Rendimiento por Corte
                </a>
            </div>
        </div>
    </div>

    <!-- MURAL DE SEGUIMIENTO DEL AULA (DATOS REALES) -->
    <div class="mt-8 bg-white p-0 sm:p-6 sm:rounded-3xl border-y sm:border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 sm:px-0 sm:py-0 mb-0 sm:mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b sm:border-0 border-slate-100">
            <div>
                <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Mural de Seguimiento del Aula
                </h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Basado en registros oficiales del sistema</p>
            </div>
            <div class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 bg-slate-50 self-start sm:self-auto shrink-0">
                Vista de Aula ({{ $aulaGuia->grado->nombre ?? '' }} "{{ $aulaGuia->nombre ?? '' }}")
            </div>
        </div>

        <div class="overflow-x-auto pb-4">
            <table class="min-w-full border-collapse min-w-[1000px]">
                <thead>
                    <tr class="border-y border-slate-200 bg-[#FFFDF5]">
                        <th class="py-4 px-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest min-w-[200px]">Indicador de Desempeño</th>
                        @php $meses = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN (C1)', 'JUL', 'AGO', 'SEP (C2)', 'OCT', 'NOV (C3)', 'DIC']; @endphp
                        @foreach($meses as $mes)
                            <th class="py-4 px-2 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest w-16 border-l border-slate-100">{{ $mes }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    
                    <!-- 1. Asistencia Estudiantil -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-4 text-sm font-bold text-[#3d2c1d]">1. Asistencia Estudiantil</td>
                        @for($i = 1; $i <= 12; $i++)
                            <td class="py-4 px-2 text-center text-xs border-l border-slate-100 {{ isset($muralAula[$i]['asistencia_est']) && $muralAula[$i]['asistencia_est'] >= 80 ? 'font-black text-emerald-600' : (isset($muralAula[$i]['asistencia_est']) ? 'font-black text-rose-600' : 'text-slate-300') }}">
                                {{ $muralAula[$i]['asistencia_est'] !== null ? $muralAula[$i]['asistencia_est'].'%' : '---' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 2. Asistencia Docente -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-4 text-sm font-bold text-[#3d2c1d]">2. Asistencia Docente</td>
                        @for($i = 1; $i <= 12; $i++)
                            <td class="py-4 px-2 text-center text-xs border-l border-slate-100 {{ isset($muralAula[$i]['asistencia_doc']) && $muralAula[$i]['asistencia_doc'] == 100 ? 'font-black text-emerald-600' : (isset($muralAula[$i]['asistencia_doc']) ? 'font-black text-amber-600' : 'text-slate-300') }}">
                                {{ $muralAula[$i]['asistencia_doc'] !== null ? $muralAula[$i]['asistencia_doc'].'%' : '---' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 3. Índices de Aprobados -->
                    <tr class="hover:bg-slate-50 transition-colors bg-slate-50/50">
                        <td class="py-4 px-4 text-sm font-bold text-[#3d2c1d]">3. Índices de Aprobados Limpios</td>
                        @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] as $mesNum)
                            <td class="py-4 px-2 text-center text-xs border-l border-slate-100">
                                @if(in_array($mesNum, [6, 9, 11, 12]))
                                    @php
                                        $mapaCortes = [6 => 1, 9 => 2, 11 => 3, 12 => 4];
                                        $numCorte = $mapaCortes[$mesNum];
                                    @endphp
                                    @if(isset($rendimientoCortes[$numCorte]))
                                        <span class="font-black {{ $rendimientoCortes[$numCorte]['indice'] >= 60 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $rendimientoCortes[$numCorte]['indice'] }}%</span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-400"><svg class="w-3 h-3 inline pb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> PND</span>
                                    @endif
                                @else
                                    <span class="text-slate-300">.</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    <!-- 4. Promedio de Calificaciones -->
                    <tr class="hover:bg-slate-50 transition-colors bg-slate-50/50">
                        <td class="py-4 px-4 text-sm font-bold text-[#3d2c1d]">4. Promedio de Calificaciones</td>
                        @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] as $mesNum)
                            <td class="py-4 px-2 text-center text-xs border-l border-slate-100">
                                @if(in_array($mesNum, [6, 9, 11, 12]))
                                    @php
                                        $mapaCortes = [6 => 1, 9 => 2, 11 => 3, 12 => 4];
                                        $numCorte = $mapaCortes[$mesNum];
                                    @endphp
                                    @if(isset($rendimientoCortes[$numCorte]))
                                        <span class="font-black text-[#3d2c1d]">{{ $rendimientoCortes[$numCorte]['promedio'] }}</span>
                                    @else
                                        <span class="text-[10px] font-bold text-slate-400"><svg class="w-3 h-3 inline pb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> PND</span>
                                    @endif
                                @else
                                    <span class="text-slate-300">.</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FORMATOS MINED EXCLUSIVOS DEL AULA -->
    <div class="mt-4 mb-6">
        <div class="flex justify-between items-end mb-4">
            <div>
                <h3 class="text-sm font-black text-[#3d2c1d] uppercase tracking-widest">Formatos MINED (Mi Aula)</h3>
                <p class="text-xs font-medium text-slate-500 mt-1">Listados oficiales filtrados exclusivamente para {{ $aulaGuia->grado->nombre ?? '' }} {{ $aulaGuia->nombre ?? '' }}</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Aprobados (Mi Aula) -->
            <a href="{{ url('/academico/reportes/mined-detallado?tipo=aprobados&grado_id=' . ($aulaGuia->grado_id ?? '')) }}" class="bg-emerald-50 p-5 rounded-2xl border border-emerald-100 hover:border-emerald-400 hover:shadow-sm transition-all flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-white text-emerald-600 flex items-center justify-center font-black group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    ✓
                </div>
                <div>
                    <h4 class="font-black text-emerald-900">Listado Aprobados</h4>
                    <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Formato Oficial</span>
                </div>
            </a>

            <!-- Aplazados 1 a 2 (Mi Aula) -->
            <a href="{{ url('/academico/reportes/mined-detallado?tipo=1_2&grado_id=' . ($aulaGuia->grado_id ?? '')) }}" class="bg-amber-50 p-5 rounded-2xl border border-amber-100 hover:border-amber-400 hover:shadow-sm transition-all flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-white text-amber-600 flex items-center justify-center font-black group-hover:bg-amber-600 group-hover:text-white transition-colors">
                    1-2
                </div>
                <div>
                    <h4 class="font-black text-amber-900">Aplazados (1 a 2)</h4>
                    <span class="text-[10px] font-bold text-amber-600 uppercase tracking-widest">Desglose de Materias</span>
                </div>
            </a>

            <!-- Aplazados 3 a más (Mi Aula) -->
            <a href="{{ url('/academico/reportes/mined-detallado?tipo=3_mas&grado_id=' . ($aulaGuia->grado_id ?? '')) }}" class="bg-rose-50 p-5 rounded-2xl border border-rose-100 hover:border-rose-400 hover:shadow-sm transition-all flex items-center gap-4 group">
                <div class="w-12 h-12 rounded-xl bg-white text-rose-600 flex items-center justify-center font-black group-hover:bg-rose-600 group-hover:text-white transition-colors">
                    3+
                </div>
                <div>
                    <h4 class="font-black text-rose-900">Aplazados (3 a más)</h4>
                    <span class="text-[10px] font-bold text-rose-600 uppercase tracking-widest">Riesgo Académico</span>
                </div>
            </a>
        </div>
    </div>

</div>
@endhasanyrole