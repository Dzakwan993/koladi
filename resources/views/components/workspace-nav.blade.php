@php
    $active = $active ?? '';
    // ✅ FIX: Ambil workspace ID dari session (bukan dari variable yang di-pass)
    $currentWorkspaceId = session('current_workspace_id');
    $currentWorkspaceName = session('current_workspace_name', 'Workspace');

    // ✅ NEW: Mapping untuk sub-menu jadwal
    $jadwalSubPages = [
        'buat-jadwal' => 'Buat Jadwal',
        'notulensi' => 'Notulensi',
        'detail-jadwal' => 'Detail Jadwal',
    ];
@endphp

<div class="flex flex-wrap items-center justify-between px-4 sm:px-6 md:px-8 py-3 md:py-4 border-b bg-white gap-3">

    <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-gray-600">
        {{-- Link kembali ke halaman list --}}
        <a href="{{ route('kelola-workspace') }}" class="text-gray-500 hover:text-gray-800">Ruang Kerja</a>

        <span class="text-gray-400">›</span>

        {{-- MODIFIKASI INI: Cek jika $workspace tersedia --}}
        @if (isset($workspace) && $workspace)
            <span class="text-gray-800 font-semibold">{{ $workspace->name }}</span>

            @if ($active)
                <span class="text-gray-400">›</span>

                {{-- ✅ ENHANCEMENT: Cek apakah ini halaman Jadwal dengan sub-menu --}}
                @if ($active == 'jadwal')
                    <a href="{{ route('jadwal', ['workspaceId' => $currentWorkspaceId]) }}"
                        class="text-gray-800 font-semibold hover:text-blue-600">Jadwal</a>

                    {{-- Cek apakah ada sub-page jadwal --}}
                    @if (isset($jadwalSubPage) && isset($jadwalSubPages[$jadwalSubPage]))
                        <span class="text-gray-400">›</span>
                        <span class="text-gray-800 font-semibold">{{ $jadwalSubPages[$jadwalSubPage] }}</span>
                    @endif
                @else
                    <span class="text-gray-800 font-semibold capitalize">{{ $active }}</span>
                @endif
            @endif
        @else
            {{-- Tampilkan judul default jika tidak ada workspace --}}
            <span class="text-gray-800 font-semibold">Dokumen & File</span>
        @endif
    </div>

    {{-- Navigation Tabs --}}
    <div
        class="flex flex-wrap items-center justify-start sm:justify-end gap-3 sm:gap-4 text-sm md:text-base w-full sm:w-auto">

        <a href="{{ url('/kanban-tugas') }}"
            class="flex items-center gap-2 {{ $active == 'tugas' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
            <img src="{{ asset('images/icons/workspace_tugas.svg') }}" alt="Tugas Icon"
                class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
            <span class="nav-text">Tugas</span>
        </a>

        {{-- MODIFIKASI: Cek jika workspace tersedia untuk link chat --}}
        @if (isset($workspace) && $workspace)
            <a href="{{ route('chat', $workspace->id) }}"
                class="flex items-center gap-2 {{ $active == 'chat' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
                <img src="{{ asset('images/icons/' . ($active == 'chat' ? 'workspace_chat1.svg' : 'workspace_chat.svg')) }}"
                    alt="Chat Icon" class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
                <span class="nav-text">Chat</span>
            </a>
        @else
            <a href="{{ url('/chat') }}"
                class="flex items-center gap-2 {{ $active == 'chat' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
                <img src="{{ asset('images/icons/' . ($active == 'chat' ? 'workspace_chat1.svg' : 'workspace_chat.svg')) }}"
                    alt="Chat Icon" class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
                <span class="nav-text">Chat</span>
            </a>
        @endif

        <a href="{{ url('/dokumen-dan-file') }}"
            class="flex items-center gap-2 {{ $active == 'dokumen' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
            <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}" alt="Dokumen Icon"
                class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
            <span class="nav-text">Dokumen</span>
        </a>

        {{-- ✅ FIX: URL Jadwal menggunakan route dengan workspaceId dari session --}}
        <a href="{{ $currentWorkspaceId ? route('jadwal', ['workspaceId' => $currentWorkspaceId]) : '#' }}"
            class="flex items-center gap-2 {{ $active == 'jadwal' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }} {{ !$currentWorkspaceId ? 'opacity-50 cursor-not-allowed' : '' }}">
            <img src="{{ asset('images/icons/' . ($active == 'jadwal' ? 'workspace_kalender1.svg' : 'workspace_kalender.svg')) }}"
                alt="Jadwal Icon" class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
            <span class="nav-text">Jadwal</span>
        </a>

        <a href="{{ url('/workspace/pengumuman') }}"
            class="flex items-center gap-2 {{ $active == 'pengumuman' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
            <img src="{{ asset('images/icons/workspace_pengumuman.svg') }}" alt="Pengumuman Icon"
                class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
            <span class="nav-text">Pengumuman</span>
        </a>
    </div>
</div>
