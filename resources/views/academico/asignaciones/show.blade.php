{{-- resources/views/academico/asignaciones/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('academico.asignaciones.index') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">
                Carga Horaria: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $aula->grado?->nombre }} - {{ $aula->nombre }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{ openExtra: false, openDocente: false, urlAction: '', nombreMateria: '', docenteActual: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="p-6 border-b border-stone-100 dark:border-neutral-800 flex justify-between items-center gap-4">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Asignaturas y Docentes</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $aula->modalidad?->nombre }} · Turno {{ ucfirst($aula->turno) }}</p>
                    </div>
                    @can('update', $aula)
                        <button type="button" @click="openExtra = true" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-xs font-bold transition-colors cursor-pointer">
                            + Materia Extraordinaria
                        </button>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-4">Asignatura</th>
                                <th class="px-6 py-4 text-center">Horas/Sem.</th>
                                <th class="px-6 py-4">Docente</th>
                                @can('update', $aula)
                                    <th class="px-6 py-4 text-right">Acciones</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($asignaciones as $asignacion)
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40">
                                    <td class="px-6 py-4 font-semibold">{{ $asignacion->asignatura?->nombre }}</td>
                                    <td class="px-6 py-4 text-center font-semibold text-neutral-500">{{ number_format((float) $asignacion->horas_semanales, 0) }}h</td>
                                    <td class="px-6 py-4">{{ $asignacion->docente?->usuario?->nombre_completo ?? 'Sin asignar' }}</td>
                                    @can('update', $aula)
                                        <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                            <button type="button" @click="urlAction = @js(route('academico.aulas.asignaturas.update', [$aula->id, $asignacion->id])); nombreMateria = @js($asignacion->asignatura?->nombre ?? ''); docenteActual = '{{ $asignacion->docente_id ?? '' }}'; openDocente = true;" class="px-3 py-1.5 bg-amber-50 dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30 rounded-xl text-xs font-semibold cursor-pointer">
                                                Asignar
                                            </button>
                                            <form action="{{ route('academico.aulas.asignaturas.destroy', [$aula->id, $asignacion->id]) }}" method="POST" class="inline form-eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-semibold cursor-pointer">Quitar</button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-10 text-center text-neutral-500">Sin materias asignadas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="openExtra" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
                <div class="bg-white dark:bg-neutral-900 rounded-3xl max-w-md w-full border border-stone-200 dark:border-neutral-800 overflow-hidden">
                    <form method="POST" action="{{ route('academico.aulas.asignaturas.store', $aula->id) }}">
                        @csrf
                        <div class="p-6 space-y-4">
                            <h3 class="text-base font-bold text-neutral-900 dark:text-white">Agregar Materia Extraordinaria</h3>
                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1">Asignatura</label>
                                <select name="asignatura_id" required class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm cursor-pointer">
                                    <option value="">Seleccione...</option>
                                    @foreach ($todasAsignaturas ?? [] as $asig)
                                        <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1">Horas Semanales</label>
                                <input type="number" name="horas_semanales" value="2" min="1" max="40" required class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm">
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-stone-50 dark:bg-neutral-800/70 flex justify-end gap-3">
                            <button type="button" @click="openExtra = false" class="text-xs font-semibold text-neutral-500 cursor-pointer">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-[#e6ac27] text-neutral-950 rounded-xl text-xs font-bold cursor-pointer">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div x-show="openDocente" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
                <div class="bg-white dark:bg-neutral-900 rounded-3xl max-w-md w-full border border-stone-200 dark:border-neutral-800 overflow-hidden">
                    <form method="POST" x-bind:action="urlAction">
                        @csrf
                        @method('PUT')
                        <div class="p-6 space-y-4">
                            <h3 class="text-base font-bold text-neutral-900 dark:text-white">Asignar Docente a <span class="text-[#c99116]" x-text="nombreMateria"></span></h3>
                            <select name="docente_id" x-model="docenteActual" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm cursor-pointer">
                                <option value="">Sin docente asignado</option>
                                @foreach (($todosDocentes ?? collect())->sortBy('usuario.nombre_completo') as $docente)
                                    <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario?->nombre_completo ?? 'Sin Nombre' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="px-6 py-4 bg-stone-50 dark:bg-neutral-800/70 flex justify-end gap-3">
                            <button type="button" @click="openDocente = false" class="text-xs font-semibold text-neutral-500 cursor-pointer">Cancelar</button>
                            <button type="submit" class="px-4 py-2 bg-[#e6ac27] text-neutral-950 rounded-xl text-xs font-bold cursor-pointer">Confirmar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>