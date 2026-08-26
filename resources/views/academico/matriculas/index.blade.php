<!-- resources/views/academico/matriculas/index.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-black text-2xl text-[#3d2c1d] tracking-tight">
                {{ __('Control General de Matrículas') }}
            </h2>
            
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <!-- Botón Nuevo Ingreso (Secundario) -->
                @can('create', App\Models\Alumno::class)
                    <a href="{{ route('academico.alumnos.create') }}" class="inline-flex justify-center items-center px-5 py-2.5 bg-white text-[#3d2c1d] border border-slate-200 rounded-xl shadow-sm font-bold text-sm hover:bg-slate-50 hover:border-slate-300 transition-all">
                        + Nuevo Ingreso (Ficha)
                    </a>
                @endcan
                
                <!-- Botón Reingreso (Principal) -->
                @can('create', App\Models\Matricula::class)
                    <a href="{{ route('academico.matriculas.create') }}" class="inline-flex justify-center items-center px-5 py-2.5 bg-[#e6ac27] text-white border border-transparent rounded-xl shadow-md shadow-[#e6ac27]/20 font-black text-sm hover:bg-[#c48e1b] transition-all transform hover:-translate-y-0.5 focus:ring-2 focus:ring-offset-2 focus:ring-[#e6ac27]">
                        + Matricular (Reingreso)
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen relative" x-data="{ showTopBtn: false }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Mensajes de Sesión -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- TARJETA DE FILTROS -->
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-200">
                <form method="GET" action="{{ route('academico.matriculas.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-5">
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Año Escolar</label>
                        <select name="anio_escolar_id" class="w-full rounded-xl border-slate-200 bg-slate-50/50 text-sm shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] font-medium text-slate-700 transition-colors">
                            @foreach ($aniosEscolares ?? [] as $anio)
                                <option value="{{ $anio->id }}" {{ request('anio_escolar_id', $anioActivo->id ?? null) == $anio->id ? 'selected' : '' }}>
                                    {{ $anio->nombre }} {{ $anio->activo ? '(Activo)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Aula / Sección</label>
                        <select name="aula_id" class="w-full rounded-xl border-slate-200 bg-slate-50/50 text-sm shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] font-medium text-slate-700 transition-colors">
                            <option value="">Todas las aulas</option>
                            @foreach ($aulas ?? [] as $aula)
                                <option value="{{ $aula->id }}" {{ request('aula_id') == $aula->id ? 'selected' : '' }}>
                                    {{ $aula->nombre }} ({{ $aula->grado->modalidad->nombre ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Estado</label>
                        <select name="estado" class="w-full rounded-xl border-slate-200 bg-slate-50/50 text-sm shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] font-medium text-slate-700 transition-colors">
                            <option value="">Todos los Estados</option>
                            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>🟢 Activo</option>
                            <option value="retirado" {{ request('estado') === 'retirado' ? 'selected' : '' }}>🔴 Retirado (Baja)</option>
                            <option value="repitente" {{ request('estado') === 'repitente' ? 'selected' : '' }}>🟠 Repitente</option>
                            <option value="promovido" {{ request('estado') === 'promovido' ? 'selected' : '' }}>🔵 Promovido</option>
                        </select>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end items-end gap-5 pt-2">
                        @if(request()->hasAny(['aula_id', 'estado', 'anio_escolar_id']))
                            <a href="{{ route('academico.matriculas.index') }}" class="text-sm font-bold text-slate-400 hover:text-rose-600 transition-colors underline underline-offset-4 decoration-2 decoration-slate-200 hover:decoration-rose-300 pb-2">
                                Limpiar
                            </a>
                        @endif
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-[#3d2c1d] text-white rounded-xl hover:bg-slate-800 text-sm font-black shadow-sm transition-all transform hover:-translate-y-0.5">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>

            <!-- TABLA DE MATRÍCULAS -->
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm border-collapse text-left">
                        <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-[11px] font-black tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="px-8 py-5">CUP</th>
                                <th class="px-6 py-5">Estudiante</th>
                                <th class="px-6 py-5">Aula Asignada</th>
                                <th class="px-6 py-5 text-center">Estado</th>
                                <th class="px-8 py-5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                            @forelse ($matriculas as $matricula)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <!-- APLICACIÓN DE LA RED DE SEGURIDAD AQUÍ -->
                                    <td class="px-8 py-5 font-black {{ $matricula->alumno ? 'text-slate-500' : 'text-rose-400' }}">
                                        {{ $matricula->alumno?->codigo_unico_persona ?? 'SIN-CUP' }}
                                    </td>
                                    <td class="px-6 py-5 font-black text-base {{ $matricula->alumno ? 'text-[#3d2c1d]' : 'text-rose-600' }}">
                                        {{ $matricula->alumno?->nombre_completo ?? '⚠️ Alumno no encontrado (Registro Huérfano)' }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <span class="block font-bold text-slate-800">{{ $matricula->aula->nombre ?? 'N/A' }}</span>
                                        <span class="text-[11px] font-black uppercase tracking-widest text-slate-400 mt-1">{{ $matricula->anioEscolar->nombre ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg shadow-sm border
                                            {{ $matricula->estado === 'activo' ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : '' }}
                                            {{ $matricula->estado === 'retirado' ? 'bg-rose-50 text-rose-700 border-rose-200/60' : '' }}
                                            {{ $matricula->estado === 'promovido' ? 'bg-blue-50 text-blue-700 border-blue-200/60' : '' }}
                                            {{ $matricula->estado === 'repitente' ? 'bg-amber-50 text-amber-700 border-amber-200/60' : '' }}">
                                            {{ $matricula->estado }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right space-x-3">
                                        
                                        <!-- Ver Expediente del Alumno (Deshabilitado si es huérfano) -->
                                        @if($matricula->alumno)
                                            <a href="{{ route('academico.alumnos.show', $matricula->alumno_id) }}" class="inline-flex p-2 bg-slate-50 text-blue-600 rounded-xl hover:bg-blue-100 border border-slate-200 hover:border-blue-300 shadow-sm transition-all" title="Ver Expediente">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>
                                        @endif

                                        @can('update', $matricula)
                                            <span class="text-slate-300">|</span>
                                            
                                            @if($matricula->estado === 'activo')
                                                <!-- Botón Dar Baja -->
                                                <form method="POST" action="{{ route('academico.matriculas.retirar', $matricula) }}" class="inline-block alerta-baja">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs uppercase tracking-wider transition-colors ml-2">
                                                        Retirar
                                                    </button>
                                                </form>
                                            @else
                                                <!-- Botón Reactivar -->
                                                <form method="POST" action="{{ route('academico.matriculas.reactivar', $matricula) }}" class="inline-block alerta-reactivar">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-bold text-xs uppercase tracking-wider transition-colors ml-2">
                                                        Reactivar
                                                    </button>
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
                                        <p class="text-sm font-medium mt-1">Ajusta los filtros arriba o inscribe a un nuevo estudiante.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="p-6 border-t border-slate-100 bg-white">
                        {{ $matriculas->appends(request()->query())->links() ?? '' }}
                    </div>
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
            // Alerta Baja (Retiro)
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

            // Alerta Reactivar
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