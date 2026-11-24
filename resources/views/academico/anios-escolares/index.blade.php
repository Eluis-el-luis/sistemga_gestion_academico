{{-- resources/views/academico/anios-escolares/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Gestión de Años Escolares</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Ciclos académicos: Planificación, Activo y Cerrado.</p>
            </div>
            @can('configuracion.gestionar')
                <a href="{{ route('academico.anios-escolares.create') }}" class="inline-flex items-center px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-sm font-bold transition-colors cursor-pointer">
                    + Nuevo Año Escolar
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Año Escolar</th>
                                <th class="px-6 py-4">Período</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4">Estadísticas</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($aniosEscolares as $anio)
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-neutral-900 dark:text-white">{{ $anio->nombre }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $anio->fecha_inicio?->format('d/m/Y') }} - {{ $anio->fecha_fin?->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-neutral-600 dark:text-neutral-300">{{ $anio->nombre_periodo }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $anio->color_estado }}">
                                            {{ $anio->etiqueta_estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-neutral-600 dark:text-neutral-300">
                                        {{ $anio->aulas_count ?? 0 }} Aulas · {{ $anio->matriculas_count ?? 0 }} Matrículas · {{ $anio->cortes_evaluativos_count ?? 0 }} Cortes
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('academico.anios-escolares.show', $anio) }}" class="text-sky-600 dark:text-sky-400 hover:underline font-semibold text-xs cursor-pointer">Ver</a>
                                        @can('configuracion.gestionar')
                                            @if (!$anio->es_cerrado)
                                                <a href="{{ route('academico.anios-escolares.edit', $anio) }}" class="text-amber-600 dark:text-amber-400 hover:underline font-semibold text-xs cursor-pointer">Editar</a>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-neutral-500 dark:text-neutral-400">No hay años escolares registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>