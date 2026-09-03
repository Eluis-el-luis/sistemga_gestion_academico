<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Historial de Notas por Estudiante</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <form method="GET" action="{{ route('academico.reportes.historial-estudiante') }}" class="flex flex-wrap items-end gap-4">
                <div class="w-full max-w-sm">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Estudiante</label>
                    <select name="alumno_id" onchange="this.form.submit()" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
                        <option value="">Seleccione un estudiante...</option>
                        @foreach($alumnos as $al)
                            <option value="{{ $al->id }}" @selected($al->id == request('alumno_id'))>{{ $al->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        @if($alumno)
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h3 class="text-xl font-black text-[#3d2c1d]">{{ $alumno->nombre_completo }}</h3>
                <p class="text-xs text-slate-500 mt-1">CUP: {{ $alumno->codigo_unico_persona ?? '—' }}</p>
            </div>
            <div class="p-6 space-y-6">
                @forelse($historial as $asignatura => $notas)
                    <div>
                        <h4 class="font-black text-[#3d2c1d] mb-3 border-l-4 border-[#e6ac27] pl-3">{{ $asignatura }}</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-black">
                                    <tr>
                                        @foreach($notas as $nota)
                                            <th class="px-4 py-2 text-center">{{ $nota->corteEvaluativo->numero ?? '—' }}° Corte</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach($notas as $nota)
                                            <td class="px-4 py-3 text-center font-black">{{ $nota->nota_cuantitativa }}</td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-stone-500 font-bold py-8">Sin notas registradas para este estudiante.</p>
                @endforelse
            </div>
        </div>
        @else
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-12 text-center text-stone-500 font-bold">
                Seleccione un estudiante para ver su historial académico.
            </div>
        @endif
    </div>
</x-app-layout>