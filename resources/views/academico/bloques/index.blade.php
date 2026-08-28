<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ __('Estructura de Horarios (Bloques)') }}
            </h2>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <!-- Mensajes de Sesión -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- FORMULARIO PARA AGREGAR UN BLOQUE -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20 flex items-center gap-2">
                <h3 class="text-lg font-black text-[#3d2c1d]">Añadir Bloque de Tiempo Oficial</h3>
            </div>
            
            <div class="p-8">
                <form action="{{ route('academico.bloques.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
                    @csrf
                    
                    <div class="md:col-span-3">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Modalidad <span class="text-rose-500">*</span></label>
                        <select name="modalidad_id" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm transition-colors font-medium text-slate-700" required>
                            <option value="">Seleccione...</option>
                            @foreach($modalidades as $mod)
                                <option value="{{ $mod->id }}">{{ $mod->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Turno <span class="text-rose-500">*</span></label>
                        <select name="turno" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm transition-colors font-medium text-slate-700" required>
                            <option value="Matutino">Matutino</option>
                            <option value="Vespertino">Vespertino</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5" title="Ej: 1ra Hora, Receso, etc.">Nombre <span class="text-rose-500">*</span></label>
                        <input type="text" name="nombre" placeholder="Ej: 1ra Hora" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-bold text-[#3d2c1d] transition-colors" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Inicio <span class="text-rose-500">*</span></label>
                        <input type="time" name="hora_inicio" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-black text-[#3d2c1d] transition-colors" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Fin <span class="text-rose-500">*</span></label>
                        <input type="time" name="hora_fin" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm font-black text-[#3d2c1d] transition-colors" required>
                    </div>

                    <div class="md:col-span-1 flex flex-col justify-end h-full">
                        <div class="mb-3 flex items-center justify-center">
                            <label class="inline-flex items-center cursor-pointer" title="Marcar si es un espacio libre/receso">
                                <input type="hidden" name="es_recreo" value="0">
                                <input type="checkbox" name="es_recreo" value="1" class="rounded border-slate-300 text-[#e6ac27] shadow-sm focus:ring-[#e6ac27] w-5 h-5">
                                <span class="ml-1.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Receso</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-2 rounded-xl shadow-md shadow-[#e6ac27]/20 transition-all transform hover:-translate-y-0.5 text-sm h-[42px] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO DE BLOQUES POR MODALIDAD -->
        <div class="pt-4">
            <h3 class="text-xl font-black text-[#3d2c1d] mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Estructura Definida
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($modalidades as $modalidad)
                    @foreach(['Matutino', 'Vespertino'] as $turno)
                        @php 
                            // Ordenamos los bloques por hora de inicio para que tenga lógica visual
                            $bloquesFiltrados = $bloques->where('modalidad_id', $modalidad->id)
                                                        ->where('turno', $turno)
                                                        ->sortBy('hora_inicio');
                        @endphp
                        
                        @if($bloquesFiltrados->count() > 0)
                            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                                <div class="bg-[#FFFDF5] px-6 py-5 border-b border-[#e6ac27]/20 flex justify-between items-center">
                                    <h4 class="font-black text-[#3d2c1d] text-base tracking-tight">{{ $modalidad->nombre }}</h4>
                                    <span class="bg-slate-100 border border-slate-200 text-slate-600 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest">{{ $turno }}</span>
                                </div>
                                
                                <div class="p-0">
                                    <ul class="divide-y divide-slate-100">
                                        @foreach($bloquesFiltrados as $bloque)
                                            <li class="flex justify-between items-center px-6 py-4 text-sm {{ $bloque->es_recreo ? 'bg-amber-50/30' : 'hover:bg-slate-50' }} transition-colors group">
                                                <div class="flex items-center gap-4">
                                                    @if($bloque->es_recreo)
                                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    @endif
                                                    
                                                    <div class="flex flex-col">
                                                        <span class="font-black text-base {{ $bloque->es_recreo ? 'text-amber-700' : 'text-slate-700' }}">{{ $bloque->nombre }}</span>
                                                        <span class="text-[11px] {{ $bloque->es_recreo ? 'text-amber-600' : 'text-slate-400' }} font-bold uppercase tracking-widest mt-1">
                                                            {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <form action="{{ route('academico.bloques.destroy', $bloque->id) }}" method="POST" class="alerta-eliminar">
                                                    @csrf
                                                    @method('DELETE')
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
            </div>
        </div>
    </div>

    <!-- Script para SweetAlert2 -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alerta-eliminar').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Eliminar Bloque de Tiempo?',
                        text: "Si eliminas este bloque, se borrará de los horarios de todas las aulas que lo utilicen.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, Eliminar',
                        cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl border border-slate-200' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>