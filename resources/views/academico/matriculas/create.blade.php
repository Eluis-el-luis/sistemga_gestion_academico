<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.matriculas.index') }}" class="text-slate-400 hover:text-[#3d2c1d] transition-colors" title="Volver al Listado de Matrículas">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                {{ __('Proceso de Matrícula') }} <span class="text-[#e6ac27]">(Reingreso)</span>
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $anioActivo = $anios->first() ?? null;
            @endphp
            
            <!-- Resumen del Periodo Limpio y Neutro -->
            <div class="bg-white border border-slate-200 py-4 px-6 rounded-2xl shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-amber-50 p-2.5 rounded-xl text-[#e6ac27]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Periodo Lectivo Activo</span>
                        <h3 class="font-black text-[#3d2c1d] text-base">Año Lectivo {{ $anioActivo->nombre ?? 'N/A' }}</h3>
                    </div>
                </div>
                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full text-xs font-black uppercase tracking-wider">
                    Matrículas Abiertas
                </span>
            </div>

            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 p-4 rounded-2xl shadow-sm">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-5 h-5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h3 class="text-xs font-black text-rose-800 uppercase tracking-widest">No se pudo procesar la matrícula</h3>
                    </div>
                    <ul class="text-xs text-rose-700 font-medium list-disc list-inside ml-7 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulario de Matrícula Limpio -->
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">

                <div class="bg-white border-b border-slate-100 px-8 py-5">
                    <h3 class="text-base font-black text-[#3d2c1d] flex items-center gap-2.5 uppercase tracking-wider">
                        <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Datos de Inscripción
                    </h3>
                </div>

                <div class="p-8">
                    <form action="{{ route('academico.matriculas.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <input type="hidden" name="anio_escolar_id" value="{{ $anioActivo->id ?? '' }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="alumno_id" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Seleccione el Estudiante <span class="text-rose-500">*</span></label>
                                <select name="alumno_id" id="alumno_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors" required>
                                    <option value="">Buscar estudiante sin matrícula en este periodo (CUP - Nombre)...</option>
                                    @foreach($alumnos ?? [] as $alumno)
                                        <option value="{{ $alumno->id }}" {{ (old('alumno_id') == $alumno->id || ($alumnoSeleccionado->id ?? null) == $alumno->id) ? 'selected' : '' }}>
                                            {{ $alumno->codigo_unico_persona }} - {{ $alumno->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('alumno_id') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="aula_id" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Aula Destino <span class="text-rose-500">*</span></label>
                                <select name="aula_id" id="aula_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors" required>
                                    <option value="">Seleccione el aula y sección...</option>
                                    @foreach($aulas ?? [] as $aula)
                                        @php
                                            $matriculadosActuales = $aula->matriculas_count ?? 0;
                                            $cuposDisponibles = max(0, $aula->cupo - $matriculadosActuales);
                                            $estaLlena = $cuposDisponibles === 0;
                                        @endphp
                                        <option value="{{ $aula->id }}" {{ old('aula_id') == $aula->id ? 'selected' : '' }} {{ $estaLlena ? 'disabled' : '' }}>
                                            {{ $aula->grado->nombre ?? 'Sin Grado' }} "{{ $aula->nombre }}" 
                                            @if($estaLlena)
                                                (¡AULA LLENA - 0 / {{ $aula->cupo }} Cupos!)
                                            @else
                                                (Disponibles: {{ $cuposDisponibles }} de {{ $aula->cupo }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('aula_id') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="estado" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Estado Inicial <span class="text-rose-500">*</span></label>
                                <select name="estado" id="estado" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors" required>
                                    <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>🟢 Activo (Regular)</option>
                                    <option value="repitente" {{ old('estado') == 'repitente' ? 'selected' : '' }}>🟠 Repitente</option>
                                </select>
                                @error('estado') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="fecha_matricula" class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Fecha de Registro <span class="text-rose-500">*</span></label>
                                <input type="date" name="fecha_matricula" id="fecha_matricula" value="{{ old('fecha_matricula', date('Y-m-d')) }}" class="w-full border-slate-200 bg-slate-50/70 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" required>
                                @error('fecha_matricula') <p class="text-rose-500 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                            <a href="{{ route('academico.matriculas.index') }}" class="text-xs font-black text-slate-400 hover:text-slate-700 uppercase tracking-wider transition-colors">Cancelar</a>
                            <button type="submit" class="inline-flex items-center justify-center bg-[#3d2c1d] hover:bg-[#2b1f14] text-white font-black px-6 py-3 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all cursor-pointer">
                                Confirmar Matrícula
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>