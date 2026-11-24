{{-- resources/views/academico/apoyo_familiar/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-4 py-3 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Apoyo Familiar</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Evaluación en el hogar ({{ $corteActivo->numero ?? 1 }}° Corte).</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{ evaluados: {{ (int) ($totalEvaluados ?? 0) }}, total: {{ max(1, $matriculas->count()) }} }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="block text-xs font-semibold text-neutral-400 uppercase tracking-wider">Promedio del Aula</span>
                        <h4 class="text-3xl font-bold text-neutral-900 dark:text-white mt-1">{{ $promedioAula ?? 0 }}%</h4>
                    </div>
                </div>
                <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="block text-xs font-semibold text-neutral-400 uppercase tracking-wider">Progreso de Evaluación</span>
                        <h4 class="text-3xl font-bold text-neutral-900 dark:text-white mt-1"><span x-text="evaluados"></span><span class="text-lg text-neutral-400">/{{ $matriculas->count() }}</span></h4>
                    </div>
                </div>
            </div>

            <form action="{{ route('academico.apoyo_familiar.store') }}" method="POST">
                @csrf
                <input type="hidden" name="corte_evaluativo_id" value="{{ $corteActivo->id ?? '' }}">

                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left border-collapse text-sm">
                            <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 border-b border-stone-200 dark:border-neutral-800 text-xs uppercase tracking-wider font-semibold text-neutral-500 dark:text-neutral-400">
                                <tr>
                                    <th class="p-4 w-14 text-center">#</th>
                                    <th class="p-4">Estudiante</th>
                                    <th class="p-4 text-center">Nivel de Apoyo (B - MB - EX)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                @forelse ($matriculas as $index => $matricula)
                                    @php $actual = $matricula->evaluacionesFamiliares->first()->evaluacion ?? ''; @endphp
                                    <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors" x-data="{ seleccion: @js($actual) }">
                                        <td class="p-4 text-center font-semibold text-neutral-400">{{ $index + 1 }}</td>
                                        <td class="p-4 font-semibold">{{ $matricula->alumno?->nombre_completo ?? 'Estudiante' }}</td>
                                        <td class="p-4">
                                            <div class="flex justify-center gap-2">
                                                @foreach (['B' => 'bg-rose-500 border-rose-500 text-white', 'MB' => 'bg-[#e6ac27] border-[#e6ac27] text-neutral-950', 'EX' => 'bg-emerald-500 border-emerald-500 text-white'] as $val => $activoCls)
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="evaluaciones[{{ $matricula->id }}]" value="{{ $val }}" class="peer sr-only" x-model="seleccion">
                                                        <div class="w-10 h-10 flex items-center justify-center rounded-xl border text-xs font-bold transition-colors" :class="seleccion === '{{ $val }}' ? '{{ $activoCls }}' : 'bg-white dark:bg-neutral-800 border-stone-300 dark:border-neutral-700 text-neutral-600 dark:text-neutral-300'">{{ $val }}</div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="p-10 text-center text-neutral-500">No hay estudiantes activos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white cursor-pointer">Cancelar</a>
                    <button type="submit" class="px-7 py-3 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-sm font-bold transition-colors cursor-pointer">
                        Guardar Evaluaciones
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>