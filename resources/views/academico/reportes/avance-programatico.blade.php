{{-- resources/views/academico/reportes/avance-programatico.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Avance Programático</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ route('academico.reportes.avance-programatico') }}" class="w-64">
                    <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                        @foreach ($anios ?? [] as $an)
                            <option value="{{ $an->id }}" @selected($an->id == ($anioId ?? null))>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-stone-300 text-neutral-900 space-y-6">
                @forelse ($gruposModalidades ?? [] as $modalidad => $asignaturas)
                    <div>
                        <h4 class="font-bold text-xs uppercase px-3 py-1.5 bg-stone-100 border border-neutral-800">Modalidad: {{ $modalidad }}</h4>
                        <table class="w-full border-collapse border border-neutral-800 text-xs">
                            <thead>
                                <tr class="font-bold uppercase text-left">
                                    <th class="border border-neutral-800 px-3 py-2">Grado / Sección</th>
                                    <th class="border border-neutral-800 px-3 py-2">Asignatura</th>
                                    <th class="border border-neutral-800 px-3 py-2">Unidades</th>
                                    <th class="border border-neutral-800 px-3 py-2 text-center w-24">% Avance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($asignaturas as $item)
                                    <tr>
                                        <td class="border border-neutral-800 px-3 py-1.5 font-semibold">{{ $item['grado'] }}</td>
                                        <td class="border border-neutral-800 px-3 py-1.5">{{ $item['asignatura'] }}</td>
                                        <td class="border border-neutral-800 px-3 py-1.5 text-neutral-600">{{ $item['unidades'] }}</td>
                                        <td class="border border-neutral-800 px-3 py-1.5 text-center font-bold">{{ $item['porcentaje'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <p class="text-center text-neutral-500 text-sm py-6">No hay avances programáticos registrados para este ciclo.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>