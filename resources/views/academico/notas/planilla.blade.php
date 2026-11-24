{{-- resources/views/academico/notas/planilla.blade.php --}}
<x-app-layout>
    @php $totalCorte = ($pesoAcumulado + $pesoExamen) > 0 ? ($pesoAcumulado + $pesoExamen) : max(1, $sumaAcumulado + $sumaExamen); @endphp
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.notas.index') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Planilla de Calificaciones</h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $asignacion->asignatura?->nombre }} · {{ $asignacion->aula?->grado?->nombre }} - {{ $asignacion->aula?->nombre }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <form action="{{ route('academico.notas.create', $asignacion->id) }}" method="GET">
                    <select name="corte_evaluativo_id" onchange="this.form.submit()" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-xs font-semibold cursor-pointer">
                        @foreach ($cortes as $corte)
                            <option value="{{ $corte->id }}" {{ $corteSeleccionado == $corte->id ? 'selected' : '' }}>Corte {{ $corte->numero }} ({{ $corte->anioEscolar?->nombre }})</option>
                        @endforeach
                    </select>
                </form>
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold text-xs uppercase rounded-xl print:hidden cursor-pointer">Imprimir / PDF</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 p-5 rounded-3xl border {{ $estaBloqueado ? 'border-rose-200 dark:border-rose-900' : 'border-stone-200 dark:border-neutral-800' }} shadow-xs flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="font-bold text-neutral-900 dark:text-white text-base">{{ $estaBloqueado ? 'Calificaciones Bloqueadas' : 'Edición Abierta' }}</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $estaBloqueado ? 'El parcial fue cerrado.' : 'Puedes ingresar y guardar calificaciones.' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('academico.notas.actividades.index', $asignacion->id) }}" class="px-4 py-2 bg-stone-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 text-xs font-bold uppercase rounded-xl cursor-pointer">Actividades</a>
                    @if ($estaBloqueado)
                        <button type="button" @click="$dispatch('abrir-modal-desbloqueo')" class="px-4 py-2 bg-rose-600 text-white text-xs font-bold uppercase rounded-xl cursor-pointer">Solicitar Desbloqueo</button>
                    @elseif ($actividades->isNotEmpty())
                        <button form="form-planilla" type="submit" class="px-5 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 text-xs font-bold uppercase rounded-xl cursor-pointer">Guardar Avance</button>
                        <button type="button" @click="$dispatch('abrir-modal-cierre')" class="px-4 py-2 border border-rose-500 text-rose-600 dark:text-rose-400 text-xs font-bold uppercase rounded-xl cursor-pointer">Cerrar Parcial</button>
                    @endif
                </div>
            </div>

            @if ($actividades->isNotEmpty())
                <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs overflow-hidden">
                    <form id="form-planilla" action="{{ route('academico.notas.store', $asignacion->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse whitespace-nowrap text-sm">
                                <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 border-b border-stone-200 dark:border-neutral-800">
                                    <tr>
                                        <th class="px-5 py-3.5 text-xs font-semibold text-neutral-500 uppercase sticky left-0 bg-[#FFFDF8] dark:bg-neutral-900"># Estudiante</th>
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
                                    @foreach ($matriculas as $index => $matricula)
                                        @php $notaCorte = $matricula->notas->firstWhere('corte_evaluativo_id', $corteSeleccionado); $totalInicial = $notaCorte ? ($notaCorte->nota_cuantitativa ?? 0) : 0; @endphp
                                        <tr x-data="{ totalFila: {{ $totalInicial }}, recalcular() { let suma = 0; this.$el.querySelectorAll('.input-nota').forEach(i => { const max = parseFloat(i.getAttribute('max')) || 0; let v = parseFloat(i.value) || 0; if (v > max) { i.value = max; v = max; } suma += v; }); this.totalFila = Math.min(100, Math.max(0, (suma / {{ $totalCorte }}) * 100)).toFixed(2).replace(/\.00$/, ''); } }">
                                            <td class="px-5 py-3 sticky left-0 bg-white dark:bg-neutral-900 font-semibold">{{ $index + 1 }}. {{ $matricula->alumno?->nombre_completo }}</td>
                                            @foreach ($actividades as $actividad)
                                                @php $na = isset($notasActividades[$matricula->id]) ? $notasActividades[$matricula->id]->firstWhere('actividad_evaluativa_id', $actividad->id) : null; @endphp
                                                <td class="px-2 py-3 text-center">
                                                    <input type="number" name="notas[{{ $matricula->id }}][{{ $actividad->id }}]" value="{{ $na?->nota_obtenida ?? '' }}" max="{{ $actividad->puntaje_maximo }}" min="0" step="0.01" @input="recalcular" {{ $estaBloqueado ? 'readonly' : '' }} class="input-nota w-20 text-center text-xs font-semibold rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 py-1.5">
                                                </td>
                                            @endforeach
                                            <td class="px-5 py-3 text-center font-bold bg-stone-50 dark:bg-neutral-800/40" x-text="totalFila"></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <div x-data="{ open: false }" @abrir-modal-cierre.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-white dark:bg-neutral-900 rounded-3xl w-full max-w-md overflow-hidden border border-stone-200 dark:border-neutral-800 p-6 space-y-4">
            <h3 class="text-base font-bold text-rose-600 dark:text-rose-400">¿Cerrar Calificaciones?</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-300">Al cerrar el parcial, las notas quedarán bloqueadas y requerirán autorización para editarse.</p>
            <form action="{{ route('academico.notas.cerrar', $asignacion->id) }}" method="POST" class="flex justify-end gap-3">
                @csrf
                <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                <button type="button" @click="open = false" class="px-4 py-2 text-xs font-semibold text-neutral-500 cursor-pointer">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-rose-600 text-white text-xs font-bold uppercase rounded-xl cursor-pointer">Sí, Cerrar Parcial</button>
            </form>
        </div>
    </div>

    <div x-data="{ open: false }" @abrir-modal-desbloqueo.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-white dark:bg-neutral-900 rounded-3xl w-full max-w-lg overflow-hidden border border-stone-200 dark:border-neutral-800">
            <form action="{{ route('academico.notas.solicitar-desbloqueo', $asignacion->id) }}" method="POST" class="p-6 space-y-4 text-sm">
                @csrf
                <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                <h3 class="text-base font-bold text-neutral-900 dark:text-white">Solicitud de Desbloqueo</h3>
                <textarea name="motivo" rows="4" required placeholder="Indica el motivo de la corrección..." class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm"></textarea>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-xs font-semibold text-neutral-500 cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-[#e6ac27] text-neutral-950 text-xs font-bold uppercase rounded-xl cursor-pointer">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>