@extends('layouts.app')

@section('title', 'Jadwal Ruang Kerja')

@section('content')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    <div class="min-h-screen bg-gradient-to-br from-[#f3f6fc] to-[#e9effd] font-[Inter,sans-serif]">
        {{-- Workspace Navigation --}}
        @include('components.workspace-nav', [
            'active' => 'jadwal',
            'workspaceId' => $workspaceId,
        ])

        <div class="px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
            <div class="max-w-7xl mx-auto">

                {{-- Hero Header --}}
                <div class="mb-6 pb-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-[#1E1E1E] mb-2">
                                Jadwal Ruang Kerja 📅
                            </h1>
                            <p class="text-xs sm:text-sm md:text-base text-[#6B7280]">
                                Kelola dan lihat semua jadwal workspace Anda dalam satu tempat
                            </p>
                        </div>

                        {{-- Button Actions --}}
                        <div class="flex gap-3">
                            <button onclick="window.location.href='{{ route('notulensi', ['workspaceId' => $workspaceId]) }}'"
                                class="inline-flex items-center justify-center gap-2 text-sm bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                <i class="fas fa-file-alt"></i>
                                <span class="hidden sm:inline">Notulensi</span>
                            </button>

                            @if (isset($canCreateSchedule) && $canCreateSchedule)
                                <a href="{{ route('buatJadwal', ['workspaceId' => $workspaceId]) }}"
                                    class="inline-flex items-center justify-center gap-2 text-sm bg-gradient-to-r from-[#225AD6] to-[#1e40af] hover:from-[#1e40af] hover:to-[#225AD6] text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                    <i class="fas fa-plus"></i>
                                    <span>Buat Jadwal</span>
                                </a>
                            @else
                                <div
                                    class="inline-flex items-center justify-center gap-2 text-sm bg-gray-100 text-gray-500 px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl font-semibold cursor-not-allowed">
                                    <i class="fas fa-lock"></i>
                                    <span class="hidden sm:inline">Tidak dapat membuat jadwal</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Main Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-7 gap-4 sm:gap-5 lg:gap-6">

                    {{-- Calendar Section (3/7 on large screens) --}}
                    <div class="lg:col-span-3 flex flex-col">
                        <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                            <div
                                class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
                                <i class="fas fa-calendar-alt text-white text-base sm:text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm sm:text-base lg:text-lg text-[#1E1E1E] font-bold">Kalender</p>
                                <p class="text-xs text-gray-500 hidden sm:block">Pilih tanggal untuk melihat jadwal</p>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-2xl shadow-lg p-3 sm:p-4 lg:p-5 border border-gray-100 h-[320px] sm:h-[400px] lg:h-[450px]">
                            <div id="calendar" class="w-full h-full"></div>
                        </div>
                    </div>

                    {{-- Schedule List Section (4/7 on large screens) --}}
                    <div class="lg:col-span-4 flex flex-col">
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-md">
                                    <i class="fas fa-list-check text-white text-base sm:text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm sm:text-base lg:text-lg text-[#1E1E1E] font-bold" id="scheduleTitle">
                                        Semua Jadwal
                                    </p>
                                    <p class="text-xs text-gray-500 hidden sm:block">Daftar jadwal yang tersedia</p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="bg-white rounded-2xl shadow-lg p-4 sm:p-5 lg:p-6 flex flex-col border border-gray-100 min-h-[400px] max-h-[600px] lg:h-[calc(100vh-280px)]">
                            <div class="overflow-y-auto flex-1 pr-2 py-2 custom-scrollbar">
                                <div class="space-y-3 sm:space-y-4 px-1" id="scheduleList">
                                    <div class="text-center py-12 sm:py-16">
                                        <div
                                            class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-blue-50 mb-4">
                                            <i class="fas fa-spinner fa-spin text-3xl sm:text-4xl text-blue-500"></i>
                                        </div>
                                        <p class="text-sm sm:text-base font-semibold text-gray-700">Memuat jadwal...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #225ad6 0%, #1e40af 100%);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #1e40af 0%, #1a46a0 100%);
        }

        /* Calendar Styles */
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
        }

        .fc .fc-scroller {
            overflow: hidden !important;
        }

        .fc .fc-scroller-liquid-absolute {
            overflow: hidden !important;
        }

        #calendar {
            overflow: hidden !important;
        }

        .fc .fc-toolbar.fc-header-toolbar {
            margin-bottom: 0.75rem !important;
        }

        .fc .fc-toolbar-title {
            font-size: 14px;
            font-weight: 700;
            color: #1E1E1E;
            font-family: 'Inter', sans-serif;
        }

        @media (min-width: 640px) {
            .fc .fc-toolbar-title {
                font-size: 16px;
            }
        }

        .fc .fc-button {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border-radius: 8px;
            width: 28px;
            height: 28px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        @media (min-width: 640px) {
            .fc .fc-button {
                width: 32px;
                height: 32px;
            }
        }

        .fc .fc-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
        }

        .fc .fc-daygrid-day-number {
            font-family: 'Inter', sans-serif;
            color: #475569;
            font-weight: 600;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            margin: 2px auto;
            border-radius: 8px;
            transition: all 0.2s;
        }

        @media (min-width: 640px) {
            .fc .fc-daygrid-day-number {
                font-size: 13px;
                width: 32px;
                height: 32px;
            }
        }

        .fc .fc-daygrid-day-number:hover {
            background: linear-gradient(135deg, #225ad6 0%, #1e40af 100%);
            color: white;
            cursor: pointer;
            transform: scale(1.1);
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            color: white !important;
            font-weight: 700;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.4);
        }

        .fc-day-selected:not(.fc-day-today) .fc-daygrid-day-number {
            background: linear-gradient(135deg, #225ad6 0%, #1e40af 100%) !important;
            color: white !important;
            font-weight: 700;
        }

        .fc .fc-col-header-cell-cushion {
            color: #64748b;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .fc .day-marker {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 16px;
            height: 16px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border: 2px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #fff;
            font-weight: 700;
            pointer-events: none;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(251, 191, 36, 0.4);
        }

        /* ✅ IMPROVED: Schedule Card Styles */
        .schedule-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .schedule-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1) !important;
        }

        /* ✅ Badge Rahasia - Clean & Subtle */
        .badge-private {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: #ef4444;
            color: white;
            padding: 0.125rem 0.375rem;
            border-radius: 0.375rem;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        /* ✅ Meeting Type Badge - Compact */
        .badge-meeting {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 10px;
            font-weight: 600;
        }

        .badge-meeting.online {
            background: #3b82f6;
            color: white;
        }

        .badge-meeting.offline {
            background: #6b7280;
            color: white;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#2563eb',
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#ef4444'
                });
            });
        </script>
    @endif

    @if (session('access_denied'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: '{{ session('access_denied')['title'] }}',
                    html: `
                    <div class="text-center">
                        <p class="mb-3">{{ session('access_denied')['message'] }}</p>
                        <div class="bg-gray-100 rounded-lg p-3 inline-block">
                            <p class="text-sm text-gray-600">Pembuat Jadwal:</p>
                            <p class="font-semibold text-gray-800">{{ session('access_denied')['creator_name'] }}</p>
                        </div>
                    </div>
                `,
                    confirmButtonColor: '#2563eb',
                    confirmButtonText: 'Mengerti'
                });
            });
        </script>
    @endif

    <script>
        window.userCanCreateSchedule = {{ $canCreateSchedule ? 'true' : 'false' }};
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const workspaceId = '{{ $workspaceId }}';
            const calendarEl = document.getElementById('calendar');
            let calendar = null;
            let allEvents = [];
            let eventCountByDate = {};

            function getFirstName(fullName) {
                if (!fullName) return 'User';
                return fullName.trim().split(' ')[0];
            }

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                selectable: true,
                fixedWeekCount: false,
                height: '100%',
                dayHeaderFormat: {
                    weekday: 'short'
                },
                headerToolbar: {
                    left: 'prev',
                    center: 'title',
                    right: 'next'
                },
                displayEventTime: false,
                displayEventEnd: false,
                eventDisplay: 'none',
                buttonText: {
                    today: 'Hari Ini'
                },
                dayHeaderContent: function(arg) {
                    const dayNames = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
                    return dayNames[arg.dow];
                },

                events: function(info, successCallback, failureCallback) {
                    const startDate = info.start.toISOString().split('T')[0] + ' 00:00:00';
                    const endDate = info.end.toISOString().split('T')[0] + ' 23:59:59';
                    const url =
                        `/workspace/${workspaceId}/calendar/events?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;

                    fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error(`Server error: ${response.status}`);
                            return response.json();
                        })
                        .then(data => {
                            if (!Array.isArray(data)) data = [];
                            allEvents = data;
                            eventCountByDate = calculateEventCountByDate(data);
                            successCallback(data);
                            renderScheduleList(data);
                            setTimeout(() => renderDayMarkers(eventCountByDate), 150);
                        })
                        .catch(error => {
                            console.error('❌ Error loading events:', error);
                            failureCallback(error);
                            showErrorMessage(error.message);
                        });
                },

                dateClick: function(info) {
                    document.querySelectorAll('.fc-day-selected')
                        .forEach(el => el.classList.remove('fc-day-selected'));
                    info.dayEl.classList.add('fc-day-selected');

                    const clickedDate = info.dateStr;
                    const filteredEvents = filterEventsByDate(allEvents, clickedDate);

                    const dateObj = new Date(clickedDate);
                    const titleEl = document.getElementById('scheduleTitle');
                    titleEl.textContent = dateObj.toLocaleDateString('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });

                    renderScheduleList(filteredEvents);
                },

                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    window.location.href = `/workspace/${workspaceId}/jadwal/${info.event.id}`;
                },

                datesSet: function(info) {
                    setTimeout(() => renderDayMarkers(eventCountByDate), 150);
                }
            });

            calendar.render();

            function calculateEventCountByDate(events) {
                const countByDate = {};
                events.forEach(event => {
                    try {
                        const startDate = event.extendedProps?.start_date || event.start.split('T')[0];
                        const endDate = event.extendedProps?.end_date || event.end.split('T')[0];
                        let currentDate = new Date(startDate);
                        const lastDate = new Date(endDate);

                        while (currentDate <= lastDate) {
                            const dateKey = currentDate.toISOString().split('T')[0];
                            countByDate[dateKey] = (countByDate[dateKey] || 0) + 1;
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                    } catch (e) {
                        console.error('Error calculating event date:', e);
                    }
                });
                return countByDate;
            }

            function renderDayMarkers(eventCountByDate) {
                document.querySelectorAll('.day-marker').forEach(el => el.remove());
                const dayCells = document.querySelectorAll('.fc-daygrid-day');

                dayCells.forEach(dayCell => {
                    const dateAttr = dayCell.getAttribute('data-date');
                    if (!dateAttr) return;

                    const eventCount = eventCountByDate[dateAttr];
                    if (eventCount && eventCount > 0) {
                        const frame = dayCell.querySelector('.fc-daygrid-day-frame');
                        if (frame && !frame.querySelector('.day-marker')) {
                            const marker = document.createElement('div');
                            marker.classList.add('day-marker');
                            marker.textContent = eventCount;
                            frame.style.position = 'relative';
                            frame.appendChild(marker);
                        }
                    }
                });
            }

            function filterEventsByDate(events, dateStr) {
                return events.filter(event => {
                    const startDate = event.extendedProps?.start_date || event.start.split('T')[0];
                    const endDate = event.extendedProps?.end_date || event.end.split('T')[0];
                    return dateStr >= startDate && dateStr <= endDate;
                });
            }

            function renderScheduleList(events) {
                const scheduleList = document.getElementById('scheduleList');

                if (!events || events.length === 0) {
                    scheduleList.innerHTML = `
                        <div class="text-center py-12 sm:py-16">
                            <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-4">
                                <i class="fas fa-calendar-times text-3xl sm:text-4xl text-gray-400"></i>
                            </div>
                            <p class="text-sm sm:text-base font-semibold text-gray-700 mb-2">Tidak Ada Jadwal</p>
                            <p class="text-xs sm:text-sm text-gray-500">Belum ada jadwal untuk tanggal ini</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                events.forEach(event => {
                    html += renderScheduleCard(event);
                });

                scheduleList.innerHTML = html;
            }

            function renderScheduleCard(event) {
                try {
                    const startDate = new Date(event.start);
                    const endDate = new Date(event.end);
                    const timeDisplay = formatTimeDisplay(startDate, endDate, event);

                    const creatorAvatar = event.extendedProps?.creator_avatar || '/images/default-avatar.png';
                    const creatorFullName = event.extendedProps?.creator_name || 'User';
                    const creatorFirstName = getFirstName(creatorFullName);

                    const isOnlineMeeting = event.extendedProps?.is_online === true || event.extendedProps?.is_online === 1;
                    const meetingLink = event.extendedProps?.meeting_link;
                    const location = event.extendedProps?.location;
                    const hasMeetingLink = meetingLink && meetingLink.trim() !== '' && meetingLink !== 'null';
                    const hasLocation = location && location.trim() !== '' && location !== 'null';

                    const commentsCount = event.extendedProps?.comments_count || 0;
                    const isPrivate = event.extendedProps?.is_private || false;

                    // ✅ Border color berdasarkan status
                    const borderColor = isPrivate ? 'border-l-red-500' : 'border-l-blue-500';
                    const avatarRing = isPrivate ? 'border-red-200' : 'border-blue-200';

                    // ✅ Meeting Type Badge - Compact
                    let meetingTypeBadge = '';
                    let locationInfo = '';

                    if (isOnlineMeeting && hasMeetingLink) {
                        meetingTypeBadge = `
                            <div class="badge-meeting online">
                                <i class="fas fa-video text-[9px]"></i>
                                <span>Online</span>
                            </div>
                        `;
                        locationInfo = `
                            <div class="flex items-center gap-1 text-[10px] text-blue-600">
                                <i class="fas fa-link"></i>
                                <span>Link tersedia</span>
                            </div>
                        `;
                    } else {
                        meetingTypeBadge = `
                            <div class="badge-meeting offline">
                                <i class="fas fa-map-marker-alt text-[9px]"></i>
                                <span>Offline</span>
                            </div>
                        `;
                        if (hasLocation) {
                            locationInfo = `
                                <div class="flex items-center gap-1 text-[10px] text-gray-600">
                                    <i class="fas fa-map-pin"></i>
                                    <span class="truncate max-w-[150px]">${location}</span>
                                </div>
                            `;
                        }
                    }

                    return `
                        <a href="/workspace/${workspaceId}/jadwal/${event.id}"
                            class="schedule-card block bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer border-l-4 ${borderColor} border-t border-r border-b border-gray-200 hover:border-gray-300">
                            <div class="p-4">
                                <div class="flex justify-between items-start gap-3">
                                    <div class="flex items-start space-x-3 flex-1 min-w-0">
                                        <!-- Avatar -->
                                        <div class="relative flex-shrink-0">
                                            <img src="${creatorAvatar}"
                                                 alt="${creatorFirstName}"
                                                 class="rounded-full w-10 h-10 object-cover border-2 ${avatarRing}">
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <!-- Title Row with Private Badge -->
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <h3 class="font-semibold text-gray-900 text-sm leading-tight flex-1 line-clamp-1">
                                                    ${event.title || 'Untitled'}
                                                </h3>
                                                ${isPrivate ? `<span class="badge-private"><i class="fas fa-lock text-[8px]"></i> RAHASIA</span>` : ''}
                                            </div>

                                            <!-- Time -->
                                            <div class="flex items-center gap-1.5 text-xs text-gray-500 mb-2">
                                                <i class="far fa-clock text-[10px]"></i>
                                                <span>${timeDisplay}</span>
                                            </div>

                                            <!-- Meeting Info -->
                                            <div class="flex flex-wrap items-center gap-2">
                                                ${meetingTypeBadge}
                                                ${locationInfo}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Comments Counter -->
                                    ${commentsCount > 0 ? `
                                        <div class="flex-shrink-0">
                                            <div class="bg-amber-400 text-gray-900 text-xs font-bold rounded-lg w-7 h-7 flex items-center justify-center shadow-sm">
                                                ${commentsCount}
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </a>
                    `;
                } catch (e) {
                    console.error('Error rendering schedule card:', e);
                    return '';
                }
            }

            function formatTimeDisplay(startDate, endDate, event) {
                const startDateStr = event.extendedProps?.start_date || startDate.toISOString().split('T')[0];
                const endDateStr = event.extendedProps?.end_date || endDate.toISOString().split('T')[0];
                const isMultiDay = startDateStr !== endDateStr;

                if (isMultiDay) {
                    return `${startDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })} - ${endDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}`;
                } else {
                    const startTime = startDate.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const endTime = endDate.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    return `${startTime} - ${endTime} WIB`;
                }
            }

            function showErrorMessage(message) {
                const scheduleList = document.getElementById('scheduleList');
                scheduleList.innerHTML = `
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
                            <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                        </div>
                        <p class="text-sm font-semibold text-gray-700 mb-1">Gagal Memuat Jadwal</p>
                        <p class="text-xs text-gray-500">${message}</p>
                    </div>
                `;
            }
        });
    </script>
@endsection
