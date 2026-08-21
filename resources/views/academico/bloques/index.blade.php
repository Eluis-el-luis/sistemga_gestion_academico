<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ __('Estructura de Horarios (Bloques)') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <!-- Mensajes de Sesión -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- FORMULARIO PARA AGREGAR UN BLOQUE -->
        <div class="bg-white rounded-xl shadow-sm border border-amber-100 overflow-hidden">
            <div class="bg-amber-50 px-6 py-4 border-b border-amber-100 flex items-center gap-2">
                <h3 class="text-lg font-bold text-amber-900">Añadir Bloque de Tiempo Oficial</h3>
            </div>
            
            <div class="p-6">
                <form action="{{ route('academico.bloques.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                    @csrf
                    
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Modalidad <span class="text-red-500">*</span></label>
                        <select name="modalidad_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm" required>
                            <option value="">Seleccione...</option>
                            @foreach($modalidades as $mod)
                                <option value="{{ $mod->id }}">{{ $mod->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Turno <span class="text-red-500">*</span></label>
                        <select name="turno" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm" required>
                            <option value="Matutino">Matutino</option>
                            <option value="Vespertino">Vespertino</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1" title="Ej: 1ra Hora, Receso, etc.">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" placeholder="Ej: 1ra Hora" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Inicio <span class="text-red-500">*</span></label>
                        <input type="time" name="hora_inicio" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm" required>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fin <span class="text-red-500">*</span></label>
                        <input type="time" name="hora_fin" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm" required>
                    </div>

                    <div class="md:col-span-1 flex flex-col justify-end h-full">
                        <div class="mb-2 flex items-center justify-center">
                            <label class="inline-flex items-center cursor-pointer" title="Marcar si es un espacio libre/receso">
                                <input type="hidden" name="es_recreo" value="0">
                                <input type="checkbox" name="es_recreo" value="1" class="rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500 w-5 h-5">
                                <span class="ml-1 text-[10px] font-bold text-gray-500 uppercase">Receso</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 rounded-lg shadow-sm transition-colors text-sm h-[42px] flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- LISTADO DE BLOQUES POR MODALIDAD -->
        <div>
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
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
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="bg-gray-800 px-5 py-3 flex justify-between items-center">
                                    <h4 class="font-bold text-white text-sm tracking-wide">{{ $modalidad->nombre }}</h4>
                                    <span class="bg-gray-600 text-gray-100 px-2 py-0.5 rounded text-xs font-semibold">{{ $turno }}</span>
                                </div>
                                
                                <div class="p-0">
                                    <ul class="divide-y divide-gray-100">
                                        @foreach($bloquesFiltrados as $bloque)
                                            <li class="flex justify-between items-center px-5 py-3 text-sm {{ $bloque->es_recreo ? 'bg-orange-50' : 'hover:bg-gray-50' }} transition-colors group">
                                                <div class="flex items-center gap-3">
                                                    @if($bloque->es_recreo)
                                                        <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                                                    @else
                                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    @endif
                                                    
                                                    <div class="flex flex-col">
                                                        <span class="font-bold {{ $bloque->es_recreo ? 'text-orange-800' : 'text-gray-800' }}">{{ $bloque->nombre }}</span>
                                                        <span class="text-xs {{ $bloque->es_recreo ? 'text-orange-600' : 'text-gray-500' }} font-mono mt-0.5">
                                                            {{ \Carbon\Carbon::parse($bloque->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($bloque->hora_fin)->format('h:i A') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                
                                                <form action="{{ route('academico.bloques.destroy', $bloque->id) }}" method="POST" class="alerta-eliminar">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-300 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-md transition-colors opacity-0 group-hover:opacity-100" title="Eliminar Bloque">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, Eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>