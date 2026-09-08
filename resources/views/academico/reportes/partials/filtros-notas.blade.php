@php
    $ruta = $ruta ?? request()->route()->getName();
@endphp
<form method="GET" action="{{ route($ruta) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año Escolar</label>
        <select name="anio_escolar_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
            <option value="">Todos</option>
            @foreach($anios as $an)
                <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{ $an->nombre }}</option>
            @endforeach
        </select>
    </div>
    @if(!isset($ocultarGrado))
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Grado</label>
        <select name="grado_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
            <option value="">Todos</option>
            @foreach($grados as $grado)
                <option value="{{ $grado->id }}" @selected($grado->id == request('grado_id'))>{{ $grado->nombre }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Asignatura</label>
        <select name="asignatura_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
            <option value="">Todas</option>
            @foreach($asignaturas as $asig)
                <option value="{{ $asig->id }}" @selected($asig->id == request('asignatura_id'))>{{ $asig->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Docente</label>
        <select name="docente_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
            <option value="">Todos</option>
            @foreach($docentes as $docente)
                <option value="{{ $docente->id }}" @selected($docente->id == request('docente_id'))>{{ $docente->usuario->nombre_completo ?? 'Sin nombre' }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Periodo Evaluativo</label>
        <select name="corte_evaluativo_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
            <option value="">Todos</option>
            @foreach($cortes as $corte)
                <option value="{{ $corte->id }}" @selected($corte->id == request('corte_evaluativo_id'))>{{ $corte->numero }}° Corte (S{{ $corte->semestre }})</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="self-end bg-[#3d2c1d] text-white font-black px-5 py-2 rounded-xl shadow-sm text-sm">Filtrar</button>
    <a href="{{ route($ruta) }}" class="self-end text-center text-sm font-bold text-slate-400 hover:text-rose-600 py-2">Limpiar</a>
</form>