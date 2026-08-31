@include('components.dashboard.asignatura-stats')

<div class="mt-8 mb-4 flex items-center justify-between">
    <h3 class="text-xl font-black text-[#3d2c1d]">Mi Agenda Semanal</h3>
    <p class="text-xs font-bold text-slate-400">Haz clic en una clase para gestionarla</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
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
                        <div x-on:click="$dispatch('abrir-modal-decision-clase', { id: '{{ $item->aulaAsignaturaDocente->id }}', asignatura: '{{ addslashes($item->aulaAsignaturaDocente->asignatura->nombre) }}', aula: '{{ addslashes($item->aulaAsignaturaDocente->aula->grado->nombre . ' - ' . $item->aulaAsignaturaDocente->aula->nombre) }}', hora: '{{ $horaTexto }}' })" class="border rounded-2xl p-3 shadow-sm hover:-translate-y-1 cursor-pointer {{ $estilosCaja }}">
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