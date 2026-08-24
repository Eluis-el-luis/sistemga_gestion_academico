@php
    // Buscamos quién es el docente logueado y cuál es su aula
    $docente = \App\Models\Docente::where('usuario_id', Auth::id())->first();
    $aulaGuia = $docente ? \App\Models\Aula::where('docente_guia_id', $docente->id)->first() : null;
    $totalAlumnos = $aulaGuia ? \App\Models\Matricula::where('aula_id', $aulaGuia->id)->where('estado', 'activo')->count() : 0;
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between mt-8">
        <h3 class="text-lg font-bold text-gray-800 border-b-2 border-emerald-400 pb-1 inline-block">
            Panel de Docente Guía
        </h3>
    </div>

    @if($aulaGuia)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="p-4 bg-emerald-100 text-emerald-600 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Mi Aula Asignada</p>
                    <h4 class="text-2xl font-extrabold text-gray-900">{{ $aulaGuia->nombre }}</h4>
                    <p class="text-xs text-gray-500 mt-1">Turno: {{ $aulaGuia->turno }} | Cupo: {{ $aulaGuia->cupo }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-emerald-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
                <div class="p-4 bg-emerald-100 text-emerald-600 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">Mis Alumnos</p>
                    <h4 class="text-3xl font-extrabold text-gray-900">{{ $totalAlumnos }}</h4>
                    <p class="text-xs text-emerald-600 font-semibold mt-1">Estudiantes activos a mi cargo</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button class="bg-white border border-gray-200 rounded-lg p-3 text-sm font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 transition-colors shadow-sm">
                📋 Pasar Asistencia
            </button>
            <button class="bg-white border border-gray-200 rounded-lg p-3 text-sm font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 transition-colors shadow-sm">
                📈 Ver Rendimiento
            </button>
            <button class="bg-white border border-gray-200 rounded-lg p-3 text-sm font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 transition-colors shadow-sm">
                📄 Generar Boletines
            </button>
            <button class="bg-white border border-gray-200 rounded-lg p-3 text-sm font-bold text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 transition-colors shadow-sm">
                🏆 Ranking de Aula
            </button>
        </div>
    @else
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md">
            <p class="text-yellow-700 font-medium">Aún no tienes un aula asignada como Docente Guía para este ciclo escolar.</p>
        </div>
    @endif
</div>