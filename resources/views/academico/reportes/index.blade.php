<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                {{ __('Centro de Reportes') }}
            </h2>
        </div>
    </x-slot>

    <!-- Cambiamos x-data para manejar las nuevas pestañas estratégicas -->
    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8" x-data="{ tabActiva: 'auditoria' }">

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm font-medium">{{ session('success') }}</div>
        @endif

        <!-- Selector de Año -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <form method="GET" action="{{ route('academico.reportes.index') }}" class="flex items-end gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-medium">
                        @foreach($anios as $an)
                            <option value="{{ $an->id }}" @selected($an->id == ($anio->id ?? null))>{{ $an->nombre }} {{ $an->activo ? '(Activo)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Tarjetas de Resumen (KPIs Superiores) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <h3 class="text-sm font-black text-slate-500 uppercase tracking-widest">Ingreso de Notas</h3>
                <p class="mt-3 text-3xl font-black {{ $resumen['ingreso_notas']['notas_pendientes'] > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    {{ $resumen['ingreso_notas']['notas_pendientes'] }} <span class="text-sm font-bold text-slate-400">pendientes</span>
                </p>
                <p class="text-sm text-slate-500 mt-1">{{ $resumen['ingreso_notas']['porcentaje'] }}% completado</p>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <h3 class="text-sm font-black text-slate-500 uppercase tracking-widest">Población Estudiantil</h3>
                <p class="mt-3 text-3xl font-black text-[#3d2c1d]">{{ $resumen['estudiantes']['matriculados'] }} <span class="text-sm font-bold text-slate-400">matriculados</span></p>
                <p class="text-sm text-rose-500 mt-1">{{ $resumen['estudiantes']['expedientes_incompletos'] }} expedientes incompletos</p>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow">
                <h3 class="text-sm font-black text-slate-500 uppercase tracking-widest">Adopción Digital</h3>
                <p class="mt-3 text-3xl font-black text-[#3d2c1d]">{{ $resumen['padres']['porcentaje_adopcion'] }}%</p>
                <p class="text-sm text-slate-500 mt-1">responsables con contacto registrado</p>
            </div>
        </div>

        <!-- NUEVO MENÚ DE PESTAÑAS ESTRATÉGICAS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            
            <div class="flex space-x-2 border-b border-slate-200 overflow-x-auto bg-slate-50/50">
                <button @click="tabActiva = 'auditoria'" 
                        :class="tabActiva === 'auditoria' ? 'border-[#e6ac27] text-[#3d2c1d] font-black bg-white shadow-[0_-2px_10px_rgba(0,0,0,0.02)]' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-bold'"
                        class="px-6 py-4 border-b-4 text-sm transition-all whitespace-nowrap rounded-t-xl flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Auditoría Interna
                </button>
                <button @click="tabActiva = 'mined'" 
                        :class="tabActiva === 'mined' ? 'border-blue-500 text-blue-900 font-black bg-white shadow-[0_-2px_10px_rgba(0,0,0,0.02)]' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-bold'"
                        class="px-6 py-4 border-b-4 text-sm transition-all whitespace-nowrap rounded-t-xl flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Formatos Oficiales (MINED)
                </button>
                <button @click="tabActiva = 'gestion'" 
                        :class="tabActiva === 'gestion' ? 'border-[#e6ac27] text-[#3d2c1d] font-black bg-white shadow-[0_-2px_10px_rgba(0,0,0,0.02)]' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-bold'"
                        class="px-6 py-4 border-b-4 text-sm transition-all whitespace-nowrap rounded-t-xl flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Administración Estudiantil
                </button>
            </div>

            <div class="p-6 md:p-8">

                <!-- ========================================== -->
                <!-- PESTAÑA 1: AUDITORÍA INTERNA -->
                <!-- ========================================== -->
                <div x-show="tabActiva === 'auditoria'" x-transition.opacity>
                    <p class="text-sm text-slate-500 mb-6 font-medium">Herramientas de monitoreo interno para evaluar el desempeño docente, calificar aulas y revisar métricas de asistencia no oficiales.</p>
                    
                    <!-- Sección Notas / Calificaciones -->
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Auditoría de Calificaciones</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
                        <a href="{{ route('academico.reportes.control-notas') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-500 uppercase tracking-wider mb-1">Cortes Evaluativos</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Seguimiento para maestros</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Revisión de notas ingresadas o pendientes por asignatura, docente o grado.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.notas-pendientes') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-rose-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-700 uppercase tracking-wider mb-1">Alerta Docente</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Notas Pendientes</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Identifica rápidamente a los estudiantes sin nota en un periodo específico.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.notas-globales') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-indigo-100 text-indigo-700 uppercase tracking-wider mb-1">Aulas</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Notas Globales (Sábana)</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Vista panorámica: Un alumno por fila cruzado con todas sus asignaturas.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.notas-por-asignatura') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-violet-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center group-hover:bg-violet-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-violet-100 text-violet-700 uppercase tracking-wider mb-1">Rendimiento</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Métrica por Asignatura</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Promedios generales y tasas de aprobación por materia y maestro.</p>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Sección Asistencia -->
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Monitor de Asistencia</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        
                        <a href="{{ route('academico.reportes.asistencia-global') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Asistencia Diaria Global</h4>
                                    <p class="text-xs text-slate-400 mt-1">Consolidado diario por turno y sección.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.estadisticas-asistencia') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Estadísticas por Fechas</h4>
                                    <p class="text-xs text-slate-400 mt-1">Ausencias y presencias en rangos de tiempo.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.asistencia-seccion-dia') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Por Sección (Día)</h4>
                                    <p class="text-xs text-slate-400 mt-1">Busca el listado de asistencia exacto de un aula.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.asistencia-seccion-rango') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Por Sección (Rango)</h4>
                                    <p class="text-xs text-slate-400 mt-1">Comportamiento del aula en varias fechas.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.asistencia-estudiante') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Ficha Individual</h4>
                                    <p class="text-xs text-slate-400 mt-1">Métricas de inasistencias por estudiante.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- PESTAÑA 2: FORMATOS MINED -->
                <!-- ========================================== -->
                <div x-show="tabActiva === 'mined'" style="display: none;" x-transition.opacity>
                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 mb-6 flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-xs text-blue-800 font-medium">Estos reportes están formateados con las columnas y cálculos exactos requeridos por el Ministerio de Educación de Nicaragua. Diseñados para impresión en formato Horizontal (Landscape).</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <a href="{{ route('academico.reportes.rendimiento-corte') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-700 uppercase tracking-wider mb-1">Cierre de Corte</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Rendimiento REA</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Matrícula Inicial, Actual, Aprobados y Aplazados (1, 2, 3+).</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.mined') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-700 uppercase tracking-wider mb-1">Estadística</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Reporte General MINED</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Cifras globales requeridas para informes estadísticos oficiales.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.avance-programatico') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-700 uppercase tracking-wider mb-1">Docencia</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Avance Programático</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Consolidado de cobertura curricular y unidades impartidas.</p>
                                </div>
                            </div>
                        </a>

                        <!-- Tarjeta: Asignaturas Aprobadas -->
                        <a href="{{ route('academico.reportes.asignaturas-aprobadas') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800 uppercase tracking-wider mb-1">Rendimiento por Materia</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Asignaturas Aprobadas</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Desglose detallado de estudiantes aprobados por asignatura, grado y género (AS y F).</p>
                                </div>
                            </div>
                        </a>

                                                <!-- Tarjeta: Formato Escalas MINED -->
                        <a href="{{ route('academico.reportes.escalas') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-blue-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-700 uppercase tracking-wider mb-1">Estadística Oficial</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Formato Escalas MINED</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Consolidado de escalas de rendimiento por niveles, grados y género (Preescolar, Primaria y Secundaria).</p>
                                </div>
                            </div>
                        </a>

                    </div>
                </div>
                

                <!-- ========================================== -->
                <!-- PESTAÑA 3: ADMINISTRACIÓN ESTUDIANTIL -->
                <!-- ========================================== -->
                <div x-show="tabActiva === 'gestion'" style="display: none;" x-transition.opacity>
                    <p class="text-sm text-slate-500 mb-6 font-medium">Auditoría de expedientes de secretaría, contactos familiares y trazabilidad histórica del estudiante.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        
                        <a href="{{ route('academico.reportes.estudiantes') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center group-hover:bg-rose-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-500 uppercase tracking-wider mb-1">Secretaría</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Auditoría de Matrícula</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Alumnos activos, retirados y listado de expedientes con datos incompletos.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.historial-estudiante') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-slate-700 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-slate-100 text-slate-500 uppercase tracking-wider mb-1">Individual</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Historial Académico</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Buscador para ver la trazabilidad completa de calificaciones de un estudiante.</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('academico.reportes.padres') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-teal-400 transition-all flex flex-col justify-between">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 shrink-0 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center group-hover:bg-teal-500 group-hover:text-white transition-colors">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-black bg-teal-100 text-teal-700 uppercase tracking-wider mb-1">Comunicación</span>
                                    <h4 class="font-black text-[#3d2c1d] leading-tight">Directorio de Responsables</h4>
                                    <p class="text-xs text-slate-400 mt-1 line-clamp-2">Nivel de adopción digital y registro de contactos de padres/tutores.</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>