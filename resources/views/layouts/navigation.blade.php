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