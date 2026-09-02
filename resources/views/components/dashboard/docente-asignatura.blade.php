<div class="space-y-6">

    <!-- BARRA DE HERRAMIENTAS RÁPIDAS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Configurar Notas -->
        <a href="{{ route('academico.notas.index') }}" class="flex items-center gap-4 p-4 rounded-3xl border border-slate-200 bg-white hover:border-[#e6ac27] hover:shadow-md transition-all group">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div>
                <h4 class="font-black text-[#3d2c1d]">Configurar Notas</h4>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Acumulados y Exámenes</p>
            </div>
        </a>
        
        <!-- Historial de Asistencia -->
        <a href="{{ route('academico.asistencia.personal.index') }}" class="flex items-center gap-4 p-4 rounded-3xl border border-slate-200 bg-white hover:border-emerald-500 hover:shadow-md transition-all group">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h4 class="font-black text-[#3d2c1d]">Mi Asistencia</h4>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Historial y reportes</p>
            </div>
        </a>

        <!-- Mis Aulas (Visor) -->
        <a href="{{ route('academico.visor.aulas') }}" class="flex items-center gap-4 p-4 rounded-3xl border border-slate-200 bg-white hover:border-blue-500 hover:shadow-md transition-all group">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h4 class="font-black text-[#3d2c1d]">Mis Aulas</h4>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Directorio de estudiantes</p>
            </div>
        </a>
    </div>

    <!-- EL CALENDARIO INTERACTIVO -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden relative">
        
        <!-- Zona 1: Indicador de Esquema Flotante -->
        <div class="absolute top-5 left-6 z-10 flex items-center gap-2">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest border border-slate-200 bg-white/80 backdrop-blur-sm px-3 py-1.5 rounded-full shadow-sm">
                Horario Activo: Tipo {{ $esquemaActivo ?? 'Regular' }}
            </span>
        </div>

        <div class="p-6 pt-16 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div>
                <h3 class="text-lg font-black text-[#3d2c1d]">Mi Agenda Semanal</h3>
                <p class="text-xs text-slate-400 font-bold mt-0.5">Haz clic en un bloque de clase para pasar asistencia o evaluar.</p>
            </div>
        </div>

        <!-- Zona 2: La Cuadrícula de Calor -->
        @if(isset($bloques) && $bloques->count() > 0)
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr>
                            <th class="w-24 bg-slate-50 border-r border-b border-slate-100 px-4 py-3 text-center sticky left-0 z-20">
                                <svg class="w-5 h-5 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </th>
                            @foreach($diasSemana as $dia)
                                <th class="bg-slate-50 border-b border-slate-100 px-4 py-3 text-center w-1/5">
                                    <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest">{{ $dia }}</span>
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
                                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $horaFmt }}</span>
                                </td>
                                
                                @foreach($diasSemana as $dia)
                                    @php 
                                        $clase = $matrizHorario[$dia][$horaStr] ?? null; 
                                    @endphp
                                    
                                    <td class="p-2 border-r border-slate-50 last:border-r-0 align-top">
                                        @if($clase)
                                            @php
                                                // Escala de colores automática según la modalidad del aula
                                                $colorBg = 'bg-amber-50'; $colorBorder = 'border-amber-200'; $colorText = 'text-amber-700'; $colorIcon = 'text-amber-500';
                                                
                                                if($clase['modalidad_id'] == 2) { 
                                                    $colorBg = 'bg-blue-50'; $colorBorder = 'border-blue-200'; $colorText = 'text-blue-700'; $colorIcon = 'text-blue-500'; 
                                                } elseif($clase['modalidad_id'] == 3) { 
                                                    $colorBg = 'bg-emerald-50'; $colorBorder = 'border-emerald-200'; $colorText = 'text-emerald-700'; $colorIcon = 'text-emerald-500'; 
                                                }
                                            @endphp
                                            
                                            <!-- Botón Interactivo que dispara el Modal de tu Dashboard -->
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
                                                <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">Libre</span>
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
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-lg font-black text-[#3d2c1d]">Sin carga horaria</h3>
                <p class="text-sm font-medium text-slate-400 mt-1 max-w-sm">No tienes asignaturas programadas para el esquema de horario activo actual.</p>
            </div>
        @endif
    </div>
</div>