<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Sin rol asignado') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-amber-200">
                <div class="p-8 text-center bg-amber-50 border-b border-amber-100">
                    <div class="w-20 h-20 mx-auto mb-6 bg-amber-100 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-[#3d2c1d] mb-3">Sin rol asignado</h3>
                    <p class="text-lg text-slate-600 max-w-md mx-auto">
                        Tu cuenta de usuario no tiene ningún rol asignado en el sistema. 
                        Por seguridad, no puedes acceder a los módulos administrativos ni académicos.
                    </p>
                </div>

                <div class="p-8 space-y-6">
                    <div class="bg-slate-50 rounded-xl p-6 border border-slate-200">
                        <h4 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ¿Qué significa esto?
                        </h4>
                        <ul class="text-slate-600 space-y-2 text-left list-disc list-inside">
                            <li>No tienes permisos para acceder al dashboard, reportes, matrículas, notas ni ningún módulo del sistema.</li>
                            <li>El administrador o gestor de usuarios debe asignarte un rol según tu función en la institución.</li>
                            <li>Los roles disponibles son: <strong class="text-[#3d2c1d]">Director, Subdirector, Coordinador, Secretaría, Gestor de Usuarios, Docente Guía, Docente por Asignatura</strong>.</li>
                        </ul>
                    </div>

                    <div class="bg-blue-50 rounded-xl p-6 border border-blue-200">
                        <h4 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ¿Qué puedes hacer ahora?
                        </h4>
                        <ol class="text-blue-800 space-y-2 text-left list-decimal list-inside">
                            <li>Contacta al <strong>Administrador</strong> o al <strong>Gestor de Usuarios</strong> de tu institución.</li>
                            <li>Solicita que te asignen el rol correspondiente a tu cargo.</li>
                            <li>Una vez asignado el rol, <a href="{{ route('logout') }}" class="underline hover:text-blue-600 font-medium">cierra sesión y vuelve a entrar</a> para ver los módulos disponibles.</li>
                        </ol>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('logout') }}" 
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-[#3d2c1d] hover:bg-stone-800 text-white rounded-2xl font-black shadow-sm transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Cerrar sesión
                        </a>
                        
                        <button onclick="window.history.back()" 
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl font-bold shadow-sm transition-all border border-slate-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver
                        </button>
                    </div>

                    <div class="text-center pt-6 border-t border-slate-100">
                        <p class="text-xs text-slate-400">
                            Usuario: <strong>{{ Auth::user()->nombre_completo }}</strong> 
                            ({{ Auth::user()->email }})
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>