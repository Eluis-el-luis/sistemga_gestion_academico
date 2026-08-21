<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Usuarios') }}
            </h2>
            <a href="{{ route('academico.usuarios.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 shadow-sm transition-colors">
                + Nuevo Usuario
            </a>
        </div>
    </x-slot>

    <div class="py-12 relative" x-data="{ 
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

            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <form method="GET" action="{{ route('academico.usuarios.index') }}" class="w-full sm:w-1/2 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o correo..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg sm:text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <button type="submit" class="hidden"></button>
                    </form>
                    <div class="text-sm text-gray-500 font-medium">Mostrando {{ $usuarios->count() }} usuarios</div>
                </div>

                <div class="overflow-x-auto p-6 pt-0 mt-4">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-500 uppercase text-xs font-bold tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left rounded-tl-lg">Nombre</th>
                                <th class="px-6 py-4 text-left">Correo / Usuario</th>
                                <th class="px-6 py-4 text-left">Roles en Sistema</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                                <th class="px-6 py-4 text-right rounded-tr-lg">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 text-gray-700">
                            @forelse ($usuarios as $usuario)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900">{{ $usuario->nombre_completo ?? $usuario->name }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $usuario->email }}</td>
                                    <td class="px-6 py-4">
                                        <!-- AQUÍ ESTÁ LA MAGIA MULTI-ROL -->
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse($usuario->roles as $rol)
                                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                                    {{ $rol->name }}
                                                </span>
                                            @empty
                                                <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-full bg-gray-100 text-gray-500 border border-gray-200">
                                                    Sin Rol
                                                </span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex px-3 py-1 text-xs font-bold rounded-full {{ $usuario->activo ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                        <a href="{{ route('academico.usuarios.edit', $usuario) }}" class="inline-flex p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 border border-amber-100 shadow-sm" title="Editar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <button type="button" @click="abrirModal({{ $usuario->id }}, '{{ addslashes($usuario->nombre_completo ?? $usuario->name) }}')" class="inline-flex p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 border border-blue-100 shadow-sm" title="Restablecer Contraseña">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                        </button>
                                        <form action="{{ route('academico.usuarios.destroy', $usuario) }}" method="POST" class="inline-block alerta-desactivar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex p-2 {{ $usuario->activo ? 'bg-red-50 text-red-500 hover:bg-red-100 border-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100 border-green-100' }} rounded-lg border shadow-sm" title="{{ $usuario->activo ? 'Desactivar' : 'Activar' }}">
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
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">No se encontraron usuarios.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-6 mb-2">{{ $usuarios->appends(request()->query())->links() ?? '' }}</div>
                </div>
            </div>
        </div>

        <!-- MODAL DE CONTRASEÑA -->
        <div x-show="modalReset" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div x-show="modalReset" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"></div>
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div x-show="modalReset" x-transition.opacity @click.away="modalReset = false" class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-gray-900">Restablecer Contraseña</h3>
                        <button @click="modalReset = false" class="text-gray-400 hover:text-gray-500 focus:outline-none"><svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                    </div>
                    <form method="POST" :action="`{{ url('academico/usuarios') }}/${usuarioId}/reset-password`">
                        @csrf
                        @method('PUT')
                        <div class="px-6 py-6">
                            <p class="text-sm text-gray-600 mb-4">Generando nueva clave para <strong class="text-gray-900" x-text="usuarioNombre"></strong>.</p>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nueva Contraseña <span class="text-red-500">*</span></label>
                            <div class="flex rounded-lg shadow-sm">
                                <div class="relative flex-grow focus-within:z-10">
                                    <input :type="showPw ? 'text' : 'password'" name="password" x-model="password" required class="block w-full rounded-none rounded-l-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400"><svg x-show="!showPw" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg><svg x-show="showPw" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg></button>
                                </div>
                                <button type="button" @click="generarClave()" class="relative -ml-px inline-flex items-center space-x-2 rounded-r-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:ring-1 focus:ring-blue-500"><svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg><span>Generar</span></button>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t border-gray-100">
                            <button type="button" @click="modalReset = false" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none">Cancelar</button>
                            <button type="submit" class="rounded-lg border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none">Guardar Contraseña</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <button x-show="showTopBtn" @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 z-50 p-3.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full shadow-lg transition-all transform hover:scale-110"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg></button>
    </div> 

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alerta-desactivar').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "El estado de este usuario será modificado.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#eab308',
                        cancelButtonColor: '#ef4444',
                        confirmButtonText: 'Sí, modificar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>