{{-- resources/views/academico/boletin/constancia.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Constancia de Calificaciones</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('academico.boletines.index') }}" class="px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 cursor-pointer">Volver</a>
                <a href="{{ route('academico.boletines.pdf.constancia', $matricula->id) }}" class="px-4 py-2 bg-neutral-900 dark:bg-neutral-800 text-white text-xs font-bold uppercase rounded-xl cursor-pointer">PDF Constancia</a>
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 text-xs font-bold uppercase rounded-xl cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 p-8 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs text-sm">
                <div class="text-center mb-6">
                    <p class="font-bold text-lg uppercase text-neutral-900 dark:text-white">Colegio Cristiano en Nicaragua</p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Constancia de Calificaciones · Ciclo Escolar {{ $matricula->anioEscolar?->nombre ?? '—' }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6 border-t border-stone-100 dark:border-neutral-800 pt-4">
                    <p><strong>Estudiante:</strong> {{ $matricula->alumno?->nombre_completo }}</p>
                    <p><strong>CUP:</strong> {{ $matricula->alumno?->codigo_unico_persona ?? 'N/A' }}</p>
                    <p><strong>Grado / Sección:</strong> {{ $matricula->aula?->grado?->nombre ?? '—' }} "{{ $matricula->aula?->nombre ?? '—' }}"</p>
                    <p><strong>Docente Guía:</strong> {{ $matricula->aula?->docenteGuia?->usuario?->nombre_completo ?? '—' }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-stone-300 dark:border-neutral-700 text-xs">
                        <thead>
                            <tr class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-center font-bold uppercase">
                                <th class="border border-stone-300 dark:border-neutral-700 px-3 py-2 text-left">Asignatura</th>
                                @foreach ($cortes as $corte)
                                    <th class="border border-stone-300 dark:border-neutral-700 px-2 py-2">{{ $corte->numero }}° Corte</th>
                                @endforeach
                                <th class="border border-stone-300 dark:border-neutral-700 px-2 py-2">Nota Final</th>
                                <th class="border border-stone-300 dark:border-neutral-700 px-2 py-2">Indicador</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse ($filas as $fila)
                                <tr>
                                    <td class="border border-stone-300 dark:border-neutral-700 px-3 py-2 text-left font-semibold">{{ $fila['asignatura'] }}</td>
                                    @foreach ($cortes as $corte)
                                        <td class="border border-stone-300 dark:border-neutral-700 px-2 py-2">{{ $fila['cortes'][$corte->numero] ?? '—' }}</td>
                                    @endforeach
                                    <td class="border border-stone-300 dark:border-neutral-700 px-2 py-2 font-bold">{{ $fila['nota_final'] ?? '—' }}</td>
                                    <td class="border border-stone-300 dark:border-neutral-700 px-2 py-2 font-bold">{{ $fila['indicador'] ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($cortes) + 3 }}" class="border border-stone-300 dark:border-neutral-700 px-3 py-6 text-center text-neutral-500">No hay notas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="text-right">
                        <p class="text-xs font-semibold uppercase text-neutral-500">Promedio General</p>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $promedioGeneral !== null ? number_format((float) $promedioGeneral, 2) : '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>