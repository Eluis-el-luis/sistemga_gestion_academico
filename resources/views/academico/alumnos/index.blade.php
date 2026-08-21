<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Directorio General de Alumnos') }}
            </h2>

            @can('create', App\Models\Alumno::class)
                <a href="{{ route('academico.alumnos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    + Nuevo Registro
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12 relative" x-data="{ showTopBtn: false }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes de Sesión -->
            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- PANEL DE FILTROS AVANZADOS -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('academico.alumnos.index') }}" class="space-y-4">
                    
                    <!-- Fila 1: Buscador de texto -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por Nombre o Código Único (CUP)..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition duration-150 shadow-sm">
                    </div>

                    <!-- Fila 2: Selectores en cascada -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Selector de Modalidad -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Modalidad</label>
                            <select name="modalidad_id" id="filtro_modalidad" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Todas</option>
                                @foreach($modalidades as $mod)
                                    <option value="{{ $mod->id }}" {{ request('modalidad_id') == $mod->id ? 'selected' : '' }}>
                                        {{ $mod->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Selector de Grado -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Grado</label>
                            <select name="grado_id" id="filtro_grado" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Todos</option>
                                @foreach($grados as $grado)
                                    <option value="{{ $grado->id }}" data-modalidad="{{ $grado->modalidad_id }}" {{ request('grado_id') == $grado->id ? 'selected' : '' }}>
                                        {{ $grado->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Selector de Aula -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Aula / Sección</label>
                            <select name="aula_id" id="filtro_aula" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Todas</option>
                                @foreach($aulas as $aula)
                                    <option value="{{ $aula->id }}" {{ request('aula_id') == $aula->id ? 'selected' : '' }}>
                                        {{ $aula->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Selector de Estado -->
                        <div>
                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Estado de Matrícula</label>
                            <select name="estado" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Todos</option>
                                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                                <option value="retirado" {{ request('estado') == 'retirado' ? 'selected' : '' }}>Retirado</option>
                                <option value="repitente" {{ request('estado') == 'repitente' ? 'selected' : '' }}>Repitente</option>
                                <option value="promovido" {{ request('estado') == 'promovido' ? 'selected' : '' }}>Promovido</option>
                            </select>
                        </div>
                    </div>

                    <!-- Botones de Acción de Filtro -->
                    <div class="flex justify-end space-x-2 pt-2">
                        @if(request()->hasAny(['buscar', 'modalidad_id', 'grado_id', 'aula_id', 'estado']))
                            <a href="{{ route('academico.alumnos.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-semibold transition-colors border border-gray-200">
                                Limpiar Filtros
                            </a>
                        @endif
                        <button type="submit" class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 font-bold text-sm shadow-sm transition-colors">
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABLA PRINCIPAL DE ESTUDIANTES -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="overflow-x-auto p-6 pt-0">
                    <table class="min-w-full divide-y divide-gray-200 text-sm mt-4">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left rounded-tl-lg">Código Único (CUP)</th>
                                <th class="px-6 py-4 text-left">Nombre Completo</th>
                                <th class="px-6 py-4 text-center">Sexo</th>
                                <th class="px-6 py-4 text-center">Edad</th>
                                <th class="px-6 py-4 text-right rounded-tr-lg">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700">
                            @forelse ($alumnos as $alumno)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600">
                                        {{ $alumno->codigo_unico_persona }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900">
                                        {{ $alumno->nombre_completo }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $alumno->sexo === 'M' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                                            {{ $alumno->sexo === 'M' ? 'Masculino' : 'Femenino' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center text-gray-600">
                                        {{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->age }} años
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        
                                        <!-- Botón Ver Ficha (Azul) -->
                                        <a href="{{ route('academico.alumnos.show', $alumno) }}" class="inline-flex items-center justify-center p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors shadow-sm border border-blue-100" title="Ver Expediente Completo">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>

                                        <!-- Botón Editar (Ámbar) - Protegido por Policy -->
                                        @can('update', $alumno)
                                            <a href="{{ route('academico.alumnos.edit', $alumno) }}" class="inline-flex items-center justify-center p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors shadow-sm border border-amber-100" title="Editar Información">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                        @endcan

                                        <!-- Botón Eliminar (Rojo) - Protegido por Policy -->
                                        @can('delete', $alumno)
                                            <form action="{{ route('academico.alumnos.destroy', $alumno) }}" method="POST" class="inline-block alerta-eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center p-2 bg-red-50 text-red-500 rounded-lg hover:bg-red-100 transition-colors shadow-sm border border-red-100" title="Eliminar Registro">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endcan

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-900">No se encontraron estudiantes</p>
                                        <p class="text-sm">Ajusta los filtros de búsqueda o registra un nuevo alumno.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <!-- Paginación de Laravel -->
                    <div class="mt-6 mb-2">
                        {{ $alumnos->appends(request()->query())->links() ?? '' }}
                    </div>
                </div>
            </div>

        </div>

        <!-- Botón flotante "Volver Arriba" -->
        <button 
            x-show="showTopBtn" 
            x-transition 
            @click="window.scrollTo({top: 0, behavior: 'smooth'})"
            class="fixed bottom-6 right-6 z-50 p-3.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full shadow-lg transition-all transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-yellow-400"
            title="Volver arriba">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
        </button>
    </div>

    <!-- Script de Cascada (Tu lógica original optimizada) y SweetAlert2 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lógica de Filtros en Cascada
            const modSelect = document.getElementById('filtro_modalidad');
            const gradoSelect = document.getElementById('filtro_grado');

            function actualizarGrados() {
                const modIdSeleccionada = modSelect.value;
                let gradoSigueSiendoValido = false;

                Array.from(gradoSelect.options).forEach(opcion => {
                    if (opcion.value === "") return; 
                    const modalidadDelGrado = opcion.getAttribute('data-modalidad');

                    if (modIdSeleccionada === "" || modalidadDelGrado === modIdSeleccionada) {
                        opcion.style.display = '';
                        opcion.hidden = false;
                        if (opcion.selected) gradoSigueSiendoValido = true;
                    } else {
                        opcion.style.display = 'none';
                        opcion.hidden = true;
                    }
                });

                if (gradoSelect.value !== "" && !gradoSigueSiendoValido) {
                    gradoSelect.value = "";
                }
            }

            gradoSelect.addEventListener('change', function() {
                const opcionSeleccionada = this.options[this.selectedIndex];
                const modalidadDelGrado = opcionSeleccionada.getAttribute('data-modalidad');
                if (modalidadDelGrado && modSelect.value !== modalidadDelGrado) {
                    modSelect.value = modalidadDelGrado;
                    actualizarGrados();
                }
            });

            modSelect.addEventListener('change', actualizarGrados);
            actualizarGrados();

            // Lógica de SweetAlert2 para eliminar
            const formulariosEliminar = document.querySelectorAll('.alerta-eliminar');
            formulariosEliminar.forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault(); 
                    
                    Swal.fire({
                        title: '¿Eliminar Expediente?',
                        text: "Esta acción no se puede deshacer. Se borrarán los datos del estudiante.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444', // Rojo Tailwind
                        cancelButtonColor: '#6b7280', // Gris Tailwind
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit(); 
                        }
                    });
                });
            });
        });
    </script>
</x-app-layout>