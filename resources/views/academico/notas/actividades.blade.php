<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <!-- Flecha de Regreso con Memoria de Filtro -->
            <a href="{{ route('academico.notas.index', ['grado_id' => $asignacion->aula->grado_id, 'aula_id' => $asignacion->aula_id]) }}" class="text-stone-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Centro de Calificaciones">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            
            <div>
                <h2 class="font-bold text-2xl text-[#3d2c1d] leading-tight">
                    {{ $modoSupervision ? 'Auditoría de Actividades:' : 'Configurar Actividades:' }} 
                    <span class="text-[#e6ac27]">{{ $asignacion->asignatura->nombre }}</span>
                </h2>
                <!-- Contexto Completo sin Emojis -->
                <p class="text-sm text-stone-500 mt-1 font-bold flex items-center gap-2">
                    <span>{{ $asignacion->aula->grado->nombre }} - Sección "{{ $asignacion->aula->nombre }}"</span>
                    <span class="text-stone-300">|</span>
                    <span class="uppercase tracking-widest text-[10px] text-[#e6ac27] bg-[#e6ac27]/10 px-2.5 py-0.5 rounded-lg border border-[#e6ac27]/20 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        {{ $asignacion->docente->usuario->nombre_completo ?? 'SIN DOCENTE ASIGNADO' }}
                    </span>
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-stone-200">
                <form method="GET" class="flex flex-col md:flex-row items-center gap-4">
                    <label class="font-black text-[#3d2c1d] uppercase tracking-widest text-sm">Seleccione el Parcial:</label>
                    <select name="corte_id" onchange="this.form.submit()" class="border-stone-200 bg-slate-50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold text-[#3d2c1d]">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-stone-200">
                    <div class="flex justify-between items-end mb-3">
                        <h4 class="font-black text-stone-700 uppercase tracking-widest text-xs">Puntos de Acumulados</h4>
                        <span class="font-black text-xl {{ $sumaAcumulados == $corteActivo->peso_acumulado ? 'text-emerald-500' : 'text-[#e6ac27]' }}">
                            {{ $sumaAcumulados }} / {{ $corteActivo->peso_acumulado }} pts
                        </span>
                    </div>
                    <div class="w-full bg-stone-100 rounded-full h-3">
                        @php $porcentajeAcum = ($corteActivo->peso_acumulado > 0) ? ($sumaAcumulados / $corteActivo->peso_acumulado) * 100 : 0; @endphp
                        <div class="{{ $sumaAcumulados == $corteActivo->peso_acumulado ? 'bg-emerald-500' : 'bg-[#e6ac27]' }} h-3 rounded-full transition-all duration-500" style="width: {{ $porcentajeAcum }}%"></div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-stone-200">
                    <div class="flex justify-between items-end mb-3">
                        <h4 class="font-black text-stone-700 uppercase tracking-widest text-xs">Puntos de Examen</h4>
                        <span class="font-black text-xl {{ $sumaExamen == $corteActivo->peso_examen ? 'text-emerald-500' : 'text-blue-500' }}">
                            {{ $sumaExamen }} / {{ $corteActivo->peso_examen }} pts
                        </span>
                    </div>
                    <div class="w-full bg-stone-100 rounded-full h-3">
                        @php $porcentajeExam = ($corteActivo->peso_examen > 0) ? ($sumaExamen / $corteActivo->peso_examen) * 100 : 0; @endphp
                        <div class="{{ $sumaExamen == $corteActivo->peso_examen ? 'bg-emerald-500' : 'bg-blue-500' }} h-3 rounded-full transition-all duration-500" style="width: {{ $porcentajeExam }}%"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 {{ $modoSupervision ? '' : 'lg:grid-cols-3' }} gap-6">
                
                @if(!$modoSupervision)
                <div class="lg:col-span-1 bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-stone-200 self-start">
                    <h3 class="text-lg font-black text-[#3d2c1d] border-b border-stone-100 pb-4 mb-5">Nueva Actividad</h3>
                    <form action="{{ route('academico.notas.actividades.store', $asignacion->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                        
                        <div class="mb-5">
                            <label class="block text-[11px] font-black text-stone-400 uppercase tracking-widest mb-2">Nombre de la Tarea/Prueba</label>
                            <input type="text" name="nombre" class="w-full border-stone-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] font-medium text-sm" required placeholder="Ej. Prueba de Fracciones">
                        </div>
                        <div class="mb-5">
                            <label class="block text-[11px] font-black text-stone-400 uppercase tracking-widest mb-2">Tipo de Evaluación</label>
                            <select name="tipo" class="w-full border-stone-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] font-medium text-sm" required>
                                <option value="acumulado">Acumulado</option>
                                <option value="examen">Examen</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label class="block text-[11px] font-black text-stone-400 uppercase tracking-widest mb-2">Puntaje Máximo</label>
                            <input type="number" name="puntaje_maximo" class="w-full border-stone-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] font-black text-lg text-center" required min="1" max="100" placeholder="Ej. 10">
                        </div>
                        <button type="submit" class="w-full bg-[#3d2c1d] hover:bg-stone-800 text-white font-black py-3.5 px-4 rounded-xl shadow-sm transition-transform transform hover:-translate-y-0.5">
                            Guardar Actividad
                        </button>
                    </form>
                </div>
                @endif

                <div class="{{ $modoSupervision ? '' : 'lg:col-span-2' }} bg-white overflow-hidden shadow-sm rounded-3xl border border-stone-200">
                    <div class="px-6 py-5 border-b border-stone-100 bg-[#FFFDF5]">
                        <h3 class="text-sm font-black text-[#3d2c1d] uppercase tracking-widest">Actividades Registradas</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-100 text-sm text-left border-collapse">
                            <thead class="bg-white text-stone-400 uppercase text-[10px] font-black tracking-widest">
                                <tr>
                                    <th class="px-6 py-4">Tipo</th>
                                    <th class="px-6 py-4">Nombre de Actividad</th>
                                    <th class="px-6 py-4 text-center">Puntos</th>
                                    @if(!$modoSupervision)
                                        <th class="px-6 py-4 text-center">Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-50">
                                @forelse($actividades as $actividad)
                                    <tr class="hover:bg-stone-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-black uppercase tracking-widest text-[10px] px-2.5 py-1 rounded-md border 
                                                {{ $actividad->tipo == 'examen' ? 'bg-blue-50 text-blue-600 border-blue-200' : 'bg-amber-50 text-amber-600 border-amber-200' }}">
                                                {{ $actividad->tipo }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-black text-[#3d2c1d] text-base">{{ $actividad->nombre }}</td>
                                        <td class="px-6 py-4 text-center font-black text-lg text-stone-600">{{ $actividad->puntaje_maximo }}</td>
                                        
                                        @if(!$modoSupervision)
                                            <td class="px-6 py-4 text-center">
                                                <form action="{{ route('academico.notas.actividades.destroy', [$asignacion->id, $actividad->id]) }}" method="POST" class="form-eliminar-actividad inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-rose-400 hover:text-rose-600 hover:bg-rose-50 p-2 rounded-xl transition-colors" title="Eliminar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $modoSupervision ? '3' : '4' }}" class="px-6 py-12 text-center text-stone-500">
                                            <div class="w-12 h-12 bg-stone-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-stone-100 text-stone-300">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                            </div>
                                            <span class="font-black text-[#3d2c1d]">Sin Actividades</span>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.mixin({
                    toast: true, position: 'top', showConfirmButton: false, timer: 3500, timerProgressBar: true,
                    customClass: { popup: 'rounded-2xl shadow-lg border border-slate-100' }
                }).fire({ icon: 'success', title: '{{ session("success") }}' });
            @endif

            @if(session('error'))
                Swal.fire({
                    title: 'Límite Superado',
                    text: '{{ session("error") }}',
                    icon: 'warning',
                    confirmButtonColor: '#3d2c1d',
                    customClass: { popup: 'rounded-3xl border border-stone-200 shadow-xl' }
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
                        customClass: { popup: 'rounded-3xl border border-stone-200 shadow-xl' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>