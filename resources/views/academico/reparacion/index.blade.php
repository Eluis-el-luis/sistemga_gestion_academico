<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                {{ __('Examen de Reparación') }}
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
                <h3 class="text-lg font-black text-[#3d2c1d]">Seleccionar Aula</h3>
            </div>
            <div class="p-8">
                <form method="GET" action="{{ route('academico.reparacion.index') }}" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Aula</label>
                        <select name="aula_id" onchange="this.form.submit()" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-medium">
                            <option value="">Seleccione...</option>
                            @foreach($aulas as $aula)
                                <option value="{{ $aula->id }}" @selected($aula->id == request('aula_id'))>
                                    {{ $aula->nombre }} - {{ $aula->grado->nombre ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        @if($aulaSeleccionada)
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h3 class="text-lg font-black text-[#3d2c1d]">Registrar Examen de Reparación</h3>
            </div>
            <div class="p-8">
                <form action="{{ route('academico.reparacion.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-5 items-end">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Alumno <span class="text-rose-500">*</span></label>
                        <select name="matricula_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-medium" required>
                            <option value="">Seleccione...</option>
                            @foreach($matriculas as $mat)
                                <option value="{{ $mat->id }}">{{ $mat->alumno->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Asignatura <span class="text-rose-500">*</span></label>
                        <select name="asignatura_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-medium" required>
                            <option value="">Seleccione...</option>
                            @foreach($asignaturas as $asig)
                                <option value="{{ $asig->id }}">{{ $asig->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Nota <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="nota_obtenida" min="0" max="100" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold" required>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Fecha <span class="text-rose-500">*</span></label>
                        <input type="date" name="fecha" value="{{ now()->toDateString() }}" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold" required>
                    </div>
                    <button type="submit" class="md:col-span-4 justify-self-start bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-2.5 px-6 rounded-xl shadow-md transition-all">Guardar</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h3 class="text-lg font-black text-[#3d2c1d]">Exámenes Registrados</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Alumno</th>
                            <th class="px-6 py-4 text-left">Asignatura</th>
                            <th class="px-6 py-4 text-left">Nota</th>
                            <th class="px-6 py-4 text-left">Resultado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $examenes = \App\Models\ExamenReparacion::with(['matricula.alumno','asignatura'])
                                ->whereIn('matricula_id', $matriculas->pluck('id'))
                                ->get();
                        @endphp
                        @forelse($examenes as $ex)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $ex->matricula->alumno->nombre_completo }}</td>
                                <td class="px-6 py-4">{{ $ex->asignatura->nombre }}</td>
                                <td class="px-6 py-4 font-bold">{{ $ex->nota_obtenida }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ $ex->resultado === 'aprobado' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                        {{ $ex->resultado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('academico.reparacion.destroy', $ex->id) }}" method="POST" class="alerta-eliminar inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-500 p-2 rounded-xl transition-colors" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-stone-500 font-bold">No hay exámenes de reparación registrados.</td></tr>
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
                        title: '¿Eliminar examen de reparación?', icon: 'warning',
                        showCancelButton: true, confirmButtonColor: '#e11d48', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, Eliminar', cancelButtonText: 'Cancelar'
                    }).then(r => { if (r.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>