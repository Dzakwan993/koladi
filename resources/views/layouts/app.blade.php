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


    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
