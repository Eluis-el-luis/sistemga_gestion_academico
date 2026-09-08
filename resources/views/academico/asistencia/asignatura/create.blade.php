<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}#mis-aulas" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                
                <div class="w-14 h-14 rounded-2xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/30 shadow-sm shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                        Asistencia: <span class="text-[#e6ac27]">{{ $asignacion->asignatura->nombre }}</span>
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 font-medium">
                        Aula: <span class="font-black text-[#3d2c1d]">{{ $asignacion->aula->grado->nombre }} - {{ $asignacion->aula->nombre }}</span>
                        <span class="ml-2 px-2 py-0.5 bg-slate-100 text-slate-600 rounded text-[10px] uppercase font-bold border border-slate-200">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- CÁLCULO DE ESTADÍSTICAS (KPIS) -->
            @php
                $totalAlumnos = $matriculas->count();
                $ausentesGuia = 0;
                $fugasClase = 0;

                foreach($matriculas as $m) {
                    // Contamos los que no vinieron al colegio
                    $estadoG = isset($asistenciaGuia[$m->id]) ? $asistenciaGuia[$m->id]->estado_asistencia : 'Presente';
                    if($estadoG === 'Ausencia Injustificada' || $estadoG === 'Ausencia Justificada') {
                        $ausentesGuia++;
                    }
                    
                    // Contamos los que se fugaron de esta clase en específico
                    $tieneFuga = isset($incidenciasPrevias[$m->id]) && $incidenciasPrevias[$m->id]->contains('estado_incidencia', 'Fuga');
                    if($tieneFuga) {
                        $fugasClase++;
                    }
                }

                $totalPresentes = $totalAlumnos - $ausentesGuia - $fugasClase;
                $porcentajeAsistencia = $totalAlumnos > 0 ? round(($totalPresentes / $totalAlumnos) * 100, 1) : 0;
            @endphp

            <!-- TARJETAS DE INDICADORES (KPIs) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Matrícula Total</p>
                        <p class="text-2xl font-black text-[#3d2c1d] leading-none mt-1">{{ $totalAlumnos }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 border border-emerald-100 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Presentes en tu Clase</p>
                        <p class="text-2xl font-black text-[#3d2c1d] leading-none mt-1">{{ $totalPresentes }}</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#FFFDF5] flex items-center justify-center text-[#e6ac27] border border-[#e6ac27]/20 shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">% Asistencia Efectiva</p>
                        <p class="text-2xl font-black {{ $porcentajeAsistencia < 80 ? 'text-rose-500' : 'text-[#e6ac27]' }} leading-none mt-1">{{ $porcentajeAsistencia }}%</p>
                    </div>
                </div>
            </div>

            <!-- TABLA DE ASISTENCIA -->
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200 mt-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-[#FFFDF5] text-[#3d2c1d] border-b border-[#e6ac27]/20">
                            <tr>
                                <th class="px-6 py-5 text-left text-xs font-black uppercase tracking-widest">Estudiante</th>
                                <th class="px-6 py-5 text-left text-xs font-black uppercase tracking-widest">Estado General (Docente Guía)</th>
                                <th class="px-6 py-5 text-left text-xs font-black uppercase tracking-widest">Registro de Incidencias</th>
                                <th class="px-6 py-5 text-center text-xs font-black uppercase tracking-widest">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($matriculas as $matricula)
                                @php
                                    $estadoGuia = isset($asistenciaGuia[$matricula->id]) ? $asistenciaGuia[$matricula->id]->estado_asistencia : 'Presente';
                                    $misIncidencias = isset($incidenciasPrevias[$matricula->id]) ? $incidenciasPrevias[$matricula->id] : collect();
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-black text-[#3d2c1d]">{{ $matricula->alumno->nombre_completo }}</div>
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $matricula->alumno->codigo_unico_persona }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        @if($estadoGuia === 'Presente')
                                            <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">✓ Presente</span>
                                        @else
                                            <span class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200">⚠ {{ $estadoGuia }}</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        @if($misIncidencias->isEmpty())
                                            <span class="text-slate-400 text-xs font-bold">Ninguna (Presente)</span>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($misIncidencias as $incidencia)
                                                    <div class="flex items-center gap-2 text-xs">
                                                        <span class="font-black text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 shadow-sm">[{{ $incidencia->bloqueHorario->nombre }}]</span> 
                                                        <span class="font-bold text-[#3d2c1d]">{{ $incidencia->estado_incidencia }}</span>
                                                        <form action="{{ route('academico.asistencia.asignatura.destroy', [$asignacion->id, $incidencia->id]) }}" method="POST" class="inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-slate-300 hover:text-rose-500 font-bold ml-1 hover:bg-rose-50 px-1.5 rounded transition-colors" title="Deshacer reporte">✕</button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        @if($estadoGuia === 'Presente' || str_contains($estadoGuia, 'Actividad'))
                                            <button x-data="" 
                                                    x-on:click.prevent="$dispatch('abrir-modal-incidencia', { id: '{{ $matricula->id }}', nombre: '{{ addslashes($matricula->alumno->nombre_completo) }}' })" 
                                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-[#3d2c1d] hover:border-[#e6ac27] rounded-xl text-[11px] font-black uppercase tracking-widest transition-all shadow-sm transform hover:-translate-y-0.5">
                                                <span>+</span> Registrar Incidencia
                                            </button>
                                        @else
                                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">Ausente Hoy</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-bold">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                            No hay estudiantes activos matriculados en esta clase.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Alpine.js para Registrar Incidencia -->
    <div x-data="{ matriculaId: '', nombreAlumno: '' }" 
         @abrir-modal-incidencia.window="matriculaId = $event.detail.id; nombreAlumno = $event.detail.nombre; $dispatch('open-modal', 'modal-registrar-incidencia')">
        
        <x-modal name="modal-registrar-incidencia" focusable maxWidth="md">
            <form method="post" action="{{ route('academico.asistencia.asignatura.store', $asignacion->id) }}">
                @csrf
                <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20 flex items-center gap-3">
                    <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <h2 class="text-lg font-black text-[#3d2c1d] leading-tight">
                        Reportar Incidencia <br><span x-text="nombreAlumno" class="text-sm font-bold text-slate-500 uppercase tracking-widest"></span>
                    </h2>
                </div>
                
                <div class="p-8 space-y-6 bg-white">
                    <input type="hidden" name="fecha" value="{{ $fecha }}">
                    <input type="hidden" name="matricula_id" x-bind:value="matriculaId">
                    
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Bloque Horario <span class="text-rose-500">*</span></label>
                        <select name="bloque_horario_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold text-[#3d2c1d] text-sm transition-colors" required>
                            <option value="">Seleccione su hora de clase...</option>
                            @foreach($bloques as $bloque)
                                <option value="{{ $bloque->id }}">{{ $bloque->nombre }} ({{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botones Rápidos (Radio Oculto) -->
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-3">Tipo de Incidencia <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="estado_incidencia" value="Fuga" class="peer sr-only" required>
                                <div class="p-3 text-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 font-bold text-xs peer-checked:bg-rose-500 peer-checked:text-white peer-checked:border-rose-600 hover:bg-slate-100 transition-all shadow-sm">
                                    Ausente
                                </div>
                            </label>
                            
                            <label class="cursor-pointer">
                                <input type="radio" name="estado_incidencia" value="Llegada Tardía" class="peer sr-only">
                                <div class="p-3 text-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 font-bold text-xs peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-600 hover:bg-slate-100 transition-all shadow-sm">
                                    Llegada Tardía
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="estado_incidencia" value="Permiso de Salida" class="peer sr-only">
                                <div class="p-3 text-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 font-bold text-xs peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-600 hover:bg-slate-100 transition-all shadow-sm">
                                    Permiso
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Observación <span class="text-slate-400 normal-case font-medium">(Opcional)</span></label>
                        <input type="text" name="observacion" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-medium text-slate-700 text-sm transition-colors" placeholder="Ej. El alumno se quedó en el patio..." maxlength="255">
                    </div>
                </div>

                <div class="bg-slate-50 px-8 py-5 flex justify-end gap-4 border-t border-slate-100 rounded-b-3xl">
                    <button type="button" x-on:click="$dispatch('close')" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors py-2">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5">
                        Guardar Incidencia
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>