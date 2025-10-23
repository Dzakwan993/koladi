@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div
        class="p-3 sm:p-4 md:p-6 lg:p-8 h-screen overflow-hidden mx-4 sm:mx-6 md:mx-12 lg:mx-16 xl:mx-24 font-[Inter,sans-serif]">
        <div class="max-w-7xl mx-auto h-full flex flex-col">
            {{-- Header - Fixed Height --}}
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 pb-2 mb-4 sm:mb-5 md:mb-6 flex-shrink-0">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Anggota Perusahaan</h1>
                </div>
                <button onClick="openInviteModal(event)"
                    class="bg-[#225AD6] hover:bg-blue-600 text-white px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg text-sm sm:text-base font-semibold transition flex items-center justify-center gap-1.5 sm:gap-2 shadow-sm">
                    <img src="{{ asset('images/icons/add-user.svg') }}" alt="Schedule" class="w-5 h-5 sm:w-6 sm:h-6" />
                    Undang
                </button>
            </div>

            {{-- Content Area - Scrollable --}}
            <div class="flex-1 overflow-y-auto flex flex-col gap-2 sm:gap-2.5 md:gap-3">

                {{-- Anggota terdaftar --}}
                @forelse($members ?? [] as $member)
                    <div
                        class="border-2 border-gray-200 bg-white rounded-lg p-3 flex items-center justify-between shadow-sm">
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
                                class="w-10 h-10 rounded-full object-cover">

                            <div>
                                <div class="font-semibold text-base text-gray-900 flex items-center gap-2">
                                    {{ $member->full_name ?? 'Unknown' }}
                                    @if (!empty($member->role_name))
                                        <span
                                            class="px-2 py-0.5 text-xs font-medium rounded-full
                                            {{ $member->role_name === 'SuperAdmin'
                                                ? 'bg-purple-100 text-purple-700'
                                                : ($member->role_name === 'Admin'
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-gray-100 text-gray-700') }}">
                                            {{ $member->role_name }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $member->email ?? 'No email' }}</div>
                            </div>
                        </div>
                        <button onclick="openDeleteModal(event, '{{ $member->id }}', 'member')"
                            class="bg-[#E26767] hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition">
                            Hapus
                        </button>

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
                                        <button onclick="openDeleteModal(event, '{{ $invite->id }}', 'invite')"
                                            class="text-red-600 hover:text-red-800 p-1.5 hover:bg-red-50 rounded transition"
                                            title="Batalkan undangan">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
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
    @include('components.invite-member-modal')

    <script>
        function cancelInvite(inviteId) {
            if (confirm('Apakah Anda yakin ingin membatalkan undangan ini?')) {
                // Implementasi AJAX untuk batalkan undangan
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
@endsection
