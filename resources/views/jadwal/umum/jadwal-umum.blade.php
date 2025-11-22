@extends('layouts.app')

@section('title', 'Jadwal Umum')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/jadwal.css'])

    <div class="bg-[#f3f6fc] min-h-screen">
        <div class="card bg-white rounded-[8px] shadow-xl flex flex-col gap-5 p-6 m-6 mx-10 responsive-container">
            <!-- Bagian atas: kalender + notulen sejajar -->
            <div class="flex flex-row gap-5 items-center justify-center responsive-top">
                <!-- Card kalender -->
                <div
                    class="card bg-white rounded-[8px] shadow-xl p-4 flex flex-col items-center justify-center w-full max-w-lg h-full calendar-card">
                    <div id="calendar" class="w-full h-full"></div>
                </div>
                <div class="flex flex-col right-section">
                    {{-- Button Buat Jadwal - HANYA TAMPIL JIKA ADA PERMISSION --}}
                    @if (isset($canCreateSchedule) && $canCreateSchedule)
                        <a href="{{ route('jadwal-umum.buat') }}"
                            class="bg-[#225ad6] rounded-[8px] shadow-xl flex items-center justify-center p-5 w-[400px] h-[40px] text-[#ffffff] font-semibold hover:bg-[#1a46a0] transition mb-4 buat-jadwal-btn">
                            Buat Jadwal Umum
                        </a>
                    @else
                        <div
                            class="bg-gray-100 rounded-[8px] shadow-xl flex items-center justify-center p-5 w-[400px] h-[40px] text-gray-500 font-semibold mb-4 buat-jadwal-btn cursor-not-allowed">
                            <i class="fas fa-lock mr-2"></i> Anda tidak dapat membuat jadwal
                        </div>
                    @endif

                    <div class="card bg-[#bbcff9] rounded-[8px] shadow-xl flex flex-col items-center justify-center p-6 w-[400px] h-[300px] notulen-card cursor-pointer hover:bg-[#a5bef5] transition"
                        onclick="window.location.href='{{ route('notulensi-umum') }}'">
                        <img src="{{ asset('images/icons/Notulen.png') }}" alt="Notulen Icon">
                        <h1 class="text-2xl text-[#102a63] mb-2 font-bold font-inter">Notulensi Rapat</h1>
                        <p class="text-[#102a63] text-center text-sm font-medium font-inter">
                            Klik untuk melihat catatan rapat terakhir
                        </p>
                    </div>
                </div>
            </div>

            <!-- Schedule List -->
            <div class="flex justify-center">
                <div class="w-[930px] flex flex-col gap-3 schedule-list" id="scheduleList">
                    <div class="text-center text-gray-500 py-8" id="loadingSchedule">
                        <i class="fas fa-spinner fa-spin text-2xl"></i>
                        <p class="mt-2">Memuat jadwal...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    confirmButtonText: 'OK',
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
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'OK'
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
            initCompanyCalendar();
        });

        function initCompanyCalendar() {
            const calendarEl = document.getElementById('calendar');

            if (!calendarEl) {
                console.error('Calendar element not found!');
                return;
            }

            let calendar = null;
            let allEvents = [];
            let eventCountByDate = {};

            console.log('🚀 Initializing company calendar');

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

                events: function(info, successCallback, failureCallback) {
                    const startDate = info.start.toISOString().split('T')[0] + ' 00:00:00';
                    const endDate = info.end.toISOString().split('T')[0] + ' 23:59:59';
                    const url =
                        `/jadwal-umum/events?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;

                    console.log('📡 Fetching company events from:', url);

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
                            console.log('✅ Events received:', data);

                            if (!Array.isArray(data)) {
                                console.warn('⚠️ Data is not an array:', data);
                                data = [];
                            }

                            allEvents = data;
                            eventCountByDate = calculateEventCountByDate(data);
                            console.log('📊 Event count by date:', eventCountByDate);

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

                    console.log('📅 Date clicked:', clickedDate, 'Events:', filteredEvents.length);
                    renderScheduleList(filteredEvents);
                },

                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    const eventUrl = `/jadwal-umum/${info.event.id}`;
                    console.log('🔗 Navigating to:', eventUrl);
                    window.location.href = eventUrl;
                },

                datesSet: function(info) {
                    setTimeout(() => renderDayMarkers(eventCountByDate), 150);
                }
            });

            calendar.render();
            console.log('✅ Calendar rendered');
        }

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
                    if (frame) {
                        if (!frame.querySelector('.day-marker')) {
                            const marker = document.createElement('div');
                            marker.classList.add('day-marker');
                            marker.textContent = eventCount;
                            frame.style.position = 'relative';
                            frame.appendChild(marker);
                        }
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
            const loading = document.getElementById('loadingSchedule');

            if (loading) loading.remove();

            console.log('📋 Rendering', events.length, 'events');

            if (!events || events.length === 0) {
                scheduleList.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-calendar-times text-4xl mb-2"></i>
                <p>Tidak ada jadwal untuk ditampilkan</p>
            </div>
        `;
                return;
            }

            const groupedEvents = {};
            events.forEach(event => {
                try {
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
                } catch (e) {
                    console.error('Error grouping event:', event, e);
                }
            });

            let html = '';

            Object.keys(groupedEvents).forEach((dateKey, index) => {
                if (index > 0) {
                    html += '<hr class="border-t border-gray-300 my-4">';
                }

                groupedEvents[dateKey].forEach(event => {
                    html += renderScheduleItem(event, dateKey);
                });
            });

            scheduleList.innerHTML = html;
        }

        function renderScheduleItem(event, dateKey) {
            try {
                const startDate = new Date(event.start);
                const endDate = new Date(event.end);

                const startDateStr = event.extendedProps?.start_date || startDate.toISOString().split('T')[0];
                const endDateStr = event.extendedProps?.end_date || endDate.toISOString().split('T')[0];
                const isMultiDay = startDateStr !== endDateStr;

                let timeDisplay = '';
                if (isMultiDay) {
                    const startFormatted = startDate.toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    }) + ' ' + startDate.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const endFormatted = endDate.toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    }) + ' ' + endDate.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    timeDisplay = `${startFormatted} - ${endFormatted}`;
                } else {
                    const startTime = startDate.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const endTime = endDate.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    timeDisplay = `${startTime} - ${endTime}`;
                }

                const hasMeetingLink = event.extendedProps?.meeting_link && event.extendedProps.meeting_link.trim() !==
                    '';
                const iconHtml = hasMeetingLink ? '<i class="fas fa-video text-gray-700 mr-2"></i>' : '';

                const bgColor = event.extendedProps?.is_creator ? 'bg-[#bbcff9]' : 'bg-[#E9EFFD]';

                const creatorAvatar = event.extendedProps?.creator_avatar || '/images/default-avatar.png';
                const creatorName = event.extendedProps?.creator_name || 'Unknown';

                const commentsCount = event.extendedProps?.comments_count || 0;

                return `
            <a href="/jadwal-umum/${event.id}"
                class="${bgColor} rounded-lg shadow-md flex items-center justify-between p-4 hover:shadow-lg transition schedule-item mb-3">

                <div class="flex flex-col items-start w-[140px] date-section">
                    <span class="font-semibold text-[14px] mx-auto">${dateKey.split(',')[0]}</span>
                    <span class="font-semibold text-[14px]">${dateKey.split(',')[1]?.trim() || ''}</span>
                </div>

                <div class="flex flex-col flex-1 px-4 content-section">
                    <div class="flex items-center gap-2 mb-2">
                        ${iconHtml}
                        <span class="font-semibold text-[#090909] text-base">${event.title || 'Untitled'}</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <img src="${creatorAvatar}"
                             alt="${creatorName}"
                             title="${creatorName}"
                             class="w-6 h-6 rounded-full border-2 border-white object-cover">
                        <span class="text-sm font-semibold text-[#102A63]">${timeDisplay}</span>
                    </div>
                </div>

                ${commentsCount > 0 ? `
                    <div class="badge-section">
                        <span class="bg-yellow-400 text-gray-700 text-xs font-bold rounded-full w-7 h-7 flex items-center justify-center shadow-sm">
                            ${commentsCount}
                        </span>
                    </div>
                    ` : ''}
            </a>
        `;
            } catch (e) {
                console.error('Error rendering schedule item:', event, e);
                return '';
            }
        }

        function showErrorMessage(message) {
            const scheduleList = document.getElementById('scheduleList');
            if (scheduleList) {
                scheduleList.innerHTML = `
            <div class="text-center text-red-500 py-8">
                <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
                <p class="font-semibold">Gagal memuat jadwal</p>
                <p class="text-sm mt-2">${message}</p>
            </div>
        `;
            }
        }
    </script>
@endsection
