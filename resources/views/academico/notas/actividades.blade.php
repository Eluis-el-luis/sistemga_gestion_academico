<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-[#3d2c1d] leading-tight">
                    Configurar Actividades: <span class="text-[#e6ac27]">{{ $asignacion->asignatura->nombre }}</span>
                </h2>
                <p class="text-sm text-stone-500 mt-1 font-bold">{{ $asignacion->aula->grado->nombre }} - {{ $asignacion->aula->nombre }}</p>
            </div>
            <a href="{{ route('academico.notas.index') }}" class="bg-stone-500 hover:bg-stone-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors">
                Volver a mis libretas
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg font-bold shadow-sm">✓ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg font-bold shadow-sm">⚠ {{ session('error') }}</div>
            @endif

            <!-- Filtro de Corte Evaluativo -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200">
                <form method="GET" class="flex flex-col md:flex-row items-center gap-4">
                    <label class="font-black text-[#3d2c1d] uppercase tracking-widest text-sm">Seleccione el Parcial:</label>
                    <select name="corte_id" onchange="this.form.submit()" class="border-stone-300 rounded-lg shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold text-[#3d2c1d]">
                        @foreach($cortes as $corte)
                            <option value="{{ $corte->id }}" {{ $corteSeleccionado == $corte->id ? 'selected' : '' }}>
                                {{ $corte->numero }}° Parcial (Semestre {{ $corte->semestre }})
                            </option>
                        @endforeach
                    </select>
                    
                    <!-- Botón para ir a Calificar (Solo aparece si ya completaron los 100 puntos) -->
                    @if($corteActivo && $sumaAcumulados == $corteActivo->peso_acumulado && $sumaExamen == $cortsoeActivo->pe_examen)
                    <div class="ml-auto">
                        <a href="{{ route('academico.notas.create', ['asignacion' => $asignacion->id, 'corte_evaluativo_id' => $corteSeleccionado]) }}" class="bg-[#e6ac27] hover:bg-[#d69f22] text-[#3d2c1d] font-black py-2 px-6 rounded-lg shadow-md transition-transform transform hover:scale-105 flex items-center gap-2">
                            Ir a Calificar Planilla 
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                    </div>
                    @endif
                </form>
            </div>

            @if($corteActivo)
            <!-- Resumen de Puntos (Barras de Progreso) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tarjeta Acumulados -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200">
                    <div class="flex justify-between items-end mb-2">
                        <h4 class="font-black text-stone-700 uppercase tracking-widest text-xs">Puntos de Acumulados</h4>
                        <span class="font-black text-lg {{ $sumaAcumulados == $corteActivo->peso_acumulado ? 'text-green-600' : 'text-[#e6ac27]' }}">
                            {{ $sumaAcumulados }} / {{ $corteActivo->peso_acumulado }} pts
                        </span>
                    </div>
                    <div class="w-full bg-stone-100 rounded-full h-3">
                        @php $porcentajeAcum = ($corteActivo->peso_acumulado > 0) ? ($sumaAcumulados / $corteActivo->peso_acumulado) * 100 : 0; @endphp
                        <div class="bg-[#e6ac27] h-3 rounded-full transition-all duration-500" style="width: {{ $porcentajeAcum }}%"></div>
                    </div>
                </div>

                <!-- Tarjeta Examen -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-stone-200">
                    <div class="flex justify-between items-end mb-2">
                        <h4 class="font-black text-stone-700 uppercase tracking-widest text-xs">Puntos de Examen</h4>
                        <span class="font-black text-lg {{ $sumaExamen == $corteActivo->peso_examen ? 'text-green-600' : 'text-blue-500' }}">
                            {{ $sumaExamen }} / {{ $corteActivo->peso_examen }} pts
                        </span>
                    </div>
                    <div class="w-full bg-stone-100 rounded-full h-3">
                        @php $porcentajeExam = ($corteActivo->peso_examen > 0) ? ($sumaExamen / $corteActivo->peso_examen) * 100 : 0; @endphp
                        <div class="bg-blue-500 h-3 rounded-full transition-all duration-500" style="width: {{ $porcentajeExam }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Zona de Gestión -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Izquierda: Formulario -->
                <div class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-sm border border-stone-200 self-start">
                    <h3 class="text-lg font-black text-[#3d2c1d] border-b border-stone-100 pb-3 mb-4">Nueva Actividad</h3>
                    <form action="{{ route('academico.notas.actividades.store', $asignacion->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                        
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-1">Nombre de la Tarea/Prueba</label>
                            <input type="text" name="nombre" class="w-full border-stone-300 rounded-lg focus:ring-[#e6ac27] focus:border-[#e6ac27]" required placeholder="Ej. Prueba de Fracciones">
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-1">Tipo de Evaluación</label>
                            <select name="tipo" class="w-full border-stone-300 rounded-lg focus:ring-[#e6ac27] focus:border-[#e6ac27]" required>
                                <option value="acumulado">Acumulado</option>
                                <option value="examen">Examen</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-stone-500 uppercase tracking-wider mb-1">Puntaje Máximo</label>
                            <input type="number" name="puntaje_maximo" class="w-full border-stone-300 rounded-lg focus:ring-[#e6ac27] focus:border-[#e6ac27]" required min="1" max="100" placeholder="Ej. 10">
                        </div>
                        <button type="submit" class="w-full bg-[#3d2c1d] hover:bg-stone-800 text-white font-bold py-3 px-4 rounded-xl shadow transition-transform transform hover:scale-105">
                            Guardar Actividad
                        </button>
                    </form>
                </div>

                <!-- Columna Derecha: Lista de Actividades -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm rounded-2xl border border-stone-200">
                    <div class="p-4 border-b border-stone-100 bg-[#FFFDF5]">
                        <h3 class="text-sm font-black text-[#3d2c1d] uppercase tracking-widest">Actividades Registradas</h3>
                    </div>
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-white text-stone-400 uppercase text-[10px] font-black tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">Tipo</th>
                                <th class="px-6 py-3 text-left">Nombre de Actividad</th>
                                <th class="px-6 py-3 text-center">Puntos</th>
                                <th class="px-6 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($actividades as $actividad)
                                <tr class="hover:bg-stone-50">
                                    <td class="px-6 py-4 font-bold {{ $actividad->tipo == 'examen' ? 'text-blue-600' : 'text-[#e6ac27]' }}">
                                        {{ $actividad->tipo }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $actividad->nombre }}</td>
                                    <td class="px-6 py-4 text-center font-black text-lg text-stone-600">{{ $actividad->puntaje_maximo }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <form action="{{ route('academico.notas.actividades.destroy', [$asignacion->id, $actividad->id]) }}" method="POST" onsubmit="return confirm('¿Eliminar esta actividad?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:bg-red-50 p-1.5 rounded" title="Eliminar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-stone-500 font-bold">Aún no has creado actividades para este parcial.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>