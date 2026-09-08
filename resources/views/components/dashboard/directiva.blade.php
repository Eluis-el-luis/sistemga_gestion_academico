<!-- Métricas Rápidas -->
<div class="flex flex-col sm:flex-row gap-4 mb-6">
    <div class="w-full sm:w-64 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Alumnos Activos</p>
            <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $totalMatriculados ?? 0 }}</h4>
        </div>
        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
    </div>
    <div class="w-full sm:w-64 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Docentes</p>
            <h4 class="text-2xl font-black text-[#3d2c1d]">{{ $totalPersonal ?? 0 }}</h4>
        </div>
        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        </div>
    </div>
</div>

<!-- Accesos Directos -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
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

<!-- VISOR DINÁMICO DE ESTADÍSTICAS (Actualizado a Datos Reales) -->
<div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm" x-data="panelEstadisticas()">
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-8">
        
        <!-- Seleccionador 1: Tipo de Dato -->
        <div class="w-full md:w-2/3">
            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">1. Indicador a Analizar</label>
            <select x-model="indicador" class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm font-bold cursor-pointer">
                <option value="matriculados">Alumnos Matriculados Activos</option>
                <option value="asistencia_alumnos">Promedio de asistencia de alumnos</option>
                <option value="asistencia_docentes">Rendimiento de asistencia de docentes</option>
                <option value="rendimiento_modalidad">Rendimiento académico por modalidad</option>
                <option value="apoyo_padres">Nivel de participación de padres de familia</option>
                <option value="aprobados">Promedio de alumnos aprobados limpios</option>
                <option value="reprobados_leves">Promedio de reprobados (1 a 2 asignaturas)</option>
                <option value="reprobados_graves">Promedio de reprobados (3 a más asignaturas)</option>
                <option value="promedio_notas">Promedio general de calificaciones</option>
                <option value="avances_silabo">Avance de contenido programático (Docentes)</option>
                <option value="retencion">Tasa de Retención Estudiantil</option>
                <option value="puntualidad">Índice de Puntualidad Diaria</option>
            </select>
        </div>

        <!-- Seleccionador 2: Tipo de Gráfica -->
        <div class="w-full md:w-1/3">
            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">2. Tipo de Gráfica</label>
            <select x-model="tipoGrafica" class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm font-bold cursor-pointer">
                <option value="bar">Gráfica de Barras</option>
                <option value="line">Gráfica de Líneas</option>
                <option value="pie">Gráfica de Pastel (Pie)</option>
                <option value="doughnut">Gráfica Circular (Doughnut)</option>
                <option value="polarArea">Área Polar (Dispersión alternativa)</option>
            </select>
        </div>

    </div>

    <!-- Contenedor del Canvas 3 -->
    <div class="relative w-full h-[400px]">
        <canvas id="visorEstadisticasCanvas"></canvas>
    </div>
</div>

<!-- Lógica del Visor (Alpine.js + Chart.js) -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('panelEstadisticas', () => ({
        indicador: 'matriculados',
        tipoGrafica: 'bar',
        chartInstance: null,
        
        // RECIBIMOS LOS DATOS REALES DIRECTO DEL CONTROLADOR DE LARAVEL
        dbMetricas: {!! json_encode($dbMetricas ?? []) !!},

        init() {
            // Protección en caso de que dbMetricas venga vacío de Laravel
            if (!this.dbMetricas || Object.keys(this.dbMetricas).length === 0) {
                this.dbMetricas = {
                    matriculados: { titulo: 'Sin datos registrados', datos: [0, 0, 0] }
                };
            }

            this.$watch('indicador', () => this.dibujarGrafica());
            this.$watch('tipoGrafica', () => this.dibujarGrafica());
            
            setTimeout(() => this.dibujarGrafica(), 100);
        },

        dibujarGrafica() {
            const ctx = document.getElementById('visorEstadisticasCanvas');
            if (!ctx) return;

            if (this.chartInstance) {
                this.chartInstance.destroy();
            }

            // Datos Seguros
            const dataSeleccionada = this.dbMetricas[this.indicador] || { titulo: 'Dato no disponible', datos: [0, 0, 0] };
            const etiquetas = ['Preescolar', 'Primaria', 'Secundaria'];
            
            const coloresRelleno = ['#fbbf24', '#60a5fa', '#34d399'];
            const coloresBorde = ['#f59e0b', '#3b82f6', '#10b981'];

            const config = {
                type: this.tipoGrafica,
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: dataSeleccionada.titulo,
                        data: dataSeleccionada.datos,
                        backgroundColor: this.tipoGrafica === 'line' ? 'rgba(230, 172, 39, 0.15)' : coloresRelleno,
                        borderColor: this.tipoGrafica === 'line' ? '#e6ac27' : coloresBorde,
                        borderWidth: 2,
                        fill: this.tipoGrafica === 'line',
                        tension: 0.4,
                        borderRadius: this.tipoGrafica === 'bar' ? 8 : 0,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: ['pie', 'doughnut', 'polarArea'].includes(this.tipoGrafica),
                            position: 'bottom',
                            labels: { font: { family: "'Figtree', sans-serif", weight: 'bold' } }
                        },
                        tooltip: {
                            backgroundColor: '#3d2c1d',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 13, family: "'Figtree', sans-serif" },
                            bodyFont: { size: 14, weight: 'bold', family: "'Figtree', sans-serif" },
                            callbacks: { 
                                // Si es conteo de matriculados, no mostrar el signo "%"
                                label: context => ' Valor: ' + context.parsed.y + (this.indicador !== 'promedio_notas' && this.indicador !== 'matriculados' ? '%' : '') 
                            }
                        }
                    },
                    scales: ['pie', 'doughnut', 'polarArea'].includes(this.tipoGrafica) ? {} : {
                        y: { 
                            beginAtZero: true, 
                            border: { display: false }, 
                            grid: { borderDash: [4, 4], color: '#f1f5f9' }, 
                            ticks: { font: { family: "'Figtree', sans-serif" }, color: '#94a3b8' } 
                        },
                        x: { 
                            border: { display: false }, 
                            grid: { display: false }, 
                            ticks: { font: { family: "'Figtree', sans-serif", weight: 'bold' }, color: '#64748b' } 
                        }
                    }
                }
            };

            this.chartInstance = new Chart(ctx, config);
        }
    }));
});
</script>