{{-- resources/views/academico/reportes/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Centro de Reportes</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{ tabActiva: 'auditoria' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                <form method="GET" action="{{ route('academico.reportes.index') }}" class="flex items-end gap-4">
                    <div class="w-64">
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Año Escolar</label>
                        <select name="anio_escolar_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-medium cursor-pointer">
                            @foreach ($anios ?? [] as $an)
                                <option value="{{ $an->id }}" @selected($an->id == ($anio->id ?? null))>{{ $an->nombre }} {{ $an->es_activo ? '(Activo)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                    <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Ingreso de Notas</h3>
                    <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white">{{ $resumen['ingreso_notas']['notas_pendientes'] ?? 0 }} <span class="text-xs font-medium text-neutral-400">pendientes</span></p>
                    <p class="text-xs text-neutral-500 mt-1">{{ $resumen['ingreso_notas']['porcentaje'] ?? 0 }}% completado</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                    <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Población Estudiantil</h3>
                    <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white">{{ $resumen['estudiantes']['matriculados'] ?? 0 }} <span class="text-xs font-medium text-neutral-400">matriculados</span></p>
                    <p class="text-xs text-rose-600 mt-1">{{ $resumen['estudiantes']['expedientes_incompletos'] ?? 0 }} incompletos</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                    <h3 class="text-xs font-semibold text-neutral-400 uppercase tracking-wider">Adopción de Contacto</h3>
                    <p class="mt-2 text-2xl font-bold text-neutral-900 dark:text-white">{{ $resumen['padres']['porcentaje_adopcion'] ?? 0 }}%</p>
                    <p class="text-xs text-neutral-500 mt-1">responsables con teléfono</p>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="flex space-x-2 border-b border-stone-200 dark:border-neutral-800 overflow-x-auto bg-[#FFFDF8] dark:bg-neutral-800/60 px-4">
                    <button type="button" @click="tabActiva = 'auditoria'" :class="tabActiva === 'auditoria' ? 'border-[#e6ac27] text-neutral-900 dark:text-white font-bold border-b-2' : 'border-transparent text-neutral-500 font-medium border-b-2'" class="px-5 py-3.5 text-xs uppercase tracking-wider whitespace-nowrap cursor-pointer">Auditoría Interna</button>
                    <button type="button" @click="tabActiva = 'mined'" :class="tabActiva === 'mined' ? 'border-[#e6ac27] text-neutral-900 dark:text-white font-bold border-b-2' : 'border-transparent text-neutral-500 font-medium border-b-2'" class="px-5 py-3.5 text-xs uppercase tracking-wider whitespace-nowrap cursor-pointer">Formatos MINED</button>
                    <button type="button" @click="tabActiva = 'gestion'" :class="tabActiva === 'gestion' ? 'border-[#e6ac27] text-neutral-900 dark:text-white font-bold border-b-2' : 'border-transparent text-neutral-500 font-medium border-b-2'" class="px-5 py-3.5 text-xs uppercase tracking-wider whitespace-nowrap cursor-pointer">Administración Estudiantil</button>
                </div>

                <div class="p-6 sm:p-8">
                    <div x-show="tabActiva === 'auditoria'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                        <a href="{{ route('academico.reportes.control-notas') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Control de Notas</h4><p class="text-xs text-neutral-500 mt-1">Ingreso y pendientes por asignación.</p></a>
                        <a href="{{ route('academico.reportes.notas-pendientes') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Notas Pendientes</h4><p class="text-xs text-neutral-500 mt-1">Estudiantes sin nota registrada.</p></a>
                        <a href="{{ route('academico.reportes.notas-globales') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Notas Globales (Sábana)</h4><p class="text-xs text-neutral-500 mt-1">Consolidado por alumno y asignatura.</p></a>
                        <a href="{{ route('academico.reportes.notas-por-asignatura') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Métrica por Asignatura</h4><p class="text-xs text-neutral-500 mt-1">Promedios y riesgo por docente/materia.</p></a>
                        <a href="{{ route('academico.reportes.asistencia-global') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Asistencia Diaria Global</h4><p class="text-xs text-neutral-500 mt-1">Resumen diario por sección.</p></a>
                        <a href="{{ route('academico.reportes.estadisticas-asistencia') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Estadísticas por Fechas</h4><p class="text-xs text-neutral-500 mt-1">Tendencia por rango de fechas.</p></a>
                        <a href="{{ route('academico.reportes.asistencia-por-seccion') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Por Sección (Día)</h4><p class="text-xs text-neutral-500 mt-1">Detalle diario por aula.</p></a>
                        <a href="{{ route('academico.reportes.asistencia-seccion-rango') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Por Sección (Rango)</h4><p class="text-xs text-neutral-500 mt-1">Acumulado por rango de fechas.</p></a>
                        <a href="{{ route('academico.reportes.asistencia-estudiante') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Ficha Individual</h4><p class="text-xs text-neutral-500 mt-1">Asistencia por estudiante.</p></a>
                    </div>

                    <div x-show="tabActiva === 'mined'" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                        <a href="{{ route('academico.reportes.rendimiento-corte') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Rendimiento REA</h4><p class="text-xs text-neutral-500 mt-1">Matrícula inicial, actual, aprobados y aplazados.</p></a>
                        <a href="{{ route('academico.reportes.mined') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Reporte General MINED</h4><p class="text-xs text-neutral-500 mt-1">Resumen por modalidad y ciclo.</p></a>
                        <a href="{{ route('academico.reportes.avance-programatico') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Avance Programático</h4><p class="text-xs text-neutral-500 mt-1">Cobertura curricular por modalidad.</p></a>
                        <a href="{{ route('academico.reportes.asignaturas-aprobadas') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Asignaturas Aprobadas</h4><p class="text-xs text-neutral-500 mt-1">Aprobados por materia y sexo.</p></a>
                        <a href="{{ route('academico.reportes.escalas') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Formato Escalas MINED</h4><p class="text-xs text-neutral-500 mt-1">Escalas cualitativas por grado.</p></a>
                        <a href="{{ route('academico.reportes.mined-detallado', ['tipo' => 'aprobados']) }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Detallado MINED</h4><p class="text-xs text-neutral-500 mt-1">Listados nominales de aprobados y aplazados.</p></a>
                    </div>

                    <div x-show="tabActiva === 'gestion'" x-cloak class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <a href="{{ route('academico.reportes.estudiantes') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Auditoría de Matrícula</h4><p class="text-xs text-neutral-500 mt-1">Activos, retirados y expedientes incompletos.</p></a>
                        <a href="{{ route('academico.reportes.historial-estudiante') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Certificado de Notas</h4><p class="text-xs text-neutral-500 mt-1">Historial por estudiante y grado.</p></a>
                        <a href="{{ route('academico.reportes.padres') }}" class="p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] transition-colors cursor-pointer"><h4 class="font-bold text-neutral-900 dark:text-white">Directorio de Responsables</h4><p class="text-xs text-neutral-500 mt-1">Contactos de padres y tutores.</p></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>