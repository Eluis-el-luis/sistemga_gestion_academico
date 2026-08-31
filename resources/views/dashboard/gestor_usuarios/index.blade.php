<x-app-layout>
    <div class="py-10 bg-slate-50 min-h-screen relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <x-dashboard.banner>
                Administra las credenciales del personal, restablece contraseñas y gestiona el soporte técnico de primer nivel.
            </x-dashboard.banner>

            <!-- KPIs Técnicos (Tema Slate/Azul Grisáceo) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex items-center gap-4">
                    <div class="w-12 h-12 bg-slate-100 text-slate-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">Cuentas Activas</p>
                        <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $cuentasActivas ?? 0 }}</h4>
                    </div>
                </div>
            </div>

            <!-- Accesos Operativos -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-6">
                <a href="{{ route('academico.usuarios.index') }}" class="group bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-blue-300 transition-all">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        </div>
                        <h4 class="font-black text-[#3d2c1d] group-hover:text-blue-600 transition-colors">Directorio de Usuarios</h4>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Modifica perfiles, suspende accesos y actualiza contraseñas de docentes.</p>
                </a>
                
                <a href="{{ route('academico.boletines.imprimir') }}" class="group bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-emerald-300 transition-all">
                    <div class="flex items-center gap-4 mb-3">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        </div>
                        <h4 class="font-black text-[#3d2c1d] group-hover:text-emerald-600 transition-colors">Centro de Impresión</h4>
                    </div>
                    <p class="text-sm text-slate-500 font-medium">Exporta e imprime los boletines oficiales consolidados.</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>