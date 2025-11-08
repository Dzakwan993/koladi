<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Koladi - @yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- ← TAMBAHKAN INI --}}
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

     <!-- Alpine.js dan Collapse Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Fix untuk mencegah overflow horizontal */
        * {
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            overflow-x: hidden;
        }
    </style>
</head>

<body class="bg-[#f3f6fc] flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <x-sidebar />

    <div class="flex-1 flex flex-col min-w-0">
        {{-- Topbar --}}
        <x-topbar />

        {{-- Konten utama --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto">
            @yield('content')
        </main>

        <x-hak-akses />
        <x-atur-hak />
    </div>

    {{-- semua script tambahan dari fitur lain masuk di sini --}}
    @stack('scripts')

    {{-- SweetAlert2 untuk pop-up --}}
    @if (session('alert'))
    <script>
        Swal.fire({
            icon: '{{ session('alert.icon') }}', // success, error, warning, info
            title: '{{ session('alert.title') }}',
            text: '{{ session('alert.text') }}',
            showConfirmButton: false,
            timer: 2000, // tampil 2 detik
            timerProgressBar: true,
            position: 'center',
            toast: false,
            background: '#f7faff', // lembut biru muda
            color: '#2b2b2b', // teks netral
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
        });
    </script>
    <script>
    // Hapus flash alert saat user menekan tombol back
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            // reload halaman tanpa cache
            window.location.reload();
        }
    });
</script>


    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Nunito:wght@400;600&display=swap');

        .swal-custom-popup {
            border: 1.8px solid #2f8cff; /* Biru lembut elegan */
            border-radius: 16px;
            box-shadow: 0 8px 28px rgba(47, 140, 255, 0.25);
            padding: 1.7rem;
            backdrop-filter: blur(10px);
            transform: scale(0.95);
        }

        .swal-custom-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a1a1a; /* Hitam lembut untuk profesional */
            margin-bottom: 0.4rem;
        }

        .swal-custom-text {
            font-family: 'Nunito', sans-serif;
            font-size: 1.05rem;
            color: #2b2b2b; /* Netral abu tua */
        }

        .swal2-timer-progress-bar {
            background: linear-gradient(90deg, #007bff, #33b3ff);
            height: 4px;
            border-radius: 10px;
        }

        /* Animasi masuk dan keluar lembut */
        .swal-fade-in {
            animation: fadeInSmooth 0.45s ease-out forwards;
        }

        .swal-fade-out {
            animation: fadeOutSmooth 0.45s ease-in forwards;
        }

        @keyframes fadeInSmooth {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes fadeOutSmooth {
            from {
                opacity: 1;
                transform: scale(1);
            }
            to {
                opacity: 0;
                transform: scale(0.92);
            }
        }
    </style>
@endif

{{-- end swart alrert --}}


    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
