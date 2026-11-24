{{-- resources/views/academico/bloques/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Estructura de Horarios (Bloques)</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="gestorBloques()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 px-6 sm:px-8 py-4 border-b border-stone-200/80 dark:border-neutral-800 flex justify-between items-center gap-4">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Añadir Bloque de Tiempo Oficial</h3>
                    <button type="button" @click="modalClonar = true" class="px-4 py-2 bg-neutral-900 dark:bg-neutral-800 hover:bg-neutral-800 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-colors cursor-pointer">
                        Clonar Estructura
                    </button>
                </div>

                <div class="p-6 sm:p-8">
                    <form action="{{ route('academico.bloques.store') }}" method="POST" class="space-y-5 text-sm">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-4">
                                <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Modalidad <span class="text-rose-500">*</span></label>
                                <select name="modalidad_id" x-model="form.modalidad_id" @change="actualizarSugerencia" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                    <option value="">Seleccione...</option>
                                    @foreach ($modalidades as $mod)
                                        <option value="{{ $mod->id }}">{{ $mod->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Turno <span class="text-rose-500">*</span></label>
                                <select name="turno" x-model="form.turno" @change="actualizarSugerencia" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                    <option value="Matutino">Matutino</option>
                                    <option value="Vespertino">Vespertino</option>
                                </select>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Tipo de Jornada <span class="text-rose-500">*</span></label>
                                <select name="tipo_jornada" x-model="form.tipo_jornada" @change="actualizarSugerencia" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-medium cursor-pointer" required>
                                    <option value="Regular">Regular</option>
                                    <option value="Viernes">Viernes</option>
                                    <option value="Corto 40m">Corto 40m</option>
                                    <option value="Especial 30m">Especial 30m</option>
                                </select>
                            </div>

                            <div class="md:col-span-5">
                                <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Nombre del Bloque <span class="text-rose-500">*</span></label>
                                <input type="text" name="nombre" placeholder="Ej: 1ra Hora" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-semibold" required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Hora Inicio <span class="text-rose-500">*</span></label>
                                <input type="time" name="hora_inicio" x-model="form.hora_inicio" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-semibold" required>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Hora Fin <span class="text-rose-500">*</span></label>
                                <input type="time" name="hora_fin" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl font-semibold" required>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-stone-100 dark:border-neutral-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="hidden" name="es_recreo" value="0">
                                <input type="checkbox" name="es_recreo" value="1" x-model="form.es_recreo" class="rounded border-stone-300 text-[#e6ac27] focus:ring-[#e6ac27] h-4 w-4 cursor-pointer">
                                <span class="ml-2.5 text-xs font-semibold text-neutral-700 dark:text-neutral-200 uppercase">Tiempo Libre / Recreo</span>
                            </label>
                            <button type="submit" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 px-6 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">
                                Guardar Bloque Oficial
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach ($modalidades as $modalidad)
                    @foreach (['Matutino', 'Vespertino'] as $turno)
                        @foreach (['Regular', 'Viernes', 'Corto 40m', 'Especial 30m'] as $jornada)
                            @php $bloquesFiltrados = $bloques->where('modalidad_id', $modalidad->id)->where('turno', $turno)->where('tipo_jornada', $jornada); @endphp
                            @if ($bloquesFiltrados->isNotEmpty())
                                <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                                    <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 px-5 py-4 border-b border-stone-200/80 dark:border-neutral-800 flex justify-between items-start gap-2">
                                        <div>
                                            <h4 class="font-bold text-neutral-900 dark:text-white text-sm">{{ $modalidad->nombre }}</h4>
                                            <span class="text-[11px] font-medium text-neutral-500 uppercase">{{ $jornada }} · {{ $turno }}</span>
                                        </div>
                                        <form action="{{ route('academico.bloques.jornada.destroy') }}" method="POST" class="form-eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">
                                            <input type="hidden" name="turno" value="{{ $turno }}">
                                            <input type="hidden" name="tipo_jornada" value="{{ $jornada }}">
                                            <button type="submit" class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">Eliminar</button>
                                        </form>
                                    </div>
                                    <ul class="divide-y divide-stone-100 dark:divide-neutral-800 text-xs">
                                        @foreach ($bloquesFiltrados as $bloque)
                                            <li class="flex justify-between items-center px-5 py-3">
                                                <div>
                                                    <span class="font-semibold text-neutral-900 dark:text-neutral-100 block">{{ $bloque->numero_bloque ? $bloque->numero_bloque . '° - ' : '' }}{{ $bloque->nombre }}</span>
                                                    <span class="text-neutral-500 dark:text-neutral-400">{{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}</span>
                                                </div>
                                                <form action="{{ route('academico.bloques.destroy', $bloque->id) }}" method="POST" class="form-eliminar">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-neutral-400 hover:text-rose-600 font-semibold cursor-pointer">Quitar</button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            </div>

            <div x-show="modalClonar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
                <div class="bg-white dark:bg-neutral-900 rounded-3xl w-full max-w-md overflow-hidden border border-stone-200 dark:border-neutral-800">
                    <div class="px-6 py-4 border-b border-stone-100 dark:border-neutral-800 bg-stone-50 dark:bg-neutral-800/70 flex justify-between items-center">
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Clonar Estructura</h3>
                        <button type="button" @click="modalClonar = false" class="text-neutral-400 hover:text-rose-500 cursor-pointer">x</button>
                    </div>
                    <form action="{{ route('academico.bloques.clonar') }}" method="POST" class="p-6 space-y-4 text-sm">
                        @csrf
                        <select name="origen_modalidad_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl cursor-pointer" required>
                            <option value="">Modalidad Origen...</option>
                            @foreach ($modalidades as $mod) <option value="{{ $mod->id }}">{{ $mod->nombre }}</option> @endforeach
                        </select>
                        <div class="grid grid-cols-2 gap-3">
                            <select name="origen_turno" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl cursor-pointer" required><option value="Matutino">Matutino</option><option value="Vespertino">Vespertino</option></select>
                            <select name="origen_jornada" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl cursor-pointer" required><option value="Regular">Regular</option><option value="Viernes">Viernes</option><option value="Corto 40m">Corto 40m</option><option value="Especial 30m">Especial 30m</option></select>
                        </div>
                        <select name="destino_modalidad_id" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl cursor-pointer" required>
                            <option value="">Modalidad Destino...</option>
                            @foreach ($modalidades as $mod) <option value="{{ $mod->id }}">{{ $mod->nombre }}</option> @endforeach
                        </select>
                        <div class="grid grid-cols-2 gap-3">
                            <select name="destino_turno" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl cursor-pointer" required><option value="Matutino">Matutino</option><option value="Vespertino">Vespertino</option></select>
                            <select name="destino_jornada" class="border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 rounded-xl cursor-pointer" required><option value="Regular">Regular</option><option value="Viernes">Viernes</option><option value="Corto 40m">Corto 40m</option><option value="Especial 30m">Especial 30m</option></select>
                        </div>
                        <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 rounded-xl text-xs uppercase cursor-pointer">Clonar Bloques</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('gestorBloques', () => ({
                modalClonar: false,
                todosBloques: @json($bloques),
                form: { modalidad_id: '', turno: 'Matutino', tipo_jornada: 'Regular', hora_inicio: '', es_recreo: false },
                actualizarSugerencia() {
                    const coincidentes = this.todosBloques.filter(b => b.modalidad_id == this.form.modalidad_id && b.turno == this.form.turno && b.tipo_jornada == this.form.tipo_jornada);
                    if (coincidentes.length > 0) {
                        const ultimo = coincidentes.reduce((prev, cur) => (prev.hora_fin > cur.hora_fin ? prev : cur));
                        this.form.hora_inicio = String(ultimo.hora_fin).substring(0, 5);
                    } else {
                        this.form.hora_inicio = '';
                    }
                }
            }));
        });
    </script>
</x-app-layout>