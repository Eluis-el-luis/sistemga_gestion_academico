<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Apoyo Familiar</h2>
                <p class="text-sm font-medium text-slate-500 mt-1">Evaluación de involucramiento en el hogar ({{ $corteActivo->numero ?? 1 }}° Corte).</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] min-h-screen" x-data="{ 
            evaluados: {{ $totalEvaluados ?? 0 }}, 
            total: {{ $matriculas->count() }} 
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Termómetro de Aula -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between border-l-4 border-l-[#e6ac27]">
                    <div>
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Promedio del Aula</span>
                        <h4 class="text-3xl font-black text-[#3d2c1d] mt-1">{{ $promedioAula ?? 0 }}<span class="text-lg text-slate-300">%</span></h4>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Progreso de Evaluación</span>
                        <h4 class="text-3xl font-black text-[#3d2c1d] mt-1"><span x-text="evaluados"></span><span class="text-lg text-slate-300">/{{ $matriculas->count() }}</span></h4>
                    </div>
                    <div class="w-full max-w-[120px] bg-slate-100 rounded-full h-2.5 ml-4">
                        <div class="bg-[#e6ac27] h-full rounded-full transition-all" :style="`width: ${(evaluados/total)*100}%`"></div>
                    </div>
                </div>
            </div>

            <!-- Formulario Masivo de Evaluación -->
            <form action="{{ route('academico.apoyo_familiar.store') }}" method="POST">
                @csrf
                <input type="hidden" name="corte_evaluativo_id" value="{{ $corteActivo->id ?? '' }}">

                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-widest font-black text-slate-400">
                                <tr>
                                    <th class="p-5 w-16 text-center">#</th>
                                    <th class="p-5">Estudiante</th>
                                    <th class="p-5 text-center">Nivel de Apoyo (B - MB - EX)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($matriculas as $index => $matricula)
                                    @php
                                        $evaluacionActual = $matricula->evaluacionesFamiliares->first()->evaluacion ?? '';
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors" x-data="{ seleccion: '{{ $evaluacionActual }}' }" :class="seleccion === 'B' ? 'bg-rose-50/50' : ''">
                                        <td class="p-5 text-center font-black text-slate-300">{{ $index + 1 }}</td>
                                        <td class="p-5 font-black text-[#3d2c1d]">{{ $matricula->alumno->nombre_completo }}</td>
                                        <td class="p-5">
                                            <!-- Tip UX: Opciones presentadas juntas para facilitar selección rápida -->
                                            <div class="flex justify-center gap-2">
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="evaluaciones[{{ $matricula->id }}]" value="B" class="peer sr-only" x-model="seleccion" @change="evaluados++">
                                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 font-black text-xs transition-all"
                                                         :class="seleccion === 'B' ? 'bg-rose-500 border-rose-500 text-white shadow-md' : 'bg-white border-slate-200 text-slate-400 hover:border-rose-300'">B</div>
                                                </label>
                                                
                                                <label class="cursor-pointer">
                                                    <input type="radio" name="evaluaciones[{{ $matricula->id }}]" value="MB" class="peer sr-only" x-model="seleccion" @change="evaluados++">
                                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 font-black text-xs transition-all"
                                                         :class="seleccion === 'MB' ? 'bg-[#e6ac27] border-[#e6ac27] text-white shadow-md' : 'bg-white border-slate-200 text-slate-400 hover:border-[#e6ac27]/50'">MB</div>
                                                </label>

                                                <label class="cursor-pointer">
                                                    <input type="radio" name="evaluaciones[{{ $matricula->id }}]" value="EX" class="peer sr-only" x-model="seleccion" @change="evaluados++">
                                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl border-2 font-black text-xs transition-all"
                                                         :class="seleccion === 'EX' ? 'bg-emerald-500 border-emerald-500 text-white shadow-md' : 'bg-white border-slate-200 text-slate-400 hover:border-emerald-300'">EX</div>
                                                </label>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="p-12 text-center text-slate-400 font-bold">No hay estudiantes activos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tip UX: Jerarquía visual en botones y lenguaje claro -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('dashboard') }}" class="text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">Cancelar</a>
                    <button type="submit" class="px-8 py-3.5 bg-[#3d2c1d] hover:bg-slate-800 text-white rounded-xl text-sm font-black shadow-md transition-transform transform hover:-translate-y-0.5">
                        Guardar Evaluaciones
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>