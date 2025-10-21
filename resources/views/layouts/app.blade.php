<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Koladi - @yield('title')</title>

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

     <!-- Alpine.js dan Collapse Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f3f6fc] flex h-screen overflow-hidden relative">

    {{-- Sidebar --}}
    <x-sidebar />

    <div class="flex-1 flex flex-col overflow-hidden relative z-5">
        {{-- Topbar --}}
        <x-topbar />

        {{-- Konten utama --}}
        <main class="flex-1 overflow-y-auto relative z-10">
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
