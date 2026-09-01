<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012-2m-6 9l2 2 4-4"></path></svg>
                {{ __('Control de Ingreso de Notas') }}
            </h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <form method="GET" class="flex items-end gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
                        @foreach($anios as $an)
                            <option value="{{ $an->id }}" @selected($an->id == ($anio->id ?? null))>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" onclick="window.print()" class="ml-auto bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Aula</th>
                            <th class="px-6 py-4 text-left">Grado</th>
                            <th class="px-6 py-4 text-left">Asignatura</th>
                            <th class="px-6 py-4 text-left">Docente</th>
                            <th class="px-6 py-4 text-center">Registradas</th>
                            <th class="px-6 py-4 text-center">Pendientes</th>
                            <th class="px-6 py-4 text-center">Avance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($filas as $fila)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $fila['aula'] }}</td>
                                <td class="px-6 py-4">{{ $fila['grado'] }}</td>
                                <td class="px-6 py-4">{{ $fila['asignatura'] }}</td>
                                <td class="px-6 py-4 {{ $fila['docente'] === 'Sin asignar' ? 'text-rose-500 font-bold' : '' }}">{{ $fila['docente'] }}</td>
                                <td class="px-6 py-4 text-center font-bold text-emerald-600">{{ $fila['registradas'] }}</td>
                                <td class="px-6 py-4 text-center font-bold {{ $fila['pendientes'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $fila['pendientes'] }}</td>
                                <td class="px-6 py-4">
                                    <div class="w-28 bg-slate-200 rounded-full h-2 mx-auto">
                                        <div class="h-2 rounded-full {{ $fila['porcentaje'] == 100 ? 'bg-emerald-500' : 'bg-[#e6ac27]' }}" style="width: {{ $fila['porcentaje'] }}%"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500">{{ $fila['porcentaje'] }}%</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center text-stone-500 font-bold">No hay asignaciones para el año seleccionado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>