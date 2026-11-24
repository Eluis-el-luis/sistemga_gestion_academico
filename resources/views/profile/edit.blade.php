<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">
                    Mi Perfil Institucional
                </h2>
                <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400 mt-0.5">
                    Gestión de datos personales y seguridad de cuenta
                </p>
            </div>

            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white font-semibold text-xs uppercase tracking-wider px-3.5 py-2 rounded-xl bg-stone-100 dark:bg-neutral-800 hover:bg-stone-200 dark:hover:bg-neutral-700 transition-colors cursor-pointer">
                Volver al Panel
            </a>
        </div>
    </x-slot>

    @php
        $perfilUser = Auth::user();
        $avatarUrl = ($perfilUser && !empty($perfilUser->avatar)) ? asset('storage/' . $perfilUser->avatar) : null;
        $iniciales = mb_strtoupper(mb_substr(trim((string) ($perfilUser?->nombre_completo ?? $perfilUser?->name ?? 'U')), 0, 1, 'UTF-8'), 'UTF-8');
        $docentePerfil = $perfilUser?->loadMissing('docente')->docente;
    @endphp

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <div class="w-full lg:w-1/3 space-y-6">
                    <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 overflow-hidden">
                        <div class="h-24 bg-[#3d2c1d] dark:bg-neutral-800"></div>

                        <div class="px-6 pb-6">
                            <div class="flex justify-center -mt-12 mb-4 relative group w-24 h-24 mx-auto">
                                @if ($avatarUrl)
                                    <img id="avatar-preview" src="{{ $avatarUrl }}" class="w-24 h-24 rounded-full border-4 border-white dark:border-neutral-900 object-cover bg-white shadow-xs" alt="Avatar">
                                @else
                                    <div id="avatar-preview-fallback" class="w-24 h-24 rounded-full border-4 border-white dark:border-neutral-900 bg-stone-100 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-200 flex items-center justify-center text-2xl font-bold shadow-xs">
                                        {{ $iniciales }}
                                    </div>
                                    <img id="avatar-preview" class="w-24 h-24 rounded-full border-4 border-white dark:border-neutral-900 object-cover bg-white shadow-xs hidden" alt="Avatar">
                                @endif

                                <label for="avatar_upload" class="absolute inset-0 bg-black/55 rounded-full flex items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer text-[10px] font-bold uppercase tracking-wider">
                                    Cambiar
                                </label>
                            </div>

                            <div class="text-center mb-5">
                                <h3 class="text-base font-bold text-neutral-900 dark:text-white">{{ $perfilUser?->nombre_completo ?? $perfilUser?->name }}</h3>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $perfilUser?->email }}</p>
                            </div>

                            <div class="flex flex-wrap justify-center gap-1.5 mb-5">
                                @foreach ($perfilUser?->roles ?? collect() as $rol)
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-semibold uppercase tracking-wider bg-amber-50 dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30">
                                        {{ $rol->name }}
                                    </span>
                                @endforeach
                            </div>

                            <hr class="border-stone-100 dark:border-neutral-800 mb-4">

                            <div class="space-y-3 text-left text-xs">
                                <div>
                                    <span class="block font-semibold text-neutral-400 uppercase tracking-wider">Código Único (CUP)</span>
                                    <span class="block font-bold text-neutral-800 dark:text-neutral-200 mt-0.5">{{ $docentePerfil?->codigo_unico_persona ?? $docentePerfil?->codigo ?? 'No asignado' }}</span>
                                </div>
                                <div>
                                    <span class="block font-semibold text-neutral-400 uppercase tracking-wider">Miembro Desde</span>
                                    <span class="block font-bold text-neutral-800 dark:text-neutral-200 mt-0.5">{{ $perfilUser?->created_at?->translatedFormat('F Y') ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-2/3 space-y-6">
                    <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 sm:p-8">
                        <header class="mb-5">
                            <h2 class="text-base font-bold text-neutral-900 dark:text-white">Información del Perfil</h2>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Actualiza tu nombre y foto de perfil. El correo institucional permanece protegido.
                            </p>
                        </header>

                        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5 text-sm">
                            @csrf
                            @method('patch')

                            <input type="file" id="avatar_upload" name="avatar" class="hidden" accept="image/*" onchange="previewImage(event)">

                            <div>
                                <label for="nombre_completo" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Nombre Completo</label>
                                <input id="nombre_completo" name="nombre_completo" type="text" value="{{ old('nombre_completo', $perfilUser?->nombre_completo ?? $perfilUser?->name) }}" required class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm font-medium focus:ring-[#e6ac27] focus:border-[#e6ac27]">
                                <x-input-error class="mt-1.5" :messages="$errors->get('nombre_completo')" />
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Correo Institucional</label>
                                <input id="email" type="email" value="{{ $perfilUser?->email }}" disabled class="w-full border-stone-200 dark:border-neutral-800 bg-stone-100 dark:bg-neutral-800/60 text-neutral-500 dark:text-neutral-400 rounded-xl text-sm cursor-not-allowed" />
                                <input type="hidden" name="email" value="{{ $perfilUser?->email }}">
                            </div>

                            <div class="flex items-center gap-4 pt-1">
                                <button type="submit" class="inline-flex items-center gap-2 bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 font-bold px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">
                                    Guardar Datos
                                </button>

                                @if (session('status') === 'profile-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        Actualizado correctamente.
                                    </p>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-neutral-900 rounded-3xl shadow-xs border border-stone-200 dark:border-neutral-800 p-6 sm:p-8">
                        <header class="mb-5">
                            <h2 class="text-base font-bold text-neutral-900 dark:text-white">Seguridad y Contraseña</h2>
                            <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                Usa una contraseña segura para proteger tu cuenta.
                            </p>
                        </header>

                        <form method="post" action="{{ route('password.update') }}" class="space-y-5 text-sm">
                            @csrf
                            @method('put')

                            <div>
                                <label for="current_password" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Contraseña Actual</label>
                                <input id="current_password" name="current_password" type="password" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm focus:ring-[#e6ac27] focus:border-[#e6ac27]" autocomplete="current-password" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
                            </div>

                            <div>
                                <label for="password" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Nueva Contraseña</label>
                                <input id="password" name="password" type="password" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm focus:ring-[#e6ac27] focus:border-[#e6ac27]" autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider mb-1.5">Confirmar Nueva Contraseña</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm focus:ring-[#e6ac27] focus:border-[#e6ac27]" autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
                            </div>

                            <div class="flex items-center gap-4 pt-1">
                                <button type="submit" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider transition-colors cursor-pointer">
                                    Actualizar Contraseña
                                </button>

                                @if (session('status') === 'password-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                        Contraseña actualizada.
                                    </p>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const output = document.getElementById('avatar-preview');
                const fallback = document.getElementById('avatar-preview-fallback');
                if (output) {
                    output.src = reader.result;
                    output.classList.remove('hidden');
                }
                if (fallback) {
                    fallback.classList.add('hidden');
                }
            };
            if (event.target.files && event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</x-app-layout>