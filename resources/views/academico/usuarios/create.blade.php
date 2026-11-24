{{-- resources/views/academico/usuarios/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('academico.usuarios.index') }}" class="text-neutral-400 hover:text-[#d49b1a] transition-colors cursor-pointer" title="Volver al directorio">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Registrar Nuevo Personal</h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('academico.usuarios.store') }}" method="POST" x-data="{
                selectedRoles: {{ json_encode(old('roles', [])) }},
                get isDocente() { return this.selectedRoles.some(r => String(r).toLowerCase().includes('docente') || String(r).toLowerCase().includes('coordinador')); },
                get isCoordinador() { return this.selectedRoles.some(r => ['coordinador', 'Coordinador'].includes(String(r))); }
            }" class="space-y-6 text-sm">
                @csrf

                <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-neutral-800">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white border-b border-stone-100 dark:border-neutral-800 pb-3 mb-5">Credenciales de Acceso</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase mb-1.5">Nombre Completo <span class="text-rose-500">*</span></label>
                            <input type="text" id="name" name="nombre_completo" value="{{ old('nombre_completo') }}" required autofocus class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm">
                            @error('nombre_completo') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase mb-1.5">Correo Institucional <span class="text-rose-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm">
                            @error('email') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div x-data="{ password: '{{ old('password', 'colegio2026') }}', show: false }" class="md:col-span-2">
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase mb-1.5">Contraseña Temporal <span class="text-rose-500">*</span></label>
                            <div class="relative md:w-1/2 flex items-center">
                                <input :type="show ? 'text' : 'password'" name="password" x-model="password" required class="block w-full pr-10 rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm">
                                <button type="button" @click="show = !show" class="absolute right-3 text-xs font-semibold text-neutral-500 hover:text-neutral-800 dark:hover:text-white cursor-pointer" x-text="show ? 'Ocultar' : 'Ver'"></button>
                            </div>
                            @error('password') <span class="text-rose-600 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-neutral-800">
                    <label class="block text-base font-bold text-neutral-900 dark:text-white border-b border-stone-100 dark:border-neutral-800 pb-3 mb-5">Roles y Funciones del Sistema <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($roles as$rol)
                            @php $valorRol = $rol->nombre ?? $rol->name; @endphp
                            <label class="inline-flex items-center cursor-pointer p-3 rounded-xl border transition-colors" :class="selectedRoles.includes(@js($valorRol)) ? 'bg-[#FFFDF8] dark:bg-neutral-800 border-[#e6ac27]' : 'bg-white dark:bg-neutral-900 border-stone-200 dark:border-neutral-800'">
                                <input type="checkbox" name="roles[]" value="{{ $valorRol }}" x-model="selectedRoles" class="rounded border-stone-300 text-[#e6ac27] focus:ring-[#e6ac27] w-4 h-4 cursor-pointer">
                                <span class="ml-2.5 text-xs font-semibold text-neutral-800 dark:text-neutral-100">{{ $valorRol }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles') <span class="text-rose-600 text-xs mt-2 block">{{ $message }}</span> @enderror
                </div>

                <div x-show="isDocente" x-cloak class="bg-[#FFFDF8] dark:bg-neutral-900 rounded-3xl p-6 sm:p-8 shadow-xs border border-[#e6ac27]/30 dark:border-neutral-800">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white border-b border-stone-200/70 dark:border-neutral-800 pb-3 mb-5">Perfil Académico del Personal</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase mb-1.5">CUP</label>
                            <input type="text" name="codigo_unico_persona" value="{{ old('codigo_unico_persona') }}" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase mb-1.5">Sexo</label>
                            <select name="sexo" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm cursor-pointer">
                                <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase mb-1.5">Modalidad Base</label>
                            <select name="modalidad_id" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm cursor-pointer">
                                <option value="">Asignación General...</option>
                                @foreach ($modalidades ?? [] as $mod)
                                    <option value="{{ $mod->id }}" {{ old('modalidad_id') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div x-show="isCoordinador" x-cloak>
                            <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase mb-1.5">Modalidad que Coordina <span class="text-rose-500">*</span></label>
                            <select name="modalidad_coordina_id" :required="isCoordinador" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-sm cursor-pointer">
                                <option value="">Seleccione nivel...</option>
                                @foreach ($modalidades ?? [] as $mod)
                                    <option value="{{ $mod->id }}" {{ old('modalidad_coordina_id') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-2">
                    <a href="{{ route('academico.usuarios.index') }}" class="text-xs font-semibold text-neutral-500 uppercase cursor-pointer">Cancelar</a>
                    <button type="submit" class="rounded-xl bg-[#e6ac27] hover:bg-[#cf9719] py-2.5 px-7 text-xs font-bold uppercase text-neutral-950 transition-colors cursor-pointer">
                        Registrar Personal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>