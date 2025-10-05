<div class="w-64 bg-white shadow-sm flex flex-col border-r border-gray-200">
    {{-- Logo --}}
   <div class="h-16 flex items-center justify-center px-4 border-b border-gray-200">
        <img src="/images/logo-koladi.png"  class="h-28 mt-12">
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-4 px-3 space-y-1 overflow-auto">
        {{-- Dashboard --}}
    <a href="{{ url('/dashboard') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
        <img src="/images/icons/dashboard.png" alt="" class="w-5 h-5">
        <span class="text-sm">Dashboard</span>
    </a>

    {{-- Workspace --}}
    <a href="{{ url('/workspace') }}"
       class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
        <img src="/images/icons/ruang-kerja.png" alt="" class="w-5 h-5">
        <span class="text-sm">Ruang Kerja</span>
    </a>

        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <img src="/images/icons/chat.png" alt="" class="w-5 h-5">
            <span class="text-sm">Chat</span>
        </a>

        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <img src="/images/icons/semua-tugas.png" alt="" class="w-5 h-5">
            <span class="text-sm">Semua Tugas</span>
        </a>

        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <img src="/images/icons/laporan-kinerja.png" alt="" class="w-5 h-5">
            <span class="text-sm">Laporan Kinerja</span>
        </a>

        <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <img src="/images/icons/cuti.png" alt="" class="w-5 h-5">
            <span class="text-sm">Cuti</span>
        </a>

       {{-- Workspace Section --}}
<div class="pt-3 mt-3 border-t border-gray-200">
    <div class="flex items-center gap-2">
        <!-- Kolom Pencarian -->
        <div class="flex items-center gap-2 px-2 py-1    rounded-lg bg-gray-50 text-gray-400 text-xs flex-1 min-w-0 hover:text-gray-600 transition">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
    </svg>
    <input
        type="text"
        placeholder="Cari ruang..."
        class="flex-1 bg-transparent outline-none border-none focus:ring-0 text-gray-700 text-xs min-w-0"
    />
</div>


        <!-- Tombol tambah pertama -->
        <button class="flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition flex-shrink-0">
            <img src="/images/icons/add.png" alt="" class="w-4 h-4">
        </button>

        <!-- Tombol tambah kedua -->
        <button class="flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition flex-shrink-0">
            <img src="/images/icons/add.png" alt="" class="w-4 h-4">
        </button>
    </div>
</div>



{{-- Workspace List --}}
<div class="mt-3 space-y-0.5" x-data="{ openHQ: true, openTim: true, openProyek: true }">

    {{-- HQ --}}
    <div class="px-3 py-1.5">
        <div class="flex items-center justify-between text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
             @click="openHQ = !openHQ">
            <div class="flex items-center gap-2">
                <img src="/images/icons/hq.png" alt="" class="w-4 h-4">
                <span>HQ</span>
            </div>

            <!-- Arrow -->
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <!-- Panah kanan -->
                <path x-show="!openHQ" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                <!-- Panah bawah -->
                <path x-show="openHQ" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

    <div x-show="openHQ" class="space-y-0.5 mt-1">
        <a href="#" class="flex items-center gap-2 px-6 py-1.5 text-sm text-gray-600 hover:bg-gray-50 rounded transition">
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
            <span>Mencari Cinta HQ</span>
        </a>
    </div>

    {{-- TIM --}}
    <div class="px-3 py-1.5 mt-2">
        <div class="flex items-center justify-between text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
             @click="openTim = !openTim">
            <div class="flex items-center gap-2">
                <img src="/images/icons/tim.png" alt="" class="w-4 h-4">
                <span>Tim</span>
            </div>

            <!-- Arrow -->
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <!-- Panah kanan -->
                <path x-show="!openTim" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                <!-- Panah bawah -->
                <path x-show="openTim" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

    <div x-show="openTim" class="space-y-0.5 mt-1">
        <a href="#" class="flex items-center gap-2 px-6 py-1.5 text-sm text-blue-600 bg-blue-50 font-medium rounded transition">
            <span class="w-1.5 h-1.5 bg-blue-600 rounded-full"></span>
            <span>Div. Marketing</span>
        </a>
        <a href="#" class="flex items-center gap-2 px-6 py-1.5 text-sm text-gray-600 hover:bg-gray-50 rounded transition">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
            <span>Div. Pelayanan</span>
        </a>
        <a href="#" class="flex items-center gap-2 px-6 py-1.5 text-sm text-gray-600 hover:bg-gray-50 rounded transition">
            <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
            <span>Div. Creativ</span>
        </a>
    </div>

    {{-- PROYEK --}}
    <div class="px-3 py-1.5 mt-2">
        <div class="flex items-center justify-between text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
             @click="openProyek = !openProyek">
            <div class="flex items-center gap-2">
                <img src="/images/icons/proyek.png" alt="" class="w-4 h-4">
                <span>Proyek</span>
            </div>

            <!-- Arrow -->
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <!-- Panah kanan -->
                <path x-show="!openProyek" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                <!-- Panah bawah -->
                <path x-show="openProyek" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>

    <div x-show="openProyek" class="space-y-0.5 mt-1">
        <a href="#" class="flex items-center gap-2 px-6 py-1.5 text-sm text-gray-600 hover:bg-gray-50 rounded transition">
            <span class="w-1.5 h-1.5 bg-blue-700 rounded-full"></span>
            <span>Creativ Konten</span>
        </a>
        <a href="#" class="flex items-center gap-2 px-6 py-1.5 text-sm text-gray-600 hover:bg-gray-50 rounded transition">
            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
            <span>Pelayan Creativ</span>
        </a>
    </div>

</div>



        </div>
    </nav>
</div>
