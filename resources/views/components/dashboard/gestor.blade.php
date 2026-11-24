<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    <a href="{{ route('academico.usuarios.index') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Usuarios del Sistema</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Altas y Bajas</span>
        </div>
    </a>

    <a href="{{ route('academico.alumnos.index') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Directorio de Alumnos</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Perfiles Estudiantiles</span>
        </div>
    </a>

    <a href="{{ route('academico.matriculas.index') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Matrículas</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Gestión de Ingresos</span>
        </div>
    </a>

    <a href="{{ route('academico.boletin.bandeja') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Impresión Boletines</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Bandeja de Recepción</span>
        </div>
    </a>

    <a href="{{ route('academico.grupo-materia.index') }}" class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
        </div>
        <div>
            <span class="block font-black text-sm text-[#3d2c1d] dark:text-white">Grupos de Materias</span>
            <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Configuración Boletines</span>
        </div>
    </a>
</div>