<x-app-layout>
    <!-- Importar Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @media print {
            body * { visibility: hidden; }
            #zona-impresion, #zona-impresion * { visibility: visible; }
            #zona-impresion { position: absolute; left: 0; top: 0; width: 100%; }
            @page { size: landscape; margin: 5mm; }
            .no-print { display: none !important; }
            .print-border { border: 1px solid #000 !important; }
            /* Ajustamos la gráfica para impresión: sin salto de página y con altura máxima reducida */
            .grafica-print { width: 100% !important; height: 220px !important; margin-top: 10px !important; }
            canvas#graficaMined { max-height: 220px !important; }
        }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <a href="{{ route('academico.reportes.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors text-sm font-bold mr-2">← Volver</a>
                Rendimiento Académico General 
            </h2>
            <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-amber-500 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Formato MINED
            </button>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Filtros -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print">
            <form method="GET" action="{{ route('academico.reportes.rendimiento-corte') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-[#e6ac27]">
                        <option value="">Seleccione</option>
                        @foreach($anios as $an)
                            <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Modalidad</label>
                    <select name="modalidad_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-[#e6ac27]">
                        <option value="">Seleccione</option>
                        @foreach($modalidades as $mod)
                            <option value="{{ $mod->id }}" @selected($mod->id == request('modalidad_id'))>{{ $mod->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Corte Evaluativo</label>
                    <select name="corte_evaluativo_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-[#e6ac27]">
                        <option value="">Seleccione</option>
                        @foreach($cortes as $cort)
                            <option value="{{ $cort->id }}" @selected($cort->id == request('corte_evaluativo_id'))>
                                {{ $cort->descripcion ?? $cort->nombre ?? $cort->numero.' Corte' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-slate-800 text-white px-5 py-2 rounded-xl font-bold text-sm hover:bg-slate-700 w-full">Generar Formato</button>
                </div>
            </form>
        </div>

        <!-- DOCUMENTO OFICIAL MINED -->
        <div id="zona-impresion" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 print-border overflow-x-auto">
            
            <!-- Membrete -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-[10px] font-bold text-black w-1/4">
                    <div class="grid grid-cols-2 gap-1 mb-1"><span class="bg-blue-600 text-white px-1 print:bg-transparent print:text-black">Cod Establecim:</span> <span>{{ $infoCentro['cod_establecimiento'] ?? '27974' }}</span></div>
                    <div class="grid grid-cols-2 gap-1 mb-1"><span class="bg-blue-600 text-white px-1 print:bg-transparent print:text-black">Cod Centro:</span> <span>{{ $infoCentro['cod_centro'] ?? '27760' }}</span></div>
                    <div class="grid grid-cols-2 gap-1 mb-1"><span class="bg-blue-600 text-white px-1 print:bg-transparent print:text-black">Centro:</span> <span>Colegio Cristiano en Nicaragua</span></div>
                    @php 
                        $modFiltro = $modalidades->firstWhere('id', request('modalidad_id')); 
                        $nombreMod = $modFiltro ? $modFiltro->nombre : 'GENERAL';
                    @endphp
                    <div class="grid grid-cols-2 gap-1 mb-1"><span class="bg-blue-600 text-white px-1 print:bg-transparent print:text-black">Modalidad:</span> <span class="uppercase">{{ $nombreMod }}</span></div>
                    <div class="grid grid-cols-2 gap-1 mb-1"><span class="bg-blue-600 text-white px-1 print:bg-transparent print:text-black">Turno:</span> <span class="uppercase">Matutino</span></div>
                </div>

                <div class="text-center w-2/4">
                    <h1 class="text-sm font-black uppercase mb-1">FORMATO DE {{ $nombreMod }}</h1>
                    <h2 class="text-xs font-bold uppercase mb-4">RENDIMIENTO ACADÉMICO GENERAL - {{ $nombreCorte }}, {{ $anioActual }}</h2>
                    <div class="flex justify-center items-center gap-8">
                        <img src="{{ asset('img/logos/mined_izq.png') }}" alt="Gobierno" class="h-10 object-contain">
                        <img src="{{ asset('img/logos/mined_der.png') }}" alt="Campaña" class="h-10 object-contain">
                    </div>
                </div>
                <div class="w-1/4"></div>
            </div>

            <!-- Tabla -->
            <table class="w-full text-center border-collapse border border-black text-[8px] print:text-[8px] mb-8">
                <thead class="bg-slate-100 text-black font-black uppercase">
                    <tr>
                        <th rowspan="2" class="border border-black px-1 py-1 w-12 align-middle">Nivel / Grado</th>
                        <th colspan="2" class="border border-black px-1 py-1">MI</th>
                        <th colspan="2" class="border border-black px-1 py-1">MA</th>
                        <th colspan="2" class="border border-black px-1 py-1">% DE PERM</th>
                        <th colspan="2" class="border border-black px-1 py-1">APROB</th>
                        <th colspan="2" class="border border-black px-1 py-1">% DE APROB</th>
                        <th colspan="2" class="border border-black px-1 py-1">APLAZ 1-2</th>
                        <th colspan="2" class="border border-black px-1 py-1">APLAZ 3-MAS</th>
                        <th colspan="2" class="border border-black px-1 py-1 leading-tight">EVALUADO PERIODO<br>OFICIAL (EXAMENES)</th>
                        <th colspan="2" class="border border-black px-1 py-1 leading-tight">EVALUADO PERIODO<br>EXTRA</th>
                        <th colspan="2" class="border border-black px-1 py-1 leading-tight">NO EVALUADO /<br>SIN EVALUAC</th>
                        <th colspan="2" class="border border-black px-1 py-1">ASIST REFORZ</th>
                        <th colspan="2" class="border border-black px-1 py-1">MAEST INT REF</th>
                    </tr>
                    <tr>
                        @for($i=0; $i<12; $i++)
                            <th class="border border-black px-0.5 py-1 w-5">(AS)</th><th class="border border-black px-0.5 py-1 w-5">(F)</th>
                        @endfor
                    </tr>
                </thead>
                <tbody class="text-black font-medium">
                    @forelse($filas ?? [] as $fila)
                        <tr>
                            <td class="border border-black px-1 py-1 font-bold">{{ $fila['grado'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['mi_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['mi_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['ma_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['ma_f'] }}</td>
                            <td class="border border-black px-1 py-1 bg-green-100/50 print:bg-transparent">{{ $fila['perm_as'] }}%</td>
                            <td class="border border-black px-1 py-1 bg-green-100/50 print:bg-transparent">{{ $fila['perm_f'] }}%</td>
                            <td class="border border-black px-1 py-1">{{ $fila['aprob_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['aprob_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['porc_aprob_as'] }}%</td><td class="border border-black px-1 py-1">{{ $fila['porc_aprob_f'] }}%</td>
                            <td class="border border-black px-1 py-1">{{ $fila['aplaz1_2_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['aplaz1_2_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['aplaz3_mas_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['aplaz3_mas_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['eval_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['eval_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['eval_extra_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['eval_extra_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['no_eval_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['no_eval_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['asist_reforz_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['asist_reforz_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['maest_ref_as'] }}</td><td class="border border-black px-1 py-1">{{ $fila['maest_ref_f'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="25" class="border border-black px-4 py-4 text-center text-slate-500">
                                Seleccione el Año Escolar y Modalidad para generar el reporte.
                            </td>
                        </tr>
                    @endforelse

                    <!-- FILA DE TOTALES -->
                    @if(isset($totales) && !empty($filas))
                        <tr class="font-black bg-slate-100 print:bg-transparent">
                            <td class="border border-black px-1 py-1">TOTAL</td>
                            <td class="border border-black px-1 py-1">{{ $totales['mi_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['mi_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $totales['ma_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['ma_f'] }}</td>
                            <td class="border border-black px-1 py-1 bg-green-200/50 print:bg-transparent">{{ $totales['perm_as'] }}%</td>
                            <td class="border border-black px-1 py-1 bg-green-200/50 print:bg-transparent">{{ $totales['perm_f'] }}%</td>
                            <td class="border border-black px-1 py-1">{{ $totales['aprob_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['aprob_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $totales['porc_aprob_as'] }}%</td><td class="border border-black px-1 py-1">{{ $totales['porc_aprob_f'] }}%</td>
                            <td class="border border-black px-1 py-1">{{ $totales['aplaz1_2_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['aplaz1_2_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $totales['aplaz3_mas_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['aplaz3_mas_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $totales['eval_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['eval_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $totales['eval_extra_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['eval_extra_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $totales['no_eval_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['no_eval_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $totales['asist_reforz_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['asist_reforz_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $totales['maest_ref_as'] }}</td><td class="border border-black px-1 py-1">{{ $totales['maest_ref_f'] }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- GRÁFICA MIXTA (MINED) -->
            @if(isset($chartData) && !empty($chartData['labels']))
                <!-- Le damos una altura fija en pantalla también -->
                <div class="grafica-print w-full max-w-4xl mx-auto mt-6 h-[300px] print:h-[220px]">
                    <h3 class="text-center font-bold text-sm mb-2 text-slate-700">Resultados del {{ $nombreCorte }} {{ $anioActual }}</h3>
                    <canvas id="graficaMined"></canvas>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const ctx = document.getElementById('graficaMined').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: @json($chartData['labels']),
                                datasets: [
                                    {
                                        type: 'bar',
                                        label: '% APROBADOS EN TODAS AS',
                                        data: @json($chartData['aprobados']),
                                        backgroundColor: '#73a84b',
                                        barPercentage: 0.5
                                    },
                                    {
                                        type: 'line',
                                        label: '% DE RETENCION AS',
                                        data: @json($chartData['retencion']),
                                        borderColor: '#4b88d6',
                                        backgroundColor: '#4b88d6',
                                        borderWidth: 2,
                                        tension: 0.1,
                                        fill: false
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false, /* CRÍTICO: Permite que el CSS controle la altura para que no se pase a otra hoja */
                                plugins: {
                                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 9 } } }
                                },
                                scales: {
                                    y: {
                                        min: 0,
                                        max: 100,
                                        ticks: {
                                            stepSize: 20,
                                            font: { size: 9 },
                                            callback: function(value) { return value + "%"; }
                                        }
                                    },
                                    x: {
                                        ticks: { font: { size: 9 } }
                                    }
                                }
                            }
                        });
                    });
                </script>
            @endif

        </div>
    </div>
</x-app-layout>