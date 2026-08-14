<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Directorio de Alumnos') }}
            </h2>
            
            <!-- Botón de crear: Solo visible si la Policy autoriza 'create' -->
            @can('create', App\Models\Alumno::class)
            <a href="{{ route('academico.alumnos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Nuevo Alumno
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Mensaje de éxito al guardar/editar/eliminar -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full whitespace-no-wrap text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Código Único</th>
                                <th class="px-6 py-3">Nombre Completo</th>
                                <th class="px-6 py-3">Sexo</th>
                                <th class="px-6 py-3">Fecha de Nac.</th>
                                <th class="px-6 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($alumnos as $alumno)
                                <tr class="hover:bg-gray-100">
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        {{ $alumno->codigo_unico_persona }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $alumno->nombre_completo }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $alumno->sexo === 'M' ? 'Masculino' : 'Femenino' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-center flex justify-center space-x-2">
                                        
                                        <!-- Botón Ver Ficha (Disponible para quien tenga viewAny) -->
                                        <a href="{{ route('academico.alumnos.show', $alumno) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Ver Ficha
                                        </a>

                                        <!-- Botón Editar (Protegido por la Policy verificando si tiene alcance sobre ESTE alumno) -->
                                        @can('update', $alumno)
                                            <span class="text-gray-300">|</span>
                                            <a href="{{ route('academico.alumnos.edit', $alumno) }}" class="text-blue-600 hover:text-blue-900">
                                                Editar
                                            </a>
                                        @endcan
                                        
                                        <!-- Botón Eliminar (Protegido por la Policy) -->
                                        @can('update', $alumno)
                                            <span class="text-gray-300">|</span>
                                            <form action="{{ route('academico.alumnos.destroy', $alumno) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este registro?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Eliminar
                                                </button>
                                            </form>
                                        @endcan

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No hay alumnos registrados en el sistema.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Paginación automática de Laravel -->
                    <div class="mt-4">
                        {{ $alumnos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>