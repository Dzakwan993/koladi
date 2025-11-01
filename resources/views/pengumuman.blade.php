@extends('layouts.app')

@section('title', 'Workspace')

@section('content')
    <!-- Tambahkan font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <div class="bg-[#e9effd] min-h-screen font-[Inter,sans-serif] text-black relative">
        @include('components.workspace-nav')

        <div class="max-w-5xl mx-auto py-8 px-4">
            <!-- Tombol Buat Pengumuman -->
            <div class="flex justify-start mb-1">
                <button id="btnPopup"
                    class="bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold hover:opacity-90 transition flex items-center gap-2">
                    <img src="images/icons/plusWhite.svg" alt="Plus" class="w-7 h-7">
                    Buat Pengumuman
                </button>
            </div>

            @php
                \Carbon\Carbon::setLocale('id');
            @endphp

            <div class="max-w-5xl mx-auto mt-4">
                <div class="bg-white rounded-2xl shadow-md p-6 h-[500px] overflow-hidden">
                    <div class="space-y-4 h-full overflow-y-auto pr-2">

                        @forelse($pengumumans as $p)
                            @php
                                $canAccess = $p->isVisibleTo($user);
                            @endphp

                            <div
                                @if ($canAccess) onclick="window.location='{{ route('pengumuman.show', $p->id) }}'"
                        class="cursor-pointer bg-[#e9effd] hover:bg-[#dce6fc] transition-colors rounded-xl shadow-sm p-4 flex justify-between items-start"
                    @else
                        class="bg-[#e9effd] rounded-xl shadow-sm p-4 flex justify-between items-start opacity-70"
                        title="Private - Anda tidak memiliki akses" @endif>
                                <div class="flex items-start space-x-3">
                                    <img src="https://i.pravatar.cc/36" alt="Avatar" class="rounded-full w-10 h-10">
                                    <div>
                                        <p class="font-semibold">{{ $p->creator->full_name ?? 'Unknown' }}</p>

                                        <p class="font-medium flex items-center gap-1 text-[#000000]/80">
                                            @if ($p->is_private)
                                                <img src="{{ asset('images/icons/Lock.svg') }}" alt="Lock"
                                                    class="w-5 h-5">
                                            @endif

                                            @if ($canAccess)
                                                <span class="hover:underline text-black font-semibold font-inter">
                                                    {{ $p->title }}
                                                </span>
                                            @else
                                                <span class="font-medium text-gray-500">
                                                    {{ $p->title }}
                                                </span>
                                            @endif
                                        </p>

                                        <p class="text-sm text-gray-500">{!! $p->description !!}</p>

                                        <div class="flex items-center space-x-2 mt-2">
                                            @if ($p->due_date)
                                                <span
                                                    class="bg-[#6B7280] text-white text-xs font-medium px-2 py-1 flex rounded-md items-center gap-1">
                                                    {{ \Carbon\Carbon::parse($p->due_date)->translatedFormat('d M') }}
                                                </span>
                                            @endif
                                            @if ($p->auto_due)
                                                <span class="text-xs text-[#102a63]/60 font-medium">
                                                    Selesai otomatis:
                                                    {{ \Carbon\Carbon::parse($p->auto_due)->translatedFormat('d M Y') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col space-y-6 items-center">
                                    <span class="bg-[#102a63]/10 text-black text-xs px-2 py-1 rounded-md font-medium">
                                        {{ \Carbon\Carbon::parse($p->created_at)->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-4">
                                Tidak ada pengumuman
                            </div>
                        @endforelse

                    </div>
                </div>
            </div>




        </div>

        <!-- POPUP -->
        <div id="popupForm" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
            <div class="bg-white rounded-2xl shadow-lg p-6 w-full max-w-3xl">
                <h2 class="text-xl font-bold mb-4 text-[#102a63] border-b pb-2">Buat Pengumuman</h2>

                <form action="{{ route('pengumuman.store', ['id' => $workspace->id]) }}" method="POST" class="space-y-5"
                    id="pengumumanForm">
                    @csrf
                    <!-- Judul -->
                    <div>
                        <label class="block text-sm font-inter font-semibold text-black mb-1">
                            Judul Pengumuman <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="title" placeholder="Masukkan judul pengumuman..."
                            class="w-full border border-[#6B7280] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 font-[Inter] text-[14px] placeholder:text-[#6B7280] pl-5" />

                        <!-- Catatan -->
                        <div class="flex flex-col" x-data x-init="createEditorFor('catatan-editor', { placeholder: 'Masukkan catatan anda disini...' })">
                            <label class="block text-sm font-inter font-semibold text-black mb-1 mt-5">
                                Deskripsi <span class="text-red-500">*</span>
                            </label>


                            <!-- CKEditor Container -->
                            <div id="catatan-editor"
                                class="border border-[#6B7280] rounded-lg bg-white min-h-[160px] p-2 font-[Inter] text-[14px] placeholder-[#6B7280]">
                            </div>
                            <input type="hidden" name="description" id="catatanInput">
                        </div>

                        <!-- CKEditor CDN -->
                        <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>

                        <script>
                            async function createEditorFor(containerId, options = {}) {
                                const el = document.getElementById(containerId);
                                if (!el) return console.warn('Element not found:', containerId);

                                try {
                                    const editor = await ClassicEditor.create(el, {
                                        toolbar: {
                                            items: [
                                                'undo', 'redo', '|',
                                                'heading', '|',
                                                'bold', 'italic', 'underline', 'strikethrough', '|',
                                                'link', 'blockQuote', '|',
                                                'bulletedList', 'numberedList', '|',
                                                'insertTable', 'imageUpload', 'mediaEmbed'
                                            ],
                                            shouldNotGroupWhenFull: true
                                        },
                                        heading: {
                                            options: [{
                                                    model: 'paragraph',
                                                    title: 'Paragraf',
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
                                                }
                                            ]
                                        },
                                        fontFamily: {
                                            options: ['Inter, sans-serif', 'Arial, Helvetica, sans-serif',
                                                'Courier New, Courier, monospace'
                                            ],
                                        },
                                        fontSize: {
                                            options: ['14px', '16px', '18px', '24px', '32px']
                                        },
                                        fontColor: {
                                            columns: 5,
                                            colors: [{
                                                    color: '#000000',
                                                    label: 'Black'
                                                },
                                                {
                                                    color: '#102a63',
                                                    label: 'Dark Blue'
                                                },
                                                {
                                                    color: '#6B7280',
                                                    label: 'Gray'
                                                },
                                                {
                                                    color: '#FFFFFF',
                                                    label: 'White'
                                                }
                                            ]
                                        },
                                        simpleUpload: {
                                            uploadUrl: '/upload',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                            }
                                        },
                                        placeholder: options.placeholder || ''
                                    });

                                    // Tambahkan tombol Upload File ke toolbar
                                    insertUploadFileButtonToToolbar(editor);

                                    // Styling editor content agar sama dengan input judul
                                    editor.editing.view.change(writer => {
                                        writer.setStyle('font-family', 'Inter, sans-serif', editor.editing.view.document.getRoot());
                                        writer.setStyle('font-size', '14px', editor.editing.view.document.getRoot());
                                        writer.setStyle('color', '#000000', editor.editing.view.document.getRoot());
                                    });

                                    window[containerId + '_editor'] = editor;

                                } catch (err) {
                                    console.error('CKEditor init error:', err);
                                }
                            }

                            // Fungsi untuk menambahkan tombol Upload File
                            function insertUploadFileButtonToToolbar(editor) {
                                try {
                                    const toolbarEl = editor.ui.view.toolbar.element;
                                    const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                                    const btn = document.createElement('button');
                                    btn.type = 'button';
                                    btn.className = 'ck ck-button';
                                    btn.title = 'Upload File';
                                    btn.innerHTML = `
            <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
                ${fileIconSVG()}
            </span>
        `;
                                    btn.style.marginLeft = '6px';
                                    btn.style.padding = '4px 8px';
                                    btn.style.borderRadius = '6px';
                                    btn.style.background = 'transparent';
                                    btn.style.border = '0';
                                    btn.style.cursor = 'pointer';

                                    btn.addEventListener('click', () => {
                                        const input = document.createElement('input');
                                        input.type = 'file';
                                        input.accept = '*/*';
                                        input.click();

                                        input.addEventListener('change', async (e) => {
                                            const file = e.target.files && e.target.files[0];
                                            if (!file) return;

                                            const originalHTML = btn.innerHTML;
                                            btn.innerHTML = 'Uploading...';

                                            const formData = new FormData();
                                            formData.append('upload', file);

                                            try {
                                                const res = await fetch('/upload', {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                    },
                                                    body: formData
                                                });

                                                const data = await res.json();

                                                if (res.ok && data.url) {
                                                    editor.model.change(writer => {
                                                        const insertPos = editor.model.document.selection
                                                            .getFirstPosition();
                                                        const linkText = writer.createText(file.name, {
                                                            linkHref: data.url
                                                        });
                                                        editor.model.insertContent(linkText, insertPos);
                                                    });
                                                } else {
                                                    console.error('Upload response:', data);
                                                    alert('Upload gagal. Cek console untuk detail.');
                                                }
                                            } catch (error) {
                                                console.error('Upload error:', error);
                                                alert('Terjadi kesalahan saat upload file.');
                                            } finally {
                                                btn.innerHTML = originalHTML;
                                            }
                                        }, {
                                            once: true
                                        });
                                    });

                                    // Sisipkan sebelum tombol "Insert Table" (kalau ada)
                                    const insertTableBtn = Array.from(itemsContainer.children).find(
                                        el => el.title?.toLowerCase().includes('table')
                                    );

                                    if (insertTableBtn) {
                                        itemsContainer.insertBefore(btn, insertTableBtn);
                                    } else {
                                        itemsContainer.appendChild(btn);
                                    }

                                } catch (err) {
                                    console.error('Insert upload button error:', err);
                                }
                            }

                            // Ikon SVG untuk tombol Upload File
                            function fileIconSVG() {
                                return `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="18" height="18">
            <path fill="currentColor" d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2 4 4H10V4z"/>
        </svg>
    `;
                            }
                        </script>




                        <!-- Tenggat -->
                        <div>
                            <label class="block font-medium text-[14px] mb-2 mt-5 text-black font-[Inter]">
                                Tenggat Pengumuman hingga selesai <span class="text-red-500">*</span>
                            </label>

                            <div class="flex items-center gap-3 mb-3 mt-3 relative">
                                <!-- Select chip 1 -->
                                <div
                                    class="flex items-center rounded-lg border border-[#d0d7e2] overflow-hidden chip-container-1">
                                    <div
                                        class="flex items-center bg-[#6B7280] text-white text-[14px] font-[Inter] px-3 py-1.5 rounded-l-lg chip-text-1">
                                        Selesai otomatis
                                    </div>
                                    <button type="button" class="px-2 text-gray-500 hover:text-gray-700 dropdown-btn-1">
                                        <img src="images/icons/down.svg" alt="down">
                                    </button>
                                </div>

                                <span class="text-[14px] text-black font-[Inter]" id="labelText">Selesai otomatis
                                    pada:</span>

                                <!-- Select chip 2 (Dropdown) -->
                                <div class="flex items-center rounded-lg border border-[#d0d7e2] overflow-hidden chip-container-2"
                                    id="dropdownChip">
                                    <div
                                        class="flex items-center bg-[#6B7280] text-white text-[14px] font-[Inter] px-3 py-1.5 rounded-l-lg chip-text-2">
                                        1 hari dari sekarang
                                    </div>
                                    <button type="button" class="px-2 text-gray-500 hover:text-gray-700 dropdown-btn-2">
                                        <img src="images/icons/down.svg" alt="down">
                                    </button>
                                </div>

                                <!-- Date Input (Hidden by default) -->
                                <div class="hidden items-center gap-2" id="dateInputContainer">
                                    <div
                                        class="flex items-center rounded-lg border border-[#d0d7e2] overflow-hidden relative">
                                        <input type="date" name="due_date" id="customDeadline"
                                            class="bg-[#6B7280] text-white text-[14px] font-[Inter] px-3 py-1.5 rounded-l-lg focus:outline-none border-0 pr-10">
                                        <button type="button"
                                            class="px-2 bg-white absolute right-0 h-full flex items-center justify-center pointer-events-auto"
                                            id="calendarBtn" style="z-index: 5;">
                                            <img src="images/icons/calendarAbu.svg" alt="calendar" class="w-5 h-5">
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="auto_due" id="autoDue" value="">
                        </div>

                        <style>
                            /* Styling untuk input date */
                            #customDeadline::-webkit-calendar-picker-indicator {
                                opacity: 0;
                                position: absolute;
                                right: 0;
                                width: 100%;
                                height: 100%;
                                cursor: pointer;
                                z-index: 10;
                            }

                            /* Warna placeholder untuk browser yang support */
                            #customDeadline::-webkit-datetime-edit-text {
                                color: white;
                            }

                            #customDeadline::-webkit-datetime-edit-month-field,
                            #customDeadline::-webkit-datetime-edit-day-field,
                            #customDeadline::-webkit-datetime-edit-year-field {
                                color: white;
                            }

                            /* Fix display saat show */
                            #dateInputContainer:not(.hidden) {
                                display: flex;
                            }
                        </style>

                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                // Dropdown 1
                                const dropdown1 = document.createElement("div");
                                dropdown1.className =
                                    "absolute bg-white border border-gray-300 rounded-lg shadow-md mt-1 w-[200px] hidden z-50 dropdown-menu-1";
                                dropdown1.innerHTML = `
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-t-lg text-black font-[Inter]" data-value="Selesai otomatis">Selesai otomatis</div>
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-b-lg text-black font-[Inter]" data-value="Atur tenggat waktu sendiri">Atur tenggat waktu sendiri</div>
        `;

                                // Dropdown 2
                                const dropdown2 = document.createElement("div");
                                dropdown2.className =
                                    "absolute bg-white border border-gray-300 rounded-lg shadow-md mt-1 w-[200px] hidden z-50 dropdown-menu-2";
                                dropdown2.innerHTML = `
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-t-lg text-black font-[Inter]" data-value="1 hari dari sekarang">1 hari dari sekarang</div>
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer text-black font-[Inter]" data-value="3 hari dari sekarang">3 hari dari sekarang</div>
            <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-b-lg text-black font-[Inter]" data-value="7 hari dari sekarang">7 hari dari sekarang</div>
        `;

                                document.body.appendChild(dropdown1);
                                document.body.appendChild(dropdown2);

                                // Get elements
                                const btn1 = document.querySelector(".dropdown-btn-1");
                                const btn2 = document.querySelector(".dropdown-btn-2");
                                const chipText1 = document.querySelector(".chip-text-1");
                                const chipText2 = document.querySelector(".chip-text-2");
                                const chipContainer1 = document.querySelector(".chip-container-1");
                                const chipContainer2 = document.querySelector(".chip-container-2");
                                const labelText = document.getElementById("labelText");
                                const dropdownChip = document.getElementById("dropdownChip");
                                const dateInputContainer = document.getElementById("dateInputContainer");
                                const customDeadline = document.getElementById("customDeadline");
                                const calendarBtn = document.getElementById("calendarBtn");

                                // Function to position and toggle dropdown
                                function toggleDropdown(dropdown, container) {
                                    const rect = container.getBoundingClientRect();
                                    dropdown.style.top = `${rect.bottom + window.scrollY + 5}px`;
                                    dropdown.style.left = `${rect.left + window.scrollX}px`;
                                    dropdown.classList.toggle("hidden");
                                }

                                // Click calendar button to trigger date picker
                                if (calendarBtn) {
                                    calendarBtn.addEventListener("click", (e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        if (customDeadline.showPicker) {
                                            customDeadline.showPicker();
                                        } else {
                                            customDeadline.click();
                                        }
                                    });
                                }

                                // Event listeners for buttons
                                btn1.addEventListener("click", (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    dropdown2.classList.add("hidden");
                                    toggleDropdown(dropdown1, chipContainer1);
                                });

                                btn2.addEventListener("click", (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    dropdown1.classList.add("hidden");
                                    toggleDropdown(dropdown2, chipContainer2);
                                });

                                // Event listeners for dropdown 1 options
                                dropdown1.querySelectorAll("div[data-value]").forEach(opt => {
                                    opt.addEventListener("click", (e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        const value = opt.getAttribute("data-value");
                                        chipText1.textContent = value;
                                        dropdown1.classList.add("hidden");

                                        // Toggle between dropdown and date input
                                        if (value === "Atur tenggat waktu sendiri") {
                                            labelText.textContent = "Tenggat :";
                                            dropdownChip.classList.add("hidden");
                                            dateInputContainer.classList.remove("hidden");
                                        } else {
                                            labelText.textContent = "Selesai otomatis pada:";
                                            dropdownChip.classList.remove("hidden");
                                            dateInputContainer.classList.add("hidden");
                                        }
                                    });
                                });

                                // Event listeners for dropdown 2 options
                                dropdown2.querySelectorAll("div[data-value]").forEach(opt => {
                                    opt.addEventListener("click", (e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        const value = opt.getAttribute("data-value"); // ✅ ambil nilai dari option
                                        chipText2.textContent = value;
                                        document.getElementById("autoDue").value = value; // ✅ simpan ke input hidden
                                        dropdown2.classList.add("hidden");
                                    });
                                });


                                // Close dropdowns when clicking outside
                                document.addEventListener("click", (e) => {
                                    const isClickInsideDropdown1 = dropdown1.contains(e.target) || chipContainer1.contains(e
                                        .target);
                                    const isClickInsideDropdown2 = dropdown2.contains(e.target) || chipContainer2.contains(e
                                        .target);

                                    if (!isClickInsideDropdown1) {
                                        dropdown1.classList.add("hidden");
                                    }
                                    if (!isClickInsideDropdown2) {
                                        dropdown2.classList.add("hidden");
                                    }
                                });

                                // Prevent dropdown from closing when moving mouse inside it
                                dropdown1.addEventListener("click", (e) => {
                                    e.stopPropagation();
                                });

                                dropdown2.addEventListener("click", (e) => {
                                    e.stopPropagation();
                                });
                            });
                        </script>

                        <!-- Penerima -->
                        <div x-data="pengumumanMembers()" class="space-y-2">
                            <label class="block text-sm font-inter font-semibold text-black mb-1 mt-5 text-left">
                                Penerima Pengumuman <span class="text-red-500">*</span>
                            </label>

                            <div class="flex items-center">
                                <!-- Avatar list -->
                                <div class="flex -space-x-2" id="selectedMembersAvatars">
                                    <template x-for="member in selectedMembers" :key="member.id">
                                        <img :src="member.avatar" :title="member.name"
                                            class="w-9 h-9 rounded-full border-2 border-white shadow-sm">
                                    </template>
                                </div>

                                <!-- Add button -->
                                <button type="button" @click="openMemberModal"
                                    class="flex items-center justify-center w-9 h-9 rounded-full bg-blue-100 text-blue-600 text-lg font-semibold border border-blue-200 hover:bg-blue-200 hover:text-blue-700 transition active:scale-95">
                                    +
                                </button>

                                <span class="ml-2 text-sm text-gray-500">Tambah atau ubah penerima</span>
                            </div>

                            <!-- Hidden input untuk dikirim ke backend -->
                            <input type="hidden" name="recipients[]" :value="selectedMembers.map(m => m.id)">

                            <!-- Modal Pilih Member -->
                            <div x-show="showManageMembersModal"
                                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                                x-transition>
                                <div class="bg-white rounded-xl shadow-lg w-full max-w-md flex flex-col" @click.stop>
                                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                        <h2 class="text-lg font-semibold text-gray-900">Pilih Penerima</h2>
                                        <button @click="closeModal"
                                            class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                                    </div>

                                    <div class="p-4 border-b border-gray-200">
                                        <input type="text" x-model="search" placeholder="Cari anggota..."
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div class="flex-1 overflow-y-auto p-4 space-y-2 max-h-[60vh]">
                                        <template x-for="member in filteredMembers" :key="member.id">
                                            <label
                                                class="flex items-center justify-between p-2 border rounded-lg hover:bg-gray-50 cursor-pointer">
                                                <div class="flex items-center gap-3">
                                                    <img :src="member.avatar || 'https://i.pravatar.cc/36'"
                                                        class="w-8 h-8 rounded-full">
                                                    <div>
                                                        <p class="font-medium text-sm" x-text="member.name"></p>
                                                        <p class="text-xs text-gray-500" x-text="member.email"></p>
                                                    </div>
                                                </div>
                                                <input type="checkbox" :value="member.id"
                                                    @change="toggleMember(member)" :checked="isSelected(member.id)"
                                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            </label>
                                        </template>

                                        <div x-show="filteredMembers.length === 0"
                                            class="text-center text-sm text-gray-500 py-6">
                                            Tidak ada anggota ditemukan
                                        </div>
                                    </div>

                                    <div
                                        class="p-4 border-t border-gray-200 flex justify-end gap-3 bg-gray-50 rounded-b-xl">
                                        <button type="button" @click="closeModal"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                            Batal
                                        </button>
                                        <button type="button" @click="applyMembers"
                                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                            Terapkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- end pilih penerima --}}


                        <!-- Rahasia -->
                        <div class="mt-3">
                            <label class="block text-sm font-inter font-semibold text-black mb-1">
                                Apakah pengumuman ini rahasia untuk penerima saja? <span class="text-red-500">*</span>
                            </label>


                            <label class="inline-flex items-center cursor-pointer">
                                <!-- Switch -->
                                <input type="checkbox" name="is_private" id="switchRahasia" class="sr-only"
                                    value="1">
                                <div id="switchBg"
                                    class="relative w-11 h-6 bg-gray-300 rounded-full transition-colors duration-300">
                                    <span id="switchCircle"
                                        class="absolute top-[2px] left-[2px] w-[20px] h-[20px] bg-white rounded-full transition-transform duration-300"></span>
                                </div>
                                <span class="ml-2 text-[#102a63] font-medium">Rahasia</span>
                            </label>
                        </div>

                        <script>
                            const switchInput = document.getElementById('switchRahasia');
                            const switchBg = document.getElementById('switchBg');
                            const switchCircle = document.getElementById('switchCircle');

                            // Event listener untuk label (karena kita klik labelnya)
                            switchBg.parentElement.addEventListener('click', function(e) {
                                e.preventDefault();

                                // Toggle checkbox
                                switchInput.checked = !switchInput.checked;

                                // Update tampilan
                                if (switchInput.checked) {
                                    switchBg.classList.remove('bg-gray-300');
                                    switchBg.classList.add('bg-[#102a63]');
                                    switchCircle.style.transform = 'translateX(20px)';
                                } else {
                                    switchBg.classList.remove('bg-[#102a63]');
                                    switchBg.classList.add('bg-gray-300');
                                    switchCircle.style.transform = 'translateX(0)';
                                }
                            });
                        </script>

                        <!-- Tombol Lebih Besar -->
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" id="btnBatal"
                                class="border border-blue-700 text-blue-600 bg-white px-8 py-2 text-[16px] rounded-lg hover:bg-red-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="bg-blue-700 text-white px-8 py-2 text-[16px] rounded-lg hover:bg-blue-800 transition">
                                Kirim
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const btnPopup = document.getElementById('btnPopup');
        const popupForm = document.getElementById('popupForm');
        const btnBatal = document.getElementById('btnBatal');

        btnPopup.addEventListener('click', () => {
            popupForm.classList.remove('hidden');
            popupForm.classList.add('flex');
        });

        btnBatal.addEventListener('click', () => {
            popupForm.classList.add('hidden');
            popupForm.classList.remove('flex');
        });

        // Tangkap data dari CKEditor saat form dikirim
        document.getElementById('popupForm').querySelector('form').addEventListener('submit', (e) => {
            const editor = window['catatan-editor_editor']; // CKEditor instance
            const editorData = editor.getData(); // Ambil isi editor

            // Masukkan ke input hidden agar terkirim ke backend
            document.getElementById('catatanInput').value = editorData;
        });
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pengumumanMembers', () => ({
                showManageMembersModal: false,
                members: [],
                selectedMembers: [],
                search: '',

                async init() {
                    await this.loadMembers();
                },

                async loadMembers() {
                    try {
                        const urlPath = window.location.pathname;
                        const workspaceId = urlPath.split('/')[
                        2]; // Pastikan sesuai struktur URL kamu

                        const res = await fetch(`/pengumuman/anggota/${workspaceId}`);
                        if (!res.ok) throw new Error('Gagal mengambil data anggota');

                        this.members = await res.json();
                        console.log('Data anggota:', this.members);
                    } catch (err) {
                        console.error('Gagal memuat anggota:', err);
                        this.members = [];
                    }
                },

                openMemberModal() {
                    this.showManageMembersModal = true;
                },

                closeModal() {
                    this.showManageMembersModal = false;
                },

                toggleMember(member) {
                    const idx = this.selectedMembers.findIndex(m => m.id === member.id);
                    if (idx === -1) {
                        this.selectedMembers.push(member);
                    } else {
                        this.selectedMembers.splice(idx, 1);
                    }
                },

                isSelected(id) {
                    return this.selectedMembers.some(m => m.id === id);
                },

                get filteredMembers() {
                    if (!this.search) return this.members;
                    const term = this.search.toLowerCase();
                    return this.members.filter(m =>
                        m.name.toLowerCase().includes(term) ||
                        m.email.toLowerCase().includes(term)
                    );
                },

                applyMembers() {
                    this.closeModal();
                }
            }))
        });

        document.addEventListener('DOMContentLoaded', function() {
            const pilihBtn = document.getElementById('btnPilihMember');
            if (pilihBtn) {
                pilihBtn.addEventListener('click', function() {
                    const modal = document.querySelector('[x-show="showManageMembersModal"]');
                    if (modal) modal.style.display = 'flex';
                });
            }
        });
    </script>




    {{-- pop up penerima pengumuman --}}
@endsection
