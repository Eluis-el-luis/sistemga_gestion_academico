{{-- resources/views/academico/cortes/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Criterios de Evaluación Institucional</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 dark:divide-neutral-800 text-sm">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-3.5 text-left">Corte Evaluativo</th>
                                <th class="px-6 py-3.5 text-left">Fechas y Pesos (100 pts)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($cortes as $corte)
                                <tr>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-neutral-900 dark:text-white text-base">{{ $corte->numero }}° Parcial</span>
                                        <p class="text-xs text-neutral-500 mt-0.5">Semestre {{ $corte->semestre }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('academico.cortes.update', $corte->id) }}" method="POST" class="flex flex-wrap items-end justify-between gap-4">
                                            @csrf
                                            @method('PUT')
                                            <div class="flex items-center gap-2">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-neutral-500 uppercase mb-1">Inicio</label>
                                                    <input type="date" name="fecha_inicio" value="{{ $corte->fecha_inicio }}" class="text-xs font-semibold border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl" required>
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-neutral-500 uppercase mb-1">Fin</label>
                                                    <input type="date" name="fecha_fin" value="{{ $corte->fecha_fin }}" class="text-xs font-semibold border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl" required>
                                                </div>
                                            </div>
                                            <div class="flex items-end gap-2">
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-neutral-500 uppercase mb-1 text-center">% Acum.</label>
                                                    <input type="number" name="peso_acumulado" value="{{ $corte->peso_acumulado ?? 60 }}" min="0" max="100" class="w-20 text-center font-bold border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-xs" required>
                                                </div>
                                                <div>
                                                    <label class="block text-[11px] font-semibold text-neutral-500 uppercase mb-1 text-center">% Exam.</label>
                                                    <input type="number" name="peso_examen" value="{{ $corte->peso_examen ?? 40 }}" min="0" max="100" class="w-20 text-center font-bold border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-xs" required>
                                                </div>
                                                <button type="submit" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-4 py-2 rounded-xl text-xs uppercase cursor-pointer">Guardar</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-6 py-8 text-center text-neutral-500">No hay cortes evaluativos configurados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>