<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Aulas') }}
            </h2>
            @can('create', App\Models\Aula::class)
            <a href="{{ route('academico.aulas.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-150">
                + Nueva Aula
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Periodo</th>
                                <th class="px-6 py-3">Modalidad / Grado</th>
                                <th class="px-6 py-3">Sección / Nombre</th>
                                <th class="px-6 py-3">Turno</th>
                                <th class="px-6 py-3">Docente Guía</th>
                                <th class="px-6 py-3">Cupo</th>
                                <th class="px-6 py-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($aulas as $aula)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-bold">{{ $aula->anioEscolar->nombre }}</td>
                                    <td class="px-6 py-4">{{ $aula->modalidad->nombre }}<br><span class="text-xs text-gray-400">{{ $aula->grado->nombre }}</span></td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $aula->nombre }}</td>
                                    <td class="px-6 py-4">{{ ucfirst($aula->turno) }}</td>
                                    <td class="px-6 py-4">{{ $aula->docenteGuia->usuario->nombre_completo ?? 'Sin asignar' }}</td>
                                    <td class="px-6 py-4">{{ $aula->cupo }}</td>
                                    <td class="px-6 py-4 text-indigo-600 font-bold hover:text-indigo-900">
                                        <a href="{{ route('academico.aulas.show', $aula->id) }}">Ver Materias</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center">No hay aulas registradas en el sistema.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $aulas->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>