<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <a href="{{ route('academico.usuarios.index') }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al directorio">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                Editar Perfil: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $usuario->nombre_completo ?? $usuario->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('academico.usuarios.update', $usuario) }}" method="POST"
                  x-data="{
                      selectedRoles: {{ json_encode(old('roles', $usuario->roles->pluck('name')->toArray())) }},
                      tieneRegistroDocente: {{ $usuario->docente ? 'true' : 'false' }},
                      tieneModalidadBase: {{ !empty($usuario->docente?->modalidad_id) ? 'true' : 'false' }},
                      normalizar(r) {
                          return String(r || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
                      },
                      get requierePerfilAcademico() {
                          if (this.tieneRegistroDocente) return true;
                          return this.selectedRoles.some((r) => {
                              const n = this.normalizar(r);
                              return n.includes('docente')
                                  || n.includes('coordinador')
                                  || n.includes('director')
                                  || n.includes('subdirector')
                                  || n.includes('gestor')
                                  || n.includes('psicolog');
                          });
                      },
                      get esDocente() {
                          return this.selectedRoles.some((r) => this.normalizar(r).includes('docente'));
                      },
                      get esCoordinador() {
                          return this.selectedRoles.some((r) => this.normalizar(r).includes('coordinador'));
                      }
                  }"
                  class="space-y-6 text-sm">
                @csrf
                @method('PUT')

                <!-- TARJETA 1: DATOS DE AUTENTICACIÓN -->
                <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-neutral-800">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white border-b border-stone-100 dark:border-neutral-800 pb-3 mb-5">
                        Datos de Autenticación
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="nombre_completo" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Nombre Completo <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo', $usuario->nombre_completo ?? $usuario->name) }}" required
                                   class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm transition-colors">
                            @error('nombre_completo')
                                <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Correo Institucional <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" required
                                   class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm transition-colors">
                            @error('email')
                                <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="activo" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Estado de Acceso <span class="text-rose-500">*</span>
                            </label>
                            <select id="activo" name="activo" required
                                    class="w-full md:w-1/2 rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm cursor-pointer transition-colors">
                                <option value="1" {{ old('activo', $usuario->activo) == 1 ? 'selected' : '' }}>Activa (Permitido)</option>
                                <option value="0" {{ old('activo', $usuario->activo) == 0 ? 'selected' : '' }}>Inactiva (Bloqueado)</option>
                            </select>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1.5">
                                Bloquear la cuenta impedirá que el usuario inicie sesión.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- TARJETA 2: ROLES Y FUNCIONES -->
                <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-neutral-800">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 dark:border-neutral-800 pb-3 mb-5">
                        <label class="text-base font-bold text-neutral-900 dark:text-white">
                            Roles y Funciones del Sistema <span class="text-rose-500">*</span>
                        </label>
                        <span class="text-xs font-semibold text-[#b88212] dark:text-[#e6ac27]" x-text="selectedRoles.length + ' seleccionado(s)'"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($roles as $rol)
                            @php $valorRol = $rol->nombre ?? $rol->name; @endphp
                            <label class="inline-flex items-center justify-between cursor-pointer p-3.5 rounded-xl border transition-colors select-none"
                                   :class="selectedRoles.includes(@js($valorRol))
                                       ? 'bg-[#FFFDF8] dark:bg-neutral-800 border-[#e6ac27] text-neutral-900 dark:text-white ring-1 ring-[#e6ac27]/40'
                                       : 'bg-white dark:bg-neutral-900 border-stone-200 dark:border-neutral-800 text-neutral-700 dark:text-neutral-200 hover:border-stone-300 dark:hover:border-neutral-700'">
                                <span class="inline-flex items-center gap-2.5">
                                    <input type="checkbox" name="roles[]" value="{{ $valorRol }}" x-model="selectedRoles"
                                           class="rounded border-stone-300 dark:border-neutral-600 text-[#e6ac27] focus:ring-[#e6ac27] w-4 h-4 cursor-pointer">
                                    <span class="text-xs font-semibold">{{ $valorRol }}</span>
                                </span>
                                <span x-show="selectedRoles.includes(@js($valorRol))" x-cloak class="text-[10px] font-bold uppercase tracking-wider text-[#b88212] dark:text-[#e6ac27]">
                                    Activo
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('roles')
                        <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-2 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- TARJETA 3: PERFIL ACADÉMICO -->
                <div x-show="requierePerfilAcademico" x-cloak x-transition
                     class="bg-[#FFFDF8] dark:bg-neutral-900 rounded-3xl p-6 sm:p-8 shadow-xs border border-[#e6ac27]/35 dark:border-neutral-800">
                    <div class="border-b border-stone-200/70 dark:border-neutral-800 pb-3 mb-5">
                        <h3 class="text-base font-bold text-neutral-900 dark:text-white">
                            Perfil Académico del Personal
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                            Actualiza el Código Único de Persona (CUP), sexo y asignación correspondiente.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="codigo_unico_persona" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                CUP (Código Único de Persona)
                            </label>
                            <input type="text" id="codigo_unico_persona" name="codigo_unico_persona"
                                   value="{{ old('codigo_unico_persona', $usuario->docente->codigo_unico_persona ?? '') }}"
                                   class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm"
                                   placeholder="Ej. DOC-2026-001">
                            @error('codigo_unico_persona')
                                <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="sexo" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Sexo
                            </label>
                            <select id="sexo" name="sexo"
                                    class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm cursor-pointer">
                                <option value="M" {{ old('sexo', $usuario->docente->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo', $usuario->docente->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>

                        <div x-show="esDocente || tieneModalidadBase" x-cloak>
                            <label for="modalidad_id" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Modalidad Base
                            </label>
                            <select id="modalidad_id" name="modalidad_id"
                                    class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm cursor-pointer">
                                <option value="">Asignación General...</option>
                                @foreach ($modalidades ?? [] as $mod)
                                    <option value="{{ $mod->id }}" {{ old('modalidad_id', $usuario->docente?->modalidad_id) == $mod->id ? 'selected' : '' }}>
                                        {{ $mod->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('modalidad_id')
                                <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div x-show="esCoordinador" x-cloak>
                            <label for="modalidad_coordina_id" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Modalidad que Coordina <span class="text-rose-500">*</span>
                            </label>
                            <select id="modalidad_coordina_id" name="modalidad_coordina_id" :required="esCoordinador"
                                    class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm cursor-pointer">
                                <option value="">Seleccione nivel académico...</option>
                                @foreach ($modalidades ?? [] as $mod)
                                    <option value="{{ $mod->id }}" {{ old('modalidad_coordina_id', $usuario->docente->modalidad_coordina_id ?? '') == $mod->id ? 'selected' : '' }}>
                                        {{ $mod->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ACCIONES -->
                <div class="flex items-center justify-end gap-4 pt-2">
                    <a href="{{ route('academico.usuarios.index') }}" class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white uppercase tracking-wider transition-colors cursor-pointer">
                        Cancelar
                    </a>
                    <button type="submit" class="rounded-xl bg-[#e6ac27] hover:bg-[#cf9719] py-2.5 px-7 text-xs font-bold uppercase tracking-wider text-neutral-950 transition-colors cursor-pointer">
                        Actualizar Perfil
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>