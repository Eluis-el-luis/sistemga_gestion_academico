<!-- resources/views/academico/alumnos/partials/_form.blade.php -->

<!-- TARJETA 1: DATOS PERSONALES -->
<div class="mb-8 p-6 bg-gray-50 border border-gray-200 rounded-lg">
    <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">I. Datos Personales del Estudiante</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Código Único Institucional *</label>
            <input type="text" name="codigo_unico_persona" value="{{ old('codigo_unico_persona', $alumno->codigo_unico_persona ?? '') }}" class="border rounded w-full py-2 px-3 focus:outline-none focus:border-indigo-500" required>
        </div>
        <div>
            <label class="block text-gray-700 font-bold mb-2">Nombre Completo *</label>
            <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $alumno->nombre_completo ?? '') }}" class="border rounded w-full py-2 px-3" required>
        </div>
        <div>
            <label class="block text-gray-700 font-bold mb-2">Sexo *</label>
            <select name="sexo" class="border rounded w-full py-2 px-3" required>
                <option value="">Seleccione</option>
                <option value="M" {{ old('sexo', $alumno->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
                <option value="F" {{ old('sexo', $alumno->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-700 font-bold mb-2">Fecha de Nacimiento *</label>
            <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ?? '') }}" class="border rounded w-full py-2 px-3" required>
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-700 font-bold mb-2">Dirección Domiciliar *</label>
            <input type="text" name="direccion_domiciliar" value="{{ old('direccion_domiciliar', $alumno->direccion_domiciliar ?? '') }}" class="border rounded w-full py-2 px-3" required>
        </div>
        <div class="md:col-span-2">
            <label class="block text-gray-700 font-bold mb-2">Hermanos en el colegio (Nombres y Grados)</label>
            <input type="text" name="hermanos_en_colegio" value="{{ old('hermanos_en_colegio', $alumno->hermanos_en_colegio ?? '') }}" class="border rounded w-full py-2 px-3">
        </div>
    </div>
</div>

<!-- TARJETA 2: DATOS FAMILIARES -->
<div class="mb-8 p-6 bg-gray-50 border border-gray-200 rounded-lg">
    <h3 class="text-lg font-bold text-gray-800 border-b pb-2 mb-4">II. Datos Familiares</h3>
    
    <!-- Madre -->
    <h4 class="font-semibold text-gray-600 mb-2">Datos de la Madre</h4>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Nombre Completo</label>
            <input type="text" name="madre_nombre_completo" value="{{ old('madre_nombre_completo', $alumno->madre_nombre_completo ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Cédula</label>
            <input type="text" name="madre_cedula" value="{{ old('madre_cedula', $alumno->madre_cedula ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Teléfono</label>
            <input type="text" name="madre_telefono" value="{{ old('madre_telefono', $alumno->madre_telefono ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Ocupación</label>
            <input type="text" name="madre_ocupacion" value="{{ old('madre_ocupacion', $alumno->madre_ocupacion ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div class="flex items-center mt-4">
            <input type="hidden" name="madre_asiste_iglesia" value="0">
            <input type="checkbox" name="madre_asiste_iglesia" value="1" {{ old('madre_asiste_iglesia', $alumno->madre_asiste_iglesia ?? false) ? 'checked' : '' }} class="mr-2">
            <label class="text-sm font-bold text-gray-700">¿Asiste a Iglesia?</label>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">¿Cuál?</label>
            <input type="text" name="madre_nombre_iglesia" value="{{ old('madre_nombre_iglesia', $alumno->madre_nombre_iglesia ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
    </div>

    <!-- Padre -->
    <h4 class="font-semibold text-gray-600 mb-2 border-t pt-4">Datos del Padre</h4>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Nombre Completo</label>
            <input type="text" name="padre_nombre_completo" value="{{ old('padre_nombre_completo', $alumno->padre_nombre_completo ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Cédula</label>
            <input type="text" name="padre_cedula" value="{{ old('padre_cedula', $alumno->padre_cedula ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Teléfono</label>
            <input type="text" name="padre_telefono" value="{{ old('padre_telefono', $alumno->padre_telefono ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Ocupación</label>
            <input type="text" name="padre_ocupacion" value="{{ old('padre_ocupacion', $alumno->padre_ocupacion ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div class="flex items-center mt-4">
            <input type="hidden" name="padre_asiste_iglesia" value="0">
            <input type="checkbox" name="padre_asiste_iglesia" value="1" {{ old('padre_asiste_iglesia', $alumno->padre_asiste_iglesia ?? false) ? 'checked' : '' }} class="mr-2">
            <label class="text-sm font-bold text-gray-700">¿Asiste a Iglesia?</label>
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">¿Cuál?</label>
            <input type="text" name="padre_nombre_iglesia" value="{{ old('padre_nombre_iglesia', $alumno->padre_nombre_iglesia ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
    </div>

    <!-- Tutor -->
    <h4 class="font-semibold text-gray-600 mb-2 border-t pt-4">Datos del Tutor</h4>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Nombre Completo</label>
            <input type="text" name="tutor_nombre_completo" value="{{ old('tutor_nombre_completo', $alumno->tutor_nombre_completo ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Cédula</label>
            <input type="text" name="tutor_cedula" value="{{ old('tutor_cedula', $alumno->tutor_cedula ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Teléfono</label>
            <input type="text" name="tutor_telefono" value="{{ old('tutor_telefono', $alumno->tutor_telefono ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Ocupación</label>
            <input type="text" name="tutor_ocupacion" value="{{ old('tutor_ocupacion', $alumno->tutor_ocupacion ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
    </div>
</div>

<!-- TARJETA 3: SALUD Y AUTORIZACIONES -->
<div class="mb-8 p-6 bg-red-50 border border-red-200 rounded-lg">
    <h3 class="text-lg font-bold text-red-800 border-b border-red-200 pb-2 mb-4">III. Información Médica y Retiro</h3>
    
    <div class="grid grid-cols-1 gap-4 mb-4">
        <div>
            <label class="block text-gray-700 font-bold mb-2">Enfermedades Crónicas (Presentar Epicrisis para Educ. Física)</label>
            <input type="text" name="enfermedades_cronicas" value="{{ old('enfermedades_cronicas', $alumno->enfermedades_cronicas ?? '') }}" class="border rounded w-full py-2 px-3" placeholder="Ninguna / Asma / etc...">
        </div>
    </div>

    <h4 class="font-semibold text-red-700 mt-4 mb-2">Persona autorizada para retirar al alumno</h4>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Nombre Completo</label>
            <input type="text" name="autorizado_retirar_nombre" value="{{ old('autorizado_retirar_nombre', $alumno->autorizado_retirar_nombre ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Cédula</label>
            <input type="text" name="autorizado_retirar_cedula" value="{{ old('autorizado_retirar_cedula', $alumno->autorizado_retirar_cedula ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
        <div>
            <label class="block text-xs font-bold text-gray-500 uppercase">Teléfono</label>
            <input type="text" name="autorizado_retirar_telefono" value="{{ old('autorizado_retirar_telefono', $alumno->autorizado_retirar_telefono ?? '') }}" class="border rounded w-full py-1 px-2">
        </div>
    </div>
</div>

<!-- COMPROMISO CRISTIANO -->
<div class="mb-8 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
    <label class="flex items-center space-x-3 cursor-pointer">
        <input type="hidden" name="acepta_compromiso_cristiano" value="0">
        <input type="checkbox" name="acepta_compromiso_cristiano" value="1" {{ old('acepta_compromiso_cristiano', $alumno->acepta_compromiso_cristiano ?? false) ? 'checked' : '' }} class="form-checkbox h-5 w-5 text-blue-600">
        <span class="text-sm font-bold text-blue-900">
            Acepto el compromiso de tener disposición para participar en todas las actividades cristianas, sociales y de cooperación que el centro requiera.
        </span>
    </label>
</div>

<!-- BOTONES -->
<div class="flex items-center justify-between mt-8 border-t pt-4">
    <a href="{{ route('academico.alumnos.index') }}" class="text-gray-500 hover:text-gray-700 font-bold py-2 px-4">
        Cancelar
    </a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
        {{ $btnText }}
    </button>
</div>