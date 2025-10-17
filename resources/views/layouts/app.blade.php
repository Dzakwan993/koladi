<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koladi - @yield('title')</title>

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f3f6fc] flex h-screen overflow-hidden">
    <x-sidebar />

    <div class="flex-1 flex flex-col overflow-hidden">
        <x-topbar />

        <main class="flex-1 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</body>

<script src="//unpkg.com/alpinejs" defer></script>


</html>
