<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver atrás">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                {{ __('Aperturar Nueva Aula') }}
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                <div class="bg-[#FFFDF5] border-b border-[#e6ac27]/20 px-8 py-5">
                    <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Datos del Grupo a Crear
                    </h3>
                </div>

                <div class="p-8">
                    <form action="{{ route('academico.aulas.store') }}" method="POST" class="space-y-8">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Año Escolar <span class="text-red-500">*</span></label>
                                <select name="anio_escolar_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" required autofocus>
                                    <option value="">Seleccione...</option>
                                    @foreach($anios as $anio)
                                        <option value="{{ $anio->id }}" {{ old('anio_escolar_id') == $anio->id ? 'selected' : '' }}>
                                            {{ $anio->nombre }} {{ $anio->activo ? '(Vigente)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Cupo Máximo <span class="text-red-500">*</span></label>
                                <input type="number" name="cupo" value="{{ old('cupo', 35) }}" min="1" max="50" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" required>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Modalidad <span class="text-red-500">*</span></label>
                                <select name="modalidad_id" id="filtro_modalidad" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id }}" {{ old('modalidad_id') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Grado <span class="text-red-500">*</span></label>
                                <select name="grado_id" id="filtro_grado" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" required>
                                    <option value="">Seleccione primero la modalidad...</option>
                                    @foreach($grados as $grado)
                                        <option value="{{ $grado->id }}" data-modalidad="{{ $grado->modalidad_id }}" {{ old('grado_id') == $grado->id ? 'selected' : '' }}>{{ $grado->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Sección / Nombre <span class="text-red-500">*</span></label>
                                <input type="text" name="nombre" value="{{ old('nombre', 'A') }}" placeholder="Ej: A, B, Única..." class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] uppercase transition-colors font-bold text-[#3d2c1d]" required>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Turno <span class="text-red-500">*</span></label>
                                <select name="turno" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors font-bold text-[#3d2c1d]" required>
                                    <option value="Matutino" {{ old('turno', 'Matutino') == 'Matutino' ? 'selected' : '' }}>Matutino</option>
                                    <option value="Vespertino" {{ old('turno') == 'Vespertino' ? 'selected' : '' }}>Vespertino</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Docente Guía Titular <span class="text-red-500">*</span></label>
                                <select name="docente_guia_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" required>
                                    <option value="">Buscar profesor titular disponible...</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('docente_guia_id') == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin nombre' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-8 p-5 bg-blue-50/50 border border-blue-200/60 rounded-2xl flex gap-4 items-start shadow-sm">
                            <div class="text-blue-500 mt-0.5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-black text-blue-900 text-sm">Clonación Automática de Malla</h4>
                                <p class="text-blue-800 text-sm mt-1.5 font-medium leading-relaxed">Al guardar, el sistema conectará esta aula con todas las asignaturas oficiales que preconfiguraste para este grado.</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-6 mt-8 pt-6 border-t border-slate-100">
                            <a href="{{ route('dashboard') }}" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" class="px-8 py-3.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:ring-offset-2">
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