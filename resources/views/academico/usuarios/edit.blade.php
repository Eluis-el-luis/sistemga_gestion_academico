<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.usuarios.index') }}" class="text-slate-400 hover:text-blue-600 transition-colors" title="Volver al directorio">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-slate-800 leading-tight">
                {{ __('Editar Perfil:') }} <span class="text-blue-600">{{ $usuario->nombre_completo ?? $usuario->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <form action="{{ route('academico.usuarios.update', $usuario) }}" method="POST" 
                  x-data="{ 
                      selectedRoles: {{ json_encode(old('roles', $usuario->roles->pluck('name')->toArray())) }},
                      get isDocente() { return this.selectedRoles.some(r => r.includes('Docente') || r === 'Coordinador'); },
                      get isCoordinador() { return this.selectedRoles.includes('Coordinador'); }
                  }" 
                  class="space-y-6">
                @csrf
                @method('PUT')

                <!-- TARJETA 1: DATOS BÁSICOS -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-6">Datos de Autenticación</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $usuario->nombre_completo ?? $usuario->name) }}" required class="w-full rounded-xl border-slate-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-slate-50/50 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Correo Electrónico <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required class="w-full rounded-xl border-slate-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-slate-50/50 transition-colors">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Estado de Acceso <span class="text-red-500">*</span></label>
                            <select name="activo" required class="w-full md:w-1/2 rounded-xl border-slate-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-slate-50/50">
                                <option value="1" {{ old('activo', $usuario->activo) == 1 ? 'selected' : '' }}>🟢 Cuenta Activa (Permitido)</option>
                                <option value="0" {{ old('activo', $usuario->activo) == 0 ? 'selected' : '' }}>🔴 Cuenta Inactiva (Bloqueado)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- TARJETA 2: ROLES DE SPATIE -->
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-6">Roles y Permisos del Sistema</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($roles as $rol)
                            <label class="flex items-center p-3 rounded-xl border cursor-pointer transition-all duration-200"
                                   :class="selectedRoles.includes('{{ $rol->nombre ?? $rol->name }}') ? 'bg-amber-50 border-amber-200 shadow-sm' : 'bg-white border-slate-200 hover:border-amber-300 hover:bg-slate-50'">
                                <input type="checkbox" name="roles[]" value="{{ $rol->nombre ?? $rol->name }}" x-model="selectedRoles" class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4 border-slate-300">
                                <span class="ml-3 text-sm font-bold"
                                      :class="selectedRoles.includes('{{ $rol->nombre ?? $rol->name }}') ? 'text-amber-800' : 'text-slate-600'">
                                    {{ $rol->nombre ?? $rol->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles') <span class="text-red-500 text-xs font-bold mt-2 block">{{ $message }}</span> @enderror
                </div>

                <!-- TARJETA 3: DATOS DOCENTES (Se muestra dinámicamente con Alpine) -->
                <div x-show="isDocente" x-transition.duration.300ms class="bg-blue-50 rounded-3xl p-8 shadow-sm border border-blue-100" style="display: none;">
                    <h3 class="text-lg font-bold text-blue-900 border-b border-blue-200/50 pb-3 mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"></path></svg>
                        Perfil Académico del Personal
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-blue-800 mb-1">CUP (Código Único Persona)</label>
                            <input type="text" name="codigo_unico_persona" value="{{ old('codigo_unico_persona', $usuario->docente->codigo_unico_persona ?? '') }}" class="w-full rounded-xl border-blue-200 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white" placeholder="Ej: DOC-0001">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-blue-800 mb-1">Sexo</label>
                            <select name="sexo" class="w-full rounded-xl border-blue-200 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                                <option value="M" {{ old('sexo', $usuario->docente->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo', $usuario->docente->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>
                        
                        <!-- Solo aparece si el rol incluye 'Coordinador' -->
                        <div x-show="isCoordinador" x-transition class="md:col-span-2 mt-2 p-4 bg-white/60 rounded-xl border border-blue-200/60" style="display: none;">
                            <label class="block text-sm font-bold text-blue-900 mb-1">Modalidad que Coordina <span class="text-red-500">*</span></label>
                            <select name="modalidad_coordina_id" :required="isCoordinador" class="w-full md:w-1/2 rounded-xl border-blue-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm bg-white">
                                <option value="">Seleccione el Nivel Académico...</option>
                                @foreach($modalidades ?? [] as $mod)
                                    <option value="{{ $mod->id }}" {{ old('modalidad_coordina_id', $usuario->docente->modalidad_coordina_id ?? '') == $mod->id ? 'selected' : '' }}>
                                        {{ $mod->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-4">
                    <a href="{{ route('academico.usuarios.index') }}" class="text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">Cancelar</a>
                    <button type="submit" class="inline-flex justify-center rounded-xl bg-blue-600 py-3 px-8 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-all transform hover:-translate-y-0.5">
                        Actualizar Perfil
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>