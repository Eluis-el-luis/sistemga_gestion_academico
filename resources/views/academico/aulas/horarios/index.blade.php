{{-- resources/views/academico/aulas/horarios/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.gestor-horarios.index') }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al Gestor de Horarios">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                        Gestor de Horarios: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $aula->grado?->nombre }} - {{ $aula->nombre }}</span>
                    </h2>
                    <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mt-0.5">
                        Asigna docentes y programa bloques semanales del aula.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center px-3.5 py-2 bg-stone-50 dark:bg-neutral-800 border border-stone-200 dark:border-neutral-700 rounded-xl font-semibold text-xs uppercase tracking-wider text-neutral-700 dark:text-neutral-200">
                    Turno: {{ ucfirst((string) $aula->turno) }}
                </span>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold text-xs uppercase tracking-wider rounded-xl transition-colors print:hidden cursor-pointer">
                    Imprimir / PDF
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            @page { size: letter landscape; margin: 10mm; }
        }
    </style>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @can('horarios.gestionar')
                <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                        <div>
                            <h3 class="font-bold text-sm text-neutral-900 dark:text-white uppercase tracking-wider">Carga Horaria y Docentes del Aula</h3>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Asigna o cambia el docente de cada materia antes de programar bloques.</p>
                        </div>

                        @can('update', $aula)
                            <button type="button" @click="$dispatch('open-modal', 'modal-agregar-materia')" class="inline-flex items-center px-4 py-2 bg-[#FFFDF8] dark:bg-neutral-800 border border-[#e6ac27]/40 dark:border-neutral-700 rounded-xl text-xs font-bold uppercase tracking-wider text-neutral-900 dark:text-neutral-100 hover:bg-[#e6ac27] hover:text-neutral-950 transition-colors cursor-pointer">
                                + Materia Extraordinaria
                            </button>
                        @endcan
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach ($asignaciones as $asignacion)
                            @php
                                $porcentaje = $asignacion->horas_semanales > 0 ? ($asignacion->horas_programadas / $asignacion->horas_semanales) * 100 : 0;
                                $completada = $asignacion->horas_restantes <= 0;
                                $sinDocente = !$asignacion->docente_id;
                            @endphp
                            <div class="bg-stone-50 dark:bg-neutral-800/60 border {{ $sinDocente ? 'border-amber-300 dark:border-amber-700' : 'border-stone-200 dark:border-neutral-700/70' }} rounded-2xl p-4 flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start gap-2 mb-1">
                                        <span class="text-xs font-bold {{ $completada ? 'text-emerald-700 dark:text-emerald-400' : 'text-neutral-900 dark:text-white' }} truncate" title="{{ $asignacion->asignatura?->nombre }}">
                                            {{ $asignacion->asignatura?->nombre }}
                                        </span>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <span class="text-[11px] font-semibold {{ $completada ? 'text-emerald-600 dark:text-emerald-400' : 'text-neutral-500 dark:text-neutral-400' }}">
                                                {{ $asignacion->horas_programadas }}/{{ $asignacion->horas_semanales }}h
                                            </span>
                                            @can('update', $aula)
                                                <form action="{{ route('academico.aulas.asignaturas.destroy', [$aula->id, $asignacion->id]) }}" method="POST" class="inline form-eliminar">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-neutral-400 hover:text-rose-600 p-0.5 cursor-pointer" title="Quitar asignatura">x</button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        @click="$dispatch('abrir-modal-profesor', {
                                            url: @js(route('academico.aulas.asignaturas.update', [$aula->id, $asignacion->id])),
                                            materia: @js($asignacion->asignatura?->nombre ?? ''),
                                            docenteId: '{{ $asignacion->docente_id ?? '' }}'
                                        })"
                                        class="w-full mt-1.5 mb-2.5 px-2.5 py-1.5 rounded-xl text-left flex items-center justify-between gap-2 text-xs font-semibold transition-colors border {{ $sinDocente ? 'bg-amber-50 dark:bg-neutral-900 text-amber-800 dark:text-amber-300 border-amber-300 dark:border-amber-700' : 'bg-white dark:bg-neutral-900 hover:border-[#e6ac27] text-neutral-700 dark:text-neutral-200 border-stone-200 dark:border-neutral-700' }} cursor-pointer"
                                    >
                                        <span class="truncate">
                                            {{ $sinDocente ? 'Asignar profesor...' : 'Prof. ' . \Illuminate\Support\Str::words($asignacion->docente?->usuario?->nombre_completo ?? 'Asignado', 2, '') }}
                                        </span>
                                    </button>
                                </div>

                                <div class="w-full bg-stone-200 dark:bg-neutral-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $completada ? 'bg-emerald-500' : ($sinDocente ? 'bg-amber-400' : 'bg-[#e6ac27]') }}" style="width: {{ min(100, $porcentaje) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 p-6 print:hidden" x-data="gestorHorario()">
                    <form action="{{ route('academico.aulas.horarios.store', $aula->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        @csrf
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Día <span class="text-rose-500">*</span></label>
                            <select name="dia_semana" x-model="diaSeleccionado" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer" required>
                                <option value="Lunes">Lunes</option>
                                <option value="Martes">Martes</option>
                                <option value="Miercoles">Miércoles</option>
                                <option value="Jueves">Jueves</option>
                                <option value="Viernes">Viernes</option>
                            </select>
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Materia / Docente <span class="text-rose-500">*</span></label>
                            <select name="aula_asignatura_docente_id" x-model="asignacionSeleccionada" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer" required>
                                <option value="">Seleccione materia con docente...</option>
                                @foreach ($asignaciones ?? [] as $asignacion)
                                    @php
                                        $completada = $asignacion->horas_restantes <= 0;
                                        $sinDocente = !$asignacion->docente_id;
                                    @endphp
                                    <option value="{{ $asignacion->id }}" {{ ($sinDocente || $completada) ? 'disabled' : '' }}>
                                        {{ $asignacion->asignatura?->nombre }}
                                        @if ($sinDocente)
                                            — (Asigne profesor arriba)
                                        @elseif ($completada)
                                            — (Horas completas)
                                        @else
                                            (Prof. {{ \Illuminate\Support\Str::words($asignacion->docente?->usuario?->nombre_completo ?? '', 2, '') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Bloque de Tiempo <span class="text-rose-500">*</span></label>
                            <select name="bloque_horario_id" x-model="bloqueSeleccionado" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer" required>
                                <option value="">Seleccione la hora...</option>
                                @foreach ($bloquesAsignables as $bloque)
                                    <option value="{{ $bloque->id }}" :disabled="isOcupado({{ $bloque->id }})">
                                        {{ $bloque->nombre }} ({{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 rounded-xl text-sm transition-colors h-[42px] cursor-pointer">
                                + Añadir
                            </button>
                        </div>
                    </form>
                </div>
            @endcan

            <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse text-sm">
                        <thead>
                            <tr class="bg-[#FFFDF8] dark:bg-neutral-800/70">
                                <th class="border border-stone-200 dark:border-neutral-800 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-600 dark:text-neutral-300 w-44">Hora</th>
                                @foreach ($dias as $dia)
                                    <th class="border border-stone-200 dark:border-neutral-800 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-neutral-900 dark:text-white">{{ $dia }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-neutral-800 dark:text-neutral-200">
                            @forelse ($matriz as $fila)
                                @php $bloque = $fila['bloque']; @endphp
                                <tr class="{{ $bloque->es_recreo ? 'bg-stone-50/80 dark:bg-neutral-800/40' : '' }}">
                                    <td class="border border-stone-200 dark:border-neutral-800 px-4 py-3 whitespace-nowrap align-top">
                                        <span class="block font-semibold text-xs text-neutral-900 dark:text-white">{{ $bloque->nombre }}</span>
                                        <span class="block text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                        </span>
                                    </td>

                                    @if ($bloque->es_recreo)
                                        <td colspan="{{ count($dias) }}" class="border border-stone-200 dark:border-neutral-800 px-4 py-3 text-center">
                                            <span class="text-xs font-semibold uppercase tracking-widest text-neutral-400">{{ $bloque->nombre }}</span>
                                        </td>
                                    @else
                                        @foreach ($dias as $dia)
                                            @php $horario = $fila['dias'][$dia] ?? null; @endphp
                                            <td class="border border-stone-200 dark:border-neutral-800 px-3 py-3 text-center align-middle">
                                                @if ($horario)
                                                    <div class="group relative">
                                                        <span class="block font-semibold text-xs text-neutral-900 dark:text-white">{{ $horario->aulaAsignaturaDocente?->asignatura?->nombre }}</span>
                                                        <span class="block text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                                            Prof. {{ $horario->aulaAsignaturaDocente?->docente?->usuario ? \Illuminate\Support\Str::words($horario->aulaAsignaturaDocente->docente->usuario->nombre_completo, 2, '') : '—' }}
                                                        </span>
                                                        @can('horarios.gestionar')
                                                            <form action="{{ route('academico.aulas.horarios.destroy', [$aula->id, $horario->id]) }}" method="POST" class="mt-1.5 print:hidden form-eliminar">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">Quitar</button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                @else
                                                    <span class="text-xs text-neutral-400">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($dias) + 1 }}" class="border border-stone-200 dark:border-neutral-800 px-4 py-10 text-center text-neutral-500 dark:text-neutral-400">
                                        No hay bloques de horario definidos para esta aula.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @php $docentesOrdenados = collect($todosDocentes ?? [])->sortBy('usuario.nombre_completo'); @endphp

    <x-modal name="modal-agregar-materia" focusable maxWidth="md">
        <form method="post" action="{{ route('academico.aulas.asignaturas.store', $aula->id) }}">
            @csrf
            <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 px-6 py-4 border-b border-stone-200/80 dark:border-neutral-800">
                <h2 class="text-base font-bold text-neutral-900 dark:text-white">Materia Extraordinaria</h2>
            </div>
            <div class="p-6 space-y-4 text-sm">
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Asignatura <span class="text-rose-500">*</span></label>
                    <select name="asignatura_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm cursor-pointer" required>
                        <option value="">Seleccione una asignatura...</option>
                        @foreach ($todasAsignaturas ?? [] as $asig)
                            <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Docente Titular (Opcional)</label>
                    <select name="docente_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm cursor-pointer">
                        <option value="">Asignar después...</option>
                        @foreach ($docentesOrdenados as $docente)
                            <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario?->nombre_completo ?? 'Sin Nombre' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Horas Semanales <span class="text-rose-500">*</span></label>
                    <input type="number" name="horas_semanales" value="2" min="1" max="40" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-bold" required>
                </div>
            </div>
            <div class="bg-stone-50 dark:bg-neutral-800/70 px-6 py-4 flex justify-end gap-3 border-t border-stone-100 dark:border-neutral-800">
                <button type="button" @click="$dispatch('close')" class="text-xs font-semibold text-neutral-500 cursor-pointer">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl font-bold text-xs uppercase cursor-pointer">Guardar</button>
            </div>
        </form>
    </x-modal>

    <div x-data="{ urlAction: '', nombreMateria: '', docenteActual: '' }" @abrir-modal-profesor.window="urlAction = $event.detail.url; nombreMateria = $event.detail.materia; docenteActual = $event.detail.docenteId; $dispatch('open-modal', 'modal-asignar-profesor')">
        <x-modal name="modal-asignar-profesor" focusable maxWidth="md">
            <form method="post" x-bind:action="urlAction">
                @csrf
                @method('PUT')
                <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 px-6 py-4 border-b border-stone-200/80 dark:border-neutral-800">
                    <h2 class="text-base font-bold text-neutral-900 dark:text-white">Asignar o Cambiar Docente</h2>
                </div>
                <div class="p-6 space-y-4 text-sm">
                    <p class="text-xs text-neutral-600 dark:text-neutral-300 bg-stone-50 dark:bg-neutral-800 p-3.5 rounded-xl border border-stone-200 dark:border-neutral-700">
                        Profesor para <strong class="font-bold text-[#c99116] dark:text-[#e6ac27]" x-text="nombreMateria"></strong> en {{ $aula->grado?->nombre }} "{{ $aula->nombre }}".
                    </p>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Docente Disponible</label>
                        <select name="docente_id" x-model="docenteActual" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm cursor-pointer">
                            <option value="">-- Dejar sin profesor asignado --</option>
                            @foreach ($docentesOrdenados as $docente)
                                <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario?->nombre_completo ?? 'Sin Nombre' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="bg-stone-50 dark:bg-neutral-800/70 px-6 py-4 flex justify-end gap-3 border-t border-stone-100 dark:border-neutral-800">
                    <button type="button" @click="$dispatch('close')" class="text-xs font-semibold text-neutral-500 cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl font-bold text-xs uppercase cursor-pointer">Guardar Docente</button>
                </div>
            </form>
        </x-modal>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('gestorHorario', () => ({
                diaSeleccionado: 'Lunes',
                asignacionSeleccionada: '',
                bloqueSeleccionado: '',
                aulaOcupada: @json($aulaOcupada ?? []),
                docentesOcupados: @json($docentesOcupados ?? []),
                mapaDocentes: {
                    @foreach ($asignaciones as $asig)
                        @if ($asig->docente_id)
                            "{{ $asig->id }}": "{{ $asig->docente_id }}",
                        @endif
                    @endforeach
                },
                bloquesDisponibles: @json($bloquesAsignables->pluck('id')),
                init() {
                    this.$watch('diaSeleccionado', () => this.autoSelect());
                    this.$watch('asignacionSeleccionada', () => this.autoSelect());
                    this.autoSelect();
                },
                isOcupado(bloqueId) {
                    if (!this.diaSeleccionado) return false;
                    const diaKey = this.diaSeleccionado === 'Miércoles' ? 'Miercoles' : this.diaSeleccionado;
                    if (this.aulaOcupada[diaKey] && this.aulaOcupada[diaKey].includes(bloqueId)) return true;
                    if (this.asignacionSeleccionada) {
                        const docenteId = this.mapaDocentes[this.asignacionSeleccionada];
                        if (docenteId && this.docentesOcupados[docenteId] && this.docentesOcupados[docenteId][diaKey]) {
                            if (this.docentesOcupados[docenteId][diaKey].includes(bloqueId)) return true;
                        }
                    }
                    return false;
                },
                autoSelect() {
                    this.bloqueSeleccionado = '';
                    for (const id of this.bloquesDisponibles) {
                        if (!this.isOcupado(id)) {
                            this.bloqueSeleccionado = id;
                            break;
                        }
                    }
                }
            }));
        });
    </script>
</x-app-layout>