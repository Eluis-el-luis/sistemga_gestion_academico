{{-- resources/views/academico/malla/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Plantilla Oficial: Malla Curricular</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{ modalidadFiltro: localStorage.getItem('mallaTab') || 'Todos', modalClonar: false }">
        <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 px-6 sm:px-8 py-4 border-b border-stone-200/80 dark:border-neutral-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Agregar Asignatura a un Grado</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Las materias agregadas aquí se clonan al aperturar nuevas aulas.</p>
                    </div>
                    <button type="button" @click="modalClonar = true" class="px-4 py-2 bg-neutral-900 dark:bg-neutral-800 hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-colors cursor-pointer">
                        Clonar Plantilla
                    </button>
                </div>

                <div class="p-6 sm:p-8">
                    <form action="{{ route('academico.malla.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end text-sm" x-data="filtroMalla()">
                        @csrf
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Grado <span class="text-rose-500">*</span></label>
                            <select name="grado_id" x-model="gradoSeleccionado" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                <option value="">Seleccione grado...</option>
                                @foreach ($grados as $grado)
                                    <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-5">
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Asignatura Oficial <span class="text-rose-500">*</span></label>
                            <select name="asignatura_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                <option value="">Seleccione la Materia...</option>
                                <template x-for="asignatura in asignaturasFiltradas()" :key="asignatura.id">
                                    <option :value="asignatura.id" x-text="asignatura.nombre"></option>
                                </template>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Horas Sem. <span class="text-rose-500">*</span></label>
                            <input type="number" name="horas_semanales_sugeridas" min="1" max="40" value="4" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-bold text-center" required>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 px-4 rounded-xl text-xs uppercase transition-colors h-[42px] cursor-pointer">
                                + Añadir
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @php $modalidadesMalla = $grados->pluck('modalidad.nombre')->filter()->unique(); @endphp
            <div class="flex flex-wrap gap-2 bg-white dark:bg-neutral-900 p-2 rounded-2xl border border-stone-200 dark:border-neutral-800 w-fit">
                <button type="button" @click="modalidadFiltro = 'Todos'; localStorage.setItem('mallaTab', 'Todos')" :class="modalidadFiltro === 'Todos' ? 'bg-[#e6ac27] text-neutral-950 font-bold' : 'text-neutral-600 dark:text-neutral-300 font-medium'" class="px-4 py-2 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">Todos</button>
                @foreach ($modalidadesMalla as $mod)
                    <button type="button" @click="modalidadFiltro = @js($mod); localStorage.setItem('mallaTab', @js($mod))" :class="modalidadFiltro === @js($mod) ? 'bg-[#e6ac27] text-neutral-950 font-bold' : 'text-neutral-600 dark:text-neutral-300 font-medium'" class="px-4 py-2 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">{{ $mod }}</button>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($grados as $grado)
                    @php
                        $limiteHoras = $grado->horas_maximas_semanales ?? 35;
                        $horasUsadas = $grado->mallaCurricular->sum('horas_semanales_sugeridas');
                        $porcentaje = $limiteHoras > 0 ? ($horasUsadas / $limiteHoras) * 100 : 0;
                    @endphp
                    <div x-data="{ abierto: false }" x-show="modalidadFiltro === 'Todos' || modalidadFiltro === @js($grado->modalidad->nombre ?? '')" class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden h-fit">
                        <div class="px-6 py-4 cursor-pointer flex justify-between items-center" @click="abierto = !abierto">
                            <div>
                                <h4 class="font-bold text-neutral-900 dark:text-white text-base">{{ $grado->nombre }}</h4>
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $grado->modalidad?->nombre ?? 'N/A' }} · {{ $grado->mallaCurricular->count() }} materias</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold text-[#c99116] dark:text-[#e6ac27]">{{ $horasUsadas }} / {{ $limiteHoras }}h</span>
                            </div>
                        </div>
                        <div x-show="abierto" x-cloak class="px-6 pb-5 pt-2 border-t border-stone-100 dark:border-neutral-800 space-y-2.5 text-xs">
                            @forelse ($grado->mallaCurricular as $item)
                                <div class="flex justify-between items-center py-1.5 border-b border-stone-100 dark:border-neutral-800 last:border-0">
                                    <div>
                                        <span class="font-semibold text-neutral-800 dark:text-neutral-200 block">{{ $item->asignatura?->nombre }}</span>
                                        <span class="text-neutral-400">{{ number_format((float) ($item->horas_semanales_sugeridas ?? 0), 0) }} h/sem</span>
                                    </div>
                                    <form action="{{ route('academico.malla.destroy', $item->id) }}" method="POST" class="form-eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline font-semibold cursor-pointer">Quitar</button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-neutral-400 py-2">Sin materias asignadas.</p>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

            <div x-show="modalClonar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
                <div class="bg-white dark:bg-neutral-900 rounded-3xl w-full max-w-md overflow-hidden border border-stone-200 dark:border-neutral-800">
                    <div class="px-6 py-4 border-b border-stone-100 dark:border-neutral-800 flex justify-between items-center">
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Clonar Plantilla</h3>
                        <button type="button" @click="modalClonar = false" class="text-neutral-400 hover:text-rose-500 cursor-pointer">x</button>
                    </div>
                    <form action="{{ route('academico.malla.clonar') }}" method="POST" class="p-6 space-y-4 text-sm">
                        @csrf
                        <select name="origen_grado_id" required class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl cursor-pointer">
                            <option value="">Grado origen...</option>
                            @foreach ($grados as $grado)
                                <option value="{{ $grado->id }}">{{ $grado->nombre }} ({{ $grado->mallaCurricular->count() }})</option>
                            @endforeach
                        </select>
                        <select name="destino_grado_id" required class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl cursor-pointer">
                            <option value="">Grado destino...</option>
                            @foreach ($grados as $grado)
                                <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 rounded-xl text-xs uppercase cursor-pointer">Clonar Ahora</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('filtroMalla', () => ({
                gradoSeleccionado: '',
                todasAsignaturas: @json($asignaturas),
                materiasPorGrado: {
                    @foreach ($grados as $grado)
                        "{{ $grado->id }}": @json($grado->mallaCurricular->pluck('asignatura_id')),
                    @endforeach
                },
                asignaturasFiltradas() {
                    if (!this.gradoSeleccionado) return this.todasAsignaturas;
                    const asignadas = this.materiasPorGrado[this.gradoSeleccionado] || [];
                    return this.todasAsignaturas.filter(a => !asignadas.includes(a.id));
                }
            }));
        });
    </script>
</x-app-layout>