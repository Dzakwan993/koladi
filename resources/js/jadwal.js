/**
 * Jadwal Calendar Module
 * File: resources/js/jadwal.js
 *
 * Penggunaan: initJadwalCalendar(workspaceId)
 */

function initJadwalCalendar(workspaceId) {
    const calendarEl = document.getElementById('calendar');

    if (!calendarEl) {
        console.error('Calendar element not found!');
        return;
    }

    if (!workspaceId) {
        console.error('Workspace ID is required!');
        return;
    }

    let calendar = null;
    let allEvents = [];
    let eventCountByDate = {};

    console.log('🚀 Initializing calendar for workspace:', workspaceId);

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

        events: function (info, successCallback, failureCallback) {
            const startDate = info.start.toISOString().split('T')[0] + ' 00:00:00';
            const endDate = info.end.toISOString().split('T')[0] + ' 23:59:59';
            const url = `/workspace/${workspaceId}/calendar/events?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;

            console.log('📡 Fetching events from:', url);

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

                    // Hitung jumlah event per tanggal
                    eventCountByDate = calculateEventCountByDate(data);
                    console.log('📊 Event count by date:', eventCountByDate);

                    successCallback(data);
                    renderScheduleList(data, workspaceId);

                    // Render markers setelah calendar dirender
                    setTimeout(() => renderDayMarkers(eventCountByDate), 150);
                })
                .catch(error => {
                    console.error('❌ Error loading events:', error);
                    failureCallback(error);
                    showErrorMessage(error.message);
                });
        },

        dateClick: function (info) {
            // Hapus selected class dari semua tanggal
            document.querySelectorAll('.fc-day-selected')
                .forEach(el => el.classList.remove('fc-day-selected'));
            info.dayEl.classList.add('fc-day-selected');

            const clickedDate = info.dateStr;
            const filteredEvents = filterEventsByDate(allEvents, clickedDate);

            console.log('📅 Date clicked:', clickedDate, 'Events:', filteredEvents.length);
            renderScheduleList(filteredEvents, workspaceId);
        },

        eventClick: function (info) {
            info.jsEvent.preventDefault();
            const eventUrl = `/workspace/${workspaceId}/jadwal/${info.event.id}`;
            console.log('🔗 Navigating to:', eventUrl);
            window.location.href = eventUrl;
        },

        datesSet: function (info) {
            // Re-render markers ketika navigasi bulan
            setTimeout(() => renderDayMarkers(eventCountByDate), 150);
        }
    });

    calendar.render();
    console.log('✅ Calendar rendered');
}

/**
 * Hitung jumlah event per tanggal (untuk buletan kuning di calendar)
 */
function calculateEventCountByDate(events) {
    const countByDate = {};

    events.forEach(event => {
        try {
            const startDate = event.extendedProps?.start_date || event.start.split('T')[0];
            const endDate = event.extendedProps?.end_date || event.end.split('T')[0];

            // Untuk event multi-day, hitung setiap tanggal
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

/**
 * Render buletan kuning di calendar untuk tanggal yang punya jadwal
 */
function renderDayMarkers(eventCountByDate) {
    // Hapus semua marker lama
    document.querySelectorAll('.day-marker').forEach(el => el.remove());

    // Ambil semua cell tanggal yang terlihat
    const dayCells = document.querySelectorAll('.fc-daygrid-day');

    dayCells.forEach(dayCell => {
        const dateAttr = dayCell.getAttribute('data-date');
        if (!dateAttr) return;

        const eventCount = eventCountByDate[dateAttr];
        if (eventCount && eventCount > 0) {
            const frame = dayCell.querySelector('.fc-daygrid-day-frame');
            if (frame) {
                // Cek apakah marker sudah ada
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

/**
 * Filter events berdasarkan tanggal
 */
function filterEventsByDate(events, dateStr) {
    return events.filter(event => {
        const startDate = event.extendedProps?.start_date || event.start.split('T')[0];
        const endDate = event.extendedProps?.end_date || event.end.split('T')[0];
        return dateStr >= startDate && dateStr <= endDate;
    });
}

/**
 * Render schedule list dengan badge komentar
 */
function renderScheduleList(events, workspaceId) {
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

    // Group events by date
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
            html += renderScheduleItem(event, workspaceId, dateKey);
        });
    });

    scheduleList.innerHTML = html;
}

/**
 * Render single schedule item dengan badge komentar (buletan kuning)
 */
function renderScheduleItem(event, workspaceId, dateKey) {
    try {
        const startDate = new Date(event.start);
        const endDate = new Date(event.end);

        const startDateStr = event.extendedProps?.start_date || startDate.toISOString().split('T')[0];
        const endDateStr = event.extendedProps?.end_date || endDate.toISOString().split('T')[0];
        const isMultiDay = startDateStr !== endDateStr;

        // Format waktu
        let timeDisplay = '';
        if (isMultiDay) {
            const startFormatted = startDate.toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric'
            }) + ' ' + startDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            const endFormatted = endDate.toLocaleDateString('id-ID', {
                day: 'numeric', month: 'short', year: 'numeric'
            }) + ' ' + endDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            timeDisplay = `${startFormatted} - ${endFormatted}`;
        } else {
            const startTime = startDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            const endTime = endDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            timeDisplay = `${startTime} - ${endTime}`;
        }

        // Icon untuk online meeting
        const hasMeetingLink = event.extendedProps?.meeting_link && event.extendedProps.meeting_link.trim() !== '';
        const iconHtml = hasMeetingLink ? '<i class="fas fa-video text-gray-700 mr-2"></i>' : '';

        // Warna background
        const bgColor = event.extendedProps?.is_creator ? 'bg-[#bbcff9]' : 'bg-[#E9EFFD]';

        // Creator info
        const creatorAvatar = event.extendedProps?.creator_avatar || '/images/default-avatar.png';
        const creatorName = event.extendedProps?.creator_name || 'Unknown';

        // ✅ PENTING: Jumlah KOMENTAR untuk badge kuning (bukan participants)
        const commentsCount = event.extendedProps?.comments_count || 0;

        return `
            <a href="/workspace/${workspaceId}/jadwal/${event.id}"
                class="${bgColor} rounded-lg shadow-md flex items-center justify-between p-4 hover:shadow-lg transition schedule-item mb-3">

                <div class="flex flex-col items-start w-[140px] date-section">
                    <span class="font-semibold text-[14px]">${dateKey.split(',')[0]}</span>
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

                <!-- ✅ Badge Komentar (Buletan Kuning) - Hanya muncul jika ada komentar -->
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

/**
 * Show error message
 */
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

// Export untuk penggunaan global
window.initJadwalCalendar = initJadwalCalendar;
