<!-- resources/views/academico/alumnos/partials/_form.blade.php -->

<div class="mb-4">
    <label for="codigo_unico_persona" class="block text-gray-700 font-bold mb-2">Código Único Institucional *</label>
    <input type="text" name="codigo_unico_persona" id="codigo_unico_persona"
           value="{{ old('codigo_unico_persona', $alumno->codigo_unico_persona ?? '') }}"
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('codigo_unico_persona') border-red-500 @enderror"
           required>
    @error('codigo_unico_persona') 
        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> 
    @enderror
</div>

<div class="mb-4">
    <label for="nombre_completo" class="block text-gray-700 font-bold mb-2">Nombre Completo *</label>
    <input type="text" name="nombre_completo" id="nombre_completo"
           value="{{ old('nombre_completo', $alumno->nombre_completo ?? '') }}"
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('nombre_completo') border-red-500 @enderror"
           required>
    @error('nombre_completo') 
        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> 
    @enderror
</div>

<div class="mb-4">
    <label for="sexo" class="block text-gray-700 font-bold mb-2">Sexo *</label>
    <select name="sexo" id="sexo" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('sexo') border-red-500 @enderror" required>
        <option value="">Seleccione una opción</option>
        <option value="M" {{ old('sexo', $alumno->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
        <option value="F" {{ old('sexo', $alumno->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
    </select>
    @error('sexo') 
        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> 
    @enderror
</div>

<div class="mb-6">
    <label for="fecha_nacimiento" class="block text-gray-700 font-bold mb-2">Fecha de Nacimiento *</label>
    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
           value="{{ old('fecha_nacimiento', $alumno->fecha_nacimiento ?? '') }}"
           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('fecha_nacimiento') border-red-500 @enderror"
           required>
    @error('fecha_nacimiento') 
        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p> 
    @enderror
</div>

<div class="flex items-center justify-between mt-8 border-t pt-4">
    <a href="{{ route('academico.alumnos.index') }}" class="text-gray-500 hover:text-gray-700 font-bold py-2 px-4">
        Cancelar
    </a>
    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline transition duration-150">
        {{ $btnText }}
    </button>
</div>