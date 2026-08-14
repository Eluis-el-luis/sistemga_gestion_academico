<!-- resources/views/academico/alumnos/edit.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Datos del Alumno: ') }} <span class="text-indigo-600">{{ $alumno->nombre_completo }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('academico.alumnos.update', $alumno) }}" method="POST">
                        @csrf
                        @method('PUT') <!-- Convierte el POST en PUT para la actualización -->
                        
                        <!-- Incluimos el mismo parcial -->
                        @include('academico.alumnos.partials._form', ['btnText' => 'Actualizar Datos'])
                        
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>