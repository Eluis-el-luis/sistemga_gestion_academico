<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] dark:hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] dark:text-white leading-tight">
                        Mis Alumnos: <span class="text-[#e6ac27]">{{ $aula->grado->nombre ?? '' }} "{{ $aula->nombre ?? '' }}"</span>
                    </h2>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Ranking de rendimiento y alertas tempranas de deserción.</p>
                </div>
            </div>
            
            <!-- Botón de Exportar (Opcional para futuro) -->
            <button class="bg-[#3d2c1d] hover:bg-slate-800 text-white font-bold py-2 px-5 rounded-xl shadow-sm text-sm transition-transform transform hover:-translate-y-0.5 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir Reporte
            </button>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] dark:bg-slate-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- KPIs de Rendimiento del Aula -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Matrícula Activa</span>
                    <div class="flex items-end justify-between mt-2">
                        <h4 class="text-4xl font-black text-[#3d2c1d] dark:text-white">{{ $totalAlumnos }}</h4>
                        <div class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-900 flex items-center justify-center text-slate-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm border-b-4 border-b-emerald-500 flex flex-col justify-between">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Aprobados Limpios</span>
                    <div class="flex items-end justify-between mt-2">
                        <h4 class="text-4xl font-black text-emerald-600">{{ $aprobadosLimpios }}</h4>
                        <span class="text-lg font-black text-slate-300">{{ $pctAprobados }}%</span>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm border-b-4 border-b-amber-500 flex flex-col justify-between">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">A Reparación (1-2)</span>
                    <div class="flex items-end justify-between mt-2">
                        <h4 class="text-4xl font-black text-amber-500">{{ $enReparacion }}</h4>
                        <span class="text-lg font-black text-slate-300">{{ $pctReparacion }}%</span>
                    </div>
                </div>
                
                <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm border-b-4 border-b-rose-500 flex flex-col justify-between">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Riesgo Crítico (3+)</span>
                    <div class="flex items-end justify-between mt-2">
                        <h4 class="text-4xl font-black text-rose-600">{{ $riesgoCritico }}</h4>
                        <span class="text-lg font-black text-slate-300">{{ $pctRiesgo }}%</span>
                    </div>
                </div>
            </div>

            <!-- Tabla de Ranking Real -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="min-w-full text-left border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 text-[11px] uppercase tracking-widest font-black text-slate-400 dark:text-slate-500">
                            <tr>
                                <th class="p-5 text-center w-16">#</th>
                                <th class="p-5">Estudiante</th>
                                <th class="p-5 text-center">Promedio Global</th>
                                <th class="p-5 text-center">Materias Reprobadas</th>
                                <th class="p-5">Situación Académica</th>
                                <th class="p-5 text-right">Expediente</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @forelse($alumnos as $index => $alumno)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="p-5 text-center font-black text-slate-300 dark:text-slate-600">{{ $index + 1 }}</td>
                                    <td class="p-5">
                                        <p class="font-black text-[#3d2c1d] dark:text-white">{{ $alumno->nombre_completo }}</p>
                                        <p class="text-[10px] font-bold text-slate-400 mt-0.5 tracking-widest">{{ $alumno->codigo_unico_persona }}</p>
                                    </td>
                                    <td class="p-5 text-center">
                                        <span class="text-xl font-black {{ $alumno->promedio_global >= 60 ? 'text-[#3d2c1d] dark:text-white' : 'text-rose-500' }}">
                                            {{ number_format($alumno->promedio_global, 1) }}
                                        </span>
                                    </td>
                                    <td class="p-5 text-center">
                                        @if($alumno->clases_reprobadas > 0)
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 font-black text-sm border border-rose-100 dark:border-rose-500/20">
                                                {{ $alumno->clases_reprobadas }}
                                            </span>
                                        @else
                                            <span class="font-black text-slate-300 dark:text-slate-600">-</span>
                                        @endif
                                    </td>
                                    <td class="p-5">
                                        <span class="px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest {{ $alumno->estado_color }}">
                                            {{ $alumno->estado_texto }}
                                        </span>
                                    </td>
                                    <td class="p-5 text-right">
                                        <button class="inline-flex px-4 py-2 bg-slate-50 dark:bg-slate-800 text-[#e6ac27] border border-slate-200 dark:border-slate-700 hover:border-[#e6ac27] rounded-xl text-xs font-black transition-all shadow-sm transform hover:-translate-y-0.5">
                                            Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-12 text-center">
                                        <div class="w-14 h-14 bg-slate-50 dark:bg-slate-900/50 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-100 dark:border-slate-700 text-slate-300">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        </div>
                                        <h3 class="text-lg font-black text-[#3d2c1d] dark:text-white">Sin estudiantes</h3>
                                        <p class="text-sm font-medium text-slate-400 mt-1">No hay alumnos matriculados activamente en esta sección.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>