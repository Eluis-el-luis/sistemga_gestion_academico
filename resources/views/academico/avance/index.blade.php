{{-- resources/views/academico/avance/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Avance de Contenidos</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                <form method="GET" action="{{ route('academico.avance.index') }}" class="max-w-md">
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Asignatura / Clase</label>
                    <select name="asignacion_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                        <option value="">Seleccione...</option>
                        @foreach ($asignaciones as $asig)
                            <option value="{{ $asig->id }}" @selected((int) $asig->id === (int) request('asignacion_id'))>
                                {{ $asig->asignatura?->nombre }} - {{ $asig->aula?->grado?->nombre ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if ($asignacionSeleccionada)
                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-4">Registrar Avance</h3>
                    <form action="{{ route('academico.avance.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        @csrf
                        <input type="hidden" name="aula_asignatura_docente_id" value="{{ $asignacionSeleccionada->id }}">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Mes <span class="text-rose-500">*</span></label>
                            <input type="month" name="mes" value="{{ now()->format('Y-m') }}" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">% Avance <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" name="porcentaje_avance" min="0" max="100" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-semibold" required>
                        </div>
                        <button type="submit" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 px-5 rounded-xl text-sm transition-colors cursor-pointer">Guardar Avance</button>
                    </form>
                </div>

                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-200 dark:divide-neutral-800 text-sm text-left">
                            <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-6 py-4">Mes</th>
                                    <th class="px-6 py-4">% Avance</th>
                                    <th class="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                @forelse ($avances as $avance)
                                    <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40">
                                        <td class="px-6 py-4 font-semibold">{{ \Carbon\Carbon::createFromFormat('Y-m', $avance->mes)->format('m/Y') }}</td>
                                        <td class="px-6 py-4 font-semibold">{{ $avance->porcentaje_avance }}%</td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('academico.avance.destroy', $avance->id) }}" method="POST" class="inline form-eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-600 text-xs font-semibold cursor-pointer">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="px-6 py-8 text-center text-neutral-500">Sin registros de avance.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>