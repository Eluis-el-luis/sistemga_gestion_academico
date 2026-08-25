<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-[#3d2c1d] leading-tight">
            {{ __('Panel Principal') }}
        </h2>
    </x-slot>

    @php
        // Lógica para el banner dinámico (Zona 1)
        $hora = now()->timezone('America/Managua')->hour;
        if ($hora < 12) {
            $saludo = 'Buenos días';
        } elseif ($hora < 18) {
            $saludo = 'Buenas tardes';
        } else {
            $saludo = 'Buenas noches';
        }
        // Identificar el primer rol del usuario para seleccionarlo por defecto
        $rolPorDefecto = auth()->user()->roles->first()->name ?? 'Usuario';
    @endphp

    <!-- INICIO DE LA VISTA CON ALPINE.JS -->
    <div class="py-8 bg-[#FFFDF5] min-h-screen" 
         x-data="{ 
            rolActivo: '{{ $rolPorDefecto }}',
            modalCrear: false, 
            modalEditar: false, 
            avisoEdit: {id:'', titulo:'', mensaje:''} 
         }">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg shadow-sm flex items-center font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- ZONA 1: BANNER DINÁMICO DE BIENVENIDA -->
            <div class="bg-white rounded-2xl shadow-sm border border-[#e6ac27]/30 p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-full bg-gradient-to-l from-[#e6ac27]/10 to-transparent"></div>
                <div class="relative z-10">
                    <h3 class="text-3xl font-black text-[#3d2c1d] tracking-tight">
                        {{ $saludo }}, {{ Auth::user()->nombre_completo ?? Auth::user()->name }}
                    </h3>
                    <p class="text-stone-500 mt-2 font-medium text-lg">Bienvenido a tu espacio de trabajo institucional.</p>
                    
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach (auth()->user()->roles as $rol)
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest bg-[#FFFDF5] text-[#e6ac27] border border-[#e6ac27]/50 shadow-sm">
                                {{ $rol->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
                
                <!-- Métricas generales visibles solo para administración -->
                @hasanyrole('Director|Subdirector|Gestor de Usuarios')
                <div class="relative z-10 flex gap-4 text-right">
                    <div class="bg-[#FFFDF5] border border-stone-200 rounded-xl p-4 shadow-sm text-center">
                        <span class="block text-xs font-bold text-stone-500 uppercase tracking-widest mb-1">Estudiantes</span>
                        <span class="block text-2xl font-black text-[#3d2c1d]">{{ $totalAlumnos }}</span>
                    </div>
                    <div class="bg-[#FFFDF5] border border-stone-200 rounded-xl p-4 shadow-sm text-center">
                        <span class="block text-xs font-bold text-stone-500 uppercase tracking-widest mb-1">Docentes</span>
                        <span class="block text-2xl font-black text-[#3d2c1d]">{{ $totalDocentes }}</span>
                    </div>
                </div>
                @endhasanyrole
            </div>

            <!-- ZONA 3: SELECTOR INTERACTIVO DE ROLES (PESTAÑAS) -->
            <div class="flex space-x-2 border-b border-stone-200 overflow-x-auto pb-px">
                @foreach(auth()->user()->roles as $rol)
                    <button @click="rolActivo = '{{ $rol->name }}'"
                            :class="rolActivo === '{{ $rol->name }}' ? 'border-[#e6ac27] text-[#3d2c1d] font-black bg-white' : 'border-transparent text-stone-500 hover:text-[#3d2c1d] hover:border-stone-300 font-bold'"
                            class="px-6 py-3 border-b-4 text-sm transition-all whitespace-nowrap rounded-t-lg">
                        Módulo: {{ $rol->name }}
                    </button>
                @endforeach
            </div>

            <!-- ÁREA PRINCIPAL DIVIDIDA EN DOS COLUMNAS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-4">
                
                <!-- COLUMNA IZQUIERDA: ZONAS 5, 6 Y 7 (Ocupa 2/3 de la pantalla, dinámico) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- MÓDULO ADMINISTRATIVO -->
                    @hasanyrole('Director|Subdirector|Gestor de Usuarios')
                    <div x-show="['Director', 'Subdirector', 'Gestor de Usuarios'].includes(rolActivo)" x-transition.opacity style="display: none;">
                        <h4 class="text-sm font-black text-[#3d2c1d] uppercase tracking-widest mb-4 border-b border-stone-200 pb-2">Gestión Administrativa</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <a href="{{ route('academico.usuarios.index') }}" class="group bg-white p-5 rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center group-hover:scale-110 transition-transform border border-[#e6ac27]/20">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-[#3d2c1d] group-hover:text-[#e6ac27] transition-colors">Directorio de Personal</span>
                                    <span class="block text-xs font-medium text-stone-500 mt-0.5">Gestión de usuarios y accesos</span>
                                </div>
                            </a>
                            <a href="{{ route('academico.aulas.index') }}" class="group bg-white p-5 rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center group-hover:scale-110 transition-transform border border-[#e6ac27]/20">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1H4z"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-[#3d2c1d] group-hover:text-[#e6ac27] transition-colors">Gestión de Aulas</span>
                                    <span class="block text-xs font-medium text-stone-500 mt-0.5">Asignación y horarios</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- MÓDULO DOCENTE GUÍA -->
                    @hasanyrole('Docente Guía')
                    <div x-show="rolActivo === 'Docente Guía'" x-transition.opacity style="display: none;">
                        <h4 class="text-sm font-black text-[#3d2c1d] uppercase tracking-widest mb-4 border-b border-stone-200 pb-2">Gestión de Tutoría (Aula Guía)</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <a href="#" class="group bg-white p-5 rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-[#3d2c1d] group-hover:text-blue-700 transition-colors">Mis Estudiantes</span>
                                    <span class="block text-xs font-medium text-stone-500 mt-0.5">Listado y perfiles</span>
                                </div>
                            </a>
                            <a href="#" class="group bg-white p-5 rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-[#3d2c1d] group-hover:text-purple-700 transition-colors">Control de Asistencia</span>
                                    <span class="block text-xs font-medium text-stone-500 mt-0.5">Pase de lista diario</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- MÓDULO DOCENTE POR ASIGNATURA -->
                    @hasanyrole('Docente por Asignatura|Docente Guía')
                    <div x-show="['Docente por Asignatura', 'Docente Guía'].includes(rolActivo)" x-transition.opacity style="display: none;">
                        <h4 class="text-sm font-black text-[#3d2c1d] uppercase tracking-widest mb-4 border-b border-stone-200 pb-2">Gestión Académica</h4>
                        
                        <!-- 1. TUS BOTONES PREMIUM ORIGINALES INTACTOS -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <a href="{{ route('academico.notas.index') }}" class="group bg-white p-5 rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-[#3d2c1d] group-hover:text-emerald-700 transition-colors">Libreta de Calificaciones</span>
                                    <span class="block text-xs font-medium text-stone-500 mt-0.5">Ingreso de acumulados y notas</span>
                                </div>
                            </a>
                            <a href="#" class="group bg-white p-5 rounded-2xl border border-stone-200 shadow-sm hover:shadow-md hover:border-[#e6ac27] transition-all flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-stone-100 text-[#3d2c1d] flex items-center justify-center group-hover:scale-110 transition-transform border border-stone-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-[#3d2c1d] transition-colors">Mi Horario</span>
                                    <span class="block text-xs font-medium text-stone-500 mt-0.5">Horas clase asignadas</span>
                                </div>
                            </a>
                        </div>

                       
                        <div class="mt-6">
                            @include('components.dashboard.asignatura-stats')
                        </div>
                    </div>
                    @endhasanyrole

                </div>

                <!-- COLUMNA DERECHA: COMUNICADOS (Zona 4 - Estática y siempre visible) -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-stone-200">
                        <div class="px-6 py-4 border-b border-stone-200 flex justify-between items-center bg-[#FFFDF5] rounded-t-2xl">
                            <h3 class="text-sm font-black text-[#3d2c1d] uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                Comunicados Oficiales
                            </h3>
                            @hasanyrole('Director|Subdirector')
                            <button @click="modalCrear = true" class="text-[#3d2c1d] hover:text-[#e6ac27] transition-colors" title="Nuevo Aviso">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                            @endhasanyrole
                        </div>
                        <div class="p-6 space-y-6">
                            @forelse ($avisos as $aviso)
                                <div class="group relative pb-6 border-b border-stone-100 last:border-0 last:pb-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-sm font-bold text-[#3d2c1d]">{{ $aviso->titulo }}</h4>
                                            <div class="flex items-center gap-2 mt-1 text-[10px] text-stone-500 font-bold uppercase tracking-wider">
                                                <span>{{ $aviso->autor_nombre }}</span>
                                                <span>&bull;</span>
                                                <span>{{ \Carbon\Carbon::parse($aviso->created_at)->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                        @hasanyrole('Director|Subdirector')
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button @click="avisoEdit = {id: {{ $aviso->id }}, titulo: '{{ addslashes($aviso->titulo) }}', mensaje: '{{ addslashes(preg_replace("/\r\n/", "\\n", $aviso->mensaje)) }}'}; modalEditar = true" class="text-blue-500 hover:bg-blue-50 p-1.5 rounded" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </button>
                                            
                                            <!-- FORMULARIO ELIMINAR ACTUALIZADO -->
                                            <form action="{{ route('dashboard.avisos.destroy', $aviso->id) }}" method="POST" class="inline form-eliminar-aviso">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:bg-red-50 p-1.5 rounded transition-colors" title="Eliminar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                        @endhasanyrole
                                    </div>
                                    <p class="text-stone-600 text-sm mt-3 leading-relaxed whitespace-pre-line">{{ $aviso->mensaje }}</p>
                                </div>
                            @empty
                                <div class="text-center py-6 text-stone-500">
                                    <p class="text-sm font-medium">No hay comunicados recientes.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODALES DE DIRECCIÓN -->
        @hasanyrole('Director|Subdirector')
        <!-- Modal Crear -->
        <div x-show="modalCrear" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-[#3d2c1d]/60 backdrop-blur-sm transition-opacity" @click="modalCrear = false"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
                    <h3 class="text-lg font-black text-[#3d2c1d] mb-4 border-b border-stone-200 pb-2">Redactar Nuevo Aviso</h3>
                    <form method="POST" action="{{ route('dashboard.avisos.store') }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-1">Título</label>
                                <input type="text" name="titulo" required class="w-full rounded-lg border-stone-300 focus:border-[#e6ac27] focus:ring-[#e6ac27]">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-1">Mensaje</label>
                                <textarea name="mensaje" rows="4" required class="w-full rounded-lg border-stone-300 focus:border-[#e6ac27] focus:ring-[#e6ac27]"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="modalCrear = false" class="px-4 py-2 text-sm font-bold text-stone-600 bg-stone-100 hover:bg-stone-200 rounded-lg">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-[#3d2c1d] bg-[#e6ac27] hover:bg-[#d69f22] rounded-lg">Publicar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Editar -->
        <div x-show="modalEditar" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-[#3d2c1d]/60 backdrop-blur-sm transition-opacity" @click="modalEditar = false"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
                    <h3 class="text-lg font-black text-[#3d2c1d] mb-4 border-b border-stone-200 pb-2">Editar Aviso</h3>
                    <form method="POST" :action="'/dashboard/avisos/' + avisoEdit.id">
                        @csrf @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-1">Título</label>
                                <input type="text" name="titulo" x-model="avisoEdit.titulo" required class="w-full rounded-lg border-stone-300 focus:border-[#e6ac27] focus:ring-[#e6ac27]">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-stone-700 mb-1">Mensaje</label>
                                <textarea name="mensaje" rows="4" x-model="avisoEdit.mensaje" required class="w-full rounded-lg border-stone-300 focus:border-[#e6ac27] focus:ring-[#e6ac27]"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="modalEditar = false" class="px-4 py-2 text-sm font-bold text-stone-600 bg-stone-100 hover:bg-stone-200 rounded-lg">Cancelar</button>
                            <button type="submit" class="px-4 py-2 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endhasanyrole

    </div>

    <!-- IMPORTAR SWEETALERT2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SCRIPT DE SWEETALERT2 PARA ELIMINAR -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.form-eliminar-aviso').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Eliminar comunicado?',
                        text: "Esta acción no se puede deshacer y desaparecerá para todo el personal.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#eab308',
                        cancelButtonColor: '#ef4444',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            popup: 'rounded-2xl border border-stone-200 shadow-xl'
                        }
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