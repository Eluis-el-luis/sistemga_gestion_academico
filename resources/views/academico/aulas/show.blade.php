{{-- resources/views/academico/aulas/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.aulas.index') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer" title="Volver a Gestión de Aulas">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                        Detalles del Aula: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $aula->grado?->nombre }} - {{ $aula->nombre }}</span>
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ $aula->anioEscolar?->nombre }} · {{ $aula->modalidad?->nombre }} · Turno {{ ucfirst((string) $aula->turno) }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{ activeTab: 'alumnos' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 shadow-xs rounded-3xl p-6 grid grid-cols-1 sm:grid-cols-4 gap-6 text-sm">
                <div>
                    <p class="text-xs font-semibold text-neutral-400 uppercase">Periodo</p>
                    <p class="font-bold text-neutral-900 dark:text-white mt-1">{{ $aula->anioEscolar?->nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-400 uppercase">Cupo</p>
                    <p class="font-bold text-neutral-900 dark:text-white mt-1">{{ $aula->cupo }} alumnos máx.</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-400 uppercase">Docente Guía</p>
                    <p class="font-semibold text-neutral-800 dark:text-neutral-200 mt-1">{{ $aula->docenteGuia?->usuario?->nombre_completo ?? 'No asignado' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-400 uppercase">Horas Académicas</p>
                    <p class="font-bold text-neutral-900 dark:text-white mt-1">{{ $horasTotales ?? 0 }} h / semana</p>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 shadow-xs rounded-3xl overflow-hidden">
                <div class="border-b border-stone-200 dark:border-neutral-800 flex overflow-x-auto px-4">
                    @foreach (['alumnos' => 'Alumnos Matriculados', 'horario' => 'Horario', 'asignaturas' => 'Asignaturas y Docentes', 'evaluaciones' => 'Evaluaciones'] as $tabKey => $tabLabel)
                        <button type="button" @click="activeTab = '{{ $tabKey }}'" :class="activeTab === '{{ $tabKey }}' ? 'border-[#e6ac27] text-neutral-900 dark:text-white font-bold border-b-2' : 'border-transparent text-neutral-500 dark:text-neutral-400 font-medium border-b-2'" class="px-5 py-3.5 text-xs uppercase tracking-wider whitespace-nowrap cursor-pointer">
                            {{ $tabLabel }}
                        </button>
                    @endforeach
                </div>

                <div x-show="activeTab === 'alumnos'" class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-5 py-3">Estudiante</th>
                                <th class="px-5 py-3">CUP</th>
                                <th class="px-5 py-3 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($matriculas as $matricula)
                                <tr>
                                    <td class="px-5 py-3.5 font-semibold">{{ $matricula->alumno?->nombre_completo }}</td>
                                    <td class="px-5 py-3.5 font-mono text-xs text-neutral-500">{{ $matricula->alumno?->codigo_unico_persona ?? '—' }}</td>
                                    <td class="px-5 py-3.5 text-center"><span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300">Activo</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-8 text-center text-neutral-500">Sin alumnos matriculados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div x-show="activeTab === 'horario'" x-cloak class="p-6 overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-[#FFFDF8] dark:bg-neutral-800/70">
                                <th class="border border-stone-200 dark:border-neutral-800 px-3 py-2.5 text-left text-xs font-semibold">Hora</th>
                                @foreach ($dias as $dia)
                                    <th class="border border-stone-200 dark:border-neutral-800 px-3 py-2.5 text-center text-xs font-semibold">{{ $dia }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-neutral-800 dark:text-neutral-200">
                            @foreach ($matriz as $fila)
                                @php $bloque = $fila['bloque']; @endphp
                                <tr>
                                    <td class="border border-stone-200 dark:border-neutral-800 px-3 py-2.5 text-xs font-semibold whitespace-nowrap">{{ $bloque->nombre }}</td>
                                    @if ($bloque->es_recreo)
                                        <td colspan="{{ count($dias) }}" class="border border-stone-200 dark:border-neutral-800 px-3 py-2.5 text-center text-xs text-neutral-400">{{ $bloque->nombre }}</td>
                                    @else
                                        @foreach ($dias as $dia)
                                            @php $h = $fila['dias'][$dia] ?? null; @endphp
                                            <td class="border border-stone-200 dark:border-neutral-800 px-3 py-2.5 text-center text-xs">
                                                {{ $h ? ($h->aulaAsignaturaDocente?->asignatura?->nombre ?? '—') : '—' }}
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div x-show="activeTab === 'asignaturas'" x-cloak class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-5 py-3">Asignatura</th>
                                <th class="px-5 py-3 text-center">Horas/Sem.</th>
                                <th class="px-5 py-3">Docente</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @foreach ($asignaciones as $asignacion)
                                <tr>
                                    <td class="px-5 py-3.5 font-semibold">{{ $asignacion->asignatura?->nombre }}</td>
                                    <td class="px-5 py-3.5 text-center">{{ number_format((float) $asignacion->horas_semanales, 0) }}h</td>
                                    <td class="px-5 py-3.5">{{ $asignacion->docente?->usuario?->nombre_completo ?? 'Sin asignar' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div x-show="activeTab === 'evaluaciones'" x-cloak class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @forelse ($evaluacionesPorCorte ?? [] as $evaluacion)
                            <div class="bg-[#FFFDF8] dark:bg-neutral-800/60 rounded-2xl p-5 border border-stone-200 dark:border-neutral-700">
                                <h4 class="font-bold text-neutral-900 dark:text-white text-base">{{ $evaluacion['corte']->nombre ?? 'Corte ' . $evaluacion['corte']->numero }}</h4>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">Promedio: <strong>{{ $evaluacion['promedio'] ?? 0 }}</strong> · Reprobados: <strong>{{ $evaluacion['reprobados'] }}</strong></p>
                            </div>
                        @empty
                            <p class="text-sm text-neutral-500">Sin evaluaciones registradas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>