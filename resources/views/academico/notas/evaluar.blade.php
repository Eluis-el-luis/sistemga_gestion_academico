<div class="space-y-6 animate-fade-in bg-[#FFFDF5] min-h-screen pb-12">
    
    <!-- Encabezado Operativo (Sin gradientes, colores institucionales puros) -->
    <div class="bg-white border-b border-slate-200 shadow-sm px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-[#3d2c1d]">Registro de Calificaciones</h2>
            <p class="text-sm font-medium text-slate-500 mt-1">Matemática | 7mo Grado "A" | 1er Parcial</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Botón Secundario (UX Tip 3: Coherencia visual, sin relleno fuerte) -->
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 text-sm font-bold text-[#3d2c1d] border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                Cancelar
            </a>
            <!-- Botón Principal (UX Tip 1 & 3: Acción clara, Dorado Institucional) -->
            <button type="submit" form="form-calificaciones" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white text-sm font-black rounded-xl shadow-sm transition-transform transform hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Guardar Calificaciones
            </button>
        </div>
    </div>

    <!-- Modal Alpine.js: Expediente Express de Asignatura -->
<div x-data="{ alumnoId: '', nombreAlumno: '', notaActual: 0 }" 
     @abrir-modal-expediente.window="alumnoId = $event.detail.id; nombreAlumno = $event.detail.nombre; notaActual = $event.detail.nota; $dispatch('open-modal', 'modal-expediente')">
    
    <x-modal name="modal-expediente" focusable maxWidth="md">
        <div class="bg-[#FFFDF5] px-6 py-5 border-b border-[#e6ac27]/20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-black text-sm border border-slate-200">
                    <span x-text="nombreAlumno.charAt(0)"></span>
                </div>
                <div>
                    <h2 class="text-base font-black text-[#3d2c1d] leading-tight" x-text="nombreAlumno"></h2>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Desglose de Rendimiento</span>
                </div>
            </div>
            <button @click="$dispatch('close')" class="text-slate-400 hover:text-rose-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="p-6 space-y-6 bg-white">
                <!-- Métrica Principal -->
                <div class="flex items-center justify-between p-4 rounded-2xl border border-rose-100 bg-rose-50/30">
                    <div>
                        <p class="text-xs font-black text-rose-500 uppercase tracking-widest">Nota Parcial</p>
                        <p class="text-3xl font-black text-[#3d2c1d] leading-none mt-1" x-text="notaActual + '/100'"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-slate-500">Estado Sugerido</p>
                        <span class="inline-block px-3 py-1 bg-rose-500 text-white text-[10px] font-black uppercase tracking-widest rounded-lg mt-1 shadow-sm">Reprobando</span>
                    </div>
                </div>

                <!-- Desglose de Pruebas -->
                <div>
                    <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3">Evidencias Acumuladas</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50">
                            <span class="text-sm font-bold text-slate-600">Prueba Corta 1</span>
                            <span class="text-sm font-black text-rose-500">0 / 20 pts</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50">
                            <span class="text-sm font-bold text-slate-600">Exposición Grupal</span>
                            <span class="text-sm font-black text-[#e6ac27]">15 / 40 pts</span>
                        </div>
                    </div>
                </div>

                <!-- Alerta de Asistencia -->
                <div class="flex items-start gap-3 p-4 rounded-xl border border-amber-200 bg-amber-50">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <p class="text-xs font-black text-amber-800 uppercase tracking-widest">Inasistencias Críticas</p>
                        <p class="text-xs font-medium text-amber-700 mt-1">El estudiante acumula 3 fugas documentadas durante los bloques de esta asignatura.</p>
                    </div>
                </div>
            </div>
        </x-modal>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
        
        <!-- Resumen de Puntos -->
        <div class="flex flex-wrap gap-4 mb-6">
            <div class="bg-white border border-slate-200 px-4 py-3 rounded-2xl shadow-sm flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                <span class="text-sm font-bold text-slate-600">Acumulado Distribuido: <strong class="text-[#3d2c1d]">60/60</strong></span>
            </div>
            <div class="bg-white border border-slate-200 px-4 py-3 rounded-2xl shadow-sm flex items-center gap-3">
                <div class="w-3 h-3 rounded-full bg-rose-500"></div>
                <span class="text-sm font-bold text-slate-600">Examen: <strong class="text-[#3d2c1d]">40/40</strong></span>
            </div>
        </div>
        <!-- Celda interactiva en la tabla de notas -->
        <td class="px-6 py-4 sticky left-0 bg-white group-hover:bg-slate-50/50 transition-colors z-10 shadow-[1px_0_0_0_#f1f5f9]">
            <button @click.prevent="$dispatch('abrir-modal-expediente', { id: 1, nombre: 'Alvarez Perez, Juan Carlos', nota: 35 })" 
                    class="text-left focus:outline-none w-full group/btn">
                <p class="font-black text-sm text-[#3d2c1d] group-hover/btn:text-[#e6ac27] transition-colors flex items-center gap-2">
                    Alvarez Perez, Juan Carlos
                    <svg class="w-4 h-4 text-slate-300 opacity-0 group-hover/btn:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                </p>
                <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest mt-0.5">Riesgo Académico</p>
            </button>
        </td>

        <!-- Tabla de Inserción de Notas -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <form id="form-calificaciones" method="POST" action="#">
                @csrf
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4 font-black text-xs text-slate-500 uppercase tracking-widest sticky left-0 bg-slate-50 z-10 shadow-[1px_0_0_0_#e2e8f0]">
                                    Estudiante
                                </th>
                                <!-- Columnas de Evaluaciones Dinámicas -->
                                <th class="px-4 py-4 text-center">
                                    <span class="block text-[11px] font-black text-[#3d2c1d] uppercase tracking-widest">Prueba Corta</span>
                                    <span class="block text-[10px] text-slate-400 font-bold mt-1">20 pts</span>
                                </th>
                                <th class="px-4 py-4 text-center border-l border-slate-200">
                                    <span class="block text-[11px] font-black text-[#3d2c1d] uppercase tracking-widest">Exposición</span>
                                    <span class="block text-[10px] text-slate-400 font-bold mt-1">40 pts</span>
                                </th>
                                <th class="px-4 py-4 text-center border-l border-slate-200 bg-amber-50/50">
                                    <span class="block text-[11px] font-black text-[#e6ac27] uppercase tracking-widest">Examen Final</span>
                                    <span class="block text-[10px] text-amber-600/60 font-bold mt-1">40 pts</span>
                                </th>
                                <th class="px-6 py-4 text-center border-l border-slate-200 bg-slate-100/50">
                                    <span class="block text-xs font-black text-[#3d2c1d] uppercase tracking-widest">Nota Final</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Fila de Ejemplo 1 -->
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 sticky left-0 bg-white group-hover:bg-slate-50/50 transition-colors z-10 shadow-[1px_0_0_0_#f1f5f9]">
                                    <p class="font-black text-sm text-[#3d2c1d]">Alvarez Perez, Juan Carlos</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" max="20" min="0" placeholder="-" class="w-16 text-center font-bold text-sm text-[#3d2c1d] border-slate-200 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] placeholder:text-slate-300 shadow-sm transition-colors bg-slate-50/50 focus:bg-white py-2">
                                </td>
                                <td class="px-4 py-3 text-center border-l border-slate-50">
                                    <input type="number" max="40" min="0" placeholder="-" class="w-16 text-center font-bold text-sm text-[#3d2c1d] border-slate-200 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] placeholder:text-slate-300 shadow-sm transition-colors bg-slate-50/50 focus:bg-white py-2">
                                </td>
                                <td class="px-4 py-3 text-center border-l border-slate-50 bg-amber-50/20">
                                    <input type="number" max="40" min="0" placeholder="-" class="w-16 text-center font-black text-sm text-[#e6ac27] border-amber-200 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] placeholder:text-amber-300/50 shadow-sm transition-colors bg-white py-2">
                                </td>
                                <td class="px-6 py-3 text-center border-l border-slate-50 bg-slate-50/50">
                                    <span class="text-lg font-black text-slate-400">0</span>
                                </td>
                            </tr>
                            
                            <!-- Fila de Ejemplo 2 -->
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="px-6 py-4 sticky left-0 bg-white group-hover:bg-slate-50/50 transition-colors z-10 shadow-[1px_0_0_0_#f1f5f9]">
                                    <p class="font-black text-sm text-[#3d2c1d]">Blandón Ruiz, Maria Fernanda</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" max="20" min="0" value="18" class="w-16 text-center font-bold text-sm text-[#3d2c1d] border-slate-200 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] placeholder:text-slate-300 shadow-sm transition-colors bg-slate-50/50 focus:bg-white py-2">
                                </td>
                                <td class="px-4 py-3 text-center border-l border-slate-50">
                                    <input type="number" max="40" min="0" value="38" class="w-16 text-center font-bold text-sm text-[#3d2c1d] border-slate-200 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] placeholder:text-slate-300 shadow-sm transition-colors bg-slate-50/50 focus:bg-white py-2">
                                </td>
                                <td class="px-4 py-3 text-center border-l border-slate-50 bg-amber-50/20">
                                    <input type="number" max="40" min="0" placeholder="-" class="w-16 text-center font-black text-sm text-[#e6ac27] border-amber-200 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] placeholder:text-amber-300/50 shadow-sm transition-colors bg-white py-2">
                                </td>
                                <td class="px-6 py-3 text-center border-l border-slate-50 bg-slate-50/50">
                                    <span class="text-lg font-black text-[#3d2c1d]">56</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>