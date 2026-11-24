{{-- resources/views/academico/asistencia/personal/monitor.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Monitor de Asistencia</h2>

            <form method="GET" action="{{ route('academico.asistencia.personal.index') }}" class="flex items-center gap-3">
                <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400">Fecha:</span>
                <input type="date" name="fecha" value="{{ $fechaFiltro ?? now()->toDateString() }}" onchange="this.form.submit()" class="bg-stone-50 dark:bg-neutral-800 border border-stone-300 dark:border-neutral-700 text-neutral-900 dark:text-neutral-100 px-3.5 py-2 rounded-xl text-sm font-medium cursor-pointer">
            </form>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-[#FFFDF8] dark:bg-neutral-800/70 border-b border-stone-200 dark:border-neutral-800 text-xs uppercase tracking-wider font-semibold text-neutral-500 dark:text-neutral-400">
                                <th class="p-4">Personal</th>
                                <th class="p-4">Rol Principal</th>
                                <th class="p-4">Hora ({{ $fechaFormateada ?? '' }})</th>
                                <th class="p-4">Estado</th>
                                @hasanyrole('directora|subdirectora|Director|Subdirector')
                                    <th class="p-4 w-1/3">Justificación</th>
                                @endhasanyrole
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @foreach ($personal as $empleado)
                                @php
                                    $marcaHoy = $empleado->asistencias->first();
                                    $estado = $marcaHoy ? $marcaHoy->estado : 'Sin Marcar';
                                    $badgeColor = match ($estado) {
                                        'Presente' => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900',
                                        'Retardo' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-900',
                                        'Ausente' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-900',
                                        default => 'bg-stone-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 border-stone-200 dark:border-neutral-700',
                                    };
                                @endphp
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <td class="p-4 font-semibold">{{ $empleado->nombre_completo ?? $empleado->name }}</td>
                                    <td class="p-4 text-neutral-500 dark:text-neutral-400">{{ $empleado->roles->pluck('name')->first() ?? 'N/A' }}</td>
                                    <td class="p-4 font-semibold">{{ $marcaHoy ? \Carbon\Carbon::parse($marcaHoy->hora_entrada)->format('h:i A') : '--:--' }}</td>
                                    <td class="p-4"><span class="px-3 py-1 rounded-lg border text-xs font-semibold {{ $badgeColor }}">{{ $estado }}</span></td>
                                    @hasanyrole('directora|subdirectora|Director|Subdirector')
                                        <td class="p-4 text-xs text-neutral-600 dark:text-neutral-300">{{ ($marcaHoy && $marcaHoy->observaciones) ? $marcaHoy->observaciones : '-' }}</td>
                                    @endhasanyrole
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>