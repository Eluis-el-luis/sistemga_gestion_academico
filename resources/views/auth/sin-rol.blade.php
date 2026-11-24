<x-app-layout>
    <x-slot name="header">
        <div class="bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <h2 class="font-bold text-lg text-neutral-900 dark:text-neutral-100 leading-tight">
                {{ __('Sin rol asignado') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-amber-200 dark:border-neutral-800">
                <div class="p-8 text-center bg-amber-50/70 dark:bg-neutral-800/60 border-b border-amber-100 dark:border-neutral-800">
                    <div class="w-16 h-16 mx-auto mb-5 bg-amber-100 dark:bg-neutral-900 rounded-2xl flex items-center justify-center border border-amber-200 dark:border-neutral-700">
                        <svg class="w-8 h-8 text-amber-600 dark:text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white mb-2">Sin rol asignado</h3>
                    <p class="text-sm sm:text-base text-neutral-600 dark:text-neutral-300 max-w-md mx-auto">
                        Tu cuenta de usuario no tiene un rol asignado en el sistema. Por seguridad, no puedes acceder a los módulos operativos ni académicos.
                    </p>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    <div class="bg-stone-50 dark:bg-neutral-800/60 rounded-2xl p-5 border border-stone-200/80 dark:border-neutral-700">
                        <h4 class="font-semibold text-neutral-900 dark:text-neutral-100 mb-2.5 text-sm">¿Qué significa esto?</h4>
                        <ul class="text-neutral-600 dark:text-neutral-300 space-y-2 text-sm text-left list-disc list-inside">
                            <li>No tienes permisos para acceder al panel, reportes, matrículas, calificaciones ni demás módulos.</li>
                            <li>El administrador o gestor de usuarios debe asignarte un rol según tu función en el centro.</li>
                            <li>Roles disponibles: <strong class="font-semibold text-neutral-900 dark:text-white">Directora, Subdirectora, Coordinadora, Secretaría, Gestor IT, Docente Guía, Docente por Asignatura, Docente Auxiliar, Docente Suplente y Psicóloga</strong>.</li>
                        </ul>
                    </div>

                    <div class="bg-amber-50/50 dark:bg-neutral-800/40 rounded-2xl p-5 border border-amber-200/70 dark:border-neutral-700">
                        <h4 class="font-semibold text-neutral-900 dark:text-neutral-100 mb-2.5 text-sm">¿Qué puedes hacer ahora?</h4>
                        <ol class="text-neutral-700 dark:text-neutral-300 space-y-2 text-sm text-left list-decimal list-inside">
                            <li>Contacta a <strong class="font-semibold text-neutral-900 dark:text-white">Dirección</strong> o al <strong class="font-semibold text-neutral-900 dark:text-white">Gestor de Usuarios</strong>.</li>
                            <li>Solicita que te asignen el rol correspondiente a tu cargo.</li>
                            <li>Una vez asignado el rol, cierra sesión y vuelve a iniciar sesión para ver tus módulos.</li>
                        </ol>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center pt-1">
                        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 rounded-2xl text-sm font-semibold transition-colors cursor-pointer">
                                Cerrar sesión
                            </button>
                        </form>

                        <button type="button" onclick="window.history.back()" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-stone-100 dark:bg-neutral-800 hover:bg-stone-200 dark:hover:bg-neutral-700 text-neutral-700 dark:text-neutral-200 rounded-2xl text-sm font-semibold transition-colors border border-stone-200 dark:border-neutral-700 cursor-pointer">
                            Volver
                        </button>
                    </div>

                    <div class="text-center pt-5 border-t border-stone-100 dark:border-neutral-800">
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            Usuario: <strong class="font-semibold text-neutral-800 dark:text-neutral-200">{{ Auth::user()?->nombre_completo ?? Auth::user()?->name }}</strong>
                            ({{ Auth::user()?->email }})
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>