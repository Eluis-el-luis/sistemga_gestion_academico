<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Solicitudes de Edición de Notas</h2>
            <a href="{{ route('dashboard') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm font-medium">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm font-medium">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <form method="GET" action="{{ route('academico.notas.solicitudes.index') }}" class="flex items-end gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Estado</label>
                    <select name="estado" onchange="this.form.submit()" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
                        <option value="Pendiente" @selected($estado === 'Pendiente')>Pendientes</option>
                        <option value="Aprobada" @selected($estado === 'Aprobada')>Aprobadas</option>
                        <option value="Rechazada" @selected($estado === 'Rechazada')>Rechazadas</option>
                        <option value="Todas" @selected($estado === 'Todas')>Todas</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Docente</th>
                            <th class="px-6 py-4 text-left">Asignatura</th>
                            <th class="px-6 py-4 text-left">Estudiante</th>
                            <th class="px-6 py-4 text-left">Motivo</th>
                            <th class="px-6 py-4 text-center">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($solicitudes as $solicitud)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $solicitud->docente?->usuario?->nombre_completo ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $solicitud->nota?->aulaAsignaturaDocente?->asignatura?->nombre ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $solicitud->nota?->matricula?->alumno?->nombre_completo ?? '—' }}</td>
                                <td class="px-6 py-4 max-w-[260px] truncate" title="{{ $solicitud->motivo }}">{{ $solicitud->motivo }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest
                                        {{ $solicitud->estado === 'Aprobada' ? 'bg-emerald-100 text-emerald-700' : ($solicitud->estado === 'Rechazada' ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $solicitud->estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($solicitud->estado === 'Pendiente')
                                        <form action="{{ route('academico.notas.solicitudes.aprobar', $solicitud->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 bg-emerald-500 hover:bg-emerald-600 text-white font-black px-3 py-1.5 rounded-xl text-xs shadow-sm transition-colors">Aprobar</button>
                                        </form>
                                        <form action="{{ route('academico.notas.solicitudes.rechazar', $solicitud->id) }}" method="POST" class="inline ml-2">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-black px-3 py-1.5 rounded-xl text-xs shadow-sm transition-colors">Rechazar</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 font-bold">Resuelta por {{ $solicitud->autorizadoPor?->nombre_completo ?? '—' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-stone-500 font-bold">No hay solicitudes con el estado seleccionado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>