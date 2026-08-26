<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.visor.aulas') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Directorio">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path></svg>
                </a>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Horario: <span class="text-[#e6ac27]">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span>
                </h2>
            </div>
            
            <span class="inline-flex items-center px-4 py-2 bg-slate-100 border border-slate-200 rounded-xl font-black text-xs uppercase tracking-widest text-slate-600 shadow-sm">
                Turno: {{ ucfirst($aula->turno) }}
            </span>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-3xl p-6 border border-slate-200 overflow-x-auto">
            <div class="min-w-[900px]">
                <div class="grid grid-cols-5 gap-5 h-full">
                    @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'] as $dia)
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 flex flex-col">
                            <div class="bg-[#FFFDF5] py-4 text-center border-b border-[#e6ac27]/20 rounded-t-2xl">
                                <h4 class="font-black text-[#3d2c1d] uppercase text-sm tracking-widest">{{ $dia }}</h4>
                            </div>
                            
                            <div class="p-4 flex-grow flex flex-col gap-4 min-h-[450px]">
                                @forelse($calendario[$dia] ?? [] as $bloqueClase)
                                    <div class="bg-white border {{ $bloqueClase->bloque->es_recreo ? 'border-amber-200 bg-amber-50/50' : 'border-slate-200 shadow-sm' }} rounded-xl p-4 text-center hover:border-[#e6ac27] transition-colors">
                                        <p class="text-[10px] font-black {{ $bloqueClase->bloque->es_recreo ? 'text-amber-500' : 'text-[#e6ac27]' }} uppercase mb-1.5 tracking-widest">
                                            {{ $bloqueClase->bloque->nombre }}
                                        </p>
                                        <p class="text-xs font-bold text-slate-500 mb-3 font-mono">
                                            {{ \Carbon\Carbon::parse($bloqueClase->bloque->hora_inicio)->format('h:i') }} - {{ \Carbon\Carbon::parse($bloqueClase->bloque->hora_fin)->format('h:i A') }}
                                        </p>
                                        @if(!$bloqueClase->bloque->es_recreo)
                                            <div class="bg-slate-50 rounded-lg py-2 px-2 border border-slate-100">
                                                <p class="font-black text-sm text-[#3d2c1d] leading-tight mb-1">
                                                    {{ $bloqueClase->aulaAsignaturaDocente->asignatura->nombre ?? 'Materia' }}
                                                </p>
                                                <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">
                                                    Prof. {{ explode(' ', trim($bloqueClase->aulaAsignaturaDocente->docente->usuario->nombre_completo ?? ''))[0] ?? 'D' }}
                                                </p>
                                            </div>
                                        @else
                                            <div class="py-2">
                                                <p class="font-black text-sm text-amber-700 leading-tight uppercase tracking-widest">Receso</p>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="flex-grow flex flex-col items-center justify-center text-center py-12 opacity-50">
                                        <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Día Libre</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>