<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Certificado de Notas (Formato MINED)</h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm print:hidden">← Volver</a>
        </div>
    </x-slot>

    <!-- El certificado oficial se imprime en vertical (carta) -->
    <style>
        @media print {
            @page { size: letter portrait; margin: 12mm; }
        }
    </style>

    <div class="pb-12 pt-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- BUSCADOR Y FILTROS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 print:hidden">
            <form method="GET" action="{{ route('academico.reportes.historial-estudiante') }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Buscar estudiante</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Nombre o código (CUP)..."
                           class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
                        @foreach($anios as $an)
                            <option value="{{ $an->id }}" @selected(($anioId ?? ($anio->id ?? null)) == $an->id)>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Grado</label>
                    <select name="grado_id" onchange="this.form.submit()" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
                        <option value="">Todos</option>
                        @foreach($gradosFiltro as $grado)
                            <option value="{{ $grado->id }}" @selected(($gradoId ?? null) == $grado->id)>{{ $grado->nombre }} ({{ $grado->modalidad->nombre ?? '' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Estudiante</label>
                    <select name="alumno_id" onchange="this.form.submit()" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
                        <option value="">Seleccione un estudiante...</option>
                        @foreach($alumnos as $al)
                            <option value="{{ $al->id }}" @selected(($alumnoId ?? null) == $al->id)>{{ $al->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2 xl:col-span-4 flex items-center gap-3">
                    <button type="submit" class="bg-[#3d2c1d] text-white font-black px-5 py-2 rounded-xl shadow-sm text-sm">Buscar</button>
                    <a href="{{ route('academico.reportes.historial-estudiante') }}" class="text-sm font-bold text-slate-400 hover:text-rose-600 py-2">Limpiar</a>
                </div>
            </form>
        </div>

        @if(!$alumno)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-12 text-center text-stone-500 font-bold print:hidden">
                Busca y selecciona un estudiante (puedes filtrar por grado y año escolar) para generar su certificado de notas.
            </div>
        @else
            @php
                $gradoBaseInfo = collect($grados)->firstWhere('grado.id', $gradoSeleccionado?->id) ?? (count($grados) ? $grados[array_key_last($grados)] : null);
                $matriculaBase = $gradoBaseInfo['matricula'] ?? $matricula;
            @endphp

            <!-- ACCIONES DEL DOCUMENTO -->
            <div class="flex justify-end gap-3 print:hidden">
                @if($matriculaBase)
                    <a href="{{ route('academico.boletines.pdf.certificado', $matriculaBase) }}" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Descargar PDF
                    </a>
                @endif
                <button onclick="window.print()" class="bg-[#3d2c1d] hover:bg-stone-800 text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
            </div>

            <!-- CERTIFICADO (FORMATO MINED) -->
            <div class="bg-white p-10 rounded-3xl border-2 border-black shadow-sm print:rounded-none print:border-2 print:border-black print:p-2 text-black">
                <!-- Datos del centro -->
                <div class="text-center mb-5 border-b-2 border-black pb-3">
                    <p class="font-black text-xs uppercase tracking-widest text-slate-600">Ministerio de Educación</p>
                    <h1 class="font-black text-xl uppercase tracking-wide">Colegio Cristiano en Nicaragua</h1>
                    <p class="font-black text-sm mt-1 underline">CERTIFICADO DE NOTAS</p>
                    <p class="text-xs mt-1 text-slate-700">
                        {{ $matriculaBase?->aula?->modalidad?->nombre ?? '—' }}
                        @if($matriculaBase?->anioEscolar) · Ciclo Escolar {{ $matriculaBase->anioEscolar->nombre }}@endif
                    </p>
                </div>

                <!-- Datos del estudiante -->
                <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm mb-5">
                    <p><span class="font-black uppercase text-xs text-slate-600">Estudiante:</span> <span class="font-black uppercase">{{ $alumno->nombre_completo }}</span></p>
                    <p><span class="font-black uppercase text-xs text-slate-600">Código de Persona (CUP):</span> <span class="font-bold">{{ $alumno->codigo_unico_persona ?? 'N/A' }}</span></p>
                    <p><span class="font-black uppercase text-xs text-slate-600">Fecha de Nacimiento:</span> <span class="font-bold">{{ $alumno->fecha_nacimiento ? \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}</span></p>
                    <p><span class="font-black uppercase text-xs text-slate-600">Sexo:</span> <span class="font-bold">{{ $alumno->sexo === 'M' ? 'Masculino' : ($alumno->sexo === 'F' ? 'Femenino' : '—') }}</span></p>
                </div>

                @if(count($grados) === 0)
                    <div class="border border-black px-4 py-6 text-center font-bold text-slate-600">
                        El estudiante no tiene matrícula registrada en el año y grado seleccionados.
                    </div>
                @else
                    <!-- Tabla comparativa por asignaturas -->
                    <table class="w-full border-collapse border border-black text-sm">
                        <thead>
                            <tr class="bg-slate-100 text-center text-xs font-black uppercase">
                                <th rowspan="2" class="border border-black px-3 py-2 text-left">Asignatura</th>
                                @foreach($grados as $g)
                                    <th colspan="2" class="border border-black px-2 py-1">
                                        {{ $g['grado']->nombre }}@if($g['anioEscolar']) <span class="block text-[10px] font-bold normal-case">Ciclo {{ $g['anioEscolar']->nombre }}@if($g['matricula']?->aula) · Sección "{{ $g['matricula']->aula->nombre }}"@endif</span>@endif
                                    </th>
                                @endforeach
                            </tr>
                            <tr class="bg-slate-100 text-center text-[10px] font-black uppercase">
                                @foreach($grados as $g)
                                    <th class="border border-black px-2 py-1">Cuant.</th>
                                    <th class="border border-black px-2 py-1">Cual.</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @forelse($asignaturas as $fila)
                                <tr>
                                    <td class="border border-black px-3 py-1.5 text-left font-bold uppercase">{{ $fila['nombre'] }}</td>
                                    @foreach($grados as $g)
                                        @php $n = $g['notas'][$fila['nombre']] ?? null; @endphp
                                        <td class="border border-black px-2 py-1.5 font-bold">{{ $n && $n['cuan'] !== null ? $n['cuan'] : '—' }}</td>
                                        <td class="border border-black px-2 py-1.5" title="{{ $n['cua_nombre'] ?? '' }}">{{ $n['cua'] ?? '—' }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 1 + count($grados) * 2 }}" class="border border-black px-3 py-6 text-center text-slate-500 font-bold">
                                        No hay asignaturas con notas registradas para los grados del certificado.
                                    </td>
                                </tr>
                            @endforelse
                            <!-- Promedio por grado -->
                            <tr class="bg-slate-100 text-center font-black">
                                <td class="border border-black px-3 py-1.5 text-left uppercase">Promedio del grado</td>
                                @foreach($grados as $g)
                                    <td class="border border-black px-2 py-1.5">{{ $g['promedio'] !== null ? number_format($g['promedio'], 2) : '—' }}</td>
                                    <td class="border border-black px-2 py-1.5" title="{{ $g['promedio_cua_nombre'] ?? '' }}">{{ $g['promedio_cua'] ?? '—' }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                @endif

                <!-- Escala cualitativa MINED -->
                <div class="mt-4 text-[10px] font-bold text-slate-600 uppercase tracking-wide">
                    Escala de logro: AA = Aprendizaje Avanzado (90-100) · AS = Aprendizaje Satisfactorio (76-89) · AF = Aprendizaje Fundamental (60-75) · AI = Aprendizaje Inicial (0-59)
                </div>

                <!-- Firmas -->
                <div class="mt-2 flex justify-between items-end">
                    <p class="text-sm font-bold text-slate-600">León, Nicaragua — {{ now()->timezone('America/Managua')->format('d/m/Y') }}</p>
                    <div class="text-center">
                        <div class="border-t border-black w-56 pt-1 mt-10 text-xs font-black uppercase">Dirección del Centro</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
