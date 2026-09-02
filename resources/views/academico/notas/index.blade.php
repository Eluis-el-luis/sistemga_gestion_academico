<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Centro de Calificaciones
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if($modoSupervision)
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm mb-6">
                    <form method="GET" action="{{ route('academico.notas.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                        
                        <div class="md:col-span-5">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Paso 1: Seleccione Grado</label>
                            <select name="grado_id" id="filtro_grado" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 text-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] font-medium text-slate-700">
                                @foreach ($grados as $grado)
                                    <option value="{{ $grado->id }}" {{ $grado->id == $gradoSeleccionadoId ? 'selected' : '' }}>
                                        {{ $grado->modalidad->nombre ?? 'N/A' }} - {{ $grado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-5">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Paso 2: Seleccione Aula</label>
                            <select name="aula_id" id="filtro_aula" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 text-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] font-medium text-slate-700">
                                <option value="">Seleccione una sección...</option>
                                @foreach ($aulas as $aula)
                                    <option value="{{ $aula->id }}" data-grado="{{ $aula->grado_id }}" {{ $aulaSeleccionadaId == $aula->id ? 'selected' : '' }}>
                                        Sección "{{ $aula->nombre }}"
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2 flex gap-2">
                            <button type="submit" class="w-full py-3 bg-[#3d2c1d] text-white rounded-xl hover:bg-slate-800 text-sm font-black shadow-sm transition-all transform hover:-translate-y-0.5">Filtrar</button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                @forelse($asignaciones as $asignacion_item)
                    <!-- AQUÍ ESTÁ LA RUTA CORRECTA A LA PLANILLA -->
                    <a href="{{ route('academico.notas.create', $asignacion_item->id) }}" class="group bg-white rounded-3xl border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all relative overflow-hidden flex flex-col justify-between">
                        <div class="absolute top-0 right-0 w-28 h-full bg-gradient-to-l from-[#e6ac27]/5 to-transparent pointer-events-none"></div>
                        
                        <div class="relative z-10">
                            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 text-[10px] font-black uppercase tracking-widest rounded-lg mb-3 border border-slate-200">
                                {{ $asignacion_item->aula->grado->nombre ?? '' }} - {{ $asignacion_item->aula->nombre ?? '' }}
                            </span>
                            <h4 class="text-xl font-black text-[#3d2c1d] group-hover:text-[#e6ac27] transition-colors leading-tight">
                                {{ $asignacion_item->asignatura->nombre ?? '' }}
                            </h4>
                            @if($modoSupervision)
                                <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-widest flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    {{ $asignacion_item->docente->usuario->nombre_completo ?? 'SIN DOCENTE' }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-slate-100 relative z-10 flex items-center justify-between text-sm font-black text-[#e6ac27]">
                            <span>Abrir Planilla</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full bg-white p-12 text-center rounded-3xl border border-slate-200 shadow-sm flex flex-col items-center justify-center">
                        <div class="w-14 h-14 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100 text-slate-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <p class="text-lg font-black text-[#3d2c1d]">No hay asignaturas disponibles</p>
                        <p class="text-sm font-medium text-slate-400 mt-1">Esta aula no tiene asignaturas vinculadas o el docente seleccionado no posee carga horaria actual.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if($modoSupervision)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const gradoSelect = document.getElementById('filtro_grado');
                const aulaSelect = document.getElementById('filtro_aula');

                function actualizarAulas(forzarReseteo = false) {
                    const gradoIdSeleccionado = gradoSelect.value;
                    let primeraOpcionValida = null;
                    let seleccionActualValida = false;

                    Array.from(aulaSelect.options).forEach(opcion => {
                        if (opcion.value === "") return; 
                        const gradoDelAula = opcion.getAttribute('data-grado');

                        if (gradoIdSeleccionado === "" || gradoDelAula === gradoIdSeleccionado) {
                            opcion.style.display = '';
                            opcion.hidden = false;
                            
                            if (!primeraOpcionValida) {
                                primeraOpcionValida = opcion;
                            }

                            if (opcion.selected) {
                                seleccionActualValida = true;
                            }
                        } else {
                            opcion.style.display = 'none';
                            opcion.hidden = true;
                        }
                    });

                    if (forzarReseteo || !seleccionActualValida) {
                        if (primeraOpcionValida) {
                            primeraOpcionValida.selected = true; 
                        } else {
                            aulaSelect.value = ""; 
                        }
                    }
                }

                if(gradoSelect && aulaSelect) {
                    gradoSelect.addEventListener('change', function() {
                        actualizarAulas(true); 
                    });
                    
                    actualizarAulas(false); 
                }
            });
        </script>
    @endif
</x-app-layout>