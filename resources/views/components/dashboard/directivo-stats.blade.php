<!-- CONTENEDOR DE ESTADÍSTICAS DIRECTIVAS -->
<div class="space-y-6 animate-fade-in-up">
    
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <h3 class="text-xl font-black text-[#3d2c1d] flex items-center gap-2">
            <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Visión General Institucional
        </h3>
        <span class="text-sm font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">
            {{ now()->timezone('America/Managua')->translatedFormat('d \d\e F, Y') }}
        </span>
    </div>

    <!-- TARJETAS DE MÉTRICAS EN TIEMPO REAL -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Tarjeta 1: Matrícula -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col hover:shadow-md hover:border-emerald-200 transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Matrícula Activa</p>
                    <h4 class="text-3xl font-black text-[#3d2c1d]">
                        <!-- Consulta a PostgreSQL en tiempo real -->
                        {{ \App\Models\Matricula::where('estado', 'activo')->count() ?? 0 }}
                    </h4>
                </div>
            </div>
            <div class="mt-auto pt-4 border-t border-slate-50">
                <p class="text-[11px] text-emerald-600 font-bold flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    Estudiantes cursando actualmente
                </p>
            </div>
        </div>

        <!-- Tarjeta 2: Aulas -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col hover:shadow-md hover:border-blue-200 transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-blue-50 text-blue-600 rounded-xl group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aulas Operativas</p>
                    <h4 class="text-3xl font-black text-[#3d2c1d]">
                        <!-- Consulta a PostgreSQL en tiempo real -->
                        {{ \App\Models\Aula::count() ?? 0 }}
                    </h4>
                </div>
            </div>
            <div class="mt-auto pt-4 border-t border-slate-50">
                <p class="text-[11px] text-blue-600 font-bold flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Distribuidas en el recinto
                </p>
            </div>
        </div>

        <!-- Tarjeta 3: Alertas (Retirados) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col hover:shadow-md hover:border-red-200 transition-all group">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-red-50 text-red-600 rounded-xl group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Deserciones</p>
                    <h4 class="text-3xl font-black text-red-600">
                        <!-- Consulta a PostgreSQL en tiempo real -->
                        {{ \App\Models\Matricula::where('estado', 'retirado')->count() ?? 0 }}
                    </h4>
                </div>
            </div>
            <div class="mt-auto pt-4 border-t border-slate-50">
                <p class="text-[11px] text-red-500 font-bold flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Requiere atención inmediata
                </p>
            </div>
        </div>
    </div>

    <!-- ACCESOS RÁPIDOS DIRECCIÓN -->
    <div class="mt-10">
        <h4 class="text-sm font-black text-slate-500 uppercase tracking-widest mb-4">Acciones Frecuentes</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            
            <a href="{{ route('academico.usuarios.index') }}" class="group flex flex-col items-center justify-center p-5 bg-white border border-slate-200 rounded-2xl hover:bg-[#FFFDF5] hover:border-[#e6ac27] transition-all shadow-sm">
                <div class="p-3 bg-slate-50 text-slate-600 rounded-xl group-hover:bg-[#e6ac27] group-hover:text-white mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <span class="text-sm font-bold text-slate-700 group-hover:text-[#3d2c1d]">Personal</span>
            </a>

            <a href="{{ route('academico.alumnos.index') }}" class="group flex flex-col items-center justify-center p-5 bg-white border border-slate-200 rounded-2xl hover:bg-[#FFFDF5] hover:border-[#e6ac27] transition-all shadow-sm">
                <div class="p-3 bg-slate-50 text-slate-600 rounded-xl group-hover:bg-[#e6ac27] group-hover:text-white mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <span class="text-sm font-bold text-slate-700 group-hover:text-[#3d2c1d]">Alumnado</span>
            </a>

            <a href="{{ route('academico.aulas.index') }}" class="group flex flex-col items-center justify-center p-5 bg-white border border-slate-200 rounded-2xl hover:bg-[#FFFDF5] hover:border-[#e6ac27] transition-all shadow-sm">
                <div class="p-3 bg-slate-50 text-slate-600 rounded-xl group-hover:bg-[#e6ac27] group-hover:text-white mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 01-1 1H4z"></path></svg>
                </div>
                <span class="text-sm font-bold text-slate-700 group-hover:text-[#3d2c1d]">Aulas</span>
            </a>

            <a href="{{ route('academico.malla.index') }}" class="group flex flex-col items-center justify-center p-5 bg-white border border-slate-200 rounded-2xl hover:bg-[#FFFDF5] hover:border-[#e6ac27] transition-all shadow-sm">
                <div class="p-3 bg-slate-50 text-slate-600 rounded-xl group-hover:bg-[#e6ac27] group-hover:text-white mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <span class="text-sm font-bold text-slate-700 group-hover:text-[#3d2c1d]">Malla Curricular</span>
            </a>
        </div>
    </div>
</div>