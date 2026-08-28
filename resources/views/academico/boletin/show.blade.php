<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                Boletín de Calificaciones
            </h2>
            <div class="flex gap-3">
                <a href="{{ route('academico.boletines.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md transition-all text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir / PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden print:shadow-none print:border-0">
            <div class="bg-[#FFFDF5] px-8 py-6 border-b border-[#e6ac27]/20">
                <div class="flex flex-col md:flex-row justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-black text-[#3d2c1d]">{{ $matricula->alumno->nombre_completo }}</h3>
                        <p class="text-stone-500 font-medium mt-1">{{ $matricula->aula->nombre }} - {{ $matricula->aula->grado->nombre ?? '' }}</p>
                        <p class="text-stone-500 font-medium">{{ $matricula->aula->modalidad->nombre ?? '' }} · {{ $matricula->anioEscolar->nombre ?? '' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-black text-[#3d2c1d]">Promedio General</p>
                        <p class="text-3xl font-black {{ $promedioGeneral >= 60 ? 'text-emerald-600' : 'text-rose-600' }}">{{ number_format($promedioGeneral, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Asignatura</th>
                            <th class="px-6 py-4 text-center">Promedio</th>
                            <th class="px-6 py-4 text-center">Indicador</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $notaService = app(\App\Services\NotaService::class);
                        @endphp
                        @forelse($detalle as $item)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $item['asignatura'] }}</td>
                                <td class="px-6 py-4 text-center font-bold">{{ is_null($item['promedio']) ? '—' : number_format($item['promedio'], 2) }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if(!is_null($item['promedio']))
                                        @php $codigo = $notaService->calcularIndicadorLogro((int) round($item['promedio'])); @endphp
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest {{ in_array($codigo, ['AA','AS']) ? 'bg-emerald-100 text-emerald-700' : (in_array($codigo, ['AF']) ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                                            {{ $codigo }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-stone-500 font-bold">No hay asignaturas con notas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>