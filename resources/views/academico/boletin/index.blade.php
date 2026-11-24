{{-- resources/views/academico/boletin/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Validación de Boletines Oficiales</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Revisa y aprueba boletines por sección.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if ($aulas->isNotEmpty())
                <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs">
                    <form method="GET" action="{{ route('academico.boletines.index') }}" class="max-w-md">
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Aula / Sección</label>
                        <select name="aula_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-medium cursor-pointer">
                            @foreach ($aulas as $aula)
                                <option value="{{ $aula->id }}" @selected((int) $aula->id === (int) $aulaSeleccionada)>{{ $aula->grado?->nombre ?? '' }} - Sección "{{ $aula->nombre }}"</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border-collapse text-sm">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="p-4 w-14 text-center">#</th>
                                <th class="p-4">Estudiante</th>
                                <th class="p-4 text-center">CUP</th>
                                <th class="p-4 text-center">Estado</th>
                                <th class="p-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($matriculas as $index => $matricula)
                                @php $aprobado = $matricula->boletines->isNotEmpty(); @endphp
                                <tr>
                                    <td class="p-4 text-center text-neutral-400 font-semibold">{{ $index + 1 }}</td>
                                    <td class="p-4 font-semibold">{{ $matricula->alumno?->nombre_completo }}</td>
                                    <td class="p-4 text-center text-neutral-500 font-mono text-xs">{{ $matricula->alumno?->codigo_unico_persona }}</td>
                                    <td class="p-4 text-center">
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold {{ $aprobado ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-800' }}">
                                            {{ $aprobado ? 'Aprobado' : 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right space-x-2">
                                        <a href="{{ route('academico.boletines.show', $matricula->id) }}" class="inline-flex px-3 py-1.5 bg-stone-100 dark:bg-neutral-800 rounded-xl text-xs font-semibold cursor-pointer">Revisar</a>
                                        <form action="{{ route('academico.boletines.aprobar', $matricula->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex px-3 py-1.5 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-xs font-bold cursor-pointer">Visto Bueno</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-10 text-center text-neutral-500">No hay matrículas activas en esta sección.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>