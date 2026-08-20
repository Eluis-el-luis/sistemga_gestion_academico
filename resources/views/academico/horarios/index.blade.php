<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Horario de Clases: ') }} <span class="text-indigo-600">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
            </h2>
            <a href="{{ route('academico.aulas.show', $aula->id) }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Volver al Aula
            </a>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ session('error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- PANEL IZQUIERDO: FORMULARIO PARA AGREGAR -->
            @can('horarios.gestionar')
            <div class="lg:col-span-1 bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-indigo-600 self-start">
                <h3 class="font-bold text-lg mb-4 text-gray-800">Añadir Clase</h3>
                
                <form action="{{ route('academico.aulas.horarios.store', $aula->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Día *</label>
                        <select name="dia_semana" class="w-full border-gray-300 rounded text-sm" required>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Materia *</label>
                        <select name="aula_asignatura_docente_id" class="w-full border-gray-300 rounded text-sm" required>
                            <option value="">Seleccione materia...</option>
                            @foreach($asignaciones as $asignacion)
                                <option value="{{ $asignacion->id }}">{{ $asignacion->asignatura->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Bloque Oficial *</label>
                        <select name="bloque_horario_id" class="w-full border-gray-300 rounded text-sm" required>
                            <option value="">Seleccione la hora...</option>
                            @foreach($bloquesOficiales as $bloque)
                                <!-- Inhabilitamos los recreos para que no asignen clases ahí -->
                                <option value="{{ $bloque->id }}" {{ $bloque->es_recreo ? 'disabled' : '' }}>
                                    {{ $bloque->nombre }} ({{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }})
                                    {{ $bloque->es_recreo ? '[RECREO]' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded text-sm">
                        + Guardar Horario
                    </button>
                </form>
            </div>
            @endcan

            <!-- PANEL DERECHO: EL CALENDARIO SEMANAL -->
            <div class="{{ auth()->user()->can('horarios.gestionar') ? 'lg:col-span-3' : 'lg:col-span-4' }} bg-white shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-5 gap-4">
                    
                    @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia)
                        <!-- Columna de un Día -->
                        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                            <div class="bg-indigo-100 py-2 text-center border-b border-indigo-200">
                                <h4 class="font-bold text-indigo-800 uppercase text-sm">{{ $dia }}</h4>
                            </div>
                            
                            <div class="p-2 space-y-2 min-h-[300px]">
                                @forelse($calendario[$dia] as $bloqueClase)
                                    <!-- Tarjeta de Bloque de Clase -->
                                    <div class="bg-white border border-gray-300 rounded p-2 text-center shadow-sm relative group">
                                        <p class="text-[10px] font-bold text-indigo-500 uppercase mb-0.5">
                                            {{ $bloqueClase->bloque->nombre }}
                                        </p>
                                        <p class="text-xs font-bold text-gray-500 mb-1">
                                            {{ \Carbon\Carbon::parse($bloqueClase->bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloqueClase->bloque->hora_fin)->format('h:i A') }}
                                        </p>
                                        <p class="font-bold text-sm text-gray-800 leading-tight">
                                            {{ $bloqueClase->aulaAsignaturaDocente->asignatura->nombre }}
                                        </p>
                                        
                                        @can('horarios.gestionar')
                                        <!-- Botón eliminar que aparece al pasar el mouse -->
                                        <form action="{{ route('academico.aulas.horarios.destroy', [$aula->id, $bloqueClase->id]) }}" method="POST" class="absolute top-1 right-1 hidden group-hover:block" onsubmit="return confirm('¿Quitar esta clase del horario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 bg-white rounded-full p-0.5 shadow border">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-xs text-gray-400 font-medium italic">
                                        Libre
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

        </div>
    </div>
</x-app-layout>