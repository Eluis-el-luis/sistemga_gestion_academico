{{-- resources/views/academico/reportes/partials/filtros-notas.blade.php --}}
@php
    $ruta =$ruta ?? request()->route()->getName();
    $mostrarEstado =$mostrarEstado ?? false;
    $hayFiltrosActivos = request()->hasAny([
        'tipo', 'anio_escolar_id', 'modalidad_id', 'grado_id',
        'aula_id', 'asignatura_id', 'docente_id', 'corte_evaluativo_id',
    ]);
@endphp

<form method="GET" action="{{ route($ruta) }}" class="space-y-4 text-sm">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @if ($mostrarEstado)
            <div>
                <label for="filtro_tipo" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Estado de Carga</label>
                <select id="filtro_tipo" name="tipo" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                    <option value="todos" @selected(request('tipo', 'todos') === 'todos' || request('tipo') === '')>Todos los Registros</option>
                    <option value="pendientes" @selected(request('tipo') === 'pendientes')>Con Notas Pendientes</option>
                    <option value="ingresadas" @selected(request('tipo') === 'ingresadas')>Con Notas Registradas</option>
                </select>
            </div>
        @endif

        <div>
            <label for="filtro_anio" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Año Escolar</label>
            <select id="filtro_anio" name="anio_escolar_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                <option value="">Ciclo Activo</option>
                @foreach ($anios ?? [] as $an)
                    <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{$an->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filtro_modalidad" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Modalidad</label>
            <select id="filtro_modalidad" name="modalidad_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                <option value="">Todas las Modalidades</option>
                @foreach ($modalidades ?? [] as $mod)
                    <option value="{{ $mod->id }}" @selected($mod->id == request('modalidad_id'))>{{$mod->nombre }}</option>
                @endforeach
            </select>
        </div>

        @if (!isset($ocultarGrado))
            <div>
                <label for="filtro_grado" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Grado Académico</label>
                <select id="filtro_grado" name="grado_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                    <option value="">Todos los Grados</option>
                    @foreach ($grados ?? [] as $grado)
                        <option value="{{ $grado->id }}" @selected($grado->id == request('grado_id'))>{{$grado->nombre }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label for="filtro_aula" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Aula / Sección</label>
            <select id="filtro_aula" name="aula_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                <option value="">Todas las Secciones</option>
                @foreach ($aulas ?? [] as $aulaOp)
                    <option value="{{ $aulaOp->id }}" @selected($aulaOp->id == request('aula_id'))>{{ $aulaOp->grado?->nombre ?? '' }} - Sec. "{{ $aulaOp->nombre }}"</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filtro_asignatura" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Asignatura</label>
            <select id="filtro_asignatura" name="asignatura_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                <option value="">Todas las Asignaturas</option>
                @foreach ($asignaturas ?? [] as $asig)
                    <option value="{{ $asig->id }}" @selected($asig->id == request('asignatura_id'))>{{$asig->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filtro_docente" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Docente Responsable</label>
            <select id="filtro_docente" name="docente_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                <option value="">Todos los Docentes</option>
                @foreach ($docentes ?? [] as $docente)
                    <option value="{{ $docente->id }}" @selected($docente->id == request('docente_id'))>{{$docente->usuario?->nombre_completo ?? 'Sin nombre' }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filtro_corte" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Periodo Evaluativo</label>
            <select id="filtro_corte" name="corte_evaluativo_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
                <option value="">Todos los Cortes</option>
                @foreach ($cortes ?? [] as $corte)
                    <option value="{{ $corte->id }}" @selected($corte->id == request('corte_evaluativo_id'))>
                        {{ $corte->descripcion ?? $corte->nombre ?? ($corte->numero . '° Corte') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="pt-4 border-t border-stone-100 dark:border-neutral-800 flex flex-col sm:flex-row justify-end items-center gap-3">
        @if ($hayFiltrosActivos)
            <a href="{{ route($ruta) }}" class="px-4 py-2 rounded-xl border border-stone-200 dark:border-neutral-700 text-xs font-semibold text-neutral-600 dark:text-neutral-300 hover:text-rose-600 cursor-pointer">
                Limpiar Filtros
            </a>
        @endif
        <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 font-bold px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">
            Aplicar Filtros
        </button>
    </div>
</form>