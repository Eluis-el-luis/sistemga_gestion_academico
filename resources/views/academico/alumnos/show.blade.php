<!-- resources/views/academico/alumnos/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Ficha del Alumno: ') }} <span class="text-indigo-600">{{ $alumno->nombre_completo }}</span>
            </h2>
            <a href="{{ route('academico.alumnos.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Volver al Directorio
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Tarjeta 1: Datos Personales -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 border-b pb-2 mb-4">Datos Personales</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide">Código Único</p>
                        <p class="font-semibold text-gray-900">{{ $alumno->codigo_unico_persona }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide">Sexo</p>
                        <p class="font-semibold text-gray-900">{{ $alumno->sexo === 'M' ? 'Masculino' : 'Femenino' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide">Fecha de Nacimiento</p>
                        <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase tracking-wide">Edad Aproximada</p>
                        <p class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->age }} años</p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Historial de Matrículas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center border-b pb-2 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Historial Académico</h3>
                    
                    <!-- Botón para Matricular -->
                    @can('create', App\Models\Matricula::class)
                        <a href="{{ route('academico.matriculas.create', ['alumno_id' => $alumno->id]) }}" class="text-sm bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-150">
                            + Nueva Matrícula
                        </a>
                    @endcan
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full whitespace-no-wrap text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Año Escolar</th>
                                <th class="px-6 py-3">Grado</th>
                                <th class="px-6 py-3">Aula</th>
                                <th class="px-6 py-3">Fecha de Matrícula</th>
                                <th class="px-6 py-3">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($alumno->matriculas as $matricula)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $matricula->anioEscolar->nombre }}</td>
                                    <td class="px-6 py-4">{{ $matricula->aula->grado->nombre }}</td>
                                    <td class="px-6 py-4">{{ $matricula->aula->nombre }}</td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($matricula->fecha_matricula)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4">
                                        <!-- Insignia dinámica de Tailwind para los 4 estados de matrícula -->
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $matricula->estado === 'activo' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $matricula->estado === 'retirado' ? 'bg-red-100 text-red-800' : '' }}
                                            {{ $matricula->estado === 'promovido' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $matricula->estado === 'repitente' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ ucfirst($matricula->estado) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        El alumno no tiene historial de matrículas registrado en el sistema.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>