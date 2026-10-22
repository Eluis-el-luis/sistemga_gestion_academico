@php
    $ruta = $ruta ?? request()->route()->getName();
    $mostrarEstado = $mostrarEstado ?? false;
    $hayFiltrosActivos = request()->hasAny([
        'tipo', 'anio_escolar_id', 'modalidad_id', 'grado_id', 
        'aula_id', 'asignatura_id', 'docente_id', 'corte_evaluativo_id'
    ]);
@endphp

<form method="GET" action="{{ route($ruta) }}" class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        @if($mostrarEstado)
        <div>
            <label for="filtro_tipo" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Estado de Carga</label>
            <select id="filtro_tipo" name="tipo" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-bold text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                <option value="todos" @selected(request('tipo', 'todos') === 'todos' || request('tipo') === '')>Todos los Registros</option>
                <option value="pendientes" @selected(request('tipo') === 'pendientes')>Con Notas Pendientes</option>
                <option value="ingresadas" @selected(request('tipo') === 'ingresadas')>Con Notas Registradas</option>
            </select>
        </div>
        @endif

        <div>
            <label for="filtro_anio" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
            <select id="filtro_anio" name="anio_escolar_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                <option value="">Ciclo Activo</option>
                @foreach($anios as $an)
                    <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{ $an->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filtro_modalidad" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Modalidad</label>
            <select id="filtro_modalidad" name="modalidad_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                <option value="">Todas las Modalidades</option>
                @foreach($modalidades as $mod)
                    <option value="{{ $mod->id }}" @selected($mod->id == request('modalidad_id'))>{{ $mod->nombre }}</option>
                @endforeach
            </select>
        </div>

        @if(!isset($ocultarGrado))
        <div>
            <label for="filtro_grado" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Grado Académico</label>
            <select id="filtro_grado" name="grado_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                <option value="">Todos los Grados</option>
                @foreach($grados as $grado)
                    <option value="{{ $grado->id }}" @selected($grado->id == request('grado_id'))>{{ $grado->nombre }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label for="filtro_aula" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Aula / Sección</label>
            <select id="filtro_aula" name="aula_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                <option value="">Todas las Secciones</option>
                @foreach($aulas as $aula)
                    <option value="{{ $aula->id }}" @selected($aula->id == request('aula_id'))>{{ $aula->grado->nombre ?? '' }} - Sec. "{{ $aula->nombre }}"</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filtro_asignatura" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Asignatura</label>
            <select id="filtro_asignatura" name="asignatura_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                <option value="">Todas las Asignaturas</option>
                @foreach($asignaturas as $asig)
                    <option value="{{ $asig->id }}" @selected($asig->id == request('asignatura_id'))>{{ $asig->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filtro_docente" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Docente Responsable</label>
            <select id="filtro_docente" name="docente_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                <option value="">Todos los Docentes</option>
                @foreach($docentes as $docente)
                    <option value="{{ $docente->id }}" @selected($docente->id == request('docente_id'))>{{ $docente->usuario->nombre_completo ?? 'Sin nombre' }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="filtro_corte" class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Periodo Evaluativo</label>
            <select id="filtro_corte" name="corte_evaluativo_id" class="w-full border-slate-200 bg-slate-50/70 hover:bg-slate-100/80 rounded-xl shadow-sm text-sm font-medium text-slate-700 focus:ring-[#e6ac27] focus:border-[#e6ac27] cursor-pointer transition-colors">
                <option value="">Todos los Cortes</option>
                @foreach($cortes as $corte)
                    <option value="{{ $corte->id }}" @selected($corte->id == request('corte_evaluativo_id'))>
                        {{ $corte->descripcion ?? $corte->nombre ?? ($corte->numero . '° Corte (S' . $corte->semestre . ')') }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Barra Inferior de Acciones -->
    <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <svg class="w-4 h-4 text-[#e6ac27] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            <span>Selecciona uno o varios criterios para segmentar la auditoría académica.</span>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
            @if($hayFiltrosActivos)
                <a href="{{ route($ruta) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50/50 text-xs font-black uppercase tracking-wider transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Limpiar Filtros
                </a>
            @endif
            <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#3d2c1d] hover:bg-[#2b1f14] text-white font-black px-6 py-2.5 rounded-xl shadow-sm hover:shadow-md text-xs uppercase tracking-widest transition-all cursor-pointer">
                <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Aplicar Filtros
            </button>
        </div>
    </div>
</form>