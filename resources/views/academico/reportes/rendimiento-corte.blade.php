{{-- resources/views/academico/reportes/rendimiento-corte.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Rendimiento Académico General</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                @include('academico.reportes.partials.filtros-rendimiento')
            </div>

            <div class="bg-white p-6 rounded-3xl border border-stone-300 text-neutral-900 overflow-x-auto">
                <div class="text-center mb-4">
                    <h1 class="text-sm font-bold uppercase">Rendimiento Académico General · {{ $nombreCorte ?? '' }} · {{ $anioActual ?? date('Y') }}</h1>
                </div>

                <table class="w-full text-center border-collapse border border-neutral-800 text-[9px] mb-6">
                    <thead>
                        <tr class="bg-stone-100 font-bold uppercase">
                            <th rowspan="2" class="border border-neutral-800 px-1.5 py-1">Grado</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">MI</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">MA</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">% Perm</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">Aprob</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">% Aprob</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">Aplaz 1-2</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">Aplaz 3+</th>
                        </tr>
                        <tr class="bg-stone-100 text-[8px]">
                            @for ($i = 0; $i < 7; $i++)
                                <th class="border border-neutral-800 px-1 py-0.5">AS</th><th class="border border-neutral-800 px-1 py-0.5">F</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($filas ?? [] as $fila)
                            <tr>
                                <td class="border border-neutral-800 px-1.5 py-1 font-bold text-left">{{ $fila['grado'] }}</td>
                                <td class="border border-neutral-800 px-1 py-1">{{ $fila['mi_as'] }}</td><td class="border border-neutral-800 px-1 py-1">{{ $fila['mi_f'] }}</td>
                                <td class="border border-neutral-800 px-1 py-1">{{ $fila['ma_as'] }}</td><td class="border border-neutral-800 px-1 py-1">{{ $fila['ma_f'] }}</td>
                                <td class="border border-neutral-800 px-1 py-1">{{ $fila['perm_as'] }}%</td><td class="border border-neutral-800 px-1 py-1">{{ $fila['perm_f'] }}%</td>
                                <td class="border border-neutral-800 px-1 py-1">{{ $fila['aprob_as'] }}</td><td class="border border-neutral-800 px-1 py-1">{{ $fila['aprob_f'] }}</td>
                                <td class="border border-neutral-800 px-1 py-1">{{ $fila['porc_aprob_as'] }}%</td><td class="border border-neutral-800 px-1 py-1">{{ $fila['porc_aprob_f'] }}%</td>
                                <td class="border border-neutral-800 px-1 py-1">{{ $fila['aplaz1_2_as'] }}</td><td class="border border-neutral-800 px-1 py-1">{{ $fila['aplaz1_2_f'] }}</td>
                                <td class="border border-neutral-800 px-1 py-1">{{ $fila['aplaz3_mas_as'] }}</td><td class="border border-neutral-800 px-1 py-1">{{ $fila['aplaz3_mas_f'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="15" class="border border-neutral-800 px-4 py-6 text-center text-neutral-500">Selecciona Año Escolar, Modalidad y Corte para generar el reporte.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                @if (isset($chartData) && !empty($chartData['labels']))
                    <div class="w-full max-w-4xl mx-auto h-[260px]">
                        <canvas id="graficaMined"></canvas>
                    </div>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const canvas = document.getElementById('graficaMined');
                            if (!canvas || typeof Chart === 'undefined') return;
                            new Chart(canvas.getContext('2d'), {
                                type: 'line',
                                data: {
                                    labels: @json($chartData['labels']),
                                    datasets: [
                                        { type: 'bar', label: '% Aprobados', data: @json($chartData['aprobados']), backgroundColor: '#e6ac27' },
                                        { type: 'line', label: '% Retención', data: @json($chartData['retencion']), borderColor: '#262626', backgroundColor: '#262626', borderWidth: 2, fill: false }
                                    ]
                                },
                                options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 100 } } }
                            });
                        });
                    </script>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>