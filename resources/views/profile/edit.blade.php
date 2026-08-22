<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-slate-800 leading-tight">
            {{ __('Mi Perfil Institucional') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @php
                $usuario = Auth::user();
                // Buscamos si el usuario tiene un perfil de docente asociado
                $docente = \App\Models\Docente::where('usuario_id', $usuario->id)->first();
                // Si es docente, buscamos si es guía de algún aula
                $aulaGuia = $docente ? \App\Models\Aula::where('docente_guia_id', $docente->id)->first() : null;
                // Buscamos las materias que imparte
                $asignaciones = $docente ? \App\Models\AulaAsignaturaDocente::where('docente_id', $docente->id)->with('asignatura', 'aula.grado')->get() : collect();
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- COLUMNA IZQUIERDA: TARJETA DE IDENTIFICACIÓN -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 text-center relative overflow-hidden">
                        <!-- Fondo decorativo superior -->
                        <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-slate-800 to-slate-700"></div>
                        
                        <!-- Foto de Perfil (Placeholder) -->
                        <div class="relative mt-8 mb-4 inline-block">
                            <div class="w-24 h-24 rounded-full bg-white p-1 shadow-md mx-auto">
                                <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 overflow-hidden group cursor-pointer relative">
                                    <svg class="w-10 h-10 text-slate-400 group-hover:opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-slate-800">{{ $usuario->nombre_completo ?? $usuario->name }}</h3>
                        <p class="text-sm text-slate-500 font-medium mb-3">{{ $usuario->email }}</p>

                        <div class="flex flex-wrap justify-center gap-1.5 mb-6">
                            @foreach($usuario->roles as $rol)
                                <span class="px-3 py-1 bg-amber-50 text-amber-700 border border-amber-100 rounded-full text-[10px] font-bold uppercase tracking-widest">
                                    {{ $rol->name }}
                                </span>
                            @endforeach
                        </div>

                        <!-- Información Institucional Adicional -->
                        <div class="border-t border-slate-100 pt-4 text-left space-y-3">
                            @if($docente)
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Código Único (CUP)</p>
                                    <p class="text-sm font-semibold text-slate-700 font-mono mt-0.5">{{ $docente->codigo_unico_persona }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Miembro desde</p>
                                <p class="text-sm font-semibold text-slate-700 mt-0.5">{{ $usuario->created_at->translatedFormat('F Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA ACADÉMICA (Solo visible para docentes) -->
                    @if($docente)
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                        <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-widest border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Perfil Académico
                        </h4>

                        @if($aulaGuia)
                            <div class="mb-4 bg-emerald-50 border border-emerald-100 p-3 rounded-xl">
                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-0.5">Tutor de Aula (Guía)</p>
                                <p class="text-sm font-bold text-emerald-900">{{ $aulaGuia->nombre }}</p>
                                <p class="text-xs text-emerald-700">{{ $aulaGuia->grado->nombre ?? 'Grado' }} - {{ $aulaGuia->turno }}</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Asignaturas que imparto</p>
                            @if($asignaciones->count() > 0)
                                <ul class="space-y-2">
                                    @foreach($asignaciones as $asig)
                                        <li class="bg-slate-50 border border-slate-100 px-3 py-2 rounded-lg text-xs flex flex-col">
                                            <span class="font-bold text-slate-700">{{ $asig->asignatura->nombre }}</span>
                                            <span class="text-slate-500 font-medium">Sección: {{ $asig->aula->nombre ?? 'N/A' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-xs text-slate-400 italic">No tienes materias asignadas actualmente.</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- COLUMNA DERECHA: FORMULARIOS DE CONFIGURACIÓN -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Formulario de Información Personal -->
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                        <header class="mb-6 border-b border-slate-100 pb-4">
                            <h2 class="text-lg font-bold text-slate-800">
                                {{ __('Información del Perfil') }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ __("Actualiza los datos personales de tu cuenta y tu dirección de correo electrónico institucional.") }}
                            </p>
                        </header>

                        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                            @csrf
                        </form>

                        <form method="post" action="{{ route('profile.update') }}" class="space-y-6 max-w-xl">
                            @csrf
                            @method('patch')

                            <div>
                                <label for="nombre_completo" class="block text-sm font-bold text-slate-700 mb-1">Nombre Completo</label>
                                <input id="nombre_completo" name="nombre_completo" type="text" class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm transition-colors" value="{{ old('nombre_completo', $usuario->nombre_completo ?? $usuario->name) }}" required autofocus autocomplete="name" />
                                <x-input-error class="mt-2" :messages="$errors->get('nombre_completo')" />
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-bold text-slate-700 mb-1">Correo Institucional</label>
                                <input id="email" name="email" type="email" class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm transition-colors bg-slate-50 cursor-not-allowed" value="{{ old('email', $usuario->email) }}" required autocomplete="username" readonly title="Solicita a Administración si necesitas cambiar tu correo." />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                <p class="text-xs text-slate-400 mt-1">El correo institucional es asignado por administración y no puede modificarse desde aquí.</p>
                            </div>

                            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                                <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-slate-800 py-2.5 px-6 text-sm font-bold text-white shadow-sm hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all">
                                    {{ __('Guardar Cambios') }}
                                </button>

                                @if (session('status') === 'profile-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 3000)"
                                        class="text-sm font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg"
                                    >{{ __('Guardado correctamente.') }}</p>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Formulario de Cambio de Contraseña -->
                    <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-slate-100">
                        <header class="mb-6 border-b border-slate-100 pb-4">
                            <h2 class="text-lg font-bold text-slate-800">
                                {{ __('Actualizar Contraseña') }}
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                {{ __('Por motivos de seguridad, te recomendamos cambiar la contraseña temporal asignada por el colegio por una segura y aleatoria.') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('password.update') }}" class="space-y-6 max-w-xl">
                            @csrf
                            @method('put')

                            <div>
                                <label for="update_password_current_password" class="block text-sm font-bold text-slate-700 mb-1">Contraseña Actual</label>
                                <input id="update_password_current_password" name="current_password" type="password" class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm transition-colors" autocomplete="current-password" placeholder="Tu contraseña temporal o actual" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                            </div>

                            <div>
                                <label for="update_password_password" class="block text-sm font-bold text-slate-700 mb-1">Nueva Contraseña</label>
                                <input id="update_password_password" name="password" type="password" class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm transition-colors" autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <label for="update_password_password_confirmation" class="block text-sm font-bold text-slate-700 mb-1">Confirmar Nueva Contraseña</label>
                                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="block w-full rounded-xl border-slate-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm transition-colors" autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4 pt-4 border-t border-slate-100">
                                <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-amber-500 py-2.5 px-6 text-sm font-bold text-white shadow-sm hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-all">
                                    {{ __('Cambiar Contraseña') }}
                                </button>

                                @if (session('status') === 'password-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 3000)"
                                        class="text-sm font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg"
                                    >{{ __('Contraseña actualizada.') }}</p>
                                @endif
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>