<x-app-layout>
    @php
        $esDirectiva = auth()->user()->hasAnyRole(['Director', 'Subdirector']);
        $esCoordinador = isset($esCoordinador) ? $esCoordinador : auth()->user()->hasRole('Coordinador');
    @endphp
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] dark:hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] dark:text-white leading-tight">
                        {{ $esDirectiva ? 'Supervisión de Evaluaciones' : ($esCoordinador ? 'Supervisión de Evaluaciones' : 'Centro de Calificaciones') }}
                    </h2>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">
                        {{ $esDirectiva ? 'Monitorea el progreso de configuración de todos los docentes.' : ($esCoordinador ? 'Vista de solo lectura: aulas de tu modalidad, sus asignaturas y evaluaciones.' : 'Selecciona una de tus clases asignadas para evaluar o configurar actividades.') }}
                    </p>
                </div>
            </div>
            
            @if($esDirectiva)
                <!-- BOTÓN EXCLUSIVO PARA DIRECCIÓN Y SUBDIRECCIÓN -->
                <a href="{{ route('academico.cortes.index') }}" class="hidden md:inline-flex items-center gap-2 bg-[#3d2c1d] dark:bg-slate-800 hover:bg-stone-800 dark:hover:bg-slate-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-transform transform hover:-translate-y-0.5 text-sm border border-transparent dark:border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Definir Criterios Base
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] dark:bg-slate-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filtro Inteligente (Exclusivo Dirección / Subdirección) -->
            @if($esDirectiva)
                <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm mb-6">
                    <form method="GET" action="{{ route('academico.notas.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                        <div class="md:col-span-5">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Paso 1: Seleccione Grado</label>
                            <select name="grado_id" id="filtro_grado" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 py-3 text-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] font-medium text-slate-700 dark:text-slate-300">
                                <option value="">Todos los grados...</option>
                                @foreach ($grados as $grado)
                                    <option value="{{ $grado->id }}" {{ $grado->id == $gradoSeleccionadoId ? 'selected' : '' }}>
                                        {{ $grado->modalidad->nombre ?? 'N/A' }} - {{ $grado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-5">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Paso 2: Seleccione Aula</label>
                            <select name="aula_id" id="filtro_aula" class="w-full rounded-xl border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 py-3 text-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] font-medium text-slate-700 dark:text-slate-300">
                                <option value="">Seleccione una sección...</option>
                                @foreach ($aulas as $aula)
                                    <option value="{{ $aula->id }}" data-grado="{{ $aula->grado_id }}" {{ $aulaSeleccionadaId == $aula->id ? 'selected' : '' }}>
                                        Sección "{{ $aula->nombre }}"
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2 flex gap-2">
                            <button type="submit" class="w-full py-3 bg-[#3d2c1d] dark:bg-slate-700 text-white rounded-xl hover:bg-slate-800 dark:hover:bg-slate-600 text-sm font-black shadow-sm transition-all transform hover:-translate-y-0.5 border border-transparent dark:border-slate-600">
                                Filtrar
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if($esCoordinador)
                <!-- VISTA COORDINADOR: Segmentado por Aulas -> Asignaturas -->
                <div class="space-y-6">
                    @php
                        $anioActivo = \App\Models\AnioEscolar::where('estado', 'activo')->first();
                    @endphp
                    
                    @if($anioActivo)
                        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 mb-4 flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-xs text-blue-800 font-medium">Vista de supervisión: Solo lectura. Seleccione una asignatura para ver sus actividades, configuración y notas (sin permisos de edición).</p>
                        </div>
                    @endif

                    @forelse($aulas as $aula)
                        @php
                            $asignacionesAula = $asignacionesPorAula[$aula->id] ?? collect();
                        @endphp
                        
                        @if($asignacionesAula->isNotEmpty())
                            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                                <!-- Header del Aula -->
                                <div class="bg-[#FFFDF5] dark:bg-slate-700/50 px-6 py-4 border-b border-slate-200 dark:border-slate-600 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        </div>
                                        <div>
                                            <h3 class="font-black text-xl text-[#3d2c1d] dark:text-white">{{ $aula->grado->nombre ?? '' }} - Sección "{{ $aula->nombre }}"</h3>
                                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                                {{ $aula->modalidad->nombre ?? '' }} • {{ ucfirst($aula->turno ?? '') }} • Docente Guía: {{ $aula->docenteGuia->usuario->nombre_completo ?? 'No asignado' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ $asignacionesAula->count() }} asignatura{{ $asignacionesAula->count() != 1 ? 's' : '' }}
                                    </span>
                                </div>

                                <!-- Tabla de Asignaturas del Aula -->
                                <div class="overflow-x-auto">
                                    <table class="w-full divide-y divide-slate-200 dark:divide-slate-700">
                                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Asignatura</th>
                                                <th class="px-6 py-3 text-left text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Docente</th>
                                                <th class="px-6 py-3 text-center text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actividades</th>
                                                <th class="px-6 py-3 text-center text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado Parcial</th>
                                                <th class="px-6 py-3 text-right text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                            @foreach($asignacionesAula as $asig)
                                                @php
                                                    // Obtener cortes del año activo para esta asignación
                                                    $cortesAsig = \App\Models\CorteEvaluativo::with('anioEscolar')
                                                        ->whereHas('anioEscolar', fn($q) => $q->where('estado', 'activo'))
                                                        ->get();
                                                    $tieneActividades = false;
                                                    $corteActivoNombre = 'Sin cortes';
                                                    foreach ($cortesAsig as $corte) {
                                                        $count = \App\Models\ActividadEvaluativa::where('aula_asignatura_docente_id', $asig->id)
                                                            ->where('corte_evaluativo_id', $corte->id)
                                                            ->count();
                                                        if ($count > 0) {
                                                            $tieneActividades = true;
                                                            $corteActivoNombre = $corte->nombre ?? 'Corte ' . $corte->numero;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                                    <td class="px-6 py-4">
                                                        <div class="font-black text-[#3d2c1d] dark:text-white">{{ $asig->asignatura->nombre }}</div>
                                                        @if($asig->asignatura->es_extracurricular)
                                                            <span class="mt-1 inline-block px-2 py-0.5 rounded text-[10px] font-black bg-purple-50 text-purple-700 border border-purple-200">Extracurricular</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($asig->docente && $asig->docente->usuario)
                                                            <div class="flex items-center gap-2">
                                                                <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 flex items-center justify-center font-black text-[10px]">
                                                                    {{ substr($asig->docente->usuario->nombre_completo ?? 'D', 0, 1) }}
                                                                </div>
                                                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ $asig->docente->usuario->nombre_completo }}</span>
                                                            </div>
                                                        @else
                                                            <span class="text-rose-600 text-xs font-black uppercase tracking-wider bg-rose-50 px-2 py-1 rounded border border-rose-100">Sin docente asignado</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-center">
                                                        @php
                                                            $totalActividades = \App\Models\ActividadEvaluativa::where('aula_asignatura_docente_id', $asig->id)->count();
                                                        @endphp
                                                        <span class="font-black text-lg {{ $totalActividades > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $totalActividades }}</span>
                                                        @if($totalActividades > 0)
                                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 block">configuradas</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-center">
                                                        @if($tieneActividades)
                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                                {{ $corteActivoNombre }}
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-500 border border-slate-200">Sin configurar</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-right">
                                                        <!-- Solo botón VER para Coordinador -->
                                                        <a href="{{ route('academico.notas.actividades.index', ['asignacion' => $asig->id, 'corte_id' => request('corte_id') ?? '']) }}" 
                                                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-black text-sm rounded-xl transition-colors border border-slate-200 dark:border-slate-600">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                            Ver / Auditar
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="col-span-full bg-white dark:bg-slate-800 p-12 text-center rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm">
                            <div class="w-14 h-14 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-700 text-slate-300 dark:text-slate-600">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <p class="text-lg font-black text-[#3d2c1d] dark:text-white">No hay aulas en tu modalidad</p>
                            <p class="text-sm font-medium text-slate-400 dark:text-slate-500 mt-1">No se encontraron aulas asignadas a la modalidad que coordinas.</p>
                        </div>
                    @endforelse
                </div>
            @else
                <!-- VISTA DOCENTE GUÍA / DOCENTE POR ASIGNATURA -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                    @forelse($asignaciones as $asignacion_item)
                        <!-- TARJETA DE ASIGNATURA -->
                        <a href="{{ $esDirectiva ? route('academico.notas.actividades.index', $asignacion_item->id) : route('academico.notas.evaluar', $asignacion_item->id) }}" class="group bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm hover:border-[#e6ac27] dark:hover:border-[#e6ac27] hover:shadow-md transition-all relative overflow-hidden flex flex-col justify-between cursor-pointer h-full">
                            <div class="absolute top-0 right-0 w-28 h-full bg-gradient-to-l from-[#e6ac27]/5 dark:from-[#e6ac27]/10 to-transparent pointer-events-none"></div>
                            
                            <div class="relative z-10">
                                <span class="inline-block px-3 py-1 bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-[10px] font-black uppercase tracking-widest rounded-lg mb-3 border border-slate-100 dark:border-slate-600">
                                    {{ $asignacion_item->aula->grado->nombre ?? '' }} - {{ $asignacion_item->aula->nombre ?? '' }}
                                </span>
                                <h4 class="text-xl font-black text-[#3d2c1d] dark:text-white group-hover:text-[#e6ac27] dark:group-hover:text-[#e6ac27] transition-colors leading-tight">
                                    {{ $asignacion_item->asignatura->nombre ?? '' }}
                                </h4>

                                @if($esDirectiva)
                                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 mt-3 uppercase tracking-widest flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        {{ $asignacion_item->docente->usuario->nombre_completo ?? 'SIN DOCENTE' }}
                                    </p>
                                @endif
                            </div>
                            
                            <!-- CTA -->
                            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 relative z-10 flex items-center justify-between text-sm font-black text-[#e6ac27]">
                                <span>{{ $esDirectiva ? 'Auditar Actividades' : 'Evaluar / Configurar' }}</span>
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </div>
                        </a>
                    @empty
                        <!-- ESTADO VACÍO -->
                        <div class="col-span-full bg-white dark:bg-slate-800 p-12 text-center rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col items-center justify-center">
                            <div class="w-14 h-14 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mb-4 border border-slate-100 dark:border-slate-700 text-slate-300 dark:text-slate-600">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            </div>
                            <p class="text-lg font-black text-[#3d2c1d] dark:text-white">No hay asignaturas disponibles</p>
                            <p class="text-sm font-medium text-slate-400 dark:text-slate-500 mt-1 max-w-md">No tienes carga horaria asignada en este momento o el aula seleccionada no posee materias vinculadas.</p>
                        </div>
                    @endforelse
                </div>
            @endif

        </div>
    </div>

    @if($esDirectiva)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const gradoSelect = document.getElementById('filtro_grado');
                const aulaSelect = document.getElementById('filtro_aula');

                function actualizarAulas(forzarReseteo = false) {
                    const gradoIdSeleccionado = gradoSelect.value;
                    let primeraOpcionValida = null;
                    let seleccionActualValida = false;

                    Array.from(aulaSelect.options).forEach(opcion => {
                        if (opcion.value === "") return; 
                        const gradoDelAula = opcion.getAttribute('data-grado');

                        if (gradoIdSeleccionado === "" || gradoDelAula === gradoIdSeleccionado) {
                            opcion.style.display = '';
                            opcion.hidden = false;
                            if (!primeraOpcionValida) primeraOpcionValida = opcion;
                            if (opcion.selected) seleccionActualValida = true;
                        } else {
                            opcion.style.display = 'none';
                            opcion.hidden = true;
                        }
                    });

                    if (forzarReseteo || !seleccionActualValida) {
                        if (primeraOpcionValida) {
                            primeraOpcionValida.selected = true; 
                        } else {
                            aulaSelect.value = ""; 
                        }
                    }
                }

                if(gradoSelect && aulaSelect) {
                    gradoSelect.addEventListener('change', function() {
                        actualizarAulas(true); 
                    });
                    actualizarAulas(false); 
                }
            });
        </script>
    @endif
</x-app-layout>