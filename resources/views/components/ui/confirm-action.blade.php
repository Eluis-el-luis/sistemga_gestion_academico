@props([
    'action',
    'method' => 'POST',
    'title' => 'Confirmar acción',
    'message' => '¿Deseas continuar con esta operación?',
    'confirmText' => 'Confirmar',
    'buttonClass' => 'px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider transition-colors cursor-pointer',
])

<form
    method="POST"
    action="{{ $action }}"
    x-data="{ confirmSubmit(e) { e.preventDefault(); const form = this.$el; const darkNow = document.documentElement.classList.contains('dark'); Swal.fire({ title: @js($title), text: @js($message), icon: 'warning', showCancelButton: true, confirmButtonColor: '#e6ac27', cancelButtonColor: '#64748b', confirmButtonText: @js($confirmText), cancelButtonText: 'Cancelar', background: darkNow ? '#0f172a' : '#ffffff', color: darkNow ? '#f1f5f9' : '#3d2c1d' }).then((res) => { if (res.isConfirmed) form.submit(); }); } }"
    @submit="confirmSubmit($event)"
    class="inline-block"
>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif
    <button type="submit" class="{{ $buttonClass }}">
        {{ $slot }}
    </button>
</form>