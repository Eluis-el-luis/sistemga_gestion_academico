<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Panel">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Buzón Disciplinario</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Gestión de Casos y Citaciones</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                @if($incidencias->isEmpty())
                    <div class="p-16 text-center flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 mb-4 border border-emerald-100">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-[#3d2c1d]">Buzón Limpio</h3>
                        <p class="text-sm font-medium text-slate-400 mt-1">No hay incidencias disciplinarias reportadas en tu modalidad.</p>
                    </div>
                @else
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Estudiante y Aula</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Falta</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Estado</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($incidencias as $inc)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="block text-sm font-black text-[#3d2c1d]">{{ $inc->matricula->alumno->nombre_completo }}</span>
                                            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">
                                                {{ $inc->matricula->aula->grado->nombre }} - {{ $inc->matricula->aula->nombre }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $colorFalta = $inc->nivel_falta === 'Leve' ? 'text-amber-500' : ($inc->nivel_falta === 'Grave' ? 'text-orange-500' : 'text-rose-600');
                                            @endphp
                                            <span class="block text-xs font-black {{ $colorFalta }} uppercase tracking-widest">{{ $inc->nivel_falta }}</span>
                                            <span class="block text-xs font-medium text-slate-500 mt-1 truncate max-w-xs" title="{{ $inc->descripcion }}">{{ $inc->descripcion }}</span>
                                            <span class="block text-[10px] text-slate-400 mt-1">Reportó: {{ $inc->docenteReporta->usuario->nombre_completo }} ({{ $inc->fecha_incidencia->format('d/m/Y') }})</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest
                                                {{ $inc->estado === 'Reportada' ? 'bg-rose-50 text-rose-600 border border-rose-200' : '' }}
                                                {{ $inc->estado === 'En Revisión' ? 'bg-amber-50 text-amber-600 border border-amber-200' : '' }}
                                                {{ $inc->estado === 'Citación a Padres' ? 'bg-blue-50 text-blue-600 border border-blue-200' : '' }}
                                                {{ $inc->estado === 'Cerrada' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : '' }}
                                            ">
                                                {{ $inc->estado }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button @click="$dispatch('abrir-modal-gestion', { 
                                                id: '{{ $inc->id }}', 
                                                estado: '{{ $inc->estado }}', 
                                                fecha: '{{ $inc->fecha_citacion_padres ? $inc->fecha_citacion_padres->format('Y-m-d\TH:i') : '' }}', 
                                                resolucion: '{{ addslashes(str_replace(["\r", "\n"], ['\r', '\n'], $inc->resolucion_final ?? '')) }}' 
                                            })" class="px-4 py-2 bg-[#e6ac27] hover:bg-amber-500 text-white text-xs font-black rounded-xl shadow-sm transition-transform transform hover:-translate-y-0.5">
                                                Gestionar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL DE GESTIÓN (Alpine.js) -->
    <div x-data="{ open: false, id: '', estado: '', fecha: '', resolucion: '' }" 
         @abrir-modal-gestion.window="open = true; id = $event.detail.id; estado = $event.detail.estado; fecha = $event.detail.fecha; resolucion = $event.detail.resolucion;" 
         x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open" x-transition.scale class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-[#3d2c1d]">Actualizar Caso</h3>
                <button @click="open = false" class="text-slate-400 hover:text-rose-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form x-bind:action="'{{ url('academico/disciplina') }}/' + id" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')
                
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Estado del Caso</label>
                    <select name="estado" x-model="estado" class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold text-[#3d2c1d] shadow-sm cursor-pointer">
                        <option value="Reportada">Reportada</option>
                        <option value="En Revisión">En Revisión (Investigando)</option>
                        <option value="Citación a Padres">Citación a Padres</option>
                        <option value="Cerrada">Cerrada / Resuelta</option>
                    </select>
                </div>
                
                <div x-show="estado === 'Citación a Padres'">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Fecha y Hora de Cita</label>
                    <input type="datetime-local" name="fecha_citacion_padres" x-model="fecha" class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm">
                </div>

                <div x-show="estado === 'Cerrada' || estado === 'Citación a Padres'">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Resolución / Acuerdos</label>
                    <textarea name="resolucion_final" x-model="resolucion" rows="3" placeholder="Detalle las medidas tomadas o acuerdos con los padres..." class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm"></textarea>
                </div>

                <button type="submit" class="w-full mt-2 bg-[#3d2c1d] hover:bg-slate-800 text-white font-black py-3 rounded-xl shadow-md transition-all transform hover:-translate-y-0.5">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>

    <!-- Script de SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 3500, icon: 'success', title: '{{ session("success") }}', customClass: { popup: 'rounded-2xl shadow-lg border border-slate-100' } });
            @endif
        });
    </script>
</x-app-layout>