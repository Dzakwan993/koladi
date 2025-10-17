@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="px-3 pb-3 sm:p-6 lg:p-8 h-screen overflow-hidden">
        <div class="max-w-7xl mx-auto h-full flex flex-col">
            {{-- Header - Fixed Height --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-gray-200 pb-3 mb-3.5 flex-shrink-0">
                <div class="mx-3">
                    <h1 class="text-xl sm:text-xl font-bold text-gray-900">Selamat datang, Sahroni</h1>
                    <p class="text-sm text-gray-600 mt-0.5">Silahkan kelola tugas anda.</p>
                </div>
                <button
                    class="mx-4 text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-2.5 rounded-lg font-medium transition flex items-center justify-center gap-1 shadow-sm">
                    <img src="{{ asset('images/icons/Plus.svg') }}" alt="Schedule" class="w-5 h-5" />
                    Tambah anggota
                </button>
            </div>

            {{-- Main Grid - Flex 1 to fill remaining space --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 flex-1 min-h-0">
                {{-- Left Column - Tasks --}}
                <div class="flex flex-col mx-3 h-full overflow-hidden">
                    {{-- Ringkasan Tugas Section - Fixed --}}
                    <div class="flex items-center gap-2 mb-3 flex-shrink-0">
                        <img src="{{ asset('images/icons/Schedule.svg') }}" alt="Schedule" class="w-6 h-6" />
                        <p class="text-sm text-gray-700">Ringkasan tugas saya</p>
                    </div>

                    {{-- Container Putih Terluar --}}
                    <div class="bg-white rounded-xl shadow-sm px-6 py-4 flex-1 flex flex-col overflow-hidden min-h-0">
                        {{-- Header: Judul + Search + Sort --}}
                        <div class="mb-3 flex-shrink-0">
                            <div class="flex items-center  justify-center gap-6">
                                {{-- Judul Perencanaan --}}
                                <h2 class="text-xl font-bold text-gray-800">Perencanaan</h2>
                                {{-- Search Box --}}
                                <div class="w-60 relative">
                                    <input type="text" placeholder="Cari tugas..."
                                        class="w-full pl-10 pr-4 py-2 bg-gray-100 border-0 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm placeholder-gray-400" />
                                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>

                                {{-- Sort Button --}}
                                <button class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition flex-shrink-0">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Divider --}}
                        <div class="border-t border-gray-200 mb-3 flex-shrink-0"></div>

                        {{-- Scroll Container dengan Cards menggunakan x-scroll-card --}}
                        <div class="flex-1 min-h-0 overflow-hidden">
                            <x-scroll-card max-height="h-full">
                                <div class="space-y-2">
                                    {{-- Card 1 --}}
                                    <x-progress-card title="Div. Marketing"
                                        description="Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan"
                                        :percentage="80" :members="[
                                            ['name' => 'Sahroni', 'avatar' => 'https://i.pravatar.cc/40?img=1'],
                                            ['name' => 'Andi', 'avatar' => 'https://i.pravatar.cc/40?img=2'],
                                            ['name' => 'Rizal', 'avatar' => 'https://i.pravatar.cc/40?img=3'],
                                            ['name' => 'Bagas', 'avatar' => 'https://i.pravatar.cc/40?img=4'],
                                        ]" :additionalCount="3" />

                                    {{-- Card 2 --}}
                                    <x-progress-card title="Div. Marketing"
                                        description="Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan"
                                        :percentage="90" :members="[
                                            ['name' => 'Sahroni', 'avatar' => 'https://i.pravatar.cc/40?img=1'],
                                            ['name' => 'Andi', 'avatar' => 'https://i.pravatar.cc/40?img=2'],
                                            ['name' => 'Rizal', 'avatar' => 'https://i.pravatar.cc/40?img=3'],
                                        ]" :additionalCount="3" />

                                    {{-- Date Separator --}}
                                    <div class="flex items-center justify-center py-2">
                                        <span class="text-sm text-gray-500">Kamis, 18 September 2025</span>
                                    </div>

                                    {{-- Card 3 --}}
                                    <x-progress-card title="Div. Marketing"
                                        description="Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan"
                                        :percentage="60" :members="[
                                            ['name' => 'Sahroni', 'avatar' => 'https://i.pravatar.cc/40?img=1'],
                                            ['name' => 'Andi', 'avatar' => 'https://i.pravatar.cc/40?img=2'],
                                            ['name' => 'Rizal', 'avatar' => 'https://i.pravatar.cc/40?img=3'],
                                        ]" :additionalCount="3" />

                                    {{-- Card 4 --}}
                                    <x-progress-card title="Div. Marketing"
                                        description="Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan"
                                        :percentage="60" :members="[
                                            ['name' => 'Sahroni', 'avatar' => 'https://i.pravatar.cc/40?img=1'],
                                            ['name' => 'Andi', 'avatar' => 'https://i.pravatar.cc/40?img=2'],
                                            ['name' => 'Rizal', 'avatar' => 'https://i.pravatar.cc/40?img=3'],
                                        ]" :additionalCount="3" />
                                    <x-progress-card title="Div. Marketing"
                                        description="Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan"
                                        :percentage="60" :members="[
                                            ['name' => 'Sahroni', 'avatar' => 'https://i.pravatar.cc/40?img=1'],
                                            ['name' => 'Andi', 'avatar' => 'https://i.pravatar.cc/40?img=2'],
                                            ['name' => 'Rizal', 'avatar' => 'https://i.pravatar.cc/40?img=3'],
                                        ]" :additionalCount="3" />
                                    <x-progress-card title="Div. Marketing"
                                        description="Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan"
                                        :percentage="60" :members="[
                                            ['name' => 'Sahroni', 'avatar' => 'https://i.pravatar.cc/40?img=1'],
                                            ['name' => 'Andi', 'avatar' => 'https://i.pravatar.cc/40?img=2'],
                                            ['name' => 'Rizal', 'avatar' => 'https://i.pravatar.cc/40?img=3'],
                                        ]" :additionalCount="3" />
                                    <x-progress-card title="Div. Marketing"
                                        description="Menargetkan penjualan di atas 40% sehingga terjadi kenaikan penjualan"
                                        :percentage="60" :members="[
                                            ['name' => 'Sahroni', 'avatar' => 'https://i.pravatar.cc/40?img=1'],
                                            ['name' => 'Andi', 'avatar' => 'https://i.pravatar.cc/40?img=2'],
                                            ['name' => 'Rizal', 'avatar' => 'https://i.pravatar.cc/40?img=3'],
                                        ]" :additionalCount="3" />
                                </div>
                            </x-scroll-card>
                        </div>
                    </div>
                </div>

                {{-- Right Column - Calendar & Details --}}
                <div class="flex flex-col h-full overflow-hidden mx-4">
                    {{-- Jadwal Header --}}
                    <div class="flex items-center gap-2 mb-3 flex-shrink-0">
                        <img src="{{ asset('images/icons/Calendar.svg') }}" alt="Schedule" class="w-6 h-6" />
                        <p class="text-base text-gray-700">Jadwal</p>
                    </div>

                    {{-- Calendar Wrapper - Fixed Height --}}
                    <div class="flex-shrink-0 mb-3" style="height: 45vh;">
                        <div class="bg-white rounded-2xl shadow-md p-3 h-full">
                            <div id="calendar" class="w-full h-full"></div>
                        </div>
                    </div>

                    {{-- Task Detail - Scrollable menggunakan x-scroll-card --}}
                    <div class="flex-1 min-h-0 overflow-hidden">
                        <x-scroll-card max-height="h-full">
                            <div class="space-y-3">
                                {{-- Card 1 --}}
                                <x-task-detail-card time="Kamis, 18 Feb 2025 11:12 AM - Kamis, 18 Feb 2025 01:00 PM"
                                    title="Div. Marketing"
                                    description="Rapat pengadaan alat yang di butuhkan dan revisi fonder agritment sebersar 50% dan juga"
                                    :members="[
                                        ['name' => 'Sahroni', 'avatar' => 'https://i.pravatar.cc/40?img=1'],
                                        ['name' => 'Andi', 'avatar' => 'https://i.pravatar.cc/40?img=2'],
                                        ['name' => 'Rizal', 'avatar' => 'https://i.pravatar.cc/40?img=3'],
                                    ]" :additionalCount="3" />

                                {{-- Card 2 --}}
                                <x-task-detail-card time="Jumat, 19 Feb 2025 09:00 AM - Jumat, 19 Feb 2025 11:00 AM"
                                    title="Div. IT"
                                    description="Meeting koordinasi pengembangan sistem baru dan maintenance server"
                                    :members="[
                                        ['name' => 'Budi', 'avatar' => 'https://i.pravatar.cc/40?img=5'],
                                        ['name' => 'Siti', 'avatar' => 'https://i.pravatar.cc/40?img=6'],
                                    ]" :additionalCount="2" />

                                {{-- Card 3 --}}
                                <x-task-detail-card time="Sabtu, 20 Feb 2025 14:00 PM - Sabtu, 20 Feb 2025 16:00 PM"
                                    title="Div. Finance" description="Review budget tahunan dan laporan keuangan Q1"
                                    :members="[
                                        ['name' => 'Andi', 'avatar' => 'https://i.pravatar.cc/40?img=7'],
                                        ['name' => 'Dina', 'avatar' => 'https://i.pravatar.cc/40?img=8'],
                                    ]" :additionalCount="1" />
                            </div>
                        </x-scroll-card>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FullCalendar CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    <style>
        /* ===== Hilangkan semua border, background, dan kotak default ===== */
        .fc-theme-standard td,
        .fc-theme-standard th,
        .fc-theme-standard .fc-scrollgrid,
        .fc .fc-daygrid-day,
        .fc .fc-daygrid-day-bg,
        .fc .fc-col-header-cell,
        .fc .fc-daygrid-day-frame,
        .fc .fc-scrollgrid-sync-inner {
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* ===== Toolbar custom ===== */
        .fc .fc-toolbar.fc-header-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem !important;
        }

        .fc .fc-toolbar-chunk .fc-button-group {
            display: contents !important;
        }

        .fc .fc-toolbar-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: black;
        }

        .fc .fc-button {
            background: #2563eb;
            color: white;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            padding: 0;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .fc .fc-button:hover {
            background: #225ad6;
        }

        .fc .fc-toolbar-chunk button.fc-next-button {
            margin-right: 10px;
            margin-top: 0px;
        }

        .fc .fc-toolbar-chunk button.fc-prev-button {
            margin-left: 10px;
            margin-top: 0px;
        }

        /* ===== Tanggal & hari ===== */
        .fc .fc-daygrid-day-top {
            justify-content: center;
            font-size: 14px;
        }

        .fc .fc-daygrid-day-number {
            font-family: 'Inter', sans-serif;
            color: #102a63;
            font-weight: 500;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            margin: auto;
        }

        .fc .fc-daygrid-day-number:hover {
            background-color: #225ad6;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto;
        }

        .fc-day-selected .fc-daygrid-day-number {
            background-color: transparent !important;
            color: #102a63 !important;
            border: none !important;
            box-shadow: none !important;
        }

        .fc-daygrid-day:focus,
        .fc-daygrid-day-number:focus {
            outline: none !important;
            box-shadow: none !important;
        }

        .fc .fc-col-header-cell-cushion {
            color: #102a63;
            font-weight: 500;
            opacity: 0.5;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            padding-bottom: 8px;
        }

        #calendar {
            overflow: hidden !important;
            height: 100% !important;
        }

        .fc .fc-scroller {
            overflow: hidden !important;
        }

        .fc .fc-daygrid-day-events {
            display: none !important;
        }

        .fc .fc-daygrid-event {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            height: auto !important;
        }

        .fc .fc-highlight {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        .fc .fc-daygrid-day-frame {
            position: relative;
            overflow: visible !important;
        }

        .fc .day-marker {
            position: absolute;
            top: 0px;
            left: 65%;
            width: 16px;
            height: 16px;
            background-color: #facc15;
            border: 2px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #6B7280;
            font-weight: 600;
            font-family: inter, sans-serif;
            pointer-events: none;
            z-index: 10;
        }
    </style>

    {{-- FullCalendar JS --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let calendarEl = document.getElementById('calendar');

            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                selectable: true,
                fixedWeekCount: false,
                height: '100%',
                headerToolbar: {
                    left: 'prev',
                    center: 'title',
                    right: 'next'
                },
                dateClick: function(info) {
                    document.querySelectorAll('.fc-day-selected')
                        .forEach(el => el.classList.remove('fc-day-selected'));
                    info.dayEl.classList.add('fc-day-selected');
                },
                dayCellDidMount: function(info) {
                    const dateStr = info.date.toISOString().split('T')[0];
                    if (dateStr === '2025-10-01') {
                        const marker = document.createElement('div');
                        marker.classList.add('day-marker');
                        marker.textContent = '1';
                        const frame = info.el.querySelector('.fc-daygrid-day-frame');
                        if (frame) frame.appendChild(marker);
                    }
                    if (dateStr === '2025-10-06') {
                        const marker = document.createElement('div');
                        marker.classList.add('day-marker');
                        marker.textContent = '6';
                        const frame = info.el.querySelector('.fc-daygrid-day-frame');
                        if (frame) frame.appendChild(marker);
                    }
                }
            });

            calendar.render();
        });
    </script>
@endsection
