<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9"></path></svg>
                {{ __('Plantilla Oficial: Malla Curricular') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes de Éxito o Error -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl shadow-sm font-bold">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl shadow-sm font-bold">
                    ⚠ {{ session('error') }}
                </div>
            @endif

            <!-- SECCIÓN 1: FORMULARIO PARA AGREGAR A LA PLANTILLA -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                <div class="p-6 text-gray-900 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Agregar Asignatura a un Grado</h3>
                    
                    <form action="{{ route('academico.malla.store') }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                        @csrf
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Grado *</label>
                            <!-- NUEVO: Agregado id="gradoSelect" y data-modalidad -->
                            <select name="grado_id" id="gradoSelect" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Seleccione el Grado...</option>
                                @foreach($grados as $grado)
                                    <option value="{{ $grado->id }}" data-modalidad="{{ strtolower($grado->modalidad->nombre ?? '') }}">
                                        {{ $grado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Asignatura Oficial *</label>
                            <!-- NUEVO: Agregado id="asignaturaSelect" y data-is-preescolar -->
                            <select name="asignatura_id" id="asignaturaSelect" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Seleccione la Materia...</option>
                                @foreach($asignaturas as $asignatura)
                                    <option value="{{ $asignatura->id }}" data-is-preescolar="{{ $asignatura->nombre === 'Tema motivador' ? 'true' : 'false' }}">
                                        {{ $asignatura->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ELIMINADO: El bloque del input de Horas Semanales -->

                        <div class="w-full md:w-auto">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2">
                                + Añadir a Plantilla
                            </button>
                        </div>
                    </form>
                    <p class="text-xs text-indigo-600 font-semibold mt-4">ℹ Las materias que agregues aquí se clonarán automáticamente cada vez que se aperture una nueva Aula para ese grado.</p>
                </div>
            </div>

            <!-- SECCIÓN 2: VISUALIZADOR DE LA MALLA POR GRADO -->
            <div>
                <h3 class="text-xl font-bold text-gray-800 mt-8 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H14a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Malla Curricular Configurada
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($grados as $grado)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                            <div class="bg-gray-50 px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg">{{ $grado->nombre }}</h4>
                                    <p class="text-xs text-gray-500 font-medium">{{ $grado->mallaCurricular->count() }} materias configuradas</p>
                                </div>
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs font-bold">{{ $grado->modalidad->nombre ?? 'N/A' }}</span>
                            </div>
                            
                            <div class="p-5 flex-grow">
                                @if($grado->mallaCurricular->count() > 0)
                                    <ul class="space-y-3">
                                        @foreach($grado->mallaCurricular as $item)
                                            <li class="flex justify-between items-center text-sm border-b border-gray-100 pb-3 last:border-0 last:pb-0 group">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-gray-700 group-hover:text-indigo-600 transition-colors">{{ $item->asignatura->nombre }}</span>
                                                    <!-- ELIMINADO: La línea de {{ $item->horas_semanales_sugeridas }} -->
                                                </div>
                                                
                                                <form action="{{ route('academico.malla.destroy', $item->id) }}" method="POST" class="alerta-eliminar" onsubmit="return confirm('¿Seguro que deseas quitar esta materia de la plantilla oficial?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-500 bg-white hover:bg-red-50 p-1.5 rounded-lg transition-colors border border-transparent hover:border-red-100" title="Quitar de la malla">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="text-center py-6 flex flex-col items-center justify-center opacity-60">
                                        <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Plantilla Vacía</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>


    <!-- SCRIPT DE FILTRADO DINÁMICO -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gradoSelect = document.getElementById('gradoSelect');
            const asignaturaSelect = document.getElementById('asignaturaSelect');
            
            // Guardamos las opciones originales en memoria al cargar la página
            const opcionesOriginales = Array.from(asignaturaSelect.options);

            gradoSelect.addEventListener('change', function() {
                const opcionSeleccionada = this.options[this.selectedIndex];
                
                // Si vuelve a la opción "Seleccione...", reiniciamos la lista
                if (!opcionSeleccionada.value) {
                    restaurarOpciones(opcionesOriginales);
                    return;
                }

                // Identificamos si el grado elegido es de Preescolar
                const esPreescolar = opcionSeleccionada.getAttribute('data-modalidad').includes('preescolar');

                // Limpiamos la lista actual
                asignaturaSelect.innerHTML = '';
                asignaturaSelect.appendChild(opcionesOriginales[0].cloneNode(true)); // Opción "Seleccione..."

                // Filtramos según la regla de negocio
                for (let i = 1; i < opcionesOriginales.length; i++) {
                    const esMateriaPreescolar = opcionesOriginales[i].getAttribute('data-is-preescolar') === 'true';

                    if (esPreescolar && esMateriaPreescolar) {
                        // Preescolar SOLO ve el Tema motivador
                        asignaturaSelect.appendChild(opcionesOriginales[i].cloneNode(true));
                    } else if (!esPreescolar && !esMateriaPreescolar) {
                        // Primaria/Secundaria ve TODO excepto el Tema motivador
                        asignaturaSelect.appendChild(opcionesOriginales[i].cloneNode(true));
                    }
                }
            });

            function restaurarOpciones(opciones) {
                asignaturaSelect.innerHTML = '';
                opciones.forEach(op => asignaturaSelect.appendChild(op.cloneNode(true)));
            }
        });
    </script>
</x-app-layout>