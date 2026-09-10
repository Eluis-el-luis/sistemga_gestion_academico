<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                        Rendimiento por Corte — <span class="text-[#e6ac27]">{{ $aula->grado->nombre ?? '' }} "{{ $aula->nombre }}"</span>
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 font-medium">{{ $aula->modalidad->nombre ?? '' }} · {{ $aula->anioEscolar->nombre ?? '' }}</p>
                </div>
            </div>

            <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir / PDF</button>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Filtro de periodo evaluativo -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <form method="GET" action="{{ route('academico.tutor.rendimiento') }}" class="flex items-end gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Periodo Evaluativo</label>
                    <select name="corte_evaluativo_id" onchange="this.form.submit()" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
                        <option value="">Todos los cortes</option>
                        @foreach($cortes as $corte)
                            <option value="{{ $corte->id }}" @selected($corte->id == request('corte_evaluativo_id'))>{{ $corte->numero }}° Corte (S{{ $corte->semestre }})</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Encabezado institucional -->
        <div class="bg-white p-6 border-2 border-black text-center rounded-3xl print:border print:border-black">
            <p class="font-black uppercase text-sm tracking-wide">MINISTERIO DE EDUCACIÓN — DEPARTAMENTO DE ESTADÍSTICA MUNICIPAL LEÓN</p>
            <p class="font-bold text-sm text-slate-700 mt-1">RENDIMIENTO ACADÉMICO POR CORTE</p>
            <p class="font-bold text-sm mt-1">Colegio Cristiano en Nicaragua — {{ $aula->modalidad->nombre ?? '' }}</p>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
                <p class="text-3xl font-black text-[#3d2c1d]">{{ $rendimientoAula['mi_as'] + $rendimientoAula['mi_f'] }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Matrícula</p>
            </div>
            <div class="bg-white rounded-2xl border border-emerald-200 p-5 text-center">
                <p class="text-3xl font-black text-emerald-600">{{ $rendimientoAula['aprobados_todas'] }}</p>
                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest mt-1">Aprobados en Todas</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
                <p class="text-3xl font-black text-[#e6ac27]">{{ $rendimientoAula['porcentaje_aprobados'] }}%</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">% Aprobados</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
                <p class="text-3xl font-black text-[#3d2c1d]">{{ $rendimientoAula['total_docentes'] }}</p>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Docentes del Grado</p>
            </div>
        </div>

        <!-- Tabla REA (solo este grado) -->
        <div class="bg-white border-2 border-black overflow-x-auto rounded-3xl print:border print:border-black">
            <table class="min-w-full border-collapse text-xs font-bold">
                <thead>
                    <tr class="text-center">
                        <th rowspan="2" class="border border-black px-2 py-2">Nivel/Grado</th>
                        <th colspan="2" class="border border-black px-2 py-2">MI</th>
                        <th colspan="2" class="border border-black px-2 py-2">Aprobados en Todas</th>
                        <th colspan="2" class="border border-black px-2 py-2">Aplazados de 1</th>
                        <th colspan="2" class="border border-black px-2 py-2">Aplazados de 2</th>
                        <th colspan="2" class="border border-black px-2 py-2">Aplazados de 3 a +</th>
                        <th colspan="2" class="border border-black px-2 py-2">Total Docentes</th>
                        <th rowspan="2" class="border border-black px-2 py-2">% Aprobados</th>
                    </tr>
                    <tr class="text-center text-[10px]">
                        @foreach(range(1, 6) as $i)
                            <th class="border border-black px-1 py-1">AS</th>
                            <th class="border border-black px-1 py-1">F</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr>
                        <td class="border border-black px-2 py-1.5 font-black">{{ $rendimientoAula['grado'] }}</td>
                        <td class="border border-black px-1">{{ $rendimientoAula['mi_as'] }}</td>
                        <td class="border border-black px-1">{{ $rendimientoAula['mi_f'] }}</td>

                        @php
                            $aprobadosAs = null; $aprobadosF = null;
                            $apl1As = null; $apl1F = null;
                            // El service no desglosa por sexo para aprobados/aplazados; mostramos el total centrado
                        @endphp
                        <td class="border border-black px-1" colspan="2">{{ $rendimientoAula['aprobados_todas'] }}</td>
                        <td class="border border-black px-1" colspan="2">{{ $rendimientoAula['aplazados_1'] }}</td>
                        <td class="border border-black px-1" colspan="2">{{ $rendimientoAula['aplazados_2'] }}</td>
                        <td class="border border-black px-1" colspan="2">{{ $rendimientoAula['aplazados_3'] }}</td>
                        <td class="border border-black px-1" colspan="2">{{ $rendimientoAula['total_docentes'] }}</td>
                        <td class="border border-black px-1">{{ $rendimientoAula['porcentaje_aprobados'] }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-xs text-slate-500 text-center">Documento generado el {{ now()->format('d/m/Y') }} — Vista exclusiva del Docente Guía de esta aula.</p>
    </div>
</x-app-layout>