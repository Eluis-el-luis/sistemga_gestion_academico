{{-- resources/views/academico/reportes/notas-por-asignatura.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Auditoría de Desempeño Docente</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Promedios y tasa de riesgo por asignatura</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white px-3 py-2 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ route('academico.reportes.notas-por-asignatura') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Año Escolar</label>
                        <select name="anio_escolar_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            @foreach ($anios ?? [] as $an)
                                <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{ $an->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Asignatura</label>
                        <select name="asignatura_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Todas</option>
                            @foreach ($asignaturas ?? [] as $asig)
                                <option value="{{ $asig->id }}" @selected($asig->id == request('asignatura_id'))>{{ $asig->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Docente</label>
                        <select name="docente_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Todos</option>
                            @foreach ($docentes ?? [] as $doc)
                                <option value="{{ $doc->id }}" @selected($doc->id == request('docente_id'))>{{ $doc->usuario?->nombre_completo ?? 'Sin nombre' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 px-5 py-2.5 rounded-xl font-bold text-xs uppercase w-full cursor-pointer">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3.5 text-center w-10">#</th>
                                <th class="px-4 py-3.5">Docente</th>
                                <th class="px-4 py-3.5">Asignatura</th>
                                <th class="px-4 py-3.5">Grupos</th>
                                <th class="px-3 py-3.5 text-center">Evaluados</th>
                                <th class="px-3 py-3.5 text-center">Aprobados</th>
                                <th class="px-3 py-3.5 text-center">Reprobados</th>
                                <th class="px-4 py-3.5 text-center">Riesgo</th>
                                <th class="px-4 py-3.5 text-center">Promedio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($filas ?? [] as $index => $fila)
                                <tr>
                                    <td class="px-4 py-3 text-center text-neutral-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ $fila['docente'] }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ $fila['asignatura'] }}</td>
                                    <td class="px-4 py-3 text-xs text-neutral-500">{{ $fila['aulas'] }}</td>
                                    <td class="px-3 py-3 text-center">{{ $fila['evaluados'] }}</td>
                                    <td class="px-3 py-3 text-center font-semibold text-emerald-600">{{ $fila['aprobados'] }}</td>
                                    <td class="px-3 py-3 text-center font-semibold text-rose-600">{{ $fila['reprobados'] }}</td>
                                    <td class="px-4 py-3 text-center font-bold">{{ $fila['tasa_riesgo'] }}%</td>
                                    <td class="px-4 py-3 text-center font-bold">{{ $fila['promedio'] > 0 ? number_format((float) $fila['promedio'], 2) : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-6 py-10 text-center text-neutral-500">No hay registros para mostrar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>