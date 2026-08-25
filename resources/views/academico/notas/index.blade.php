<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-[#3d2c1d] leading-tight">
            Centro de Calificaciones
        </h2>
    </x-slot>

    <div class="py-12 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white p-6 rounded-2xl border border-stone-200 shadow-sm flex flex-col md:flex-row items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/30 shrink-0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div class="flex-grow flex justify-between items-start">
                    <div>
                        @if($modoSupervision)
                            <h3 class="text-xl font-black text-[#3d2c1d]">Supervisión de Calificaciones</h3>
                            <p class="text-stone-500 font-medium mt-1">Monitorea las libretas de calificaciones de todas las asignaturas y aulas del centro educativo.</p>
                        @else
                            <h3 class="text-xl font-black text-[#3d2c1d]">Mis Libretas de Evaluación</h3>
                            <p class="text-stone-500 font-medium mt-1">Selecciona el aula y asignatura que deseas calificar hoy.</p>
                        @endif
                    </div>
                    
                    @if($modoSupervision)
                    <!-- BOTÓN EXCLUSIVO PARA SUBDIRECCIÓN -->
                    <a href="{{ route('academico.cortes.index') }}" class="hidden md:inline-flex items-center gap-2 bg-[#3d2c1d] hover:bg-stone-800 text-white font-bold py-2 px-4 rounded-xl shadow-sm transition-transform transform hover:scale-105 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Definir Criterios
                    </a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                @forelse($asignaciones as $asignacion)
                    <a href="{{ route('academico.notas.actividades.index', $asignacion->id) }}" class="group bg-white rounded-2xl border border-stone-200 p-6 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-full bg-gradient-to-l from-[#e6ac27]/5 to-transparent"></div>
                        
                        <div class="relative z-10 flex flex-col h-full justify-between">
                            <div>
                                <span class="inline-block px-3 py-1 bg-stone-100 text-stone-600 text-[10px] font-black uppercase tracking-widest rounded-lg mb-3 border border-stone-200">
                                    {{ $asignacion->aula->grado->nombre ?? '' }} - {{ $asignacion->aula->nombre ?? '' }}
                                </span>
                                <h4 class="text-2xl font-black text-[#3d2c1d] group-hover:text-[#e6ac27] transition-colors leading-tight">
                                    {{ $asignacion->asignatura->nombre ?? '' }}
                                </h4>
                            </div>
                            
                            <div class="mt-6 flex items-center text-sm font-bold text-[#e6ac27] group-hover:translate-x-2 transition-transform">
                                Abrir Planilla 
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full bg-white p-8 text-center rounded-2xl border border-stone-200 shadow-sm">
                        <p class="text-stone-500 font-bold text-lg">Aún no tienes asignaturas asignadas para calificar en este ciclo.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>