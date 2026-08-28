<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('academico.visor.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Menú">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Directorio de Docentes
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse ($docentes as $docente)
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                        
                        <!-- Cabecera de la tarjeta con iniciales -->
                        <div class="bg-[#FFFDF5] p-6 border-b border-[#e6ac27]/20 flex flex-col items-center text-center relative">
                            <!-- Código del docente flotante -->
                            <span class="absolute top-3 right-4 text-[10px] font-black text-slate-400 uppercase tracking-widest bg-white px-2 py-1 rounded-md border border-slate-200 shadow-sm">
                                {{ $docente->codigo_unico_persona ?? 'N/A' }}
                            </span>

                            <div class="w-16 h-16 bg-white border border-slate-200 text-[#e6ac27] rounded-full flex items-center justify-center text-2xl font-black shadow-sm mb-4">
                                {{ substr(trim($docente->usuario->nombre_completo ?? 'D'), 0, 1) }}
                            </div>
                            <h3 class="font-black text-lg text-[#3d2c1d] leading-tight">
                                {{ $docente->usuario->nombre_completo ?? 'Profesor Sin Nombre' }}
                            </h3>
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-1">Personal Docente</p>
                        </div>

                        <!-- Botón de acción -->
                        <div class="p-6 flex-grow flex flex-col justify-end">
                            <a href="{{ route('academico.visor.docente.show', $docente->id) }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-slate-50 hover:bg-[#e6ac27] text-slate-600 hover:text-white border border-slate-200 hover:border-[#e6ac27] rounded-xl text-sm font-black transition-all transform hover:-translate-y-0.5 shadow-sm group" title="Consultar Horario Semanal">
                                Ver Horario Semanal
                                <svg class="w-4 h-4 ml-2 text-slate-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 px-6 py-16 text-center text-slate-500">
                            <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <p class="text-lg font-black text-slate-600">No hay docentes registrados</p>
                            <p class="text-sm font-medium mt-1">Registra personal docente para visualizar sus horarios.</p>
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>