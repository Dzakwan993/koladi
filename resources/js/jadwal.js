document.addEventListener('DOMContentLoaded', function () {
    const workspaceId = '{{ $workspaceId }}';
    let calendarEl = document.getElementById('calendar');
    let calendar = null;
    let allEvents = [];

    console.log('Initializing calendar for workspace:', workspaceId);

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

        // ✅ FIXED: Load events via AJAX dengan format datetime yang benar
        events: function (info, successCallback, failureCallback) {
            // Format tanggal untuk PostgreSQL: YYYY-MM-DD HH:MM:SS
            const startDate = info.start.toISOString().split('T')[0] + ' 00:00:00';
            const endDate = info.end.toISOString().split('T')[0] + ' 23:59:59';

            const url = `/workspace/${workspaceId}/calendar/events?start=${encodeURIComponent(startDate)}&end=${encodeURIComponent(endDate)}`;

            console.log('Fetching events from:', url);

            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);

                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Error response:', text);
                            throw new Error(`Server error: ${response.status}`);
                        });
                    }

                    return response.json();
                })
                .then(data => {
                    console.log('Events received:', data);

                    // Pastikan data adalah array
                    if (!Array.isArray(data)) {
                        console.warn('Data is not an array:', data);
                        data = [];
                    }

                    allEvents = data;
                    successCallback(data);

                    // Update schedule list
                    renderScheduleList(data);
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                    failureCallback(error);

                    // Tampilkan pesan error
                    const scheduleList = document.getElementById('scheduleList');
                    if (scheduleList) {
                        scheduleList.innerHTML = `
                            <div class="text-center text-red-500 py-8">
                                <i class="fas fa-exclamation-triangle text-4xl mb-2"></i>
                                <p class="font-semibold">Gagal memuat jadwal</p>
                                <p class="text-sm mt-2">${error.message}</p>
                            </div>
                        `;
                    }
                });
        },

        // ✅ Handle click pada tanggal
        dateClick: function (info) {
            document.querySelectorAll('.fc-day-selected')
                .forEach(el => el.classList.remove('fc-day-selected'));
            info.dayEl.classList.add('fc-day-selected');

            // Filter schedule list berdasarkan tanggal yang diklik
            const clickedDate = info.dateStr;
            const filteredEvents = allEvents.filter(event => {
                const eventDate = event.start.split('T')[0];
                return eventDate === clickedDate;
            });

            console.log('Date clicked:', clickedDate, 'Filtered events:', filteredEvents.length);
            renderScheduleList(filteredEvents);
        },

        // ✅ Handle click pada event
        eventClick: function (info) {
            info.jsEvent.preventDefault();
            const eventUrl = `/workspace/${workspaceId}/jadwal/${info.event.id}`;
            console.log('Event clicked, navigating to:', eventUrl);
            window.location.href = eventUrl;
        },

        // ✅ Custom marker untuk tanggal dengan events
        dayCellDidMount: function (info) {
            setTimeout(() => {
                const eventsOnDate = calendar.getEvents().filter(event => {
                    const eventDate = new Date(event.start);
                    return eventDate.toDateString() === info.date.toDateString();
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
    console.log('Calendar rendered');

    // ✅ Function to render schedule list
    function renderScheduleList(events) {
        const scheduleList = document.getElementById('scheduleList');
        const loading = document.getElementById('loadingSchedule');

        if (loading) {
            loading.remove();
        }

        console.log('Rendering schedule list with', events.length, 'events');

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
                console.error('Error processing event:', event, e);
            }
        });

        let html = '';

        Object.keys(groupedEvents).forEach((dateKey, index) => {
            if (index > 0) {
                html += '<hr class="border-t border-gray-300 my-4">';
            }

            groupedEvents[dateKey].forEach(event => {
                try {
                    const startTime = new Date(event.start).toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const endTime = new Date(event.end).toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    // ✅ Icon berdasarkan jenis meeting
                    const isOnline = event.extendedProps?.is_online || false;
                    const hasMeetingLink = event.extendedProps?.meeting_link && event.extendedProps.meeting_link.trim() !== '';

                    // Gunakan icon camera jika ada meeting link
                    const icon = (isOnline && hasMeetingLink) ?
                        '<i class="fas fa-video text-blue-600 w-6 text-center text-xl"></i>' :
                        '<i class="fas fa-users text-gray-600 w-6 text-center text-xl"></i>';

                    const bgColor = event.extendedProps?.is_creator ?
                        'bg-[#bbcff9]' :
                        'bg-[#d4e4ff]';

                    // ✅ Get participants untuk avatar display
                    const participants = event.extendedProps?.participants || [];
                    const participantsCount = event.extendedProps?.participants_count || 0;
                    const remainingCount = Math.max(0, participantsCount - participants.length);

                    // Build avatar HTML
                    let avatarsHtml = '<div class="flex items-center -space-x-2">';

                    participants.forEach((participant, index) => {
                        if (index < 3) { // Hanya tampilkan 3 avatar pertama
                            avatarsHtml += `
                                <img src="${participant.avatar}"
                                     alt="${participant.name}"
                                     title="${participant.name}"
                                     class="w-8 h-8 rounded-full border-2 border-white object-cover">
                            `;
                        }
                    });

                    // Jika ada lebih dari 3 participants, tampilkan badge +N
                    if (remainingCount > 0) {
                        avatarsHtml += `
                            <div class="w-8 h-8 rounded-full bg-gray-300 border-2 border-white flex items-center justify-center">
                                <span class="text-xs font-semibold text-gray-700">+${remainingCount}</span>
                            </div>
                        `;
                    }

                    avatarsHtml += '</div>';

                    html += `
                        <a href="/workspace/${workspaceId}/jadwal/${event.id}"
                            class="${bgColor} rounded-lg shadow-md flex items-center justify-between p-4 hover:shadow-lg transition schedule-item">

                            <div class="flex flex-col items-start w-[120px] date-section">
                                <span class="font-semibold text-[14px]">${dateKey.split(',')[0]}</span>
                                <span class="font-semibold text-[14px]">${dateKey.split(',')[1]?.trim()}</span>
                            </div>

                            <div class="flex flex-col flex-1 px-4 content-section">
                                <div class="flex items-center gap-2 mb-2">
                                    ${icon}
                                    <span class="font-semibold text-[#090909] text-base">${event.title}</span>
                                    ${hasMeetingLink ? '<i class="fas fa-external-link-alt text-blue-500 text-xs ml-2" title="Meeting Online"></i>' : ''}
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-clock text-[#102a63] w-6 text-center"></i>
                                        <span class="text-sm font-medium text-[#102a63]">${startTime} - ${endTime}</span>
                                    </div>

                                    ${avatarsHtml}
                                </div>
                            </div>

                            <div class="badge-section">
                                <span class="bg-yellow-400 text-[#6B7280] text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-sm">
                                    ${participantsCount}
                                </span>
                            </div>
                        </a>
                    `;
                } catch (e) {
                    console.error('Error rendering event:', event, e);
                }
            });
        });

        scheduleList.innerHTML = html;
    }
});
