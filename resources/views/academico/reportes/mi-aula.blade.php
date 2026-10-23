<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Centro de Reportes
                </h2>
                <p class="text-sm font-bold text-[#e6ac27] mt-0.5 uppercase tracking-widest">
                    {{ $aulaGuia->grado->nombre ?? '' }} - Sección "{{ $aulaGuia->nombre ?? '' }}"
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-slate-600 font-bold text-sm transition-colors cursor-pointer">← Volver al Panel</a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-6 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir Documento
                </button>
            </div>
        </div>
    </x-slot>

    <!-- INICIALIZACIÓN DE ALPINE JS PARA FILTROS DINÁMICOS Y ANIMADOS -->
    <div class="pb-12 pt-6 max-w-full mx-auto sm:px-6 lg:px-8 space-y-6 print:max-w-none print:p-0 print:space-y-4" x-data="{ tipoReporte: '{{ $tipoReporte }}' }">
        
        <!-- PANEL DE FILTROS INTELIGENTE (Oculto al imprimir) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 print:hidden relative overflow-hidden">
            <!-- Detalle de diseño: Borde decorativo superior -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#e6ac27] to-[#3d2c1d]"></div>
            
            <form method="GET" action="{{ route('academico.reportes.mi-aula') }}" class="flex flex-col md:flex-row items-end gap-5">
                
                <!-- 1. Tipo de Reporte -->
                <div class="w-full md:w-1/4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Naturaleza del Reporte
                    </label>
                    <select name="tipo_reporte" x-model="tipoReporte" class="w-full border-slate-200 bg-slate-50 hover:bg-slate-100 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                        <option value="general">Sábana General (Todas las materias)</option>
                        <option value="riesgo">Alerta de Reprobados (Riesgo Académico)</option>
                        <option value="materia">Rendimiento por Materia Específica</option>
                    </select>
                </div>

                <!-- 2A. Condicional: Filtro de Riesgo (Transición suave) -->
                <div class="w-full md:w-1/4" x-show="tipoReporte === 'riesgo'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <label class="block text-[10px] font-black text-rose-400 uppercase tracking-widest mb-2">Filtrar por Severidad</label>
                    <select name="filtro_riesgo" class="w-full border-rose-200 bg-rose-50 hover:bg-rose-100 rounded-xl shadow-sm text-sm font-bold text-rose-900 focus:ring-rose-400 focus:border-rose-400 cursor-pointer transition-colors">
                        <option value="todos" @selected($filtroRiesgo == 'todos')>Todos los Reprobados</option>
                        <option value="1_2" @selected($filtroRiesgo == '1_2')>Reprobaron 1 o 2 materias</option>
                        <option value="3_mas" @selected($filtroRiesgo == '3_mas')>Riesgo Crítico (3 o más)</option>
                    </select>
                </div>

                <!-- 2B. Condicional: Filtro de Materia -->
                <div class="w-full md:w-1/4" x-show="tipoReporte === 'materia'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <label class="block text-[10px] font-black text-blue-400 uppercase tracking-widest mb-2">Seleccionar Materia</label>
                    <select name="materia_id" class="w-full border-blue-200 bg-blue-50 hover:bg-blue-100 rounded-xl shadow-sm text-sm font-bold text-blue-900 focus:ring-blue-400 focus:border-blue-400 cursor-pointer transition-colors">
                        @foreach($todasLasAsignaciones as $asig)
                            <option value="{{ $asig->id }}" @selected($materiaId == $asig->id)>{{ $asig->asignatura->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Periodo -->
                <div class="w-full md:w-1/4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Periodo a Evaluar</label>
                    <select name="corte_id" class="w-full border-slate-200 bg-slate-50 hover:bg-slate-100 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                        <option value="">Promedio General Acumulado</option>
                        @foreach($cortes as $corte)
                            <option value="{{ $corte->id }}" @selected($corteId == $corte->id)>
                                {{ $corte->descripcion ?? $corte->nombre ?? 'Corte Evaluativo ' . $corte->numero }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botón de Acción -->
                <div class="w-full md:w-auto mt-4 md:mt-0">
                    <button type="submit" class="w-full bg-[#3d2c1d] hover:bg-[#2b1f14] text-[#e6ac27] hover:text-white font-black px-8 py-2.5 rounded-xl shadow-sm hover:shadow-md text-sm transition-all cursor-pointer border border-[#3d2c1d]">
                        Analizar Datos
                    </button>
                </div>
            </form>
        </div>

        @php
            // Ajustamos el título del membrete institucional
            $tituloMembrete = 'Sábana de Calificaciones y Rendimiento Global';
            if ($tipoReporte === 'riesgo') {
                $tituloMembrete = 'Reporte de Alerta Temprana y Riesgo Académico';
            } elseif ($tipoReporte === 'materia') {
                $materiaNombre = $asignaciones->first()->asignatura->nombre ?? '';
                $tituloMembrete = 'Reporte de Rendimiento: ' . $materiaNombre;
            }
            
            // Orientación Inteligente
            $orientacion = ($tipoReporte === 'general') ? 'landscape' : 'portrait';
        @endphp

        <!-- MEMBRETE OFICIAL DE IMPRESIÓN -->
        <x-membrete-impresion 
            :titulo="$tituloMembrete"
            subtitulo="{{ $aulaGuia->grado->nombre }} • Sección '{{ $aulaGuia->nombre }}'"
            badge="Ciclo {{ $anio->nombre ?? date('Y') }}"
            detalle="Periodo Evaluado: <span class='text-[#3d2c1d]'>{{ $corteSeleccionado ? ($corteSeleccionado->descripcion ?? $corteSeleccionado->nombre ?? 'Corte ' . $corteSeleccionado->numero) : 'Promedio General' }}</span> <span class='mx-2 text-slate-300'>|</span> Tutor a Cargo: <span class='text-[#3d2c1d]'>{{ Auth::user()->nombre_completo }}</span>"
            :orientacion="$orientacion"
        />

        <!-- TARJETAS DE INDICADORES (KPIs) - Resilientes a Impresión en Grid Estricto -->
        <div class="grid grid-cols-2 md:grid-cols-4 print:grid print:grid-cols-4 gap-4 print:gap-3 mb-8 print:mb-6 text-center">
            <!-- 1. Matrícula -->
            <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-5 print:p-3 shadow-sm print:shadow-none flex flex-col justify-center">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Estudiantes Evaluados</span>
                <span class="block text-3xl print:text-xl font-black text-[#3d2c1d] leading-none">{{ count($alumnosReporte) }}</span>
            </div>
            
            <!-- 2 y 3. Dinámicas según el reporte -->
            @if($tipoReporte === 'general' || $tipoReporte === 'riesgo')
                <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-5 print:p-3 shadow-sm print:shadow-none flex flex-col justify-center">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 flex items-center justify-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Alumnos en Riesgo
                    </span>
                    <span class="block text-3xl print:text-xl font-black text-rose-600 leading-none">{{ $alumnosReporte->where('reprobadas_count', '>=', 3)->count() }}</span>
                </div>
                <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-5 print:p-3 shadow-sm print:shadow-none flex flex-col justify-center">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 flex items-center justify-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Historial Limpio
                    </span>
                    <span class="block text-3xl print:text-xl font-black text-emerald-600 leading-none">{{ $alumnosReporte->where('reprobadas_count', 0)->count() }}</span>
                </div>
            @elseif($tipoReporte === 'materia')
                <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-5 print:p-3 shadow-sm print:shadow-none flex flex-col justify-center">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Aprobados en Materia</span>
                    <span class="block text-3xl print:text-xl font-black text-emerald-600 leading-none">
                        {{ $alumnosReporte->filter(function($a) use ($asignaciones) { return isset($a['notas'][$asignaciones->first()->id]) && $a['notas'][$asignaciones->first()->id] >= 60; })->count() }}
                    </span>
                </div>
                <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-5 print:p-3 shadow-sm print:shadow-none flex flex-col justify-center">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Reprobados en Materia</span>
                    <span class="block text-3xl print:text-xl font-black text-rose-500 leading-none">
                        {{ $alumnosReporte->filter(function($a) use ($asignaciones) { return isset($a['notas'][$asignaciones->first()->id]) && $a['notas'][$asignaciones->first()->id] < 60; })->count() }}
                    </span>
                </div>
            @endif

            <!-- 4. Promedio General -->
            <div class="bg-[#FFFDF5] print:bg-slate-50 border border-[#e6ac27]/30 print:border-slate-300 rounded-2xl p-5 print:p-3 shadow-sm print:shadow-none flex flex-col justify-center relative overflow-hidden">
                <span class="block text-[10px] font-black text-[#e6ac27] print:text-slate-400 uppercase tracking-widest mb-1">Promedio Global Aula</span>
                <span class="block text-3xl print:text-xl font-black text-[#3d2c1d] leading-none">{{ $alumnosReporte->avg('promedio_general') > 0 ? round($alumnosReporte->avg('promedio_general'), 1) : 0 }}</span>
            </div>
        </div>

        <!-- =========================================================================================
             VISTA 1: SÁBANA GENERAL Y MATERIA ESPECÍFICA (Tabla Matriz de Alta Densidad)
             ========================================================================================= -->
        @if($tipoReporte === 'general' || $tipoReporte === 'materia')
            <div class="bg-white rounded-3xl print:rounded-none shadow-sm print:shadow-none border border-slate-200 print:border-none overflow-hidden page-break-inside-avoid">
                <div class="overflow-x-auto custom-scrollbar print:overflow-visible">
                    <!-- Ajustes anti-clipping: min-w-max para pantalla, table-fixed para PDF -->
                    <table class="w-full divide-y divide-slate-200 text-sm print:text-[8px] border-collapse min-w-max print:min-w-full print:table-fixed">
                        <thead class="bg-slate-50 print:bg-[#3d2c1d] text-slate-500 print:text-white uppercase text-xs print:text-[7px] font-black tracking-wider leading-tight border-b-2 border-slate-200 print:border-none">
                            <tr>
                                <th class="px-4 py-4 print:px-2 print:py-2.5 text-center w-10">#</th>
                                <th class="px-4 py-4 print:px-2 print:py-2.5 text-left w-24 print:w-16">ID / CUP</th>
                                <th class="px-4 py-4 print:px-2 print:py-2.5 text-left print:text-[#e6ac27] print:w-32">Estudiante</th>
                                
                                <!-- Bucle Dinámico de Asignaturas -->
                                @foreach($asignaciones as $asig)
                                    <th class="px-2 py-4 print:px-1 print:py-2.5 text-center break-words align-bottom group cursor-help" title="{{ $asig->asignatura->nombre }}">
                                        <div class="w-16 print:w-[35px] mx-auto overflow-hidden text-ellipsis whitespace-nowrap print:whitespace-normal group-hover:text-[#e6ac27] transition-colors">
                                            {{ $tipoReporte === 'general' ? str($asig->asignatura->nombre)->limit(10, '.') : $asig->asignatura->nombre }}
                                        </div>
                                    </th>
                                @endforeach

                                <th class="px-4 py-4 print:px-1 print:py-2.5 text-center print:text-[#e6ac27] border-l border-slate-200 print:border-[#e6ac27]/30 bg-[#FFFDF5] print:bg-transparent print:w-12">Prom.</th>
                                <th class="px-4 py-4 print:px-1 print:py-2.5 text-center print:w-16">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 print:divide-slate-300/50">
                            @forelse($alumnosReporte as $index => $alumno)
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="px-4 py-3 print:px-2 print:py-2 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 print:px-2 print:py-2 font-mono text-[10px] print:text-[7px] text-slate-400">{{ $alumno['cup'] ?? 'S/C' }}</td>
                                    <td class="px-4 py-3 print:px-2 print:py-2 font-black text-[#3d2c1d] uppercase truncate max-w-[200px] print:max-w-none print:whitespace-normal">{{ $alumno['nombre_completo'] }}</td>
                                    
                                    <!-- Impresión de Notas -->
                                    @foreach($asignaciones as $asig)
                                        @php $nota = $alumno['notas'][$asig->id] ?? null; @endphp
                                        <td class="px-2 py-3 print:px-1 print:py-2 text-center font-black text-base print:text-[8px] {{ $nota === null ? 'text-slate-200' : ($nota >= 60 ? 'text-[#3d2c1d]' : 'text-rose-600') }}">
                                            {{ $nota ?? '-' }}
                                        </td>
                                    @endforeach

                                    <!-- Promedio Final -->
                                    <td class="px-4 py-3 print:px-1 print:py-2 text-center font-black border-l border-slate-100 print:border-slate-300 text-lg print:text-[9px] bg-[#FFFDF5]/50 print:bg-transparent {{ $alumno['promedio_general'] >= 60 ? 'text-[#e6ac27]' : 'text-rose-600' }}">
                                        {{ $alumno['promedio_general'] ?? '-' }}
                                    </td>

                                    <!-- Etiqueta UX de Estado -->
                                    <td class="px-4 py-3 print:px-1 print:py-2 text-center">
                                        @if($alumno['reprobadas_count'] >= 3)
                                            <span class="inline-flex items-center justify-center w-full px-2 py-1 rounded-md text-[10px] print:text-[6px] font-black uppercase tracking-widest bg-rose-100 print:bg-transparent print:border print:border-rose-500 text-rose-700">Crítico</span>
                                        @elseif($alumno['reprobadas_count'] > 0)
                                            <span class="inline-flex items-center justify-center w-full px-2 py-1 rounded-md text-[10px] print:text-[6px] font-black uppercase tracking-widest bg-amber-100 print:bg-transparent print:border print:border-amber-500 text-amber-700">{{ $alumno['reprobadas_count'] }} Rep</span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-full px-2 py-1 rounded-md text-[10px] print:text-[6px] font-black uppercase tracking-widest bg-emerald-50 print:bg-transparent print:text-emerald-700 text-emerald-600">Limpio</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($asignaciones) + 5 }}" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                            <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                            <span class="font-bold">No se encontraron estudiantes para los parámetros seleccionados.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- =========================================================================================
             VISTA 2: RIESGO ACADÉMICO (Formato Bento Grid - Optimizado para PDF Portrait)
             ========================================================================================= -->
        @elseif($tipoReporte === 'riesgo')
            <div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 print:gap-4 print:grid-cols-2">
                    @forelse($alumnosReporte as $alumno)
                        <div class="bg-white print:bg-transparent border border-rose-200 shadow-sm print:shadow-none rounded-3xl print:rounded-2xl p-5 print:p-4 hover:border-rose-400 transition-colors flex flex-col h-full page-break-inside-avoid">
                            
                            <!-- Cabecera de la Tarjeta del Estudiante -->
                            <div class="flex justify-between items-start mb-4 border-b border-rose-100 print:border-rose-200 pb-4">
                                <div>
                                    <h4 class="font-black text-[#3d2c1d] uppercase text-sm print:text-xs leading-tight pr-2">{{ $alumno['nombre_completo'] }}</h4>
                                    <span class="font-mono text-[10px] font-bold text-slate-400 mt-1 block tracking-wider">{{ $alumno['cup'] ?? 'S/C' }}</span>
                                </div>
                                <div class="text-right shrink-0 bg-rose-50 print:bg-transparent px-3 py-2 print:p-0 rounded-xl">
                                    <span class="block text-[9px] font-black text-rose-400 uppercase tracking-widest">Promedio</span>
                                    <span class="block text-2xl print:text-xl font-black text-rose-600 leading-none">{{ $alumno['promedio_general'] ?? '-' }}</span>
                                </div>
                            </div>
                            
                            <!-- Lista de Clases Reprobadas -->
                            <div class="flex-grow">
                                <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    Asignaturas Reprobadas ({{ $alumno['reprobadas_count'] }})
                                </span>
                                <ul class="space-y-2">
                                    @foreach($alumno['detalle_reprobadas'] as $materia)
                                        <li class="flex justify-between items-center bg-rose-50/50 print:bg-transparent print:border-b print:border-slate-100 p-2.5 print:p-1 rounded-xl border border-rose-100/50">
                                            <span class="text-xs print:text-[10px] font-bold text-slate-700 leading-tight">{{ $materia['asignatura'] }}</span>
                                            <span class="text-xs print:text-[10px] font-black text-rose-600 bg-white print:bg-transparent px-2.5 py-1 print:p-0 rounded-lg shadow-sm print:shadow-none">{{ $materia['nota'] }} pts</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-16 text-center bg-emerald-50 rounded-3xl border border-emerald-100 shadow-sm print:shadow-none">
                            <div class="w-16 h-16 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black text-emerald-700">¡Excelentes noticias!</h3>
                            <p class="text-sm font-bold text-emerald-600/70 mt-1">No hay estudiantes en riesgo académico bajo estos parámetros.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</x-app-layout>