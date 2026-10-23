<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Control de Ingreso de Calificaciones</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Supervisión y Auditoría Académica de Docentes</p>
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

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 print:max-w-none print:p-0 print:space-y-4" x-data="{ vistaAuditoria: 'materias' }">
        
        <!-- PANEL DE FILTROS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 print:hidden relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#3d2c1d] via-[#e6ac27] to-[#3d2c1d]"></div>
            @include('academico.reportes.partials.filtros-notas', [
                'ruta' => 'academico.reportes.control-notas',
                'mostrarEstado' => true
            ])
        </div>

        <!-- MEMBRETE OFICIAL -->
        <x-membrete-impresion 
            titulo="Acta de Supervisión y Control de Calificaciones"
            subtitulo="Auditoría Académica Institucional • Colegio Cristiano en Nicaragua"
            badge="Ciclo {{ date('Y') }}"
            orientacion="landscape"
        />

        <!-- TABLA DE RESULTADOS DE AUDITORÍA -->
        <div class="bg-white rounded-3xl print:rounded-xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
            <div class="overflow-x-auto print:overflow-visible">
                <table class="min-w-full divide-y divide-slate-200 text-sm print:text-[10px] border-collapse">
                    <thead class="bg-[#FFFDF5] print:bg-[#3d2c1d] text-slate-500 print:text-white uppercase text-[11px] font-black tracking-wider">
                        <tr>
                            <th class="px-4 py-4 text-center w-10">#</th>
                            <th class="px-4 py-4 text-left">Grado y Sección</th>
                            <th class="px-4 py-4 text-left">Asignatura</th>
                            <th class="px-4 py-4 text-left">Docente Responsable</th>
                            <th class="px-3 py-4 text-center">Notas Ingresadas</th>
                            <th class="px-3 py-4 text-center">Notas Faltantes</th>
                            <th class="px-4 py-4 text-center">Estado Acta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($filas as $index => $fila)
                            <tr class="hover:bg-amber-50/30 transition-colors">
                                <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-3.5 font-black text-[#3d2c1d]">{{ $fila['grado'] }} Sec. "{{ $fila['aula'] }}"</td>
                                <td class="px-4 py-3.5 font-bold text-[#3d2c1d]">{{ $fila['asignatura'] }}</td>
                                <td class="px-4 py-3.5 font-medium text-slate-700">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full {{ $fila['docente'] !== 'Sin asignar' ? 'bg-emerald-500' : 'bg-rose-400' }}"></div>
                                        <span>{{ $fila['docente'] }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-center font-black {{ $fila['registradas'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $fila['registradas'] }}</td>
                                <td class="px-3 py-3.5 text-center font-black {{ $fila['pendientes'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $fila['pendientes'] }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    @if(!empty($fila['cerrado']))
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-slate-100 text-slate-700 border border-slate-200">Cerrada</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">Abierta</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400 font-bold">No hay registros de notas para los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FIRMAS OFICIALES PARA IMPRESIÓN -->
        <div class="hidden print:grid print:grid-cols-2 gap-16 pt-14 px-12 text-center page-break-inside-avoid">
            <div class="border-t border-[#3d2c1d] pt-2">
                <p class="text-[10px] font-black text-[#3d2c1d] uppercase tracking-widest">Revisado por Subdirección</p>
                <p class="text-[9px] font-bold text-slate-500 mt-0.5">Firma y Sello</p>
            </div>
            <div class="border-t border-[#3d2c1d] pt-2">
                <p class="text-[10px] font-black text-[#3d2c1d] uppercase tracking-widest">Vo.Bo. Dirección General</p>
                <p class="text-[9px] font-bold text-slate-500 mt-0.5">Colegio Cristiano en Nicaragua</p>
            </div>
        </div>

    </div>
</x-app-layout>