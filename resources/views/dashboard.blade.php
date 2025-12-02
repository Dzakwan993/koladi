@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @php
        function getShortName($fullName) {
            if (!$fullName) return 'User';
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
                        <!-- ✅ Improved button with better mobile layout -->
                        <a href="{{ url('/tambah-anggota') }}"
                           role="button"
                           aria-label="Tambah Anggota Baru"
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
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
                                    <i class="fas fa-bullhorn text-white text-base sm:text-lg" aria-hidden="true"></i>
                                </div>
                                <div>
                                    <p class="text-sm sm:text-base lg:text-lg text-[#1E1E1E] font-bold">Pengumuman Terbaru</p>
                                    <p class="text-xs text-gray-500 hidden sm:block">Update terkini untuk Anda</p>
                                </div>
                            </div>
                        </div>

                        {{-- ✅ Container Pengumuman - Flexible height --}}
                        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-5 lg:p-6 flex flex-col border border-gray-100 min-h-[400px] max-h-[600px] xl:h-[calc(100vh-280px)]">
                            <div class="overflow-y-auto flex-1 pr-2 py-2 custom-scrollbar">
                                <div class="space-y-3 sm:space-y-4 px-1">
                                    @forelse($pengumumans as $p)
                                        <div class="group bg-gradient-to-br from-[#E9EFFD] to-[#dce6fc] rounded-xl p-3 sm:p-4 shadow-sm hover:shadow-2xl transition-all duration-300 cursor-pointer border border-blue-100 hover:border-blue-400 focus-within:ring-2 focus-within:ring-blue-400"
                                             onclick="window.location.href='{{ route('pengumuman-perusahaan.show', ['company_id' => $company->id, 'id' => $p->id]) }}'"
                                             tabindex="0"
                                             role="article"
                                             aria-label="Pengumuman: {{ $p->title }}">

                                            {{-- Header dengan Avatar dan Tanggal --}}
                                            <div class="flex items-start justify-between mb-3 gap-2">
                                                <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                                                    <img src="{{ $p->creator->avatar_url }}"
                                                         alt="Avatar {{ $p->creator->full_name }}"
                                                         loading="lazy"
                                                         class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover border-2 border-white shadow-md flex-shrink-0 ring-2 ring-blue-200">

                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-semibold text-xs sm:text-sm text-gray-800 truncate">
                                                            {{ $p->creator->full_name }}
                                                        </p>
                                                        <div class="flex items-center gap-1.5 sm:gap-2 mt-0.5">
                                                            @if ($p->is_private)
                                                                <i class="fas fa-lock text-gray-400 text-xs" aria-label="Pengumuman Privat"></i>
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
                                                            <div class="absolute -top-1 -right-1 w-2 h-2 sm:w-2.5 sm:h-2.5 bg-red-500 rounded-full animate-pulse" aria-hidden="true"></div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Title --}}
                                            <h3 class="font-bold text-[#090909] text-sm sm:text-base mb-2 line-clamp-1 group-hover:text-blue-700 transition-colors">
                                                {{ $p->title }}
                                            </h3>

                                            {{-- Description --}}
                                            <p class="text-xs sm:text-sm text-gray-600 mb-3 line-clamp-2 leading-relaxed">
                                                {!! strip_tags($p->description) !!}
                                            </p>

                                            {{-- Footer dengan metadata --}}
                                            @if ($p->due_date || $p->auto_due)
                                                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 pt-2 sm:pt-3 border-t border-blue-200/50">
                                                    <i class="fas fa-calendar-check text-blue-600 text-xs" aria-hidden="true"></i>
                                                    @if ($p->due_date)
                                                        <span class="text-xs font-medium text-blue-700">
                                                            Tenggat: {{ \Carbon\Carbon::parse($p->due_date)->translatedFormat('d M Y') }}
                                                        </span>
                                                    @endif
                                                    @if ($p->auto_due)
                                                        <span class="text-xs text-gray-600">
                                                            • Selesai: {{ \Carbon\Carbon::parse($p->auto_due)->translatedFormat('d M Y') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="text-center py-12 sm:py-16">
                                            <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-4">
                                                <i class="fas fa-bullhorn text-3xl sm:text-4xl text-gray-400" aria-hidden="true"></i>
                                            </div>
                                            <p class="text-sm sm:text-base font-semibold text-gray-700 mb-2">Belum Ada Pengumuman</p>
                                            <p class="text-xs sm:text-sm text-gray-500">Pengumuman terbaru akan muncul di sini</p>
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
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center shadow-md">
                                <i class="fas fa-calendar-alt text-white text-base sm:text-lg" aria-hidden="true"></i>
                            </div>
                            <div>
                                <p class="text-sm sm:text-base lg:text-lg text-[#1E1E1E] font-bold">Jadwal</p>
                                <p class="text-xs text-gray-500 hidden sm:block">Agenda Anda</p>
                            </div>
                        </div>

                        {{-- ✅ Calendar - Improved responsive height --}}
                        <div class="bg-white rounded-2xl shadow-lg p-3 sm:p-4 lg:p-5 mb-4 sm:mb-5 border border-gray-100 h-[280px] sm:h-[320px] lg:h-[350px]">
                            <div id="calendar" class="w-full h-full" role="application" aria-label="Kalender Jadwal"></div>
                        </div>

                        {{-- ✅ Jadwal List - Better flexible sizing --}}
                        <div class="bg-white rounded-2xl shadow-lg p-3 sm:p-4 lg:p-5 flex flex-col border border-gray-100 min-h-[200px] max-h-[400px] xl:flex-1">
                            <h3 class="font-bold text-[#1E1E1E] mb-3 text-sm sm:text-base flex items-center gap-2" id="scheduleTitle">
                                <i class="fas fa-list-check text-blue-600" aria-hidden="true"></i>
                                Jadwal Hari Ini
                            </h3>

                            <div class="overflow-y-auto flex-1 pr-2 custom-scrollbar">
                                <div class="space-y-2 sm:space-y-3 schedule-cards-container">
                                    {{-- Loading state --}}
                                    <div class="text-center py-8 sm:py-12">
                                        <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-blue-50 mb-3 sm:mb-4">
                                            <i class="fas fa-spinner fa-spin text-2xl sm:text-3xl text-blue-500" aria-hidden="true"></i>
                                        </div>
                                        <p class="text-xs sm:text-sm font-semibold text-gray-700">Memuat jadwal...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FullCalendar CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">

    @vite(['resources/css/dashboard.css'])

    {{-- FullCalendar JS --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    @push('scripts')
        @vite('resources/js/dashboard.js')
    @endpush
@endsection
