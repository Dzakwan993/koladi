@extends('layouts.app')

@section('title', 'Jadwal')

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
                    <a href="{{ route('buatJadwal', ['workspaceId' => $workspaceId]) }}"
                        class="bg-[#225ad6] rounded-[8px] shadow-xl
                        flex items-center justify-center
                        p-5 w-[400px] h-[40px]
                        text-[#ffffff] font-semibold hover:bg-[#1a46a0] transition mb-4 buat-jadwal-btn">
                        Buat Jadwal
                    </a>

                    <div
                        class="card bg-[#bbcff9] rounded-[8px] shadow-xl flex flex-col items-center justify-center
                                p-6 w-[400px] h-[300px] notulen-card">
                        <img src="{{ asset('images/icons/Notulen.png') }}" alt="Notulen Icon">

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
                <div class="w-[930px] flex flex-col gap-3 schedule-list" id="scheduleList">
                    <!-- Events akan dirender di sini via JavaScript -->
                    <div class="text-center text-gray-500 py-8" id="loadingSchedule">
                        <i class="fas fa-spinner fa-spin text-2xl"></i>
                        <p class="mt-2">Tidak ada jadwal saat ini</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- font --}}
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        {{-- FullCalendar CSS --}}
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

        {{-- Custom CSS --}}
        <style>
            /* ===== Calendar Styles ===== */
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

            .fc .fc-toolbar.fc-header-toolbar {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .fc .fc-toolbar-chunk .fc-button-group {
                display: contents !important;
            }

            .fc .fc-toolbar-title {
                text-align: center;
                font-size: 20px;
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
                border: none;
            }

            .fc .fc-button:hover {
                background: #225ad6;
            }

            .fc .fc-toolbar-chunk button.fc-next-button {
                margin-right: 10px;
            }

            .fc .fc-toolbar-chunk button.fc-prev-button {
                margin-left: 10px;
            }

            .fc .fc-daygrid-day-top {
                justify-content: center;
                font-size: 14px;
            }

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

            .fc .fc-daygrid-day-number:hover {
                background-color: #225ad6;
                color: white;
                border-radius: 50%;
                cursor: pointer;
            }

            .fc .fc-day-today .fc-daygrid-day-number {
                background-color: #2563eb;
                color: white;
                font-weight: bold;
                border-radius: 50%;
            }

            .fc-day-selected .fc-daygrid-day-number {
                background-color: transparent !important;
                color: #102a63 !important;
            }

            .fc .fc-col-header-cell-cushion {
                color: #102a63;
                font-weight: 500;
                opacity: 0.5;
                font-family: 'Inter', sans-serif;
                font-size: 16px;
                padding-bottom: 10px;
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
                font-family: Inter, sans-serif;
                pointer-events: none;
                z-index: 10;
            }

            /* ===== Schedule List Styles ===== */
            .schedule-item {
                transition: all 0.3s ease;
            }

            .schedule-item:hover {
                transform: translateY(-2px);
            }

            /* ===== RESPONSIVE STYLES ===== */
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

                .buat-jadwal-btn,
                .notulen-card {
                    width: 100% !important;
                }

                .schedule-list {
                    width: 100% !important;
                }
            }

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
            }

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
            }
        </style>

        {{-- FullCalendar JS --}}
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const workspaceId = '{{ $workspaceId }}';
                let calendarEl = document.getElementById('calendar');
                let calendar = null;
                let allEvents = [];

                // ✅ Initialize Calendar
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    locale: 'id',
                    selectable: true,
                    fixedWeekCount: false,
                    headerToolbar: {
                        left: 'prev',
                        center: 'title',
                        right: 'next'
                    },

                    // ✅ Load events via AJAX
                    events: function(info, successCallback, failureCallback) {
                        fetch(
                                `/workspace/${workspaceId}/calendar/events?start=${info.startStr}&end=${info.endStr}`)
                            .then(response => response.json())
                            .then(data => {
                                allEvents = data;
                                successCallback(data);

                                // Update schedule list
                                renderScheduleList(data);
                            })
                            .catch(error => {
                                console.error('Error loading events:', error);
                                failureCallback(error);
                            });
                    },

                    // ✅ Handle click pada tanggal
                    dateClick: function(info) {
                        document.querySelectorAll('.fc-day-selected')
                            .forEach(el => el.classList.remove('fc-day-selected'));
                        info.dayEl.classList.add('fc-day-selected');

                        // Filter schedule list berdasarkan tanggal yang diklik
                        const clickedDate = info.dateStr;
                        const filteredEvents = allEvents.filter(event => {
                            const eventDate = event.start.split('T')[0];
                            return eventDate === clickedDate;
                        });
                        renderScheduleList(filteredEvents);
                    },

                    // ✅ Handle click pada event
                    eventClick: function(info) {
                        info.jsEvent.preventDefault();
                        window.location.href = `/workspace/${workspaceId}/jadwal/${info.event.id}`;
                    },

                    // ✅ Custom marker untuk tanggal dengan events
                    dayCellDidMount: function(info) {
                        // Tunggu sampai events dimuat
                        setTimeout(() => {
                            const eventsOnDate = calendar.getEvents().filter(event => {
                                const eventDate = new Date(event.start);
                                return eventDate.toDateString() === info.date
                            .toDateString();
                            });

                            if (eventsOnDate.length > 0) {
                                // Hapus marker lama jika ada
                                const existingMarker = info.el.querySelector('.day-marker');
                                if (existingMarker) {
                                    existingMarker.remove();
                                }

                                const marker = document.createElement('div');
                                marker.classList.add('day-marker');
                                marker.textContent = eventsOnDate.length;
                                const frame = info.el.querySelector('.fc-daygrid-day-frame');
                                if (frame) frame.appendChild(marker);
                            }
                        }, 100);
                    }
                });

                calendar.render();

                // ✅ Function to render schedule list
                function renderScheduleList(events) {
                    const scheduleList = document.getElementById('scheduleList');
                    const loading = document.getElementById('loadingSchedule');

                    if (loading) {
                        loading.remove();
                    }

                    if (!events || events.length === 0) {
                        scheduleList.innerHTML = `
                            <div class="text-center text-gray-500 py-8">
                                <i class="fas fa-calendar-times text-4xl mb-2"></i>
                                <p>Tidak ada jadwal untuk ditampilkan</p>
                            </div>
                        `;
                        return;
                    }

                    // Group events by date
                    const groupedEvents = {};
                    events.forEach(event => {
                        const date = new Date(event.start);
                        const dateKey = date.toLocaleDateString('id-ID', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });

                        if (!groupedEvents[dateKey]) {
                            groupedEvents[dateKey] = [];
                        }
                        groupedEvents[dateKey].push(event);
                    });

                    let html = '';

                    Object.keys(groupedEvents).forEach((dateKey, index) => {
                        if (index > 0) {
                            html += '<hr class="border-t border-gray-300 my-4">';
                        }

                        groupedEvents[dateKey].forEach(event => {
                            const startTime = new Date(event.start).toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                            const endTime = new Date(event.end).toLocaleTimeString('id-ID', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });

                            const icon = event.extendedProps.is_online ?
                                '{{ asset('images/icons/Zoom.png') }}' :
                                '{{ asset('images/icons/Meeting.png') }}';

                            const bgColor = event.extendedProps.is_creator ?
                                'bg-[#bbcff9]' :
                                'bg-[#d4e4ff]';

                            html += `
                                <a href="/workspace/${workspaceId}/jadwal/${event.id}"
                                    class="${bgColor} rounded-lg shadow-md flex items-center justify-between p-4 hover:shadow-lg transition schedule-item">

                                    <div class="flex flex-col items-start w-[120px] date-section">
                                        <span class="font-semibold text-[14px]">${dateKey.split(',')[0]}</span>
                                        <span class="font-semibold text-[14px]">${dateKey.split(',')[1]?.trim()}</span>
                                    </div>

                                    <div class="flex flex-col flex-1 px-4 content-section">
                                        <div class="flex items-center gap-2">
                                            <img src="${icon}" class="w-6 h-6" alt="Meeting Icon">
                                            <span class="font-semibold text-[#090909] text-base">${event.title}</span>
                                        </div>

                                        <div class="flex items-center gap-2 mt-1">
                                            <i class="fas fa-clock text-[#102a63] w-6 text-center"></i>
                                            <span class="text-sm font-medium text-[#102a63]">${startTime} - ${endTime}</span>
                                        </div>
                                    </div>

                                    <div class="badge-section">
                                        <span class="bg-yellow-400 text-[#6B7280] text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-sm">
                                            ${event.extendedProps.participants_count}
                                        </span>
                                    </div>
                                </a>
                            `;
                        });
                    });

                    scheduleList.innerHTML = html;
                }
            });
        </script>
    @endsection
