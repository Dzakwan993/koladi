<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koladi - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f3f6fc] flex h-screen overflow-hidden relative">

    {{-- Sidebar --}}
    <x-sidebar />

    <div class="flex-1 flex flex-col overflow-hidden relative z-10">
        {{-- Topbar --}}
        <x-topbar />

        {{-- Konten utama --}}
        <main class="flex-1 overflow-y-auto relative z-10">
            @yield('content')
        </main>
    </div>

    {{-- semua script tambahan dari fitur lain masuk di sini --}}
    @stack('scripts')

    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
