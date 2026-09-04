<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                        Validación de Boletines Oficiales
                    </h2>
                    <p class="text-sm font-medium text-slate-500 mt-1">Verifique las notas de sus alumnos y envíe el paquete aprobado al Gestor de Impresión.</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($aulas->count() > 0)
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm mb-6 border-l-4 border-l-[#e6ac27] flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h3 class="font-black text-lg text-[#3d2c1d]">
                            Tutoría Activa: <span class="text-[#e6ac27]">{{ $aulas->first()->grado->nombre ?? '' }} - Sección "{{ $aulas->first()->nombre }}"</span>
                        </h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">Estado del paquete: En revisión por el tutor</p>
                    </div>

                    <!-- Botón para empaquetar y enviar todo al Gestor de Usuarios -->
                    <button type="button" onclick="Swal.fire('¡Paquete enviado!', 'Los boletines validados han sido colocados en la caja para el Gestor de Usuarios.', 'success')" class="px-5 py-3 bg-[#3d2c1d] text-white rounded-xl hover:bg-slate-800 text-xs font-black uppercase tracking-widest shadow-md transition-all">
                         Enviar Paquete al Gestor de Usuarios
                    </button>
                </div>
            @endif

            <!-- Tabla de Alumnos y Validación -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left border-collapse">
                        <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-widest font-black text-slate-400">
                            <tr>
                                <th class="p-5 w-16 text-center">#</th>
                                <th class="p-5">Estudiante</th>
                                <th class="p-5 text-center">Código MINED</th>
                                <th class="p-5 text-center">Estado de Revisión</th>
                                <th class="p-5 text-right">Acciones de Auditoría</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($matriculas as $index => $matricula)
                                @php
                                    $tieneBoletinAprobado = $matricula->boletines->isNotEmpty();
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="p-5 text-center font-black text-slate-300">{{ $index + 1 }}</td>
                                    <td class="p-5 font-black text-[#3d2c1d]">
                                        {{ $matricula->alumno->nombre_completo }}
                                    </td>
                                    <td class="p-5 text-center font-bold text-slate-500">
                                        {{ $matricula->alumno->codigo_unico_persona }}
                                    </td>
                                    <td class="p-5 text-center">
                                        @if($tieneBoletinAprobado)
                                            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                ✔ En Caja (Aprobado)
                                            </span>
                                        @else
                                            <span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                                Pendiente de Revisión
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-5 text-right flex justify-end gap-2">
                                        <!-- Ver Previsualización para chequear notas -->
                                        <a href="{{ route('academico.boletines.show', $matricula->id) }}" class="inline-flex items-center gap-1 px-3 py-2 bg-slate-50 border border-slate-200 hover:border-[#e6ac27] text-slate-600 rounded-xl text-xs font-black transition-all shadow-sm">
                                            Revisar Notas
                                        </a>

                                        <!-- Botón para dar Visto Bueno / Guardar en Caja -->
                                        <form action="{{ route('academico.boletines.aprobar', $matricula->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white rounded-xl text-xs font-black transition-all shadow-sm">
                                                Dar Visto Bueno
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-12 text-center text-slate-400 font-bold">No hay matrículas activas en esta sección.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>