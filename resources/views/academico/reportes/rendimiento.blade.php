<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                {{ __('Rendimiento Académico') }}
            </h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año</label>
                    <select name="anio_escolar_id" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm">
                        @foreach($anios as $an)
                            <option value="{{ $an->id }}" @selected($an->id == ($anio->id ?? null))>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Modalidad</label>
                    <select name="modalidad_id" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm">
                        <option value="">Todas</option>
                        @foreach($modalidades as $mod)
                            <option value="{{ $mod->id }}" @selected($mod->id == request('modalidad_id'))>{{ $mod->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-[#3d2c1d] text-white font-black px-5 py-2 rounded-xl shadow-sm text-sm">Filtrar</button>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Aula</th>
                            <th class="px-6 py-4 text-left">Grado</th>
                            <th class="px-6 py-4 text-left">Modalidad</th>
                            <th class="px-6 py-4 text-center">Evaluados</th>
                            <th class="px-6 py-4 text-center">Promedio</th>
                            <th class="px-6 py-4 text-center">Aprobados</th>
                            <th class="px-6 py-4 text-center">Reprobados</th>
                            <th class="px-6 py-4 text-center">% Aprobación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($filas as $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $fila['aula'] }}</td>
                                <td class="px-6 py-4">{{ $fila['grado'] }}</td>
                                <td class="px-6 py-4">{{ $fila['modalidad'] }}</td>
                                <td class="px-6 py-4 text-center font-bold">{{ $fila['total_evaluados'] }}</td>
                                <td class="px-6 py-4 text-center font-black">{{ number_format($fila['promedio'], 2) }}</td>
                                <td class="px-6 py-4 text-center text-emerald-600 font-bold">{{ $fila['aprobados'] }}</td>
                                <td class="px-6 py-4 text-center text-rose-600 font-bold">{{ $fila['reprobados'] }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $fila['porcentaje_aprobacion'] >= 90 ? 'bg-emerald-100 text-emerald-700' : ($fila['porcentaje_aprobacion'] >= 70 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                        {{ $fila['porcentaje_aprobacion'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-12 text-center text-stone-500 font-bold">No hay datos de rendimiento.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>