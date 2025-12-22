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
                        WORKSPACES LOKAL HARGA MASUK AKAL
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
        {{-- <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3">
                </path>
            </svg>
        </div> --}}
    </section>

    <!-- Problem Section -->
    <section class="py-20 bg-gradient-to-br from-blue-400 via-blue-100 to-blue-100 relative overflow-hidden">
        <!-- Animated Background -->

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
                        <p class="font-semibold text-lg"> responden mengatakan: "Dokumen kerja sering tersebar di banyak
                            tempat dan sulit ditemukan saat dibutuhkan."
                        </p>
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

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <h2 class="text-3xl md:text-5xl font-extrabold text-center mb-6 text-black">
                Masalah yang Hampir <span class="text-red-600">Semua Tim</span> Alami
            </h2>
            <p class="text-center text-xl text-blue-700 mb-16 font-semibold"></p>

            <div class="flex flex-wrap gap-6 justify-center max-w-4xl mx-auto mb-16">

                <div
                    class="w-40 h-40 rounded-full bg-blue-800/80 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
                    <div class="text-4xl mb-2 text-white">📅</div>
                    <p class="text-sm text-white text-center px-3 font-semibold">Waktu rapat susah cocok</p>
                </div>

                <div
                    class="w-40 h-40 rounded-full bg-blue-800/80 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
                    <div class="text-4xl mb-2 text-white">💬</div>
                    <p class="text-sm text-white text-center px-3 font-semibold">Chat penting tenggelam</p>
                </div>

                <div
                    class="w-40 h-40 rounded-full bg-blue-800/80 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
                    <div class="text-4xl mb-2 text-white">🧩</div>
                    <p class="text-sm text-white text-center px-3 font-semibold">Terlalu banyak tools</p>
                </div>

                <div
                    class="w-40 h-40 rounded-full bg-blue-800/80 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
                    <div class="text-4xl mb-2 text-white">📂</div>
                    <p class="text-sm text-white text-center px-3 font-semibold">File berserakan</p>
                </div>

                <div
                    class="w-40 h-40 rounded-full bg-blue-800/80 backdrop-blur-2xl border border-blue-400/40 shadow-[0_0_25px_rgba(30,144,255,0.35)] flex flex-col items-center justify-center hover:bg-blue-600/60 hover:shadow-[0_0_35px_rgba(30,144,255,0.55)] transition-all duration-300">
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

    <!-- AI Decision Support (Creative / Premium) -->
    <section class="py-20 bg-white relative overflow-hidden">
        <!-- premium background -->
        <div class="absolute inset-0 pointer-events-none">
            <div
                class="absolute -top-32 left-1/2 -translate-x-1/2 w-[900px] h-[900px] bg-gradient-to-tr from-blue-500/15 via-purple-500/10 to-red-500/10 rounded-full blur-3xl">
            </div>

            <!-- subtle grid -->
            <div
                class="absolute inset-0 opacity-[0.06] [background-image:linear-gradient(to_right,#0f172a_1px,transparent_1px),linear-gradient(to_bottom,#0f172a_1px,transparent_1px)] [background-size:48px_48px]">
            </div>

            <!-- floating accents -->
            <div class="absolute top-24 left-10 w-24 h-24 rounded-3xl bg-blue-500/10 blur-xl"></div>
            <div class="absolute bottom-24 right-10 w-28 h-28 rounded-3xl bg-red-500/10 blur-xl"></div>
        </div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="max-w-6xl mx-auto">
                <!-- header -->
                <div class="text-center mb-10">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-blue-200 bg-white/70 backdrop-blur-xl shadow-sm">
                        <span class="text-base">✨</span>
                        <span class="text-sm font-extrabold text-slate-900">Koladi AI </span>
                        <span class="px-2 py-0.5 rounded-full text-[12px] font-black bg-blue-600 text-white">DSS</span>
                    </div>

                    <h3 class="mt-4 text-3xl md:text-5xl font-extrabold text-slate-900 leading-tight">
                        Tapi...Ada Masalah Yang <span class="text-red-600">Lebih Berbahaya!!</span> <br>

                        <span class="relative inline-block mt-4">
                            <!-- Teks -->
                            <span class="relative z-10 text-white drop-shadow-[0_0_20px_rgba(220,38,38,0.6)]">
                                "Tim Berkerja Tanpa Arah"
                            </span>

                            <!-- Highlight penuh: merah solid, cerah -->
                            <span class="absolute inset-0 z-0 bg-red-600"></span>

                            <!-- Glow luar -->
                            <span class="absolute -inset-1 z-0 bg-red-600 blur-md opacity-50"></span>
                        </span>
                    </h3>

                    <p class="mt-4 text-base md:text-lg text-slate-700 max-w-3xl mx-auto leading-relaxed">
                        Risiko terbesar bukan teknis, tapi
                        <span
                            class="text-slate-900 font-semibold px-2 bg-red-600/15 drop-shadow-[0_0_14px_rgba(220,38,38,0.35)]">tim
                            tanpa arah</span>
                        dan
                        <span
                            class="text-slate-900 font-semibold px-2 bg-red-600/15 drop-shadow-[0_0_14px_rgba(220,38,38,0.35)]">keputusan
                            tanpa data</span>.
                    </p>
                </div>

                <!-- Koladi · AI Decision Support Card (padat, 3 card sejajar) -->
                <div
                    class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white/70 backdrop-blur-2xl shadow-[0_20px_70px_rgba(15,23,42,0.12)]">
                    <!-- top accent -->
                    <div class="absolute inset-x-0 top-0 h-1 bg-blue-600"></div>

                    <div class="p-6 md:p-10">
                        <div class="grid lg:grid-cols-12 gap-8 items-start">
                            <!-- Left: message + 3 feature cards sejajar -->
                            <div class="lg:col-span-7">
                                <!-- label -->
                                <div class="flex items-center gap-3">
                                    <span
                                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-md">
                                        🤖
                                    </span>
                                    <div class="leading-tight">
                                        <p class="text-sm font-extrabold text-slate-900">Koladi • AI Decision Support
                                        </p>
                                        <p class="text-xs font-medium text-slate-500">
                                            Aktivitas tim → insight → keputusan
                                        </p>
                                    </div>
                                </div>

                                <!-- headline -->
                                <h4 class="mt-5 text-2xl md:text-3xl font-extrabold text-slate-900 leading-tight">
                                    Koladi mengubah aktivitas kerja menjadi <span class="text-blue-700">insight</span>
                                    yang siap dipakai.
                                </h4>

                                <p class="mt-3 text-slate-700 leading-relaxed">
                                    Lihat risiko lebih cepat—sebelum deadline molor dan performa turun.
                                </p>

                                <!-- proof line -->
                                <div class="mt-5 rounded-2xl border border-blue-200/80 bg-blue-50 p-4">
                                    <p class="text-slate-900 font-extrabold">
                                        Keputusan bukan perasaan —
                                        <span class="text-blue-700">berdasarkan data kerja nyata.</span>
                                    </p>
                                    <p class="mt-1 text-sm text-slate-600 font-medium">
                                        Ringkas, jelas, dan langsung bisa ditindaklanjuti.
                                    </p>
                                </div>

                                <!-- 3 feature cards sejajar -->
                                <div class="mt-6 grid sm:grid-cols-3 gap-4">
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="h-9 w-9 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-sm">
                                                ✴️
                                            </span>
                                            <div class="leading-tight">
                                                <p class="text-sm font-extrabold text-slate-900">Pola Kerja</p>
                                                <p class="text-[11px] font-semibold text-slate-500">Ritme & kebiasaan
                                                </p>
                                            </div>
                                        </div>
                                        <p class="mt-3 text-[13px] text-slate-700 leading-relaxed">
                                            Baca pola yang tidak terlihat dari aktivitas harian.
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="h-9 w-9 rounded-2xl bg-slate-800 text-white flex items-center justify-center text-sm">
                                                ⚖️
                                            </span>
                                            <div class="leading-tight">
                                                <p class="text-sm font-extrabold text-slate-900">Beban Kerja</p>
                                                <p class="text-[11px] font-semibold text-slate-500">Adil & terukur</p>
                                            </div>
                                        </div>
                                        <p class="mt-3 text-[13px] text-slate-700 leading-relaxed">
                                            Ukur kapasitas dan distribusi tugas biar tidak timpang.
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="h-9 w-9 rounded-2xl bg-red-600 text-white flex items-center justify-center text-sm">
                                                🚨
                                            </span>
                                            <div class="leading-tight">
                                                <p class="text-sm font-extrabold text-slate-900">Risiko Dini</p>
                                                <p class="text-[11px] font-semibold text-slate-500">Early warning</p>
                                            </div>
                                        </div>
                                        <p class="mt-3 text-[13px] text-slate-700 leading-relaxed">
                                            Deteksi bottleneck dan potensi telat sebelum membesar.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: risk box -->
                            <div class="lg:col-span-5">
                                <div
                                    class="relative overflow-hidden rounded-3xl border border-red-200/70 bg-white shadow-sm p-6">
                                    <!-- subtle background glow -->
                                    <div
                                        class="absolute -top-24 -right-24 h-56 w-56 rounded-full bg-red-600/10 blur-2xl">
                                    </div>

                                    <div class="relative flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-black text-red-700 tracking-wider uppercase">Risk
                                            </p>
                                            <p class="mt-1 text-base font-extrabold text-slate-900 leading-tight">
                                                Yang sering terlambat disadari
                                            </p>
                                        </div>

                                        <span
                                            class="inline-flex items-center gap-2 rounded-full bg-red-600 px-3 py-1 text-xs font-black text-white">
                                            <span class="h-2 w-2 rounded-full bg-white/90"></span>
                                            RISK
                                        </span>
                                    </div>

                                    <div class="relative mt-5 space-y-3">
                                        <div
                                            class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                            <span
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-red-600 text-white shadow-sm">
                                                ⚠️
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-extrabold text-slate-900 leading-snug">Overload
                                                    tersembunyi</p>
                                                <p class="mt-0.5 text-sm text-slate-600 font-medium leading-snug">
                                                    Kelihatan aman, padahal beban sudah berlebih.
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                            <span
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-sm">
                                                ⏳
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-extrabold text-slate-900 leading-snug">Deadline rawan
                                                    molor</p>
                                                <p class="mt-0.5 text-sm text-slate-600 font-medium leading-snug">
                                                    Progres tampak normal, tapi risiko menumpuk diam-diam.
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                            <span
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-sm">
                                                📉
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-extrabold text-slate-900 leading-snug">Performa turun
                                                    diam-diam</p>
                                                <p class="mt-0.5 text-sm text-slate-600 font-medium leading-snug">
                                                    Output menurun tanpa alarm yang jelas.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative mt-5 border-t border-slate-200 pt-4">
                                        <p class="text-sm text-slate-600 font-semibold">
                                            Koladi memberi <span class="text-slate-900 font-extrabold">early
                                                warning</span> + rekomendasi action.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CTA row (dirapikan, sekarang ada spasi yang enak dibaca) -->
                        <div class="mt-10 flex justify-center">
                            <p class="text-lg md:text-xl text-slate-700 font-semibold text-center">
                                Semua solusi ini <span class="font-extrabold text-slate-900">terintegrasi dalam
                                    Koladi</span>
                            </p>
                        </div>

                    </div>
                </div>

                <!-- trust note -->
                <p class="mt-6 text-center text-sm text-slate-500 font-semibold">
                    {{-- Koladi = manajemen kerja + AI insight untuk keputusan yang lebih cepat dan terukur. --}}
                </p>
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
                        <div class="text-gray-500 line-through text-lg leading-none mb-1">Rp
                            {{ number_format($basicPrice * 2, 0, ',', '.') }}</div>
                        <span class="text-3xl font-bold text-purple-600">Rp
                            {{ number_format($basicPrice, 0, ',', '.') }}</span>
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
                        <div class="text-white line-through text-lg leading-none mb-1">Rp
                            {{ number_format($standardPrice * 2, 0, ',', '.') }}</div>
                        <span class="text-3xl font-bold">Rp {{ number_format($standardPrice, 0, ',', '.') }}</span>
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
                        <div class="text-gray-500 line-through text-lg leading-none mb-1">Rp
                            {{ number_format($businessPrice * 2, 0, ',', '.') }}</div>
                        <span class="text-3xl font-bold text-purple-600">Rp
                            {{ number_format($businessPrice, 0, ',', '.') }}</span>
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
    <section id="feedback"
        class="py-20 bg-gradient-to-br from-black via-blue-700 to-blue-800 relative overflow-hidden">
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
                    <form action="{{ route('feedback.store') }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <label class="block text-white font-bold mb-3 text-lg">Nama Anda (Opsional)</label>
                            <input type="text" name="name" placeholder="Masukkan nama Anda..."
                                class="w-full px-6 py-4 rounded-2xl border-2 border-white/30 bg-white/20 text-white placeholder-white/60 focus:border-yellow-400 focus:outline-none transition-colors backdrop-blur-sm">
                        </div>

                        <div>
                            <label class="block text-white font-bold mb-3 text-lg">Email (Opsional)</label>
                            <input type="email" name="email" placeholder="email@example.com"
                                class="w-full px-6 py-4 rounded-2xl border-2 border-white/30 bg-white/20 text-white placeholder-white/60 focus:border-yellow-400 focus:outline-none transition-colors backdrop-blur-sm">
                        </div>

                        <div>
                            <label class="block text-white font-bold mb-3 text-lg">Masukan Anda</label>
                            <textarea name="message" rows="6"
                                placeholder="Ceritakan pengalaman Anda, saran, atau fitur yang Anda inginkan..."
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
                @if (session('success'))
                    <div id="successPopup"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4" role="dialog"
                        aria-modal="true" aria-labelledby="successTitle" aria-describedby="successDesc">
                        <div
                            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/10
             animate-[pop_180ms_ease-out]">
                            <!-- Header -->
                            <div class="flex items-start gap-4 px-6 pt-6">
                                <div
                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                    <svg class="h-6 w-6 text-emerald-600" viewBox="0 0 24 24" fill="none"
                                        aria-hidden="true">
                                        <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 id="successTitle" class="text-lg font-semibold text-gray-900 leading-snug">
                                        Berhasil
                                    </h3>
                                    <p id="successDesc" class="mt-1 text-sm text-gray-600 leading-relaxed">
                                        {{ session('success') }}
                                    </p>
                                </div>

                                <!-- Close icon -->
                                <button type="button" data-success-close
                                    class="ml-1 inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500
                 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-600"
                                    aria-label="Tutup">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.5"
                                            stroke-linecap="round" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Footer -->
                            <div class="px-6 pb-6 pt-5">
                                <button type="button" data-success-close
                                    class="w-full rounded-xl bg-blue-600 py-3 text-sm font-semibold text-white shadow-sm
                 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 transition">
                                    OKE
                                </button>

                                <p class="mt-3 text-center text-xs text-gray-500">
                                    Popup akan tertutup otomatis dalam <span class="font-semibold">3 detik</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <style>
                        @keyframes pop {
                            0% {
                                opacity: 0;
                                transform: translateY(8px) scale(.98);
                            }

                            100% {
                                opacity: 1;
                                transform: translateY(0) scale(1);
                            }
                        }
                    </style>

                    <script>
                        (() => {
                            const popup = document.getElementById('successPopup');
                            if (!popup) return;

                            const close = () => popup.remove();

                            // close button(s)
                            popup.querySelectorAll('[data-success-close]').forEach(btn => {
                                btn.addEventListener('click', close);
                            });

                            // click backdrop
                            popup.addEventListener('click', (e) => {
                                if (e.target === popup) close();
                            });

                            // ESC
                            const onKeydown = (e) => {
                                if (e.key === 'Escape') close();
                            };
                            document.addEventListener('keydown', onKeydown);

                            // auto close 3s
                            const t = setTimeout(() => {
                                document.removeEventListener('keydown', onKeydown);
                                close();
                            }, 3000);
                        })();
                    </script>
                @endif


                <div class="mt-12 text-center bg-white/10 backdrop-blur-lg rounded-2xl p-8 border-2 border-white/20">
                    <p class="text-white text-lg font-bold mb-4">Atau langsung coba Koladi GRATIS!</p>
                    <a href="{{ route('daftar') }}">
                        <button
                            class="px-7 py-3 bg-white text-purple-600 rounded-full text-[13px] font-black hover:bg-gray-100 shadow-xl transform hover:scale-110 transition-all duration-300 animate-pulse">
                            MULAI TRIAL 7 HARI GRATIS →
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
