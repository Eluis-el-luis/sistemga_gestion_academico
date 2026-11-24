@hasanyrole('directora|subdirectora|gestor_it|coordinador|Director|Subdirector|Gestor de Usuarios|Coordinador')
<div
    x-show="sidebarOpen"
    @click="sidebarOpen = false"
    x-transition.opacity
    style="display: none;"
    class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs lg:hidden"
></div>

<aside
    @mouseenter="if (window.innerWidth >= 1024) sidebarOpen = true"
    @mouseleave="if (window.innerWidth >= 1024) sidebarOpen = false"
    :class="sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-20'"
    class="fixed inset-y-0 left-0 z-50 bg-[#FFFDF8] dark:bg-neutral-900 border-r border-stone-200 dark:border-neutral-800 shadow-lg transition-all duration-200 ease-in-out flex flex-col overflow-hidden"
>
    <div class="h-16 border-b border-stone-200/80 dark:border-neutral-800 shrink-0 bg-white/70 dark:bg-neutral-900 flex items-center justify-center">
        <span x-show="sidebarOpen" x-transition.opacity class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">Navegación</span>
        <div x-show="!sidebarOpen" class="w-2 h-2 rounded-full bg-[#e6ac27]/60 hidden lg:block"></div>
    </div>

    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-5 space-y-1.5">
        <a href="{{ route('dashboard') }}" title="Panel Principal" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('dashboard') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap">Panel Principal</span>
        </a>

        @hasanyrole('directora|subdirectora|gestor_it|Director|Subdirector|Gestor de Usuarios')
        <div class="pt-4 pb-1">
            <p x-show="sidebarOpen" class="px-4 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 mb-1.5 whitespace-nowrap">Administración</p>
            <div x-show="!sidebarOpen" class="w-6 h-px bg-stone-200 dark:bg-neutral-800 mx-auto my-2.5"></div>

            <a href="{{ route('academico.usuarios.index') }}" title="Personal y Docentes" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.usuarios.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Personal y Docentes</span>
            </a>

            @hasanyrole('directora|subdirectora|Director|Subdirector')
            <a href="{{ route('academico.notas.solicitudes.index') }}" title="Solicitudes de Edición de Notas" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.notas.solicitudes.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Solicitudes de Notas</span>
            </a>

            <a href="{{ route('academico.respaldos.index') }}" title="Respaldos de la Base de Datos" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.respaldos.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Respaldos</span>
            </a>
            @endhasanyrole
        </div>
        @endhasanyrole

        @hasanyrole('directora|subdirectora|coordinador|Director|Subdirector|Coordinador')
        <div class="pt-4 pb-1">
            <p x-show="sidebarOpen" class="px-4 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 mb-1.5 whitespace-nowrap">Académico</p>
            <div x-show="!sidebarOpen" class="w-6 h-px bg-stone-200 dark:bg-neutral-800 mx-auto my-2.5"></div>

            <a href="{{ route('academico.alumnos.create') }}" title="Registrar Alumno" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.alumnos.create') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Registrar Alumno</span>
            </a>

            <a href="{{ route('academico.matriculas.create') }}" title="Nueva Matrícula" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.matriculas.create') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Nueva Matrícula</span>
            </a>

            <a href="{{ route('academico.cortes.index') }}" title="Criterios de Evaluación" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.cortes.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Criterios de Evaluación</span>
            </a>
        </div>

        <div class="pt-4 pb-1">
            <p x-show="sidebarOpen" class="px-4 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 mb-1.5 whitespace-nowrap">Planificación Escolar</p>
            <div x-show="!sidebarOpen" class="w-6 h-px bg-stone-200 dark:bg-neutral-800 mx-auto my-2.5"></div>

            <a href="{{ route('academico.aulas.index') }}" title="Gestión de Aulas" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.aulas.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Gestión de Aulas</span>
            </a>

            <a href="{{ route('academico.asignaciones.index') }}" title="Asignación de Maestros" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.asignaciones.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Asignación Maestros</span>
            </a>

            <a href="{{ route('academico.gestor-horarios.index') }}" title="Gestor de Horarios" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.gestor-horarios.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Gestor de Horarios</span>
            </a>

            <a href="{{ route('academico.asignaturas.index') }}" title="Catálogo de Asignaturas" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.asignaturas.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Asignaturas</span>
            </a>

            <a href="{{ route('academico.malla.index') }}" title="Malla Curricular" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.malla.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Pensum</span>
            </a>

            <a href="{{ route('academico.anios-escolares.index') }}" title="Gestión de Años Escolares" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.anios-escolares.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Años Escolares</span>
            </a>

            <a href="{{ route('academico.bloques.index') }}" title="Bloques de Modalidades" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.bloques.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Bloques Horarios</span>
            </a>
        </div>

        <div class="pt-4 pb-1">
            <p x-show="sidebarOpen" class="px-4 text-[11px] font-semibold uppercase tracking-wider text-neutral-400 mb-1.5 whitespace-nowrap">Reportes</p>
            <div x-show="!sidebarOpen" class="w-6 h-px bg-stone-200 dark:bg-neutral-800 mx-auto my-2.5"></div>
            <a href="{{ route('academico.reportes.index') }}" title="Centro de Reportes" class="flex items-center gap-3 px-3.5 mx-2.5 py-2.5 rounded-xl text-sm transition-colors {{ request()->routeIs('academico.reportes.*') ? 'bg-amber-50 dark:bg-amber-500/15 text-[#b88212] dark:text-[#e6ac27] font-semibold border border-[#e6ac27]/30' : 'text-neutral-600 dark:text-neutral-300 hover:bg-stone-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Centro de Reportes</span>
            </a>
        </div>
        @endhasanyrole
    </nav>

    <div class="p-4 border-t border-stone-200 dark:border-neutral-800 shrink-0 lg:hidden bg-white/70 dark:bg-neutral-900">
        <button type="button" @click="sidebarOpen = false" class="flex justify-center items-center gap-2 w-full px-4 py-2.5 rounded-xl bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 text-sm font-medium hover:bg-stone-200 dark:hover:bg-neutral-700 transition-colors cursor-pointer">
            Cerrar Menú
        </button>
    </div>
</aside>
@endhasanyrole