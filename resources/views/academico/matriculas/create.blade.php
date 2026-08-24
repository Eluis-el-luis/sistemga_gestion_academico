<!-- resources/views/academico/matriculas/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.matriculas.index') }}" class="text-gray-400 hover:text-emerald-600 transition-colors" title="Volver al Directorio">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                {{ __('Proceso de Matrícula (Reingreso)') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Resumen del Año Escolar -->
            <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl shadow-sm flex items-start gap-4">
                <div class="bg-emerald-100 p-2 rounded-lg text-emerald-600 mt-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="font-bold text-emerald-900 text-lg">Periodo Académico Vigente: Año Lectivo {{ $anioActivo->nombre ?? 'N/A' }}</h3>
                    <p class="text-emerald-700 text-sm mt-1">Verifique cuidadosamente el aula antes de procesar la inscripción. Solo las aulas con cupo disponible aparecerán en el listado.</p>
                </div>
            </div>

            <!-- Formulario de Matrícula -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-8">
                    <form action="{{ route('academico.matriculas.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <input type="hidden" name="anio_escolar_id" value="{{ $anioActivo->id ?? '' }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fila 0: Seleccionar Alumno -->
                            <div class="md:col-span-2">
                                <label for="alumno_id" class="block text-sm font-bold text-gray-700 mb-1">Seleccione el Estudiante <span class="text-red-500">*</span></label>
                                <select name="alumno_id" id="alumno_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors" required>
                                    <option value="">Buscar estudiante (CUP - Nombre)...</option>
                                    @foreach($alumnos ?? [] as $alumno)
                                        <option value="{{ $alumno->id }}" {{ (old('alumno_id') == $alumno->id || ($alumnoSeleccionado->id ?? null) == $alumno->id) ? 'selected' : '' }}>
                                            {{ $alumno->codigo_unico_persona }} - {{ $alumno->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('alumno_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Fila 1: Aula -->
                            <div>
                                <label for="aula_id" class="block text-sm font-bold text-gray-700 mb-1">Aula Destino <span class="text-red-500">*</span></label>
                                <select name="aula_id" id="aula_id" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors" required>
                                    <option value="">Seleccione el aula...</option>
                                    @foreach($aulas ?? [] as $aula)
                                        <option value="{{ $aula->id }}" {{ old('aula_id') == $aula->id ? 'selected' : '' }}>
                                            {{ $aula->grado->nombre ?? 'Sin Grado' }} - {{ $aula->nombre }} (Cupo Máx: {{ $aula->cupo }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('aula_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Fila 2: Estado y Fecha -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="estado" class="block text-sm font-bold text-gray-700 mb-1">Estado Inicial <span class="text-red-500">*</span></label>
                                    <select name="estado" id="estado" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-50" required>
                                        <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                        <option value="repitente" {{ old('estado') == 'repitente' ? 'selected' : '' }}>Repitente</option>
                                    </select>
                                    @error('estado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="fecha_matricula" class="block text-sm font-bold text-gray-700 mb-1">Fecha Registro <span class="text-red-500">*</span></label>
                                    <input type="date" name="fecha_matricula" id="fecha_matricula" value="{{ old('fecha_matricula', date('Y-m-d')) }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                                    @error('fecha_matricula') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                            <a href="{{ route('academico.matriculas.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold text-sm transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-bold text-sm shadow-sm transition-colors focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                Confirmar Matrícula
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>