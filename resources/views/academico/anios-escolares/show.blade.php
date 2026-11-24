{{-- resources/views/academico/anios-escolares/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div>
                <h2 class="text-xl font-bold text-neutral-900 dark:text-white">{{ $anioEscolar->nombre }}</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $anioEscolar->fecha_inicio?->format('d/m/Y') }} - {{ $anioEscolar->fecha_fin?->format('d/m/Y') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $anioEscolar->color_estado }}">
                    {{ $anioEscolar->etiqueta_estado }}
                </span>
                @can('configuracion.gestionar')
                    @if (!$anioEscolar->es_cerrado)
                        <a href="{{ route('academico.anios-escolares.edit', $anioEscolar) }}" class="px-3.5 py-2 border border-stone-300 dark:border-neutral-700 rounded-xl text-xs font-semibold text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-800 hover:bg-stone-50 cursor-pointer">Editar</a>
                    @endif
                @endcan
                <a href="{{ route('academico.anios-escolares.index') }}" class="px-3.5 py-2 border border-stone-300 dark:border-neutral-700 rounded-xl text-xs font-semibold text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-800 hover:bg-stone-50 cursor-pointer">Volver</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 p-6">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white mb-4">Información General</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold text-neutral-400 uppercase">Nombre</dt>
                            <dd class="mt-1 font-semibold text-neutral-900 dark:text-neutral-100">{{ $anioEscolar->nombre }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-neutral-400 uppercase">Período</dt>
                            <dd class="mt-1 font-semibold text-neutral-900 dark:text-neutral-100">{{ $anioEscolar->nombre_periodo }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-neutral-400 uppercase">Fecha Inicio</dt>
                            <dd class="mt-1 text-neutral-700 dark:text-neutral-300">{{ $anioEscolar->fecha_inicio?->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-neutral-400 uppercase">Fecha Fin</dt>
                            <dd class="mt-1 text-neutral-700 dark:text-neutral-300">{{ $anioEscolar->fecha_fin?->format('d/m/Y') }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($anioEscolar->es_activo && isset($reporteValidaciones))
                    <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 p-6 space-y-3">
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">Validaciones para Cierre</h3>
                        @foreach ($reporteValidaciones['validaciones'] ?? [] as $validacion)
                            <div class="p-4 rounded-2xl border {{ $validacion['cumple'] ? 'border-emerald-200 dark:border-emerald-900 bg-emerald-50/60 dark:bg-emerald-950/30' : 'border-amber-200 dark:border-amber-900 bg-amber-50/60 dark:bg-amber-950/30' }} flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">{{ $validacion['mensaje'] }}</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $validacion['cumple'] ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $validacion['cumple'] ? 'Cumple' : 'Pendiente' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                @can('configuracion.gestionar')
                    @if (!$anioEscolar->es_cerrado && !empty($transiciones))
                        <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 p-6 space-y-3">
                            <h3 class="text-base font-bold text-neutral-900 dark:text-white">Cambiar Estado</h3>
                            @foreach ($transiciones as $transicion)
                                <form method="POST" action="{{ route('academico.anios-escolares.cambiar-estado', $anioEscolar) }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="estado" value="{{ $transicion }}">
                                    @if ($transicion === 'cerrado')
                                        <textarea name="observaciones_cierre" rows="2" required placeholder="Observaciones del cierre..." class="mb-2 w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-xs text-neutral-900 dark:text-neutral-100"></textarea>
                                    @endif
                                    <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 transition-colors cursor-pointer">
                                        Cambiar a {{ ucfirst($transicion) }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>