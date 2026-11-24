<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="text-neutral-400 hover:text-[#d49b1a] dark:hover:text-[#e6ac27] transition-colors cursor-pointer" title="Volver al Panel">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-neutral-900 dark:text-white leading-tight">Respaldos de la Base de Datos</h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Copia de seguridad y restauración del sistema</p>
                </div>
            </div>

            <form method="POST" action="{{ route('academico.respaldos.store') }}" id="form-crear-respaldo" class="shrink-0">
                @csrf
                <input type="hidden" name="motivo" value="manual-desde-interfaz">
                <button type="button" onclick="generarRespaldo()" class="bg-[#e6ac27] hover:bg-[#cf9719] text-neutral-950 font-bold py-2.5 px-5 rounded-xl transition-colors flex items-center gap-2 text-xs uppercase tracking-wider cursor-pointer">
                    Crear respaldo ahora
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-neutral-900 dark:text-white text-base">Conexión activa</h3>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Las operaciones se aplican sobre la base conectada:</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2.5 text-xs">
                        <span class="px-3 py-1 rounded-full font-bold uppercase bg-amber-50 dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30">{{ strtoupper((string) ($conexion['driver'] ?? '')) }}</span>
                        <span class="px-3 py-1 rounded-full font-semibold bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200">{{ $conexion['host'] ?? 'local' }}</span>
                        <span class="px-3 py-1 rounded-full font-semibold bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200">Base: {{ $conexion['base'] ?? '-' }}</span>
                        <span class="px-3 py-1 rounded-full font-semibold bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200">{{ $conexion['tablas'] ?? 0 }} tablas</span>
                    </div>
                </div>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 max-w-xs border-l-4 border-[#e6ac27]/50 pl-3">
                    Descarga el archivo .zip y consérvalo fuera del equipo como respaldo adicional.
                </p>
            </div>

            <div class="bg-white dark:bg-neutral-900 overflow-hidden shadow-xs rounded-3xl border border-stone-200 dark:border-neutral-800">
                <div class="p-6 border-b border-stone-100 dark:border-neutral-800 flex items-center justify-between">
                    <h3 class="font-bold text-base text-neutral-900 dark:text-white">Historial de respaldos</h3>
                    <span class="text-xs font-semibold text-neutral-400 uppercase">{{ count($lista) }} de {{ config('respaldos.retencion') }}</span>
                </div>

                @if (count($lista) === 0)
                    <div class="p-12 text-center text-neutral-500 dark:text-neutral-400 text-sm">
                        Aún no existe ningún respaldo generado.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr class="text-xs uppercase tracking-wider text-neutral-500 dark:text-neutral-400 border-b border-stone-100 dark:border-neutral-800 bg-[#FFFDF8] dark:bg-neutral-800/70">
                                    <th class="px-6 py-3.5 font-semibold">Archivo</th>
                                    <th class="px-4 py-3.5 font-semibold">Generado</th>
                                    <th class="px-4 py-3.5 font-semibold">Contenido</th>
                                    <th class="px-4 py-3.5 font-semibold">Motivo</th>
                                    <th class="px-4 py-3.5 font-semibold text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                                @foreach ($lista as$respaldo)
                                    <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                        <td class="px-6 py-4">
                                            <p class="font-semibold text-neutral-900 dark:text-white text-xs sm:text-sm break-all">{{ $respaldo['nombre'] }}</p>
                                            <p class="text-xs text-neutral-400 mt-0.5">{{ number_format(($respaldo['bytes'] ?? 0) / 1024, 1) }} KB</p>
                                        </td>
                                        <td class="px-4 py-4 text-xs whitespace-nowrap">
                                            <p class="font-semibold">{{ \Carbon\Carbon::parse($respaldo['creado'])->format('d/m/Y H:i') }}</p>
                                            <p class="text-neutral-400 mt-0.5">por {{ $respaldo['generado_por'] ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-4 text-xs whitespace-nowrap">
                                            <p class="font-semibold">{{ $respaldo['motor'] ?? '?' }} · {{$respaldo['base'] ?? '?' }}</p>
                                            @if (!is_null($respaldo['filas']))
                                                <p class="text-neutral-400 mt-0.5">{{ number_format((int) $respaldo['filas']) }} filas</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-stone-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200">{{ $respaldo['motivo'] ?? '-' }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-center gap-2 text-xs font-semibold">
                                                <a href="{{ route('academico.respaldos.descargar', $respaldo['nombre']) }}" class="px-3 py-1.5 rounded-xl bg-stone-100 dark:bg-neutral-800 hover:bg-stone-200 dark:hover:bg-neutral-700 text-neutral-800 dark:text-neutral-100 cursor-pointer">
                                                    Descargar
                                                </a>

                                                @hasanyrole('directora|Director')
                                                    <form method="POST" action="{{ route('academico.respaldos.destroy', $respaldo['nombre']) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" onclick="eliminarRespaldo(@js($respaldo['nombre']), this.closest('form'))" class="px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 hover:bg-rose-100 cursor-pointer">
                                                            Eliminar
                                                        </button>
                                                    </form>

                                                    <button type="button" @click="abrirRestaurar(@js($respaldo['nombre']), @js($respaldo['base'] ?? '?'), '{{ \Carbon\Carbon::parse($respaldo['creado'])->format('d/m/Y H:i') }}', '{{ isset($respaldo['filas']) ? number_format((int) $respaldo['filas']) : '—' }}', @js(route('academico.respaldos.restaurar',$respaldo['nombre'])))" class="px-3 py-1.5 rounded-xl bg-amber-50 dark:bg-neutral-800 text-[#b88212] dark:text-[#e6ac27] border border-[#e6ac27]/30 cursor-pointer">
                                                        Restaurar
                                                    </button>
                                                @endhasanyrole
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

        <div x-show="modalRestaurar" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
            <div class="relative bg-white dark:bg-neutral-900 rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden border border-stone-200 dark:border-neutral-800">
                <form method="POST" :action="urlRestaurar">
                    @csrf
                    <div class="p-5 bg-rose-600 text-white">
                        <h3 class="font-bold text-base">Restaurar base de datos</h3>
                        <p class="text-rose-100 text-xs mt-0.5">Operación destructiva — requiere confirmación escrita</p>
                    </div>

                    <div class="p-6 space-y-4 text-sm">
                        <div class="bg-stone-50 dark:bg-neutral-800/60 rounded-2xl p-4 space-y-1 text-xs text-neutral-700 dark:text-neutral-300">
                            <p><strong>Archivo:</strong> <span class="font-mono" x-text="archivoRestaurar"></span></p>
                            <p><strong>Generado:</strong> <span x-text="fechaRestaurar"></span> · <span x-text="filasRestaurar"></span> filas</p>
                            <p><strong>Base original:</strong> <span x-text="baseRestaurar"></span></p>
                        </div>

                        <p class="text-xs text-neutral-600 dark:text-neutral-300">
                            Se creará primero un respaldo de seguridad del estado actual y luego se aplicará la restauración.
                        </p>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-neutral-500 mb-1.5">Escribe <strong class="text-rose-600">RESTAURAR</strong></label>
                            <input type="text" name="confirmacion" x-model="confirmacionTexto" autocomplete="off" spellcheck="false" class="w-full rounded-xl border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 font-mono font-bold text-sm" placeholder="RESTAURAR">
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-stone-50 dark:bg-neutral-800/70 flex justify-end gap-3 border-t border-stone-100 dark:border-neutral-800">
                        <button type="button" @click="modalRestaurar = false" class="px-4 py-2 rounded-xl bg-white dark:bg-neutral-800 border border-stone-300 dark:border-neutral-700 text-xs font-semibold cursor-pointer">Cancelar</button>
                        <button type="submit" :disabled="confirmacionTexto !== 'RESTAURAR'" class="px-5 py-2 rounded-xl text-xs font-bold uppercase text-white bg-rose-600 hover:bg-rose-700 disabled:bg-stone-200 disabled:text-neutral-400 disabled:cursor-not-allowed cursor-pointer">
                            Restaurar ahora
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function generarRespaldo() {
            const darkNow = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: '¿Crear respaldo ahora?',
                text: 'Se generará una copia completa de la base de datos activa.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, crear',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e6ac27',
                input: 'text',
                inputPlaceholder: 'Motivo (opcional)',
                inputAttributes: { maxlength: 100 },
                background: darkNow ? '#171717' : '#ffffff',
                color: darkNow ? '#f5f5f5' : '#171717'
            }).then((result) => {
                if (!result.isConfirmed) return;
                const motivo = (result.value || '').trim();
                if (motivo) document.querySelector('#form-crear-respaldo input[name="motivo"]').value = motivo;
                document.getElementById('form-crear-respaldo').submit();
            });
        }

        function eliminarRespaldo(nombre, formulario) {
            const darkNow = document.documentElement.classList.contains('dark');
            Swal.fire({
                title: '¿Eliminar respaldo?',
                text: `Se eliminará ${nombre} de forma permanente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#e11d48',
                background: darkNow ? '#171717' : '#ffffff',
                color: darkNow ? '#f5f5f5' : '#171717'
            }).then((result) => {
                if (result.isConfirmed && formulario) formulario.submit();
            });
        }
    </script>
</x-app-layout>