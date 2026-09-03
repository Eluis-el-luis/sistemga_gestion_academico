<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ __('Estructura de Horarios (Bloques)') }}
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8" x-data="gestorBloques()">
        
        <!-- ALERTS -->
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

        <!-- FORMULARIO INDIVIDUAL INTELIGENTE -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h3 class="text-lg font-black text-[#3d2c1d]">Añadir Bloque de Tiempo Oficial</h3>
                
                <!-- BOTONES DE ACCIÓN RÁPIDA -->
                <div class="flex gap-2 w-full md:w-auto">
                    <button @click="modalGenerar = true" class="flex-1 md:flex-none px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Generador Masivo
                    </button>
                    <button @click="modalClonar = true" class="flex-1 md:flex-none px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-md transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                        Clonar
                    </button>
                </div>
            </div>
            
            <div class="p-8">
                <form action="{{ route('academico.bloques.store') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                        <!-- Contexto -->
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Modalidad <span class="text-rose-500">*</span></label>
                            <select name="modalidad_id" x-model="form.modalidad_id" @change="actualizarSugerencia" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] text-sm font-medium text-slate-700" required>
                                <option value="">Seleccione...</option>
                                @foreach($modalidades as $mod)
                                    <option value="{{ $mod->id }}">{{ $mod->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Turno <span class="text-rose-500">*</span></label>
                            <select name="turno" x-model="form.turno" @change="actualizarSugerencia" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] text-sm font-medium text-slate-700" required>
                                <option value="Matutino">Matutino</option>
                                <option value="Vespertino">Vespertino</option>
                            </select>
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Tipo de Jornada <span class="text-rose-500">*</span></label>
                            <select name="tipo_jornada" x-model="form.tipo_jornada" @change="actualizarSugerencia" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] text-sm font-medium text-slate-700" required>
                                <option value="Regular">Regular</option>
                                <option value="Viernes">Viernes</option>
                                <option value="Corto 40m">Corto 40m</option>
                                <option value="Especial 30m">Especial 30m</option>
                            </select>
                        </div>

                        <div class="col-span-full border-t border-slate-100 my-2"></div>

                        <!-- Tiempos -->
                        <div class="md:col-span-2">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5" title="Se autocalcula solo">N° Bloque</label>
                            <input type="text" :disabled="!form.es_recreo" :value="form.es_recreo ? '' : 'Auto'" :placeholder="form.es_recreo ? '-' : 'Auto'" class="w-full border-slate-200 bg-slate-100/50 rounded-xl shadow-sm text-sm font-bold text-slate-400 text-center cursor-not-allowed">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Nombre del Bloque <span class="text-rose-500">*</span></label>
                            <input type="text" name="nombre" placeholder="Ej: 1ra Hora" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] text-sm font-bold text-[#3d2c1d]" required>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Hora Inicio <span class="text-rose-500">*</span></label>
                            <input type="time" name="hora_inicio" x-model="form.hora_inicio" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] text-sm font-black text-indigo-600" required>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Hora Fin <span class="text-rose-500">*</span></label>
                            <input type="time" name="hora_fin" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] text-sm font-black text-[#3d2c1d]" required>
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
                        <label class="inline-flex items-center cursor-pointer bg-amber-50 px-5 py-3 rounded-xl border border-amber-200/60 hover:bg-amber-100 transition-colors shadow-sm w-full md:w-auto">
                            <input type="hidden" name="es_recreo" value="0">
                            <input type="checkbox" name="es_recreo" value="1" x-model="form.es_recreo" class="rounded border-amber-300 text-amber-500 shadow-sm focus:ring-amber-500 w-5 h-5">
                            <span class="ml-3 text-[11px] font-black text-amber-700 uppercase tracking-widest">Marcar como Receso / Libre</span>
                        </label>
                        
                        <button type="submit" class="w-full md:w-auto bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-3 px-8 rounded-xl shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 text-sm flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                            Guardar Bloque Oficial
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO DE ESTRUCTURAS -->
        <div class="pt-4">
            <h3 class="text-xl font-black text-[#3d2c1d] mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Estructura Definida
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($modalidades as $modalidad)
                    @foreach(['Matutino', 'Vespertino'] as $turno)
                        @foreach(['Regular', 'Viernes', 'Corto 40m', 'Especial 30m'] as $jornada)
                            @php 
                                $bloquesFiltrados = $bloques->where('modalidad_id', $modalidad->id)->where('turno', $turno)->where('tipo_jornada', $jornada);
                            @endphp
                            
                            @if($bloquesFiltrados->count() > 0)
                                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                                    
                                    <!-- CABECERA CON BOTÓN ELIMINAR JORNADA -->
                                    <div class="bg-[#FFFDF5] px-6 py-5 border-b border-[#e6ac27]/20 flex justify-between items-start">
                                        <div>
                                            <h4 class="font-black text-[#3d2c1d] text-base tracking-tight">{{ $modalidad->nombre }}</h4>
                                            <span class="text-[11px] font-black text-slate-400 uppercase tracking-widest block mt-1">Jornada: {{ $jornada }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="bg-slate-100 border border-slate-200 text-slate-600 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $turno }}</span>
                                            
                                            <form action="{{ route('academico.bloques.jornada.destroy') }}" method="POST" class="alerta-eliminar-jornada m-0 inline-block">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="modalidad_id" value="{{ $modalidad->id }}">
                                                <input type="hidden" name="turno" value="{{ $turno }}">
                                                <input type="hidden" name="tipo_jornada" value="{{ $jornada }}">
                                                <button type="submit" class="p-1.5 text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Eliminar Jornada Completa">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="p-0">
                                        <ul class="divide-y divide-slate-100">
                                            @foreach($bloquesFiltrados as $bloque)
                                                <li class="flex justify-between items-center px-6 py-4 text-sm {{ $bloque->es_recreo ? 'bg-amber-50/40' : 'hover:bg-slate-50' }} transition-colors group">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-8 h-8 rounded-xl {{ $bloque->es_recreo ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        </div>
                                                        <div class="flex flex-col">
                                                            <span class="font-black text-base {{ $bloque->es_recreo ? 'text-amber-800' : 'text-slate-700' }}">
                                                                {{ $bloque->numero_bloque ? $bloque->numero_bloque.'° - ' : '' }}{{ $bloque->nombre }}
                                                            </span>
                                                            <span class="text-[11px] {{ $bloque->es_recreo ? 'text-amber-600' : 'text-slate-400' }} font-bold uppercase tracking-widest mt-1">
                                                                {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    
                                                    <form action="{{ route('academico.bloques.destroy', $bloque->id) }}" method="POST" class="alerta-eliminar inline-block m-0">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-slate-300 hover:text-rose-500 hover:bg-rose-50 p-2 rounded-xl transition-colors opacity-0 group-hover:opacity-100" title="Eliminar Bloque">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            </div>
        </div>

        <!-- MODAL: GENERADOR MASIVO -->
        <div x-show="modalGenerar" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="modalGenerar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalGenerar = false"></div>
            <div x-show="modalGenerar" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-indigo-50 flex justify-between items-center">
                    <h3 class="text-lg font-black text-indigo-900 flex items-center gap-2"><svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg> Generador Masivo</h3>
                    <button @click="modalGenerar = false" class="text-indigo-400 hover:text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <form action="{{ route('academico.bloques.generar-masivo') }}" method="POST" class="p-6">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Modalidad</label>
                            <select name="modalidad_id" class="w-full border-slate-200 bg-slate-50 rounded-xl text-sm" required>
                                @foreach($modalidades as $mod) <option value="{{ $mod->id }}">{{ $mod->nombre }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Turno</label>
                            <select name="turno" class="w-full border-slate-200 bg-slate-50 rounded-xl text-sm" required>
                                <option value="Matutino">Matutino</option><option value="Vespertino">Vespertino</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Tipo de Jornada</label>
                            <select name="tipo_jornada" class="w-full border-slate-200 bg-slate-50 rounded-xl text-sm" required>
                                <option value="Regular">Regular</option><option value="Viernes">Viernes</option>
                            </select>
                        </div>
                        
                        <div class="col-span-full border-t border-slate-100 my-1"></div>
                        
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Hora Inicio Base</label>
                            <input type="time" name="hora_inicio_base" value="07:00" class="w-full border-slate-200 bg-slate-50 rounded-xl text-sm font-bold" required>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Cant. de Bloques</label>
                            <input type="number" name="cantidad_bloques" value="6" min="1" max="12" class="w-full border-slate-200 bg-slate-50 rounded-xl text-sm font-bold" required>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Duración Clase (min)</label>
                            <input type="number" name="duracion_clase" value="45" min="15" max="90" class="w-full border-slate-200 bg-slate-50 rounded-xl text-sm font-bold" required>
                        </div>
                        <div class="col-span-2 md:col-span-1 border border-amber-200 bg-amber-50 rounded-xl p-3">
                            <label class="block text-[11px] font-black text-amber-700 uppercase tracking-widest mb-1.5">Receso antes del bloque:</label>
                            <div class="flex gap-2">
                                <input type="number" name="posicion_receso" placeholder="N°" min="2" max="8" class="w-1/2 border-amber-200 rounded-lg text-sm" title="Ej: 4 para poner receso antes de la 4ta hora">
                                <input type="number" name="duracion_receso" placeholder="Min" min="10" max="60" class="w-1/2 border-amber-200 rounded-lg text-sm" title="Minutos de receso">
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="w-full mt-6 bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3 rounded-xl shadow-md transition-all">Generar Estructura</button>
                </form>
            </div>
        </div>

        <!-- MODAL: CLONACIÓN -->
        <div x-show="modalClonar" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="modalClonar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalClonar = false"></div>
            <div x-show="modalClonar" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-lg font-black text-[#3d2c1d]">Clonar Estructura</h3>
                    <button @click="modalClonar = false" class="text-slate-400 hover:text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <form action="{{ route('academico.bloques.clonar') }}" method="POST" class="p-6">
                    @csrf
                    
                    <h4 class="text-xs font-black text-indigo-600 uppercase tracking-widest mb-3">1. Copiar desde (Origen)</h4>
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div class="col-span-2">
                            <select name="origen_modalidad_id" class="w-full border-slate-200 bg-slate-50 rounded-xl text-sm" required>
                                <option value="">Modalidad Origen...</option>
                                @foreach($modalidades as $mod) <option value="{{ $mod->id }}">{{ $mod->nombre }}</option> @endforeach
                            </select>
                        </div>
                        <select name="origen_turno" class="border-slate-200 bg-slate-50 rounded-xl text-sm" required><option value="Matutino">Matutino</option><option value="Vespertino">Vespertino</option></select>
                        <select name="origen_jornada" class="border-slate-200 bg-slate-50 rounded-xl text-sm" required><option value="Regular">Regular</option><option value="Viernes">Viernes</option></select>
                    </div>

                    <h4 class="text-xs font-black text-rose-600 uppercase tracking-widest mb-3 border-t border-slate-100 pt-4">2. Pegar hacia (Destino)</h4>
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="col-span-2">
                            <select name="destino_modalidad_id" class="w-full border-slate-200 bg-slate-50 rounded-xl text-sm" required>
                                <option value="">Modalidad Destino...</option>
                                @foreach($modalidades as $mod) <option value="{{ $mod->id }}">{{ $mod->nombre }}</option> @endforeach
                            </select>
                        </div>
                        <select name="destino_turno" class="border-slate-200 bg-slate-50 rounded-xl text-sm" required><option value="Matutino">Matutino</option><option value="Vespertino">Vespertino</option></select>
                        <select name="destino_jornada" class="border-slate-200 bg-slate-50 rounded-xl text-sm" required><option value="Regular">Regular</option><option value="Viernes">Viernes</option></select>
                    </div>
                    
                    <button type="submit" class="w-full mt-4 bg-slate-800 hover:bg-slate-700 text-white font-black py-3 rounded-xl shadow-md transition-all">Clonar Bloques</button>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPT ALPINEJS + SWEETALERT -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('gestorBloques', () => ({
                modalGenerar: false,
                modalClonar: false,
                todosBloques: @json($bloques),
                form: {
                    modalidad_id: '',
                    turno: 'Matutino',
                    tipo_jornada: 'Regular',
                    hora_inicio: '',
                    es_recreo: false
                },
                actualizarSugerencia() {
                    const coincidentes = this.todosBloques.filter(b => 
                        b.modalidad_id == this.form.modalidad_id && 
                        b.turno == this.form.turno && 
                        b.tipo_jornada == this.form.tipo_jornada
                    );

                    if(coincidentes.length > 0) {
                        const ultimo = coincidentes.reduce((prev, current) => (prev.hora_fin > current.hora_fin) ? prev : current);
                        this.form.hora_inicio = ultimo.hora_fin.substring(0, 5); 
                    } else {
                        this.form.hora_inicio = '';
                    }
                }
            }));
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Confirmación para Eliminar un Bloque
            document.querySelectorAll('.alerta-eliminar').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Eliminar Bloque de Tiempo?',
                        text: "Si eliminas este bloque, se borrará de los horarios de todas las aulas que lo utilicen.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, Eliminar', cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });

            // Confirmación para Eliminar Jornada Completa
            document.querySelectorAll('.alerta-eliminar-jornada').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Destruir Jornada Completa?',
                        text: "Esto borrará TODOS los bloques de esta estructura de un solo golpe. No se puede deshacer.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, Destruir Todo', cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-rose-200' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>