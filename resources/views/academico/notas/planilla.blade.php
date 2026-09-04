<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 z-50 relative">
            <div>
                <h2 class="text-xl font-black text-[#3d2c1d] tracking-tight flex items-center gap-2">
                    <a href="{{ route('academico.notas.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    Planilla de Calificaciones
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1 ml-8">
                    {{ $asignacion->asignatura->nombre }} | {{ $asignacion->aula->grado->nombre }} - {{ $asignacion->aula->nombre }}
                </p>
            </div>
            
            <!-- Selector de Corte Evaluativo (Recarga la página al cambiar) -->
            <form action="{{ route('academico.notas.create', $asignacion->id) }}" method="GET" class="flex items-center gap-3">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Parcial:</label>
                <select name="corte_evaluativo_id" onchange="this.form.submit()" class="border-slate-200 bg-white rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] font-bold shadow-sm cursor-pointer">
                    @foreach($cortes as $corte)
                        <option value="{{ $corte->id }}" {{ $corteSeleccionado == $corte->id ? 'selected' : '' }}>
                            Corte {{ $corte->numero }} ({{ $corte->anioEscolar->nombre }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen bg-slate-50 relative">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- BARRA DE ESTADO Y ACCIONES -->
            <div class="bg-white p-5 rounded-3xl border {{ $estaBloqueado ? 'border-rose-200 bg-rose-50/30' : 'border-[#e6ac27]/30' }} shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $estaBloqueado ? 'bg-rose-100 text-rose-500' : 'bg-emerald-100 text-emerald-500' }}">
                        @if($estaBloqueado)
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-black text-[#3d2c1d] text-base leading-tight">
                            {{ $estaBloqueado ? 'Calificaciones Bloqueadas' : 'Edición Abierta' }}
                        </h3>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">
                            {{ $estaBloqueado ? 'El parcial fue cerrado. Solo lectura.' : 'Puedes ingresar y modificar notas.' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('academico.notas.actividades.index', $asignacion->id) }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black rounded-xl transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Gestionar Actividades
                    </a>
                    
                    @if($estaBloqueado)
                        <button @click="$dispatch('abrir-modal-desbloqueo')" class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-xs font-black rounded-xl shadow-md transition-colors flex items-center gap-2">
                            Solicitar Desbloqueo
                        </button>
                    @else
                        @if($actividades->count() > 0)
                            <button form="form-planilla" type="submit" class="px-6 py-2.5 bg-[#3d2c1d] hover:bg-slate-800 text-white text-xs font-black rounded-xl shadow-md transition-all flex items-center gap-2">
                                Guardar Avance
                            </button>
                            <button @click="$dispatch('abrir-modal-cierre')" class="px-5 py-2.5 border-2 border-rose-500 text-rose-500 hover:bg-rose-500 hover:text-white text-xs font-black rounded-xl transition-colors">
                                Cerrar Parcial
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            <!-- CONTENEDOR DE LA PLANILLA -->
            @if($actividades->isEmpty())
                <div class="bg-white p-10 rounded-3xl border border-slate-200 shadow-sm text-center">
                    <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mx-auto text-amber-500 mb-4">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-[#3d2c1d] mb-2">No hay actividades configuradas</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">Debes crear la distribución de puntos (tareas, exposiciones, examen) antes de poder ingresar calificaciones.</p>
                    <a href="{{ route('academico.notas.actividades.index', $asignacion->id) }}" class="inline-block px-8 py-3 bg-[#e6ac27] hover:bg-amber-500 text-[#3d2c1d] font-black rounded-xl shadow-md transition-all transform hover:-translate-y-0.5">
                        Ir a Configurar Acumulado
                    </a>
                </div>
            @else
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <form id="form-planilla" action="{{ route('academico.notas.store', $asignacion->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                        
                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse whitespace-nowrap">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="px-6 py-4 text-[11px] font-black text-slate-400 uppercase tracking-widest sticky left-0 bg-slate-50 z-20 shadow-[1px_0_0_0_#e2e8f0]"># Estudiante</th>
                                        
                                        @foreach($actividades as $actividad)
                                            <th class="px-4 py-4 text-center">
                                                <span class="block text-xs font-bold text-[#3d2c1d] truncate max-w-[120px]" title="{{ $actividad->nombre }}">{{ $actividad->nombre }}</span>
                                                <span class="block text-[10px] font-black text-[#e6ac27] uppercase tracking-widest mt-1">Máx: {{ $actividad->puntaje_maximo }} pts</span>
                                            </th>
                                        @endforeach
                                        
                                        <th class="px-6 py-4 text-center bg-[#FFFDF5] border-l border-amber-100">
                                            <span class="block text-xs font-black text-[#3d2c1d]">Total Parcial</span>
                                            <span class="block text-[10px] font-black text-amber-500 uppercase tracking-widest mt-1">Auto-suma</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($matriculas as $index => $matricula)
                                        @php
                                            $notaCorte = $matricula->notas->firstWhere('corte_evaluativo_id', $corteSeleccionado);
                                            $totalInicial = $notaCorte ? ($notaCorte->nota_cuantitativa ?? 0) : 0;
                                        @endphp
                                        
                                        <!-- ALPINE.JS: Cada fila maneja su propia auto-suma en tiempo real -->
                                        <tr class="hover:bg-slate-50/50 transition-colors" 
                                            x-data="{ 
                                                totalFila: {{ $totalInicial }},
                                                recalcular() {
                                                    let suma = 0;
                                                    this.$el.querySelectorAll('.input-nota').forEach(input => {
                                                        let valor = parseFloat(input.value) || 0;
                                                        let max = parseFloat(input.getAttribute('max')) || 0;
                                                        if(valor > max) { input.value = max; valor = max; } // Bloqueo visual
                                                        suma += valor;
                                                    });
                                                    this.totalFila = suma.toFixed(2).replace(/\.00$/, '');
                                                }
                                            }">
                                            
                                            <td class="px-6 py-3 sticky left-0 bg-white z-10 shadow-[1px_0_0_0_#f1f5f9]">
                                                <div class="flex items-center gap-3">
                                                    <span class="text-xs font-bold text-slate-400">{{ $index + 1 }}</span>
                                                    <span class="text-sm font-black text-[#3d2c1d]">{{ $matricula->alumno->nombre_completo }}</span>
                                                </div>
                                            </td>
                                            
                                            @foreach($actividades as $actividad)
                                                @php
                                                    $na = isset($notasActividades[$matricula->id])
                                                        ? $notasActividades[$matricula->id]->firstWhere('actividad_evaluativa_id', $actividad->id)
                                                        : null;
                                                    $notaExistente = $na ? ($na->nota_obtenida ?? '') : '';
                                                @endphp
                                                <td class="px-2 py-3 text-center">
                                                    <input type="number" 
                                                           name="notas[{{ $matricula->id }}][{{ $actividad->id }}]" 
                                                           value="{{ $notaExistente }}"
                                                           max="{{ $actividad->puntaje_maximo }}" 
                                                           min="0" step="0.01"
                                                           @input="recalcular"
                                                           {{ $estaBloqueado ? 'readonly' : '' }}
                                                           class="input-nota w-20 text-center text-sm font-bold rounded-lg border-slate-200 focus:ring-[#e6ac27] focus:border-[#e6ac27] 
                                                                  {{ $estaBloqueado ? 'bg-slate-100 text-slate-400 cursor-not-allowed border-transparent' : 'text-[#3d2c1d]' }}">
                                                </td>
                                            @endforeach
                                            
                                            <td class="px-6 py-3 text-center bg-[#FFFDF5] border-l border-amber-100">
                                                <span class="text-base font-black text-[#3d2c1d]" x-text="totalFila"></span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL: CERRAR PARCIAL -->
    <div x-data="{ open: false }" @abrir-modal-cierre.window="open = true" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open" x-transition.scale class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-5 border-b border-rose-100 bg-rose-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-rose-600 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    ¿Cerrar Calificaciones?
                </h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                    Al cerrar el parcial, <strong>las notas quedarán bloqueadas y no podrán ser editadas</strong>. Esta acción genera el reporte oficial para la Directiva. <br><br>¿Estás seguro de que todas las calificaciones son correctas?
                </p>
                <form action="{{ route('academico.notas.cerrar', $asignacion->id) }}" method="POST" class="flex justify-end gap-3">
                    @csrf
                    <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition-colors">Revisar de nuevo</button>
                    <button type="submit" class="px-5 py-2.5 bg-rose-500 hover:bg-rose-600 text-white text-sm font-black rounded-xl shadow-md transition-transform transform hover:-translate-y-0.5">Sí, Cerrar Parcial</button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: SOLICITAR DESBLOQUEO -->
    <div x-data="{ open: false }" @abrir-modal-desbloqueo.window="open = true" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open" x-transition.scale class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-amber-100 bg-amber-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-amber-600 flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Solicitud de Desbloqueo
                </h3>
            </div>
            <form action="{{ route('academico.notas.solicitar-desbloqueo', $asignacion->id) }}" method="POST">
                @csrf
                <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">
                <div class="p-6">
                    <p class="text-xs font-bold text-slate-500 mb-4">Esta solicitud será enviada a Subdirección. Detalla claramente qué nota necesitas cambiar y por qué.</p>
                    
                    <label class="block text-[11px] font-black text-[#3d2c1d] uppercase tracking-widest mb-2">Motivo de la corrección</label>
                    <textarea name="motivo" rows="4" required placeholder="Ej: Hubo un error de digitación en la tarea X del estudiante Y..." class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-amber-400 focus:border-amber-400 text-sm text-[#3d2c1d]"></textarea>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-200 rounded-xl transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-black rounded-xl shadow-md transition-transform transform hover:-translate-y-0.5">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script de SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    toast: true, position: 'top', showConfirmButton: false, timer: 3500,
                    icon: 'success', title: '{{ session("success") }}',
                    customClass: { popup: 'rounded-2xl shadow-lg border border-slate-100' }
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    title: 'Acción Denegada', text: '{{ session("error") }}', icon: 'error',
                    confirmButtonColor: '#e53e3e', customClass: { popup: 'rounded-3xl border border-stone-200 shadow-xl' }
                });
            @endif
        });
    </script>
</x-app-layout>