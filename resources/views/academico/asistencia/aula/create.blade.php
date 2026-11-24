<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al Panel">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                        Pase de Lista (Guía)
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 font-medium">
                        Aula: <span class="font-semibold text-[#c99116] dark:text-[#e6ac27]">{{ $aula->grado?->nombre }} - {{ $aula->nombre }}</span>
                        <span class="mx-1.5">·</span>
                        Cupo: {{ $aula->cupo }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-neutral-900 dark:text-white text-base">Fecha de Asistencia</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Selecciona el día para consultar o actualizar el pase de lista.</p>
                </div>
                <form method="GET" action="{{ route('academico.asistencia.aula.create') }}" class="flex items-center gap-3 w-full sm:w-auto">
                    <input type="date" name="fecha" value="{{ $fecha }}" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-semibold text-sm w-full sm:w-auto" required>
                    <button type="submit" class="bg-stone-100 dark:bg-neutral-800 hover:bg-stone-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-100 font-semibold py-2.5 px-4 rounded-xl transition-colors text-xs uppercase tracking-wider cursor-pointer">
                        Cambiar
                    </button>
                </form>
            </div>

            <form action="{{ route('academico.asistencia.aula.store') }}" method="POST">
                @csrf
                <input type="hidden" name="fecha" value="{{ $fecha }}">

                <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-200 dark:divide-neutral-800 text-sm text-left">
                            <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-600 dark:text-neutral-300 border-b border-stone-200 dark:border-neutral-800">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider w-12">#</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider min-w-[220px]">Estudiante</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider">Estado de Asistencia</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                @forelse ($matriculas as $index => $matricula)
                                    @php
                                        $estadoActual = isset($asistenciasPrevias[$matricula->id])
                                            ? $asistenciasPrevias[$matricula->id]->estado_asistencia
                                            : 'Presente';
                                        $alertas = $incidenciasHoy->where('matricula_id', $matricula->id);
                                    @endphp
                                    <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                        <td class="px-6 py-4 font-semibold text-neutral-400">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4">
                                            <input type="hidden" name="asistencias[{{ $index }}][matricula_id]" value="{{ $matricula->id }}">
                                            <div class="font-semibold text-neutral-900 dark:text-white">{{ $matricula->alumno?->nombre_completo }}</div>
                                            <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mt-0.5">{{ $matricula->alumno?->codigo_unico_persona }}</div>

                                            @if ($alertas->isNotEmpty())
                                                <div class="mt-2 flex flex-col items-start gap-1">
                                                    @foreach ($alertas as $incidencia)
                                                        <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/50 px-2 py-0.5 rounded-lg border border-rose-200 dark:border-rose-900">
                                                            {{ $incidencia->estado_incidencia }} en {{ optional($incidencia->asignatura)->nombre ?? 'clase' }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                @foreach ([
                                                    'Presente' => ['label' => 'Presente', 'active' => 'peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600'],
                                                    'Ausencia Injustificada' => ['label' => 'Falta', 'active' => 'peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600'],
                                                    'Ausencia Justificada' => ['label' => 'Justificada', 'active' => 'peer-checked:bg-[#e6ac27] peer-checked:text-neutral-950 peer-checked:border-[#e6ac27]'],
                                                    'Retiro Anticipado' => ['label' => 'Se Retiró', 'active' => 'peer-checked:bg-amber-600 peer-checked:text-white peer-checked:border-amber-600'],
                                                    'Actividad Institucional' => ['label' => 'Actividad', 'active' => 'peer-checked:bg-sky-600 peer-checked:text-white peer-checked:border-sky-600'],
                                                ] as $valorEstado => $metaEstado)
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="{{ $valorEstado }}" class="peer sr-only" {{ $estadoActual === $valorEstado ? 'checked' : '' }}>
                                                        <div class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors border bg-white dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 border-stone-300 dark:border-neutral-700 {{ $metaEstado['active'] }}">
                                                            {{ $metaEstado['label'] }}
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400 font-medium">
                                            No hay alumnos activos matriculados en esta aula.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6 flex justify-end sticky bottom-6 z-20">
                    <button type="submit" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-3 px-7 rounded-2xl shadow-lg transition-colors flex items-center gap-2 text-sm cursor-pointer">
                        Guardar Pase de Lista
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>