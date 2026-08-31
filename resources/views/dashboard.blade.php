<x-app-layout>
    <div class="py-10 bg-slate-50 min-h-screen relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Reutilizamos el banner transversal -->
            <x-dashboard.banner>
                Bienvenido al Sistema de Gestión Académica. Actualmente no tienes un perfil operativo habilitado. Contacta a un Gestor de Usuarios para recibir tus accesos.
            </x-dashboard.banner>

            <!-- Mensaje de estado estático -->
            <div class="bg-white p-10 rounded-3xl shadow-sm border border-slate-200 text-center flex flex-col items-center justify-center">
                <div class="w-20 h-20 bg-slate-50 text-slate-400 rounded-full flex items-center justify-center mb-5 border border-slate-100">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h3 class="text-xl font-black text-[#3d2c1d]">Panel en Espera</h3>
                <p class="text-sm text-slate-500 font-medium max-w-md mt-2 leading-relaxed">
                    Tu cuenta está activa, pero requiere que la administración te asigne un rol específico (Directiva, Docente, Coordinador o Gestor) para cargar las herramientas operativas.
                </p>
            </div>

        </div>
    </div>
</x-app-layout>