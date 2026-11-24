{{-- resources/views/academico/matriculas/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Historial General de Matrículas</h2>
            </div>
            @can('create', \App\Models\Matricula::class)
                <a href="{{ route('academico.matriculas.create') }}" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-xs font-bold uppercase cursor-pointer">+ Nueva Matrícula</a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @php $anioSeleccionadoId = request('anio_escolar_id', $anioActivo->id ?? null); @endphp
            <div class="flex gap-2.5 overflow-x-auto pb-2">
                @foreach ($aniosEscolares as $anio)
                    <a href="{{ route('academico.matriculas.index', ['anio_escolar_id' => $anio->id]) }}" class="px-4 py-2.5 rounded-2xl border text-xs font-bold shrink-0 cursor-pointer {{ $anioSeleccionadoId == $anio->id ? 'bg-[#FFFDF8] dark:bg-neutral-800 border-[#e6ac27] text-neutral-900 dark:text-white' : 'bg-white dark:bg-neutral-900 border-stone-200 dark:border-neutral-800 text-neutral-500' }}">
                        {{ $anio->nombre }}
                    </a>
                @endforeach
            </div>

            <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 space-y-6">
                <form method="GET" action="{{ route('academico.matriculas.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 text-sm">
                    <input type="hidden" name="anio_escolar_id" value="{{ $anioSeleccionadoId }}">
                    <div class="md:col-span-6">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por Nombre o CUP..." class="w-full bg-stone-50 dark:bg-neutral-800 border-stone-300 dark:border-neutral-700 rounded-xl text-sm">
                    </div>
                    <div class="md:col-span-4">
                        <select name="aula_id" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-sm cursor-pointer">
                            <option value="">Todas las aulas...</option>
                            @foreach ($aulas ?? [] as $aula)
                                <option value="{{ $aula->id }}" {{ request('aula_id') == $aula->id ? 'selected' : '' }}>{{ $aula->grado?->nombre ?? '' }} "{{ $aula->nombre }}"</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="w-full py-2.5 bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 rounded-xl text-xs font-bold uppercase cursor-pointer">Buscar</button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-5 py-3.5">CUP</th>
                                <th class="px-5 py-3.5">Estudiante</th>
                                <th class="px-5 py-3.5">Aula</th>
                                <th class="px-5 py-3.5 text-center">Estado</th>
                                <th class="px-5 py-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($matriculas as $matricula)
                                <tr>
                                    <td class="px-5 py-3.5 font-mono text-xs text-neutral-500">{{ $matricula->alumno?->codigo_unico_persona ?? 'SIN-CUP' }}</td>
                                    <td class="px-5 py-3.5 font-semibold">{{ $matricula->alumno?->nombre_completo ?? 'No encontrado' }}</td>
                                    <td class="px-5 py-3.5">{{ $matricula->aula?->grado?->nombre ?? '' }} "{{ $matricula->aula?->nombre ?? '' }}"</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold uppercase {{ $matricula->estado === 'activo' ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-neutral-600' }}">{{ $matricula->estado }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right space-x-2">
                                        @if ($matricula->alumno)
                                            <a href="{{ route('academico.alumnos.show', $matricula->alumno_id) }}" class="text-xs font-semibold text-sky-600 hover:underline cursor-pointer">Ficha</a>
                                        @endif
                                        @can('update', $matricula)
                                            @if ($matricula->estado === 'activo')
                                                <form method="POST" action="{{ route('academico.matriculas.retirar', $matricula) }}" class="inline form-eliminar">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline cursor-pointer">Retirar</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('academico.matriculas.reactivar', $matricula) }}" class="inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-xs font-semibold text-emerald-600 hover:underline cursor-pointer">Reactivar</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-neutral-500">No hay matrículas registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div>{{ $matriculas->appends(request()->query())->links() ?? '' }}</div>
            </div>
        </div>
    </div>
</x-app-layout>