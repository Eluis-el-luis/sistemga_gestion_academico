<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Notas Globales</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            @include('academico.reportes.partials.filtros-notas', ['ruta' => 'academico.reportes.notas-globales'])
            <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-4 py-3 text-left sticky left-0 bg-[#FFFDF5] z-10">#</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Estudiante</th>
                            <th class="px-4 py-3 text-left whitespace-nowrap">Grado</th>
                            @foreach($columnas as $col)
                                <th class="px-1 py-3 text-center whitespace-nowrap" title="{{ $col->nombre }}">
                                    <span class="block max-w-[90px] truncate">{{ $col->nombre }}</span>
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-center bg-slate-100/50 whitespace-nowrap">Nota Final</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($filas as $index => $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-bold text-slate-400 sticky left-0 bg-white z-10">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-bold text-[#3d2c1d] whitespace-nowrap">
                                    {{ $fila['alumno'] }}
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase">CUP: {{ $fila['cup'] }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $fila['grado'] }}</td>

                                @php $suma = 0; $contados = 0; @endphp
                                @foreach($columnas as $col)
                                    @php
                                        $valor = $fila['notas'][$col->id] ?? null;
                                        if (!is_null($valor)) { $suma += $valor; $contados++; }
                                    @endphp
                                    <td class="px-1 py-3 text-center font-bold {{ is_null($valor) ? 'text-slate-300' : ($valor < 60 ? 'text-rose-600' : 'text-[#3d2c1d]') }}">
                                        {{ is_null($valor) ? '—' : $valor }}
                                    </td>
                                @endforeach

                                <td class="px-4 py-3 text-center font-black bg-slate-50/50 {{ $contados > 0 && (round($suma / $contados, 1) < 60) ? 'text-rose-600' : 'text-[#3d2c1d]' }}">
                                    {{ $contados > 0 ? number_format(round($suma / $contados, 1), 1) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($columnas) + 4 }}" class="px-6 py-12 text-center text-stone-500 font-bold">No hay notas registradas con los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>