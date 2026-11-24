{{-- resources/views/academico/reportes/asignaturas-aprobadas.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Reporte MINED · Asignaturas Aprobadas</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ route('academico.reportes.asignaturas-aprobadas') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Año Escolar</label>
                        <select name="anio_escolar_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Seleccione</option>
                            @foreach ($anios ?? [] as $an)
                                <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{ $an->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Modalidad</label>
                        <select name="modalidad_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Seleccione</option>
                            @foreach ($modalidades ?? [] as $mod)
                                <option value="{{ $mod->id }}" @selected($mod->id == request('modalidad_id'))>{{ $mod->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Corte Evaluativo</label>
                        <select name="corte_evaluativo_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            @foreach ($cortes ?? [] as $cort)
                                <option value="{{ $cort->id }}" @selected($cort->id == request('corte_evaluativo_id'))>{{ $cort->descripcion ?? $cort->nombre ?? ($cort->numero . ' Corte') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 px-5 py-2.5 rounded-xl font-bold text-xs uppercase w-full cursor-pointer">Generar Reporte</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-stone-300 text-neutral-900 overflow-x-auto">
                <div class="text-center mb-4">
                    <h1 class="text-xs font-bold uppercase">Ministerio de Educación · Delegación Municipal León</h1>
                    <p class="text-xs font-semibold uppercase mt-1">Asignaturas Aprobadas · {{ $nombreCorte ?? '' }} · {{ $anioActual ?? date('Y') }}</p>
                </div>
                <table class="w-full text-center border-collapse border border-neutral-800 text-[10px]">
                    <thead>
                        <tr class="bg-stone-100 font-bold uppercase">
                            <th rowspan="2" class="border border-neutral-800 px-2 py-1">Grado</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">MI</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">MA</th>
                            <th colspan="2" class="border border-neutral-800 px-1 py-1">Aprob. Todas</th>
                            @foreach ($listaAsignaturas ?? [] as $asig)
                                <th colspan="2" class="border border-neutral-800 px-1 py-1">{{ \Illuminate\Support\Str::limit($asig->nombre, 10, '.') }}</th>
                            @endforeach
                        </tr>
                        <tr class="bg-stone-100 text-[9px]">
                            <th class="border border-neutral-800 px-1 py-0.5">AS</th><th class="border border-neutral-800 px-1 py-0.5">F</th>
                            <th class="border border-neutral-800 px-1 py-0.5">AS</th><th class="border border-neutral-800 px-1 py-0.5">F</th>
                            <th class="border border-neutral-800 px-1 py-0.5">AS</th><th class="border border-neutral-800 px-1 py-0.5">F</th>
                            @foreach ($listaAsignaturas ?? [] as $asig)
                                <th class="border border-neutral-800 px-1 py-0.5">AP</th><th class="border border-neutral-800 px-1 py-0.5">F</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($filas ?? [] as $fila)
                            <tr>
                                <td class="border border-neutral-800 px-2 py-1 font-bold text-left">{{ $fila['grado'] }}</td>
                                <td class="border border-neutral-800 px-1 py-1">{{ $fila['mi_as'] }}</td><td class="border border-neutral-800 px-1 py-1">{{ $fila['mi_f'] }}</td>
                                <td class="border border-neutral-800 px-1 py-1">{{ $fila['ma_as'] }}</td><td class="border border-neutral-800 px-1 py-1">{{ $fila['ma_f'] }}</td>
                                <td class="border border-neutral-800 px-1 py-1 font-bold">{{ $fila['aprob_todas_as'] }}</td><td class="border border-neutral-800 px-1 py-1 font-bold">{{ $fila['aprob_todas_f'] }}</td>
                                @foreach ($listaAsignaturas ?? [] as $asig)
                                    @php $mat = $fila['por_asignatura'][$asig->id] ?? ['as' => 0, 'f' => 0]; @endphp
                                    <td class="border border-neutral-800 px-1 py-1">{{ $mat['as'] }}</td>
                                    <td class="border border-neutral-800 px-1 py-1">{{ $mat['f'] }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="100%" class="border border-neutral-800 px-4 py-6 text-center text-neutral-500">Selecciona Año Escolar, Modalidad y Corte para generar el reporte.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>