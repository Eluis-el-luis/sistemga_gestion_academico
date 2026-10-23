<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <!-- Flecha de regreso -->
                <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-[#e6ac27] transition-colors mr-2" title="Volver al Panel">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>

                <div>
                    <h2 class="font-black text-2xl text-[#3d2c1d] dark:text-white leading-tight">
                        Respaldos de la Base de Datos
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Copia de seguridad y restauración del contenido académico</p>
                </div>
            </div>

            <form method="POST" action="{{ route('academico.respaldos.store') }}" id="form-crear-respaldo" x-data class="shrink-0">
                @csrf
                <input type="hidden" name="motivo" value="manual-desde-interfaz">
                <button type="button"
                        @click="generarRespaldo()"
                        class="bg-[#e6ac27] hover:bg-[#c48e1b] text-white font-black py-2.5 px-6 rounded-xl shadow-md transition-all transform hover:-translate-y-0.5 flex items-center gap-2 text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Crear respaldo ahora
                </button>
            </form>
        </div>
    </x-slot>

    <!-- ALPINE MAESTRO: modal de restauración -->
    <div class="py-10 bg-slate-50 dark:bg-slate-900 min-h-screen relative" x-data="{
            modalRestaurar: false,
            archivoRestaurar: '',
            baseRestaurar: '',
            fechaRestaurar: '',
            filasRestaurar: '',
            urlRestaurar: '',
            confirmacionTexto: '',
            abrirRestaurar(nombre, base, fecha, filas, url) {
                this.archivoRestaurar = nombre;
                this.baseRestaurar = base;
                this.fechaRestaurar = fecha;
                this.filasRestaurar = filas;
                this.urlRestaurar = url;
                this.confirmacionTexto = '';
                this.modalRestaurar = true;
            }
        }">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Mensajes de Sesión -->
            @if (session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm flex items-center gap-3 font-medium">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- ESTADO DE LA CONEXIÓN -->
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-3xl border border-slate-200 dark:border-slate-700">
                <div class="p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-black text-[#3d2c1d] dark:text-white">Conexión activa</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                                Los respaldos y restauraciones se aplican sobre esta base de datos:
                            </p>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wide
                                            {{ $conexion['driver'] === 'pgsql' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ strtoupper($conexion['driver']) }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    {{ $conexion['host'] }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    Base: {{ $conexion['base'] }}
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    {{ $conexion['tablas'] }} tablas
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="text-xs text-slate-400 dark:text-slate-500 max-w-xs border-l-4 border-[#e6ac27]/40 pl-3 leading-relaxed">
                        <strong class="text-slate-500 dark:text-slate-400">Recuerda:</strong> un respaldo sin copia fuera de este
                        equipo no es un respaldo. Descarga el .zip y guárdalo en un lugar seguro con regularidad.
                    </div>
                </div>
            </div>

            <!-- LISTADO DE RESPALDOS -->
            <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-3xl border border-slate-200 dark:border-slate-700">
                <div class="p-6 md:p-8 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <h3 class="font-black text-lg text-[#3d2c1d] dark:text-white">Historial de respaldos</h3>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ count($lista) }} de {{ config('respaldos.retencion') }} (retención)</span>
                </div>

                @if (count($lista) === 0)
                    <div class="p-12 text-center">
                        <svg class="w-14 h-14 mx-auto text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <p class="mt-4 font-black text-slate-500 dark:text-slate-400">Aún no existe ningún respaldo</p>
                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Presiona «Crear respaldo ahora» para proteger la información del colegio.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-[11px] uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-700">
                                    <th class="px-6 py-4 font-black">Archivo</th>
                                    <th class="px-4 py-4 font-black">Generado</th>
                                    <th class="px-4 py-4 font-black">Contenido</th>
                                    <th class="px-4 py-4 font-black">Motivo</th>
                                    <th class="px-4 py-4 font-black text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-700/60">
                                @foreach ($lista as $respaldo)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-bold text-[#3d2c1d] dark:text-white text-xs sm:text-sm break-all">{{ $respaldo['nombre'] }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5">{{ number_format($respaldo['bytes'] / 1024, 1) }} KB</p>
                                        </td>
                                        <td class="px-4 py-4 text-slate-600 dark:text-slate-300 whitespace-nowrap">
                                            <p class="font-semibold text-xs">{{ \Carbon\Carbon::parse($respaldo['creado'])->format('d/m/Y H:i') }}</p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">por {{ $respaldo['generado_por'] }}</p>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <p class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ $respaldo['motor'] }} · {{ $respaldo['base'] }}</p>
                                            @if (!is_null($respaldo['filas']))
                                                <p class="text-[11px] text-slate-400 mt-0.5">{{ number_format($respaldo['filas']) }} filas</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold
                                                        {{ str_starts_with($respaldo['motivo'], 'automatico') ? 'bg-sky-50 text-sky-600' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}">
                                                {{ $respaldo['motivo'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <!-- Descargar -->
                                                <a href="{{ route('academico.respaldos.descargar', $respaldo['nombre']) }}"
                                                   title="Descargar .zip"
                                                   class="p-2 rounded-xl text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                </a>

                                                @hasrole('Director')
                                                    <!-- Eliminar -->
                                                    <form method="POST" action="{{ route('academico.respaldos.destroy', $respaldo['nombre']) }}"
                                                          class="form-eliminar" x-data>
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                                @click="eliminarRespaldo('{{ $respaldo['nombre'] }}', $el.closest('form'))"
                                                                title="Eliminar respaldo"
                                                                class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/30 transition-colors">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>

                                                    <!-- Restaurar -->
                                                    <button type="button"
                                                            @click="abrirRestaurar(
                                                                '{{ $respaldo['nombre'] }}',
                                                                '{{ $respaldo['base'] }}',
                                                                '{{ \Carbon\Carbon::parse($respaldo['creado'])->format('d/m/Y H:i') }}',
                                                                '{{ $respaldo['filas'] !== null ? number_format($respaldo['filas']) : '—' }}',
                                                                '{{ route('academico.respaldos.restaurar', $respaldo['nombre']) }}')"
                                                            title="Restaurar esta copia"
                                                            class="p-2 rounded-xl text-slate-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/30 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 4v6h6m-1-2a8 8 0 1114 0 8 8 0 01-14 0"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 20v-6h-6m-1 2a8 8 0 11-14 0 8 8 0 0114 0"></path></svg>
                                                    </button>
                                                @endhasrole
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- ==================== MODAL DE RESTAURACIÓN ==================== -->
        <div x-show="modalRestaurar" class="fixed inset-0 z-[70] flex items-center justify-center p-4" style="display: none;">
            <div class="absolute inset-0 bg-slate-900/70 backdrop-blur-sm" @click="modalRestaurar = false" x-show="modalRestaurar" x-transition.opacity style="display: none;"></div>

            <div x-show="modalRestaurar" x-transition class="relative bg-white dark:bg-slate-800 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden" style="display: none;">
                <form method="POST" action="#" id="form-restaurar"
                      @submit.prevent="if (confirmacionTexto === 'RESTAURAR' && urlRestaurar) { this.action = urlRestaurar; $el.submit(); }">
                    @csrf

                    <div class="p-6 bg-rose-600 text-white">
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <h3 class="font-black text-lg leading-tight">Restaurar base de datos</h3>
                                <p class="text-rose-100 text-xs font-semibold">Operación destructiva — requiere confirmación</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-2xl p-4 text-sm space-y-1.5">
                            <p class="text-slate-600 dark:text-slate-300"><span class="font-black">Archivo:</span> <span class="font-mono text-xs" x-text="archivoRestaurar"></span></p>
                            <p class="text-slate-600 dark:text-slate-300"><span class="font-black">Generado:</span> <span x-text="fechaRestaurar"></span> · <span x-text="filasRestaurar"></span> filas</p>
                            <p class="text-slate-600 dark:text-slate-300"><span class="font-black">Base original:</span> <span x-text="baseRestaurar"></span></p>
                        </div>

                        <div class="text-sm text-slate-600 dark:text-slate-300 space-y-1">
                            <p>Todos los datos <strong class="text-rose-600">actuales</strong> serán <strong class="text-rose-600">borrados</strong> y sustituidos por el contenido de este respaldo.</p>
                            <p class="text-xs text-slate-400">Antes de restaurar, el sistema genera automáticamente un respaldo de seguridad del estado actual.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-1.5">
                                Escribe <span class="text-rose-600">RESTAURAR</span> para habilitar el botón
                            </label>
                            <input type="text" x-model="confirmacionTexto" autocomplete="off" spellcheck="false"
                                   class="w-full rounded-xl border-slate-200 dark:border-slate-600 dark:bg-slate-900 focus:ring-rose-500 focus:border-rose-500 font-mono font-bold tracking-widest"
                                   placeholder="RESTAURAR">
                        </div>
                    </div>

                    <div class="p-6 pt-0 flex justify-end gap-3">
                        <button type="button" @click="modalRestaurar = false"
                                class="py-2.5 px-5 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-sm hover:bg-slate-200 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="confirmacionTexto !== 'RESTAURAR'"
                                class="py-2.5 px-5 rounded-xl font-black text-sm text-white transition-all
                                       bg-rose-600 hover:bg-rose-700 disabled:bg-slate-200 disabled:text-slate-400 dark:disabled:bg-slate-700 disabled:cursor-not-allowed">
                            Sí, restaurar ahora
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts del módulo (el layout carga SweetAlert2 y Alpine desde el head) -->
    <script>
        function generarRespaldo() {
            Swal.fire({
                title: '¿Crear respaldo ahora?',
                text: 'Se copiará el contenido completo de la base de datos activa.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear respaldo',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e6ac27',
                input: 'text',
                inputPlaceholder: 'Motivo (opcional, ej: cierre-de-corte)',
                inputAttributes: { maxlength: 100 }
            }).then((result) => {
                if (!result.isConfirmed) return;

                const motivo = (result.value || '').trim();
                if (motivo) document.querySelector('#form-crear-respaldo input[name=motivo]').value = motivo;

                Swal.fire({
                    title: 'Generando respaldo...',
                    text: 'Leyendo todas las tablas. No cierres esta página.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                document.getElementById('form-crear-respaldo').submit();
            });
        }

        function eliminarRespaldo(nombre, formulario) {
            Swal.fire({
                title: '¿Eliminar respaldo?',
                html: `El archivo <strong class="font-mono">${nombre}</strong> se borrará definitivamente del disco.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e11d48'
            }).then((result) => {
                if (result.isConfirmed) formulario.submit();
            });
        }
    </script>
</x-app-layout>
