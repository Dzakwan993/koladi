<div class="w-64 bg-white shadow-sm flex flex-col border-r border-gray-200 h-screen">
    {{-- Logo --}}
    <div class="h-16 flex items-center justify-center px-4 border-b border-gray-200">
        <img src="/images/logo-koladi.png" class="h-28 mt-12">
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
        {{-- Dashboard --}}
        <a href="{{ url('/dashboard') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-blue-600 bg-blue-50 font-medium transition">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span class="text-sm">Dashboard</span>
        </a>

        {{-- Ruang Kerja --}}
        <a href="{{ url('/workspace') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <img src="{{ asset('images/ruang-kerja.svg') }}" alt="Ruang Kerja"
                class="w-5 h-5 flex-shrink-0 object-contain">
            <span class="text-sm">Ruang Kerja</span>
        </a>

        {{-- Laporan Kinerja --}}
        <a href="#"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <img src="{{ asset('images/kinerja.svg') }}" alt="Laporan Kinerja"
                class="w-5 h-5 flex-shrink-0 object-contain">
            <span class="text-sm">Laporan Kinerja</span>
        </a>

        {{-- Cuti --}}
        <a href="{{ url('/cutimanajer') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <img src="{{ asset('images/cuti.svg') }}" alt="Cuti" class="w-5 h-5 flex-shrink-0 object-contain">
            <span class="text-sm">Cuti</span>
        </a>

        {{-- Semua Tugas --}}
        <a href="#"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 transition">
            <img src="{{ asset('images/tugas.svg') }}" alt="Semua Tugas" class="w-5 h-5 flex-shrink-0 object-contain">
            <span class="text-sm">Semua Tugas</span>
        </a>

        {{-- Search & Action Buttons --}}
        <div class="pt-3 mt-3 border-t border-gray-200">
            <div class="flex items-center gap-2">
                {{-- Search Input --}}
                <div
                    class="flex items-center gap-2 px-2.5 py-2 rounded-lg bg-gray-50 text-gray-400 text-xs flex-1 min-w-0 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" placeholder="Cari Tim..."
                        class="flex-1 bg-transparent outline-none border-none focus:ring-0 text-gray-700 text-xs min-w-0 p-0" />
                </div>

                {{-- Filter Button --}}
                <button
                    class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                </button>

                {{-- Add Button --}}
                <button
                    class="flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Workspace List --}}
        <div class="mt-3 space-y-1" x-data="{ openHQ: true, openTim: true, openProyek: true }">

            {{-- HQ Section --}}
            <div>
                <button @click="openHQ = !openHQ"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg transition">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/hq.svg') }}" alt="HQ"
                            class="w-4 h-4 flex-shrink-0 object-contain">
                        <span>HQ</span>
                    </div>
                    <svg class="w-3 h-3 transition-transform flex-shrink-0" :class="{ 'rotate-90': openHQ }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <div x-show="openHQ" x-transition class="mt-1 space-y-0.5">
                    <a href="#"
                        class="flex items-center gap-2.5 px-6 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition">
                        <span class="w-2 h-2 bg-gray-400 rounded-full flex-shrink-0"></span>
                        <span>Mencari Cinta HQ</span>
                    </a>
                </div>
            </div>

            {{-- TIM Section --}}
            <div>
                <button @click="openTim = !openTim"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg transition">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/tim.svg') }}" alt="Tim"
                            class="w-4 h-4 flex-shrink-0 object-contain">
                        <span>Tim</span>
                    </div>
                    <svg class="w-3 h-3 transition-transform flex-shrink-0" :class="{ 'rotate-90': openTim }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <div x-show="openTim" x-transition class="mt-1 space-y-0.5">
                    <a href="#"
                        class="flex items-center gap-2.5 px-6 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition">
                        <span class="w-2 h-2 bg-blue-600 rounded-full flex-shrink-0"></span>
                        <span>Div. Marketing</span>
                    </a>
                    <a href="#"
                        class="flex items-center gap-2.5 px-6 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition">
                        <span class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></span>
                        <span>Div. Pelayanan</span>
                    </a>
                    <a href="#"
                        class="flex items-center gap-2.5 px-6 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition">
                        <span class="w-2 h-2 bg-purple-500 rounded-full flex-shrink-0"></span>
                        <span>Div. Creativ</span>
                    </a>
                </div>
            </div>

            {{-- PROYEK Section --}}
            <div>
                <button @click="openProyek = !openProyek"
                    class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg transition">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/proyek.svg') }}" alt="Proyek"
                            class="w-4 h-4 flex-shrink-0 object-contain">
                        <span>Proyek</span>
                    </div>
                    <svg class="w-3 h-3 transition-transform flex-shrink-0" :class="{ 'rotate-90': openProyek }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                        </path>
                    </svg>
                </button>

                <div x-show="openProyek" x-transition class="mt-1 space-y-0.5">
                    <a href="#"
                        class="flex items-center gap-2.5 px-6 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition">
                        <span class="w-2 h-2 bg-blue-700 rounded-full flex-shrink-0"></span>
                        <span>Creativ Konten</span>
                    </a>
                    <a href="#"
                        class="flex items-center gap-2.5 px-6 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition">
                        <span class="w-2 h-2 bg-gray-400 rounded-full flex-shrink-0"></span>
                        <span>Pelayan Creativ</span>
                    </a>
                    <a href="#"
                        class="flex items-center gap-2.5 px-6 py-2 text-sm text-gray-600 hover:bg-gray-50 rounded-lg transition">
                        <span class="w-2 h-2 bg-gray-400 rounded-full flex-shrink-0"></span>
                        <span>Pelayan Creativ</span>
                    </a>
                </div>
            </div>

        </div>
    </nav>
</div>
