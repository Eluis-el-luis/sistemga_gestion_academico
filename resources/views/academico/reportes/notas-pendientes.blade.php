<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Alerta de Notas Pendientes</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Auditoría detallada por Estudiante y Asignatura</p>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ route('academico.reportes.index') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all transform hover:-translate-y-0.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir / PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 print:max-w-none print:p-0 print:space-y-4">
        
        <!-- PANEL DE FILTROS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 print:hidden relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-rose-700 via-rose-500 to-rose-700"></div>
            @include('academico.reportes.partials.filtros-notas', [
                'ruta' => 'academico.reportes.notas-pendientes',
                'mostrarEstado' => false
            ])
        </div>

        <!-- MEMBRETE OFICIAL -->
        <x-membrete-impresion 
            titulo="Reporte de Calificaciones Pendientes (Alerta Docente)"
            subtitulo="Auditoría Académica Institucional • Colegio Cristiano en Nicaragua"
            badge="Ciclo {{ date('Y') }}"
            orientacion="portrait"
        />

        <!-- TABLA DE RESULTADOS DE AUDITORÍA -->
        <div class="bg-white rounded-3xl print:rounded-xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
            <div class="overflow-x-auto print:overflow-visible">
                <table class="min-w-full divide-y divide-slate-200 text-sm print:text-[10px] border-collapse">
                    <thead class="bg-rose-50 print:bg-rose-900 text-rose-700 print:text-white uppercase text-[10px] font-black tracking-wider">
                        <tr>
                            <th class="px-4 py-4 text-center w-10">#</th>
                            <th class="px-4 py-4 text-left">CUP</th>
                            <th class="px-4 py-4 text-left">Estudiante</th>
                            <th class="px-4 py-4 text-left">Grado y Sección</th>
                            <th class="px-4 py-4 text-left">Asignatura Pendiente</th>
                            <th class="px-4 py-4 text-left">Docente Responsable</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($filas ?? [] as $index => $fila)
                            <tr class="hover:bg-rose-50/50 transition-colors">
                                <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 font-mono text-xs text-slate-500">{{ $fila['cup'] }}</td>
                                <td class="px-4 py-3.5 font-black text-[#3d2c1d]">{{ $fila['estudiante'] }}</td>
                                <td class="px-4 py-3.5 font-bold text-slate-600">{{ $fila['grado_aula'] }}</td>
                                <td class="px-4 py-3.5 font-black text-rose-600">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $fila['asignatura'] }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-slate-700">{{ $fila['docente'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <p class="text-slate-500 font-bold">¡Excelente! No hay calificaciones pendientes para los filtros seleccionados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>