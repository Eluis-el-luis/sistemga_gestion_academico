<!-- TARJETA 1: DATOS PERSONALES -->
<div class="mb-8 bg-white border border-slate-200 shadow-sm rounded-3xl overflow-hidden">
    <div class="bg-[#FFFDF5] border-b border-[#e6ac27]/20 px-8 py-5">
        <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
            <svg class="w-6 h-6 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            I. Datos Personales del Estudiante
        </h3>
    </div>
    
    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Código Único (CUP) <span class="text-red-500">*</span></label>
            <input type="text" name="codigo_unico_persona" value="{{ old('codigo_unico_persona', $alumno->codigo_unico_persona ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors" required autofocus>
            @error('codigo_unico_persona') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
            <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $alumno->nombre_completo ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors" required>
            @error('nombre_completo') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Sexo <span class="text-red-500">*</span></label>
            <select name="sexo" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors" required>
                <option value="">Seleccione...</option>
                <option value="M" {{ old('sexo', $alumno->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
                <option value="F" {{ old('sexo', $alumno->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
            </select>
            @error('sexo') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label class="block text-sm font-bold text-slate-700 mb-1">Fecha de Nacimiento <span class="text-red-500">*</span></label>
            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors" required>
            @error('fecha_nacimiento') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-slate-700 mb-1">Dirección Domiciliar <span class="text-red-500">*</span></label>
            <input type="text" name="direccion_domiciliar" value="{{ old('direccion_domiciliar', $alumno->direccion_domiciliar ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors" required>
            @error('direccion_domiciliar') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>
        
        <!-- 🌟 COMPONENTE BUSCADOR DE HERMANOS (Alpine.js) -->
        <div class="md:col-span-2 border-t border-slate-100 pt-6 mt-2" x-data="buscadorHermanos('{{ old('hermanos_en_colegio', $alumno->hermanos_en_colegio ?? '') }}')">
            <label class="block text-sm font-bold text-slate-700 mb-2">Hermanos en el colegio (Buscador Inteligente)</label>
            
            <div class="relative">
                <!-- Barra de búsqueda -->
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="query" @input.debounce.500ms="buscar" placeholder="Escriba el CUP o Nombre del hermano..." class="block w-full pl-11 pr-4 py-3 rounded-xl border-slate-200 bg-white shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] transition-colors">
                </div>

                <!-- Menú desplegable de resultados -->
                <ul x-show="resultados.length > 0" @click.away="resultados = []" class="absolute z-10 mt-2 w-full bg-white shadow-xl max-h-60 rounded-xl py-2 text-base border border-slate-100 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                    <template x-for="resultado in resultados" :key="resultado.id">
                        <li @click="agregarHermano(resultado)" class="text-slate-700 cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-[#FFFDF5] hover:text-[#e6ac27] transition-colors border-b border-slate-50 last:border-0">
                            <div class="flex flex-col">
                                <span class="font-black block truncate" x-text="resultado.nombre_completo"></span>
                                <span class="text-slate-500 font-medium text-xs block truncate mt-0.5" x-text="resultado.codigo_unico_persona + ' - ' + resultado.grado_seccion"></span>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Etiquetas visuales de los hermanos seleccionados -->
            <div class="mt-4 flex flex-wrap gap-2">
                <template x-for="(hermano, index) in seleccionados" :key="index">
                    <span class="inline-flex items-center py-1.5 pl-3 pr-2 rounded-lg text-sm font-bold bg-slate-100 text-slate-700 border border-slate-200">
                        <span x-text="hermano"></span>
                        <button type="button" @click="removerHermano(index)" class="flex-shrink-0 ml-2 h-5 w-5 rounded-md inline-flex items-center justify-center text-slate-400 hover:bg-rose-100 hover:text-rose-500 focus:outline-none transition-colors">
                            <svg class="h-3 w-3" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="2" d="M1 1l6 6m0-6L1 7" /></svg>
                        </button>
                    </span>
                </template>
            </div>

            <!-- Input Oculto que guarda el texto final -->
            <input type="hidden" name="hermanos_en_colegio" x-model="textoFinal">
            
            <p class="text-xs text-slate-500 font-medium mt-3">Busca y selecciona al hermano. El sistema vinculará su grado automáticamente.</p>
        </div>
    </div>
</div>

<!-- TARJETA 2: DATOS FAMILIARES -->
<div class="mb-8 bg-white border border-slate-200 shadow-sm rounded-3xl overflow-hidden">
    <div class="bg-slate-50 border-b border-slate-100 px-8 py-5">
        <h3 class="text-lg font-black text-[#3d2c1d] flex items-center gap-2">
            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            II. Datos Familiares
        </h3>
    </div>
    
    <div class="p-8 space-y-10">
        <!-- Madre (Con Alpine.js para la Iglesia) -->
        <div x-data="{ asisteIglesia: {{ old('madre_asiste_iglesia', $alumno->madre_asiste_iglesia ?? false) ? 'true' : 'false' }} }">
            <h4 class="font-black text-slate-800 border-b border-slate-100 pb-3 mb-5 uppercase tracking-widest text-[11px]">Datos de la Madre</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Nombre Completo</label>
                    <input type="text" name="madre_nombre_completo" value="{{ old('madre_nombre_completo', $alumno->madre_nombre_completo ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Cédula</label>
                    <input type="text" name="madre_cedula" value="{{ old('madre_cedula', $alumno->madre_cedula ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Teléfono</label>
                    <input type="text" name="madre_telefono" value="{{ old('madre_telefono', $alumno->madre_telefono ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Ocupación</label>
                    <input type="text" name="madre_ocupacion" value="{{ old('madre_ocupacion', $alumno->madre_ocupacion ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div class="flex items-center pt-5">
                    <input type="hidden" name="madre_asiste_iglesia" value="0">
                    <label class="inline-flex items-center cursor-pointer group">
                        <input type="checkbox" name="madre_asiste_iglesia" value="1" x-model="asisteIglesia" class="rounded border-slate-300 text-[#e6ac27] shadow-sm focus:ring-[#e6ac27] h-5 w-5 transition-colors">
                        <span class="ml-3 text-sm font-bold text-slate-700 group-hover:text-[#3d2c1d]">¿Asiste a Iglesia?</span>
                    </label>
                </div>
                <div x-show="asisteIglesia" x-transition>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">¿A cuál Iglesia?</label>
                    <input type="text" name="madre_nombre_iglesia" value="{{ old('madre_nombre_iglesia', $alumno->madre_nombre_iglesia ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
            </div>
        </div>

        <!-- Padre (Con Alpine.js para la Iglesia) -->
        <div x-data="{ asisteIglesia: {{ old('padre_asiste_iglesia', $alumno->padre_asiste_iglesia ?? false) ? 'true' : 'false' }} }">
            <h4 class="font-black text-slate-800 border-b border-slate-100 pb-3 mb-5 uppercase tracking-widest text-[11px]">Datos del Padre</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Nombre Completo</label>
                    <input type="text" name="padre_nombre_completo" value="{{ old('padre_nombre_completo', $alumno->padre_nombre_completo ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Cédula</label>
                    <input type="text" name="padre_cedula" value="{{ old('padre_cedula', $alumno->padre_cedula ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Teléfono</label>
                    <input type="text" name="padre_telefono" value="{{ old('padre_telefono', $alumno->padre_telefono ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Ocupación</label>
                    <input type="text" name="padre_ocupacion" value="{{ old('padre_ocupacion', $alumno->padre_ocupacion ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div class="flex items-center pt-5">
                    <input type="hidden" name="padre_asiste_iglesia" value="0">
                    <label class="inline-flex items-center cursor-pointer group">
                        <input type="checkbox" name="padre_asiste_iglesia" value="1" x-model="asisteIglesia" class="rounded border-slate-300 text-[#e6ac27] shadow-sm focus:ring-[#e6ac27] h-5 w-5 transition-colors">
                        <span class="ml-3 text-sm font-bold text-slate-700 group-hover:text-[#3d2c1d]">¿Asiste a Iglesia?</span>
                    </label>
                </div>
                <div x-show="asisteIglesia" x-transition>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">¿A cuál Iglesia?</label>
                    <input type="text" name="padre_nombre_iglesia" value="{{ old('padre_nombre_iglesia', $alumno->padre_nombre_iglesia ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
            </div>
        </div>

        <!-- Tutor -->
        <div>
            <h4 class="font-black text-slate-800 border-b border-slate-100 pb-3 mb-5 uppercase tracking-widest text-[11px]">Datos del Tutor / Encargado Legal</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Nombre Completo</label>
                    <input type="text" name="tutor_nombre_completo" value="{{ old('tutor_nombre_completo', $alumno->tutor_nombre_completo ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Cédula</label>
                    <input type="text" name="tutor_cedula" value="{{ old('tutor_cedula', $alumno->tutor_cedula ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Teléfono</label>
                    <input type="text" name="tutor_telefono" value="{{ old('tutor_telefono', $alumno->tutor_telefono ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Ocupación</label>
                    <input type="text" name="tutor_ocupacion" value="{{ old('tutor_ocupacion', $alumno->tutor_ocupacion ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-[#e6ac27] focus:ring-[#e6ac27] sm:text-sm">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TARJETA 3: SALUD Y AUTORIZACIONES -->
<div class="mb-8 bg-white border border-rose-100 shadow-sm rounded-3xl overflow-hidden">
    <div class="bg-rose-50/50 border-b border-rose-100 px-8 py-5">
        <h3 class="text-lg font-black text-rose-900 flex items-center gap-2">
            <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            III. Información Médica y Retiro
        </h3>
    </div>
    
    <div class="p-8">
        <div class="mb-8">
            <label class="block text-sm font-bold text-slate-700 mb-2">Enfermedades Crónicas <span class="font-medium text-slate-400 text-[11px] uppercase tracking-wider ml-2">(Presentar Epicrisis para Educación Física)</span></label>
            <input type="text" name="enfermedades_cronicas" value="{{ old('enfermedades_cronicas', $alumno->enfermedades_cronicas ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-rose-500 focus:ring-rose-500 transition-colors" placeholder="Ej. Asma, Alergias, Ninguna...">
        </div>

        <h4 class="font-black text-slate-800 border-b border-slate-100 pb-3 mb-5 uppercase tracking-widest text-[11px]">Persona autorizada para retirar al alumno (En caso de emergencia)</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Nombre Completo</label>
                <input type="text" name="autorizado_retirar_nombre" value="{{ old('autorizado_retirar_nombre', $alumno->autorizado_retirar_nombre ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-rose-500 focus:ring-rose-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Cédula</label>
                <input type="text" name="autorizado_retirar_cedula" value="{{ old('autorizado_retirar_cedula', $alumno->autorizado_retirar_cedula ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-rose-500 focus:ring-rose-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-1.5">Teléfono</label>
                <input type="text" name="autorizado_retirar_telefono" value="{{ old('autorizado_retirar_telefono', $alumno->autorizado_retirar_telefono ?? '') }}" class="block w-full rounded-xl border-slate-200 bg-slate-50/50 shadow-sm focus:border-rose-500 focus:ring-rose-500 sm:text-sm">
            </div>
        </div>
    </div>
</div>

<!-- COMPROMISO CRISTIANO -->
<div class="mb-8 p-6 bg-amber-50/50 border border-amber-200/60 rounded-3xl shadow-sm">
    <label class="flex items-start space-x-4 cursor-pointer group">
        <input type="hidden" name="acepta_compromiso_cristiano" value="0">
        <input type="checkbox" name="acepta_compromiso_cristiano" value="1" {{ old('acepta_compromiso_cristiano', $alumno->acepta_compromiso_cristiano ?? false) ? 'checked' : '' }} class="mt-1 rounded border-slate-300 text-[#e6ac27] shadow-sm focus:ring-[#e6ac27] h-5 w-5 transition-colors">
        <span class="text-sm font-bold text-slate-700 leading-relaxed group-hover:text-[#3d2c1d] transition-colors">
            Acepto el compromiso de tener disposición para participar en todas las actividades cristianas, sociales y de cooperación que el centro educativo requiera.
        </span>
    </label>
</div>

<!-- BOTONES DE ACCIÓN (UX TIP #1: JERARQUÍA VISUAL) -->
<div class="flex items-center justify-end gap-6 mt-8 pt-6 border-t border-slate-200">
    
    <!-- ACCIÓN SECUNDARIA: Sutil, tipo enlace -->
    <a href="{{ url()->previous() }}" class="text-sm font-bold text-slate-400 hover:text-slate-800 transition-colors">
        Cancelar
    </a>
    
    <!-- ACCIÓN PRINCIPAL: Botón con todo el peso visual -->
    <button type="submit" class="px-8 py-3.5 bg-[#e6ac27] text-white rounded-xl hover:bg-[#c48e1b] font-black text-sm shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#e6ac27] focus:ring-offset-2">
        {{ $btnText }}
    </button>
</div>

<!-- ⚙️ Script de Alpine.js para el Buscador (Mantenemos tu lógica intacta) -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('buscadorHermanos', (valorInicial) => ({
            query: '',
            resultados: [],
            seleccionados: valorInicial ? valorInicial.split(', ').filter(i => i) : [],
            
            get textoFinal() {
                return this.seleccionados.join(', ');
            },

            async buscar() {
                if (this.query.length < 2) {
                    this.resultados = [];
                    return;
                }
                
                try {
                    const response = await fetch(`/api/alumnos/buscar?q=${this.query}`);
                    if(response.ok) {
                        this.resultados = await response.json();
                    }
                } catch (error) {
                    console.error("Error buscando hermanos", error);
                    this.resultados = [
                        { id: 99, nombre_completo: 'Juan Pérez', codigo_unico_persona: 'CUP-001', grado_seccion: '5to Grado A' },
                        { id: 98, nombre_completo: 'Ana Pérez', codigo_unico_persona: 'CUP-002', grado_seccion: '3er Nivel (Preescolar)' }
                    ];
                }
            },

            agregarHermano(hermano) {
                const formato = `${hermano.nombre_completo} (${hermano.grado_seccion})`;
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