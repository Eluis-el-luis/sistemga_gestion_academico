<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                {{ __('Población Estudiantil') }}
            </h2>
            <a href="{{ route('academico.reportes.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <form method="GET" class="flex items-end gap-4">
                <div>
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Año</label>
                    <select name="anio_escolar_id" onchange="this.form.submit()" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm">
                        @foreach($anios as $an)
                            <option value="{{ $an->id }}" @selected($an->id == ($anio->id ?? null))>{{ $an->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" onclick="window.print()" class="ml-auto bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-4 py-2 rounded-xl shadow-md text-sm">Imprimir</button>
            </form>
        </div>

        <!-- Tarjetas de resumen -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 text-center">
                <p class="text-3xl font-black text-[#3d2c1d]">{{ $datos['total_alumnos'] }}</p>
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Total Alumnos</p>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 text-center">
                <p class="text-3xl font-black text-emerald-600">{{ $datos['matriculados'] }}</p>
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Matriculados</p>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 text-center">
                <p class="text-3xl font-black text-rose-600">{{ $datos['retirados'] }}</p>
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Retirados</p>
            </div>
            <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 text-center">
                <p class="text-3xl font-black text-amber-600">{{ $datos['expedientes_incompletos'] }}</p>
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mt-2">Exped. Incompletos</p>
            </div>
        </div>

        <!-- Listado de expedientes incompletos -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-8 py-5 border-b border-[#e6ac27]/20">
                <h3 class="text-lg font-black text-[#3d2c1d]">Expedientes Incompletos</h3>
                <p class="text-xs text-slate-500 mt-1">Alumnos con campos obligatorios vacíos (dirección, madre, tutor o fecha de nacimiento).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                        <tr>
                            <th class="px-6 py-4 text-left">CUP</th>
                            <th class="px-6 py-4 text-left">Nombre</th>
                            <th class="px-6 py-4 text-left">Faltante(s)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($incompletos as $alumno)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 font-bold">{{ $alumno->codigo_unico_persona ?? '—' }}</td>
                                <td class="px-6 py-4 font-bold text-[#3d2c1d]">{{ $alumno->nombre_completo }}</td>
                                <td class="px-6 py-4 text-slate-500">
                                    @php
                                        $faltantes = [];
                                        if (empty($alumno->direccion_domiciliar)) $faltantes[] = 'Dirección';
                                        if (empty($alumno->madre_nombre_completo)) $faltantes[] = 'Madre';
                                        if (empty($alumno->madre_telefono)) $faltantes[] = 'Teléfono de madre';
                                        if (empty($alumno->tutor_nombre_completo)) $faltantes[] = 'Tutor';
                                        if (empty($alumno->fecha_nacimiento)) $faltantes[] = 'Fecha de nacimiento';
                                    @endphp
                                    {{ implode(', ', $faltantes) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-12 text-center text-stone-500 font-bold">No hay expedientes incompletos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>