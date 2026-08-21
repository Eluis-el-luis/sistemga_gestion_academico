<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('academico.usuarios.index') }}" class="text-gray-500 hover:text-blue-600 transition-colors" title="Volver a la lista">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-xl text-gray-800 leading-tight">
                {{ __('Editar Perfil:') }} <span class="text-blue-600">{{ $usuario->nombre_completo ?? $usuario->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-8 border border-gray-100">
                
                <form action="{{ route('academico.usuarios.update', $usuario) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre Completo -->
                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="nombre_completo" value="{{ old('nombre_completo', $usuario->nombre_completo ?? $usuario->name) }}" required autofocus class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors">
                            @error('nombre_completo') <span class="text-red-500 text-xs font-medium mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Correo Electrónico -->
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Correo Electrónico <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email', $usuario->email) }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors">
                            @error('email') <span class="text-red-500 text-xs font-medium mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Asignar Roles Múltiples (CHECKBOXES) -->
                    <div class="pt-4">
                        <label class="block text-sm font-bold text-gray-700 mb-3">Roles y Accesos del Usuario <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 bg-amber-50/40 p-5 rounded-lg border border-amber-100">
                            @foreach($roles as $rol)
                                <label class="inline-flex items-center cursor-pointer group bg-white p-2.5 rounded shadow-sm border border-gray-200 hover:border-amber-400 transition-colors">
                                    <!-- Aquí está la lógica de validación para marcar lo que ya tiene -->
                                    <input type="checkbox" name="roles[]" value="{{ $rol->nombre ?? $rol->name }}" 
                                        {{ in_array(($rol->nombre ?? $rol->name), old('roles', $usuario->roles->pluck('name')->toArray())) ? 'checked' : '' }} 
                                        class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500 w-4 h-4">
                                    <span class="ml-2 text-sm font-medium text-gray-700 group-hover:text-amber-800">{{ $rol->nombre ?? $rol->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Al cambiar los roles, el menú del usuario se actualizará automáticamente en su próximo inicio de sesión.</p>
                        @error('roles') <span class="text-red-500 text-xs font-medium mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado (Activo / Inactivo) -->
                    <div class="pt-4">
                        <label for="activo" class="block text-sm font-bold text-gray-700 mb-1">Estado de Acceso <span class="text-red-500">*</span></label>
                        <select id="activo" name="activo" required class="block w-48 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition-colors bg-white">
                            <option value="1" {{ old('activo', $usuario->activo) == 1 ? 'selected' : '' }}>Activo (Permitido)</option>
                            <option value="0" {{ old('activo', $usuario->activo) == 0 ? 'selected' : '' }}>Inactivo (Bloqueado)</option>
                        </select>
                        @error('activo') <span class="text-red-500 text-xs font-medium mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-6 flex items-center justify-end space-x-4 border-t border-gray-100 mt-8">
                        <a href="{{ route('academico.usuarios.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Cancelar</a>
                        <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent bg-amber-500 py-2 px-6 text-sm font-medium text-white shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all">
                            Actualizar Perfil
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>