<!-- resources/views/academico/alumnos/create.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Alumno') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('academico.alumnos.store') }}" method="POST">
                        @csrf
                        
                        <!-- Incluimos el parcial y le pasamos la variable btnText -->
                        @include('academico.alumnos.partials._form', ['btnText' => 'Guardar Registro'])
                        
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>