{{-- resources/views/academico/grupo-materia/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Agrupaciones de Materias</h2>
            <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white cursor-pointer">Volver</a>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @hasanyrole('directora|subdirectora|Director|Subdirector')
                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                    <h3 class="font-bold text-neutral-900 dark:text-white mb-4 text-base">Crear Nuevo Grupo</h3>
                    <form action="{{ route('academico.grupo-materia.store') }}" method="POST" class="flex flex-wrap items-end gap-4 text-sm">
                        @csrf
                        <div class="flex-1 min-w-[220px]">
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Nombre del Grupo</label>
                            <input type="text" name="nombre" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-semibold" required>
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Orden</label>
                            <input type="number" name="orden" value="0" min="0" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-semibold">
                        </div>
                        <button type="submit" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase cursor-pointer">Crear</button>
                    </form>
                </div>

                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                    <h3 class="font-bold text-neutral-900 dark:text-white mb-4 text-base">Asignar Materias a Grupos</h3>
                    <form action="{{ route('academico.grupo-materia.asignar') }}" method="POST">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-stone-200 dark:divide-neutral-800 text-sm text-left">
                                <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                                    <tr>
                                        <th class="px-4 py-3">Materia</th>
                                        <th class="px-4 py-3">Grupo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                    @foreach ($asignaturas as $asignatura)
                                        <tr>
                                            <td class="px-4 py-2.5 font-semibold">{{ $asignatura->nombre }}</td>
                                            <td class="px-4 py-2.5">
                                                <select name="asignaciones[{{ $asignatura->id }}]" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                                                    <option value="">— Sin grupo —</option>
                                                    @foreach ($grupos as $grupo)
                                                        <option value="{{ $grupo->id }}" @selected((int) $asignatura->grupo_materia_id === (int) $grupo->id)>{{ $grupo->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="mt-4 bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 font-bold px-6 py-2.5 rounded-xl text-xs uppercase cursor-pointer">Guardar Asignaciones</button>
                    </form>
                </div>
            @endhasanyrole

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 px-6 py-4 border-b border-stone-200/80 dark:border-neutral-800">
                    <h3 class="font-bold text-neutral-900 dark:text-white text-base">Grupos Existentes</h3>
                </div>
                <div class="p-6 space-y-4">
                    @forelse ($grupos as $grupo)
                        <div class="border border-stone-200 dark:border-neutral-800 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="font-semibold text-neutral-900 dark:text-white text-sm">{{ $grupo->nombre }} <span class="text-xs text-neutral-400">(Orden: {{ $grupo->orden }})</span></div>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ $grupo->asignaturas->count() }} materia(s) asignadas</p>
                            </div>
                            @hasanyrole('directora|subdirectora|Director|Subdirector')
                                <form action="{{ route('academico.grupo-materia.destroy', $grupo->id) }}" method="POST" class="form-eliminar">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline text-xs font-semibold uppercase cursor-pointer">Eliminar</button>
                                </form>
                            @endhasanyrole
                        </div>
                    @empty
                        <p class="text-neutral-500 text-sm">No hay grupos definidos.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>