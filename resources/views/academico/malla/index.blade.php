<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                {{ __('Plantilla Oficial: Malla Curricular') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 relative" x-data="{ showTopBtn: false }" @scroll.window="showTopBtn = (window.pageYOffset > 150)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Mensajes de Sesión -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- SECCIÓN 1: FORMULARIO PARA AGREGAR A LA PLANTILLA -->
            <div class="bg-white rounded-xl shadow-sm border border-indigo-100 overflow-hidden">
                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
                    <h3 class="text-lg font-bold text-indigo-900">Agregar Asignatura a un Grado</h3>
                    <p class="text-xs text-indigo-700 mt-1">Las materias que agregues aquí se clonarán automáticamente al aperturar una nueva aula de ese grado.</p>
                </div>
                
                <div class="p-6">
                    <form action="{{ route('academico.malla.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        @csrf
                        
                        <div class="md:col-span-3">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Grado <span class="text-red-500">*</span></label>
                            <select name="grado_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                                <option value="">Seleccione...</option>
                                @foreach($grados as $grado)
                                    <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-5">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Asignatura Oficial <span class="text-red-500">*</span></label>
                            <select name="asignatura_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                                <option value="">Seleccione la Materia...</option>
                                @foreach($asignaturas as $asignatura)
                                    <option value="{{ $asignatura->id }}">{{ $asignatura->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1" title="Horas a la semana">Horas Sem. <span class="text-red-500">*</span></label>
                            <input type="number" name="horas_semanales_sugeridas" min="1" max="40" value="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm text-center" required>
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors text-sm flex items-center justify-center gap-2 h-[42px]">
                                <span>+ Añadir</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SECCIÓN 2: VISUALIZADOR DE LA MALLA POR GRADO -->
            <div>
                <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Malla Curricular Configurada
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($grados as $grado)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                            <!-- Cabecera de la Tarjeta -->
                            <div class="bg-gray-50 px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg">{{ $grado->nombre }}</h4>
                                    <p class="text-xs text-gray-500 font-medium">{{ $grado->mallaCurricular->count() }} materias configuradas</p>
                                </div>
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs font-bold">{{ $grado->modalidad->nombre ?? 'N/A' }}</span>
                            </div>
                            
                            <!-- Lista de Materias -->
                            <div class="p-5 flex-grow">
                                @if($grado->mallaCurricular->count() > 0)
                                    <ul class="space-y-3">
                                        @foreach($grado->mallaCurricular as $item)
                                            <li class="flex justify-between items-center text-sm border-b border-gray-100 pb-3 last:border-0 last:pb-0 group">
                                                <div class="flex flex-col">
                                                    <span class="font-bold text-gray-700 group-hover:text-indigo-600 transition-colors">{{ $item->asignatura->nombre }}</span>
                                                    <span class="text-[11px] text-gray-400 uppercase tracking-wide">{{ $item->horas_semanales_sugeridas }} horas / semana</span>
                                                </div>
                                                
                                                <!-- Botón Eliminar con SweetAlert -->
                                                <form action="{{ route('academico.malla.destroy', $item->id) }}" method="POST" class="alerta-eliminar">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-300 hover:text-red-500 hover:bg-red-50 p-1.5 rounded-md transition-colors" title="Quitar de la malla">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="h-full flex flex-col items-center justify-center text-center py-6">
                                        <svg class="w-10 h-10 text-gray-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-sm text-gray-400 font-medium">Plantilla vacía.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- Botón Volver Arriba -->
        <button x-show="showTopBtn" x-transition @click="window.scrollTo({top: 0, behavior: 'smooth'})" class="fixed bottom-6 right-6 z-50 p-3.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full shadow-lg transition-all transform hover:scale-110">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
        </button>
    </div>

    <!-- Script para SweetAlert2 -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.alerta-eliminar').forEach(formulario => {
                formulario.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Quitar de la Plantilla?',
                        text: "Esta materia dejará de clonarse en las nuevas aulas de este grado.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Sí, Quitar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>