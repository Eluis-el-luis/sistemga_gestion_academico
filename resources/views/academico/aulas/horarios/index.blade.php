<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.aulas.show', $aula->id) }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Aula">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                </a>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Gestor de Horarios: <span class="text-[#e6ac27]">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
                </h2>
            </div>
            
            <!-- Badge Informativo del Turno -->
            <span class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-xl font-black text-xs uppercase tracking-widest text-slate-600 shadow-sm">
                Turno: {{ ucfirst($aula->turno) }}
            </span>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        @can('horarios.gestionar')
        <!-- BLOQUE SUPERIOR 1: LA BOLSA DE HORAS HORIZONTAL (ESTILO MÉTRICAS) -->
        <div class="bg-white shadow-sm rounded-3xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-black text-sm text-slate-700 uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Progreso de la Bolsa de Horas Semanales
                </h3>
                <span class="text-xs font-bold text-slate-400">Las materias completas se deshabilitan automáticamente al programarlas</span>
            </div>

            <!-- Grid horizontal de materias -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($asignaciones as $asignacion)
                    @php
                        $porcentaje = $asignacion->horas_semanales > 0 ? ($asignacion->horas_programadas / $asignacion->horas_semanales) * 100 : 0;
                        $completada = $asignacion->horas_restantes <= 0;
                    @endphp
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-1.5">
                                <span class="text-xs font-black {{ $completada ? 'text-emerald-700' : 'text-[#3d2c1d]' }} truncate pr-1" title="{{ $asignacion->asignatura->nombre }}">
                                    {{ $asignacion->asignatura->nombre }}
                                </span>
                                <span class="text-[11px] font-black {{ $completada ? 'text-emerald-600 bg-emerald-50 border border-emerald-200 px-1.5 py-0.5 rounded-md' : 'text-slate-500' }} shrink-0">
                                    {{ $asignacion->horas_programadas }}/{{ $asignacion->horas_semanales }}h
                                </span>
                            </div>
                        </div>
                        <!-- Barra de progreso -->
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden mt-2">
                            <div class="h-2 rounded-full transition-all duration-500 {{ $completada ? 'bg-emerald-500' : 'bg-[#e6ac27]' }}" style="width: {{ min(100, $porcentaje) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- BLOQUE SUPERIOR 2: FORMULARIO DE AÑADIR CLASE EN LÍNEA (HORIZONTAL) -->
        <div class="bg-white shadow-sm rounded-3xl border border-slate-200 p-6">
            <form action="{{ route('academico.aulas.horarios.store', $aula->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @csrf
                
                <div class="md:col-span-3">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Día de la Semana <span class="text-rose-500">*</span></label>
                    <select name="dia_semana" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-medium transition-colors" required>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                    </select>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Materia / Docente <span class="text-rose-500">*</span></label>
                    <select name="aula_asignatura_docente_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-medium transition-colors" required>
                        <option value="">Seleccione materia...</option>
                        @foreach($asignaciones ?? [] as $asignacion)
                            @php
                                $completada = $asignacion->horas_restantes <= 0;
                                $sinDocente = !$asignacion->docente_id;
                            @endphp
                            <option value="{{ $asignacion->id }}" {{ ($sinDocente || $completada) ? 'disabled' : '' }}>
                                {{ $asignacion->asignatura->nombre }} 
                                {{ $sinDocente ? '(Sin Docente)' : '' }}
                                {{ (!$sinDocente && $completada) ? '(Horas Completas ✅)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Bloque de Tiempo <span class="text-rose-500">*</span></label>
                    <select name="bloque_horario_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-medium transition-colors" required>
                        <option value="">Seleccione la hora...</option>
                        @foreach($bloquesOficiales ?? [] as $bloque)
                            <option value="{{ $bloque->id }}" {{ $bloque->es_recreo ? 'disabled' : '' }}>
                                {{ $bloque->nombre }} ({{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }})
                                {{ $bloque->es_recreo ? '☕ [RECESO]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-2.5 rounded-xl text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 focus:ring-2 focus:ring-offset-2 focus:ring-[#e6ac27] h-[42px] flex items-center justify-center gap-2">
                        <span>+ Añadir</span>
                    </button>
                </div>
            </form>
        </div>
        @endcan

        <!-- SECCIÓN PRINCIPAL: CALENDARIO SEMANAL A TODO EL ANCHO -->
        <div class="bg-white shadow-sm rounded-3xl p-6 border border-slate-200 overflow-x-auto">
            <div class="min-w-[900px]">
                <div class="grid grid-cols-5 gap-5 h-full">
                    
                    @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia)
                        <!-- Columna de un Día -->
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 flex flex-col">
                            <div class="bg-[#FFFDF5] py-4 text-center border-b border-[#e6ac27]/20 rounded-t-2xl">
                                <h4 class="font-black text-[#3d2c1d] uppercase text-sm tracking-widest">{{ $dia }}</h4>
                            </div>
                            
                            <div class="p-4 flex-grow flex flex-col gap-4 min-h-[450px]">
                                @forelse($calendario[$dia] ?? [] as $bloqueClase)
                                    <!-- Tarjeta de Bloque de Clase -->
                                    <div class="bg-white border {{ $bloqueClase->bloque->es_recreo ? 'border-amber-200 bg-amber-50/50' : 'border-slate-200 shadow-sm' }} rounded-xl p-4 text-center relative group hover:border-[#e6ac27] transition-colors">
                                        
                                        <!-- Etiqueta de la Hora -->
                                        <p class="text-[10px] font-black {{ $bloqueClase->bloque->es_recreo ? 'text-amber-500' : 'text-[#e6ac27]' }} uppercase mb-1.5 tracking-widest">
                                            {{ $bloqueClase->bloque->nombre }}
                                        </p>
                                        
                                        <!-- Rango de Hora -->
                                        <p class="text-xs font-bold text-slate-500 mb-3 font-mono">
                                            {{ \Carbon\Carbon::parse($bloqueClase->bloque->hora_inicio)->format('h:i') }} - {{ \Carbon\Carbon::parse($bloqueClase->bloque->hora_fin)->format('h:i A') }}
                                        </p>
                                        
                                        <!-- Materia y Docente -->
                                        @if(!$bloqueClase->bloque->es_recreo)
                                            <div class="bg-slate-50 rounded-lg py-2 px-2 border border-slate-100">
                                                <p class="font-black text-sm text-[#3d2c1d] leading-tight mb-1">
                                                    {{ $bloqueClase->aulaAsignaturaDocente->asignatura->nombre ?? 'Materia' }}
                                                </p>
                                                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">
                                                    Prof. {{ explode(' ', trim($bloqueClase->aulaAsignaturaDocente->docente->usuario->nombre_completo ?? ''))[0] ?? 'D' }}
                                                </p>
                                            </div>
                                        @else
                                            <div class="py-2">
                                                <p class="font-black text-sm text-amber-700 leading-tight uppercase tracking-widest">Receso</p>
                                            </div>
                                        @endif
                                        
                                        @can('horarios.gestionar')
                                        <!-- Botón eliminar que aparece al pasar el mouse -->
                                        @if(!$bloqueClase->bloque->es_recreo)
                                            <form action="{{ route('academico.aulas.horarios.destroy', [$aula->id, $bloqueClase->id]) }}" method="POST" class="absolute -top-3 -right-3 hidden group-hover:block alerta-eliminar-horario z-10">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-white bg-rose-500 hover:bg-rose-600 rounded-full p-1.5 shadow-md transition-transform transform hover:scale-110" title="Quitar clase del horario">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </form>
                                        @endif
                                        @endcan
                                    </div>
                                @empty
                                    <div class="flex-grow flex flex-col items-center justify-center text-center py-12 opacity-50">
                                        <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Día Libre</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach

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
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, Quitar',
                        cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>