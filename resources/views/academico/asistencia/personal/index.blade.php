<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <!-- Botón Atrás (Jerarquía secundaria clara) -->
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] dark:hover:text-[#e6ac27] transition-colors" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] dark:text-white leading-tight">
                        Mi Historial de Asistencia
                    </h2>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">Registro personal de marcajes y justificaciones.</p>
                </div>
            </div>
            
            <!-- Filtro Simplificado (Mes en lugar de día específico) -->
            <form method="GET" action="{{ route('academico.asistencia.personal.index') }}" class="flex items-center gap-3 w-full sm:w-auto">
                <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest hidden sm:block">Consultar mes:</span>
                <input type="month" name="mes" value="{{ request('mes', now()->format('Y-m')) }}" onchange="this.form.submit()" 
                       class="w-full sm:w-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-[#3d2c1d] dark:text-slate-200 px-4 py-2 rounded-xl text-sm font-bold shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors cursor-pointer">
            </form>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] dark:bg-slate-900 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- KPIs de Bolsillo (Resumen mensual) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Días Presente</span>
                        <span class="block text-2xl font-black text-[#3d2c1d] dark:text-white">{{ $totalPresentes ?? 0 }}</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Retardos</span>
                        <span class="block text-2xl font-black text-[#3d2c1d] dark:text-white">{{ $totalRetardos ?? 0 }}</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Ausencias</span>
                        <span class="block text-2xl font-black text-[#3d2c1d] dark:text-white">{{ $totalAusencias ?? 0 }}</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Tabla de Historial (Iterando sobre $asistencias del usuario) -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 text-[11px] uppercase tracking-widest font-black text-slate-400 dark:text-slate-500">
                                <th class="p-5">Fecha</th>
                                <th class="p-5">Hora de Marcaje</th>
                                <th class="p-5">Estado</th>
                                <th class="p-5 w-1/3">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            @forelse($asistencias as $registro)
                                @php
                                    $estado = $registro->estado;
                                    $badgeColor = match($estado) {
                                        'Presente' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                                        'Retardo' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20',
                                        'Ausente', 'Justificado' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/20',
                                        default => 'bg-slate-50 dark:bg-slate-500/10 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-500/20',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="p-5">
                                        <span class="font-black text-[#3d2c1d] dark:text-white block tracking-widest">
                                            {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/y') }}
                                        </span>
                                    </td>
                                    <td class="p-5">
                                        <span class="font-black text-base text-slate-800 dark:text-slate-300">
                                            {{ \Carbon\Carbon::parse($registro->hora_entrada)->format('h:i A') }}
                                        </span>
                                    </td>
                                    <td class="p-5">
                                        <span class="px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest {{ $badgeColor }}">
                                            {{ $estado }}
                                        </span>
                                    </td>
                                    <td class="p-5">
                                        @if($registro->observaciones)
                                            <p class="text-xs text-slate-600 dark:text-slate-400 font-medium italic">
                                                "{{ $registro->observaciones }}"
                                            </p>
                                        @else
                                            <span class="text-[10px] text-slate-300 dark:text-slate-600 font-bold uppercase tracking-widest">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-10 text-center">
                                        <span class="text-slate-400 dark:text-slate-500 font-bold text-sm">No hay registros de asistencia para este mes.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>