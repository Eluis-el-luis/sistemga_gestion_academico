<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
           <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Panel Principal">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                Directorio de Aulas (Consulta)
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse ($aulas as $aula)
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow relative group">
                        
                        <!-- Línea decorativa superior según modalidad -->
                        <div class="h-2 w-full
                            {{ str_contains(strtolower($aula->modalidad->nombre ?? ''), 'preescolar') ? 'bg-pink-400' : '' }}
                            {{ str_contains(strtolower($aula->modalidad->nombre ?? ''), 'primaria') ? 'bg-blue-400' : '' }}
                            {{ str_contains(strtolower($aula->modalidad->nombre ?? ''), 'secundaria') ? 'bg-emerald-400' : '' }}
                            {{ !str_contains(strtolower($aula->modalidad->nombre ?? ''), 'preescolar') && !str_contains(strtolower($aula->modalidad->nombre ?? ''), 'primaria') && !str_contains(strtolower($aula->modalidad->nombre ?? ''), 'secundaria') ? 'bg-slate-300' : '' }}
                        "></div>

                        <div class="p-6 flex-grow flex flex-col">
                            <!-- Cabecera del Locker -->
                            <div class="flex justify-between items-start mb-5">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-1">{{ $aula->anioEscolar->nombre ?? 'N/A' }} • {{ $aula->modalidad->nombre ?? 'N/A' }}</span>
                                    <h3 class="font-black text-2xl text-[#3d2c1d] leading-none">
                                        {{ $aula->grado->nombre ?? 'N/A' }} <span class="text-[#e6ac27]">{{ $aula->nombre }}</span>
                                    </h3>
                                </div>
                                <span class="bg-slate-50 border border-slate-200 text-slate-600 px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $aula->turno }}</span>
                            </div>

                            <!-- Info rápida (Cupo y Maestro Guía) -->
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

                            <!-- Botón de acción: Ir al horario de lectura -->
                            <div class="pt-4 border-t border-slate-100">
                                <a href="{{ route('academico.visor.aula.show', $aula->id) }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-[#FFFDF5] text-[#e6ac27] border border-[#e6ac27]/30 hover:bg-[#e6ac27] hover:text-white rounded-xl text-sm font-black transition-colors shadow-sm" title="Consultar Horario">
                                    Ver Horario Semanal
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 px-6 py-16 text-center text-slate-500">
                            <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <p class="text-lg font-black text-slate-600">No hay aulas aperturadas aún</p>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>