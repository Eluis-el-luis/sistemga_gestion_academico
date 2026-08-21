<!-- resources/views/academico/alumnos/show.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.alumnos.index') }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Volver al Directorio">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Expediente: <span class="text-indigo-700">{{ $alumno->nombre_completo }}</span>
                </h2>
            </div>
            
            <div class="flex items-center gap-3">
                <span class="px-4 py-2 bg-indigo-50 text-indigo-700 font-mono font-bold rounded-lg border border-indigo-100 shadow-sm">
                    CUP: {{ $alumno->codigo_unico_persona }}
                </span>
                
                @can('update', $alumno)
                    <a href="{{ route('academico.alumnos.edit', $alumno) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-lg shadow-sm transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Editar Ficha
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- TARJETA 1: DATOS PERSONALES -->
            <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                <div class="bg-indigo-50 border-b border-indigo-100 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Datos Personales
                    </h3>
                    @if($alumno->acepta_compromiso_cristiano)
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full border border-blue-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Compromiso Cristiano
                        </span>
                    @endif
                </div>
                
                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Sexo</p>
                        <p class="font-medium text-gray-900">{{ $alumno->sexo === 'M' ? 'Masculino' : 'Femenino' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Fecha de Nacimiento</p>
                        <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d / m / Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Edad Actual</p>
                        <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->age }} años</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Dirección Domiciliar</p>
                        <p class="font-medium text-gray-900">{{ $alumno->direccion_domiciliar ?: 'No registrada' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Hermanos en el Colegio</p>
                        @if($alumno->hermanos_en_colegio)
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach(explode(',', $alumno->hermanos_en_colegio) as $hermano)
                                    <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-800 text-sm font-medium rounded-full border border-gray-200">
                                        {{ trim($hermano) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="font-medium text-gray-500 italic">No aplica</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- FILA DE 2 COLUMNAS: FAMILIA Y SALUD -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- TARJETA 2: DATOS FAMILIARES (Con Alpine.js Tabs) -->
                <div class="lg:col-span-2 bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden" x-data="{ tab: 'madre' }">
                    <div class="bg-blue-50 border-b border-blue-100 px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <h3 class="text-lg font-bold text-blue-900 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Núcleo Familiar
                        </h3>
                        
                        <!-- Navegación de Pestañas -->
                        <div class="flex space-x-1 bg-blue-100/50 p-1 rounded-lg">
                            <button @click="tab = 'madre'" :class="tab === 'madre' ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-600 hover:bg-blue-100/50'" class="px-4 py-1.5 text-sm font-bold rounded-md transition-all">Madre</button>
                            <button @click="tab = 'padre'" :class="tab === 'padre' ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-600 hover:bg-blue-100/50'" class="px-4 py-1.5 text-sm font-bold rounded-md transition-all">Padre</button>
                            <button @click="tab = 'tutor'" :class="tab === 'tutor' ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-600 hover:bg-blue-100/50'" class="px-4 py-1.5 text-sm font-bold rounded-md transition-all">Tutor</button>
                        </div>
                    </div>
                    
                    <div class="p-6 min-h-[220px]">
                        <!-- Contenido Madre -->
                        <div x-show="tab === 'madre'" x-transition.opacity class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Nombre</p><p class="font-medium text-gray-900">{{ $alumno->madre_nombre_completo ?: '—' }}</p></div>
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Cédula</p><p class="font-medium text-gray-900">{{ $alumno->madre_cedula ?: '—' }}</p></div>
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Teléfono</p><p class="font-medium text-gray-900">{{ $alumno->madre_telefono ?: '—' }}</p></div>
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Ocupación</p><p class="font-medium text-gray-900">{{ $alumno->madre_ocupacion ?: '—' }}</p></div>
                            <div class="md:col-span-2">
                                <p class="text-xs font-bold text-gray-400 uppercase mb-1">Congregación Religiosa</p>
                                <p class="font-medium text-gray-900">{{ $alumno->madre_asiste_iglesia ? 'Asiste a: ' . ($alumno->madre_nombre_iglesia ?: 'No especificada') : 'No asiste' }}</p>
                            </div>
                        </div>

                        <!-- Contenido Padre -->
                        <div x-show="tab === 'padre'" x-transition.opacity style="display: none;" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Nombre</p><p class="font-medium text-gray-900">{{ $alumno->padre_nombre_completo ?: '—' }}</p></div>
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Cédula</p><p class="font-medium text-gray-900">{{ $alumno->padre_cedula ?: '—' }}</p></div>
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Teléfono</p><p class="font-medium text-gray-900">{{ $alumno->padre_telefono ?: '—' }}</p></div>
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Ocupación</p><p class="font-medium text-gray-900">{{ $alumno->padre_ocupacion ?: '—' }}</p></div>
                            <div class="md:col-span-2">
                                <p class="text-xs font-bold text-gray-400 uppercase mb-1">Congregación Religiosa</p>
                                <p class="font-medium text-gray-900">{{ $alumno->padre_asiste_iglesia ? 'Asiste a: ' . ($alumno->padre_nombre_iglesia ?: 'No especificada') : 'No asiste' }}</p>
                            </div>
                        </div>

                        <!-- Contenido Tutor -->
                        <div x-show="tab === 'tutor'" x-transition.opacity style="display: none;" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Nombre Legal</p><p class="font-medium text-gray-900">{{ $alumno->tutor_nombre_completo ?: '—' }}</p></div>
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Cédula</p><p class="font-medium text-gray-900">{{ $alumno->tutor_cedula ?: '—' }}</p></div>
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Teléfono</p><p class="font-medium text-gray-900">{{ $alumno->tutor_telefono ?: '—' }}</p></div>
                            <div><p class="text-xs font-bold text-gray-400 uppercase mb-1">Ocupación</p><p class="font-medium text-gray-900">{{ $alumno->tutor_ocupacion ?: '—' }}</p></div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 italic border-t border-gray-100 pt-4 mt-2">
                                    El tutor legal es la persona responsable directa del alumno ante la institución en caso de que los padres no estén presentes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TARJETA 3: SALUD Y AUTORIZACIONES -->
                <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                    <div class="bg-red-50 border-b border-red-100 px-6 py-4">
                        <h3 class="text-lg font-bold text-red-900 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Salud y Emergencias
                        </h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Enfermedades Crónicas / Alergias</p>
                            @if($alumno->enfermedades_cronicas)
                                <span class="inline-flex px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded-md border border-red-200">
                                    {{ $alumno->enfermedades_cronicas }}
                                </span>
                            @else
                                <p class="font-medium text-gray-500 italic">Ninguna reportada</p>
                            @endif
                        </div>
                        
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Autorizado para Retirar</p>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <p class="font-bold text-gray-900">{{ $alumno->autorizado_retirar_nombre ?: 'Solo Padres/Tutor' }}</p>
                                @if($alumno->autorizado_retirar_nombre)
                                    <div class="flex flex-col sm:flex-row gap-4 mt-2 text-sm text-gray-600">
                                        <span class="flex items-center gap-1"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg> {{ $alumno->autorizado_retirar_cedula ?: 'Sin cédula' }}</span>
                                        <span class="flex items-center gap-1"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> {{ $alumno->autorizado_retirar_telefono ?: 'Sin teléfono' }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- FIN FILA 2 COLUMNAS -->

            <!-- TARJETA 4: HISTORIAL DE MATRÍCULAS -->
            <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
                <div class="bg-emerald-50 border-b border-emerald-100 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-emerald-900 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Historial Académico (Matrículas)
                    </h3>
                    
                    @can('create', App\Models\Matricula::class)
                        <a href="{{ route('academico.matriculas.create', ['alumno_id' => $alumno->id]) }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-900 bg-white px-3 py-1.5 rounded-lg border border-emerald-200 shadow-sm transition-colors">
                            + Nueva Matrícula
                        </a>
                    @endcan
                </div>
                
                <div class="overflow-x-auto p-6 pt-0 mt-4">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left rounded-tl-lg">Año Lectivo</th>
                                <th class="px-6 py-4 text-left">Nivel / Grado</th>
                                <th class="px-6 py-4 text-left">Sección</th>
                                <th class="px-6 py-4 text-left">Fecha Matrícula</th>
                                <th class="px-6 py-4 text-center rounded-tr-lg">Estado Final</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-800">
                            @forelse ($alumno->matriculas->sortByDesc('anioEscolar.nombre') as $matricula)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ $matricula->anioEscolar->nombre }}</td>
                                    <td class="px-6 py-4 font-medium">{{ $matricula->aula->grado->nombre }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $matricula->aula->nombre }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ \Carbon\Carbon::parse($matricula->fecha_matricula)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full shadow-sm border
                                            {{ $matricula->estado === 'activo' ? 'bg-green-50 text-green-700 border-green-200' : '' }}
                                            {{ $matricula->estado === 'retirado' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                            {{ $matricula->estado === 'promovido' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                            {{ $matricula->estado === 'repitente' ? 'bg-amber-50 text-amber-700 border-amber-200' : '' }}">
                                            {{ $matricula->estado }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-base font-medium text-gray-900">Sin historial académico</p>
                                        <p class="text-sm">El estudiante aún no tiene registros de matrículas en el sistema.</p>
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