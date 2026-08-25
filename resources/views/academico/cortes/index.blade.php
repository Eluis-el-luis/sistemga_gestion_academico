<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-[#3d2c1d] leading-tight">
                Criterios de Evaluación Institucional
            </h2>
            <a href="{{ route('academico.notas.index') }}" class="text-stone-500 hover:text-stone-700 font-bold text-sm">
                Volver a Supervisión
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-[#FFFDF5] min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm font-bold">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm font-bold">
                    ⚠ {{ session('error') }}
                </div>
            @endif

            <div class="bg-white p-6 rounded-2xl border border-stone-200 shadow-sm flex flex-col md:flex-row items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-[#FFFDF5] text-[#e6ac27] flex items-center justify-center border border-[#e6ac27]/30 shrink-0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-black text-[#3d2c1d]">Distribución de Puntajes - {{ $anioActivo->nombre ?? 'Año no activo' }}</h3>
                    <p class="text-stone-500 font-medium mt-1">Defina el peso oficial que tendrán los acumulados y el examen final en cada corte evaluativo. Esta regla será aplicada automáticamente a todos los docentes.</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-stone-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-[#FFFDF5] text-stone-500 uppercase text-xs font-black tracking-wider">
                            <tr>
                                <th class="px-6 py-4 text-left">Corte Evaluativo</th>
                                <th class="px-6 py-4 text-left">Fechas</th>
                                <th class="px-6 py-4 text-center">Configuración de Pesos (100 pts)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @forelse($cortes as $corte)
                                <tr class="hover:bg-stone-50 transition-colors">
                                    <td class="px-6 py-5">
                                        <span class="font-black text-[#3d2c1d] text-base">{{ $corte->numero }}° Parcial</span>
                                        <p class="text-xs font-bold text-stone-400 mt-0.5">Semestre {{ $corte->semestre }}</p>
                                    </td>
                                    <td class="px-6 py-5 text-stone-600 font-medium">
                                        {{ \Carbon\Carbon::parse($corte->fecha_inicio)->format('d/m/Y') }}<br>
                                        <span class="text-stone-400 text-xs">al</span><br>
                                        {{ \Carbon\Carbon::parse($corte->fecha_fin)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-5">
                                        <form action="{{ route('academico.cortes.update', $corte->id) }}" method="POST" class="flex items-end justify-center gap-4">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="block text-[10px] font-bold text-stone-500 uppercase tracking-wider mb-1">% Acumulado</label>
                                                <input type="number" name="peso_acumulado" value="{{ $corte->peso_acumulado ?? 60 }}" class="w-20 text-center font-bold text-[#3d2c1d] border-stone-300 rounded-lg focus:ring-[#e6ac27] focus:border-[#e6ac27]" min="0" max="100" required>
                                            </div>
                                            <div class="pb-3 text-stone-400 font-black">+</div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-stone-500 uppercase tracking-wider mb-1">% Examen</label>
                                                <input type="number" name="peso_examen" value="{{ $corte->peso_examen ?? 40 }}" class="w-20 text-center font-bold text-[#3d2c1d] border-stone-300 rounded-lg focus:ring-[#e6ac27] focus:border-[#e6ac27]" min="0" max="100" required>
                                            </div>
                                            <button type="submit" class="mb-1 ml-2 bg-[#e6ac27] hover:bg-[#d69f22] text-[#3d2c1d] font-black p-2 rounded-lg shadow-sm transition-transform transform hover:scale-105" title="Guardar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            </button>
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
</x-app-layout>