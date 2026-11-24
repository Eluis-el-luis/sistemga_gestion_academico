@php
    $matriculaActivaCount = \App\Models\Matricula::where('estado', 'activo')->count();
    $aulasCount = \App\Models\Aula::count();
    $retiradosCount = \App\Models\Matricula::where('estado', 'retirado')->count();
@endphp

<div class="space-y-6">
    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
        <h3 class="text-xl font-black text-[#3d2c1d] dark:text-white flex items-center gap-2">
            <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Visión General Institucional
        </h3>
        <span class="text-sm font-bold text-slate-500 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-3 py-1 rounded-lg">
            {{ now()->timezone('America/Managua')->translatedFormat('d \d\e F, Y') }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex items-center gap-4 hover:shadow-md hover:border-emerald-200 transition-all group">
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Matrícula Activa</p>
                <h4 class="text-3xl font-black text-[#3d2c1d] dark:text-white">{{ $matriculaActivaCount }}</h4>
                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-1">Estudiantes cursando actualmente</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-800 p-6 flex items-center gap-4 hover:shadow-md hover:border-blue-200 transition-all group">
            <div class="p-3 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-xl group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Aulas Operativas</p>
                <h4 class="text-3xl font-black text-[#3d2c1d] dark:text-white">{{ $aulasCount }}</h4>
                <p class="text-[11px] text-blue-600 dark:text-blue-400 font-bold mt-1">Distribuidas en el centro</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-rose-100 dark:border-rose-900/50 p-6 flex items-center gap-4 hover:shadow-md hover:border-rose-200 transition-all group">
            <div class="p-3 bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 rounded-xl group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bajas / Retiros</p>
                <h4 class="text-3xl font-black text-rose-600 dark:text-rose-400">{{ $retiradosCount }}</h4>
                <p class="text-[11px] text-rose-500 dark:text-rose-400 font-bold mt-1">Requiere seguimiento</p>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h4 class="text-sm font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4">Acciones Frecuentes</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('academico.usuarios.index') }}" class="group flex flex-col items-center justify-center p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl hover:bg-[#FFFDF5] dark:hover:bg-slate-800/80 hover:border-[#e6ac27] transition-all shadow-sm cursor-pointer">
                <div class="p-3 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl group-hover:bg-[#e6ac27] group-hover:text-white mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-[#3d2c1d] dark:group-hover:text-white">Personal</span>
            </a>

            <a href="{{ route('academico.alumnos.index') }}" class="group flex flex-col items-center justify-center p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl hover:bg-[#FFFDF5] dark:hover:bg-slate-800/80 hover:border-[#e6ac27] transition-all shadow-sm cursor-pointer">
                <div class="p-3 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl group-hover:bg-[#e6ac27] group-hover:text-white mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-[#3d2c1d] dark:group-hover:text-white">Alumnado</span>
            </a>

            <a href="{{ route('academico.aulas.index') }}" class="group flex flex-col items-center justify-center p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl hover:bg-[#FFFDF5] dark:hover:bg-slate-800/80 hover:border-[#e6ac27] transition-all shadow-sm cursor-pointer">
                <div class="p-3 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl group-hover:bg-[#e6ac27] group-hover:text-white mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-[#3d2c1d] dark:group-hover:text-white">Aulas</span>
            </a>

            <a href="{{ route('academico.malla.index') }}" class="group flex flex-col items-center justify-center p-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl hover:bg-[#FFFDF5] dark:hover:bg-slate-800/80 hover:border-[#e6ac27] transition-all shadow-sm cursor-pointer">
                <div class="p-3 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-xl group-hover:bg-[#e6ac27] group-hover:text-white mb-3 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-[#3d2c1d] dark:group-hover:text-white">Malla Curricular</span>
            </a>
        </div>
    </div>
</div>