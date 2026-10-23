<!-- Métricas Rápidas -->
<div class="flex flex-col sm:flex-row gap-4 mb-6">
    <div class="w-full sm:w-64 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Alumnos Activos</p>
            <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $totalMatriculados ?? 0 }}</h4>
        </div>
        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
    </div>
    <div class="w-full sm:w-64 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Docentes</p>
            <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $totalPersonal ?? 0 }}</h4>
        </div>
        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        </div>
    </div>
</div>

<!-- Accesos Directos -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('academico.usuarios.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d]">Personal</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Directorio</span>
        </div>
    </a>

    <a href="{{ route('academico.aulas.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d]">Aulas</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Asignaciones</span>
        </div>
    </a>

    <a href="{{ route('academico.visor.index') }}" class="text-left group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer w-full">
        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d]">Horarios</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Visor Global</span>
        </div>
    </a>

    <a href="{{ route('academico.alumnos.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d]">Alumnos</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Directorio</span>
        </div>
    </a>

    <a href="{{ route('academico.matriculas.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d]">Matrículas</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Historial General</span>
        </div>
    </a>
    
    <a href="{{ route('academico.notas.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d]">Calificaciones</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Centro de Supervisión</span>
        </div>
    </a>

    <button @click.prevent="$dispatch('abrir-modal-asistencia')" class="text-left group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer w-full">
        <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d]">Asistencia</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Monitoreo Global</span>
        </div>
    </button>
</div>


<!-- ========================================================================= -->
<!-- MURAL DIGITAL INTERACTIVO (Con Datos Reales) -->
<!-- ========================================================================= -->

@php
    // MOTOR DE COLORES: Analiza el porcentaje y asigna el color correspondiente (Verde, Amarillo, Rojo)
    $badgeColor = function($valor) {
        if ($valor === null || $valor === '') return 'bg-slate-100 text-slate-400 opacity-50'; // Sin datos
        if ($valor >= 90) return 'bg-emerald-100 text-emerald-700 shadow-sm'; // Óptimo
        if ($valor >= 75) return 'bg-amber-100 text-amber-700 shadow-sm'; // Alerta
        return 'bg-rose-100 text-rose-700 shadow-sm'; // Peligro
    };

    // Estructura de meses estándar MINED (Enero a Diciembre)
    $meses = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun (C1)', 
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep (C2)', 10 => 'Oct', 11 => 'Nov (C3)', 12 => 'Dic'
    ];
@endphp

<div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mt-8">
    <div class="bg-[#FFFDF5] border-b border-[#e6ac27]/20 px-6 py-5 sm:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Mural de Seguimiento Institucional
            </h3>
            <p class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest">Basado en registros oficiales del sistema</p>
        </div>
        
        <!-- El filtro de nivel (si quieres conectarlo en el futuro por Request) -->
        <form method="GET" class="w-full sm:w-auto">
            <select name="modalidad_mural" onchange="this.form.submit()" class="w-full sm:w-64 border-slate-200 bg-white rounded-xl shadow-sm text-sm font-bold text-slate-600 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer">
                <option value="">Vista Global (Centro Educativo)</option>
                @foreach(\App\Models\Modalidad::all() as $mod)
                    <option value="{{ $mod->id }}" {{ request('modalidad_mural') == $mod->id ? 'selected' : '' }}>
                        Solo {{ $mod->nombre }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="overflow-x-auto pb-4">
        <table class="w-full text-left text-sm whitespace-nowrap min-w-[1000px]">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400 font-black">
                    <th class="px-6 py-4 sticky left-0 bg-slate-50/90 backdrop-blur-sm z-10 w-1/4 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">Indicador de Desempeño</th>
                    @foreach($meses as $num => $nombreMes)
                        <th class="px-3 py-4 text-center {{ str_contains($nombreMes, 'C') ? 'border-x border-slate-200 bg-slate-100/50 text-slate-600' : '' }}">
                            {{ $nombreMes }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                
                <!-- 1. Asistencia Estudiantes (Métricas Mensuales) -->
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-[#3d2c1d] sticky left-0 bg-white z-10 border-r border-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.02)]">
                        1. Asistencia Estudiantil
                    </td>
                    @foreach($meses as $num => $nombreMes)
                        @php $valor = $muralData['asistencia_estudiantes'][$num] ?? null; @endphp
                        <td class="px-3 py-4 text-center {{ str_contains($nombreMes, 'C') ? 'border-x border-slate-100 bg-slate-50/50' : '' }}">
                            @if($valor !== null)
                                <span class="inline-flex w-12 items-center justify-center px-2 py-1 rounded-md font-black text-xs {{ $badgeColor($valor) }}">
                                    {{ $valor }}%
                                </span>
                            @else
                                <span class="text-slate-300 font-black text-xs">---</span>
                            @endif
                        </td>
                    @endforeach
                </tr>

                <!-- 2. Asistencia Docente -->
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-[#3d2c1d] sticky left-0 bg-white z-10 border-r border-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.02)]">
                        2. Asistencia Docente
                    </td>
                    @foreach($meses as $num => $nombreMes)
                        @php $valor = $muralData['asistencia_docentes'][$num] ?? null; @endphp
                        <td class="px-3 py-4 text-center {{ str_contains($nombreMes, 'C') ? 'border-x border-slate-100 bg-slate-50/50' : '' }}">
                            @if($valor !== null)
                                <span class="inline-flex w-12 items-center justify-center px-2 py-1 rounded-md font-black text-xs {{ $badgeColor($valor) }}">
                                    {{ $valor }}%
                                </span>
                            @else
                                <span class="text-slate-300 font-black text-xs">---</span>
                            @endif
                        </td>
                    @endforeach
                </tr>

                <!-- 3. Índices de Aprobados (Solo en meses de Corte) -->
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-[#3d2c1d] sticky left-0 bg-white z-10 border-r border-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.02)]">
                        3. Índices de Aprobados Limpios
                    </td>
                    @foreach($meses as $num => $nombreMes)
                        @php $valor = $muralData['aprobados_cortes'][$num] ?? null; @endphp
                        
                        @if(str_contains($nombreMes, 'C'))
                            <td class="px-3 py-4 text-center border-x border-slate-100 bg-slate-50/50">
                                @if($valor !== null)
                                    <span class="inline-flex w-12 items-center justify-center px-2 py-1 rounded-md font-black text-xs {{ $badgeColor($valor) }}">
                                        {{ $valor }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-400 text-[10px] font-bold tracking-widest uppercase">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Pnd
                                    </span>
                                @endif
                            </td>
                        @else
                            <td class="px-3 py-4 text-center text-slate-200 text-xs font-black">·</td>
                        @endif
                    @endforeach
                </tr>

                <!-- 4. Promedio General de Notas (Solo en meses de Corte) -->
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-[#3d2c1d] sticky left-0 bg-white z-10 border-r border-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.02)]">
                        4. Promedio de Calificaciones
                    </td>
                    @foreach($meses as $num => $nombreMes)
                        @php $valor = $muralData['promedio_cortes'][$num] ?? null; @endphp
                        
                        @if(str_contains($nombreMes, 'C'))
                            <td class="px-3 py-4 text-center border-x border-slate-100 bg-slate-50/50">
                                @if($valor !== null)
                                    <span class="inline-flex w-12 items-center justify-center px-2 py-1 rounded-md font-black text-xs {{ $badgeColor($valor) }}">
                                        {{ $valor }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-slate-400 text-[10px] font-bold tracking-widest uppercase">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Pnd
                                    </span>
                                @endif
                            </td>
                        @else
                            <td class="px-3 py-4 text-center text-slate-200 text-xs font-black">·</td>
                        @endif
                    @endforeach
                </tr>

                <!-- 5. Participación de Padres -->
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-[#3d2c1d] sticky left-0 bg-white z-10 border-r border-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.02)]">
                        5. Participación de Padres
                    </td>
                    @foreach($meses as $num => $nombreMes)
                        @php $valor = $muralData['padres'][$num] ?? null; @endphp
                        <td class="px-3 py-4 text-center {{ str_contains($nombreMes, 'C') ? 'border-x border-slate-100 bg-slate-50/50' : '' }}">
                            @if($valor !== null)
                                <span class="inline-flex w-12 items-center justify-center px-2 py-1 rounded-md font-black text-xs {{ $badgeColor($valor) }}">
                                    {{ $valor }}%
                                </span>
                            @else
                                <span class="text-slate-300 font-black text-xs">---</span>
                            @endif
                        </td>
                    @endforeach
                </tr>

                <!-- 6. Avance de Contenido -->
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-[#3d2c1d] sticky left-0 bg-white z-10 border-r border-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.02)]">
                        6. Avance Programático
                    </td>
                    @foreach($meses as $num => $nombreMes)
                        @php $valor = $muralData['avance_contenido'][$num] ?? null; @endphp
                        <td class="px-3 py-4 text-center {{ str_contains($nombreMes, 'C') ? 'border-x border-slate-100 bg-slate-50/50' : '' }}">
                            @if($valor !== null)
                                <span class="inline-flex w-12 items-center justify-center px-2 py-1 rounded-md font-black text-xs {{ $badgeColor($valor) }}">
                                    {{ $valor }}%
                                </span>
                            @else
                                <span class="text-slate-300 font-black text-xs">---</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>