<!-- TARJETA 1: DATOS PERSONALES -->
<div class="mb-8 bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden">
    <div class="bg-indigo-50 border-b border-indigo-100 px-6 py-4">
        <h3 class="text-lg font-bold text-indigo-900 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            I. Datos Personales del Estudiante
        </h3>
    </div>
    
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
        <div>
            <label class="block text-gray-700 font-bold mb-1">Código Único Institucional (CUP) <span class="text-red-500">*</span></label>
            <input type="text" name="codigo_unico_persona" value="{{ old('codigo_unico_persona', $alumno->codigo_unico_persona ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors" required autofocus>
            @error('codigo_unico_persona') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label class="block text-gray-700 font-bold mb-1">Nombre Completo <span class="text-red-500">*</span></label>
            <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $alumno->nombre_completo ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors" required>
            @error('nombre_completo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label class="block text-gray-700 font-bold mb-1">Sexo <span class="text-red-500">*</span></label>
            <select name="sexo" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors bg-white" required>
                <option value="">Seleccione...</option>
                <option value="M" {{ old('sexo', $alumno->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
                <option value="F" {{ old('sexo', $alumno->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
            </select>
            @error('sexo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>
        
        <div>
            <label class="block text-gray-700 font-bold mb-1">Fecha de Nacimiento <span class="text-red-500">*</span></label>
            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors" required>
            @error('fecha_nacimiento') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>
        
        <div class="md:col-span-2">
            <label class="block text-gray-700 font-bold mb-1">Dirección Domiciliar <span class="text-red-500">*</span></label>
            <input type="text" name="direccion_domiciliar" value="{{ old('direccion_domiciliar', $alumno->direccion_domiciliar ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors" required>
            @error('direccion_domiciliar') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
        </div>
        
        <!-- 🌟 COMPONENTE BUSCADOR DE HERMANOS (Alpine.js) -->
        <div class="md:col-span-2" x-data="buscadorHermanos('{{ old('hermanos_en_colegio', $alumno->hermanos_en_colegio ?? '') }}')">
            <label class="block text-gray-700 font-bold mb-1">Hermanos en el colegio (Buscador)</label>
            
            <div class="relative">
                <!-- Barra de búsqueda -->
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="query" @input.debounce.500ms="buscar" placeholder="Escriba el CUP o Nombre del hermano..." class="block w-full pl-10 pr-3 py-2 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors">
                </div>

                <!-- Menú desplegable de resultados -->
                <ul x-show="resultados.length > 0" @click.away="resultados = []" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" style="display: none;">
                    <template x-for="resultado in resultados" :key="resultado.id">
                        <li @click="agregarHermano(resultado)" class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 transition-colors">
                            <div class="flex flex-col">
                                <span class="font-bold block truncate" x-text="resultado.nombre_completo"></span>
                                <span class="text-gray-500 text-xs block truncate" x-text="resultado.codigo_unico_persona + ' - ' + resultado.grado_seccion"></span>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Etiquetas visuales de los hermanos seleccionados -->
            <div class="mt-3 flex flex-wrap gap-2">
                <template x-for="(hermano, index) in seleccionados" :key="index">
                    <span class="inline-flex items-center py-1.5 pl-3 pr-2 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700">
                        <span x-text="hermano"></span>
                        <button type="button" @click="removerHermano(index)" class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none focus:bg-indigo-500 focus:text-white">
                            <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8"><path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" /></svg>
                        </button>
                    </span>
                </template>
            </div>

            <!-- Input Oculto que guarda el texto final para que Luis lo reciba en el Backend -->
            <input type="hidden" name="hermanos_en_colegio" x-model="textoFinal">
            
            <p class="text-xs text-gray-500 mt-2">Busca y selecciona al hermano. El sistema vinculará su grado automáticamente.</p>
        </div>
    </div>
</div>

<!-- TARJETA 2: DATOS FAMILIARES -->
<div class="mb-8 bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden text-sm">
    <div class="bg-blue-50 border-b border-blue-100 px-6 py-4">
        <h3 class="text-lg font-bold text-blue-900 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            II. Datos Familiares
        </h3>
    </div>
    
    <div class="p-6 space-y-8">
        <!-- Madre (Con Alpine.js para la Iglesia) -->
        <div x-data="{ asisteIglesia: {{ old('madre_asiste_iglesia', $alumno->madre_asiste_iglesia ?? false) ? 'true' : 'false' }} }">
            <h4 class="font-bold text-gray-800 border-b pb-2 mb-4 uppercase tracking-wider text-xs">Datos de la Madre</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                    <input type="text" name="madre_nombre_completo" value="{{ old('madre_nombre_completo', $alumno->madre_nombre_completo ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cédula</label>
                    <input type="text" name="madre_cedula" value="{{ old('madre_cedula', $alumno->madre_cedula ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono</label>
                    <input type="text" name="madre_telefono" value="{{ old('madre_telefono', $alumno->madre_telefono ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ocupación</label>
                    <input type="text" name="madre_ocupacion" value="{{ old('madre_ocupacion', $alumno->madre_ocupacion ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex items-center pt-5">
                    <input type="hidden" name="madre_asiste_iglesia" value="0">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="madre_asiste_iglesia" value="1" x-model="asisteIglesia" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 h-5 w-5">
                        <span class="ml-2 text-sm font-bold text-gray-700">¿Asiste a Iglesia?</span>
                    </label>
                </div>
                <div x-show="asisteIglesia" x-transition>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">¿A cuál Iglesia?</label>
                    <input type="text" name="madre_nombre_iglesia" value="{{ old('madre_nombre_iglesia', $alumno->madre_nombre_iglesia ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Padre (Con Alpine.js para la Iglesia) -->
        <div x-data="{ asisteIglesia: {{ old('padre_asiste_iglesia', $alumno->padre_asiste_iglesia ?? false) ? 'true' : 'false' }} }">
            <h4 class="font-bold text-gray-800 border-b pb-2 mb-4 uppercase tracking-wider text-xs">Datos del Padre</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                    <input type="text" name="padre_nombre_completo" value="{{ old('padre_nombre_completo', $alumno->padre_nombre_completo ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cédula</label>
                    <input type="text" name="padre_cedula" value="{{ old('padre_cedula', $alumno->padre_cedula ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono</label>
                    <input type="text" name="padre_telefono" value="{{ old('padre_telefono', $alumno->padre_telefono ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ocupación</label>
                    <input type="text" name="padre_ocupacion" value="{{ old('padre_ocupacion', $alumno->padre_ocupacion ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex items-center pt-5">
                    <input type="hidden" name="padre_asiste_iglesia" value="0">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="padre_asiste_iglesia" value="1" x-model="asisteIglesia" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 h-5 w-5">
                        <span class="ml-2 text-sm font-bold text-gray-700">¿Asiste a Iglesia?</span>
                    </label>
                </div>
                <div x-show="asisteIglesia" x-transition>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">¿A cuál Iglesia?</label>
                    <input type="text" name="padre_nombre_iglesia" value="{{ old('padre_nombre_iglesia', $alumno->padre_nombre_iglesia ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Tutor -->
        <div>
            <h4 class="font-bold text-gray-800 border-b pb-2 mb-4 uppercase tracking-wider text-xs">Datos del Tutor / Encargado Legal</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                    <input type="text" name="tutor_nombre_completo" value="{{ old('tutor_nombre_completo', $alumno->tutor_nombre_completo ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cédula</label>
                    <input type="text" name="tutor_cedula" value="{{ old('tutor_cedula', $alumno->tutor_cedula ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono</label>
                    <input type="text" name="tutor_telefono" value="{{ old('tutor_telefono', $alumno->tutor_telefono ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Ocupación</label>
                    <input type="text" name="tutor_ocupacion" value="{{ old('tutor_ocupacion', $alumno->tutor_ocupacion ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TARJETA 3: SALUD Y AUTORIZACIONES -->
<div class="mb-8 bg-white border border-red-100 shadow-sm rounded-xl overflow-hidden text-sm">
    <div class="bg-red-50 border-b border-red-100 px-6 py-4">
        <h3 class="text-lg font-bold text-red-900 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            III. Información Médica y Retiro
        </h3>
    </div>
    
    <div class="p-6">
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Enfermedades Crónicas <span class="font-normal text-gray-500 text-xs">(Presentar Epicrisis para Educación Física)</span></label>
            <input type="text" name="enfermedades_cronicas" value="{{ old('enfermedades_cronicas', $alumno->enfermedades_cronicas ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" placeholder="Ej. Asma, Alergias, Ninguna...">
        </div>

        <h4 class="font-bold text-gray-800 border-b pb-2 mb-4 uppercase tracking-wider text-xs">Persona autorizada para retirar al alumno (En caso de emergencia)</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nombre Completo</label>
                <input type="text" name="autorizado_retirar_nombre" value="{{ old('autorizado_retirar_nombre', $alumno->autorizado_retirar_nombre ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Cédula</label>
                <input type="text" name="autorizado_retirar_cedula" value="{{ old('autorizado_retirar_cedula', $alumno->autorizado_retirar_cedula ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Teléfono</label>
                <input type="text" name="autorizado_retirar_telefono" value="{{ old('autorizado_retirar_telefono', $alumno->autorizado_retirar_telefono ?? '') }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
            </div>
        </div>
    </div>
</div>

<!-- COMPROMISO CRISTIANO -->
<div class="mb-8 p-5 bg-blue-50 border-l-4 border-blue-500 rounded-lg shadow-sm">
    <label class="flex items-start space-x-3 cursor-pointer">
        <input type="hidden" name="acepta_compromiso_cristiano" value="0">
        <input type="checkbox" name="acepta_compromiso_cristiano" value="1" {{ old('acepta_compromiso_cristiano', $alumno->acepta_compromiso_cristiano ?? false) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500 h-5 w-5">
        <span class="text-sm font-bold text-blue-900 leading-snug">
            Acepto el compromiso de tener disposición para participar en todas las actividades cristianas, sociales y de cooperación que el centro educativo requiera.
        </span>
    </label>
</div>

<!-- BOTONES DE ACCIÓN -->
<div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
    <a href="{{ route('academico.alumnos.index') }}" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold text-sm transition-colors">
        Cancelar
    </a>
    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold text-sm shadow-sm transition-colors focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        {{ $btnText }}
    </button>
</div>

<!-- ⚙️ Script de Alpine.js para el Buscador -->
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
                
                // 🔔 AVISO PARA LUIS (Backend):
                // Aquí debes habilitar una ruta en api.php o web.php (ej. /api/alumnos/buscar)
                // que reciba ?q=... y devuelva un JSON con {id, nombre_completo, codigo_unico_persona, grado_seccion}
                
                try {
                    const response = await fetch(`/api/alumnos/buscar?q=${this.query}`);
                    if(response.ok) {
                        this.resultados = await response.json();
                    }
                } catch (error) {
                    console.error("Error buscando hermanos", error);
                    // Mock de prueba visual por si Luis aún no hace la ruta:
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