<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Bandeja de Impresión</h2>
                <p class="text-sm font-medium text-slate-500 mt-1">Recepción de boletines autorizados por docentes guías.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Bloque 1: Filtro de Parcial -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden p-6 flex justify-between items-center">
                <h3 class="font-black text-[#3d2c1d]">Seleccionar Corte Evaluativo</h3>
                <form method="GET" action="{{ route('academico.boletin.bandeja') }}" class="w-64">
                    <select name="corte_id" onchange="this.form.submit()" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-black text-[#3d2c1d]">
                        @foreach($cortes as $corte)
                            <option value="{{ $corte->id }}" @selected($corte->id == $corteActivo)>
                                {{ $corte->numero }}° Parcial - {{ $anioActivo->nombre }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Bloque 2: Bandeja de Recepción -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-widest font-black text-slate-400">
                            <tr>
                                <th class="p-5">Aula y Grado</th>
                                <th class="p-5">Docente Guía</th>
                                <th class="p-5 text-center">Estado del Paquete</th>
                                <th class="p-5 text-right">Acciones de Impresión</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($aulas as $aula)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-5">
                                        <span class="block font-black text-[#3d2c1d]">{{ $aula->grado->nombre ?? 'Grado' }} - "{{ $aula->nombre }}"</span>
                                        <span class="block text-xs font-bold text-slate-400 mt-0.5">{{ $aula->turno }}</span>
                                    </td>
                                    <td class="p-5 font-medium text-slate-600">
                                        {{ $aula->docenteGuia->usuario->nombre_completo ?? 'Sin asignar' }}
                                    </td>
                                    <td class="p-5 text-center">
                                        @if($aula->estado_impresion === 'Autorizado')
                                            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-black uppercase tracking-widest block w-max mx-auto">
                                                ✔ Listo para Imprimir
                                            </span>
                                            <span class="block text-[9px] font-bold text-slate-400 mt-1">Autorizado: {{ \Carbon\Carbon::parse($aula->fecha_autorizacion)->format('d/m Y H:i') }}</span>
                                        @elseif($aula->estado_impresion === 'Impreso')
                                            <span class="px-3 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-[10px] font-black uppercase tracking-widest block w-max mx-auto">
                                                🖨️ Ya Impreso
                                            </span>
                                        @elseif($aula->estado_impresion === 'Devuelto')
                                            <span class="px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-black uppercase tracking-widest block w-max mx-auto">
                                                ⚠️ Devuelto al Docente
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-slate-100 text-slate-500 border border-slate-200 rounded-lg text-[10px] font-black uppercase tracking-widest block w-max mx-auto">
                                                ⏳ Esperando Envío
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-5 text-right">
                                        @if($aula->estado_impresion === 'Autorizado' || $aula->estado_impresion === 'Impreso')
                                            <div class="flex justify-end gap-2">
                                                <button class="px-4 py-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-xl text-xs font-black shadow-sm transition-all flex items-center gap-2">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                    Generar PDF
                                                </button>
                                                @if($aula->estado_impresion === 'Autorizado')
                                                    <button class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-black transition-all" title="Devolver al Maestro">
                                                        Devolver
                                                    </button>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs font-bold text-slate-300">Acción Bloqueada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-slate-400 font-bold">No hay aulas registradas en el ciclo activo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>