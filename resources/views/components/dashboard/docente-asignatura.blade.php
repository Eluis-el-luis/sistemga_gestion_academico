<div class="space-y-8 animate-fade-in">

    <!-- BARRA DE HERRAMIENTAS RÁPIDAS (2 Columnas) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <!-- Configurar Notas -->
        <a href="{{ route('academico.notas.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex items-center gap-4 cursor-pointer transform hover:-translate-y-0.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div>
                <span class="block font-black text-base text-[#3d2c1d] group-hover:text-[#e6ac27] transition-colors">Configurar Notas</span>
                <span class="block text-xs font-medium text-slate-500 mt-0.5">Acumulados y exámenes</span>
            </div>
        </a>
        
        <!-- Historial de Asistencia -->
        <a href="{{ route('academico.asistencia.personal.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex items-center gap-4 cursor-pointer transform hover:-translate-y-0.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="block font-black text-base text-[#3d2c1d] group-hover:text-[#e6ac27] transition-colors">Mi Asistencia</span>
                <span class="block text-xs font-medium text-slate-500 mt-0.5">Historial y reportes</span>
            </div>
        </a>
    </div>

    <!-- EL CALENDARIO INTERACTIVO -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative">
        
        <!-- Cabecera Limpia sin elementos flotantes superpuestos -->
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
                <h3 class="text-xl font-black text-[#3d2c1d]">Agenda Semanal</h3>
                <p class="text-sm font-medium text-slate-500 mt-1">Selecciona una clase para evaluar o pasar asistencia.</p>
            </div>
            
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl border border-emerald-100 font-bold text-sm">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                </span>
                Esquema {{ $esquemaActivo ?? 'Regular' }} Activo
            </div>
        </div>

        <!-- Zona 2: La Cuadrícula de Calor -->
        @if(isset($bloques) && $bloques->count() > 0)
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr>
                            <th class="w-24 bg-white border-r border-b border-slate-100 px-4 py-3 text-center sticky left-0 z-20">
                                <svg class="w-5 h-5 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </th>
                            @foreach($diasSemana as $dia)
                                <th class="bg-white border-b border-slate-100 px-4 py-3 text-center w-1/5">
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest">{{ $dia }}</span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($bloques as $bloque)
                            @php 
                                $horaStr = $bloque->hora_inicio; 
                                $horaFmt = \Carbon\Carbon::parse($horaStr)->format('h:i A');
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="border-r border-slate-100 px-2 py-4 text-center sticky left-0 bg-white z-10 shadow-[1px_0_0_0_#f1f5f9]">
                                    <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">{{ $horaFmt }}</span>
                                </td>
                                
                                @foreach($diasSemana as $dia)
                                    @php 
                                        $clase = $matrizHorario[$dia][$horaStr] ?? null; 
                                    @endphp
                                    
                                    <td class="p-2 border-r border-slate-50 last:border-r-0 align-top">
                                        @if($clase)
                                            @php
                                                $colorBg = 'bg-amber-50'; $colorBorder = 'border-amber-200'; $colorText = 'text-amber-700'; $colorIcon = 'text-amber-500';
                                                
                                                if($clase['modalidad_id'] == 2) { 
                                                    $colorBg = 'bg-blue-50'; $colorBorder = 'border-blue-200'; $colorText = 'text-blue-700'; $colorIcon = 'text-blue-500'; 
                                                } elseif($clase['modalidad_id'] == 3) { 
                                                    $colorBg = 'bg-emerald-50'; $colorBorder = 'border-emerald-200'; $colorText = 'text-emerald-700'; $colorIcon = 'text-emerald-500'; 
                                                }
                                            @endphp
                                            
                                            <button @click="$dispatch('abrir-modal-decision-clase', { 
                                                        id: '{{ $clase['asignacion_id'] }}', 
                                                        asignatura: '{{ addslashes($clase['asignatura']) }}', 
                                                        aula: '{{ addslashes($clase['aula']) }}', 
                                                        hora: '{{ $clase['hora_inicio'] }} - {{ $clase['hora_fin'] }}' 
                                                    })" 
                                                    class="w-full h-full text-left p-3 rounded-xl border {{ $colorBorder }} {{ $colorBg }} hover:shadow-md transition-all transform hover:-translate-y-0.5 group">
                                                
                                                <div class="flex justify-between items-start mb-2">
                                                    <span class="text-xs font-black {{ $colorText }} leading-tight group-hover:underline">{{ $clase['asignatura'] }}</span>
                                                    <svg class="w-4 h-4 {{ $colorIcon }} opacity-50 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                                                </div>
                                                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-auto">{{ $clase['aula'] }}</span>
                                            </button>
                                        @else
                                            <div class="w-full h-full min-h-[4.5rem] rounded-xl border border-dashed border-slate-200 bg-slate-50/50 flex flex-col items-center justify-center">
                                                <span class="text-[10px] font-bold text-slate-400 tracking-wide">Libre</span>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-16 text-center flex flex-col items-center justify-center">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 mb-4 border border-slate-100">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-[#3d2c1d]">Sin carga horaria</h3>
                <p class="text-sm font-medium text-slate-500 mt-1 max-w-sm">No tienes asignaturas programadas para el esquema activo actual.</p>
            </div>
        @endif
    </div>
</div>