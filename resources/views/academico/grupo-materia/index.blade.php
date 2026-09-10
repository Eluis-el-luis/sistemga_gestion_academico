<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Agrupaciones de Materias (Boletín Oficial)</h2>
            <a href="{{ route('dashboard') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">← Volver</a>
        </div>
    </x-slot>

    <div class="pb-12 pt-6 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm font-medium">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm font-medium">{{ session('error') }}</div>
        @endif

        <!-- Crear grupo -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-black text-[#3d2c1d] mb-4">Crear Nuevo Grupo</h3>
            <form action="{{ route('academico.grupo-materia.store') }}" method="POST" class="flex flex-wrap items-end gap-4">
                @csrf
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Nombre del Grupo</label>
                    <input type="text" name="nombre" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-bold" required>
                </div>
                <div class="w-24">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-1.5">Orden</label>
                    <input type="number" name="orden" value="0" min="0" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-bold">
                </div>
                <button type="submit" class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-5 py-2 rounded-xl shadow-md text-sm">Crear</button>
            </form>
        </div>

        <!-- Asignación de materias a grupos -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-black text-[#3d2c1d] mb-4">Asignar Materias a Grupos</h3>
            <form action="{{ route('academico.grupo-materia.asignar') }}" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-[#FFFDF5] text-slate-500 uppercase text-xs font-black">
                            <tr>
                                <th class="px-4 py-3 text-left">Materia</th>
                                <th class="px-4 py-3 text-left">Grupo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($asignaturas as $asignatura)
                                <tr>
                                    <td class="px-4 py-2 font-bold text-[#3d2c1d]">{{ $asignatura->nombre }}</td>
                                    <td class="px-4 py-2">
                                        <select name="asignaciones[{{ $asignatura->id }}]" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm">
                                            <option value="">— Sin grupo —</option>
                                            @foreach($grupos as $grupo)
                                                <option value="{{ $grupo->id }}" @selected($asignatura->grupo_materia_id == $grupo->id)>{{ $grupo->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="mt-4 bg-[#3d2c1d] text-white font-black px-6 py-2.5 rounded-xl shadow-md text-sm">Guardar Asignaciones</button>
            </form>
        </div>

        <!-- Listado de grupos -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="bg-[#FFFDF5] px-6 py-4 border-b border-[#e6ac27]/20">
                <h3 class="font-black text-[#3d2c1d]">Grupos Existentes</h3>
            </div>
            <div class="p-6 space-y-4">
                @forelse($grupos as $grupo)
                    <div class="border border-slate-200 rounded-2xl p-4 flex items-start justify-between gap-4">
                        <div>
                            <form action="{{ route('academico.grupo-materia.update', $grupo->id) }}" method="POST" class="flex items-center gap-3">
                                @csrf @method('PUT')
                                <input type="text" name="nombre" value="{{ $grupo->nombre }}" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-bold w-72">
                                <input type="number" name="orden" value="{{ $grupo->orden }}" class="border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-bold w-20">
                                <button type="submit" class="text-[#e6ac27] hover:text-amber-600 text-xs font-black uppercase">Actualizar</button>
                            </form>
                            <p class="text-xs text-slate-500 mt-2">
                                {{ $grupo->asignaturas->count() }} materia(s) asignadas
                            </p>
                        </div>
                        <form action="{{ route('academico.grupo-materia.destroy', $grupo->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este grupo? Las materias quedarán sin grupo.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-black uppercase">Eliminar</button>
                        </form>
                    </div>
                @empty
                    <p class="text-slate-500">No hay grupos definidos. Crea los 3 grupos oficiales del colegio.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>