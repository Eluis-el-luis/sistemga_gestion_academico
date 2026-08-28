<x-app-layout>

    <!-- ESTILOS QUIRÚRGICOS PARA IMPRESIÓN -->
    <style type="text/css" media="print">
        /* 1. Ocultar la barra lateral, navegación y encabezados del sistema */
        nav, aside, header, .flex-shrink-0, [x-data] > div:first-child {
            display: none !important;
        }

        /* 2. Destruir las restricciones de altura y scroll del layout para que la impresora "vea" el contenido */
        html, body, #app, main, .min-h-screen, .h-screen, .flex-1, .overflow-y-auto, .overflow-hidden {
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            background-color: white !important;
        }

        /* 3. Forzar al certificado a usar todo el ancho de la hoja */
        .max-w-\[1000px\] {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
        }

        /* 4. Obligar al navegador a imprimir los colores de fondo (Tailwind bg-gray, bg-black, etc.) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    </style>
    
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                Boletín de Calificaciones
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('academico.boletines.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md transition-all text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir / PDF
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Contenedor optimizado para impresión tamaño carta -->
    <div class="pb-12 pt-6 max-w-[1000px] mx-auto bg-white print:pt-0 print:pb-0">
        <div class="bg-white p-8 print:p-0 text-black font-sans">
            
            <!-- Encabezado del Certificado -->
            <div class="flex items-center gap-4 mb-2">
                <!-- Espacio para el logo del colegio -->
                <div class="w-16 h-16 bg-gray-200 border border-gray-400 flex items-center justify-center text-[10px] text-center">LOGO</div>
                <h1 class="flex-1 text-center font-black text-xl uppercase underline tracking-wide">
                    CERTIFICADO DE CALIFICACIONES
                </h1>
            </div>

            <p class="text-xs font-bold mb-4 leading-tight">
                La secretaria del Colegio Cristiano en Nicaragua, hace constar que las presentes calificaciones del {{ $corteActual ? $corteActual->numero . '° Corte' : 'Ciclo' }} del Ciclo Escolar {{ $matricula->anioEscolar->nombre ?? '2026' }}, fueron obtenidas por el/la alumno(a):
            </p>

            <!-- Datos del Estudiante -->
            <div class="grid grid-cols-[200px_1fr] gap-y-1 text-sm font-black mb-4 uppercase">
                <div class="text-right pr-2">CODIGO DE PERSONA:</div>
                <div>{{ $matricula->alumno->codigo_unico_persona ?? 'N/A' }}</div>
                
                <div class="text-right pr-2">NOMBRE Y APELLIDOS:</div>
                <div>{{ $matricula->alumno->nombre_completo }}</div>
                
                <div class="text-right pr-2">AÑO:</div>
                <div>{{ $matricula->aula->grado->nombre ?? '' }} {{ $matricula->aula->nombre ?? '' }}</div>
            </div>

            <!-- Tabla de Calificaciones (Estilo Excel) -->
            <table class="w-full border-collapse border-2 border-black text-xs font-bold">
                <thead class="bg-white text-center">
                    <tr>
                        <th rowspan="2" class="border-2 border-black p-1">MATERIAS</th>
                        <th colspan="2" class="border-2 border-black p-1">I CORTE</th>
                        <th colspan="2" class="border-2 border-black p-1">II CORTE</th>
                        <th colspan="2" class="border-2 border-black p-1">III CORTE</th>
                        <th colspan="2" class="border-2 border-black p-1">IV CORTE</th>
                        <th colspan="2" class="border-2 border-black p-1">NOTA FINA</th>
                    </tr>
                    <tr class="text-[10px]">
                        <th class="border-2 border-black w-8 p-1">CUA.</th>
                        <th class="border-2 border-black w-10 p-1">CUAN</th>
                        <th class="border-2 border-black w-8 p-1">CUA.</th>
                        <th class="border-2 border-black w-10 p-1">CUAN</th>
                        <th class="border-2 border-black w-8 p-1">CUA.</th>
                        <th class="border-2 border-black w-10 p-1">CUAN</th>
                        <th class="border-2 border-black w-8 p-1">CUA.</th>
                        <th class="border-2 border-black w-10 p-1">CUAN</th>
                        <th class="border-2 border-black w-8 p-1">CUA.</th>
                        <th class="border-2 border-black w-10 p-1">CUANT</th>
                    </tr>
                </thead>
                <tbody class="bg-white text-[11px] uppercase">
                    
                    @php
                        // Estructura de ejemplo que tu controlador debería enviar:
                        // $areas = [
                        //     'DESARROLLO PERSONAL, SOCIAL Y EMOCIONAL' => [
                        //          ['nombre' => 'CRECIENDO EN VALORES', 'cortes' => [1 => ['cua'=>'AS', 'cuan'=>82], 2 => ['cua'=>'AF', 'cuan'=>75], 3 => null, 4 => null]],
                        //     ],
                        // ]
                    @endphp

                    @forelse($areas ?? [] as $nombreArea => $asignaturas)
                        <!-- Cabecera de Área -->
                        <tr>
                            <td colspan="11" class="border-2 border-black px-2 py-1 font-black bg-gray-50">
                                {{ $nombreArea }}
                            </td>
                        </tr>

                        <!-- Materias del Área -->
                        @foreach($asignaturas as $asig)
                            <tr>
                                <td class="border-2 border-black px-2 py-1">{{ $asig['nombre'] }}</td>
                                
                                <!-- I Corte -->
                                <td class="border-2 border-black text-center py-1">{{ $asig['cortes'][1]['cua'] ?? '' }}</td>
                                <td class="border-2 border-black text-center py-1">{{ $asig['cortes'][1]['cuan'] ?? '' }}</td>
                                <!-- II Corte -->
                                <td class="border-2 border-black text-center py-1">{{ $asig['cortes'][2]['cua'] ?? '' }}</td>
                                <td class="border-2 border-black text-center py-1">{{ $asig['cortes'][2]['cuan'] ?? '' }}</td>
                                <!-- III Corte -->
                                <td class="border-2 border-black text-center py-1">{{ $asig['cortes'][3]['cua'] ?? '' }}</td>
                                <td class="border-2 border-black text-center py-1">{{ $asig['cortes'][3]['cuan'] ?? '' }}</td>
                                <!-- IV Corte -->
                                <td class="border-2 border-black text-center py-1">{{ $asig['cortes'][4]['cua'] ?? '' }}</td>
                                <td class="border-2 border-black text-center py-1">{{ $asig['cortes'][4]['cuan'] ?? '' }}</td>
                                <!-- Final -->
                                <td class="border-2 border-black text-center py-1">{{ $asig['final']['cua'] ?? '' }}</td>
                                <td class="border-2 border-black text-center py-1 bg-gray-100">{{ $asig['final']['cuan'] ?? '' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="11" class="text-center py-4 border-2 border-black">No hay asignaturas registradas.</td></tr>
                    @endforelse

                    <!-- Fila de Promedio General -->
                    <tr class="font-black text-[12px]">
                        <td class="border-2 border-black px-2 py-1 text-center">PROMEDIO</td>
                        <td class="border-2 border-black text-center py-1">{{ $promedios[1]['cua'] ?? '' }}</td>
                        <td class="border-2 border-black text-center py-1">{{ $promedios[1]['cuan'] ?? '' }}</td>
                        <td class="border-2 border-black text-center py-1">{{ $promedios[2]['cua'] ?? '' }}</td>
                        <td class="border-2 border-black text-center py-1">{{ $promedios[2]['cuan'] ?? '' }}</td>
                        <td class="border-2 border-black text-center py-1"></td>
                        <td class="border-2 border-black text-center py-1"></td>
                        <td class="border-2 border-black text-center py-1"></td>
                        <td class="border-2 border-black text-center py-1"></td>
                        <td class="border-2 border-black text-center py-1"></td>
                        <td class="border-2 border-black text-center py-1 bg-gray-100"></td>
                    </tr>

                    <!-- Sección OTROS -->
                    <tr class="font-black text-center bg-gray-50">
                        <td class="border-2 border-black px-2 py-1">OTROS</td>
                        <td colspan="2" class="border-2 border-black py-1">I P</td>
                        <td colspan="2" class="border-2 border-black py-1">II P</td>
                        <td colspan="2" class="border-2 border-black py-1">III P</td>
                        <td colspan="2" class="border-2 border-black py-1">IV P</td>
                        <td colspan="2" class="border-2 border-black py-1">NF</td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black px-2 py-1">AUSENCIAS INJUSTIFICADAS</td>
                        <td colspan="2" class="border-2 border-black text-center py-1">{{ $asistencia[1]['injustificadas'] ?? '0' }}</td>
                        <td colspan="2" class="border-2 border-black text-center py-1">{{ $asistencia[2]['injustificadas'] ?? '0' }}</td>
                        <td colspan="2" class="border-2 border-black text-center py-1"></td>
                        <td colspan="2" class="border-2 border-black text-center py-1"></td>
                        <td colspan="2" class="border-2 border-black text-center py-1"></td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black px-2 py-1">AUSENCIAS JUSTIFICADAS</td>
                        <td colspan="2" class="border-2 border-black text-center py-1">{{ $asistencia[1]['justificadas'] ?? '0' }}</td>
                        <td colspan="2" class="border-2 border-black text-center py-1">{{ $asistencia[2]['justificadas'] ?? '0' }}</td>
                        <td colspan="2" class="border-2 border-black text-center py-1"></td>
                        <td colspan="2" class="border-2 border-black text-center py-1"></td>
                        <td colspan="2" class="border-2 border-black text-center py-1"></td>
                    </tr>
                    <tr>
                        <td class="border-2 border-black px-2 py-1">COMPROMISO DE PADRES DE FAMILIA</td>
                        <td colspan="2" class="border-2 border-black text-center py-1">{{ $compromiso[1] ?? 'MB' }}</td>
                        <td colspan="2" class="border-2 border-black text-center py-1">{{ $compromiso[2] ?? 'MB' }}</td>
                        <td colspan="2" class="border-2 border-black text-center py-1"></td>
                        <td colspan="2" class="border-2 border-black text-center py-1"></td>
                        <td colspan="2" class="border-2 border-black text-center py-1"></td>
                    </tr>

                    <!-- Firma Profesor -->
                    <tr>
                        <td colspan="11" class="border-2 border-black px-2 py-1.5 font-black text-center uppercase tracking-wide">
                            PROFESOR GUIA : {{ $matricula->aula->docenteGuia->usuario->nombre_completo ?? '' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Firma Directora (Footer Impresión) -->
            <div class="mt-20 flex justify-center print:mt-16">
                <div class="text-center">
                    <div class="border-t-2 border-black w-64 pt-1 font-black text-sm uppercase">
                        DIRECTORA
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>