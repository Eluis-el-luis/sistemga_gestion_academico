<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center print:hidden">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Control de Notas</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6 print:max-w-none print:space-y-4">

        <!-- PANEL DE FILTROS (Se oculta por completo al imprimir) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4 print:hidden">
            @include('academico.reportes.partials.filtros-notas', ['ruta' => 'academico.reportes.control-notas', 'conEstado' => true])
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-md text-xs uppercase tracking-widest transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Imprimir / PDF
            </button>
        </div>

        <!-- COMPONENTE CENTRALIZADO DE IMPRESIÓN -->
        @php
            $estadoFiltro = request('tipo') === 'pendientes' ? 'Notas Pendientes' : (request('tipo') === 'ingresadas' ? 'Notas Ingresadas' : 'Todos los Estados');
            $detalleReporte = 'Filtro: <span class="text-[#3d2c1d]">' . $estadoFiltro . '</span> <span class="mx-1 text-slate-300">|</span> Registros: <span class="text-[#3d2c1d]">' . count($filas) . '</span>';
        @endphp
        <x-membrete-impresion 
            titulo="Reporte de Control de Ingreso de Notas"
            subtitulo="Auditoría de Calificaciones • {{ date('Y') }}"
            badge="Control de Notas"
            :detalle="$detalleReporte"
            orientacion="landscape"
        />

        <!-- TABLA DE RESULTADOS -->
        <div class="bg-white rounded-3xl print:rounded-2xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
            <div class="overflow-x-auto print:overflow-visible">
                <table class="min-w-full divide-y divide-slate-200 text-sm print:text-xs border-collapse">
                    <thead class="bg-[#FFFDF5] print:bg-[#3d2c1d] text-slate-500 print:text-white uppercase text-xs print:text-[11px] font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4 print:px-3 print:py-2.5 text-left print:text-[#e6ac27]">Aula</th>
                            <th class="px-6 py-4 print:px-3 print:py-2.5 text-left">Grado</th>
                            <th class="px-6 py-4 print:px-3 print:py-2.5 text-left">Asignatura</th>
                            <th class="px-6 py-4 print:px-3 print:py-2.5 text-left">Docente</th>
                            <th class="px-6 py-4 print:px-3 print:py-2.5 text-center">Registradas</th>
                            <th class="px-6 py-4 print:px-3 print:py-2.5 text-center">Pendientes</th>
                            <th class="px-6 py-4 print:px-3 print:py-2.5 text-center">Estado</th>
                            <th class="px-6 py-4 print:px-3 print:py-2.5 text-center">Avance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 print:divide-slate-200">
                        @forelse($filas as $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 print:px-3 print:py-2 font-black text-[#3d2c1d]">{{ $fila['aula'] }}</td>
                                <td class="px-6 py-4 print:px-3 print:py-2 font-medium text-slate-700">{{ $fila['grado'] }}</td>
                                <td class="px-6 py-4 print:px-3 print:py-2 font-bold text-[#3d2c1d]">{{ $fila['asignatura'] }}</td>
                                <td class="px-6 py-4 print:px-3 print:py-2 {{ $fila['docente'] === 'Sin asignar' ? 'text-rose-500 font-bold' : 'text-slate-700' }}">{{ $fila['docente'] }}</td>
                                <td class="px-6 py-4 print:px-3 print:py-2 text-center font-black text-emerald-600">{{ $fila['registradas'] }}</td>
                                <td class="px-6 py-4 print:px-3 print:py-2 text-center font-black {{ $fila['pendientes'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $fila['pendientes'] }}</td>
                                <td class="px-6 py-4 print:px-3 print:py-2 text-center">
                                    @if(!empty($fila['cerrado']))
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-rose-100 text-rose-700">Cerrado</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700">Abierto</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 print:px-3 print:py-2 text-center">
                                    <div class="w-28 print:w-20 bg-slate-200 rounded-full h-2 mx-auto overflow-hidden">
                                        <div class="h-2 rounded-full {{ $fila['porcentaje'] == 100 ? 'bg-emerald-500' : 'bg-[#e6ac27]' }}" style="width: {{ $fila['porcentaje'] }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-500 mt-0.5 block">{{ $fila['porcentaje'] }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-stone-500 font-bold">
                                    No hay resultados para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>