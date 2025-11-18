@extends('layouts.app')

@section('title', 'Buat Jadwal')

@section('content')
    <div class="bg-[#e9effd] min-h-screen">
        @include('components.workspace-nav', ['active' => 'jadwal'])

        <div class="min-h-screen flex justify-center pt-10 bg-[#f3f6fc] responsive-wrapper">
            <!-- Form -->
            <form action="{{ route('calendar.store', ['workspaceId' => $workspaceId]) }}" method="POST" id="scheduleForm"
                class="bg-white rounded-xl shadow-xl p-6 w-full max-w-3xl flex flex-col gap-6 form-container">
                @csrf

                <!-- Alert Messages -->
                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif
                <!-- Judul -->
                <h2 class="text-xl font-inter font-bold text-[20px] text-[#102a63] border-b pb-2">Buat Jadwal</h2>

                <!-- Nama Jadwal -->
                <div class="flex flex-col font-inter">
                    <label class="mb-1 font-medium text-[16px] text-black">Nama Jadwal <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Masukkan nama jadwal..."
                        class="border rounded-md pl-5 py-2 focus:outline-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280]"
                        required />
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal & Waktu Mulai dan Akhir -->
                <div class="grid grid-cols-2 gap-4 datetime-grid">
                    <!-- Mulai -->
                    <div class="flex flex-col font-inter">
                        <label class="mb-1 font-medium text-[16px] text-black">Mulai <span
                                class="text-red-500">*</span></label>
                        <div class="flex gap-2 datetime-inputs">
                            <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}"
                                class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5"
                                required />
                            <input type="time" name="start_time" id="start_time" value="{{ old('start_time') }}"
                                class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5"
                                required />
                        </div>
                        @error('start_datetime')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Akhir -->
                    <div class="flex flex-col font-inter">
                        <label class="mb-1 font-medium text-[16px] text-black">Akhir <span
                                class="text-red-500">*</span></label>
                        <div class="flex gap-2 datetime-inputs">
                            <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                                class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5"
                                required />
                            <input type="time" name="end_time" id="end_time" value="{{ old('end_time') }}"
                                class="border rounded-md p-2 w-1/2 font-inter text-[14px] text-[#6B7280] placeholder-[#6B7280] border-[#6B7280] focus:outline-none pl-5"
                                required />
                        </div>
                        @error('end_datetime')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Hidden fields for combined datetime -->
                <input type="hidden" name="start_datetime" id="start_datetime">
                <input type="hidden" name="end_datetime" id="end_datetime">

                <!-- Pengulangan -->
                <div class="flex flex-col font-sans">
                    <label for="pengulangan-btn" class="mb-1 font-medium text-base text-black">
                        Pengulangan<span class="text-red-500">*</span>
                    </label>

                    <div x-data="{
                        open: false,
                        options: ['Jangan Ulangi', 'Setiap hari', 'Setiap minggu', 'Setiap kuartal', 'Setiap tahun', 'Setiap hari kerja (Sen - Jum)'],
                        selected: '{{ old('recurrence', 'Jangan Ulangi') }}'
                    }" class="relative w-full md:w-1/3 pengulangan-dropdown">
                        <input type="hidden" name="recurrence" x-model="selected">
                        <button type="button" @click="open = !open" @click.away="open = false" id="pengulangan-btn"
                            class="w-full flex items-center justify-between text-left bg-white border border-gray-300 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    <label class="mb-1 font-medium text-[16px] text-black">Peserta <span
                            class="text-red-500">*</span></label>

                    <div x-data="{
                        openPopup: false,
                        selectedParticipants: {{ json_encode(old('participants', [Auth::id()])) }},
                        searchQuery: '',
                        allMembers: {{ json_encode($members->toArray()) }},
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
                                    <img :src="participant.avatar ? '/storage/' + participant.avatar : '/images/default-avatar.png'"
                                        :alt="participant.full_name" class="rounded-full border w-10 h-10"
                                        :title="participant.full_name" />
                                </div>
                            </template>

                            <!-- Tombol tambah peserta -->
                            <button @click="openPopup = true" type="button">
                                <img src="/images/icons/Plus.png" alt="Tambah" class="w-10 h-10" />
                            </button>
                        </div>

                        <!-- Hidden inputs for selected participants -->
                        <template x-for="participantId in selectedParticipants" :key="participantId">
                            <input type="hidden" name="participants[]" :value="participantId">
                        </template>

                        <!-- POPUP TAMBAH PESERTA -->
                        <div x-show="openPopup"
                            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 popup-wrapper"
                            x-cloak>
                            <!-- Card Popup -->
                            <div class="bg-white rounded-xl shadow-lg p-5 w-[350px] popup-card"
                                @click.away="openPopup = false">
                                <!-- Header -->
                                <h2 class="text-center font-bold text-[18px] mb-3">Tambah Peserta</h2>

                                <!-- Input cari -->
                                <input type="text" x-model="searchQuery" placeholder="Cari anggota..."
                                    class="border w-full rounded-md px-3 py-2 mb-3 text-sm" />

                                <!-- Pilih semua -->
                                <div class="flex items-center justify-between border-b-2 border-black px-2 pb-3">
                                    <span class="font-medium">Pilih Semua</span>
                                    <input type="checkbox" @click="toggleAll()"
                                        :checked="selectedParticipants.length === allMembers.length"
                                        class="w-5 h-5 rounded-md accent-blue" />
                                </div>

                                <!-- List members -->
                                <div class="flex flex-col gap-2 max-h-40 overflow-y-auto px-2 pt-3">
                                    <template x-for="member in filteredMembers" :key="member.id">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <img :src="member.avatar ? '/storage/' + member.avatar : '/images/dk.jpg'"
                                                    class="w-6 h-6 rounded-full" :alt="member.full_name" />
                                                <span x-text="member.full_name"></span>
                                            </div>
                                            <input type="checkbox" :checked="isSelected(member.id)"
                                                @click="toggleParticipant(member.id)"
                                                class="w-5 h-5 rounded-md accent-blue" />
                                        </div>
                                    </template>

                                    <!-- No results -->
                                    <div x-show="filteredMembers.length === 0" class="text-center text-gray-500 py-4">
                                        Tidak ada anggota ditemukan
                                    </div>
                                </div>

                                <!-- Tombol Simpan -->
                                <div class="flex justify-end mt-4">
                                    <button @click="openPopup = false" type="button"
                                        class="bg-[#102a63] text-white px-4 py-1 rounded-md font-inter text-inter">
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
                    <div class="flex flex-col gap-2" x-data="{ isPrivate: {{ old('is_private', 0) }} }">
                        <label class="mb-1 font-medium text-[16px] text-black font-inter">Privasi</label>
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
                        <span class="text-sm text-gray-500 font-inter">Hanya peserta yang diundang bisa melihat</span>
                    </div>

                    <!-- Rapat -->
                    <div class="flex flex-col gap-2" x-data="{ isOnlineMeeting: {{ old('is_online_meeting', 0) }} }">
                        <span class="font-medium text-[16px] text-black font-inter">Apakah anda akan mengadakan
                            rapat?</span>
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="is_online_meeting" :value="isOnlineMeeting ? 1 : 0">
                            <!-- Toggle switch Rapat -->
                            <button type="button" @click="isOnlineMeeting = !isOnlineMeeting"
                                :class="isOnlineMeeting ? 'bg-[#102a63]' : 'bg-gray-300'"
                                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors">
                                <span :class="isOnlineMeeting ? 'translate-x-6' : 'translate-x-1'"
                                    class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
                            </button>
                            <span class="text-sm font-medium text-[#102a63] font-inter">Rapat</span>
                        </div>

                        <!-- Link Rapat (Conditional) -->
                        <div x-show="isOnlineMeeting" x-transition class="flex flex-col mt-2">
                            <label class="mb-1 font-medium font-inter text-[16px]">Link Rapat (Opsional)</label>
                            <input type="url" name="meeting_link" value="{{ old('meeting_link') }}"
                                placeholder="Masukkan link rapat anda..."
                                class="border rounded-md pl-5 py-2 focus:outline-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280]" />
                            <span class="text-xs text-gray-400 font-inter">Opsional - isi jika rapat dilakukan
                                online</span>
                            @error('meeting_link')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Catatan (Description using CKEditor) -->
                <div class="flex flex-col">
                    <label class="mb-1 font-medium text-[16px]">Catatan <span class="text-red-500">*</span></label>
                    <input type="hidden" name="description" id="description-input">
                    <!-- CKEditor Container -->
                    <div id="catatan-editor"></div>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol Kirim & Batal -->
                <div class="flex gap-2 action-buttons">
                    <button type="submit"
                        class="bg-blue-600 text-white w-[80px] h-[30px] rounded-md hover:bg-blue-700 transition">
                        Kirim
                    </button>
                    <a href="{{ route('jadwal', ['workspaceId' => $workspaceId]) }}"
                        class="border border-blue-600 text-blue-600 w-[80px] h-[30px] rounded-md hover:bg-blue-50 transition flex items-center justify-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Alpine.js & CKEditor --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <script>
        // Store editor instance
        let catatanEditor = null;

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').min = today;
            document.getElementById('end_date').min = today;

            // Set default times
            const now = new Date();
            const startTime =
                `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
            const endTime =
                `${String(now.getHours() + 1).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

            if (!document.getElementById('start_time').value) {
                document.getElementById('start_time').value = startTime;
            }
            if (!document.getElementById('end_time').value) {
                document.getElementById('end_time').value = endTime;
            }

            // Auto set end date when start date changes
            document.getElementById('start_date').addEventListener('change', function() {
                if (!document.getElementById('end_date').value) {
                    document.getElementById('end_date').value = this.value;
                }
                document.getElementById('end_date').min = this.value;
            });

            // Initialize CKEditor
            const editorElement = document.getElementById('catatan-editor');
            if (editorElement) {
                ClassicEditor
                    .create(editorElement, {
                        toolbar: [
                            'heading', '|',
                            'bold', 'italic', 'underline', '|',
                            'link', 'bulletedList', 'numberedList', '|',
                            'blockQuote', 'insertTable', '|',
                            'undo', 'redo'
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
                        console.log('CKEditor initialized');

                        // Set old value if exists
                        @if (old('description'))
                            editor.setData({{ json_encode(old('description')) }});
                        @endif
                    })
                    .catch(error => {
                        console.error('CKEditor error:', error);
                        // Fallback to textarea
                        editorElement.innerHTML = `
                    <textarea
                        name="description"
                        placeholder="Masukkan catatan anda disini..."
                        class="border rounded-md p-2 h-32 resize-none font-inter text-[14px] placeholder-[#6B7280] border-[#6B7280] pl-5 w-full"
                    >{{ old('description') }}</textarea>
                `;
                    });
            }

            // Form submission handler
            document.getElementById('scheduleForm').addEventListener('submit', function(e) {
                // Combine date and time for start_datetime
                const startDate = document.getElementById('start_date').value;
                const startTime = document.getElementById('start_time').value;
                document.getElementById('start_datetime').value = `${startDate} ${startTime}`;

                // Combine date and time for end_datetime
                const endDate = document.getElementById('end_date').value;
                const endTime = document.getElementById('end_time').value;
                document.getElementById('end_datetime').value = `${endDate} ${endTime}`;

                // Get CKEditor data
                if (catatanEditor) {
                    document.getElementById('description-input').value = catatanEditor.getData();
                }

                // Validate
                if (!startDate || !startTime || !endDate || !endTime) {
                    e.preventDefault();
                    alert('Mohon isi tanggal dan waktu dengan lengkap');
                    return false;
                }

                const startDateTime = new Date(`${startDate} ${startTime}`);
                const endDateTime = new Date(`${endDate} ${endTime}`);

                if (endDateTime <= startDateTime) {
                    e.preventDefault();
                    alert('Waktu akhir harus lebih besar dari waktu mulai');
                    return false;
                }
            });
        });
    </script>

    <style>
        /* CKEditor Custom Styles */
        #catatan-editor {
            min-height: 200px;
        }

        .ck-editor__editable {
            min-height: 200px;
            max-height: 400px;
        }

        .ck.ck-editor__main>.ck-editor__editable {
            background-color: #ffffff;
            border: 1px solid #6B7280 !important;
            border-radius: 0 0 0.375rem 0.375rem;
        }

        .ck.ck-editor__top .ck-sticky-panel .ck-toolbar {
            border: 1px solid #6B7280 !important;
            border-bottom: none !important;
            border-radius: 0.375rem 0.375rem 0 0;
            background-color: #f9fafb;
        }

        .ck.ck-toolbar {
            border-color: #6B7280 !important;
        }

        .ck.ck-editor__editable:not(.ck-editor__nested-editable).ck-focused {
            border-color: #6B7280 !important;
            box-shadow: none !important;
        }

        /* Alpine.js cloak */
        [x-cloak] {
            display: none !important;
        }

        /* Responsive Styles */
        @media (max-width: 1024px) {
            .responsive-wrapper {
                padding: 1.5rem !important;
            }

            .form-container {
                padding: 1.5rem !important;
                margin: 0 1rem !important;
            }

            .pengulangan-dropdown {
                width: 50% !important;
            }
        }

        @media (max-width: 768px) {
            .responsive-wrapper {
                padding: 1rem !important;
                padding-top: 2rem !important;
            }

            .form-container {
                padding: 1rem !important;
                margin: 0 0.5rem !important;
                gap: 1rem !important;
            }

            .datetime-grid {
                grid-columns: 1 !important;
                display: flex !important;
                flex-direction: column !important;
            }

            .pengulangan-dropdown {
                width: 100% !important;
            }

            .popup-card {
                width: 90% !important;
                max-width: 350px !important;
                margin: 0 1rem !important;
            }
        }

        @media (max-width: 480px) {
            .responsive-wrapper {
                padding: 0.5rem !important;
                padding-top: 1.5rem !important;
            }

            .form-container {
                padding: 0.75rem !important;
                margin: 0 !important;
                gap: 0.75rem !important;
                border-radius: 0.5rem !important;
            }

            .form-container h2 {
                font-size: 18px !important;
            }

            .form-container label {
                font-size: 14px !important;
            }

            .form-container input,
            .form-container textarea,
            .form-container select {
                font-size: 13px !important;
                padding: 0.5rem !important;
            }

            .datetime-inputs {
                flex-direction: column !important;
                gap: 0.5rem !important;
            }

            .datetime-inputs input {
                width: 100% !important;
            }

            .action-buttons {
                flex-direction: column !important;
            }

            .action-buttons button,
            .action-buttons a {
                width: 100% !important;
                height: 36px !important;
            }

            .popup-wrapper {
                padding: 1rem !important;
            }

            .popup-card {
                width: 100% !important;
                padding: 1rem !important;
                margin: 0 !important;
            }

            .popup-card h2 {
                font-size: 16px !important;
            }

            #catatan-editor {
                min-height: 150px !important;
            }

            .ck-editor__editable {
                min-height: 150px !important;
                font-size: 13px !important;
            }

            .ck.ck-toolbar {
                font-size: 12px !important;
            }

            .ck.ck-toolbar .ck-toolbar__items {
                flex-wrap: wrap !important;
            }
        }

        @media (max-width: 375px) {
            .form-container {
                padding: 0.5rem !important;
            }

            .form-container h2 {
                font-size: 16px !important;
            }

            .form-container label {
                font-size: 13px !important;
            }

            .form-container input,
            .form-container textarea {
                font-size: 12px !important;
            }
        }
    </style>
@endsection
