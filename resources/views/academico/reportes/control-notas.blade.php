{{-- resources/views/academico/reportes/control-notas.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Control de Ingreso de Calificaciones</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Supervisión y auditoría por asignación</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white px-3 py-2 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">Imprimir / PDF</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                @include('academico.reportes.partials.filtros-notas', ['ruta' => 'academico.reportes.control-notas', 'mostrarEstado' => true])
            </div>

            <x-membrete-impresion titulo="Acta de Supervisión y Control de Calificaciones" subtitulo="Colegio Cristiano en Nicaragua" badge="Ciclo {{ date('Y') }}" orientacion="landscape" />

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3.5 text-center w-10">#</th>
                                <th class="px-4 py-3.5">Grado y Sección</th>
                                <th class="px-4 py-3.5">Asignatura</th>
                                <th class="px-4 py-3.5">Docente</th>
                                <th class="px-3 py-3.5 text-center">Ingresadas</th>
                                <th class="px-3 py-3.5 text-center">Faltantes</th>
                                <th class="px-4 py-3.5 text-center">Acta</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($filas ?? [] as $index => $fila)
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40">
                                    <td class="px-4 py-3 text-center text-neutral-400">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ $fila['grado'] }} Sec. "{{ $fila['aula'] }}"</td>
                                    <td class="px-4 py-3 font-semibold">{{ $fila['asignatura'] }}</td>
                                    <td class="px-4 py-3 text-neutral-600 dark:text-neutral-300">{{ $fila['docente'] }}</td>
                                    <td class="px-3 py-3 text-center font-bold text-emerald-600">{{ $fila['registradas'] }}</td>
                                    <td class="px-3 py-3 text-center font-bold {{ ($fila['pendientes'] ?? 0) > 0 ? 'text-rose-600' : 'text-neutral-400' }}">{{ $fila['pendientes'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ !empty($fila['cerrado']) ? 'bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200' : 'bg-emerald-50 text-emerald-700' }}">
                                            {{ !empty($fila['cerrado']) ? 'Cerrada' : 'Abierta' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-10 text-center text-neutral-500">No hay registros para los filtros seleccionados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>