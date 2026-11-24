{{-- resources/views/academico/reportes/notas-globales.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Sábana de Notas Globales</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Consolidado por sección y asignatura</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white px-3 py-2 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider cursor-pointer">Imprimir Sábana</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ route('academico.reportes.notas-globales') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Año Escolar</label>
                        <select name="anio_escolar_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            @foreach ($anios ?? [] as $an)
                                <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{ $an->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Corte Evaluativo</label>
                        <select name="corte_evaluativo_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Promedio Final (Anual)</option>
                            @foreach ($cortes ?? [] as $cort)
                                <option value="{{ $cort->id }}" @selected($cort->id == request('corte_evaluativo_id'))>{{ $cort->descripcion ?? $cort->nombre ?? ($cort->numero . '° Corte') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Aula / Sección</label>
                        <select name="aula_id" required class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Seleccione el Aula...</option>
                            @foreach ($aulas ?? [] as $aulaOp)
                                <option value="{{ $aulaOp->id }}" @selected($aulaOp->id == request('aula_id'))>{{ $aulaOp->grado?->nombre ?? '' }} - {{ $aulaOp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 px-5 py-2.5 rounded-xl font-bold text-xs uppercase w-full cursor-pointer">Generar Sábana</button>
                    </div>
                </form>
            </div>

            @if (request('aula_id'))
                <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-x-auto">
                    <table class="w-full text-center border-collapse border border-stone-300 dark:border-neutral-700 text-xs">
                        <thead>
                            <tr class="bg-[#FFFDF8] dark:bg-neutral-800/70 font-bold uppercase">
                                <th class="border border-stone-300 dark:border-neutral-700 px-2 py-2 w-8">#</th>
                                <th class="border border-stone-300 dark:border-neutral-700 px-3 py-2 text-left">Estudiante</th>
                                @foreach ($asignaturas ?? [] as $asignatura)
                                    <th class="border border-stone-300 dark:border-neutral-700 px-2 py-2">{{ \Illuminate\Support\Str::limit($asignatura->nombre, 10, '.') }}</th>
                                @endforeach
                                <th class="border border-stone-300 dark:border-neutral-700 px-2 py-2 bg-stone-100 dark:bg-neutral-800">PROM</th>
                                <th class="border border-stone-300 dark:border-neutral-700 px-2 py-2 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300">REP</th>
                            </tr>
                        </thead>
                        <tbody class="text-neutral-800 dark:text-neutral-200">
                            @forelse ($filas ?? [] as $index => $fila)
                                <tr>
                                    <td class="border border-stone-300 dark:border-neutral-700 px-2 py-1.5 text-neutral-400">{{ $index + 1 }}</td>
                                    <td class="border border-stone-300 dark:border-neutral-700 px-3 py-1.5 text-left font-semibold">{{ $fila['alumno'] }}</td>
                                    @foreach ($asignaturas ?? [] as $asignatura)
                                        @php $nota = $fila['notas'][$asignatura->id] ?? null; @endphp
                                        <td class="border border-stone-300 dark:border-neutral-700 px-2 py-1.5 font-semibold {{ $nota !== null && $nota < 60 ? 'text-rose-600' : '' }}">{{ $nota !== null ? $nota : '-' }}</td>
                                    @endforeach
                                    <td class="border border-stone-300 dark:border-neutral-700 px-2 py-1.5 font-bold bg-stone-50 dark:bg-neutral-800/40">{{ $fila['promedio_general'] > 0 ? $fila['promedio_general'] : '-' }}</td>
                                    <td class="border border-stone-300 dark:border-neutral-700 px-2 py-1.5 font-bold {{ ($fila['reprobadas'] ?? 0) > 0 ? 'text-rose-600' : 'text-neutral-400' }}">{{ ($fila['reprobadas'] ?? 0) > 0 ? $fila['reprobadas'] : '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="100%" class="border border-stone-300 px-4 py-8 text-center text-neutral-500">No hay estudiantes matriculados en esta aula.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>