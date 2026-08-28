<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

@php
    $hora = now()->timezone('America/Managua')->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $rolPorDefecto = auth()->user()->roles->first()->name ?? 'Usuario';
    $nombreLimpio = explode(' ', trim(Auth::user()->nombre_completo ?? Auth::user()->name))[0];
    
    $ultimoAvisoId = $avisos->first()->id ?? 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="relative flex justify-between items-center w-full min-h-[2rem]">
            
            <div class="flex items-center gap-3 relative z-10">
                <h2 class="text-xl font-black text-[#3d2c1d] tracking-tight">
                    {{ $saludo }}, <span class="text-[#e6ac27]">{{ $nombreLimpio }}</span>
                </h2>
                <span class="hidden sm:inline-flex px-2.5 py-1 rounded-md bg-slate-200/50 text-slate-600 font-black text-[10px] uppercase tracking-widest border border-slate-300/50">
                    {{ Auth::user()->roles->pluck('name')->first() ?? 'Miembro' }}
                </span>
            </div>
            
            <h2 class="absolute left-1/2 transform -translate-x-1/2 font-black text-lg text-[#3d2c1d] uppercase tracking-widest hidden md:block">
                Panel Principal
            </h2>
            
            <div class="relative z-10" x-data="{ 
                openAvisos: false, 
                ultimoVisto: localStorage.getItem('avisoVisto_{{ Auth::id() }}') || 0, 
                avisoActual: {{ $ultimoAvisoId }} 
            }">
                <button @click="openAvisos = !openAvisos; localStorage.setItem('avisoVisto_{{ Auth::id() }}', avisoActual); ultimoVisto = avisoActual;" 
                        @click.outside="openAvisos = false" 
                        class="relative p-2 text-slate-400 hover:text-[#e6ac27] transition-colors rounded-full hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    
                    <span x-show="avisoActual > ultimoVisto" style="display: none;" class="absolute top-1 right-2 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                    </span>
                </button>
                
                <div x-show="openAvisos" x-transition class="absolute right-0 mt-3 w-96 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 overflow-hidden" style="display: none;">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                        <span class="text-xs font-black text-slate-800 uppercase tracking-widest">Comunicados</span>
                        @hasanyrole('Director|Subdirector')
                            <button @click="$dispatch('abrir-modal-aviso'); openAvisos = false;" class="text-xs font-bold text-[#e6ac27] hover:text-amber-600 transition-colors">+ Nuevo</button>
                        @endhasanyrole
                    </div>
                    <div class="max-h-96 overflow-y-auto p-5 space-y-5">
                        @forelse ($avisos as $aviso)
                            <div class="pb-5 border-b border-slate-50 last:border-0 last:pb-0">
                                <div class="flex justify-between items-start gap-4">
                                    <h4 class="text-sm font-bold text-slate-800 leading-tight">{{ $aviso->titulo }}</h4>
                                    
                                    @hasanyrole('Director|Subdirector')
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button @click="$dispatch('abrir-modal-editar-aviso', { 
                                                    id: '{{ $aviso->id }}', 
                                                    titulo: '{{ addslashes($aviso->titulo) }}', 
                                                    mensaje: '{{ addslashes(str_replace(["\r", "\n"], ['\r', '\n'], $aviso->mensaje)) }}' 
                                                }); openAvisos = false;" 
                                                class="text-amber-500 hover:text-amber-700 transition-colors" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <form action="{{ url('/dashboard/avisos/' . $aviso->id) }}" method="POST" class="inline form-eliminar-aviso">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-400 hover:text-rose-600 transition-colors" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                    @endhasanyrole
                                </div>
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed whitespace-pre-line">{{ $aviso->mensaje }}</p>
                                <div class="flex items-center gap-2 mt-3 text-[10px] text-slate-400 font-bold uppercase">
                                    <span>{{ $aviso->autor_nombre }}</span>
                                    <span>&bull;</span>
                                    <span>{{ \Carbon\Carbon::parse($aviso->created_at)->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-slate-400">
                                <svg class="w-8 h-8 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-xs">No hay comunicados recientes.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6 min-h-screen relative bg-slate-50" x-data="{ rolActivo: '{{ $rolPorDefecto }}' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @hasanyrole('Director|Subdirector|Gestor de Usuarios')
            <div x-show="['Director', 'Subdirector', 'Gestor de Usuarios'].includes(rolActivo)" class="flex flex-col sm:flex-row gap-4 mb-2">
                <div class="w-full sm:w-64 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Alumnos</p>
                        <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $totalAlumnos ?? 0 }}</h4>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
                <div class="w-full sm:w-64 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Docentes</p>
                        <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $totalDocentes ?? 0 }}</h4>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
            </div>
            @endhasanyrole

            <div class="flex space-x-2 border-b border-slate-200 overflow-x-auto pb-px">
                @foreach(auth()->user()->roles as $rol)
                    <button @click="rolActivo = '{{ $rol->name }}'"
                            :class="rolActivo === '{{ $rol->name }}' ? 'border-[#e6ac27] text-[#3d2c1d] font-black bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-bold'"
                            class="px-6 py-3 border-b-4 text-sm transition-all whitespace-nowrap rounded-t-xl">
                        Módulo: {{ $rol->name }}
                    </button>
                @endforeach
            </div>

            <div>
                @hasanyrole('Director|Subdirector|Gestor de Usuarios')
                <div x-show="['Director', 'Subdirector', 'Gestor de Usuarios'].includes(rolActivo)" x-transition.opacity style="display: none;" class="space-y-6">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ route('academico.usuarios.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <div>
                                <span class="block font-black text-sm text-[#3d2c1d]">Personal</span>
                                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Directorio</span>
                            </div>
                        </a>

                        <a href="{{ route('academico.aulas.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <div>
                                <span class="block font-black text-sm text-[#3d2c1d]">Aulas</span>
                                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Asignaciones</span>
                            </div>
                        </a>

                        <button @click.prevent="$dispatch('abrir-modal-horarios')" class="text-left group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer w-full">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <span class="block font-black text-sm text-[#3d2c1d]">Horarios</span>
                                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Visor Global</span>
                            </div>
                        </button>

                        <a href="{{ route('academico.alumnos.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <span class="block font-black text-sm text-[#3d2c1d]">Alumnos</span>
                                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Directorio</span>
                            </div>
                        </a>

                        <a href="{{ route('academico.matriculas.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <div>
                                <span class="block font-black text-sm text-[#3d2c1d]">Matrículas</span>
                                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Historial General</span>
                            </div>
                        </a>
                        
                        <!-- NUEVA TARJETA: CENTRO DE SUPERVISIÓN DE CALIFICACIONES -->
                        <a href="{{ route('academico.notas.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <div>
                                <span class="block font-black text-sm text-[#3d2c1d]">Calificaciones</span>
                                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Centro de Supervisión</span>
                            </div>
                        </a>

                        <button @click.prevent="$dispatch('abrir-modal-asistencia')" class="text-left group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4 cursor-pointer w-full">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <span class="block font-black text-sm text-[#3d2c1d]">Asistencia</span>
                                <span class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Monitoreo Global</span>
                            </div>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Rendimiento Promedio</h4>
                            <div class="relative h-48 w-full"><canvas id="chartRendimiento"></canvas></div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Asistencia Estudiantes</h4>
                            <div class="relative h-48 w-full"><canvas id="chartAsistenciaAlumnos"></canvas></div>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Asistencia Docentes</h4>
                            <div class="relative h-48 w-full"><canvas id="chartAsistenciaDocentes"></canvas></div>
                        </div>
                    </div>
                </div>
                @endhasanyrole

                <!-- MÓDULOS DE DOCENTES -->
                @hasanyrole('Docente Guía')
                <div x-show="rolActivo === 'Docente Guía'" x-transition.opacity style="display: none;" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a href="{{ route('academico.alumnos.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div>
                                <span class="block font-bold text-slate-800">Mis Estudiantes</span>
                                <span class="block text-xs font-medium text-slate-500">Listado y perfiles del aula</span>
                            </div>
                        </a>
                        <a href="{{ route('academico.asistencia.aula.create') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <span class="block font-bold text-slate-800">Asistencia</span>
                                <span class="block text-xs font-medium text-slate-500">Pase de lista diario</span>
                            </div>
                        </a>
                    </div>
                </div>
                @endhasanyrole

                @hasanyrole('Docente por Asignatura')
                <div x-show="rolActivo === 'Docente por Asignatura'" x-transition.opacity style="display: none;" class="space-y-6">
                    @include('components.dashboard.asignatura-stats')
                    <div class="mt-8 mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-black text-[#3d2c1d]">Mi Agenda Semanal</h3>
                        <p class="text-xs font-bold text-slate-400">Haz clic en una clase para gestionarla</p>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                        @foreach($diasSemana as $dia)
                            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full">
                                <div class="bg-[#FFFDF5] text-center py-3 border-b border-[#e6ac27]/20">
                                    <h4 class="font-black text-[#3d2c1d] uppercase tracking-widest text-sm">{{ $dia }}</h4>
                                </div>
                                <div class="p-4 flex-1 flex flex-col gap-3 bg-slate-50/30">
                                    @if(isset($horarios[$dia]) && $horarios[$dia]->count() > 0)
                                        @foreach($horarios[$dia] as $item)
                                            @php
                                                $modalidad = strtolower($item->aulaAsignaturaDocente->aula->modalidad->nombre ?? '');
                                                $estilosCaja = match(true) {
                                                    str_contains($modalidad, 'preescolar') => 'bg-amber-50 border-amber-200 text-amber-900',
                                                    str_contains($modalidad, 'primaria')   => 'bg-blue-50 border-blue-200 text-blue-900',
                                                    str_contains($modalidad, 'secundaria') => 'bg-emerald-50 border-emerald-200 text-emerald-900',
                                                    default => 'bg-white border-slate-200 text-slate-800'
                                                };
                                                $horaTexto = \Carbon\Carbon::parse($item->bloqueHorario->hora_inicio)->format('h:i') . ' - ' . \Carbon\Carbon::parse($item->bloqueHorario->hora_fin)->format('h:i A');
                                            @endphp
                                            <div x-on:click="$dispatch('abrir-modal-decision-clase', { id: '{{ $item->aulaAsignaturaDocente->id }}', asignatura: '{{ addslashes($item->aulaAsignaturaDocente->asignatura->nombre) }}', aula: '{{ addslashes($item->aulaAsignaturaDocente->aula->grado->nombre . ' - ' . $item->aulaAsignaturaDocente->aula->nombre) }}', hora: '{{ $horaTexto }}' })" class="border rounded-2xl p-3 shadow-sm hover:-translate-y-1 cursor-pointer {{ $estilosCaja }}">
                                                <h5 class="font-black text-sm leading-tight mb-1">{{ $item->aulaAsignaturaDocente->asignatura->nombre }}</h5>
                                                <p class="text-[11px] font-bold opacity-80 uppercase">{{ $item->aulaAsignaturaDocente->aula->grado->nombre }}</p>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="h-full flex flex-col items-center justify-center text-center opacity-50 py-4">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Libre</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endhasanyrole
            </div>
        </div>
    </div>

    <!-- MODALES GLOBALES -->
    <div x-data="{ open: false }" @abrir-modal-horarios.window="open = true" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open" x-transition.scale class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-[#3d2c1d]">Consulta de Horarios</h3>
                <button @click="open = false" class="text-slate-400 hover:text-rose-500 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="p-6 grid gap-4">
                <a href="{{ url('academico/visor-horarios/aulas') }}" class="group flex items-center p-4 border border-slate-200 rounded-2xl hover:border-[#e6ac27] hover:bg-[#FFFDF5] transition-all">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] group-hover:bg-[#e6ac27] group-hover:text-white flex items-center justify-center mr-4 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <span class="block font-black text-[#3d2c1d]">Horarios de Aulas</span>
                        <span class="block text-xs text-slate-500 font-medium mt-0.5">Por grado y sección</span>
                    </div>
                </a>
                <a href="{{ url('academico/visor-horarios/docentes') }}" class="group flex items-center p-4 border border-slate-200 rounded-2xl hover:border-[#e6ac27] hover:bg-[#FFFDF5] transition-all">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] group-hover:bg-[#e6ac27] group-hover:text-white flex items-center justify-center mr-4 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <span class="block font-black text-[#3d2c1d]">Horarios de Docentes</span>
                        <span class="block text-xs text-slate-500 font-medium mt-0.5">Carga horaria individual</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div x-data="{ open: false }" @abrir-modal-asistencia.window="open = true" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open" x-transition.scale class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-[#3d2c1d]">Supervisión de Asistencia</h3>
                <button @click="open = false" class="text-slate-400 hover:text-rose-500 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <div class="p-6 grid gap-4">
                <a href="{{ route('academico.asistencia.aula.create') }}" class="group flex items-center p-4 border border-slate-200 rounded-2xl hover:border-[#e6ac27] hover:bg-[#FFFDF5] transition-all">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] group-hover:bg-[#e6ac27] group-hover:text-white flex items-center justify-center mr-4 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block font-black text-[#3d2c1d]">Asistencia de Aulas</span>
                        <span class="block text-xs text-slate-500 font-medium mt-0.5">Control de estudiantes</span>
                    </div>
                </a>
                <a href="#" class="group flex items-center p-4 border border-slate-200 rounded-2xl hover:border-[#e6ac27] hover:bg-[#FFFDF5] transition-all">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 text-[#e6ac27] group-hover:bg-[#e6ac27] group-hover:text-white flex items-center justify-center mr-4 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <span class="block font-black text-[#3d2c1d]">Asistencia de Docentes</span>
                        <span class="block text-xs text-slate-500 font-medium mt-0.5">Control del personal</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div x-data="{ open: false }" @abrir-modal-aviso.window="open = true" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open" x-transition.scale class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-[#3d2c1d]">Nuevo Comunicado</h3>
                <button @click="open = false" class="text-slate-400 hover:text-rose-500 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form action="{{ url('/dashboard/avisos') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Título</label>
                        <input type="text" name="titulo" required class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Mensaje</label>
                        <textarea name="mensaje" rows="4" required class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-5 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-[#3d2c1d] hover:bg-slate-800 text-white text-sm font-black rounded-xl shadow-md transition-all transform hover:-translate-y-0.5">Publicar</button>
                </div>
            </form>
        </div>
    </div>

    <div x-data="{ open: false, id: '', titulo: '', mensaje: '' }" 
         @abrir-modal-editar-aviso.window="open = true; id = $event.detail.id; titulo = $event.detail.titulo; mensaje = $event.detail.mensaje;" 
         x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open" x-transition.scale class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200">
            <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="text-lg font-black text-[#3d2c1d]">Editar Comunicado</h3>
                <button @click="open = false" class="text-slate-400 hover:text-rose-500 transition-colors"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form x-bind:action="'/dashboard/avisos/' + id" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Título</label>
                        <input type="text" name="titulo" x-model="titulo" required class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Mensaje</label>
                        <textarea name="mensaje" x-model="mensaje" rows="4" required class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-5 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-black rounded-xl shadow-md transition-all transform hover:-translate-y-0.5">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <div x-data="{ open: false, asignacionId: '', asignaturaInfo: '', aulaInfo: '', horaInfo: '' }" 
         @abrir-modal-decision-clase.window="
            open = true;
            asignacionId = $event.detail.id; 
            asignaturaInfo = $event.detail.asignatura; 
            aulaInfo = $event.detail.aula;
            horaInfo = $event.detail.hora;
         " x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="open = false"></div>
        <div x-show="open" x-transition.scale class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-[#e6ac27]/10 flex items-center justify-center text-[#e6ac27]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                </div>
                <div>
                    <h2 class="text-lg font-black text-[#3d2c1d] leading-tight" x-text="asignaturaInfo"></h2>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-0.5" x-text="aulaInfo + ' | ' + horaInfo"></p>
                </div>
            </div>
            
            <div class="p-8 bg-white">
                <h3 class="text-center font-bold text-slate-600 mb-6">¿Qué deseas gestionar para esta clase?</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a x-bind:href="'{{ url('academico/asistencia/asignatura') }}/' + asignacionId + '/create'" class="flex flex-col items-center justify-center gap-3 p-5 rounded-2xl border border-[#e6ac27]/20 bg-[#FFFDF5] text-[#3d2c1d] hover:bg-[#e6ac27] hover:text-white transition-all transform hover:-translate-y-1 shadow-sm group">
                        <svg class="w-8 h-8 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span class="font-black uppercase tracking-widest text-[11px] text-center">Pasar<br>Asistencia</span>
                    </a>
                    <a x-bind:href="'{{ url('academico/notas/actividades') }}/' + asignacionId" class="flex flex-col items-center justify-center gap-3 p-5 rounded-2xl border border-[#e6ac27]/20 bg-[#FFFDF5] text-[#3d2c1d] hover:bg-[#e6ac27] hover:text-white transition-all transform hover:-translate-y-1 shadow-sm group">
                        <svg class="w-8 h-8 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <span class="font-black uppercase tracking-widest text-[11px] text-center">Gestionar<br>Notas</span>
                    </a>
                </div>
            </div>
            <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 rounded-b-2xl text-center">
                <button type="button" @click="open = false" class="text-xs font-bold text-slate-400 hover:text-slate-800 transition-colors uppercase tracking-widest py-2">Cancelar y Cerrar</button>
            </div>
        </div>
    </div>

    @hasanyrole('Director|Subdirector|Gestor de Usuarios')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const labels = {!! json_encode($nombresModalidades ?? []) !!};
            
            const coloresModalidad = labels.map(label => {
                const nombre = label.toLowerCase();
                if (nombre.includes('preescolar')) return '#fbbf24'; 
                if (nombre.includes('primaria')) return '#60a5fa';   
                if (nombre.includes('secundaria')) return '#34d399'; 
                return '#cbd5e1'; 
            });

            const chartOptions = {
                responsive: true, maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#3d2c1d', padding: 12, cornerRadius: 8,
                        titleFont: { size: 13, family: "'Figtree', sans-serif" },
                        bodyFont: { size: 14, weight: 'bold', family: "'Figtree', sans-serif" },
                        callbacks: { label: context => ' Porcentaje: ' + context.parsed.y + '%' }
                    }
                },
                scales: { 
                    y: { beginAtZero: true, max: 100, border: {display: false}, grid: { borderDash: [4, 4], color: '#f1f5f9' }, ticks: { font: { family: "'Figtree', sans-serif" }, color: '#94a3b8', stepSize: 25 } },
                    x: { border: {display: false}, grid: { display: false }, ticks: { font: { family: "'Figtree', sans-serif", weight: 'bold' }, color: '#64748b' } }
                }
            };

            const crearGrafica = (id, data) => {
                const canvas = document.getElementById(id);
                if (canvas) {
                    new Chart(canvas, {
                        type: 'bar',
                        data: { 
                            labels: labels, 
                            datasets: [{ 
                                data: data, 
                                backgroundColor: coloresModalidad, 
                                borderRadius: 8, 
                                barPercentage: 0.5 
                            }] 
                        },
                        options: chartOptions
                    });
                }
            };

            crearGrafica('chartRendimiento', {!! json_encode($rendimientoData ?? []) !!});
            crearGrafica('chartAsistenciaAlumnos', {!! json_encode($asistenciaAlumnosData ?? []) !!});
            crearGrafica('chartAsistenciaDocentes', {!! json_encode($asistenciaDocentesData ?? []) !!});
        });
    </script>
    @endhasanyrole

    <!-- SWEETALERT2 PARA NOTIFICACIONES PREMIUM -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                    customClass: { popup: 'rounded-2xl shadow-lg border border-slate-100' },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: '{{ session("success") }}'
                });
            @endif

            const formsEliminar = document.querySelectorAll('.form-eliminar-aviso');
            formsEliminar.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: '¿Eliminar comunicado?',
                        text: "Esta acción no se puede deshacer.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444', 
                        cancelButtonColor: '#94a3b8', 
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200 shadow-xl' }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit(); 
                        }
                    });
                });
            });
        });
    </script>
</x-app-layout>