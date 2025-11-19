@extends('layouts.app')

@section('title', 'Detail Jadwal')

@section('content')
    <div class="bg-[#e9effd] min-h-screen">
        @include('components.workspace-nav', ['active' => 'jadwal'])

        <div x-data="{ openPopup: false }" class="min-h-screen flex justify-center items-start pt-10 bg-[#f3f6fc] px-4">
            <div class="bg-white rounded-[8px] shadow-xl p-6 md:p-8 w-full max-w-3xl flex flex-col gap-6">

                <!-- Header -->
                <header class="flex justify-between items-start">
                    <div class="flex items-center gap-4">
                        <div class="bg-[#2563eb] rounded-lg p-2">
                            <img src="{{ asset('images/icons/Calendar.svg') }}" alt="Calendar Icon" class="h-8 w-8">
                        </div>
                        <div>
                            <h1 class="font-bold text-xl text-black">{{ $event->title }}</h1>
                            <p class="text-sm font-semibold text-[16px] text-[#6B7280]">
                                Dibuat oleh {{ $event->creator->full_name }} pada
                                {{ \Carbon\Carbon::parse($event->created_at)->translatedFormat('l, d M Y') }}
                            </p>
                        </div>
                    </div>

                    <!-- Tombol Action (Edit/Delete untuk creator) -->
                    @if($isCreator)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="text-[#6B7280] hover:text-gray-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                            <a href="{{ route('calendar.edit', ['workspaceId' => $workspaceId, 'id' => $event->id]) }}"
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Jadwal
                            </a>
                            <form action="{{ route('calendar.destroy', ['workspaceId' => $workspaceId, 'id' => $event->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')"
                                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus Jadwal
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </header>

                <hr class="border-gray-300" />

                <!-- Informasi Jadwal -->
                <div class="flex flex-col gap-4 text-sm">
                    <!-- Waktu -->
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/icons/jampasir.svg') }}" alt="Icon Waktu" class="w-5 h-5 mt-1">
                        <div>
                            <h2 class="font-semibold text-black text-[16px]">Kapan</h2>
                            <p class="font-medium text-[14px] text-[#6B7280]">
                                {{ \Carbon\Carbon::parse($event->start_datetime)->translatedFormat('l, d M Y, H:i') }} -
                                {{ \Carbon\Carbon::parse($event->end_datetime)->translatedFormat('H:i') }}
                                @if($event->recurrence)
                                    <br><span class="text-blue-600">({{ $event->recurrence }})</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Peserta -->
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/icons/bj1.svg') }}" alt="Icon Peserta" class="w-5 h-5 mt-1">
                        <div class="w-full">
                            <h2 class="font-semibold text-black text-[16px] mb-2">Peserta</h2>
                            <div class="flex flex-wrap items-center gap-2">
                                @foreach($event->participants as $participant)
                                    <div class="flex items-center gap-2 bg-gray-50 rounded-full px-3 py-1">
                                        <img src="{{ $participant->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($participant->user->full_name) . '&background=3B82F6&color=fff&bold=true&size=128' }}"
                                             alt="{{ $participant->user->full_name }}"
                                             class="w-6 h-6 rounded-full object-cover">
                                        <span class="text-sm text-gray-700">{{ $participant->user->full_name }}</span>
                                        <span class="text-xs px-2 py-1 rounded-full
                                            @if($participant->status === 'accepted') bg-green-100 text-green-800
                                            @elseif($participant->status === 'declined') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                            {{ $participant->status === 'accepted' ? 'Diterima' :
                                               ($participant->status === 'declined' ? 'Ditolak' : 'Menunggu') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Status Peserta (jika bukan creator) -->
                    @if(!$isCreator && $isParticipant)
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/icons/status.svg') }}" alt="Icon Status" class="w-5 h-5 mt-1">
                        <div>
                            <h2 class="font-semibold text-black text-[16px]">Status Anda</h2>
                            @php
                                $userParticipant = $event->participants->where('user_id', Auth::id())->first();
                            @endphp
                            @if($userParticipant && $userParticipant->status === 'pending')
                            <div class="flex gap-2 mt-2">
                                <form action="{{ route('calendar.participant.status', ['workspaceId' => $workspaceId, 'id' => $event->id]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition-colors">
                                        Terima Undangan
                                    </button>
                                </form>
                                <form action="{{ route('calendar.participant.status', ['workspaceId' => $workspaceId, 'id' => $event->id]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="declined">
                                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition-colors">
                                        Tolak Undangan
                                    </button>
                                </form>
                            </div>
                            @else
                            <p class="font-medium text-[14px]
                                @if($userParticipant->status === 'accepted') text-green-600
                                @elseif($userParticipant->status === 'declined') text-red-600
                                @else text-yellow-600 @endif">
                                Anda telah
                                @if($userParticipant->status === 'accepted') <span class="font-semibold">menerima</span>
                                @elseif($userParticipant->status === 'declined') <span class="font-semibold">menolak</span>
                                @else <span class="font-semibold">belum merespons</span> @endif
                                undangan ini
                            </p>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Mode Rapat -->
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/icons/hbj1.svg') }}" alt="Icon Rapat" class="w-5 h-5 mt-1">
                        <div>
                            <h2 class="font-semibold text-black text-[16px]">
                                Rapat dilakukan dengan {{ $event->is_online_meeting ? 'online' : 'offline' }}
                            </h2>
                            @if($event->is_online_meeting && $event->meeting_link)
                            <button @click="openPopup = true"
                                class="mt-2 bg-[#2563eb] text-white font-semibold py-2 px-4 rounded-lg text-sm hover:bg-blue-700 transition-colors flex items-center gap-2">
                                <img src="{{ asset('images/icons/ZoomPutih.svg') }}" alt="Zoom Icon" class="w-5 h-5">
                                <span>Gabung rapat</span>
                            </button>
                            @endif
                        </div>
                    </div>
                    <!-- Catatan -->
                    @if($event->description)
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/icons/Edit.svg') }}" alt="Icon Catatan" class="w-5 h-5 mt-1">
                        <div>
                            <h2 class="font-semibold text-black text-[16px]">Catatan</h2>
                            <div class="prose max-w-none mt-1 text-[#6B7280] font-medium text-[14px]">
                                {!! $event->description !!}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Komentar Section -->
                <div class="mt-6">
                    <h2 class="font-semibold text-black text-[16px] font-inter mb-4">Komentar</h2>

                    <!-- Form Komentar -->
                    <div class="flex items-start gap-3">
                        <!-- Avatar User -->
                        <img src="{{ Auth::user()->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->full_name) . '&background=3B82F6&color=fff&bold=true&size=128' }}"
                             alt="User Avatar" class="h-10 w-10 rounded-full flex-shrink-0">

                        <!-- Form Input -->
                        <div class="flex flex-col w-full">
                            <!-- Toolbar (opsional) -->
                            <div class="flex items-center gap-1 border border-b-0 rounded-t-md bg-gray-50 px-2 py-1 text-sm overflow-x-auto">
                                <!-- Tombol formatting sederhana -->
                                <button type="button" class="hover:bg-gray-200 rounded p-1" title="Bold">
                                    <strong>B</strong>
                                </button>
                                <button type="button" class="hover:bg-gray-200 rounded p-1" title="Italic">
                                    <em>I</em>
                                </button>
                                <button type="button" class="hover:bg-gray-200 rounded p-1" title="List">
                                    • List
                                </button>
                            </div>

                            <!-- Textarea -->
                            <textarea name="comment" placeholder="Tulis komentar anda disini..."
                                class="border rounded-b-md p-3 h-24 resize-none font-inter text-[14px] placeholder-[#6B7280] border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>

                            <!-- Tombol Action -->
                            <div class="flex gap-2 mt-2">
                                <button type="button"
                                    class="bg-blue-600 text-white w-20 h-8 rounded-md hover:bg-blue-700 transition text-sm font-medium">
                                    Kirim
                                </button>
                                <button type="button"
                                    class="border border-blue-600 text-blue-600 w-20 h-8 rounded-md hover:bg-blue-50 transition text-sm font-medium">
                                    Batal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- POPUP Konfirmasi Gabung Rapat -->
            @if($event->is_online_meeting && $event->meeting_link)
            <div x-show="openPopup" x-transition
                class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
                <div @click.away="openPopup = false"
                    class="bg-[#f3f6fc] rounded-2xl shadow-lg p-8 w-full max-w-sm text-center">

                    <img src="{{ asset('images/icons/teamimage.svg') }}" alt="Ilustrasi rapat" class="w-48 mx-auto mb-6">

                    <h2 class="text-xl font-medium text-black mb-4">
                        Apakah anda ingin bergabung dengan rapat?
                    </h2>

                    <p class="text-sm text-gray-600 mb-6">
                        Anda akan diarahkan ke link rapat eksternal
                    </p>

                    <div class="flex justify-center gap-4">
                        <button @click="openPopup = false"
                            class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors text-sm">
                            Batal
                        </button>

                        <a href="{{ $event->meeting_link }}" target="_blank"
                           @click="openPopup = false"
                           class="bg-blue-800 hover:bg-blue-900 text-white font-semibold py-2 px-6 rounded-lg transition-colors text-sm">
                            Gabung Rapat
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <style>
        .prose ul {
            list-style-type: disc;
            padding-left: 1.5rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .prose ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .prose li {
            margin-bottom: 0.25rem;
        }

        .prose strong {
            font-weight: 600;
        }

        .prose em {
            font-style: italic;
        }
    </style>
@endsection
