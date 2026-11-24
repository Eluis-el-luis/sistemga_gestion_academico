{{-- resources/views/academico/alumnos/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-4 py-3 shadow-xs">
            <a href="{{ route('academico.alumnos.index') }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al Directorio">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                Editar Expediente: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $alumno->nombre_completo }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('academico.alumnos.update', $alumno) }}" method="POST">
                @csrf
                @method('PUT')
                @include('academico.alumnos.partials._form', ['btnText' => 'Actualizar Expediente'])
            </form>
        </div>
    </div>
</x-app-layout>