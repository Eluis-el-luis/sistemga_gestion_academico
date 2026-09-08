<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                {{ __('Centro de Reportes') }}
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

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

        <!-- Tarjetas de Resumen -->
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

        <!-- Grid de Reportes -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @php
                $reportes = [
                    // Control de ingreso de notas
                    ['ruta' => 'reportes.control-notas', 'titulo' => 'Control de Notas', 'desc' => 'Notas ingresadas o pendientes por asignatura, docente o grado.'],
                    ['ruta' => 'reportes.notas-globales', 'titulo' => 'Notas Globales', 'desc' => 'Listado global de notas con filtros por asignatura, docente, grado y corte.'],
                    ['ruta' => 'reportes.notas-pendientes', 'titulo' => 'Notas Pendientes', 'desc' => 'Estudiantes con notas pendientes por periodo evaluativo y docente.'],
                    // Asistencia
                    ['ruta' => 'reportes.asistencia-global', 'titulo' => 'Asistencia Global', 'desc' => 'Asistencia diaria por turno, grado y sección.'],
                    ['ruta' => 'reportes.estadisticas-asistencia', 'titulo' => 'Estadísticas de Asistencia', 'desc' => 'Presencias y ausencias agregadas por rango de fechas.'],
                    ['ruta' => 'reportes.asistencia-seccion-dia', 'titulo' => 'Asistencia por Sección (Día)', 'desc' => 'Asistencia de una sección específica en un día.'],
                    ['ruta' => 'reportes.asistencia-seccion-rango', 'titulo' => 'Asistencia por Sección (Rango)', 'desc' => 'Asistencia de una sección en un rango de fechas.'],
                    ['ruta' => 'reportes.asistencia-estudiante', 'titulo' => 'Asistencia por Estudiante', 'desc' => 'Estadísticas de asistencia individual por estudiante.'],
                    // Rendimiento
                    ['ruta' => 'reportes.notas-por-asignatura', 'titulo' => 'Notas por Asignatura', 'desc' => 'Rendimiento por asignatura, aula y docente.'],
                    ['ruta' => 'reportes.historial-estudiante', 'titulo' => 'Historial por Estudiante', 'desc' => 'Historial completo de notas de un estudiante.'],
                    // Otros
                    ['ruta' => 'reportes.mined', 'titulo' => 'Reportes MINED', 'desc' => 'Reportes estadísticos del Ministerio de Educación.'],
                    ['ruta' => 'reportes.estudiantes', 'titulo' => 'Estudiantes', 'desc' => 'Matrícula, retiros y expedientes incompletos.'],
                    ['ruta' => 'reportes.padres', 'titulo' => 'Responsables y Padres', 'desc' => 'Contacto y adopción digital de responsables.'],
                ];
            @endphp

            @foreach($reportes as $rep)
                <a href="{{ route('academico.'.$rep['ruta']) }}" class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 hover:border-[#e6ac27]/40 hover:shadow-md transition-all group">
                    <h3 class="font-black text-[#3d2c1d] text-lg">{{ $rep['titulo'] }}</h3>
                    <p class="text-sm text-slate-500 mt-2 leading-relaxed">{{ $rep['desc'] }}</p>
                    <span class="inline-flex items-center gap-1 text-sm font-bold text-[#e6ac27] mt-4 group-hover:gap-2 transition-all">
                        Ver reporte
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>