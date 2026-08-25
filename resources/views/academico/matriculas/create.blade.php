<!-- resources/views/academico/matriculas/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.matriculas.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Directorio">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                {{ __('Proceso de Matrícula') }} <span class="text-[#e6ac27]">(Reingreso)</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Resumen del Año Escolar -->
            <div class="bg-amber-50 border border-amber-200/60 p-6 rounded-3xl shadow-sm flex items-start gap-4">
                <div class="bg-amber-100 p-2.5 rounded-xl text-amber-600 mt-0.5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h3 class="font-black text-[#3d2c1d] text-lg">Periodo Vigente: Año Lectivo {{ $anioActivo->nombre ?? 'N/A' }}</h3>
                    <p class="text-slate-600 text-sm mt-1.5 font-medium leading-relaxed">Verifica cuidadosamente el aula antes de procesar la inscripción. Solo las aulas con cupo disponible aparecerán en el listado a continuación.</p>
                </div>
            </div>

            <!-- Formulario de Matrícula -->
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                <div class="bg-[#FFFDF5] border-b border-[#e6ac27]/20 px-8 py-5">
                    <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Datos de Inscripción
                    </h3>
                </div>

                <div class="p-8">
                    <form action="{{ route('academico.matriculas.store') }}" method="POST" class="space-y-8">
                        @csrf
                        
                        <input type="hidden" name="anio_escolar_id" value="{{ $anioActivo->id ?? '' }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Fila 0: Seleccionar Alumno -->
                            <div class="md:col-span-2">
                                <label for="alumno_id" class="block text-sm font-bold text-slate-700 mb-2">Seleccione el Estudiante <span class="text-red-500">*</span></label>
                                <select name="alumno_id" id="alumno_id" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm transition-colors" required>
                                    <option value="">Buscar estudiante (CUP - Nombre)...</option>
                                    @foreach($alumnos ?? [] as $alumno)
                                        <option value="{{ $alumno->id }}" {{ (old('alumno_id') == $alumno->id || ($alumnoSeleccionado->id ?? null) == $alumno->id) ? 'selected' : '' }}>
                                            {{ $alumno->codigo_unico_persona }} - {{ $alumno->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('alumno_id') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                            </div>

                            <!-- Fila 1: Aula -->
                            <div class="md:col-span-2">
                                <label for="aula_id" class="block text-sm font-bold text-slate-700 mb-2">Aula Destino <span class="text-red-500">*</span></label>
                                <select name="aula_id" id="aula_id" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm transition-colors" required>
                                    <option value="">Seleccione el aula y sección...</option>
                                    @foreach($aulas ?? [] as $aula)
                                        <option value="{{ $aula->id }}" {{ old('aula_id') == $aula->id ? 'selected' : '' }}>
                                            {{ $aula->grado->nombre ?? 'Sin Grado' }} - {{ $aula->nombre }} (Cupo Máx: {{ $aula->cupo }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('aula_id') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                            </div>

                            <!-- Fila 2: Estado y Fecha -->
                            <div>
                                <label for="estado" class="block text-sm font-bold text-slate-700 mb-2">Estado Inicial <span class="text-red-500">*</span></label>
                                <select name="estado" id="estado" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm transition-colors" required>
                                    <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>🟢 Activo (Regular)</option>
                                    <option value="repitente" {{ old('estado') == 'repitente' ? 'selected' : '' }}>🟠 Repitente</option>
                                </select>
                                @error('estado') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="fecha_matricula" class="block text-sm font-bold text-slate-700 mb-2">Fecha de Registro <span class="text-red-500">*</span></label>
                                <input type="date" name="fecha_matricula" id="fecha_matricula" value="{{ old('fecha_matricula', date('Y-m-d')) }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm transition-colors" required>
                                @error('fecha_matricula') <p class="text-red-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- BOTONES DE ACCIÓN (UX Tip #1) -->
                        <div class="flex items-center justify-end gap-6 mt-8 pt-6 border-t border-slate-100">
                            <a href="{{ route('academico.matriculas.index') }}" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-8 py-3.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:ring-offset-2">
                                Confirmar Matrícula
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>