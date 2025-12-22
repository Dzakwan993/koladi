<style>
    [x-cloak] {
        display: none !important;
    }

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
        class="w-64 bg-white shadow-sm border-r border-gray-200 h-screen transition-all duration-300 fixed md:relative"
        x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
        {{-- Logo --}}
        <div class="h-16 flex items-center justify-center px-4 border-b border-gray-200">
            <img src="{{ asset('images/logo-koladi.svg') }}" class="h-9 object-cover object-center" alt="Logo Koladi">
        </div>

        {{-- ✅ DEFINISI PHP VARIABLES DULU --}}
        @php
            $user = Auth::user();
            $activeCompanyId = session('active_company_id');

            // Ambil workspace berdasarkan role user
            if ($activeCompanyId) {
                $userCompany = $user->userCompanies()->where('company_id', $activeCompanyId)->with('role')->first();
                $userRole = $userCompany?->role?->name ?? 'Member';

                // Jika SuperAdmin/Admin/Manager, tampilkan semua workspace
                if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
                    $timWorkspaces = \App\Models\Workspace::where('company_id', $activeCompanyId)
                        ->where('type', 'Tim')
                        ->orderBy('name')
                        ->get();

                    $proyekWorkspaces = \App\Models\Workspace::where('company_id', $activeCompanyId)
                        ->where('type', 'Proyek')
                        ->orderBy('name')
                        ->get();
                } else {
                    // Jika Member, hanya tampilkan workspace yang diikuti
                    $timWorkspaces = \App\Models\Workspace::where('company_id', $activeCompanyId)
                        ->where('type', 'Tim')
                        ->whereHas('userWorkspaces', function ($query) use ($user) {
                            $query->where('user_id', $user->id)->where('status_active', true);
                        })
                        ->orderBy('name')
                        ->get();

                    $proyekWorkspaces = \App\Models\Workspace::where('company_id', $activeCompanyId)
                        ->where('type', 'Proyek')
                        ->whereHas('userWorkspaces', function ($query) use ($user) {
                            $query->where('user_id', $user->id)->where('status_active', true);
                        })
                        ->orderBy('name')
                        ->get();
                }
            } else {
                $timWorkspaces = collect();
                $proyekWorkspaces = collect();
            }

            // Warna dot untuk workspace
            $colors = ['blue-600', 'green-500', 'purple-500', 'yellow-500', 'red-500', 'pink-500', 'indigo-500'];
        @endphp

        {{-- Navigation --}}
        {{-- SESUDAH (BENAR) --}}
        <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto" x-data="workspaceFilter()" x-init="init(@js($timWorkspaces->toArray()), @js($proyekWorkspaces->toArray()))">

            {{-- Dashboard --}}
            <a href="{{ url('/dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                      {{ Request::is('dashboard*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="{{ asset('images/icons/sidebar_dashboard.svg') }}" alt="Dashboard"
                    class="w-5 h-5 {{ Request::is('dashboard*') ? 'filter-blue' : '' }}">
                <span class="text-sm">Dashboard</span>
            </a>

            {{-- Ruang Kerja --}}
            <a href="{{ url('/kelola-workspace') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                      {{ Request::is('kelola-workspace*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="{{ asset('images/icons/sidebar_ruang-kerja.svg') }}" alt="Ruang Kerja"
                    class="w-5 h-5 {{ Request::is('kelola-workspace*') ? 'filter-blue' : '' }}">
                <span class="text-sm">Ruang Kerja</span>
            </a>

            @php
                $company_id = session('active_company_id');
            @endphp

            @if ($company_id)
                <a href="{{ route('pengumuman-perusahaan.index', ['company_id' => $company_id]) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
   {{ Request::is('companies/*/pengumuman-perusahaan*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                    <img src="{{ Request::is('companies/*/pengumuman-perusahaan*') ? asset('images/icons/workspace_pengumuman1.svg') : asset('images/icons/workspace_pengumuman.svg') }}"
                        class="w-5 h-5">
                    <span class="text-sm">Pengumuman</span>
                </a>
            @endif

            {{-- Chat Perusahaan --}}
            @auth
                @php
                    $activeCompany = $activeCompanyId ? \App\Models\Company::find($activeCompanyId) : null;
                @endphp

                @if ($activeCompany)
                    <a href="{{ route('company.chat', $activeCompany) }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                  {{ Request::is('company/*/chat*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                        <img src="{{ asset('images/icons/sidebar_chat.svg') }}" alt="Chat Perusahaan"
                            class="w-5 h-5 {{ Request::is('company/*/chat*') ? 'filter-blue' : '' }}">
                        <span class="text-sm">Chat</span>
                    </a>
                @else
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 cursor-not-allowed"
                        title="Pilih perusahaan terlebih dahulu">
                        <img src="{{ asset('images/icons/sidebar_chat.svg') }}" alt="Chat Perusahaan" class="w-5 h-5">
                        <span class="text-sm">Chat</span>
                    </div>
                @endif
            @endauth

            {{-- Jadwal Umum --}}
            <a href="{{ route('jadwal-umum') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                {{ Request::is('jadwal-umum*') || Request::is('notulensi-umum*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="{{ asset('images/icons/workspace_kalender.svg') }}" alt="Jadwal"
                    class="w-5 h-5 {{ Request::is('jadwal-umum*') || Request::is('notulensi-umum*') ? 'filter-blue' : '' }}">
                <span class="text-sm">Jadwal</span>
            </a>

            {{-- Dokumen --}}
            <a href="{{ route('company-documents.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
            {{ Request::is('company-documents*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}" alt="Dokumen"
                    class="w-5 h-5 {{ Request::is('company-documents*') ? 'filter-blue' : '' }}">
                <span class="text-sm">Dokumen</span>
            </a>

            {{-- Laporan Kinerja --}}
            <a href="{{ url('/statistik') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                      {{ Request::is('statistik*') ? 'bg-[#e9effd] text-[#225ad6] font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="{{ asset('images/icons/sidebar_laporan-kinerja.svg') }}" alt="Laporan Kinerja"
                    class="w-5 h-5 {{ Request::is('statistik*') ? 'filter-blue' : '' }}">
                <span class="text-sm">Laporan Kinerja</span>
            </a>

            {{-- Search & Actions --}}
            <div class="pt-3 mt-3 border-t border-gray-200">
                <div class="flex items-center gap-1.5 relative">
                    {{-- Search Bar --}}
                    <div
                        class="flex items-center gap-1.5 px-2 py-1.5 rounded-md bg-gray-50 text-gray-400 text-xs flex-1 hover:bg-gray-100 transition">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" x-model="searchQuery" placeholder="Cari Ruang Kerja"
                            class="flex-1 bg-transparent outline-none border-none focus:ring-0 text-gray-700 text-[11px] p-0">
                    </div>

                    {{-- Filter Button --}}
                    <div class="relative flex-shrink-0">
                        <button @click="showFilterMenu = !showFilterMenu"
                            class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition"
                            title="Filter">
                            <img src="{{ asset('images/icons/sidebar_filter.svg') }}" alt="Filter"
                                class="w-3.5 h-3.5">
                        </button>

                        {{-- Filter Dropdown --}}
                        <div x-show="showFilterMenu" x-cloak @click.away="showFilterMenu = false" x-transition
                            class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <button @click="sortOrder = 'asc'; showFilterMenu = false"
                                class="w-full px-3 py-2 text-left text-xs hover:bg-gray-50 transition flex items-center justify-between"
                                :class="{ 'bg-blue-50 text-blue-600 font-medium': sortOrder === 'asc' }">
                                <span>A → Z</span>
                                <svg x-show="sortOrder === 'asc'" class="w-3 h-3" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button @click="sortOrder = 'desc'; showFilterMenu = false"
                                class="w-full px-3 py-2 text-left text-xs hover:bg-gray-50 transition flex items-center justify-between"
                                :class="{ 'bg-blue-50 text-blue-600 font-medium': sortOrder === 'desc' }">
                                <span>Z → A</span>
                                <svg x-show="sortOrder === 'desc'" class="w-3 h-3" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Add Button --}}
                    <a href="{{ url('/kelola-workspace') }}"
                        class="w-7 h-7 flex-shrink-0 flex items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition"
                        title="Tambah Workspace">
                        <img src="{{ asset('images/icons/sidebar_tambah.svg') }}" alt="Tambah"
                            class="w-3.5 h-3.5 pointer-events-none">
                    </a>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                {{-- TIM --}}
                <div>
                    <button @click="openTim = !openTim"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg transition">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/icons/sidebar_tim.svg') }}" alt="Tim" class="w-4 h-4">
                            <span>Tim</span>
                        </div>
                        <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': openTim }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="openTim" x-transition class="mt-1 space-y-0.5">
                        <template x-if="filteredTimWorkspaces.length > 0">
                            <div>
                                <template x-for="(workspace, index) in filteredTimWorkspaces" :key="workspace.id">
                                    <a :href="`{{ url('workspace') }}/${workspace.id}`"
                                        class="flex items-center gap-2 px-6 py-1.5 text-sm rounded transition text-gray-600 hover:bg-gray-50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                        <span class="truncate" x-text="workspace.name"></span>
                                    </a>
                                </template>
                            </div>
                        </template>
                        <template x-if="filteredTimWorkspaces.length === 0">
                            <div class="px-6 py-2">
                                <p class="text-xs text-gray-400 text-center"
                                    x-text="searchQuery.length >= 2 ? 'Tidak ada hasil' : 'Belum ada workspace Tim'">
                                </p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- PROYEK --}}
                <div>
                    <button @click="openProyek = !openProyek"
                        class="w-full flex items-center justify-between px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider hover:bg-gray-50 rounded-lg transition">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/icons/sidebar_proyek.svg') }}" alt="Proyek"
                                class="w-4 h-4">
                            <span>Proyek</span>
                        </div>
                        <svg class="w-3 h-3 transition-transform" :class="{ 'rotate-90': openProyek }" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="openProyek" x-transition class="mt-1 space-y-0.5">
                        <template x-if="filteredProyekWorkspaces.length > 0">
                            <div>
                                <template x-for="(workspace, index) in filteredProyekWorkspaces"
                                    :key="workspace.id">
                                    <a :href="`{{ url('workspace') }}/${workspace.id}`"
                                        class="flex items-center gap-2 px-6 py-1.5 text-sm rounded transition text-gray-600 hover:bg-gray-50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-600"></span>
                                        <span class="truncate" x-text="workspace.name"></span>
                                    </a>
                                </template>
                            </div>
                        </template>
                        <template x-if="filteredProyekWorkspaces.length === 0">
                            <div class="px-6 py-2">
                                <p class="text-xs text-gray-400 text-center"
                                    x-text="searchQuery.length >= 2 ? 'Tidak ada hasil' : 'Belum ada workspace Proyek'">
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    {{-- Overlay --}}
    <div x-show="openSidebar && window.innerWidth < 768" x-transition.opacity @click="openSidebar = false"
        class="fixed inset-0 bg-black bg-opacity-30 z-10 md:hidden">
    </div>
</div>


<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('workspaceFilter', () => ({
            openTim: true,
            openProyek: true,
            searchQuery: '',
            sortOrder: 'asc',
            showFilterMenu: false,
            timWorkspaces: [],
            proyekWorkspaces: [],

            init(tim, proyek) {
                this.timWorkspaces = tim || [];
                this.proyekWorkspaces = proyek || [];
            },

            get filteredTimWorkspaces() {
                let workspaces = [...this.timWorkspaces];

                if (this.searchQuery.length > 0) {
                    workspaces = workspaces.filter(w =>
                        w.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                }

                return workspaces.sort((a, b) => {
                    return this.sortOrder === 'asc' 
                        ? a.name.localeCompare(b.name)
                        : b.name.localeCompare(a.name);
                });
            },

            get filteredProyekWorkspaces() {
                let workspaces = [...this.proyekWorkspaces];

                if (this.searchQuery.length > 0) {
                    workspaces = workspaces.filter(w =>
                        w.name.toLowerCase().includes(this.searchQuery.toLowerCase())
                    );
                }

                return workspaces.sort((a, b) => {
                    return this.sortOrder === 'asc' 
                        ? a.name.localeCompare(b.name)
                        : b.name.localeCompare(a.name);
                });
            }
        }));
    });
</script>