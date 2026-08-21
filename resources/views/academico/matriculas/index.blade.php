<!-- resources/views/academico/matriculas/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Control General de Matrículas') }}
            </h2>
            
            <div class="flex space-x-3">
                <!-- Botón Nuevo Ingreso: Dirige a crear el Expediente primero -->
                @can('create', App\Models\Alumno::class)
                    <a href="{{ route('academico.alumnos.create') }}" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 border border-blue-200 rounded-lg shadow-sm font-bold text-sm hover:bg-blue-50 transition-colors">
                        + Nuevo Ingreso (Expediente)
                    </a>
                @endcan
                
                <!-- Botón Reingreso: Dirige directo a la matrícula -->
                @can('create', App\Models\Matricula::class)
                    <a href="{{ route('academico.matriculas.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white border border-transparent rounded-lg shadow-sm font-bold text-sm hover:bg-emerald-700 transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        + Matricular (Reingreso)
                    </a>
                @endcan
            </div>
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

            <!-- TARJETA DE FILTROS -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <form method="GET" action="{{ route('academico.matriculas.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Año Escolar</label>
                        <select name="anio_escolar_id" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @foreach ($aniosEscolares ?? [] as $anio)
                                <option value="{{ $anio->id }}" {{ request('anio_escolar_id', $anioActivo->id ?? null) == $anio->id ? 'selected' : '' }}>
                                    {{ $anio->nombre }} {{ $anio->activo ? '(Activo)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Aula / Sección</label>
                        <select name="aula_id" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Todas las aulas</option>
                            @foreach ($aulas ?? [] as $aula)
                                <option value="{{ $aula->id }}" {{ request('aula_id') == $aula->id ? 'selected' : '' }}>
                                    {{ $aula->nombre }} ({{ $aula->grado->modalidad->nombre ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Estado</label>
                        <select name="estado" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Todos los Estados</option>
                            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="retirado" {{ request('estado') === 'retirado' ? 'selected' : '' }}>Retirado (Baja)</option>
                            <option value="repitente" {{ request('estado') === 'repitente' ? 'selected' : '' }}>Repitente</option>
                            <option value="promovido" {{ request('estado') === 'promovido' ? 'selected' : '' }}>Promovido</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2 pt-2 md:pt-0">
                        @if(request()->hasAny(['aula_id', 'estado', 'anio_escolar_id']))
                            <a href="{{ route('academico.matriculas.index') }}" class="w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-semibold transition-colors border border-gray-200">
                                Limpiar
                            </a>
                        @endif
                        <button type="submit" class="w-full px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-900 text-sm font-bold shadow-sm transition-colors">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABLA DE MATRÍCULAS -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="overflow-x-auto p-6 pt-0">
                    <table class="min-w-full divide-y divide-gray-200 text-sm mt-4">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left rounded-tl-lg">CUP</th>
                                <th class="px-6 py-4 text-left">Estudiante</th>
                                <th class="px-6 py-4 text-left">Aula Asignada</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-right rounded-tr-lg">Acciones (Depuración)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700">
                            @forelse ($matriculas as $matricula)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600">{{ $matricula->alumno->codigo_unico_persona }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $matricula->alumno->nombre_completo }}</td>
                                    <td class="px-6 py-4">
                                        <span class="block text-gray-900">{{ $matricula->aula->nombre }}</span>
                                        <span class="text-xs text-gray-500">{{ $matricula->anioEscolar->nombre }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full shadow-sm border
                                            {{ $matricula->estado === 'activo' ? 'bg-green-50 text-green-700 border-green-200' : '' }}
                                            {{ $matricula->estado === 'retirado' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                            {{ $matricula->estado === 'promovido' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                            {{ $matricula->estado === 'repitente' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}">
                                            {{ $matricula->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-3">
                                        
                                        <!-- Ver Expediente del Alumno -->
                                        <a href="{{ route('academico.alumnos.show', $matricula->alumno_id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-xs uppercase tracking-wider" title="Ver Expediente">
                                            Ficha
                                        </a>

                                        @can('update', $matricula)
                                            <span class="text-gray-300">|</span>
                                            
                                            @if($matricula->estado === 'activo')
                                                <!-- Botón Dar Baja (SweetAlert) -->
                                                <form method="POST" action="{{ route('academico.matriculas.retirar', $matricula) }}" class="inline-block alerta-baja">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-xs uppercase tracking-wider transition-colors">
                                                        Retirar
                                                    </button>
                                                </form>
                                            @else
                                                <!-- Botón Reactivar -->
                                                <form method="POST" action="{{ route('academico.matriculas.reactivar', $matricula) }}" class="inline-block alerta-reactivar">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-emerald-600 hover:text-emerald-900 font-bold text-xs uppercase tracking-wider transition-colors">
                                                        Reactivar
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-lg font-medium text-gray-900">No hay matrículas registradas</p>
                                        <p class="text-sm">Ajusta los filtros o inscribe a un nuevo estudiante.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-6 mb-2">{{ $matriculas->appends(request()->query())->links() ?? '' }}</div>
                </div>
            </div>

        </div>

        <!-- Botón Volver Arriba -->
        <button x-show="showTopBtn" @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 z-50 p-3.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full shadow-lg transition-all transform hover:scale-110">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
        </button>
    </div>

    <!-- Script de SweetAlert2 -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Alerta Baja (Retiro)
            document.querySelectorAll('.alerta-baja').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Confirmar Retiro?',
                        text: "El estudiante será dado de baja en el sistema para este año escolar.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, Retirar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });

            // Alerta Reactivar
            document.querySelectorAll('.alerta-reactivar').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Reactivar Matrícula?',
                        text: "El estudiante volverá a estar Activo en su aula.",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, Reactivar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>