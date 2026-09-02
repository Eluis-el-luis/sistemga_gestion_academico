<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"></path></svg>
                {{ __('Responsables y Padres') }}
            </h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Resumen de adopción digital -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 text-center">
                <p class="text-3xl font-black text-[#3d2c1d]">{{ $datos['total'] }}</p>
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Total Estudiantes</p>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 text-center">
                <p class="text-3xl font-black text-emerald-600">{{ $datos['con_telefono'] }}</p>
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Con Teléfono</p>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 text-center">
                <p class="text-3xl font-black text-sky-600">{{ $datos['con_email'] }}</p>
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Con Correo</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-black text-slate-500 uppercase tracking-widest">Adopción Digital</span>
                <span class="text-lg font-black text-[#e6ac27]">{{ $datos['porcentaje_adopcion'] }}%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden">
                <div class="h-3 rounded-full bg-emerald-500 transition-all" style="width: {{ $datos['porcentaje_adopcion'] }}%"></div>
            </div>
        </div>

        <!-- Tabla de contacto -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h3 class="text-lg font-black text-[#3d2c1d]">Contacto de Responsables</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">Estudiante</th>
                            <th class="px-6 py-4 text-left">Madre</th>
                            <th class="px-6 py-4 text-left">Padre</th>
                            <th class="px-6 py-4 text-left">Tutor</th>
                            <th class="px-6 py-4 text-center">Autorizado Retiro</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($alumnos as $alumno)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $alumno->nombre_completo }}</td>
                                <td class="px-6 py-4">
                                    @if($alumno->madre_nombre_completo)
                                        <span class="block font-bold">{{ $alumno->madre_nombre_completo }}</span>
                                        <span class="text-xs text-slate-500">{{ $alumno->madre_telefono ?: 'Sin teléfono' }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($alumno->padre_nombre_completo)
                                        <span class="block font-bold">{{ $alumno->padre_nombre_completo }}</span>
                                        <span class="text-xs text-slate-500">{{ $alumno->padre_telefono ?: 'Sin teléfono' }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($alumno->tutor_nombre_completo)
                                        <span class="block font-bold">{{ $alumno->tutor_nombre_completo }}</span>
                                        <span class="text-xs text-slate-500">{{ $alumno->tutor_telefono ?: 'Sin teléfono' }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($alumno->autorizado_retirar_nombre)
                                        <span class="font-bold">{{ $alumno->autorizado_retirar_nombre }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-stone-500 font-bold">No hay estudiantes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>