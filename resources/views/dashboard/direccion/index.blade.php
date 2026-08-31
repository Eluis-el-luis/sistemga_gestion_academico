<x-app-layout>
    <!-- TOPBAR EXCLUSIVO: TÍTULO Y CAMPANA DE COMUNICADOS -->
    <x-slot name="header">
        <div class="relative flex justify-between items-center w-full min-h-[2rem]">
            <div class="w-10 hidden md:block"></div> <!-- Espaciador -->
            
            <h2 class="absolute left-1/2 transform -translate-x-1/2 font-black text-lg text-[#3d2c1d] uppercase tracking-widest hidden sm:block">
                Centro de Mando
            </h2>
            
            <!-- Campana de Avisos (Recuperada de tu diseño viejo) -->
            <div class="relative ml-auto" x-data="{ openAvisos: false, modalCrear: false, modalEditar: false, avisoEdit: {id:'', titulo:'', mensaje:''} }">
                <button @click="openAvisos = !openAvisos" @click.outside="openAvisos = false" class="relative p-2 text-slate-400 hover:text-[#e6ac27] transition-colors rounded-full hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    @if(isset($avisos) && $avisos->count() > 0)
                        <span class="absolute top-1 right-2 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                        </span>
                    @endif
                </button>
                
                <!-- Dropdown de Avisos -->
                <div x-show="openAvisos" x-transition class="absolute right-0 mt-3 w-80 md:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 overflow-hidden" style="display: none;">
                    <div class="px-5 py-4 border-b border-slate-100 bg-[#FFFDF5] flex justify-between items-center">
                        <span class="text-xs font-black text-[#3d2c1d] uppercase tracking-widest">Comunicados</span>
                        <button @click="$dispatch('abrir-modal-crear'); openAvisos = false" class="text-xs font-bold text-[#e6ac27] hover:text-amber-600 transition-colors">+ Nuevo</button>
                    </div>
                    <div class="max-h-80 overflow-y-auto p-5 space-y-5">
                        @forelse ($avisos ?? [] as $aviso)
                            <div class="group relative pb-5 border-b border-slate-50 last:border-0 last:pb-0">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <h4 class="text-sm font-bold text-slate-800">{{ $aviso->titulo }}</h4>
                                        <div class="flex items-center gap-2 mt-1 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                            <span>{{ $aviso->autor_nombre }}</span> &bull; <span>{{ \Carbon\Carbon::parse($aviso->created_at)->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                        <button @click="$dispatch('abrir-modal-editar', { id: '{{ $aviso->id }}', titulo: '{{ addslashes($aviso->titulo) }}', mensaje: '{{ addslashes(preg_replace("/\r\n/", "\n", $aviso->mensaje)) }}' }); openAvisos = false;" class="text-amber-500 hover:text-amber-700 transition-colors" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <form action="{{ url('/dashboard/avisos/' . $aviso->id) }}" method="POST" class="inline alerta-eliminar">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-rose-400 hover:text-rose-600 transition-colors" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed whitespace-pre-line">{{ Str::limit($aviso->mensaje, 100) }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">No hay comunicados recientes.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen relative" x-data="{ showTopBtn: false }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <x-dashboard.banner>
                Supervisa el rendimiento general del colegio, monitorea asistencias y accede rápidamente a los módulos de gestión operativa.
            </x-dashboard.banner>

            <!-- KPIs Y ESTADO DEL SISTEMA (Recuperado y mejorado) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex items-center gap-4 hover:border-[#e6ac27]/50 transition-colors">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Matrícula</p>
                        <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $matriculaActiva ?? 0 }}</h4>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 flex items-center gap-4 hover:border-[#e6ac27]/50 transition-colors">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Docentes</p>
                        <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $totalDocentes ?? 0 }}</h4>
                    </div>
                </div>

                <!-- Estado del Sistema Integrado a la Cuadrícula -->
                <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col justify-center relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-full bg-gradient-to-l from-[#e6ac27]/5 to-transparent pointer-events-none"></div>
                    <h3 class="text-[11px] font-black text-[#3d2c1d] uppercase tracking-widest border-b border-slate-100 pb-2 mb-3">Estado Operativo</h3>
                    <ul class="flex flex-col sm:flex-row gap-4 sm:gap-8 text-sm">
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-slate-500 font-medium">DB:</span> 
                            <span class="font-bold text-slate-800">PostgreSQL</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-slate-500 font-medium">Aulas:</span> 
                            <span class="font-bold text-slate-800">{{ $aulasEnCurso ?? 0 }} Activas</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- ACCESOS RÁPIDOS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <a href="{{ route('academico.usuarios.index') }}" class="group bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md hover:border-[#e6ac27]/50 transition-all">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="w-10 h-10 bg-[#FFFDF5] border border-[#e6ac27]/30 text-[#e6ac27] rounded-xl flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div><h4 class="font-black text-[#3d2c1d]">Personal</h4><span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Directorio</span></div>
                    </div>
                </a>

                <a href="{{ route('academico.aulas.index') }}" class="group bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md hover:border-[#e6ac27]/50 transition-all">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="w-10 h-10 bg-[#FFFDF5] border border-[#e6ac27]/30 text-[#e6ac27] rounded-xl flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div><h4 class="font-black text-[#3d2c1d]">Aulas</h4><span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asignaciones</span></div>
                    </div>
                </a>

                <a href="{{ route('academico.visor.index') }}" class="group bg-white p-5 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md hover:border-[#e6ac27]/50 transition-all">
                    <div class="flex items-center gap-4 mb-2">
                        <div class="w-10 h-10 bg-[#FFFDF5] border border-[#e6ac27]/30 text-[#e6ac27] rounded-xl flex items-center justify-center group-hover:bg-[#e6ac27] group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div><h4 class="font-black text-[#3d2c1d]">Horarios</h4><span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Visor Global</span></div>
                    </div>
                </a>
            </div>

            <!-- GRÁFICAS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                    <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Rendimiento Promedio</h4>
                    <div class="relative h-48 w-full"><canvas id="chartRendimiento"></canvas></div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                    <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Asistencia Estudiantes</h4>
                    <div class="relative h-48 w-full"><canvas id="chartAsistenciaAlumnos"></canvas></div>
                </div>
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
                    <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-4">Asistencia Docentes</h4>
                    <div class="relative h-48 w-full"><canvas id="chartAsistenciaDocentes"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALES DE AVISOS Y SCRIPTS DE GRÁFICAS -->
    <div x-data="{ open: false }" @abrir-modal-crear.window="open = true" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Contenido de tu modal original de crear avisos -->
    </div>
    
    <div x-data="{ open: false, id: '', titulo: '', mensaje: '' }" @abrir-modal-editar.window="open = true; id = $event.detail.id; titulo = $event.detail.titulo; mensaje = $event.detail.mensaje;" x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Contenido de tu modal original de editar avisos -->
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const labels = {!! json_encode($nombresModalidades ?? ['Preescolar', 'Primaria', 'Secundaria']) !!};
            const chartOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100, border: {display: false}, grid: { borderDash: [4, 4] } }, x: { grid: { display: false } } } };

            new Chart(document.getElementById('chartRendimiento'), { type: 'bar', data: { labels: labels, datasets: [{ data: {!! json_encode($rendimientoData ?? [0,0,0]) !!}, backgroundColor: ['#fbbf24', '#60a5fa', '#34d399'], borderRadius: 100, barPercentage: 0.5 }] }, options: chartOptions });
            new Chart(document.getElementById('chartAsistenciaAlumnos'), { type: 'line', data: { labels: labels, datasets: [{ data: {!! json_encode($asistenciaAlumnosData ?? [0,0,0]) !!}, borderColor: '#e6ac27', tension: 0.4, fill: false }] }, options: chartOptions });
            new Chart(document.getElementById('chartAsistenciaDocentes'), { type: 'line', data: { labels: labels, datasets: [{ data: {!! json_encode($asistenciaDocentesData ?? [0,0,0]) !!}, borderColor: '#8b5cf6', tension: 0.4, fill: false }] }, options: chartOptions });
        });
    </script>
</x-app-layout>