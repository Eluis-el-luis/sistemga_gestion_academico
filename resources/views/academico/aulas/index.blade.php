<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Gestión de Aulas
                </h2>
            </div>
        </div>
    </x-slot>
    <div class="pb-12 pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse ($aulas as $aula)
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow relative group">
                        
                        <div class="h-2 w-full
                            {{ str_contains(strtolower($aula->modalidad->nombre ?? ''), 'preescolar') ? 'bg-pink-400' : '' }}
                            {{ str_contains(strtolower($aula->modalidad->nombre ?? ''), 'primaria') ? 'bg-blue-400' : '' }}
                            {{ str_contains(strtolower($aula->modalidad->nombre ?? ''), 'secundaria') ? 'bg-emerald-400' : '' }}
                            {{ !str_contains(strtolower($aula->modalidad->nombre ?? ''), 'preescolar') && !str_contains(strtolower($aula->modalidad->nombre ?? ''), 'primaria') && !str_contains(strtolower($aula->modalidad->nombre ?? ''), 'secundaria') ? 'bg-slate-300' : '' }}
                        "></div>

                        <div class="p-6 flex-grow flex flex-col">
                            <div class="flex justify-between items-start mb-5">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-1">{{ $aula->anioEscolar->nombre ?? 'N/A' }} • {{ $aula->modalidad->nombre ?? 'N/A' }}</span>
                                    <h3 class="font-black text-2xl text-[#3d2c1d] leading-none">
                                        {{ $aula->grado->nombre ?? 'N/A' }} <span class="text-[#e6ac27]">{{ $aula->nombre }}</span>
                                    </h3>
                                </div>
                                <span class="bg-slate-50 border border-slate-200 text-slate-600 px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $aula->turno }}</span>
                            </div>

                            <div class="space-y-3 mb-6 flex-grow">
                                <div class="flex items-center gap-2.5 text-sm">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span class="font-bold text-slate-600">{{ $aula->cupo }} Alumnos máx.</span>
                                </div>
                                
                                <div class="flex items-center gap-2.5 text-sm">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    @if($aula->docenteGuia)
                                        <span class="font-bold text-slate-600 truncate" title="{{ $aula->docenteGuia->usuario->nombre_completo ?? 'Sin nombre' }}">
                                            Prof. {{ explode(' ', trim($aula->docenteGuia->usuario->nombre_completo ?? ''))[0] ?? 'D' }}
                                        </span>
                                    @else
                                        <span class="font-bold text-rose-500">Sin Docente Guía</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-4 border-t border-slate-100">
                                
                                @if($contexto === 'asignacion')
                                    <a href="{{ route('academico.asignaciones.show', $aula->id) }}" class="flex-grow inline-flex justify-center items-center px-4 py-2 bg-[#FFFDF5] text-[#e6ac27] border border-[#e6ac27]/30 hover:bg-[#e6ac27] hover:text-white rounded-xl text-xs font-black transition-colors shadow-sm" title="Asignar Maestros">
                                        Asignar Maestros
                                    </a>
                                @elseif($contexto === 'horarios')
                                    <a href="{{ route('academico.aulas.horarios.index', $aula->id) }}" class="flex-grow inline-flex justify-center items-center px-4 py-2 bg-[#FFFDF5] text-[#e6ac27] border border-[#e6ac27]/30 hover:bg-[#e6ac27] hover:text-white rounded-xl text-xs font-black transition-colors shadow-sm" title="Armar Horario">
                                        Armar Horario
                                    </a>
                                @elseif($contexto === 'gestion')
                                    @can('update', $aula)
                                        <a href="{{ route('academico.aulas.edit', $aula->id) }}" class="flex-1 flex justify-center items-center gap-2 py-2 bg-slate-50 text-amber-600 rounded-xl hover:bg-amber-50 border border-slate-200 hover:border-amber-200 shadow-sm transition-colors text-xs font-black" title="Editar Aula">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Editar
                                        </a>
                                    @endcan

                                    @can('delete', $aula)
                                        <form action="{{ route('academico.aulas.destroy', $aula->id) }}" method="POST" class="flex-1 alerta-eliminar m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full flex justify-center items-center gap-2 py-2 bg-slate-50 text-rose-500 rounded-xl hover:bg-rose-50 border border-slate-200 hover:border-rose-200 shadow-sm transition-colors text-xs font-black" title="Eliminar Aula">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Borrar
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-12 text-center rounded-3xl border border-slate-200 shadow-sm flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-slate-100">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <h3 class="text-lg font-black text-[#3d2c1d]">No hay aulas aperturadas aún</h3>
                        <p class="text-sm font-bold text-slate-400 mt-2">Apertura una nueva aula desde el panel lateral para comenzar a organizar los espacios.</p>
                    </div>
                @endforelse
            </div>

            @if($aulas->hasPages())
                <div class="p-6 border-t border-slate-200 mt-6">
                    {{ $aulas->links() }}
                </div>
            @endif

        </div>
    </div>

    @if($contexto === 'gestion')
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
            
            @if(session('success'))
                Swal.mixin({
                    toast: true, position: 'top', showConfirmButton: false, timer: 3500, timerProgressBar: true,
                    customClass: { popup: 'rounded-2xl shadow-lg border border-slate-100' }
                }).fire({ icon: 'success', title: '{{ session("success") }}' });
            @endif
        });
    </script>
    @endif
</x-app-layout>