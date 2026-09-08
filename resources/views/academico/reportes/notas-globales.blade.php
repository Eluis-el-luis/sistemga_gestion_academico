<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Notas Globales</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            @include('academico.reportes.partials.filtros-notas', ['ruta' => 'academico.reportes.notas-globales'])
            <button type="button" onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Estudiante</th>
                            <th class="px-6 py-4 text-left">Grado</th>
                            <th class="px-6 py-4 text-left">Asignatura</th>
                            <th class="px-6 py-4 text-left">Corte</th>
                            <th class="px-6 py-4 text-center">Nota</th>
                            <th class="px-6 py-4 text-center">Indicador</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($notas as $nota)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $nota->matricula->alumno->nombre_completo }}</td>
                                <td class="px-6 py-4">{{ $nota->matricula->aula->grado->nombre ?? '' }}</td>
                                <td class="px-6 py-4">{{ $nota->aulaAsignaturaDocente->asignatura->nombre }}</td>
                                <td class="px-6 py-4">{{ $nota->corteEvaluativo->numero ?? '—' }}° (S{{ $nota->corteEvaluativo->semestre ?? '—' }})</td>
                                <td class="px-6 py-4 text-center font-black">{{ $nota->nota_cuantitativa }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase {{ $nota->indicadorLogro?->codigo ?? '' }}">
                                        {{ $nota->indicadorLogro?->codigo ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-stone-500 font-bold">No hay notas registradas con los filtros seleccionados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">{{ $notas->appends(request()->query())->links() ?? '' }}</div>
        </div>
    </div>
</x-app-layout>