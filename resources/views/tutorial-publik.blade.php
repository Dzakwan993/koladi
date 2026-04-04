<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial - Koladi</title>
    <link rel="icon" type="image/png" href="/images/LogoAtas.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif !important; }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    {{-- ================================================ --}}
    {{-- NAVBAR (sama dengan landing page) --}}
    {{-- ================================================ --}}
    <nav class="fixed top-0 w-full bg-white/90 backdrop-blur-md shadow-sm z-50 transition-all duration-300">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                    <a href="{{ route('landingpage') }}">
                        <img src="/images/LogoKoladi.svg" alt="Koladi">
                    </a>
                </div>

                {{-- Desktop Menu --}}
                <div class="hidden md:flex items-center space-x-12">
                    <a href="{{ route('landingpage') }}#beranda" class="font-bold hover:text-purple-600 transition-colors">Beranda</a>
                    <a href="{{ route('landingpage') }}#fitur"   class="font-bold hover:text-purple-600 transition-colors">Fitur</a>
                    <a href="{{ route('landingpage') }}#tentang" class="font-bold hover:text-purple-600 transition-colors">Tentang</a>
                    <a href="{{ route('landingpage') }}#paket"   class="font-bold hover:text-purple-600 transition-colors">Paket</a>
                    <a href="{{ route('tutorial.publik') }}"     class="font-bold text-blue-600 border-b-2 border-blue-600 pb-0.5 transition-colors">Tutorial</a>
                </div>

                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('masuk') }}">
                        <button class="px-6 py-2.5 bg-blue-600 text-white font-bold text-[13px] rounded-full
                            hover:bg-blue-700 hover:-translate-y-0.5 active:scale-95
                            transition-all duration-200 shadow-sm hover:shadow-md">
                            Masuk
                        </button>
                    </a>
                    <a href="{{ route('daftar') }}">
                        <button class="px-6 py-2.5 bg-gray-200 text-black font-bold text-[13px] rounded-full
                            hover:bg-gray-300 hover:-translate-y-0.5 active:scale-95
                            transition-all duration-200 shadow-sm hover:shadow-md">
                            Daftar
                        </button>
                    </a>
                </div>

                {{-- Mobile Menu Button --}}
                <button id="mobileMenuBtn" class="md:hidden p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
            <div class="container mx-auto px-4 py-4 space-y-4">
                <a href="{{ route('landingpage') }}#beranda" class="block hover:text-purple-600 transition-colors">Beranda</a>
                <a href="{{ route('landingpage') }}#fitur"   class="block hover:text-purple-600 transition-colors">Fitur</a>
                <a href="{{ route('landingpage') }}#tentang" class="block hover:text-purple-600 transition-colors">Tentang</a>
                <a href="{{ route('landingpage') }}#paket"   class="block hover:text-purple-600 transition-colors">Paket</a>
                <a href="{{ route('tutorial.publik') }}"     class="block font-bold text-blue-600 transition-colors">Tutorial</a>
                <div class="flex flex-col space-y-2 pt-4">
                    <a href="{{ route('masuk') }}">
                        <button class="px-6 py-2.5 bg-blue-600 text-white font-bold text-[13px] rounded-full
                            hover:bg-blue-700 transition-all duration-200 shadow-sm">Masuk</button>
                    </a>
                    <a href="{{ route('daftar') }}">
                        <button class="px-6 py-2.5 bg-gray-200 text-black font-bold text-[13px] rounded-full
                            hover:bg-gray-300 transition-all duration-200 shadow-sm">Daftar</button>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- ================================================ --}}
    {{-- CONTENT --}}
    {{-- ================================================ --}}
    <div class="pt-16"> {{-- offset navbar --}}

        {{-- Header Section --}}
        <div class="bg-white border-b border-gray-100 px-8 py-12">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center gap-2 mb-3">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                            stroke="#2563eb" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
                        </svg>
                    </span>
                    <span class="text-sm font-medium text-blue-600">Video Tutorial</span>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    Belajar Pakai Koladi Lebih Cepat 🚀
                </h1>
                <p class="text-gray-600 text-sm leading-relaxed mb-1">
                    Mulai dari setup awal sampai koordinasi tim, semua bisa kamu pelajari lewat video singkat di bawah ini.
                </p>
                <p class="text-gray-500 text-sm leading-relaxed mb-1">
                    Ikuti urutannya supaya kamu bisa langsung menggunakan Koladi dengan lancar bersama timmu — tanpa bingung, tanpa ribet.
                </p>
                <p class="text-gray-500 text-sm leading-relaxed mb-4">
                    Tonton satu per satu, dan dalam beberapa menit kamu sudah siap kerja pakai Koladi.
                </p>

                <a href="{{ route('daftar') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-full
                          hover:bg-blue-700 hover:-translate-y-0.5 transition-all duration-200 shadow-sm">
                    Coba Gratis Sekarang →
                </a>
            </div>
        </div>

        {{-- Video Grid --}}
        <div class="px-8 py-10 max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach ($videos as $index => $video)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col
                                hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">

                        {{-- Video Preview with Custom Thumbnail Overlay --}}
                        <div class="relative w-full group cursor-pointer" style="aspect-ratio: 16/9;"
                             x-data="{ isPlaying: false }">

                            {{-- Static Preview State --}}
                            <template x-if="!isPlaying">
                                <div class="absolute inset-0 z-10 overflow-hidden" @click="isPlaying = true">
                                    <img src="https://img.youtube.com/vi/{{ $video['youtube_id'] }}/hqdefault.jpg"
                                         alt="{{ $video['title'] }}"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/30 transition-colors duration-300"></div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-16 h-16 bg-white/90 rounded-full flex items-center justify-center shadow-xl
                                                    group-hover:bg-blue-600 group-hover:scale-110 transition-all duration-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                                 class="w-8 h-8 text-blue-600 group-hover:text-white transition-colors ml-1">
                                                <path fill-rule="evenodd" d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Actual Video State --}}
                            <template x-if="isPlaying">
                                <iframe
                                    class="w-full h-full absolute inset-0 z-10"
                                    :src="`https://www.youtube.com/embed/{{ $video['youtube_id'] }}?autoplay=1&modestbranding=1&rel=0&iv_load_policy=3`"
                                    title="{{ $video['title'] }}"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen>
                                </iframe>
                            </template>
                        </div>

                        {{-- Card Content --}}
                        <div class="p-5 flex flex-col flex-1">
                            
                            <h3 class="text-base font-semibold text-gray-900 mb-2 leading-snug">
                                {{ $video['title'] }}
                            </h3>
                            <p class="text-sm text-gray-500 leading-relaxed flex-1">
                                {{ $video['desc'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- CTA Bottom --}}
            <div class="mt-16 text-center bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-10 shadow-xl">
                <h2 class="text-2xl font-bold text-white mb-2">Siap pakai Koladi bersama timmu?</h2>
                <p class="text-blue-100 mb-6 text-sm">Coba gratis 7 hari.</p>
                <a href="{{ route('daftar') }}"
                   class="inline-flex items-center gap-2 px-8 py-3 bg-white text-blue-700 text-sm font-bold rounded-full
                          hover:bg-gray-100 hover:-translate-y-0.5 transition-all duration-200 shadow-md">
                    Mulai Gratis Sekarang →
                </a>
            </div>
        </div>
    </div>

    <script>
        const btn = document.getElementById('mobileMenuBtn');
        const menu = document.getElementById('mobileMenu');
        btn.addEventListener('click', () => menu.classList.toggle('hidden'));
    </script>

</body>
</html>
