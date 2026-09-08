<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Asistencia por Sección (Día)</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <form method="GET" action="{{ route('academico.reportes.asistencia-seccion-dia') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Aula / Sección</label>
                    <select name="aula_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
                        <option value="">Todas</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}" @selected($aula->id == request('aula_id'))>{{ $aula->nombre }} - {{ $aula->grado->nombre ?? '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Fecha</label>
                    <input type="date" name="fecha" value="{{ request('fecha', now()->toDateString()) }}" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm">
                </div>
                <button type="submit" class="self-end bg-[#3d2c1d] text-white font-black px-5 py-2 rounded-xl shadow-sm text-sm">Filtrar</button>
                <a href="{{ route('academico.reportes.asistencia-seccion-dia') }}" class="self-end text-center text-sm font-bold text-slate-400 hover:text-rose-600 py-2">Limpiar</a>
            </form>
            <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Aula</th>
                            <th class="px-6 py-4 text-left">Turno</th>
                            <th class="px-6 py-4 text-left">Grado</th>
                            <th class="px-6 py-4 text-center">Total</th>
                            <th class="px-6 py-4 text-center">Presentes</th>
                            <th class="px-6 py-4 text-center">Ausentes</th>
                            <th class="px-6 py-4 text-center">Justificadas</th>
                            <th class="px-6 py-4 text-center">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($filas as $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $fila['aula'] }}</td>
                                <td class="px-6 py-4">{{ $fila['turno'] }}</td>
                                <td class="px-6 py-4">{{ $fila['grado'] }}</td>
                                <td class="px-6 py-4 text-center font-bold">{{ $fila['total'] }}</td>
                                <td class="px-6 py-4 text-center text-emerald-600 font-bold">{{ $fila['presentes'] }}</td>
                                <td class="px-6 py-4 text-center text-rose-600 font-bold">{{ $fila['ausentes'] }}</td>
                                <td class="px-6 py-4 text-center text-amber-600 font-bold">{{ $fila['justificadas'] }}</td>
                                <td class="px-6 py-4 text-center font-black">{{ $fila['porcentaje'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-12 text-center text-stone-500 font-bold">Sin datos para la sección seleccionada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>