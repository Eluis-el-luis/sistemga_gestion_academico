<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Monitor de Asistencia
            </h2>
            
            <form method="GET" action="{{ route('academico.asistencia.personal.index') }}" class="flex items-center gap-3">
                <span class="text-sm font-bold text-slate-500">Fecha:</span>
                <input type="date" name="fecha" value="{{ $fechaFiltro }}" onchange="this.form.submit()" 
                       class="bg-white border border-slate-200 text-[#3d2c1d] px-4 py-2 rounded-xl text-sm font-black tracking-widest shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer">
            </form>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FFFDF5] border-b border-[#e6ac27]/20 text-[11px] uppercase tracking-widest font-black text-slate-400">
                            <th class="p-5">Docente</th>
                            <th class="p-5">Rol Principal</th>
                            <th class="p-5">Hora ({{ $fechaFormateada }})</th>
                            <th class="p-5">Estado</th>
                            @hasanyrole('Director|Subdirector')
                                <th class="p-5 w-1/3">Justificación del Docente</th>
                            @endhasanyrole
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($personal as $empleado)
                            @php
                                $marcaHoy = $empleado->asistencias->first();
                                $estado = $marcaHoy ? $marcaHoy->estado : 'Sin Marcar';
                                
                                $badgeColor = match($estado) {
                                    'Presente' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'Retardo' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Ausente' => 'bg-rose-100 text-rose-700 border-rose-200',
                                    default => 'bg-slate-100 text-slate-500 border-slate-200',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-5">
                                    <span class="font-black text-[#3d2c1d] block">{{ $empleado->nombre_completo ?? $empleado->name }}</span>
                                </td>
                                <td class="p-5 text-sm font-bold text-slate-500">
                                    {{ $empleado->roles->first()->name ?? 'N/A' }}
                                </td>
                                <td class="p-5">
                                    <span class="font-black text-base {{ $marcaHoy ? 'text-slate-800' : 'text-slate-300' }}">
                                        {{ $marcaHoy ? \Carbon\Carbon::parse($marcaHoy->hora_entrada)->format('h:i A') : '--:--' }}
                                    </span>
                                </td>
                                <td class="p-5">
                                    <span class="px-3 py-1.5 rounded-lg border text-[10px] font-black uppercase tracking-widest {{ $badgeColor }}">
                                        {{ $estado }}
                                    </span>
                                </td>
                                
                                @hasanyrole('Director|Subdirector')
                                <td class="p-5">
                                    @if($marcaHoy && $marcaHoy->observaciones)
                                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                                            <p class="text-xs text-slate-600 font-medium leading-relaxed italic">
                                                "{{ $marcaHoy->observaciones }}"
                                            </p>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-300 font-bold uppercase tracking-widest">- Ninguna -</span>
                                    @endif
                                </td>
                                @endhasanyrole
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>