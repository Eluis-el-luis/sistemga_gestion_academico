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
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Estadísticas de Asistencia por Estudiante</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Ficha Individual y Control de Inasistencias</p>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ url('/academico/reportes') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir Fichas
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- PANEL DE FILTROS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-700 via-teal-500 to-emerald-700"></div>
            
            <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Aula / Sección</label>
                    <select name="aula_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-emerald-500">
                        <option value="">Todas las Aulas</option>
                        @foreach($aulas ?? \App\Models\Aula::with('grado')->get() as $aulaOp)
                            <option value="{{ $aulaOp->id }}" @selected($aulaOp->id == request('aula_id'))>
                                {{ $aulaOp->grado->nombre ?? '' }} - {{ $aulaOp->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Buscar Estudiante</label>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre del alumno..." class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ $fecha_inicio ?? now()->subMonth()->toDateString() }}" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ $fecha_fin ?? now()->toDateString() }}" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-emerald-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-[#3d2c1d] text-white px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-black flex-1 shadow-sm">Filtrar</button>
                    <a href="{{ request()->url() }}" class="bg-slate-100 text-slate-600 px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-slate-200 text-center flex items-center justify-center">Limpiar</a>
                </div>
            </form>
        </div>

        <div id="zona-impresion" class="space-y-6">
            <!-- MEMBRETE DE IMPRESIÓN -->
            <div class="hidden print:block mb-6">
                <div class="flex items-center justify-between border-b-2 border-[#3d2c1d] pb-4">
                    <div>
                        <h1 class="text-xl font-black uppercase tracking-wider text-black">Consolidado de Asistencia Individual</h1>
                        <h2 class="text-sm font-bold text-slate-600">Colegio Cristiano en Nicaragua</h2>
                    </div>
                    <div class="text-right">
                        <span class="block text-xs font-bold text-slate-500">Período: {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- TABLA PRINCIPAL -->
            <div class="bg-white rounded-3xl print:rounded-xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
                <div class="overflow-x-auto print:overflow-visible">
                    <table class="min-w-full divide-y divide-slate-200 text-sm print:text-xs border-collapse">
                        <thead class="bg-[#FFFDF5] print:bg-slate-100 text-slate-500 print:text-[#3d2c1d] uppercase text-[10px] font-black tracking-wider">
                            <tr>
                                <th class="px-5 py-4 text-left w-24">CUP</th>
                                <th class="px-5 py-4 text-left">Estudiante</th>
                                <th class="px-5 py-4 text-left">Aula</th>
                                <th class="px-5 py-4 text-center">Registros</th>
                                <th class="px-5 py-4 text-center text-emerald-600">Presentes</th>
                                <th class="px-5 py-4 text-center text-rose-600">Ausentes</th>
                                <th class="px-5 py-4 text-center text-amber-600">Justificadas</th>
                                <th class="px-5 py-4 text-center">% Asistencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($filas ?? [] as $fila)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-4 font-mono text-xs font-bold text-slate-500">{{ $fila['cup'] }}</td>
                                    <td class="px-5 py-4 font-black text-[#3d2c1d]">{{ $fila['estudiante'] }}</td>
                                    <td class="px-5 py-4 font-bold text-slate-600 text-xs">{{ $fila['aula'] }}</td>
                                    <td class="px-5 py-4 text-center font-bold text-slate-700">{{ $fila['registros'] }}</td>
                                    <td class="px-5 py-4 text-center font-black text-emerald-600">{{ $fila['presentes'] }}</td>
                                    <td class="px-5 py-4 text-center font-black {{ $fila['ausentes'] > 0 ? 'text-rose-600' : 'text-slate-300' }}">{{ $fila['ausentes'] }}</td>
                                    <td class="px-5 py-4 text-center font-bold {{ $fila['justificadas'] > 0 ? 'text-amber-600' : 'text-slate-300' }}">{{ $fila['justificadas'] }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-black {{ $fila['porcentaje'] >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                            {{ $fila['porcentaje'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-500 font-bold">
                                        No se encontraron registros de estudiantes con los filtros aplicados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>