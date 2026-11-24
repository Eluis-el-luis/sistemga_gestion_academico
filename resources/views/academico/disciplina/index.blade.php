{{-- resources/views/academico/disciplina/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Buzón Disciplinario</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Gestión de casos y citaciones.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{ open: false, id: '', estado: '', fecha: '', resolucion: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs overflow-hidden">
                @if ($incidencias->isEmpty())
                    <div class="p-12 text-center text-neutral-500">No hay incidencias disciplinarias reportadas.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-6 py-3.5">Estudiante y Aula</th>
                                    <th class="px-6 py-3.5">Falta</th>
                                    <th class="px-6 py-3.5 text-center">Estado</th>
                                    <th class="px-6 py-3.5 text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                @foreach ($incidencias as $inc)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-neutral-900 dark:text-white block">{{ $inc->matricula?->alumno?->nombre_completo }}</span>
                                            <span class="text-xs text-neutral-500">{{ $inc->matricula?->aula?->grado?->nombre }} - {{ $inc->matricula?->aula?->nombre }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-xs font-bold uppercase">{{ $inc->nivel_falta }}</span>
                                            <p class="text-xs text-neutral-600 dark:text-neutral-300 mt-0.5 max-w-xs truncate">{{ $inc->descripcion }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-lg text-xs font-semibold bg-stone-100 dark:bg-neutral-800">{{ $inc->estado }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <button type="button" @click="open = true; id = '{{ $inc->id }}'; estado = @js($inc->estado); fecha = '{{ $inc->fecha_citacion_padres ? $inc->fecha_citacion_padres->format('Y-m-d\TH:i') : '' }}'; resolucion = @js($inc->resolucion_final ?? '');" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 text-xs font-bold uppercase rounded-xl cursor-pointer">
                                                Gestionar
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl w-full max-w-md overflow-hidden border border-stone-200 dark:border-neutral-800">
                <form x-bind:action="'{{ url('academico/disciplina') }}/' + id" method="POST" class="p-6 space-y-4 text-sm">
                    @csrf
                    @method('PUT')
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Actualizar Caso</h3>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Estado</label>
                        <select name="estado" x-model="estado" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                            <option value="Reportada">Reportada</option>
                            <option value="En Revisión">En Revisión</option>
                            <option value="Citación a Padres">Citación a Padres</option>
                            <option value="Cerrada">Cerrada</option>
                        </select>
                    </div>
                    <div x-show="estado === 'Citación a Padres'">
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Fecha y Hora de Cita</label>
                        <input type="datetime-local" name="fecha_citacion_padres" x-model="fecha" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm">
                    </div>
                    <div x-show="estado === 'Cerrada' || estado === 'Citación a Padres'">
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Resolución / Acuerdos</label>
                        <textarea name="resolucion_final" x-model="resolucion" rows="3" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="open = false" class="px-4 py-2 text-xs font-semibold text-neutral-500 cursor-pointer">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-[#e6ac27] text-neutral-950 text-xs font-bold uppercase rounded-xl cursor-pointer">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>