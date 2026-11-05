@hasanyrole('Docente Guia')
<div x-show="rolActivo === 'Docente Guia'" x-transition.opacity style="display: none;" class="space-y-6">
    
    <!-- Encabezado de Contexto -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
        <div>
            @if($aulaGuia)
                <h3 class="text-xl font-black text-[#3d2c1d]">
                    Tutoría Activa: <span class="text-[#e6ac27]">{{ $aulaGuia->grado->nombre ?? 'Sin Grado' }} Sección "{{ $aulaGuia->nombre ?? 'N/A' }}"</span>
                </h3>
                <p class="text-sm text-slate-500 font-medium mt-1">Panel de control y seguimiento integral del grupo asignado.</p>
            @else
                <h3 class="text-xl font-black text-[#3d2c1d]">Tutoría Activa: <span class="text-slate-400">Sin aula asignada</span></h3>
                <p class="text-sm text-slate-500 font-medium mt-1">No tienes un aula asignada como docente guía. Contacta al administrador para que te asigne un grupo.</p>
            @endif
        </div>

        @if($aulaGuia)
            @php
                $rutaHorarioGuia = Route::has('academico.tutor.horario') 
                    ? route('academico.tutor.horario') 
                    : (Route::has('academico.docente-guia.horario') ? route('academico.docente-guia.horario') : '#');
            @endphp
            <a href="{{ $rutaHorarioGuia }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#3d2c1d] hover:bg-stone-800 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all">
                <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Ver Horario
            </a>
        @endif
    </div>

    @if($aulaGuia)

    <!-- 5 Accesos Rápidos Operativos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
        
        <!-- 1. Mis Alumnos -->
        <a href="{{ route('academico.tutor.mis-alumnos') }}" class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d] leading-tight text-sm">Mis Alumnos</span>
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Directorio</span>
            </div>
        </a>

        <!-- 2. Detalles de Mi Aula -->
        <a href="{{ route('academico.tutor.detalles-mi-aula') }}" class="group bg-[#FFFDF5] p-4 sm:p-5 rounded-2xl border border-[#e6ac27]/30 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px]">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d] leading-tight text-sm">Mi Aula</span>
                <span class="block text-[9px] font-bold text-[#e6ac27] uppercase tracking-widest mt-1">Materias</span>
            </div>
        </a>

        <!-- 3. Boletines -->
        <a href="{{ route('academico.boletines.index') }}" class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d] leading-tight text-sm">Boletines</span>
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Consolidados</span>
            </div>
        </a>

        <!-- 4. Apoyo Familiar -->
        <a href="{{ route('academico.apoyo_familiar.index') }}" class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d] leading-tight text-sm">Apoyo Familiar</span>
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Hogar</span>
            </div>
        </a>

        <!-- 5. Prematrícula -->
        <a href="{{ route('academico.prematricula.index') }}" class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d] leading-tight text-sm">Pases/Matrícula</span>
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Movilidad</span>
            </div>
        </a>

        <!-- 6. Centro Reportes -->
        <a href="{{ route('academico.reportes.mi-aula') }}" class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[120px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d] leading-tight text-sm">Reportes</span>
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Auditoría</span>
            </div>
        </a>
    </div>

    <!-- Sección de Rendimiento y Asistencia -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Rendimiento por Corte -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-black text-[#3d2c1d]">Rendimiento por Corte</h3>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $rendimientoAula['grado'] ?? '' }} "{{ $rendimientoAula['seccion'] ?? '' }}"</span>
            </div>

            @if($rendimientoAula)
            <div class="grid grid-cols-3 gap-4 mb-5">
                <div class="bg-slate-50 rounded-2xl p-4 text-center border border-slate-100">
                    <p class="text-2xl font-black text-[#3d2c1d]">{{ ($rendimientoAula['mi_as'] ?? 0) + ($rendimientoAula['mi_f'] ?? 0) }}</p>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Matrícula</p>
                </div>
                <div class="bg-emerald-50 rounded-2xl p-4 text-center border border-emerald-100">
                    <p class="text-2xl font-black text-emerald-600">{{ $rendimientoAula['aprobados_todas'] ?? 0 }}</p>
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mt-1">Aprobados</p>
                </div>
                <div class="bg-rose-50 rounded-2xl p-4 text-center border border-rose-100">
                    <p class="text-2xl font-black text-rose-600">{{ ($rendimientoAula['aplazados_1'] ?? 0) + ($rendimientoAula['aplazados_2'] ?? 0) + ($rendimientoAula['aplazados_3'] ?? 0) }}</p>
                    <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest mt-1">Aplazados</p>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs font-bold text-slate-500">% Aprobados en todas</span>
                    <span class="text-lg font-black text-[#e6ac27]">{{ $rendimientoAula['porcentaje_aprobados'] ?? 0 }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="h-full rounded-full bg-[#e6ac27]" style="width: {{ $rendimientoAula['porcentaje_aprobados'] ?? 0 }}%"></div>
                </div>
            </div>
            @else
                <p class="text-sm font-medium text-slate-400 py-4">Aún no hay datos de rendimiento para esta aula.</p>
            @endif
        </div>

        <!-- Asistencia Semanal -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="text-center mb-6">
                <h3 class="font-black text-[#3d2c1d] mb-4 text-left">Asistencia Semanal</h3>
                <span class="text-6xl font-black text-[#e6ac27] drop-shadow-sm">{{ $asistenciaSemanal['porcentaje'] ?? 0 }}<span class="text-3xl text-slate-300">%</span></span>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Promedio General</p>
            </div>
            <div class="space-y-2 mt-auto">
                <a href="{{ route('academico.asistencia.aula.create') }}" class="w-full inline-flex items-center justify-center bg-[#FFFDF5] hover:bg-amber-50 border border-[#e6ac27]/30 text-[#e6ac27] rounded-xl py-3 text-sm font-black transition-colors">
                    Pasar Asistencia
                </a>
            </div>
        </div>
    </div>

    <!-- MURAL DE SEGUIMIENTO (CON EL PARCHE DE ERROR PHP 8) -->
    <div class="bg-white p-0 sm:p-6 sm:rounded-3xl border-y sm:border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 sm:px-0 sm:py-0 mb-0 sm:mb-4 flex justify-between items-center border-b sm:border-0 border-slate-100">
            <div>
                <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Mural Oficial
                </h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Registros del sistema</p>
            </div>
        </div>

        <div class="overflow-x-auto pb-2">
            <table class="min-w-full border-collapse min-w-[900px]">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="py-3 px-4 text-left text-[10px] font-black text-slate-500 uppercase tracking-widest rounded-l-xl">Indicador de Desempeño</th>
                        @foreach(['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN (C1)', 'JUL', 'AGO', 'SEP (C2)', 'OCT', 'NOV (C3)', 'DIC'] as $mes)
                            <th class="py-3 px-2 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest w-16">{{ $mes }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    
                    <!-- 1. Asistencia Estudiantil -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4 text-xs font-bold text-[#3d2c1d]">1. Asistencia Estudiantil</td>
                        @for($i = 1; $i <= 12; $i++)
                            <td class="py-4 px-2 text-center text-xs border-l border-slate-100 {{ isset($muralAula[$i]['asistencia_est']) && $muralAula[$i]['asistencia_est'] >= 80 ? 'font-black text-emerald-600' : (isset($muralAula[$i]['asistencia_est']) && $muralAula[$i]['asistencia_est'] !== null ? 'font-black text-rose-600' : 'text-slate-300') }}">
                                {{ $muralAula[$i]['asistencia_est'] !== null ? $muralAula[$i]['asistencia_est'].'%' : '---' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 2. Índices de Aprobados (SOLUCIONADO EL UNDEFINED KEY 1) -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-4 text-sm font-bold text-[#3d2c1d]">2. Asistencia Docente</td>
                        @for($i = 1; $i <= 12; $i++)
                            <td class="py-4 px-2 text-center text-xs border-l border-slate-100 {{ isset($muralAula[$i]['asistencia_doc']) && $muralAula[$i]['asistencia_doc'] == 100 ? 'font-black text-emerald-600' : (isset($muralAula[$i]['asistencia_doc']) && $muralAula[$i]['asistencia_doc'] !== null ? 'font-black text-amber-600' : 'text-slate-300') }}">
                                {{ $muralAula[$i]['asistencia_doc'] !== null ? $muralAula[$i]['asistencia_doc'].'%' : '---' }}
                            </td>
                        @endfor
                    </tr>

                    <!-- 3. Índices de Aprobados -->
                    <tr class="hover:bg-slate-50 transition-colors bg-slate-50/50">
                        <td class="py-4 px-4 text-sm font-bold text-[#3d2c1d]">3. Índices de Aprobados Limpios</td>
                        @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] as $mesNum)
                            <td class="py-3 px-2 text-center text-xs">
                                @if(in_array($mesNum, [6, 9, 11, 12]))
                                    @php
                                        $mapa = [6 => 1, 9 => 2, 11 => 3, 12 => 4];
                                        $corte = $mapa[$mesNum];
                                        // Prevención Null Coalescing para PHP 8
                                        $indice = $rendimientoCortes[$corte]['indice'] ?? null; 
                                    @endphp
                                    @if($indice !== null)
                                        <span class="font-black {{ $indice >= 60 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $indice }}%</span>
                                    @else
                                        <span class="text-[9px] font-bold text-slate-400">PND</span>
                                    @endif
                                @else
                                    <span class="text-slate-200">.</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    <!-- 3. Promedio de Calificaciones (SOLUCIONADO EL UNDEFINED KEY 1) -->
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4 text-xs font-bold text-[#3d2c1d]">3. Promedio Global</td>
                        @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] as $mesNum)
                            <td class="py-3 px-2 text-center text-xs">
                                @if(in_array($mesNum, [6, 9, 11, 12]))
                                    @php
                                        $mapa = [6 => 1, 9 => 2, 11 => 3, 12 => 4];
                                        $corte = $mapa[$mesNum];
                                        $promedio = $rendimientoCortes[$corte]['promedio'] ?? null;
                                    @endphp
                                    @if($promedio !== null)
                                        <span class="font-black text-[#3d2c1d]">{{ $promedio }}</span>
                                    @else
                                        <span class="text-[9px] font-bold text-slate-400">PND</span>
                                    @endif
                                @else
                                    <span class="text-slate-200">.</span>
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

@else
    <!-- Estado vacío cuando no hay aula asignada -->
    <div class="bg-white p-12 rounded-3xl border border-slate-200 shadow-sm text-center">
        <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <h3 class="text-xl font-black text-[#3d2c1d] mb-2">Sin Aula Asignada</h3>
        <p class="text-slate-500 mb-6 max-w-md mx-auto">No tienes ningún aula asignada como docente guía para el año escolar actual. Contacta al administrador o coordinador para que te asignen un grupo.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('academico.aulas.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#3d2c1d] hover:bg-stone-800 text-white rounded-2xl text-sm font-black shadow-sm transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Ver Aulas Disponibles
            </a>
        </div>
    </div>
@endif

</div>
@endhasanyrole