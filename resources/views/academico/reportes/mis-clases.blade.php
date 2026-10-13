<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Centro de Reportes: <span class="text-[#e6ac27]">Mis Clases</span></h2>
            <a href="{{ route('dashboard') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 print:max-w-none print:p-0 print:space-y-4">
        
        <!-- FILTROS (Ocultos en impresión) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 print:hidden">
            <form method="GET" action="{{ route('academico.reportes.mis-clases') }}" class="flex flex-col sm:flex-row items-end gap-4">
                <div class="w-full sm:w-1/2">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">1. Selecciona la Clase</label>
                    <select name="asignacion_id" required class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium focus:ring-[#e6ac27] focus:border-[#e6ac27]">
                        <option value="">-- Elige una asignatura --</option>
                        @foreach($asignaciones as $asig)
                            <option value="{{ $asig->id }}" @selected($asignacionId == $asig->id)>
                                {{ $asig->aula->grado->nombre }} "{{ $asig->aula->nombre }}" - {{ $asig->asignatura->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-1/3">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">2. Periodo (Opcional)</label>
                    <select name="corte_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium focus:ring-[#e6ac27] focus:border-[#e6ac27]">
                        <option value="">Todos (Promedio General)</option>
                        @foreach($cortes as $corte)
                            <!-- CORREGIDO: Muestra descripcion, si no existe muestra nombre, y si no "Corte X" -->
                            <option value="{{ $corte->id }}" @selected($corteId == $corte->id)>
                                {{ $corte->descripcion ?? $corte->nombre ?? 'Corte Evaluativo ' . $corte->numero }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <button type="submit" class="w-full bg-[#3d2c1d] hover:bg-[#2b1f14] text-white font-black px-6 py-2.5 rounded-xl shadow-md text-sm transition-colors">
                        Generar
                    </button>
                </div>
                
                @if($asignacionId)
                    <div class="w-full sm:w-auto">
                        <button type="button" onclick="window.print()" class="w-full bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-6 py-2.5 rounded-xl shadow-md text-sm transition-colors inline-flex justify-center items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Imprimir PDF
                        </button>
                    </div>
                @endif
            </form>
        </div>

        <!-- RESULTADOS EN TABLA -->
        @if($asignacionId && $asignacionSeleccionada)
            
            <x-membrete-impresion 
                titulo="Listado Oficial de Calificaciones"
                subtitulo="{{ $asignacionSeleccionada->asignatura->nombre }} • {{ $asignacionSeleccionada->aula->grado->nombre }} '{{ $asignacionSeleccionada->aula->nombre }}'"
                badge="Ciclo {{ $anio->nombre ?? date('Y') }}"
                detalle="Periodo: <span class='text-[#3d2c1d]'>{{ $corteSeleccionado ? ($corteSeleccionado->descripcion ?? $corteSeleccionado->nombre ?? 'Corte ' . $corteSeleccionado->numero) : 'Promedio General' }}</span> <span class='mx-1 text-slate-300'>|</span> Docente: <span class='text-[#3d2c1d]'>{{ explode(' ', Auth::user()->nombre_completo)[0] }} {{ explode(' ', Auth::user()->nombre_completo)[1] ?? '' }}</span>"
                orientacion="portrait"
            />

            <!-- Resumen Estadístico para Impresión (Forzado a Cuadrícula) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 print:grid print:grid-cols-4 gap-4 print:gap-2 mb-6 print:mb-4 text-center">
                
                <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-4 print:p-2">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Matriculados</span>
                    <span class="block text-2xl print:text-lg font-black text-[#3d2c1d]">{{ count($alumnosReporte) }}</span>
                </div>
                
                <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-4 print:p-2">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Aprobados</span>
                    <span class="block text-2xl print:text-lg font-black text-emerald-600">
                        {{ $estadisticas['aprobados'] }} 
                        @if(($estadisticas['aprobados'] + $estadisticas['reprobados']) > 0)
                            <span class="text-sm print:text-[10px] font-bold text-emerald-400 ml-1">({{ $estadisticas['porcentaje_aprobados'] }}%)</span>
                        @endif
                    </span>
                </div>
                
                <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-4 print:p-2">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Reprobados</span>
                    <span class="block text-2xl print:text-lg font-black text-rose-500">
                        {{ $estadisticas['reprobados'] }}
                        @if(($estadisticas['aprobados'] + $estadisticas['reprobados']) > 0)
                            <span class="text-sm print:text-[10px] font-bold text-rose-400 ml-1">({{ $estadisticas['porcentaje_reprobados'] }}%)</span>
                        @endif
                    </span>
                </div>
                
                <div class="bg-white print:bg-slate-50 border border-slate-200 print:border-slate-300 rounded-2xl p-4 print:p-2">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Promedio Aula</span>
                    <span class="block text-2xl print:text-lg font-black text-blue-600">{{ $estadisticas['promedio_general'] }}</span>
                </div>

            </div>

            <!-- Tabla Oficial -->
            <div class="bg-white rounded-3xl print:rounded-2xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
                <table class="min-w-full divide-y divide-slate-200 text-sm print:text-[11px] border-collapse">
                    <thead class="bg-[#FFFDF5] print:bg-[#3d2c1d] text-slate-500 print:text-white uppercase text-xs print:text-[11px] font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4 print:px-3 print:py-2 text-center w-12">#</th>
                            <th class="px-6 py-4 print:px-3 print:py-2 text-left">Código CUP</th>
                            <th class="px-6 py-4 print:px-3 print:py-2 text-left print:text-[#e6ac27]">Apellidos y Nombres</th>
                            <th class="px-6 py-4 print:px-3 print:py-2 text-center">Calificación</th>
                            <th class="px-6 py-4 print:px-3 print:py-2 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 print:divide-slate-200">
                        @forelse($alumnosReporte as $index => $alumno)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 print:px-3 print:py-1.5 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-6 py-3 print:px-3 print:py-1.5 font-mono text-xs text-slate-500">{{ $alumno['cup'] ?? 'S/C' }}</td>
                                <td class="px-6 py-3 print:px-3 print:py-1.5 font-bold text-[#3d2c1d] uppercase">{{ $alumno['nombre_completo'] }}</td>
                                <td class="px-6 py-3 print:px-3 print:py-1.5 text-center font-black {{ $alumno['nota'] >= 60 ? 'text-emerald-600' : ($alumno['nota'] !== null ? 'text-rose-600' : 'text-slate-300') }}">
                                    {{ $alumno['nota'] ?? '-' }}
                                </td>
                                <td class="px-6 py-3 print:px-3 print:py-1.5 text-center">
                                    @if($alumno['nota'] !== null)
                                        @if($alumno['nota'] >= 60)
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-100 print:bg-transparent print:border print:border-emerald-500 text-emerald-700">Aprobado</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-rose-100 print:bg-transparent print:border print:border-rose-500 text-rose-700">Reprobado</span>
                                        @endif
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-slate-100 print:bg-transparent print:border print:border-slate-300 text-slate-500">Sin nota</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-stone-500 font-bold">No hay estudiantes activos en esta clase.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <!-- Estado vacío antes de seleccionar -->
            <div class="bg-white p-16 text-center rounded-3xl border border-slate-200 shadow-sm flex flex-col items-center justify-center print:hidden">
                <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center text-[#e6ac27] mb-5 border border-amber-100">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-[#3d2c1d]">Generador de Reportes</h3>
                <p class="text-base font-medium text-slate-500 mt-2 max-w-md">Selecciona una asignatura y un parcial en los filtros superiores para generar el listado oficial y visualizar el estado de tus alumnos.</p>
            </div>
        @endif
    </div>
</x-app-layout>