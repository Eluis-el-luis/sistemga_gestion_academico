<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Asistencia: <span class="text-indigo-600">{{ $asignacion->asignatura->nombre }}</span>
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Aula: <span class="font-bold text-gray-700">{{ $asignacion->aula->grado->nombre }} - {{ $asignacion->aula->nombre }}</span>
                </p>
            </div>
            <a href="{{ route('dashboard') }}#mis-aulas" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors">
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-12 relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm font-medium">✓ {{ session('success') }}</div>
            @endif

            <div class="bg-indigo-50 border-l-4 border-indigo-500 p-4 rounded-lg shadow-sm">
                <h3 class="font-bold text-indigo-900 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Asistencia por Excepción ({{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }})
                </h3>
                <p class="text-sm text-indigo-800 mt-1">Todos los alumnos se consideran <strong>Presentes</strong>. Solo utilice los botones para reportar Fugas, Retrasos o Permisos específicos en su hora de clase.</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">Estudiante</th>
                                <th class="px-6 py-3 text-left">Reporte Mañana (Guía)</th>
                                <th class="px-6 py-3 text-left">Incidencias en tu Clase</th>
                                <th class="px-6 py-3 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($matriculas as $matricula)
                                @php
                                    $estadoGuia = isset($asistenciaGuia[$matricula->id]) ? $asistenciaGuia[$matricula->id]->estado_asistencia : 'Presente';
                                    $misIncidencias = isset($incidenciasPrevias[$matricula->id]) ? $incidenciasPrevias[$matricula->id] : collect();
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $matricula->alumno->nombre_completo }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        @if($estadoGuia === 'Presente')
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-100 text-green-700">✓ Presente</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800">⚠ {{ $estadoGuia }}</span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        @if($misIncidencias->isEmpty())
                                            <span class="text-gray-400 text-xs font-medium">Ninguna (Presente)</span>
                                        @else
                                            <div class="space-y-1">
                                                @foreach($misIncidencias as $incidencia)
                                                    <div class="flex items-center gap-2 text-xs">
                                                        <span class="font-bold text-red-600">[{{ $incidencia->bloqueHorario->nombre }}]</span> 
                                                        <span class="text-gray-800">{{ $incidencia->estado_incidencia }}</span>
                                                        <form action="{{ route('academico.asistencia.asignatura.destroy', $incidencia->id) }}" method="POST" class="inline">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="text-gray-400 hover:text-red-500 font-bold ml-1" title="Deshacer reporte">✕</button>
                                                        </form>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        @if($estadoGuia === 'Presente' || str_contains($estadoGuia, 'Actividad'))
                                            <button x-data="" 
                                                    x-on:click.prevent="$dispatch('abrir-modal-incidencia', { id: '{{ $matricula->id }}', nombre: '{{ addslashes($matricula->alumno->nombre_completo) }}' })" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-white border border-red-200 text-red-600 hover:bg-red-50 hover:border-red-300 rounded-lg text-xs font-bold transition-colors shadow-sm">
                                                ⚠ Reportar Ausencia
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400">Ausente el día de hoy</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No hay estudiantes activos matriculados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Alpine.js para Registrar Incidencia -->
    <div x-data="{ matriculaId: '', nombreAlumno: '' }" 
         @abrir-modal-incidencia.window="matriculaId = $event.detail.id; nombreAlumno = $event.detail.nombre; $dispatch('open-modal', 'modal-registrar-incidencia')">
        
        <x-modal name="modal-registrar-incidencia" focusable maxWidth="md">
            <form method="post" action="{{ route('academico.asistencia.asignatura.store', $asignacion->id) }}">
                @csrf
                <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                    <h2 class="text-lg font-bold text-red-900">
                        Reportar Fuga/Retraso: <br><span x-text="nombreAlumno" class="text-red-700"></span>
                    </h2>
                </div>
                
                <div class="p-6 space-y-4">
                    <input type="hidden" name="fecha" value="{{ $fecha }}">
                    <input type="hidden" name="matricula_id" x-bind:value="matriculaId">
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Bloque Horario de su clase <span class="text-red-500">*</span></label>
                        <select name="bloque_horario_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">Seleccione el bloque de clase...</option>
                            @foreach($bloques as $bloque)
                                <option value="{{ $bloque->id }}">{{ $bloque->nombre }} ({{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Tipo de Incidencia <span class="text-red-500">*</span></label>
                        <select name="estado_incidencia" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="Fuga">Fuga (Se ausentó sin permiso)</option>
                            <option value="Llegada Tardía">Llegada Tardía</option>
                            <option value="Permiso de Salida">Permiso de Salida (Enfermería, Dirección)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Observación (Opcional)</label>
                        <input type="text" name="observacion" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Ej. El alumno se quedó en el patio..." maxlength="255">
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t border-gray-200">
                    <button type="button" x-on:click="$dispatch('close')" class="bg-white border border-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg shadow-sm hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm">
                        Guardar Incidencia
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>