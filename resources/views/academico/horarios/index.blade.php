<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.aulas.show', $aula->id) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Volver al Aula">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Horario de Clases: <span class="text-indigo-700">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
                </h2>
            </div>
            
            <!-- Badge Informativo del Turno -->
            <span class="inline-flex items-center px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-lg font-bold text-sm text-indigo-800 shadow-sm">
                Turno: {{ ucfirst($aula->turno) }}
            </span>
        </div>
    </x-slot>

    <div class="py-12 max-w-full mx-auto sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
            
            <!-- PANEL IZQUIERDO: FORMULARIO PARA AGREGAR -->
            @can('horarios.gestionar')
            <div class="xl:col-span-1 bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-100 self-start sticky top-20">
                <div class="bg-gray-800 px-5 py-4 border-b border-gray-900">
                    <h3 class="font-bold text-lg text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Añadir Clase
                    </h3>
                </div>
                
                <div class="p-5">
                    <form action="{{ route('academico.aulas.horarios.store', $aula->id) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Día de la Semana <span class="text-red-500">*</span></label>
                            <select name="dia_semana" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miércoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Materia / Docente <span class="text-red-500">*</span></label>
                            <select name="aula_asignatura_docente_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                                <option value="">Seleccione materia...</option>
                                @foreach($asignaciones ?? [] as $asignacion)
                                    <option value="{{ $asignacion->id }}" {{ !$asignacion->docente_id ? 'disabled' : '' }}>
                                        {{ $asignacion->asignatura->nombre }} {{ !$asignacion->docente_id ? '(Sin Docente)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Bloque de Tiempo <span class="text-red-500">*</span></label>
                            <select name="bloque_horario_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                                <option value="">Seleccione la hora...</option>
                                @foreach($bloquesOficiales ?? [] as $bloque)
                                    <!-- Inhabilitamos los recreos -->
                                    <option value="{{ $bloque->id }}" {{ $bloque->es_recreo ? 'disabled' : '' }}>
                                        {{ $bloque->nombre }} ({{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }})
                                        {{ $bloque->es_recreo ? '☕ [RECESO]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-lg text-sm shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Guardar en Horario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endcan

            <!-- PANEL DERECHO: EL CALENDARIO SEMANAL -->
            <div class="{{ auth()->user()->can('horarios.gestionar') ? 'xl:col-span-4' : 'xl:col-span-5' }} bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100 overflow-x-auto">
                <div class="min-w-[800px]">
                    <div class="grid grid-cols-5 gap-4 h-full">
                        
                        @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia)
                            <!-- Columna de un Día -->
                            <div class="bg-gray-50 rounded-xl border border-gray-200 flex flex-col">
                                <div class="bg-indigo-100/50 py-3 text-center border-b border-indigo-100 rounded-t-xl">
                                    <h4 class="font-bold text-indigo-900 uppercase text-sm tracking-wider">{{ $dia }}</h4>
                                </div>
                                
                                <div class="p-3 flex-grow flex flex-col gap-3 min-h-[400px]">
                                    @forelse($calendario[$dia] ?? [] as $bloqueClase)
                                        <!-- Tarjeta de Bloque de Clase -->
                                        <div class="bg-white border {{ $bloqueClase->bloque->es_recreo ? 'border-orange-200 bg-orange-50/50' : 'border-indigo-100 shadow-sm' }} rounded-lg p-3 text-center relative group hover:border-indigo-300 transition-colors">
                                            
                                            <!-- Etiqueta de la Hora -->
                                            <p class="text-[10px] font-bold {{ $bloqueClase->bloque->es_recreo ? 'text-orange-500' : 'text-indigo-500' }} uppercase mb-1 tracking-widest">
                                                {{ $bloqueClase->bloque->nombre }}
                                            </p>
                                            
                                            <!-- Rango de Hora -->
                                            <p class="text-xs font-semibold text-gray-500 mb-2 font-mono">
                                                {{ \Carbon\Carbon::parse($bloqueClase->bloque->hora_inicio)->format('h:i') }} - {{ \Carbon\Carbon::parse($bloqueClase->bloque->hora_fin)->format('h:i A') }}
                                            </p>
                                            
                                            <!-- Materia y Docente -->
                                            @if(!$bloqueClase->bloque->es_recreo)
                                                <div class="bg-gray-50 rounded py-1.5 px-2 border border-gray-100">
                                                    <p class="font-bold text-sm text-gray-800 leading-tight mb-0.5">
                                                        {{ $bloqueClase->aulaAsignaturaDocente->asignatura->nombre ?? 'Materia' }}
                                                    </p>
                                                    <p class="text-[10px] text-gray-500 uppercase font-semibold">
                                                        Prof. {{ explode(' ', trim($bloqueClase->aulaAsignaturaDocente->docente->usuario->nombre_completo ?? ''))[0] ?? 'D' }}
                                                    </p>
                                                </div>
                                            @else
                                                <div class="py-1">
                                                    <p class="font-bold text-sm text-orange-700 leading-tight uppercase tracking-wider">Receso</p>
                                                </div>
                                            @endif
                                            
                                            @can('horarios.gestionar')
                                            <!-- Botón eliminar que aparece al pasar el mouse -->
                                            @if(!$bloqueClase->bloque->es_recreo)
                                                <form action="{{ route('academico.aulas.horarios.destroy', [$aula->id, $bloqueClase->id]) }}" method="POST" class="absolute -top-2 -right-2 hidden group-hover:block alerta-eliminar-horario">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-white hover:text-red-100 bg-red-500 hover:bg-red-600 rounded-full p-1 shadow-md transition-colors" title="Quitar clase del horario">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </form>
                                            @endif
                                            @endcan
                                        </div>
                                    @empty
                                        <div class="flex-grow flex flex-col items-center justify-center text-center py-8 opacity-50">
                                            <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Día Libre</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script para SweetAlert2 -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alerta-eliminar-horario').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Quitar del Horario?',
                        text: "Esta materia dejará de impartirse en este día y bloque de tiempo.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, Quitar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>