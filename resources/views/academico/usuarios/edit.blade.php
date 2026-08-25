<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.usuarios.index') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors" title="Volver al directorio">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight tracking-tight">
                {{ __('Editar Perfil:') }} <span class="text-[#e6ac27]">{{ $usuario->nombre_completo ?? $usuario->name }}</span>
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
                  class="space-y-8">
                @csrf
                @method('PUT')

                <!-- TARJETA 1: DATOS BÁSICOS -->
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl p-8 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-6">Datos de Autenticación</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $usuario->nombre_completo ?? $usuario->name) }}" required class="w-full rounded-xl border-slate-200 shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm bg-slate-50/50 transition-colors">
                            @error('nombre_completo') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Correo Institucional <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required class="w-full rounded-xl border-slate-200 shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm bg-slate-50/50 transition-colors">
                            @error('email') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-1">Estado de Acceso <span class="text-red-500">*</span></label>
                            <select name="activo" required class="w-full md:w-1/2 rounded-xl border-slate-200 shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm bg-slate-50/50 transition-colors">
                                <option value="1" {{ old('activo', $usuario->activo) == 1 ? 'selected' : '' }}>🟢 Cuenta Activa (Permitido)</option>
                                <option value="0" {{ old('activo', $usuario->activo) == 0 ? 'selected' : '' }}>🔴 Cuenta Inactiva (Bloqueado)</option>
                            </select>
                            <p class="text-xs text-slate-500 mt-2 font-medium">Bloquear la cuenta impedirá que el usuario ingrese al sistema.</p>
                        </div>
                    </div>
                </div>

                <!-- TARJETA 2: ROLES DE SPATIE -->
                <div class="bg-white overflow-hidden shadow-sm rounded-3xl p-8 border border-slate-200">
                    <label class="block text-lg font-bold text-slate-800 border-b border-slate-100 pb-3 mb-6">Roles y Funciones del Sistema <span class="text-red-500">*</span></label>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($roles as $rol)
                            <label class="inline-flex items-center cursor-pointer group transition-colors p-3.5 rounded-xl shadow-sm border hover:border-[#e6ac27]"
                                   :class="selectedRoles.includes('{{ $rol->nombre ?? $rol->name }}') ? 'bg-[#FFFDF5] border-[#e6ac27]' : 'bg-white border-slate-200'">
                                <input type="checkbox" name="roles[]" value="{{ $rol->nombre ?? $rol->name }}" x-model="selectedRoles" class="rounded border-slate-300 text-[#e6ac27] shadow-sm focus:ring-[#e6ac27] w-5 h-5">
                                <span class="ml-3 text-sm transition-colors"
                                      :class="selectedRoles.includes('{{ $rol->nombre ?? $rol->name }}') ? 'text-[#3d2c1d] font-black' : 'text-slate-600 font-bold group-hover:text-[#3d2c1d]'">
                                    {{ $rol->nombre ?? $rol->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles') <span class="text-red-500 text-xs font-bold mt-2 block">{{ $message }}</span> @enderror
                </div>

                <!-- TARJETA 3: DATOS DOCENTES (Se muestra dinámicamente con Alpine) -->
                <div x-show="isDocente" x-transition.duration.300ms class="bg-[#FFFDF5] rounded-3xl p-8 shadow-sm border border-[#e6ac27]/30" style="display: none;">
                    <h3 class="text-lg font-bold text-[#3d2c1d] border-b border-[#e6ac27]/20 pb-3 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"></path></svg>
                        Perfil Académico del Personal
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-[#3d2c1d] mb-1">CUP (Código Único Persona)</label>
                            <input type="text" name="codigo_unico_persona" value="{{ old('codigo_unico_persona', $usuario->docente->codigo_unico_persona ?? '') }}" class="w-full rounded-xl border-slate-300 shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm bg-white transition-colors" placeholder="Ej: DOC-2026-001">
                            @error('codigo_unico_persona') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#3d2c1d] mb-1">Sexo</label>
                            <select name="sexo" class="w-full rounded-xl border-slate-300 shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm bg-white transition-colors">
                                <option value="M" {{ old('sexo', $usuario->docente->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo', $usuario->docente->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>
                        
                        <!-- Solo aparece si el rol incluye 'Coordinador' -->
                        <div x-show="isCoordinador" x-transition class="md:col-span-2 mt-2" style="display: none;">
                            <label class="block text-sm font-bold text-[#3d2c1d] mb-1">Modalidad que Coordina <span class="text-red-500">*</span></label>
                            <select name="modalidad_coordina_id" :required="isCoordinador" class="w-full md:w-1/2 rounded-xl border-slate-300 shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] sm:text-sm bg-white transition-colors">
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
                    <button type="submit" class="inline-flex justify-center rounded-xl bg-[#e6ac27] py-3 px-8 text-sm font-black text-white shadow-sm hover:bg-[#c48e1b] transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:ring-offset-2">
                        Actualizar Perfil
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>