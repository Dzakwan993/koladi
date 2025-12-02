@extends('layouts.app')

@section('title', 'Edit Jadwal')

@section('content')
    <div class="bg-[#e9effd] min-h-screen">
        <div class="min-h-screen flex justify-center pt-4 md:pt-8 pb-8 bg-[#f3f6fc] px-3 md:px-6">
            <!-- Form -->
            <form action="{{ route('jadwal-umum.update', ['id' => $event->id]) }}" method="POST" id="scheduleForm"
                class="bg-white rounded-xl shadow-xl p-4 md:p-6 w-full max-w-4xl flex flex-col gap-4 md:gap-5 h-fit">
                @csrf
                @method('PUT')

                <!-- Judul -->
                <h2 class="text-lg md:text-xl font-inter font-bold text-[#102a63] border-b-2 border-black pb-3">Edit Jadwal
                </h2>

                <!-- Nama Jadwal -->
                <div class="flex flex-col font-inter">
                    <label class="mb-2 font-medium text-sm md:text-base text-gray-700">
                        Nama Jadwal <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}"
                        placeholder="Masukkan nama jadwal..."
                        class="border rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 font-inter text-sm placeholder-gray-400 border-gray-300 transition"
                        required />
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal & Waktu Mulai dan Akhir -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Mulai -->
                    <div class="flex flex-col font-inter">
                        <label class="mb-2 font-medium text-sm md:text-base text-gray-700">
                            Mulai <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="date" name="start_date" id="start_date"
                                value="{{ old('start_date', \Carbon\Carbon::parse($event->start_datetime)->format('Y-m-d')) }}"
                                class="border rounded-lg p-2.5 flex-1 font-inter text-sm text-gray-700 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                required />
                            <input type="time" name="start_time" id="start_time"
                                value="{{ old('start_time', \Carbon\Carbon::parse($event->start_datetime)->format('H:i')) }}"
                                class="border rounded-lg p-2.5 w-32 font-inter text-sm text-gray-700 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                required />
                        </div>
                    </div>

                    <!-- Akhir -->
                    <div class="flex flex-col font-inter">
                        <label class="mb-2 font-medium text-sm md:text-base text-gray-700">
                            Akhir <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <input type="date" name="end_date" id="end_date"
                                value="{{ old('end_date', \Carbon\Carbon::parse($event->end_datetime)->format('Y-m-d')) }}"
                                class="border rounded-lg p-2.5 flex-1 font-inter text-sm text-gray-700 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                required />
                            <input type="time" name="end_time" id="end_time"
                                value="{{ old('end_time', \Carbon\Carbon::parse($event->end_datetime)->format('H:i')) }}"
                                class="border rounded-lg p-2.5 w-32 font-inter text-sm text-gray-700 border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                required />
                        </div>
                    </div>
                </div>

                <!-- Hidden fields for combined datetime -->
                <input type="hidden" name="start_datetime" id="start_datetime">
                <input type="hidden" name="end_datetime" id="end_datetime">

                <!-- Pengulangan -->
                <div class="flex flex-col font-sans">
                    <label for="pengulangan-btn" class="mb-1 font-medium text-sm md:text-base text-black">
                        Pengulangan<span class="text-red-500">*</span>
                    </label>

                    <div x-data="{
                        open: false,
                        options: ['Jangan Ulangi', 'Setiap hari', 'Setiap minggu', 'Setiap kuartal', 'Setiap tahun', 'Setiap hari kerja (Sen - Jum)'],
                        selected: '{{ old('recurrence', $event->recurrence ?? 'Jangan Ulangi') }}'
                    }" class="relative w-full md:w-1/3 pengulangan-dropdown">
                        <input type="hidden" name="recurrence" x-model="selected">
                        <button type="button" @click="open = !open" @click.away="open = false" id="pengulangan-btn"
                            class="w-full flex items-center justify-between text-left bg-white border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <span class="flex items-center bg-gray-100 text-gray-700 text-sm font-medium px-2 py-1 rounded">
                                <span x-text="selected"></span>
                                <svg @click.stop="selected = 'Jangan Ulangi'" xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 ml-2 text-gray-500 hover:text-gray-800 cursor-pointer" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>

                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open" x-transition
                            class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200">
                            <ul class="py-1">
                                <template x-for="option in options" :key="option">
                                    <li @click="selected = option; open = false"
                                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                        x-text="option">
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Peserta -->
                <div class="flex flex-col gap-2">
                    <label class="mb-1 font-medium text-sm md:text-base text-black">Peserta <span
                            class="text-red-500">*</span></label>

                    @php
                        $selectedParticipantIds = old(
                            'participants',
                            $event->participants->pluck('user_id')->toArray(),
                        );
                    @endphp

                    <div x-data="{
                        openPopup: false,
                        selectedParticipants: {{ json_encode($selectedParticipantIds) }},
                        searchQuery: '',
                        allMembers: {{ json_encode(
                            $members->map(function ($m) {
                                    return [
                                        'id' => $m->id,
                                        'full_name' => $m->full_name,
                                        'avatar_url' =>
                                            $m->avatar_url ??
                                            'https://ui-avatars.com/api/?name=' .
                                                urlencode($m->full_name) .
                                                '&background=3B82F6&color=fff&bold=true&size=128',
                                    ];
                                })->toArray(),
                        ) }},
                        get filteredMembers() {
                            if (!this.searchQuery) return this.allMembers;
                            return this.allMembers.filter(member =>
                                member.full_name.toLowerCase().includes(this.searchQuery.toLowerCase())
                            );
                        },
                        get displayedParticipants() {
                            return this.allMembers.filter(member =>
                                this.selectedParticipants.includes(member.id)
                            );
                        },
                        toggleParticipant(userId) {
                            const index = this.selectedParticipants.indexOf(userId);
                            if (index > -1) {
                                this.selectedParticipants.splice(index, 1);
                            } else {
                                this.selectedParticipants.push(userId);
                            }
                        },
                        toggleAll() {
                            if (this.selectedParticipants.length === this.allMembers.length) {
                                this.selectedParticipants = [];
                            } else {
                                this.selectedParticipants = this.allMembers.map(m => m.id);
                            }
                        },
                        isSelected(userId) {
                            return this.selectedParticipants.includes(userId);
                        }
                    }">
                        <!-- Display selected participants -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <template x-for="participant in displayedParticipants" :key="participant.id">
                                <div class="relative">
                                    <img :src="participant.avatar_url" :alt="participant.full_name"
                                        class="rounded-full border-2 border-gray-200 w-10 h-10 object-cover"
                                        :title="participant.full_name"
                                        onerror="this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(this.alt) + '&background=3B82F6&color=fff&bold=true&size=128'" />
                                </div>
                            </template>

                            <!-- Tombol tambah peserta -->
                            <button @click.prevent="openPopup = true" type="button"
                                class="w-10 h-10 rounded-full bg-blue-500 hover:bg-blue-600 flex items-center justify-center transition-colors">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>

                        <!-- Hidden inputs for selected participants -->
                        <template x-for="participantId in selectedParticipants" :key="participantId">
                            <input type="hidden" name="participants[]" :value="participantId">
                        </template>

                        <!-- POPUP TAMBAH PESERTA -->
                        <div x-show="openPopup" @click.self="openPopup = false"
                            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 popup-wrapper p-4"
                            x-cloak style="display: none;">
                            <!-- Card Popup -->
                            <div class="bg-white rounded-xl shadow-lg p-4 md:p-5 w-full max-w-sm popup-card" @click.stop>
                                <!-- Header -->
                                <h2 class="text-center font-bold text-base md:text-lg mb-3">Tambah Peserta</h2>

                                <!-- Input cari -->
                                <input type="text" x-model="searchQuery" placeholder="Cari anggota..."
                                    class="border w-full rounded-md px-3 py-2 mb-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />

                                <!-- Pilih semua -->
                                <div class="flex items-center justify-between border-b-2 border-gray-300 px-2 pb-3 mb-3">
                                    <span class="font-medium text-sm">Pilih Semua</span>
                                    <input type="checkbox" @click="toggleAll()"
                                        :checked="selectedParticipants.length === allMembers.length"
                                        class="w-5 h-5 rounded-md accent-blue-600 cursor-pointer" />
                                </div>

                                <!-- List members -->
                                <div class="flex flex-col gap-2 max-h-60 overflow-y-auto px-2">
                                    <template x-for="member in filteredMembers" :key="member.id">
                                        <div class="flex items-center justify-between py-1">
                                            <div class="flex items-center gap-2">
                                                <img :src="member.avatar_url"
                                                    class="w-8 h-8 rounded-full object-cover border border-gray-200"
                                                    :alt="member.full_name"
                                                    onerror="this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent(this.alt) + '&background=3B82F6&color=fff&bold=true&size=128'" />
                                                <span class="text-sm" x-text="member.full_name"></span>
                                            </div>
                                            <input type="checkbox" :checked="isSelected(member.id)"
                                                @click="toggleParticipant(member.id)"
                                                class="w-5 h-5 rounded-md accent-blue-600 cursor-pointer" />
                                        </div>
                                    </template>

                                    <!-- No results -->
                                    <div x-show="filteredMembers.length === 0"
                                        class="text-center text-gray-500 py-4 text-sm">
                                        Tidak ada anggota ditemukan
                                    </div>
                                </div>

                                <!-- Tombol Simpan -->
                                <div class="flex justify-end mt-4">
                                    <button @click.prevent="openPopup = false" type="button"
                                        class="bg-[#102a63] text-white px-6 py-2 rounded-md font-inter text-sm hover:bg-[#0d1f4d] transition-colors">
                                        Simpan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Privasi & Rapat -->
                <div class="flex flex-col gap-4">
                    <!-- Privasi -->
                    <div class="flex flex-col gap-2" x-data="{ isPrivate: {{ old('is_private', $event->is_private ? 1 : 0) }} }">
                        <label class="mb-1 font-medium text-sm md:text-base text-black font-inter">Privasi</label>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_private" :value="isPrivate ? 1 : 0">
                            <!-- Toggle switch Rahasia -->
                            <button type="button" @click="isPrivate = !isPrivate"
                                :class="isPrivate ? 'bg-[#102a63]' : 'bg-gray-300'"
                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors">
                                <span :class="isPrivate ? 'translate-x-6' : 'translate-x-1'"
                                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
                            </button>
                            <span class="text-sm font-medium text-[#102a63] font-inter">Rahasia</span>
                        </div>
                        <span class="text-xs md:text-sm text-gray-500 font-inter">Hanya peserta yang diundang bisa
                            melihat</span>
                    </div>
                    <!-- ✅ MODE RAPAT: ONLINE (DEFAULT) / OFFLINE -->
                    <div class="flex flex-col gap-3" x-data="{
                        meetingMode: '{{ old('meeting_mode', $event->is_online_meeting ? 'online' : 'offline') }}'
                    }">
                        <span class="font-medium text-sm md:text-base text-black font-inter">
                            Mode Rapat <span class="text-red-500">*</span>
                        </span>

                        <!-- Radio Buttons -->
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="meeting_mode" value="online" @click="meetingMode = 'online'"
                                    :checked="meetingMode === 'online'"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700">Online</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="meeting_mode" value="offline"
                                    @click="meetingMode = 'offline'" :checked="meetingMode === 'offline'"
                                    class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700">Offline</span>
                            </label>
                        </div>

                        <!-- Input Link Rapat (Online) -->
                        <div x-show="meetingMode === 'online'" x-transition class="flex flex-col">
                            <label class="mb-1 font-medium font-inter text-sm md:text-base">
                                Link Rapat <span class="text-red-500">*</span>
                            </label>
                            <input type="url" name="meeting_link"
                                value="{{ old('meeting_link', $event->meeting_link) }}"
                                placeholder="https://zoom.us/j/..." :required="meetingMode === 'online'"
                                class="border rounded-md px-4 py-2 focus:outline-none font-inter text-sm placeholder-[#6B7280] border-[#6B7280] focus:ring-2 focus:ring-blue-500" />
                            <span class="text-xs text-gray-400 font-inter mt-1">Masukkan link Zoom, Google Meet,
                                dll.</span>
                            @error('meeting_link')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Input Lokasi (Offline) -->
                        <div x-show="meetingMode === 'offline'" x-transition class="flex flex-col">
                            <label class="mb-1 font-medium font-inter text-sm md:text-base">
                                Lokasi Rapat <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="location" value="{{ old('location', $event->location) }}"
                                placeholder="Contoh: Ruang Meeting Lt. 3" :required="meetingMode === 'offline'"
                                class="border rounded-md px-4 py-2 focus:outline-none font-inter text-sm placeholder-[#6B7280] border-[#6B7280] focus:ring-2 focus:ring-blue-500" />
                            <span class="text-xs text-gray-400 font-inter mt-1">Masukkan lokasi tempat rapat</span>
                            @error('location')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="flex flex-col">
                    <label class="mb-2 font-medium text-sm md:text-base text-gray-700">
                        Catatan <span class="text-red-500">*</span>
                    </label>
                    <input type="hidden" name="description" id="description-input">
                    <div id="catatan-editor"></div>
                </div>

                <!-- Tombol Update & Batal -->
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition text-sm md:text-base font-semibold shadow-md hover:shadow-lg flex-1 md:flex-none">
                        Update
                    </button>
                    <a href="{{ route('jadwal-umum.show', ['id' => $event->id]) }}"
                        class="border-2 border-blue-600 text-blue-600 px-6 py-2.5 rounded-lg hover:bg-blue-50 transition flex items-center justify-center text-sm md:text-base font-semibold flex-1 md:flex-none">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <script>
        let catatanEditor = null;

        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').min = today;
            document.getElementById('end_date').min = today;

            document.getElementById('start_date').addEventListener('change', function() {
                document.getElementById('end_date').min = this.value;
            });

            // Initialize CKEditor dengan data existing
            ClassicEditor
                .create(document.getElementById('catatan-editor'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'underline', '|', 'link', 'bulletedList',
                        'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo'
                    ],
                    heading: {
                        options: [{
                                model: 'paragraph',
                                title: 'Paragraph',
                                class: 'ck-heading_paragraph'
                            },
                            {
                                model: 'heading1',
                                view: 'h1',
                                title: 'Heading 1',
                                class: 'ck-heading_heading1'
                            },
                            {
                                model: 'heading2',
                                view: 'h2',
                                title: 'Heading 2',
                                class: 'ck-heading_heading2'
                            },
                            {
                                model: 'heading3',
                                view: 'h3',
                                title: 'Heading 3',
                                class: 'ck-heading_heading3'
                            }
                        ]
                    },
                    placeholder: 'Masukkan catatan anda disini...'
                })
                .then(editor => {
                    catatanEditor = editor;
                    // Set existing content
                    const existingContent = {!! json_encode(old('description', $event->description)) !!};
                    if (existingContent) {
                        editor.setData(existingContent);
                    }
                })
                .catch(error => console.error('CKEditor error:', error));

            document.getElementById('scheduleForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const startDate = document.getElementById('start_date').value;
                const startTime = document.getElementById('start_time').value;
                document.getElementById('start_datetime').value = `${startDate} ${startTime}`;

                const endDate = document.getElementById('end_date').value;
                const endTime = document.getElementById('end_time').value;
                document.getElementById('end_datetime').value = `${endDate} ${endTime}`;

                if (catatanEditor) {
                    document.getElementById('description-input').value = catatanEditor.getData();
                }

                if (!startDate || !startTime || !endDate || !endTime) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Mohon isi tanggal dan waktu dengan lengkap',
                        confirmButtonColor: '#3B82F6'
                    });
                    return false;
                }

                const startDateTime = new Date(`${startDate} ${startTime}`);
                const endDateTime = new Date(`${endDate} ${endTime}`);

                if (endDateTime <= startDateTime) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Waktu akhir harus lebih besar dari waktu mulai',
                        confirmButtonColor: '#3B82F6'
                    });
                    return false;
                }

                this.submit();
            });
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                confirmButtonColor: '#3B82F6',
                timer: 3000,
                showConfirmButton: true
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#EF4444',
            });
        </script>
    @endif

    <style>
        [x-cloak] {
            display: none !important;
        }

        #catatan-editor {
            min-height: 180px;
        }

        .ck-editor__editable {
            min-height: 180px;
            max-height: 400px;
        }

        .ck.ck-editor__main>.ck-editor__editable {
            background-color: #ffffff;
            border: 1px solid #D1D5DB !important;
            border-radius: 0 0 0.5rem 0.5rem;
        }

        .ck.ck-editor__top .ck-sticky-panel .ck-toolbar {
            border: 1px solid #D1D5DB !important;
            border-bottom: none !important;
            border-radius: 0.5rem 0.5rem 0 0;
            background-color: #F9FAFB;
        }

        .ck.ck-editor__editable:not(.ck-editor__nested-editable).ck-focused {
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        }

        @media (max-width: 768px) {
            .ck-editor__editable {
                min-height: 150px;
                font-size: 14px;
            }
        }
    </style>
@endsection
