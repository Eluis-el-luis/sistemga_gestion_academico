<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <!-- Botón de Regreso -->
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Panel Principal">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Visor de Horarios
                </h2>
            </div>

            <!-- Indicador de Contexto Temporal -->
            <span class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-xl font-black text-xs uppercase tracking-widest text-slate-600 shadow-sm">
                <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Año Escolar {{ date('Y') }}
            </span>
        </div>
    </x-slot>

    <div class="pb-12 pt-8 max-w-5xl mx-auto sm:px-6 lg:px-8">
        
        <div class="text-center mb-10">
            <h3 class="font-black text-3xl text-slate-800 tracking-tight mb-3">¿Qué horario deseas consultar?</h3>
            <p class="text-slate-500 font-medium">Selecciona una categoría para acceder a los horarios de clase en tiempo real.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- TARJETA 1: DOCENTES -->
            <a href="{{ route('academico.visor.docentes') }}" class="group block bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:border-[#e6ac27]/50 transition-all duration-300 transform hover:-translate-y-1">
                <div class="bg-gradient-to-br from-[#FFFDF5] to-white p-10 flex flex-col items-center text-center h-full">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 border-4 border-white shadow-md group-hover:bg-[#e6ac27]/10 transition-colors">
                        <svg class="w-12 h-12 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h4 class="font-black text-2xl text-[#3d2c1d] mb-2">Por Docente</h4>
                    <p class="text-slate-500 font-medium text-sm px-4">Busca a un profesor específico y visualiza en qué aula imparte clases cada día de la semana.</p>
                    <div class="mt-8 inline-flex items-center text-[#e6ac27] font-bold text-sm group-hover:translate-x-2 transition-transform">
                        Explorar Docentes <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>

            <!-- TARJETA 2: AULAS -->
            <a href="{{ route('academico.visor.aulas') }}" class="group block bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-xl hover:border-[#e6ac27]/50 transition-all duration-300 transform hover:-translate-y-1">
                <div class="bg-gradient-to-br from-[#FFFDF5] to-white p-10 flex flex-col items-center text-center h-full">
                    <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 border-4 border-white shadow-md group-hover:bg-[#e6ac27]/10 transition-colors">
                        <svg class="w-12 h-12 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h4 class="font-black text-2xl text-[#3d2c1d] mb-2">Por Aula</h4>
                    <p class="text-slate-500 font-medium text-sm px-4">Selecciona una sección o grado para ver qué materias reciben los estudiantes durante la semana.</p>
                    <div class="mt-8 inline-flex items-center text-[#e6ac27] font-bold text-sm group-hover:translate-x-2 transition-transform">
                        Explorar Aulas <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>

        </div>
    </div>
</x-app-layout>