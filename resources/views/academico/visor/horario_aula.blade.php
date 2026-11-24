{{-- resources/views/academico/visor/horario_aula.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <div class="flex items-center gap-3">
                @php
                    $rutaVolver = ($desdeGuia ?? false) ? route('dashboard', ['tab' => 'Docente Guia']) : route('academico.visor.aulas');
                @endphp
                <a href="{{ $rutaVolver }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white">
                        Horario: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $aula->grado?->nombre }} - Sección "{{ $aula->nombre }}"</span>
                    </h2>
                    @if ($aula->docenteGuia?->usuario)
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Guía: {{ $aula->docenteGuia->usuario->nombre_completo }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="px-3.5 py-2 bg-stone-100 dark:bg-neutral-800 rounded-xl font-semibold text-xs uppercase text-neutral-700 dark:text-neutral-200">Turno: {{ ucfirst((string) $aula->turno) }}</span>
                <button type="button" onclick="window.print()" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold text-xs uppercase rounded-xl cursor-pointer">Imprimir / PDF</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @php
                $detalleImpresion = 'Turno: ' . ucfirst((string) $aula->turno);
                if ($aula->docenteGuia?->usuario) {
                    $detalleImpresion .= ' · Guía: ' . e($aula->docenteGuia->usuario->nombre_completo);
                }
            @endphp
            <x-membrete-impresion titulo="Horario Oficial de Clases" subtitulo="Ciclo Escolar {{ $aula->anioEscolar?->nombre ?? date('Y') }}" badge="{{ $aula->grado?->nombre }} - Sección \"{{ $aula->nombre }}\"" :detalle="$detalleImpresion" orientacion="landscape" />

            <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-xs sm:text-sm">
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
                                <tr class="{{ $bloque->es_recreo ? 'bg-stone-50/80 dark:bg-neutral-800/40' : '' }}">
                                    <td class="border border-stone-200 dark:border-neutral-800 px-3.5 py-2.5 whitespace-nowrap">
                                        <span class="block font-semibold text-xs text-neutral-900 dark:text-white">{{ $bloque->nombre }}</span>
                                        <span class="block text-[11px] text-neutral-500 dark:text-neutral-400">{{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}</span>
                                    </td>
                                    @if ($bloque->es_recreo)
                                        <td colspan="{{ count($dias) }}" class="border border-stone-200 dark:border-neutral-800 px-4 py-2 text-center text-xs font-semibold text-neutral-400 uppercase tracking-widest">{{ $bloque->nombre }}</td>
                                    @else
                                        @foreach ($dias as $dia)
                                            @php $horario = $fila['dias'][$dia] ?? null; @endphp
                                            <td class="border border-stone-200 dark:border-neutral-800 px-2.5 py-2.5 text-center">
                                                @if ($horario)
                                                    <span class="block font-semibold text-xs text-neutral-900 dark:text-white">{{ $horario->aulaAsignaturaDocente?->asignatura?->nombre ?? '—' }}</span>
                                                    <span class="block text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">Prof. {{ $horario->aulaAsignaturaDocente?->docente?->usuario ? \Illuminate\Support\Str::words($horario->aulaAsignaturaDocente->docente->usuario->nombre_completo, 2, '') : '—' }}</span>
                                                @else
                                                    <span class="text-neutral-400">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                            @empty
                                <tr><td colspan="6" class="border border-stone-200 dark:border-neutral-800 px-4 py-10 text-center text-neutral-500">No hay bloques de horario definidos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>