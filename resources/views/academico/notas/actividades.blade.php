{{-- resources/views/academico/notas/actividades.blade.php --}}
<x-app-layout>
    @php
        $esDirectiva = auth()->user()->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector']);
        $esCoordinador = auth()->user()->hasAnyRole(['coordinador', 'Coordinador']);
        $modoSupervision = $esDirectiva || $esCoordinador;
        $rutaRetorno = $modoSupervision
            ? route('academico.notas.index', ['grado_id' => $asignacion->aula?->grado_id, 'aula_id' => $asignacion->aula_id])
            : route('academico.notas.evaluar', ['asignacion' => $asignacion->id, 'corte_evaluativo_id' => $corteSeleccionado]);
    @endphp
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ $rutaRetorno }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white">
                        {{ $modoSupervision ? 'Auditoría de Actividades:' : 'Configurar Actividades:' }}
                        <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $asignacion->asignatura?->nombre }}</span>
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        {{ $asignacion->aula?->grado?->nombre }} - Sección "{{ $asignacion->aula?->nombre }}" · {{ $asignacion->docente?->usuario?->nombre_completo ?? 'Sin docente' }}
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <form method="GET" class="flex items-center gap-3">
                    <label class="text-xs font-semibold text-neutral-500 uppercase">Parcial:</label>
                    <select name="corte_id" onchange="this.form.submit()" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-semibold cursor-pointer">
                        @foreach ($cortes as $corte)
                            <option value="{{ $corte->id }}" {{ $corteSeleccionado == $corte->id ? 'selected' : '' }}>{{ $corte->numero }}° Parcial</option>
                        @endforeach
                    </select>
                </form>

                @if ($corteActivo && $actividades->isNotEmpty() && auth()->user()->can('calificar', $asignacion))
                    <a href="{{ route('academico.notas.evaluar', ['asignacion' => $asignacion->id, 'corte_evaluativo_id' => $corteSeleccionado]) }}" class="px-5 py-2.5 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold text-xs uppercase rounded-xl transition-colors cursor-pointer">
                        Ir a Calificar Planilla
                    </a>
                @endif
            </div>

            @if ($corteActivo)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800">
                        <span class="text-xs font-semibold text-neutral-400 uppercase">Puntos de Acumulado</span>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">{{ $sumaAcumulados }} / {{ $corteActivo->peso_acumulado }} pts</p>
                    </div>
                    <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800">
                        <span class="text-xs font-semibold text-neutral-400 uppercase">Puntos de Examen</span>
                        <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">{{ $sumaExamen }} / {{ $corteActivo->peso_examen }} pts</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 {{ $modoSupervision ? '' : 'lg:grid-cols-12' }} gap-6">
                    @can('calificar', $asignacion)
                        <div class="lg:col-span-4 bg-white dark:bg-neutral-900 p-6 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 h-fit">
                            <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-4">Nueva Actividad</h3>
                            <form action="{{ route('academico.notas.actividades.store', $asignacion->id) }}" method="POST" class="space-y-4 text-sm">
                                @csrf
                                <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Nombre</label>
                                    <input type="text" name="nombre" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Tipo</label>
                                    <select name="tipo" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer" required>
                                        <option value="acumulado">Acumulado</option>
                                        <option value="examen">Examen</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Puntaje Máximo</label>
                                    <input type="number" name="puntaje_maximo" min="1" max="100" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-bold text-center" required>
                                </div>
                                <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 rounded-xl text-xs uppercase cursor-pointer">Guardar Actividad</button>
                            </form>
                        </div>
                    @endcan

                    <div class="{{ $modoSupervision ? '' : 'lg:col-span-8' }} bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                        <table class="min-w-full text-sm text-left">
                            <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-6 py-3.5">Tipo</th>
                                    <th class="px-6 py-3.5">Actividad</th>
                                    <th class="px-6 py-3.5 text-center">Puntos</th>
                                    <th class="px-6 py-3.5 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                @forelse ($actividades as $actividad)
                                    <tr>
                                        <td class="px-6 py-3.5 uppercase text-xs font-semibold">{{ $actividad->tipo }}</td>
                                        <td class="px-6 py-3.5 font-semibold">{{ $actividad->nombre }}</td>
                                        <td class="px-6 py-3.5 text-center font-bold">{{ $actividad->puntaje_maximo }}</td>
                                        <td class="px-6 py-3.5 text-center">
                                            @can('calificar', $asignacion)
                                                <form action="{{ route('academico.notas.actividades.destroy', [$asignacion->id, $actividad->id]) }}" method="POST" class="inline form-eliminar">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">Eliminar</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-neutral-400">Solo lectura</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-10 text-center text-neutral-500">Sin actividades registradas.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>