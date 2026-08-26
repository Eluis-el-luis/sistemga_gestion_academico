<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Pase de Lista (Guía)
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Aula: <span class="font-bold text-indigo-600">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span> | Cupo: {{ $aula->cupo }}
                </p>
            </div>
            
            <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors">
                Volver al Panel
            </a>
        </div>
    </x-slot>

    <div class="py-12 relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alertas de Éxito o Error -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm font-medium">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm font-medium">
                    ⚠ {{ session('error') }}
                </div>
            @endif

            <!-- Selector de Fecha (Para viajar en el tiempo si el maestro faltó ayer) -->
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-800">Fecha de Asistencia</h3>
                    <p class="text-xs text-gray-500">Seleccione un día anterior si desea actualizar un registro pasado.</p>
                </div>
                <form method="GET" action="{{ route('academico.asistencia.aula.create') }}" class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="date" name="fecha" value="{{ $fecha }}" class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-semibold text-gray-700 w-full sm:w-auto" required>
                    <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold py-2 px-4 rounded-lg border border-indigo-200 transition-colors">
                        Cambiar
                    </button>
                </form>
            </div>

            <!-- Formulario Principal de Asistencia -->
            <form action="{{ route('academico.asistencia.aula.store') }}" method="POST">
                @csrf
                <input type="hidden" name="fecha" value="{{ $fecha }}">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-indigo-50 text-indigo-900 uppercase text-xs font-extrabold tracking-wider">
                                <tr>
                                    <th class="px-6 py-4 text-left w-12">#</th>
                                    <th class="px-6 py-4 text-left min-w-[200px]">Estudiante</th>
                                    <th class="px-6 py-4 text-center">Estado de Asistencia</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($matriculas as $index => $matricula)
                                    @php
                                        // Si existe en la BD para esa fecha, tomamos el valor. Si no, por defecto es "Presente"
                                        // El campo coincide con el tipo varchar oficial del diccionario[cite: 1, 5]
                                        $estadoActual = isset($asistenciasPrevias[$matricula->id]) 
                                                        ? $asistenciasPrevias[$matricula->id]->estado_asistencia 
                                                        : 'Presente';
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-500">{{ $loop->iteration }}</td>
                                        
                                        <td class="px-6 py-4">
                                            <input type="hidden" name="asistencias[{{ $index }}][matricula_id]" value="{{ $matricula->id }}">
                                            <div class="font-bold text-gray-900">{{ $matricula->alumno->nombre_completo }}</div>
                                            <div class="text-xs text-gray-400">{{ $matricula->alumno->codigo_unico_persona }}</div>
                                        
                                            @php
                                            
                                                $alertas = $incidenciasHoy->where('matricula_id', $matricula->id);
                                            @endphp
                                            
                                            @if($alertas->isNotEmpty())
                                                <div class="mt-2 flex flex-col items-start gap-1">
                                                    @foreach($alertas as $incidencia)
                                                        <div class="text-[10px] font-bold text-red-700 bg-red-50 inline-block px-2 py-0.5 rounded border border-red-200 shadow-sm">
                                                            ⚠ Reporte: {{ $incidencia->estado_incidencia }} en {{ optional($incidencia->asignatura)->nombre ?? 'su clase' }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                
                                                <!-- Presente (Verde) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Presente" class="peer sr-only" {{ $estadoActual === 'Presente' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border peer-checked:bg-green-500 peer-checked:text-white peer-checked:border-green-600 bg-white text-gray-500 border-gray-200 hover:bg-gray-50">
                                                        Presente
                                                    </div>
                                                </label>

                                                <!-- Ausencia Injustificada (Rojo) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Ausencia Injustificada" class="peer sr-only" {{ $estadoActual === 'Ausencia Injustificada' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border peer-checked:bg-red-500 peer-checked:text-white peer-checked:border-red-600 bg-white text-gray-500 border-gray-200 hover:bg-gray-50">
                                                        Falta
                                                    </div>
                                                </label>

                                                <!-- Ausencia Justificada (Amarillo) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Ausencia Justificada" class="peer sr-only" {{ $estadoActual === 'Ausencia Justificada' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border peer-checked:bg-yellow-400 peer-checked:text-yellow-900 peer-checked:border-yellow-500 bg-white text-gray-500 border-gray-200 hover:bg-gray-50">
                                                        Justificada
                                                    </div>
                                                </label>

                                                <!-- Retiro Anticipado (Naranja) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Retiro Anticipado" class="peer sr-only" {{ $estadoActual === 'Retiro Anticipado' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-600 bg-white text-gray-500 border-gray-200 hover:bg-gray-50">
                                                        Se Retiró
                                                    </div>
                                                </label>

                                                <!-- Actividad Institucional (Azul) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Actividad Institucional" class="peer sr-only" {{ $estadoActual === 'Actividad Institucional' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all border peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-600 bg-white text-gray-500 border-gray-200 hover:bg-gray-50">
                                                        Actividad
                                                    </div>
                                                </label>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-8 text-center text-gray-500 font-medium">
                                            No hay alumnos activos matriculados en esta aula actualmente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Botón Flotante para Guardar Rápido -->
                <div class="mt-6 flex justify-end sticky bottom-6">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-transform transform hover:scale-105 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Guardar Pase de Lista
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>