{{-- resources/views/academico/notas/evaluar.blade.php --}}
<x-app-layout>
    @php
        $esDirectiva = auth()->user()->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector']);
        $totalCorte = ($pesoAcumulado + $pesoExamen) > 0 ? ($pesoAcumulado + $pesoExamen) : max(1, $sumaAcumulado + $sumaExamen);
    @endphp
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ $esDirectiva ? route('academico.notas.index', ['grado_id' => $asignacion->aula?->grado_id, 'aula_id' => $asignacion->aula_id]) : route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Registro de Calificaciones</h2>
                    <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ $asignacion->asignatura?->nombre }} · {{ $asignacion->aula?->grado?->nombre }} - {{ $asignacion->aula?->nombre }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if (!$estaBloqueado && auth()->user()->can('calificar', $asignacion))
                    <a href="{{ route('academico.notas.actividades.index', ['asignacion' => $asignacion->id, 'corte_id' => $corteSeleccionado]) }}" class="px-4 py-2 bg-neutral-900 dark:bg-neutral-800 text-white text-xs font-bold uppercase rounded-xl cursor-pointer">
                        Configurar Actividades
                    </a>
                @endif
                <form action="{{ route('academico.notas.evaluar', $asignacion->id) }}" method="GET" class="flex items-center gap-2">
                    <select name="corte_evaluativo_id" onchange="this.form.submit()" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-xs font-semibold cursor-pointer">
                        @foreach ($cortes as $corte)
                            <option value="{{ $corte->id }}" {{ $corteSeleccionado == $corte->id ? 'selected' : '' }}>Corte {{ $corte->numero }} ({{ $corte->anioEscolar?->nombre }})</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($actividades->isEmpty())
                <div class="bg-white dark:bg-neutral-900 p-10 rounded-3xl border border-stone-200 dark:border-neutral-800 text-center">
                    <h3 class="text-lg font-bold text-neutral-900 dark:text-white mb-2">No hay actividades configuradas</h3>
                    <p class="text-sm text-neutral-500 mb-6">Configura las actividades del corte antes de ingresar calificaciones.</p>
                    <a href="{{ route('academico.notas.actividades.index', ['asignacion' => $asignacion->id, 'corte_id' => $corteSeleccionado]) }}" class="inline-block px-6 py-2.5 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold text-xs uppercase rounded-xl cursor-pointer">
                        Configurar Actividades
                    </a>
                </div>
            @else
                <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs overflow-hidden">
                    <form id="form-calificaciones" action="{{ route('academico.notas.store', $asignacion->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse min-w-[800px] text-sm">
                                <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 border-b border-stone-200 dark:border-neutral-800">
                                    <tr>
                                        <th class="px-5 py-3.5 text-xs font-semibold text-neutral-500 uppercase sticky left-0 bg-[#FFFDF8] dark:bg-neutral-900">Estudiante</th>
                                        @foreach ($actividades as $actividad)
                                            <th class="px-3 py-3.5 text-center text-xs font-semibold">
                                                <span class="block text-neutral-900 dark:text-white">{{ $actividad->nombre }}</span>
                                                <span class="block text-[10px] text-[#c99116]">Máx: {{ $actividad->puntaje_maximo }}</span>
                                            </th>
                                        @endforeach
                                        <th class="px-5 py-3.5 text-center text-xs font-semibold bg-stone-50 dark:bg-neutral-800/40">Nota Final (0-100)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                    @foreach ($matriculas as $matricula)
                                        @php
                                            $notaCorte = $matricula->notas->firstWhere('corte_evaluativo_id', $corteSeleccionado);
                                            $totalInicial = $notaCorte ? ($notaCorte->nota_cuantitativa ?? 0) : 0;
                                        @endphp
                                        <tr x-data="{ totalFila: {{ $totalInicial }}, recalcular() { let suma = 0; this.$el.querySelectorAll('.input-nota').forEach(i => { suma += parseFloat(i.value) || 0; }); this.totalFila = Math.min(100, Math.max(0, (suma / {{ $totalCorte }}) * 100)).toFixed(2).replace(/\.00$/, ''); } }">
                                            <td class="px-5 py-3 sticky left-0 bg-white dark:bg-neutral-900 font-semibold">{{ $matricula->alumno?->nombre_completo }}</td>
                                            @foreach ($actividades as $actividad)
                                                @php $na = isset($notasActividades[$matricula->id]) ? $notasActividades[$matricula->id]->firstWhere('actividad_evaluativa_id', $actividad->id) : null; @endphp
                                                <td class="px-2 py-3 text-center">
                                                    <input type="number" name="notas[{{ $matricula->id }}][{{ $actividad->id }}]" value="{{ $na?->nota_obtenida ?? '' }}" max="{{ $actividad->puntaje_maximo }}" min="0" step="0.01" @input="recalcular" {{ $estaBloqueado ? 'readonly' : '' }} class="input-nota w-20 text-center font-semibold text-xs rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 py-1.5">
                                                </td>
                                            @endforeach
                                            <td class="px-5 py-3 text-center font-bold bg-stone-50 dark:bg-neutral-800/40" x-text="totalFila"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if (!$estaBloqueado)
                            <div class="p-5 flex justify-end gap-3 border-t border-stone-100 dark:border-neutral-800">
                                <button type="submit" class="px-6 py-2.5 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 text-xs font-bold uppercase rounded-xl cursor-pointer">
                                    Guardar Calificaciones
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>