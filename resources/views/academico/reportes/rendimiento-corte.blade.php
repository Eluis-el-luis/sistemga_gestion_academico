<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Rendimiento por Corte (Formato MINED)</h2>
            <div class="flex gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
                <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir / PDF</button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Filtros -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            @include('academico.reportes.partials.filtros-rendimiento')
        </div>

        <!-- Encabezado institucional -->
        <div class="bg-white p-6 border-2 border-black text-center print:border print:border-black rounded-3xl">
            <p class="font-black uppercase text-sm tracking-wide">MINISTERIO DE EDUCACIÓN — DEPARTAMENTO DE ESTADÍSTICA MUNICIPAL LEÓN</p>
            <p class="font-bold text-sm text-slate-700 mt-1">RENDIMIENTO ACADÉMICO GENERAL</p>
            <p class="font-bold text-sm mt-1">Colegio Cristiano en Nicaragua {{ $modalidad ? '— ' . $modalidad : '' }}</p>
        </div>

        <!-- Tabla REA -->
        <div class="bg-white border-2 border-black overflow-x-auto rounded-3xl print:border print:border-black">
            <table class="min-w-full border-collapse text-xs font-bold">
                <thead>
                    <tr class="text-center">
                        <th rowspan="2" class="border border-black px-2 py-2">Nivel/Grado</th>
                        <th colspan="2" class="border border-black px-2 py-2">MI</th>
                        <th colspan="2" class="border border-black px-2 py-2">MA</th>
                        <th colspan="2" class="border border-black px-2 py-2">Aprobados en Todas</th>
                        <th colspan="2" class="border border-black px-2 py-2">Aplazados de 1</th>
                        <th colspan="2" class="border border-black px-2 py-2">Aplazados de 2</th>
                        <th colspan="2" class="border border-black px-2 py-2">Aplazados de 3 a +</th>
                        <th colspan="2" class="border border-black px-2 py-2">Est. Asisten al REP</th>
                        <th colspan="2" class="border border-black px-2 py-2">Maestros Asisten al REP</th>
                        <th colspan="2" class="border border-black px-2 py-2">Total Docentes por Grado</th>
                        <th colspan="2" class="border border-black px-2 py-2">Validación R.A</th>
                        <th rowspan="2" class="border border-black px-2 py-2">% Aprobados</th>
                        <th rowspan="2" class="border border-black px-2 py-2">% Retención</th>
                    </tr>
                    <tr class="text-center text-[10px]">
                        @foreach(range(1, 13) as $i)
                            <th class="border border-black px-1 py-1">AS</th>
                            <th class="border border-black px-1 py-1">F</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="text-center">
                    @forelse($filas as $fila)
                        <tr>
                            <td class="border border-black px-2 py-1.5 font-black">{{ $fila['grado'] }}</td>
                            <td class="border border-black px-1">{{ $fila['mi_as'] }}</td>
                            <td class="border border-black px-1">{{ $fila['mi_f'] }}</td>
                            <td class="border border-black px-1">{{ $fila['ma_as'] }}</td>
                            <td class="border border-black px-1">{{ $fila['ma_f'] }}</td>
                            <td class="border border-black px-1">{{ $fila['aprobados_todas_as'] }}</td>
                            <td class="border border-black px-1">{{ $fila['aprobados_todas_f'] }}</td>
                            <td class="border border-black px-1">{{ $fila['aplazados_1_as'] }}</td>
                            <td class="border border-black px-1">{{ $fila['aplazados_1_f'] }}</td>
                            <td class="border border-black px-1">{{ $fila['aplazados_2_as'] }}</td>
                            <td class="border border-black px-1">{{ $fila['aplazados_2_f'] }}</td>
                            <td class="border border-black px-1">{{ $fila['aplazados_3_as'] }}</td>
                            <td class="border border-black px-1">{{ $fila['aplazados_3_f'] }}</td>
                            <td class="border border-black px-1">—</td>
                            <td class="border border-black px-1">—</td>
                            <td class="border border-black px-1">—</td>
                            <td class="border border-black px-1">—</td>
                            <td class="border border-black px-1">—</td>
                            <td class="border border-black px-1">{{ $fila['total_docentes'] }}</td>
                            <td class="border border-black px-2" colspan="2">CORRECTO</td>
                            <td class="border border-black px-1">{{ $fila['porcentaje_aprobados'] }}%</td>
                            <td class="border border-black px-1">{{ $fila['porcentaje_retencion'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="28" class="border border-black px-4 py-8 text-center text-slate-500">No hay datos para los filtros seleccionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="text-xs text-slate-500 text-center">Documento generado el {{ now()->format('d/m/Y') }} — Formato oficial para el Departamento de Estadística Municipal (León).</p>
    </div>
</x-app-layout>