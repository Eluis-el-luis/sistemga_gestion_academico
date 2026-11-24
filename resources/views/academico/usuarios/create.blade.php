<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.usuarios.index') }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al directorio">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                        Registrar Nuevo Personal
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                        Crea cuentas y asigna roles académicos o administrativos
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('academico.usuarios.store') }}" method="POST"
                  x-data="{
                      selectedRoles: {{ json_encode(old('roles', [])) }},
                      normalizar(r) {
                          return String(r || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
                      },
                      get requierePerfilAcademico() {
                          return this.selectedRoles.some((r) => {
                              const n = this.normalizar(r);
                              return n.includes('docente') || n.includes('coordinador') || n.includes('psicolog');
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

                <!-- TARJETA 1: CREDENCIALES DE ACCESO -->
                <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl p-6 sm:p-8 border border-stone-200 dark:border-neutral-800">
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white border-b border-stone-100 dark:border-neutral-800 pb-3 mb-5">
                        Credenciales de Acceso
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="nombre_completo" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Nombre Completo <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo') }}" required autofocus
                                   class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm transition-colors"
                                   placeholder="Ej. Juan Pérez">
                            @error('nombre_completo')
                                <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Correo Institucional <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                   class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm transition-colors"
                                   placeholder="usuario@colegio.edu.ni">
                            @error('email')
                                <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div x-data="{ password: '{{ old('password', 'colegio2026') }}', show: false }" class="md:col-span-2">
                            <label for="password" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Contraseña Temporal <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative md:w-1/2 flex items-center">
                                <input id="password" :type="show ? 'text' : 'password'" name="password" x-model="password" required minlength="8"
                                       class="block w-full pr-14 rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm transition-colors">
                                <button type="button" @click="show = !show"
                                        class="absolute right-3 text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-white cursor-pointer"
                                        x-text="show ? 'Ocultar' : 'Ver'"></button>
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1.5">
                                Mínimo 8 caracteres. Se recomienda que el usuario la actualice en su primer inicio de sesión.
                            </p>
                            @error('password')
                                <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span>
                            @enderror
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

                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-4">
                        Puedes asignar más de un rol cuando corresponda (por ejemplo, Coordinador y Docente Guía).
                    </p>
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
                            Completa los datos de identificación y nivel según los roles marcados.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="codigo_unico_persona" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                CUP (Código Único de Persona)
                            </label>
                            <input type="text" id="codigo_unico_persona" name="codigo_unico_persona" value="{{ old('codigo_unico_persona') }}"
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
                            <select id="sexo" name="sexo" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm cursor-pointer">
                                <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                                <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </div>

                        <div>
                            <label for="modalidad_id" class="block text-xs font-semibold text-neutral-600 dark:text-neutral-300 uppercase tracking-wider mb-1.5">
                                Modalidad Base
                            </label>
                            <select id="modalidad_id" name="modalidad_id" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm cursor-pointer">
                                <option value="">Asignación General...</option>
                                @foreach ($modalidades ?? [] as $mod)
                                    <option value="{{ $mod->id }}" {{ old('modalidad_id') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
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
                                    <option value="{{ $mod->id }}" {{ old('modalidad_coordina_id') == $mod->id ? 'selected' : '' }}>{{ $mod->nombre }}</option>
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
                        Registrar Personal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>