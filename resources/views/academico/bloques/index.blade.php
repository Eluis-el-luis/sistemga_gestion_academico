<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Plantilla Oficial: Estructura de Horarios') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">{{ session('success') }}</div>
        @endif

        <!-- FORMULARIO PARA AGREGAR UN BLOQUE -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-indigo-600">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-bold mb-4">Añadir Bloque de Tiempo Oficial</h3>
                
                <form action="{{ route('academico.bloques.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                    @csrf
                    
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Modalidad y Turno *</label>
                        <div class="flex gap-2">
                            <select name="modalidad_id" class="w-full border-gray-300 rounded text-sm" required>
                                <option value="">Modalidad...</option>
                                @foreach($modalidades as $mod)
                                    <option value="{{ $mod->id }}">{{ $mod->nombre }}</option>
                                @endforeach
                            </select>
                            <select name="turno" class="w-full border-gray-300 rounded text-sm" required>
                                <option value="Matutino">Matutino</option>
                                <option value="Vespertino">Vespertino</option>
                            </select>
                        </div>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nombre *</label>
                        <input type="text" name="nombre" placeholder="Ej: 1ra Hora" class="w-full border-gray-300 rounded text-sm" required>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Inicio *</label>
                        <input type="time" name="hora_inicio" class="w-full border-gray-300 rounded text-sm" required>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fin *</label>
                        <input type="time" name="hora_fin" class="w-full border-gray-300 rounded text-sm" required>
                    </div>

                    <div class="md:col-span-1 flex flex-col justify-between h-full">
                        <div class="mb-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="es_recreo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm">
                                <span class="ml-2 text-xs font-bold text-gray-700 uppercase">Es Receso</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded text-sm shadow">
                            + Añadir
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO DE BLOQUES POR MODALIDAD -->
        <h3 class="text-xl font-bold text-gray-800 mt-8 mb-4">Estructura Definida</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($modalidades as $modalidad)
                @foreach(['Matutino', 'Vespertino'] as $turno)
                    @php 
                        $bloquesFiltrados = $bloques->where('modalidad_id', $modalidad->id)->where('turno', $turno);
                    @endphp
                    
                    @if($bloquesFiltrados->count() > 0)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                <h4 class="font-bold text-gray-800">{{ $modalidad->nombre }} - {{ $turno }}</h4>
                            </div>
                            <div class="p-4">
                                <ul class="space-y-2">
                                    @foreach($bloquesFiltrados as $bloque)
                                        <li class="flex justify-between items-center text-sm border-b pb-2 last:border-0 last:pb-0 {{ $bloque->es_recreo ? 'bg-orange-50 rounded p-1' : '' }}">
                                            <div>
                                                <span class="font-bold {{ $bloque->es_recreo ? 'text-orange-700' : 'text-gray-800' }}">{{ $bloque->nombre }}</span>
                                                <span class="ml-2 text-gray-600 font-mono">
                                                    {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} a {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                                </span>
                                            </div>
                                            <form action="{{ route('academico.bloques.destroy', $bloque->id) }}" method="POST" onsubmit="return confirm('¿Seguro? Si eliminas esto, borrarás esta hora de todas las aulas.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endforeach
        </div>

    </div>
</x-app-layout>