{{-- resources/views/academico/visor/horario_docente.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.visor.docentes') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                </a>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">
                    Horario de Clases: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $docente->usuario?->nombre_completo ?? 'Profesor' }}</span>
                </h2>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="px-3.5 py-2 bg-stone-100 dark:bg-neutral-800 rounded-xl font-semibold text-xs uppercase text-neutral-700 dark:text-neutral-200">{{ $docente->codigo_unico_persona ?? 'Docente' }}</span>
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold text-xs uppercase rounded-xl cursor-pointer">Imprimir / PDF</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-[#FFFDF8] dark:bg-neutral-800/70">
                                <th class="border border-stone-200 dark:border-neutral-800 px-3.5 py-3 text-left text-xs font-semibold uppercase text-neutral-600 dark:text-neutral-300 w-36">Hora</th>
                                @foreach ($dias as $dia)
                                    <th class="border border-stone-200 dark:border-neutral-800 px-3 py-3 text-center text-xs font-semibold uppercase text-neutral-900 dark:text-white">{{ $dia }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-neutral-800 dark:text-neutral-200">
                            @forelse ($matriz as $fila)
                                @php $bloque = $fila['bloque']; @endphp
                                <tr>
                                    <td class="border border-stone-200 dark:border-neutral-800 px-3.5 py-2.5 whitespace-nowrap">
                                        <span class="block font-semibold text-xs text-neutral-900 dark:text-white">{{ $bloque->nombre }}</span>
                                        <span class="block text-[11px] text-neutral-500 dark:text-neutral-400">{{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}</span>
                                    </td>
                                    @foreach ($dias as $dia)
                                        @php $horario = $fila['dias'][$dia] ?? null; @endphp
                                        <td class="border border-stone-200 dark:border-neutral-800 px-2.5 py-2.5 text-center">
                                            @if ($horario)
                                                <span class="block font-semibold text-xs text-neutral-900 dark:text-white">{{ $horario->aulaAsignaturaDocente?->asignatura?->nombre ?? '—' }}</span>
                                                <span class="block text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $horario->aulaAsignaturaDocente?->aula?->grado?->nombre ?? '' }} {{ $horario->aulaAsignaturaDocente?->aula?->nombre ?? '' }}</span>
                                            @else
                                                <span class="text-neutral-400">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr><td colspan="6" class="border border-stone-200 dark:border-neutral-800 px-4 py-10 text-center text-neutral-500">Este docente aún no tiene clases programadas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>