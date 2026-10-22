<x-app-layout>
    <style>
        @media print {
            body * { visibility: hidden; }
            #zona-impresion, #zona-impresion * { visibility: visible; }
            #zona-impresion { position: absolute; left: 0; top: 0; width: 100%; border: none !important; }
            @page { size: landscape; margin: 10mm; }
            .no-print { display: none !important; }
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Auditoría de Desempeño Docente</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Evaluación de Rendimiento, Promedios y Dificultad</p>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ route('academico.reportes.index') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir Evaluación
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- PANEL DE FILTROS SIMPLIFICADO -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-700 via-blue-500 to-blue-700"></div>
            
            <form method="GET" action="{{ route('academico.reportes.notas-por-asignatura') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <!-- Año Escolar -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-blue-500">
                        @foreach($anios ?? [] as $an)
                            <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id', optional(\App\Models\AnioEscolar::where('activo', true)->first())->id))>                                {{ $an->nombre }} {{ $an->activo ? '(Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Asignatura -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Asignatura</label>
                    <select name="asignatura_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-blue-500">
                        <option value="">Todas las Asignaturas</option>
                        @foreach($asignaturas ?? [] as $asig)
                            <option value="{{ $asig->id }}" @selected($asig->id == request('asignatura_id'))>
                                {{ $asig->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Docente Responsable -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Docente Responsable</label>
                    <select name="docente_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-blue-500">
                        <option value="">Todos los Docentes</option>
                        @foreach($docentes ?? [] as $doc)
                            <option value="{{ $doc->id }}" @selected($doc->id == request('docente_id'))>
                                {{ $doc->usuario->nombre_completo ?? 'Sin nombre' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Periodo Evaluativo -->
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Periodo Evaluativo</label>
                    <select name="corte_evaluativo_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-blue-500">
                        <option value="">Todos los Cortes</option>
                        @foreach($cortes ?? [] as $cort)
                            <option value="{{ $cort->id }}" @selected($cort->id == request('corte_evaluativo_id'))>
                                {{ $cort->nombre ?? ($cort->numero . '° Corte') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Botones de Acción -->
                <div class="sm:col-span-2 lg:col-span-4 flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('academico.reportes.notas-por-asignatura') }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-500 hover:bg-slate-100 transition-colors">
                        Limpiar Filtros
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 bg-[#3d2c1d] hover:bg-black text-white font-black px-6 py-2.5 rounded-xl text-xs uppercase tracking-widest transition-all cursor-pointer shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>

        <!-- MEMBRETE OFICIAL -->
        <x-membrete-impresion 
            titulo="Consolidado de Desempeño y Nivel de Dificultad Docente"
            subtitulo="Auditoría Académica Interna • Colegio Cristiano en Nicaragua"
            badge="Ciclo {{ date('Y') }}"
            orientacion="landscape"
        />

        <!-- MATRIZ DE RENDIMIENTO DOCENTE -->
        <div id="zona-impresion" class="bg-white rounded-3xl print:rounded-xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
            <div class="overflow-x-auto print:overflow-visible">
                <table class="min-w-full divide-y divide-slate-200 text-sm print:text-[10px] border-collapse">
                    <thead class="bg-[#FFFDF5] print:bg-[#3d2c1d] text-slate-500 print:text-white uppercase text-[10px] font-black tracking-wider">
                        <tr>
                            <th class="px-4 py-4 text-center w-10">#</th>
                            <th class="px-4 py-4 text-left">Docente Evaluado</th>
                            <th class="px-4 py-4 text-left">Asignatura</th>
                            <th class="px-4 py-4 text-left">Grupos Asignados</th>
                            <th class="px-3 py-4 text-center">Evaluados</th>
                            <th class="px-3 py-4 text-center">Aprobados</th>
                            <th class="px-3 py-4 text-center">Reprobados</th>
                            <th class="px-4 py-4 text-center" title="Porcentaje de alumnos reprobados sobre el total">Tasa Dificultad</th>
                            <th class="px-4 py-4 text-center">Promedio Clase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($filas ?? [] as $index => $fila)
                            @php
                                // Alertas Visuales
                                $tasaRiesgo = $fila['tasa_riesgo'];
                                $alertaFondo = $tasaRiesgo >= 30 ? 'bg-rose-50/50' : 'hover:bg-amber-50/30';
                                $promedioColor = $fila['promedio'] < 60 ? 'text-rose-600' : ($fila['promedio'] >= 90 ? 'text-emerald-600' : 'text-[#3d2c1d]');
                                $riesgoBadge = $tasaRiesgo >= 30 ? 'text-rose-700 bg-rose-100 border-rose-200' : 'text-slate-600 bg-slate-100 border-slate-200';
                            @endphp
                            
                            <tr class="transition-colors {{ $alertaFondo }}">
                                <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 font-black text-blue-900">{{ $fila['docente'] }}</td>
                                <td class="px-4 py-3.5 font-bold text-[#3d2c1d]">{{ $fila['asignatura'] }}</td>
                                <td class="px-4 py-3.5 font-medium text-slate-500 text-[11px] leading-tight max-w-[200px] truncate" title="{{ $fila['aulas'] }}">
                                    {{ $fila['aulas'] }}
                                </td>
                                
                                <td class="px-3 py-3.5 text-center font-bold text-slate-500">{{ $fila['evaluados'] }}</td>
                                <td class="px-3 py-3.5 text-center font-bold text-emerald-600">{{ $fila['aprobados'] }}</td>
                                <td class="px-3 py-3.5 text-center font-black {{ $fila['reprobados'] > 0 ? 'text-rose-500' : 'text-slate-400' }}">{{ $fila['reprobados'] }}</td>
                                
                                <td class="px-4 py-3.5 text-center">
                                    @if($fila['evaluados'] > 0)
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-black border {{ $riesgoBadge }}">
                                            {{ $tasaRiesgo }}%
                                        </span>
                                    @else
                                        <span class="text-slate-300 font-bold">-</span>
                                    @endif
                                </td>
                                
                                <td class="px-4 py-3.5 text-center font-black text-sm {{ $promedioColor }}">
                                    {{ $fila['promedio'] > 0 ? number_format($fila['promedio'], 2) : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-600 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                    </div>
                                    <p class="text-slate-500 font-bold">No hay registros de calificaciones para evaluar el desempeño docente.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>