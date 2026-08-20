@php
    // Buscamos las materias que da este profesor en distintas aulas
    $docente = \App\Models\Docente::where('usuario_id', Auth::id())->first();
    $asignaciones = $docente ? \App\Models\AulaAsignaturaDocente::where('docente_id', $docente->id)->get() : collect();
    $totalAulas = $asignaciones->pluck('aula_id')->unique()->count();
    $totalMaterias = $asignaciones->count();
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between mt-8">
        <h3 class="text-lg font-bold text-gray-800 border-b-2 border-blue-400 pb-1 inline-block">
            Panel de Docente por Asignatura
        </h3>
    </div>

    @if($totalMaterias > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="p-4 bg-blue-100 text-blue-600 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Aulas que atiendo</p>
                    <h4 class="text-3xl font-extrabold text-gray-900">{{ $totalAulas }}</h4>
                    <p class="text-xs text-blue-600 font-semibold mt-1">Secciones distintas</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-blue-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="p-4 bg-blue-100 text-blue-600 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Carga Horaria</p>
                    <h4 class="text-3xl font-extrabold text-gray-900">{{ $totalMaterias }}</h4>
                    <p class="text-xs text-blue-600 font-semibold mt-1">Bloques de clases asignados</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button class="bg-white border border-gray-200 rounded-lg p-3 text-sm font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 transition-colors shadow-sm">
                📝 Subir Calificaciones
            </button>
            <button class="bg-white border border-gray-200 rounded-lg p-3 text-sm font-bold text-gray-700 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-300 transition-colors shadow-sm">
                📅 Mi Horario
            </button>
        </div>
    @else
        <div class="bg-gray-50 border-l-4 border-gray-400 p-4 rounded-md">
            <p class="text-gray-700 font-medium">No tienes asignaturas asignadas en este momento.</p>
        </div>
    @endif
</div>