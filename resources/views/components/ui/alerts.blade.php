<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal === 'undefined') {
            return;
        }

        const isDark = document.documentElement.classList.contains('dark');
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3800,
            timerProgressBar: true,
            background: isDark ? '#0f172a' : '#ffffff',
            color: isDark ? '#f1f5f9' : '#3d2c1d',
            customClass: {
                popup: 'rounded-2xl shadow-lg border border-slate-100 dark:border-slate-800 transition-colors'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        @if (session('success'))
            Toast.fire({
                icon: 'success',
                title: @json(session('success'))
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: 'Verificación del Sistema',
                text: @json(session('error')),
                icon: 'warning',
                confirmButtonColor: '#e6ac27',
                background: isDark ? '#0f172a' : '#ffffff',
                color: isDark ? '#f1f5f9' : '#3d2c1d',
                customClass: {
                    popup: 'rounded-3xl border border-stone-200 dark:border-slate-800 shadow-xl transition-colors'
                }
            });
        @endif

        document.querySelectorAll('.form-eliminar').forEach((form) => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const darkNow = document.documentElement.classList.contains('dark');

                Swal.fire({
                    title: '¿Acción irreversible?',
                    text: 'Esta información será borrada del sistema.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    background: darkNow ? '#0f172a' : '#ffffff',
                    color: darkNow ? '#f1f5f9' : '#3d2c1d',
                    customClass: {
                        popup: 'rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl transition-colors'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        });
    });
</script>