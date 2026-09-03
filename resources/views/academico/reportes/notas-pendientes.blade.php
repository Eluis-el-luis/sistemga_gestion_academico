<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Notas Pendientes</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            @include('academico.reportes.partials.filtros-notas', ['ruta' => 'academico.reportes.notas-pendientes'])
            <button type="button" onclick="window.print()" class="mt-4 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">CUP</th>
                            <th class="px-6 py-4 text-left">Estudiante</th>
                            <th class="px-6 py-4 text-left">Grado/Aula</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pendientes as $matricula)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold">{{ $matricula->alumno->codigo_unico_persona ?? '—' }}</td>
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $matricula->alumno->nombre_completo }}</td>
                                <td class="px-6 py-4">{{ $matricula->aula->grado->nombre ?? '' }} - {{ $matricula->aula->nombre ?? '' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-12 text-center text-stone-500 font-bold">No hay notas pendientes con los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>