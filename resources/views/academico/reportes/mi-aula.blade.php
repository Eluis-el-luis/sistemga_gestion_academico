{{-- resources/views/academico/reportes/mi-aula.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Centro de Reportes · Mi Aula</h2>
                <p class="text-xs font-semibold text-[#c99116] dark:text-[#e6ac27] mt-0.5">{{ $aulaGuia->grado?->nombre ?? '' }} - Sección "{{ $aulaGuia->nombre ?? '' }}"</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{ tipoReporte: '{{ $tipoReporte ?? 'general' }}' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ route('academico.reportes.mi-aula') }}" class="flex flex-col md:flex-row items-end gap-4 text-sm">
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Tipo de Reporte</label>
                        <select name="tipo_reporte" x-model="tipoReporte" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-medium cursor-pointer">
                            <option value="general">Sábana General</option>
                            <option value="riesgo">Alerta de Reprobados</option>
                            <option value="materia">Por Materia</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/4" x-show="tipoReporte === 'riesgo'" x-cloak>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Severidad</label>
                        <select name="filtro_riesgo" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="todos" @selected(($filtroRiesgo ?? '') == 'todos')>Todos</option>
                            <option value="1_2" @selected(($filtroRiesgo ?? '') == '1_2')>1 o 2 materias</option>
                            <option value="3_mas" @selected(($filtroRiesgo ?? '') == '3_mas')>3 o más materias</option>
                        </select>
                    </div>
                    <div class="w-full md:w-1/4" x-show="tipoReporte === 'materia'" x-cloak>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Materia</label>
                        <select name="materia_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            @foreach ($todasLasAsignaciones ?? [] as $asig)
                                <option value="{{ $asig->id }}" @selected(($materiaId ?? '') == $asig->id)>{{ $asig->asignatura?->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Periodo</label>
                        <select name="corte_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Promedio General</option>
                            @foreach ($cortes ?? [] as $corte)
                                <option value="{{ $corte->id }}" @selected(($corteId ?? '') == $corte->id)>{{ $corte->descripcion ?? $corte->nombre ?? ('Corte ' . $corte->numero) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 font-bold px-6 py-2.5 rounded-xl text-xs uppercase cursor-pointer">Analizar</button>
                </form>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-stone-100 dark:divide-neutral-800 text-xs sm:text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3.5 text-center w-10">#</th>
                                <th class="px-4 py-3.5">Estudiante</th>
                                @foreach ($asignaciones ?? [] as $asig)
                                    <th class="px-2 py-3.5 text-center">{{ \Illuminate\Support\Str::limit($asig->asignatura?->nombre ?? '', 10, '.') }}</th>
                                @endforeach
                                <th class="px-4 py-3.5 text-center">Prom.</th>
                                <th class="px-4 py-3.5 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($alumnosReporte ?? [] as $index => $alumno)
                                <tr>
                                    <td class="px-4 py-3 text-center text-neutral-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ $alumno['nombre_completo'] }}</td>
                                    @foreach ($asignaciones ?? [] as $asig)
                                        @php $nota = $alumno['notas'][$asig->id] ?? null; @endphp
                                        <td class="px-2 py-3 text-center font-semibold {{ $nota !== null && $nota < 60 ? 'text-rose-600' : '' }}">{{ $nota ?? '-' }}</td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center font-bold">{{ $alumno['promedio_general'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center text-xs font-semibold">
                                        @if (($alumno['reprobadas_count'] ?? 0) >= 3)
                                            <span class="text-rose-600">Crítico</span>
                                        @elseif (($alumno['reprobadas_count'] ?? 0) > 0)
                                            <span class="text-amber-600">{{ $alumno['reprobadas_count'] }} Rep</span>
                                        @else
                                            <span class="text-emerald-600">Limpio</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($asignaciones ?? []) + 4 }}" class="px-6 py-10 text-center text-neutral-500">No hay estudiantes para los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>