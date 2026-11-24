{{-- resources/views/academico/reportes/mined-detallado.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Formatos Oficiales MINED</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Tipo</label>
                        <select name="tipo" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="aprobados" @selected(request('tipo') == 'aprobados')>Aprobados</option>
                            <option value="1_2" @selected(request('tipo') == '1_2')>Aplazados 1 a 2</option>
                            <option value="3_mas" @selected(request('tipo') == '3_mas')>Aplazados 3 o más</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Corte Evaluativo</label>
                        <select name="corte_evaluativo_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            @foreach ($cortes ?? [] as $corte)
                                <option value="{{ $corte->id }}" @selected($corte->id == request('corte_evaluativo_id'))>{{ $corte->nombre ?? ('Corte ' . $corte->numero) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Grado</label>
                        @if (!empty($esDocenteGuia) && !empty($aulaGuia))
                            <div class="w-full bg-stone-100 dark:bg-neutral-800 border border-stone-200 dark:border-neutral-700 rounded-xl px-4 py-2.5 text-xs font-semibold">{{ $aulaGuia->grado?->nombre ?? '' }} "{{ $aulaGuia->nombre ?? '' }}"</div>
                            <input type="hidden" name="grado_id" value="{{ $aulaGuia->grado_id }}">
                        @else
                            <select name="grado_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                                <option value="">Todo el Colegio</option>
                                @foreach ($grados ?? [] as $grado)
                                    <option value="{{ $grado->id }}" @selected($grado->id == request('grado_id'))>{{ $grado->nombre }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 px-5 py-2.5 rounded-xl font-bold text-xs uppercase w-full cursor-pointer">Generar Reporte</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-stone-300 text-neutral-900 overflow-x-auto">
                <table class="min-w-full border-collapse border border-neutral-800 text-xs">
                    <thead>
                        <tr class="bg-stone-100 font-bold uppercase text-center">
                            <th class="border border-neutral-800 p-2 w-10">#</th>
                            <th class="border border-neutral-800 p-2">Modalidad</th>
                            <th class="border border-neutral-800 p-2">Nivel</th>
                            <th class="border border-neutral-800 p-2">Turno</th>
                            <th class="border border-neutral-800 p-2">Código</th>
                            <th class="border border-neutral-800 p-2 text-left">Nombre Completo</th>
                            <th class="border border-neutral-800 p-2">Sexo</th>
                            @if (($tipo ?? 'aprobados') !== 'aprobados')
                                <th class="border border-neutral-800 p-2">Asignaturas</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($filas ?? [] as $fila)
                            <tr>
                                <td class="border border-neutral-800 p-1.5 text-center">{{ $fila['n'] }}</td>
                                <td class="border border-neutral-800 p-1.5 text-center">{{ $fila['modalidad'] }}</td>
                                <td class="border border-neutral-800 p-1.5 text-center">{{ $fila['nivel'] }}</td>
                                <td class="border border-neutral-800 p-1.5 text-center">{{ $fila['turno'] }}</td>
                                <td class="border border-neutral-800 p-1.5 text-center font-mono">{{ $fila['codigo'] }}</td>
                                <td class="border border-neutral-800 p-1.5 font-semibold uppercase">{{ $fila['nombre_completo'] }}</td>
                                <td class="border border-neutral-800 p-1.5 text-center font-bold">{{ $fila['sexo'] }}</td>
                                @if (($tipo ?? 'aprobados') !== 'aprobados')
                                    <td class="border border-neutral-800 p-1.5 text-center font-semibold text-rose-700">{{ $fila['asignaturas'] }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ ($tipo ?? 'aprobados') !== 'aprobados' ? 8 : 7 }}" class="border border-neutral-800 p-6 text-center text-neutral-500">No hay registros para los criterios seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>