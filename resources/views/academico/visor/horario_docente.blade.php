<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.visor.docentes') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Directorio">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-16.03-4z"></path></svg>
                </a>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Horario de Clases: <span class="text-[#e6ac27]">{{ $docente->usuario->nombre_completo ?? 'Profesor' }}</span>
                </h2>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-200 rounded-xl font-black text-xs uppercase tracking-widest text-slate-600 shadow-sm">
                    {{ $docente->codigo_unico_persona ?? 'Docente' }}
                </span>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md transition-all print:hidden">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir / PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-3xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#FFFDF5]">
                            <th class="border border-slate-300 px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-slate-600">Hora</th>
                            @foreach($dias as $dia)
                                <th class="border border-slate-300 px-4 py-3 text-center text-[11px] font-black uppercase tracking-widest text-[#3d2c1d]">{{ $dia }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriz as $fila)
                            @php $bloque = $fila['bloque']; @endphp
                            <tr>
                                <td class="border border-slate-300 px-4 py-3 whitespace-nowrap">
                                    <span class="block font-black text-xs text-[#3d2c1d]">{{ $bloque->nombre }}</span>
                                    <span class="block text-[10px] font-bold text-slate-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                    </span>
                                </td>

                                @foreach($dias as $dia)
                                    @php $horario = $fila['dias'][$dia] ?? null; @endphp
                                    @if($horario)
                                        <td class="border border-slate-300 px-4 py-3 text-center">
                                            <span class="block font-black text-[#3d2c1d] text-xs">{{ $horario->aulaAsignaturaDocente->asignatura->nombre ?? '—' }}</span>
                                            <span class="block text-[10px] font-bold text-slate-400 mt-0.5">
                                                {{ $horario->aulaAsignaturaDocente->aula->grado->nombre ?? '' }} {{ $horario->aulaAsignaturaDocente->aula->nombre ?? '' }}
                                            </span>
                                        </td>
                                    @else
                                        <td class="border border-slate-300 px-4 py-3 text-center text-slate-300 font-bold text-xs">—</td>
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border border-slate-300 px-4 py-12 text-center text-stone-500 font-bold">
                                    Este docente aún no tiene clases programadas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>