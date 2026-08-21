<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.alumnos.show', $alumno) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Volver a la Ficha">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                {{ __('Editar Expediente: ') }} <span class="text-indigo-600">{{ $alumno->nombre_completo }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('academico.alumnos.update', $alumno) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Invocamos el mismo formulario, pero con botón diferente -->
                @include('academico.alumnos.partials._form', ['btnText' => 'Actualizar Expediente'])
                
            </form>
        </div>
    </div>
</x-app-layout>