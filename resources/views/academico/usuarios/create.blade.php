<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crear Nuevo Usuario</h2>
    </x-slot>

    <div class="py-12 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form action="{{ route('academico.usuarios.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block font-bold mb-2">Nombre Completo *</label>
                    <input type="text" name="nombre_completo" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block font-bold mb-2">Correo Electrónico (Para Iniciar Sesión) *</label>
                    <input type="email" name="email" class="w-full border rounded p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block font-bold mb-2">Contraseña Temporal *</label>
                    <input type="text" name="password" class="w-full border rounded p-2" value="colegio2026" required>
                    <p class="text-xs text-gray-500 mt-1">El usuario podrá cambiarla después.</p>
                </div>

                <div class="mb-6">
                    <label class="block font-bold mb-2">Asignar Rol *</label>
                    <select name="rol_id" class="w-full border rounded p-2" required>
                        <option value="">Seleccione el nivel de acceso...</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}">{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end border-t pt-4">
                    <a href="{{ route('academico.usuarios.index') }}" class="mr-4 mt-2 text-gray-500 font-bold">Cancelar</a>
                    <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-6 rounded">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>