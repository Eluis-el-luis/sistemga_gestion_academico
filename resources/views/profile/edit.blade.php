<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">
                    Mi Perfil Institucional
                </h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">
                    Gestión de credenciales y seguridad de cuenta
                </p>
            </div>
            
            <!-- Botón para regresar al Dashboard -->
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-stone-500 hover:text-[#3d2c1d] font-bold text-sm px-3 py-2 rounded-xl hover:bg-stone-100 transition-colors cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver al Panel
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            
            <!-- COLUMNA IZQUIERDA: TARJETA DE IDENTIFICACIÓN -->
            <div class="w-full lg:w-1/3 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative">
                    <!-- Banner superior -->
                    <div class="h-24 bg-[#3d2c1d]"></div>
                    
                    <div class="px-6 pb-6 relative">
                        <!-- Avatar con botón de subida integrado en el formulario principal -->
                        <div class="flex justify-center -mt-12 mb-4 relative group w-24 h-24 mx-auto">
                            @php
                                $avatarUrl = Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : null;
                                $iniciales = substr(Auth::user()->nombre_completo ?? Auth::user()->name ?? 'U', 0, 1);
                            @endphp
                            
                            @if($avatarUrl)
                                <img id="avatar-preview" src="{{ $avatarUrl }}" class="w-24 h-24 rounded-full border-4 border-white object-cover bg-white shadow-sm">
                            @else
                                <div id="avatar-preview-fallback" class="w-24 h-24 rounded-full border-4 border-white bg-slate-100 text-slate-400 flex items-center justify-center text-3xl font-black shadow-sm">
                                    {{ $iniciales }}
                                </div>
                                <img id="avatar-preview" class="w-24 h-24 rounded-full border-4 border-white object-cover bg-white shadow-sm hidden">
                            @endif

                            <!-- Botón overlay para cambiar foto -->
                            <label for="avatar_upload" class="absolute inset-0 bg-black/50 rounded-full flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer border-4 border-transparent">
                                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-[9px] font-bold uppercase tracking-wider">Cambiar</span>
                            </label>
                        </div>

                        <!-- Info del Usuario -->
                        <div class="text-center mb-6">
                            <h3 class="text-lg font-black text-[#3d2c1d]">{{ Auth::user()->nombre_completo ?? Auth::user()->name }}</h3>
                            <p class="text-xs font-medium text-slate-500 mt-0.5">{{ Auth::user()->email }}</p>
                        </div>

                        <!-- Roles (Etiquetas) -->
                        <div class="flex flex-wrap justify-center gap-2 mb-6">
                            @foreach(Auth::user()->roles as $rol)
                                <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200/60">
                                    {{ $rol->name }}
                                </span>
                            @endforeach
                        </div>

                        <hr class="border-slate-100 mb-4">

                        <!-- Datos Intocables (Solo Lectura) -->
                        <div class="space-y-4 text-left">
                            <div>
                                <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Código Único (CUP)</span>
                                @php
                                    $docente = Auth::user()->load('docente')->docente;
                                @endphp
                                <span class="block text-sm font-bold text-[#3d2c1d] mt-1">{{ $docente->codigo ?? 'No asignado' }}</span>
                            </div>
                            <div>
                                <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Miembro Desde</span>
                                <span class="block text-sm font-bold text-[#3d2c1d] mt-1">{{ Auth::user()->created_at->translatedFormat('F Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA DERECHA: FORMULARIOS INDEPENDIENTES -->
            <div class="w-full lg:w-2/3 space-y-6">
                
                <!-- FORMULARIO 1: INFORMACIÓN GENERAL Y AVATAR -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <header class="mb-6">
                        <h2 class="text-lg font-black text-[#3d2c1d]">Información del Perfil</h2>
                        <p class="mt-1 text-xs font-medium text-slate-500">
                            Actualiza tu nombre y foto de perfil. El correo institucional está bloqueado por seguridad.
                        </p>
                    </header>

                    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('patch')

                        <input type="file" id="avatar_upload" name="avatar" class="hidden" accept="image/*" onchange="previewImage(event)">

                        <div>
                            <label for="nombre_completo" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Nombre Completo</label>
                            <input id="nombre_completo" name="nombre_completo" type="text" value="{{ old('nombre_completo', Auth::user()->nombre_completo ?? Auth::user()->name) }}" required class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" />
                            <x-input-error class="mt-2" :messages="$errors->get('nombre_completo')" />
                        </div>

                        <div>
                            <label for="email" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-2">
                                Correo Institucional
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </label>
                            <!-- Campo bloqueado -->
                            <input id="email" type="email" value="{{ Auth::user()->email }}" disabled class="w-full border-slate-200 bg-slate-100 text-slate-500 rounded-xl shadow-sm text-sm font-medium cursor-not-allowed opacity-80" />
                            <!-- Si necesitas enviarlo oculto para que no dé error de validación, usa esto, pero Laravel lo ignora al usar ignore($this->user()->id) en el Request -->
                            <input type="hidden" name="email" value="{{ Auth::user()->email }}">
                            <p class="mt-2 text-[10px] font-bold text-slate-400">Este correo es asignado por administración y no puede modificarse.</p>
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 bg-[#3d2c1d] hover:bg-[#2b1f14] text-white font-black px-6 py-2.5 rounded-xl shadow-sm text-xs uppercase tracking-widest transition-all cursor-pointer">
                                Guardar Datos
                            </button>

                            @if (session('status') === 'profile-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm font-bold text-emerald-600">
                                    ✓ Actualizado correctamente.
                                </p>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- FORMULARIO 2: CAMBIO DE CONTRASEÑA -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <header class="mb-6">
                        <h2 class="text-lg font-black text-[#3d2c1d]">Seguridad y Contraseña</h2>
                        <p class="mt-1 text-xs font-medium text-slate-500">
                            Asegúrate de usar una contraseña larga y aleatoria para mantener tu cuenta institucional segura.
                        </p>
                    </header>

                    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                        @csrf
                        @method('put')

                        <div>
                            <label for="current_password" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Contraseña Actual</label>
                            <input id="current_password" name="current_password" type="password" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" autocomplete="current-password" />
                            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Nueva Contraseña</label>
                            <input id="password" name="password" type="password" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" autocomplete="new-password" />
                            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Confirmar Nueva Contraseña</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="w-full border-slate-200 bg-slate-50/50 rounded-xl shadow-sm text-sm font-medium text-[#3d2c1d] focus:ring-[#e6ac27] focus:border-[#e6ac27] transition-colors" autocomplete="new-password" />
                            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4 pt-2">
                            <button type="submit" class="inline-flex items-center gap-2 bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black px-6 py-2.5 rounded-xl shadow-sm text-xs uppercase tracking-widest transition-all cursor-pointer">
                                Actualizar Contraseña
                            </button>

                            @if (session('status') === 'password-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-sm font-bold text-emerald-600">
                                    ✓ Contraseña protegida.
                                </p>
                            @endif
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Script para previsualizar la foto de perfil antes de guardar -->
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('avatar-preview');
                const fallback = document.getElementById('avatar-preview-fallback');
                output.src = reader.result;
                output.classList.remove('hidden');
                if(fallback) fallback.classList.add('hidden');
            };
            if(event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</x-app-layout>