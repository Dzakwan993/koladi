<script>
    console.log("SweetAlert COMPONENT LOADED");

    // Fungsi reusable untuk semua alert
    function showSwalAlert({ icon, title, text, timer = 2000, confirmButton = false }) {
        Swal.fire({
            icon: icon,
            title: title,
            text: text,
            showConfirmButton: confirmButton ? true : false,
            timer: confirmButton ? undefined : timer,
            timerProgressBar: confirmButton ? false : true,
            position: 'center',
            toast: false,
            background: '#f7faff',
            color: '#2b2b2b',
            customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title',
                htmlContainer: 'swal-custom-text'
            },
            didOpen: (popup) => {
                popup.classList.add('swal-fade-in');
            },
            willClose: (popup) => {
                popup.classList.remove('swal-fade-in');
                popup.classList.add('swal-fade-out');
            }
        }).then(() => {
            if (window.history.replaceState) {
                window.history.replaceState(null, '', window.location.href);
            }
        });
    }
</script>

@if (session('alert'))
    <script>
        document.addEventListener('alpine:init', () => {
            showSwalAlert({
                icon: '{{ session('alert.icon') }}',
                title: '{{ session('alert.title') }}',
                text: '{{ session('alert.text') }}',
                timer: 2000,
                confirmButton: false
            });
        });
    </script>

    @include('components.sweet-alert-style')
@endif

@if ($errors->any())
    <script>
        document.addEventListener('alpine:init', () => {
            showSwalAlert({
                icon: 'error',
                title: 'Gagal!',
                text: @json($errors->first()),
                confirmButton: true // error biasanya pakai confirm button
            });
        });
    </script>

    @include('components.sweet-alert-style')
@endif
