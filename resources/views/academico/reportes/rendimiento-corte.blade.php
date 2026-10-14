<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Rendimiento por Corte (Formato MINED III Corte 2026)</h2>
            <div class="flex gap-3 print:hidden">
                <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
                <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir / PDF</button>
            </div>
        </div>
    </x-slot>

    <!-- El formato oficial trae tablas muy anchas: impresión horizontal -->
    <style>
        @media print {
            @page { size: letter landscape; margin: 8mm; }
        }
    </style>

    <div class="pb-12 pt-6 max-w-[98%] mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Filtros -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 print:hidden">
            @include('academico.reportes.partials.filtros-rendimiento')
        </div>

        <!-- Encabezado institucional -->
        <div class="bg-white p-6 border-2 border-black text-center print:border print:border-black rounded-3xl print:rounded-none">
            <p class="font-black uppercase text-sm tracking-wide">MINISTERIO DE EDUCACIÓN — DEPARTAMENTO DE ESTADÍSTICA MUNICIPAL LEÓN</p>
            <p class="font-bold text-sm text-slate-700 mt-1">FORMATO DE REND. GENERAL Y ESCALA POR ESCUELA — {{ $corte ? $corte->numero . '° CORTE' : 'AÑO COMPLETO' }} {{ date('Y') }}</p>
            <p class="font-bold text-sm mt-1">Colegio Cristiano en Nicaragua{{ $modalidad ? ' — ' . $modalidad : '' }}</p>
        </div>

        @php
            // Grupos de columnas del formato RENDIMIENTO (etiqueta ⇒ prefijo/par en $fila)
            $gruposRendimiento = [
                'MI' => 'mi',
                'MA' => 'ma',
                '% DE PERM' => 'perm',
                'APROB' => 'aprob',
                '% DE APROB' => 'aprob_pct',
                'APLAZ 1-2' => 'aplaz12',
                'APLAZ 3-MAS' => 'aplaz3',
                'EVALUADO PERIODO OFICIAL' => 'eval_oficial',
                'EVALUADO PERIODO EXTRA' => 'eval_extra',
                'NO EVALUADO / SIN EVALUAC' => 'no_evaluado',
                'ASIST REFORZ' => 'asist_ref',
                'MAEST INT REF' => 'maest_ref',
            ];
            // Grupos de la tabla ESCALA
            $gruposEscala = [
                'MI' => 'mi',
                'MA' => 'ma',
                'APROB' => 'aprob',
                'AA' => 'aa',
                'AS' => 'as',
                'AE' => 'ae',
                'AI' => 'ai',
                'VALOR' => 'valor',
            ];
        @endphp

        @forelse($secciones as $seccion)
            <!-- ============ RENDIMIENTO ACADÉMICO GENERAL — {{ $seccion['modalidad'] }} ============ -->
            <div class="bg-white border-2 border-black overflow-x-auto rounded-3xl print:border print:border-black print:rounded-none">
                <p class="text-center font-black text-xs uppercase tracking-widest py-2 border-b-2 border-black bg-slate-50">
                    Rendimiento Académico General — Formato {{ $seccion['modalidad'] }}
                </p>
                <table class="min-w-full border-collapse text-[10px] font-bold">
                    <thead>
                        <tr class="text-center">
                            <th rowspan="2" class="border border-black px-2 py-2">{{ $seccion['modalidad'] === 'Preescolar Regular' ? 'NIVEL' : 'GRADO' }}</th>
                            @foreach($gruposRendimiento as $etiqueta => $prefijo)
                                <th colspan="2" class="border border-black px-1 py-1">{{ $etiqueta }}</th>
                            @endforeach
                        </tr>
                        <tr class="text-center text-[9px]">
                            @foreach($gruposRendimiento as $etiqueta => $prefijo)
                                <th class="border border-black px-1 py-1 w-8">AS</th>
                                <th class="border border-black px-1 py-1 w-8">F</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach($seccion['filas'] as $fila)
                            <tr>
                                <td class="border border-black px-2 py-1 font-black">{{ $fila['grado'] }}</td>
                                @foreach($gruposRendimiento as $etiqueta => $prefijo)
                                    <td class="border border-black px-1">{{ $prefijo === 'perm' || $prefijo === 'aprob_pct' ? $fila["{$prefijo}_as"] . '%' : $fila["{$prefijo}_as"] }}</td>
                                    <td class="border border-black px-1">{{ $prefijo === 'perm' || $prefijo === 'aprob_pct' ? $fila["{$prefijo}_f"] . '%' : $fila["{$prefijo}_f"] }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="bg-slate-100 font-black">
                            <td class="border border-black px-2 py-1">TOTAL</td>
                            @foreach($gruposRendimiento as $etiqueta => $prefijo)
                                <td class="border border-black px-1">{{ $prefijo === 'perm' || $prefijo === 'aprob_pct' ? $seccion['total']["{$prefijo}_as"] . '%' : $seccion['total']["{$prefijo}_as"] }}</td>
                                <td class="border border-black px-1">{{ $prefijo === 'perm' || $prefijo === 'aprob_pct' ? $seccion['total']["{$prefijo}_f"] . '%' : $seccion['total']["{$prefijo}_f"] }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ============ ESCALA DE RENDIMIENTO — {{ $seccion['modalidad'] }} ============ -->
            <div class="bg-white border-2 border-black overflow-x-auto rounded-3xl print:border print:border-black print:rounded-none">
                <p class="text-center font-black text-xs uppercase tracking-widest py-2 border-b-2 border-black bg-slate-50">
                    Escala de Rendimiento — Formato Escala {{ $seccion['modalidad'] }}
                </p>
                <table class="min-w-full border-collapse text-[10px] font-bold">
                    <thead>
                        <tr class="text-center">
                            <th rowspan="2" class="border border-black px-2 py-2">{{ $seccion['modalidad'] === 'Preescolar Regular' ? 'NIVEL' : 'GRADO' }}</th>
                            @foreach($gruposEscala as $etiqueta => $prefijo)
                                <th colspan="2" class="border border-black px-1 py-1">{{ $etiqueta }}</th>
                            @endforeach
                        </tr>
                        <tr class="text-center text-[9px]">
                            @foreach($gruposEscala as $etiqueta => $prefijo)
                                <th class="border border-black px-1 py-1 w-8">AS</th>
                                <th class="border border-black px-1 py-1 w-8">F</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach($seccion['filas'] as $fila)
                            <tr>
                                <td class="border border-black px-2 py-1 font-black">{{ $fila['grado'] }}</td>
                                @foreach($gruposEscala as $etiqueta => $prefijo)
                                    <td class="border border-black px-1">{{ $fila["{$prefijo}_as"] }}</td>
                                    <td class="border border-black px-1">{{ $fila["{$prefijo}_f"] }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="bg-slate-100 font-black">
                            <td class="border border-black px-2 py-1">TOTAL</td>
                            @foreach($gruposEscala as $etiqueta => $prefijo)
                                <td class="border border-black px-1">{{ $seccion['total']["{$prefijo}_as"] }}</td>
                                <td class="border border-black px-1">{{ $seccion['total']["{$prefijo}_f"] }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        @empty
            <div class="bg-white border-2 border-black rounded-3xl p-10 text-center text-slate-500 font-bold">
                No hay datos para los filtros seleccionados.
            </div>
        @endforelse

        <p class="text-[10px] text-slate-500 text-center print:hidden">
            Escala de logro MINED 2026: AA (90-100) · AS (76-89) · AE (60-75) · AI (0-59) ·
            Las columnas ASIST REFORZ y MAEST INT REF se muestran en 0 (sin módulo de reforzamiento en el sistema).
            Documento generado el {{ now()->format('d/m/Y') }} — Departamento de Estadística Municipal (León).
        </p>
    </div>
</x-app-layout>
