<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <!-- Flecha de regreso -->
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Directorio de Estudiantes
                </h2>
            </div>
            
            <!-- Botón de Acción Principal -->
            <a href="{{ route('academico.alumnos.create') }}" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-2.5 px-6 rounded-xl shadow-md transition-all transform hover:-translate-y-0.5 flex items-center gap-2 text-sm">
                <span>+</span> Registrar Estudiante
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen relative" x-data="{ showTopBtn: false }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes de Sesión -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- PANEL DE FILTROS AVANZADOS (Estilo Premium) -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                <form method="GET" action="{{ route('academico.alumnos.index') }}" class="space-y-6">
                    
                    <!-- Fila 1: Buscador de texto -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por Nombre Completo o Código Único (CUP)..." class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl leading-5 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm transition-all shadow-sm">
                    </div>

                    <!-- Fila 2: Selectores en cascada -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                        <!-- Selector de Modalidad -->
                        <div>
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Modalidad</label>
                            <select name="modalidad_id" id="filtro_modalidad" class="w-full border-slate-200 bg-white rounded-xl shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm font-medium text-slate-700 transition-colors">
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
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Grado</label>
                            <select name="grado_id" id="filtro_grado" class="w-full border-slate-200 bg-white rounded-xl shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm font-medium text-slate-700 transition-colors">
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
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Aula / Sección</label>
                            <select name="aula_id" id="filtro_aula" class="w-full border-slate-200 bg-white rounded-xl shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm font-medium text-slate-700 transition-colors">
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
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Estado Académico</label>
                            <select name="estado" class="w-full border-slate-200 bg-white rounded-xl shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm font-medium text-slate-700 transition-colors">
                                <option value="">Cualquier estado</option>
                                <option value="activo" {{ (request('estado') === 'activo' || (!request()->hasAny(['buscar', 'modalidad_id', 'grado_id', 'aula_id', 'estado']) && request('estado') !== '')) ? 'selected' : '' }}>🟢 Activo</option>
                                <option value="retirado" {{ request('estado') == 'retirado' ? 'selected' : '' }}>🔴 Retirado</option>
                                <option value="repitente" {{ request('estado') == 'repitente' ? 'selected' : '' }}>🟠 Repitente</option>
                                <option value="promovido" {{ request('estado') == 'promovido' ? 'selected' : '' }}>🔵 Promovido</option>
                            </select>
                        </div>
                    </div>

                    <!-- Botones de Acción de Filtro -->
                    <div class="flex flex-col sm:flex-row justify-end items-center pt-4 gap-6 border-t border-slate-100 mt-4">
                        @if(request()->hasAny(['buscar', 'modalidad_id', 'grado_id', 'aula_id', 'estado']))
                            <a href="{{ route('academico.alumnos.index') }}" class="text-sm font-bold text-slate-400 hover:text-rose-600 transition-colors underline underline-offset-4 decoration-2 decoration-slate-200 hover:decoration-rose-300">
                                Limpiar filtros
                            </a>
                        @endif
                        <button type="submit" class="px-6 py-2.5 bg-[#3d2c1d] text-white rounded-xl hover:bg-slate-800 font-black text-sm shadow-md transition-all transform hover:-translate-y-0.5 w-full sm:w-auto">
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABLA PRINCIPAL DE ESTUDIANTES -->
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left border-collapse">
                        <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-[11px] font-black tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="px-8 py-5">Código (CUP)</th>
                                <th class="px-6 py-5">Nombre Completo</th>
                                <th class="px-6 py-5">Grado y Sección</th>
                                <th class="px-6 py-5 text-center">Sexo / Edad</th>
                                <th class="px-8 py-5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                            @forelse ($alumnos as $alumno)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-8 py-5">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $alumno->codigo_unico_persona }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <p class="font-black text-[#3d2c1d] text-base">{{ $alumno->nombre_completo }}</p>
                                    </td>
                                    
                                    <!-- LÓGICA PARA MOSTRAR EL GRADO ACTUAL -->
                                    <td class="px-6 py-5">
                                        @php
                                            $matriculaActiva = $alumno->matriculas->first();
                                        @endphp
                                        
                                        @if($matriculaActiva && $matriculaActiva->aula && $matriculaActiva->aula->grado)
                                            <div class="flex flex-col">
                                                <span class="font-bold text-[#3d2c1d]">{{ $matriculaActiva->aula->grado->nombre }}</span>
                                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-0.5">Sección: {{ $matriculaActiva->aula->nombre }}</span>
                                            </div>
                                        @else
                                            <span class="inline-flex px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg bg-rose-50 text-rose-600 border border-rose-100">
                                                Sin Matrícula
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-5 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold shadow-sm {{ $alumno->sexo === 'M' ? 'bg-blue-50 text-blue-700 border border-blue-200/60' : 'bg-pink-50 text-pink-700 border border-pink-200/60' }}" title="{{ $alumno->sexo === 'M' ? 'Masculino' : 'Femenino' }}">
                                                {{ $alumno->sexo }}
                                            </span>
                                            <span class="text-sm font-medium text-slate-500">{{ \Carbon\Carbon::parse($alumno->fecha_nacimiento)->age }} años</span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-8 py-5 text-right space-x-2 whitespace-nowrap">
                                        <!-- Botón Ver Ficha -->
                                        <a href="{{ route('academico.alumnos.show', $alumno) }}" class="inline-flex p-2 bg-slate-50 text-blue-600 rounded-xl hover:bg-blue-50 border border-slate-200 hover:border-blue-200 shadow-sm transition-all" title="Ver Expediente Completo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>

                                        <!-- Botón Editar -->
                                        @can('update', $alumno)
                                            <a href="{{ route('academico.alumnos.edit', $alumno) }}" class="inline-flex p-2 bg-slate-50 text-amber-600 rounded-xl hover:bg-amber-50 border border-slate-200 hover:border-amber-200 shadow-sm transition-all" title="Editar Información">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                        @endcan

                                        <!-- Botón Eliminar -->
                                        @can('delete', $alumno)
                                            <form action="{{ route('academico.alumnos.destroy', $alumno) }}" method="POST" class="inline-block alerta-eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex p-2 bg-slate-50 text-red-500 rounded-xl hover:bg-red-50 border border-slate-200 hover:border-red-200 shadow-sm transition-all" title="Eliminar Registro">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <p class="text-lg font-black text-slate-600">No se encontraron estudiantes</p>
                                        <p class="text-sm font-medium mt-1">Intenta con otro término en el buscador o limpia los filtros.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="p-6 border-t border-slate-100 bg-white flex justify-between items-center">
                        <div class="text-[11px] text-slate-500 font-black uppercase tracking-widest bg-slate-100 px-4 py-2 rounded-xl border border-slate-200">
                            Total: {{ $alumnos->total() }} registros
                        </div>
                        <div class="mt-2 sm:mt-0">
                            {{ $alumnos->appends(request()->query())->links() ?? '' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Botón flotante "Volver Arriba" -->
        <button x-show="showTopBtn" x-transition @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-8 right-8 z-50 p-3.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-full shadow-lg transition-all transform hover:scale-110 focus:outline-none" title="Volver arriba">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
        </button>
    </div>

    <!-- Script de Cascada y SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            if(gradoSelect && modSelect){
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
            }

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
                        confirmButtonColor: '#ef4444', 
                        cancelButtonColor: '#64748b', 
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
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