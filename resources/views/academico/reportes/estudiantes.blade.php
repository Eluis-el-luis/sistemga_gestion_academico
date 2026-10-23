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
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Auditoría de Matrícula</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Control Poblacional y Expedientes de Secretaría</p>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ url('/academico/reportes') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Volver
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H8v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir Reporte
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- PANEL DE FILTRO SUPERIOR -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-700 via-emerald-500 to-emerald-700"></div>
            <form method="GET" action="{{ url('/academico/reportes/estudiantes') }} class="flex flex-col sm:flex-row items-end gap-4">
                <div class="w-full sm:w-64">
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Consultar Año Escolar</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="w-full border-slate-200 rounded-xl text-sm font-medium focus:ring-emerald-500">
                        @foreach(\App\Models\AnioEscolar::orderBy('id', 'desc')->get() as $an)
                            <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id', $anio['id'] ?? null))>
                                {{ $an->nombre }} {{ $an->activo ? '(Activo)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <div id="zona-impresion" class="space-y-6">
            <!-- MEMBRETE OFICIAL -->
            <div class="hidden print:block mb-8">
                <div class="flex items-center justify-between border-b-2 border-[#3d2c1d] pb-4">
                    <div>
                        <h1 class="text-xl font-black uppercase tracking-wider text-black">Auditoría de Matrícula y Expedientes</h1>
                        <h2 class="text-sm font-bold text-slate-600">Colegio Cristiano en Nicaragua</h2>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 bg-slate-100 border border-slate-300 text-slate-800 font-bold rounded-lg text-xs">
                            Ciclo: {{ $anio['nombre'] ?? date('Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- TARJETAS DE MÉTRICAS -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col justify-center print:border-slate-300 print:shadow-none">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Histórico</span>
                    <span class="text-3xl font-black text-[#3d2c1d] mt-1">{{ $total_alumnos ?? 0 }}</span>
                    <span class="text-xs font-bold text-slate-500 mt-1">Alumnos registrados</span>
                </div>
                
                <div class="bg-emerald-50 rounded-2xl border border-emerald-100 p-6 flex flex-col justify-center print:border-slate-300 print:bg-white print:shadow-none">
                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Activos</span>
                    <span class="text-3xl font-black text-emerald-700 mt-1">{{ $matriculados ?? 0 }}</span>
                    <span class="text-xs font-bold text-emerald-600 mt-1">Matriculados este ciclo</span>
                </div>

                <div class="bg-rose-50 rounded-2xl border border-rose-100 p-6 flex flex-col justify-center print:border-slate-300 print:bg-white print:shadow-none">
                    <span class="text-[10px] font-black text-rose-600 uppercase tracking-widest">Retirados / Bajas</span>
                    <span class="text-3xl font-black text-rose-700 mt-1">{{ $retirados ?? 0 }}</span>
                    <span class="text-xs font-bold text-rose-600 mt-1">En el ciclo actual</span>
                </div>

                <div class="bg-amber-50 rounded-2xl border border-amber-100 p-6 flex flex-col justify-center relative overflow-hidden print:border-slate-300 print:bg-white print:shadow-none">
                    <div class="absolute right-0 top-0 w-16 h-full bg-gradient-to-l from-amber-200/50 to-transparent"></div>
                    <span class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Atención Requerida</span>
                    <span class="text-3xl font-black text-amber-600 mt-1">{{ $expedientes_incompletos ?? 0 }}</span>
                    <span class="text-xs font-bold text-amber-700 mt-1">Expedientes incompletos</span>
                </div>
            </div>

            <!-- TABLA DE EXPEDIENTES INCOMPLETOS -->
            <div class="bg-white rounded-3xl print:rounded-xl shadow-sm print:shadow-none border border-slate-200 print:border-2 print:border-[#3d2c1d]/20 overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-slate-800">Alerta de Expedientes Incompletos</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Alumnos matriculados a los que les faltan datos obligatorios de contacto o legales.</p>
                    </div>
                </div>
                
                <div class="overflow-x-auto print:overflow-visible">
                    <table class="min-w-full divide-y divide-slate-200 text-sm print:text-xs border-collapse">
                        <thead class="bg-white text-slate-500 uppercase text-[10px] font-black tracking-wider">
                            <tr>
                                <th class="px-5 py-4 text-left w-24">CUP</th>
                                <th class="px-5 py-4 text-left">Estudiante</th>
                                <th class="px-5 py-4 text-left">Grado y Sección</th>
                                <th class="px-5 py-4 text-left text-amber-600">Datos Faltantes</th>
                                <th class="px-5 py-4 text-left">Contacto Principal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($lista_incompletos ?? [] as $fila)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-4 font-mono text-xs font-bold text-slate-500">{{ $fila['cup'] }}</td>
                                    <td class="px-5 py-4 font-black text-[#3d2c1d]">{{ $fila['alumno'] }}</td>
                                    <td class="px-5 py-4 font-bold text-slate-600">{{ $fila['grado_aula'] }}</td>
                                    <td class="px-5 py-4 font-bold text-amber-600 text-[11px] leading-relaxed">
                                        {{ $fila['faltantes'] }}
                                    </td>
                                    <td class="px-5 py-4 font-medium text-slate-600">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ $fila['telefono'] }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mb-3">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <p class="text-slate-500 font-bold">¡Excelente trabajo! Todos los alumnos matriculados tienen sus expedientes completos.</p>
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