<x-guest-layout>
    <div class="flex min-h-screen">
        
        <!-- Mitad Izquierda: Escudo y Branding (Oculto en Celulares) -->
        <div class="hidden md:flex md:w-1/2 bg-amber-50 flex-col justify-center items-center p-12 border-r border-amber-200 shadow-inner">
            <img src="{{ asset('img/Logo.png') }}" alt="Escudo Colegio Cristiano en Nicaragua" class="w-72 h-auto drop-shadow-xl mb-8">
            <h1 class="text-4xl font-bold text-amber-900 text-center tracking-tight">Sistema de Gestión Académica</h1>
            <p class="text-lg text-amber-700 mt-4 text-center font-medium uppercase tracking-widest">Colegio Cristiano en Nicaragua</p>
        </div>

        <!-- Mitad Derecha: Formulario de Ingreso -->
        <div class="w-full md:w-1/2 flex flex-col justify-center items-center bg-white p-8 sm:p-12">
            <div class="w-full max-w-md">
                
                <!-- Logo para vista Móvil (Solo visible en celulares) -->
                <div class="md:hidden flex flex-col items-center mb-8">
                    <img src="{{ asset('img/Logo.png') }}" alt="Logo" class="w-32 h-auto drop-shadow-md mb-4">
                    <h2 class="text-2xl font-bold text-amber-900 text-center">SGA</h2>
                </div>

                <div class="mb-8 text-center md:text-left">
                    <h2 class="text-3xl font-extrabold text-gray-800">Bienvenido</h2>
                    <p class="text-sm text-gray-500 mt-2">Ingresa tus credenciales para acceder a tu panel.</p>
                </div>

                <!-- Alertas de Sesión -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Campo Correo -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-amber-900">Correo Electrónico</label>
                        <input id="email" class="block mt-2 w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm py-3" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="usuario@colegio.edu.ni" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Campo Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-amber-900">Contraseña</label>
                        <input id="password" class="block mt-2 w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm py-3" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Recordarme y Olvidé mi clave -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500" name="remember">
                            <span class="ms-2 text-sm text-gray-600 font-medium">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-semibold text-amber-600 hover:text-amber-800 transition-colors" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <!-- Botón Ingresar -->
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-sm font-bold text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all transform hover:scale-[1.02]">
                        INICIAR SESIÓN
                    </button>
                </form>
            </div>
        </div>
        
    </div>
</x-guest-layout>