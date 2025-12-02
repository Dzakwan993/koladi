
document.addEventListener('DOMContentLoaded', function () {
    let calendarEl = document.getElementById('calendar');
    let calendar = null;
    let allEvents = [];
    let eventCountByDate = {};

    console.log('🚀 Initializing Dashboard Calendar...');

    // ✅ Helper function untuk ambil nama depan
    function getFirstName(fullName) {
        if (!fullName) return 'User';
        return fullName.trim().split(' ')[0];
    }

    // Initialize FullCalendar dengan Bahasa Indonesia
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
        dayHeaderContent: function (arg) {
            const dayNames = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
            return dayNames[arg.dow];
        },

        events: function (info, successCallback, failureCallback) {
            const startDate = info.start.toISOString().split('T')[0] + ' 00:00:00';
            const endDate = info.end.toISOString().split('T')[0] + ' 23:59:59';
            const url =
                `/jadwal-umum/events?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;

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

                    const today = new Date().toISOString().split('T')[0];
                    loadSchedulesForDate(today);

                    setTimeout(() => {
                        renderDayMarkers(eventCountByDate);
                        attachHoverTooltips();
                    }, 150);
                })
                .catch(error => {
                    console.error('❌ Error loading events:', error);
                    failureCallback(error);
                    showErrorMessage(error.message);
                });
        },

        dateClick: function (info) {
            document.querySelectorAll('.fc-day-selected')
                .forEach(el => el.classList.remove('fc-day-selected'));
            info.dayEl.classList.add('fc-day-selected');
            loadSchedulesForDate(info.dateStr);
        },

        eventClick: function (info) {
            info.jsEvent.preventDefault();
            window.location.href = `/jadwal-umum/${info.event.id}`;
        },

        datesSet: function (info) {
            setTimeout(() => {
                renderDayMarkers(eventCountByDate);
                attachHoverTooltips();
            }, 150);
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

    // ✅ FUNGSI TOOLTIP dengan tanggal lebih BOLD
    function attachHoverTooltips() {
        const dayCells = document.querySelectorAll('.fc-daygrid-day');

        dayCells.forEach(dayCell => {
            const dateAttr = dayCell.getAttribute('data-date');
            if (!dateAttr) return;

            const eventsOnThisDate = allEvents.filter(event => {
                const startDate = event.extendedProps?.start_date || event.start.split('T')[
                    0];
                const endDate = event.extendedProps?.end_date || event.end.split('T')[0];
                return dateAttr >= startDate && dateAttr <= endDate;
            });

            if (eventsOnThisDate.length === 0) return;

            dayCell.addEventListener('mouseenter', function (e) {
                const oldTooltip = document.querySelector('.calendar-tooltip');
                if (oldTooltip) oldTooltip.remove();

                const tooltip = document.createElement('div');
                tooltip.classList.add('calendar-tooltip');

                // ✅ Tanggal dibuat lebih BOLD (font-weight: 800)
                let tooltipContent = `
                    <div style="font-weight: 800; font-size: 13px; margin-bottom: 10px; color: #1E1E1E; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">
                        ${new Date(dateAttr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}
                    </div>
                `;

                eventsOnThisDate.forEach((event, index) => {
                    const startDate = new Date(event.start);
                    const endDate = new Date(event.end);
                    const startDateStr = event.extendedProps?.start_date ||
                        startDate.toISOString().split('T')[0];
                    const endDateStr = event.extendedProps?.end_date || endDate
                        .toISOString().split('T')[0];
                    const isMultiDay = startDateStr !== endDateStr;

                    let timeDisplay = '';
                    if (isMultiDay) {
                        timeDisplay =
                            `${startDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })} - ${endDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}`;
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

                    const isOnline = event.extendedProps?.is_online === true ||
                        event.extendedProps?.is_online === 1;
                    const meetingIcon = isOnline ?
                        '<i class="fas fa-video" style="color: #3b82f6; margin-right: 4px;"></i>' :
                        '';

                    tooltipContent += `
                        <div style="padding: 8px 0; ${index < eventsOnThisDate.length - 1 ? 'border-bottom: 1px solid #f3f4f6;' : ''}">
                            <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px; display: flex; align-items: center; gap: 4px;">
                                ${meetingIcon}
                                <span style="flex: 1;">${event.title || 'Untitled'}</span>
                            </div>
                            <div style="font-size: 11px; color: #6b7280; display: flex; align-items: center; gap: 4px;">
                                <i class="far fa-clock" style="color: #9ca3af;"></i>
                                ${timeDisplay}
                            </div>
                            ${event.extendedProps?.location ? `
                                                    <div style="font-size: 11px; color: #6b7280; display: flex; align-items: center; gap: 4px; margin-top: 2px;">
                                                        <i class="fas fa-map-marker-alt" style="color: #ef4444;"></i>
                                                        ${event.extendedProps.location}
                                                    </div>
                                                ` : ''}
                        </div>
                    `;
                });

                tooltip.innerHTML = tooltipContent;

                document.body.appendChild(tooltip);
                const rect = dayCell.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();

                let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
                let top = rect.bottom + 10;

                if (left < 10) left = 10;
                if (left + tooltipRect.width > window.innerWidth - 10) {
                    left = window.innerWidth - tooltipRect.width - 10;
                }
                if (top + tooltipRect.height > window.innerHeight - 10) {
                    top = rect.top - tooltipRect.height - 10;
                }

                tooltip.style.left = `${left}px`;
                tooltip.style.top = `${top}px`;
            });

            dayCell.addEventListener('mouseleave', function () {
                const tooltip = document.querySelector('.calendar-tooltip');
                if (tooltip) {
                    setTimeout(() => tooltip.remove(), 100);
                }
            });
        });
    }

    function loadSchedulesForDate(dateStr) {
        const filteredEvents = allEvents.filter(event => {
            const startDate = event.extendedProps?.start_date || event.start.split('T')[0];
            const endDate = event.extendedProps?.end_date || event.end.split('T')[0];
            return dateStr >= startDate && dateStr <= endDate;
        });

        const dateObj = new Date(dateStr);
        const today = new Date().toISOString().split('T')[0];
        const titleEl = document.getElementById('scheduleTitle');

        if (dateStr === today) {
            titleEl.innerHTML = '<i class="fas fa-list-check text-blue-600 mr-2"></i>Jadwal Hari Ini';
        } else {
            titleEl.innerHTML = `<i class="fas fa-calendar-day text-purple-600 mr-2"></i>${dateObj.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            })}`;
        }

        renderScheduleCards(filteredEvents, dateStr);
    }

    function renderScheduleCards(events, dateStr) {
        const container = document.querySelector('.schedule-cards-container');
        if (!container) return;

        if (!events || events.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-calendar-times text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 mb-1">Tidak Ada Jadwal</p>
                    <p class="text-xs text-gray-500">Belum ada jadwal untuk tanggal ini</p>
                </div>
            `;
            return;
        }

        let html = '';
        events.forEach(event => {
            try {
                const startDate = new Date(event.start);
                const endDate = new Date(event.end);
                const timeDisplay = formatTimeDisplay(startDate, endDate, event);
                const bgColor = event.extendedProps?.is_creator ? 'from-[#bbcff9] to-[#a8bef5]' :
                    'from-[#E9EFFD] to-[#dce6fc]';
                const creatorAvatar = event.extendedProps?.creator_avatar ||
                    '/images/default-avatar.png';

                // ✅ Ambil nama depan saja
                const creatorFullName = event.extendedProps?.creator_name || 'User';
                const creatorFirstName = getFirstName(creatorFullName);

                const isOnlineMeeting = event.extendedProps?.is_online === true || event
                    .extendedProps?.is_online === 1;
                const meetingLink = event.extendedProps?.meeting_link;
                const hasMeetingLink = meetingLink && meetingLink.trim() !== '' && meetingLink !==
                    'null';

                const meetingIcon = (isOnlineMeeting && hasMeetingLink) ?
                    '<i class="fas fa-video text-blue-600 mr-1.5"></i>' : '';

                const commentsCount = event.extendedProps?.comments_count || 0;

                html += `
                    <a href="/jadwal-umum/${event.id}"
                        class="group bg-gradient-to-br ${bgColor} rounded-xl shadow-md p-4 hover:shadow-2xl transition-all duration-300 cursor-pointer block border border-blue-100 hover:border-blue-400">
                        <div class="flex justify-between items-start gap-3">
                            <div class="flex items-start space-x-3 flex-1 min-w-0">
                                <div class="relative">
                                    <img src="${creatorAvatar}"
                                         alt="${creatorFirstName}"
                                         class="rounded-full w-11 h-11 object-cover border-3 border-white shadow-lg bg-gray-100 flex-shrink-0 ring-2 ring-blue-200">
                                    ${meetingIcon ? '<div class="absolute -bottom-1 -right-1 bg-blue-600 rounded-full w-5 h-5 flex items-center justify-center shadow-md"><i class="fas fa-video text-white text-[10px]"></i></div>' : ''}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start gap-2 mb-2">
                                        <span class="font-bold text-[#090909] text-sm leading-tight flex-1 group-hover:text-blue-700 transition-colors line-clamp-2">
                                            ${event.title || 'Untitled'}
                                        </span>
                                    </div>

                                    <div class="flex flex-col gap-1.5">
                                        <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                            <i class="far fa-clock text-blue-500"></i>
                                            <span class="font-medium">${timeDisplay}</span>
                                        </div>
                                        ${event.extendedProps?.location ? `
                                                                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                                                    <i class="fas fa-map-marker-alt text-red-500"></i>
                                                                    <span class="truncate">${event.extendedProps.location}</span>
                                                                </div>
                                                            ` : ''}
                                    </div>
                                </div>
                            </div>

                            ${commentsCount > 0 ? `
                                                    <div class="flex-shrink-0">
                                                        <div class="relative">
                                                            <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 text-gray-800 text-xs font-bold rounded-lg w-8 h-8 flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                                                ${commentsCount}
                                                            </div>
                                                            <div class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></div>
                                                        </div>
                                                    </div>
                                                ` : ''}
                        </div>
                    </a>
                `;
            } catch (e) {
                console.error('Error rendering schedule card:', e);
            }
        });

        container.innerHTML = html;
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
        const container = document.querySelector('.schedule-cards-container');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
                        <i class="fas fa-exclamation-triangle text-3xl text-red-500"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 mb-1">Gagal Memuat Jadwal</p>
                    <p class="text-xs text-gray-500">${message}</p>
                </div>
            `;
        }
    }
});

