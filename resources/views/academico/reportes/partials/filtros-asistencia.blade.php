@php $ruta = $ruta ?? request()->route()->getName(); @endphp
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
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Grado</label>
        <select name="grado_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
            <option value="">Todos</option>
            @foreach($grados as $grado)
                <option value="{{ $grado->id }}" @selected($grado->id == request('grado_id'))>{{ $grado->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Modalidad</label>
        <select name="modalidad_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
            <option value="">Todas</option>
            @foreach($modalidades as $mod)
                <option value="{{ $mod->id }}" @selected($mod->id == request('modalidad_id'))>{{ $mod->nombre }}</option>
            @endforeach
        </select>
    </div>
    @if(!isset($soloFecha))
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Desde</label>
        <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm">
    </div>
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Hasta</label>
        <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm">
    </div>
    @else
    <div>
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Fecha</label>
        <input type="date" name="fecha" value="{{ request('fecha', now()->toDateString()) }}" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm">
    </div>
    @endif
    <button type="submit" class="self-end bg-[#3d2c1d] text-white font-black px-5 py-2 rounded-xl shadow-sm text-sm">Filtrar</button>
    <a href="{{ route($ruta) }}" class="self-end text-center text-sm font-bold text-slate-400 hover:text-rose-600 py-2">Limpiar</a>
</form>