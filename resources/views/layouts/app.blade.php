<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ✅ Hanya aktifkan CSP di production --}}
    @unless (app()->environment('local'))
        <meta http-equiv="Content-Security-Policy"
            content="
            default-src 'self';
            script-src 'self' 'unsafe-inline' 'unsafe-eval'
                https://app.midtrans.com
                https://api.midtrans.com
                https://cdn.jsdelivr.net
                https://js.pusher.com;
            style-src 'self' 'unsafe-inline'
                https://fonts.googleapis.com
                https://cdn.jsdelivr.net;
            font-src 'self'
                https://fonts.gstatic.com;
            img-src 'self' data: https:;
            connect-src 'self'
                https://app.midtrans.com
                https://api.midtrans.com
                wss://ws-ap1.pusher.com
                https://sockjs-ap1.pusher.com;
            frame-src 'self'
                https://app.midtrans.com;
        ">
    @endunless

    <title>Koladi - @yield('title')</title>

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            overflow-x: hidden;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    @if (session('alert_type'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: '{{ session('alert_type') }}', // warning, error, success, info
                    title: '{{ session('alert_title') }}',
                    html: '{{ session('alert_message') }}',
                    showCancelButton: @json(session('alert_button') ? true : false),
                    confirmButtonText: '{{ session('alert_button') ?? 'OK' }}',
                    cancelButtonText: 'Tutup',
                    confirmButtonColor: '#3b82f6',
                    cancelButtonColor: '#6b7280',
                }).then((result) => {
                    @if (session('alert_url'))
                        if (result.isConfirmed) {
                            window.location.href = '{{ session('alert_url') }}';
                        }
                    @endif
                });
            });
        </script>
    @endif
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

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Alpine.js dan Collapse Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Pusher -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <!-- Midtrans Snap -->
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
    </script>

    <script>
        window.roleContext = {
            type: 'company',
            workspaceId: null
        };

        window.setRoleContext = function(ctx) {
            window.roleContext = {
                type: ctx?.type || 'company',
                workspaceId: ctx?.workspaceId || null,
            };
        };
    </script>

    @include('components.sweet-alert')
    @stack('scripts')

</body>

</html>
