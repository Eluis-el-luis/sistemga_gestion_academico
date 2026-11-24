@php $ruta =$ruta ?? request()->route()->getName(); @endphp
<form method="GET" action="{{ route($ruta) }}" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 text-sm">
    <div>
        <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Año Escolar</label>
        <select name="anio_escolar_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
            <option value="">Todos</option>
            @foreach ($anios ?? [] as $an)
                <option value="{{ $an->id }}" @selected($an->id == request('anio_escolar_id'))>{{$an->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Grado</label>
        <select name="grado_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
            <option value="">Todos</option>
            @foreach ($grados ?? [] as $grado)
                <option value="{{ $grado->id }}" @selected($grado->id == request('grado_id'))>{{$grado->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Modalidad</label>
        <select name="modalidad_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium cursor-pointer">
            <option value="">Todas</option>
            @foreach ($modalidades ?? [] as $mod)
                <option value="{{ $mod->id }}" @selected($mod->id == request('modalidad_id'))>{{$mod->nombre }}</option>
            @endforeach
        </select>
    </div>
    @if (!isset($soloFecha))
        <div>
            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Desde</label>
            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Hasta</label>
            <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm">
        </div>
    @else
        <div>
            <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Fecha</label>
            <input type="date" name="fecha" value="{{ request('fecha', now()->toDateString()) }}" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm">
        </div>
    @endif
    <div class="flex items-center gap-3 md:col-span-2 xl:col-span-2">
        <button type="submit" class="bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 font-bold px-5 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">Filtrar</button>
        <a href="{{ route($ruta) }}" class="text-xs font-semibold text-neutral-500 hover:text-rose-600 py-2 cursor-pointer">Limpiar</a>
    </div>
</form>