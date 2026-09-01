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
                    ['ruta' => 'reportes.ingreso-notas', 'icono' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-6 9l2 2 4-4', 'titulo' => 'Control de Ingreso de Notas', 'desc' => 'Monitoreo del registro de calificaciones: profesores/asignaturas con notas pendientes.'],
                    ['ruta' => 'reportes.asistencia', 'icono' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2', 'titulo' => 'Asistencia', 'desc' => 'Control diario por turno y grado/sección, estadísticas de presencia y ausencias.'],
                    ['ruta' => 'reportes.rendimiento', 'icono' => 'M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'titulo' => 'Rendimiento Académico', 'desc' => 'Calificaciones detalladas por sección, asignatura y estudiante.'],
                    ['ruta' => 'reportes.mined', 'icono' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'titulo' => 'Reportes MINED', 'desc' => 'Reportes estadísticos requeridos por el Ministerio de Educación.'],
                    ['ruta' => 'reportes.estudiantes', 'icono' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'titulo' => 'Estudiantes', 'desc' => 'Matrícula, retiros y expedientes incompletos de la población estudiantil.'],
                    ['ruta' => 'reportes.padres', 'icono' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0', 'titulo' => 'Responsables y Padres', 'desc' => 'Contacto y adopción digital de los responsables de los estudiantes.'],
                ];
            @endphp

            @foreach($reportes as $rep)
                <a href="{{ route('academico.'.$rep['ruta'], ['anio_escolar_id' => $anio->id ?? null]) }}" class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 hover:border-[#e6ac27]/40 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/30 mb-4 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $rep['icono'] }}"></path></svg>
                    </div>
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