<x-guest-layout>
    <div class="flex min-h-screen bg-white dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 transition-colors">
        <div class="hidden lg:flex lg:w-1/2 bg-[#FFFDF8] dark:bg-neutral-900 relative flex-col justify-center items-center p-12 overflow-hidden border-r border-stone-200/80 dark:border-neutral-800">
            <div class="relative z-10 flex flex-col items-center">
                <img src="{{ asset('img/Logo.png') }}" alt="Escudo Colegio Cristiano en Nicaragua" class="w-56 h-auto drop-shadow-md mb-8 transform hover:scale-105 transition-transform duration-300">
                <h1 class="text-3xl xl:text-4xl font-bold text-neutral-900 dark:text-white text-center tracking-tight leading-tight">
                    Sistema de Gestión <br>
                    <span class="text-[#c99116] dark:text-[#e6ac27]">Académica</span>
                </h1>
                <div class="mt-6 w-16 h-1 bg-[#e6ac27] rounded-full"></div>
                <p class="mt-6 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-[0.2em] text-center">
                    Colegio Cristiano en Nicaragua
                </p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-white dark:bg-neutral-950 p-6 sm:p-12 lg:p-20">
            <div class="w-full max-w-md" x-data="{
                recordar: {{ old('remember') ? 'true' : 'false' }},
                correoGuardado: '',
                init() {
                    const guardado = localStorage.getItem('login_recordar_email');
                    if (guardado) {
                        this.recordar = true;
                        this.correoGuardado = guardado;
                        const inputEmail = document.getElementById('email');
                        if (inputEmail && !inputEmail.value) {
                            inputEmail.value = guardado;
                        }
                    }
                },
                alEnviar() {
                    const inputEmail = document.getElementById('email');
                    if (this.recordar && inputEmail && inputEmail.value.trim()) {
                        localStorage.setItem('login_recordar_email', inputEmail.value.trim());
                    } else {
                        localStorage.removeItem('login_recordar_email');
                    }
                }
            }">
                <div class="flex justify-end mb-6">
                    <x-ui.theme-toggle />
                </div>

                <div class="lg:hidden flex flex-col items-center mb-8 text-center">
                    <img src="{{ asset('img/Logo.png') }}" alt="Logo" class="w-28 h-auto drop-shadow-sm mb-3">
                    <h2 class="text-xl font-bold text-neutral-900 dark:text-white">
                        Sistema de Gestión <span class="text-[#c99116] dark:text-[#e6ac27]">Académica</span>
                    </h2>
                </div>

                <div class="mb-8 text-center lg:text-left">
                    <h2 class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-white tracking-tight">¡Bienvenido de nuevo!</h2>
                    <p class="text-sm font-normal text-neutral-500 dark:text-neutral-400 mt-1.5">Ingresa tus credenciales para continuar</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" @submit="alEnviar()" class="space-y-5">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-semibold text-neutral-800 dark:text-neutral-200">Correo Electrónico</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input
                                id="email"
                                class="block w-full pl-11 pr-4 py-3 bg-stone-50 dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-2xl focus:ring-2 focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors text-sm"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="username"
                            />
                        </div>
                        <p class="text-xs font-normal text-neutral-500 dark:text-neutral-400 pl-1">Ejemplo: usuario.apellido@ccn.edu.ni</p>
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

                    <div class="space-y-1.5" x-data="{ showPw: false }">
                        <label for="password" class="block text-sm font-semibold text-neutral-800 dark:text-neutral-200">Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none z-10">
                                <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>

                            <input
                                id="password"
                                :type="showPw ? 'text' : 'password'"
                                class="block w-full pl-11 pr-12 py-3 bg-stone-50 dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-2xl focus:ring-2 focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors text-sm"
                                name="password"
                                required
                                autocomplete="current-password"
                            />

                            <button
                                type="button"
                                @click="showPw = !showPw"
                                aria-label="Mostrar u ocultar contraseña"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 focus:outline-none transition-colors cursor-pointer"
                            >
                                <svg x-show="!showPw" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPw" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs font-normal text-neutral-500 dark:text-neutral-400 pl-1">Mínimo 8 caracteres asignados por administración.</p>
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                            <input
                                id="remember_me"
                                type="checkbox"
                                x-model="recordar"
                                class="w-4 h-4 border-stone-300 dark:border-neutral-700 rounded text-[#e6ac27] focus:ring-[#e6ac27] cursor-pointer"
                                name="remember"
                            >
                            <span class="ml-2.5 text-sm font-medium text-neutral-600 dark:text-neutral-300 group-hover:text-neutral-900 dark:group-hover:text-white transition-colors">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-semibold text-[#c99116] dark:text-[#e6ac27] hover:underline underline-offset-4 transition-colors cursor-pointer" href="{{ route('password.request') }}">
                                ¿Problemas para entrar?
                            </a>
                        @endif
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="w-full flex justify-center items-center gap-2 py-3.5 px-4 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 rounded-2xl text-sm font-bold uppercase tracking-wider transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#e6ac27] dark:focus:ring-offset-neutral-950 cursor-pointer">
                            Iniciar Sesión
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>