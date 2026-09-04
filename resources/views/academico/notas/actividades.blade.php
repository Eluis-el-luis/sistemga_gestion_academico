<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <!-- Flecha de Regreso con Memoria de Filtro -->
            <a href="{{ route('academico.notas.index', ['grado_id' => $asignacion->aula->grado_id, 'aula_id' => $asignacion->aula_id]) }}" class="text-slate-400 hover:text-[#e6ac27] dark:hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Centro de Calificaciones">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] dark:text-white leading-tight">
                    {{ $modoSupervision ? 'Auditoría de Actividades:' : 'Configurar Actividades:' }} 
                    <span class="text-[#e6ac27]">{{ $asignacion->asignatura->nombre }}</span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 font-bold flex flex-wrap items-center gap-2">
                    <span>{{ $asignacion->aula->grado->nombre }} - Sección "{{ $asignacion->aula->nombre }}"</span>
                    <span class="text-slate-300 dark:text-slate-600 hidden sm:inline">|</span>
                    <span class="uppercase tracking-widest text-[10px] text-[#e6ac27] bg-[#e6ac27]/10 px-2.5 py-0.5 rounded-lg border border-[#e6ac27]/20 flex items-center gap-1.5 mt-1 sm:mt-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        {{ $asignacion->docente->usuario->nombre_completo ?? 'SIN DOCENTE ASIGNADO' }}
                    </span>
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] dark:bg-slate-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                <form method="GET" class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full md:w-auto">
                    <label class="font-black text-[#3d2c1d] dark:text-white uppercase tracking-widest text-sm">Seleccione el Parcial:</label>
                    <select name="corte_id" onchange="this.form.submit()" class="w-full sm:w-auto border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold text-[#3d2c1d] dark:text-slate-200 transition-colors">
                        @foreach($cortes as $corte)
                            <option value="{{ $corte->id }}" {{ $corteSeleccionado == $corte->id ? 'selected' : '' }}>
                                {{ $corte->numero }}° Parcial (Semestre {{ $corte->semestre }})
                            </option>
                        @endforeach
                    </select>
                </form>
                
                <!-- Botón para ir a Calificar -->
                @if($corteActivo && $sumaAcumulados == $corteActivo->peso_acumulado && $sumaExamen == $corteActivo->peso_examen)
                    <a href="{{ route('academico.notas.create', ['asignacion' => $asignacion->id, 'corte_evaluativo_id' => $corteSeleccionado]) }}" class="w-full md:w-auto bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-3 px-6 rounded-xl shadow-sm transition-transform transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
                        Ir a Calificar Planilla 
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                @endif
            </div>

            @if($corteActivo)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Barra Acumulado -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <div class="flex justify-between items-end mb-3">
                        <h4 class="font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest text-xs">Puntos de Acumulados</h4>
                        <span class="font-black text-2xl {{ $sumaAcumulados == $corteActivo->peso_acumulado ? 'text-emerald-500' : 'text-[#e6ac27]' }}">
                            {{ $sumaAcumulados }} <span class="text-base text-slate-400">/ {{ $corteActivo->peso_acumulado }} pts</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-700/50 rounded-full h-3 overflow-hidden">
                        @php $porcentajeAcum = ($corteActivo->peso_acumulado > 0) ? ($sumaAcumulados / $corteActivo->peso_acumulado) * 100 : 0; @endphp
                        <div class="{{ $sumaAcumulados == $corteActivo->peso_acumulado ? 'bg-emerald-500' : 'bg-[#e6ac27]' }} h-3 rounded-full transition-all duration-500" style="width: {{ $porcentajeAcum }}%"></div>
                    </div>
                </div>

                <!-- Barra Examen -->
                <div class="bg-white dark:bg-slate-800 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <div class="flex justify-between items-end mb-3">
                        <h4 class="font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest text-xs">Puntos de Examen</h4>
                        <span class="font-black text-2xl {{ $sumaExamen == $corteActivo->peso_examen ? 'text-emerald-500' : 'text-blue-500' }}">
                            {{ $sumaExamen }} <span class="text-base text-slate-400">/ {{ $corteActivo->peso_examen }} pts</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-700/50 rounded-full h-3 overflow-hidden">
                        @php $porcentajeExam = ($corteActivo->peso_examen > 0) ? ($sumaExamen / $corteActivo->peso_examen) * 100 : 0; @endphp
                        <div class="{{ $sumaExamen == $corteActivo->peso_examen ? 'bg-emerald-500' : 'bg-blue-500' }} h-3 rounded-full transition-all duration-500" style="width: {{ $porcentajeExam }}%"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 {{ $modoSupervision ? '' : 'lg:grid-cols-12' }} gap-6">
                
                @if(!$modoSupervision)
                <div class="lg:col-span-4 bg-white dark:bg-slate-800 p-6 md:p-8 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 self-start">
                    <h3 class="text-lg font-black text-[#3d2c1d] dark:text-white border-b border-slate-100 dark:border-slate-700 pb-4 mb-5">Nueva Actividad</h3>
                    <form action="{{ route('academico.notas.actividades.store', $asignacion->id) }}" method="POST" class="space-y-5">
                        @csrf
                        <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                        
                        <div>
                            <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-2">Nombre de la Evaluación</label>
                            <input type="text" name="nombre" class="w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-[#3d2c1d] dark:text-white font-medium text-sm transition-colors" required placeholder="Ej. Prueba de Fracciones">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-2">Tipo de Evaluación</label>
                            <select name="tipo" class="w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-[#3d2c1d] dark:text-white font-medium text-sm transition-colors" required>
                                <option value="acumulado">Acumulado</option>
                                <option value="examen">Examen</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-2">Puntaje Máximo</label>
                            <input type="number" name="puntaje_maximo" class="w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-[#3d2c1d] dark:text-white font-black text-lg text-center transition-colors" required min="1" max="100" placeholder="Ej. 10">
                        </div>
                        <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-3.5 px-4 rounded-xl shadow-sm transition-transform transform hover:-translate-y-0.5 mt-2">
                            Guardar Actividad
                        </button>
                    </form>
                </div>
                @endif

                <div class="{{ $modoSupervision ? '' : 'lg:col-span-8' }} bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-3xl border border-slate-200 dark:border-slate-700">
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 bg-[#FFFDF5] dark:bg-slate-800/50">
                        <h3 class="text-sm font-black text-[#3d2c1d] dark:text-white uppercase tracking-widest">Actividades Registradas</h3>
                    </div>
                    
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="min-w-full text-sm text-left border-collapse">
                            <thead class="bg-white dark:bg-slate-800 text-slate-400 dark:text-slate-500 uppercase text-[10px] font-black tracking-widest border-b border-slate-100 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-4">Tipo</th>
                                    <th class="px-6 py-4">Nombre de Actividad</th>
                                    <th class="px-6 py-4 text-center">Puntos</th>
                                    @if(!$modoSupervision)
                                        <th class="px-6 py-4 text-center">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                                @forelse($actividades as $actividad)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-black uppercase tracking-widest text-[10px] px-2.5 py-1 rounded-md border 
                                                {{ $actividad->tipo == 'examen' ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-200 dark:border-blue-500/20' : 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-200 dark:border-amber-500/20' }}">
                                                {{ $actividad->tipo }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-black text-[#3d2c1d] dark:text-white text-base">{{ $actividad->nombre }}</td>
                                        <td class="px-6 py-4 text-center font-black text-lg text-slate-600 dark:text-slate-300">{{ $actividad->puntaje_maximo }}</td>
                                        
                                        @if(!$modoSupervision)
                                            <td class="px-6 py-4 text-center">
                                                <form action="{{ route('academico.notas.actividades.destroy', [$asignacion->id, $actividad->id]) }}" method="POST" class="form-eliminar-actividad inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-rose-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 p-2 rounded-xl transition-colors" title="Eliminar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $modoSupervision ? '3' : '4' }}" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                            <div class="w-12 h-12 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-100 dark:border-slate-700 text-slate-300 dark:text-slate-600">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                            </div>
                                            <span class="font-black text-[#3d2c1d] dark:text-white">Sin Actividades</span>
                                            <p class="text-xs mt-1">Aún no se han configurado actividades para este parcial.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Scripts unificados con el componente de alertas general -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('error'))
                Swal.fire({
                    title: 'Límite Superado',
                    text: '{{ session("error") }}',
                    icon: 'warning',
                    confirmButtonColor: '#e6ac27',
                    customClass: { popup: 'rounded-3xl border border-slate-200 dark:border-slate-700 dark:bg-slate-900 shadow-xl dark:text-white' }
                });
            @endif

            document.querySelectorAll('.form-eliminar-actividad').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Eliminar Actividad?',
                        text: "Se borrarán también las calificaciones asignadas a los alumnos en esta actividad. No se puede deshacer.",
                        icon: 'error',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200 dark:border-slate-700 dark:bg-slate-900 shadow-xl dark:text-white' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>