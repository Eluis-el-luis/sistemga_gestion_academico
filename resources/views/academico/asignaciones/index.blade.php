{{-- resources/views/academico/asignaciones/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Asignación de Maestros</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Asigna docentes en bloque por materia/modalidad o revisa cada sección.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden"
                 x-data="{
                     asignaturaId: '',
                     modalidadFiltro: 'todas',
                     aulasSeleccionadas: [],
                     malla: {{ \Illuminate\Support\Js::from($mallaPorAsignatura ?? []) }},
                     aulasInfo: {{ \Illuminate\Support\Js::from(($todasAulasActivas ?? collect())->map(fn($a) => [
                         'id' => $a->id,
                         'grado_id' => $a->grado_id,
                         'modalidad_id' => $a->modalidad_id,
                     ])) }},
                     alCambiarAsignatura() {
                         if (!this.asignaturaId) { this.aulasSeleccionadas = []; return; }
                         const gradosConMateria = this.malla[this.asignaturaId] || [];
                         this.aulasSeleccionadas = this.aulasInfo
                             .filter(a => gradosConMateria.includes(a.grado_id) && (this.modalidadFiltro === 'todas' || a.modalidad_id == this.modalidadFiltro))
                             .map(a => a.id);
                     },
                     seleccionarPorModalidad(idModalidad) {
                         this.modalidadFiltro = idModalidad;
                         this.aulasSeleccionadas = this.aulasInfo
                             .filter(a => idModalidad === 'todas' || a.modalidad_id == idModalidad)
                             .map(a => a.id);
                     }
                 }">
                <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 px-6 sm:px-8 py-4 border-b border-stone-200/80 dark:border-neutral-800 flex flex-wrap justify-between items-center gap-3">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Asignación Rápida por Materia y Nivel</h3>
                    <span class="px-3 py-1 bg-amber-50 dark:bg-neutral-900 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30 rounded-xl text-xs font-semibold" x-text="aulasSeleccionadas.length + ' sección(es) marcada(s)'"></span>
                </div>

                <form action="{{ route('academico.asignaciones.masivo') }}" method="POST" class="p-6 sm:p-8 space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">1. Asignatura <span class="text-rose-500">*</span></label>
                            <select name="asignatura_id" x-model="asignaturaId" @change="alCambiarAsignatura()" required class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                                <option value="">Elegir materia...</option>
                                @foreach ($asignaturas ?? [] as $asig)
                                    <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">2. Docente Titular <span class="text-rose-500">*</span></label>
                            <select name="docente_id" required class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                                <option value="">Elegir profesor...</option>
                                @foreach ($docentes ?? [] as $docente)
                                    <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario?->nombre_completo ?? 'Sin Nombre' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                            <label class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">3. Secciones a asignar <span class="text-rose-500">*</span></label>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="seleccionarPorModalidad('todas')" class="px-3 py-1 rounded-lg text-xs font-semibold border border-stone-300 dark:border-neutral-700 cursor-pointer">Todas</button>
                                @foreach ($modalidades ?? [] as $mod)
                                    <button type="button" @click="seleccionarPorModalidad('{{ $mod->id }}')" class="px-3 py-1 rounded-lg text-xs font-semibold border border-stone-300 dark:border-neutral-700 cursor-pointer">{{ $mod->nombre }}</button>
                                @endforeach
                                <button type="button" @click="aulasSeleccionadas = []" class="px-3 py-1 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">Desmarcar</button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2.5 p-4 bg-stone-50 dark:bg-neutral-800/60 rounded-2xl border border-stone-200 dark:border-neutral-700 max-h-60 overflow-y-auto">
                            @foreach ($todasAulasActivas ?? [] as $aulaItem)
                                <label x-show="modalidadFiltro === 'todas' || modalidadFiltro == '{{ $aulaItem->modalidad_id }}'" class="flex items-center gap-2 p-2.5 rounded-xl bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-700 cursor-pointer hover:border-[#e6ac27] text-xs">
                                    <input type="checkbox" name="aulas_ids[]" value="{{ $aulaItem->id }}" x-model="aulasSeleccionadas" class="rounded border-stone-300 text-[#e6ac27] focus:ring-[#e6ac27] cursor-pointer">
                                    <span class="font-semibold text-neutral-800 dark:text-neutral-100 truncate">{{ $aulaItem->grado?->nombre }} "{{ $aulaItem->nombre }}"</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 font-bold text-xs uppercase tracking-wider rounded-xl transition-colors cursor-pointer">
                            Aplicar Asignación
                        </button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @forelse ($aulas as $aula)
                    <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-5 flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider block mb-1">{{ $aula->anioEscolar?->nombre ?? 'N/A' }} · {{ $aula->modalidad?->nombre ?? 'N/A' }}</span>
                            <h3 class="font-bold text-xl text-neutral-900 dark:text-white">{{ $aula->grado?->nombre }} <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $aula->nombre }}</span></h3>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2">
                                Guía: {{ $aula->docenteGuia?->usuario?->nombre_completo ?? 'Sin asignar' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 pt-4 mt-4 border-t border-stone-100 dark:border-neutral-800">
                            <a href="{{ route('academico.asignaciones.show', $aula->id) }}" class="flex-1 text-center py-2 px-3 bg-[#FFFDF8] dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30 hover:bg-[#e6ac27] hover:text-neutral-950 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                                Materias
                            </a>
                            <a href="{{ route('academico.aulas.horarios.index', $aula->id) }}" class="py-2 px-3 bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-200 dark:hover:bg-neutral-700 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                                Horario
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white dark:bg-neutral-900 p-10 text-center rounded-3xl border border-stone-200 dark:border-neutral-800 text-neutral-500">
                        No hay aulas aperturadas.
                    </div>
                @endforelse
            </div>

            @if ($aulas->hasPages())
                <div>{{ $aulas->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>