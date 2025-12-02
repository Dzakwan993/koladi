@extends('layouts.app')

@section('title', 'Notulensi Rapat')

@section('content')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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

        .filter-btn {
            transition: all 0.2s ease;
        }

        .filter-btn:hover {
            transform: scale(1.05);
        }

        .filter-btn.active {
            background: #2563eb !important;
            color: white !important;
        }
    </style>

    <div class="bg-[#f3f6fc] min-h-screen">
        @include('components.workspace-nav', [
            'active' => 'jadwal',
            'workspaceId' => $workspaceId
        ])

        <div class="container mx-auto px-4 py-8 max-w-6xl">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class=rounded-lg">
                            <img src="{{ asset('images/icons/notulen.gif') }}" alt="Notulen Icon" class="w-14 h-14">
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Notulensi Rapat</h1>
                            <p class="text-gray-600 text-sm mt-1">Catatan dan diskusi dari rapat online</p>
                        </div>
                    </div>

                    <a href="{{ route('jadwal', ['workspaceId' => $workspaceId]) }}"
                        class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                </div>
            </div>

            <!-- Filter & Stats -->
            <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex gap-2">
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
                    </div>

                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <i class="fas fa-file-alt"></i>
                        <span class="font-semibold" id="totalNotulensi">{{ count($notulensis) }}</span>
                        <span>Notulensi</span>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="text-center py-12" style="display: none;">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Memuat notulensi...</p>
            </div>

            <!-- List Notulensi -->
            <div id="notulensiList" class="space-y-4">
                @forelse($notulensis as $notulensi)
                    @php
                        $startDate = \Carbon\Carbon::parse($notulensi->start_datetime);
                        $endDate = \Carbon\Carbon::parse($notulensi->end_datetime);
                        $isMultiDay = $startDate->format('Y-m-d') !== $endDate->format('Y-m-d');
                    @endphp

                    <a href="{{ route('calendar.show', ['workspaceId' => $workspaceId, 'id' => $notulensi->id]) }}"
                        class="notulensi-card block bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition"
                        data-date="{{ $startDate->format('Y-m-d') }}">

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
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fas fa-video text-blue-600"></i>
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
                                @if($notulensi->description)
                                    <div class="text-sm text-gray-600 line-clamp-2 mb-2">
                                        {!! strip_tags($notulensi->description) !!}
                                    </div>
                                @endif

                                <!-- Participants -->
                                <div class="flex items-center gap-2">
                                    <div class="flex -space-x-2">
                                        @foreach($notulensi->participants->take(4) as $participant)
                                            <img src="{{ $participant->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($participant->user->full_name) . '&background=3B82F6&color=fff&bold=true&size=128' }}"
                                                alt="{{ $participant->user->full_name }}"
                                                title="{{ $participant->user->full_name }}"
                                                class="w-8 h-8 rounded-full border-2 border-white object-cover">
                                        @endforeach
                                    </div>
                                    @if($notulensi->participants->count() > 4)
                                        <span class="text-xs text-gray-500">
                                            +{{ $notulensi->participants->count() - 4 }} lainnya
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Right: Comment Badge -->
                            <div class="flex-shrink-0">
                                <div class="badge-comment text-white rounded-full w-14 h-14 flex flex-col items-center justify-center">
                                    <div class="text-xl font-bold">{{ $notulensi->comments_count }}</div>
                                    <div class="text-[9px] uppercase">komen</div>
                                </div>
                            </div>
                        </div>

                        <!-- Meeting Link -->
                        @if($notulensi->meeting_link)
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <div class="flex items-center gap-2 text-sm text-blue-600">
                                    <i class="fas fa-link"></i>
                                    <span class="truncate">{{ $notulensi->meeting_link }}</span>
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
                            Notulensi akan muncul setelah rapat online memiliki komentar
                        </p>
                        <a href="{{ route('jadwal', ['workspaceId' => $workspaceId]) }}"
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
    </div>

    <script>
        function filterNotulensi(type) {
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

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

                let show = false;

                switch (type) {
                    case 'all':
                        show = true;
                        break;
                    case 'today':
                        show = cardDate.getTime() === today.getTime();
                        break;
                    case 'week':
                        show = cardDate >= weekStart && cardDate <= today;
                        break;
                    case 'month':
                        show = cardDate >= monthStart && cardDate <= today;
                        break;
                }

                if (show) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show/hide empty state
            const notulensiList = document.getElementById('notulensiList');
            const emptyState = document.getElementById('emptyFilterState');
            const totalCount = document.getElementById('totalNotulensi');

            if (visibleCount === 0 && type !== 'all') {
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
