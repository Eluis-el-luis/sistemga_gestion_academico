<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Certificado de Notas</h2>
            <div class="flex gap-3">
                <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
                @if($alumno)
                    <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir / PDF</button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Selector de estudiante -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 print:hidden">
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
        <!-- CERTIFICADO -->
        <div class="bg-white p-10 rounded-3xl border-2 border-black shadow-sm print:border print:border-black print:p-6 text-black">
            <!-- Encabezado institucional -->
            <div class="text-center mb-6 border-b-2 border-black pb-4">
                <h1 class="font-black text-xl uppercase tracking-wide">Colegio Cristiano en Nicaragua</h1>
                <p class="font-bold text-sm mt-1">CERTIFICADO DE NOTAS</p>
                <p class="text-xs mt-1 text-slate-700">
                    Ciclo Escolar {{ $matricula->anioEscolar->nombre ?? '—' }} · {{ $matricula->aula->modalidad->nombre ?? '—' }}
                </p>
            </div>

            <!-- Datos del estudiante -->
            <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm mb-6">
                <p><span class="font-black uppercase text-xs text-slate-600">Estudiante:</span> <span class="font-black">{{ $alumno->nombre_completo }}</span></p>
                <p><span class="font-black uppercase text-xs text-slate-600">Código de Persona:</span> <span class="font-bold">{{ $alumno->codigo_unico_persona ?? 'N/A' }}</span></p>
                <p><span class="font-black uppercase text-xs text-slate-600">Grado:</span> <span class="font-bold">{{ $matricula->aula->grado->nombre ?? '—' }}</span></p>
                <p><span class="font-black uppercase text-xs text-slate-600">Sección:</span> <span class="font-bold">{{ $matricula->aula->nombre ?? '—' }}</span></p>
            </div>

            <!-- Tabla de notas -->
            <table class="w-full border-collapse border border-black text-sm">
                <thead>
                    <tr class="bg-slate-100 text-center text-xs font-black uppercase">
                        <th class="border border-black px-3 py-2 text-left">Asignatura</th>
                        <th class="border border-black px-2 py-2">I Corte</th>
                        <th class="border border-black px-2 py-2">II Corte</th>
                        <th class="border border-black px-2 py-2">III Corte</th>
                        <th class="border border-black px-2 py-2">IV Corte</th>
                        <th class="border border-black px-2 py-2">Nota Final</th>
                        <th class="border border-black px-2 py-2">Estado</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @forelse($resumenAsignaturas as $item)
                        @php $r = $item['resumen']; @endphp
                        <tr>
                            <td class="border border-black px-3 py-1.5 text-left font-bold">{{ $item['asignatura'] }}</td>
                            @for($c = 1; $c <= 4; $c++)
                                <td class="border border-black px-2 py-1.5">
                                    {{ isset($r['cortes'][$c]) && !is_null($r['cortes'][$c]) ? $r['cortes'][$c] : '—' }}
                                </td>
                            @endfor
                            <td class="border border-black px-2 py-1.5 font-black">{{ $r['nota_final'] ?? '—' }}</td>
                            <td class="border border-black px-2 py-1.5">
                                @if($r['aprobado'] === true)
                                    <span class="font-black text-emerald-700">APROBADO</span>
                                @elseif($r['aprobado'] === false)
                                    <span class="font-black text-rose-700">REPROBADO</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="border border-black px-3 py-6 text-center text-slate-500">No hay notas registradas para este estudiante.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Promedio general -->
            <div class="mt-6 flex justify-between items-end">
                <div class="text-left">
                    <p class="text-xs font-black uppercase text-slate-600">Promedio General</p>
                    <p class="text-3xl font-black {{ $promedioGeneral !== null && $promedioGeneral >= 60 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $promedioGeneral !== null ? number_format($promedioGeneral, 2) : '—' }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-slate-600">León, Nicaragua — {{ now()->format('d/m/Y') }}</p>
                    <p class="text-xs font-bold text-slate-500 mt-4">Firma y sello de la Dirección</p>
                </div>
            </div>
        </div>
        @else
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-12 text-center text-stone-500 font-bold">
                Seleccione un estudiante para generar su certificado de notas.
            </div>
        @endif
    </div>
</x-app-layout>