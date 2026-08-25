@php
    // Buscamos las materias que da este profesor
    $docente = \App\Models\Docente::where('usuario_id', Auth::id())->first();
    $asignaciones = $docente ? \App\Models\AulaAsignaturaDocente::where('docente_id', $docente->id)->get() : collect();
    $totalAulas = $asignaciones->pluck('aula_id')->unique()->count();
    $totalMaterias = $asignaciones->count();
@endphp

@if($totalMaterias > 0)
    <!-- Tarjetas de Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5 flex items-center gap-4 hover:border-[#e6ac27] transition-colors">
            <div class="w-12 h-12 rounded-xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1H4z"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-stone-500 uppercase tracking-widest">Aulas que atiendo</span>
                <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $totalAulas }} <span class="text-sm font-bold text-stone-400">secciones</span></h4>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5 flex items-center gap-4 hover:border-[#e6ac27] transition-colors">
            <div class="w-12 h-12 rounded-xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-stone-500 uppercase tracking-widest">Carga Horaria</span>
                <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $totalMaterias }} <span class="text-sm font-bold text-stone-400">bloques</span></h4>
            </div>
        </div>
    </div>

    <!-- Tabla Dinámica de Materias Asignadas -->
    <div id="mis-aulas" class="bg-white overflow-hidden shadow-sm rounded-2xl border border-stone-200">
        <div class="p-4 border-b border-stone-200 bg-[#FFFDF5]">
            <h4 class="font-black text-[#3d2c1d] text-sm uppercase tracking-widest">Mis Aulas Asignadas</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-white text-stone-500 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-6 py-3 text-left">Grado y Aula</th>
                        <th class="px-6 py-3 text-left">Asignatura</th>
                        <th class="px-6 py-3 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($asignaciones as $asignacion)
                        <tr class="hover:bg-stone-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-[#3d2c1d]">
                                {{ $asignacion->aula->grado->nombre ?? '' }} - {{ $asignacion->aula->nombre ?? '' }}
                            </td>
                            <td class="px-6 py-4 text-[#e6ac27] font-black">
                                {{ $asignacion->asignatura->nombre ?? '' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                
                                <a href="{{ route('academico.notas.create', $asignacion->id) }}" class="inline-flex items-center gap-1 bg-[#e6ac27] hover:bg-[#d69f22] text-[#3d2c1d] font-black py-1.5 px-4 rounded-lg shadow-sm text-xs transition-transform transform hover:scale-105">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Calificar
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="bg-stone-50 border border-stone-200 p-6 rounded-2xl text-center mt-6">
        <p class="text-stone-500 font-bold">No tienes asignaturas asignadas en este momento.</p>
    </div>
@endif