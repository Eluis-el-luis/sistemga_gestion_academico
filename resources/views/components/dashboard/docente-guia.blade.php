@hasanyrole('Docente Guia')
<div x-show="rolActivo === 'Docente Guia'" x-transition.opacity style="display: none;" class="space-y-6">
    
    <!-- Encabezado de Contexto -->
    <div class="flex items-center justify-between mb-2 px-2">
        <div>
            <h3 class="text-xl font-black text-[#3d2c1d]">Tutoría Activa: <span class="text-[#e6ac27]">{{ $aulaGuia->grado->nombre ?? 'Sin Grado' }} - Sección "{{ $aulaGuia->nombre ?? 'N/A' }}"</span></h3>
            <p class="text-sm text-slate-500 font-medium mt-1">Panel de control y seguimiento integral del grupo asignado.</p>
        </div>
    </div>

    <!-- 4 Accesos Rápidos Operativos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Mis Alumnos (Apunta a la tabla que ya hicimos) -->
        <a href="{{ route('academico.tutor.mis-alumnos') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[130px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d]">Mis Alumnos</span>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Directorio y Promedios</span>
            </div>
        </a>

        <!-- 2. Boletines Oficiales -->
        <a href="{{ route('academico.boletines.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[130px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d]">Boletines Oficiales</span>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Consolidado por Parcial</span>
            </div>
        </a>

        <!-- 3. Apoyo Familiar -->
        <a href="#" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[130px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d]">Apoyo Familiar</span>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Evaluación de Hogar</span>
            </div>
        </a>

        <!-- 4. Prematrícula y Pases -->
        <a href="#" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] hover:shadow-md transition-all flex flex-col justify-between min-h-[130px]">
            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div class="mt-4">
                <span class="block font-black text-[#3d2c1d]">Prematrícula y Pases</span>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Proyección Escolar</span>
            </div>
        </a>
    </div>

    <!-- Sección Inferior: Gráfica y Asistencia Demográfica -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Gráfica de Rendimiento -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col">
            <h3 class="font-black text-[#3d2c1d] mb-4">Rendimiento Académico del Aula</h3>
            <div class="flex-grow bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-center min-h-[250px]">
                <span class="text-sm font-bold text-slate-400">Gráfica de Promedios Globales (Próximamente con Chart.js)</span>
            </div>
        </div>

        <!-- Widget de Asistencia Demográfica -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-black text-[#3d2c1d] mb-6">Asistencia Semanal</h3>
                
                <!-- KPI General -->
                <div class="text-center mb-8">
                    <span class="text-6xl font-black text-[#e6ac27] drop-shadow-sm">95<span class="text-3xl text-slate-300">%</span></span>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-2">Promedio General de la Semana</p>
                </div>

                <!-- Desglose por Sexo -->
                <div class="space-y-5">
                    <!-- Niñas (Rosa) -->
                    <div>
                        <div class="flex justify-between items-end text-xs font-black uppercase tracking-widest mb-1.5">
                            <span class="text-pink-500">Niñas (20/21)</span>
                            <span class="text-slate-500 text-sm">98%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-pink-400 h-full rounded-full transition-all" style="width: 98%"></div>
                        </div>
                    </div>
                    <!-- Niños (Celeste) -->
                    <div>
                        <div class="flex justify-between items-end text-xs font-black uppercase tracking-widest mb-1.5">
                            <span class="text-sky-500">Niños (18/19)</span>
                            <span class="text-slate-500 text-sm">92%</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-sky-400 h-full rounded-full transition-all" style="width: 92%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="w-full mt-8 py-3.5 bg-[#FFFDF5] hover:bg-slate-50 border border-[#e6ac27]/30 text-[#e6ac27] rounded-xl text-sm font-black shadow-sm transition-colors">
                Ver Reporte Detallado
            </button>
        </div>
    </div>
</div>
@endhasanyrole