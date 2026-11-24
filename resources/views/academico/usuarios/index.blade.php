{{-- resources/views/academico/usuarios/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer" title="Volver al Panel">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Gestión de Personal y Docentes</h2>
            </div>

            <a href="{{ route('academico.usuarios.create') }}" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 px-5 rounded-xl transition-colors text-xs uppercase tracking-wider cursor-pointer">
                + Registrar Personal
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{
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
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="p-6 border-b border-stone-100 dark:border-neutral-800">
                    <form method="GET" action="{{ route('academico.usuarios.index') }}" class="flex flex-col sm:flex-row gap-3 text-sm">
                        <select name="modalidad" onchange="this.form.submit()" class="rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 text-sm w-full sm:w-56 cursor-pointer">
                            <option value="">Todo el personal</option>
                            @foreach ($modalidades as $mod)
                                <option value="{{ $mod->id }}" {{ request('modalidad') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por nombre o correo..." class="flex-1 rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm">
                        <button type="submit" class="px-5 py-2.5 bg-neutral-900 dark:bg-[#e6ac27] text-white dark:text-neutral-950 rounded-xl text-xs font-bold uppercase cursor-pointer">Buscar</button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-left border-collapse text-sm">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/70 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-3.5">Nombre Completo</th>
                                <th class="px-6 py-3.5">Correo</th>
                                <th class="px-6 py-3.5">Roles</th>
                                <th class="px-6 py-3.5 text-center">Acceso</th>
                                <th class="px-6 py-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($usuarios as $usuario)
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-neutral-900 dark:text-white">{{ $usuario->nombre_completo ?? $usuario->name }}</p>
                                        @if ($usuario->docente)
                                            <span class="text-xs text-neutral-400">CUP: {{ $usuario->docente->codigo_unico_persona }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-neutral-600 dark:text-neutral-300">{{ $usuario->email }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1.5">
                                            @forelse ($usuario->roles as $rol)
                                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-lg bg-amber-50 dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30">{{ $rol->name }}</span>
                                            @empty
                                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-lg bg-stone-100 dark:bg-neutral-800 text-neutral-500">Sin Rol</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ ($usuario->activo ?? true) ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300' }}">
                                            {{ ($usuario->activo ?? true) ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap text-xs font-semibold">
                                        <a href="{{ route('academico.usuarios.edit', $usuario) }}" class="text-amber-600 dark:text-amber-400 hover:underline cursor-pointer">Editar</a>
                                        <button type="button" @click="abrirModal({{ $usuario->id }}, @js($usuario->nombre_completo ?? $usuario->name))" class="text-sky-600 dark:text-sky-400 hover:underline cursor-pointer">Clave</button>
                                        <form action="{{ route('academico.usuarios.destroy', $usuario) }}" method="POST" class="inline form-eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 dark:text-rose-400 hover:underline cursor-pointer">{{ ($usuario->activo ?? true) ? 'Bloquear' : 'Activar' }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-neutral-500">No se encontraron usuarios.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-5 border-t border-stone-100 dark:border-neutral-800 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs">
                    <span class="font-semibold text-neutral-500">Total: {{ $usuarios->total() }} registros</span>
                    <div>{{ $usuarios->appends(request()->query())->links() ?? '' }}</div>
                </div>
            </div>
        </div>

        <div x-show="modalReset" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl w-full max-w-md overflow-hidden border border-stone-200 dark:border-neutral-800 p-6">
                <form method="POST" :action="`{{ url('academico/usuarios') }}/${usuarioId}/reset-password`" class="space-y-4 text-sm">
                    @csrf
                    @method('PUT')
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Restablecer Contraseña</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">Nueva clave para <strong x-text="usuarioNombre"></strong>.</p>
                    <div class="flex gap-2">
                        <input :type="showPw ? 'text' : 'password'" name="password" x-model="password" required class="flex-1 rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-sm font-mono">
                        <button type="button" @click="showPw = !showPw" class="px-3 py-2 rounded-xl bg-stone-100 dark:bg-neutral-800 text-xs font-semibold cursor-pointer" x-text="showPw ? 'Ocultar' : 'Ver'"></button>
                        <button type="button" @click="generarClave()" class="px-3 py-2 rounded-xl bg-amber-50 dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] text-xs font-bold cursor-pointer">Generar</button>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="modalReset = false" class="px-4 py-2 text-xs font-semibold text-neutral-500 cursor-pointer">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-[#e6ac27] text-neutral-950 rounded-xl text-xs font-bold uppercase cursor-pointer">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>