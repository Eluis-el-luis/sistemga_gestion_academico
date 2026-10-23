<x-app-layout>
    <style>
        @media print {
            body * { visibility: hidden; }
            #zona-impresion, #zona-impresion * { visibility: visible; }
            #zona-impresion { position: absolute; left: 0; top: 0; width: 100%; border: none !important; padding: 0 !important; margin: 0 !important; }
            @page { size: portrait; margin: 15mm 10mm; }
            .no-print { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>

    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <a href="{{ route('academico.reportes.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors text-sm font-bold mr-2">← Volver</a>
                Avance Programático — Cobertura Curricular
            </h2>
            <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-amber-500 text-white px-6 py-2 rounded-xl font-bold text-sm shadow-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Formato MINED
            </button>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Filtro -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print">
            <form method="GET" action="{{ route('academico.reportes.avance-programatico') }}" class="flex items-end gap-4">
                <div class="w-64">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
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
            
            <!-- Membrete MINED -->
            <div class="flex justify-between items-center mb-8 border-b-4 border-blue-800 pb-6">
                <div class="text-xs font-bold text-black w-1/3 space-y-1">
                    <div><span class="text-blue-800">CÓDIGO ESTABLECIMIENTO:</span> {{ $infoCentro['cod_establecimiento'] ?? '27974' }}</div>
                    <div><span class="text-blue-800">CÓDIGO DE CENTRO:</span> {{ $infoCentro['cod_centro'] ?? '27760' }}</div>
                    <div><span class="text-blue-800">DEPARTAMENTO:</span> LEÓN</div>
                </div>

                <div class="text-center w-1/3">
                    <img src="{{ asset('img/logos/mined_izq.png') }}" alt="Gobierno" class="h-14 object-contain mx-auto mb-2">
                    <h1 class="text-sm font-black uppercase text-slate-900">Ministerio de Educación</h1>
                    <h2 class="text-xs font-bold uppercase text-slate-700">Control de Avance Curricular</h2>
                </div>

                <div class="w-1/3 text-right text-xs font-bold text-black space-y-1">
                    <img src="{{ asset('img/logos/mined_der.png') }}" alt="Campaña" class="h-14 object-contain ml-auto mb-2">
                    <div><span class="text-blue-800">FECHA:</span> {{ date('d/m/Y') }}</div>
                    <div><span class="text-blue-800">CICLO:</span> {{ $anioActual }}</div>
                </div>
            </div>

            <!-- Título -->
            <div class="text-center mb-8">
                <h3 class="text-lg font-black uppercase text-black tracking-wide">Informe de Cobertura Programática Consolidado</h3>
                <p class="text-sm font-bold text-slate-700 mt-1">Colegio Cristiano en Nicaragua</p>
            </div>

            <!-- Tablas Agrupadas por Modalidad -->
            @forelse($gruposModalidades ?? [] as $modalidad => $asignaturas)
                <div class="mb-8">
                    <h4 class="bg-blue-800 text-white font-black text-xs uppercase px-4 py-2 rounded-t-xl tracking-wider">
                        Modalidad: {{ $modalidad }}
                    </h4>
                    <table class="w-full text-center border-collapse border-2 border-black text-xs">
                        <thead class="bg-slate-100 text-black font-black uppercase text-[10px]">
                            <tr>
                                <th class="border-2 border-black px-3 py-2 text-left">Grado / Sección</th>
                                <th class="border-2 border-black px-3 py-2 text-left">Asignatura</th>
                                <th class="border-2 border-black px-3 py-2">Unidades Impartidas</th>
                                <th class="border-2 border-black px-3 py-2 w-28">% Avance</th>
                            </tr>
                        </thead>
                        <tbody class="text-black font-medium">
                            @foreach($asignaturas as $item)
                                <tr>
                                    <td class="border-2 border-black px-3 py-2 text-left font-bold">{{ $item['grado'] }}</td>
                                    <td class="border-2 border-black px-3 py-2 text-left">{{ $item['asignatura'] }}</td>
                                    <td class="border-2 border-black px-3 py-2 text-slate-600 font-bold">{{ $item['unidades'] }}</td>
                                    <td class="border-2 border-black px-3 py-2 font-black text-sm {{ $item['porcentaje'] >= 80 ? 'text-emerald-700' : ($item['porcentaje'] >= 50 ? 'text-amber-600' : 'text-rose-700') }}">
                                        {{ $item['porcentaje'] }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="border-2 border-black p-8 text-center text-slate-500 text-sm font-bold">
                    No hay asignaturas o avances programáticos registrados para este ciclo escolar.
                </div>
            @endforelse

            <!-- Firmas -->
            <div class="mt-16 grid grid-cols-2 gap-12 text-center text-xs font-bold text-black">
                <div>
                    <div class="w-64 mx-auto border-t-2 border-black pt-2">
                        Firma del(a) Director(a)<br>
                        <span class="text-[10px] font-normal">Colegio Cristiano en Nicaragua</span>
                    </div>
                </div>
                <div>
                    <div class="w-64 mx-auto border-t-2 border-black pt-2">
                        Sello del Centro Educativo
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center text-[9px] text-slate-400">
                Documento oficial interno y de auditoría curricular — Delegación MINED León.
            </div>

        </div>
    </div>
</x-app-layout>