@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        function getShortName($fullName)
        {
            if (!$fullName) {
                return 'User';
            }
            $words = explode(' ', trim($fullName));
            return implode(' ', array_slice($words, 0, 2));
        }
    @endphp

    <div class="min-h-screen bg-gradient-to-br from-[#f3f6fc] to-[#e9effd] font-[Inter,sans-serif]">
        <!-- ✅ Improved padding with better mobile spacing -->
        <div class="px-4 py-4 sm:px-6 sm:py-6 lg:px-8 lg:py-8">
            <div class="max-w-7xl mx-auto">

                {{-- Hero Header - Improved Responsive --}}
                <div class="mb-6 pb-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold text-[#1E1E1E] mb-2 truncate">
                                Selamat datang, {{ getShortName(Auth::user()->full_name) }} 👋
                            </h1>
                            <p class="text-xs sm:text-sm md:text-base text-[#6B7280]">
                                Kelola pekerjaan, jadwal, dan komunikasi tim Anda — semua dalam satu layar.
                            </p>
                        </div>
                        <!-- ✅ Improved button with better mobile layout + ID untuk onboarding -->
                        <a href="{{ url('/tambah-anggota') }}" role="button" aria-label="Tambah Anggota Baru"
                            id="tambah-anggota-btn"
                            class="inline-flex items-center justify-center gap-2 text-sm sm:text-base bg-gradient-to-r from-[#225AD6] to-[#1e40af] hover:from-[#1e40af] hover:to-[#225AD6] text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-[#225AD6] focus:ring-offset-2 whitespace-nowrap">
                            <i class="fas fa-user-plus" aria-hidden="true"></i>
                            <span class="hidden xs:inline">Tambah Anggota</span>
                            <span class="xs:hidden">Tambah Anggota</span>
                        </a>
                    </div>
                </div>

                {{-- ✅ Improved Grid - Better mobile stacking --}}
                <div class="grid grid-cols-1 xl:grid-cols-7 gap-4 sm:gap-5 lg:gap-6">

                    {{-- Left Column - Pengumuman (4/7 width on XL screens) --}}
                    <div class="xl:col-span-4 flex flex-col order-2 xl:order-1">
                        {{-- Header Pengumuman --}}
                        <div class="flex items-center justify-between mb-3 sm:mb-4">
                            <div class="flex items-center gap-2 sm:gap-3">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
                                    <i class="fas fa-bullhorn text-white text-base sm:text-lg" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <p class="text-sm sm:text-base lg:text-lg text-[#1E1E1E] font-bold">Pengumuman Terbaru
                                    </p>
                                    <p class="text-xs text-gray-500 hidden sm:block">Update terkini untuk Anda</p>
                                </div>
                            </div>
                        </div>

                        {{-- ✅ Container Pengumuman - Flexible height --}}
                        <div
                            class="bg-white rounded-2xl shadow-lg p-4 sm:p-5 lg:p-6 flex flex-col border border-gray-100 min-h-[400px] max-h-[600px] xl:h-[calc(100vh-280px)]">
                            <div class="overflow-y-auto flex-1 pr-2 py-2 custom-scrollbar">
                                <div class="space-y-3 sm:space-y-4 px-1">
                                    @forelse($pengumumans as $p)
                                        <div class="group bg-gradient-to-br from-[#E9EFFD] to-[#dce6fc] rounded-xl p-3 sm:p-4 shadow-sm hover:shadow-2xl transition-all duration-300 cursor-pointer border border-blue-100 hover:border-blue-400 focus-within:ring-2 focus-within:ring-blue-400"
                                            onclick="window.location.href='{{ route('pengumuman-perusahaan.show', ['company_id' => $company->id, 'id' => $p->id]) }}'"
                                            tabindex="0" role="article" aria-label="Pengumuman: {{ $p->title }}">

                                            {{-- Header dengan Avatar dan Tanggal --}}
                                            <div class="flex items-start justify-between mb-3 gap-2">
                                                <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                    <img src="{{ $p->creator->avatar_url }}"
                                                        alt="Avatar {{ $p->creator->full_name }}" loading="lazy"
                                                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-white shadow-md flex-shrink-0 ring-2 ring-blue-200">

                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-semibold text-xs sm:text-sm text-gray-800 truncate">
                                                            {{ $p->creator->full_name }}
                                                        </p>
                                                        <div class="flex items-center gap-1.5 sm:gap-2 mt-0.5">
                                                            @if ($p->is_private)
                                                                <i class="fas fa-lock text-gray-400 text-xs"
                                                                    aria-label="Pengumuman Privat"></i>
                                                            @endif
                                                            <span class="text-xs text-gray-500">
                                                                <i class="far fa-clock mr-1" aria-hidden="true"></i>
                                                                {{ $p->display_relative_time ?? $p->created_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Comment Badge --}}
                                                @if ($p->comments_count > 0)
                                                    <div class="flex-shrink-0">
                                                        <div class="relative">
                                                            <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 text-gray-800 text-xs font-bold rounded-lg w-8 h-8 sm:w-9 sm:h-9 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-300"
                                                                aria-label="{{ $p->comments_count }} komentar">
                                                                {{ $p->comments_count }}
                                                            </div>
                                                            <div class="absolute -top-1 -right-1 w-2 h-2 sm:w-2.5 sm:h-2.5 bg-red-500 rounded-full animate-pulse"
                                                                aria-hidden="true"></div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Title --}}
                                            <h3
                                                class="font-bold text-[#090909] text-sm sm:text-base mb-2 line-clamp-1 group-hover:text-blue-700 transition-colors">
                                                {{ $p->title }}
                                            </h3>

                                            {{-- Description --}}
                                            <p class="text-xs sm:text-sm text-gray-600 mb-3 line-clamp-2 leading-relaxed">
                                                {!! strip_tags($p->description) !!}
                                            </p>

                                            {{-- Footer dengan metadata --}}
                                            @if ($p->due_date || $p->auto_due)
                                                <div
                                                    class="flex flex-wrap items-center gap-1.5 sm:gap-2 pt-2 sm:pt-3 border-t border-blue-200/50">
                                                    <i class="fas fa-calendar-check text-blue-600 text-xs"
                                                        aria-hidden="true"></i>
                                                    @if ($p->due_date)
                                                        <span class="text-xs font-medium text-blue-700">
                                                            Tenggat:
                                                            {{ \Carbon\Carbon::parse($p->due_date)->translatedFormat('d M Y') }}
                                                        </span>
                                                    @endif
                                                    @if ($p->auto_due)
                                                        <span class="text-xs text-gray-600">
                                                            • Selesai:
                                                            {{ \Carbon\Carbon::parse($p->auto_due)->translatedFormat('d M Y') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-center py-12 sm:py-16">
                                            <div
                                                class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-4">
                                                <i class="fas fa-bullhorn text-3xl sm:text-4xl text-gray-400"
                                                    aria-hidden="true"></i>
                                            </div>
                                            <p class="text-sm sm:text-base font-semibold text-gray-700 mb-2">Belum Ada
                                                Pengumuman</p>
                                            <p class="text-xs sm:text-sm text-gray-500">Pengumuman terbaru akan muncul di
                                                sini</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column - Calendar & Jadwal (3/7 width on XL screens) --}}
                    <div class="xl:col-span-3 flex flex-col order-1 xl:order-2">
                        {{-- Header Jadwal --}}
                        <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
                            <div
                                class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
                                <i class="fas fa-calendar-alt text-white text-base sm:text-lg" aria-hidden="true"></i>
                            </div>
                            <div>
                                <p class="text-sm sm:text-base lg:text-lg text-[#1E1E1E] font-bold">Jadwal</p>
                                <p class="text-xs text-gray-500 hidden sm:block">Agenda Anda</p>
                            </div>
                        </div>

                        {{-- ✅ Calendar - Improved responsive height --}}
                        <div
                            class="bg-white rounded-2xl shadow-lg p-3 sm:p-4 lg:p-5 mb-4 sm:mb-5 border border-gray-100 h-[280px] sm:h-[320px] lg:h-[350px]">
                            <div id="calendar" class="w-full h-full" role="application" aria-label="Kalender Jadwal"></div>
                        </div>

                        {{-- ✅ Jadwal List - Better flexible sizing --}}
                        <div
                            class="bg-white rounded-2xl shadow-lg p-3 sm:p-4 lg:p-5 flex flex-col border border-gray-100 min-h-[200px] max-h-[400px] xl:flex-1">
                            <h3 class="font-bold text-[#1E1E1E] mb-3 text-sm sm:text-base flex items-center gap-2"
                                id="scheduleTitle">
                                <i class="fas fa-list-check text-blue-600" aria-hidden="true"></i>
                                Jadwal Hari Ini
                            </h3>

                            <div class="overflow-y-auto flex-1 pr-2 custom-scrollbar">
                                <div class="space-y-2 sm:space-y-3 schedule-cards-container">
                                    @forelse($todaySchedules as $schedule)
                                        <a href="{{ $schedule->schedule_type === 'company' ? route('jadwal-umum.show', $schedule->id) : route('calendar.show', ['workspaceId' => $schedule->workspace_id, 'id' => $schedule->id]) }}"
                                            class="group bg-gradient-to-br from-[#E9EFFD] to-[#dce6fc] rounded-xl p-3 sm:p-4 shadow-sm hover:shadow-2xl transition-all duration-300 cursor-pointer border border-blue-100 hover:border-blue-400 block">

                                            {{-- Header dengan Avatar & Badge Type --}}
                                            <div class="flex items-start justify-between mb-2 gap-2">
                                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                                    <img src="{{ $schedule->creator->avatar_url }}"
                                                        alt="{{ $schedule->creator->full_name }}"
                                                        class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-md flex-shrink-0">

                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-semibold text-sm text-gray-800 truncate">
                                                            {{ $schedule->creator->full_name }}
                                                        </p>
                                                        {{-- ✅ BADGE TYPE (Company/Workspace) --}}
                                                        <span
                                                            class="inline-block text-xs px-2 py-0.5 rounded-full mt-1 {{ $schedule->schedule_type === 'company' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                            {{ $schedule->schedule_label }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Comment Badge --}}
                                                @if ($schedule->comments_count > 0)
                                                    <div class="flex-shrink-0">
                                                        <div
                                                            class="bg-gradient-to-br from-yellow-400 to-yellow-500 text-gray-800 text-xs font-bold rounded-lg w-8 h-8 flex items-center justify-center shadow-md">
                                                            {{ $schedule->comments_count }}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Title --}}
                                            <h4
                                                class="font-bold text-[#090909] text-sm mb-2 line-clamp-1 group-hover:text-blue-700 transition-colors">
                                                {{ $schedule->title }}
                                            </h4>

                                            {{-- Time & Location --}}
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                                    <i class="far fa-clock text-blue-500"></i>
                                                    <span>{{ $schedule->start_datetime->format('H:i') }} -
                                                        {{ $schedule->end_datetime->format('H:i') }} WIB</span>
                                                </div>

                                                @if ($schedule->is_online_meeting)
                                                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                                        <i class="fas fa-video text-blue-600"></i>
                                                        <span>Online Meeting</span>
                                                    </div>
                                                @elseif($schedule->location)
                                                    <div class="flex items-center gap-1.5 text-xs text-gray-600">
                                                        <i class="fas fa-map-marker-alt text-red-500"></i>
                                                        <span class="truncate">{{ $schedule->location }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-center py-8 sm:py-12">
                                            <div
                                                class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-blue-50 mb-3 sm:mb-4">
                                                <i class="fas fa-calendar-times text-2xl sm:text-3xl text-gray-400"
                                                    aria-hidden="true"></i>
                                            </div>
                                            <p class="text-xs sm:text-sm font-semibold text-gray-700">Tidak Ada Jadwal Hari
                                                Ini</p>
                                            <p class="text-xs text-gray-500 mt-1">Jadwal Anda akan muncul di sini</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 🎯 CONDITIONAL ONBOARDING --}}
    @if (isset($showOnboarding) && $showOnboarding)
        @if ($onboardingType === 'member')
            {{-- ✅ ONBOARDING UNTUK MEMBER --}}
            @include('components.onboarding-member')
        @else
            {{-- ✅ ONBOARDING UNTUK FULL (SuperAdmin/Founder) --}}
            <div id="onboarding-overlay" class="hidden fixed inset-0 z-[9999]">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/30 transition-opacity duration-500"></div>

                <!-- Spotlight -->
                <div id="spotlight" class="absolute rounded-xl transition-all duration-500"
                    style="pointer-events: none;">
                    <div class="absolute inset-0 rounded-xl bg-blue-500/10 animate-ping"></div>
                </div>

                <!-- Tooltip Card -->
                <div id="onboarding-tooltip"
                    class="absolute bg-white rounded-2xl shadow-2xl p-6 w-[400px] max-w-[90vw] border-2 border-blue-500/30 transition-all duration-500"
                    style="z-index: 10001; opacity: 0; transform: translateY(20px);">

                    <div class="relative">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg ring-4 ring-blue-100 relative">
                                    <i class="fas fa-user-plus text-white text-lg"></i>
                                    <div
                                        class="absolute -top-1 -right-1 w-3 h-3 bg-green-400 rounded-full border-2 border-white animate-pulse">
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-1">Selamat Datang! 🎉</h3>
                                <p class="text-sm text-gray-600 leading-relaxed mb-1">
                                    <strong class="text-blue-600">Klik tombol "Tambah Anggota"</strong> untuk mengundang
                                    tim Anda!
                                </p>
                                <p class="text-xs text-gray-500 leading-relaxed mb-4">
                                    Anda akan diarahkan ke halaman undangan.
                                </p>

                                <div class="flex items-center gap-2 mb-4">
                                    <div class="flex-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"
                                            style="width: 25%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-500">1/4</span>
                                </div>

                                <button onclick="skipOnboarding()"
                                    class="w-full px-4 py-2 text-sm font-semibold text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-all duration-200">
                                    Lewati Tutorial
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="tooltip-arrow" class="absolute pointer-events-none"></div>
                </div>
            </div>
        @endif
    @endif

    {{-- FullCalendar CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    @vite(['resources/css/dashboard.css'])

    {{-- FullCalendar JS --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    @push('scripts')
        @vite('resources/js/dashboard.js')
        <script>
            // 🎯 ONBOARDING SCRIPT - ULTIMATE FIX
            document.addEventListener('DOMContentLoaded', function() {
                const shouldShowOnboarding = {{ isset($showOnboarding) && $showOnboarding ? 'true' : 'false' }};

                console.log('Should show onboarding:', shouldShowOnboarding);

                if (shouldShowOnboarding) {
                    setTimeout(() => {
                        showOnboardingStep1();
                    }, 800);
                }
            });

            function showOnboardingStep1() {
                const overlay = document.getElementById('onboarding-overlay');
                const button = document.getElementById('tambah-anggota-btn');
                const spotlight = document.getElementById('spotlight');
                const tooltip = document.getElementById('onboarding-tooltip');
                const arrow = document.getElementById('tooltip-arrow');

                if (!button) {
                    console.error('Tombol Tambah Anggota tidak ditemukan');
                    return;
                }

                // ✅ PENTING: Naikkan z-index button agar bisa diklik
                button.style.position = 'relative';
                button.style.zIndex = '10000';

                // Tampilkan overlay
                overlay.classList.remove('hidden');

                // Posisikan spotlight di button
                const rect = button.getBoundingClientRect();
                const padding = 8;

                spotlight.style.left = (rect.left - padding) + 'px';
                spotlight.style.top = (rect.top - padding) + 'px';
                spotlight.style.width = (rect.width + padding * 2) + 'px';
                spotlight.style.height = (rect.height + padding * 2) + 'px';
                spotlight.style.boxShadow = `
        0 0 0 9999px rgba(0, 0, 0, 0.3),
        0 0 0 ${padding + 3}px rgba(59, 130, 246, 0.6),
        0 0 40px 8px rgba(59, 130, 246, 0.4)
    `;
                spotlight.style.border = '3px solid rgba(59, 130, 246, 0.9)';
                spotlight.style.backgroundColor = 'transparent';
                spotlight.style.zIndex = '9998'; // Di bawah button

                // ✅ POSITIONING TOOLTIP
                const tooltipWidth = 400;
                const tooltipHeight = 280;
                const viewportHeight = window.innerHeight;
                const gap = 20;

                let tooltipTop;
                let arrowPosition;

                const spaceBelow = viewportHeight - rect.bottom;
                const spaceAbove = rect.top;

                if (spaceBelow >= tooltipHeight + gap) {
                    tooltipTop = rect.bottom + gap;
                    arrowPosition = 'top';
                } else if (spaceAbove >= tooltipHeight + gap) {
                    tooltipTop = rect.top - tooltipHeight - gap;
                    arrowPosition = 'bottom';
                } else {
                    tooltipTop = rect.bottom + gap;
                    arrowPosition = 'top';
                }

                let tooltipLeft = rect.left + (rect.width / 2) - (tooltipWidth / 2);

                const viewportWidth = window.innerWidth;
                if (tooltipLeft < 20) tooltipLeft = 20;
                if (tooltipLeft + tooltipWidth > viewportWidth - 20) {
                    tooltipLeft = viewportWidth - tooltipWidth - 20;
                }

                tooltip.style.left = tooltipLeft + 'px';
                tooltip.style.top = tooltipTop + 'px';

                // ✅ ARROW STYLING
                const arrowLeft = rect.left + (rect.width / 2) - tooltipLeft;
                arrow.style.left = arrowLeft + 'px';

                if (arrowPosition === 'top') {
                    arrow.style.top = '-12px';
                    arrow.style.bottom = 'auto';
                    arrow.style.width = '0';
                    arrow.style.height = '0';
                    arrow.style.borderLeft = '12px solid transparent';
                    arrow.style.borderRight = '12px solid transparent';
                    arrow.style.borderBottom = '12px solid white';
                    arrow.style.borderTop = 'none';
                    arrow.style.filter = 'drop-shadow(0 -2px 4px rgba(0,0,0,0.1))';
                    arrow.style.transform = 'translateX(-50%)';
                } else {
                    arrow.style.bottom = '-12px';
                    arrow.style.top = 'auto';
                    arrow.style.width = '0';
                    arrow.style.height = '0';
                    arrow.style.borderLeft = '12px solid transparent';
                    arrow.style.borderRight = '12px solid transparent';
                    arrow.style.borderTop = '12px solid white';
                    arrow.style.borderBottom = 'none';
                    arrow.style.filter = 'drop-shadow(0 2px 4px rgba(0,0,0,0.1))';
                    arrow.style.transform = 'translateX(-50%)';
                }

                // Animasi masuk
                setTimeout(() => {
                    tooltip.style.opacity = '1';
                    tooltip.style.transform = 'translateY(0)';
                }, 200);

                // ✅ INTERCEPT KLIK - Versi lebih robust
                const originalHref = button.getAttribute('href');

                // Hapus semua event listener lama
                const newButton = button.cloneNode(true);
                button.parentNode.replaceChild(newButton, button);

                // Tambahkan event listener ke button baru
                newButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    console.log('✅ Tombol berhasil diklik!');

                    // Simpan step
                    fetch('{{ route('update-onboarding-step') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                step: 'tambah-anggota-clicked'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('✅ Step berhasil disimpan:', data);
                            hideOnboardingOverlay();
                            setTimeout(() => {
                                console.log('🚀 Redirect ke:', originalHref);
                                window.location.href = originalHref;
                            }, 300);
                        })
                        .catch(err => {
                            console.error('❌ Error saving step:', err);
                            // Tetap redirect meskipun save gagal
                            window.location.href = originalHref;
                        });

                    return false;
                });

                // Update reference untuk resize
                window._onboardingButton = newButton;
                window._onboardingSpotlight = spotlight;
                window._onboardingTooltip = tooltip;
                window._onboardingArrow = arrow;

                window.addEventListener('resize', updateTooltipPosition);
            }

            function updateTooltipPosition() {
                const button = window._onboardingButton;
                const spotlight = window._onboardingSpotlight;
                const tooltip = window._onboardingTooltip;
                const arrow = window._onboardingArrow;

                if (!button || !spotlight || !tooltip || !arrow) return;

                const rect = button.getBoundingClientRect();
                const padding = 8;

                spotlight.style.left = (rect.left - padding) + 'px';
                spotlight.style.top = (rect.top - padding) + 'px';

                const tooltipWidth = 400;
                const tooltipHeight = 280;
                const viewportHeight = window.innerHeight;
                const viewportWidth = window.innerWidth;
                const gap = 20;

                let tooltipTop;
                let arrowPosition;

                const spaceBelow = viewportHeight - rect.bottom;
                const spaceAbove = rect.top;

                if (spaceBelow >= tooltipHeight + gap) {
                    tooltipTop = rect.bottom + gap;
                    arrowPosition = 'top';
                } else if (spaceAbove >= tooltipHeight + gap) {
                    tooltipTop = rect.top - tooltipHeight - gap;
                    arrowPosition = 'bottom';
                } else {
                    tooltipTop = rect.bottom + gap;
                    arrowPosition = 'top';
                }

                let tooltipLeft = rect.left + (rect.width / 2) - (tooltipWidth / 2);
                if (tooltipLeft < 20) tooltipLeft = 20;
                if (tooltipLeft + tooltipWidth > viewportWidth - 20) {
                    tooltipLeft = viewportWidth - tooltipWidth - 20;
                }

                tooltip.style.left = tooltipLeft + 'px';
                tooltip.style.top = tooltipTop + 'px';

                const arrowLeft = rect.left + (rect.width / 2) - tooltipLeft;
                arrow.style.left = arrowLeft + 'px';

                if (arrowPosition === 'top') {
                    arrow.style.top = '-12px';
                    arrow.style.bottom = 'auto';
                    arrow.style.borderBottom = '12px solid white';
                    arrow.style.borderTop = 'none';
                } else {
                    arrow.style.bottom = '-12px';
                    arrow.style.top = 'auto';
                    arrow.style.borderTop = '12px solid white';
                    arrow.style.borderBottom = 'none';
                }
            }

            function hideOnboardingOverlay() {
                const overlay = document.getElementById('onboarding-overlay');
                const tooltip = window._onboardingTooltip;
                const button = window._onboardingButton;

                if (tooltip) {
                    tooltip.style.opacity = '0';
                    tooltip.style.transform = 'translateY(20px)';
                }

                setTimeout(() => {
                    if (overlay) {
                        overlay.classList.add('hidden');
                    }
                    // Reset z-index button
                    if (button) {
                        button.style.zIndex = '';
                        button.style.position = '';
                    }
                }, 300);

                // Cleanup
                window.removeEventListener('resize', updateTooltipPosition);
                delete window._onboardingButton;
                delete window._onboardingSpotlight;
                delete window._onboardingTooltip;
                delete window._onboardingArrow;
            }

            function skipOnboarding() {
                fetch('{{ route('mark-onboarding-seen') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(() => {
                    hideOnboardingOverlay();
                });
            }

            // CSS animation
            const style = document.createElement('style');
            style.textContent = `
    @keyframes pulse {
        0%, 100% {
            box-shadow:
                0 0 0 9999px rgba(0, 0, 0, 0.3),
                0 0 0 13px rgba(59, 130, 246, 0.6),
                0 0 40px 8px rgba(59, 130, 246, 0.4);
            border-color: rgba(59, 130, 246, 0.9);
        }
        50% {
            box-shadow:
                0 0 0 9999px rgba(0, 0, 0, 0.3),
                0 0 0 16px rgba(59, 130, 246, 0.7),
                0 0 60px 12px rgba(59, 130, 246, 0.5);
            border-color: rgba(59, 130, 246, 1);
        }
    }

    #spotlight {
        animation: pulse 2.5s ease-in-out infinite;
    }

    /* ✅ Pastikan button bisa diklik */
    #tambah-anggota-btn {
        cursor: pointer !important;
        pointer-events: auto !important;
    }
`;
            document.head.appendChild(style);

            // ====================================
            // STEP 3: HIGHLIGHT RUANG KERJA DI SIDEBAR
            // ====================================
            // Tambahkan di dashboard.blade.php atau layout app

            function showOnboardingStep3() {
                // Cari link "Ruang Kerja" di sidebar
                const ruangKerjaLink = document.querySelector('a[href*="kelola-workspace"]');

                if (!ruangKerjaLink) {
                    console.error('Link Ruang Kerja tidak ditemukan');
                    proceedToStep4();
                    return;
                }

                // Buat overlay untuk step 3
                const overlay = document.createElement('div');
                overlay.id = 'onboarding-step3-sidebar';
                overlay.className = 'fixed inset-0 z-[9999]';
                overlay.innerHTML = `
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 transition-opacity duration-500"></div>

        <!-- Spotlight -->
        <div id="spotlight-step3" class="absolute rounded-lg transition-all duration-500" style="pointer-events: none;"></div>

        <!-- Tooltip -->
        <div id="tooltip-step3" class="absolute bg-white rounded-2xl shadow-2xl p-6 w-[380px] max-w-[90vw] border-2 border-blue-500/30" style="z-index: 10001; opacity: 0;">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg ring-4 ring-blue-100">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Kelola Ruang Kerja 🏢</h3>
                    <p class="text-sm text-gray-600 leading-relaxed mb-4">
                        Klik <strong class="text-blue-600">"Ruang Kerja"</strong> di sidebar untuk membuat dan mengelola workspace tim Anda.
                    </p>

                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex-1 h-1 bg-gray-200 rounded-full">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width: 75%"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-500">3/4</span>
                    </div>

                    <button onclick="skipOnboardingStep3()" class="w-full px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition">
                        Lewati Tutorial
                    </button>
                </div>
            </div>
        </div>
    `;

                document.body.appendChild(overlay);

                const spotlight = document.getElementById('spotlight-step3');
                const tooltip = document.getElementById('tooltip-step3');

                // Set z-index link agar bisa diklik
                ruangKerjaLink.style.position = 'relative';
                ruangKerjaLink.style.zIndex = '10000';

                // Posisikan spotlight
                const rect = ruangKerjaLink.getBoundingClientRect();
                const padding = 8;

                spotlight.style.left = (rect.left - padding) + 'px';
                spotlight.style.top = (rect.top - padding) + 'px';
                spotlight.style.width = (rect.width + padding * 2) + 'px';
                spotlight.style.height = (rect.height + padding * 2) + 'px';
                spotlight.style.boxShadow = `
        0 0 0 9999px rgba(0, 0, 0, 0.6),
        0 0 0 ${padding + 3}px rgba(59, 130, 246, 0.6),
        0 0 50px 8px rgba(59, 130, 246, 0.4)
    `;
                spotlight.style.border = '3px solid rgba(59, 130, 246, 0.9)';
                spotlight.style.animation = 'pulse 2.5s infinite';

                // Posisikan tooltip
                tooltip.style.left = (rect.right + 20) + 'px';
                tooltip.style.top = (rect.top - 20) + 'px';

                setTimeout(() => tooltip.style.opacity = '1', 300);

                // Intercept klik
                const originalHref = ruangKerjaLink.getAttribute('href');
                ruangKerjaLink.addEventListener('click', function interceptClick(e) {
                    e.preventDefault();

                    fetch('{{ route('update-onboarding-step') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                step: 'kelola-workspace'
                            })
                        })
                        .then(() => {
                            overlay.remove();
                            window.location.href = originalHref;
                        });
                }, {
                    once: true
                });
            }

            function skipOnboardingStep3() {
                fetch('{{ route('mark-onboarding-seen') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(() => {
                        document.getElementById('onboarding-step3-sidebar')?.remove();
                    });
            }
        </script>
    @endpush
@endsection
