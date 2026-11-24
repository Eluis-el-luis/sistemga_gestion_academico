{{-- resources/views/academico/matriculas/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('academico.matriculas.index') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Proceso de Matrícula</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @php $anioActivo = $anios->first() ?? null; @endphp
            <div class="bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 py-4 px-6 rounded-2xl shadow-xs flex items-center justify-between">
                <div>
                    <span class="block text-[11px] font-semibold text-neutral-400 uppercase tracking-wider">Periodo Lectivo Activo</span>
                    <h3 class="font-bold text-neutral-900 dark:text-white text-base">Año Lectivo {{ $anioActivo?->nombre ?? 'N/A' }}</h3>
                </div>
                <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-semibold">Abierto</span>
            </div>

            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 p-6 sm:p-8">
                <form action="{{ route('academico.matriculas.store') }}" method="POST" class="space-y-5 text-sm">
                    @csrf
                    <input type="hidden" name="anio_escolar_id" value="{{ $anioActivo?->id ?? '' }}">

                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Estudiante <span class="text-rose-500">*</span></label>
                        <select name="alumno_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                            <option value="">Seleccione estudiante sin matrícula en este periodo...</option>
                            @foreach ($alumnos ?? [] as $alumno)
                                <option value="{{ $alumno->id }}" {{ (old('alumno_id') == $alumno->id || ($alumnoSeleccionado ?? null) == $alumno->id) ? 'selected' : '' }}>
                                    {{ $alumno->codigo_unico_persona }} - {{ $alumno->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Aula Destino <span class="text-rose-500">*</span></label>
                        <select name="aula_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                            <option value="">Seleccione aula y sección...</option>
                            @foreach ($aulas ?? [] as $aula)
                                @php
                                    $disponibles = max(0, (int) $aula->cupo - (int) ($aula->matriculas_count ?? 0));
                                    $llena = $disponibles === 0;
                                @endphp
                                <option value="{{ $aula->id }}" {{ old('aula_id') == $aula->id ? 'selected' : '' }} {{ $llena ? 'disabled' : '' }}>
                                    {{ $aula->grado?->nombre ?? 'Sin Grado' }} "{{ $aula->nombre }}" ({{ $llena ? 'Llena' : 'Disponibles: ' . $disponibles . '/' . $aula->cupo }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Estado Inicial <span class="text-rose-500">*</span></label>
                            <select name="estado" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                <option value="activo" {{ old('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="repitente" {{ old('estado') == 'repitente' ? 'selected' : '' }}>Repitente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Fecha de Registro <span class="text-rose-500">*</span></label>
                            <input type="date" name="fecha_matricula" value="{{ old('fecha_matricula', date('Y-m-d')) }}" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-semibold" required>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-5 border-t border-stone-100 dark:border-neutral-800">
                        <a href="{{ route('academico.matriculas.index') }}" class="text-xs font-semibold text-neutral-500 uppercase cursor-pointer">Cancelar</a>
                        <button type="submit" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-6 py-2.5 rounded-xl text-xs uppercase cursor-pointer">Confirmar Matrícula</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>