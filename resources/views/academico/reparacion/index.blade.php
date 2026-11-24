{{-- resources/views/academico/reparacion/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Exámenes de Reparación</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Estudiantes con asignaturas pendientes de rescate.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{ alumnoAbierto: null }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @forelse ($matriculas as $matricula)
                <div class="bg-white dark:bg-neutral-900 rounded-2xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                    <button type="button" @click="alumnoAbierto = alumnoAbierto === {{ $matricula->id }} ? null : {{ $matricula->id }}" class="w-full px-6 py-4 text-left flex justify-between items-center bg-[#FFFDF8] dark:bg-neutral-800/60 cursor-pointer">
                        <div>
                            <h3 class="font-bold text-neutral-900 dark:text-white text-base">{{ $matricula->alumno?->nombre_completo }}</h3>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $matricula->aula?->grado?->nombre ?? '' }} "{{ $matricula->aula?->nombre ?? '' }}" · {{ optional($matricula->clases_reprobadas)->count() ?? 0 }} materia(s)</span>
                        </div>
                        <span class="text-xs font-semibold text-[#c99116] dark:text-[#e6ac27]" x-text="alumnoAbierto === {{ $matricula->id }} ? 'Ocultar' : 'Evaluar'"></span>
                    </button>

                    <div x-show="alumnoAbierto === {{ $matricula->id }}" x-cloak class="p-6 border-t border-stone-100 dark:border-neutral-800 space-y-3 text-sm">
                        @foreach ($matricula->clases_reprobadas ?? [] as $clase)
                            <form action="{{ route('academico.reparacion.store') }}" method="POST" class="flex flex-wrap items-end gap-3 p-4 rounded-xl bg-stone-50 dark:bg-neutral-800/50 border border-stone-200 dark:border-neutral-700">
                                @csrf
                                <input type="hidden" name="matricula_id" value="{{ $matricula->id }}">
                                <input type="hidden" name="asignatura_id" value="{{ $clase->aulaAsignaturaDocente?->asignatura_id }}">

                                <div class="flex-1 min-w-[180px]">
                                    <label class="block text-[11px] font-semibold text-neutral-500 uppercase mb-1">Materia</label>
                                    <div class="font-semibold text-neutral-900 dark:text-white px-3 py-2 bg-white dark:bg-neutral-900 rounded-xl border border-stone-200 dark:border-neutral-700">
                                        {{ $clase->aulaAsignaturaDocente?->asignatura?->nombre }} (Nota: {{ $clase->nota_cuantitativa }})
                                    </div>
                                </div>
                                <div class="w-28">
                                    <label class="block text-[11px] font-semibold text-neutral-500 uppercase mb-1">Nota Rescate</label>
                                    <input type="number" step="0.01" name="nota_obtenida" min="0" max="100" class="w-full border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 rounded-xl text-sm font-semibold" required>
                                </div>
                                <div class="w-36">
                                    <label class="block text-[11px] font-semibold text-neutral-500 uppercase mb-1">Fecha</label>
                                    <input type="date" name="fecha" value="{{ now()->toDateString() }}" class="w-full border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 rounded-xl text-sm font-semibold" required>
                                </div>
                                <button type="submit" class="px-4 py-2.5 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-xs font-bold uppercase cursor-pointer">Guardar</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-neutral-900 p-12 rounded-3xl border border-stone-200 dark:border-neutral-800 text-center text-neutral-500">
                    No hay estudiantes que requieran examen de reparación.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>