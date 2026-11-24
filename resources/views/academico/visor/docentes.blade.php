{{-- resources/views/academico/visor/docentes.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer" title="Volver al Panel Principal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Directorio de Docentes</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @forelse ($docentes as $docente)
                    <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden flex flex-col justify-between">
                        <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 p-6 border-b border-stone-200/80 dark:border-neutral-800 flex flex-col items-center text-center">
                            <span class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider mb-2">{{ $docente->codigo_unico_persona ?? 'N/A' }}</span>
                            <div class="w-14 h-14 bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-700 text-[#c99116] dark:text-[#e6ac27] rounded-full flex items-center justify-center text-xl font-bold mb-3">
                                {{ mb_strtoupper(mb_substr(trim((string) ($docente->usuario?->nombre_completo ?? 'D')), 0, 1, 'UTF-8'), 'UTF-8') }}
                            </div>
                            <h3 class="font-bold text-base text-neutral-900 dark:text-white">{{ $docente->usuario?->nombre_completo ?? 'Profesor Sin Nombre' }}</h3>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Personal Docente</p>
                        </div>

                        <div class="p-5">
                            <a href="{{ route('academico.visor.docente.show', $docente->id) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-stone-100 dark:bg-neutral-800 hover:bg-[#e6ac27] text-neutral-800 dark:text-neutral-100 hover:text-neutral-950 rounded-xl text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer">
                                Ver Horario Semanal
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-12 text-center text-neutral-500">
                        No hay docentes registrados.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>