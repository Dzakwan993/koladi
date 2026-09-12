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
    <section id="beranda" class="min-h-screen flex items-center bg-white overflow-hidden relative pt-24 pb-12">
        <!-- Subtle Glow Effects -->
        <div class="absolute -top-40 -left-40 w-96 h-96 bg-blue-100/60 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 right-0 w-[500px] h-[500px] bg-purple-100/40 rounded-full blur-3xl pointer-events-none"></div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-12 gap-12 items-center">

                <!-- Left Content -->
                <div class="lg:col-span-6 text-center lg:text-left space-y-6 flex flex-col items-center lg:items-start mt-8 lg:mt-0 order-2 lg:order-1">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 leading-[1.15] tracking-tight">
                        SATU PROYEK<br>
                        <span class="text-blue-600">SATU KONTEKS</span>
                    </h1>

                    <p class="text-lg sm:text-xl font-bold text-blue-800">
                        AI PROJECT WORKSPACE
                    </p>

                    <p class="text-sm sm:text-base text-gray-600 font-medium leading-relaxed max-w-lg mx-auto lg:mx-0">
                        Kelola seluruh proyek, tugas tim, pengumuman, chat, dan analisis AI dalam satu tempat yang terintegrasi dan efisien.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-wrap justify-center lg:justify-start items-center gap-4 pt-2">
                        <a href="{{ route('daftar') }}">
                            <button class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm rounded-full shadow-lg hover:scale-105 transition-all duration-200">
                                COBA GRATIS ->
                            </button>
                        </a>
                        <a href="#fitur">
                            <button class="px-8 py-3.5 bg-white border-2 border-gray-300 hover:border-black text-gray-900 font-extrabold text-sm rounded-full hover:scale-105 transition-all duration-200 shadow-sm">
                                Lihat Paket
                            </button>
                        </a>
                    </div>
                </div>

                <!-- Right Content - Laptop Mockup -->
                <div class="lg:col-span-6 relative animate-slideInRight flex justify-center items-center w-full mt-10 lg:mt-0 order-1 lg:order-2">
                    <div class="relative w-full max-w-2xl transform hover:scale-105 transition-all duration-500 animate-float">

                        <!-- Screen -->
                        <img src="/images/laptop.svg" alt="Dashboard Preview" class="w-full h-auto object-contain drop-shadow-2xl relative z-10">

                        <!-- Floating Elements -->
                        <div class="absolute -top-10 -right-4 sm:-right-10 w-16 h-16 sm:w-24 sm:h-24 bg-yellow-400 rounded-2xl shadow-lg animate-float z-0"
                            style="animation-delay: 1s"></div>
                        <div class="absolute -bottom-10 -left-4 sm:-left-10 w-14 h-14 sm:w-20 sm:h-20 bg-pink-400 rounded-full shadow-lg animate-float z-0"
                            style="animation-delay: 2s"></div>
                        <div class="absolute top-1/2 -right-8 sm:-right-12 w-10 h-10 sm:w-16 sm:h-16 bg-blue-400 rounded-lg shadow-lg animate-float z-0"
                            style="animation-delay: 3s"></div>
                    </div>
                </div>

            </div>
        </div>

    </section>

    <!-- Problem Section -->
    <section class="py-20 bg-blue-50 relative overflow-hidden">
        <!-- Clean Background -->

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16 animate-fadeInUp">

                    <h2 class="text-4xl md:text-5xl font-extrabold mb-6 text-gray-900 leading-tight">
                        Brief Klien Berantakan.<br>Meeting Transcript Panjang.<br>
                        <span class="text-red-600">Pusing Buat Task Satu-satu?</span>
                    </h2>
                </div>

                <div class="grid md:grid-cols-3 gap-8 mb-12">
                    <div
                        class="text-center p-8 rounded-3xl bg-white text-slate-800 shadow-xl border border-slate-100 transform hover:-translate-y-2 transition-all duration-300">
                        <div class="text-6xl font-black mb-4 text-blue-600">PDF</div>
                        <p class="font-medium text-slate-600">Proposal dan requirement klien yang mencapai belasan halaman.</p>
                    </div>

                    <div
                        class="text-center p-8 rounded-3xl bg-white text-slate-800 shadow-xl border border-slate-100 transform hover:-translate-y-2 transition-all duration-300">
                        <div class="text-6xl font-black mb-4 text-purple-600">CHAT</div>
                        <p class="font-medium text-slate-600">Revisi dan tambahan brief yang tersebar di WhatsApp atau Email.</p>
                    </div>

                    <div
                        class="text-center p-8 rounded-3xl bg-white text-slate-800 shadow-xl border border-slate-100 transform hover:-translate-y-2 transition-all duration-300">
                        <div class="text-6xl font-black mb-4 text-blue-800">DOCX</div>
                        <p class="font-medium text-slate-600">Transcript hasil meeting yang formatnya berantakan dan susah dibaca.</p>
                    </div>
                </div>

                <div
                    class="text-center p-10 bg-blue-900 rounded-3xl shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                    <p class="text-2xl md:text-3xl font-black text-white mb-6">
                        Hasilnya? Project Manager habis waktu membaca semuanya hanya untuk membuat setup project.
                    </p>
                    <a href="{{ route('daftar') }}">
                        <button
                            class="px-8 py-4 bg-white text-blue-600 rounded-full text-lg font-bold hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                            COBA SOLUSINYA GRATIS! →
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Problems List -->
    <section class="py-20 bg-white relative overflow-hidden">
        <!-- Clean Background -->

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <h2 class="text-3xl md:text-5xl font-extrabold text-center mb-6 text-black">
                Masalah yang Hampir <span class="text-red-600">Semua Tim</span> Alami
            </h2>
            <p class="text-center text-xl text-blue-700 mb-16 font-semibold"></p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto mb-16">

                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center hover:shadow-md transition-shadow">
                    <svg class="w-8 h-8 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                    <p class="text-sm text-slate-700 font-medium">Copy paste manual</p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center hover:shadow-md transition-shadow">
                    <svg class="w-8 h-8 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <p class="text-sm text-slate-700 font-medium">Info terlewatkan</p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center hover:shadow-md transition-shadow">
                    <svg class="w-8 h-8 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm text-slate-700 font-medium">Banyak waktu terbuang</p>
                </div>

                <div class="p-6 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col items-center justify-center text-center hover:shadow-md transition-shadow">
                    <svg class="w-8 h-8 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <p class="text-sm text-slate-700 font-medium">Typo & Human Error</p>
                </div>

            </div>

            <div
                class="text-center max-w-2xl mx-auto bg-blue-700 p-12 rounded-2xl shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                <p class="text-[16px] md:text-[20px] font-black text-white mb-6 leading-tight">
                    Eits... tenang! Sekarang ada koladi yang bisa mengatasi
                    semua permasalahan tersebut!
                </p>
                <div class="space-y-4">
                    <a href="{{ route('daftar') }}">
                        <button
                            class="px-4 py-3 bg-white text-blue-600 rounded-full text-[14px] font-bold hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300">
                            COBA SOLUSINYA GRATIS! →
                        </button>
                    </a>
                    <p class="text-white font-semibold text-[15px]">Gratis 7 hari, tanpa tanpa biaya apapun</p>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Project Planning Assistant (Creative / Premium) -->
    <section class="py-20 bg-white relative overflow-hidden">
        <!-- Clean Background -->
        <div class="absolute inset-0 pointer-events-none">
            <!-- subtle grid -->
            <div
                class="absolute inset-0 opacity-[0.06] [background-image:linear-gradient(to_right,#0f172a_1px,transparent_1px),linear-gradient(to_bottom,#0f172a_1px,transparent_1px)] [background-size:48px_48px]">
            </div>
        </div>

        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="max-w-6xl mx-auto">
                <!-- header -->
                <div class="text-center mb-10">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 bg-white/70 backdrop-blur-xl shadow-sm">
                        <span class="text-sm font-extrabold text-slate-900">Koladi</span>
                        <span class="px-2 py-0.5 rounded-full text-[12px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Project Planner</span>
                    </div>

                    <h3 class="mt-4 text-3xl md:text-5xl font-extrabold text-slate-900 leading-tight">
                        Cukup Upload, <span class="text-blue-600">Sistem Buatkan Draft-nya!</span> <br>
                        <span class="text-slate-500 text-2xl md:text-3xl font-semibold mt-2 block">
                            Kerja Cerdas Tanpa Setup Manual
                        </span>
                    </h3>

                    <p class="mt-4 text-base md:text-lg text-slate-700 max-w-3xl mx-auto leading-relaxed">
                        Anda tidak perlu lagi memindahkan data satu per satu. AI akan membaca seluruh konteks dan Anda tinggal
                        <span class="text-slate-900 font-semibold px-2 bg-blue-600/15 drop-shadow-[0_0_14px_rgba(37,99,235,0.35)]">Review & Approve</span>.
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
                                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 shadow-sm border border-blue-100">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    </span>
                                    <div class="leading-tight">
                                        <p class="text-sm font-extrabold text-slate-900">Koladi • Brief Parser
                                        </p>
                                        <p class="text-xs font-medium text-slate-500">
                                            Baca Brief → Ekstrak Info → Jadi Task
                                        </p>
                                    </div>
                                </div>

                                <!-- headline -->
                                <h4 class="mt-5 text-2xl md:text-3xl font-extrabold text-slate-900 leading-tight">
                                    Menganalisis dokumen dalam format <span class="text-blue-700">PDF, DOCX, TXT, dan Chat</span> sekaligus.
                                </h4>

                                <p class="mt-3 text-slate-700 leading-relaxed">
                                    Koladi AI secara otomatis menghasilkan struktur JSON terstruktur siap pakai yang sangat detail.
                                </p>

                                <!-- proof line -->
                                <div class="mt-5 rounded-2xl border border-blue-200/80 bg-blue-50 p-4">
                                    <p class="text-slate-900 font-extrabold">
                                        Tetap Anda Kendalinya —
                                        <span class="text-blue-700">AI Bukan Pengambil Keputusan.</span>
                                    </p>
                                    <p class="mt-1 text-sm text-slate-600 font-medium">
                                        AI hanya membuatkan draft. Anda yang menentukan, mengedit, dan setuju untuk membuat project.
                                    </p>
                                </div>

                                <div class="mt-6 grid sm:grid-cols-3 gap-4">
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="h-9 w-9 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                            </span>
                                            <div class="leading-tight">
                                                <p class="text-sm font-extrabold text-slate-900">Draft Tasks</p>
                                            </div>
                                        </div>
                                        <p class="mt-3 text-[13px] text-slate-700 leading-relaxed">
                                            Membuat daftar tugas lengkap dengan prioritas dan estimasi deadline.
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="h-9 w-9 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </span>
                                            <div class="leading-tight">
                                                <p class="text-sm font-extrabold text-slate-900">Missing Info</p>
                                            </div>
                                        </div>
                                        <p class="mt-3 text-[13px] text-slate-700 leading-relaxed">
                                            Mendeteksi informasi yang belum jelas (budget, PIC) untuk diklarifikasi.
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="h-9 w-9 rounded-xl bg-slate-50 text-slate-600 border border-slate-200 flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            </span>
                                            <div class="leading-tight">
                                                <p class="text-sm font-extrabold text-slate-900">Traceability</p>
                                            </div>
                                        </div>
                                        <p class="mt-3 text-[13px] text-slate-700 leading-relaxed">
                                            Memberitahu Anda dari dokumen mana informasi tersebut didapatkan.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: risk box -->
                            <div class="lg:col-span-5">
                                <div
                                    class="relative overflow-hidden rounded-3xl border border-blue-200/70 bg-white shadow-sm p-6">
                                    <div class="relative flex items-center justify-between gap-4">
                                        <div>
                                            <p class="text-xs font-black text-blue-700 tracking-wider uppercase">FLOW
                                            </p>
                                            <p class="mt-1 text-base font-extrabold text-slate-900 leading-tight">
                                                Proses Kerja AI
                                            </p>
                                        </div>

                                        <span
                                            class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-3 py-1 text-xs font-black text-white">
                                            <span class="h-2 w-2 rounded-full bg-white/90"></span>
                                            SIMPLE
                                        </span>
                                    </div>

                                    <div class="relative mt-5 space-y-3">
                                        <div
                                            class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                            <span
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 shadow-sm border border-slate-200 font-bold text-sm">
                                                1
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-extrabold text-slate-900 leading-snug">Upload Dokumen</p>
                                                <p class="mt-0.5 text-sm text-slate-600 font-medium leading-snug">
                                                    Unggah PDF, DOCX, TXT, Email, atau Chat sekaligus.
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                            <span
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 shadow-sm border border-slate-200 font-bold text-sm">
                                                2
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-extrabold text-slate-900 leading-snug">AI Menganalisis</p>
                                                <p class="mt-0.5 text-sm text-slate-600 font-medium leading-snug">
                                                    Parser pintar menormalisasi teks dan mengekstrak poin-poin.
                                                </p>
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                                            <span
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 text-slate-700 shadow-sm border border-slate-200 font-bold text-sm">
                                                3
                                            </span>
                                            <div class="min-w-0">
                                                <p class="font-extrabold text-slate-900 leading-snug">Human Review</p>
                                                <p class="mt-0.5 text-sm text-slate-600 font-medium leading-snug">
                                                    Validasi, edit jika perlu, lalu simpan menjadi project.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative mt-5 border-t border-slate-200 pt-4">
                                        <p class="text-sm text-slate-600 font-semibold">
                                            AI di Koladi tidak mengarang. <span class="text-slate-900 font-extrabold">Hanya mengambil fakta</span> dari dokumen.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="mt-10 md:mt-20 flex flex-col items-center text-center">
                    <!-- JUDUL -->
                    <h2 class="mb-6 md:mb-10 max-w-4xl text-2xl md:text-4xl font-bold text-slate-900">
                        <span class="text-slate-800">
                            Didukung dengan Teknologi Terkini
                        </span>
                    </h2>

                    <!-- IMAGE -->
                    <div class="relative w-full max-w-7xl">
                        <img src="images/ai.svg" alt="Koladi AI"
                            class="w-full h-auto max-h-[700px] object-contain scale-110 md:scale-125" />
                    </div>
                </div>
            </div>
        </div>
    </section>




    <!-- Features Section -->
    <section id="fitur" class="py-20 bg-slate-50 text-slate-800 relative overflow-hidden">
        <div class="container mx-auto px-4 lg:px-8 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-[40px] font-black mb-4 text-slate-900">
                    Fitur-Fitur Koladi
                </h2>
                <p class="text-lg text-slate-600 font-medium">Semua yang tim Anda butuhkan, dalam satu tempat!</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto mb-16">
                <!-- Tambah Fitur AI di Sini -->
                <div
                    class="group p-8 bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                    <div class="mb-4 text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-slate-800">AI Planner</h3>
                    <p class="text-sm text-slate-500">Otomatis buat task dari dokumen</p>
                </div>

                <div
                    class="group p-8 bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                    <div class="mb-4 text-slate-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-slate-800">Workspaces</h3>
                    <p class="text-sm text-slate-500">Ruang kerja terpadu untuk tim</p>
                </div>

                <div
                    class="group p-8 bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                    <div class="mb-4 text-slate-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-slate-800">Pengumuman</h3>
                    <p class="text-sm text-slate-500">Broadcast info penting dengan mudah</p>
                </div>

                <div
                    class="group p-8 bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                    <div class="mb-4 text-slate-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-slate-800">Kanban Tugas</h3>
                    <p class="text-sm text-slate-500">Kelola task dengan sistem kanban</p>
                </div>

                <div
                    class="group p-8 bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                    <div class="mb-4 text-slate-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-slate-800">Chat</h3>
                    <p class="text-sm text-slate-500">Komunikasi real-time dengan tim</p>
                </div>

                <div
                    class="group p-8 bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                    <div class="mb-4 text-slate-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-slate-800">Timeline</h3>
                    <p class="text-sm text-slate-500">Pantau progress secara visual</p>
                </div>

                <div
                    class="group p-8 bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                    <div class="mb-4 text-slate-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-slate-800">Statistik</h3>
                    <p class="text-sm text-slate-500">Analitik performa tim lengkap</p>
                </div>

                <div
                    class="group p-8 bg-white rounded-2xl shadow-sm border border-slate-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300">
                    <div class="mb-4 text-slate-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold mb-2 text-slate-800">Dokumen</h3>
                    <p class="text-sm text-slate-500">Kelola semua file di satu tempat</p>
                </div>
            </div>

            <div
                class="text-center bg-blue-700 p-10 rounded-2xl shadow-xl max-w-2xl mx-auto transform hover:-translate-y-1 transition-all duration-300">
                <h3 class="text-3xl md:text-[25px] font-black text-white mb-4">
                    Dapatkan Semua Fitur Ini GRATIS!
                </h3>
                <p class=" text-white mb-6 font-bold text[16px]">Coba selama 7 hari tanpa biaya apapun</p>
                <a href="{{ route('daftar') }}">
                    <button
                        class="px-4 py-3 bg-white text-blue-600 rounded-full text-[13px] font-black hover:bg-gray-100 shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                        MULAI GRATIS SEKARANG! →
                    </button>
                </a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="tentang" class="py-20 bg-slate-50">
        <div class="container mx-auto px-4 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-4xl md:text-5xl font-bold mb-8 text-gray-800">
                    Apa itu Koladi?
                </h2>
                <p class="text-xl text-gray-700 leading-relaxed">
                    Koladi adalah AI Project Planning Assistant yang membantu Anda dan tim mengubah dokumen acak (PDF, Word, WhatsApp) menjadi task yang terstruktur tanpa perlu pusing copy-paste secara manual. Terintegrasi langsung ke fitur Manajemen Tugas, Chat, Penjadwalan, dan File Sharing.
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
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">Starter</h3>
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
                    class="bg-blue-700 rounded-2xl shadow-xl p-8 transform md:scale-105 hover:-translate-y-1 transition-all duration-300 text-white border border-blue-600">
                    <div
                        class="bg-yellow-400 text-purple-900 text-sm font-bold px-4 py-1 rounded-full inline-block mb-4">
                        POPULER</div>
                    <h3 class="text-2xl font-bold mb-4">Team</h3>
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
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">Agency</h3>
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
        class="py-20 bg-slate-900 relative overflow-hidden">
        <!-- Clean Background -->
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
