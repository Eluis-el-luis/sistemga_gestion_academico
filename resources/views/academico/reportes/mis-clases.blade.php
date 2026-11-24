{{-- resources/views/academico/reportes/mis-clases.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Centro de Reportes: <span class="text-[#c99116] dark:text-[#e6ac27]">Mis Clases</span></h2>
            <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white cursor-pointer">Volver</a>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ route('academico.reportes.mis-clases') }}" class="flex flex-col sm:flex-row items-end gap-4 text-sm">
                    <div class="w-full sm:w-1/2">
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">1. Clase</label>
                        <select name="asignacion_id" required class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Elige una asignatura...</option>
                            @foreach ($asignaciones ?? [] as $asig)
                                <option value="{{ $asig->id }}" @selected($asignacionId == $asig->id)>{{ $asig->aula?->grado?->nombre }} "{{ $asig->aula?->nombre }}" - {{ $asig->asignatura?->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-1/3">
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">2. Periodo (Opcional)</label>
                        <select name="corte_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="">Promedio General</option>
                            @foreach ($cortes ?? [] as $corte)
                                <option value="{{ $corte->id }}" @selected($corteId == $corte->id)>{{ $corte->descripcion ?? $corte->nombre ?? ('Corte ' . $corte->numero) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase cursor-pointer">Generar</button>
                        @if ($asignacionId)
                            <button type="button" onclick="window.print()" class="bg-[#e6ac27] text-neutral-950 font-bold px-4 py-2.5 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
                        @endif
                    </div>
                </form>
            </div>

            @if ($asignacionId && $asignacionSeleccionada)
                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                            <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-5 py-3.5 text-center w-12">#</th>
                                    <th class="px-5 py-3.5">CUP</th>
                                    <th class="px-5 py-3.5">Estudiante</th>
                                    <th class="px-5 py-3.5 text-center">Calificación</th>
                                    <th class="px-5 py-3.5 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                @forelse ($alumnosReporte ?? [] as $index => $alumno)
                                    <tr>
                                        <td class="px-5 py-3 text-center text-neutral-400">{{ $index + 1 }}</td>
                                        <td class="px-5 py-3 font-mono text-xs text-neutral-500">{{ $alumno['cup'] ?? 'S/C' }}</td>
                                        <td class="px-5 py-3 font-semibold">{{ $alumno['nombre_completo'] }}</td>
                                        <td class="px-5 py-3 text-center font-bold">{{ $alumno['nota'] ?? '-' }}</td>
                                        <td class="px-5 py-3 text-center">
                                            @if (!is_null($alumno['nota']))
                                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $alumno['nota'] >= 60 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                    {{ $alumno['nota'] >= 60 ? 'Aprobado' : 'Reprobado' }}
                                                </span>
                                            @else
                                                <span class="text-xs text-neutral-400">Sin nota</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-10 text-center text-neutral-500">No hay estudiantes activos en esta clase.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>