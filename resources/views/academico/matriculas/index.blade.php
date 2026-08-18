<!-- resources/views/academico/matriculas/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Registro General de Matrículas') }}
            </h2>
            
            <div class="flex space-x-3">
                <!-- Botón Nuevo Ingreso: Dirige a crear el Expediente primero -->
                @can('create', App\Models\Alumno::class)
                <a href="{{ route('academico.alumnos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-150">
                    + Nuevo Ingreso
                </a>
                @endcan
                
                <!-- Botón Reingreso: Dirige directo a la matrícula con buscador -->
                @can('create', App\Models\Matricula::class)
                <a href="{{ route('academico.matriculas.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-150">
                    + Reingreso
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Alumno</th>
                                <th class="px-6 py-3">Periodo</th>
                                <th class="px-6 py-3">Aula</th>
                                <th class="px-6 py-3">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($matriculas as $matricula)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $matricula->alumno->nombre_completo }}</td>
                                    <td class="px-6 py-4">{{ $matricula->anioEscolar->nombre }}</td>
                                    <td class="px-6 py-4">{{ $matricula->aula->grado->nombre ?? '' }} - {{ $matricula->aula->nombre }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($matricula->estado) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center">No hay matrículas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $matriculas->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>