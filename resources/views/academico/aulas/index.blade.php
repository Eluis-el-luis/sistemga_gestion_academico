<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                {{ __('Directorio de Aulas Activas') }}
            </h2>
            
            @can('create', App\Models\Aula::class)
            <a href="{{ route('academico.aulas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-bold text-sm text-white hover:bg-indigo-700 shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                + Aperturar Nueva Aula
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12 relative" x-data="{ showTopBtn: false }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- TABLA PRINCIPAL -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="overflow-x-auto p-6 pt-0 mt-4">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left rounded-tl-lg">Periodo</th>
                                <th class="px-6 py-4 text-left">Nivel / Grado</th>
                                <th class="px-6 py-4 text-left">Sección</th>
                                <th class="px-6 py-4 text-center">Turno & Cupo</th>
                                <th class="px-6 py-4 text-left">Docente Titular (Guía)</th>
                                <th class="px-6 py-4 text-right rounded-tr-lg">Estructura</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700">
                            @forelse ($aulas as $aula)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Año Escolar -->
                                    <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                        {{ $aula->anioEscolar->nombre }}
                                    </td>
                                    
                                    <!-- Modalidad y Grado -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full mb-1
                                            {{ str_contains(strtolower($aula->modalidad->nombre), 'preescolar') ? 'bg-pink-100 text-pink-800' : '' }}
                                            {{ str_contains(strtolower($aula->modalidad->nombre), 'primaria') ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ str_contains(strtolower($aula->modalidad->nombre), 'secundaria') ? 'bg-emerald-100 text-emerald-800' : '' }}">
                                            {{ $aula->modalidad->nombre }}
                                        </span>
                                        <p class="font-bold text-gray-800 text-sm">{{ $aula->grado->nombre }}</p>
                                    </td>
                                    
                                    <!-- Sección -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-black text-lg text-indigo-700">{{ $aula->nombre }}</span>
                                    </td>
                                    
                                    <!-- Turno y Cupo -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <p class="font-bold text-gray-800">{{ ucfirst($aula->turno) }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $aula->cupo }} Estudiantes</p>
                                    </td>
                                    
                                    <!-- Docente Guía -->
                                    <td class="px-6 py-4">
                                        @if($aula->docenteGuia)
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                                    {{ substr($aula->docenteGuia->usuario->nombre_completo ?? 'D', 0, 2) }}
                                                </div>
                                                <span class="font-medium text-gray-900">{{ $aula->docenteGuia->usuario->nombre_completo ?? 'Sin nombre' }}</span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                ⚠ Sin Asignar
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Acción: Ver Materias/Horario -->
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <a href="{{ route('academico.aulas.show', $aula->id) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-indigo-200 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-50 hover:text-indigo-900 transition-colors shadow-sm">
                                            Gestionar Horario
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <p class="text-lg font-medium text-gray-900">No hay aulas aperturadas</p>
                                        <p class="text-sm">Haga clic en "+ Aperturar Nueva Aula" para iniciar el año lectivo.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-6 mb-2">
                        {{ $aulas->links() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón Volver Arriba -->
        <button x-show="showTopBtn" x-transition @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 z-50 p-3.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full shadow-lg transition-all transform hover:scale-110">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
        </button>
    </div>
</x-app-layout>