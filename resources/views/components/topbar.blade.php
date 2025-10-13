<div class="h-16 bg-white shadow-sm flex items-center px-6 justify-between border-b border-gray-200">
    <!-- Left Section: Logo & Company Name -->
    <div class="flex items-center gap-3">
        <img src="{{ asset('images/logo-pt.svg') }}" alt="Logo PT" class="h-8 w-8">
        <span class="text-gray-600 font-medium text-sm whitespace-nowrap">PT. Mencari Cinta Sejati</span>
    </div>

    <!-- Center Section: Search Bar -->
    <div class="flex-1 max-w-md mx-6">
        <div class="relative">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" placeholder="Cari ruang kerja, tugas..."
                class="w-full pl-10 pr-4 py-2 bg-[#E9EFFD] border-0 rounded-lg text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:bg-white transition">
        </div>
    </div>

    <!-- Right Section: Active Users & Action Buttons -->
    <div class="flex items-center gap-3">
        <!-- Active Users -->
        <div class="flex items-center gap-2">
            <div class="flex -space-x-2">
                <img src="https://i.pravatar.cc/32?img=1" alt="User 1"
                    class="w-7 h-7 rounded-full border-2 border-white ring-1 ring-gray-200">
                <img src="https://i.pravatar.cc/32?img=2" alt="User 2"
                    class="w-7 h-7 rounded-full border-2 border-white ring-1 ring-gray-200">
                <img src="https://i.pravatar.cc/32?img=3" alt="User 3"
                    class="w-7 h-7 rounded-full border-2 border-white ring-1 ring-gray-200">
            </div>
            <span class="text-xs text-gray-600 whitespace-nowrap">
                <span class="font-medium">Sahroni</span> dan
                <button class="text-blue-600 hover:text-blue-700 font-medium" onclick="openModal()">
                    5 lainnya
                </button>
                aktif
            </span>
        </div>

        <!-- Divider -->
        <div class="h-6 w-px bg-gray-200"></div>

        <!-- Action Buttons -->
        <button class="p-2 hover:bg-gray-100 rounded-lg transition" title="Atur Akses">
            <img src="{{ asset('images/icons/akses.svg') }}" alt="Atur Akses" class="w-5 h-5">
        </button>

        <button class="p-2 hover:bg-gray-100 rounded-lg transition" title="Dollar">
            <img src="{{ asset('images/icons/dollar.svg') }}" alt="Dollar" class="w-5 h-5">
        </button>

        <button class="p-2 hover:bg-gray-100 rounded-lg transition relative" title="Notifikasi">
            <img src="{{ asset('images/icons/notifikasi.svg') }}" alt="Notifikasi" class="w-5 h-5">
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>

        <!-- Button Perusahaan dengan Alpine.js -->
        <div class="relative" x-data="{ openCompany: false }">
            <button @click="openCompany = !openCompany" class="p-2 hover:bg-gray-100 rounded-lg transition"
                title="Perusahaan">
                <img src="{{ asset('images/icons/kantor.svg') }}" alt="Perusahaan" class="w-5 h-5">
            </button>

            <!-- Pop-up Ganti Perusahaan -->
            <div x-show="openCompany" @click.away="openCompany = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-gray-200 z-50"
                style="display: none;">

                <!-- Header -->
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="text-base font-semibold text-gray-900">Beralih perusahaan</h3>
                </div>

                <!-- List Perusahaan -->
                <div class="py-2 max-h-96 overflow-y-auto">
                    <!-- PT. Mencari Cinta Sejati -->
                    <a href="#"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700">PT. Mencari Cinta Sejati</span>
                        </div>
                        <div x-data="{ showModal: false, showConfirm: false }" class="relative">
                            <!-- Tombol Pengaturan -->
                            <div class="flex items-center gap-1">
                                <img src="{{ asset('images/icons/pengaturan.svg') }}" alt="Perusahaan"
                                    class="w-5 h-5 cursor-pointer transition duration-200 hover:text-[#225ad6]"
                                    @click="showModal = true">
                                <img src="{{ asset('images/icons/centang.svg') }}" alt="Perusahaan" class="w-5 h-5">
                            </div>

                            <!-- Modal Popup -->
                            <div x-show="showModal"
                                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
                                x-transition>
                                <div
                                    class="bg-gradient-to-br from-[#f4f7ff] to-[#e9f0ff] rounded-2xl shadow-2xl w-[520px] p-8 relative border border-white/30">
                                    <!-- Tombol Close -->
                                    <button @click="showModal = false"
                                        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>

                                    <!-- Gambar -->
                                    <div class="flex justify-center mb-6">
                                        <img src="{{ asset('images/pengaturan-perusahaan.svg') }}" alt="Kantor"
                                            class="w-64 h-auto drop-shadow-md">
                                    </div>

                                    <!-- Input Nama -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama
                                            perusahaan</label>
                                        <input type="text" value="PT. Mencari cinta sejati"
                                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-[#225ad6] focus:border-[#225ad6] shadow-sm transition">
                                    </div>

                                    <!-- Tombol Aksi -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex gap-3">
                                            <button
                                                class="bg-[#2563EB] text-white px-5 py-2.5 rounded-lg hover:bg-[#1d4cc1] shadow-sm transition">
                                                Simpan
                                            </button>
                                            <button
                                                class="border border-[#2563EB] text-[#2563EB] px-5 py-2.5 rounded-lg hover:bg-[#225ad6] hover:text-white transition"
                                                @click="showModal = false">
                                                Batal
                                            </button>
                                        </div>

                                        <!-- Tombol Hapus -->
                                        <button
                                            class="flex items-center gap-2 bg-[#b7791f] hover:bg-[#695609] text-white px-4 py-2.5 rounded-lg transition shadow-sm"
                                            @click="showConfirm = true">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m5 0H6" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>

                                    <!-- Konfirmasi Hapus -->
                                    <div x-show="showConfirm"
                                        class="absolute right-6 bottom-24 bg-white border border-gray-200 rounded-xl shadow-xl p-4 w-64 transition-all duration-200"
                                        x-transition>
                                        <p class="font-semibold text-gray-800 mb-1">Hapus perusahaan?</p>
                                        <p class="text-sm text-gray-500 mb-4 leading-snug">Perusahaan akan dihapus dan
                                            semua datanya akan hilang selamanya.</p>
                                        <button
                                            class="bg-[#b7791f] hover:bg-[#695609] text-white w-full py-2.5 rounded-lg font-medium transition">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>

                    <!-- PT. Mencari cinta sejati (3) -->
                    <a href="#"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700">PT. Mencari cinta sejati</span>
                        </div>
                        <div
                            class="w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold text-white">
                            10
                        </div>
                    </a>

                    <!-- PT. Mencari cinta sejati (4) -->
                    <a href="#"
                        class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700">PT. Mencari cinta sejati</span>
                        </div>
                        <div
                            class="w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold text-white">
                            10
                        </div>
                    </a>
                </div>

                <!-- Footer - Tambah Perusahaan -->
                <div class="border-t border-gray-200">
                    <a href="{{ url('buat-perusahaan') }}"
                        class="flex items-center gap-3 px-4 py-3 w-full text-left hover:bg-gray-50 transition">
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Tambah perusahaan</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false"
                class="w-9 h-9 rounded-full overflow-hidden border-2 border-gray-200 hover:border-[#225ad6] transition">
                <img src="https://i.pravatar.cc/36?img=8" alt="Profile" class="w-full h-full object-cover">
            </button>

            <!-- Dropdown -->
            <div x-show="open" x-transition
                class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
                style="display: none;">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 text-center">Profil</h3>
                </div>

                <!-- User Info -->
                <div class="px-6 py-5">
                    <div class="flex items-center gap-3">
                        <img src="https://i.pravatar.cc/80?img=12" alt="User Photo"
                            class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="font-semibold text-gray-900 text-base">Muhammad Sahroni</h4>
                            </div>
                            <span
                                class="inline-block bg-[#225ad6] text-white text-xs font-semibold px-2.5 py-1 rounded mt-1">
                                Super Admin
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <div class="border-t border-gray-200">
                    <a href="#"
                        class="flex items-center gap-3 px-6 py-3.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <span class="font-medium">Ubah profile</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-3 px-6 py-3.5 text-sm text-gray-700 hover:bg-gray-50 transition w-full text-left">
                            <div
                                class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                            </div>
                            <span class="font-medium">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
