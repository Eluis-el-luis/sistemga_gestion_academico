<div class="mb-8 bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 shadow-xs rounded-3xl overflow-hidden transition-colors">
    <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 border-b border-stone-200/80 dark:border-neutral-800 px-6 sm:px-8 py-4">
        <h3 class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-[#d49b1a] dark:text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            I. Datos Personales del Estudiante
        </h3>
    </div>

    <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
        <div>
            <label for="codigo_unico_persona" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-1.5">Código Único (CUP) <span class="text-rose-500">*</span></label>
            <input id="codigo_unico_persona" type="text" name="codigo_unico_persona" value="{{ old('codigo_unico_persona', $alumno->codigo_unico_persona ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors" required autofocus>
            @error('codigo_unico_persona') <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="nombre_completo" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-1.5">Nombre Completo <span class="text-rose-500">*</span></label>
            <input id="nombre_completo" type="text" name="nombre_completo" value="{{ old('nombre_completo', $alumno->nombre_completo ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors" required>
            @error('nombre_completo') <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="sexo" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-1.5">Sexo <span class="text-rose-500">*</span></label>
            <select id="sexo" name="sexo" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors cursor-pointer" required>
                <option value="">Seleccione...</option>
                <option value="M" {{ old('sexo', $alumno->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
                <option value="F" {{ old('sexo', $alumno->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
            </select>
            @error('sexo') <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="fecha_nacimiento" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-1.5">Fecha de Nacimiento <span class="text-rose-500">*</span></label>
            <input id="fecha_nacimiento" type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors" required>
            @error('fecha_nacimiento') <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="md:col-span-2">
            <label for="direccion_domiciliar" class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-1.5">Dirección Domiciliar <span class="text-rose-500">*</span></label>
            <input id="direccion_domiciliar" type="text" name="direccion_domiciliar" value="{{ old('direccion_domiciliar', $alumno->direccion_domiciliar ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors" required>
            @error('direccion_domiciliar') <span class="text-rose-600 dark:text-rose-400 text-xs font-medium mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="md:col-span-2 border-t border-stone-100 dark:border-neutral-800 pt-6 mt-2" x-data="buscadorHermanos(@js(old('hermanos_en_colegio', $alumno->hermanos_en_colegio ?? '')))">
            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-2">Hermanos en el colegio (Buscador Inteligente)</label>

            <div class="relative">
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" x-model="query" @input.debounce.400ms="buscar" placeholder="Escriba el CUP o Nombre del hermano..." class="block w-full pl-11 pr-4 py-2.5 rounded-xl border-stone-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors">
                </div>

                <ul x-show="resultados.length > 0" x-cloak @click.outside="resultados = []" class="absolute z-20 mt-2 w-full bg-white dark:bg-neutral-900 shadow-xl max-h-60 rounded-xl py-2 text-sm border border-stone-200 dark:border-neutral-800 overflow-auto">
                    <template x-for="resultado in resultados" :key="resultado.id">
                        <li @click="agregarHermano(resultado)" class="text-neutral-700 dark:text-neutral-200 cursor-pointer select-none relative py-2.5 px-4 hover:bg-amber-50 dark:hover:bg-neutral-800 transition-colors border-b border-stone-100 dark:border-neutral-800 last:border-0">
                            <div class="flex flex-col">
                                <span class="font-semibold block truncate" x-text="resultado.nombre_completo"></span>
                                <span class="text-neutral-500 dark:text-neutral-400 text-xs block truncate mt-0.5" x-text="(resultado.codigo_unico_persona || '') + ' - ' + (resultado.grado_seccion || '')"></span>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <template x-for="(hermano, index) in seleccionados" :key="index">
                    <span class="inline-flex items-center py-1.5 pl-3 pr-2 rounded-lg text-xs font-semibold bg-stone-100 dark:bg-neutral-800 text-neutral-800 dark:text-neutral-200 border border-stone-200 dark:border-neutral-700">
                        <span x-text="hermano"></span>
                        <button type="button" @click="removerHermano(index)" class="ml-2 h-5 w-5 rounded-md inline-flex items-center justify-center text-neutral-400 hover:bg-rose-100 dark:hover:bg-rose-950/50 hover:text-rose-600 transition-colors cursor-pointer" aria-label="Quitar hermano">
                            <svg class="h-3 w-3" stroke="currentColor" fill="none" viewBox="0 0 8 8" aria-hidden="true"><path stroke-linecap="round" stroke-width="2" d="M1 1l6 6m0-6L1 7" /></svg>
                        </button>
                    </span>
                </template>
            </div>

            <input type="hidden" name="hermanos_en_colegio" x-model="textoFinal">
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-2.5">Busca y selecciona al hermano para vincular su referencia.</p>
        </div>
    </div>
</div>

<div class="mb-8 bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 shadow-xs rounded-3xl overflow-hidden transition-colors">
    <div class="bg-stone-50 dark:bg-neutral-800/70 border-b border-stone-200/80 dark:border-neutral-800 px-6 sm:px-8 py-4">
        <h3 class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            II. Datos Familiares
        </h3>
    </div>

    <div class="p-6 sm:p-8 space-y-8">
        <div x-data="{ asisteIglesia: {{ old('madre_asiste_iglesia', $alumno->madre_asiste_iglesia ?? false) ? 'true' : 'false' }} }">
            <h4 class="font-semibold text-neutral-800 dark:text-neutral-200 border-b border-stone-100 dark:border-neutral-800 pb-2.5 mb-4 uppercase tracking-wider text-xs">Datos de la Madre</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Nombre Completo</label>
                    <input type="text" name="madre_nombre_completo" value="{{ old('madre_nombre_completo', $alumno->madre_nombre_completo ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Cédula</label>
                    <input type="text" name="madre_cedula" value="{{ old('madre_cedula', $alumno->madre_cedula ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Teléfono</label>
                    <input type="text" name="madre_telefono" value="{{ old('madre_telefono', $alumno->madre_telefono ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Ocupación</label>
                    <input type="text" name="madre_ocupacion" value="{{ old('madre_ocupacion', $alumno->madre_ocupacion ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div class="flex items-center pt-5">
                    <input type="hidden" name="madre_asiste_iglesia" value="0">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="madre_asiste_iglesia" value="1" x-model="asisteIglesia" class="rounded border-stone-300 dark:border-neutral-700 text-[#e6ac27] focus:ring-[#e6ac27] h-4 w-4 cursor-pointer">
                        <span class="ml-2.5 text-sm font-medium text-neutral-700 dark:text-neutral-200">¿Asiste a Iglesia?</span>
                    </label>
                </div>
                <div x-show="asisteIglesia" x-cloak x-transition>
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">¿A cuál Iglesia?</label>
                    <input type="text" name="madre_nombre_iglesia" value="{{ old('madre_nombre_iglesia', $alumno->madre_nombre_iglesia ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
            </div>
        </div>

        <div x-data="{ asisteIglesia: {{ old('padre_asiste_iglesia', $alumno->padre_asiste_iglesia ?? false) ? 'true' : 'false' }} }">
            <h4 class="font-semibold text-neutral-800 dark:text-neutral-200 border-b border-stone-100 dark:border-neutral-800 pb-2.5 mb-4 uppercase tracking-wider text-xs">Datos del Padre</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Nombre Completo</label>
                    <input type="text" name="padre_nombre_completo" value="{{ old('padre_nombre_completo', $alumno->padre_nombre_completo ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Cédula</label>
                    <input type="text" name="padre_cedula" value="{{ old('padre_cedula', $alumno->padre_cedula ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Teléfono</label>
                    <input type="text" name="padre_telefono" value="{{ old('padre_telefono', $alumno->padre_telefono ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Ocupación</label>
                    <input type="text" name="padre_ocupacion" value="{{ old('padre_ocupacion', $alumno->padre_ocupacion ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div class="flex items-center pt-5">
                    <input type="hidden" name="padre_asiste_iglesia" value="0">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="padre_asiste_iglesia" value="1" x-model="asisteIglesia" class="rounded border-stone-300 dark:border-neutral-700 text-[#e6ac27] focus:ring-[#e6ac27] h-4 w-4 cursor-pointer">
                        <span class="ml-2.5 text-sm font-medium text-neutral-700 dark:text-neutral-200">¿Asiste a Iglesia?</span>
                    </label>
                </div>
                <div x-show="asisteIglesia" x-cloak x-transition>
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">¿A cuál Iglesia?</label>
                    <input type="text" name="padre_nombre_iglesia" value="{{ old('padre_nombre_iglesia', $alumno->padre_nombre_iglesia ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
            </div>
        </div>

        <div>
            <h4 class="font-semibold text-neutral-800 dark:text-neutral-200 border-b border-stone-100 dark:border-neutral-800 pb-2.5 mb-4 uppercase tracking-wider text-xs">Datos del Tutor / Encargado Legal</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Nombre Completo</label>
                    <input type="text" name="tutor_nombre_completo" value="{{ old('tutor_nombre_completo', $alumno->tutor_nombre_completo ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Cédula</label>
                    <input type="text" name="tutor_cedula" value="{{ old('tutor_cedula', $alumno->tutor_cedula ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Teléfono</label>
                    <input type="text" name="tutor_telefono" value="{{ old('tutor_telefono', $alumno->tutor_telefono ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Ocupación</label>
                    <input type="text" name="tutor_ocupacion" value="{{ old('tutor_ocupacion', $alumno->tutor_ocupacion ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-8 bg-white dark:bg-neutral-900 border border-rose-200/70 dark:border-neutral-800 shadow-xs rounded-3xl overflow-hidden transition-colors">
    <div class="bg-rose-50/60 dark:bg-neutral-800/70 border-b border-rose-100 dark:border-neutral-800 px-6 sm:px-8 py-4">
        <h3 class="text-base sm:text-lg font-bold text-rose-900 dark:text-rose-300 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            III. Información Médica y Retiro
        </h3>
    </div>

    <div class="p-6 sm:p-8">
        <div class="mb-6">
            <label class="block text-sm font-semibold text-neutral-700 dark:text-neutral-200 mb-2">Enfermedades Crónicas <span class="font-normal text-neutral-500 dark:text-neutral-400 text-xs ml-1">(Presentar epicrisis si aplica)</span></label>
            <input type="text" name="enfermedades_cronicas" value="{{ old('enfermedades_cronicas', $alumno->enfermedades_cronicas ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm" placeholder="Ej. Asma, Alergias, Ninguna...">
        </div>

        <h4 class="font-semibold text-neutral-800 dark:text-neutral-200 border-b border-stone-100 dark:border-neutral-800 pb-2.5 mb-4 uppercase tracking-wider text-xs">Persona autorizada para retirar al alumno</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Nombre Completo</label>
                <input type="text" name="autorizado_retirar_nombre" value="{{ old('autorizado_retirar_nombre', $alumno->autorizado_retirar_nombre ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Cédula</label>
                <input type="text" name="autorizado_retirar_cedula" value="{{ old('autorizado_retirar_cedula', $alumno->autorizado_retirar_cedula ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Teléfono</label>
                <input type="text" name="autorizado_retirar_telefono" value="{{ old('autorizado_retirar_telefono', $alumno->autorizado_retirar_telefono ?? '') }}" class="block w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50/70 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:border-[#e6ac27] focus:ring-[#e6ac27] text-sm">
            </div>
        </div>
    </div>
</div>

<div class="mb-8 p-6 bg-[#FFFDF8] dark:bg-neutral-900 border border-[#e6ac27]/30 dark:border-neutral-800 rounded-3xl shadow-xs">
    <label class="flex items-start gap-3.5 cursor-pointer">
        <input type="hidden" name="acepta_compromiso_cristiano" value="0">
        <input type="checkbox" name="acepta_compromiso_cristiano" value="1" {{ old('acepta_compromiso_cristiano', $alumno->acepta_compromiso_cristiano ?? false) ? 'checked' : '' }} class="mt-1 rounded border-stone-300 dark:border-neutral-700 text-[#e6ac27] focus:ring-[#e6ac27] h-4 w-4 cursor-pointer">
        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-200 leading-relaxed">
            Acepto el compromiso de participar en las actividades cristianas, formativas y comunitarias que el centro educativo promueva.
        </span>
    </label>
</div>

<div class="flex items-center justify-end gap-5 mt-8 pt-6 border-t border-stone-200 dark:border-neutral-800">
    <a href="{{ url()->previous() }}" class="text-sm font-semibold text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors cursor-pointer">
        Cancelar
    </a>
    <button type="submit" class="px-7 py-3 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-xl font-bold text-sm shadow-xs transition-colors focus:outline-none focus:ring-2 focus:ring-[#e6ac27] cursor-pointer">
        {{ $btnText ?? 'Guardar' }}
    </button>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('buscadorHermanos', (valorInicial) => ({
            query: '',
            resultados: [],
            seleccionados: valorInicial ? String(valorInicial).split(',').map(s => s.trim()).filter(Boolean) : [],
            get textoFinal() {
                return this.seleccionados.join(', ');
            },
            async buscar() {
                if (!this.query || this.query.trim().length < 2) {
                    this.resultados = [];
                    return;
                }
                try {
                    const response = await fetch(`/api/alumnos/buscar?q=${encodeURIComponent(this.query.trim())}`);
                    if (response.ok) {
                        this.resultados = await response.json();
                    }
                } catch (e) {
                    this.resultados = [];
                }
            },
            agregarHermano(hermano) {
                const formato = `${hermano.nombre_completo}${hermano.grado_seccion ? ' (' + hermano.grado_seccion + ')' : ''}`;
                if (!this.seleccionados.includes(formato)) {
                    this.seleccionados.push(formato);
                }
                this.query = '';
                this.resultados = [];
            },
            removerHermano(index) {
                this.seleccionados.splice(index, 1);
            }
        }));
    });
</script>