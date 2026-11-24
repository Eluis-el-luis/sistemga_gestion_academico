{{-- resources/views/academico/boletin/show.blade.php --}}
<x-app-layout>
    @php
        $numeroCorteActual = $corteActual->numero ?? 1;
        $faltanNotas = false;
        foreach ($areas ?? [] as $asignaturasArea) {
            foreach ($asignaturasArea as $asig) {
                if (empty($asig['cortes'][$numeroCorteActual]['cuan'])) {
                    $faltanNotas = true;
                    break 2;
                }
            }
        }
    @endphp
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Boletín de Calificaciones</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('academico.boletines.index') }}" class="px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 cursor-pointer">Volver</a>
                <a href="{{ route('academico.boletines.constancia', $matricula->id) }}" class="px-3.5 py-2 bg-stone-100 dark:bg-neutral-800 rounded-xl text-xs font-semibold cursor-pointer">Constancia</a>
                <a href="{{ route('academico.boletines.pdf.boletin', $matricula->id) }}" class="px-3.5 py-2 bg-neutral-900 dark:bg-neutral-800 text-white rounded-xl text-xs font-bold cursor-pointer">PDF Boletín</a>
                @if ($faltanNotas)
                    <button type="button" disabled class="px-4 py-2 bg-stone-300 text-neutral-600 rounded-xl text-xs font-bold cursor-not-allowed">Impresión Bloqueada</button>
                @else
                    <button type="button" onclick="window.print()" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-xs font-bold cursor-pointer">Imprimir</button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-[820px] mx-auto px-4">
        <div class="bg-white p-6 rounded-3xl border border-stone-200 text-neutral-900 text-xs">
            <div class="text-center mb-4">
                <h1 class="font-bold text-base uppercase underline">Certificado de Calificaciones</h1>
                <p class="text-[11px] text-neutral-600 mt-1">{{ $numeroCorteActual }}° Corte · Ciclo Escolar {{ $matricula->anioEscolar?->nombre ?? '2026' }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 font-semibold mb-4 uppercase text-[11px]">
                <div>CUP: {{ $matricula->alumno?->codigo_unico_persona ?? 'N/A' }}</div>
                <div>Estudiante: {{ $matricula->alumno?->nombre_completo }}</div>
                <div>Grado / Sección: {{ $matricula->aula?->grado?->nombre ?? '' }} "{{ $matricula->aula?->nombre ?? '' }}"</div>
                <div>Guía: {{ $matricula->aula?->docenteGuia?->usuario?->nombre_completo ?? '' }}</div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-neutral-800 text-[10px]">
                    <thead>
                        <tr class="text-center font-bold">
                            <th rowspan="2" class="border border-neutral-800 p-1 text-left">Materias</th>
                            <th colspan="2" class="border border-neutral-800 p-1">I Corte</th>
                            <th colspan="2" class="border border-neutral-800 p-1">II Corte</th>
                            <th colspan="2" class="border border-neutral-800 p-1">III Corte</th>
                            <th colspan="2" class="border border-neutral-800 p-1">IV Corte</th>
                            <th colspan="2" class="border border-neutral-800 p-1 bg-stone-100">Final</th>
                        </tr>
                        <tr>
                            @foreach (range(1, 5) as $col)
                                <th class="border border-neutral-800 p-0.5">Cua</th>
                                <th class="border border-neutral-800 p-0.5">Cuan</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($areas ?? [] as $nombreArea => $asignaturasArea)
                            <tr class="bg-stone-50 font-bold"><td colspan="11" class="border border-neutral-800 px-2 py-1">{{ $nombreArea }}</td></tr>
                            @foreach ($asignaturasArea as $asig)
                                <tr>
                                    <td class="border border-neutral-800 px-2 py-1">{{ $asig['nombre'] }}</td>
                                    @foreach ([1, 2, 3, 4] as $numCorte)
                                        <td class="border border-neutral-800 text-center p-1">{{ $asig['cortes'][$numCorte]['cua'] ?? '' }}</td>
                                        <td class="border border-neutral-800 text-center p-1">{{ $asig['cortes'][$numCorte]['cuan'] ?? '' }}</td>
                                    @endforeach
                                    <td class="border border-neutral-800 text-center p-1 font-bold bg-stone-100">{{ $numeroCorteActual == 4 ? ($asig['final']['cua'] ?? '') : '' }}</td>
                                    <td class="border border-neutral-800 text-center p-1 font-bold bg-stone-100">{{ $numeroCorteActual == 4 ? ($asig['final']['cuan'] ?? '') : '' }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="11" class="border border-neutral-800 text-center p-3">Sin asignaturas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>