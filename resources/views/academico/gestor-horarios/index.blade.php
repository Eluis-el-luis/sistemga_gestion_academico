{{-- resources/views/academico/gestor-horarios/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer" title="Volver al Panel">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Gestor de Horarios</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @forelse ($aulas as $aula)
                    <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-3">
                                <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $aula->anioEscolar?->nombre ?? 'N/A' }} · {{ $aula->modalidad?->nombre ?? 'N/A' }}</span>
                                <span class="bg-stone-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 px-2 py-0.5 rounded-lg text-[10px] font-semibold uppercase">{{ $aula->turno }}</span>
                            </div>
                            <h3 class="font-bold text-xl text-neutral-900 dark:text-white">
                                {{ $aula->grado?->nombre ?? 'N/A' }} <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $aula->nombre }}</span>
                            </h3>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">Cupo: {{ $aula->cupo }} alumnos máx.</p>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 truncate">
                                Guía: {{ $aula->docenteGuia?->usuario?->nombre_completo ?? 'Sin Docente Guía' }}
                            </p>
                        </div>

                        <div class="pt-4 mt-5 border-t border-stone-100 dark:border-neutral-800">
                            <a href="{{ route('academico.aulas.horarios.index', $aula->id) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-[#FFFDF8] dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30 hover:bg-[#e6ac27] hover:text-neutral-950 rounded-xl text-xs font-bold uppercase tracking-wider transition-colors cursor-pointer">
                                Armar Horario
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-neutral-900 p-12 text-center rounded-3xl border border-stone-200 dark:border-neutral-800 text-neutral-500">
                        No hay aulas aperturadas aún.
                    </div>
                @endforelse
            </div>

            @if ($aulas->hasPages())
                <div>{{ $aulas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>