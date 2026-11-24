{{-- resources/views/academico/notas/index.blade.php --}}
<x-app-layout>
    @php
        $esDirectiva = auth()->user()->hasAnyRole(['directora', 'subdirectora', 'Director', 'Subdirector']);
        $esCoordinador = isset($esCoordinador) ? $esCoordinador : auth()->user()->hasAnyRole(['coordinador', 'Coordinador']);
    @endphp
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white">{{ ($esDirectiva || $esCoordinador) ? 'Supervisión de Evaluaciones' : 'Centro de Calificaciones' }}</h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Selecciona una asignatura para revisar o calificar.</p>
                </div>
            </div>
            @if ($esDirectiva)
                <a href="{{ route('academico.cortes.index') }}" class="px-4 py-2 bg-neutral-900 dark:bg-neutral-800 text-white text-xs font-bold uppercase rounded-xl cursor-pointer">
                    Criterios Base
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if ($esDirectiva)
                <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs">
                    <form method="GET" action="{{ route('academico.notas.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end text-sm">
                        <div class="md:col-span-5">
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Grado</label>
                            <select name="grado_id" id="filtro_grado" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-sm cursor-pointer">
                                <option value="">Todos los grados...</option>
                                @foreach ($grados as $grado)
                                    <option value="{{ $grado->id }}" {{ $grado->id == $gradoSeleccionadoId ? 'selected' : '' }}>{{ $grado->modalidad?->nombre ?? '' }} - {{ $grado->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-5">
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Aula</label>
                            <select name="aula_id" id="filtro_aula" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-sm cursor-pointer">
                                <option value="">Seleccione sección...</option>
                                @foreach ($aulas as $aula)
                                    <option value="{{ $aula->id }}" data-grado="{{ $aula->grado_id }}" {{ $aulaSeleccionadaId == $aula->id ? 'selected' : '' }}>Sección "{{ $aula->nombre }}"</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit" class="w-full py-2.5 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-xs font-bold uppercase cursor-pointer">Filtrar</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($asignaciones as $asignacion_item)
                    <a href="{{ $esDirectiva ? route('academico.notas.actividades.index', $asignacion_item->id) : route('academico.notas.evaluar', $asignacion_item->id) }}" class="bg-white dark:bg-neutral-900 rounded-3xl border border-stone-200 dark:border-neutral-800 p-6 shadow-xs hover:border-[#e6ac27] transition-colors flex flex-col justify-between cursor-pointer">
                        <div>
                            <span class="inline-block px-2.5 py-1 bg-stone-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 text-xs font-semibold rounded-lg mb-3">
                                {{ $asignacion_item->aula?->grado?->nombre ?? '' }} - {{ $asignacion_item->aula?->nombre ?? '' }}
                            </span>
                            <h4 class="text-lg font-bold text-neutral-900 dark:text-white">{{ $asignacion_item->asignatura?->nombre ?? '' }}</h4>
                            @if ($esDirectiva)
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">{{ $asignacion_item->docente?->usuario?->nombre_completo ?? 'Sin docente' }}</p>
                            @endif
                        </div>
                        <div class="mt-5 pt-3 border-t border-stone-100 dark:border-neutral-800 text-xs font-bold text-[#c99116] dark:text-[#e6ac27] flex justify-between items-center">
                            <span>{{ $esDirectiva ? 'Auditar Actividades' : 'Evaluar / Configurar' }}</span>
                            <span>-></span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full bg-white dark:bg-neutral-900 p-12 text-center rounded-3xl border border-stone-200 dark:border-neutral-800 text-neutral-500">
                        No hay asignaturas disponibles.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>