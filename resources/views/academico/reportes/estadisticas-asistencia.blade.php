<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Estadísticas de Asistencia</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            @include('academico.reportes.partials.filtros-asistencia', ['ruta' => 'academico.reportes.estadisticas-asistencia'])
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Aula</th>
                            <th class="px-6 py-4 text-left">Grado</th>
                            <th class="px-6 py-4 text-left">Modalidad</th>
                            <th class="px-6 py-4 text-center">Registros</th>
                            <th class="px-6 py-4 text-center">Presentes</th>
                            <th class="px-6 py-4 text-center">Ausentes</th>
                            <th class="px-6 py-4 text-center">Justificadas</th>
                            <th class="px-6 py-4 text-center">% Asistencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($filas as $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $fila['aula'] }}</td>
                                <td class="px-6 py-4">{{ $fila['grado'] }}</td>
                                <td class="px-6 py-4">{{ $fila['modalidad'] }}</td>
                                <td class="px-6 py-4 text-center font-bold">{{ $fila['total_registros'] }}</td>
                                <td class="px-6 py-4 text-center text-emerald-600 font-bold">{{ $fila['presentes'] }}</td>
                                <td class="px-6 py-4 text-center text-rose-600 font-bold">{{ $fila['ausentes'] }}</td>
                                <td class="px-6 py-4 text-center text-amber-600 font-bold">{{ $fila['justificadas'] }}</td>
                                <td class="px-6 py-4 text-center font-black">{{ $fila['porcentaje'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-12 text-center text-stone-500 font-bold">No hay registros en el rango seleccionado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>