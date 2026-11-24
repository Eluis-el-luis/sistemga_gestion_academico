{{-- resources/views/academico/apoyo-padres/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Apoyo de Padres</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                <form method="GET" action="{{ route('academico.apoyo-padres.index') }}" class="max-w-md">
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Seleccionar Aula</label>
                    <select name="aula_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                        <option value="">Seleccione...</option>
                        @foreach ($aulas as $aula)
                            <option value="{{ $aula->id }}" @selected((int) $aula->id === (int) $aulaSeleccionada)>
                                {{ $aula->nombre }} - {{ $aula->grado->nombre ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if ($aulaSeleccionada)
                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-4">Registrar Apoyo Mensual</h3>
                    <form action="{{ route('academico.apoyo-padres.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        @csrf
                        <input type="hidden" name="aula_id" value="{{ $aulaSeleccionada }}">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Mes <span class="text-rose-500">*</span></label>
                            <input type="month" name="mes" value="{{ now()->format('Y-m') }}" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Padres que apoyan <span class="text-rose-500">*</span></label>
                            <input type="number" name="cantidad_apoyan" min="0" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase mb-1.5">Total de padres <span class="text-rose-500">*</span></label>
                            <input type="number" name="total_padres" min="1" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm" required>
                        </div>
                        <button type="submit" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 px-5 rounded-xl text-sm transition-colors cursor-pointer">
                            Guardar
                        </button>
                    </form>
                </div>

                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-200 dark:divide-neutral-800 text-sm text-left">
                            <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-6 py-4">Mes</th>
                                    <th class="px-6 py-4">Apoyan</th>
                                    <th class="px-6 py-4">Total</th>
                                    <th class="px-6 py-4">% Apoyo</th>
                                    <th class="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                @forelse ($registros as $reg)
                                    <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40">
                                        <td class="px-6 py-4 font-semibold">{{ \Carbon\Carbon::createFromFormat('Y-m', $reg->mes)->format('m/Y') }}</td>
                                        <td class="px-6 py-4">{{ $reg->cantidad_apoyan }}</td>
                                        <td class="px-6 py-4">{{ $reg->total_padres }}</td>
                                        <td class="px-6 py-4 font-semibold">{{ $reg->total_padres > 0 ? round(($reg->cantidad_apoyan / $reg->total_padres) * 100, 1) : 0 }}%</td>
                                        <td class="px-6 py-4 text-right">
                                            <form action="{{ route('academico.apoyo-padres.destroy', $reg->id) }}" method="POST" class="form-eliminar inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-600 p-2 rounded-xl cursor-pointer" title="Eliminar">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-neutral-500">Sin registros para esta aula.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>