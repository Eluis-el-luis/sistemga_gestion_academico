<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.gestor-horarios.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Gestor de Horarios">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                        Gestor de Horarios: <span class="text-[#e6ac27]">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
                    </h2>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                        Asigna docentes a cada materia y distribuye los bloques semanales en un solo lugar
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-xl font-black text-xs uppercase tracking-widest text-slate-600 shadow-sm">
                    Turno: {{ ucfirst($aula->turno) }}
                </span>
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black text-xs uppercase tracking-widest rounded-xl shadow-md transition-all print:hidden">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir / PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium print:hidden">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium print:hidden">{{ session('error') }}</div>
        @endif

        @can('horarios.gestionar')
        <!-- BOLSA DE HORAS INTERACTIVA (ASIGNACIÓN DE DOCENTES INTEGRADA) -->
        <div class="bg-white shadow-sm rounded-3xl border border-slate-200 p-6 print:hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                <div>
                    <h3 class="font-black text-sm text-[#3d2c1d] uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Carga Horaria y Docentes del Aula
                    </h3>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Haz clic en el docente de cualquier asignatura para asignarlo o cambiarlo antes de programar sus horas.</p>
                </div>

                @can('update', $aula)
                    <button x-data x-on:click.prevent="$dispatch('open-modal', 'modal-agregar-materia')" 
                            class="inline-flex items-center px-4 py-2 bg-[#FFFDF5] border border-[#e6ac27]/30 rounded-xl shadow-sm text-xs font-black uppercase tracking-wider text-[#3d2c1d] hover:bg-[#e6ac27] hover:text-white transition-all self-start sm:self-auto">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Materia Extraordinaria
                    </button>
                @endcan
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($asignaciones as $asignacion)
                    @php
                        $porcentaje = $asignacion->horas_semanales > 0 ? ($asignacion->horas_programadas / $asignacion->horas_semanales) * 100 : 0;
                        $completada = $asignacion->horas_restantes <= 0;
                        $sinDocente = !$asignacion->docente_id;
                    @endphp
                    <div class="bg-slate-50 border {{ $sinDocente ? 'border-amber-300 bg-amber-50/30' : 'border-slate-200/80' }} rounded-2xl p-4 flex flex-col justify-between transition-all hover:shadow-sm">
                        <div>
                            <div class="flex justify-between items-start gap-2 mb-1">
                                <span class="text-xs font-black {{ $completada ? 'text-emerald-700' : 'text-[#3d2c1d]' }} truncate" title="{{ $asignacion->asignatura->nombre }}">
                                    {{ $asignacion->asignatura->nombre }}
                                </span>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="text-[11px] font-black {{ $completada ? 'text-emerald-600' : 'text-slate-500' }}">
                                        {{ $asignacion->horas_programadas }}/{{ $asignacion->horas_semanales }}h
                                    </span>
                                    @can('update', $aula)
                                        <form action="{{ route('academico.aulas.asignaturas.destroy', [$aula->id, $asignacion->id]) }}" method="POST" class="inline form-eliminar-extra">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-slate-300 hover:text-rose-500 transition-colors p-0.5" title="Quitar asignatura del aula">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>

                            <!-- Botón integrado para Asignar / Cambiar Docente -->
                            <button type="button"
                                    x-data
                                    x-on:click.prevent="$dispatch('abrir-modal-profesor', { 
                                        url: '{{ route('academico.aulas.asignaturas.update', [$aula->id, $asignacion->id]) }}', 
                                        materia: '{{ addslashes($asignacion->asignatura->nombre) }}',
                                        docenteId: '{{ $asignacion->docente_id ?? '' }}'
                                    })"
                                    class="w-full mt-1.5 mb-2.5 px-2.5 py-1.5 rounded-xl text-left flex items-center justify-between gap-2 text-[11px] font-bold transition-all border
                                        {{ $sinDocente 
                                            ? 'bg-amber-100/80 hover:bg-amber-200/70 text-amber-800 border-amber-300' 
                                            : 'bg-white hover:border-[#e6ac27] text-slate-600 border-slate-200/80' }}">
                                <span class="truncate flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 shrink-0 {{ $sinDocente ? 'text-amber-600' : 'text-[#e6ac27]' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    @if($sinDocente)
                                        <span>Asignar profesor...</span>
                                    @else
                                        <span class="truncate">Prof. {{ \Str::words($asignacion->docente->usuario->nombre_completo ?? 'Asignado', 2, '') }}</span>
                                    @endif
                                </span>
                                <svg class="w-3 h-3 shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        </div>

                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full transition-all duration-500 {{ $completada ? 'bg-emerald-500' : ($sinDocente ? 'bg-amber-400' : 'bg-[#e6ac27]') }}" style="width: {{ min(100, $porcentaje) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- FORMULARIO DE AÑADIR CLASE CON INTELIGENCIA ALPINE -->
        <div class="bg-white shadow-sm rounded-3xl border border-slate-200 p-6 print:hidden" x-data="gestorHorario()">
            <form action="{{ route('academico.aulas.horarios.store', $aula->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @csrf
                <div class="md:col-span-3">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Día de la Semana <span class="text-rose-500">*</span></label>
                    <select name="dia_semana" x-model="diaSeleccionado" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium focus:ring-[#e6ac27] focus:border-[#e6ac27]" required>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miercoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                    </select>
                </div>
                
                <div class="md:col-span-4">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Materia / Docente <span class="text-rose-500">*</span></label>
                    <select name="aula_asignatura_docente_id" x-model="asignacionSeleccionada" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium focus:ring-[#e6ac27] focus:border-[#e6ac27]" required>
                        <option value="">Seleccione materia con docente...</option>
                        @foreach($asignaciones ?? [] as $asignacion)
                            @php
                                $completada = $asignacion->horas_restantes <= 0;
                                $sinDocente = !$asignacion->docente_id;
                            @endphp
                            <option value="{{ $asignacion->id }}" {{ ($sinDocente || $completada) ? 'disabled' : '' }}>
                                {{ $asignacion->asignatura->nombre }}
                                @if($sinDocente)
                                    — (Asigne un profesor arriba primero)
                                @elseif($completada)
                                    — (Horas Completas)
                                @else
                                    (Prof. {{ \Str::words($asignacion->docente->usuario->nombre_completo ?? '', 2, '') }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-3">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Bloque de Tiempo <span class="text-rose-500">*</span></label>
                    <select name="bloque_horario_id" x-model="bloqueSeleccionado" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium focus:ring-[#e6ac27] focus:border-[#e6ac27]" required>
                        <option value="">Seleccione la hora...</option>
                        @foreach($bloquesAsignables as $bloque)
                            <option value="{{ $bloque->id }}" :disabled="isOcupado({{ $bloque->id }})">
                                {{ $bloque->nombre }} ({{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }})
                                <span x-text="isOcupado({{ $bloque->id }}) ? '— Ocupado' : ''"></span>
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-2.5 rounded-xl text-sm shadow-md transition-all h-[42px] flex items-center justify-center gap-2">
                        + Añadir
                    </button>
                </div>
            </form>
        </div>
        @endcan

        <!-- TABLA DE HORARIO (GENERICA, EXPORTABLE) -->
        <div class="bg-white shadow-sm rounded-3xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#FFFDF5]">
                            <th class="border border-slate-300 px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-slate-600 w-44">Hora</th>
                            @foreach($dias as $dia)
                                <th class="border border-slate-300 px-4 py-3 text-center text-[11px] font-black uppercase tracking-widest text-[#3d2c1d]">{{ $dia }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriz as $fila)
                            @php $bloque = $fila['bloque']; @endphp
                            <tr class="{{ $bloque->es_recreo ? 'bg-slate-50/80 border-y border-slate-200' : '' }}">
                                <td class="border border-slate-300 px-4 py-3 whitespace-nowrap align-top">
                                    <span class="block font-black text-xs {{ $bloque->es_recreo ? 'text-slate-500' : 'text-[#3d2c1d]' }}">{{ $bloque->nombre }}</span>
                                    <span class="block text-[10px] font-bold text-slate-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                    </span>
                                </td>

                                @if($bloque->es_recreo)
                                    <td colspan="{{ count($dias) }}" class="border border-slate-300 px-4 py-3 text-center bg-slate-50/50 select-none">
                                        <span class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">— {{ $bloque->nombre }} —</span>
                                    </td>
                                @else
                                    @foreach($dias as $dia)
                                        @php $horario = $fila['dias'][$dia] ?? null; @endphp
                                        @if($horario)
                                            <td class="border border-slate-300 px-4 py-3 text-center align-middle">
                                                <div class="group relative">
                                                    <span class="block font-black text-[#3d2c1d] text-xs">{{ $horario->aulaAsignaturaDocente->asignatura->nombre }}</span>
                                                    <span class="block text-[10px] font-bold text-slate-400 mt-0.5">
                                                        Prof. {{ $horario->aulaAsignaturaDocente->docente ? \Str::words($horario->aulaAsignaturaDocente->docente->usuario->nombre_completo, 2, '') : '—' }}
                                                    </span>
                                                    @can('horarios.gestionar')
                                                    <form action="{{ route('academico.aulas.horarios.destroy', [$aula->id, $horario->id]) }}" method="POST" class="absolute -top-3 -right-3 hidden group-hover:block print:hidden z-10 form-quitar-bloque">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-white bg-rose-500 hover:bg-rose-600 rounded-full p-1.5 shadow-md" title="Quitar clase">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        </button>
                                                    </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        @else
                                            <td class="border border-slate-300 px-4 py-3 text-center align-middle">
                                                <span class="text-[10px] font-bold text-slate-300">—</span>
                                            </td>
                                        @endif
                                    @endforeach
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($dias) + 1 }}" class="border border-slate-300 px-4 py-12 text-center text-stone-500 font-bold">
                                    No hay bloques de horario definidos para esta aula. Configúrelos en "Bloques de Horarios".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL 1: AGREGAR MATERIA EXTRAORDINARIA DESDE EL HORARIO -->
    <x-modal name="modal-agregar-materia" focusable maxWidth="md">
        <form method="post" action="{{ route('academico.aulas.asignaturas.store', $aula->id) }}">
            @csrf
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h2 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Materia Extraordinaria
                </h2>
            </div>
            
            <div class="p-8 space-y-5">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Asignatura <span class="text-red-500">*</span></label>
                    <select name="asignatura_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm font-medium transition-colors" required>
                        <option value="">Seleccione una asignatura...</option>
                        @foreach($todasAsignaturas ?? [] as $asig)
                            <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Docente Titular (Opcional)</label>
                    <select name="docente_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm font-medium transition-colors">
                        <option value="">Asignar después...</option>
                        @php $docentesOrdenados = collect($todosDocentes ?? [])->sortBy('usuario.nombre_completo'); @endphp
                        @foreach($docentesOrdenados as $docente)
                            <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin Nombre' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Horas Semanales <span class="text-red-500">*</span></label>
                    <input type="number" name="horas_semanales" value="2" min="1" max="40" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm font-black text-[#3d2c1d] transition-colors" required>
                </div>
            </div>

            <div class="bg-slate-50 px-8 py-5 flex justify-end gap-4 border-t border-slate-100 rounded-b-3xl">
                <button type="button" x-on:click="$dispatch('close')" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">Cancelar</button>
                <button type="submit" class="px-6 py-2.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md transition-all">Guardar</button>
            </div>
        </form>
    </x-modal>

    <!-- MODAL 2: ASIGNAR O CAMBIAR PROFESOR SIN SALIR DEL HORARIO -->
    <div x-data="{ urlAction: '', nombreMateria: '', docenteActual: '' }" 
         @abrir-modal-profesor.window="urlAction = $event.detail.url; nombreMateria = $event.detail.materia; docenteActual = $event.detail.docenteId; $dispatch('open-modal', 'modal-asignar-profesor')">
        <x-modal name="modal-asignar-profesor" focusable maxWidth="md">
            <form method="post" x-bind:action="urlAction">
                @csrf
                @method('PUT')
                <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                    <h2 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Asignar o Cambiar Docente
                    </h2>
                </div>

                <div class="p-8 space-y-6">
                    <p class="text-sm font-medium text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        Selecciona el profesor que impartirá <strong x-text="nombreMateria" class="font-black text-[#e6ac27]"></strong> en <strong>{{ $aula->grado->nombre }} "{{ $aula->nombre }}"</strong>.
                    </p>
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Docente Disponible</label>
                        <select name="docente_id" x-model="docenteActual" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm font-medium transition-colors">
                            <option value="">-- Dejar sin profesor asignado --</option>
                            @foreach($docentesOrdenados as $docente)
                                <option value="{{ $docente->id }}">{{ $docente->codigo_unico_persona }} - {{ $docente->usuario->nombre_completo ?? 'Sin Nombre' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="bg-slate-50 px-8 py-5 flex justify-end gap-4 border-t border-slate-100 rounded-b-3xl">
                    <button type="button" x-on:click="$dispatch('close')" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">Cancelar</button>
                    <button type="submit" class="px-6 py-2.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md transition-all">Guardar Docente</button>
                </div>
            </form>
        </x-modal>
    </div>

    <!-- SCRIPTS -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('gestorHorario', () => ({
                diaSeleccionado: 'Lunes',
                asignacionSeleccionada: '',
                bloqueSeleccionado: '',
                
                aulaOcupada: @json($aulaOcupada ?? []),
                docentesOcupados: @json($docentesOcupados ?? []),
                
                mapaDocentes: {
                    @foreach($asignaciones as $asig)
                        @if($asig->docente_id)
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
                    
                    let diaIndex = this.diaSeleccionado === 'Miércoles' ? 'Miercoles' : this.diaSeleccionado;
                    if (this.aulaOcupada[diaIndex] && this.aulaOcupada[diaIndex].includes(bloqueId)) {
                        return true;
                    }
                    
                    if (this.asignacionSeleccionada) {
                        let docenteId = this.mapaDocentes[this.asignacionSeleccionada];
                        if (docenteId && this.docentesOcupados[docenteId] && this.docentesOcupados[docenteId][diaIndex]) {
                            if (this.docentesOcupados[docenteId][diaIndex].includes(bloqueId)) {
                                return true;
                            }
                        }
                    }
                    return false;
                },

                autoSelect() {
                    this.bloqueSeleccionado = '';
                    for (let id of this.bloquesDisponibles) {
                        if (!this.isOcupado(id)) {
                            this.bloqueSeleccionado = id;
                            break;
                        }
                    }
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.form-quitar-bloque').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Quitar clase del horario?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, quitar', cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((r) => { if (r.isConfirmed) form.submit(); });
                });
            });

            document.querySelectorAll('.form-eliminar-extra').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Quitar materia del aula?',
                        text: 'Se eliminará esta asignatura y sus bloques horarios de esta sección.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((r) => { if (r.isConfirmed) form.submit(); });
                });
            });
        });
    </script>
</x-app-layout>