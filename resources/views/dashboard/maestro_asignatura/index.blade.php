<x-app-layout>
    <div class="py-8 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-dashboard.banner>
                Revisa tu agenda interactiva, pasa asistencia y gestiona las calificaciones de tus secciones asignadas.
            </x-dashboard.banner>

            @include('components.dashboard.asignatura-stats')

            <div class="mt-8 mb-4 flex items-center justify-between">
                <h3 class="text-xl font-black text-[#3d2c1d]">Mi Agenda Semanal</h3>
                <p class="text-xs font-bold text-slate-400">Haz clic en una clase para gestionarla</p>
            </div>

            <!-- Calendario Semanal Interactivo -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-10">
                @foreach($diasSemana as $dia)
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full">
                        <div class="bg-[#FFFDF5] text-center py-3 border-b border-[#e6ac27]/20">
                            <h4 class="font-black text-[#3d2c1d] uppercase tracking-widest text-sm">{{ $dia }}</h4>
                        </div>
                        <div class="p-4 flex-1 flex flex-col gap-3 bg-slate-50/30">
                            @if(isset($horarios[$dia]) && $horarios[$dia]->count() > 0)
                                @foreach($horarios[$dia] as $item)
                                    @php
                                        $modalidad = strtolower($item->aulaAsignaturaDocente->aula->modalidad->nombre ?? '');
                                        $estilosCaja = match(true) {
                                            str_contains($modalidad, 'preescolar') => 'bg-amber-50 border-amber-200 text-amber-900',
                                            str_contains($modalidad, 'primaria')   => 'bg-blue-50 border-blue-200 text-blue-900',
                                            str_contains($modalidad, 'secundaria') => 'bg-emerald-50 border-emerald-200 text-emerald-900',
                                            default => 'bg-white border-slate-200 text-slate-800'
                                        };
                                        $horaTexto = \Carbon\Carbon::parse($item->bloqueHorario->hora_inicio)->format('h:i') . ' - ' . \Carbon\Carbon::parse($item->bloqueHorario->hora_fin)->format('h:i A');
                                    @endphp
                                    <div x-data x-on:click="$dispatch('abrir-modal-decision-clase', { id: '{{ $item->aulaAsignaturaDocente->id }}', asignatura: '{{ addslashes($item->aulaAsignaturaDocente->asignatura->nombre) }}', aula: '{{ addslashes($item->aulaAsignaturaDocente->aula->grado->nombre . ' - ' . $item->aulaAsignaturaDocente->aula->nombre) }}', hora: '{{ $horaTexto }}' })" 
                                         class="border rounded-2xl p-3 shadow-sm hover:-translate-y-1 cursor-pointer transition-transform {{ $estilosCaja }}">
                                        <h5 class="font-black text-sm leading-tight mb-1">{{ $item->aulaAsignaturaDocente->asignatura->nombre }}</h5>
                                        <p class="text-[11px] font-bold opacity-80 uppercase">{{ $item->aulaAsignaturaDocente->aula->grado->nombre }}</p>
                                    </div>
                                @endforeach
                            @else
                                <div class="h-full flex flex-col items-center justify-center text-center opacity-50 py-4">
                                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Libre</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- MODAL: DECISIÓN CLASE ALPINE -->
        <div x-data="{ open: false, asignacionId: '', asignaturaInfo: '', aulaInfo: '', horaInfo: '' }" 
             @abrir-modal-decision-clase.window="open = true; asignacionId = $event.detail.id; asignaturaInfo = $event.detail.asignatura; aulaInfo = $event.detail.aula; horaInfo = $event.detail.hora;" 
             x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="open" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
            <div x-show="open" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20 flex items-center gap-3">
                    <div>
                        <h2 class="text-lg font-black text-[#3d2c1d] leading-tight" x-text="asignaturaInfo"></h2>
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-0.5" x-text="aulaInfo + ' | ' + horaInfo"></p>
                    </div>
                </div>
                <div class="p-8 bg-white grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a x-bind:href="'{{ url('academico/asistencia/asignatura') }}/' + asignacionId + '/create'" class="flex flex-col items-center p-5 rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-700 hover:bg-emerald-500 hover:text-white transition-all shadow-sm">
                        <span class="font-black uppercase tracking-widest text-[11px] text-center">Pasar<br>Asistencia</span>
                    </a>
                    <a x-bind:href="'{{ url('academico/notas/actividades') }}/' + asignacionId" class="flex flex-col items-center p-5 rounded-2xl border border-blue-100 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                        <span class="font-black uppercase tracking-widest text-[11px] text-center">Gestionar<br>Notas</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>