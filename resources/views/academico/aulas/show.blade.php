<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.aulas.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Directorio de Aulas">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                    Estructura: <span class="text-[#e6ac27]">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
                </h2>
            </div>
            
            <a href="{{ route('academico.aulas.horarios.index', $aula->id) }}" class="inline-flex items-center px-5 py-2.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-xl font-black text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 focus:ring-2 focus:ring-offset-2 focus:ring-[#e6ac27]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Armar Horario Semanal
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- TARJETA DE RESUMEN DEL AULA -->
            <div class="bg-white border border-slate-200 shadow-sm rounded-3xl overflow-hidden flex flex-col md:flex-row">
                <div class="bg-[#FFFDF5] p-8 md:w-1/4 flex flex-col justify-center border-b md:border-b-0 md:border-r border-[#e6ac27]/20">
                    <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Periodo Lectivo</span>
                    <h3 class="text-2xl font-black text-[#3d2c1d]">{{ $aula->anioEscolar->nombre }}</h3>
                    <span class="mt-3 inline-flex items-center px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-slate-100 text-slate-600 border border-slate-200 self-start">
                        {{ $aula->modalidad->nombre }}
                    </span>
                </div>
                <div class="p-8 grid grid-cols-1 sm:grid-cols-3 gap-8 flex-grow">
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
                                {{ substr($aula->docenteGuia->usuario->nombre_completo ?? 'D', 0, 2) }}
                            </div>
                            <p class="font-bold text-slate-800">{{ $aula->docenteGuia->usuario->nombre_completo ?? 'No asignado' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA DE ASIGNATURAS DEL AULA -->
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                <div class="p-8 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white">
                    <div>
                        <h3 class="text-xl font-black text-[#3d2c1d]">Carga Horaria y Docentes</h3>
                        <p class="text-sm font-medium text-slate-500 mt-1">Administra los profesores que impartirán clase a este grupo.</p>
                    </div>
                    
                    @can('update', $aula)
                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'modal-agregar-materia')" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-xl shadow-sm text-sm font-bold text-slate-600 hover:bg-slate-50 hover:text-[#e6ac27] transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Materia Extraordinaria
                        </button>
                    @endcan
                </div>

                <div class="overflow-x-auto p-0">
                    <table class="min-w-full divide-y divide-slate-100 text-sm border-collapse text-left">
                        <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-[11px] font-black tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="px-8 py-5">Asignatura</th>
                                <th class="px-6 py-5 text-center">Horas/Sem.</th>
                                <th class="px-6 py-5">Docente que Imparte</th>
                                <th class="px-6 py-5 text-center">Estado</th>
                                @can('update', $aula)
                                    <th class="px-8 py-5 text-right">Acción</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                            @forelse ($asignaciones as $asignacion)
                                <tr class="hover:bg-slate-50/80 transition-colors {{ !$asignacion->activo ? 'opacity-60' : '' }}">
                                    <td class="px-8 py-5">
                                        <span class="font-black text-[#3d2c1d] text-base block">{{ $asignacion->asignatura->nombre }}</span>
                                        @if($asignacion->asignatura->es_extracurricular)
                                            <span class="mt-2 inline-flex px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-widest bg-purple-50 text-purple-700 border border-purple-200">Extracurricular</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-center font-black text-slate-500 text-base">
                                        {{ number_format($asignacion->horas_semanales, 0) }}h
                                    </td>
                                    <td class="px-6 py-5">
                                        @if($asignacion->docente_id)
                                            <div class="flex items-center gap-3">
                                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 border border-slate-200 flex items-center justify-center font-black text-[10px]">
                                                    {{ substr($asignacion->docente->usuario->nombre_completo ?? 'D', 0, 1) }}
                                                </div>
                                                <span class="font-bold text-slate-800">{{ $asignacion->docente->usuario->nombre_completo }}</span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 text-rose-600 text-xs font-black uppercase tracking-widest bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                Requiere Profesor
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg border {{ $asignacion->activo ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : 'bg-slate-100 text-slate-500 border-slate-200' }}">
                                            {{ $asignacion->activo ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    
                                    @can('update', $aula)
                                    <td class="px-8 py-5 text-right">
                                        <button x-data="" 
                                                x-on:click.prevent="$dispatch('abrir-modal-profesor', { 
                                                    url: '{{ route('academico.aulas.asignaturas.update', [$aula->id, $asignacion->id]) }}', 
                                                    materia: '{{ addslashes($asignacion->asignatura->nombre) }}',
                                                    docenteId: '{{ $asignacion->docente_id ?? '' }}'
                                                })" 
                                                class="inline-flex items-center px-4 py-2 bg-white text-[#e6ac27] hover:bg-[#FFFDF5] border border-[#e6ac27]/30 rounded-xl text-xs font-black transition-all shadow-sm transform hover:-translate-y-0.5">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            Asignar
                                        </button>
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        <p class="font-black text-[#3d2c1d] text-lg">Sin materias asignadas</p>
                                        <p class="text-sm font-medium mt-1">Este grupo no heredó materias de la Malla Curricular oficial.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODALES (Alpine.js) ================= -->
    
    <!-- Modal 1: Agregar Materia Extra -->
    <x-modal name="modal-agregar-materia" focusable maxWidth="md">
        <form method="post" action="{{ route('academico.aulas.asignaturas.store', $aula->id) }}">
            @csrf
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h2 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    {{ __('Materia Extraordinaria') }}
                </h2>
            </div>
            
            <div class="p-8 space-y-6">
                <p class="text-sm text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-200 font-medium">
                    Añade una materia exclusiva para esta aula sin afectar la plantilla general del grado.
                </p>

                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Asignatura <span class="text-red-500">*</span></label>
                    <select name="asignatura_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm font-medium transition-colors" required>
                        <option value="">Seleccione una asignatura...</option>
                        @foreach($todasAsignaturas ?? [] as $asig)
                            <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Horas Semanales <span class="text-red-500">*</span></label>
                    <input type="number" name="horas_semanales" value="2" min="1" max="40" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm font-black text-[#3d2c1d] transition-colors" required>
                </div>
            </div>

            <div class="bg-slate-50 px-8 py-5 flex justify-end gap-4 border-t border-slate-100">
                <button type="button" x-on:click="$dispatch('close')" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5">
                    Guardar
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal 2: Asignar Profesor -->
    <div x-data="{ urlAction: '', nombreMateria: '', docenteActual: '' }" 
         @abrir-modal-profesor.window="urlAction = $event.detail.url; nombreMateria = $event.detail.materia; docenteActual = $event.detail.docenteId; $dispatch('open-modal', 'modal-asignar-profesor')">
        
        <x-modal name="modal-asignar-profesor" focusable maxWidth="md">
            <form method="post" x-bind:action="urlAction">
                @csrf
                @method('PUT')
                
                <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                    <h2 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Asignar Docente
                    </h2>
                </div>

                <div class="p-8 space-y-6">
                    <p class="text-sm font-medium text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-200 leading-relaxed">
                        Selecciona el profesor que impartirá <strong x-text="nombreMateria" class="font-black text-[#e6ac27]"></strong> en esta sección.
                    </p>

                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Docente Disponible <span class="text-red-500">*</span></label>
                        <select name="docente_id" x-model="docenteActual" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm font-medium transition-colors" required>
                            <option value="">Buscar profesor en la lista...</option>
                            @foreach($todosDocentes ?? [] as $docente)
                                <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin Nombre' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-slate-50 px-8 py-5 flex justify-end gap-4 border-t border-slate-100">
                    <button type="button" x-on:click="$dispatch('close')" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5">
                        Confirmar
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>