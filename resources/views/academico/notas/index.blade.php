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
                <div>
                    <h3 class="text-xl font-black text-[#3d2c1d]">Mis Libretas de Evaluación</h3>
                    <p class="text-stone-500 font-medium mt-1">Selecciona el aula y asignatura que deseas calificar hoy. El sistema guardará tus avances automáticamente.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
                @forelse($asignaciones as $asignacion)
                    <a href="{{ route('academico.notas.create', $asignacion->id) }}" class="group bg-white rounded-2xl border border-stone-200 p-6 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all relative overflow-hidden">
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