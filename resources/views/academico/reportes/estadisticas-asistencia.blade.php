{{-- resources/views/academico/reportes/estadisticas-asistencia.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Estadísticas por Fechas</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Promedio del rango: {{ $promedio_rango ?? 0 }}%</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white px-3 py-2 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                @include('academico.reportes.partials.filtros-asistencia', ['ruta' => 'academico.reportes.estadisticas-asistencia'])
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-5 py-3.5">Fecha</th>
                                <th class="px-5 py-3.5 text-center">Total</th>
                                <th class="px-5 py-3.5 text-center">Presentes</th>
                                <th class="px-5 py-3.5 text-center">Ausencias</th>
                                <th class="px-5 py-3.5 text-center">Justificadas</th>
                                <th class="px-5 py-3.5 text-center">% Asistencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($filas ?? [] as $fila)
                                <tr>
                                    <td class="px-5 py-3.5 font-semibold">{{ \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y') }}</td>
                                    <td class="px-5 py-3.5 text-center">{{ $fila['total'] }}</td>
                                    <td class="px-5 py-3.5 text-center font-bold text-emerald-600">{{ $fila['presentes'] }}</td>
                                    <td class="px-5 py-3.5 text-center font-bold text-rose-600">{{ $fila['ausentes'] }}</td>
                                    <td class="px-5 py-3.5 text-center font-semibold text-amber-600">{{ $fila['justificadas'] }}</td>
                                    <td class="px-5 py-3.5 text-center font-bold">{{ $fila['porcentaje'] }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-10 text-center text-neutral-500">No hay registros en el rango seleccionado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>