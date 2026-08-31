<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-stone-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel Principal">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-2xl text-[#3d2c1d] leading-tight">
                Criterios de Evaluación Institucional
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-stone-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-[#FFFDF5] text-stone-500 uppercase text-[10px] font-black tracking-widest">
                            <tr>
                                <th class="px-6 py-4 text-left">Corte Evaluativo</th>
                                <th class="px-6 py-4 text-left">Rango de Fechas</th>
                                <th class="px-6 py-4 text-center">Configuración de Pesos (100 pts)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($cortes as $corte)
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="px-6 py-5">
                                        <span class="font-black text-[#3d2c1d] text-base">{{ $corte->numero }}° Parcial</span>
                                        <p class="text-[11px] font-bold text-stone-400 uppercase tracking-widest mt-0.5">Semestre {{ $corte->semestre }}</p>
                                    </td>
                                    
                                    <td class="px-6 py-5" colspan="2">
                                        <!-- Se añadió la clase form-corte -->
                                        <form action="{{ route('academico.cortes.update', $corte->id) }}" method="POST" class="form-corte flex flex-col md:flex-row md:items-end justify-between gap-6">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="flex items-center gap-2">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-stone-500 uppercase tracking-wider mb-1">Inicio</label>
                                                    <input type="date" name="fecha_inicio" value="{{ $corte->fecha_inicio }}" class="text-sm font-bold text-[#3d2c1d] border-stone-200 rounded-lg focus:ring-[#e6ac27] focus:border-[#e6ac27] shadow-sm" required>
                                                </div>
                                                <span class="text-stone-300 mt-4">-</span>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-stone-500 uppercase tracking-wider mb-1">Fin</label>
                                                    <input type="date" name="fecha_fin" value="{{ $corte->fecha_fin }}" class="text-sm font-bold text-[#3d2c1d] border-stone-200 rounded-lg focus:ring-[#e6ac27] focus:border-[#e6ac27] shadow-sm" required>
                                                </div>
                                            </div>

                                            <div class="flex items-end gap-3">
                                                <div>
                                                    <label class="block text-[10px] font-bold text-stone-500 uppercase tracking-wider mb-1 text-center">% Acumulado</label>
                                                    <input type="number" name="peso_acumulado" value="{{ $corte->peso_acumulado ?? 60 }}" class="w-20 text-center font-black text-[#3d2c1d] border-stone-200 rounded-lg focus:ring-[#e6ac27] focus:border-[#e6ac27] shadow-sm" min="0" max="100" required>
                                                </div>
                                                <div class="pb-3 text-stone-300 font-black">+</div>
                                                <div>
                                                    <label class="block text-[10px] font-bold text-stone-500 uppercase tracking-wider mb-1 text-center">% Examen</label>
                                                    <input type="number" name="peso_examen" value="{{ $corte->peso_examen ?? 40 }}" class="w-20 text-center font-black text-[#3d2c1d] border-stone-200 rounded-lg focus:ring-[#e6ac27] focus:border-[#e6ac27] shadow-sm" min="0" max="100" required>
                                                </div>
                                                <button type="submit" class="mb-1 bg-[#e6ac27] hover:bg-[#d69f22] text-white font-black p-2 rounded-lg shadow-sm transition-transform transform hover:-translate-y-0.5" title="Guardar Cambios">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-stone-500 font-bold">No hay cortes evaluativos configurados para el año actual.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas SweetAlert2 y Validación Frontend -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Validación en tiempo real antes de recargar la página
            document.querySelectorAll('.form-corte').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const inicioStr = this.querySelector('[name="fecha_inicio"]').value;
                    const finStr = this.querySelector('[name="fecha_fin"]').value;
                    const acumulado = parseInt(this.querySelector('[name="peso_acumulado"]').value) || 0;
                    const examen = parseInt(this.querySelector('[name="peso_examen"]').value) || 0;

                    // Validar fechas
                    if (inicioStr && finStr) {
                        const inicio = new Date(inicioStr);
                        const fin = new Date(finStr);
                        
                        if (fin <= inicio) {
                            e.preventDefault();
                            Swal.fire({
                                icon: 'warning',
                                title: 'Inconsistencia Cronológica',
                                text: 'La fecha de fin debe ser posterior a la fecha de inicio.',
                                confirmButtonColor: '#3d2c1d',
                                customClass: { popup: 'rounded-3xl border border-stone-200 shadow-xl' }
                            });
                            return;
                        }
                    }

                    // Validar los 100 puntos
                    const suma = acumulado + examen;
                    if (suma !== 100) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Puntaje Inválido',
                            text: `La suma del Acumulado y Examen debe ser exactamente 100. Actualmente suma: ${suma} puntos.`,
                            confirmButtonColor: '#3d2c1d',
                            customClass: { popup: 'rounded-3xl border border-stone-200 shadow-xl' }
                        });
                        return;
                    }
                });
            });

            // 2. Manejo de Errores nativos de Laravel (Por si se vulnera el frontend)
            @if ($errors->any())
                Swal.fire({
                    title: 'Error de Validación',
                    html: '{!! implode("<br>", $errors->all()) !!}',
                    icon: 'error',
                    confirmButtonColor: '#3d2c1d',
                    customClass: { popup: 'rounded-3xl border border-stone-200 shadow-xl' }
                });
            @endif

            // 3. Alertas de Sesión
            @if(session('success'))
                Swal.mixin({
                    toast: true, position: 'top', showConfirmButton: false, timer: 3500, timerProgressBar: true,
                    customClass: { popup: 'rounded-2xl shadow-lg border border-slate-100' }
                }).fire({ icon: 'success', title: '{{ session("success") }}' });
            @endif

            @if(session('error'))
                Swal.fire({
                    title: 'Verificación del Sistema',
                    text: '{{ session("error") }}',
                    icon: 'warning',
                    confirmButtonColor: '#3d2c1d',
                    confirmButtonText: 'Corregir',
                    customClass: { popup: 'rounded-3xl border border-stone-200 shadow-xl' }
                });
            @endif
        });
    </script>
</x-app-layout>