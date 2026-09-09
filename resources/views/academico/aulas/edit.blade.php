<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.aulas.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver atrás">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                {{ __('Editar Aula:') }} <span class="text-[#e6ac27]">{{ $aula->grado->nombre ?? '' }} - Sección "{{ $aula->nombre }}"</span>
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                <div class="bg-[#FFFDF5] border-b border-[#e6ac27]/20 px-8 py-5">
                    <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Modificar Estructura y Asignación
                    </h3>
                </div>

                <div class="p-8">
                    <div class="mb-8 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex gap-3 text-sm font-medium text-amber-800 shadow-sm">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p>Si cambias el <strong>grado</strong>, las asignaturas se re-sincronizarán automáticamente con la malla curricular de ese grado (las asignaciones de maestros se restablecerán).</p>
                    </div>

                    <form action="{{ route('academico.aulas.update', $aula->id) }}" method="POST" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Modalidad -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Modalidad <span class="text-rose-500">*</span></label>
                                <select name="modalidad_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold" required>
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id }}" {{ old('modalidad_id', $aula->modalidad_id) == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Grado -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Grado <span class="text-rose-500">*</span></label>
                                <select name="grado_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold" required>
                                    @foreach($grados as $grado)
                                        <option value="{{ $grado->id }}" {{ old('grado_id', $aula->grado_id) == $grado->id ? 'selected' : '' }}>{{ $grado->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sección / Nombre -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Sección / Nombre <span class="text-rose-500">*</span></label>
                                <input type="text" name="nombre" value="{{ old('nombre', $aula->nombre) }}" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold uppercase" required>
                            </div>

                            <!-- Turno -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Turno <span class="text-rose-500">*</span></label>
                                <select name="turno" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold" required>
                                    <option value="Matutino" {{ old('turno', $aula->turno) == 'Matutino' ? 'selected' : '' }}>Matutino</option>
                                    <option value="Vespertino" {{ old('turno', $aula->turno) == 'Vespertino' ? 'selected' : '' }}>Vespertino</option>
                                </select>
                            </div>

                            <!-- Cupo -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Cupo Máximo <span class="text-rose-500">*</span></label>
                                <input type="number" name="cupo" value="{{ old('cupo', $aula->cupo) }}" min="1" max="50" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold" required>
                            </div>

                            <!-- Año Escolar -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Año Escolar <span class="text-rose-500">*</span></label>
                                <select name="anio_escolar_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold" required>
                                    @foreach($anios as $anio)
                                        <option value="{{ $anio->id }}" {{ old('anio_escolar_id', $aula->anio_escolar_id) == $anio->id ? 'selected' : '' }}>
                                            {{ $anio->nombre }} {{ $anio->activo ? '(Vigente)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Docente Guía Titular -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Docente Guía Titular <span class="text-rose-500">*</span></label>
                                <select name="docente_guia_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27]" required>
                                    <option value="">Seleccione un profesor titular disponible...</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('docente_guia_id', $aula->docente_guia_id) == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin nombre' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-6 mt-8 pt-6 border-t border-slate-100">
                            <a href="{{ route('academico.aulas.index') }}" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-8 py-3.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:ring-offset-2">
                                Actualizar Aula
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>