<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                {{ __('Reporte MINED - Formato Oficial') }}
            </h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm">
                        @foreach($anios as $an)
                            <option value="{{ $an->id }}" @selected($an->id == ($anio->id ?? null))>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" onclick="window.print()" class="ml-auto bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
            </form>
        </div>

        <!-- Encabezado oficial -->
        <div class="bg-white p-8 border-2 border-black text-center print:border-0">
            <p class="font-black uppercase text-lg tracking-wide">Ministerio de Educación - Delegación León</p>
            <p class="font-bold text-sm text-slate-600 mt-1">Reporte Estadístico de Rendimiento Escolar</p>
            <p class="font-bold text-sm mt-1">Colegio Cristiano en Nicaragua - Ciclo Escolar {{ $anio->nombre ?? '—' }}</p>
        </div>

        <div class="bg-white border-2 border-black overflow-hidden print:border print:border-black">
            <table class="min-w-full divide-y divide-black text-sm font-bold">
                <thead>
                    <tr class="bg-gray-100 text-center uppercase text-xs">
                        <th class="border border-black px-4 py-3">Modalidad</th>
                        <th class="border border-black px-4 py-3">Matriculados</th>
                        <th class="border border-black px-4 py-3">Retirados</th>
                        <th class="border border-black px-4 py-3">Promedio</th>
                        <th class="border border-black px-4 py-3">% Retención</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black">
                    @foreach($datos as $dato)
                        <tr class="text-center">
                            <td class="border border-black px-4 py-3 text-left">{{ $dato['modalidad'] }}</td>
                            <td class="border border-black px-4 py-3">{{ $dato['matriculados'] }}</td>
                            <td class="border border-black px-4 py-3">{{ $dato['retirados'] }}</td>
                            <td class="border border-black px-4 py-3">{{ number_format($dato['promedio'], 2) }}</td>
                            <td class="border border-black px-4 py-3">{{ $dato['retencion'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-xs text-slate-500 text-center font-medium">Documento generado el {{ now()->format('d/m/Y') }} — Sujetos a validación por la Delegación de León.</p>
    </div>
</x-app-layout>