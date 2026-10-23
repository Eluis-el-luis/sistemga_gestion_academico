<x-app-layout>
    <style>
        @media print {
            body * { visibility: hidden; }
            #zona-impresion, #zona-impresion * { visibility: visible; }
            #zona-impresion { position: absolute; left: 0; top: 0; width: 100%; border: none !important; }
            @page { size: landscape; margin: 5mm; }
            .no-print { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <a href="{{ route('academico.reportes.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors text-sm font-bold mr-2">← Volver</a>
                Reporte MINED - Asignaturas Aprobadas
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
            <form method="GET" action="{{ route('academico.reportes.asignaturas-aprobadas') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
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
                <div class="flex gap-2">
                    <button type="submit" class="bg-slate-800 text-white px-5 py-2 rounded-xl font-bold text-sm hover:bg-slate-700 w-full">Generar Reporte</button>
                </div>
            </form>
        </div>

        <!-- DOCUMENTO OFICIAL -->
        <div id="zona-impresion" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 overflow-x-auto">
            
            <!-- Membrete -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-[10px] font-bold text-black w-1/4 space-y-1">
                    <div><span class="bg-blue-800 text-white px-1">Cod Establecim:</span> {{ $infoCentro['cod_establecimiento'] ?? '27974' }}</div>
                    <div><span class="bg-blue-800 text-white px-1">Cod Centro:</span> {{ $infoCentro['cod_centro'] ?? '27760' }}</div>
                    <div><span class="bg-blue-800 text-white px-1">Centro:</span> Colegio Cristiano en Nicaragua</div>
                    @php 
                        $modFiltro = $modalidades->firstWhere('id', request('modalidad_id')); 
                        $nombreMod = $modFiltro ? $modFiltro->nombre : 'GENERAL';
                    @endphp
                    <div><span class="bg-blue-800 text-white px-1">Modalidad:</span> <span class="uppercase">{{ $nombreMod }}</span></div>
                    <div><span class="bg-blue-800 text-white px-1">Turno:</span> MATUTINO</div>
                </div>

                <div class="text-center w-2/4">
                    <h1 class="text-xs font-black uppercase mb-1">MINISTERIO DE EDUCACIÓN — DEPARTAMENTO DE ESTADÍSTICA MUNICIPAL LEÓN</h1>
                    <h2 class="text-xs font-bold uppercase mb-1">ASIGNATURA, {{ $nombreMod }}</h2>
                    <h3 class="text-[10px] font-bold uppercase mb-3">CORTE EVALUATIVO: {{ $nombreCorte }}, {{ $anioActual }}</h3>
                    <div class="flex justify-center items-center gap-8">
                        <img src="{{ asset('img/logos/mined_izq.png') }}" alt="Gobierno" class="h-9 object-contain">
                        <img src="{{ asset('img/logos/mined_der.png') }}" alt="Campaña" class="h-9 object-contain">
                    </div>
                </div>
                <div class="w-1/4"></div>
            </div>

            <!-- Tabla de Asignaturas Aprobadas -->
            <table class="w-full text-center border-collapse border-2 border-black text-[9px]">
                <thead class="bg-blue-800 text-white font-black uppercase">
                    <tr>
                        <th rowspan="2" class="border border-black px-1 py-2 w-16 align-middle">Nivel / Grado</th>
                        <th colspan="2" class="border border-black px-1 py-1">MI</th>
                        <th colspan="2" class="border border-black px-1 py-1">MA</th>
                        <th colspan="2" class="border border-black px-1 py-1 leading-tight">APROBADOS<br>EN TODAS</th>
                        
                        <!-- Columnas dinámicas por cada Asignatura -->
                        @foreach($listaAsignaturas as $asig)
                            <th colspan="2" class="border border-black px-1 py-1 truncate max-w-[80px]">{{ $asig->nombre }}</th>
                        @endforeach

                        <th colspan="2" class="border border-black px-1 py-1 align-middle">Validación</th>
                    </tr>
                    <tr>
                        <!-- Subcolumnas AS y F para MI, MA, Aprobados en todas y cada asignatura -->
                        <th class="border border-black px-0.5 py-1 w-5 bg-blue-900">(AS)</th><th class="border border-black px-0.5 py-1 w-5 bg-blue-900">(F)</th>
                        <th class="border border-black px-0.5 py-1 w-5 bg-blue-900">(AS)</th><th class="border border-black px-0.5 py-1 w-5 bg-blue-900">(F)</th>
                        <th class="border border-black px-0.5 py-1 w-5 bg-blue-900">(AS)</th><th class="border border-black px-0.5 py-1 w-5 bg-blue-900">(F)</th>
                        
                        @foreach($listaAsignaturas as $asig)
                            <th class="border border-black px-0.5 py-1 w-5 bg-blue-900">APROB</th><th class="border border-black px-0.5 py-1 w-5 bg-blue-900">F</th>
                        @endforeach

                        <th class="border border-black px-0.5 py-1 w-6 bg-blue-900">AS</th><th class="border border-black px-0.5 py-1 w-6 bg-blue-900">F</th>
                    </tr>
                </thead>
                <tbody class="text-black font-medium">
                    @forelse($filas ?? [] as $fila)
                        <tr>
                            <td class="border border-black px-1 py-1 font-bold bg-slate-50">{{ $fila['grado'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['mi_as'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['mi_f'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['ma_as'] }}</td>
                            <td class="border border-black px-1 py-1">{{ $fila['ma_f'] }}</td>
                            <td class="border border-black px-1 py-1 font-bold">{{ $fila['aprob_todas_as'] }}</td>
                            <td class="border border-black px-1 py-1 font-bold">{{ $fila['aprob_todas_f'] }}</td>

                            <!-- Recuento de aprobados por materia -->
                            @foreach($listaAsignaturas as $asig)
                                @php $mat = $fila['por_asignatura'][$asig->id] ?? ['as' => 0, 'f' => 0]; @endphp
                                <td class="border border-black px-1 py-1">{{ $mat['as'] }}</td>
                                <td class="border border-black px-1 py-1">{{ $mat['f'] }}</td>
                            @endforeach

                            <td class="border border-black px-1 py-1 text-emerald-700 font-bold">CORRECTO</td>
                            <td class="border border-black px-1 py-1 text-emerald-700 font-bold">CORRECTO</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 8 + (count($listaAsignaturas) * 2) }}" class="border border-black px-4 py-6 text-center text-slate-500">
                                Seleccione el Año Escolar, Modalidad y Corte Evaluativo para generar el formato de asignaturas aprobadas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>