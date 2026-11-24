{{-- resources/views/academico/notas/solicitudes.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Solicitudes de Edición de Notas</h2>
            <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white cursor-pointer">Volver</a>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6">
                <form method="GET" action="{{ route('academico.notas.solicitudes.index') }}" class="flex items-end gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Estado</label>
                        <select name="estado" onchange="this.form.submit()" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-medium cursor-pointer">
                            <option value="Pendiente" @selected($estado === 'Pendiente')>Pendientes</option>
                            <option value="Aprobada" @selected($estado === 'Aprobada')>Aprobadas</option>
                            <option value="Rechazada" @selected($estado === 'Rechazada')>Rechazadas</option>
                            <option value="Todas" @selected($estado === 'Todas')>Todas</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-5 py-3.5">Docente</th>
                                <th class="px-5 py-3.5">Asignatura</th>
                                <th class="px-5 py-3.5">Estudiante</th>
                                <th class="px-5 py-3.5">Motivo</th>
                                <th class="px-5 py-3.5 text-center">Estado</th>
                                <th class="px-5 py-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($solicitudes as $solicitud)
                                <tr>
                                    <td class="px-5 py-3.5 font-semibold">{{ $solicitud->docente?->usuario?->nombre_completo ?? '—' }}</td>
                                    <td class="px-5 py-3.5">{{ $solicitud->nota?->aulaAsignaturaDocente?->asignatura?->nombre ?? '—' }}</td>
                                    <td class="px-5 py-3.5">{{ $solicitud->nota?->matricula?->alumno?->nombre_completo ?? '—' }}</td>
                                    <td class="px-5 py-3.5 max-w-[260px] truncate" title="{{ $solicitud->motivo }}">{{ $solicitud->motivo }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $solicitud->estado === 'Aprobada' ? 'bg-emerald-50 text-emerald-700' : ($solicitud->estado === 'Rechazada' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-800') }}">{{ $solicitud->estado }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right space-x-2">
                                        @if ($solicitud->estado === 'Pendiente')
                                            <form action="{{ route('academico.notas.solicitudes.aprobar', $solicitud->id) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-xl text-xs cursor-pointer">Aprobar</button>
                                            </form>
                                            <form action="{{ route('academico.notas.solicitudes.rechazar', $solicitud->id) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="bg-stone-200 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-bold px-3 py-1.5 rounded-xl text-xs cursor-pointer">Rechazar</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-neutral-400">Resuelta por {{ $solicitud->autorizadoPor?->nombre_completo ?? '—' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-neutral-500">No hay solicitudes con este estado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>