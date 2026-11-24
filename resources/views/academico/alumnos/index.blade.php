<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-4 py-3 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al Panel Principal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                    Directorio de Estudiantes
                </h2>
            </div>

            @can('create', \App\Models\Alumno::class)
                <a href="{{ route('academico.alumnos.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-sm font-semibold transition-colors cursor-pointer">
                    + Nuevo Alumno
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 p-6 sm:p-8 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800">
                <form method="GET" action="{{ route('academico.alumnos.index') }}" class="space-y-5">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por Nombre Completo o Código Único (CUP)..." class="block w-full pl-11 pr-4 py-2.5 bg-stone-50 dark:bg-neutral-800 border border-stone-300 dark:border-neutral-700 rounded-xl text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm transition-colors">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="filtro_modalidad" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Modalidad</label>
                            <select name="modalidad_id" id="filtro_modalidad" class="w-full border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 rounded-xl focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm font-medium text-neutral-800 dark:text-neutral-100 cursor-pointer">
                                <option value="">Todas</option>
                                @foreach ($modalidades ?? [] as $mod)
                                    <option value="{{ $mod->id }}" {{ request('modalidad_id') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filtro_grado" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Grado</label>
                            <select name="grado_id" id="filtro_grado" class="w-full border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 rounded-xl focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm font-medium text-neutral-800 dark:text-neutral-100 cursor-pointer">
                                <option value="">Todos</option>
                                @foreach ($grados ?? [] as $grado)
                                    <option value="{{ $grado->id }}" data-modalidad="{{ $grado->modalidad_id }}" {{ request('grado_id') == $grado->id ? 'selected' : '' }}>{{ $grado->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filtro_aula" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Aula / Sección</label>
                            <select name="aula_id" id="filtro_aula" class="w-full border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 rounded-xl focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm font-medium text-neutral-800 dark:text-neutral-100 cursor-pointer">
                                <option value="">Todas</option>
                                @foreach ($aulas ?? [] as $aula)
                                    <option value="{{ $aula->id }}" {{ request('aula_id') == $aula->id ? 'selected' : '' }}>{{ $aula->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="filtro_estado" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Estado Académico</label>
                            @php $estadoSel = request('estado', !request()->hasAny(['buscar', 'modalidad_id', 'grado_id', 'aula_id', 'estado']) ? 'activo' : ''); @endphp
                            <select name="estado" id="filtro_estado" class="w-full border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 rounded-xl focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm font-medium text-neutral-800 dark:text-neutral-100 cursor-pointer">
                                <option value="">Cualquier estado</option>
                                <option value="activo" {{ $estadoSel === 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="retirado" {{ $estadoSel === 'retirado' ? 'selected' : '' }}>Retirado</option>
                                <option value="repitente" {{ $estadoSel === 'repitente' ? 'selected' : '' }}>Repitente</option>
                                <option value="promovido" {{ $estadoSel === 'promovido' ? 'selected' : '' }}>Promovido</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end items-center pt-4 gap-4 border-t border-stone-100 dark:border-neutral-800">
                        @if (request()->hasAny(['buscar', 'modalidad_id', 'grado_id', 'aula_id', 'estado']))
                            <a href="{{ route('academico.alumnos.index') }}" class="text-sm font-medium text-neutral-500 hover:text-rose-600 transition-colors cursor-pointer">
                                Limpiar filtros
                            </a>
                        @endif
                        <button type="submit" class="px-6 py-2.5 bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 rounded-xl font-semibold text-sm transition-colors w-full sm:w-auto cursor-pointer">
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 dark:divide-neutral-800 text-left border-collapse">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold tracking-wider border-b border-stone-200 dark:border-neutral-800">
                            <tr>
                                <th class="px-6 py-4">Código (CUP)</th>
                                <th class="px-6 py-4">Nombre Completo</th>
                                <th class="px-6 py-4">Grado y Sección</th>
                                <th class="px-6 py-4 text-center">Sexo / Edad</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-700 dark:text-neutral-200 text-sm">
                            @forelse ($alumnos as $alumno)
                                @php $matriculaActiva = $alumno->matriculas->first(); @endphp
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 border border-stone-200 dark:border-neutral-700">
                                            {{ $alumno->codigo_unico_persona }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-neutral-900 dark:text-white">
                                        {{ $alumno->nombre_completo }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($matriculaActiva && $matriculaActiva->aula && $matriculaActiva->aula->grado)
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $matriculaActiva->aula->grado->nombre }}</span>
                                                <span class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Sección: {{ $matriculaActiva->aula->nombre }}</span>
                                            </div>
                                        @else
                                            <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-900">
                                                Sin Matrícula
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $alumno->sexo === 'M' ? 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300' : 'bg-pink-50 dark:bg-pink-950/50 text-pink-700 dark:text-pink-300' }}">
                                                {{ $alumno->sexo }}
                                            </span>
                                            <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ $alumno->fecha_nacimiento ? \Carbon\Carbon::parse($alumno->fecha_nacimiento)->age . ' años' : '-' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('academico.alumnos.show', $alumno) }}" class="inline-flex p-2 bg-stone-50 dark:bg-neutral-800 text-sky-600 dark:text-sky-400 rounded-xl hover:bg-stone-100 dark:hover:bg-neutral-700 border border-stone-200 dark:border-neutral-700 transition-colors cursor-pointer" title="Ver Expediente">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>

                                        @can('update', $alumno)
                                            <a href="{{ route('academico.alumnos.edit', $alumno) }}" class="inline-flex p-2 bg-stone-50 dark:bg-neutral-800 text-amber-600 dark:text-amber-400 rounded-xl hover:bg-stone-100 dark:hover:bg-neutral-700 border border-stone-200 dark:border-neutral-700 transition-colors cursor-pointer" title="Editar Información">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                        @endcan

                                        @can('delete', $alumno)
                                            <form action="{{ route('academico.alumnos.destroy', $alumno) }}" method="POST" class="inline-block form-eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex p-2 bg-stone-50 dark:bg-neutral-800 text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-stone-200 dark:border-neutral-700 transition-colors cursor-pointer" title="Eliminar Registro">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-neutral-500 dark:text-neutral-400">
                                        <p class="text-base font-semibold text-neutral-700 dark:text-neutral-200">No se encontraron estudiantes</p>
                                        <p class="text-sm mt-1">Ajusta los filtros de búsqueda.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="p-5 border-t border-stone-100 dark:border-neutral-800 bg-white dark:bg-neutral-900 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div class="text-xs text-neutral-600 dark:text-neutral-300 font-semibold bg-stone-100 dark:bg-neutral-800 px-3.5 py-2 rounded-xl">
                            Total: {{ $alumnos->total() }} registros
                        </div>
                        <div>
                            {{ $alumnos->appends(request()->query())->links() ?? '' }}
                        </div>
                    </div>
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

                if (gradoSelect.value !== '' && !valido) {
                    gradoSelect.value = '';
                }
            }

            if (modSelect && gradoSelect) {
                modSelect.addEventListener('change', actualizarGrados);
                actualizarGrados();
            }
        });
    </script>
</x-app-layout>