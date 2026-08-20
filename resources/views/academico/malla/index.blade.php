<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Plantilla Oficial: Malla Curricular') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes de Éxito o Error -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- SECCIÓN 1: FORMULARIO PARA AGREGAR A LA PLANTILLA -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-indigo-600">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Agregar Asignatura a un Grado</h3>
                    
                    <form action="{{ route('academico.malla.store') }}" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                        @csrf
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Grado *</label>
                            <select name="grado_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Seleccione el Grado...</option>
                                @foreach($grados as $grado)
                                    <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Asignatura *</label>
                            <select name="asignatura_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Seleccione la Materia...</option>
                                @foreach($asignaturas as $asignatura)
                                    <option value="{{ $asignatura->id }}">{{ $asignatura->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-48">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Horas Semanales *</label>
                            <input type="number" name="horas_semanales_sugeridas" min="1" max="40" value="4" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>

                        <div class="w-full md:w-auto">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-md shadow">
                                + Añadir a Plantilla
                            </button>
                        </div>
                    </form>
                    <p class="text-xs text-gray-500 mt-3">ℹ️ Las materias que agregues aquí se clonarán automáticamente cada vez que se aperture una nueva Aula para ese grado.</p>
                </div>
            </div>

            <!-- SECCIÓN 2: VISUALIZADOR DE LA MALLA POR GRADO -->
            <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4">Malla Curricular Configurada</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($grados as $grado)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                            <h4 class="font-bold text-gray-800">{{ $grado->nombre }}</h4>
                            <p class="text-xs text-gray-500">{{ $grado->mallaCurricular->count() }} materias configuradas</p>
                        </div>
                        
                        <div class="p-4">
                            @if($grado->mallaCurricular->count() > 0)
                                <ul class="space-y-3">
                                    @foreach($grado->mallaCurricular as $item)
                                        <li class="flex justify-between items-center text-sm border-b pb-2 last:border-0 last:pb-0">
                                            <div>
                                                <span class="font-semibold text-gray-700">{{ $item->asignatura->nombre }}</span>
                                                <span class="block text-xs text-gray-500">{{ $item->horas_semanales_sugeridas }} horas semanales</span>
                                            </div>
                                            
                                            <!-- Botón Eliminar de la Plantilla -->
                                            <form action="{{ route('academico.malla.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas quitar esta materia de la plantilla oficial de este grado?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Quitar de la malla">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-sm text-gray-400 font-medium">Plantilla vacía.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>