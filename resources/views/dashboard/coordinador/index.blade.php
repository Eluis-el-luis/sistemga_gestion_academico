<x-app-layout>
    <div class="py-10 bg-slate-50 min-h-screen relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <x-dashboard.banner>
                Supervisa el avance programático, el rendimiento de los estudiantes y la presencialidad de tu modalidad asignada.
            </x-dashboard.banner>

            <!-- KPIs de Modalidad (Tema Púrpura) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-3xl shadow-sm border border-purple-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="p-4 bg-purple-50 text-purple-600 rounded-2xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Aulas a Cargo</p>
                        <h4 class="text-3xl font-black text-[#3d2c1d]">{{ $totalAulas ?? 0 }}</h4>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-purple-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="p-4 bg-purple-50 text-purple-600 rounded-2xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Matrícula de Nivel</p>
                        <h4 class="text-3xl font-black text-[#3d2c1d]">{{ $totalAlumnos ?? 0 }}</h4>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-purple-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
                    <div class="p-4 bg-purple-50 text-purple-600 rounded-2xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Docentes Activos</p>
                        <h4 class="text-3xl font-black text-[#3d2c1d]">{{ $totalDocentes ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <!-- Accesos Rápidos de Supervisión -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                <a href="{{ route('academico.visor.docentes') }}" class="flex flex-col items-center justify-center p-6 bg-white border border-slate-200 rounded-3xl hover:border-purple-300 hover:bg-purple-50 transition-all shadow-sm group">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <span class="font-black text-sm text-[#3d2c1d]">Maestros</span>
                </a>
                <a href="{{ route('academico.visor.aulas') }}" class="flex flex-col items-center justify-center p-6 bg-white border border-slate-200 rounded-3xl hover:border-purple-300 hover:bg-purple-50 transition-all shadow-sm group">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <span class="font-black text-sm text-[#3d2c1d]">Aulas</span>
                </a>
                <a href="#" class="flex flex-col items-center justify-center p-6 bg-white border border-slate-200 rounded-3xl hover:border-purple-300 hover:bg-purple-50 transition-all shadow-sm group">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <span class="font-black text-sm text-[#3d2c1d]">Seguimiento</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>