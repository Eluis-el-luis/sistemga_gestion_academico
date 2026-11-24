{{-- resources/views/academico/reportes/historial-estudiante.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs print:hidden">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Certificado de Notas (Formato MINED)</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white cursor-pointer">Volver</a>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 print:hidden">
                <form method="GET" action="{{ route('academico.reportes.historial-estudiante') }}" class="space-y-4 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">1. Buscar estudiante</label>
                            <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o CUP..." class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">2. Estudiante</label>
                            <select name="alumno_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                                <option value="">Seleccione un estudiante...</option>
                                @foreach ($alumnos ?? [] as $al)
                                    <option value="{{ $al->id }}" @selected(($alumnoId ?? null) == $al->id)>{{ $al->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($alumno && isset($matriculasAlumno) && $matriculasAlumno->isNotEmpty())
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">3. Año y Grado</label>
                            <select name="matricula_id" onchange="this.form.submit()" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm cursor-pointer">
                                @foreach ($matriculasAlumno as $m)
                                    <option value="{{ $m->id }}" @selected(($matriculaSel?->id ?? null) == $m->id)>
                                        Ciclo {{ $m->anioEscolar?->nombre ?? '—' }} · {{ $m->aula?->grado?->nombre ?? '—' }} "{{ $m->aula?->nombre ?? '—' }}"
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-center gap-3">
                        <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 font-bold px-5 py-2 rounded-xl text-xs uppercase cursor-pointer">Buscar</button>
                        <a href="{{ route('academico.reportes.historial-estudiante') }}" class="text-xs font-semibold text-neutral-500 cursor-pointer">Limpiar</a>
                    </div>
                </form>
            </div>

            @if ($alumno)
                @php
                    $gradoBaseInfo = collect($grados ?? [])->firstWhere('grado.id', $gradoSeleccionado?->id) ?? (!empty($grados) ? $grados[array_key_last($grados)] : null);
                    $matriculaBase = $gradoBaseInfo['matricula'] ?? $matricula;
                @endphp
                <div class="flex justify-end gap-3 print:hidden">
                    @if ($matriculaBase)
                        <a href="{{ route('academico.boletines.pdf.certificado', $matriculaBase) }}" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-4 py-2 rounded-xl text-xs uppercase cursor-pointer">Descargar PDF</a>
                    @endif
                    <button type="button" onclick="window.print()" class="bg-neutral-900 text-white font-bold px-4 py-2 rounded-xl text-xs uppercase cursor-pointer">Imprimir</button>
                </div>

                <div class="bg-white p-8 rounded-3xl border border-stone-300 text-neutral-900 text-xs space-y-4">
                    <div class="text-center border-b border-neutral-800 pb-3">
                        <p class="font-bold uppercase text-neutral-600">Ministerio de Educación</p>
                        <h1 class="font-bold text-lg uppercase">Colegio Cristiano en Nicaragua</h1>
                        <p class="font-bold underline mt-1">CERTIFICADO DE NOTAS</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 font-semibold">
                        <p>Estudiante: {{ $alumno->nombre_completo }}</p>
                        <p>CUP: {{ $alumno->codigo_unico_persona ?? 'N/A' }}</p>
                        <p>Nacimiento: {{ $alumno->fecha_nacimiento ? \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}</p>
                        <p>Sexo: {{ $alumno->sexo === 'M' ? 'Masculino' : ($alumno->sexo === 'F' ? 'Femenino' : '—') }}</p>
                    </div>

                    @if (!empty($grados))
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-neutral-800 text-xs">
                                <thead>
                                    <tr class="bg-stone-100 text-center font-bold uppercase">
                                        <th rowspan="2" class="border border-neutral-800 px-3 py-2 text-left">Asignatura</th>
                                        @foreach ($grados as $g)
                                            <th colspan="2" class="border border-neutral-800 px-2 py-1">{{ $g['grado']->nombre }}</th>
                                        @endforeach
                                    </tr>
                                    <tr class="bg-stone-100 text-center text-[10px] font-bold uppercase">
                                        @foreach ($grados as $g)
                                            <th class="border border-neutral-800 px-2 py-1">Cuan.</th>
                                            <th class="border border-neutral-800 px-2 py-1">Cual.</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="text-center">
                                    @forelse ($asignaturas ?? [] as $fila)
                                        <tr>
                                            <td class="border border-neutral-800 px-3 py-1.5 text-left font-semibold">{{ $fila['nombre'] }}</td>
                                            @foreach ($grados as $g)
                                                @php $n = $g['notas'][$fila['nombre']] ?? null; @endphp
                                                <td class="border border-neutral-800 px-2 py-1.5 font-bold">{{ $n && $n['cuan'] !== null ? $n['cuan'] : '—' }}</td>
                                                <td class="border border-neutral-800 px-2 py-1.5">{{ $n['cua'] ?? '—' }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ 1 + count($grados) * 2 }}" class="border border-neutral-800 px-3 py-6 text-center text-neutral-500">Sin asignaturas registradas.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>