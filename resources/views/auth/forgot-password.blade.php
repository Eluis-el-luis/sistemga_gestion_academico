<x-guest-layout>
    <div class="flex min-h-screen bg-white items-center justify-center p-4">
        
        <div class="max-w-md w-full bg-[#FFFDF5] rounded-3xl shadow-xl border border-[#e6ac27]/20 p-8 text-center relative overflow-hidden">
            <!-- Efecto visual de iluminación -->
            <div class="absolute top-0 right-0 -mt-10 -mr-10 w-32 h-32 bg-[#e6ac27]/10 rounded-full blur-2xl pointer-events-none"></div>

            <!-- Ícono de Seguridad -->
            <div class="flex justify-center mb-6 relative z-10">
                <div class="w-20 h-20 bg-white rounded-full shadow-md flex items-center justify-center border border-gray-100">
                    <svg class="w-10 h-10 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
            </div>

            <h2 class="text-2xl font-black text-[#3d2c1d] mb-4 relative z-10">¿Olvidaste tu contraseña?</h2>
            
            <p class="text-sm font-medium text-[#3d2c1d]/70 leading-relaxed mb-8 relative z-10">
                Por políticas de seguridad institucional, la recuperación automática de contraseñas se encuentra deshabilitada.<br><br>
                Por favor, abócate con el <strong>Gestor de Usuarios</strong> o la <strong>Administración</strong> del colegio para solicitar la asignación de una nueva clave.
            </p>

            <!-- Botón de Retorno -->
            <a href="{{ route('login') }}" class="inline-flex justify-center items-center gap-2 w-full py-4 px-4 bg-[#3d2c1d] hover:bg-[#2a1e14] text-white rounded-2xl shadow-lg text-sm font-black uppercase tracking-widest transition-all transform hover:-translate-y-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#3d2c1d] relative z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                VOLVER AL INICIO
            </a>
        </div>
        
    </div>
</x-guest-layout>