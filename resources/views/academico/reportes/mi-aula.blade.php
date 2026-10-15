<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Centro de Reportes: <span class="text-[#e6ac27]">Mi Aula Asignada</span></h2>
            <div class="flex gap-3">
                <a href="{{ route('dashboard') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm mt-2">← Volver</a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-md text-xs uppercase tracking-widest transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir Sábana
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-full mx-auto sm:px-6 lg:px-8 space-y-6 print:max-w-none print:p-0 print:space-y-4">
        
        <!-- PANEL DE FILTROS (Oculto al imprimir) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 print:hidden">
            <form method="GET" action="{{ route('academico.reportes.mi-aula') }}" class="flex flex-col sm:flex-row items-end gap-4">
                <div class="w-full sm:w-1/3">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Periodo a Evaluar</label>
                    <select name="corte_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer">
                        <option value="">Promedio General Acumulado</option>
                        @foreach($cortes as $corte)
                            <option value="{{ $corte->id }}" @selected($corteId == $corte->id)>
                                {{ $corte->descripcion ?? $corte->nombre ?? 'Corte Evaluativo ' . $corte->numero }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <button type="submit" class="w-full bg-[#3d2c1d] hover:bg-[#2b1f14] text-white font-black px-6 py-2.5 rounded-xl shadow-md text-sm transition-colors">
                        Generar Matriz
                    </button>
                </div>
            </form>
        </div>

        <!-- COMPONENTE DE MEMBRETE (Modo Landscape / Apaisado) -->
        <x-membrete-impresion 
            titulo="Sábana de Calificaciones y Rendimiento Global"
            subtitulo="{{ $aulaGuia->grado->nombre }} • Sección '{{ $aulaGuia->nombre }}'"
            badge="Ciclo {{ $anio->nombre ?? date('Y') }}"
            detalle="Periodo: <span class='text-[#3d2c1d]'>{{ $corteSeleccionado ? ($corteSeleccionado->descripcion ?? $corteSeleccionado->nombre ?? 'Corte ' . $corteSeleccionado->numero) : 'Promedio General' }}</span> <span class='mx-1 text-slate-300'>|</span> Tutor: <span class='text-[#3d2c1d]'>{{ Auth::user()->nombre_completo }}</span>"
            orientacion="landscape"
        />

        <!-- RESUMEN ESTADÍSTICO PARA IMPRESIÓN -->
        <div class="grid grid-cols-2 md:grid-cols-4 print:grid print:grid-cols-4 gap-4 print:gap-2 mb-6 print:mb-4 text-center">
            <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-4 print:p-2">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Estudiantes Activos</span>
                <span class="block text-2xl print:text-lg font-black text-[#3d2c1d]">{{ count($alumnosReporte) }}</span>
            </div>
            <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-4 print:p-2">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Asignaturas</span>
                <span class="block text-2xl print:text-lg font-black text-blue-600">{{ count($asignaciones) }}</span>
            </div>
            <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-4 print:p-2">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Alumnos en Riesgo</span>
                <span class="block text-2xl print:text-lg font-black text-rose-500">{{ $alumnosReporte->where('reprobadas', '>=', 3)->count() }}</span>
            </div>
            <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-4 print:p-2">
                <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Promedio General Aula</span>
                <span class="block text-2xl print:text-lg font-black text-[#e6ac27]">{{ $alumnosReporte->avg('promedio_general') > 0 ? round($alumnosReporte->avg('promedio_general'), 1) : 0 }}</span>
            </div>
        </div>

        <!-- TABLA MAESTRA (SÁBANA DE CALIFICACIONES) -->
        <div class="bg-white rounded-3xl print:rounded-2xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden page-break-inside-avoid">
            <div class="overflow-x-auto custom-scrollbar print:overflow-visible">
                <table class="w-full divide-y divide-slate-200 text-sm print:text-[9px] border-collapse min-w-max print:min-w-0">
                    <thead class="bg-[#FFFDF5] print:bg-[#3d2c1d] text-slate-500 print:text-white uppercase text-xs print:text-[8px] font-black tracking-wider">
                        <tr>
                            <th class="px-3 py-4 print:px-2 print:py-2 text-center w-8">#</th>
                            <th class="px-4 py-4 print:px-2 print:py-2 text-left w-20">CUP</th>
                            <th class="px-4 py-4 print:px-2 print:py-2 text-left print:text-[#e6ac27]">Estudiante</th>
                            
                            <!-- Bucle de Columnas de Asignaturas -->
                            @foreach($asignaciones as $asig)
                                <th class="px-2 py-4 print:px-1 print:py-2 text-center" title="{{ $asig->asignatura->nombre }}">
                                    <span class="block truncate w-16 print:w-auto mx-auto">{{ str($asig->asignatura->nombre)->limit(12, '.') }}</span>
                                </th>
                            @endforeach

                            <th class="px-4 py-4 print:px-2 print:py-2 text-center print:text-[#e6ac27] border-l border-slate-200 print:border-[#e6ac27]/30">Promedio</th>
                            <th class="px-4 py-4 print:px-2 print:py-2 text-center">Estado Integral</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 print:divide-slate-200">
                        @forelse($alumnosReporte as $index => $alumno)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-3 py-2.5 print:px-2 print:py-1.5 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5 print:px-2 print:py-1.5 font-mono text-[10px] print:text-[8px] text-slate-500">{{ $alumno['cup'] ?? 'S/C' }}</td>
                                <td class="px-4 py-2.5 print:px-2 print:py-1.5 font-bold text-[#3d2c1d] uppercase truncate max-w-[200px] print:max-w-none">{{ $alumno['nombre_completo'] }}</td>
                                
                                <!-- Bucle de Notas por Asignatura -->
                                @foreach($asignaciones as $asig)
                                    @php
                                        $nota = $alumno['notas'][$asig->id] ?? null;
                                    @endphp
                                    <td class="px-2 py-2.5 print:px-1 print:py-1.5 text-center font-black {{ $nota === null ? 'text-slate-300' : ($nota >= 60 ? 'text-emerald-600' : 'text-rose-600') }}">
                                        {{ $nota ?? '-' }}
                                    </td>
                                @endforeach

                                <!-- Promedio General del Estudiante -->
                                <td class="px-4 py-2.5 print:px-2 print:py-1.5 text-center font-black border-l border-slate-100 print:border-slate-300 text-lg print:text-[10px] {{ $alumno['promedio_general'] >= 60 ? 'text-[#3d2c1d]' : 'text-rose-600' }}">
                                    {{ $alumno['promedio_general'] ?? '-' }}
                                </td>

                                <!-- Alertas Tempranas / Estado Integral -->
                                <td class="px-4 py-2.5 print:px-2 print:py-1.5 text-center">
                                    @if($alumno['reprobadas'] >= 3)
                                        <span class="inline-block w-full px-2 py-1 rounded-md text-[10px] print:text-[8px] font-black uppercase tracking-widest bg-rose-100 print:bg-transparent print:border print:border-rose-500 text-rose-700">Riesgo Crítico</span>
                                    @elseif($alumno['reprobadas'] > 0)
                                        <span class="inline-block w-full px-2 py-1 rounded-md text-[10px] print:text-[8px] font-black uppercase tracking-widest bg-amber-100 print:bg-transparent print:border print:border-amber-500 text-amber-700">{{ $alumno['reprobadas'] }} Reprobadas</span>
                                    @else
                                        <span class="inline-block w-full px-2 py-1 rounded-md text-[10px] print:text-[8px] font-black uppercase tracking-widest bg-emerald-100 print:bg-transparent print:text-slate-500 text-emerald-700">Limpio</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($asignaciones) + 5 }}" class="px-6 py-12 text-center text-stone-500 font-bold">
                                    No hay estudiantes matriculados en esta aula en el ciclo activo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>