{{-- resources/views/academico/aulas/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('academico.aulas.index') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer" title="Volver atrás">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Aperturar Nueva Aula</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 border-b border-stone-200/80 dark:border-neutral-800 px-6 sm:px-8 py-4">
                    <h3 class="text-sm font-bold text-neutral-900 dark:text-white uppercase tracking-wider">Datos del Grupo a Crear</h3>
                </div>

                <div class="p-6 sm:p-8">
                    <form action="{{ route('academico.aulas.store') }}" method="POST" class="space-y-6 text-sm">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Año Escolar <span class="text-rose-500">*</span></label>
                                <select name="anio_escolar_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required autofocus>
                                    <option value="">Seleccione...</option>
                                    @foreach ($anios as $anio)
                                        <option value="{{ $anio->id }}" {{ old('anio_escolar_id') == $anio->id ? 'selected' : '' }}>{{ $anio->nombre }} {{ $anio->es_activo ? '(Vigente)' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Cupo Máximo <span class="text-rose-500">*</span></label>
                                <input type="number" name="cupo" value="{{ old('cupo', 50) }}" min="1" max="100" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-semibold" required>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Modalidad <span class="text-rose-500">*</span></label>
                                <select name="modalidad_id" id="filtro_modalidad" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                    <option value="">Seleccione...</option>
                                    @foreach ($modalidades as $mod)
                                        <option value="{{ $mod->id }}" {{ old('modalidad_id') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Grado <span class="text-rose-500">*</span></label>
                                <select name="grado_id" id="filtro_grado" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                    <option value="">Seleccione primero la modalidad...</option>
                                    @foreach ($grados as $grado)
                                        <option value="{{ $grado->id }}" data-modalidad="{{ $grado->modalidad_id }}" {{ old('grado_id') == $grado->id ? 'selected' : '' }}>{{ $grado->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Sección / Nombre <span class="text-rose-500">*</span></label>
                                <input type="text" name="nombre" value="{{ old('nombre', 'A') }}" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-bold uppercase" required>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Turno <span class="text-rose-500">*</span></label>
                                <select name="turno" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                    <option value="Matutino" {{ old('turno', 'Matutino') == 'Matutino' ? 'selected' : '' }}>Matutino</option>
                                    <option value="Vespertino" {{ old('turno') == 'Vespertino' ? 'selected' : '' }}>Vespertino</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Docente Guía Titular <span class="text-rose-500">*</span></label>
                                <select name="docente_guia_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                    <option value="">Seleccione profesor titular disponible...</option>
                                    @foreach ($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('docente_guia_id') == $docente->id ? 'selected' : '' }}>{{ $docente->codigo_unico_persona }} - {{ $docente->usuario?->nombre_completo ?? 'Sin nombre' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-stone-100 dark:border-neutral-800">
                            <a href="{{ route('academico.aulas.index') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white uppercase tracking-wider cursor-pointer">Cancelar</a>
                            <button type="submit" class="inline-flex items-center justify-center bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">
                                Guardar y Asignar Materias
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modSelect = document.getElementById('filtro_modalidad');
            const gradoSelect = document.getElementById('filtro_grado');
            function actualizarGrados() {
                if (!modSelect || !gradoSelect) return;
                const modId = modSelect.value;
                let valido = false;
                Array.from(gradoSelect.options).forEach((op) => {
                    if (op.value === '') return;
                    const match = modId === '' || op.getAttribute('data-modalidad') === modId;
                    op.hidden = !match;
                    if (match && op.selected) valido = true;
                });
                if (gradoSelect.value !== '' && !valido) gradoSelect.value = '';
            }
            if (modSelect && gradoSelect) {
                modSelect.addEventListener('change', actualizarGrados);
                actualizarGrados();
            }
        });
    </script>
</x-app-layout>