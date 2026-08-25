<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-black text-2xl text-[#3d2c1d] tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                {{ __('Directorio de Aulas Activas') }}
            </h2>
            
            @can('create', App\Models\Aula::class)
            <a href="{{ route('academico.aulas.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-xl font-black text-sm shadow-lg shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 focus:ring-2 focus:ring-offset-2 focus:ring-[#e6ac27]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Aperturar Nueva Aula
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen relative" x-data="{ showTopBtn: false }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- TABLA PRINCIPAL -->
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm border-collapse text-left">
                        <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-[11px] font-black tracking-widest border-b border-slate-200">
                            <tr>
                                <th class="px-8 py-5">Periodo</th>
                                <th class="px-6 py-5">Nivel / Grado</th>
                                <th class="px-6 py-5">Sección</th>
                                <th class="px-6 py-5 text-center">Turno & Cupo</th>
                                <th class="px-6 py-5">Docente Titular (Guía)</th>
                                <th class="px-8 py-5 text-right">Estructura</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                            @forelse ($aulas as $aula)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <!-- Año Escolar -->
                                    <td class="px-8 py-5 font-black text-[#3d2c1d] whitespace-nowrap">
                                        {{ $aula->anioEscolar->nombre }}
                                    </td>
                                    
                                    <!-- Modalidad y Grado -->
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg border shadow-sm mb-1.5
                                            {{ str_contains(strtolower($aula->modalidad->nombre), 'preescolar') ? 'bg-pink-50 text-pink-700 border-pink-200/60' : '' }}
                                            {{ str_contains(strtolower($aula->modalidad->nombre), 'primaria') ? 'bg-blue-50 text-blue-700 border-blue-200/60' : '' }}
                                            {{ str_contains(strtolower($aula->modalidad->nombre), 'secundaria') ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60' : '' }}">
                                            {{ $aula->modalidad->nombre }}
                                        </span>
                                        <p class="font-bold text-slate-800 text-sm">{{ $aula->grado->nombre }}</p>
                                    </td>
                                    
                                    <!-- Sección -->
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span class="font-black text-xl text-[#e6ac27]">{{ $aula->nombre }}</span>
                                    </td>
                                    
                                    <!-- Turno y Cupo -->
                                    <td class="px-6 py-5 text-center whitespace-nowrap">
                                        <p class="font-bold text-[#3d2c1d]">{{ ucfirst($aula->turno) }}</p>
                                        <p class="text-xs font-medium text-slate-500 mt-1">{{ $aula->cupo }} Máximo</p>
                                    </td>
                                    
                                    <!-- Docente Guía -->
                                    <td class="px-6 py-5">
                                        @if($aula->docenteGuia)
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-black text-xs border border-slate-200">
                                                    {{ substr($aula->docenteGuia->usuario->nombre_completo ?? 'D', 0, 2) }}
                                                </div>
                                                <span class="font-bold text-slate-800">{{ $aula->docenteGuia->usuario->nombre_completo ?? 'Sin nombre' }}</span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-rose-50 text-rose-700 border border-rose-200/60">
                                                ⚠ Sin Asignar
                                            </span>
                                        @endif
                                    </td>
                                    
                                    <!-- Acción -->
                                    <!-- Acción -->
                                    <td class="px-8 py-5 text-right whitespace-nowrap space-x-2">
                                        <!-- Botón Gestionar -->
                                        <a href="{{ route('academico.aulas.show', $aula->id) }}" class="inline-flex p-2 bg-slate-50 text-blue-600 rounded-xl hover:bg-blue-100 border border-slate-200 hover:border-blue-300 shadow-sm transition-all" title="Gestionar Aula y Horario">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                        </a>

                                        @can('update', $aula)
                                            <!-- Botón Editar -->
                                            <a href="{{ route('academico.aulas.edit', $aula->id) }}" class="inline-flex p-2 bg-slate-50 text-amber-600 rounded-xl hover:bg-amber-100 border border-slate-200 hover:border-amber-300 shadow-sm transition-all" title="Editar Aula">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                        @endcan

                                        @can('delete', $aula)
                                            <!-- Botón Eliminar -->
                                            <form action="{{ route('academico.aulas.destroy', $aula->id) }}" method="POST" class="inline-block alerta-eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex p-2 bg-slate-50 text-rose-500 rounded-xl hover:bg-rose-100 border border-slate-200 hover:border-rose-300 shadow-sm transition-all" title="Eliminar Aula">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        <p class="text-lg font-black text-slate-600">No hay aulas aperturadas aún</p>
                                        <p class="text-sm font-medium mt-1">Crea un aula nueva para poder matricular a los estudiantes.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="p-6 border-t border-slate-100 bg-white">
                        {{ $aulas->links() }}
                    </div>
                </div>
            </div>
        </div>

        <button x-show="showTopBtn" x-transition @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-8 right-8 z-50 p-3.5 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-full shadow-lg transition-all transform hover:scale-110">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const formulariosEliminar = document.querySelectorAll('.alerta-eliminar');
            formulariosEliminar.forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Borrar esta aula?',
                        text: "Se eliminarán sus horarios y no podrás recuperarla. Asegúrate de que no tenga alumnos matriculados.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, borrar aula',
                        cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>