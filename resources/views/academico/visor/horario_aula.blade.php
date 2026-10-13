<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 print:hidden">
            <div class="flex items-center gap-3">
                @php
                    $rutaVolver = ($desdeGuia ?? false)
                        ? route('dashboard', ['tab' => 'Docente Guia'])
                        : route('academico.visor.aulas');
                    $tituloVolver = ($desdeGuia ?? false)
                        ? 'Volver al Módulo Docente Guía'
                        : 'Volver al Directorio';
                @endphp
                <a href="{{ $rutaVolver }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="{{ $tituloVolver }}">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                        Horario: <span class="text-[#e6ac27]">{{ $aula->grado->nombre }} - Sección "{{ $aula->nombre }}"</span>
                    </h2>
                    @if($aula->docenteGuia && $aula->docenteGuia->usuario)
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                            Docente Guía: {{ $aula->docenteGuia->usuario->nombre_completo }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-200 rounded-xl font-black text-xs uppercase tracking-widest text-slate-600 shadow-sm">
                    Turno: {{ ucfirst($aula->turno) }}
                </span>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir / PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-none">

        <!-- COMPONENTE CENTRALIZADO DE IMPRESIÓN -->
        @php
            $detalleImpresion = 'Turno: <span class="text-[#3d2c1d]">' . ucfirst($aula->turno) . '</span>';
            if ($aula->docenteGuia && $aula->docenteGuia->usuario) {
                $detalleImpresion .= ' <span class="mx-1 text-slate-300">|</span> Guía: <span class="text-[#3d2c1d]">' . e($aula->docenteGuia->usuario->nombre_completo) . '</span>';
            }
        @endphp
        <x-membrete-impresion 
            titulo="Horario Oficial de Clases"
            subtitulo="Ciclo Escolar {{ $aula->anioEscolar->nombre ?? date('Y') }}"
            badge="{{ $aula->grado->nombre }} - Sección &quot;{{ $aula->nombre }}&quot;"
            :detalle="$detalleImpresion"
            orientacion="landscape"
        />

        <!-- TABLA DE HORARIO CON COLORES INSTITUCIONALES -->
        <div class="bg-white shadow-sm print:shadow-none rounded-3xl print:rounded-2xl border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
            <div class="overflow-x-auto print:overflow-visible">
                <table class="w-full border-collapse text-sm print:text-xs table-fixed">
                    <thead>
                        <tr class="bg-[#3d2c1d] text-white">
                            <th class="border border-[#3d2c1d] px-4 py-3 print:py-2.5 text-left text-[11px] font-black uppercase tracking-widest text-[#e6ac27] w-36">Hora</th>
                            @foreach($dias as $dia)
                                <th class="border border-[#3d2c1d] px-3 py-3 print:py-2.5 text-center text-[11px] font-black uppercase tracking-widest text-white">{{ $dia }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriz as $fila)
                            @php $bloque = $fila['bloque']; @endphp
                            <tr class="{{ $bloque->es_recreo ? 'bg-[#FFFDF5]' : 'bg-white' }}">
                                <td class="border border-slate-200 px-3 py-3 print:py-2 whitespace-nowrap {{ $bloque->es_recreo ? 'bg-amber-50/80' : 'bg-slate-50/60' }}">
                                    <span class="block font-black text-xs {{ $bloque->es_recreo ? 'text-amber-700' : 'text-[#3d2c1d]' }}">{{ $bloque->nombre }}</span>
                                    <span class="block text-[10px] font-bold text-slate-500 mt-0.5">
                                        {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                    </span>
                                </td>

                                @if($bloque->es_recreo)
                                    <td colspan="{{ count($dias) }}" class="border border-slate-200 px-4 py-2 text-center bg-amber-50/70">
                                        <span class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-700">— {{ $bloque->nombre }} —</span>
                                    </td>
                                @else
                                    @foreach($dias as $dia)
                                        @php $horario = $fila['dias'][$dia] ?? null; @endphp
                                        @if($horario)
                                            <td class="border border-slate-200 px-2 py-2.5 print:py-2 text-center align-middle">
                                                <span class="block font-black text-[#3d2c1d] text-xs leading-tight">{{ $horario->aulaAsignaturaDocente->asignatura->nombre ?? '—' }}</span>
                                                <span class="inline-block text-[10px] font-bold text-amber-700 bg-amber-50/80 px-2 py-0.5 rounded-md mt-1">
                                                    Prof. {{ $horario->aulaAsignaturaDocente->docente ? \Str::words($horario->aulaAsignaturaDocente->docente->usuario->nombre_completo, 2, '') : '—' }}
                                                </span>
                                            </td>
                                        @else
                                            <td class="border border-slate-200 px-2 py-2.5 print:py-2 text-center text-slate-300 font-bold text-xs">—</td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border border-slate-200 px-4 py-12 text-center text-stone-500 font-bold">
                                    No hay bloques de horario definidos para este aula.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>