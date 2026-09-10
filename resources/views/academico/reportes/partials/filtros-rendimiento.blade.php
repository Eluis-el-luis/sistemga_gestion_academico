<form method="GET" action="{{ route('academico.reportes.rendimiento-corte') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Modalidad</label>
        <select name="modalidad_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium">
            <option value="">Todas</option>
            @foreach($modalidades as $mod)
                <option value="{{ $mod->id }}" @selected($mod->id == request('modalidad_id'))>{{ $mod->nombre }}</option>
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
    <div class="md:col-span-3 flex items-center gap-4">
        <button type="submit" class="bg-[#3d2c1d] text-white font-black px-5 py-2 rounded-xl shadow-sm text-sm">Filtrar</button>
        <a href="{{ route('academico.reportes.rendimiento-corte') }}" class="text-sm font-bold text-slate-400 hover:text-rose-600">Limpiar</a>
    </div>
</form>