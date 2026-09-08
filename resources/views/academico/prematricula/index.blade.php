<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Gestión de Prematrícula</h2>
                <p class="text-sm font-medium text-slate-500 mt-1">Proyección y promoción automática al siguiente ciclo escolar.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-widest font-black text-slate-400">
                            <tr>
                                <th class="p-5 w-16 text-center">#</th>
                                <th class="p-5">Estudiante</th>
                                <th class="p-5 text-center">Estado Académico</th>
                                <th class="p-5 text-right">Acción de Movilidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($matriculas as $index => $matricula)
                                @php
                                    $esLimpio = $matricula->clases_reprobadas == 0;
                                @endphp
                                <tr class="{{ !$esLimpio ? 'bg-rose-50/40 hover:bg-rose-50/60' : 'hover:bg-slate-50' }} transition-colors">
                                    <td class="p-5 text-center font-black text-slate-300">{{ $index + 1 }}</td>
                                    <td class="p-5 font-black text-[#3d2c1d]">
                                        {{ $matricula->alumno->nombre_completo }}
                                        @if(!$esLimpio)
                                            <span class="block text-[10px] text-rose-500 font-bold mt-0.5">Materia(s) reprobada(s): {{ $matricula->clases_reprobadas }}</span>
                                        @endif
                                    </td>
                                    <td class="p-5 text-center">
                                        @if($esLimpio)
                                            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                ✔ Limpio
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                ⚠️ En Reparación
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-5 text-right flex justify-end gap-2">
                                        @if($esLimpio)
                                            <form action="{{ route('academico.prematricula.promover', $matricula->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="estado_academico" value="Limpio">
                                                <button type="submit" class="px-4 py-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-xl text-xs font-black shadow-sm transition-all transform hover:-translate-y-0.5">
                                                    Promover a Siguiente Grado
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('academico.prematricula.remitir', $matricula->id) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="estado_academico" value="En Reparación">
                                                <button type="submit" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-600 rounded-xl text-xs font-black shadow-sm transition-all">
                                                    Remitir a Dirección
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-slate-400 font-bold">No hay estudiantes activos asignados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>