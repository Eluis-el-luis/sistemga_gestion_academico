<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                {{ __('Boletines') }}
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm font-medium">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h3 class="text-lg font-black text-[#3d2c1d]">Seleccionar Aula</h3>
            </div>
            <div class="p-8">
                <form method="GET" action="{{ route('academico.boletines.index') }}" class="flex flex-wrap items-end gap-4">
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
                <h3 class="text-lg font-black text-[#3d2c1d]">Alumnos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Alumno</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($matriculas as $matricula)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $matricula->alumno->nombre_completo }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('academico.boletines.show', $matricula->id) }}" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-3 py-2 rounded-xl shadow-md transition-all text-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Ver Boletín
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-6 py-8 text-center text-stone-500 font-bold">No hay alumnos matriculados en esta aula.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>