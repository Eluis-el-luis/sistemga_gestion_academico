<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel Principal">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-black text-2xl text-[#3d2c1d] tracking-tight">
                    {{ __('Historial General de Matrículas') }}
                </h2>
            </div>
        </div>
    </x-slot>

    <!-- Usamos Alpine para gestionar las pestañas de Parciales de forma visual -->
    <div class="py-10 bg-slate-50 min-h-screen relative" x-data="{ showTopBtn: false, parcialActivo: '1' }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center font-medium">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- 1. ZONA DE LOCKERS (SELECCIÓN DE AÑO ESCOLAR) -->
            <div>
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3">1. Seleccione el Año Escolar</h3>
                <div class="flex gap-3 overflow-x-auto pb-2 custom-scrollbar">
                    @php
                        // Determinamos qué año está seleccionado (por defecto el activo si no hay request)
                        $anioSeleccionadoId = request('anio_escolar_id', $anioActivo->id ?? null);
                    @endphp
                    
                    @foreach($aniosEscolares as $anio)
                        <a href="{{ route('academico.matriculas.index', ['anio_escolar_id' => $anio->id]) }}" 
                           class="min-w-[140px] px-5 py-4 rounded-2xl border-2 transition-all flex flex-col items-center justify-center gap-1 shadow-sm shrink-0
                           {{ $anioSeleccionadoId == $anio->id ? 'bg-[#FFFDF5] border-[#e6ac27] text-[#e6ac27]' : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300' }}">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            <span class="font-black text-sm {{ $anioSeleccionadoId == $anio->id ? 'text-[#3d2c1d]' : 'text-slate-700' }}">{{ $anio->nombre }}</span>
                            @if($anio->activo)
                                <span class="text-[9px] font-black uppercase tracking-widest text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-md mt-1 border border-emerald-100">Activo</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- 2. ZONA DE PESTAÑAS (PARCIALES) -->
            <div class="mt-8">
                <h3 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3">2. Seleccione el Corte Evaluativo</h3>
                <div class="flex space-x-2 border-b border-slate-200 overflow-x-auto">
                    @foreach(['1' => 'I Parcial', '2' => 'II Parcial', '3' => 'III Parcial', '4' => 'IV Parcial'] as $key => $nombreParcial)
                        <button @click="parcialActivo = '{{ $key }}'"
                                :class="parcialActivo === '{{ $key }}' ? 'border-[#e6ac27] text-[#3d2c1d] font-black bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-bold'"
                                class="px-6 py-3 border-b-4 text-sm transition-all whitespace-nowrap rounded-t-xl flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            {{ $nombreParcial }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- 3. ZONA DE BÚSQUEDA Y RESULTADOS -->
            <div class="bg-white p-6 md:p-8 rounded-3xl shadow-sm border border-slate-200 mt-6">
                <!-- Buscador Integrado -->
                <form method="GET" action="{{ route('academico.matriculas.index') }}" class="mb-6">
                    <!-- Mantenemos el año seleccionado oculto para no perderlo al buscar -->
                    <input type="hidden" name="anio_escolar_id" value="{{ $anioSeleccionadoId }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6 relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar estudiante por Nombre o CUP..." class="block w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-[#e6ac27] focus:border-[#e6ac27]">
                        </div>
                        
                        <div class="md:col-span-3">
                            <select name="aula_id" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 text-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] font-medium text-slate-700">
                                <option value="">Filtrar por Aula...</option>
                                @foreach ($aulas ?? [] as $aula)
                                    <option value="{{ $aula->id }}" {{ request('aula_id') == $aula->id ? 'selected' : '' }}>
                                        {{ $aula->grado->nombre ?? '' }} "{{ $aula->nombre }}"
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-3 flex gap-2">
                            <button type="submit" class="w-full py-3 bg-[#3d2c1d] text-white rounded-xl hover:bg-slate-800 text-sm font-black shadow-sm transition-all transform hover:-translate-y-0.5">Buscar</button>
                            @if(request()->hasAny(['buscar', 'aula_id']))
                                <a href="{{ route('academico.matriculas.index', ['anio_escolar_id' => $anioSeleccionadoId]) }}" class="flex items-center justify-center px-4 bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 transition-colors" title="Limpiar Búsqueda">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                <!-- TABLA DE MATRÍCULAS -->
                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="min-w-full divide-y divide-slate-100 text-sm border-collapse text-left">
                        <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-[10px] font-black tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">CUP</th>
                                <th class="px-6 py-4">Estudiante</th>
                                <th class="px-6 py-4">Aula Asignada</th>
                                <th class="px-6 py-4 text-center">Estado General</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                            @forelse ($matriculas as $matricula)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-6 py-4 font-black {{ $matricula->alumno ? 'text-slate-500' : 'text-rose-400' }}">
                                        {{ $matricula->alumno?->codigo_unico_persona ?? 'SIN-CUP' }}
                                    </td>
                                    <td class="px-6 py-4 font-black text-base {{ $matricula->alumno ? 'text-[#3d2c1d]' : 'text-rose-600' }}">
                                        {{ $matricula->alumno?->nombre_completo ?? '⚠️ Alumno no encontrado' }}
                                    </td>
                                    
                                    <!-- AULA ASIGNADA CORREGIDA -->
                                    <td class="px-6 py-4">
                                        <span class="block font-black text-[#e6ac27]">{{ $matricula->aula->grado->nombre ?? 'Sin Grado' }} "{{ $matricula->aula->nombre ?? 'N/A' }}"</span>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mt-0.5">Modalidad: {{ $matricula->aula->modalidad->nombre ?? 'N/A' }}</span>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg shadow-sm border
                                            {{ $matricula->estado === 'activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : '' }}
                                            {{ $matricula->estado === 'retirado' ? 'bg-rose-50 text-rose-700 border-rose-200/60' : '' }}
                                            {{ $matricula->estado === 'promovido' ? 'bg-blue-50 text-blue-700 border-blue-200/60' : '' }}
                                            {{ $matricula->estado === 'repitente' ? 'bg-amber-50 text-amber-700 border-amber-200/60' : '' }}">
                                            {{ $matricula->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        @if($matricula->alumno)
                                            <a href="{{ route('academico.alumnos.show', $matricula->alumno_id) }}" class="inline-flex p-2 bg-slate-50 text-blue-600 rounded-xl hover:bg-blue-100 border border-slate-200 hover:border-blue-300 shadow-sm transition-all" title="Ver Expediente">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                        @endif

                                        @can('update', $matricula)
                                            @if($matricula->estado === 'activo')
                                                <form method="POST" action="{{ route('academico.matriculas.retirar', $matricula) }}" class="inline-block alerta-baja">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs uppercase tracking-wider transition-colors ml-2">Retirar</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('academico.matriculas.reactivar', $matricula) }}" class="inline-block alerta-reactivar">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-bold text-xs uppercase tracking-wider transition-colors ml-2">Reactivar</button>
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-lg font-black text-slate-600">No encontramos matrículas</p>
                                        <p class="text-sm font-medium mt-1">Selecciona otro año, usa el buscador o inscribe a un estudiante desde el menú.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="pt-6">
                    {{ $matriculas->appends(request()->query())->links() ?? '' }}
                </div>
            </div>

        </div>

        <!-- Botón Volver Arriba -->
        <button x-show="showTopBtn" @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-8 right-8 z-50 p-3.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-full shadow-lg transition-all transform hover:scale-110 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
        </button>
    </div>

    <!-- Script de SweetAlert2 con UX Tip #2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alerta-baja').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Retirar a este estudiante?',
                        text: "Pasará a estado Inactivo y liberará su cupo en el aula actual.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, retirar',
                        cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });

            document.querySelectorAll('.alerta-reactivar').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Reactivar matrícula?',
                        text: "El estudiante volverá a figurar como Activo en su grado y sección.",
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, reactivar',
                        cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>