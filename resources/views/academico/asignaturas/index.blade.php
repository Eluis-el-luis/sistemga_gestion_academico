<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-[#3d2c1d] leading-tight">Catálogo de Asignaturas</h2>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Configuración del Currículo</p>
            </div>
            <button @click="$dispatch('abrir-modal-crear')" class="px-5 py-2.5 bg-[#3d2c1d] hover:bg-slate-800 text-white text-sm font-black rounded-xl shadow-md transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#e6ac27]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nueva Asignatura
            </button>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50 min-h-screen" x-data="{ modalCrear: false, modalEditar: false, asigId: '', asigNombre: '' }"
         @abrir-modal-crear.window="modalCrear = true"
         @abrir-modal-editar.window="modalEditar = true; asigId = $event.detail.id; asigNombre = $event.detail.nombre;">
        
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nombre de la Asignatura</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($asignaturas as $asignatura)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="block text-sm font-black text-[#3d2c1d]">{{ $asignatura->nombre }}</span>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-3">
                                    <button @click="$dispatch('abrir-modal-editar', { id: '{{ $asignatura->id }}', nombre: '{{ addslashes($asignatura->nombre) }}' })" class="text-amber-500 hover:text-amber-700 transition-colors" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <form action="{{ route('academico.asignaturas.destroy', $asignatura->id) }}" method="POST" class="inline form-eliminar">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-600 transition-colors" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL CREAR -->
        <div x-show="modalCrear" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="modalCrear" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalCrear = false"></div>
            <div x-show="modalCrear" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-lg font-black text-[#3d2c1d]">Nueva Asignatura</h3>
                    <button @click="modalCrear = false" class="text-slate-400 hover:text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <form action="{{ route('academico.asignaturas.store') }}" method="POST" class="p-6">
                    @csrf
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre Oficial</label>
                    <input type="text" name="nombre" required placeholder="Ej. Lengua y Literatura" class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm mb-4">
                    <button type="submit" class="w-full bg-[#3d2c1d] hover:bg-slate-800 text-white font-black py-3 rounded-xl shadow-md">Guardar Asignatura</button>
                </form>
            </div>
        </div>

        <!-- MODAL EDITAR -->
        <div x-show="modalEditar" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div x-show="modalEditar" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="modalEditar = false"></div>
            <div x-show="modalEditar" class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h3 class="text-lg font-black text-[#3d2c1d]">Editar Asignatura</h3>
                    <button @click="modalEditar = false" class="text-slate-400 hover:text-rose-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>
                <form x-bind:action="'{{ url('academico/asignaturas') }}/' + asigId" method="POST" class="p-6">
                    @csrf @method('PUT')
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre Oficial</label>
                    <input type="text" name="nombre" x-model="asigNombre" required class="w-full border-slate-200 bg-slate-50 rounded-xl focus:ring-[#e6ac27] text-sm text-[#3d2c1d] shadow-sm mb-4">
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-black py-3 rounded-xl shadow-md">Actualizar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 3500, icon: 'success', title: '{{ session("success") }}', customClass: { popup: 'rounded-2xl shadow-lg border border-slate-100' } });
            @endif
            @if(session('error'))
                Swal.fire({ title: 'No permitido', text: '{{ session("error") }}', icon: 'error', confirmButtonColor: '#3d2c1d', customClass: { popup: 'rounded-3xl border border-stone-200 shadow-xl' } });
            @endif

            document.querySelectorAll('.form-eliminar').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: '¿Eliminar asignatura?',
                        text: "Se borrará del catálogo permanentemente.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444', cancelButtonColor: '#94a3b8',
                        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
                        customClass: { popup: 'rounded-3xl shadow-xl' }
                    }).then((result) => { if (result.isConfirmed) this.submit(); });
                });
            });
        });
    </script>
</x-app-layout>