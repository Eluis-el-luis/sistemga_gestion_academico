<div class="space-y-6">
    <!-- PANEL DE ATENCIÓN PRIORITARIA -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Alertas de Asistencia Docente -->
        <div class="bg-white p-6 rounded-3xl border border-rose-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-full bg-gradient-to-l from-rose-50 to-transparent pointer-events-none"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-[#3d2c1d]">Ausencias del Día</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Docentes sin marcar</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 bg-rose-100 text-rose-600 text-xs font-black rounded-lg">2 Alertas</span>
            </div>
            <!-- Lista Simulada (Se conectará al backend luego) -->
            <div class="space-y-3 relative z-10">
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-xs font-bold text-slate-700">Prof. Carlos Mendoza</span>
                    <span class="text-[10px] font-black text-rose-500 uppercase tracking-widest">Revisar</span>
                </div>
            </div>
        </div>

        <!-- Solicitudes de Desbloqueo de Notas -->
        <div class="bg-white p-6 rounded-3xl border border-amber-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-full bg-gradient-to-l from-amber-50 to-transparent pointer-events-none"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-[#3d2c1d]">Edición de Notas</h3>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Permisos pendientes</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 bg-amber-100 text-amber-600 text-xs font-black rounded-lg">1 Petición</span>
            </div>
            <div class="space-y-3 relative z-10">
                <div class="flex justify-between items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-xs font-bold text-slate-700">1ro A - TAC (Profa. Ana)</span>
                    <button class="text-[10px] font-black text-amber-600 hover:text-amber-700 uppercase tracking-widest bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200 transition-colors">Evaluar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ACCESOS DIRECTOS MEJORADOS -->
    <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mt-8 mb-4 ml-2">Herramientas de Coordinación</h3>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
        <!-- 1. Aulas y Asignaciones -->
        <a href="{{ route('academico.aulas.index') }}" class="group bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col h-full cursor-pointer">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <span class="block font-black text-base text-[#3d2c1d] mb-1">Aulas y Cargas</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Estructura Académica</span>
        </a>

        <!-- 2. Visor de Horarios -->
        <button @click.prevent="$dispatch('abrir-modal-horarios')" class="text-left group bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col h-full cursor-pointer w-full">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <span class="block font-black text-base text-[#3d2c1d] mb-1">Visor de Horarios</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Control de Clases</span>
        </button>

        <!-- 3. Calificaciones -->
        <a href="{{ route('academico.notas.index') }}" class="group bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col h-full cursor-pointer">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            </div>
            <span class="block font-black text-base text-[#3d2c1d] mb-1">Calificaciones</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Revisión de Rendimiento</span>
        </a>

        <!-- 4. Asistencia de Personal -->
        <a href="{{ route('academico.asistencia.personal.index') }}" class="group bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col h-full cursor-pointer">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <span class="block font-black text-base text-[#3d2c1d] mb-1">Personal Docente</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Monitoreo de Asistencia</span>
        </a>

        <!-- 5. Buzón Disciplinario (NUEVO) -->
        <a href="{{ route('academico.disciplina.index') }}" class="group bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col h-full cursor-pointer">
            <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <span class="block font-black text-base text-[#3d2c1d] mb-1">Disciplina</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-relaxed">Gestión de Casos</span>
        </a>
    </div>
</div>