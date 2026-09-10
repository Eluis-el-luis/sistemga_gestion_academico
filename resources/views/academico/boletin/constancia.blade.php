<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Registro Académico / Constancia de Notas</h2>
            <div class="flex gap-3">
                <a href="{{ route('academico.boletines.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
                <button onclick="window.print()" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir / PDF</button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm print:border-0 print:shadow-none">
            <!-- Encabezado institucional -->
            <div class="text-center mb-6">
                <p class="font-black text-lg uppercase tracking-wide">Colegio Cristiano en Nicaragua</p>
                <p class="text-sm text-slate-600 font-bold">Constancia de Calificaciones — Ciclo Escolar {{ $matricula->anioEscolar->nombre ?? '—' }}</p>
                <p class="text-xs text-slate-500 mt-1">Documento para trámites académicos (universidad, traslado, entre otros)</p>
            </div>

            <!-- Datos del estudiante -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-1 text-sm mb-6 border-t border-slate-200 pt-4">
                <p><span class="font-black text-slate-500">Estudiante:</span> <span class="font-black text-[#3d2c1d]">{{ $matricula->alumno->nombre_completo }}</span></p>
                <p><span class="font-black text-slate-500">Código de Persona:</span> <span class="font-bold">{{ $matricula->alumno->codigo_unico_persona ?? 'N/A' }}</span></p>
                <p><span class="font-black text-slate-500">Grado:</span> <span class="font-bold">{{ $matricula->aula->grado->nombre ?? '—' }}</span></p>
                <p><span class="font-black text-slate-500">Sección:</span> <span class="font-bold">{{ $matricula->aula->nombre ?? '—' }}</span></p>
                <p><span class="font-black text-slate-500">Modalidad:</span> <span class="font-bold">{{ $matricula->aula->modalidad->nombre ?? '—' }}</span></p>
                <p><span class="font-black text-slate-500">Docente Guía:</span> <span class="font-bold">{{ $matricula->aula->docenteGuia->usuario->nombre_completo ?? '—' }}</span></p>
            </div>

            <!-- Tabla de notas -->
            <table class="w-full border-collapse border border-black text-sm">
                <thead>
                    <tr class="bg-slate-50 text-center text-xs font-black uppercase">
                        <th class="border border-black px-3 py-2 text-left">Asignatura</th>
                        @foreach($cortes as $corte)
                            <th class="border border-black px-2 py-2">{{ $corte->numero }}° Corte</th>
                        @endforeach
                        <th class="border border-black px-2 py-2">Nota Final</th>
                        <th class="border border-black px-2 py-2">Indicador</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @forelse($filas as $fila)
                        <tr>
                            <td class="border border-black px-3 py-2 text-left font-bold">{{ $fila['asignatura'] }}</td>
                            @foreach($cortes as $corte)
                                <td class="border border-black px-2 py-2">{{ $fila['cortes'][$corte->numero] ?? '—' }}</td>
                            @endforeach
                            <td class="border border-black px-2 py-2 font-black">{{ $fila['nota_final'] ?? '—' }}</td>
                            <td class="border border-black px-2 py-2 font-black">{{ $fila['indicador'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($cortes) + 3 }}" class="border border-black px-3 py-6 text-center text-slate-500">No hay notas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Promedio general -->
            <div class="mt-6 flex justify-end">
                <div class="text-right">
                    <p class="text-xs font-black uppercase tracking-widest text-slate-500">Promedio General</p>
                    <p class="text-3xl font-black {{ $promedioGeneral !== null && $promedioGeneral >= 60 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $promedioGeneral !== null ? number_format($promedioGeneral, 2) : '—' }}
                    </p>
                </div>
            </div>

            <!-- Firma -->
            <div class="mt-16 flex justify-between print:mt-12">
                <div class="text-center">
                    <div class="border-t-2 border-black w-48 pt-1 font-black text-sm uppercase">Docente Guía</div>
                </div>
                <div class="text-center">
                    <div class="border-t-2 border-black w-48 pt-1 font-black text-sm uppercase">Dirección</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>