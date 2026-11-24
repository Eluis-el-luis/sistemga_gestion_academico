{{-- resources/views/academico/boletin/bandeja.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Bandeja de Impresión</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Boletines autorizados por docentes guías.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h3 class="font-bold text-neutral-900 dark:text-white text-base">Seleccionar Corte Evaluativo</h3>
                <form method="GET" action="{{ route('academico.boletin.bandeja') }}" class="w-full sm:w-64">
                    <select name="corte_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-semibold cursor-pointer">
                        @foreach ($cortes as $corte)
                            <option value="{{ $corte->id }}" @selected($corte->id == $corteActivo)>{{ $corte->numero }}° Parcial - {{ $anioActivo?->nombre }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border-collapse text-sm">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="p-4">Aula y Grado</th>
                                <th class="p-4">Docente Guía</th>
                                <th class="p-4 text-center">Estado</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($aulas as $aula)
                                <tr>
                                    <td class="p-4 font-semibold">{{ $aula->grado?->nombre ?? 'Grado' }} - "{{ $aula->nombre }}"</td>
                                    <td class="p-4 text-neutral-600 dark:text-neutral-300">{{ $aula->docenteGuia?->usuario?->nombre_completo ?? 'Sin asignar' }}</td>
                                    <td class="p-4 text-center"><span class="px-3 py-1 rounded-lg text-xs font-semibold bg-stone-100 dark:bg-neutral-800">{{ $aula->estado_impresion }}</span></td>
                                    <td class="p-4 text-right">
                                        @if (in_array($aula->estado_impresion, ['Autorizado', 'Impreso'], true))
                                            <div class="flex flex-wrap justify-end gap-2">
                                                <a href="{{ route('academico.boletines.pdf.masivo', ['aula_id' => $aula->id, 'corte_evaluativo_id' => $corteActivo, 'tipo' => 'boletin']) }}" class="px-3 py-1.5 bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 rounded-xl text-xs font-bold cursor-pointer">PDF Boletines</a>
                                                <a href="{{ route('academico.boletines.pdf.masivo', ['aula_id' => $aula->id, 'corte_evaluativo_id' => $corteActivo, 'tipo' => 'constancia']) }}" class="px-3 py-1.5 bg-stone-200 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 rounded-xl text-xs font-bold cursor-pointer">PDF Constancias</a>
                                                <a href="{{ route('academico.boletines.pdf.masivo', ['aula_id' => $aula->id, 'corte_evaluativo_id' => $corteActivo, 'tipo' => 'certificado']) }}" class="px-3 py-1.5 bg-[#e6ac27] text-neutral-950 rounded-xl text-xs font-bold cursor-pointer">PDF Certificados</a>
                                            </div>
                                        @else
                                            <span class="text-xs text-neutral-400">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-10 text-center text-neutral-500">No hay aulas registradas en el ciclo activo.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>