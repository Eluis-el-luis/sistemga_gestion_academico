<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Configuración base de la notificación Toast (Estilo Teléfono)
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            customClass: { 
                popup: 'rounded-2xl shadow-lg border border-slate-100 dark:bg-slate-900 dark:border-slate-800 dark:text-white transition-colors' 
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // 1. Alertas de Éxito Flotantes
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: '{{ session("success") }}'
            });
        @endif

        // 2. Alertas de Inconsistencia (Bloqueantes por seguridad)
        @if(session('error'))
            Swal.fire({
                title: 'Verificación del Sistema',
                text: '{{ session("error") }}',
                icon: 'warning',
                confirmButtonColor: '#e6ac27',
                customClass: { 
                    popup: 'rounded-3xl border border-stone-200 shadow-xl dark:bg-slate-900 dark:border-slate-800 dark:text-white transition-colors' 
                }
            });
        @endif

        // 3. Interceptor Universal para Botones de Borrado
        // Uso: Agrega la clase "form-eliminar" a tu formulario y listo.
        document.querySelectorAll('.form-eliminar').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); 
                
                Swal.fire({
                    title: '¿Acción irreversible?',
                    text: "Esta información será borrada del sistema.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    customClass: { 
                        popup: 'rounded-3xl border border-slate-200 shadow-xl dark:bg-slate-900 dark:border-slate-800 dark:text-white transition-colors' 
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