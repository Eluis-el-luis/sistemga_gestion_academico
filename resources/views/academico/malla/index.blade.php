<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                {{ __('Plantilla Oficial: Malla Curricular') }}
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Mensajes de Éxito o Error -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- SECCIÓN 1: FORMULARIO PARA AGREGAR A LA PLANTILLA -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                    <h3 class="text-lg font-black text-[#3d2c1d]">Agregar Asignatura a un Grado</h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Las materias que agregues aquí se clonarán automáticamente al aperturar una nueva aula de ese grado.</p>
                </div>
                
                <div class="p-8">
                    <form action="{{ route('academico.malla.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end" x-data="filtroMalla()">
                        @csrf
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Grado <span class="text-rose-500">*</span></label>
                            <select name="grado_id" x-model="gradoSeleccionado" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm transition-colors font-medium text-slate-700" required>
                                <option value="">Seleccione...</option>
                                @foreach($grados as $grado)
                                    <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-5">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Asignatura Oficial <span class="text-rose-500">*</span></label>
                            <select name="asignatura_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm transition-colors font-medium text-slate-700" required>
                                <option value="">Seleccione la Materia...</option>
                                <template x-for="asignatura in asignaturasFiltradas()" :key="asignatura.id">
                                    <option :value="asignatura.id" x-text="asignatura.nombre"></option>
                                </template>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5" title="Horas a la semana">Horas Sem. <span class="text-rose-500">*</span></label>
                            <input type="number" name="horas_semanales_sugeridas" min="1" max="40" value="4" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-black text-center text-[#3d2c1d] transition-colors" required>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-2 px-4 rounded-xl shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 text-sm flex items-center justify-center gap-2 h-[42px]">
                                <span>+ Añadir</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SECCIÓN 2: VISUALIZADOR Y GESTIÓN AVANZADA (Con Alpine.js) -->
            <div class="pt-4" x-data="{ modalidadFiltro: 'Todos', modalClonar: false }">
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <h3 class="text-xl font-black text-[#3d2c1d] flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Malla Curricular Configurada
                    </h3>
                    
                    <!-- BOTÓN CLONAR MALLA -->
                    <button @click="modalClonar = true" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-md transition-all flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                        Clonar Plantilla
                    </button>
                </div>
                
                <!-- PESTAÑAS DE FILTRADO DINÁMICO -->
                @php $modalidades = $grados->pluck('modalidad.nombre')->filter()->unique(); @endphp
                <div class="flex flex-wrap gap-2 mb-6 bg-white p-2 rounded-2xl border border-slate-200 w-fit shadow-sm">
                    <button @click="modalidadFiltro = 'Todos'" :class="modalidadFiltro === 'Todos' ? 'bg-[#e6ac27] text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Todos</button>
                    @foreach($modalidades as $mod)
                        <button @click="modalidadFiltro = '{{ $mod }}'" :class="modalidadFiltro === '{{ $mod }}' ? 'bg-[#e6ac27] text-white shadow-md' : 'text-slate-500 hover:bg-slate-50'" class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">{{ $mod }}</button>
                    @endforeach
                </div>

                <!-- CUADRÍCULA DE GRADOS -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($grados as $grado)
                        @php
                            $limiteHoras = $grado->horas_maximas_semanales ?? 35;
                            $horasUsadas = $grado->mallaCurricular->sum('horas_semanales_sugeridas');
                            $porcentaje = $limiteHoras > 0 ? ($horasUsadas / $limiteHoras) * 100 : 0;
                            $colorBarra = $porcentaje > 90 ? 'bg-rose-500' : 'bg-[#e6ac27]';
                            $estaLleno = $porcentaje >= 100;
                        @endphp

                        <!-- TARJETA CON FILTRO ALPINE -->
                        <div x-show="modalidadFiltro === 'Todos' || modalidadFiltro === '{{ $grado->modalidad->nombre ?? '' }}'" x-transition 
                             class="bg-white rounded-3xl shadow-sm border {{ $estaLleno ? 'border-rose-200' : 'border-slate-200' }} overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                            
                            <!-- Cabecera -->
                            <div class="bg-[#FFFDF5] px-6 py-5 border-b border-[#e6ac27]/20">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-black text-[#3d2c1d] text-lg">{{ $grado->nombre }}</h4>
                                        <span class="bg-slate-100 border border-slate-200 text-slate-600 px-2 py-0.5 rounded-lg text-[10px] uppercase tracking-widest font-black inline-block mt-1">{{ $grado->modalidad->nombre ?? 'N/A' }}</span>
                                    </div>
                                    <div class="text-right flex items-center justify-end gap-1">
                                        <span class="text-xl font-black {{ $estaLleno ? 'text-rose-600' : 'text-[#e6ac27]' }}">{{ $horasUsadas }}</span>
                                        
                                        <form action="{{ route('academico.malla.grado.horas', $grado->id) }}" method="POST" class="inline-block form-editar-horas">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="horas_maximas_semanales" class="input-horas-oculto">
                                            <button type="button" class="text-xs font-bold text-slate-400 hover:text-[#e6ac27] transition-colors cursor-pointer flex items-center gap-0.5 mt-1.5 btn-editar-horas" data-grado="{{ $grado->nombre }}" data-horas="{{ $limiteHoras }}" title="Editar límite de horas">
                                                / {{ $limiteHoras }}h
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full transition-all duration-500 {{ $colorBarra }}" style="width: {{ min(100, $porcentaje) }}%"></div>
                                </div>
                            </div>
                            
                            <!-- Lista de Materias -->
                            <div class="p-6 flex-grow">
                                @if($grado->mallaCurricular->count() > 0)
                                    <ul class="space-y-4">
                                        @foreach($grado->mallaCurricular as $item)
                                            <li class="flex justify-between items-center text-sm border-b border-slate-100 pb-4 last:border-0 last:pb-0 group">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-slate-700 group-hover:text-[#e6ac27] transition-colors">{{ $item->asignatura->nombre }}</span>
                                                    <span class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-1">{{ number_format($item->horas_semanales_sugeridas ?? 0, 0) }} horas / sem</span>
                                                </div>
                                                
                                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <!-- BOTÓN EDITAR MATERIA -->
                                                    <form action="{{ route('academico.malla.item.update', $item->id) }}" method="POST" class="form-editar-item">
                                                        @csrf @method('PUT')
                                                        <input type="hidden" name="horas_semanales_sugeridas" class="input-item-oculto">
                                                        <button type="button" class="text-slate-300 hover:text-amber-500 hover:bg-amber-50 p-2 rounded-xl transition-colors btn-editar-item" data-nombre="{{ $item->asignatura->nombre }}" data-horas="{{ $item->horas_semanales_sugeridas }}" title="Editar horas">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                        </button>
                                                    </form>

                                                    <!-- BOTÓN ELIMINAR -->
                                                    <form action="{{ route('academico.malla.destroy', $item->id) }}" method="POST" class="alerta-eliminar">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-slate-300 hover:text-rose-500 hover:bg-rose-50 p-2 rounded-xl transition-colors" title="Quitar de la malla">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="h-full flex flex-col items-center justify-center text-center py-8">
                                        <svg class="w-10 h-10 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-sm text-slate-400 font-bold">Plantilla vacía.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- MODAL DE CLONACIÓN -->
                <div x-show="modalClonar" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div x-show="modalClonar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalClonar = false"></div>
                    <div x-show="modalClonar" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                            <h3 class="text-lg font-black text-[#3d2c1d]">Clonar Plantilla</h3>
                            <button @click="modalClonar = false" class="text-slate-400 hover:text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                        </div>
                        <form action="{{ route('academico.malla.clonar') }}" method="POST" class="p-6 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Copiar malla de (Origen)</label>
                                <select name="origen_grado_id" required class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm">
                                    <option value="">Seleccione grado origen...</option>
                                    @foreach($grados as $grado)
                                        @if($grado->mallaCurricular->count() > 0)
                                            <option value="{{ $grado->id }}">{{ $grado->nombre }} ({{ $grado->mallaCurricular->count() }} materias)</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Pegar materias en (Destino)</label>
                                <select name="destino_grado_id" required class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm">
                                    <option value="">Seleccione grado destino...</option>
                                    @foreach($grados as $grado)
                                        <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="w-full mt-4 bg-slate-800 hover:bg-slate-700 text-white font-black py-3 rounded-xl shadow-md transition-all">Clonar Ahora</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('filtroMalla', () => ({
                gradoSeleccionado: '',
                todasAsignaturas: @json($asignaturas),
                materiasPorGrado: {
                    @foreach($grados as $grado)
                        "{{ $grado->id }}": @json($grado->mallaCurricular->pluck('asignatura_id')),
                    @endforeach
                },
                asignaturasFiltradas() {
                    if (!this.gradoSeleccionado) return this.todasAsignaturas; 
                    const materiasAsignadas = this.materiasPorGrado[this.gradoSeleccionado] || [];
                    return this.todasAsignaturas.filter(asig => !materiasAsignadas.includes(asig.id));
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Eliminar materia
            document.querySelectorAll('.alerta-eliminar').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Quitar de la Plantilla?',
                        text: "Esta materia dejará de clonarse en las nuevas aulas.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, Quitar', cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });

            // Editar límite de horas (Grado)
            document.querySelectorAll('.btn-editar-horas').forEach(boton => {
                boton.addEventListener('click', function () {
                    const gradoNombre = this.getAttribute('data-grado');
                    const horasActuales = this.getAttribute('data-horas');
                    const formulario = this.closest('form');
                    const inputOculto = formulario.querySelector('.input-horas-oculto');

                    Swal.fire({
                        title: 'Límite de Horas',
                        text: `Máximo de horas semanales para ${gradoNombre}`,
                        input: 'number', inputValue: horasActuales,
                        inputAttributes: { min: 1, max: 60, step: 1 },
                        showCancelButton: true,
                        confirmButtonColor: '#e6ac27', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Guardar', cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            inputOculto.value = result.value;
                            formulario.submit();
                        }
                    });
                });
            });

            // NUEVO: Editar horas de materia individual
            document.querySelectorAll('.btn-editar-item').forEach(boton => {
                boton.addEventListener('click', function () {
                    const materiaNombre = this.getAttribute('data-nombre');
                    const horasActuales = this.getAttribute('data-horas');
                    const formulario = this.closest('form');
                    const inputOculto = formulario.querySelector('.input-item-oculto');

                    Swal.fire({
                        title: materiaNombre,
                        text: 'Actualizar la cantidad de horas semanales.',
                        input: 'number', inputValue: horasActuales,
                        inputAttributes: { min: 1, max: 40, step: 1 },
                        showCancelButton: true,
                        confirmButtonColor: '#e6ac27', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Actualizar Horas', cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            inputOculto.value = result.value;
                            formulario.submit();
                        }
                    });
                });
            });
        });
    </script>
</x-app-layout>