<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.alumnos.index') }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Volver al Directorio">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                {{ __('Registrar Nuevo Alumno') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Quitamos el fondo blanco de este contenedor porque las tarjetas del _form.blade.php ya traen su propio fondo y bordes -->
            <form action="{{ route('academico.alumnos.store') }}" method="POST">
                @csrf
                
                <!-- Invocamos la magia del formulario -->
                @include('academico.alumnos.partials._form', ['btnText' => 'Guardar Registro'])
                
            </form>
        </div>
    </div>
</x-app-layout>