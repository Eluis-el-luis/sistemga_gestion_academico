{{-- resources/views/academico/reportes/mined.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Reporte MINED · Consolidado General</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ route('academico.reportes.mined') }}" class="w-64">
                    <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                        @foreach ($anios ?? [] as $an)
                            <option value="{{ $an->id }}" @selected($an->id == ($anioId ?? null))>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-stone-300 text-neutral-900 overflow-x-auto">
                <table class="w-full text-center border-collapse border border-neutral-800 text-xs">
                    <thead>
                        <tr class="bg-stone-100 font-bold uppercase">
                            <th rowspan="2" class="border border-neutral-800 px-3 py-2 text-left">Modalidad</th>
                            <th colspan="2" class="border border-neutral-800 px-2 py-1">MI</th>
                            <th colspan="2" class="border border-neutral-800 px-2 py-1">MA</th>
                            <th colspan="2" class="border border-neutral-800 px-2 py-1">Retirados</th>
                            <th rowspan="2" class="border border-neutral-800 px-2 py-2">% Retención</th>
                            <th rowspan="2" class="border border-neutral-800 px-2 py-2">Promedio</th>
                        </tr>
                        <tr class="bg-stone-100 text-[10px]">
                            @for ($i = 0; $i < 3; $i++)
                                <th class="border border-neutral-800 px-2 py-0.5">AS</th><th class="border border-neutral-800 px-2 py-0.5">F</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($filas ?? [] as $fila)
                            <tr>
                                <td class="border border-neutral-800 px-3 py-2 font-semibold text-left">{{ $fila['modalidad'] }}</td>
                                <td class="border border-neutral-800 px-2 py-2">{{ $fila['mi_as'] }}</td><td class="border border-neutral-800 px-2 py-2">{{ $fila['mi_f'] }}</td>
                                <td class="border border-neutral-800 px-2 py-2 font-bold">{{ $fila['ma_as'] }}</td><td class="border border-neutral-800 px-2 py-2 font-bold">{{ $fila['ma_f'] }}</td>
                                <td class="border border-neutral-800 px-2 py-2 text-rose-700">{{ $fila['ret_as'] }}</td><td class="border border-neutral-800 px-2 py-2 text-rose-700">{{ $fila['ret_f'] }}</td>
                                <td class="border border-neutral-800 px-2 py-2 font-bold">{{ $fila['retencion'] }}%</td>
                                <td class="border border-neutral-800 px-2 py-2 font-bold">{{ $fila['promedio'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="border border-neutral-800 px-4 py-8 text-center text-neutral-500">No hay datos para el ciclo seleccionado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>