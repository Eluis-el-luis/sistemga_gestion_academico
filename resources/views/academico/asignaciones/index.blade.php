<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                        <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Asignación de Maestros
                    </h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                        Asigna un profesor a múltiples grados/aulas en bloque o gestiona cada sección individualmente
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm font-bold text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm font-bold text-sm">{{ session('error') }}</div>
            @endif

            <!-- NUEVO: ASIGNADOR MASIVO POR ASIGNATURA Y MODALIDAD (PRIMARIA / SECUNDARIA) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden"
                 x-data="{
                     asignaturaId: '',
                     modalidadFiltro: 'todas',
                     aulasSeleccionadas: [],
                     malla: {{ \Illuminate\Support\Js::from($mallaPorAsignatura ?? []) }},
                     aulasInfo: {{ \Illuminate\Support\Js::from(($todasAulasActivas ?? collect())->map(fn($a) => [
                         'id' => $a->id,
                         'grado_id' => $a->grado_id,
                         'modalidad_id' => $a->modalidad_id,
                         'modalidad_nombre' => strtolower($a->modalidad->nombre ?? ''),
                     ])) }},

                     alCambiarAsignatura() {
                         if (!this.asignaturaId) {
                             this.aulasSeleccionadas = [];
                             return;
                         }
                         let gradosConMateria = this.malla[this.asignaturaId] || [];
                         this.aulasSeleccionadas = this.aulasInfo
                             .filter(a => gradosConMateria.includes(a.grado_id) && (this.modalidadFiltro === 'todas' || a.modalidad_id == this.modalidadFiltro))
                             .map(a => a.id);
                     },

                     seleccionarPorModalidad(idModalidad) {
                         this.modalidadFiltro = idModalidad;
                         this.aulasSeleccionadas = this.aulasInfo
                             .filter(a => idModalidad === 'todas' || a.modalidad_id == idModalidad)
                             .map(a => a.id);
                     },

                     limpiarSeleccion() {
                         this.aulasSeleccionadas = [];
                     }
                 }">
                <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Asignación Rápida por Materia y Nivel
                        </h3>
                        <p class="text-xs font-medium text-slate-500 mt-0.5">Ideal para profesores que imparten una misma asignatura en toda Primaria, toda Secundaria o varios grados.</p>
                    </div>
                    <span class="px-3 py-1 bg-[#e6ac27]/10 text-[#3d2c1d] border border-[#e6ac27]/30 rounded-xl text-xs font-black uppercase tracking-widest self-start sm:self-auto"
                          x-text="aulasSeleccionadas.length + ' sección(es) marcada(s)'"></span>
                </div>

                <form action="{{ route('academico.asignaciones.masivo') }}" method="POST" class="p-8 space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Paso 1: Asignatura -->
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">1. Seleccione la Asignatura <span class="text-rose-500">*</span></label>
                            <select name="asignatura_id" x-model="asignaturaId" @change="alCambiarAsignatura()" required class="w-full border-slate-200 bg-slate-50/70 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold text-[#3d2c1d]">
                                <option value="">Elegir materia (ej. Matemática, TIC, Inglés)...</option>
                                @foreach($asignaturas ?? [] as $asig)
                                    <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Paso 2: Docente -->
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">2. Seleccione el Docente Titular <span class="text-rose-500">*</span></label>
                            <select name="docente_id" required class="w-full border-slate-200 bg-slate-50/70 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold text-[#3d2c1d]">
                                <option value="">Elegir profesor...</option>
                                @foreach($docentes ?? [] as $docente)
                                    <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin Nombre' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Paso 3: Segmentación por Nivel / Grados -->
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest">3. Seleccione los Grados / Aulas donde impartirá esta materia <span class="text-rose-500">*</span></label>
                            
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="seleccionarPorModalidad('todas')" class="px-3 py-1 rounded-lg text-[11px] font-black uppercase tracking-wider border transition-colors" :class="modalidadFiltro === 'todas' ? 'bg-[#3d2c1d] text-white border-[#3d2c1d]' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'">
                                    Todas las Aulas
                                </button>
                                @foreach($modalidades ?? [] as $mod)
                                    <button type="button" @click="seleccionarPorModalidad('{{ $mod->id }}')" class="px-3 py-1 rounded-lg text-[11px] font-black uppercase tracking-wider border transition-colors" :class="modalidadFiltro == '{{ $mod->id }}' ? 'bg-[#e6ac27] text-white border-[#e6ac27]' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'">
                                        Solo {{ $mod->nombre }}
                                    </button>
                                @endforeach
                                <button type="button" @click="limpiarSeleccion()" class="px-3 py-1 rounded-lg text-[11px] font-bold text-rose-500 hover:bg-rose-50 transition-colors">
                                    Desmarcar todo
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 p-4 bg-slate-50/70 rounded-2xl border border-slate-200/80 max-h-64 overflow-y-auto">
                            @foreach($todasAulasActivas ?? [] as $aulaItem)
                                <label x-show="modalidadFiltro === 'todas' || modalidadFiltro == '{{ $aulaItem->modalidad_id }}'"
                                       class="flex items-center gap-2.5 p-3 rounded-xl bg-white border border-slate-200 cursor-pointer hover:border-[#e6ac27] transition-all select-none shadow-sm">
                                    <input type="checkbox" name="aulas_ids[]" value="{{ $aulaItem->id }}" x-model="aulasSeleccionadas"
                                           class="rounded border-slate-300 text-[#e6ac27] focus:ring-[#e6ac27] w-4 h-4">
                                    <div class="leading-tight overflow-hidden">
                                        <span class="block text-xs font-black text-[#3d2c1d] truncate">{{ $aulaItem->grado->nombre ?? '' }} "{{ $aulaItem->nombre }}"</span>
                                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider truncate">{{ $aulaItem->modalidad->nombre ?? '' }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-[#3d2c1d] hover:bg-stone-800 text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md transition-all transform hover:-translate-y-0.5">
                            <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            Aplicar Asignación en las Secciones Marcadas
                        </button>
                    </div>
                </form>
            </div>

            <!-- DETALLE POR AULA INDIVIDUAL -->
            <div>
                <h3 class="font-black text-sm text-slate-400 uppercase tracking-widest mb-4 px-1">O gestionar por Aula Individualmente</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse ($aulas as $aula)
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow relative group">
                            
                            <div class="h-2 w-full
                                {{ str_contains(strtolower($aula->modalidad->nombre ?? ''), 'preescolar') ? 'bg-pink-400' : '' }}
                                {{ str_contains(strtolower($aula->modalidad->nombre ?? ''), 'primaria') ? 'bg-blue-400' : '' }}
                                {{ str_contains(strtolower($aula->modalidad->nombre ?? ''), 'secundaria') ? 'bg-emerald-400' : '' }}
                                {{ !str_contains(strtolower($aula->modalidad->nombre ?? ''), 'preescolar') && !str_contains(strtolower($aula->modalidad->nombre ?? ''), 'primaria') && !str_contains(strtolower($aula->modalidad->nombre ?? ''), 'secundaria') ? 'bg-slate-300' : '' }}
                            "></div>

                            <div class="p-6 flex-grow flex flex-col">
                                <div class="flex justify-between items-start mb-5">
                                    <div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-1">{{ $aula->anioEscolar->nombre ?? 'N/A' }} • {{ $aula->modalidad->nombre ?? 'N/A' }}</span>
                                        <h3 class="font-black text-2xl text-[#3d2c1d] leading-none">
                                            {{ $aula->grado->nombre ?? 'N/A' }} <span class="text-[#e6ac27]">{{ $aula->nombre }}</span>
                                        </h3>
                                    </div>
                                    <span class="bg-slate-50 border border-slate-200 text-slate-600 px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $aula->turno }}</span>
                                </div>

                                <div class="space-y-3 mb-6 flex-grow">
                                    <div class="flex items-center gap-2.5 text-sm">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <span class="font-bold text-slate-600">{{ $aula->cupo }} Alumnos máx.</span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2.5 text-sm">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        @if($aula->docenteGuia)
                                            <span class="font-bold text-slate-600 truncate" title="{{ $aula->docenteGuia->usuario->nombre_completo ?? 'Sin nombre' }}">
                                                Prof. {{ explode(' ', trim($aula->docenteGuia->usuario->nombre_completo ?? ''))[0] ?? 'D' }}
                                            </span>
                                        @else
                                            <span class="font-bold text-rose-500">Sin Docente Guía</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-4 border-t border-slate-100">
                                    <a href="{{ route('academico.asignaciones.show', $aula->id) }}" class="flex-grow inline-flex justify-center items-center px-3 py-2 bg-[#FFFDF5] text-[#e6ac27] border border-[#e6ac27]/30 hover:bg-[#e6ac27] hover:text-white rounded-xl text-xs font-black transition-colors shadow-sm" title="Ver lista de materias">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        Materias
                                    </a>
                                    <a href="{{ route('academico.aulas.horarios.index', $aula->id) }}" class="inline-flex justify-center items-center px-3 py-2 bg-slate-50 text-slate-600 border border-slate-200 hover:bg-[#3d2c1d] hover:text-white rounded-xl text-xs font-black transition-colors shadow-sm" title="Ir directo a Armar Horario">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full bg-white p-12 text-center rounded-3xl border border-slate-200 shadow-sm flex flex-col items-center justify-center">
                            <h3 class="text-lg font-black text-[#3d2c1d]">No hay aulas aperturadas aún</h3>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($aulas->hasPages())
                <div class="p-6 border-t border-slate-200 mt-6">
                    {{ $aulas->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>