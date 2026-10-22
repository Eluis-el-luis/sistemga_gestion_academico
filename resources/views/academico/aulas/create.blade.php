<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.aulas.index') }}" class="text-slate-400 hover:text-[#3d2c1d] transition-colors" title="Volver atrás">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                {{ __('Aperturar Nueva Aula') }}
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                
                <div class="bg-white border-b border-slate-100 px-8 py-5">
                    <h3 class="text-base font-black text-[#3d2c1d] flex items-center gap-2.5 uppercase tracking-wider">
                        <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Datos del Grupo a Crear
                    </h3>
                </div>

                <div class="p-8">
                    <form action="{{ route('academico.aulas.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Año Escolar <span class="text-rose-500">*</span></label>
                                <select name="anio_escolar_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors" required autofocus>
                                    <option value="">Seleccione...</option>
                                    @foreach($anios as $anio)
                                        <option value="{{ $anio->id }}" {{ old('anio_escolar_id') == $anio->id ? 'selected' : '' }}>
                                            {{ $anio->nombre }} {{ $anio->activo ? '(Vigente)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Cupo Máximo <span class="text-rose-500">*</span></label>
                                <input type="number" name="cupo" value="{{ old('cupo', 50) }}" min="1" max="100" class="w-full border-slate-200 bg-slate-50/70 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" required>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Modalidad <span class="text-rose-500">*</span></label>
                                <select name="modalidad_id" id="filtro_modalidad" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id }}" {{ old('modalidad_id') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Grado <span class="text-rose-500">*</span></label>
                                <select name="grado_id" id="filtro_grado" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors" required>
                                    <option value="">Seleccione primero la modalidad...</option>
                                    @foreach($grados as $grado)
                                        <option value="{{ $grado->id }}" data-modalidad="{{ $grado->modalidad_id }}" {{ old('grado_id') == $grado->id ? 'selected' : '' }}>{{ $grado->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Sección / Nombre <span class="text-rose-500">*</span></label>
                                <input type="text" name="nombre" value="{{ old('nombre', 'A') }}" placeholder="Ej: A, B, Única..." class="w-full border-slate-200 bg-slate-50/70 rounded-xl shadow-sm text-sm font-black text-[#3d2c1d] uppercase focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" required>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Turno <span class="text-rose-500">*</span></label>
                                <select name="turno" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors" required>
                                    <option value="Matutino" {{ old('turno', 'Matutino') == 'Matutino' ? 'selected' : '' }}>Matutino</option>
                                    <option value="Vespertino" {{ old('turno') == 'Vespertino' ? 'selected' : '' }}>Vespertino</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Docente Guía Titular <span class="text-rose-500">*</span></label>
                                <select name="docente_guia_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors" required>
                                    <option value="">Buscar profesor titular disponible...</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('docente_guia_id') == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin nombre' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Acciones del Formulario -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                            <a href="{{ route('academico.aulas.index') }}" class="text-xs font-black text-slate-400 hover:text-slate-700 uppercase tracking-wider transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center bg-[#3d2c1d] hover:bg-[#2b1f14] text-white font-black px-6 py-3 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all cursor-pointer">
                                Guardar y Asignar Materias
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modSelect = document.getElementById('filtro_modalidad');
            const gradoSelect = document.getElementById('filtro_grado');

            function actualizarGrados() {
                const modIdSeleccionada = modSelect.value;
                let gradoSigueSiendoValido = false;

                Array.from(gradoSelect.options).forEach(opcion => {
                    if (opcion.value === "") return;
                    const modalidadDelGrado = opcion.getAttribute('data-modalidad');
                    
                    if (modIdSeleccionada === "" || modalidadDelGrado === modIdSeleccionada) {
                        opcion.style.display = '';
                        opcion.hidden = false;
                        if (opcion.selected) gradoSigueSiendoValido = true;
                    } else {
                        opcion.style.display = 'none';
                        opcion.hidden = true;
                    }
                });

                if (gradoSelect.value !== "" && !gradoSigueSiendoValido) gradoSelect.value = "";
            }

            gradoSelect.addEventListener('change', function() {
                const opcionSeleccionada = this.options[this.selectedIndex];
                const modalidadDelGrado = opcionSeleccionada.getAttribute('data-modalidad');
                if (modalidadDelGrado && modSelect.value !== modalidadDelGrado) {
                    modSelect.value = modalidadDelGrado;
                    actualizarGrados();
                }
            });

            modSelect.addEventListener('change', actualizarGrados);
            actualizarGrados();
        });
    </script>
</x-app-layout>