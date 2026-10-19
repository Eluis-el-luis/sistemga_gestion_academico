<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Centro de Auditoría Académica
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">
                    Supervisión de Ingreso de Notas y Avance Programático (MINED)
                </p>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ route('academico.reportes.index') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir / PDF
                </button>
            </div>
        </div>
    </x-slot>

    @php
        $tipoAuditoria = $tipoAuditoria ?? 'notas';
        $perspectivaAvance = $perspectivaAvance ?? 'docente';
        $coleccionFilas = collect($filas);
        
        $tituloMembrete = ($tipoAuditoria === 'avance') 
            ? 'Reporte Oficial de Avance Programático (' . ucfirst($perspectivaAvance) . ')' 
            : 'Acta de Supervisión y Control de Calificaciones';
    @endphp

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 print:max-w-none print:p-0 print:space-y-4" x-data="{ tipo: '{{ $tipoAuditoria }}', perspectiva: '{{ $perspectivaAvance }}' }">
        
        <!-- PESTAÑAS DE NAVEGACIÓN EJECUTIVA (Ocultas al imprimir) -->
        <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row gap-2 print:hidden">
            <a href="{{ route('academico.reportes.control-notas', ['tipo_auditoria' => 'notas']) }}" 
               :class="tipo === 'notas' ? 'bg-[#3d2c1d] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
               class="flex-1 text-center py-3 px-4 rounded-xl font-black text-xs uppercase tracking-widest transition-all cursor-pointer flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Control de Notas
            </a>
            <a href="{{ route('academico.reportes.control-notas', ['tipo_auditoria' => 'avance', 'perspectiva' => 'docente']) }}" 
               :class="tipo === 'avance' ? 'bg-[#3d2c1d] text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
               class="flex-1 text-center py-3 px-4 rounded-xl font-black text-xs uppercase tracking-widest transition-all cursor-pointer flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                Avance Programático (MINED)
            </a>
        </div>

        <!-- PANEL UNIFICADO DE FILTROS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 print:hidden relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#3d2c1d] via-[#e6ac27] to-[#3d2c1d]"></div>
            
            <form method="GET" action="{{ route('academico.reportes.control-notas') }}" class="space-y-5">
                <input type="hidden" name="tipo_auditoria" :value="tipo">
                <input type="hidden" name="perspectiva" :value="perspectiva">

                <!-- Si estamos en Avance, mostramos el selector de Perspectiva (Docente vs Aula) -->
                <div x-show="tipo === 'avance'" class="pb-4 border-b border-slate-100 flex items-center gap-3" style="display: none;">
                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Ver avance por:</span>
                    <button type="button" @click="perspectiva = 'docente'; $el.closest('form').submit();" :class="perspectiva === 'docente' ? 'bg-[#e6ac27] text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider transition-colors cursor-pointer">
                        Docente
                    </button>
                    <button type="button" @click="perspectiva = 'aula'; $el.closest('form').submit();" :class="perspectiva === 'aula' ? 'bg-[#e6ac27] text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider transition-colors cursor-pointer">
                        Aula / Grado
                    </button>
                </div>

                @include('academico.reportes.partials.filtros-notas', [
                    'ruta' => 'academico.reportes.control-notas',
                    'mostrarEstado' => ($tipoAuditoria === 'notas')
                ])
            </form>
        </div>

        <!-- MEMBRETE OFICIAL -->
        <x-membrete-impresion 
            :titulo="$tituloMembrete"
            subtitulo="Auditoría Académica Institucional • Colegio Cristiano en Nicaragua"
            badge="Ciclo {{ date('Y') }}"
            detalle="Perspectiva de Análisis: <span class='text-[#3d2c1d]'>{{ $tipoAuditoria === 'avance' ? 'Avance Curricular (' . ucfirst($perspectivaAvance) . ')' : 'Control de Ingreso de Calificaciones' }}</span>"
            orientacion="landscape"
        />

        <!-- TABLA CONDICIONAL SEGÚN EL TIPO DE AUDITORÍA -->
        <div class="bg-white rounded-3xl print:rounded-xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
            <div class="overflow-x-auto print:overflow-visible">
                
                @if($tipoAuditoria === 'notas')
                    <!-- ================= VISTA 1: TABLA DE NOTAS ================= -->
                    <table class="min-w-full divide-y divide-slate-200 text-sm print:text-[10px] border-collapse">
                        <thead class="bg-[#FFFDF5] print:bg-[#3d2c1d] text-slate-500 print:text-white uppercase text-[11px] font-black tracking-wider">
                            <tr>
                                <th class="px-4 py-4 text-center w-10">#</th>
                                <th class="px-4 py-4 text-left">Grado y Sección</th>
                                <th class="px-4 py-4 text-left">Asignatura</th>
                                <th class="px-4 py-4 text-left">Docente Responsable</th>
                                <th class="px-3 py-4 text-center">Matrícula</th>
                                <th class="px-3 py-4 text-center">Registradas</th>
                                <th class="px-3 py-4 text-center">Pendientes</th>
                                <th class="px-4 py-4 text-center">Acta</th>
                                <th class="px-5 py-4 text-center w-36">Avance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($filas as $index => $fila)
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3.5 font-black text-[#3d2c1d]">{{ $fila['grado'] }} - Sección "{{ $fila['aula'] }}"</td>
                                    <td class="px-4 py-3.5 font-bold text-[#3d2c1d]">{{ $fila['asignatura'] }}</td>
                                    <td class="px-4 py-3.5 text-slate-700">{{ $fila['docente'] }}</td>
                                    <td class="px-3 py-3.5 text-center font-bold text-slate-600">{{ $fila['total'] }}</td>
                                    <td class="px-3 py-3.5 text-center font-black {{ $fila['registradas'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $fila['registradas'] }}</td>
                                    <td class="px-3 py-3.5 text-center font-black {{ $fila['pendientes'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $fila['pendientes'] }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if(!empty($fila['cerrado']))
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-slate-100 text-slate-700">Cerrada</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-emerald-50 text-emerald-700">Abierta</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full {{ $fila['porcentaje'] == 100 ? 'bg-emerald-500' : 'bg-[#e6ac27]' }}" style="width: {{ $fila['porcentaje'] }}%"></div>
                                            </div>
                                            <span class="text-xs font-black w-10 text-right">{{ $fila['porcentaje'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-6 py-12 text-center text-slate-400 font-bold">No hay registros de notas para los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>

                @else
                    <!-- ================= VISTA 2: TABLA DE AVANCE PROGRAMÁTICO (MINED 0-100%) ================= -->
                    <table class="min-w-full divide-y divide-slate-200 text-sm print:text-[10px] border-collapse">
                        <thead class="bg-[#FFFDF5] print:bg-[#3d2c1d] text-slate-500 print:text-white uppercase text-[11px] font-black tracking-wider">
                            <tr>
                                <th class="px-4 py-4 text-center w-10">#</th>
                                @if($perspectivaAvance === 'docente')
                                    <th class="px-4 py-4 text-left">Docente</th>
                                    <th class="px-4 py-4 text-left">Asignatura</th>
                                    <th class="px-4 py-4 text-left">Grado y Sección</th>
                                @else
                                    <th class="px-4 py-4 text-left">Grado y Sección</th>
                                    <th class="px-4 py-4 text-left">Asignatura</th>
                                    <th class="px-4 py-4 text-left">Docente Responsable</th>
                                @endif
                                <th class="px-4 py-4 text-left">Unidades / Contenidos Reportados</th>
                                <th class="px-5 py-4 text-center w-48">Avance Curricular (0-100%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($filas as $index => $fila)
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                    
                                    @if($perspectivaAvance === 'docente')
                                        <td class="px-4 py-3.5 font-black text-[#3d2c1d]">{{ $fila['docente'] }}</td>
                                        <td class="px-4 py-3.5 font-bold text-slate-700">{{ $fila['asignatura'] }}</td>
                                        <td class="px-4 py-3.5 text-slate-600">{{ $fila['grado'] }}</td>
                                    @else
                                        <td class="px-4 py-3.5 font-black text-[#3d2c1d]">{{ $fila['grado'] }}</td>
                                        <td class="px-4 py-3.5 font-bold text-slate-700">{{ $fila['asignatura'] }}</td>
                                        <td class="px-4 py-3.5 text-slate-600">{{ $fila['docente'] }}</td>
                                    @endif

                                    <td class="px-4 py-3.5 text-xs text-slate-500 italic">{{ $fila['detalle'] }}</td>

                                    <!-- Barra de Progreso Dinámica con Semáforo MINED -->
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden border border-slate-200">
                                                @php
                                                    $colorBarra = 'bg-rose-500'; // < 50%
                                                    if ($fila['porcentaje'] >= 90) $colorBarra = 'bg-emerald-500';
                                                    elseif ($fila['porcentaje'] >= 50) $colorBarra = 'bg-[#e6ac27]';
                                                @endphp
                                                <div class="h-full rounded-full {{ $colorBarra }}" style="width: {{ $fila['porcentaje'] }}%"></div>
                                            </div>
                                            <span class="text-xs font-black w-12 text-right {{ $fila['porcentaje'] >= 90 ? 'text-emerald-600' : ($fila['porcentaje'] >= 50 ? 'text-[#3d2c1d]' : 'text-rose-600') }}">
                                                {{ $fila['porcentaje'] }}%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-12 text-center text-slate-400 font-bold">No hay registros de avance programático para mostrar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif

            </div>
        </div>

        <!-- FIRMAS OFICIALES PARA IMPRESIÓN -->
        <div class="hidden print:grid print:grid-cols-2 gap-16 pt-14 px-12 text-center page-break-inside-avoid">
            <div class="border-t border-[#3d2c1d] pt-2">
                <p class="text-[10px] font-black text-[#3d2c1d] uppercase tracking-widest">Revisado por Subdirección Académica</p>
                <p class="text-[9px] font-bold text-slate-500 mt-0.5">Firma y Sello</p>
            </div>
            <div class="border-t border-[#3d2c1d] pt-2">
                <p class="text-[10px] font-black text-[#3d2c1d] uppercase tracking-widest">Vo.Bo. Dirección General / MINED</p>
                <p class="text-[9px] font-bold text-slate-500 mt-0.5">Colegio Cristiano en Nicaragua</p>
            </div>
        </div>

    </div>
</x-app-layout>