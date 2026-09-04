<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.aulas.show', $aula->id) }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Aula">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                </a>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Gestor de Horarios: <span class="text-[#e6ac27]">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
                </h2>
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
        <!-- BOLSA DE HORAS (solo gestión, no se imprime) -->
        <div class="bg-white shadow-sm rounded-3xl border border-slate-200 p-6 print:hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-black text-sm text-slate-700 uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Progreso de la Bolsa de Horas Semanales
                </h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($asignaciones as $asignacion)
                    @php
                        $porcentaje = $asignacion->horas_semanales > 0 ? ($asignacion->horas_programadas / $asignacion->horas_semanales) * 100 : 0;
                        $completada = $asignacion->horas_restantes <= 0;
                    @endphp
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4">
                        <div class="flex justify-between items-start mb-1.5">
                            <span class="text-xs font-black {{ $completada ? 'text-emerald-700' : 'text-[#3d2c1d]' }} truncate pr-1" title="{{ $asignacion->asignatura->nombre }}">{{ $asignacion->asignatura->nombre }}</span>
                            <span class="text-[11px] font-black {{ $completada ? 'text-emerald-600' : 'text-slate-500' }} shrink-0">{{ $asignacion->horas_programadas }}/{{ $asignacion->horas_semanales }}h</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden mt-2">
                            <div class="h-2 rounded-full transition-all duration-500 {{ $completada ? 'bg-emerald-500' : 'bg-[#e6ac27]' }}" style="width: {{ min(100, $porcentaje) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- FORMULARIO DE AÑADIR CLASE (solo gestión, no se imprime) -->
        <div class="bg-white shadow-sm rounded-3xl border border-slate-200 p-6 print:hidden">
            <form action="{{ route('academico.aulas.horarios.store', $aula->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @csrf
                <div class="md:col-span-3">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Día de la Semana <span class="text-rose-500">*</span></label>
                    <select name="dia_semana" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium" required>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miercoles">Miercoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Materia / Docente <span class="text-rose-500">*</span></label>
                    <select name="aula_asignatura_docente_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium" required>
                        <option value="">Seleccione materia...</option>
                        @foreach($asignaciones ?? [] as $asignacion)
                            @php
                                $completada = $asignacion->horas_restantes <= 0;
                                $sinDocente = !$asignacion->docente_id;
                            @endphp
                            <option value="{{ $asignacion->id }}" {{ ($sinDocente || $completada) ? 'disabled' : '' }}>
                                {{ $asignacion->asignatura->nombre }}
                                {{ $sinDocente ? '(Sin Docente)' : '' }}
                                {{ (!$sinDocente && $completada) ? '(Horas Completas)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Bloque de Tiempo <span class="text-rose-500">*</span></label>
                    <select name="bloque_horario_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium" required>
                        <option value="">Seleccione la hora...</option>
                        @foreach($bloquesOficiales as $bloque)
                            <option value="{{ $bloque->id }}" {{ $bloque->es_recreo ? 'disabled' : '' }}>
                                {{ $bloque->nombre }} ({{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }})
                                {{ $bloque->es_recreo ? '— RECESO —' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-2.5 rounded-xl text-sm shadow-md transition-all h-[42px]">+ Añadir</button>
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
                            <th class="border border-slate-300 px-4 py-3 text-left text-[11px] font-black uppercase tracking-widest text-slate-600">Hora</th>
                            @foreach($dias as $dia)
                                <th class="border border-slate-300 px-4 py-3 text-center text-[11px] font-black uppercase tracking-widest text-[#3d2c1d]">{{ $dia }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($matriz as $fila)
                            @php $bloque = $fila['bloque']; @endphp
                            <tr class="{{ $bloque->es_recreo ? 'bg-amber-50/60' : '' }}">
                                <!-- Celda de hora -->
                                <td class="border border-slate-300 px-4 py-3 whitespace-nowrap">
                                    <span class="block font-black text-xs {{ $bloque->es_recreo ? 'text-amber-700' : 'text-[#3d2c1d]' }}">{{ $bloque->nombre }}</span>
                                    <span class="block text-[10px] font-bold text-slate-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                    </span>
                                </td>

                                @foreach($dias as $dia)
                                    @php $horario = $fila['dias'][$dia] ?? null; @endphp
                                    @if($bloque->es_recreo)
                                        <td class="border border-slate-300 px-4 py-3 text-center">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-amber-600">Receso</span>
                                        </td>
                                    @elseif($horario)
                                        <td class="border border-slate-300 px-4 py-3 text-center">
                                            <div class="group relative">
                                                <span class="block font-black text-[#3d2c1d] text-xs">{{ $horario->aulaAsignaturaDocente->asignatura->nombre }}</span>
                                                <span class="block text-[10px] font-bold text-slate-400 mt-0.5">
                                                    Prof. {{ $horario->aulaAsignaturaDocente->docente ? \Str::words($horario->aulaAsignaturaDocente->docente->usuario->nombre_completo, 2, '') : '—' }}
                                                </span>
                                                @can('horarios.gestionar')
                                                <form action="{{ route('academico.aulas.horarios.destroy', [$aula->id, $horario->id]) }}" method="POST" class="absolute -top-2 -right-2 hidden group-hover:block print:hidden">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-white bg-rose-500 hover:bg-rose-600 rounded-full p-1 shadow-md" title="Quitar clase">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    @else
                                        <td class="border border-slate-300 px-4 py-3 text-center">
                                            <span class="text-[10px] font-bold text-slate-300">—</span>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="border border-slate-300 px-4 py-12 text-center text-stone-500 font-bold">
                                    No hay bloques de horario definidos para esta aula. Configúrelos en "Bloques de Horarios".
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[action*="horarios/"]').forEach(function (form) {
                if (form.method.toLowerCase() === 'post' && form.querySelector('input[name="_method"][value="DELETE"]')) {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        Swal.fire({
                            title: '¿Quitar clase del horario?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e11d48', cancelButtonColor: '#94a3b8',
                            confirmButtonText: 'Sí, quitar', cancelButtonText: 'Cancelar'
                        }).then((r) => { if (r.isConfirmed) form.submit(); });
                    });
                }
            });
        });
    </script>
</x-app-layout>