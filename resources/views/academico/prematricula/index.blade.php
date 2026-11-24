{{-- resources/views/academico/prematriculas/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Gestión de Prematrícula</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Proyección y promoción al siguiente ciclo escolar.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border-collapse text-sm">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="p-4 w-14 text-center">#</th>
                                <th class="p-4">Estudiante</th>
                                <th class="p-4 text-center">Estado Académico</th>
                                <th class="p-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($matriculas as $index => $matricula)
                                @php $esLimpio = ($matricula->clases_reprobadas ?? 0) == 0; @endphp
                                <tr>
                                    <td class="p-4 text-center font-semibold text-neutral-400">{{ $index + 1 }}</td>
                                    <td class="p-4 font-semibold">{{ $matricula->alumno?->nombre_completo }}</td>
                                    <td class="p-4 text-center">
                                        <span class="px-3 py-1 rounded-lg text-xs font-semibold {{ $esLimpio ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                            {{ $esLimpio ? 'Limpio' : 'En Reparación (' . $matricula->clases_reprobadas . ')' }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        @if ($esLimpio)
                                            <form action="{{ route('academico.prematricula.promover', $matricula->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="estado_academico" value="Limpio">
                                                <button type="submit" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-xs font-bold uppercase cursor-pointer">Promover</button>
                                            </form>
                                        @else
                                            <form action="{{ route('academico.prematricula.remitir', $matricula->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="estado_academico" value="En Reparación">
                                                <button type="submit" class="px-4 py-2 bg-stone-200 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 rounded-xl text-xs font-bold uppercase cursor-pointer">Remitir a Dirección</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-10 text-center text-neutral-500">No hay estudiantes activos asignados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>