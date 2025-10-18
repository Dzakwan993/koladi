<div class="h-16 bg-white shadow-sm flex items-center px-6 justify-between border-b border-gray-200">
    <!-- Left Section: Logo & Company Name -->
    <div class="flex items-center gap-3">
        <img src="{{ asset('images/logo-pt.svg') }}" alt="Logo PT" class="h-8 w-8">
        <span class="text-gray-600 font-medium text-sm whitespace-nowrap">
            {{ $activeCompany->name ?? 'Belum ada perusahaan' }}
        </span>
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
                <img src="https://i.pravatar.cc/32?img=6" alt="User 3"
                    class="w-7 h-7 rounded-full border-2 border-white ring-1 ring-gray-200">
            </div>
            <span class="text-xs text-gray-600 whitespace-nowrap">
                <span class="font-medium">Sahroni</span> dan
                <button class="text-blue-600 hover:text-blue-700 font-medium">
                    5 lainnya
                </button>
                aktif
            </span>
        </div>

        <!-- Divider -->
        <div class="h-6 w-px bg-gray-200"></div>

        <!-- Action Buttons -->
        <button class="p-1 hover:bg-gray-50 rounded-lg transition" title="Atur Akses" onclick="openModal()">
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

                <!-- List Perusahaan di Dropdown -->
                <div class="py-2 max-h-96 overflow-y-auto">
                    @forelse($companies as $company)
                        <a href="{{ route('company.switch', $company->id) }}"
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
                                <span class="text-sm font-medium text-gray-700">{{ $company->name }}</span>
                            </div>
                            @if ($activeCompany && $company->id == $activeCompany->id)
                                <img src="{{ asset('images/icons/centang.svg') }}" alt="Active" class="w-5 h-5">
                            @endif
                        </a>
                    @empty
                        <div class="px-4 py-3 text-sm text-gray-500 text-center">
                            Belum ada perusahaan
                        </div>
                    @endforelse
                </div>

                <!-- Footer - Tambah Perusahaan -->
                <div class="border-t border-gray-200">
                    <a href="{{ url('buat-perusahaan') }}"
                        class="flex items-center gap-3 px-4 py-3 w-full text-left hover:bg-gray-50 transition">
                        <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <a href="{{ url('profile') }}"
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
