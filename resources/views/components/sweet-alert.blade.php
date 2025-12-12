<script>
    console.log("SweetAlert COMPONENT LOADED");

    // Fungsi reusable untuk semua alert
        function showSwalAlert({ icon, title, text, timer = 2000, confirmButton = false }) {
            // unique key agar per-alert per-path
            const alertKey = 'swal_alert_shown::' + window.location.pathname + '::' + (title||'') + '::' + (text||'');

            // jika sudah ditandai sudah tampil di sessionStorage => skip
            if (sessionStorage.getItem(alertKey)) {
                console.log('swal: already shown in this session, skipping');
                return;
            }

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
                // tandai sebagai sudah tampil di session
                try { sessionStorage.setItem(alertKey, '1'); } catch(e){}

                // replaceState agar history entry tidak trigger ulang visual state
                try {
                    const newState = Object.assign({}, history.state || {}, { swal_shown: true });
                    history.replaceState(newState, '', window.location.href);
                } catch(e){}
            });
        }

        // tutup swal bila halaman dikembalikan dari bfcache dan alert sudah ditampilkan
        window.addEventListener('pageshow', function(e){
            if (e.persisted) {
                // build key sama seperti di showSwalAlert
                // (cek semua possible alert keys is expensive so we check history.state.swal_shown first)
                if (history.state && history.state.swal_shown) {
                    if (window.Swal) {
                        try { Swal.close(); } catch(e){}
                    }
                }
            }
        }, false);

        // Optional safety: jika user menavigasi back/forward and state has swal_shown, ensure no auto-show
        window.addEventListener('popstate', function(e){
            if (history.state && history.state.swal_shown) {
                // nothing to do; prevents logic that depends on session('alert') from re-running visually
                if (window.Swal) {
                    try { Swal.close(); } catch(e){}
                }
            }
        });

    </script>

    @if (session('alert'))
        <script>
            document.addEventListener('alpine:init', () => {
                // session('alert_once') dipakai server-side; client-side pastikan hanya show sekali per session
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