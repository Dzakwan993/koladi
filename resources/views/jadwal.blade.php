@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <div class="bg-[#f3f6fc] min-h-screen">
        @include('components.workspace-nav')

        <!-- Card putih besar sebagai parent -->
        <div class="card bg-white rounded-[8px] shadow-xl flex flex-col gap-5 p-6 m-6 mx-10 h-[900px] responsive-container">

            <!-- Bagian atas: kalender + notulen sejajar -->
            <div class="flex flex-row gap-5 items-center justify-center responsive-top">

                <!-- Card kalender -->
                <div
                    class="card bg-white rounded-[8px] shadow-xl p-4 flex flex-col items-center justify-center w-full max-w-lg h-full calendar-card">
                    <div id="calendar" class="w-full h-full"></div>
                </div>

                <div class="flex flex-col right-section">
                    <a href="{{ route('buatJadwal') }}"
                        class="bg-[#225ad6] rounded-[8px] shadow-xl
   flex items-center justify-center
   p-5 w-[400px] h-[40px]
   text-[#ffffff] font-semibold hover:bg-[#1a46a0] transition mb-4 buat-jadwal-btn">
                        Buat Jadwal
                    </a>


                    <div
                        class="card bg-[#bbcff9] rounded-[8px] shadow-xl flex flex-col items-center justify-center
                p-6 w-[400px] h-[300px] notulen-card">

                        <img src="{{ asset('images/icons/Notulen.png') }}">

                        <!-- Judul -->
                        <h1 class="text-2xl text-[#102a63] mb-2 font-bold font-inter">
                            Notulensi Rapat
                        </h1>

                        <!-- Deskripsi -->
                        <p class="text-[#102a63] text-center text-sm font-medium font-inter">
                            Klik untuk melihat catatan rapat terakhir
                        </p>
                    </div>
                </div>
            </div>

            <!-- Card hitam full di bawah -->
            <div class="flex justify-center">
                <div class="w-[930px] flex flex-col gap-3 schedule-list">
                    <div class="flex justify-center">
                        <div class="w-[930px] flex flex-col gap-4 schedule-list">

                            <!-- Wrapper semua item -->
                            <div class="space-y-6 font-inter">

                                <!-- Grup Hari: Rabu -->
                                <div class="space-y-2 font-inter">

                                    <!-- start -->
                                    <!-- Item Card 1 -->
                                    <a href="{{ url('/isiJadwalOnline') }}"
                                        class="bg-[#bbcff9] rounded-lg shadow-md flex items-center justify-between p-4 hover:shadow-lg transition schedule-item">

                                        <!-- Tanggal -->
                                        <div class="flex flex-col items-center w-[120px] date-section">
                                            <span class="font-semibold text-[14px]">Rabu</span>
                                            <span class="font-semibold text-[14px]">1 Oktober 2025</span>
                                        </div>
                                        <!-- Isi -->
                                        <div class="flex flex-col flex-1 px-4 content-section">
                                            <!-- Judul + Icon sejajar -->
                                            <div class="flex items-center gap-2">
                                                <img src="{{ asset('images/icons/Zoom.png') }}" class="w-6 h-6"
                                                    alt="Zoom">
                                                <span class="font-semibold text-[#090909] text-base">Membahas projek terbaru</span>
                                            </div>

                                            <!-- Jam + Profil sejajar -->
                                            <div class="flex items-center gap-2 mt-1">
                                                <img src="{{ asset('images/dk.jpg') }}" class="w-6 h-6 rounded-full"
                                                    alt="Profile">
                                                <span class="text-sm font-medium text-[#102a63]">06:00 PM - 05:00 AM</span>
                                            </div>
                                        </div>

                                        <!-- Badge -->
                                        <div class="badge-section">
                                            <span
                                                class="bg-yellow-400 text-[#6B7280] text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-sm">
                                                2
                                            </span>
                                        </div>
                                    </a>
                                    <!-- end -->


                                    <!-- start -->
                                    <!-- Item Card 2 -->
                                    <a href="{{ url('/isiJadwalOffline') }}"
                                        class="bg-[#bbcff9] rounded-lg shadow-md flex items-center justify-between p-4 hover:shadow-lg transition schedule-item">

                                        <!-- Tanggal -->
                                        <div class="flex flex-col items-center w-[120px] date-section">
                                            <span class="font-semibold text-[14px]">Rabu</span>
                                            <span class="font-semibold text-[14px]">1 Oktober 2025</span>
                                        </div>
                                        <!-- Isi -->
                                        <div class="flex flex-col flex-1 px-4 content-section">
                                            <!-- Judul + Icon sejajar -->
                                            <div class="flex items-center gap-2">
                                                <img src="{{ asset('images/icons/Zoom.png') }}" class="w-6 h-6"
                                                    alt="Zoom">
                                                <span class="font-semibold text-[#090909] text-base">Rapat dilakukan di kantor</span>
                                            </div>

                                            <!-- Jam + Profil sejajar -->
                                            <div class="flex items-center gap-2 mt-1">
                                                <img src="{{ asset('images/dk.jpg') }}" class="w-6 h-6 rounded-full"
                                                    alt="Profile">
                                                <span class="text-sm font-medium text-[#102a63]">06:00 PM - 05:00 AM</span>
                                            </div>
                                        </div>

                                        <!-- Badge -->
                                        <div class="badge-section">
                                            <span
                                                class="bg-yellow-400 text-[#6B7280] text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-sm">
                                                2
                                            </span>
                                        </div>
                                    </a>
                                    <!-- end -->

                                    <!-- Divider antar hari -->
                                    <hr class="border-t border-gray-300">


                                    <!-- start -->
                                    <!-- Item Card 3 -->
                                    <a href="{{ url('/isiJadwalTidakAdaRapat') }}"
                                        class="bg-[#bbcff9] rounded-lg shadow-md flex items-center justify-between p-4 hover:shadow-lg transition schedule-item">

                                        <!-- Tanggal -->
                                        <div class="flex flex-col items-center w-[120px] date-section">
                                            <span class="font-semibold text-[14px]">Rabu</span>
                                            <span class="font-semibold text-[14px]">1 Oktober 2025</span>
                                        </div>
                                        <!-- Isi -->
                                        <div class="flex flex-col flex-1 px-4 content-section">
                                            <!-- Judul + Icon sejajar -->
                                            <div class="flex items-center gap-2">
                                                <img src="{{ asset('images/icons/Zoom.png') }}" class="w-6 h-6"
                                                    alt="Zoom">
                                                <span class="font-semibold text-[#090909] text-base">Rapat dilakukan di kantor</span>
                                            </div>

                                            <!-- Jam + Profil sejajar -->
                                            <div class="flex items-center gap-2 mt-1">
                                                <img src="{{ asset('images/dk.jpg') }}" class="w-6 h-6 rounded-full"
                                                    alt="Profile">
                                                <span class="text-sm font-medium text-[#102a63]">06:00 PM - 05:00 AM</span>
                                            </div>
                                        </div>

                                        <!-- Badge -->
                                        <div class="badge-section">
                                            <span
                                                class="bg-yellow-400 text-[#6B7280] text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-sm">
                                                2
                                            </span>
                                        </div>
                                    </a>
                                    <!-- end -->


                                    <!-- start -->
                                    <!-- Item Card 1 -->
                                    <div
                                        class="bg-[#bbcff9] rounded-lg shadow-md flex items-center justify-between p-4 hover:shadow-lg transition schedule-item">

                                        <!-- Tanggal -->
                                        <div class="flex flex-col items-center w-[120px] date-section">
                                            <span class="font-semibold text-[14px]">Rabu</span>
                                            <span class="font-semibold text-[14px]">1 Oktober 2025</span>
                                        </div>
                                        <!-- Isi -->
                                        <div class="flex flex-col flex-1 px-4 content-section">
                                            <!-- Judul + Icon sejajar -->
                                            <div class="flex items-center gap-2">
                                                <img src="{{ asset('images/icons/Zoom.png') }}" class="w-6 h-6"
                                                    alt="Zoom">
                                                <span class="font-semibold text-[#090909] text-base">Pengumuman
                                                    Darurat</span>
                                            </div>

                                            <!-- Jam + Profil sejajar -->
                                            <div class="flex items-center gap-2 mt-1">
                                                <img src="{{ asset('images/dk.jpg') }}" class="w-6 h-6 rounded-full"
                                                    alt="Profile">
                                                <span class="text-sm font-medium text-[#102a63]">06:00 PM - 05:00 AM</span>
                                            </div>
                                        </div>

                                        <!-- Badge -->
                                        <div class="badge-section">
                                            <span
                                                class="bg-yellow-400 text-[#6B7280] text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-sm">
                                                2
                                            </span>
                                        </div>
                                    </div>
                                    <!-- end -->

                                </div>
                            </div>



                        </div>

                    </div>
                </div>

                {{-- font --}}
                <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
                    rel="stylesheet">

                {{-- FullCalendar CSS --}}
                <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

                {{-- Custom CSS --}}
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
                    }

                    /* Lepas grouping tombol prev & next */
                    .fc .fc-toolbar-chunk .fc-button-group {
                        display: contents !important;
                    }

                    /* Judul bulan */
                    .fc .fc-toolbar-title {
                        text-align: center;
                        font-size: 20px;
                        font-weight: bold;
                        color: black;
                    }

                    /* Tombol navigasi bulat sempurna */
                    .fc .fc-button {
                        background: #2563eb;
                        color: white;
                        border-radius: 50%;
                        /* bulat sempurna */
                        width: 28px;
                        /* lebar tombol */
                        height: 28px;
                        /* tinggi tombol */
                        padding: 0;
                        /* hapus padding default */
                        font-size: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        transition: background 0.2s;
                    }

                    .fc .fc-button:hover {
                        background: #225ad6;
                    }

                    /* Jarak tombol prev & next */
                    .fc .fc-toolbar-chunk button.fc-next-button {
                        margin-right: 10px;
                        margin-top: 0px;
                    }

                    .fc .fc-toolbar-chunk button.fc-prev-button {
                        margin-left: 10px;
                        margin-top: 0px;
                    }


                    /* ===== Tanggal & hari ===== */
                    /* Tanggal di tengah */
                    .fc .fc-daygrid-day-top {
                        justify-content: center;
                        font-size: 14px;
                    }

                    /* Style angka tanggal */
                    .fc .fc-daygrid-day-number {
                        font-family: 'Inter', sans-serif;
                        color: #102a63;
                        font-weight: 500;
                        font-size: 18px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 42px;
                        height: 42px;
                        margin: auto;
                    }

                    /* Hover efek bulat */
                    .fc .fc-daygrid-day-number:hover {
                        background-color: #225ad6;
                        color: white;
                        border-radius: 50%;
                        cursor: pointer;
                        width: 42px;
                        height: 42px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: auto;
                    }

                    /* Highlight hari ini */
                    .fc .fc-day-today .fc-daygrid-day-number {
                        background-color: #2563eb;
                        color: white;
                        font-weight: bold;
                        border-radius: 50%;
                        width: 42px;
                        height: 42px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: auto;
                    }

                    /* Hilangkan highlight default saat klik tanggal */
                    .fc-day-selected .fc-daygrid-day-number {
                        background-color: transparent !important;
                        color: #102a63 !important;
                        border: none !important;
                        box-shadow: none !important;
                    }

                    /* Hilangkan outline/focus browser */
                    .fc-daygrid-day:focus,
                    .fc-daygrid-day-number:focus {
                        outline: none !important;
                        box-shadow: none !important;
                    }

                    /* Style judul hari (Sen, Sel, …) */
                    .fc .fc-col-header-cell-cushion {
                        color: #102a63;
                        font-weight: 500;
                        opacity: 0.5;
                        font-family: 'Inter', sans-serif;
                        font-size: 16px;
                        padding-bottom: 10px;
                    }

                    /* Hilangkan scroll */
                    #calendar {
                        overflow: hidden !important;
                        height: 100% !important;
                    }

                    .fc .fc-scroller {
                        overflow: hidden !important;
                    }

                    /* ===== Hilangkan semua event container ===== */
                    .fc .fc-daygrid-day-events {
                        display: none !important;
                        /* hilangkan seluruh event container */
                    }

                    .fc .fc-daygrid-event {
                        background: transparent !important;
                        border: none !important;
                        box-shadow: none !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        height: auto !important;
                    }

                    /* Hilangkan highlight default FullCalendar */
                    .fc .fc-highlight {
                        background: transparent !important;
                        border: none !important;
                        box-shadow: none !important;
                    }

                    /* Pastikan frame tanggal tidak memotong elemen custom */
                    .fc .fc-daygrid-day-frame {
                        position: relative;
                        overflow: visible !important;
                        /* biar marker tidak terpotong */
                    }

                    /* Marker kuning dengan angka di dalam */
                    .fc .day-marker {
                        position: absolute;
                        top: 0px;
                        /* posisi di atas angka tanggal */
                        left: 65%;
                        width: 16px;
                        /* lingkaran lebih besar supaya muat angka */
                        height: 16px;
                        background-color: #facc15;
                        /* kuning */
                        border: 2px solid #ffffff;
                        /* border putih */
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 10px;
                        /* ukuran angka */
                        color: #6B7280;
                        /* warna angka di lingkaran */
                        font-weight: 600;
                        font-family: inter: sans-serif pointer-events: none;
                        z-index: 10;
                    }

                    /* ===== RESPONSIVE STYLES ===== */

                    /* Tablet - 1024px and below */
                    @media (max-width: 1024px) {
                        .responsive-container {
                            margin: 1rem !important;
                            padding: 1rem !important;
                            height: auto !important;
                        }

                        .responsive-top {
                            flex-direction: column !important;
                        }

                        .calendar-card {
                            max-width: 100% !important;
                            height: 400px !important;
                        }

                        .right-section {
                            width: 100% !important;
                        }

                        .buat-jadwal-btn {
                            width: 100% !important;
                        }

                        .notulen-card {
                            width: 100% !important;
                        }

                        .schedule-list {
                            width: 100% !important;
                        }
                    }

                    /* Mobile - 768px and below */
                    @media (max-width: 768px) {
                        .responsive-container {
                            margin: 0.5rem !important;
                            padding: 0.75rem !important;
                        }

                        .calendar-card {
                            padding: 0.5rem !important;
                            height: 350px !important;
                        }

                        .schedule-item {
                            flex-direction: column !important;
                            align-items: flex-start !important;
                            gap: 0.75rem !important;
                        }

                        .date-section {
                            width: 100% !important;
                            flex-direction: row !important;
                            justify-content: flex-start !important;
                            gap: 0.5rem !important;
                        }

                        .content-section {
                            padding-left: 0 !important;
                            padding-right: 0 !important;
                        }

                        .badge-section {
                            align-self: flex-end !important;
                        }

                        .fc .fc-toolbar-title {
                            font-size: 16px !important;
                        }

                        .fc .fc-daygrid-day-number {
                            font-size: 14px !important;
                            width: 32px !important;
                            height: 32px !important;
                        }

                        .fc .fc-day-today .fc-daygrid-day-number {
                            width: 32px !important;
                            height: 32px !important;
                        }

                        .fc .fc-col-header-cell-cushion {
                            font-size: 12px !important;
                        }
                    }

                    /* Small Mobile - 480px and below */
                    @media (max-width: 480px) {
                        .responsive-container {
                            margin: 0.25rem !important;
                            padding: 0.5rem !important;
                        }

                        .calendar-card {
                            height: 300px !important;
                        }

                        .notulen-card {
                            height: auto !important;
                            padding: 1rem !important;
                        }

                        .notulen-card h1 {
                            font-size: 1.25rem !important;
                        }

                        .notulen-card p {
                            font-size: 0.75rem !important;
                        }

                        .schedule-item {
                            padding: 0.75rem !important;
                        }

                        .date-section span {
                            font-size: 12px !important;
                        }

                        .content-section span {
                            font-size: 14px !important;
                        }

                        .fc .fc-toolbar-title {
                            font-size: 14px !important;
                        }

                        .fc .fc-button {
                            width: 24px !important;
                            height: 24px !important;
                            font-size: 12px !important;
                        }

                        .fc .fc-daygrid-day-number {
                            font-size: 12px !important;
                            width: 28px !important;
                            height: 28px !important;
                        }

                        .fc .fc-day-today .fc-daygrid-day-number {
                            width: 28px !important;
                            height: 28px !important;
                        }

                        .fc .day-marker {
                            width: 14px !important;
                            height: 14px !important;
                            font-size: 8px !important;
                        }
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
                            fixedWeekCount: false, // ⬅️ Tambahkan ini
                            headerToolbar: {
                                left: 'prev',
                                center: 'title',
                                right: 'next'
                            },
                            dateClick: function(info) {
                                // Highlight biru berpindah
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
