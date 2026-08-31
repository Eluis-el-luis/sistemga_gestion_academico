<x-app-layout>
    <div class="py-10 bg-slate-50 min-h-screen relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <x-dashboard.banner>
                Monitorea el ingreso diario de estudiantes y el consolidado de asistencia por aulas.
            </x-dashboard.banner>

            <!-- Resumen Rápido del Día (Tema Esmeralda/Azul) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex items-center gap-4">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Presencialidad Hoy</p>
                        <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $asistenciaTotalHoy ?? 0 }} / {{ $matriculaTotal ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <!-- Tabla de Recepción Operativa -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-[#FFFDF5] px-6 py-5 border-b border-[#e6ac27]/20 flex justify-between items-center">
                    <h3 class="font-black text-lg text-[#3d2c1d]">Control de Aulas - {{ now()->format('d/m/Y') }}</h3>
                    <input type="date" class="border-slate-200 rounded-xl text-sm text-slate-600 focus:ring-[#e6ac27]" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-[10px] uppercase tracking-widest text-slate-400">
                                <th class="p-4 font-black">Aula</th>
                                <th class="p-4 font-black">Responsable</th>
                                <th class="p-4 font-black text-center">Mujeres</th>
                                <th class="p-4 font-black text-center">Varones</th>
                                <th class="p-4 font-black text-center">Total Asistencia</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-slate-600 font-medium">
                            @forelse($aulasAsistencia as $aula)
                            <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 font-bold text-[#3d2c1d]">{{ $aula->nombre }}</td>
                                <td class="p-4">{{ $aula->docente_guia_nombre }}</td>
                                <td class="p-4 text-center text-rose-600">{{ $aula->asistencia_femenino }} / {{ $aula->total_femenino }}</td>
                                <td class="p-4 text-center text-blue-600">{{ $aula->asistencia_masculino }} / {{ $aula->total_masculino }}</td>
                                <td class="p-4 text-center font-black text-emerald-600">{{ $aula->asistencia_total }} / {{ $aula->total_alumnos }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">Seleccione un día para visualizar los reportes consolidados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>