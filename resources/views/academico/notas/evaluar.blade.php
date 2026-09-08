<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-black text-[#3d2c1d] tracking-tight flex items-center gap-2">
                    <a href="{{ route('academico.notas.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    </a>
                    Registro de Calificaciones
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1 ml-8">
                    {{ $asignacion->asignatura->nombre }} | {{ $asignacion->aula->grado->nombre }} - {{ $asignacion->aula->nombre }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('academico.notas.evaluar', $asignacion->id) }}" method="GET" class="flex items-center gap-3">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Parcial:</label>
                    <select name="corte_evaluativo_id" onchange="this.form.submit()" class="border-slate-200 bg-white rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] font-bold shadow-sm cursor-pointer">
                        @foreach($cortes as $corte)
                            <option value="{{ $corte->id }}" {{ $corteSeleccionado == $corte->id ? 'selected' : '' }}>
                                Corte {{ $corte->numero }} ({{ $corte->anioEscolar->nombre }})
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen bg-slate-50 relative">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- GUÍA DE PESOS -->
            @if($corteActivo)
            <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Peso Acumulado</p>
                    <p class="text-xl font-black text-[#3d2c1d]">{{ $sumaAcumulado }} / {{ $pesoAcumulado }} pts</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Peso Examen</p>
                    <p class="text-xl font-black text-[#3d2c1d]">{{ $sumaExamen }} / {{ $pesoExamen }} pts</p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Estado</p>
                    <p class="text-xl font-black {{ $estaBloqueado ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $estaBloqueado ? 'Parcial Cerrado' : 'Edición Abierta' }}
                    </p>
                </div>
            </div>
            @endif

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm font-medium">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm font-medium">{{ session('error') }}</div>
            @endif

            @if($actividades->isEmpty())
                <div class="bg-white p-10 rounded-3xl border border-slate-200 shadow-sm text-center">
                    <h3 class="text-xl font-black text-[#3d2c1d] mb-2">No hay actividades configuradas</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">Debes configurar la distribución de puntos antes de ingresar calificaciones.</p>
                    <a href="{{ route('academico.notas.actividades.index', $asignacion->id) }}" class="inline-block px-8 py-3 bg-[#e6ac27] hover:bg-amber-500 text-[#3d2c1d] font-black rounded-xl shadow-md transition-all">
                        Configurar Actividades
                    </a>
                </div>
            @else
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                    <form id="form-calificaciones" action="{{ route('academico.notas.store', $asignacion->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="corte_evaluativo_id" value="{{ $corteSeleccionado }}">

                        <div class="overflow-x-auto custom-scrollbar">
                            <table class="w-full text-left border-collapse min-w-[800px]">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th class="px-6 py-4 font-black text-xs text-slate-500 uppercase tracking-widest sticky left-0 bg-slate-50 z-10 shadow-[1px_0_0_0_#f1f5f9]">Estudiante</th>
                                        @foreach($actividades as $actividad)
                                            <th class="px-4 py-3 text-center">
                                                <span class="block text-[11px] font-black text-[#3d2c1d] uppercase tracking-widest">{{ $actividad->nombre }}</span>
                                                <span class="block text-[10px] text-slate-400 font-bold mt-1">Máx: {{ $actividad->puntaje_maximo }} pts</span>
                                            </th>
                                        @endforeach
                                        <th class="px-6 py-3 text-center border-l border-slate-200 bg-slate-100/50">
                                            <span class="block text-xs font-black text-[#3d2c1d] uppercase tracking-widest">Nota Final</span>
                                            <span class="block text-[10px] text-slate-400 font-bold mt-1">Auto-suma</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($matriculas as $index => $matricula)
                                        @php
                                            $notaCorte = $matricula->notas->firstWhere('corte_evaluativo_id', $corteSeleccionado);
                                            $totalInicial = $notaCorte ? ($notaCorte->nota_cuantitativa ?? 0) : 0;
                                        @endphp
                                        <tr class="hover:bg-slate-50/50 transition-colors group"
                                            x-data="{
                                                totalFila: {{ $totalInicial }},
                                                recalcular() {
                                                    let suma = 0;
                                                    this.$el.querySelectorAll('.input-nota').forEach(input => {
                                                        suma += parseFloat(input.value) || 0;
                                                    });
                                                    this.totalFila = suma.toFixed(2).replace(/\.00$/, '');
                                                }
                                            }">
                                            <td class="px-6 py-4 sticky left-0 bg-white group-hover:bg-slate-50/50 transition-colors z-10 shadow-[1px_0_0_0_#f1f5f9]">
                                                <p class="font-black text-sm text-[#3d2c1d]">{{ $matricula->alumno->nombre_completo }}</p>
                                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ $matricula->alumno->codigo_unico_persona }}</p>
                                            </td>

                                            @foreach($actividades as $actividad)
                                                @php
                                                    $na = isset($notasActividades[$matricula->id])
                                                        ? $notasActividades[$matricula->id]->firstWhere('actividad_evaluativa_id', $actividad->id)
                                                        : null;
                                                    $notaExistente = $na ? ($na->nota_obtenida ?? '') : '';
                                                @endphp
                                                <td class="px-4 py-3 text-center">
                                                    <input type="number"
                                                           name="notas[{{ $matricula->id }}][{{ $actividad->id }}]"
                                                           value="{{ $notaExistente }}"
                                                           max="{{ $actividad->puntaje_maximo }}"
                                                           min="0" step="0.01"
                                                           @input="recalcular"
                                                           {{ $estaBloqueado ? 'readonly' : '' }}
                                                           class="input-nota w-16 text-center font-bold text-sm text-[#3d2c1d] border-slate-200 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] placeholder:text-slate-300 shadow-sm transition-colors bg-slate-50/50 focus:bg-white py-2 {{ $estaBloqueado ? 'opacity-60 cursor-not-allowed' : '' }}">
                                                </td>
                                            @endforeach

                                            <td class="px-6 py-3 text-center border-l border-slate-200 bg-slate-50/50">
                                                <span class="text-lg font-black text-[#3d2c1d]" x-text="totalFila"></span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(!$estaBloqueado)
                        <div class="p-6 flex justify-end gap-3 border-t border-slate-100">
                            <a href="{{ route('academico.notas.index') }}" class="px-5 py-2.5 text-sm font-bold text-[#3d2c1d] border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">Cancelar</a>
                            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white text-sm font-black rounded-xl shadow-sm transition-transform transform hover:-translate-y-0.5">
                                Guardar Calificaciones
                            </button>
                        </div>
                        @endif
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>