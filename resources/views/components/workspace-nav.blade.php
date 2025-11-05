@php
    $active = $active ?? '';
@endphp

<div class="flex flex-wrap items-center justify-between px-4 sm:px-6 md:px-8 py-3 md:py-4 border-b bg-white gap-3">

    <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-gray-600">

        {{-- Link ini kembali ke halaman list (Gambar 1) --}}
        <a href="{{ route('kelola-workspace') }}" class="text-gray-500 hover:text-gray-800">Ruang Kerja</a>

        <span class="text-gray-400">›</span>

        {{-- TAMBAHKAN INI: Menampilkan nama workspace saat ini --}}
        {{-- Kita asumsikan propertinya adalah 'name'. Sesuaikan jika salah. --}}
        <span class="text-gray-800 font-semibold">{{ $workspace->name }}</span>

        {{-- Blok ini akan menampilkan '› Chat' atau '› Tugas' jika $active ada --}}
        @if ($active)
            <span class="text-gray-400">›</span>
            <span class="text-gray-800 font-semibold capitalize">{{ $active }}</span>
        @endif
    </div>

    {{-- CSS untuk ikon --}}
    <style>
        .nav-icon {
            filter: grayscale(100%) brightness(0%) invert(40%) sepia(0%) saturate(0%) hue-rotate(0deg);
            transition: filter 0.2s ease;
        }

        a:hover .nav-icon,
        a.active .nav-icon {
            filter: invert(37%) sepia(89%) saturate(7473%) hue-rotate(203deg) brightness(97%) contrast(101%);
        }

        /* Tampilkan teks di layar ≥992px (lg) dan ≥1200px (xl), sembunyikan di bawah 992px */
        @media (max-width: 991.98px) {
            .nav-text {
                display: none !important;
            }
        }
    </style>

    {{-- Navigation Tabs --}}
    <div
        class="flex flex-wrap items-center justify-start sm:justify-end gap-3 sm:gap-4 text-sm md:text-base w-full sm:w-auto">

        <a href="{{ url('/workspace/insight') }}"
            class="flex items-center gap-2 {{ $active == 'insight' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
            <img src="{{ asset('images/icons/workspace_insight.svg') }}" alt="Insight Icon"
                class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
            <span class="nav-text">Insight</span>
        </a>

        <a href="{{ url('/kanban-tugas') }}"
            class="flex items-center gap-2 {{ $active == 'tugas' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
            <img src="{{ asset('images/icons/workspace_tugas.svg') }}" alt="Tugas Icon"
                class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
            <span class="nav-text">Tugas</span>
        </a>

        <a href="{{ route('chat', $workspace->id) }}"
            class="flex items-center gap-2 {{ $active == 'chat' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
            <img src="{{ asset('images/icons/workspace_chat.svg') }}" alt="Chat Icon"
                class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
            <span class="nav-text">Chat</span>
        </a>

        <a href="{{ url('/dokumen-dan-file') }}"
            class="flex items-center gap-2 {{ $active == 'dokumen' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
            <img src="{{ asset('images/icons/workspace_dokumen&file.svg') }}" alt="Dokumen Icon"
                class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
            <span class="nav-text">Dokumen</span>
        </a>

        <a href="{{ url('/workspace/jadwal') }}"
            class="flex items-center gap-2 {{ $active == 'jadwal' ? 'active text-blue-600 font-semibold border-b-2 border-blue-600 pb-1' : 'text-gray-600 hover:text-blue-600' }}">
            <img src="{{ asset('images/icons/workspace_kalender.svg') }}" alt="Jadwal Icon"
                class="nav-icon w-4 h-4 sm:w-5 sm:h-5">
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
