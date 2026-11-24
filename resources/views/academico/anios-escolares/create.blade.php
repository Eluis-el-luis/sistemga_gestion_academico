{{-- resources/views/academico/anios-escolares/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <h2 class="text-xl font-bold text-neutral-900 dark:text-white">Crear Año Escolar</h2>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Se creará en estado Planificación.</p>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 p-6 sm:p-8">
                <form method="POST" action="{{ route('academico.anios-escolares.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="nombre" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-1.5">Nombre del Año Escolar <span class="text-rose-500">*</span></label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" placeholder="Ej: 2026-2027" class="block w-full px-3.5 py-2.5 border border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm" required>
                        @error('nombre') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="fecha_inicio" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-1.5">Fecha de Inicio <span class="text-rose-500">*</span></label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ old('fecha_inicio') }}" class="block w-full px-3.5 py-2.5 border border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm" required>
                            @error('fecha_inicio') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="fecha_fin" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-1.5">Fecha de Fin <span class="text-rose-500">*</span></label>
                            <input type="date" name="fecha_fin" id="fecha_fin" value="{{ old('fecha_fin') }}" class="block w-full px-3.5 py-2.5 border border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm" required>
                            @error('fecha_fin') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100 dark:border-neutral-800">
                        <a href="{{ route('academico.anios-escolares.index') }}" class="inline-flex items-center px-4 py-2.5 border border-stone-300 dark:border-neutral-700 rounded-xl text-sm font-semibold text-neutral-700 dark:text-neutral-200 bg-white dark:bg-neutral-800 hover:bg-stone-50 dark:hover:bg-neutral-700 transition-colors cursor-pointer">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl text-sm font-bold transition-colors cursor-pointer">
                            Crear Año Escolar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>