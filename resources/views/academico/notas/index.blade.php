<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <!-- Flecha de regreso -->
                <a href="{{ route('dashboard') }}" class="text-stone-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Centro de Calificaciones
                </h2>
            </div>
            
            @if($modoSupervision)
                <!-- Botón Responsivo para Definir Criterios (Subdirección) -->
                <a href="{{ route('academico.cortes.index') }}" class="inline-flex items-center gap-2 bg-[#3d2c1d] hover:bg-stone-800 text-white font-bold py-2.5 px-5 rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5 text-sm">
                    <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Definir Criterios Globales
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Banner Informativo -->
            <div class="bg-white p-6 md:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col md:flex-row items-start md:items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/30 shrink-0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div class="flex-grow">
                    @if($modoSupervision)
                        <h3 class="text-xl font-black text-[#3d2c1d]">Supervisión de Calificaciones</h3>
                        <p class="text-stone-500 font-medium mt-1">Monitorea y supervisa las libretas de calificaciones de todas las asignaturas y aulas habilitadas en el centro educativo.</p>
                    @else
                        <h3 class="text-xl font-black text-[#3d2c1d]">Mis Libretas de Evaluación</h3>
                        <p class="text-stone-500 font-medium mt-1">Selecciona el aula y la asignatura correspondiente para gestionar los acumulados y exámenes de tus estudiantes.</p>
                    @endif
                </div>
            </div>

            <!-- Listado de Asignaciones / Planillas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                @forelse($asignaciones as $asignacion)
                    <a href="{{ route('academico.notas.actividades.index', $asignacion->id) }}" class="group bg-white rounded-3xl border border-stone-200 p-6 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all relative overflow-hidden flex flex-col justify-between">
                        <div class="absolute top-0 right-0 w-28 h-full bg-gradient-to-l from-[#e6ac27]/5 to-transparent pointer-events-none"></div>
                        
                        <div class="relative z-10">
                            <span class="inline-block px-3 py-1 bg-stone-100 text-stone-600 text-[10px] font-black uppercase tracking-widest rounded-lg mb-3 border border-stone-200">
                                {{ $asignacion->aula->grado->nombre ?? '' }} - {{ $asignacion->aula->nombre ?? '' }}
                            </span>
                            <h4 class="text-xl font-black text-[#3d2c1d] group-hover:text-[#e6ac27] transition-colors leading-tight">
                                {{ $asignacion->asignatura->nombre ?? '' }}
                            </h4>
                        </div>
                        
                        <div class="mt-6 pt-4 border-t border-stone-100 relative z-10 flex items-center justify-between text-sm font-black text-[#e6ac27]">
                            <span>Abrir Planilla</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full bg-white p-12 text-center rounded-3xl border border-stone-200 shadow-sm flex flex-col items-center justify-center">
                        <div class="w-14 h-14 bg-stone-50 rounded-full flex items-center justify-center mb-4 border border-stone-100 text-stone-300">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        </div>
                        <p class="text-lg font-black text-[#3d2c1d]">No hay libretas disponibles</p>
                        <p class="text-sm font-medium text-stone-400 mt-1">Aún no tienes asignaturas o aulas vinculadas para calificar en este ciclo escolar.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>