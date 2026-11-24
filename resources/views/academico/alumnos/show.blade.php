<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-4 py-3 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('academico.alumnos.index') }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al Directorio">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                    Expediente: <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $alumno->nombre_completo }}</span>
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <span class="px-3.5 py-2 bg-[#FFFDF8] dark:bg-neutral-800 text-neutral-800 dark:text-neutral-100 font-mono font-semibold text-xs rounded-xl border border-stone-200 dark:border-neutral-700">
                    CUP: {{ $alumno->codigo_unico_persona }}
                </span>

                @can('update', $alumno)
                    <a href="{{ route('academico.alumnos.edit', $alumno) }}" class="inline-flex items-center px-4 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 text-xs font-bold uppercase tracking-wider rounded-xl transition-colors cursor-pointer">
                        Editar Ficha
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 shadow-xs rounded-3xl overflow-hidden">
                <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 border-b border-stone-200/80 dark:border-neutral-800 px-6 sm:px-8 py-4 flex flex-wrap justify-between items-center gap-3">
                    <h3 class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white">Datos Personales</h3>
                    @if ($alumno->acepta_compromiso_cristiano)
                        <span class="inline-flex items-center px-3 py-1 bg-amber-50 dark:bg-neutral-900 text-amber-800 dark:text-[#e6ac27] text-xs font-semibold rounded-lg border border-amber-200/70 dark:border-neutral-700">
                            Compromiso Cristiano
                        </span>
                    @endif
                </div>

                <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-4 gap-6 text-sm">
                    <div>
                        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Sexo</p>
                        <p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->sexo === 'M' ? 'Masculino' : 'Femenino' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Fecha de Nacimiento</p>
                        <p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->fecha_nacimiento ? \Carbon\Carbon::parse($alumno->fecha_nacimiento)->format('d / m / Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Edad Actual</p>
                        <p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->fecha_nacimiento ? \Carbon\Carbon::parse($alumno->fecha_nacimiento)->age . ' años' : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Dirección Domiciliar</p>
                        <p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->direccion_domiciliar ?: 'No registrada' }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 shadow-xs rounded-3xl overflow-hidden" x-data="{ tab: 'madre' }">
                    <div class="bg-stone-50 dark:bg-neutral-800/70 border-b border-stone-200/80 dark:border-neutral-800 px-6 sm:px-8 py-4 flex flex-col sm:flex-row justify-between items-center gap-3">
                        <h3 class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white">Núcleo Familiar</h3>
                        <div class="flex space-x-1 bg-stone-200/70 dark:bg-neutral-900 p-1 rounded-xl">
                            <button type="button" @click="tab = 'madre'" :class="tab === 'madre' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-2xs' : 'text-neutral-600 dark:text-neutral-400'" class="px-4 py-1.5 text-xs font-semibold rounded-lg transition-colors cursor-pointer">Madre</button>
                            <button type="button" @click="tab = 'padre'" :class="tab === 'padre' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-2xs' : 'text-neutral-600 dark:text-neutral-400'" class="px-4 py-1.5 text-xs font-semibold rounded-lg transition-colors cursor-pointer">Padre</button>
                            <button type="button" @click="tab = 'tutor'" :class="tab === 'tutor' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white shadow-2xs' : 'text-neutral-600 dark:text-neutral-400'" class="px-4 py-1.5 text-xs font-semibold rounded-lg transition-colors cursor-pointer">Tutor</button>
                        </div>
                    </div>

                    <div class="p-6 sm:p-8 text-sm">
                        <div x-show="tab === 'madre'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Nombre</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->madre_nombre_completo ?: '—' }}</p></div>
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Cédula</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->madre_cedula ?: '—' }}</p></div>
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Teléfono</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->madre_telefono ?: '—' }}</p></div>
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Ocupación</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->madre_ocupacion ?: '—' }}</p></div>
                        </div>

                        <div x-show="tab === 'padre'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Nombre</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->padre_nombre_completo ?: '—' }}</p></div>
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Cédula</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->padre_cedula ?: '—' }}</p></div>
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Teléfono</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->padre_telefono ?: '—' }}</p></div>
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Ocupación</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->padre_ocupacion ?: '—' }}</p></div>
                        </div>

                        <div x-show="tab === 'tutor'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Nombre Legal</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->tutor_nombre_completo ?: '—' }}</p></div>
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Cédula</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->tutor_cedula ?: '—' }}</p></div>
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Teléfono</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->tutor_telefono ?: '—' }}</p></div>
                            <div><p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1">Ocupación</p><p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->tutor_ocupacion ?: '—' }}</p></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 shadow-xs rounded-3xl overflow-hidden">
                    <div class="bg-rose-50/60 dark:bg-neutral-800/70 border-b border-rose-100 dark:border-neutral-800 px-6 sm:px-8 py-4">
                        <h3 class="text-base sm:text-lg font-bold text-rose-900 dark:text-rose-300">Salud y Emergencias</h3>
                    </div>
                    <div class="p-6 sm:p-8 space-y-5 text-sm">
                        <div>
                            <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-1.5">Enfermedades / Alergias</p>
                            <p class="font-semibold text-neutral-800 dark:text-neutral-100">{{ $alumno->enfermedades_cronicas ?: 'Ninguna reportada' }}</p>
                        </div>
                        <div class="border-t border-stone-100 dark:border-neutral-800 pt-4">
                            <p class="text-xs font-semibold text-neutral-400 uppercase tracking-wider mb-2">Autorizado para Retirar</p>
                            <div class="bg-stone-50 dark:bg-neutral-800/70 p-4 rounded-2xl border border-stone-200 dark:border-neutral-700 space-y-1">
                                <p class="font-semibold text-neutral-900 dark:text-white">{{ $alumno->autorizado_retirar_nombre ?: 'Solo Padres / Tutor' }}</p>
                                @if ($alumno->autorizado_retirar_nombre)
                                    <p class="text-xs text-neutral-600 dark:text-neutral-300">Cédula: {{ $alumno->autorizado_retirar_cedula ?: 'Sin cédula' }}</p>
                                    <p class="text-xs text-neutral-600 dark:text-neutral-300">Tel: {{ $alumno->autorizado_retirar_telefono ?: 'Sin teléfono' }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-neutral-900 border border-stone-200 dark:border-neutral-800 shadow-xs rounded-3xl overflow-hidden">
                <div class="bg-stone-50 dark:bg-neutral-800/70 border-b border-stone-200/80 dark:border-neutral-800 px-6 sm:px-8 py-4 flex justify-between items-center">
                    <h3 class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white">Historial Académico</h3>
                    @can('create', \App\Models\Matricula::class)
                        <a href="{{ route('academico.matriculas.create', ['alumno_id' => $alumno->id]) }}" class="text-xs font-bold uppercase tracking-wider text-[#b88212] dark:text-[#e6ac27] hover:underline cursor-pointer">
                            + Nueva Matrícula
                        </a>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-100 dark:divide-neutral-800 text-sm text-left">
                        <thead class="bg-[#FFFDF8] dark:bg-neutral-800/50 text-neutral-500 dark:text-neutral-400 uppercase text-xs font-semibold tracking-wider">
                            <tr>
                                <th class="px-6 py-4">Año Lectivo</th>
                                <th class="px-6 py-4">Nivel / Grado</th>
                                <th class="px-6 py-4">Sección</th>
                                <th class="px-6 py-4">Fecha Matrícula</th>
                                <th class="px-6 py-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                            @forelse ($alumno->matriculas->sortByDesc('anioEscolar.nombre') as $matricula)
                                <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                    <td class="px-6 py-4 font-semibold">{{ $matricula->anioEscolar?->nombre ?? '-' }}</td>
                                    <td class="px-6 py-4 font-medium">{{ $matricula->aula?->grado?->nombre ?? '-' }}</td>
                                    <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">{{ $matricula->aula?->nombre ?? '-' }}</td>
                                    <td class="px-6 py-4 text-neutral-500 dark:text-neutral-400">{{ $matricula->fecha_matricula ? \Carbon\Carbon::parse($matricula->fecha_matricula)->format('d / m / Y') : '-' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold uppercase rounded-lg border
                                            {{ $matricula->estado === 'activo' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-900' : '' }}
                                            {{ $matricula->estado === 'retirado' ? 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-900' : '' }}
                                            {{ $matricula->estado === 'promovido' ? 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border-sky-200 dark:border-sky-900' : '' }}
                                            {{ $matricula->estado === 'repitente' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-900' : '' }}">
                                            {{ $matricula->estado }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-neutral-500 dark:text-neutral-400 text-sm">
                                        Sin registros de matrícula.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>