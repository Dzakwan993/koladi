@extends('layouts.app')

@section('title', 'Notulensi Rapat')

@section('content')
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .notulensi-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .notulensi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
            border-left-color: #2563eb;
        }

        .badge-comment {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            box-shadow: 0 2px 4px rgba(251, 191, 36, 0.3);
        }

        .badge-online {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
        }

        .badge-offline {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }

        .empty-state {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ✅ Style untuk Filter Button */
        .filter-btn,
        .filter-type-btn {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .filter-btn:hover,
        .filter-type-btn:hover {
            transform: scale(1.05);
            background: #e5e7eb !important;
        }

        /* ✅ Active state untuk kedua tipe filter */
        .filter-btn.active,
        .filter-type-btn.active {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%) !important;
            color: white !important;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);
            font-weight: 600;
        }

        .filter-btn.active:hover,
        .filter-type-btn.active:hover {
            transform: scale(1.05);
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%) !important;
        }
    </style>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header dengan Tombol Kembali -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="rounded-lg">
                        <img src="{{ asset('images/icons/notulen.gif') }}" alt="Notulen Icon" class="w-14 h-14">
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Notulensi Rapat</h1>
                        <p class="text-gray-600 text-sm mt-1">Catatan dan diskusi dari semua rapat</p>
                    </div>
                </div>

                <a href="{{ route('jadwal-umum') }}"
                    class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <!-- Filter & Stats -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex gap-2 flex-wrap">
                    <!-- ✅ Filter Waktu -->
                    <button onclick="filterNotulensi('all')"
                        class="filter-btn active px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">
                        Semua
                    </button>
                    <button onclick="filterNotulensi('today')"
                        class="filter-btn px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">
                        Hari Ini
                    </button>
                    <button onclick="filterNotulensi('week')"
                        class="filter-btn px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">
                        Minggu Ini
                    </button>
                    <button onclick="filterNotulensi('month')"
                        class="filter-btn px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">
                        Bulan Ini
                    </button>

                    <div class="w-px bg-gray-300 mx-2"></div>

                    <!-- ✅ Filter Online/Offline -->
                    <button onclick="filterByType('all-type')"
                        class="filter-type-btn active px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">
                        <i class="fas fa-globe"></i> Semua Tipe
                    </button>
                    <button onclick="filterByType('online')"
                        class="filter-type-btn px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">
                        <i class="fas fa-video"></i> Online
                    </button>
                    <button onclick="filterByType('offline')"
                        class="filter-type-btn px-4 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-medium">
                        <i class="fas fa-map-marker-alt"></i> Offline
                    </button>
                </div>

                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-file-alt"></i>
                    <span class="font-semibold" id="totalNotulensi">{{ count($notulensis) }}</span>
                    <span>Notulensi</span>
                </div>
            </div>
        </div>

        <!-- List Notulensi -->
        <div id="notulensiList" class="space-y-4">
            @forelse($notulensis as $notulensi)
                @php
                    $startDate = \Carbon\Carbon::parse($notulensi->start_datetime);
                    $endDate = \Carbon\Carbon::parse($notulensi->end_datetime);
                    $isMultiDay = $startDate->format('Y-m-d') !== $endDate->format('Y-m-d');
                @endphp

                <a href="{{ route('jadwal-umum.show', ['id' => $notulensi->id]) }}"
                    class="notulensi-card block bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition"
                    data-date="{{ $startDate->format('Y-m-d') }}"
                    data-is-online="{{ $notulensi->is_online_meeting ? '1' : '0' }}">

                    <div class="flex items-start justify-between gap-4">
                        <!-- Left: Date Badge -->
                        <div class="flex-shrink-0 text-center bg-blue-50 rounded-lg p-3 min-w-[80px]">
                            <div class="text-2xl font-bold text-blue-600">
                                {{ $startDate->format('d') }}
                            </div>
                            <div class="text-xs text-gray-600 uppercase">
                                {{ $startDate->translatedFormat('M Y') }}
                            </div>
                        </div>

                        <!-- Center: Content -->
                        <div class="flex-1">
                            <!-- ✅ Title + Badge Online/Offline -->
                            <div class="flex items-center gap-2 mb-2 flex-wrap">
                                @if ($notulensi->is_online_meeting)
                                    <span
                                        class="badge-online text-white text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                        <i class="fas fa-video text-xs"></i>
                                        Online
                                    </span>
                                @else
                                    <span
                                        class="badge-offline text-white text-xs px-2.5 py-1 rounded-full font-semibold inline-flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt text-xs"></i>
                                        Offline
                                    </span>
                                @endif
                                <h3 class="text-lg font-semibold text-gray-800">{{ $notulensi->title }}</h3>
                            </div>

                            <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                                <div class="flex items-center gap-1">
                                    <i class="far fa-clock"></i>
                                    <span>
                                        @if ($isMultiDay)
                                            {{ $startDate->format('d M H:i') }} - {{ $endDate->format('d M H:i') }}
                                        @else
                                            {{ $startDate->format('H:i') }} - {{ $endDate->format('H:i') }}
                                        @endif
                                    </span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <i class="fas fa-user"></i>
                                    <span>{{ $notulensi->creator->full_name }}</span>
                                </div>
                            </div>

                            <!-- Description Preview -->
                            @if ($notulensi->description)
                                <div class="text-sm text-gray-600 line-clamp-2 mb-2">
                                    {!! strip_tags($notulensi->description) !!}
                                </div>
                            @endif

                            <!-- Participants -->
                            <div class="flex items-center gap-2">
                                <div class="flex -space-x-2">
                                    @foreach ($notulensi->participants->take(4) as $participant)
                                        <img src="{{ $participant->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($participant->user->full_name) . '&background=3B82F6&color=fff&bold=true&size=128' }}"
                                            alt="{{ $participant->user->full_name }}"
                                            title="{{ $participant->user->full_name }}"
                                            class="w-8 h-8 rounded-full border-2 border-white object-cover">
                                    @endforeach
                                </div>
                                @if ($notulensi->participants->count() > 4)
                                    <span class="text-xs text-gray-500">
                                        +{{ $notulensi->participants->count() - 4 }} lainnya
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Comment Badge -->
                        <div class="flex-shrink-0">
                            <div
                                class="badge-comment text-white rounded-full w-14 h-14 flex flex-col items-center justify-center">
                                <div class="text-xl font-bold">{{ $notulensi->comments_count }}</div>
                                <div class="text-[9px] uppercase">komen</div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ Meeting Link atau Location -->
                    @if ($notulensi->is_online_meeting && $notulensi->meeting_link)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center gap-2 text-sm text-blue-600">
                                <i class="fas fa-link"></i>
                                <span class="truncate">{{ $notulensi->meeting_link }}</span>
                            </div>
                        </div>
                    @elseif(!$notulensi->is_online_meeting && $notulensi->location)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="fas fa-map-pin text-red-500"></i>
                                <span>{{ $notulensi->location }}</span>
                            </div>
                        </div>
                    @endif
                </a>
            @empty
                <div class="empty-state text-center py-16">
                    <div class="inline-block bg-gray-100 rounded-full p-6 mb-4">
                        <i class="fas fa-file-alt text-5xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Belum Ada Notulensi</h3>
                    <p class="text-gray-500 mb-6">
                        Notulensi akan muncul setelah rapat memiliki komentar
                    </p>
                    <a href="{{ route('jadwal-umum') }}"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition">
                        <i class="fas fa-calendar"></i>
                        <span>Lihat Jadwal</span>
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Empty State for Filter -->
        <div id="emptyFilterState" class="empty-state text-center py-16" style="display: none;">
            <div class="inline-block bg-gray-100 rounded-full p-6 mb-4">
                <i class="fas fa-search text-5xl text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak Ada Hasil</h3>
            <p class="text-gray-500 mb-6">Tidak ada notulensi untuk filter yang dipilih</p>
        </div>
    </div>

    <script>
        let currentDateFilter = 'all';
        let currentTypeFilter = 'all-type';

        // ✅ Filter by Date
        function filterNotulensi(type) {
            currentDateFilter = type;
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            applyFilters();
        }

        // ✅ Filter by Type (Online/Offline)
        function filterByType(type) {
            currentTypeFilter = type;
            const buttons = document.querySelectorAll('.filter-type-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            applyFilters();
        }

        // ✅ Apply Combined Filters
        function applyFilters() {
            const cards = document.querySelectorAll('.notulensi-card');
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());

            const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);

            let visibleCount = 0;

            cards.forEach(card => {
                const cardDate = new Date(card.dataset.date);
                cardDate.setHours(0, 0, 0, 0);
                const isOnline = card.dataset.isOnline === '1';

                let showByDate = false;
                let showByType = false;

                // Date Filter
                switch (currentDateFilter) {
                    case 'all':
                        showByDate = true;
                        break;
                    case 'today':
                        showByDate = cardDate.getTime() === today.getTime();
                        break;
                    case 'week':
                        showByDate = cardDate >= weekStart && cardDate <= today;
                        break;
                    case 'month':
                        showByDate = cardDate >= monthStart && cardDate <= today;
                        break;
                }

                // Type Filter
                switch (currentTypeFilter) {
                    case 'all-type':
                        showByType = true;
                        break;
                    case 'online':
                        showByType = isOnline;
                        break;
                    case 'offline':
                        showByType = !isOnline;
                        break;
                }

                // Show card only if both filters pass
                if (showByDate && showByType) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const notulensiList = document.getElementById('notulensiList');
            const emptyState = document.getElementById('emptyFilterState');
            const totalCount = document.getElementById('totalNotulensi');

            if (visibleCount === 0) {
                notulensiList.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                notulensiList.style.display = 'block';
                emptyState.style.display = 'none';
            }

            totalCount.textContent = visibleCount;
        }
    </script>
@endsection
