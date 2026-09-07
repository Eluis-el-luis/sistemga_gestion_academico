<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Exámenes de Reparación</h2>
                <p class="text-sm font-medium text-slate-500 mt-1">Alumnos remitidos a nivel institucional (Fin de año).</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4" x-data="{ alumnoAbierto: null }">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm font-medium flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @forelse($matriculas as $matricula)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden transition-all hover:border-[#e6ac27]/50">
                    
                    <!-- Encabezado del Acordeón (Clickeable) -->
                    <div @click="alumnoAbierto = alumnoAbierto === {{ $matricula->id }} ? null : {{ $matricula->id }}" 
                         class="px-6 py-5 cursor-pointer flex justify-between items-center bg-[#FFFDF5] hover:bg-amber-50/30 transition-colors">
                        <div>
                            <h3 class="font-black text-[#3d2c1d] text-lg">{{ $matricula->alumno->nombre_completo }}</h3>
                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">
                                {{ $matricula->aula->grado->nombre ?? '' }} - "{{ $matricula->aula->nombre ?? '' }}" 
                                <span class="text-rose-500 ml-2">• {{ optional($matricula->clases_reprobadas)->count() ?? 0 }} materia(s) reprobada(s)</span>
                            </span>
                        </div>
                        <div class="text-slate-400 transform transition-transform" :class="alumnoAbierto === {{ $matricula->id }} ? 'rotate-180' : ''">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>

                    <!-- Cuerpo del Acordeón (Formulario Desplegable) -->
                    <div x-show="alumnoAbierto === {{ $matricula->id }}" x-collapse x-cloak>
                        <div class="p-6 border-t border-slate-100 bg-white">
                            
                            <!-- Cambia la línea actual por esta: -->
                            @foreach($matricula->clases_reprobadas ?? [] as $clase)
                                <form action="{{ route('academico.reparacion.store') }}" method="POST" class="flex flex-wrap items-end gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100 mb-3">
                                    @csrf
                                    <input type="hidden" name="matricula_id" value="{{ $matricula->id }}">
                                    <input type="hidden" name="asignatura_id" value="{{ $clase->aulaAsignaturaDocente->asignatura_id }}">
                                    
                                    <div class="flex-1 min-w-[200px]">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Materia Reprobada</label>
                                        <div class="font-black text-[#3d2c1d] px-3 py-2 bg-white rounded-lg border border-slate-200">
                                            {{ $clase->aulaAsignaturaDocente->asignatura->nombre }}
                                            <span class="text-rose-500 text-xs ml-2">(Nota Anual: {{ $clase->nota_cuantitativa }})</span>
                                        </div>
                                    </div>

                                    <div class="w-32">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Nota de Rescate</label>
                                        <input type="number" step="0.01" name="nota_obtenida" min="0" max="100" placeholder="Ej: 65" class="w-full border-slate-200 bg-white rounded-lg shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold" required>
                                    </div>

                                    <div class="w-40">
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Fecha Examen</label>
                                        <input type="date" name="fecha" value="{{ now()->toDateString() }}" class="w-full border-slate-200 bg-white rounded-lg shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold" required>
                                    </div>

                                    <button type="submit" class="px-5 py-2.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-lg text-xs font-black shadow-sm transition-all transform hover:-translate-y-0.5">
                                        Guardar
                                    </button>
                                </form>
                            @endforeach

                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-12 rounded-3xl border border-slate-200 text-center shadow-sm">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-xl font-black text-[#3d2c1d]">Sistema Limpio</h3>
                    <p class="text-slate-500 font-medium mt-2">No hay estudiantes que requieran examen de reparación en este momento.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>