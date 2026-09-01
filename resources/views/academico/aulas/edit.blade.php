<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.aulas.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver atrás">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                {{ __('Editar Asignación:') }} <span class="text-[#e6ac27]">{{ $aula->grado->nombre ?? '' }} - Sección "{{ $aula->nombre }}"</span>
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                <div class="bg-[#FFFDF5] border-b border-[#e6ac27]/20 px-8 py-5 flex justify-between items-center">
                    <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Modificar Asignaciones
                    </h3>
                </div>

                <div class="p-8">
                    <!-- Mensaje Educativo de UX -->
                    <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-2xl flex gap-3 text-sm font-medium text-blue-800 shadow-sm">
                        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p>Por integridad académica, la estructura del aula (Grado, Modalidad, Turno y Cupo) no puede ser modificada una vez aperturada. Toda aula debe mantener obligatoriamente un Docente Guía titular asignado.</p>
                    </div>

                    <form action="{{ route('academico.aulas.update', $aula->id) }}" method="POST" class="space-y-8">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Año Escolar (Automático / Vigente) -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Año Escolar (Automático)</label>
                                <select name="anio_escolar_id" class="w-full border-slate-200 bg-slate-100 text-slate-600 font-bold rounded-xl shadow-sm cursor-not-allowed opacity-90" required>
                                    @foreach($anios as $anio)
                                        <option value="{{ $anio->id }}" {{ old('anio_escolar_id', $aula->anio_escolar_id) == $anio->id ? 'selected' : '' }}>
                                            {{ $anio->nombre }} {{ $anio->activo ? '(Vigente)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Cupo Máximo (BLOQUEADO) -->
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-1.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Cupo Máximo
                                </label>
                                <input type="number" value="{{ $aula->cupo }}" disabled class="w-full border-slate-200 bg-slate-100 text-slate-400 rounded-xl shadow-sm cursor-not-allowed opacity-80">
                            </div>

                            <!-- Modalidad (BLOQUEADA) -->
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-1.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Modalidad
                                </label>
                                <select disabled class="w-full border-slate-200 bg-slate-100 text-slate-400 rounded-xl shadow-sm cursor-not-allowed opacity-80">
                                    <option>{{ $aula->modalidad->nombre ?? 'N/A' }}</option>
                                </select>
                            </div>

                            <!-- Grado (BLOQUEADO) -->
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-1.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Grado
                                </label>
                                <select disabled class="w-full border-slate-200 bg-slate-100 text-slate-400 rounded-xl shadow-sm cursor-not-allowed opacity-80">
                                    <option>{{ $aula->grado->nombre ?? 'N/A' }}</option>
                                </select>
                            </div>

                            <!-- Sección / Nombre (BLOQUEADO) -->
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-1.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Sección / Nombre
                                </label>
                                <input type="text" value="{{ $aula->nombre }}" disabled class="w-full border-slate-200 bg-slate-100 text-slate-400 uppercase font-bold rounded-xl shadow-sm cursor-not-allowed opacity-80">
                            </div>

                            <!-- Turno (BLOQUEADO) -->
                            <div>
                                <label class="block text-sm font-bold text-slate-400 mb-1.5 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Turno
                                </label>
                                <select disabled class="w-full border-slate-200 bg-slate-100 text-slate-400 font-bold rounded-xl shadow-sm cursor-not-allowed opacity-80">
                                    <option>{{ $aula->turno }}</option>
                                </select>
                            </div>

                            <!-- Docente Guía Titular (OBLIGATORIO Y EDITABLE) -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Docente Guía Titular <span class="text-rose-500">*</span></label>
                                <select name="docente_guia_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" required>
                                    <option value="">Seleccione un profesor titular disponible...</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('docente_guia_id', $aula->docente_guia_id) == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin nombre' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- BOTONES DE ACCIÓN -->
                        <div class="flex items-center justify-end gap-6 mt-8 pt-6 border-t border-slate-100">
                            <a href="{{ route('academico.aulas.index') }}" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-8 py-3.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:ring-offset-2">
                                Actualizar Asignación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>