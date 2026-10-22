<x-app-layout>
    <style>
        @media print {
            body * { visibility: hidden; }
            #zona-impresion, #zona-impresion * { visibility: visible; }
            
            #zona-impresion { 
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 100%; 
                /* Eliminamos la caja y las sombras para usar el 100% de la hoja real */
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
            }
            
            @page { size: portrait; margin: 20mm 15mm; } 
            .no-print { display: none !important; }
            
            /* CRÍTICO: Fuerza al navegador a imprimir los colores de fondo (Azul, verdes, rojos) */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <a href="{{ route('academico.reportes.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors text-sm font-bold mr-2">← Volver</a>
                Reporte MINED - Consolidado General
            </h2>
            <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-amber-500 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Documento Oficial
            </button>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Filtro -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print">
            <form method="GET" action="{{ route('academico.reportes.mined') }}" class="flex items-end gap-4">
                <div class="w-64">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar (Ciclo)</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-[#e6ac27]">
                        @foreach($anios as $an)
                            <option value="{{ $an->id }}" @selected($an->id == $anioId)>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- DOCUMENTO OFICIAL -->
        <div id="zona-impresion" class="bg-white p-8 md:p-12 rounded-3xl shadow-sm border border-slate-200">
            
            <!-- Encabezado Institucional -->
            <div class="flex justify-between items-center mb-10 border-b-4 border-blue-800 pb-6">
                <div class="text-xs font-bold text-black w-1/3 space-y-1">
                    <div><span class="text-blue-800">CÓDIGO ESTABLECIMIENTO:</span> {{ $infoCentro['cod_establecimiento'] ?? '27974' }}</div>
                    <div><span class="text-blue-800">CÓDIGO DE CENTRO:</span> {{ $infoCentro['cod_centro'] ?? '27760' }}</div>
                    <div><span class="text-blue-800">DEPARTAMENTO:</span> LEÓN</div>
                </div>

                <div class="text-center w-1/3">
                    <img src="{{ asset('img/logos/mined_izq.png') }}" alt="Gobierno" class="h-16 object-contain mx-auto mb-3">
                    <h1 class="text-sm font-black uppercase text-slate-900">Ministerio de Educación</h1>
                    <h2 class="text-xs font-bold uppercase text-slate-700">Delegación Municipal León</h2>
                </div>

                <div class="w-1/3 text-right text-xs font-bold text-black space-y-1">
                    <img src="{{ asset('img/logos/mined_der.png') }}" alt="Campaña" class="h-16 object-contain ml-auto mb-3">
                    <div><span class="text-blue-800">FECHA:</span> {{ date('d/m/Y') }}</div>
                    <div><span class="text-blue-800">CICLO ESCOLAR:</span> {{ $anioActual }}</div>
                </div>
            </div>

            <!-- Título del Reporte -->
            <div class="text-center mb-10">
                <h3 class="text-xl font-black uppercase text-black tracking-wide">Reporte Estadístico de Rendimiento y Matrícula</h3>
                <p class="text-base font-bold text-slate-800 mt-2">Colegio Cristiano en Nicaragua</p>
            </div>

            <!-- Tabla Consolidada (Aumentamos tamaños de texto y padding) -->
            <table class="w-full text-center border-collapse border-2 border-black text-sm mb-16">
                <thead class="bg-blue-800 text-white font-black uppercase text-[11px] tracking-wider">
                    <tr>
                        <th rowspan="2" class="border-2 border-black px-3 py-4 align-middle w-48">Modalidad</th>
                        <th colspan="2" class="border-2 border-black px-2 py-2">Matrícula Inicial (MI)</th>
                        <th colspan="2" class="border-2 border-black px-2 py-2">Matrícula Actual (MA)</th>
                        <th colspan="2" class="border-2 border-black px-2 py-2">Retirados</th>
                        <th rowspan="2" class="border-2 border-black px-2 py-4 align-middle">% Retención</th>
                        <th rowspan="2" class="border-2 border-black px-2 py-4 align-middle">Promedio Global</th>
                    </tr>
                    <tr>
                        <th class="border-2 border-black px-2 py-2 w-16 bg-blue-900">AS</th>
                        <th class="border-2 border-black px-2 py-2 w-16 bg-blue-900">F</th>
                        <th class="border-2 border-black px-2 py-2 w-16 bg-blue-900">AS</th>
                        <th class="border-2 border-black px-2 py-2 w-16 bg-blue-900">F</th>
                        <th class="border-2 border-black px-2 py-2 w-16 bg-blue-900">AS</th>
                        <th class="border-2 border-black px-2 py-2 w-16 bg-blue-900">F</th>
                    </tr>
                </thead>
                <tbody class="text-black font-medium">
                    @forelse($filas ?? [] as $fila)
                        <tr>
                            <td class="border-2 border-black px-3 py-3 font-bold text-left bg-slate-50">{{ $fila['modalidad'] }}</td>
                            <td class="border-2 border-black px-2 py-3">{{ $fila['mi_as'] }}</td>
                            <td class="border-2 border-black px-2 py-3">{{ $fila['mi_f'] }}</td>
                            <td class="border-2 border-black px-2 py-3 font-bold">{{ $fila['ma_as'] }}</td>
                            <td class="border-2 border-black px-2 py-3 font-bold">{{ $fila['ma_f'] }}</td>
                            <td class="border-2 border-black px-2 py-3 text-rose-700 font-bold bg-rose-50">{{ $fila['ret_as'] }}</td>
                            <td class="border-2 border-black px-2 py-3 text-rose-700 font-bold bg-rose-50">{{ $fila['ret_f'] }}</td>
                            <td class="border-2 border-black px-2 py-3 font-black bg-emerald-50 text-emerald-800">{{ $fila['retencion'] }}%</td>
                            <td class="border-2 border-black px-2 py-3 font-black {{ $fila['promedio'] < 60 ? 'text-rose-700 bg-rose-50' : 'text-slate-900' }}">{{ $fila['promedio'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="border-2 border-black px-4 py-12 text-center text-slate-500 text-base">
                                No hay datos registrados para el ciclo escolar seleccionado.
                            </td>
                        </tr>
                    @endforelse

                    <!-- Fila de Totales -->
                    @if(isset($totales) && !empty($filas))
                        <tr class="font-black bg-slate-200 text-black uppercase text-sm">
                            <td class="border-2 border-black px-3 py-4 text-right pr-4">TOTAL GENERAL</td>
                            <td class="border-2 border-black px-2 py-4">{{ $totales['mi_as'] }}</td>
                            <td class="border-2 border-black px-2 py-4">{{ $totales['mi_f'] }}</td>
                            <td class="border-2 border-black px-2 py-4 text-base">{{ $totales['ma_as'] }}</td>
                            <td class="border-2 border-black px-2 py-4 text-base">{{ $totales['ma_f'] }}</td>
                            <td class="border-2 border-black px-2 py-4 text-rose-700">{{ $totales['ret_as'] }}</td>
                            <td class="border-2 border-black px-2 py-4 text-rose-700">{{ $totales['ret_f'] }}</td>
                            <td class="border-2 border-black px-2 py-4 text-emerald-800 text-base">{{ $totales['retencion'] }}%</td>
                            <td class="border-2 border-black px-2 py-4 text-base">{{ $totales['promedio'] }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- Área de Firmas -->
            <div class="mt-32 grid grid-cols-2 gap-12 text-center text-sm font-bold text-black">
                <div>
                    <div class="w-64 mx-auto border-t-2 border-black pt-2">
                        Firma del(a) Director(a)<br>
                        <span class="text-xs font-normal">Colegio Cristiano en Nicaragua</span>
                    </div>
                </div>
                <div>
                    <div class="w-64 mx-auto border-t-2 border-black pt-2">
                        Sello del Centro Educativo
                    </div>
                </div>
            </div>

            <div class="mt-16 text-center text-[10px] text-slate-500 font-medium">
                Documento estadístico generado por el Sistema de Gestión Académica — Sujeto a validación por la Delegación MINED León.
            </div>

        </div>
    </div>
</x-app-layout>