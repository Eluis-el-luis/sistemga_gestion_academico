<!-- Fondo oscuro solo para celulares cuando el menú está abierto -->
<div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" x-transition.opacity style="display: none;"></div>

<!-- SIDEBAR: Cambia de w-64 a w-20 en pantallas grandes -->
<aside :class="sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0 lg:w-20'" class="fixed inset-y-0 left-0 z-50 bg-[#FFFDF5] border-r border-[#e6ac27]/20 shadow-xl transition-all duration-300 ease-in-out flex flex-col overflow-hidden">
    
    <!-- ZONA LOGO: Ajustado a h-16 exactos para alinearse con el topbar -->
    <div class="flex items-center justify-center h-16 border-b border-[#e6ac27]/20 shrink-0 transition-all">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group px-4 w-full" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:px-0'">
            <x-application-logo class="block h-8 w-8 object-contain shrink-0" />
            <span x-show="sidebarOpen" x-transition.opacity class="font-black text-[#3d2c1d] text-lg tracking-tight group-hover:text-[#e6ac27] transition-colors whitespace-nowrap">
                  SGA PRO
            </span>
        </a>
<nav x-data="{ open: false }" class="bg-white border-b border-amber-200/80 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            
            <div class="flex">
                <div class="shrink-0 flex items-center justify-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                        <x-application-logo class="block h-10 w-10 rounded-full object-cover border border-amber-300 shadow-sm group-hover:scale-105 transition-transform" />
                        <span class="font-extrabold text-amber-900 text-lg hidden xl:inline-block tracking-tight group-hover:text-amber-700 transition-colors">
                            SGA Institucional
                        </span>
                    </a>
                </div>

                <div class="hidden sm:block h-6 w-px bg-amber-200 mx-6 my-auto"></div>

                <div class="hidden space-x-6 sm:-my-px sm:flex">
                    
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="hover:text-amber-800 focus:text-amber-800">
                        {{ __('Panel') }}
                    </x-nav-link>

                    <!-- Solo Director y Gestor -->
                    @hasanyrole('Director|Gestor de Usuarios')
                        <x-nav-link :href="route('academico.usuarios.index')" :active="request()->routeIs('academico.usuarios.*')" class="hover:text-amber-800">
                            {{ __('Personal y Docentes') }}
                        </x-nav-link>
                    @endhasanyrole

                    <!-- Alumnos y Matrícula -->
                    @hasanyrole('Director|Subdirector|Gestor de Usuarios|Coordinador|Docente Guía')
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-amber-800 hover:border-amber-300 focus:outline-none transition duration-150 ease-in-out h-16">
                                    <div>Alumnos</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('academico.alumnos.index')" class="hover:bg-amber-50 hover:text-amber-900">
                                    {{ __('Información General') }}
                                </x-dropdown-link>
                                
                                <!-- Excluimos al Coordinador de la acción de Matricular -->
                                @hasanyrole('Director|Subdirector|Gestor de Usuarios|Docente Guía')
                                <x-dropdown-link :href="route('academico.matriculas.index')" class="hover:bg-amber-50 hover:text-amber-900">
                                    {{ __('Matrícula') }}
                                </x-dropdown-link>
                                @endhasanyrole

                                <x-dropdown-link href="#" class="text-gray-400 hover:bg-gray-50">
                                    {{ __('Historial de Calificaciones') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endhasanyrole

                    <!-- Aulas y Horarios -->
                    @hasanyrole('Director|Subdirector|Gestor de Usuarios|Coordinador')
                    <div class="hidden sm:flex sm:items-center">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-amber-800 hover:border-amber-300 focus:outline-none transition duration-150 ease-in-out h-16">
                                    <div>Aulas y Horarios</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('academico.aulas.index')" class="hover:bg-amber-50 hover:text-amber-900">
                                    {{ __('Gestión de Aulas') }}
                                </x-dropdown-link>
                                @hasanyrole('Director|Subdirector|Gestor de Usuarios')
                                <x-dropdown-link :href="route('academico.malla.index')" class="hover:bg-amber-50 hover:text-amber-900">
                                    {{ __('Malla Curricular') }}
                                </x-dropdown-link>
                                @endhasanyrole
                                <x-dropdown-link :href="route('academico.bloques.index')" class="hover:bg-amber-50 hover:text-amber-900">
                                    {{ __('Horario General') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endhasanyrole

                    @can('notas.ver')
                        <x-nav-link :href="route('academico.notas.index')" :active="request()->routeIs('academico.notas.*')" class="hover:text-amber-800">Calificaciones</x-nav-link>
                    @endcan
                    @can('asistencia.ver')
                        <x-nav-link :href="'#'" :active="false" class="hover:text-amber-800">Asistencia</x-nav-link>
                    @endcan
                    @hasanyrole('Director|Subdirector|Coordinador')
                        <x-nav-link :href="'#'" :active="false" class="hover:text-amber-800">Reportes</x-nav-link>
                    @endhasanyrole
                </div>
            </div>

            <!-- Menú de Usuario a la derecha -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3.5 py-2 border border-amber-200 text-sm leading-4 font-bold rounded-lg text-amber-900 bg-amber-50/60 hover:bg-amber-100 hover:text-amber-950 focus:outline-none transition ease-in-out duration-150 shadow-sm gap-2">
                            <span>{{ Auth::user()->nombre_completo ?? Auth::user()->name ?? 'Usuario' }}</span>
                            <svg class="fill-current h-4 w-4 text-amber-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')" class="hover:bg-amber-50 hover:text-amber-900">
                            {{ __('Mi Perfil') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 font-semibold hover:bg-red-50">
                                {{ __('Cerrar Sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-amber-800 hover:text-amber-900 hover:bg-amber-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- ENLACES DE NAVEGACIÓN -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-6 space-y-2 custom-scrollbar">
        
        <a href="{{ route('dashboard') }}" title="Panel Principal" class="flex items-center gap-3 px-4 mx-3 py-3 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-[#e6ac27]/10 text-[#e6ac27] font-bold border border-[#e6ac27]/20' : 'text-slate-600 hover:bg-slate-100 hover:text-[#3d2c1d] font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span x-show="sidebarOpen" class="whitespace-nowrap">Panel Principal</span>
        </a>

        @can('configuracion.ver')
        <div class="pt-4 pb-1">
            <p x-show="sidebarOpen" class="px-4 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 whitespace-nowrap">Administración</p>
            <div x-show="!sidebarOpen" class="w-6 h-px bg-[#e6ac27]/30 mx-auto my-3"></div>
            
            <a href="{{ route('academico.usuarios.index') }}" title="Personal y Docentes" class="flex items-center gap-3 px-4 mx-3 py-3 rounded-xl transition-all {{ request()->routeIs('academico.usuarios.*') ? 'bg-[#e6ac27]/10 text-[#e6ac27] font-bold border border-[#e6ac27]/20' : 'text-slate-600 hover:bg-slate-100 hover:text-[#3d2c1d] font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Personal y Docentes</span>
            </a>
        </div>
        @endcan

        @can('alumnos.ver')
        <div class="pt-4 pb-1">
            <p x-show="sidebarOpen" class="px-4 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 whitespace-nowrap">Académico</p>
            <div x-show="!sidebarOpen" class="w-6 h-px bg-[#e6ac27]/30 mx-auto my-3"></div>
            
            <a href="{{ route('academico.alumnos.index') }}" title="Directorio Alumnos" class="flex items-center gap-3 px-4 mx-3 py-3 rounded-xl transition-all {{ request()->routeIs('academico.alumnos.*') ? 'bg-[#e6ac27]/10 text-[#e6ac27] font-bold border border-[#e6ac27]/20' : 'text-slate-600 hover:bg-slate-100 hover:text-[#3d2c1d] font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Directorio Alumnos</span>
            </a>
            <a href="{{ route('academico.matriculas.index') }}" title="Matrículas" class="flex items-center gap-3 px-4 mx-3 py-3 rounded-xl transition-all {{ request()->routeIs('academico.matriculas.*') ? 'bg-[#e6ac27]/10 text-[#e6ac27] font-bold border border-[#e6ac27]/20' : 'text-slate-600 hover:bg-slate-100 hover:text-[#3d2c1d] font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Matrículas</span>
            </a>
        </div>
        @endcan

        @can('aulas.ver')
        <div class="pt-4 pb-1">
            <p x-show="sidebarOpen" class="px-4 text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 whitespace-nowrap">Aulas y Horarios</p>
            <div x-show="!sidebarOpen" class="w-6 h-px bg-[#e6ac27]/30 mx-auto my-3"></div>
            
            <a href="{{ route('academico.aulas.index') }}" title="Gestión de Aulas" class="flex items-center gap-3 px-4 mx-3 py-3 rounded-xl transition-all {{ request()->routeIs('academico.aulas.*') ? 'bg-[#e6ac27]/10 text-[#e6ac27] font-bold border border-[#e6ac27]/20' : 'text-slate-600 hover:bg-slate-100 hover:text-[#3d2c1d] font-medium' }}" :class="sidebarOpen ? 'justify-start' : 'justify-center lg:mx-2 lg:px-0'">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Gestión de Aulas</span>
            </a>
        </div>
        @endcan

    </nav>
    
    <!-- Botón para cerrar menú en celulares (Oculto en PC) -->
    <div class="p-4 border-t border-[#e6ac27]/20 shrink-0 lg:hidden">
        <button @click="sidebarOpen = false" class="flex justify-center items-center gap-2 w-full px-4 py-3 rounded-xl bg-slate-100 text-slate-600 font-bold hover:bg-slate-200 transition-colors">
            Cerrar Menú
        </button>
    </div>
</aside>