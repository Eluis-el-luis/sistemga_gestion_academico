<x-app-layout>

    <style type="text/css">
        @media print {
            @page { size: letter portrait; margin: 5mm; }
            nav, aside, header, .print-hidden { display: none !important; }
            html, body, #app, main { background-color: white !important; margin: 0 !important; padding: 0 !important; }
            
            /* Mantiene el tamaño de media hoja para un solo boletín */
            .boletin-mitad {
                height: 134mm !important; 
                overflow: hidden !important;
                page-break-inside: avoid !important;
            }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>

    @php
        $numeroCorteActual = $corteActual->numero ?? 1;
        $faltanNotas = false;

        foreach ($areas ?? [] as $asignaturas) {
            foreach ($asignaturas as $asig) {
                if (empty($asig['cortes'][$numeroCorteActual]['cuan'])) {
                    $faltanNotas = true;
                    break 2;
                }
            }
        }
    @endphp
    
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print-hidden">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                Boletín de Calificaciones
            </h2>
            <div class="flex gap-3 w-full sm:w-auto">
                <a href="{{ route('academico.boletines.index') }}" class="text-slate-500 hover:text-slate-700 font-bold text-sm flex items-center gap-1 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                
                @if($faltanNotas)
                    <button disabled class="bg-slate-300 text-white font-black px-4 py-2 rounded-xl shadow-sm text-sm flex items-center gap-2 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Impresión Bloqueada
                    </button>
                @else
                    <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md transition-all text-sm flex items-center gap-2 transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Imprimir / PDF
                    </button>
                @endif
            </div>
        </div>

        @if($faltanNotas)
            <div class="mt-4 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl shadow-sm flex items-center gap-3 font-medium print-hidden text-sm">
                Atención: No se puede generar el boletín oficial porque existen asignaturas sin calificar en el {{ $numeroCorteActual }}° Corte.
            </div>
        @endif
    </x-slot>

    <div class="pb-12 pt-6 max-w-[800px] mx-auto print:max-w-full print:pt-0 print:pb-0">
        
        <!-- Contenedor Único -->
        <div class="boletin-mitad bg-white p-6 print:p-2 text-black font-sans">
            
            <div class="flex items-center gap-4 mb-2">
                <div class="w-16 h-16 shrink-0">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo Colegio" class="w-full h-full object-contain">
                </div>
                <h1 class="flex-1 text-center font-black text-lg md:text-xl uppercase underline tracking-wide">
                    CERTIFICADO DE CALIFICACIONES
                </h1>
            </div>

            <p class="text-[10px] font-bold mb-3 leading-tight text-justify">
                La secretaria del Colegio Cristiano en Nicaragua, hace constar que las presentes calificaciones del {{ $numeroCorteActual }}° Corte del Ciclo Escolar {{ $matricula->anioEscolar->nombre ?? '2026' }}, fueron obtenidas por el/la alumno(a):
            </p>

            <div class="grid grid-cols-[150px_1fr] gap-y-0.5 text-[11px] font-black mb-3 uppercase tracking-wide">
                <div class="text-right pr-2">CÓDIGO DE PERSONA:</div>
                <div>{{ $matricula->alumno->codigo_unico_persona ?? 'N/A' }}</div>
                
                <div class="text-right pr-2">NOMBRE Y APELLIDOS:</div>
                <div>{{ $matricula->alumno->nombre_completo }}</div>
                
                <div class="text-right pr-2">AÑO:</div>
                <div>{{ $matricula->aula->grado->nombre ?? '' }} - SECCIÓN "{{ $matricula->aula->nombre ?? '' }}"</div>
            </div>

            <table class="w-full border-collapse border border-black text-[9px] font-bold">
                <thead class="bg-white text-center">
                    <tr>
                        <th rowspan="2" class="border border-black p-0.5">MATERIAS</th>
                        <th colspan="2" class="border border-black p-0.5">I CORTE</th>
                        <th colspan="2" class="border border-black p-0.5">II CORTE</th>
                        <th colspan="2" class="border border-black p-0.5">III CORTE</th>
                        <th colspan="2" class="border border-black p-0.5">IV CORTE</th>
                        <th colspan="2" class="border border-black p-0.5 bg-slate-100">NOTA FINAL</th>
                    </tr>
                    <tr class="text-[8px]">
                        <th class="border border-black w-6 p-0.5">CUA.</th><th class="border border-black w-7 p-0.5">CUAN</th>
                        <th class="border border-black w-6 p-0.5">CUA.</th><th class="border border-black w-7 p-0.5">CUAN</th>
                        <th class="border border-black w-6 p-0.5">CUA.</th><th class="border border-black w-7 p-0.5">CUAN</th>
                        <th class="border border-black w-6 p-0.5">CUA.</th><th class="border border-black w-7 p-0.5">CUAN</th>
                        <th class="border border-black w-6 p-0.5 bg-slate-100">CUA.</th><th class="border border-black w-7 p-0.5 bg-slate-100">CUANT</th>
                    </tr>
                </thead>
                <tbody class="bg-white uppercase text-[9px]">
                    @forelse($areas ?? [] as $nombreArea => $asignaturas)
                        <tr>
                            <td colspan="11" class="border border-black px-1.5 py-0.5 font-black bg-slate-50 tracking-wider">
                                {{ $nombreArea }}
                            </td>
                        </tr>
                        @foreach($asignaturas as $asig)
                            <tr>
                                <td class="border border-black px-1.5 py-0.5">{{ $asig['nombre'] }}</td>
                                
                                <td class="border border-black text-center py-0.5">{{ $asig['cortes'][1]['cua'] ?? '' }}</td>
                                <td class="border border-black text-center py-0.5">{{ $asig['cortes'][1]['cuan'] ?? '' }}</td>
                                
                                <td class="border border-black text-center py-0.5">{{ $asig['cortes'][2]['cua'] ?? '' }}</td>
                                <td class="border border-black text-center py-0.5">{{ $asig['cortes'][2]['cuan'] ?? '' }}</td>
                                
                                <td class="border border-black text-center py-0.5">{{ $asig['cortes'][3]['cua'] ?? '' }}</td>
                                <td class="border border-black text-center py-0.5">{{ $asig['cortes'][3]['cuan'] ?? '' }}</td>
                                
                                <td class="border border-black text-center py-0.5">{{ $asig['cortes'][4]['cua'] ?? '' }}</td>
                                <td class="border border-black text-center py-0.5">{{ $asig['cortes'][4]['cuan'] ?? '' }}</td>
                                
                                <td class="border border-black text-center py-0.5 bg-slate-100 font-black">{{ $numeroCorteActual == 4 ? ($asig['final']['cua'] ?? '') : '' }}</td>
                                <td class="border border-black text-center py-0.5 bg-slate-100 font-black">{{ $numeroCorteActual == 4 ? ($asig['final']['cuan'] ?? '') : '' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="11" class="text-center py-2 border border-black">No hay asignaturas.</td></tr>
                    @endforelse

                    <!-- Promedio General -->
                    <tr class="font-black">
                        <td class="border border-black px-1.5 py-0.5 text-center">PROMEDIO</td>
                        <td class="border border-black text-center py-0.5">{{ $promedios[1]['cua'] ?? '' }}</td><td class="border border-black text-center py-0.5">{{ $promedios[1]['cuan'] ?? '' }}</td>
                        <td class="border border-black text-center py-0.5">{{ $promedios[2]['cua'] ?? '' }}</td><td class="border border-black text-center py-0.5">{{ $promedios[2]['cuan'] ?? '' }}</td>
                        <td class="border border-black text-center py-0.5">{{ $promedios[3]['cua'] ?? '' }}</td><td class="border border-black text-center py-0.5">{{ $promedios[3]['cuan'] ?? '' }}</td>
                        <td class="border border-black text-center py-0.5">{{ $promedios[4]['cua'] ?? '' }}</td><td class="border border-black text-center py-0.5">{{ $promedios[4]['cuan'] ?? '' }}</td>
                        <td class="border border-black text-center py-0.5 bg-slate-100"></td><td class="border border-black text-center py-0.5 bg-slate-100"></td>
                    </tr>

                    <!-- Sección Otros (Con lógica de ocultamiento futuro) -->
                    <tr class="font-black text-center bg-slate-50 text-[8px]">
                        <td class="border border-black px-1.5 py-0.5">OTROS</td>
                        <td colspan="2" class="border border-black py-0.5">I P</td><td colspan="2" class="border border-black py-0.5">II P</td>
                        <td colspan="2" class="border border-black py-0.5">III P</td><td colspan="2" class="border border-black py-0.5">IV P</td>
                        <td colspan="2" class="border border-black py-0.5 bg-slate-100">NF</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-1.5 py-0.5">AUSENCIAS INJUSTIFICADAS</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $numeroCorteActual >= 1 ? ($asistencia[1]['injustificadas'] ?? '0') : '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $numeroCorteActual >= 2 ? ($asistencia[2]['injustificadas'] ?? '0') : '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $numeroCorteActual >= 3 ? ($asistencia[3]['injustificadas'] ?? '0') : '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $numeroCorteActual >= 4 ? ($asistencia[4]['injustificadas'] ?? '0') : '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5 bg-slate-100"></td>
                    </tr>
                    <tr>
                        <td class="border border-black px-1.5 py-0.5">AUSENCIAS JUSTIFICADAS</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $numeroCorteActual >= 1 ? ($asistencia[1]['justificadas'] ?? '0') : '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $numeroCorteActual >= 2 ? ($asistencia[2]['justificadas'] ?? '0') : '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $numeroCorteActual >= 3 ? ($asistencia[3]['justificadas'] ?? '0') : '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $numeroCorteActual >= 4 ? ($asistencia[4]['justificadas'] ?? '0') : '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5 bg-slate-100"></td>
                    </tr>
                    <tr>
                        <td class="border border-black px-1.5 py-0.5">COMPROMISO DE PADRES DE FAMILIA</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $compromiso[1] ?? '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $compromiso[2] ?? '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $compromiso[3] ?? '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5">{{ $compromiso[4] ?? '' }}</td>
                        <td colspan="2" class="border border-black text-center py-0.5 bg-slate-100"></td>
                    </tr>

                    <tr>
                        <td colspan="11" class="border border-black px-1.5 py-1 font-black text-center uppercase tracking-wide">
                            PROFESOR GUÍA: {{ $matricula->aula->docenteGuia->usuario->nombre_completo ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-8 flex justify-center">
                <div class="text-center">
                    <div class="border-t border-black w-48 pt-0.5 font-black text-[10px] uppercase">
                        DIRECTORA
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>