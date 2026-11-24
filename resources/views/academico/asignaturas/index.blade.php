{{-- resources/views/academico/asignaturas/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4 bg-white dark:bg-neutral-900 border border-stone-200/80 dark:border-neutral-800 rounded-2xl px-5 py-3.5 shadow-xs">
            <div>
                <h2 class="font-bold text-xl text-neutral-900 dark:text-white">Catálogo de Asignaturas</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">Configuración del currículo</p>
            </div>
            @hasanyrole('directora|subdirectora|Director|Subdirector')
                <button type="button" @click="$dispatch('abrir-modal-crear')" class="px-4 py-2 bg-neutral-900 dark:bg-[#e6ac27] hover:bg-neutral-800 dark:hover:bg-[#cf9719] text-white dark:text-neutral-950 text-xs font-bold uppercase tracking-wider rounded-xl transition-colors cursor-pointer">
                    + Nueva Asignatura
                </button>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="py-8 bg-stone-50 dark:bg-neutral-950 min-h-screen transition-colors" x-data="{ modalCrear: false, modalEditar: false, asigId: '', asigNombre: '' }" @abrir-modal-crear.window="modalCrear = true" @abrir-modal-editar.window="modalEditar = true; asigId = $event.detail.id; asigNombre = $event.detail.nombre;">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl border border-stone-200 dark:border-neutral-800 shadow-xs overflow-hidden">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-[#FFFDF8] dark:bg-neutral-800/70 border-b border-stone-200 dark:border-neutral-800">
                            <th class="px-6 py-4 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Nombre de la Asignatura</th>
                            @hasanyrole('directora|subdirectora|Director|Subdirector')
                                <th class="px-6 py-4 text-xs font-semibold text-neutral-500 dark:text-neutral-400 uppercase tracking-wider text-right">Acciones</th>
                            @endhasanyrole
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-neutral-800 text-neutral-800 dark:text-neutral-200">
                        @foreach ($asignaturas as $asignatura)
                            <tr class="hover:bg-stone-50 dark:hover:bg-neutral-800/40 transition-colors">
                                <td class="px-6 py-4 font-semibold">{{ $asignatura->nombre }}</td>
                                @hasanyrole('directora|subdirectora|Director|Subdirector')
                                    <td class="px-6 py-4 text-right flex justify-end gap-3">
                                        <button type="button" @click="$dispatch('abrir-modal-editar', { id: '{{ $asignatura->id }}', nombre: @js($asignatura->nombre) })" class="text-amber-600 dark:text-amber-400 hover:underline text-xs font-semibold cursor-pointer">Editar</button>
                                        <form action="{{ route('academico.asignaturas.destroy', $asignatura->id) }}" method="POST" class="inline form-eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-600 text-xs font-semibold cursor-pointer">Eliminar</button>
                                        </form>
                                    </td>
                                @endhasanyrole
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="modalCrear" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
            <div class="bg-white dark:bg-neutral-900 rounded-3xl w-full max-w-sm overflow-hidden border border-stone-200 dark:border-neutral-800">
                <form action="{{ route('academico.asignaturas.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <h3 class="text-base font-bold text-neutral-900 dark:text-white">Nueva Asignatura</h3>
                    <div>
                        <label class="block text-xs font-semibold text-neutral-500 uppercase mb-1.5">Nombre Oficial</label>
                        <input type="text" name="nombre" required class="w-full border-stone-300 dark:border-neutral-700 bg-stone-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 rounded-xl text-sm">
                    </div>