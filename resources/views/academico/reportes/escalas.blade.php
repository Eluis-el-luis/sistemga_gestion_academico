<x-app-layout>
    <style>
        @media print {
            body * { visibility: hidden; }
            #zona-impresion, #zona-impresion * { visibility: visible; }
            #zona-impresion { position: absolute; left: 0; top: 0; width: 100%; border: none !important; }
            @page { size: landscape; margin: 10mm; }
            .no-print { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <a href="{{ route('academico.reportes.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors text-sm font-bold mr-2">← Volver</a>
                Reporte MINED - Formato Escalas
            </h2>
            <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-amber-500 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Formato MINED
            </button>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Filtros -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print">
            <form method="GET" action="{{ route('academico.reportes.escalas') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
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
                        @foreach($cortes as $cort)
                            <option value="{{ $cort->id }}" @selected($cort->id == request('corte_evaluativo_id'))>
                                {{ $cort->descripcion ?? $cort->nombre ?? $cort->numero.' Corte' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-slate-800 text-white px-5 py-2 rounded-xl font-bold text-sm hover:bg-slate-700 w-full">Generar Reporte</button>
                </div>
            </form>
        </div>

        <!-- DOCUMENTO OFICIAL -->
        <div id="zona-impresion" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 overflow-x-auto">
            
            @php 
                $modFiltro = $modalidades->firstWhere('id', request('modalidad_id')); 
                $nombreMod = $modFiltro ? $modFiltro->nombre : 'GENERAL';
            @endphp

            <!-- Membrete Oficial -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-[10px] font-bold text-black w-1/4 space-y-1">
                    <div><span class="bg-blue-800 text-white px-1">Cod Establecim:</span> {{ $infoCentro['cod_establecimiento'] ?? '27974' }}</div>
                    <div><span class="bg-blue-800 text-white px-1">Cod Centro:</span> {{ $infoCentro['cod_centro'] ?? '27760' }}</div>
                    <div><span class="bg-blue-800 text-white px-1">Centro:</span> Colegio Cristiano en Nicaragua</div>
                    <div><span class="bg-blue-800 text-white px-1">Modalidad:</span> <span class="uppercase">{{ $nombreMod }}</span></div>
                    <div><span class="bg-blue-800 text-white px-1">Turno:</span> MATUTINO</div>
                </div>

                <div class="text-center w-2/4">
                    <h1 class="text-xs font-black uppercase mb-1">MINISTERIO DE EDUCACIÓN — DEPARTAMENTO DE ESTADÍSTICA MUNICIPAL LEÓN</h1>
                    <h2 class="text-xs font-bold uppercase mb-1">FORMATO ESCALA, {{ $nombreMod }}</h2>
                    <h3 class="text-[10px] font-bold uppercase mb-3">CORTE EVALUATIVO: {{ $nombreCorte }}, {{ $anioActual }}</h3>
                    <div class="flex justify-center items-center gap-8">
                        <img src="{{ asset('img/logos/mined_izq.png') }}" alt="Gobierno" class="h-9 object-contain">
                        <img src="{{ asset('img/logos/mined_der.png') }}" alt="Campaña" class="h-9 object-contain">
                    </div>
                </div>
                <div class="w-1/4"></div>
            </div>

            <!-- Tabla de Escalas -->
            <table class="w-full text-center border-collapse border-2 border-black text-xs">
                <thead class="bg-[#93c47d] text-black font-black uppercase text-[10px]">
                    <tr>
                        <th rowspan="2" class="border border-black px-2 py-2 align-middle">Nivel / Grado</th>
                        <th colspan="2" class="border border-black px-1 py-1">MI</th>
                        <th colspan="2" class="border border-black px-1 py-1">MA</th>
                        <th colspan="2" class="border border-black px-1 py-1">APROB</th>
                        <th colspan="2" class="border border-black px-1 py-1">AA</th>
                        <th colspan="2" class="border border-black px-1 py-1">AS</th>
                        <th colspan="2" class="border border-black px-1 py-1">AE</th>
                        <th colspan="2" class="border border-black px-1 py-1">AI</th>
                        <th class="border border-black px-2 py-1 bg-[#f6b26b] align-middle">VALOR (AS)</th>
                        <th class="border border-black px-2 py-1 bg-[#f6b26b] align-middle">VALOR (F)</th>
                    </tr>
                    <tr>
                        <th class="border border-black px-1 py-1">(AS)</th><th class="border border-black px-1 py-1">(F)</th>
                        <th class="border border-black px-1 py-1">(AS)</th><th class="border border-black px-1 py-1">(F)</th>
                        <th class="border border-black px-1 py-1">(AS)</th><th class="border border-black px-1 py-1">(F)</th>
                        <th class="border border-black px-1 py-1">(AS)</th><th class="border border-black px-1 py-1">(F)</th>
                        <th class="border border-black px-1 py-1">(AS)</th><th class="border border-black px-1 py-1">(F)</th>
                        <th class="border border-black px-1 py-1">(AS)</th><th class="border border-black px-1 py-1">(F)</th>
                        <th class="border border-black px-1 py-1">(AS)</th><th class="border border-black px-1 py-1">(F)</th>
                        <th class="border border-black px-1 py-1 bg-[#f6b26b]">0</th>
                        <th class="border border-black px-1 py-1 bg-[#f6b26b]">0</th>
                    </tr>
                </thead>
                <tbody class="text-black font-medium text-xs">
                    @php 
                        $t_mi_as = 0; $t_mi_f = 0; $t_ma_as = 0; $t_ma_f = 0;
                        $t_ap_as = 0; $t_ap_f = 0; $t_aa_as = 0; $t_aa_f = 0;
                        $t_as_as = 0; $t_as_f = 0; $t_ae_as = 0; $t_ae_f = 0;
                        $t_ai_as = 0; $t_ai_f = 0;
                    @endphp
                    @forelse($filas ?? [] as $fila)
                        @php
                            $t_mi_as += $fila['mi_as']; $t_mi_f += $fila['mi_f'];
                            $t_ma_as += $fila['ma_as']; $t_ma_f += $fila['ma_f'];
                            $t_ap_as += $fila['aprob_as']; $t_ap_f += $fila['aprob_f'];
                            $t_aa_as += $fila['aa_as']; $t_aa_f += $fila['aa_f'];
                            $t_as_as += $fila['as_as']; $t_as_f += $fila['as_f'];
                            $t_ae_as += $fila['ae_as']; $t_ae_f += $fila['ae_f'];
                            $t_ai_as += $fila['ai_as']; $t_ai_f += $fila['ai_f'];
                        @endphp
                        <tr>
                            <td class="border border-black px-2 py-1 font-bold bg-slate-50">{{ $fila['nivel_grado'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['mi_as'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['mi_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['ma_as'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['ma_f'] }}</td>
                            <td class="border border-black px-1 py-1 font-bold">{{ $fila['aprob_as'] }}</td>
                            <td class="border border-black px-1 py-1 font-bold">{{ $fila['aprob_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['aa_as'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['aa_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['as_as'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['as_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['ae_as'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['ae_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['ai_as'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['ai_f'] }}</td>
                            <td class="border border-black px-1 py-1 bg-amber-50">0</td>
                            <td class="border border-black px-1 py-1 bg-amber-50">0</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="border border-black px-4 py-6 text-center text-slate-500">
                                Seleccione el Año Escolar, Modalidad y Corte Evaluativo para generar el formato de escalas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!empty($filas))
                    <tfoot class="bg-slate-100 font-black text-black">
                        <tr>
                            <td class="border border-black px-2 py-1 text-left">TOTAL</td>
                            <td class="border border-black px-1 py-1">{{ $t_mi_as }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_mi_f }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_ma_as }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_ma_f }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_ap_as }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_ap_f }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_aa_as }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_aa_f }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_as_as }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_as_f }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_ae_as }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_ae_f }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_ai_as }}</td>
                            <td class="border border-black px-1 py-1">{{ $t_ai_f }}</td>
                            <td class="border border-black px-1 py-1 bg-amber-100">0</td>
                            <td class="border border-black px-1 py-1 bg-amber-100">0</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>