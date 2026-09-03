<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Notas por Asignatura</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            @include('academico.reportes.partials.filtros-notas', ['ruta' => 'academico.reportes.notas-por-asignatura'])
            <button type="button" onclick="window.print()" class="mt-4 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Aula</th>
                            <th class="px-6 py-4 text-left">Grado</th>
                            <th class="px-6 py-4 text-left">Asignatura</th>
                            <th class="px-6 py-4 text-center">Evaluados</th>
                            <th class="px-6 py-4 text-center">Promedio</th>
                            <th class="px-6 py-4 text-center">Aprobados</th>
                            <th class="px-6 py-4 text-center">Reprobados</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($filas as $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $fila['aula'] }}</td>
                                <td class="px-6 py-4">{{ $fila['grado'] }}</td>
                                <td class="px-6 py-4">{{ $fila['asignatura'] }}</td>
                                <td class="px-6 py-4 text-center font-bold">{{ $fila['total'] }}</td>
                                <td class="px-6 py-4 text-center font-black">{{ number_format($fila['promedio'], 2) }}</td>
                                <td class="px-6 py-4 text-center text-emerald-600 font-bold">{{ $fila['aprobados'] }}</td>
                                <td class="px-6 py-4 text-center text-rose-600 font-bold">{{ $fila['reprobados'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center text-stone-500 font-bold">Sin datos para los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>