<style>
    .filter-blue {
        filter: brightness(0) saturate(100%) invert(30%) sepia(91%) saturate(1539%) hue-rotate(213deg) brightness(90%) contrast(96%);
    }
</style>


{{-- resources/views/components/sidebar.blade.php --}}
<div x-data="{ openSidebar: window.innerWidth >= 992 }" x-init="const handleResize = () => {
    if (window.innerWidth < 992 && openSidebar) openSidebar = false;
    else if (window.innerWidth >= 992 && !openSidebar) openSidebar = true;
};
window.addEventListener('resize', handleResize);" class="flex h-screen relative">

    {{-- Tombol Toggle (hamburger / close) --}}
    <button @click="openSidebar = !openSidebar"
        class="absolute top-4 left-4 z-10 bg-white border border-gray-200 shadow-md rounded-lg p-2 hover:bg-gray-100 transition">
        <template x-if="!openSidebar">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="w-5 h-5 text-gray-700">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </template>
        <template x-if="openSidebar">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="w-5 h-5 text-gray-700">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </template>
    </button>

    {{-- Sidebar --}}
    <div x-show="openSidebar"
        class="w-64 bg-white shadow-sm border-r border-gray-200 h-screen transition-all duration-300 fixed md:relative "
        x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
        {{-- Logo --}}
        <div class="h-16 flex items-center justify-center px-4 border-b border-gray-200">
            <img src="/images/logo-koladi.png" class="h-28 mt-12" alt="Koladi Logo">
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto" x-data="{ openHQ: true, openTim: true, openProyek: true }">

            {{-- Dashboard --}}
            <a href="{{ url('/dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                      {{ Request::is('dashboard*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="/images/icons/sidebar_dashboard.svg" alt="Dashboard"
                    class="w-5 h-5 {{ Request::is('dashboard*') ? 'filter-blue' : '' }}"> <span
                    class="text-sm">Dashboard</span>
            </a>

            {{-- Ruang Kerja --}}
            <a href="{{ url('/kelola-workspace') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                      {{ Request::is('kelola-workspace*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="/images/icons/sidebar_ruang-kerja.svg" alt="Ruang Kerja"
                    class="w-5 h-5 {{ Request::is('kelola-workspace*') ? 'filter-blue' : '' }}"> <span
                    class="text-sm">Ruang Kerja</span>
            </a>

         @php
    $company_id = session('active_company_id');
@endphp

@if($company_id)
<a href="{{ route('pengumuman-perusahaan.index', ['company_id' => $company_id]) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
   {{ Request::is('companies/*/pengumuman-perusahaan*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
    <img src="{{ asset('images/icons/workspace_pengumuman.svg') }}" class="w-5 h-5">
    <span class="text-sm">Pengumuman</span>
</a>
@endif








            {{-- Chat --}}
            <a href="{{ url('/chat') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                      {{ Request::is('chat*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="/images/icons/sidebar_chat.svg" alt="Chat"
                    class="w-5 h-5 {{ Request::is('chat*') ? 'filter-blue' : '' }}">
                <span class="text-sm">Chat</span>
            </a>

            {{-- Jadwal --}}
            <a href="{{ url('/jadwal') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                {{ Request::is('jadwal*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="images/icons/workspace_kalender.svg" alt="Jadwal"
                    class="w-5 h-5 {{ Request::is('jadwal*') ? 'filter-blue' : '' }}"> <span
                    class="text-sm">Jadwal</span>
            </a>

            {{-- Dokumen --}}
            <a href="{{ url('/dokumen-dan-file') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                {{ Request::is('dokumen*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="images/icons/workspace_dokumen&file.svg" alt="Dokumen"
                    class="w-5 h-5 {{ Request::is('dokumen*') ? 'filter-blue' : '' }}"> <span
                    class="text-sm">Dokumen</span>
            </a>

            {{-- Laporan Kinerja --}}
             {{-- Laporan Kinerja --}}
            <a href="{{ url('/statistik') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                      {{ Request::is('statistik*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="/images/icons/sidebar_laporan-kinerja.svg" alt="Laporan Kinerja"
                    class="w-5 h-5 {{ Request::is('statistik*') ? 'filter-blue' : '' }}"> <span class="text-sm">Laporan
                    Kinerja</span>
            </a>

            {{-- Search & Actions --}}
            <div class="pt-3 mt-3 border-t border-gray-200">
                <div class="flex items-center gap-1.5">
                    {{-- Search Bar (kecil) --}}
                    <div
                        class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-gray-50 text-gray-400 text-xs flex-1 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" placeholder="Cari ruang..."
                            class="flex-1 bg-transparent outline-none border-none focus:ring-0 text-gray-700 text-[11px]">
                    </div>

                    {{-- Filter Button (pakai gambar kamu) --}}
                    <button
                        class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition"
                        title="Filter">
                        <img src="/images/icons/sidebar_filter.svg" alt="Filter" class="w-3.5 h-3.5">
                    </button>

                    {{-- Add Button (pakai gambar kamu) --}}
                    <button
                        class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition"
                        title="Tambah">
                        <img src="/images/icons/sidebar_tambah.svg" alt="Tambah" class="w-3.5 h-3.5">
                    </button>
                </div>
            </div>

            {{-- Workspace List --}}
            <div class="mt-3 space-y-1">

                {{-- HQ
                <div>
                    <button @click="openHQ = !openHQ"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg transition">
                        <div class="flex items-center gap-2">
                            <img src="/images/icons/sidebar_hq.svg" alt="HQ" class="w-4 h-4">
                            <span>HQ</span>
                        </div>
                        <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': openHQ }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="openHQ" x-transition class="mt-1 space-y-0.5">
                        <a href="{{ url('/workspace/hq') }}"
                            class="flex items-center gap-2 px-6 py-1.5 text-sm rounded transition
                                {{ Request::is('workspace/hq*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                            <span>Mencari Cinta HQ</span>
                        </a>
                    </div>
                </div> --}}

                {{-- TIM --}}
                <div>
                    <button @click="openTim = !openTim"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg transition">
                        <div class="flex items-center gap-2">
                            <img src="/images/icons/sidebar_tim.svg" alt="Tim" class="w-4 h-4">
                            <span>Tim</span>
                        </div>
                        <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': openTim }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="openTim" x-transition class="mt-1 space-y-0.5">
                        <a href="{{ url('/workspace') }}"
                            class="flex items-center gap-2 px-6 py-1.5 text-sm rounded transition
                            {{ Request::is('workspace') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="w-1.5 h-1.5 bg-blue-600 rounded-full"></span>
                            <span>Koladi</span>
                        </a>
                        <a href="{{ url('/workspace/pelayanan') }}"
                            class="flex items-center gap-2 px-6 py-1.5 text-sm rounded transition
                            {{ Request::is('workspace/pelayanan*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                            <span>Div. Pelayanan</span>
                        </a>
                        <a href="{{ url('/workspace/creativ') }}"
                            class="flex items-center gap-2 px-6 py-1.5 text-sm rounded transition
                            {{ Request::is('workspace/creativ*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                            <span>Div. Creativ</span>
                        </a>
                    </div>
                </div>

                {{-- PROYEK --}}
                <div>
                    <button @click="openProyek = !openProyek"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg transition">
                        <div class="flex items-center gap-2">
                            <img src="/images/icons/sidebar_proyek.svg" alt="Proyek" class="w-4 h-4">
                            <span>Proyek</span>
                        </div>
                        <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': openProyek }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="openProyek" x-transition class="mt-1 space-y-0.5">
                        <a href="{{ url('/workspace/creativ-konten') }}"
                            class="flex items-center gap-2 px-6 py-1.5 text-sm rounded transition
                                  {{ Request::is('workspace/creativ-konten*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="w-1.5 h-1.5 bg-blue-700 rounded-full"></span>
                            <span>Creativ Konten</span>
                        </a>
                        <a href="{{ url('/workspace/pelayan-creativ') }}"
                            class="flex items-center gap-2 px-6 py-1.5 text-sm rounded transition
                                  {{ Request::is('workspace/pelayan-creativ*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                            <span>Pelayan Creativ</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    {{-- Overlay (blur background di layar kecil) --}}
    <div x-show="openSidebar && window.innerWidth < 768" x-transition.opacity @click="openSidebar = false"
        class="fixed inset-0 bg-black bg-opacity-30 z-10 md:hidden">
    </div>
</div>
