<x-app-layout>
    <style>
        @media print {
            body * { visibility: hidden; }
            #zona-impresion, #zona-impresion * { visibility: visible; }
            #zona-impresion { position: absolute; left: 0; top: 0; width: 100%; border: none !important; }
            @page { size: portrait; margin: 10mm; }
            .no-print { display: none !important; }
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Asistencia por Sección (Día)</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Auditoría detallada por estudiante</p>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ url('/academico/reportes') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                @if($aula)
                    <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Imprimir Lista
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- PANEL DE FILTROS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-700 via-indigo-500 to-blue-700"></div>
            
            <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Fecha</label>
                    <input type="date" name="fecha" value="{{ $fecha }}" required class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-blue-500">
                        @foreach($anios ?? \App\Models\AnioEscolar::orderBy('id', 'desc')->get() as $an)
                            <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id', $anio?->id))>
                                {{ $an->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5 text-rose-600">Aula / Sección (Requerido)</label>
                    <select name="aula_id" required class="w-full border-rose-200 bg-rose-50 rounded-xl text-sm font-medium focus:ring-rose-500">
                        <option value="">Seleccione el Aula...</option>
                        @foreach($aulas ?? \App\Models\Aula::with('grado')->get() as $aulaOp)
                            <option value="{{ $aulaOp->id }}" @selected($aulaOp->id == request('aula_id'))>
                                {{ $aulaOp->grado->nombre ?? '' }} - {{ $aulaOp->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-[#3d2c1d] text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-black w-full shadow-sm">Ver Asistencia</button>
                </div>
            </form>
        </div>

        @if($aula)
            <div id="zona-impresion" class="space-y-6">
                <!-- MEMBRETE -->
                <div class="hidden print:block mb-6">
                    <div class="flex items-center justify-between border-b-2 border-[#3d2c1d] pb-4">
                        <div>
                            <h1 class="text-xl font-black uppercase tracking-wider text-black">Lista de Asistencia Diaria</h1>
                            <h2 class="text-sm font-bold text-slate-600">Colegio Cristiano en Nicaragua</h2>
                        </div>
                        <div class="text-right">
                            <span class="block text-sm font-black text-[#3d2c1d]">Sección: {{ $aula->grado->nombre ?? '' }} - {{ $aula->nombre }}</span>
                            <span class="block text-xs font-bold text-slate-500">Fecha: {{ \Carbon\Carbon::parse($fecha)->translatedFormat('l, d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- TARJETAS DE MÉTRICAS -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 no-print">
                    <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Matrícula</span>
                        <span class="block text-2xl font-black text-[#3d2c1d] mt-1">{{ $totales['total'] }}</span>
                    </div>
                    <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-4 text-center">
                        <span class="block text-[10px] font-black text-emerald-600 uppercase tracking-widest">Presentes</span>
                        <span class="block text-2xl font-black text-emerald-700 mt-1">{{ $totales['presentes'] }}</span>
                    </div>
                    <div class="bg-rose-50 rounded-2xl border border-rose-100 p-4 text-center">
                        <span class="block text-[10px] font-black text-rose-600 uppercase tracking-widest">Ausentes</span>
                        <span class="block text-2xl font-black text-rose-700 mt-1">{{ $totales['ausentes'] }}</span>
                    </div>
                    <div class="bg-amber-50 rounded-2xl border border-amber-100 p-4 text-center">
                        <span class="block text-[10px] font-black text-amber-600 uppercase tracking-widest">Justificadas</span>
                        <span class="block text-2xl font-black text-amber-700 mt-1">{{ $totales['justificadas'] }}</span>
                    </div>
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-4 text-center relative">
                        @if($totales['sin_registrar'] > 0)
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 rounded-full animate-pulse"></div>
                        @endif
                        <span class="block text-[10px] font-black text-slate-500 uppercase tracking-widest">Sin Registrar</span>
                        <span class="block text-2xl font-black text-slate-700 mt-1">{{ $totales['sin_registrar'] }}</span>
                    </div>
                </div>

                <!-- BARRA DE ASISTENCIA -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 no-print">
                    <div class="flex justify-between items-end mb-2">
                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Presencialidad del Grupo</span>
                        <span class="text-sm font-black {{ $porcentaje >= 80 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $porcentaje }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $porcentaje >= 80 ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ $porcentaje }}%"></div>
                    </div>
                </div>

                <!-- TABLA DE ALUMNOS -->
                <div class="bg-white rounded-3xl print:rounded-xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
                    <div class="overflow-x-auto print:overflow-visible">
                        <table class="min-w-full divide-y divide-slate-200 text-sm print:text-xs border-collapse">
                            <thead class="bg-[#FFFDF5] print:bg-slate-100 text-slate-500 print:text-[#3d2c1d] uppercase text-[10px] font-black tracking-wider">
                                <tr>
                                    <th class="px-5 py-4 text-center w-12">N°</th>
                                    <th class="px-5 py-4 text-left">Estudiante</th>
                                    <th class="px-5 py-4 text-center">Estado de Asistencia</th>
                                    <th class="px-5 py-4 text-left">Observaciones / Justificación</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($filas ?? [] as $index => $fila)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-5 py-4 text-center font-bold text-slate-400">{{ $index + 1 }}</td>
                                        <td class="px-5 py-4 font-black text-[#3d2c1d]">{{ $fila['estudiante'] }} <span class="text-[10px] font-mono text-slate-400 ml-1">({{ $fila['cup'] }})</span></td>
                                        <td class="px-5 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-black border {{ $fila['color_tema'] }}">
                                                {{ mb_strtoupper($fila['estado']) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 font-medium text-slate-500 text-xs">{{ $fila['observacion'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-500 font-bold">
                                            No hay estudiantes matriculados en esta sección.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- ESTADO VACÍO (CUANDO NO SE HA SELECCIONADO AULA) -->
            <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-12 text-center no-print">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 text-indigo-600 mb-4 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-indigo-900 mb-2">Seleccione una Sección</h3>
                <p class="text-indigo-700/80 max-w-md mx-auto text-sm font-medium">Para visualizar la lista de asistencia exacta y auditar los registros, seleccione un aula en los filtros superiores.</p>
            </div>
        @endif
    </div>
</x-app-layout>