@extends('layouts.app')

@section('title', 'Jadwal')

@section('content')
    {{-- Font Awesome untuk Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Custom CSS --}}
    @vite(['resources/css/jadwal.css'])

    <div class="bg-[#f3f6fc] min-h-screen">
        {{-- Workspace Navigation --}}
        @include('components.workspace-nav', [
            'active' => 'jadwal',
            'workspaceId' => $workspaceId
        ])

        <div class="card bg-white rounded-[8px] shadow-xl flex flex-col gap-5 p-6 m-6 mx-10 responsive-container">
            <!-- Bagian atas: kalender + notulen sejajar -->
            <div class="flex flex-row gap-5 items-center justify-center responsive-top">
                <!-- Card kalender -->
                <div class="card bg-white rounded-[8px] shadow-xl p-4 flex flex-col items-center justify-center w-full max-w-lg h-full calendar-card">
                    <div id="calendar" class="w-full h-full"></div>
                </div>
                <div class="flex flex-col right-section">
                    {{-- ✅ Button Buat Jadwal - HANYA TAMPIL JIKA ADA PERMISSION --}}
                    @if(isset($canCreateSchedule) && $canCreateSchedule)
                        <a href="{{ route('buatJadwal', ['workspaceId' => $workspaceId]) }}"
                            class="bg-[#225ad6] rounded-[8px] shadow-xl flex items-center justify-center p-5 w-[400px] h-[40px] text-[#ffffff] font-semibold hover:bg-[#1a46a0] transition mb-4 buat-jadwal-btn">
                            Buat Jadwal
                        </a>
                    @else
                        {{-- ✅ Jika tidak punya permission, tampilkan info atau hide button --}}
                        <div class="bg-gray-100 rounded-[8px] shadow-xl flex items-center justify-center p-5 w-[400px] h-[40px] text-gray-500 font-semibold mb-4 buat-jadwal-btn cursor-not-allowed">
                            <i class="fas fa-lock mr-2"></i> Anda tidak dapat membuat jadwal
                        </div>
                    @endif

                    <div class="card bg-[#bbcff9] rounded-[8px] shadow-xl flex flex-col items-center justify-center p-6 w-[400px] h-[300px] notulen-card cursor-pointer hover:bg-[#a5bef5] transition"
                        onclick="window.location.href='{{ route('notulensi', ['workspaceId' => $workspaceId]) }}'">
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

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- FullCalendar JS --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    {{-- Custom JS --}}
    @vite(['resources/js/jadwal.js'])

    {{-- SWEETALERT NOTIFICATIONS --}}
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

    {{-- ✅ Pass canCreateSchedule ke JavaScript untuk feature gating --}}
    <script>
        window.userCanCreateSchedule = {{ $canCreateSchedule ? 'true' : 'false' }};
    </script>

    {{-- Initialize Calendar --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const workspaceId = '{{ $workspaceId }}';

            function tryInitCalendar(attempts = 0) {
                if (typeof window.initJadwalCalendar === 'function') {
                    window.initJadwalCalendar(workspaceId);
                } else if (attempts < 10) {
                    setTimeout(() => tryInitCalendar(attempts + 1), 200);
                } else {
                    console.error('Failed to load initJadwalCalendar after 10 attempts');
                }
            }

            tryInitCalendar();
        });
    </script>
@endsection
