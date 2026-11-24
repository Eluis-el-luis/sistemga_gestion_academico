{{-- resources/views/academico/visor/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer" title="Volver al Panel Principal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Visor de Horarios</h2>
            </div>

            <span class="px-3.5 py-2 bg-stone-100 dark:bg-neutral-800 rounded-xl font-semibold text-xs uppercase text-neutral-600 dark:text-neutral-300">
                Año Escolar {{ date('Y') }}
            </span>
        </div>
    </x-slot>

    <div class="py-10 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h3 class="font-bold text-2xl text-neutral-900 dark:text-white">¿Qué horario deseas consultar?</h3>
                <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">Selecciona una categoría para consultar los horarios semanales.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <a href="{{ route('academico.visor.docentes') }}" class="block bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] p-8 text-center transition-colors cursor-pointer">
                    <div class="w-16 h-16 bg-amber-50 dark:bg-neutral-800 rounded-2xl flex items-center justify-center mx-auto mb-4 text-[#d49b1a] dark:text-[#e6ac27]">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h4 class="font-bold text-xl text-neutral-900 dark:text-white mb-1.5">Por Docente</h4>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Consulta la carga horaria semanal de cada profesor.</p>
                </a>

                <a href="{{ route('academico.visor.aulas') }}" class="block bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 hover:border-[#e6ac27] p-8 text-center transition-colors cursor-pointer">
                    <div class="w-16 h-16 bg-amber-50 dark:bg-neutral-800 rounded-2xl flex items-center justify-center mx-auto mb-4 text-[#d49b1a] dark:text-[#e6ac27]">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h4 class="font-bold text-xl text-neutral-900 dark:text-white mb-1.5">Por Aula</h4>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Consulta el horario de clases de cada grado y sección.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>