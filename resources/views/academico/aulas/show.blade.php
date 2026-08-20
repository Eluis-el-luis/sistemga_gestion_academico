<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Estructura del Aula: ') }} <span class="text-indigo-600">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
            </h2>
            <a href="{{ route('academico.aulas.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Volver a la lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- TARJETA DE RESUMEN DEL AULA -->
            <div class="bg-indigo-50 border-l-4 border-indigo-500 p-6 rounded-lg shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-indigo-700 uppercase font-bold">Año Escolar</p>
                        <p class="font-semibold text-gray-900">{{ $aula->anioEscolar->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-indigo-700 uppercase font-bold">Modalidad</p>
                        <p class="font-semibold text-gray-900">{{ $aula->modalidad->nombre }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-indigo-700 uppercase font-bold">Turno y Cupo</p>
                        <p class="font-semibold text-gray-900">{{ ucfirst($aula->turno) }} (Max: {{ $aula->cupo }} alum.)</p>
                    </div>
                    <div>
                        <p class="text-xs text-indigo-700 uppercase font-bold">Docente Guía Titular</p>
                        <p class="font-semibold text-gray-900">{{ $aula->docenteGuia->usuario->nombre_completo ?? 'No asignado' }}</p>
                    </div>
                </div>
            </div>

            <!-- TABLA DE ASIGNATURAS DEL AULA -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Carga Horaria y Materias Asignadas</h3>
                    <a href="{{ route('academico.aulas.horarios.index', $aula->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2 px-4 rounded shadow">
                         Ver Horario Semanal
                    </a>
                    @can('update', $aula)
                    <!-- Botón que abre el modal de Materia Extra -->
                    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'modal-agregar-materia')" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded shadow">
                        + Agregar Materia Extra
                    </button>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full whitespace-no-wrap text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Asignatura</th>
                                <th class="px-6 py-3 text-center">Horas/Semana</th>
                                <th class="px-6 py-3">Docente Imparte</th>
                                <th class="px-6 py-3 text-center">Estado</th>
                                @can('update', $aula)
                                <th class="px-6 py-3 text-center">Acciones</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($asignaciones as $asignacion)
                                <tr class="hover:bg-gray-50 {{ !$asignacion->activo ? 'opacity-50' : '' }}">
                                    <td class="px-6 py-4 font-bold text-gray-900">
                                        {{ $asignacion->asignatura->nombre }}
                                        @if($asignacion->asignatura->es_extracurricular)
                                            <span class="ml-2 px-2 py-0.5 rounded text-[10px] bg-purple-100 text-purple-800">Extra</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center font-semibold">
                                        {{ number_format($asignacion->horas_semanales, 0) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($asignacion->docente_id)
                                            {{ $asignacion->docente->usuario->nombre_completo }}
                                        @else
                                            <span class="text-red-500 text-xs font-bold flex items-center">
                                                ⚠ Falta asignar profesor
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($asignacion->activo)
                                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">Activa</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">Inactiva</span>
                                        @endif
                                    </td>
                                    @can('update', $aula)
                                    <td class="px-6 py-4 text-center space-x-2">
                                        <!-- Botón que abre el modal de Profesor y le pasa los datos -->
                                        <button x-data="" 
                                                x-on:click.prevent="$dispatch('abrir-modal-profesor', { 
                                                    url: '{{ route('academico.aulas.asignaturas.update', [$aula->id, $asignacion->id]) }}', 
                                                    materia: '{{ $asignacion->asignatura->nombre }}' 
                                                })" 
                                                class="text-blue-600 hover:text-blue-900 font-semibold text-xs">
                                            Asignar Profesor
                                        </button>
                                    </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <p class="font-bold">No hay asignaturas registradas para esta aula.</p>
                                        <p class="text-xs mt-1">Verifique la Malla Curricular oficial del grado correspondiente.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ================= MODALES (Ventanas Emergentes) ================= -->
    
    <!-- Modal 1: Agregar Materia Extra -->
    <x-modal name="modal-agregar-materia" focusable>
        <form method="post" action="{{ route('academico.aulas.asignaturas.store', $aula->id) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Agregar Materia Extraordinaria') }}
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Esta materia se agregará únicamente a esta aula, sin afectar la plantilla oficial del grado.
            </p>

            <div class="mt-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Asignatura</label>
                <select name="asignatura_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Seleccione una asignatura...</option>
                    @foreach($todasAsignaturas as $asig)
                        <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Horas Semanales</label>
                <input type="number" name="horas_semanales" value="2" min="1" max="40" class="w-full border-gray-300 rounded-md shadow-sm" required>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded mr-3">
                    Cancelar
                </button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Guardar Materia Extra
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal 2: Asignar Profesor -->
    <div x-data="{ urlAction: '', nombreMateria: '' }" 
         @abrir-modal-profesor.window="urlAction = $event.detail.url; nombreMateria = $event.detail.materia; $dispatch('open-modal', 'modal-asignar-profesor')">
        
        <x-modal name="modal-asignar-profesor" focusable>
            <form method="post" x-bind:action="urlAction" class="p-6">
                @csrf
                @method('PUT')
                
                <h2 class="text-lg font-medium text-gray-900">
                    Asignar Docente a: <span x-text="nombreMateria" class="text-indigo-600 font-bold"></span>
                </h2>

                <div class="mt-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Seleccionar Docente</label>
                    <select name="docente_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Buscar profesor en la lista...</option>
                        @foreach($todosDocentes as $docente)
                            <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin Nombre' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="button" x-on:click="$dispatch('close')" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded mr-3">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Confirmar Asignación
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>