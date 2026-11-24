{{-- resources/views/academico/asistencia/personal/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al Panel">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                        Mi Historial de Asistencia
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Registro personal de marcajes y justificaciones.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('academico.asistencia.personal.index') }}" class="flex items-center gap-3 w-full sm:w-auto">
                <span class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider hidden sm:block">Mes:</span>
                <input type="month" name="mes" value="{{ request('mes', now()->format('Y-m')) }}" onchange="this.form.submit()" class="w-full sm:w-auto bg-stone-50 dark:bg-neutral-800 border border-stone-300 dark:border-neutral-700 text-neutral-900 dark:text-neutral-100 px-3.5 py-2 rounded-xl text-sm font-medium cursor-pointer">
            </form>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-white dark:bg-neutral-900 p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="block text-xs font-semibold text-neutral-400 uppercase tracking-wider">Días Presente</span>
                        <span class="block text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ $totalPresentes ?? 0 }}</span>
                    </div>
                </div>
                <div class="bg-white dark:bg-neutral-900 p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="block text-xs font-semibold text-neutral-400 uppercase tracking-wider">Retardos</span>
                        <span class="block text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ $totalRetardos ?? 0 }}</span>
                    </div>
                </div>
                <div class="bg-white dark:bg-neutral-900 p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="block text-xs font-semibold text-neutral-400 uppercase tracking-wider">Ausencias</span>
                        <span class="block text-2xl font-bold text-neutral-900 dark:text-white mt-0.5">{{ $totalAusencias ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px] text-sm">
                        <thead>
                            <tr class="bg-[#FFFDF8] dark:bg-neutral-800/70 border-b border-stone-200 dark:border-neutral-800 text-xs uppercase tracking-wider font-semibold text-neutral-500 dark:text-neutral-400">
                                <th class="p-4">Fecha</th>
                                <th class="p-4">Hora de Marcaje</th>
                                <th class="p-4">Estado</th>
                                <th class="p-4 w-1/3">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($asistencias as $registro)
                                @php
                                    $estado = $registro->estado;
                                    $badgeColor = match ($estado) {
                                        'Presente' => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900',
                                        'Retardo' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-900',
                                        'Ausente', 'Justificado' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-900',
                                        default => 'bg-stone-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300 border-stone-200 dark:border-neutral-700',
                                    };
                                @endphp
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <td class="p-4 font-semibold">{{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/y') }}</td>
                                    <td class="p-4 font-semibold">{{ \Carbon\Carbon::parse($registro->hora_entrada)->format('h:i A') }}</td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-lg border text-xs font-semibold {{ $badgeColor }}">{{ $estado }}</span>
                                    </td>
                                    <td class="p-4 text-xs text-neutral-600 dark:text-neutral-300">
                                        {{ $registro->observaciones ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-10 text-center text-neutral-500 dark:text-neutral-400">No hay registros de asistencia para este mes.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>