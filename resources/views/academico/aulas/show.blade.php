<x-app-layout>
    <!-- ================= 1. CABECERA ================= -->
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.aulas.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver a Gestión de Aulas">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Detalles del Aula: <span class="text-[#e6ac27]">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
                </h2>
                <p class="text-sm font-medium text-slate-500 mt-0.5">
                    {{ $aula->anioEscolar->nombre }} • {{ $aula->modalidad->nombre }} • Turno {{ ucfirst($aula->turno) }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                @can('update', $aula)
                    <button x-data x-on:click.prevent="$dispatch('open-modal', 'modal-agregar-materia')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-xl font-black text-sm shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Materia Extraordinaria
                    </button>
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- ================= 2. CONTENIDO PRINCIPAL ================= -->
    <div class="pb-12 pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Tarjeta de Resumen del Aula -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-3xl overflow-hidden flex flex-col md:flex-row">
                <div class="bg-[#FFFDF5] p-8 md:w-1/4 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#e6ac27]/20">
                    <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Periodo Lectivo</span>
                    <h3 class="text-2xl font-black text-[#3d2c1d]">{{ $aula->anioEscolar->nombre }}</h3>
                    <span class="mt-3 inline-flex items-center px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200 self-start">
                        {{ $aula->modalidad->nombre }}
                    </span>
                </div>

                <div class="p-8 grid grid-cols-1 sm:grid-cols-4 gap-8 flex-grow">
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Sección y Turno</p>
                        <p class="font-black text-slate-800 text-lg">{{ $aula->nombre }} <span class="text-slate-300 font-normal mx-1">|</span> {{ ucfirst($aula->turno) }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Capacidad / Cupo</p>
                        <p class="font-black text-slate-800 text-lg">{{ $aula->cupo }} <span class="text-slate-500 font-bold text-sm">Alumnos máx.</span></p>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Docente Titular (Guía)</p>
                        <div class="flex items-center gap-3 mt-1.5">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center font-black text-xs">
                                {{ $aula->docenteGuia && $aula->docenteGuia->usuario ? substr($aula->docenteGuia->usuario->nombre_completo ?? 'D', 0, 2) : '?' }}
                            </div>
                            <p class="font-bold text-slate-800">{{ $aula->docenteGuia && $aula->docenteGuia->usuario ? $aula->docenteGuia->usuario->nombre_completo : 'No asignado' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Horas Académicas</p>
                        <p class="font-black text-slate-800 text-lg">{{ $horasTotales ?? 0 }} <span class="text-slate-500 font-bold text-sm">h / semana</span></p>
                    </div>
                </div>
            </div>

            <!-- PESTAÑAS -->
            <div x-data="{ activeTab: 'alumnos' }" class="bg-white border border-slate-200 shadow-sm rounded-3xl overflow-hidden">
                <div class="border-b border-slate-200">
                    <nav class="flex" aria-label="Tabs">
                        <button x-on:click="activeTab = 'alumnos'"
                                :class="activeTab === 'alumnos' ? 'border-[#e6ac27] text-[#e6ac27] border-b-2' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2'"
                                class="px-6 py-4 font-black text-sm uppercase tracking-wider border-b-2 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Alumnos Matriculados
                        </button>
                        <button x-on:click="activeTab = 'horario'"
                                :class="activeTab === 'horario' ? 'border-[#e6ac27] text-[#e6ac27] border-b-2' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2'"
                                class="px-6 py-4 font-black text-sm uppercase tracking-wider border-b-2 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Horario del Aula
                        </button>
                        <button x-on:click="activeTab = 'asignaturas'"
                                :class="activeTab === 'asignaturas' ? 'border-[#e6ac27] text-[#e6ac27] border-b-2' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2'"
                                class="px-6 py-4 font-black text-sm uppercase tracking-wider border-b-2 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            Asignaturas y Docentes
                        </button>
                        <button x-on:click="activeTab = 'evaluaciones'"
                                :class="activeTab === 'evaluaciones' ? 'border-[#e6ac27] text-[#e6ac27] border-b-2' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 border-b-2'"
                                class="px-6 py-4 font-black text-sm uppercase tracking-wider border-b-2 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            Evaluaciones
                        </button>
                    </nav>
                </div>

                <!-- PANEL 1: ALUMNOS MATRICULADOS -->
                <div x-show="activeTab === 'alumnos'" x-transition class="p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <h3 class="font-black text-xl text-[#3d2c1d]">Alumnos Matriculados</h3>
                            <p class="text-sm text-slate-500 mt-1">Listado de estudiantes con matrícula activa en este aula ({{ $matriculas->count() }}).</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-black uppercase tracking-wider">
                            {{ $matriculas->count() }} {{ $matriculas->count() == 1 ? 'alumno' : 'alumnos' }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 bg-white">
                            <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-[11px] font-black tracking-widest border-b border-slate-200">
                                <tr>
                                    <th class="px-8 py-5 text-left">Nombre del Estudiante</th>
                                    <th class="px-6 py-5 text-left">Código (CUP)</th>
                                    <th class="px-6 py-5 text-center">Estado</th>
                                    <th class="px-6 py-5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($matriculas as $matricula)
                                    <?php $alumno = $matricula->alumno; ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors">
                                        <td class="px-8 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 border border-amber-200 flex items-center justify-center font-black text-xs">
                                                    {{ substr($alumno->nombre_completo ?? 'A', 0, 1) }}
                                                </div>
                                                <span class="font-bold text-slate-800">{{ $alumno->nombre_completo }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 font-mono text-sm text-slate-500">{{ $alumno->codigo_unico_persona ?? '—' }}</td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg border bg-emerald-50 text-emerald-700 border-emerald-200/60">
                                                Activo
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <a href="{{ route('academico.alumnos.show', $alumno->id) ?? '#' }}"
                                               class="inline-flex items-center gap-1.5 text-xs font-black text-[#e6ac27] hover:text-[#c48e1b] underline underline-offset-4">
                                                Ver expediente
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-8 py-16 text-center text-slate-500">
                                            <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                            <p class="font-black text-[#3d2c1d] text-lg">Sin alumnos matriculados</p>
                                            <p class="text-sm font-medium mt-1">No hay estudiantes con matrícula activa en este aula.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PANEL 2: HORARIO DEL AULA -->
                <div x-show="activeTab === 'horario'" x-transition class="p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <h3 class="font-black text-xl text-[#3d2c1d]">Horario del Aula</h3>
                            <p class="text-sm text-slate-500 mt-1">Bloques horarios asignados a este salón.</p>
                        </div>
                        <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Imprimir / PDF
                        </button>
                    </div>

                    <style>
                        @media print {
                            @page { size: letter landscape; margin: 10mm; }
                            .print:hidden { display: none !important; }
                        }
                    </style>

                    <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse text-sm table-fixed">
                                <thead>
                                    <tr class="bg-[#3d2c1d] text-white">
                                        <th class="border border-[#3d2c1d] px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-[#e6ac27] w-40">Hora</th>
                                        @foreach($dias as $dia)
                                            <th class="border border-[#3d2c1d] px-3 py-3 text-center text-[11px] font-black uppercase tracking-widest text-white">{{ $dia }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($matriz as $fila)
                                        @php $bloque = $fila['bloque']; @endphp
                                        <tr class="{{ $bloque->es_recreo ? 'bg-[#FFFDF5]' : 'bg-white' }}">
                                            <td class="border border-slate-200 px-3 py-3 whitespace-nowrap {{ $bloque->es_recreo ? 'bg-amber-50/80' : 'bg-slate-50/60' }}">
                                                <span class="block font-black text-xs {{ $bloque->es_recreo ? 'text-amber-700' : 'text-[#3d2c1d]' }}">{{ $bloque->nombre }}</span>
                                                <span class="block text-[10px] font-bold text-slate-500 mt-0.5">
                                                    {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                                </span>
                                            </td>

                                            @if($bloque->es_recreo)
                                                <td colspan="{{ count($dias) }}" class="border border-slate-200 px-4 py-2 text-center bg-amber-50/70">
                                                    <span class="text-[10px] font-black uppercase tracking-[0.25em] text-amber-700">— {{ $bloque->nombre }} —</span>
                                                </td>
                                            @else
                                                @foreach($dias as $dia)
                                                    @php $horario = $fila['dias'][$dia] ?? null; @endphp
                                                    @if($horario)
                                                        <td class="border border-slate-200 px-2 py-2.5 text-center align-middle group relative">
                                                            <span class="block font-black text-[#3d2c1d] text-xs leading-tight">{{ $horario->aulaAsignaturaDocente->asignatura->nombre ?? '—' }}</span>
                                                            <span class="inline-block text-[10px] font-bold text-amber-700 bg-amber-50/80 px-2 py-0.5 rounded-md mt-1">
                                                                Prof. {{ $horario->aulaAsignaturaDocente->docente ? \Str::words($horario->aulaAsignaturaDocente->docente->usuario->nombre_completo, 2, '') : '—' }}
                                                            </span>
                                                        </td>
                                                    @else
                                                        <td class="border border-slate-200 px-2 py-2.5 text-center text-slate-300 font-bold text-xs">—</td>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($dias) + 1 }}" class="border border-slate-200 px-4 py-12 text-center text-stone-500 font-bold">
                                                No hay bloques de horario definidos para este aula.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PANEL 3: ASIGNATURAS Y DOCENTES -->
                <div x-show="activeTab === 'asignaturas'" x-transition class="p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <h3 class="font-black text-xl text-[#3d2c1d]">Asignaturas y Carga Académica</h3>
                            <p class="text-sm text-slate-500 mt-1">Listado de asignaturas impartidas en este aula y sus docentes asignados.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100 bg-white">
                            <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-[11px] font-black tracking-widest border-b border-slate-200">
                                <tr>
                                    <th class="px-8 py-5 text-left">Asignatura</th>
                                    <th class="px-6 py-5 text-center">Horas / Sem.</th>
                                    <th class="px-8 py-5 text-left">Docente Asignado</th>
                                    <th class="px-6 py-5 text-center">Estado</th>
                                    <th class="px-6 py-5 text-right">Horarios</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @forelse($asignaciones as $asignacion)
                                    <tr class="hover:bg-slate-50/80 transition-colors {{ !$asignacion->activo ? 'opacity-60' : '' }}">
                                        <td class="px-8 py-5">
                                            <span class="font-black text-[#3d2c1d] text-base block">{{ $asignacion->asignatura->nombre }}</span>
                                            @if($asignacion->asignatura->es_extracurricular)
                                                <span class="mt-2 inline-flex px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest bg-purple-50 text-purple-700 border border-purple-200">Extracurricular</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-center font-black text-slate-500 text-base">{{ number_format($asignacion->horas_semanales, 0) }}h</td>
                                        <td class="px-8 py-5">
                                            @if($asignacion->docente_id && $asignacion->docente && $asignacion->docente->usuario)
                                                <div class="flex items-center gap-3">
                                                    <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center font-black text-[10px]">
                                                        {{ substr($asignacion->docente->usuario->nombre_completo ?? 'D', 0, 1) }}
                                                    </div>
                                                    <span class="font-bold text-slate-800">{{ $asignacion->docente->usuario->nombre_completo }}</span>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-rose-600 text-xs font-black uppercase tracking-widest bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                    Sin docente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg border {{ $asignacion->activo ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                                {{ $asignacion->activo ? 'Activa' : 'Inactiva' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <span class="text-xs font-bold text-slate-500">{{ $asignacion->horarios->count() }} bloque{{ $asignacion->horarios->count() != 1 ? 's' : '' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-8 py-16 text-center text-slate-500">
                                            <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                            <p class="font-black text-[#3d2c1d] text-lg">Sin asignaturas asignadas</p>
                                            <p class="text-sm font-medium mt-1">Este aula no tiene asignaturas cargadas en su carga académica.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

                <!-- PANEL 4: EVALUACIONES POR CORTE -->
                <div x-show="activeTab === 'evaluaciones'" x-transition class="p-8">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <div>
                            <h3 class="font-black text-xl text-[#3d2c1d]">Evaluaciones por Corte</h3>
                            <p class="text-sm text-slate-500 mt-1">Resumen de notas registradas por corte evaluativo en este aula.</p>
                        </div>
                    </div>

                    <div class="p-8">
                        @if(count($evaluacionesPorCorte) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($evaluacionesPorCorte as $evaluacion)
                                    <div class="bg-[#FFFDF5] rounded-2xl p-5 border border-[#e6ac27]/20">
                                        <h4 class="font-black text-[#3d2c1d] text-lg mb-1">{{ $evaluacion['corte']->nombre ?? 'Corte ' . $evaluacion['corte']->numero }}</h4>
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-4">{{ $evaluacion['corte']->descripcion ?? '' }}</p>
                                        <div class="grid grid-cols-2 gap-3 text-center">
                                            <div>
                                                <p class="text-2xl font-black text-emerald-600">{{ $evaluacion['promedio'] ?? 0 }}</p>
                                                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mt-1">Promedio</p>
                                            </div>
                                            <div>
                                                <p class="text-2xl font-black text-rose-600">{{ $evaluacion['reprobados'] }}</p>
                                                <p class="text-[10px] font-bold text-rose-500 uppercase tracking-wider mt-1">Reprobados</p>
                                            </div>
                                        </div>
                                        <div class="mt-4 w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 rounded-full bg-[#e6ac27]"
                                                 style="width: {{ $evaluacion['total_alumnos'] > 0 ? round(($evaluacion['alumnos_con_nota'] / $evaluacion['total_alumnos']) * 100) : 0 }}%"></div>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">
                                            {{ $evaluacion['alumnos_con_nota'] }} / {{ $evaluacion['total_alumnos'] }} alumnos con nota
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="px-8 py-16 text-center text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="font-black text-[#3d2c1d] text-lg">Sin evaluaciones registradas</p>
                                <p class="text-sm font-medium mt-1">Aún no hay notas cargadas por corte en este aula.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- MODAL: AGREGAR MATERIA EXTRAORDINARIA -->
            <x-modal name="modal-agregar-materia" focusable maxWidth="md">
                <form method="post" action="{{ route('academico.aulas.asignaturas.store', $aula->id) }}">
                    @csrf
                    <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                        <h2 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Materia Extraordinaria
                        </h2>
                    </div>

                    <div class="p-8 space-y-6">
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Asignatura <span class="text-red-500">*</span></label>
                            <select name="asignatura_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm font-medium transition-colors" required>
                                <option value="">Seleccione una asignatura...</option>
                                @foreach(\App\Models\Asignatura::orderBy('nombre')->get() as $asig)
                                    <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Horas Semanales <span class="text-red-500">*</span></label>
                            <input type="number" name="horas_semanales" value="2" min="1" max="40" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm font-black text-[#3d2c1d] transition-colors" required>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-8 py-5 flex justify-end gap-4 border-t border-slate-100 rounded-b-3xl">
                        <button type="button" x-on:click="$dispatch('close')" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">Cancelar</button>
                        <button type="submit" class="px-6 py-2.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md transition-all">Guardar</button>
                    </div>
                </form>
            </x-modal>

        </div>
    </div>
</x-app-layout>
