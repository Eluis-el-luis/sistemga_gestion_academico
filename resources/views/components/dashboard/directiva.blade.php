<div class="flex flex-col sm:flex-row gap-4 mb-6">
    <div class="w-full sm:w-64 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Alumnos Activos</p>
            <h4 class="text-2xl font-black text-[#3d2c1d] dark:text-white">{{ $totalMatriculados ?? 0 }}</h4>
        </div>
        <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-500 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
        </div>
    </div>

    <div class="w-full sm:w-64 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Docentes</p>
            <h4 class="text-2xl font-black text-[#3d2c1d] dark:text-white">{{ $totalPersonal ?? 0 }}</h4>
        </div>
        <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-950/50 text-emerald-500 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('academico.usuarios.index') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Personal</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Directorio</span>
        </div>
    </a>

    <a href="{{ route('academico.aulas.index') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Aulas</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Asignaciones</span>
        </div>
    </a>

    <a href="{{ route('academico.visor.index') }}" class="text-left group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer w-full">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Horarios</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Visor Global</span>
        </div>
    </a>

    <a href="{{ route('academico.alumnos.index') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Alumnos</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Directorio</span>
        </div>
    </a>

    <a href="{{ route('academico.matriculas.index') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Matrículas</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Historial General</span>
        </div>
    </a>

    <a href="{{ route('academico.notas.index') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Calificaciones</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Centro de Supervisión</span>
        </div>
    </a>

    <button type="button" @click.prevent="$dispatch('abrir-modal-asistencia')" class="text-left group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer w-full">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Asistencia</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Monitoreo Global</span>
        </div>
    </button>
</div>

@php
    $badgeColor = function ($valor) {
        if ($valor === null || $valor === '') {
            return 'bg-slate-100 dark:bg-slate-800 text-slate-400 opacity-60';
        }
        if ($valor >= 90) {
            return 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 shadow-sm';
        }
        if ($valor >= 75) {
            return 'bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 shadow-sm';
        }
        return 'bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 shadow-sm';
    };

    $meses = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun (C1)',
        7 => 'Jul', 8 => 'Ago', 9 => 'Sep (C2)', 10 => 'Oct', 11 => 'Nov (C3)', 12 => 'Dic',
    ];
    $modalidadesMural = \App\Models\Modalidad::orderBy('id')->get();
@endphp

<div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden mt-8">
    <div class="bg-[#FFFDF5] dark:bg-slate-800/70 border-b border-[#e6ac27]/20 dark:border-slate-700 px-6 py-5 sm:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div>
            <h3 class="text-lg font-black text-[#3d2c1d] dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Mural de Seguimiento Institucional
            </h3>
            <p class="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest">Basado en registros oficiales del sistema</p>
        </div>

        <form method="GET" class="w-full sm:w-auto">
            <select name="modalidad_mural" onchange="this.form.submit()" class="w-full sm:w-64 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 rounded-xl shadow-sm text-sm font-bold text-slate-600 dark:text-slate-200 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer">
                <option value="">Vista Global (Centro Educativo)</option>
                @foreach ($modalidadesMural as $mod)
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
                <tr class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-100 dark:border-slate-800 text-[10px] uppercase tracking-widest text-slate-400 font-black">
                    <th class="px-6 py-4 sticky left-0 bg-slate-50 dark:bg-slate-900 z-10 w-1/4">Indicador de Desempeño</th>
                    @foreach ($meses as $num => $nombreMes)
                        <th class="px-3 py-4 text-center {{ str_contains($nombreMes, 'C') ? 'border-x border-slate-200 dark:border-slate-700 bg-slate-100/60 dark:bg-slate-800 text-slate-600 dark:text-slate-300' : '' }}">
                            {{ $nombreMes }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @php
                    $filasMural = [
                        ['titulo' => '1. Asistencia Estudiantil', 'clave' => 'asistencia_estudiantes', 'solo_corte' => false, 'sufijo' => '%'],
                        ['titulo' => '2. Asistencia Docente', 'clave' => 'asistencia_docentes', 'solo_corte' => false, 'sufijo' => '%'],
                        ['titulo' => '3. Índices de Aprobados Limpios', 'clave' => 'aprobados_cortes', 'solo_corte' => true, 'sufijo' => '%'],
                        ['titulo' => '4. Promedio de Calificaciones', 'clave' => 'promedio_cortes', 'solo_corte' => true, 'sufijo' => ''],
                        ['titulo' => '5. Participación de Padres', 'clave' => 'padres', 'solo_corte' => false, 'sufijo' => '%'],
                        ['titulo' => '6. Avance Programático', 'clave' => 'avance_contenido', 'solo_corte' => false, 'sufijo' => '%'],
                    ];
                @endphp

                @foreach ($filasMural as $fila)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <td class="px-6 py-4 font-bold text-[#3d2c1d] dark:text-slate-200 sticky left-0 bg-white dark:bg-slate-900 z-10 border-r border-slate-50 dark:border-slate-800">
                            {{ $fila['titulo'] }}
                        </td>
                        @foreach ($meses as $num => $nombreMes)
                            @php
                                $esCorte = str_contains($nombreMes, 'C');
                                $valor = $muralData[$fila['clave']][$num] ?? null;
                            @endphp
                            @if (!$fila['solo_corte'] || $esCorte)
                                <td class="px-3 py-4 text-center {{ $esCorte ? 'border-x border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30' : '' }}">
                                    @if ($valor !== null)
                                        <span class="inline-flex w-12 items-center justify-center px-2 py-1 rounded-md font-black text-xs {{ $badgeColor($valor) }}">
                                            {{ $valor }}{{ $fila['sufijo'] }}
                                        </span>
                                    @elseif ($fila['solo_corte'])
                                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pnd</span>
                                    @else
                                        <span class="text-slate-400 font-black text-xs">---</span>
                                    @endif
                                </td>
                            @else
                                <td class="px-3 py-4 text-center text-slate-300 dark:text-slate-600 text-xs font-black">·</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>