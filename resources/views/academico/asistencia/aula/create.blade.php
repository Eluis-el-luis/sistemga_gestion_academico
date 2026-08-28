<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/30 shadow-sm shrink-0">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                        Pase de Lista (Guía)
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 font-medium flex items-center gap-2">
                        Aula: <span class="font-black text-[#e6ac27]">{{ $aula->grado->nombre }} - {{ $aula->nombre }}</span> 
                        <span class="text-slate-300">|</span> 
                        Cupo: {{ $aula->cupo }}
                    </p>
                </div>
            </div>
            
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al Panel">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alertas de Éxito o Error -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Selector de Fecha -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-black text-[#3d2c1d] text-lg">Fecha de Asistencia</h3>
                    <p class="text-xs font-medium text-slate-500 mt-1">Seleccione un día anterior si desea actualizar un registro pasado.</p>
                </div>
                <form method="GET" action="{{ route('academico.asistencia.aula.create') }}" class="flex items-center gap-3 w-full sm:w-auto">
                    <input type="date" name="fecha" value="{{ $fecha }}" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] font-bold text-[#3d2c1d] w-full sm:w-auto transition-colors" required>
                    <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-5 rounded-xl transition-colors shadow-sm text-sm">
                        Cambiar
                    </button>
                </form>
            </div>

            <!-- Formulario Principal de Asistencia -->
            <form action="{{ route('academico.asistencia.aula.store') }}" method="POST">
                @csrf
                <input type="hidden" name="fecha" value="{{ $fecha }}">

                <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-[#FFFDF5] text-[#3d2c1d] border-b border-[#e6ac27]/20">
                                <tr>
                                    <th class="px-6 py-5 text-left w-12 text-xs font-black uppercase tracking-widest">#</th>
                                    <th class="px-6 py-5 text-left min-w-[200px] text-xs font-black uppercase tracking-widest">Estudiante</th>
                                    <th class="px-6 py-5 text-center text-xs font-black uppercase tracking-widest">Estado de Asistencia</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @forelse ($matriculas as $index => $matricula)
                                    @php
                                        $estadoActual = isset($asistenciasPrevias[$matricula->id]) 
                                                        ? $asistenciasPrevias[$matricula->id]->estado_asistencia 
                                                        : 'Presente';
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-slate-400">{{ $loop->iteration }}</td>
                                        
                                        <td class="px-6 py-4">
                                            <input type="hidden" name="asistencias[{{ $index }}][matricula_id]" value="{{ $matricula->id }}">
                                            <div class="font-black text-[#3d2c1d]">{{ $matricula->alumno->nombre_completo }}</div>
                                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $matricula->alumno->codigo_unico_persona }}</div>
                                            
                                            @php
                                                $alertas = $incidenciasHoy->where('matricula_id', $matricula->id);
                                            @endphp
                                            
                                            @if($alertas->isNotEmpty())
                                                <div class="mt-2 flex flex-col items-start gap-1">
                                                    @foreach($alertas as $incidencia)
                                                        <div class="text-[10px] font-bold text-rose-700 bg-rose-50 inline-block px-2 py-0.5 rounded-lg border border-rose-200 shadow-sm">
                                                            ⚠ {{ $incidencia->estado_incidencia }} en {{ optional($incidencia->asignatura)->nombre ?? 'su clase' }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                
                                                <!-- Presente (Verde) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Presente" class="peer sr-only" {{ $estadoActual === 'Presente' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border peer-checked:bg-emerald-500 peer-checked:text-white peer-checked:border-emerald-600 bg-white text-slate-500 border-slate-200 hover:bg-slate-50 shadow-sm">
                                                        Presente
                                                    </div>
                                                </label>

                                                <!-- Ausencia Injustificada (Rojo) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Ausencia Injustificada" class="peer sr-only" {{ $estadoActual === 'Ausencia Injustificada' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border peer-checked:bg-rose-500 peer-checked:text-white peer-checked:border-rose-600 bg-white text-slate-500 border-slate-200 hover:bg-slate-50 shadow-sm">
                                                        Falta
                                                    </div>
                                                </label>

                                                <!-- Ausencia Justificada (Amarillo) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Ausencia Justificada" class="peer sr-only" {{ $estadoActual === 'Ausencia Justificada' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border peer-checked:bg-amber-400 peer-checked:text-amber-900 peer-checked:border-amber-500 bg-white text-slate-500 border-slate-200 hover:bg-slate-50 shadow-sm">
                                                        Justificada
                                                    </div>
                                                </label>

                                                <!-- Retiro Anticipado (Naranja) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Retiro Anticipado" class="peer sr-only" {{ $estadoActual === 'Retiro Anticipado' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border peer-checked:bg-orange-500 peer-checked:text-white peer-checked:border-orange-600 bg-white text-slate-500 border-slate-200 hover:bg-slate-50 shadow-sm">
                                                        Se Retiró
                                                    </div>
                                                </label>

                                                <!-- Actividad Institucional (Azul) -->
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="asistencias[{{ $index }}][estado_asistencia]" value="Actividad Institucional" class="peer sr-only" {{ $estadoActual === 'Actividad Institucional' ? 'checked' : '' }}>
                                                    <div class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all border peer-checked:bg-blue-500 peer-checked:text-white peer-checked:border-blue-600 bg-white text-slate-500 border-slate-200 hover:bg-slate-50 shadow-sm">
                                                        Actividad
                                                    </div>
                                                </label>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-slate-400 font-bold">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                No hay alumnos activos matriculados en esta aula.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Botón Flotante para Guardar Rápido -->
                <div class="mt-8 flex justify-end sticky bottom-6 z-20">
                    <button type="submit" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-3.5 px-8 rounded-2xl shadow-xl shadow-[#e6ac27]/20 transition-transform transform hover:-translate-y-1 flex items-center gap-2 text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        Guardar Pase de Lista
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>