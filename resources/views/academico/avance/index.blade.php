<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16M4 12h16M4 19h16"></path></svg>
                {{ __('Avance de Contenidos') }}
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm font-medium">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm font-medium">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h3 class="text-lg font-black text-[#3d2c1d]">Seleccionar Asignatura</h3>
            </div>
            <div class="p-8">
                <form method="GET" action="{{ route('academico.avance.index') }}" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Asignatura / Clase</label>
                        <select name="asignacion_id" onchange="this.form.submit()" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-medium">
                            <option value="">Seleccione...</option>
                            @foreach($asignaciones as $asig)
                                <option value="{{ $asig->id }}" @selected($asig->id == request('asignacion_id'))>
                                    {{ $asig->asignatura->nombre }} - {{ $asig->aula->grado->nombre ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if($asignacionSeleccionada)
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20 flex items-center gap-2">
                <h3 class="text-lg font-black text-[#3d2c1d]">Registrar Avance</h3>
            </div>
            <div class="p-8">
                <form action="{{ route('academico.avance.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
                    @csrf
                    <input type="hidden" name="aula_asignatura_docente_id" value="{{ $asignacionSeleccionada->id }}">
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Mes <span class="text-rose-500">*</span></label>
                        <input type="month" name="mes" value="{{ now()->format('Y-m') }}" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">% Avance <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="porcentaje_avance" min="0" max="100" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold" required>
                    </div>
                    <button type="submit" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-2.5 rounded-xl shadow-md transition-all">Guardar Avance</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h3 class="text-lg font-black text-[#3d2c1d]">Historial de Avance</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Mes</th>
                            <th class="px-6 py-4 text-left">% Avance</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($avances as $avance)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ \Carbon\Carbon::createFromFormat('Y-m', $avance->mes)->format('m/Y') }}</td>
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $avance->porcentaje_avance }}%</td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('academico.avance.destroy', $avance->id) }}" method="POST" class="alerta-eliminar inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-500 p-2 rounded-xl transition-colors" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-stone-500 font-bold">No hay registros de avance para esta asignatura.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alerta-eliminar').forEach(f => {
                f.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Eliminar registro de avance?', icon: 'warning',
                        showCancelButton: true, confirmButtonColor: '#e11d48', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, Eliminar', cancelButtonText: 'Cancelar'
                    }).then(r => { if (r.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>