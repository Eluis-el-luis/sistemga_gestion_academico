{{-- resources/views/academico/reportes/estudiantes.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Auditoría de Matrícula</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white px-3 py-2 cursor-pointer">Volver</a>
                <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ route('academico.reportes.estudiantes') }}" class="w-64">
                    <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                        @foreach ($anios ?? [] as $an)
                            <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id', $datos['anio']->id ?? null))>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-stone-200 dark:border-neutral-800 p-5">
                    <span class="text-xs font-semibold text-neutral-400 uppercase">Total Histórico</span>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-white mt-1">{{ $datos['total_alumnos'] ?? 0 }}</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-stone-200 dark:border-neutral-800 p-5">
                    <span class="text-xs font-semibold text-emerald-600 uppercase">Activos</span>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $datos['matriculados'] ?? 0 }}</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-stone-200 dark:border-neutral-800 p-5">
                    <span class="text-xs font-semibold text-rose-600 uppercase">Retirados</span>
                    <p class="text-2xl font-bold text-rose-600 mt-1">{{ $datos['retirados'] ?? 0 }}</p>
                </div>
                <div class="bg-white dark:bg-neutral-900 rounded-2xl border border-stone-200 dark:border-neutral-800 p-5">
                    <span class="text-xs font-semibold text-amber-600 uppercase">Incompletos</span>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $datos['expedientes_incompletos'] ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-5 py-3.5">CUP</th>
                                <th class="px-5 py-3.5">Estudiante</th>
                                <th class="px-5 py-3.5">Grado y Sección</th>
                                <th class="px-5 py-3.5">Datos Faltantes</th>
                                <th class="px-5 py-3.5">Teléfono</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($datos['lista_incompletos'] ?? [] as $fila)
                                <tr>
                                    <td class="px-5 py-3 font-mono text-xs text-neutral-500">{{ $fila['cup'] }}</td>
                                    <td class="px-5 py-3 font-semibold">{{ $fila['alumno'] }}</td>
                                    <td class="px-5 py-3">{{ $fila['grado_aula'] }}</td>
                                    <td class="px-5 py-3 text-xs font-semibold text-amber-600">{{ $fila['faltantes'] }}</td>
                                    <td class="px-5 py-3 text-xs">{{ $fila['telefono'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-neutral-500">Todos los expedientes activos están completos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>