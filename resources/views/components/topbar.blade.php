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
            <!-- Notification Badge (optional) -->
            <!-- <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span> -->
        </button>

        <button class="p-2 hover:bg-gray-100 rounded-lg transition" title="Kantor">
            <img src="{{ asset('images/icons/kantor.svg') }}" alt="Kantor" class="w-5 h-5">
        </button>

        <!-- Profile Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.away="open = false"
                class="w-9 h-9 rounded-full overflow-hidden border-2 border-gray-200 hover:border-blue-400 transition focus:outline-none focus:ring-2 focus:ring-blue-300">
                <img src="https://i.pravatar.cc/36?img=8" alt="Profile" class="w-full h-full object-cover">
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">

                <!-- User Info -->
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-medium text-gray-900">Sahroni</p>
                    <p class="text-xs text-gray-500 truncate">sahroni@example.com</p>
                </div>

                <!-- Menu Items -->
                <a href="#"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Profil</span>
                </a>

                <button onclick="handleLogout()"
                    class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition w-full text-left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Logout</span>
                </button>
            </div>
        </div>
    </div>
</div>

