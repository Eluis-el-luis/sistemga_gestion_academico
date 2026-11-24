{{-- resources/views/academico/docente-guia/mis-alumnos.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white">
                        Mis Alumnos: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $aula->grado?->nombre ?? '' }} "{{ $aula->nombre ?? '' }}"</span>
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Ranking de rendimiento y seguimiento de la sección.</p>
                </div>
            </div>
            <button type="button" onclick="window.print()" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 font-bold py-2 px-4 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-neutral-900 p-5 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs">
                    <span class="text-xs font-semibold text-neutral-400 uppercase">Matrícula Activa</span>
                    <h4 class="text-3xl font-bold text-neutral-900 dark:text-white mt-1">{{ $totalAlumnos }}</h4>
                </div>
                <div class="bg-white dark:bg-neutral-900 p-5 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs">
                    <span class="text-xs font-semibold text-emerald-600 uppercase">Aprobados</span>
                    <h4 class="text-3xl font-bold text-emerald-600 mt-1">{{ $aprobadosLimpios }} <span class="text-sm text-neutral-400">({{ $pctAprobados }}%)</span></h4>
                </div>
                <div class="bg-white dark:bg-neutral-900 p-5 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs">
                    <span class="text-xs font-semibold text-amber-600 uppercase">Reparación (1-2)</span>
                    <h4 class="text-3xl font-bold text-amber-600 mt-1">{{ $enReparacion }} <span class="text-sm text-neutral-400">({{ $pctReparacion }}%)</span></h4>
                </div>
                <div class="bg-white dark:bg-neutral-900 p-5 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs">
                    <span class="text-xs font-semibold text-rose-600 uppercase">Riesgo Crítico (3+)</span>
                    <h4 class="text-3xl font-bold text-rose-600 mt-1">{{ $riesgoCritico }} <span class="text-sm text-neutral-400">({{ $pctRiesgo }}%)</span></h4>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border-collapse text-sm">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="p-4 text-center w-14">#</th>
                                <th class="p-4">Estudiante</th>
                                <th class="p-4 text-center">Promedio</th>
                                <th class="p-4 text-center">Reprobadas</th>
                                <th class="p-4">Situación</th>
                                <th class="p-4 text-right">Expediente</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($alumnos as $index => $alumno)
                                <tr>
                                    <td class="p-4 text-center text-neutral-400 font-semibold">{{ $index + 1 }}</td>
                                    <td class="p-4 font-semibold">{{ $alumno->nombre_completo }}</td>
                                    <td class="p-4 text-center font-bold">{{ number_format((float) $alumno->promedio_global, 1) }}</td>
                                    <td class="p-4 text-center font-semibold">{{ $alumno->clases_reprobadas }}</td>
                                    <td class="p-4"><span class="px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $alumno->estado_color }}">{{ $alumno->estado_texto }}</span></td>
                                    <td class="p-4 text-right"><a href="{{ route('academico.alumnos.show', $alumno->id) }}" class="text-xs font-bold text-[#c99116] dark:text-[#e6ac27] hover:underline cursor-pointer">Ver Ficha</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-10 text-center text-neutral-500">Sin estudiantes activos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>