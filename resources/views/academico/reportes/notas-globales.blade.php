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
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Sábana de Notas Globales</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Consolidado general por Aula y Sección</p>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ route('academico.reportes.index') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir Sábana
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- PANEL DE FILTROS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-[#3d2c1d]"></div>
            
            <form method="GET" action="{{ route('academico.reportes.notas-globales') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-[#e6ac27]">
                        @foreach($anios as $an)
                            <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Corte Evaluativo</label>
                    <select name="corte_evaluativo_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-[#e6ac27]">
                        <option value="">Promedio Final (Anual)</option>
                        @foreach($cortes as $cort)
                            <option value="{{ $cort->id }}" @selected($cort->id == request('corte_evaluativo_id'))>
                                {{ $cort->descripcion ?? $cort->nombre ?? $cort->numero.' Corte' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5 text-rose-600">Aula / Sección (Requerido)</label>
                    <select name="aula_id" required class="w-full border-rose-200 bg-rose-50 rounded-xl text-sm font-medium focus:ring-rose-500">
                        <option value="">Seleccione el Aula...</option>
                        @foreach($aulas ?? \App\Models\Aula::with('grado')->get() as $aulaOp)
                            <option value="{{ $aulaOp->id }}" @selected($aulaOp->id == request('aula_id'))>
                                {{ $aulaOp->grado->nombre ?? '' }} - {{ $aulaOp->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-[#3d2c1d] text-white px-5 py-2 rounded-xl font-bold text-sm hover:bg-black w-full">Generar Sábana</button>
                </div>
            </form>
        </div>

        @if(request('aula_id'))
            <!-- DOCUMENTO OFICIAL (SÁBANA) -->
            <div id="zona-impresion" class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200">
                
                <div class="text-center mb-6">
                    <h1 class="text-sm font-black uppercase tracking-wider text-black">Sábana de Calificaciones Globales</h1>
                    <h2 class="text-xs font-bold text-slate-600 mt-1">Colegio Cristiano en Nicaragua</h2>
                    <h3 class="text-[10px] font-bold text-slate-500 mt-2 uppercase">
                        SECCIÓN: {{ $aula->grado->nombre ?? '' }} - {{ $aula->nombre ?? '' }} | 
                        CORTE: {{ request('corte_evaluativo_id') ? \App\Models\CorteEvaluativo::find(request('corte_evaluativo_id'))->nombre : 'PROMEDIO FINAL' }}
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-center border-collapse border-2 border-slate-800 text-xs">
                        <thead class="bg-slate-100 text-black font-black uppercase text-[9px] tracking-tighter">
                            <tr>
                                <th class="border border-slate-800 px-1 py-2 w-6">#</th>
                                <th class="border border-slate-800 px-2 py-2 text-left w-48">Estudiante</th>
                                
                                @foreach($asignaturas ?? [] as $asignatura)
                                    <!-- Abreviamos el nombre para que la columna no sea ancha -->
                                    <th class="border border-slate-800 px-1 py-2 w-12 truncate max-w-[50px]" title="{{ $asignatura->nombre }}">
                                        {{ str()->limit($asignatura->nombre, 10, '.') }}
                                    </th>
                                @endforeach
                                
                                <th class="border border-slate-800 px-2 py-2 bg-slate-200 w-16">PROM</th>
                                <th class="border border-slate-800 px-1 py-2 bg-rose-100 w-12 text-rose-800">REP</th>
                            </tr>
                        </thead>
                        <tbody class="text-black font-medium text-[11px]">
                            @forelse($filas ?? [] as $index => $fila)
                                <tr class="hover:bg-amber-50">
                                    <td class="border border-slate-400 px-1 py-1.5 font-bold text-slate-500">{{ $index + 1 }}</td>
                                    <td class="border border-slate-400 px-2 py-1.5 text-left truncate max-w-[200px] font-bold">
                                        {{ $fila['alumno'] }}
                                    </td>
                                    
                                    @foreach($asignaturas ?? [] as $asignatura)
                                        @php 
                                            $nota = $fila['notas'][$asignatura->id] ?? null; 
                                            $claseColor = '';
                                            if($nota !== null) {
                                                if($nota < 60) $claseColor = 'text-rose-600 font-black bg-rose-50';
                                                elseif($nota >= 90) $claseColor = 'text-blue-600 font-bold';
                                            }
                                        @endphp
                                        <td class="border border-slate-400 px-1 py-1.5 {{ $claseColor }}">
                                            {{ $nota !== null ? $nota : '-' }}
                                        </td>
                                    @endforeach
                                    
                                    <td class="border border-slate-800 px-2 py-1.5 font-black bg-slate-50 {{ $fila['promedio_general'] < 60 ? 'text-rose-600' : 'text-slate-800' }}">
                                        {{ $fila['promedio_general'] > 0 ? $fila['promedio_general'] : '-' }}
                                    </td>
                                    <td class="border border-slate-800 px-1 py-1.5 font-black {{ $fila['reprobadas'] > 0 ? 'text-rose-600 bg-rose-50' : 'text-slate-400' }}">
                                        {{ $fila['reprobadas'] > 0 ? $fila['reprobadas'] : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="border border-slate-400 px-4 py-8 text-center text-slate-500">
                                        No hay estudiantes matriculados en esta aula.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center no-print">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-600 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <h3 class="text-lg font-black text-amber-900 mb-2">Seleccione un Aula</h3>
                <p class="text-amber-700 max-w-md mx-auto">Para generar la sábana de notas, debe seleccionar obligatoriamente una sección específica en los filtros superiores.</p>
            </div>
        @endif
    </div>
</x-app-layout>