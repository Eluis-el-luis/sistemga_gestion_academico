{{-- resources/views/academico/docente-guia/rendimiento.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white">
                        Rendimiento por Corte · <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $aula->grado?->nombre ?? '' }} "{{ $aula->nombre }}"</span>
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $aula->modalidad?->nombre ?? '' }} · {{ $aula->anioEscolar?->nombre ?? '' }}</p>
                </div>
            </div>
            <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-4 py-2 rounded-xl text-xs uppercase cursor-pointer">Imprimir / PDF</button>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                <form method="GET" action="{{ route('academico.tutor.rendimiento') }}" class="flex items-end gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Periodo Evaluativo</label>
                        <select name="corte_evaluativo_id" onchange="this.form.submit()" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-medium cursor-pointer">
                            <option value="">Todos los cortes</option>
                            @foreach ($cortes as $corte)
                                <option value="{{ $corte->id }}" @selected($corte->id == request('corte_evaluativo_id'))>{{ $corte->numero }}° Corte</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-stone-200 dark:border-neutral-800 p-5 text-center">
                    <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ ($rendimientoAula['mi_as'] ?? 0) + ($rendimientoAula['mi_f'] ?? 0) }}</p>
                    <p class="text-xs font-semibold text-neutral-400 uppercase mt-1">Matrícula</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-stone-200 dark:border-neutral-800 p-5 text-center">
                    <p class="text-2xl font-bold text-emerald-600">{{ $rendimientoAula['aprobados_todas'] ?? 0 }}</p>
                    <p class="text-xs font-semibold text-neutral-400 uppercase mt-1">Aprobados</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-stone-200 dark:border-neutral-800 p-5 text-center">
                    <p class="text-2xl font-bold text-[#c99116] dark:text-[#e6ac27]">{{ $rendimientoAula['porcentaje_aprobados'] ?? 0 }}%</p>
                    <p class="text-xs font-semibold text-neutral-400 uppercase mt-1">% Aprobados</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-stone-200 dark:border-neutral-800 p-5 text-center">
                    <p class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $rendimientoAula['total_docentes'] ?? 0 }}</p>
                    <p class="text-xs font-semibold text-neutral-400 uppercase mt-1">Docentes</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>