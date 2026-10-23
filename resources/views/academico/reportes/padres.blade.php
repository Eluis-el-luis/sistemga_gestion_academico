<x-app-layout>
    <style>
        @media print {
            body * { visibility: hidden; }
            #zona-impresion, #zona-impresion * { visibility: visible; }
            #zona-impresion { position: absolute; left: 0; top: 0; width: 100%; border: none !important; }
            @page { size: landscape; margin: 10mm; }
            .no-print { display: none !important; }
        }
    </style>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Directorio de Responsables</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Contactos de Emergencia y Adopción Digital</p>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ url('/academico/reportes') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir Directorio
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- PANEL DE FILTROS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-700 via-sky-500 to-blue-700"></div>
            
            <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
                    <select name="anio_escolar_id" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-blue-500">
                        @foreach($anios ?? \App\Models\AnioEscolar::orderBy('id', 'desc')->get() as $an)
                            <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id', $anio?->id))>
                                {{ $an->nombre }} {{ $an->activo ? '(Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5 text-blue-600">Filtrar por Aula (Para Maestro Guía)</label>
                    <select name="aula_id" class="w-full border-blue-200 bg-blue-50 rounded-xl text-sm font-medium focus:ring-blue-500">
                        <option value="">Todo el Colegio</option>
                        @foreach($aulas ?? \App\Models\Aula::with('grado')->get() as $aulaOp)
                            <option value="{{ $aulaOp->id }}" @selected($aulaOp->id == request('aula_id'))>
                                {{ $aulaOp->grado->nombre ?? '' }} - {{ $aulaOp->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-[#3d2c1d] text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-black w-full shadow-sm">Generar Directorio</button>
                </div>
            </form>
        </div>

        <div id="zona-impresion" class="space-y-6">
            
            <!-- MEMBRETE DE IMPRESIÓN (SOLO VISIBLE AL IMPRIMIR) -->
            <div class="hidden print:block mb-6">
                <div class="flex items-center justify-between border-b-2 border-[#3d2c1d] pb-4">
                    <div>
                        <h1 class="text-xl font-black uppercase tracking-wider text-black">Directorio de Contactos de Emergencia</h1>
                        <h2 class="text-sm font-bold text-slate-600">Colegio Cristiano en Nicaragua</h2>
                    </div>
                    <div class="text-right">
                        <span class="block text-sm font-black text-[#3d2c1d]">
                            {{ $aula ? ($aula->grado->nombre . ' - ' . $aula->nombre) : 'Directorio General' }}
                        </span>
                        <span class="block text-xs font-bold text-slate-500">Ciclo: {{ $anio->nombre ?? date('Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- TARJETAS DE MÉTRICAS (VISIBLES EN PANTALLA) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 no-print">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                    <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Evaluados</span>
                    <span class="block text-3xl font-black text-[#3d2c1d] mt-1">{{ $total ?? 0 }}</span>
                </div>
                <div class="bg-sky-50 rounded-2xl border border-sky-100 p-6 text-center">
                    <span class="block text-[10px] font-black text-sky-600 uppercase tracking-widest">Con Teléfono de Rescate</span>
                    <span class="block text-3xl font-black text-sky-700 mt-1">{{ $con_telefono ?? 0 }}</span>
                </div>
                <div class="bg-blue-50 rounded-2xl border border-blue-100 p-6 text-center">
                    <span class="block text-[10px] font-black text-blue-600 uppercase tracking-widest">Nivel de Adopción (Al menos un N°)</span>
                    <span class="block text-3xl font-black text-blue-700 mt-1">{{ $porcentaje_adopcion ?? 0 }}%</span>
                </div>
            </div>

            <!-- BARRA DE PROGRESO DE ADOPCIÓN -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 no-print">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Cobertura Telefónica del Grupo</span>
                    <span class="text-sm font-black text-blue-600">{{ $porcentaje_adopcion ?? 0 }}%</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-3">
                    <div class="bg-gradient-to-r from-sky-400 to-blue-500 h-3 rounded-full transition-all duration-1000" style="width: {{ $porcentaje_adopcion ?? 0 }}%"></div>
                </div>
            </div>

            <!-- TABLA DEL DIRECTORIO -->
            <div class="bg-white rounded-3xl print:rounded-xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
                <div class="overflow-x-auto print:overflow-visible">
                    <table class="min-w-full divide-y divide-slate-200 text-sm print:text-xs border-collapse">
                        <thead class="bg-[#FFFDF5] print:bg-slate-100 text-slate-500 print:text-[#3d2c1d] uppercase text-[10px] font-black tracking-wider">
                            <tr>
                                <th class="px-5 py-4 text-left">Estudiante</th>
                                <th class="px-5 py-4 text-left print:hidden">Grado/Sección</th>
                                <th class="px-5 py-4 text-left">Madre</th>
                                <th class="px-5 py-4 text-left">Padre</th>
                                <th class="px-5 py-4 text-left">Tutor</th>
                                <th class="px-5 py-4 text-left">Autorizado Retiro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($filas ?? [] as $fila)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-4 font-black text-[#3d2c1d]">{{ $fila['estudiante'] }}<br><span class="text-[10px] font-mono font-normal text-slate-400">CUP: {{ $fila['cup'] }}</span></td>
                                    <td class="px-5 py-4 font-bold text-slate-600 print:hidden">{{ $fila['grado_aula'] }}</td>
                                    <td class="px-5 py-4 text-sm text-slate-700 leading-tight">{!! $fila['madre'] !!}</td>
                                    <td class="px-5 py-4 text-sm text-slate-700 leading-tight">{!! $fila['padre'] !!}</td>
                                    <td class="px-5 py-4 text-sm text-slate-700 leading-tight">{!! $fila['tutor'] !!}</td>
                                    <td class="px-5 py-4 text-sm text-slate-700 leading-tight">{!! $fila['autorizado'] !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <p class="text-slate-500 font-bold">No hay estudiantes matriculados en los filtros seleccionados.</p>
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