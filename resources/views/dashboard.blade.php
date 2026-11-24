@php
    $usuarioDash = Auth::user();
    $hora = now()->timezone('America/Managua')->hour;
    $saludo = $hora < 12 ? 'Buenos días' : ($hora < 18 ? 'Buenas tardes' : 'Buenas noches');
    $nombreCompleto = trim((string) ($usuarioDash?->nombre_completo ?? $usuarioDash?->name ?? 'Usuario'));
    $nombreLimpio = explode(' ', $nombreCompleto)[0] ?: 'Usuario';

    $rolesPermitidos = [
        'directora',
        'subdirectora',
        'gestor_it',
        'coordinador',
        'secretaria',
        'docente_guia',
        'docente_asignatura',
        'docente_auxiliar',
        'docente_suplente',
        'psicologa',
        'Director',
        'Subdirector',
        'Gestor de Usuarios',
        'Coordinador',
        'Secretaria',
        'Docente Guia',
        'Docente Guía',
        'Docente por Asignatura',
        'Docente Auxiliar',
        'Docente Suplente',
        'Psicologa',
        'Psicóloga',
    ];

    $rolesDashboard = $usuarioDash
        ? $usuarioDash->roles->filter(fn ($rol) => in_array($rol->name, $rolesPermitidos, true))->values()
        : collect();

    $nombresRolesUsuario = $rolesDashboard->pluck('name')->values()->toArray();
    $rolPorDefecto = request('tab') && in_array(request('tab'), $nombresRolesUsuario, true)
        ? request('tab')
        : ($nombresRolesUsuario[0] ?? 'Usuario');

    $ultimoAvisoId = isset($avisos) ? ($avisos->first()->id ?? 0) : 0;

    $asistenciaHoy = null;
    if (
        $usuarioDash
        && class_exists('\App\Models\AsistenciaPersonal')
        && \Illuminate\Support\Facades\Schema::hasTable('asistencia_personal')
    ) {
        $asistenciaHoy = \App\Models\AsistenciaPersonal::where('usuario_id', $usuarioDash->id)
            ->where('fecha', now()->timezone('America/Managua')->toDateString())
            ->first();
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="relative flex justify-between items-center w-full min-h-[2.25rem] z-30 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-4 py-3 shadow-xs transition-colors">
            <div class="flex items-center gap-3">
                <h2 class="text-lg sm:text-xl font-bold text-neutral-900 dark:text-neutral-100 tracking-tight">
                    {{ $saludo }}, <span class="text-[#c99116] dark:text-[#e6ac27]">{{ $nombreLimpio }}</span>
                </h2>
                <span class="hidden sm:inline-flex px-2.5 py-1 rounded-lg bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 font-semibold text-[11px] uppercase tracking-wider border border-stone-200 dark:border-neutral-700">
                    {{ $usuarioDash?->roles->pluck('name')->first() ?? 'Miembro' }}
                </span>
            </div>

            <h2 class="hidden lg:block font-semibold text-sm text-neutral-600 dark:text-neutral-300 uppercase tracking-widest">
                Panel Principal
            </h2>

            <div class="relative" x-data="{
                openAvisos: false,
                ultimoVisto: Number(localStorage.getItem('avisoVisto_{{ $usuarioDash?->id ?? 0 }}') || 0),
                avisoActual: {{ (int) $ultimoAvisoId }}
            }">
                <button
                    type="button"
                    @click="openAvisos = !openAvisos; localStorage.setItem('avisoVisto_{{ $usuarioDash?->id ?? 0 }}', avisoActual); ultimoVisto = avisoActual;"
                    @click.outside="openAvisos = false"
                    aria-label="Ver comunicados"
                    class="relative p-2 text-neutral-500 dark:text-neutral-300 hover:text-[#c99116] dark:hover:text-[#e6ac27] transition-colors rounded-xl hover:bg-stone-100 dark:hover:bg-neutral-800 cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span x-show="avisoActual > ultimoVisto" x-cloak class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                    </span>
                </button>

                <div
                    x-show="openAvisos"
                    x-cloak
                    x-transition
                    class="absolute right-0 mt-3 w-80 sm:w-96 bg-white dark:bg-neutral-900 rounded-2xl shadow-xl border border-stone-200 dark:border-neutral-800 z-50 overflow-hidden"
                >
                    <div class="px-5 py-3 border-b border-stone-100 dark:border-neutral-800 bg-stone-50 dark:bg-neutral-800/70 flex justify-between items-center">
                        <span class="text-xs font-bold text-neutral-800 dark:text-neutral-100 uppercase tracking-wider">Comunicados</span>
                        @hasanyrole('directora|subdirectora|coordinador|Director|Subdirector|Coordinador')
                            <button type="button" @click="$dispatch('abrir-modal-aviso'); openAvisos = false;" class="text-xs font-semibold text-[#c99116] dark:text-[#e6ac27] hover:underline transition-colors cursor-pointer">
                                + Nuevo
                            </button>
                        @endhasanyrole
                    </div>

                    <div class="max-h-96 overflow-y-auto p-5 space-y-4">
                        @forelse ($avisos ?? collect() as $aviso)
                            <div class="pb-4 border-b border-stone-100 dark:border-neutral-800 last:border-0 last:pb-0">
                                <div class="flex justify-between items-start gap-3">
                                    <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 leading-snug">{{ $aviso->titulo }}</h4>
                                    @hasanyrole('directora|subdirectora|coordinador|Director|Subdirector|Coordinador')
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button
                                                type="button"
                                                @click="$dispatch('abrir-modal-editar-aviso', {
                                                    id: '{{ $aviso->id }}',
                                                    titulo: @js($aviso->titulo),
                                                    mensaje: @js($aviso->mensaje)
                                                }); openAvisos = false;"
                                                class="text-amber-600 dark:text-amber-400 hover:text-amber-700 transition-colors cursor-pointer"
                                                title="Editar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                </svg>
                                            </button>
                                            <form action="{{ url('/dashboard/avisos/' . $aviso->id) }}" method="POST" class="inline form-eliminar">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-600 transition-colors cursor-pointer" title="Eliminar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endhasanyrole
                                </div>
                                <p class="text-xs text-neutral-600 dark:text-neutral-300 mt-1.5 leading-relaxed whitespace-pre-line">{{ $aviso->mensaje }}</p>
                                <div class="flex items-center gap-2 mt-2.5 text-[10px] text-neutral-400 font-semibold uppercase tracking-wider">
                                    <span>{{ $aviso->autor_nombre ?? 'Administración' }}</span>
                                    <span>&bull;</span>
                                    <span>{{ \Carbon\Carbon::parse($aviso->created_at)->diffForHumans() }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-6 text-neutral-400">
                                <p class="text-xs font-medium">No hay comunicados recientes.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div
        class="py-6 min-h-screen relative bg-stone-50 dark:bg-neutral-950 transition-colors duration-200"
        x-data="{
            rolesValidos: {{ \Illuminate\Support\Js::from($nombresRolesUsuario) }},
            rolPorDefecto: @js($rolPorDefecto),
            rolUrl: @js(request('tab', '')),
            rolActivo: @js($rolPorDefecto),
            init() {
                const guardado = localStorage.getItem('rolActivo_{{ $usuarioDash?->id ?? 0 }}');
                if (this.rolUrl && this.rolesValidos.includes(this.rolUrl)) {
                    this.rolActivo = this.rolUrl;
                    localStorage.setItem('rolActivo_{{ $usuarioDash?->id ?? 0 }}', this.rolUrl);
                } else if (guardado && this.rolesValidos.includes(guardado)) {
                    this.rolActivo = guardado;
                } else {
                    this.rolActivo = this.rolPorDefecto;
                    localStorage.setItem('rolActivo_{{ $usuarioDash?->id ?? 0 }}', this.rolPorDefecto);
                }
            },
            cambiarRol(nuevoRol) {
                this.rolActivo = nuevoRol;
                localStorage.setItem('rolActivo_{{ $usuarioDash?->id ?? 0 }}', nuevoRol);
            }
        }"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @unless ($usuarioDash && $usuarioDash->hasAnyRole(['directora', 'subdirectora', 'gestor_it', 'Director', 'Subdirector', 'Gestor de Usuarios']))
                <div class="bg-white dark:bg-neutral-900 p-6 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-neutral-800 border border-amber-200/60 dark:border-neutral-700 flex items-center justify-center text-[#d49b1a] shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-neutral-900 dark:text-neutral-100 text-base">Registro de Asistencia Diaria</h3>
                            <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mt-0.5">{{ now()->timezone('America/Managua')->format('d/m/Y') }}</p>
                        </div>
                    </div>

                    <div class="w-full md:w-auto min-w-[280px]">
                        @if ($asistenciaHoy)
                            <div class="px-5 py-3 rounded-xl border flex items-center gap-3 w-full justify-center
                                {{ $asistenciaHoy->estado === 'Presente' ? 'bg-emerald-50 dark:bg-emerald-950/50 border-emerald-200 dark:border-emerald-900 text-emerald-700 dark:text-emerald-300' : '' }}
                                {{ $asistenciaHoy->estado === 'Retardo' ? 'bg-amber-50 dark:bg-amber-950/50 border-amber-200 dark:border-amber-900 text-amber-700 dark:text-amber-300' : '' }}
                                {{ in_array($asistenciaHoy->estado, ['Ausente', 'Justificado'], true) ? 'bg-rose-50 dark:bg-rose-950/50 border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300' : '' }}
                            ">
                                <div class="flex flex-col text-center sm:text-left">
                                    <span class="font-bold text-sm leading-tight">{{ $asistenciaHoy->estado }}</span>
                                    <span class="text-[11px] font-medium uppercase tracking-wider opacity-80">Marcó: {{ \Carbon\Carbon::parse($asistenciaHoy->hora_entrada)->format('h:i A') }}</span>
                                </div>
                            </div>
                        @elseif (Route::has('asistencia.personal.marcar'))
                            <form action="{{ route('asistencia.personal.marcar') }}" method="POST" class="flex flex-col gap-3">
                                @csrf
                                @php
                                    $esTarde = now()->timezone('America/Managua')->format('H:i:s') > '07:15:00';
                                @endphp
                                @if ($esTarde)
                                    <div class="w-full">
                                        <label class="block text-[11px] font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider mb-1">Justificación Obligatoria</label>
                                        <textarea name="observaciones" rows="2" required placeholder="Explica el motivo de tu llegada después de las 7:15 AM..." class="w-full border-rose-200 dark:border-rose-900 bg-rose-50/70 dark:bg-neutral-800 rounded-xl focus:ring-rose-500 focus:border-rose-500 text-sm text-neutral-900 dark:text-neutral-100"></textarea>
                                    </div>
                                @endif
                                <button type="submit" class="w-full bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 font-semibold py-2.5 px-6 rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                    Registrar mi Llegada
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endunless

            <div class="flex space-x-2 border-b border-stone-200 dark:border-neutral-800 overflow-x-auto pb-px">
                @foreach ($rolesDashboard as $rol)
                    <button
                        type="button"
                        @click="cambiarRol(@js($rol->name))"
                        :class="rolActivo === @js($rol->name) ? 'border-[#e6ac27] text-neutral-900 dark:text-white font-semibold bg-white dark:bg-neutral-900' : 'border-transparent text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 font-medium'"
                        class="px-5 py-2.5 border-b-2 text-sm transition-colors whitespace-nowrap rounded-t-xl cursor-pointer"
                    >
                        Módulo: {{ $rol->name }}
                    </button>
                @endforeach
            </div>

            <div class="mt-6">
                @hasanyrole('directora|subdirectora|Director|Subdirector')
                    <div x-show="['directora', 'subdirectora', 'Director', 'Subdirector'].includes(rolActivo)" x-cloak x-transition.opacity class="space-y-6">
                        @include('components.dashboard.directiva')
                    </div>
                @endhasanyrole

                @hasanyrole('gestor_it|Gestor de Usuarios')
                    <div x-show="['gestor_it', 'Gestor de Usuarios'].includes(rolActivo)" x-cloak x-transition.opacity class="space-y-6">
                        @include('components.dashboard.gestor')
                    </div>
                @endhasanyrole

                @hasanyrole('coordinador|Coordinador')
                    <div x-show="['coordinador', 'Coordinador'].includes(rolActivo)" x-cloak x-transition.opacity class="space-y-6">
                        @include('components.dashboard.coordinador')
                    </div>
                @endhasanyrole

                @hasanyrole('secretaria|Secretaria')
                    <div x-show="['secretaria', 'Secretaria'].includes(rolActivo)" x-cloak x-transition.opacity class="space-y-6">
                        @include('components.dashboard.secretaria')
                    </div>
                @endhasanyrole

                @hasanyrole('docente_guia|Docente Guia|Docente Guía')
                    <div x-show="['docente_guia', 'Docente Guia', 'Docente Guía'].includes(rolActivo)" x-cloak x-transition.opacity class="space-y-4">
                        @include('components.dashboard.docente-guia')
                    </div>
                @endhasanyrole

                @hasanyrole('docente_asignatura|Docente por Asignatura')
                    <div x-show="['docente_asignatura', 'Docente por Asignatura'].includes(rolActivo)" x-cloak x-transition.opacity class="space-y-6">
                        @include('components.dashboard.docente-asignatura')
                    </div>
                @endhasanyrole

                @hasanyrole('docente_auxiliar|Docente Auxiliar')
                    <div x-show="['docente_auxiliar', 'Docente Auxiliar'].includes(rolActivo)" x-cloak x-transition.opacity class="space-y-6">
                        @include('components.dashboard.docente-auxiliar')
                    </div>
                @endhasanyrole

                @hasanyrole('docente_suplente|Docente Suplente')
                    <div x-show="['docente_suplente', 'Docente Suplente'].includes(rolActivo)" x-cloak x-transition.opacity class="space-y-6">
                        @include('components.dashboard.docente-suplente')
                    </div>
                @endhasanyrole

                @hasanyrole('psicologa|Psicologa|Psicóloga')
                    <div x-show="['psicologa', 'Psicologa', 'Psicóloga'].includes(rolActivo)" x-cloak x-transition.opacity class="space-y-6">
                        @include('components.dashboard.psicologa')
                    </div>
                @endhasanyrole
            </div>
        </div>
    </div>

    <!-- MODAL ASISTENCIA -->
    <div x-data="{ open: false }" @abrir-modal-asistencia.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="open = false"></div>
        <div x-show="open" x-transition class="relative bg-white dark:bg-neutral-900 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-stone-200 dark:border-neutral-800">
            <div class="px-6 py-4 border-b border-stone-100 dark:border-neutral-800 bg-stone-50 dark:bg-neutral-800/70 flex justify-between items-center">
                <h3 class="text-base font-bold text-neutral-900 dark:text-neutral-100">Supervisión de Asistencia</h3>
                <button type="button" @click="open = false" class="text-neutral-400 hover:text-rose-500 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 grid gap-3">
                <a href="{{ route('academico.asistencia.aula.create') }}" class="group flex items-center p-4 border border-stone-200 dark:border-neutral-800 rounded-2xl hover:border-[#e6ac27] bg-white dark:bg-neutral-900 hover:bg-amber-50/40 dark:hover:bg-neutral-800/70 transition-colors cursor-pointer">
                    <div>
                        <span class="block font-semibold text-neutral-900 dark:text-neutral-100">Asistencia de Aulas</span>
                        <span class="block text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Control de estudiantes</span>
                    </div>
                </a>
                <a href="{{ route('academico.asistencia.personal.index') }}" class="group flex items-center p-4 border border-stone-200 dark:border-neutral-800 rounded-2xl hover:border-[#e6ac27] bg-white dark:bg-neutral-900 hover:bg-amber-50/40 dark:hover:bg-neutral-800/70 transition-colors cursor-pointer">
                    <div>
                        <span class="block font-semibold text-neutral-900 dark:text-neutral-100">Asistencia del Personal</span>
                        <span class="block text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Lista maestra diaria</span>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- MODAL NUEVO COMUNICADO -->
    <div x-data="{ open: false }" @abrir-modal-aviso.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="open = false"></div>
        <div x-show="open" x-transition class="relative bg-white dark:bg-neutral-900 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-stone-200 dark:border-neutral-800">
            <div class="px-6 py-4 border-b border-stone-100 dark:border-neutral-800 bg-stone-50 dark:bg-neutral-800/70 flex justify-between items-center">
                <h3 class="text-base font-bold text-neutral-900 dark:text-neutral-100">Nuevo Comunicado</h3>
                <button type="button" @click="open = false" class="text-neutral-400 hover:text-rose-500 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form action="{{ url('/dashboard/avisos') }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Público Destino</label>
                        <select name="modalidad_id" class="w-full border-stone-200 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm">
                            @hasanyrole('directora|subdirectora|Director|Subdirector')
                                <option value="">Aviso Global (Todo el Personal)</option>
                                @isset($modalidades)
                                    @foreach ($modalidades as $modalidad)
                                        <option value="{{ $modalidad->id }}">Solo {{ $modalidad->nombre }}</option>
                                    @endforeach
                                @endisset
                            @else
                                @isset($modalidades)
                                    @foreach ($modalidades->where('id', $usuarioDash?->docente?->modalidad_coordina_id ?? 0) as $modalidad)
                                        <option value="{{ $modalidad->id }}" selected>Solo {{ $modalidad->nombre }}</option>
                                    @endforeach
                                @endisset
                            @endhasanyrole
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Título</label>
                        <input type="text" name="titulo" required class="w-full border-stone-200 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Mensaje</label>
                        <textarea name="mensaje" rows="4" required class="w-full border-stone-200 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-stone-50 dark:bg-neutral-800/70 border-t border-stone-100 dark:border-neutral-800 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white transition-colors cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 text-sm font-semibold rounded-xl transition-colors cursor-pointer">Publicar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR COMUNICADO -->
    <div x-data="{ open: false, id: '', titulo: '', mensaje: '' }" @abrir-modal-editar-aviso.window="open = true; id = $event.detail.id; titulo = $event.detail.titulo; mensaje = $event.detail.mensaje;" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="open = false"></div>
        <div x-show="open" x-transition class="relative bg-white dark:bg-neutral-900 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-stone-200 dark:border-neutral-800">
            <div class="px-6 py-4 border-b border-stone-100 dark:border-neutral-800 bg-stone-50 dark:bg-neutral-800/70 flex justify-between items-center">
                <h3 class="text-base font-bold text-neutral-900 dark:text-neutral-100">Editar Comunicado</h3>
                <button type="button" @click="open = false" class="text-neutral-400 hover:text-rose-500 transition-colors cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form x-bind:action="'/dashboard/avisos/' + id" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Título</label>
                        <input type="text" name="titulo" x-model="titulo" required class="w-full border-stone-200 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Mensaje</label>
                        <textarea name="mensaje" x-model="mensaje" rows="4" required class="w-full border-stone-200 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl focus:ring-[#e6ac27] focus:border-[#e6ac27] text-sm"></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 bg-stone-50 dark:bg-neutral-800/70 border-t border-stone-100 dark:border-neutral-800 flex justify-end gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white transition-colors cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 text-sm font-semibold rounded-xl transition-colors cursor-pointer">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DECISIÓN DE CLASE -->
    <div x-data="{ open: false, asignacionId: '', asignaturaInfo: '', aulaInfo: '', horaInfo: '' }" @abrir-modal-decision-clase.window="open = true; asignacionId = $event.detail.id; asignaturaInfo = $event.detail.asignatura; aulaInfo = $event.detail.aula; horaInfo = $event.detail.hora;" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="open = false"></div>
        <div x-show="open" x-transition class="relative bg-white dark:bg-neutral-900 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-stone-200 dark:border-neutral-800">
            <div class="bg-[#FFFDF8] dark:bg-neutral-800/70 px-6 py-5 border-b border-stone-200/80 dark:border-neutral-800 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-neutral-900 flex items-center justify-center text-[#d49b1a] shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100 leading-tight" x-text="asignaturaInfo"></h2>
                    <p class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mt-0.5" x-text="aulaInfo + ' | ' + horaInfo"></p>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-neutral-900">
                <h3 class="text-center font-medium text-sm text-neutral-600 dark:text-neutral-300 mb-5">¿Qué deseas gestionar para esta clase?</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a x-bind:href="'{{ url('academico/asistencia/asignatura') }}/' + asignacionId" class="flex flex-col items-center justify-center gap-2.5 p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 bg-[#FFFDF8] dark:bg-neutral-800/60 text-neutral-900 dark:text-neutral-100 hover:bg-[#e6ac27] hover:text-neutral-950 hover:border-[#e6ac27] transition-colors cursor-pointer">
                        <span class="font-semibold uppercase tracking-wider text-[11px] text-center">Pasar<br>Asistencia</span>
                    </a>
                    <a x-bind:href="'{{ url('academico/notas/evaluar') }}/' + asignacionId" class="flex flex-col items-center justify-center gap-2.5 p-5 rounded-2xl border border-stone-200 dark:border-neutral-800 bg-[#FFFDF8] dark:bg-neutral-800/60 text-neutral-900 dark:text-neutral-100 hover:bg-[#e6ac27] hover:text-neutral-950 hover:border-[#e6ac27] transition-colors cursor-pointer">
                        <span class="font-semibold uppercase tracking-wider text-[11px] text-center">Ingresar<br>Calificaciones</span>
                    </a>
                </div>
            </div>

            <div class="bg-stone-50 dark:bg-neutral-800/70 px-6 py-3.5 border-t border-stone-100 dark:border-neutral-800 text-center">
                <button type="button" @click="open = false" class="text-xs font-semibold text-neutral-500 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white transition-colors uppercase tracking-wider py-1 cursor-pointer">
                    Cancelar y Cerrar
                </button>
            </div>
        </div>
    </div>
</x-app-layout>