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
                            {{ __('Usuarios') }}
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
                        <x-nav-link :href="'#'" :active="false" class="hover:text-amber-800">Calificaciones</x-nav-link>
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

    <!-- Menú Móvil -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-amber-50/90 border-t border-amber-200">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-amber-900 font-bold">
                {{ __('Panel Principal') }}
            </x-responsive-nav-link>

            @hasanyrole('Director|Gestor de Usuarios')
                <x-responsive-nav-link :href="route('academico.usuarios.index')">Usuarios</x-responsive-nav-link>
            @endhasanyrole

            @hasanyrole('Director|Subdirector|Gestor de Usuarios|Coordinador|Docente Guía')
                <x-responsive-nav-link :href="route('academico.alumnos.index')">Directorio Alumnos</x-responsive-nav-link>
            @endhasanyrole

            @hasanyrole('Director|Subdirector|Gestor de Usuarios|Docente Guía')
                <x-responsive-nav-link :href="route('academico.matriculas.index')">Matrículas</x-responsive-nav-link>
            @endhasanyrole

            @hasanyrole('Director|Subdirector|Gestor de Usuarios|Coordinador')
                <x-responsive-nav-link :href="route('academico.aulas.index')">Gestión de Aulas</x-responsive-nav-link>
            @endhasanyrole
        </div>

        <div class="pt-4 pb-1 border-t border-amber-200 px-4">
            <div class="font-bold text-base text-amber-900">{{ Auth::user()->nombre_completo ?? 'Usuario' }}</div>
            <div class="font-medium text-sm text-amber-700">{{ Auth::user()->email }}</div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Mi Perfil') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 font-bold">
                        {{ __('Cerrar Sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>