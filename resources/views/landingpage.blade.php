<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koladi - All in One Workspaces</title>
    <link rel="icon" type="image/png" href="/images/LogoAtas.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif !important;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
            }

            50% {
                box-shadow: 0 0 40px rgba(59, 130, 246, 0.8);
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease-out;
        }

        .animate-slideInLeft {
            animation: slideInLeft 0.8s ease-out;
        }

        .animate-slideInRight {
            animation: slideInRight 0.8s ease-out;
        }

        .animate-pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-white/90 backdrop-blur-md shadow-sm z-50 transition-all duration-300">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div
                    class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                    <img src="images/LogoKoladi.svg" alt="">
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-12">
                    <a href="#beranda" class="font-bold hover:text-purple-600 transition-colors">Beranda</a>
                    <a href="#fitur" class="font-bold hover:text-purple-600 transition-colors">Fitur</a>
                    <a href="#tentang" class=" font-bold hover:text-purple-600 transition-colors">Tentang</a>
                    <a href="#paket" class=" font-bold hover:text-purple-600 transition-colors">Paket</a>
                </div>

                <div class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('masuk') }}">
                    <button
                        class="px-6 py-2.5 bg-blue-600 text-white font-bold text-[13px] rounded-full
               hover:bg-blue-700 hover:-translate-y-0.5 active:scale-95
               transition-all duration-200 shadow-sm hover:shadow-md">
                        Masuk
                    </button>
                    </a>

                    <!-- Button Daftar -->
                    <a href="{{ route('daftar') }}">
                    <button
                        class="px-6 py-2.5 bg-gray-200 text-black font-bold text-[13px] rounded-full
               hover:bg-gray-300 hover:-translate-y-0.5 active:scale-95
               transition-all duration-200 shadow-sm hover:shadow-md">
                        Daftar
                    </button>
                    </a>

                </div>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
            <div class="container mx-auto px-4 py-4 space-y-4">
                <a href="#beranda" class="block hover:text-purple-600 transition-colors">Beranda</a>
                <a href="#fitur" class="block hover:text-purple-600 transition-colors">Fitur</a>
                <a href="#tentang" class="block hover:text-purple-600 transition-colors">Tentang</a>
                <a href="#paket" class="block hover:text-purple-600 transition-colors">Paket</a>
                <div class="flex flex-col space-y-2 pt-4">
                    <a href="{{ route('masuk') }}">
                    <button
                        class="px-6 py-2.5 bg-blue-600 text-white font-bold text-[13px] rounded-full
               hover:bg-blue-700 hover:-translate-y-0.5 active:scale-95
               transition-all duration-200 shadow-sm hover:shadow-md">
                        Masuk
                    </button>
                    </a>

                    <!-- Button Daftar -->
                    <a href="{{ route('daftar') }}">
                    <button
                        class="px-6 py-2.5 bg-gray-200 text-black font-bold text-[13px] rounded-full
               hover:bg-gray-300 hover:-translate-y-0.5 active:scale-95
               transition-all duration-200 shadow-sm hover:shadow-md">
                        Daftar
                    </button>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="beranda"
        class="min-h-screen flex items-center bg-gradient-to-br from-indigo-100 via-white-500 to-blue-100 overflow-hidden relative">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div
                class="absolute top-20 left-10 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float">
            </div>
            <div class="absolute top-40 right-10 w-72 h-72 bg-red-400 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float"
                style="animation-delay: 2s"></div>
            <div class="absolute -bottom-8 left-20 w-72 h-72 bg-yellow-400 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float"
                style="animation-delay: 4s"></div>
        </div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10 pt-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="text-left animate-slideInLeft">


                    <h1 class="text-5xl md:text-6xl font-extrabold mb-4 text-gray-900 leading-tight">
                        ALL IN ONE<br>
                        <span class="bg-gradient-to-r from-blue-600 via-black to-black bg-clip-text text-transparent">
                            WORKSPACES
                        </span>
                    </h1>

                    <p class="text-xl md:text-2xl font-bold text-blue-800 mb-8">
                        WORKSPACES LOKAL HARGA MASOK AKAL
                    </p>
                    <a href="{{ route('daftar') }}">
                    <button
                        class="px-3 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg text-lg font-bold hover:shadow-2xl transform hover:scale-105 transition-all duration-300 hover:-translate-y-1">
                        <p class="">COBA GRATIS -></p>
                    </button>
                    </a>
                </div>

                <!-- Right Content - Laptop Mockup -->
                <div class="relative animate-slideInRight">
                    <div class="relative transform hover:scale-105 transition-all duration-500 animate-float">
                        <!-- Laptop Frame -->
                        <div class="relative bg-gray-800 rounded-t-2xl p-2 shadow-2xl">
                            <!-- Screen -->
                            <div class="bg-white rounded-t-lg overflow-hidden flex items-center justify-center py-4">
                                <img src="images/laptop.svg" alt="Dashboard Preview" class="w-50 h-auto object-contain">
                            </div>

                        </div>
                        <!-- Laptop Base -->
                        <div class="bg-gray-700 h-4 rounded-b-2xl shadow-xl"></div>
                        <div class="bg-gray-600 h-1 w-3/4 mx-auto rounded-b-lg"></div>

                        <!-- Floating Elements -->
                        <div class="absolute -top-10 -right-10 w-20 h-20 bg-yellow-400 rounded-2xl shadow-lg animate-float"
                            style="animation-delay: 1s"></div>
                        <div class="absolute -bottom-10 -left-10 w-16 h-16 bg-pink-400 rounded-full shadow-lg animate-float"
                            style="animation-delay: 2s"></div>
                        <div class="absolute top-1/2 -right-5 w-12 h-12 bg-blue-400 rounded-lg shadow-lg animate-float"
                            style="animation-delay: 3s"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3">
                </path>
            </svg>
        </div>
    </section>

    <!-- Problem Section -->
    <section class="py-20 bg-gradient-to-br from-blue-400 via-blue-100 to-blue-100 relative overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0 opacity-30">
            <div
                class="absolute top-10 left-10 w-64 h-64 bg-blue-900 rounded-full mix-blend-multiply filter blur-3xl animate-float">
            </div>
            <div class="absolute bottom-10 right-10 w-64 h-64 bg-blue-700 rounded-full mix-blend-multiply filter blur-3xl animate-float"
                style="animation-delay: 2s"></div>
        </div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16 animate-fadeInUp">

                    <h2 class="text-4xl md:text-5xl font-extrabold mb-6 text-gray-900 leading-tight">
                        Tim sibuk. Chat penuh.<br>Dokumen tercecer.<br>
                        <span class="text-red-600">Hasil berantakan?</span>
                    </h2>
                </div>

                <div class="grid md:grid-cols-3 gap-8 mb-12">
                    <div
                        class="text-center p-8 rounded-3xl bg-gradient-to-br from-blue-400 to-blue-900 text-white shadow-2xl transform hover:scale-110 hover:rotate-2 transition-all duration-300">
                        <div class="text-7xl font-black mb-4 animate-pulse">80%</div>
                        <p class="font-semibold text-lg">responden mengatakan: "Sulit menemukan waktu rapat yang cocok
                            untuk semua orang."</p>
                    </div>

                    <div
                        class="text-center p-8 rounded-3xl bg-gradient-to-br from-blue-400 to-blue-900 text-white shadow-2xl transform hover:scale-110 hover:rotate-2 transition-all duration-300">
                        <div class="text-7xl font-black mb-4 animate-pulse" style="animation-delay: 0.5s">70%</div>
                        <p class="font-semibold text-lg">merasa informasi penting sering tenggelam di chat.</p>
                    </div>

                    <div
                        class="text-center p-8 rounded-3xl bg-gradient-to-br from-blue-400 to-blue-900 text-white shadow-2xl transform hover:scale-110 hover:rotate-2 transition-all duration-300">
                        <div class="text-7xl font-black mb-4 animate-pulse" style="animation-delay: 1s">50%</div>
                        <p class="font-semibold text-lg">Sebagian besar harus pakai banyak tools berbeda</p>
                    </div>
                </div>

                <div
                    class="text-center p-10 bg-gradient-to-r from-blue-400 via-blue-900 to-blue-400 rounded-3xl shadow-2xl transform hover:scale-105 transition-all duration-300">
                    <p class="text-2xl md:text-3xl font-black text-white mb-6">
                        Hasilnya? Waktu habis cuma buat sinkronisasi, bukan kerja penting.
                    </p>
                    <a href="{{ route('daftar') }}">
                    <button
                        class="px-8 py-4 bg-white text-blue-600 rounded-full text-lg font-bold hover:shadow-2xl transform hover:scale-110 transition-all duration-300 animate-bounce">
                        COBA SOLUSINYA GRATIS! →
                    </button>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Problems List -->
    <section class="py-20 bg-gradient-to-br from-white via-white to-white relative overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0 opacity-20">
            <div
                class="absolute top-20 right-20 w-96 h-96 bg-blue-0 rounded-full mix-blend-multiply filter blur-3xl animate-float">
            </div>
            <div class="absolute bottom-20 left-20 w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl animate-float"
                style="animation-delay: 3s"></div>
        </div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <h2 class="text-3xl md:text-5xl font-extrabold text-center mb-6 text-black">
                Masalah yang Hampir <span class="text-red-600">Semua Tim</span> Alami
            </h2>
            <p class="text-center text-xl text-blue-700 mb-16 font-semibold"></p>

            <div class="flex flex-wrap gap-6 justify-center max-w-4xl mx-auto mb-16">

                <div
                    class="w-40 h-40 rounded-full bg-blue-600/40 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
                    <div class="text-4xl mb-2 text-white">📅</div>
                    <p class="text-sm text-white text-center px-3 font-semibold">Waktu rapat susah cocok</p>
                </div>

                <div
                    class="w-40 h-40 rounded-full bg-blue-600/40 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
                    <div class="text-4xl mb-2 text-white">💬</div>
                    <p class="text-sm text-white text-center px-3 font-semibold">Chat penting tenggelam</p>
                </div>

                <div
                    class="w-40 h-40 rounded-full bg-blue-600/40 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
                    <div class="text-4xl mb-2 text-white">🧩</div>
                    <p class="text-sm text-white text-center px-3 font-semibold">Terlalu banyak tools</p>
                </div>

                <div
                    class="w-40 h-40 rounded-full bg-blue-600/40 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
                    <div class="text-4xl mb-2 text-white">📂</div>
                    <p class="text-sm text-white text-center px-3 font-semibold">File berserakan</p>
                </div>

                <div
                    class="w-40 h-40 rounded-full bg-blue-600/40 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
                    <div class="text-4xl mb-2 text-white">🚫</div>
                    <p class="text-sm text-white text-center px-3 font-semibold">Progress tidak sinkron</p>
                </div>


            </div>

            <div
                class="text-center max-w-2xl mx-auto bg-gradient-to-r from-blue-500 via-blue-700 to-blue-500 p-12 rounded-2xl shadow-2xl transform hover:scale-105 transition-all duration-300">
                <p class="text-[16px] md:text-[20px] font-black text-white mb-6 leading-tight">
                    Eits... tenang! Sekarang ada koladi yang bisa mengatasi
                    semua permasalahan tersebut!
                </p>
                <div class="space-y-4">
                    <a href="{{ route('daftar') }}">
                    <button
                        class="px-4 py-3 bg-white text-blue-600 rounded-full text-[14px] font-bold hover:shadow-2xl transform hover:scale-110 transition-all duration-300 animate-bounce">
                        COBA SOLUSINYA GRATIS! →
                    </button>
                    </a>
                    <p class="text-white font-semibold text-[15px]">Gratis 7 hari, tanpa tanpa biaya apapun</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur"
        class="py-20 bg-gradient-to-br from-slate-900 via-blue-400 to-slate-900 text-white relative overflow-hidden">
        <!-- Animated Stars Background -->
        <div class="absolute inset-0">
            <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-white rounded-full animate-pulse"></div>
            <div class="absolute top-1/3 right-1/3 w-1 h-1 bg-white rounded-full animate-pulse"
                style="animation-delay: 1s"></div>
            <div class="absolute bottom-1/4 left-1/2 w-2 h-2 bg-white rounded-full animate-pulse"
                style="animation-delay: 2s"></div>
            <div class="absolute top-1/2 right-1/4 w-1 h-1 bg-white rounded-full animate-pulse"
                style="animation-delay: 1.5s"></div>
        </div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="text-center mb-16">

                <h2 class="text-5xl md:text-[40px] font-black mb-4">
                    Fitur-Fitur Koladi
                </h2>
                <p class="text-xl text-white-900 text-[12px]">Semua yang tim Anda butuhkan, dalam satu tempat!</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto mb-16">
                <div
                    class="group p-8 bg-gradient-to-br from-blue to-blue rounded-3xl shadow-2xl hover:shadow-purple-500/50 transform hover:-translate-y-3 hover:rotate-3 transition-all duration-300 border-2 border-purple-400">
                    <div class="text-5xl mb-4 transform group-hover:scale-125 transition-transform duration-300"><img
                            src="/images/workspaces.svg" alt="" class="invert">
                    </div>
                    <h3 class="text-2xl font-black mb-3">WORKSPACES</h3>
                    <p class="text-purple-100">Ruang kerja terpadu untuk tim</p>
                </div>

                <div
                    class="group p-8 bg-gradient-to-br from-blue to-blue rounded-3xl shadow-2xl hover:shadow-purple-500/50 transform hover:-translate-y-3 hover:rotate-3 transition-all duration-300 border-2 border-purple-400">
                    <div class="text-5xl mb-4 transform group-hover:scale-125 transition-transform duration-300"><img
                            src="/images/pengumuman.svg" alt="" class="invert">
                    </div>
                    <h3 class="text-2xl font-black mb-3">PENGUMUMAN</h3>
                    <p class="text-blue-100">Broadcast info penting dengan mudah</p>
                </div>

                <div
                    class="group p-8 bg-gradient-to-br from-blue to-blue rounded-3xl shadow-2xl hover:shadow-purple-500/50 transform hover:-translate-y-3 hover:rotate-3 transition-all duration-300 border-2 border-purple-400">
                    <div class="text-5xl mb-4 transform group-hover:scale-125 transition-transform duration-300"><img
                            src="/images/kanban.svg" alt="" class="invert">
                    </div>
                    <h3 class="text-2xl font-black mb-3">KANBAN TUGAS</h3>
                    <p class="text-indigo-100">Kelola task dengan sistem kanban</p>
                </div>

                <div
                    class="group p-8 bg-gradient-to-br from-blue to-blue rounded-3xl shadow-2xl hover:shadow-purple-500/50 transform hover:-translate-y-3 hover:rotate-3 transition-all duration-300 border-2 border-purple-400">
                    <div class="text-5xl mb-4 transform group-hover:scale-125 transition-transform duration-300"><img
                            src="/images/Chat.svg" alt="" class="invert">
                    </div>
                    <h3 class="text-2xl font-black mb-3">CHAT</h3>
                    <p class="text-pink-100">Komunikasi real-time dengan tim</p>
                </div>

                <div
                    class="group p-8 bg-gradient-to-br from-blue to-blue rounded-3xl shadow-2xl hover:shadow-purple-500/50 transform hover:-translate-y-3 hover:rotate-3 transition-all duration-300 border-2 border-purple-400">
                    <div class="text-5xl mb-4 transform group-hover:scale-125 transition-transform duration-300"><img
                            src="/images/timeline.svg" alt="" class="invert">
                    </div>
                    <h3 class="text-2xl font-black mb-3">TIMELINE</h3>
                    <p class="text-green-100">Pantau progress secara visual</p>
                </div>

                <div
                    class="group p-8 bg-gradient-to-br from-blue to-blue rounded-3xl shadow-2xl hover:shadow-purple-500/50 transform hover:-translate-y-3 hover:rotate-3 transition-all duration-300 border-2 border-purple-400">
                    <div class="text-5xl mb-4 transform group-hover:scale-125 transition-transform duration-300"><img
                            src="/images/mindmap.svg" alt="" class="invert">
                    </div>
                    <h3 class="text-2xl font-black mb-3">MINDMAP</h3>
                    <p class="text-yellow-100">Visualisasi ide dan konsep</p>
                </div>

                <div
                    class="group p-8 bg-gradient-to-br from-blue to-blue rounded-3xl shadow-2xl hover:shadow-purple-500/50 transform hover:-translate-y-3 hover:rotate-3 transition-all duration-300 border-2 border-purple-400">
                    <div class="text-5xl mb-4 transform group-hover:scale-125 transition-transform duration-300"><img
                            src="/images/statistik.svg" alt="" class="invert">
                    </div>
                    <h3 class="text-2xl font-black mb-3">STATISTIK</h3>
                    <p class="text-red-100">Analitik performa tim lengkap</p>
                </div>

                <div
                    class="group p-8 bg-gradient-to-br from-blue to-blue rounded-3xl shadow-2xl hover:shadow-purple-500/50 transform hover:-translate-y-3 hover:rotate-3 transition-all duration-300 border-2 border-purple-400">
                    <div class="text-5xl mb-4 transform group-hover:scale-125 transition-transform duration-300"><img
                            src="/images/dokumen.svg" alt="" class="invert">
                    </div>
                    <h3 class="text-2xl font-black mb-3">DOKUMEN</h3>
                    <p class="text-teal-100">Kelola semua file di satu tempat</p>
                </div>
            </div>

            <div
                class="text-center bg-gradient-to-r from-blue-500 via-blue-700 to-blue-500 p-10 rounded-2xl shadow-2xl max-w-2xl mx-auto transform hover:scale-105 transition-all duration-300">
                <h3 class="text-3xl md:text-[25px] font-black text-white mb-4">
                    Dapatkan Semua Fitur Ini GRATIS!
                </h3>
                <p class=" text-white mb-6 font-bold text[16px]">Coba selama 7 hari tanpa biaya apapun</p>
                <a href="{{ route('daftar') }}">
                <button
                    class="px-4 py-3 bg-white text-blue-600 rounded-full text-[13px] font-black hover:bg-gray-100 shadow-2xl transform hover:scale-110 transition-all duration-300 animate-pulse">
                    MULAI GRATIS SEKARANG! →
                </button>
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="tentang" class="py-20 bg-gradient-to-br from-purple-50 to-blue-50">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-8 text-gray-800">
                    Apa itu Koladi?
                </h2>
                <p class="text-xl text-gray-700 leading-relaxed">
                    Koladi adalah tools untuk mempermudah komunikasi, manajemen tugas, penjadwalan, dan dokumen dalam
                    satu platform yang rapih. Tanpa ribut pindah aplikasi, tim bisa bekerja lebih terstruktur,
                    transparan, dan efisien. Koladi juga memberikan visualisasi & insight performa anlamnya terkoneksi
                    dalam satu ekosistem yang mudah diadopsi.
                </p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="paket" class="py-20 bg-white">
        <div class="container mx-auto px-4 lg:px-8">
            <h2 class="text-4xl md:text-5xl font-bold text-center mb-4 text-gray-800">
                Berapa sih harga Koladi?
            </h2>
            <p class="text-center text-gray-600 mb-16">*Harga untuk 1 perusahaan/organisasi<br>*Untuk setiap penambahan
                1 user dikenakan biaya Rp4.000 / bulan</p>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Basic Plan -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-8 border-2 border-gray-200 hover:border-purple-500 transform hover:-translate-y-2 transition-all duration-300">
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">Basic</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-purple-600">Rp. 15.000</span>
                        <span class="text-gray-600">/ bulan</span>
                    </div>
                    <button
                        class="w-full py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-full font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-300 mb-6">
                        Coba versi trial dulu
                    </button>
                    <div class="space-y-3">
                        <p class="font-semibold text-gray-800 mb-4">Benefit:</p>
                        <p class="text-gray-700">✓ Dapat 5 pengguna</p>
                        <p class="text-gray-700">✓ Akses semua fitur</p>
                        <p class="text-gray-700">✓ Penyimpanan Unlimited</p>
                        <p class="text-gray-700">✓ Tim & Proyek tanpa batas</p>
                    </div>
                </div>

                <!-- Standard Plan -->
                <div
                    class="bg-gradient-to-br from-purple-500 to-blue-600 rounded-2xl shadow-2xl p-8 transform scale-105 hover:-translate-y-2 transition-all duration-300 text-white">
                    <div
                        class="bg-yellow-400 text-purple-900 text-sm font-bold px-4 py-1 rounded-full inline-block mb-4">
                        POPULER</div>
                    <h3 class="text-2xl font-bold mb-4">Standard</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold">Rp. 45.000</span>
                        <span>/ bulan</span>
                    </div>
                    <button
                        class="w-full py-3 bg-white text-purple-600 rounded-full font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-300 mb-6">
                        Coba versi trial dulu
                    </button>
                    <div class="space-y-3">
                        <p class="font-semibold mb-4">Benefit:</p>
                        <p>✓ Dapat 15 pengguna</p>
                        <p>✓ Akses semua fitur</p>
                        <p>✓ Penyimpanan Unlimited</p>
                        <p>✓ Tim & Proyek tanpa batas</p>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div
                    class="bg-white rounded-2xl shadow-xl p-8 border-2 border-gray-200 hover:border-purple-500 transform hover:-translate-y-2 transition-all duration-300">
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">Premium</h3>
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-purple-600">Rp. 100.000</span>
                        <span class="text-gray-600">/ bulan</span>
                    </div>
                    <button
                        class="w-full py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-full font-semibold hover:shadow-lg transform hover:scale-105 transition-all duration-300 mb-6">
                        Coba versi trial dulu
                    </button>
                    <div class="space-y-3">
                        <p class="font-semibold text-gray-800 mb-4">Benefit:</p>
                        <p class="text-gray-700">✓ Dapat 50 pengguna</p>
                        <p class="text-gray-700">✓ Akses semua fitur</p>
                        <p class="text-gray-700">✓ Penyimpanan Unlimited</p>
                        <p class="text-gray-700">✓ Tim & Proyek tanpa batas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feedback Section -->
    <section class="py-20 bg-gradient-to-br from-black via-blue-700 to-blue-800 relative overflow-hidden">
        <!-- Animated Background -->
        <div class="absolute inset-0 opacity-30">
            <div
                class="absolute top-10 left-10 w-72 h-72 rounded-full mix-blend-overlay filter blur-3xl animate-float">
            </div>
            <div class="absolute bottom-10 right-10 w-72 h-72 bg-blue rounded-full mix-blend-overlay filter blur-3xl animate-float"
                style="animation-delay: 2s"></div>
        </div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="max-w-3xl mx-auto">
                <div class="text-center mb-12">
                    <div
                        class="inline-block bg-white px-6 py-2 rounded-full text-purple-900 font-bold mb-4 animate-bounce">
                        FEEDBACK & SARAN
                    </div>
                    <h2 class="text-4xl md:text-4xl font-black text-white mb-4">
                        Beri Masukan untuk Koladi
                    </h2>
                    <p class="text-xl text-white">Bantu kami menjadi lebih baik! Suara Anda sangat berarti</p>
                </div>

                <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 md:p-12 shadow-2xl border-2 border-white/20">
                    <form class="space-y-6">
                        <div>
                            <label class="block text-white font-bold mb-3 text-lg">Nama Anda (Opsional)</label>
                            <input type="text" placeholder="Masukkan nama Anda..."
                                class="w-full px-6 py-4 rounded-2xl border-2 border-white/30 bg-white/20 text-white placeholder-white/60 focus:border-yellow-400 focus:outline-none transition-colors backdrop-blur-sm">
                        </div>

                        <div>
                            <label class="block text-white font-bold mb-3 text-lg">Email (Opsional)</label>
                            <input type="email" placeholder="email@example.com"
                                class="w-full px-6 py-4 rounded-2xl border-2 border-white/30 bg-white/20 text-white placeholder-white/60 focus:border-yellow-400 focus:outline-none transition-colors backdrop-blur-sm">
                        </div>

                        <div>
                            <label class="block text-white font-bold mb-3 text-lg">Masukan Anda</label>
                            <textarea rows="6" placeholder="Ceritakan pengalaman Anda, saran, atau fitur yang Anda inginkan..."
                                class="w-full px-6 py-4 rounded-2xl border-2 border-white/30 bg-white/20 text-white placeholder-white/60 focus:border-yellow-400 focus:outline-none transition-colors resize-none backdrop-blur-sm"></textarea>
                        </div>

                        <button type="submit"
                            class="w-full py-4 bg-white text-blue-700 rounded-2xl text-[18px] font-black
                        hover:bg-blue-700 hover:text-white
                        shadow-2xl transform hover:scale-105 transition-all duration-300">
                            KIRIM MASUKAN
                        </button>

                    </form>
                </div>

                <div class="mt-12 text-center bg-white/10 backdrop-blur-lg rounded-2xl p-8 border-2 border-white/20">
                    <p class="text-white text-lg font-bold mb-4">Atau langsung coba Koladi GRATIS!</p>
                    <a href="{{ route('daftar') }}">
                    <button
                        class="px-7 py-3 bg-white text-purple-600 rounded-full text-[13px] font-black hover:bg-gray-100 shadow-xl transform hover:scale-110 transition-all duration-300 animate-pulse">
                        MULAI TRIAL 14 HARI GRATIS →
                    </button>
                    </a>
                    <p class="text-purple-100 mt-4 text-sm">Tanpa kartu kredit • Batalkan kapan saja • Setup mudah
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-b from-blue-700 via-[#07152f] to-black text-white py-16">
        <div class="container mx-auto px-4 lg:px-8 text-center">
            <h3 class="text-3xl md:text-4xl font-bold mb-4">All in one workspaces</h3>
            <p class="text-xl mb-8">Saatnya timmu berhenti sibuk mengatur... dan mulai fokus bekerja.</p>

            <hr class="border-white/30 w-1/2 mx-auto mb-6">

            <p class="text-xs opacity-80">Copyright ©2025 Koladi - All in one workspaces</p>
        </div>
    </footer>




    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    mobileMenu.classList.add('hidden');
                }
            });
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('section > div').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            observer.observe(el);
        });
    </script>
</body>

</html>
