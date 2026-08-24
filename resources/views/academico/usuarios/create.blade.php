<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.usuarios.index') }}" class="text-gray-500 hover:text-blue-600 transition-colors" title="Volver a la lista">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                {{ __('Crear Nuevo Usuario') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100">
                
                <form action="{{ route('academico.usuarios.store') }}" method="POST" 
                    x-data="{ 
                        selectedRoles: {{ json_encode(old('roles', [])) }},
                        get isDocente() { return this.selectedRoles.some(r => r.includes('Docente') || r === 'Coordinador'); },
                        get isCoordinador() { return this.selectedRoles.includes('Coordinador'); }
                    }" 
                    class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre Completo -->
                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="nombre_completo" value="{{ old('nombre_completo') }}" required autofocus class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors" placeholder="Ej. Juan Pérez">
                            @error('nombre_completo') <span class="text-red-500 text-xs font-medium mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Correo Electrónico -->
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors" placeholder="usuario@colegio.edu.ni">
                            @error('email') <span class="text-red-500 text-xs font-medium mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div x-data="{ 
                            password: '{{ old('password', 'colegio2026') }}', 
                            show: false,
                            generarClave() {
                                const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!?#$%';
                                let result = '';
                                for (let i = 0; i < 10; i++) result += chars.charAt(Math.floor(Math.random() * chars.length));
                                this.password = result;
                            }
                        }">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Contraseña Temporal <span class="text-red-500">*</span></label>
                        <div class="mt-1 flex rounded-lg shadow-sm max-w-md">
                            <div class="relative flex-grow focus-within:z-10">
                                <input :type="show ? 'text' : 'password'" name="password" x-model="password" required class="block w-full rounded-none rounded-l-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    <svg x-show="show" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                </button>
                            </div>
                            <button type="button" @click="generarClave()" class="relative -ml-px inline-flex items-center space-x-2 rounded-r-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:ring-1 focus:ring-blue-500 transition-colors">
                                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                <span>Generar</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Por seguridad, el usuario deberá cambiarla al iniciar sesión por primera vez.</p>
                        @error('password') <span class="text-red-500 text-xs font-medium mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Asignar Roles Múltiples -->
                    <div class="pt-4">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Roles y Accesos del Usuario <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 bg-blue-50/50 p-5 rounded-lg border border-blue-100">
                            @foreach($roles as $rol)
                                <label class="inline-flex items-center cursor-pointer group transition-colors p-2.5 rounded shadow-sm border border-gray-200 hover:border-blue-400"
                                    :class="selectedRoles.includes('{{ $rol->nombre ?? $rol->name }}') ? 'bg-blue-50 border-blue-200' : 'bg-white'">
                                    <input type="checkbox" name="roles[]" value="{{ $rol->nombre ?? $rol->name }}" x-model="selectedRoles" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 w-4 h-4">
                                    <span class="ml-2 text-sm font-medium transition-colors" :class="selectedRoles.includes('{{ $rol->nombre ?? $rol->name }}') ? 'text-blue-800 font-bold' : 'text-gray-700 group-hover:text-blue-700'">{{ $rol->nombre ?? $rol->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Puedes seleccionar más de un rol (Ej: Coordinador + Docente Guía).</p>
                        @error('roles') <span class="text-red-500 text-xs font-medium mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- TARJETA 3: DATOS DOCENTES (Oculta por defecto, aparece con Alpine) -->
                    <div x-show="isDocente" x-transition.duration.300ms class="bg-amber-50 rounded-2xl p-6 shadow-sm border border-amber-100 mt-6" style="display: none;">
                        <h3 class="text-md font-bold text-amber-900 border-b border-amber-200/50 pb-2 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"></path></svg>
                            Perfil Académico del Personal
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-amber-800 mb-1">CUP (Código Único Persona)</label>
                                <input type="text" name="codigo_unico_persona" value="{{ old('codigo_unico_persona') }}" class="w-full rounded-lg border-amber-200 shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm bg-white" placeholder="Ej: DOC-2026-001">
                                @error('codigo_unico_persona') <span class="text-red-500 text-xs font-medium mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-amber-800 mb-1">Sexo</label>
                                <select name="sexo" class="w-full rounded-lg border-amber-200 shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm bg-white">
                                    <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                                </select>
                            </div>
                            
                            <div x-show="isCoordinador" x-transition class="md:col-span-2 mt-2" style="display: none;">
                                <label class="block text-sm font-bold text-amber-900 mb-1">Modalidad que Coordina <span class="text-red-500">*</span></label>
                                <select name="modalidad_coordina_id" :required="isCoordinador" class="w-full md:w-1/2 rounded-lg border-amber-300 shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm bg-white">
                                    <option value="">Seleccione el Nivel Académico...</option>
                                    @foreach($modalidades ?? [] as $mod)
                                        <option value="{{ $mod->id }}" {{ old('modalidad_coordina_id') == $mod->id ? 'selected' : '' }}>
                                            {{ $mod->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 flex items-center justify-end space-x-4 border-t border-gray-100 mt-8">
                        <a href="{{ route('academico.usuarios.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Cancelar</a>
                        <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent bg-blue-600 py-2 px-6 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                            Registrar Personal
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>