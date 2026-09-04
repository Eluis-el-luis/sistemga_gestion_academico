<div class="space-y-8 animate-fade-in">
    <!-- Encabezado de Sección -->
    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
        <div>
            <h3 class="text-xl font-black text-[#3d2c1d]">Mi Aula Asignada</h3>
            <p class="text-sm font-medium text-slate-500 mt-1">Gestión integral, asistencia y rendimiento de estudiantes.</p>
        </div>
        <!-- Botón de acción principal respetando Title Case, White-space y Jerarquía -->
        <a href="{{ route('academico.asistencia.aula.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#e6ac27] hover:bg-[#c48e1b] text-white text-sm font-black rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Pasar Asistencia Hoy
        </a>
    </div>

    <!-- Tarjetas de Acceso Directo (Jerarquía visual de colores) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Tarjeta 1: Alumnos -->
        <a href="{{ route('academico.alumnos.index') }}" class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex flex-col h-full cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <span class="block font-black text-base text-[#3d2c1d] mb-1">Directorio de Estudiantes</span>
            <span class="block text-xs font-medium text-slate-500 leading-relaxed">Perfiles, seguimiento y promedios individuales.</span>
        </a>

        <!-- Tarjeta 2: Boletines -->
        <a href="#" class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex flex-col h-full cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a3 3 0 106 0v-1m-6 0a3 3 0 006 0v-1a3 3 0 00-6 0v1zm-5-3a2 2 0 01-2-2V7a2 2 0 012-2h10a2 2 0 012 2v5a2 2 0 01-2 2H4z"></path></svg>
            </div>
            <span class="block font-black text-base text-[#3d2c1d] mb-1">Boletines Oficiales</span>
            <span class="block text-xs font-medium text-slate-500 leading-relaxed">Consolidado de notas por parcial.</span>
        </a>

        <!-- Tarjeta 3: Apoyo de Padres -->
        <a href="{{ route('academico.apoyo-padres.index') }}" class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex flex-col h-full cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <span class="block font-black text-base text-[#3d2c1d] mb-1">Apoyo Familiar</span>
            <span class="block text-xs font-medium text-slate-500 leading-relaxed">Evaluación del acompañamiento en casa.</span>
        </a>

        <!-- Tarjeta 4: Prematrícula -->
        <a href="#" class="group bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex flex-col h-full cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
            </div>
            <span class="block font-black text-base text-[#3d2c1d] mb-1">Prematrícula y Pases</span>
            <span class="block text-xs font-medium text-slate-500 leading-relaxed">Continuidad escolar y promoción de grado.</span>
        </a>
    </div>

    <!-- Módulo Estadístico Simplificado -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="col-span-1 lg:col-span-2 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h4 class="font-black text-[#3d2c1d] mb-4">Rendimiento Académico del Aula</h4>
            <!-- Aquí irá el Canvas para la gráfica de aprobados/reprobados (1-2 clases, 3+ clases) -->
            <div class="relative h-64 w-full bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-center">
                <span class="text-sm font-bold text-slate-400">Gráfica de Rendimiento</span>
            </div>
        </div>
        
        <div class="col-span-1 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h4 class="font-black text-[#3d2c1d] mb-4">Asistencia Semanal</h4>
                <div class="relative h-40 w-full flex items-center justify-center">
                     <!-- Minigráfica circular -->
                     <span class="text-4xl font-black text-[#e6ac27]">95%</span>
                </div>
            </div>
            <button class="w-full mt-4 py-3 bg-slate-50 border border-slate-200 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-100 transition-colors">
                Ver Reporte Detallado
            </button>
        </div>
    </div>
</div>