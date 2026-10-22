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
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Formatos Oficiales MINED</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Reportes de Rendimiento y Aplazados</p>
            </div>
            <div class="flex items-center gap-3 self-end sm:self-auto">
                <a href="{{ url('/academico/reportes') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                    Volver
                </a>
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2.5 rounded-xl shadow-sm text-xs uppercase tracking-widest transition-all cursor-pointer">
                    Imprimir Formato Oficial
                </button>
            </div>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- PANEL DE FILTROS -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 no-print relative overflow-hidden">
            <form method="GET" action="{{ request()->url() }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Tipo de Listado</label>
                    <select name="tipo" class="w-full border-slate-200 rounded-xl text-sm font-medium">
                        <option value="aprobados" @selected(request('tipo') == 'aprobados')>Estudiantes Aprobados</option>
                        <option value="1_2" @selected(request('tipo') == '1_2')>Aplazados de 1 a 2 Asignaturas</option>
                        <option value="3_mas" @selected(request('tipo') == '3_mas')>Aplazados de 3 a más Asignaturas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Corte Evaluativo</label>
                    <select name="corte_evaluativo_id" class="w-full border-slate-200 rounded-xl text-sm font-medium">
                        @foreach(\App\Models\CorteEvaluativo::orderBy('numero')->get() as $corte)
                            <option value="{{ $corte->id }}" @selected($corte->id == request('corte_evaluativo_id'))>{{ $corte->nombre ?? 'Corte '.$corte->numero }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Grado / Sección (Opcional)</label>
                    <select name="grado_id" class="w-full border-slate-200 rounded-xl text-sm font-medium">
                        <option value="">Todo el Colegio (General)</option>
                        @foreach(\App\Models\Grado::all() as $grado)
                            <option value="{{ $grado->id }}" @selected($grado->id == request('grado_id'))>{{ $grado->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="bg-[#3d2c1d] text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-black w-full shadow-sm">Generar Reporte</button>
                </div>
            </form>
        </div>

        <!-- ZONA DE IMPRESIÓN MINED -->
        <div id="zona-impresion" class="bg-white p-8 rounded-3xl print:rounded-none shadow-sm print:shadow-none space-y-6">
            
            <!-- ENCABEZADO OFICIAL MINED -->
            <div class="border-b-2 border-black pb-4 mb-4">
                <div class="grid grid-cols-12 items-center gap-2">
                    <!-- Logo Izquierdo (MINED / Gobierno) -->
                    <div class="col-span-2 flex justify-start items-center pl-2">
                        <img src="{{ asset('img/logos/mined_izq.png') }}" alt="Gobierno de Reconciliación y Unidad Nacional" class="h-14 w-auto object-contain">
                    </div>
                    
                    <!-- Títulos Centrales Oficiales -->
                    <div class="col-span-8 text-center space-y-0.5">
                        <h3 class="text-[10px] font-black uppercase text-black tracking-widest">Gobierno de Reconciliación y Unidad Nacional</h3>
                        <h4 class="text-[10px] font-bold uppercase text-black tracking-wide">Ministerio de Educación</h4>
                        <h2 class="text-[10px] font-bold uppercase text-black">Delegación Departamental de Educación - León</h2>
                        <h1 class="text-xs font-black uppercase text-black pt-1">
                            @if($tipo === 'aprobados')
                                LISTADO DE ESTUDIANTES APROBADOS
                            @elseif($tipo === '1_2')
                                LISTADO DE ESTUDIANTES APLAZADOS DE 1 A 2 ASIGNATURAS
                            @else
                                LISTADO DE ESTUDIANTES APLAZADOS DE 3 A MÁS ASIGNATURAS
                            @endif
                        </h1>
                    </div>

                    <!-- Logo Derecho Ministerial -->
                    <div class="col-span-2 flex justify-end items-center pr-2">
                        <img src="{{ asset('img/logos/mined_der.png') }}" alt="Ministerio de Educación" class="h-14 w-auto object-contain">
                    </div>
                </div>

                <!-- DATOS DEL CENTRO Y ESCUDO INSTITUCIONAL -->
                <div class="grid grid-cols-12 items-center pt-4 px-2 text-xs font-bold text-black">
                    <div class="col-span-5 space-y-0.5">
                        <p>CENTRO: COLEGIO CRISTIANO EN NICARAGUA</p>
                        <p>CODIGO UNICO: 27974</p>
                    </div>
                    <div class="col-span-2 flex justify-center items-center">
                        <img src="{{ asset('img/Logo.png') }}" alt="Logo Colegio" class="h-12 w-auto object-contain">
                    </div>
                    <div class="col-span-5 space-y-0.5 text-right">
                        <p>FECHA: {{ date('d/m/y') }}</p>
                        <p>CODIGO CENTRO: 27760</p>
                    </div>
                </div>
            </div>

            <!-- TABLA MINED -->
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-black text-xs">
                    <thead>
                        <tr class="bg-slate-200 text-black font-black uppercase text-center">
                            <th class="border border-black p-2 w-12">N°</th>
                            <th class="border border-black p-2">MODALIDAD</th>
                            <th class="border border-black p-2">NIVEL</th>
                            <th class="border border-black p-2">TURNO</th>
                            <th class="border border-black p-2">CODIGO</th>
                            <th class="border border-black p-2 text-left">NOMBRE COMPLETO</th>
                            <th class="border border-black p-2 w-16">SEXO</th>
                            @if($tipo !== 'aprobados')
                                <th class="border border-black p-2">ASIGNATURAS</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($filas as $fila)
                            <tr class="text-black font-medium">
                                <td class="border border-black p-1.5 text-center">{{ $fila['n'] }}</td>
                                <td class="border border-black p-1.5 text-center">{{ $fila['modalidad'] }}</td>
                                <td class="border border-black p-1.5 text-center">{{ $fila['nivel'] }}</td>
                                <td class="border border-black p-1.5 text-center">{{ $fila['turno'] }}</td>
                                <td class="border border-black p-1.5 text-center font-mono">{{ $fila['codigo'] }}</td>
                                <td class="border border-black p-1.5 font-bold uppercase">{{ $fila['nombre_completo'] }}</td>
                                <td class="border border-black p-1.5 text-center font-bold">{{ $fila['sexo'] }}</td>
                                @if($tipo !== 'aprobados')
                                    <td class="border border-black p-1.5 text-center font-bold text-rose-700">{{ $fila['asignaturas'] }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $tipo !== 'aprobados' ? 8 : 7 }}" class="border border-black p-6 text-center font-bold text-slate-500">
                                    No se encontraron registros bajo los criterios seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- FIRMA Y SELLO -->
            <div class="pt-16 text-center page-break-inside-avoid">
                <div class="w-72 mx-auto border-t-2 border-black pt-2">
                    <p class="text-xs font-black uppercase text-black">FIRMA Y SELLO DEL DIRECTOR</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>