<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-black text-[#3d2c1d] dark:text-white">Apoyo y Acompañamiento en Aula</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Consulta de horarios, grupos asignados y registro de asistencia.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('academico.visor.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-[#3d2c1d] dark:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-colors cursor-pointer">
                Visor de Horarios
            </a>
            <a href="{{ route('academico.asistencia.personal.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-xl text-xs font-black uppercase tracking-wider transition-colors cursor-pointer">
                Mi Asistencia
            </a>
        </div>
    </div>
</div>