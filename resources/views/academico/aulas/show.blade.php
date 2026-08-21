<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.aulas.index') }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Volver al Directorio">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Estructura del Aula: <span class="text-indigo-700">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
                </h2>
            </div>
            
            <a href="{{ route('academico.aulas.horarios.index', $aula->id) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-indigo-700 shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Armar Horario Semanal
            </a>
        </div>
    </x-slot>

    <div class="py-12 relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- TARJETA DE RESUMEN DEL AULA -->
            <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden flex flex-col md:flex-row">
                <div class="bg-indigo-50 p-6 md:w-1/4 flex flex-col justify-center border-b md:border-b-0 md:border-r border-indigo-100">
                    <span class="text-xs font-bold text-indigo-500 uppercase tracking-wider mb-1">Periodo Lectivo</span>
                    <h3 class="text-2xl font-black text-indigo-900">{{ $aula->anioEscolar->nombre }}</h3>
                    <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-200 text-indigo-800 self-start">
                        {{ $aula->modalidad->nombre }}
                    </span>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-6 flex-grow">
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Sección y Turno</p>
                        <p class="font-bold text-gray-900 text-lg">{{ $aula->nombre }} <span class="text-gray-400 font-normal">|</span> {{ ucfirst($aula->turno) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Capacidad / Cupo</p>
                        <p class="font-bold text-gray-900 text-lg">{{ $aula->cupo }} <span class="text-gray-500 font-medium text-sm">Estudiantes máx.</span></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Docente Titular (Guía)</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                {{ substr($aula->docenteGuia->usuario->nombre_completo ?? 'D', 0, 2) }}
                            </div>
                            <p class="font-bold text-gray-900">{{ $aula->docenteGuia->usuario->nombre_completo ?? 'No asignado' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA DE ASIGNATURAS DEL AULA -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4 bg-gray-50/50">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Carga Horaria y Docentes</h3>
                        <p class="text-sm text-gray-500">Administra los profesores que impartirán clase a este grupo.</p>
                    </div>
                    
                    @can('update', $aula)
                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'modal-agregar-materia')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Materia Extra
                        </button>
                    @endcan
                </div>

                <div class="overflow-x-auto p-6 pt-0 mt-4">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left rounded-tl-lg">Asignatura</th>
                                <th class="px-6 py-3 text-center">Horas/Sem.</th>
                                <th class="px-6 py-3 text-left">Docente que Imparte</th>
                                <th class="px-6 py-3 text-center">Estado</th>
                                @can('update', $aula)
                                    <th class="px-6 py-3 text-center rounded-tr-lg">Acción</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700">
                            @forelse ($asignaciones as $asignacion)
                                <tr class="hover:bg-gray-50 transition-colors {{ !$asignacion->activo ? 'opacity-60 bg-gray-50' : '' }}">
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-900 block">{{ $asignacion->asignatura->nombre }}</span>
                                        @if($asignacion->asignatura->es_extracurricular)
                                            <span class="mt-1 inline-flex px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-700 border border-purple-200">Extracurricular</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono font-semibold text-gray-600">
                                        {{ number_format($asignacion->horas_semanales, 0) }}h
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($asignacion->docente_id)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                                    {{ substr($asignacion->docente->usuario->nombre_completo ?? 'D', 0, 1) }}
                                                </div>
                                                <span class="font-medium text-gray-900">{{ $asignacion->docente->usuario->nombre_completo }}</span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-red-600 text-xs font-bold bg-red-50 px-2 py-1 rounded-md border border-red-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                Requiere Profesor
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full border {{ $asignacion->activo ? 'bg-green-50 text-green-700 border-green-200' : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                            {{ $asignacion->activo ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    
                                    @can('update', $aula)
                                    <td class="px-6 py-4 text-center">
                                        <!-- Botón que dispara el Modal 2 de Alpine -->
                                        <button x-data="" 
                                                x-on:click.prevent="$dispatch('abrir-modal-profesor', { 
                                                    url: '{{ route('academico.aulas.asignaturas.update', [$aula->id, $asignacion->id]) }}', 
                                                    materia: '{{ addslashes($asignacion->asignatura->nombre) }}',
                                                    docenteId: '{{ $asignacion->docente_id ?? '' }}'
                                                })" 
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:text-blue-900 rounded-lg text-xs font-bold transition-colors border border-blue-100 shadow-sm" title="Cambiar Docente">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            Asignar
                                        </button>
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        <p class="font-bold text-gray-900 text-base">Sin materias asignadas</p>
                                        <p class="text-sm mt-1">Este grupo no heredó materias de la Malla Curricular oficial.</p>
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
            <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
                <h2 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    {{ __('Materia Extraordinaria') }}
                </h2>
            </div>
            
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-200">
                    Añade una materia exclusiva para esta aula sin afectar la plantilla del grado.
                </p>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Asignatura <span class="text-red-500">*</span></label>
                    <select name="asignatura_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                        <option value="">Seleccione una asignatura...</option>
                        @foreach($todasAsignaturas ?? [] as $asig)
                            <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Horas Semanales <span class="text-red-500">*</span></label>
                    <input type="number" name="horas_semanales" value="2" min="1" max="40" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                <button type="button" x-on:click="$dispatch('close')" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-2 px-4 rounded-lg text-sm transition-colors shadow-sm">
                    Cancelar
                </button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg text-sm transition-colors shadow-sm">
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
                
                <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                    <h2 class="text-lg font-bold text-blue-900 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Asignar Docente
                    </h2>
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-sm text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-200">
                        Selecciona el profesor que impartirá <strong x-text="nombreMateria" class="text-blue-700"></strong> en esta sección.
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Docente Disponible <span class="text-red-500">*</span></label>
                        <select name="docente_id" x-model="docenteActual" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" required>
                            <option value="">Buscar profesor en la lista...</option>
                            @foreach($todosDocentes ?? [] as $docente)
                                <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin Nombre' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-100">
                    <button type="button" x-on:click="$dispatch('close')" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-2 px-4 rounded-lg text-sm transition-colors shadow-sm">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg text-sm transition-colors shadow-sm">
                        Confirmar Asignación
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>