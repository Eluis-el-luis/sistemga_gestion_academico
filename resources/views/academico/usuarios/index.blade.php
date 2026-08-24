<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-slate-800 leading-tight">
                {{ __('Gestión de Personal y Docentes') }}
            </h2>
            <a href="{{ route('academico.usuarios.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 rounded-xl font-bold text-sm text-white hover:bg-blue-700 shadow-sm transition-all transform hover:-translate-y-0.5">
                + Registrar Personal
            </a>
        </div>
    </x-slot>

    <!-- AQUÍ INYECTAMOS LA LÓGICA DE ALPINE PARA EL MODAL -->
    <div class="py-10 bg-slate-50 min-h-screen relative" x-data="{ 
        showTopBtn: false,
        modalReset: false,
        usuarioId: null,
        usuarioNombre: '',
        password: '',
        showPw: false,
        generarClave() {
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!?#$%';
            let result = '';
            for (let i = 0; i < 10; i++) result += chars.charAt(Math.floor(Math.random() * chars.length));
            this.password = result;
        },
        abrirModal(id, nombre) {
            this.usuarioId = id;
            this.usuarioNombre = nombre;
            this.showPw = false;
            this.generarClave();
            this.modalReset = true;
        }
    }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes de Sesión -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Contenedor Principal de la Tabla -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-3xl border border-slate-100">
                
                <!-- Buscador -->
                <div class="p-6 md:p-8 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <form method="GET" action="{{ route('academico.usuarios.index') }}" class="w-full sm:w-1/2 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre, correo o rol..." class="block w-full pl-11 pr-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all shadow-sm">
                        <button type="submit" class="hidden"></button>
                    </form>
                    <div class="text-sm text-slate-500 font-bold bg-slate-50 px-4 py-2 rounded-xl border border-slate-100">
                        Mostrando {{ $usuarios->count() }} registros
                    </div>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/50 text-slate-400 uppercase text-[11px] font-extrabold tracking-widest">
                            <tr>
                                <th class="px-8 py-5 text-left">Nombre Completo</th>
                                <th class="px-6 py-5 text-left">Correo Institucional</th>
                                <th class="px-6 py-5 text-left">Funciones / Roles</th>
                                <th class="px-6 py-5 text-center">Acceso</th>
                                <th class="px-8 py-5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 text-slate-700">
                            @forelse ($usuarios as $usuario)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-8 py-5">
                                        <p class="font-black text-slate-800 text-base">{{ $usuario->nombre_completo ?? $usuario->name }}</p>
                                        @if($usuario->docente)
                                            <p class="text-xs text-slate-400 font-mono mt-0.5">CUP: {{ $usuario->docente->codigo_unico_persona }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-5 font-medium text-slate-500">{{ $usuario->email }}</td>
                                    <td class="px-6 py-5">
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse($usuario->roles as $rol)
                                                <span class="inline-flex px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg bg-blue-50 text-blue-600 border border-blue-100/50">
                                                    {{ $rol->name }}
                                                </span>
                                            @empty
                                                <span class="inline-flex px-2.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg bg-slate-100 text-slate-500">
                                                    Sin Rol
                                                </span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl {{ $usuario->activo ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $usuario->activo ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                            {{ $usuario->activo ? 'Activo' : 'Bloqueado' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right space-x-2 whitespace-nowrap">
                                        
                                        <!-- Botón Editar -->
                                        <a href="{{ route('academico.usuarios.edit', $usuario) }}" class="inline-flex p-2 bg-white text-amber-500 rounded-xl hover:bg-amber-50 border border-slate-200 hover:border-amber-200 shadow-sm transition-all" title="Editar Información">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>

                                        <!-- Botón Restablecer Contraseña -->
                                        <button type="button" @click="abrirModal({{ $usuario->id }}, '{{ addslashes($usuario->nombre_completo ?? $usuario->name) }}')" class="inline-flex p-2 bg-white text-blue-500 rounded-xl hover:bg-blue-50 border border-slate-200 hover:border-blue-200 shadow-sm transition-all" title="Restablecer Contraseña">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                        </button>

                                        <!-- Botón Desactivar -->
                                        <form action="{{ route('academico.usuarios.destroy', $usuario) }}" method="POST" class="inline-block alerta-desactivar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex p-2 bg-white {{ $usuario->activo ? 'text-red-500 hover:bg-red-50 hover:border-red-200' : 'text-emerald-500 hover:bg-emerald-50 hover:border-emerald-200' }} rounded-xl border border-slate-200 shadow-sm transition-all" title="{{ $usuario->activo ? 'Bloquear Acceso' : 'Restaurar Acceso' }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if($usuario->activo)
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    @endif
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-8 py-12 text-center">
                                        <p class="text-lg font-bold text-slate-500">No se encontraron docentes o personal registrado.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="p-6 border-t border-slate-50">
                        {{ $usuarios->appends(request()->query())->links() ?? '' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE RESTABLECER CONTRASEÑA -->
        <div x-show="modalReset" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="modalReset" x-transition.opacity class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>

            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div x-show="modalReset" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    @click.away="modalReset = false"
                    class="relative transform overflow-hidden rounded-[2rem] bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100 p-8">
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-black text-slate-800" id="modal-title">Restablecer Contraseña</h3>
                        <button @click="modalReset = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 p-2 rounded-xl transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form method="POST" :action="`{{ url('academico/usuarios') }}/${usuarioId}/reset-password`" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <p class="text-sm text-slate-500 font-medium">
                            Estás a punto de generar una nueva clave de acceso para <strong class="text-slate-800 font-bold" x-text="usuarioNombre"></strong>.
                        </p>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nueva Contraseña <span class="text-red-500">*</span></label>
                            <div class="flex rounded-xl shadow-sm">
                                <div class="relative flex-grow focus-within:z-10">
                                    <input :type="showPw ? 'text' : 'password'" name="password" x-model="password" required class="block w-full rounded-none rounded-l-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600">
                                        <svg x-show="!showPw" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        <svg x-show="showPw" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                    </button>
                                </div>
                                <button type="button" @click="generarClave()" class="relative -ml-px inline-flex items-center gap-2 rounded-r-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100 focus:outline-none transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    <span>Generar</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4">
                            <button type="button" @click="modalReset = false" class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" class="rounded-xl border border-transparent bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-colors">
                                Guardar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div> 

    <!-- Script de SweetAlert2 -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alerta-desactivar').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Modificar Acceso?',
                        text: "El estado de este miembro del personal cambiará.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#eab308',
                        cancelButtonColor: '#ef4444',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar',
                        customClass: {
                            popup: 'rounded-3xl'
                        }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>