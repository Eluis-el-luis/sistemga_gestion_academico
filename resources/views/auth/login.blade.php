<x-guest-layout>
    <div class="flex min-h-screen bg-white">
        
        <!-- Mitad Izquierda: Branding Premium (Oculto en Celulares) -->
        <div class="hidden lg:flex lg:w-1/2 bg-[#FFFDF5] relative flex-col justify-center items-center p-12 overflow-hidden border-r border-[#e6ac27]/20">
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-[#e6ac27]/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-full h-1/2 bg-gradient-to-t from-[#3d2c1d]/5 to-transparent"></div>
            </div>

            <div class="relative z-10 flex flex-col items-center">
                <img src="{{ asset('img/Logo.png') }}" alt="Escudo Colegio Cristiano en Nicaragua" class="w-64 h-auto drop-shadow-2xl mb-8 transform hover:scale-105 transition-transform duration-500">
                <h1 class="text-4xl font-black text-[#3d2c1d] text-center tracking-tight leading-tight">
                    Sistema de Gestión <br>
                    <span class="text-[#e6ac27]">Académica</span>
                </h1>
                <div class="mt-6 w-16 h-1 bg-[#e6ac27] rounded-full shadow-sm"></div>
                <p class="mt-6 text-sm font-bold text-[#3d2c1d]/60 uppercase tracking-[0.2em] text-center">
                    Colegio Cristiano en Nicaragua
                </p>
            </div>
        </div>

        <!-- Mitad Derecha: Formulario de Ingreso -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-white p-8 sm:p-12 lg:p-24">
            <div class="w-full max-w-md">
                
                <!-- Logo para vista Móvil -->
                <div class="lg:hidden flex flex-col items-center mb-10">
                    <img src="{{ asset('img/Logo.png') }}" alt="Logo" class="w-32 h-auto drop-shadow-lg mb-4">
                    <center> <h2 class="text-2xl font-black text-[#3d2c1d]">SISTEMA DE GESTION <span class="text-[#e6ac27]"> ACADEMICO </span></h2> </center>
                </div>

                <div class="mb-10 text-center lg:text-left">
                   <center> <h2 class="text-3xl font-extrabold text-[#3d2c1d] tracking-tight">¡Bienvenido de nuevo!</h2> </center>
                   <center> <p class="text-sm font-medium text-[#3d2c1d]/60 mt-2">Por favor, ingresa tus credenciales para continuar</p> </center>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Campo Correo -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-bold text-[#3d2c1d]">Correo Electrónico</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-[#3d2c1d]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                            </div>
                            <input id="email" class="block w-full pl-11 pr-4 py-3.5 bg-gray-50 border border-gray-200 text-[#3d2c1d] rounded-2xl focus:ring-2 focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-all text-sm shadow-sm" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        </div>
                        <p class="text-xs font-medium text-[#3d2c1d]/50 pl-1 mt-1">Ejemplo: usuario.apellido@ccn.edu.ni</p>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Campo Contraseña con Alpine.js -->
                    <div class="space-y-1.5" x-data="{ showPw: false }">
                        <label for="password" class="block text-sm font-bold text-[#3d2c1d]">Contraseña</label>
                        <div class="relative">
                            <!-- Ícono Candado -->
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                <svg class="h-5 w-5 text-[#3d2c1d]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            
                            <!-- Input dinámico -->
                            <input id="password" :type="showPw ? 'text' : 'password'" class="block w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-200 text-[#3d2c1d] rounded-2xl focus:ring-2 focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-all text-sm shadow-sm" name="password" required autocomplete="current-password" />
                            
                            <!-- Botón Mostrar/Ocultar -->
                            <button type="button" @click="showPw = !showPw" class="absolute inset-y-0 right-0 pr-4 flex items-center text-[#3d2c1d]/40 hover:text-[#3d2c1d] focus:outline-none transition-colors">
                                <svg x-show="!showPw" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg x-show="showPw" style="display: none;" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                            </button>
                        </div>
                        <p class="text-xs font-medium text-[#3d2c1d]/50 pl-1 mt-1">Mínimo 8 caracteres asignados por administración.</p>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Recordarme y Olvidé mi clave -->
                    <div class="flex items-center justify-between pt-2">
                        <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                            <div class="relative flex items-center justify-center w-5 h-5">
                                <!-- El name="remember" es el que Laravel usa para mantener la sesión viva -->
                                <input id="remember_me" type="checkbox" class="peer appearance-none w-5 h-5 border-2 border-gray-300 rounded-md checked:bg-[#e6ac27] checked:border-[#e6ac27] focus:ring-[#e6ac27] focus:ring-offset-1 transition-all" name="remember">
                                <svg class="absolute w-3 h-3 text-white pointer-events-none opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="ml-3 text-sm font-semibold text-[#3d2c1d]/70 group-hover:text-[#3d2c1d] transition-colors">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-bold text-[#e6ac27] hover:text-[#c48e1b] transition-colors underline-offset-4 hover:underline" href="{{ route('password.request') }}">
                                ¿Problemas para entrar?
                            </a>
                        @endif
                    </div>

                    <!-- Botón Ingresar -->
                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center items-center gap-2 py-4 px-4 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-2xl shadow-lg shadow-[#e6ac27]/30 text-sm font-black uppercase tracking-widest transition-all transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#e6ac27]">
                            INICIAR SESIÓN
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</x-guest-layout>