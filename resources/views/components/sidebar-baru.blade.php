{{-- resources/views/components/sidebar-baru.blade.php --}}

<style>
    [x-cloak] {
        display: none !important;
    }

    .filter-blue {
        filter: brightness(0) saturate(100%) invert(30%) sepia(91%) saturate(1539%) hue-rotate(213deg) brightness(90%) contrast(96%);
    }

    .sidebar-icon {
        transition: all 0.2s ease;
    }

    .sidebar-link:hover .sidebar-icon {
        filter: brightness(0) saturate(100%) invert(30%) sepia(91%) saturate(1539%) hue-rotate(213deg) brightness(90%) contrast(96%);
    }

    .sidebar-link.active .sidebar-icon {
        filter: brightness(0) saturate(100%) invert(30%) sepia(91%) saturate(1539%) hue-rotate(213deg) brightness(90%) contrast(96%);
    }
</style>

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
        class="w-64 bg-white shadow-sm border-r border-gray-200 h-screen transition-all duration-300 fixed md:relative z-20"
        x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-300"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">

        {{-- Logo --}}
        <div class="h-16 flex items-center justify-center px-4 border-b border-gray-200">
            <img src="{{ asset('images/logo-koladi.svg') }}" class="h-9 object-cover object-center" alt="Logo Koladi">
        </div>

        {{-- ✅ AMBIL DATA WORKSPACE AKTIF --}}
        @php
            $workspaceParam = request()->route('workspace');

            if ($workspaceParam instanceof \App\Models\Workspace) {
                $currentWorkspace = $workspaceParam;
            } elseif ($workspaceParam) {
                $currentWorkspace = \App\Models\Workspace::find($workspaceParam);
            } else {
                $currentWorkspace = null;
            }
        @endphp

        {{-- Navigation --}}
        <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto">

            {{-- ✅ 1. Dashboard --}}
            <a href="{{ route('dashboard') }}"
                class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                      {{ Request::is('dashboard*') ? 'bg-[#e9effd] text-[#225ad6] font-medium active' : 'text-gray-600 hover:bg-gray-50' }}">
                <img src="{{ asset('images/icons/sidebar_dashboard.svg') }}" alt="Dashboard"
                    class="w-5 h-5 sidebar-icon">
                <span class="text-sm">Dashboard</span>
            </a>

            {{-- ✅ 2. Semua Tugas --}}
            @if ($currentWorkspace)
                <a href="{{ route('kanban-tugas', $currentWorkspace->id) }}"
                    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                          {{ Request::is('kanban-tugas/*') ? 'bg-[#e9effd] text-[#225ad6] font-medium active' : 'text-gray-600 hover:bg-gray-50' }}">
                    <img src="{{ asset('images/icons/workspace_tugas.svg') }}" alt="Semua Tugas"
                        class="w-5 h-5 sidebar-icon">
                    <span class="text-sm">Semua Tugas</span>
                </a>
            @else
                <div class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 cursor-not-allowed"
                    title="Pilih workspace terlebih dahulu">
                    <img src="{{ asset('images/icons/workspace_tugas.svg') }}" alt="Semua Tugas"
                        class="w-5 h-5 opacity-50">
                    <span class="text-sm">Semua Tugas</span>
                </div>
            @endif

            {{-- ✅ 3. Chat --}}
            @if ($currentWorkspace)
                <a href="{{ route('chat', $currentWorkspace->id) }}"
                    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                          {{ Request::is('chat/*') ? 'bg-[#e9effd] text-[#225ad6] font-medium active' : 'text-gray-600 hover:bg-gray-50' }}">
                    <img src="{{ asset('images/icons/workspace_chat.svg') }}" alt="Chat"
                        class="w-5 h-5 sidebar-icon">
                    <span class="text-sm">Chat</span>
                </a>
            @else
                <div class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 cursor-not-allowed"
                    title="Pilih workspace terlebih dahulu">
                    <img src="{{ asset('images/icons/workspace_chat.svg') }}" alt="Chat"
                        class="w-5 h-5 opacity-50">
                    <span class="text-sm">Chat</span>
                </div>
            @endif

            {{-- ✅ 4. Pengumuman --}}
            @if ($currentWorkspace)
                <a href="{{ route('workspace.pengumuman', ['workspace' => $currentWorkspace->id]) }}"
                    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                          {{ Request::is('workspace/*/pengumuman*') ? 'bg-[#e9effd] text-[#225ad6] font-medium active' : 'text-gray-600 hover:bg-gray-50' }}">
                    <img src="{{ asset('images/icons/workspace_pengumuman.svg') }}" alt="Pengumuman"
                        class="w-5 h-5 sidebar-icon">
                    <span class="text-sm">Pengumuman</span>
                </a>
            @else
                <div class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 cursor-not-allowed"
                    title="Pilih workspace terlebih dahulu">
                    <img src="{{ asset('images/icons/workspace_pengumuman.svg') }}" alt="Pengumuman"
                        class="w-5 h-5 opacity-50">
                    <span class="text-sm">Pengumuman</span>
                </div>
            @endif

            {{-- ✅ 5. Mind Map --}}
            @if ($currentWorkspace)
                <a href="{{ url('/workspace/' . $currentWorkspace->id . '/mindmap') }}"
                    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                          {{ Request::is('workspace/*/mindmap*') ? 'bg-[#e9effd] text-[#225ad6] font-medium active' : 'text-gray-600 hover:bg-gray-50' }}">
                    <img src="{{ asset('images/icons/workspace_insight.svg') }}" alt="Mind Map"
                        class="w-5 h-5 sidebar-icon">
                    <span class="text-sm">Mind Map</span>
                </a>
            @else
                <div class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 cursor-not-allowed"
                    title="Pilih workspace terlebih dahulu">
                    <img src="{{ asset('images/icons/workspace_insight.svg') }}" alt="Mind Map"
                        class="w-5 h-5 opacity-50">
                    <span class="text-sm">Mind Map</span>
                </div>
            @endif

            {{-- ✅ 6. Dokumen --}}
            @if ($currentWorkspace)
                <a href="{{ route('dokumen-dan-file', $currentWorkspace->id) }}"
                    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                          {{ Request::is('dokumen-dan-file/*') ? 'bg-[#e9effd] text-[#225ad6] font-medium active' : 'text-gray-600 hover:bg-gray-50' }}">
                    <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}" alt="Dokumen"
                        class="w-5 h-5 sidebar-icon">
                    <span class="text-sm">Dokumen</span>
                </a>
            @else
                <div class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 cursor-not-allowed"
                    title="Pilih workspace terlebih dahulu">
                    <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}" alt="Dokumen"
                        class="w-5 h-5 opacity-50">
                    <span class="text-sm">Dokumen</span>
                </div>
            @endif

            {{-- ✅ 7. AI Brief --}}
            @if ($currentWorkspace)
                <a href="{{ route('upload-brief', $currentWorkspace->id) }}"
                    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition
            {{ Request::is('workspace/*/upload-brief*') || Request::is('workspace/*/ai-brief*')
                ? 'bg-[#e9effd] text-[#225ad6] font-medium active'
                : 'text-gray-600 hover:bg-gray-50' }}">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                        stroke="currentColor" class="w-5 h-5 sidebar-icon">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
                    </svg>

                    <span class="text-sm">AI Brief</span>
                </a>
            @else
                <div class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 cursor-not-allowed"
                    title="Pilih workspace terlebih dahulu">

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                        stroke="currentColor" class="w-5 h-5 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z" />
                    </svg>

                    <span class="text-sm">AI Brief</span>
                </div>
            @endif

            {{-- ✅ 8. Log Aktivitas --}}
            @if ($currentWorkspace)
                <a href="{{ route('activity-log', $currentWorkspace->id) }}"
                    class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg transition
            {{ Request::is('activity-log/*') ? 'bg-[#e9effd] text-[#225ad6] font-medium active' : 'text-gray-600 hover:bg-gray-50' }}">

                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <span class="text-sm">Log Aktivitas</span>
                </a>
            @else
                <div class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-400 cursor-not-allowed"
                    title="Pilih workspace terlebih dahulu">

                    <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>

                    <span class="text-sm">Log Aktivitas</span>
                </div>
            @endif

        </nav>

        {{-- ✅ FOOTER SIDEBAR (Opsional) --}}
        <div class="px-3 py-3 border-t border-gray-200">
            <p class="text-[10px] text-gray-400 text-center">Koladi v1.0</p>
        </div>
    </div>

    {{-- Overlay untuk mobile --}}
    <div x-show="openSidebar && window.innerWidth < 768" x-transition.opacity @click="openSidebar = false"
        class="fixed inset-0 bg-black bg-opacity-30 z-10 md:hidden">
    </div>
</div>
