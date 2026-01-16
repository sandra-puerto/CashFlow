<script>
    document.addEventListener('DOMContentLoaded', () => {

        // Errores de validación
        @if ($errors->any())
            Swal.fire({
                icon: 'warning',
                title: 'Datos inválidos',
                text: "{{ $errors->first() }}",
                confirmButtonText: 'Aceptar'
            });
            return;
        @endif

        // Mensajes de sesión
        @if(session('message'))
            @php
                $msg = session('message');
                $icon = $msg['success'] ? 'success' : 'error';
                $title = $msg['success'] ? 'Éxito' : 'Error';
            @endphp

            Swal.fire({
                icon: "{{ $icon }}",
                title: "{{ $title }}",
                text: "{{ $msg['message'] }}",
                confirmButtonText: 'Aceptar'
            });
        @endif

    });
</script>
