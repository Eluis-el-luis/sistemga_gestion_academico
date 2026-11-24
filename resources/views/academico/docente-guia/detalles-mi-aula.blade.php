{{-- resources/views/academico/docente-guia/detalles-mi-aula.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard', ['tab' => 'Docente Guia']) }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al Panel del Docente Guía">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                        Detalles de Mi Aula: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $aula->grado?->nombre ?? '' }} - "{{ $aula->nombre ?? '' }}"</span>
                    </h2>
                    <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ $aula->anioEscolar?->nombre ?? '' }} · {{ $aula->modalidad?->nombre ?? '' }} · Turno {{ ucfirst((string) ($aula->turno ?? '')) }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Tarjeta de Resumen del Aula -->
            <div class="bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 shadow-xs rounded-3xl overflow-hidden flex flex-col md:flex-row">
                <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 p-6 md:w-1/4 flex flex-col justify-center border-b md:border-b-0 md:border-r border-stone-200/80 dark:border-neutral-800">
                    <span class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider mb-1">Ciclo Escolar</span>
                    <h3 class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $aula->anioEscolar?->nombre ?? 'N/A' }}</h3>
                    <span class="mt-3 inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold uppercase tracking-wider bg-stone-100 dark:bg-neutral-900 text-neutral-700 dark:text-neutral-200 border border-stone-200 dark:border-neutral-700 self-start">
                        {{ $aula->modalidad?->nombre ?? 'N/A' }}
                    </span>
                </div>

                <div class="p-6 sm:p-8 grid grid-cols-1 sm:grid-cols-3 gap-6 flex-grow text-sm">
                    <div>
                        <p class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider mb-1">Sección y Turno</p>
                        <p class="font-bold text-neutral-900 dark:text-white text-base">
                            {{ $aula->nombre ?? '' }} <span class="text-neutral-400 font-normal mx-1">|</span> {{ ucfirst((string) ($aula->turno ?? '')) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider mb-1">Alumnos Matriculados</p>
                        <p class="font-bold text-neutral-900 dark:text-white text-base">
                            {{ $totalAlumnos }} <span class="text-neutral-500 dark:text-neutral-400 font-medium text-xs">{{ $totalAlumnos == 1 ? 'alumno activo' : 'alumnos activos' }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider mb-1">Docente Guía</p>
                        <div class="flex items-center gap-2.5 mt-1">
                            <div class="w-8 h-8 rounded-full bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 border border-stone-200 dark:border-neutral-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ mb_strtoupper(mb_substr((string) ($aula->docenteGuia?->usuario?->nombre_completo ?? 'D'), 0, 2, 'UTF-8'), 'UTF-8') }}
                            </div>
                            <p class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $aula->docenteGuia?->usuario?->nombre_completo ?? 'No asignado' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alumnos Matriculados -->
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="p-6 border-b border-stone-100 dark:border-neutral-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <h3 class="font-bold text-lg text-neutral-900 dark:text-white">Alumnos Matriculados</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Listado de estudiantes con matrícula activa en tu aula.</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/70 dark:border-emerald-900 text-xs font-semibold">
                        {{ $totalAlumnos }} {{ $totalAlumnos == 1 ? 'alumno' : 'alumnos' }}
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold tracking-wider border-b border-stone-200 dark:border-neutral-800">
                            <tr>
                                <th class="px-6 py-3.5">Nombre del Estudiante</th>
                                <th class="px-6 py-3.5">Código (CUP)</th>
                                <th class="px-6 py-3.5 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($matriculas as $matricula)
                                @php $alumno = $matricula->alumno; @endphp
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <td class="px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-amber-50 dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30 dark:border-neutral-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ mb_strtoupper(mb_substr((string) ($alumno?->nombre_completo ?? 'A'), 0, 1, 'UTF-8'), 'UTF-8') }}
                                            </div>
                                            <span class="font-semibold text-neutral-900 dark:text-white">{{ $alumno?->nombre_completo ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3.5 font-mono text-xs text-neutral-500 dark:text-neutral-400">{{ $alumno?->codigo_unico_persona ?? '—' }}</td>
                                    <td class="px-6 py-3.5 text-center">
                                        <span class="px-3 py-1 text-xs font-semibold uppercase rounded-lg border bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200/70 dark:border-emerald-900">
                                            Activo
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                        No hay estudiantes con matrícula activa en esta aula.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Horario del Aula -->
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="p-6 border-b border-stone-100 dark:border-neutral-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-neutral-900 dark:text-white">Horario del Aula</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Bloques horarios asignados a tu salón esta semana.</p>
                    </div>
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold text-xs uppercase tracking-wider rounded-xl transition-colors print:hidden cursor-pointer">
                        Imprimir / PDF
                    </button>
                </div>

                <style>
                    @media print {
                        @page { size: letter landscape; margin: 10mm; }
                        .print\:hidden { display: none !important; }
                    }
                </style>

                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full border-collapse text-xs">
                        <thead>
                            <tr class="bg-[#FFFDF8] dark:bg-neutral-800/70">
                                <th class="border border-stone-200 dark:border-neutral-800 px-3.5 py-2.5 text-left font-semibold uppercase tracking-wider text-neutral-600 dark:text-neutral-300 w-44">Hora</th>
                                @foreach ($dias as $dia)
                                    <th class="border border-stone-200 dark:border-neutral-800 px-3 py-2.5 text-center font-semibold uppercase tracking-wider text-neutral-900 dark:text-white">{{ $dia }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-neutral-800 dark:text-neutral-200">
                            @forelse ($matriz as $fila)
                                @php $bloque = $fila['bloque']; @endphp
                                <tr class="{{ $bloque->es_recreo ? 'bg-stone-50/80 dark:bg-neutral-800/40' : '' }}">
                                    <td class="border border-stone-200 dark:border-neutral-800 px-3.5 py-2.5 whitespace-nowrap">
                                        <span class="block font-semibold text-neutral-900 dark:text-white">{{ $bloque->nombre }}</span>
                                        <span class="block text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                        </span>
                                    </td>

                                    @if ($bloque->es_recreo)
                                        <td colspan="{{ count($dias) }}" class="border border-stone-200 dark:border-neutral-800 px-4 py-2.5 text-center">
                                            <span class="text-[11px] font-semibold uppercase tracking-widest text-neutral-400">{{ $bloque->nombre }}</span>
                                        </td>
                                    @else
                                        @foreach ($dias as $dia)
                                            @php $horario = $fila['dias'][$dia] ?? null; @endphp
                                            <td class="border border-stone-200 dark:border-neutral-800 px-2.5 py-2.5 text-center align-middle">
                                                @if ($horario)
                                                    <span class="block font-semibold text-neutral-900 dark:text-white leading-snug">{{ $horario->aulaAsignaturaDocente?->asignatura?->nombre ?? '—' }}</span>
                                                    <span class="block text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                                        Prof. {{ $horario->aulaAsignaturaDocente?->docente?->usuario ? \Illuminate\Support\Str::words($horario->aulaAsignaturaDocente->docente->usuario->nombre_completo, 2, '') : '—' }}
                                                    </span>
                                                @else
                                                    <span class="text-neutral-400">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($dias) + 1 }}" class="border border-stone-200 dark:border-neutral-800 px-4 py-10 text-center text-neutral-500">
                                        No hay bloques de horario definidos para esta aula.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Asignaturas y Docentes Asignados -->
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="p-6 border-b border-stone-100 dark:border-neutral-800">
                    <h3 class="font-bold text-lg text-neutral-900 dark:text-white">Asignaturas y Docentes Asignados</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Carga académica de tu aula por asignatura y profesor titular.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold tracking-wider border-b border-stone-200 dark:border-neutral-800">
                            <tr>
                                <th class="px-6 py-3.5">Asignatura</th>
                                <th class="px-6 py-3.5 text-center">Horas / Sem.</th>
                                <th class="px-6 py-3.5">Docente Asignado</th>
                                <th class="px-6 py-3.5 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($asignaciones as $asignacion)
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors {{ !$asignacion->activo ? 'opacity-60' : '' }}">
                                    <td class="px-6 py-3.5">
                                        <span class="font-semibold text-neutral-900 dark:text-white block">{{ $asignacion->asignatura?->nombre }}</span>
                                        @if ($asignacion->asignatura?->es_extracurricular)
                                            <span class="mt-1 inline-flex px-2 py-0.5 rounded-md text-[10px] font-semibold uppercase tracking-wider bg-amber-50 dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30">Extracurricular</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-center font-bold text-neutral-600 dark:text-neutral-300">{{ number_format((float) $asignacion->horas_semanales, 0) }}h</td>
                                    <td class="px-6 py-3.5">
                                        @if ($asignacion->docente_id && $asignacion->docente?->usuario)
                                            <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $asignacion->docente->usuario->nombre_completo }}</span>
                                        @else
                                            <span class="inline-flex items-center text-rose-600 dark:text-rose-400 text-xs font-semibold bg-rose-50 dark:bg-rose-950/50 px-2.5 py-1 rounded-lg border border-rose-200/70 dark:border-rose-900">
                                                Sin docente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-center">
                                        <span class="px-3 py-1 text-xs font-semibold uppercase rounded-lg border {{ $asignacion->activo ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200/70 dark:border-emerald-900' : 'bg-stone-100 dark:bg-neutral-800 text-neutral-500 border-stone-200 dark:border-neutral-700' }}">
                                            {{ $asignacion->activo ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                        No hay asignaturas cargadas en este aula.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Evaluaciones por Corte -->
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="p-6 border-b border-stone-100 dark:border-neutral-800">
                    <h3 class="font-bold text-lg text-neutral-900 dark:text-white">Evaluaciones por Corte</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Resumen de calificaciones registradas por corte en tu aula.</p>
                </div>

                <div class="p-6">
                    @if (count($evaluacionesPorCorte ?? []) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach ($evaluacionesPorCorte as $evaluacion)
                                <div class="bg-[#FFFDF8] dark:bg-neutral-800/60 rounded-2xl p-5 border border-stone-200 dark:border-neutral-700">
                                    <h4 class="font-bold text-neutral-900 dark:text-white text-base mb-0.5">
                                        {{ $evaluacion['corte']->nombre ?? ('Corte ' . $evaluacion['corte']->numero) }}
                                    </h4>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-4">{{ $evaluacion['corte']->descripcion ?? '' }}</p>
                                    <div class="grid grid-cols-2 gap-3 text-center">
                                        <div class="bg-white dark:bg-neutral-900 rounded-xl p-3 border border-stone-100 dark:border-neutral-800">
                                            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $evaluacion['promedio'] ?? 0 }}</p>
                                            <p class="text-[10px] font-semibold text-neutral-400 uppercase tracking-wider mt-0.5">Promedio</p>
                                        </div>
                                        <div class="bg-white dark:bg-neutral-900 rounded-xl p-3 border border-stone-100 dark:border-neutral-800">
                                            <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $evaluacion['reprobados'] ?? 0 }}</p>
                                            <p class="text-[10px] font-semibold text-neutral-400 uppercase tracking-wider mt-0.5">Reprobados</p>
                                        </div>
                                    </div>
                                    @php
                                        $totalAl = (int) ($evaluacion['total_alumnos'] ?? 0);
                                        $conNota = (int) ($evaluacion['alumnos_con_nota'] ?? 0);
                                        $pctNota = $totalAl > 0 ? round(($conNota / $totalAl) * 100) : 0;
                                    @endphp
                                    <div class="mt-4 w-full bg-stone-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full bg-[#e6ac27]" style="width: {{ $pctNota }}%"></div>
                                    </div>
                                    <p class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 mt-2">
                                        {{ $conNota }} / {{ $totalAl }} alumnos con nota
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-10 text-center text-neutral-500 dark:text-neutral-400 text-sm">
                            Aún no hay notas cargadas por corte en tu aula.
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>