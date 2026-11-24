<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}#mis-aulas" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al Panel">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>

                <div class="w-12 h-12 rounded-2xl bg-[#FFFDF8] dark:bg-neutral-800 text-[#d49b1a] dark:text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/30 dark:border-neutral-700 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                        Asistencia: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $asignacion->asignatura?->nombre }}</span>
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 font-medium">
                        Aula: <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $asignacion->aula?->grado?->nombre }} - {{ $asignacion->aula?->nombre }}</span>
                        <span class="ml-2 px-2 py-0.5 bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 rounded text-[10px] uppercase font-semibold border border-stone-200 dark:border-neutral-700">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @php
                $totalAlumnos = $matriculas->count();
                $ausentesGuia = 0;
                $fugasClase = 0;

                foreach ($matriculas as $m) {
                    $estadoG = isset($asistenciaGuia[$m->id]) ? $asistenciaGuia[$m->id]->estado_asistencia : 'Presente';
                    if ($estadoG === 'Ausencia Injustificada' || $estadoG === 'Ausencia Justificada') {
                        $ausentesGuia++;
                    }
                    $tieneFuga = isset($incidenciasPrevias[$m->id]) && $incidenciasPrevias[$m->id]->contains('estado_incidencia', 'Fuga');
                    if ($tieneFuga) {
                        $fugasClase++;
                    }
                }

                $totalPresentes = max(0, $totalAlumnos - $ausentesGuia - $fugasClase);
                $porcentajeAsistencia = $totalAlumnos > 0 ? round(($totalPresentes / $totalAlumnos) * 100, 1) : 0;
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white dark:bg-neutral-900 p-5 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs flex items-center gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-stone-100 dark:bg-neutral-800 flex items-center justify-center text-neutral-500 dark:text-neutral-300 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider">Matrícula Total</p>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white leading-none mt-1">{{ $totalAlumnos }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 p-5 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs flex items-center gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 dark:bg-neutral-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider">Presentes en tu Clase</p>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white leading-none mt-1">{{ $totalPresentes }}</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 p-5 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs flex items-center gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-neutral-800 flex items-center justify-center text-[#d49b1a] dark:text-[#e6ac27] shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider">% Asistencia Efectiva</p>
                        <p class="text-2xl font-bold {{ $porcentajeAsistencia < 80 ? 'text-rose-600 dark:text-rose-400' : 'text-[#c99116] dark:text-[#e6ac27]' }} leading-none mt-1">{{ $porcentajeAsistencia }}%</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-600 dark:text-neutral-300 border-b border-stone-200 dark:border-neutral-800">
                            <tr>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Estudiante</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Estado General (Docente Guía)</th>
                                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider">Registro de Incidencias</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($matriculas as $matricula)
                                @php
                                    $estadoGuia = isset($asistenciaGuia[$matricula->id]) ? $asistenciaGuia[$matricula->id]->estado_asistencia : 'Presente';
                                    $misIncidencias = isset($incidenciasPrevias[$matricula->id]) ? $incidenciasPrevias[$matricula->id] : collect();
                                @endphp
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-neutral-900 dark:text-white">{{ $matricula->alumno?->nombre_completo }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mt-0.5">{{ $matricula->alumno?->codigo_unico_persona }}</div>
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($estadoGuia === 'Presente')
                                            <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-900">Presente</span>
                                        @else
                                            <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200/70 dark:border-amber-900">{{ $estadoGuia }}</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4">
                                        @if ($misIncidencias->isEmpty())
                                            <span class="text-neutral-400 text-xs font-medium">Ninguna</span>
                                        @else
                                            <div class="space-y-1.5">
                                                @foreach ($misIncidencias as $incidencia)
                                                    <div class="flex items-center gap-2 text-xs">
                                                        <span class="font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/50 px-2 py-0.5 rounded border border-rose-200 dark:border-rose-900">[{{ $incidencia->bloqueHorario?->nombre ?? 'Bloque' }}]</span>
                                                        <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $incidencia->estado_incidencia }}</span>
                                                        <form action="{{ route('academico.asistencia.asignatura.destroy', [$asignacion->id, $incidencia->id]) }}" method="POST" class="inline form-eliminar">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-neutral-400 hover:text-rose-600 font-bold px-1.5 rounded transition-colors cursor-pointer" title="Deshacer reporte">x</button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        @if ($estadoGuia === 'Presente' || str_contains((string) $estadoGuia, 'Actividad'))
                                            <button
                                                type="button"
                                                @click="$dispatch('abrir-modal-incidencia', { id: '{{ $matricula->id }}', nombre: @js($matricula->alumno?->nombre_completo ?? '') })"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white dark:bg-neutral-800 border border-stone-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-200 hover:border-[#e6ac27] rounded-xl text-xs font-semibold transition-colors cursor-pointer"
                                            >
                                                + Registrar Incidencia
                                            </button>
                                        @else
                                            <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 bg-stone-100 dark:bg-neutral-800 px-3 py-1.5 rounded-lg">Ausente Hoy</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400 font-medium">
                                        No hay estudiantes activos matriculados en esta clase.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{ matriculaId: '', nombreAlumno: '' }" @abrir-modal-incidencia.window="matriculaId = $event.detail.id; nombreAlumno = $event.detail.nombre; $dispatch('open-modal', 'modal-registrar-incidencia')">
        <x-modal name="modal-registrar-incidencia" focusable maxWidth="md">
            <form method="post" action="{{ route('academico.asistencia.asignatura.store', $asignacion->id) }}">
                @csrf
                <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 px-6 py-4 border-b border-stone-200/80 dark:border-neutral-800">
                    <h2 class="text-base font-bold text-neutral-900 dark:text-white leading-snug">
                        Reportar Incidencia · <span x-text="nombreAlumno" class="text-[#c99116] dark:text-[#e6ac27]"></span>
                    </h2>
                </div>

                <div class="p-6 space-y-5 bg-white dark:bg-neutral-900 text-sm">
                    <input type="hidden" name="fecha" value="{{ $fecha }}">
                    <input type="hidden" name="matricula_id" x-bind:value="matriculaId">

                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Bloque Horario <span class="text-rose-500">*</span></label>
                        <select name="bloque_horario_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium text-sm cursor-pointer" required>
                            <option value="">Seleccione su hora de clase...</option>
                            @foreach ($bloques as $bloque)
                                <option value="{{ $bloque->id }}">{{ $bloque->nombre }} ({{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-2">Tipo de Incidencia <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="estado_incidencia" value="Fuga" class="peer sr-only" required>
                                <div class="p-3 text-center rounded-xl border border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-xs peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 transition-colors">
                                    Fuga / Ausente
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="estado_incidencia" value="Llegada Tardía" class="peer sr-only">
                                <div class="p-3 text-center rounded-xl border border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-xs peer-checked:bg-amber-500 peer-checked:text-neutral-950 peer-checked:border-amber-500 transition-colors">
                                    Llegada Tardía
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="estado_incidencia" value="Permiso de Salida" class="peer sr-only">
                                <div class="p-3 text-center rounded-xl border border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-xs peer-checked:bg-sky-600 peer-checked:text-white peer-checked:border-sky-600 transition-colors">
                                    Permiso
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Observación (Opcional)</label>
                        <input type="text" name="observacion" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm" placeholder="Detalle breve..." maxlength="255">
                    </div>
                </div>

                <div class="bg-stone-50 dark:bg-neutral-800/70 px-6 py-4 flex justify-end gap-3 border-t border-stone-100 dark:border-neutral-800">
                    <button type="button" @click="$dispatch('close')" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white py-2 cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="px-5 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl font-bold text-xs uppercase tracking-wider transition-colors cursor-pointer">
                        Guardar Incidencia
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>