<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Libro de Calificaciones
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $asignacion->aula->grado->nombre }} - {{ $asignacion->aula->nombre }} | Materia: <span class="font-bold text-indigo-600">{{ $asignacion->asignatura->nombre }}</span>
                </p>
            </div>
            
            <a href="{{ route('dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition-colors">
                Volver al Panel
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alertas de Éxito o Error -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm font-medium">
                    ✓ {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm font-medium">
                    ⚠ Hubo un error en las notas ingresadas. Revisa que todas estén entre 0 y 100.
                </div>
            @endif

            <form action="{{ route('academico.notas.store') }}" method="POST">
                @csrf
                
                <!-- Datos Ocultos Requeridos por el Request -->
                <input type="hidden" name="aula_asignatura_docente_id" value="{{ $asignacion->id }}">
                
                <!-- Selector de Corte Evaluativo (IP, IIP, IIIP, IVP) -->
                <div class="bg-white p-6 rounded-t-xl border-b border-gray-100 shadow-sm flex items-center gap-4">
                    <label class="font-bold text-gray-700 uppercase text-sm">Seleccione el Corte Evaluativo:</label>
                    <select name="corte_evaluativo_id" class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-semibold text-indigo-700" required>
                        <option value="">Seleccione...</option>
                        @foreach($cortes as $corte)
                            <option value="{{ $corte->id }}" {{ $corteSeleccionado == $corte->id ? 'selected' : '' }}>
                                {{ $corte->nombre }} ({{ $corte->anioEscolar->nombre }})
                            </option>
                        @endforeach
                    </select>
                    
                    <button type="submit" class="ml-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Guardar Planilla Completa
                    </button>
                </div>

                <!-- Sábana Estilo Excel -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-b-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-indigo-50 text-indigo-900 uppercase text-xs font-extrabold tracking-wider">
                                <tr>
                                    <th class="px-6 py-4 text-left w-12">#</th>
                                    <th class="px-6 py-4 text-left">Código / Estudiante</th>
                                    <th class="px-6 py-4 text-center w-48">Nota Cuantitativa<br><span class="text-[10px] text-indigo-600 font-medium">(0 - 100)</span></th>
                                    <th class="px-6 py-4 text-center w-48">Indicador Cualitativo<br><span class="text-[10px] text-indigo-600 font-medium">(Automático MINED)</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($matriculas as $index => $matricula)
                                    @php
                                        // Buscamos si ya existe una nota para este alumno en este corte y materia
                                        $notaExistente = $matricula->notas->first(); 
                                    @endphp
                                    <tr class="hover:bg-yellow-50 transition-colors focus-within:bg-yellow-50">
                                        <td class="px-6 py-3 font-medium text-gray-500">{{ $loop->iteration }}</td>
                                        
                                        <td class="px-6 py-3">
                                            <!-- Input Oculto de la Matrícula -->
                                            <input type="hidden" name="notas[{{ $index }}][matricula_id]" value="{{ $matricula->id }}">
                                            
                                            <div class="font-bold text-gray-900">{{ $matricula->alumno->nombre_completo }}</div>
                                            <div class="text-xs text-gray-400">{{ $matricula->alumno->codigo_unico_persona }}</div>
                                        </td>
                                        
                                        <td class="px-6 py-3 text-center">
                                            <!-- Input de Nota (El nombre sigue la regla del Request: notas.*.nota_cuantitativa) -->
                                            <input type="number" 
                                                   name="notas[{{ $index }}][nota_cuantitativa]" 
                                                   value="{{ old('notas.'.$index.'.nota_cuantitativa', $notaExistente->nota_cuantitativa ?? '') }}" 
                                                   min="0" max="100" 
                                                   class="w-24 text-center font-bold text-lg border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('notas.'.$index.'.nota_cuantitativa') border-red-500 bg-red-50 @enderror"
                                                   placeholder="--"
                                                   tabindex="{{ $loop->iteration }}">
                                        </td>
                                        
                                        <td class="px-6 py-3 text-center">
                                            @if($notaExistente && $notaExistente->indicadorLogro)
                                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-extrabold 
                                                    {{ $notaExistente->indicadorLogro->codigo == 'AA' ? 'bg-blue-100 text-blue-800' : '' }}
                                                    {{ $notaExistente->indicadorLogro->codigo == 'AS' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $notaExistente->indicadorLogro->codigo == 'AF' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $notaExistente->indicadorLogro->codigo == 'AI' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ $notaExistente->indicadorLogro->codigo }}
                                                </span>
                                            @else
                                                <span class="text-gray-300 text-xs font-semibold uppercase tracking-wider">Pendiente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 font-medium">
                                            No hay alumnos matriculados en esta aula.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-4 flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition-transform transform hover:scale-105 flex items-center gap-2">
                        Guardar Planilla Completa
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>