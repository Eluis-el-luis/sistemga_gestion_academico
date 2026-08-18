<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Directorio de Alumnos') }}
            </h2>

            @can('create', App\Models\Alumno::class)
                <a href="{{ route('academico.alumnos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-150">
                    + Nuevo Registro de Alumno
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- BARRA DE FILTROS -->
            <form method="GET" action="{{ route('academico.alumnos.index') }}" class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200 flex flex-col md:flex-row gap-4 items-end">
                <!-- Selector de Modalidad -->
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Modalidad</label>
                    <select name="modalidad_id" id="filtro_modalidad" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Todas</option>
                        @foreach($modalidades as $mod)
                            <option value="{{ $mod->id }}" {{ request('modalidad_id') == $mod->id ? 'selected' : '' }}>
                                {{ $mod->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Selector de Grado -->
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Grado</label>
                    <select name="grado_id" id="filtro_grado" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Todos</option>
                        @foreach($grados as $grado)
                            <!-- MAGIA: Guardamos el modalidad_id en un atributo "data-modalidad" -->
                            <option value="{{ $grado->id }}" data-modalidad="{{ $grado->modalidad_id }}" {{ request('grado_id') == $grado->id ? 'selected' : '' }}>
                                {{ $grado->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Aula</label>
                    <select name="aula_id" id="filtro_aula" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">Todas</option>
                        @foreach($aulas as $aula)
                            <option value="{{ $aula->id }}" {{ request('aula_id') == $aula->id ? 'selected' : '' }}>{{ $aula->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex space-x-2 w-full md:w-auto">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded w-full md:w-auto">
                        Filtrar
                    </button>
                    <a href="{{ route('academico.alumnos.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded text-center w-full md:w-auto">
                        Limpiar
                    </a>
                </div>
            </form>

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


    <!-- Script para Filtros Dinámicos (Cascada) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modSelect = document.getElementById('filtro_modalidad');
            const gradoSelect = document.getElementById('filtro_grado');

            // Función 1: Cuando cambia la Modalidad, filtramos los Grados
            function actualizarGrados() {
                const modIdSeleccionada = modSelect.value;
                let gradoSigueSiendoValido = false;

                // Recorremos todas las opciones del select de Grados
                Array.from(gradoSelect.options).forEach(opcion => {
                    if (opcion.value === "") return; // Ignoramos la opción "Todos"

                    const modalidadDelGrado = opcion.getAttribute('data-modalidad');

                    // Si no hay modalidad seleccionada, o si el grado pertenece a la modalidad: lo mostramos
                    if (modIdSeleccionada === "" || modalidadDelGrado === modIdSeleccionada) {
                        opcion.style.display = '';
                        opcion.hidden = false;
                        if (opcion.selected) gradoSigueSiendoValido = true;
                    } else {
                        // Si no pertenece, lo ocultamos
                        opcion.style.display = 'none';
                        opcion.hidden = true;
                    }
                });

                // Si teníamos seleccionado un grado que acaba de ser ocultado, lo reseteamos a "Todos"
                if (gradoSelect.value !== "" && !gradoSigueSiendoValido) {
                    gradoSelect.value = "";
                }
            }

            // Función 2: Cuando cambia el Grado, ajustamos la Modalidad
            gradoSelect.addEventListener('change', function() {
                const opcionSeleccionada = this.options[this.selectedIndex];
                const modalidadDelGrado = opcionSeleccionada.getAttribute('data-modalidad');

                // Si seleccionó un grado específico, forzamos a que la Modalidad cambie a su padre
                if (modalidadDelGrado && modSelect.value !== modalidadDelGrado) {
                    modSelect.value = modalidadDelGrado;
                    actualizarGrados(); // Actualizamos la lista para ocultar el resto
                }
            });

            // Escuchamos los cambios en el select de Modalidad
            modSelect.addEventListener('change', actualizarGrados);

            // Ejecutamos la función una vez al cargar la página (por si hay filtros activos en la URL)
            actualizarGrados();
        });
    </script>
</x-app-layout>