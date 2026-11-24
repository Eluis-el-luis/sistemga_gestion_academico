{{-- resources/views/academico/reportes/partials/filtros-rendimiento.blade.php --}}
<form method="GET" action="{{ route('academico.reportes.rendimiento-corte') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
    <div>
        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Año Escolar</label>
        <select name="anio_escolar_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-medium cursor-pointer">
            <option value="">Todos</option>
            @foreach ($anios ?? [] as $an)
                <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{$an->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Modalidad</label>
        <select name="modalidad_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-medium cursor-pointer">
            <option value="">Todas</option>
            @foreach ($modalidades ?? [] as $mod)
                <option value="{{ $mod->id }}" @selected($mod->id == request('modalidad_id'))>{{$mod->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Periodo Evaluativo</label>
        <select name="corte_evaluativo_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl text-sm font-medium cursor-pointer">
            <option value="">Todos</option>
            @foreach ($cortes ?? [] as $corte)
                <option value="{{ $corte->id }}" @selected($corte->id == request('corte_evaluativo_id'))>{{$corte->numero }}° Corte</option>
            @endforeach
        </select>
    </div>
    <div class="md:col-span-3 flex items-center gap-4">
        <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 font-bold px-5 py-2 rounded-xl text-xs uppercase cursor-pointer">Filtrar</button>
        <a href="{{ route('academico.reportes.rendimiento-corte') }}" class="text-xs font-semibold text-neutral-500 hover:text-rose-600 cursor-pointer">Limpiar</a>
    </div>
</form>