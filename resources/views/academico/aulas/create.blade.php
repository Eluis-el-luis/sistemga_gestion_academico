<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Aperturar Nueva Aula') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('academico.aulas.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            
                            <!-- Año Escolar -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Año Escolar *</label>
                                <select name="anio_escolar_id" class="border rounded w-full py-2 px-3" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($anios as $anio)
                                        <option value="{{ $anio->id }}" {{ old('anio_escolar_id') == $anio->id ? 'selected' : '' }}>{{ $anio->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Cupo -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Cupo Máximo (Alumnos) *</label>
                                <input type="number" name="cupo" value="{{ old('cupo', 35) }}" min="1" max="50" class="border rounded w-full py-2 px-3" required>
                            </div>

                            <!-- Modalidad (Cascada) -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Modalidad *</label>
                                <select name="modalidad_id" id="filtro_modalidad" class="border rounded w-full py-2 px-3" required>
                                    <option value="">Seleccione...</option>
                                    @foreach($modalidades as $mod)
                                        <option value="{{ $mod->id }}" {{ old('modalidad_id') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Grado (Cascada) -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Grado *</label>
                                <select name="grado_id" id="filtro_grado" class="border rounded w-full py-2 px-3" required>
                                    <option value="">Seleccione primero la modalidad...</option>
                                    @foreach($grados as $grado)
                                        <option value="{{ $grado->id }}" data-modalidad="{{ $grado->modalidad_id }}" {{ old('grado_id') == $grado->id ? 'selected' : '' }}>{{ $grado->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Nombre/Sección -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Sección / Nombre *</label>
                                <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: A, B, Única..." class="border rounded w-full py-2 px-3 uppercase" required>
                            </div>

                            <!-- Turno -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">Turno *</label>
                                <select name="turno" class="border rounded w-full py-2 px-3" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Matutino" {{ old('turno') == 'Matutino' ? 'selected' : '' }}>Matutino</option>
                                    <option value="Vespertino" {{ old('turno') == 'Vespertino' ? 'selected' : '' }}>Vespertino</option>
                                </select>
                            </div>

                            <!-- Docente Guía -->
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 font-bold mb-2">Docente Guía *</label>
                                <select name="docente_guia_id" class="border rounded w-full py-2 px-3" required>
                                    <option value="">Buscar profesor titular...</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('docente_guia_id') == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin nombre' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <!-- Info sobre automatización -->
                        <div class="mb-6 p-4 bg-blue-50 text-blue-800 rounded text-sm font-semibold border-l-4 border-blue-500">
                            ℹ Al crear esta aula, el sistema le asignará automáticamente todas las asignaturas correspondientes a su grado según la Malla Curricular oficial.
                        </div>

                        <div class="flex items-center justify-between border-t pt-4">
                            <a href="{{ route('academico.aulas.index') }}" class="text-gray-500 hover:text-gray-700 font-bold py-2 px-4">Cancelar</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">Guardar y Asignar Materias</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Reutilizamos tu magia del script de cascada -->
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