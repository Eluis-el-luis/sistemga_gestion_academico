<x-guest-layout>
    <div class="flex min-h-screen bg-stone-50 dark:bg-neutral-950 items-center justify-center p-4 transition-colors">
        <div class="max-w-md w-full bg-white dark:bg-neutral-900 rounded-3xl shadow-sm border border-stone-200 dark:border-neutral-800 p-8 text-center relative overflow-hidden">
            <div class="flex justify-end mb-2">
                <x-ui.theme-toggle />
            </div>

            <div class="flex justify-center mb-5">
                <div class="w-16 h-16 bg-amber-50 dark:bg-neutral-800 rounded-2xl flex items-center justify-center border border-[#e6ac27]/30 dark:border-neutral-700">
                    <svg class="w-8 h-8 text-[#d49b1a] dark:text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-3">¿Olvidaste tu contraseña?</h2>

            <p class="text-sm font-normal text-neutral-600 dark:text-neutral-300 leading-relaxed mb-7">
                Por políticas de seguridad institucional, la recuperación automática de contraseñas se encuentra deshabilitada.
                <br><br>
                Por favor, abócate con el <strong class="font-semibold text-neutral-900 dark:text-white">Gestor de Usuarios</strong> o la <strong class="font-semibold text-neutral-900 dark:text-white">Administración</strong> del colegio para solicitar la asignación de una nueva clave.
            </p>

            <a href="{{ route('login') }}" class="inline-flex justify-center items-center gap-2 w-full py-3.5 px-4 bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 rounded-2xl text-sm font-semibold uppercase tracking-wider transition-colors focus:outline-none focus:ring-2 focus:ring-[#e6ac27] cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al inicio
            </a>
        </div>
    </div>
</x-guest-layout>