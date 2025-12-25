@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div
        class="p-3 sm:p-4 md:p-6 lg:p-8 h-screen overflow-hidden mx-4 sm:mx-6 md:mx-12 lg:mx-16 xl:mx-24 font-[Inter,sans-serif]">
        <div class="max-w-7xl mx-auto h-full flex flex-col">
           {{-- ================= HEADER ================= --}}
<div class="flex flex-col gap-4 mb-6 flex-shrink-0">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Anggota Perusahaan</h1>
            <p class="text-sm text-gray-500">
                Kelola anggota dan izin akses perusahaan
            </p>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-sm font-semibold
                {{ $isLimitReached ? 'text-red-600' : 'text-gray-700' }}">
                {{ $activeUserCount }} / {{ $userLimit }} user aktif
            </div>

            @if ($canInvite ?? false)
                <button
                    @if($isLimitReached) disabled @else onclick="openInviteModal(event)" @endif
                    class="px-4 py-2 rounded-lg text-sm font-semibold
                        {{ $isLimitReached
                            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                            : 'bg-blue-600 hover:bg-blue-700 text-white' }}">
                    Undang
                </button>
            @endif
        </div>
    </div>


    @if ($isLimitReached && $currentUserRole === 'SuperAdmin')
        <div class="flex gap-3 items-start bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="mt-0.5">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
            </div>

            <div class="flex-1">
                <p class="font-semibold text-red-800">
                    Batas maksimal {{ $userLimit }} user aktif tercapai
                </p>
                <p class="text-sm text-red-700 mt-1">
                    Anda tidak dapat menambahkan user baru.
                </p>
                <p class="text-xs text-red-600 mt-2">
                    💡 Solusi:
                    <a href="{{ route('pembayaran') }}" class="underline font-semibold">nonaktifkan user</a>
                    atau
                    <a href="{{ route('pembayaran') }}" class="underline font-semibold">upgrade paket</a>
                </p>
            </div>
        </div>
    @endif

</div>
{{-- ================= END HEADER ================= --}}


            {{-- Content Area - Scrollable --}}
            <div class="flex-1 overflow-y-auto flex flex-col gap-2 sm:gap-2.5 md:gap-3">
                {{-- Anggota terdaftar --}}
                @forelse($members ?? [] as $member)
                    <div
                        class="border-2 border-gray-200 bg-white rounded-lg p-3 flex items-center justify-between shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-3">
                            @php
                                if ($member->avatar && Str::startsWith($member->avatar, ['http://', 'https://'])) {
                                    $avatarUrl = $member->avatar;
                                } elseif ($member->avatar) {
                                    $avatarUrl = asset('storage/' . $member->avatar);
                                } else {
                                    $avatarUrl =
                                        'https://ui-avatars.com/api/?name=' .
                                        urlencode($member->full_name ?? 'User') .
                                        '&background=4F46E5&color=fff&bold=true';
                                }
                            @endphp

                            <img src="{{ $avatarUrl }}" alt="{{ $member->full_name ?? 'User' }}"
                                class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-200">

                            <div>
                                <div class="font-semibold text-base text-gray-900 flex items-center gap-2">
                                    {{ $member->full_name ?? 'Unknown' }}
                                    @if (!empty($member->role_name))
                                        @php
                                            $roleColors = [
                                                'SuperAdmin' => 'bg-[#102A63] text-white',
                                                'Super Admin' => 'bg-[#102A63] text-white',
                                                'Admin' => 'bg-[#225AD6] text-white',
                                                'Administrator' => 'bg-[#DC2626] text-white',
                                                'Manager' => 'bg-[#0FA875] text-white',
                                                'Member' => 'bg-[#E4BA13] text-white',
                                            ];
                                            $roleClass = $roleColors[$member->role_name] ?? 'bg-gray-100 text-gray-700';
                                        @endphp
                                        <span
                                            class="px-2.5 py-0.5 text-xs font-semibold rounded-bl-xl rounded-tr-xl {{ $roleClass }}">
                                            {{ $member->role_name }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $member->email ?? 'No email' }}</div>
                            </div>
                        </div>

                        {{-- ✅ Tombol Hapus - Hanya tampil jika punya permission --}}
                        @if ($member->can_delete ?? false)
                            <button onclick="openDeleteModal(event, '{{ $member->id }}', 'member')"
                                class="bg-[#E26767] hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition shadow-sm">
                                Hapus
                            </button>
                        @else
                            {{-- ❌ Tidak punya izin hapus - tampilkan icon lock atau hide button --}}
                            <div class="text-gray-400 px-3 py-2 text-xs flex items-center gap-1"
                                title="Anda tidak memiliki izin untuk menghapus anggota ini">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                                <span class="hidden sm:inline">Terkunci</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500">Belum ada anggota terdaftar.</div>
                @endforelse

                {{-- Undangan (pending) --}}
                @if (!empty($invites) && $invites->count() > 0)
                    <div class="mt-6">
                        <h3 class="text-base font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Undangan Tertunda ({{ $invites->count() }})
                        </h3>
                        <div class="space-y-2">
                            @foreach ($invites as $invite)
                                <div
                                    class="border border-dashed border-yellow-300 bg-yellow-50 rounded-lg p-3 flex items-center justify-between hover:bg-yellow-100 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-yellow-200 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-yellow-700" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $invite->email_target }}</div>
                                            <div class="text-xs text-gray-600">
                                                Diundang oleh {{ $invite->inviter->full_name ?? 'Unknown' }} •
                                                {{ $invite->created_at->diffForHumans() }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                Kadaluarsa: {{ $invite->expired_at->format('d M Y, H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="px-2.5 py-1 text-xs font-semibold bg-yellow-200 text-yellow-800 rounded-full">
                                            Menunggu
                                        </span>

                                        {{-- ✅ Tombol Batalkan - Hanya tampil jika punya izin undang --}}
                                        @if ($canInvite ?? false)
                                            <button onclick="openDeleteModal(event, '{{ $invite->id }}', 'invite')"
                                                class="text-red-600 hover:text-red-800 p-1.5 hover:bg-red-50 rounded transition"
                                                title="Batalkan undangan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('components.delete-member-modal')

    {{-- ✅ Modal Undang - Hanya include jika punya izin --}}
    @if ($canInvite ?? false)
        @include('components.invite-member-modal')
    @endif

    <script>
        // Jika tidak punya izin undang, disable fungsi
        @if (!($canInvite ?? false))
            function openInviteModal(event) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Akses Ditolak',
                    text: 'Anda tidak memiliki izin untuk mengundang anggota. Hanya SuperAdmin, Admin, dan Manager yang dapat mengundang.',
                    confirmButtonColor: '#E26767',
                });
            }
        @endif

        function cancelInvite(inviteId) {
            if (confirm('Apakah Anda yakin ingin membatalkan undangan ini?')) {
                fetch(`/invitation/${inviteId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    </script>

    {{-- 🎯 ONBOARDING STEP 2 --}}
    <div id="onboarding-step2" class="hidden fixed inset-0 z-[9999]">
        <div class="absolute inset-0 bg-black/50 transition-opacity duration-500"></div>
        <div id="spotlight-step2" class="absolute rounded-xl transition-all duration-500"></div>

        <div id="tooltip-step2"
            class="absolute bg-white rounded-2xl shadow-2xl p-6 w-[400px] max-w-[90vw] border-2 border-blue-500/30 transition-all duration-500"
            style="z-index: 10001; opacity: 0;">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg ring-4 ring-blue-100">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Undang Anggota Tim! 👥</h3>
                    <p class="text-sm text-gray-600 leading-relaxed mb-4">
                        Klik tombol <strong class="text-blue-600">"Undang"</strong> untuk menambahkan anggota ke perusahaan
                        Anda.
                    </p>

                    <div class="flex items-center gap-2 mb-5">
                        <div class="flex-1 h-1 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"
                                style="width: 50%">
                            </div>
                        </div>
                        <span class="text-xs font-medium text-gray-500">2/4</span>
                    </div>

                    <div class="flex gap-3">
                        <button onclick="skipStep2()"
                            class="flex-1 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            Lewati
                        </button>
                        <button onclick="proceedToStep3()"
                            class="flex-1 px-4 py-2 text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition flex items-center justify-center gap-2">
                            Lanjut <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Arrow -->
            <div id="arrow-step2" class="absolute pointer-events-none"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const step = '{{ Auth::user()->onboarding_step ?? '' }}';
                console.log('🎯 Tambah Anggota - Current step:', step);

                if (step === 'tambah-anggota-clicked') {
                    setTimeout(() => showOnboardingStep2(), 800);
                }
            });

            function showOnboardingStep2() {
                const overlay = document.getElementById('onboarding-step2');
                const undangBtn = document.querySelector('button[onClick*="openInviteModal"]');
                const spotlight = document.getElementById('spotlight-step2');
                const tooltip = document.getElementById('tooltip-step2');
                const arrow = document.getElementById('arrow-step2');

                if (!undangBtn) {
                    console.warn('⚠️ Tombol Undang tidak ditemukan, skip ke step 3');
                    proceedToStep3();
                    return;
                }

                console.log('✅ Showing Step 2: Undang button');
                overlay.classList.remove('hidden');

                undangBtn.style.position = 'relative';
                undangBtn.style.zIndex = '10000';

                // ✅ Fungsi positioning yang bisa dipanggil ulang
                function positionTooltipStep2() {
                    const rect = undangBtn.getBoundingClientRect();
                    const padding = 10;

                    // Spotlight
                    spotlight.style.left = (rect.left - padding) + 'px';
                    spotlight.style.top = (rect.top - padding) + 'px';
                    spotlight.style.width = (rect.width + padding * 2) + 'px';
                    spotlight.style.height = (rect.height + padding * 2) + 'px';
                    spotlight.style.boxShadow = `
            0 0 0 9999px rgba(0, 0, 0, 0.5),
            0 0 0 ${padding + 3}px rgba(59, 130, 246, 0.6),
            0 0 60px 10px rgba(59, 130, 246, 0.4)
        `;
                    spotlight.style.border = '4px solid rgba(59, 130, 246, 0.9)';
                    spotlight.style.zIndex = '9998';
                    spotlight.style.pointerEvents = 'none';
                    spotlight.style.animation = 'pulse 2.5s infinite';

                    // ✅ TOOLTIP POSITIONING - RESPONSIVE
                    const tooltipWidth = window.innerWidth < 640 ? 340 : 400;
                    const gap = 25;
                    let tooltipLeft, tooltipTop, arrowPosition;

                    if (window.innerWidth < 768) {
                        // Mobile: di bawah button
                        tooltipTop = rect.bottom + gap;
                        tooltipLeft = Math.max(20, Math.min(window.innerWidth - tooltipWidth - 20,
                            rect.left + (rect.width / 2) - (tooltipWidth / 2)));
                        arrowPosition = 'top';
                    } else {
                        // Desktop: di kiri button
                        tooltipLeft = rect.left - tooltipWidth - gap;
                        tooltipTop = rect.top - 20;
                        arrowPosition = 'right';

                        if (tooltipLeft < 20) {
                            tooltipLeft = rect.right + gap;
                            arrowPosition = 'left';
                        }
                    }

                    tooltip.style.left = tooltipLeft + 'px';
                    tooltip.style.top = tooltipTop + 'px';
                    tooltip.style.opacity = '1';
                    tooltip.classList.add('onboarding-tooltip');

                    // ✅ ARROW STYLING
                    const arrowLeft = rect.left + (rect.width / 2) - tooltipLeft;

                    // ✅ ARROW STYLING
                    if (arrowPosition === 'top') {
                        // Arrow pointing UP (tooltip below button)
                        const arrowLeft = rect.left + (rect.width / 2) - tooltipLeft;
                        arrow.style.top = '-12px'; // ⬅️ PERUBAHAN: tambah angka -12
                        arrow.style.left = arrowLeft + 'px';
                        arrow.style.right = 'auto';
                        arrow.style.bottom = 'auto';
                        arrow.style.transform = 'translateX(-50%)';
                        arrow.style.width = '0';
                        arrow.style.height = '0';
                        arrow.style.borderBottom = '12px solid white';
                        arrow.style.borderLeft = '12px solid transparent';
                        arrow.style.borderRight = '12px solid transparent';
                        arrow.style.borderTop = 'none';
                        arrow.style.filter = 'drop-shadow(0 -2px 4px rgba(0,0,0,0.1))';
                    } else if (arrowPosition === 'right') {
                        // Arrow pointing RIGHT (tooltip on left of button)
                        arrow.style.right = '-12px';
                        arrow.style.top = '35px'; // ⬅️ PERUBAHAN: ganti dari '50%' jadi '35px' agar lebih naik
                        arrow.style.left = 'auto';
                        arrow.style.bottom = 'auto';
                        arrow.style.transform = 'none'; // ⬅️ PERUBAHAN: hapus translateY
                        arrow.style.width = '0';
                        arrow.style.height = '0';
                        arrow.style.borderLeft = '12px solid white';
                        arrow.style.borderTop = '12px solid transparent';
                        arrow.style.borderBottom = '12px solid transparent';
                        arrow.style.borderRight = 'none';
                        arrow.style.filter = 'drop-shadow(2px 0 4px rgba(0,0,0,0.1))';
                    } else {
                        // Arrow pointing LEFT (tooltip on right of button)
                        arrow.style.left = '-12px';
                        arrow.style.top = '35px'; // ⬅️ PERUBAHAN: ganti dari '50%' jadi '35px'
                        arrow.style.right = 'auto';
                        arrow.style.bottom = 'auto';
                        arrow.style.transform = 'none'; // ⬅️ PERUBAHAN: hapus translateY
                        arrow.style.width = '0';
                        arrow.style.height = '0';
                        arrow.style.borderRight = '12px solid white';
                        arrow.style.borderTop = '12px solid transparent';
                        arrow.style.borderBottom = '12px solid transparent';
                        arrow.style.borderLeft = 'none';
                        arrow.style.filter = 'drop-shadow(-2px 0 4px rgba(0,0,0,0.1))';
                    }
                }

                // ✅ Panggil pertama kali
                positionTooltipStep2();

                // ✅ Simpan reference untuk cleanup
                window._onboardingStep2 = {
                    overlay,
                    spotlight,
                    tooltip,
                    arrow,
                    button: undangBtn,
                    positionFunc: positionTooltipStep2
                };

                // ✅ TAMBAHKAN RESIZE LISTENER
                window.addEventListener('resize', positionTooltipStep2);
            }

            // ✅ UPDATE fungsi hideOnboardingStep2
            function hideOnboardingStep2() {
                const refs = window._onboardingStep2;
                if (!refs) return;

                if (refs.tooltip) refs.tooltip.style.opacity = '0';

                setTimeout(() => {
                    if (refs.overlay) refs.overlay.classList.add('hidden');
                    if (refs.button) {
                        refs.button.style.zIndex = '';
                        refs.button.style.position = '';
                    }
                }, 300);

                // ✅ HAPUS RESIZE LISTENER
                if (refs.positionFunc) {
                    window.removeEventListener('resize', refs.positionFunc);
                }

                delete window._onboardingStep2;
            }

            function proceedToStep3() {
                console.log('➡️ Proceeding to step 3');

                fetch('{{ route('update-onboarding-step') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            step: 'kelola-workspace-sidebar'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('✅ Step updated:', data);
                        hideOnboardingStep2();
                        // ✅ TIDAK REDIRECT! Tampilkan step 3 di halaman yang sama
                        setTimeout(() => showOnboardingStep3(), 500);
                    })
                    .catch(err => {
                        console.error('❌ Error:', err);
                        hideOnboardingStep2();
                    });
            }

            function skipStep2() {
                console.log('⏭️ Skipping onboarding');

                fetch('{{ route('mark-onboarding-seen') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(() => {
                        hideOnboardingStep2();
                    });
            }

            // ====================================
            // ✅ STEP 3: HIGHLIGHT RUANG KERJA DI SIDEBAR
            // ====================================
            function showOnboardingStep3() {
                const ruangKerjaLink = document.querySelector('a[href*="kelola-workspace"]');

                if (!ruangKerjaLink) {
                    console.error('❌ Link Ruang Kerja tidak ditemukan');
                    return;
                }

                console.log('✅ Showing Step 3: Ruang Kerja sidebar');

                // Buat overlay untuk step 3
                const overlay = document.createElement('div');
                overlay.id = 'onboarding-step3-sidebar';
                overlay.className = 'fixed inset-0 z-[9999]';
                overlay.innerHTML = `
        <div class="absolute inset-0 bg-black/50 transition-opacity duration-500"></div>
        <div id="spotlight-step3" class="absolute rounded-lg transition-all duration-500" style="pointer-events: none;"></div>

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
                        Klik <strong class="text-blue-600">"Ruang Kerja"</strong> di sidebar untuk membuat workspace tim Anda.
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

            <!-- Arrow -->
            <div id="arrow-step3" class="absolute pointer-events-none"></div>
        </div>
    `;

                document.body.appendChild(overlay);

                const spotlight = document.getElementById('spotlight-step3');
                const tooltip = document.getElementById('tooltip-step3');
                const arrow = document.getElementById('arrow-step3');

                ruangKerjaLink.style.position = 'relative';
                ruangKerjaLink.style.zIndex = '10000';

                const rect = ruangKerjaLink.getBoundingClientRect();
                const padding = 8;

                spotlight.style.left = (rect.left - padding) + 'px';
                spotlight.style.top = (rect.top - padding) + 'px';
                spotlight.style.width = (rect.width + padding * 2) + 'px';
                spotlight.style.height = (rect.height + padding * 2) + 'px';
                spotlight.style.boxShadow = `
        0 0 0 9999px rgba(0, 0, 0, 0.5),
        0 0 0 ${padding + 3}px rgba(59, 130, 246, 0.6),
        0 0 50px 8px rgba(59, 130, 246, 0.4)
    `;
                spotlight.style.border = '3px solid rgba(59, 130, 246, 0.9)';
                spotlight.style.animation = 'pulse 2.5s infinite';

                // Cari bagian ini dan ganti:
                const tooltipWidth = window.innerWidth < 640 ? 340 : 380;
                const gap = window.innerWidth < 768 ? 15 : 20;
                let tooltipLeft, tooltipTop, arrowPos;

                if (window.innerWidth < 1024) {
                    // Mobile & Tablet: di bawah link
                    tooltipTop = (rect.bottom + gap);
                    tooltipLeft = Math.max(20, Math.min(window.innerWidth - tooltipWidth - 20,
                        rect.left - 50));
                    arrowPos = 'top';
                } else {
                    // Desktop: di kanan
                    tooltipLeft = (rect.right + gap);
                    tooltipTop = (rect.top - 20);
                    arrowPos = 'right';
                }

                tooltip.style.left = tooltipLeft + 'px';
                tooltip.style.top = tooltipTop + 'px';
                tooltip.classList.add('onboarding-tooltip');

                // Arrow positioning
                if (arrowPos === 'top') {
                    arrow.style.top = '-12px';
                    arrow.style.left = (rect.left + rect.width / 2 - tooltipLeft) + 'px';
                    arrow.style.right = 'auto';
                    arrow.style.bottom = 'auto';
                    arrow.style.transform = 'translateX(-50%)';
                    arrow.style.width = '0';
                    arrow.style.height = '0';
                    arrow.style.borderBottom = '12px solid white';
                    arrow.style.borderLeft = '12px solid transparent';
                    arrow.style.borderRight = '12px solid transparent';
                    arrow.style.borderTop = 'none';
                    arrow.style.filter = 'drop-shadow(0 -2px 4px rgba(0,0,0,0.1))';
                } else {
                    // Arrow pointing LEFT (tooltip on right of sidebar item)
                    arrow.style.left = '-12px';
                    arrow.style.top = '25px'; // ⬅️ PERUBAHAN: ganti dari '50%' jadi '35px'
                    arrow.style.right = 'auto';
                    arrow.style.bottom = 'auto';
                    arrow.style.transform = 'none'; // ⬅️ PERUBAHAN: hapus translateY
                    arrow.style.width = '0';
                    arrow.style.height = '0';
                    arrow.style.borderRight = '12px solid white';
                    arrow.style.borderTop = '12px solid transparent';
                    arrow.style.borderBottom = '12px solid transparent';
                    arrow.style.borderLeft = 'none';
                    arrow.style.filter = 'drop-shadow(-2px 0 4px rgba(0,0,0,0.1))';
                }

                setTimeout(() => tooltip.style.opacity = '1', 300);

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


            // Di file tambah-anggota.blade.php atau file JS terpisah

            // 🔥 Fungsi kirim undangan (dengan error handling limit)
            async function sendInvitation() {
                const emailInput = document.getElementById('inviteEmail');
                const email = emailInput.value.trim();

                if (!email) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Email Kosong',
                        text: 'Silakan masukkan email yang akan diundang',
                        confirmButtonColor: '#dc2626'
                    });
                    return;
                }

                // Validasi email format
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Format Email Salah',
                        text: 'Silakan masukkan email yang valid',
                        confirmButtonColor: '#dc2626'
                    });
                    return;
                }

                // Show loading
                Swal.fire({
                    title: 'Mengirim Undangan...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch('/invite/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            email_target: email
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // 🔥 Handle error limit user
                        if (response.status === 400 && data.error.includes('Batas maksimal')) {
                            await Swal.fire({
                                icon: 'warning',
                                title: 'Batas User Tercapai',
                                html: `
                        <p class="text-gray-700 mb-3">${data.error}</p>
                        <div class="bg-blue-50 rounded-lg p-3 text-sm text-left">
                            <p class="font-semibold text-blue-900 mb-2">💡 Solusi:</p>
                            <ol class="list-decimal list-inside space-y-1 text-blue-800">
                                <li>Nonaktifkan user lain terlebih dahulu, atau</li>
                                <li>Upgrade paket subscription Anda</li>
                            </ol>
                        </div>
                    `,
                                confirmButtonText: 'Upgrade Paket',
                                showCancelButton: true,
                                cancelButtonText: 'Tutup',
                                confirmButtonColor: '#2563eb',
                                cancelButtonColor: '#6b7280'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '/pembayaran';
                                }
                            });
                            return;
                        }

                        throw new Error(data.error || 'Terjadi kesalahan');
                    }

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Undangan Terkirim!',
                            text: `Undangan telah dikirim ke ${email}`,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Reset form dan reload
                        emailInput.value = '';
                        closeInviteModal();
                        setTimeout(() => window.location.reload(), 2000);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    await Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengirim Undangan',
                        text: error.message || 'Terjadi kesalahan saat mengirim undangan',
                        confirmButtonColor: '#dc2626'
                    });
                }
            }

            // 🔥 Fungsi toggle user status (dengan error handling limit)
            async function toggleUserStatus(userCompanyId, isActive) {
                const statusText = isActive ? 'mengaktifkan' : 'menonaktifkan';

                // Konfirmasi dulu
                const result = await Swal.fire({
                    title: 'Konfirmasi',
                    text: `Anda yakin ingin ${statusText} user ini?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isActive ? '#16a34a' : '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                });

                if (!result.isConfirmed) {
                    event.target.checked = !isActive;
                    return;
                }

                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch('/subscription/toggle-user-status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            user_company_id: userCompanyId,
                            status_active: isActive
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        // 🔥 Handle error limit user
                        if (response.status === 400 && data.message.includes('Batas maksimal')) {
                            await Swal.fire({
                                icon: 'warning',
                                title: 'Batas User Tercapai',
                                html: `
                        <p class="text-gray-700 mb-3">${data.message}</p>
                        <div class="bg-blue-50 rounded-lg p-3 text-sm text-left">
                            <p class="font-semibold text-blue-900 mb-2">💡 Solusi:</p>
                            <ol class="list-decimal list-inside space-y-1 text-blue-800">
                                <li>Nonaktifkan user lain terlebih dahulu, atau</li>
                                <li>Upgrade paket subscription Anda</li>
                            </ol>
                        </div>
                    `,
                                confirmButtonText: 'Upgrade Paket',
                                showCancelButton: true,
                                cancelButtonText: 'Tutup',
                                confirmButtonColor: '#2563eb',
                                cancelButtonColor: '#6b7280'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '/pembayaran';
                                }
                            });

                            // Revert toggle
                            event.target.checked = !isActive;
                            return;
                        }

                        throw new Error(data.message || 'Gagal mengubah status user');
                    }

                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Refresh halaman
                    setTimeout(() => window.location.reload(), 2000);

                } catch (error) {
                    console.error('Error:', error);

                    await Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: error.message || 'Terjadi kesalahan saat mengubah status user',
                        confirmButtonColor: '#dc2626'
                    });

                    // Revert toggle jika error
                    event.target.checked = !isActive;
                }
            }
        </script>
    @endpush
@endsection
