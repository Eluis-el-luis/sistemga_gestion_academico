<!-- resources/views/academico/matriculas/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Matricular Alumno') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Resumen del Alumno -->
            <div class="bg-indigo-50 border-l-4 border-indigo-400 p-4 rounded-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-indigo-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Formulario de Matrícula -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('academico.matriculas.store') }}" method="POST">
                        @csrf

                        <!-- Fila 0: Seleccionar Alumno -->
                        <div class="mb-6">
                            <label for="alumno_id" class="block text-gray-700 font-bold mb-2">Seleccione el Estudiante *</label>
                            <select name="alumno_id" id="alumno_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('alumno_id') border-red-500 @enderror" required>
                                <option value="">Buscar estudiante (Código - Nombre)...</option>
                                @foreach($alumnos as $alumno)
                                    <option value="{{ $alumno->id }}" {{ (old('alumno_id') == $alumno->id || $alumnoSeleccionado == $alumno->id) ? 'selected' : '' }}>
                                        {{ $alumno->codigo_unico_persona }} - {{ $alumno->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('alumno_id') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Fila 1: Año Escolar y Fecha -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="anio_escolar_id" class="block text-gray-700 font-bold mb-2">Año Escolar *</label>
                                <select name="anio_escolar_id" id="anio_escolar_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('anio_escolar_id') border-red-500 @enderror" required>
                                    <option value="">Seleccione el periodo</option>
                                    @foreach($anios as $anio)
                                        <option value="{{ $anio->id }}" {{ old('anio_escolar_id') == $anio->id ? 'selected' : '' }}>
                                            {{ $anio->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('anio_escolar_id') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="fecha_matricula" class="block text-gray-700 font-bold mb-2">Fecha de Matrícula *</label>
                                <input type="date" name="fecha_matricula" id="fecha_matricula"
                                       value="{{ old('fecha_matricula', date('Y-m-d')) }}"
                                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('fecha_matricula') border-red-500 @enderror"
                                       required>
                                @error('fecha_matricula') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Fila 2: Aula y Estado -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="aula_id" class="block text-gray-700 font-bold mb-2">Aula Asignada *</label>
                                <select name="aula_id" id="aula_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('aula_id') border-red-500 @enderror" required>
                                    <option value="">Seleccione el aula</option>
                                    @foreach($aulas as $aula)
                                        <option value="{{ $aula->id }}" {{ old('aula_id') == $aula->id ? 'selected' : '' }}>
                                            {{ $aula->grado->nombre ?? 'Sin Grado' }} - {{ $aula->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('aula_id') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="estado" class="block text-gray-700 font-bold mb-2">Estado Inicial *</label>
                                <select name="estado" id="estado" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('estado') border-red-500 @enderror" required>
                                    <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo (Nuevo Ingreso/Regular)</option>
                                    <option value="repitente" {{ old('estado') == 'repitente' ? 'selected' : '' }}>Repitente</option>
                                </select>
                                @error('estado') <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-8 border-t pt-4">
                            <a href="{{ route('academico.matriculas.index') }}" class="text-gray-500 hover:text-gray-700 font-bold py-2 px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline transition duration-150">
                                Procesar Matrícula
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>