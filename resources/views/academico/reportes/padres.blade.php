{{-- resources/views/academico/reportes/padres.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Directorio de Responsables</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white px-3 py-2 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Año Escolar</label>
                        <select name="anio_escolar_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            @foreach ($anios ?? [] as $an)
                                <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id', $anio?->id))>{{ $an->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Aula / Sección</label>
                        <select name="aula_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Todo el Colegio</option>
                            @foreach ($aulas ?? [] as $aulaOp)
                                <option value="{{ $aulaOp->id }}" @selected($aulaOp->id == request('aula_id'))>{{ $aulaOp->grado?->nombre ?? '' }} - {{ $aulaOp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 px-5 py-2.5 rounded-xl font-bold text-xs uppercase w-full cursor-pointer">Generar</button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-5 py-3.5">Estudiante</th>
                                <th class="px-5 py-3.5">Grado/Sección</th>
                                <th class="px-5 py-3.5">Madre</th>
                                <th class="px-5 py-3.5">Padre</th>
                                <th class="px-5 py-3.5">Tutor</th>
                                <th class="px-5 py-3.5">Autorizado Retiro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200 text-xs">
                            @forelse ($filas ?? [] as $fila)
                                <tr>
                                    <td class="px-5 py-3 font-semibold text-sm">{{ $fila['estudiante'] }}<br><span class="font-mono text-[10px] text-neutral-400">CUP: {{ $fila['cup'] }}</span></td>
                                    <td class="px-5 py-3">{{ $fila['grado_aula'] }}</td>
                                    <td class="px-5 py-3">{!! $fila['madre'] !!}</td>
                                    <td class="px-5 py-3">{!! $fila['padre'] !!}</td>
                                    <td class="px-5 py-3">{!! $fila['tutor'] !!}</td>
                                    <td class="px-5 py-3">{!! $fila['autorizado'] !!}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-10 text-center text-neutral-500">No hay estudiantes matriculados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>