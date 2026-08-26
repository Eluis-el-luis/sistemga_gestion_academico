<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-xl text-[#3d2c1d] leading-tight text-center tracking-tight">
            {{ __('Panel Principal') }}
        </h2>
    </x-slot>

    @php
        $hora = now()->timezone('America/Managua')->hour;
        if ($hora < 12) $saludo = 'Buenos días';
        elseif ($hora < 18) $saludo = 'Buenas tardes';
        else $saludo = 'Buenas noches';
        
        $rolPorDefecto = auth()->user()->roles->first()->name ?? 'Usuario';
    @endphp

    <div class="py-8 min-h-screen relative bg-slate-50" 
         x-data="{ 
            rolActivo: '{{ $rolPorDefecto }}',
            modalCrear: false, 
            modalEditar: false, 
            avisoEdit: {id:'', titulo:'', mensaje:''} 
         }">
         
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center font-medium">
                    <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <!-- ZONA 1: BANNER DE BIENVENIDA DINÁMICO Y LIMPIO -->
            <div class="bg-white rounded-[2rem] p-8 md:p-10 shadow-sm border border-slate-200/80 relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-6 transition-all">
                
                <!-- Sutil destello rojo vino opaco en la esquina -->
                <div class="absolute top-0 right-0 -mt-16 -mr-16 w-72 h-72 bg-gradient-to-br from-rose-950/15 via-rose-900/5 to-transparent rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10">
                    @php
                        // Lógica precisa para el saludo según la hora local de Nicaragua
                        $hora = now()->timezone('America/Managua')->hour;
                        if ($hora < 12) {
                            $saludo = 'Buenos días';
                        } elseif ($hora < 18) {
                            $saludo = 'Buenas tardes';
                        } else {
                            $saludo = 'Buenas noches';
                        }

                        // Extraemos la primera palabra del nombre completo de forma segura
                        $nombreCompleto = Auth::user()->nombre_completo ?? Auth::user()->name ?? 'Usuario';
                        $nombreLimpio = explode(' ', trim($nombreCompleto))[0];
                    @endphp

                    <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight mb-2">
                        {{ $saludo }}, <span class="text-[#e6ac27]">{{ $nombreLimpio }}</span>
                    </h2>
                    <p class="text-slate-500 text-sm md:text-base font-medium max-w-xl leading-relaxed">
                        Bienvenido a tu espacio de trabajo institucional en el Sistema de Gestión Académica.
                    </p>
                </div>
                
                <!-- Insignia de Rol Elegante -->
                <div class="relative z-10 shrink-0 mt-4 md:mt-0">
                    <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-rose-50 text-rose-900 border border-rose-100/60 font-bold text-xs uppercase tracking-widest shadow-sm">
                        {{ Auth::user()->roles->pluck('name')->first() ?? 'Miembro' }}
                    </span>
                </div>
            </div>

            <!-- ZONA 3: SELECTOR DE TABS (PESTAÑAS POR ROL) -->
            <div class="flex space-x-2 border-b border-slate-200 overflow-x-auto pb-px">
                @foreach(auth()->user()->roles as $rol)
                    <button @click="rolActivo = '{{ $rol->name }}'"
                            :class="rolActivo === '{{ $rol->name }}' ? 'border-[#e6ac27] text-[#3d2c1d] font-black bg-white shadow-sm' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300 font-bold'"
                            class="px-6 py-3 border-b-4 text-sm transition-all whitespace-nowrap rounded-t-xl">
                        Módulo: {{ $rol->name }}
                    </button>
                @endforeach
            </div>

            <!-- GRID PRINCIPAL (ZONAS 4, 5, 6 Y 7) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-4">
                
                <!-- COLUMNA IZQUIERDA (Ocupa 2/3): MÓDULOS INTERACTIVOS -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- MÓDULO ADMINISTRATIVO (Director / Subdirector / Gestor) -->
                    @hasanyrole('Director|Subdirector|Gestor de Usuarios')
                    <div x-show="['Director', 'Subdirector', 'Gestor de Usuarios'].includes(rolActivo)" x-transition.opacity style="display: none;" class="space-y-6">
                        
                        <!-- Tarjetas de Métricas Reales desde PostgreSQL -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Estudiantes Activos</p>
                                    <h4 class="text-3xl font-black text-[#3d2c1d]">{{ $totalAlumnos ?? 0 }}</h4>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Docentes Registrados</p>
                                    <h4 class="text-3xl font-black text-[#3d2c1d]">{{ $totalDocentes ?? 0 }}</h4>
                                </div>
                                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfica de Rendimiento Académico -->
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                            <h4 class="text-sm font-black text-[#3d2c1d] uppercase tracking-widest mb-4">Rendimiento Académico Promedio</h4>
                            <div class="relative h-64 w-full">
                                <canvas id="matriculaChart"></canvas>
                            </div>
                        </div>

                        <!-- Accesos Rápidos Operativos -->
                        <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest pt-2">Gestión Operativa</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            <!-- Botón 1: Directorio -->
                            <a href="{{ route('academico.usuarios.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex flex-col gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-slate-800 leading-tight">Directorio Personal</span>
                                    <span class="block text-xs font-medium text-slate-400 mt-1">Accesos y roles</span>
                                </div>
                            </a>
                            <!-- Botón 2: Aulas -->
                            <a href="{{ route('academico.aulas.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex flex-col gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-slate-800 leading-tight">Gestión de Aulas</span>
                                    <span class="block text-xs font-medium text-slate-400 mt-1">Asignación y espacios</span>
                                </div>
                            </a>
                            <!-- Botón 3: Visor de Horarios (NUEVO) -->
                            <a href="{{ route('academico.visor.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-[#e6ac27] transition-all flex flex-col gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-slate-800 leading-tight">Visor de Horarios</span>
                                    <span class="block text-xs font-medium text-slate-400 mt-1">Consulta rápida</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- MÓDULO DOCENTE GUÍA -->
                    @hasanyrole('Docente Guía')
                    <div x-show="rolActivo === 'Docente Guía'" x-transition.opacity style="display: none;" class="space-y-4">
                        <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 pb-2">Tutoría y Aula Guía</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <a href="{{ route('academico.alumnos.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-blue-300 transition-all flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-slate-800">Mis Estudiantes</span>
                                    <span class="block text-xs font-medium text-slate-500">Listado y perfiles del aula</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- MÓDULO DOCENTE POR ASIGNATURA -->
                    @hasanyrole('Docente por Asignatura')
                    <div x-show="rolActivo === 'Docente por Asignatura'" x-transition.opacity style="display: none;" class="space-y-4">
                        <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 pb-2">Gestión Académica de Clases</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <a href="#" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-300 transition-all flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-slate-800">Calificaciones</span>
                                    <span class="block text-xs font-medium text-slate-500">Ingreso de acumulados</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endhasanyrole

                    <!-- MÓDULO COORDINADOR -->
                    @hasanyrole('Coordinador')
                    <div x-show="rolActivo === 'Coordinador'" x-transition.opacity style="display: none;" class="space-y-4">
                        <h4 class="text-sm font-black text-slate-400 uppercase tracking-widest border-b border-slate-200 pb-2">Supervisión Académica</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <a href="{{ route('academico.malla.index') }}" class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-300 transition-all flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <div>
                                    <span class="block font-bold text-slate-800">Malla Curricular</span>
                                    <span class="block text-xs font-medium text-slate-500">Planes de estudio</span>
                                </div>
                            </a>
                        </div>
                        <div class="mt-6">
                            @include('components.dashboard.asignatura-stats')
                        </div>
                    </div>
                    @endhasanyrole

                </div>

                <!-- COLUMNA DERECHA (Ocupa 1/3): TABLERO DE COMUNICADOS (ZONA 4) -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50 rounded-t-2xl">
                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                Comunicados Oficiales
                            </h3>
                            @hasanyrole('Director|Subdirector')
                            <button @click="modalCrear = true" class="text-slate-500 hover:text-[#e6ac27] transition-colors" title="Nuevo Aviso">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                            @endhasanyrole
                        </div>
                        <div class="p-6 space-y-6">
                            @forelse ($avisos ?? [] as $aviso)
                                <div class="group relative pb-6 border-b border-slate-100 last:border-0 last:pb-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900">{{ $aviso->titulo }}</h4>
                                            <div class="flex items-center gap-2 mt-1 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                                <span>{{ $aviso->autor_nombre }}</span>
                                                <span>&bull;</span>
                                                <span>{{ \Carbon\Carbon::parse($aviso->created_at)->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-slate-600 text-sm mt-3 leading-relaxed whitespace-pre-line">{{ $aviso->mensaje }}</p>
                                </div>
                            @empty
                                <div class="text-center py-6 text-slate-400">
                                    <p class="text-sm font-medium">No hay comunicados recientes.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Script de Chart.js para la Gráfica de Rendimiento -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('matriculaChart');
            if(ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Preescolar', 'Primaria', 'Secundaria'],
                        datasets: [{
                            label: 'Rendimiento Global (%)',
                            data: [88, 76, 82], 
                            // COLORES ACTUALIZADOS: Rosado (Preescolar), Azul (Primaria), Esmeralda (Secundaria)
                            backgroundColor: ['#f472b6', '#60a5fa', '#34d399'],
                            borderRadius: 6,
                            borderWidth: 0,
                            barPercentage: 0.55 
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#3d2c1d',
                                padding: 12,
                                titleFont: { size: 13, family: "'Figtree', sans-serif" },
                                bodyFont: { size: 14, weight: 'bold', family: "'Figtree', sans-serif" },
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        return ' Promedio: ' + context.parsed.y + '%';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true,
                                max: 100, 
                                grid: { borderDash: [4, 4], color: '#f1f5f9' },
                                ticks: { font: { family: "'Figtree', sans-serif" }, color: '#78716c' }
                            },
                            x: { 
                                grid: { display: false },
                                ticks: { font: { family: "'Figtree', sans-serif", weight: '600' }, color: '#3d2c1d' }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>