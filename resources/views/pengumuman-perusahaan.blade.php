@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('components.sweet-alert')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Background tetap biru --}}
    <div class="bg-[#f3f6fc] min-h-screen font-[Inter,sans-serif] text-black">

        <div class="max-w-6xl mx-auto py-6 px-4">

            {{-- Header Section --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 mb-1">📢 Pengumuman</h1>
                        <p class="text-sm text-gray-500">Lihat dan kelola pengumuman tim Anda</p>
                    </div>

                    {{-- Tombol Buat Pengumuman - Redesign --}}
                    <button id="btnPopup"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Pengumuman
                    </button>
                </div>
            </div>

            @php
                \Carbon\Carbon::setLocale('id');
            @endphp

            {{-- List Pengumuman --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar">

                    @forelse($pengumumans as $p)
                        @php
                            $creator = $p->creator;
                            $avatarPath = $creator->avatar ? 'storage/' . $creator->avatar : null;
                            $hasAvatarFile = $avatarPath && file_exists(public_path($avatarPath));

                            $avatarUrl = $hasAvatarFile
                                ? asset($avatarPath)
                                : ($creator->full_name
                                    ? 'https://ui-avatars.com/api/?name=' . urlencode($creator->full_name) . '&background=random&color=fff'
                                    : asset('images/dk.jpg'));
                        @endphp

                        {{-- Card Pengumuman - Clean Design --}}
                        <div onclick="window.location='{{ route('pengumuman-perusahaan.show', ['company_id' => $company_id, 'id' => $p->id]) }}'"
                            class="group cursor-pointer bg-gray-50 hover:bg-blue-50 transition-all duration-200 rounded-lg p-4 border border-gray-200 hover:border-blue-200 hover:shadow-md">

                            <div class="flex gap-3">
                                {{-- Avatar --}}
                                <img src="{{ $avatarUrl }}" alt="Avatar"
                                    class="rounded-full w-11 h-11 object-cover border-2 border-white shadow-sm flex-shrink-0">

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    {{-- Header: Name & Time --}}
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <p class="font-semibold text-gray-800 text-sm">
                                                {{ $p->creator->full_name ?? 'Unknown' }}
                                            </p>

                                            {{-- Title dengan icon lock --}}
                                            <div class="flex items-center gap-1.5 mt-1">
                                                @if ($p->is_private)
                                                    <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                                <h3 class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-1">
                                                    {{ $p->title }}
                                                </h3>
                                            </div>
                                        </div>

                                        {{-- Time & Comment Count --}}
                                        <div class="flex flex-col items-end gap-2 ml-3">
                                            <span class="text-xs text-gray-500 whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($p->created_at)->diffForHumans() }}
                                            </span>
                                            @if($p->comments_count > 0)
                                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-yellow-400 text-gray-800 font-bold text-xs">
                                                {{ $p->comments_count }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Description --}}
                                    <div class="text-sm text-gray-600 line-clamp-2 mb-3 ck-content">
                                        {!! preg_replace(
                                            ['/<img[^>]+>/i', '/<figure[^>]*>.*?<\/figure>/is', '/<p>\s*<\/p>/i', '/<br\s*\/?>/i'],
                                            '',
                                            $p->description,
                                        ) !!}
                                    </div>

                                    {{-- Tags: Due Date & Auto Due --}}
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if ($p->due_date)
                                            <span class="inline-flex items-center gap-1 bg-gray-600 text-white text-xs font-medium px-2.5 py-1 rounded-md">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ \Carbon\Carbon::parse($p->due_date)->translatedFormat('d M') }}
                                            </span>
                                        @endif
                                        @if ($p->auto_due)
                                            <span class="text-xs text-gray-500 font-medium">
                                                Selesai: {{ \Carbon\Carbon::parse($p->auto_due)->translatedFormat('d M Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Empty State --}}
                        <div class="text-center py-16">
                            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="text-gray-500 font-medium text-lg">Belum ada pengumuman</p>
                            <p class="text-sm text-gray-400 mt-1">Buat pengumuman pertama Anda</p>
                        </div>
                    @endforelse

                </div>
            </div>
        </div>

        <!-- POPUP FORM -->
        <div id="popupForm" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between mb-5 pb-4 border-b-2 border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Buat Pengumuman</h2>
                    <button id="btnBatalHeader" type="button" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('pengumuman-perusahaan.store', ['company_id' => $company_id]) }}" method="POST" class="space-y-5" id="pengumumanForm">
                    @csrf

                    <!-- Judul -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Judul Pengumuman <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" placeholder="Masukkan judul pengumuman..."
                            class="w-full border-2 border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                    </div>

                    <!-- Deskripsi -->
                    <div class="flex flex-col" x-data x-init="createEditorFor('catatan-editor', { placeholder: 'Masukkan catatan anda disini...' })">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Deskripsi <span class="text-red-500">*</span>
                        </label>
                        <div id="catatan-editor" class="border-2 border-gray-300 rounded-lg bg-white min-h-[160px]"></div>
                        <input type="hidden" name="description" id="catatanInput">
                    </div>

                    <!-- CKEditor Script -->
                    <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>
                    <script>
                        async function createEditorFor(containerId, options = {}) {
                            const el = document.getElementById(containerId);
                            if (!el) return console.warn('Element not found:', containerId);

                            try {
                                const editor = await ClassicEditor.create(el, {
                                    toolbar: {
                                        items: ['undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'underline', 'strikethrough', '|',
                                            'link', 'blockQuote', '|', 'bulletedList', 'numberedList', '|', 'insertTable', '|'],
                                        shouldNotGroupWhenFull: true
                                    },
                                    heading: {
                                        options: [
                                            {model: 'paragraph', title: 'Paragraf', class: 'ck-heading_paragraph'},
                                            {model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1'},
                                            {model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2'}
                                        ]
                                    },
                                    fontFamily: {options: ['Inter, sans-serif', 'Arial, Helvetica, sans-serif', 'Courier New, Courier, monospace']},
                                    fontSize: {options: ['14px', '16px', '18px', '24px', '32px']},
                                    fontColor: {
                                        columns: 5,
                                        colors: [
                                            {color: '#000000', label: 'Black'},
                                            {color: '#102a63', label: 'Dark Blue'},
                                            {color: '#6B7280', label: 'Gray'},
                                            {color: '#FFFFFF', label: 'White'}
                                        ]
                                    },
                                    simpleUpload: {
                                        uploadUrl: '/upload',
                                        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                                    },
                                    placeholder: options.placeholder || ''
                                });

                                insertUploadFileButtonToToolbar(editor);
                                insertUploadImageButtonToToolbar(editor);

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

                        function insertUploadImageButtonToToolbar(editor) {
                            try {
                                const toolbarEl = editor.ui.view.toolbar.element;
                                const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'ck ck-button';
                                btn.title = 'Upload Image';
                                btn.innerHTML = `<span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">${imageIconSVG()}</span>`;
                                btn.style.cssText = 'margin-left:6px;padding:4px 8px;border-radius:6px;background:transparent;border:0;cursor:pointer';

                                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                                btn.addEventListener('click', () => {
                                    const input = document.createElement('input');
                                    input.type = 'file';
                                    input.accept = 'image/*';
                                    input.click();

                                    input.addEventListener('change', async (e) => {
                                        const file = e.target.files[0];
                                        if (!file) return;

                                        btn.innerHTML = '⏳';
                                        const formData = new FormData();
                                        formData.append('upload', file);

                                        try {
                                            const res = await fetch('/upload-image', {
                                                method: 'POST',
                                                headers: {'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}'},
                                                body: formData
                                            });

                                            const data = await res.json();
                                            if (res.ok && data.url) {
                                                editor.model.change(writer => {
                                                    const insertPos = editor.model.document.selection.getFirstPosition();
                                                    const imageElement = writer.createElement('imageBlock', {src: data.url});
                                                    editor.model.insertContent(imageElement, insertPos);
                                                });
                                            } else {
                                                alert('Upload gagal. Cek console.');
                                                console.error(data);
                                            }
                                        } catch (err) {
                                            console.error('Upload error:', err);
                                            alert('Terjadi kesalahan saat upload image.');
                                        } finally {
                                            btn.innerHTML = `<span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">${imageIconSVG()}</span>`;
                                        }
                                    }, {once: true});
                                });

                                itemsContainer.appendChild(btn);
                            } catch (err) {
                                console.error('Insert upload image button error:', err);
                            }

                            function imageIconSVG() {
                                return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 11a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM5 19l4.5-6 3.5 4.5 2.5-3L19 19H5z"/></svg>`;
                            }
                        }

                        function insertUploadFileButtonToToolbar(editor) {
                            try {
                                const toolbarEl = editor.ui.view.toolbar.element;
                                const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'ck ck-button';
                                btn.title = 'Upload File';
                                btn.innerHTML = `<span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">${fileIconSVG()}</span>`;
                                btn.style.cssText = 'margin-left:6px;padding:4px 8px;border-radius:6px;background:transparent;border:0;cursor:pointer';

                                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                                btn.addEventListener('click', () => {
                                    const input = document.createElement('input');
                                    input.type = 'file';
                                    input.accept = ".pdf,.doc,.docx,.xls,.xlsx,.csv,.sql,.txt,.zip,.rar,.ppt,.pptx";
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
                                                headers: {'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}'},
                                                body: formData
                                            });

                                            const data = await res.json();

                                            if (res.ok && data.url) {
                                                editor.model.change(writer => {
                                                    const insertPos = editor.model.document.selection.getFirstPosition();
                                                    const linkElement = writer.createElement('paragraph');
                                                    const textNode = writer.createText(file.name, {linkHref: data.url});
                                                    writer.append(textNode, linkElement);
                                                    editor.model.insertContent(linkElement, insertPos);
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
                                    }, {once: true});
                                });

                                const insertTableBtn = Array.from(itemsContainer.children).find(el => el.title?.toLowerCase().includes('table'));
                                if (insertTableBtn) {
                                    itemsContainer.insertBefore(btn, insertTableBtn);
                                } else {
                                    itemsContainer.appendChild(btn);
                                }

                            } catch (err) {
                                console.error('Insert upload button error:', err);
                            }
                        }

                        function fileIconSVG() {
                            return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20" height="20"><path fill="currentColor" d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2 4 4H10V4z"/></svg>`;
                        }
                    </script>

                    <!-- Tenggat -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Tenggat Pengumuman hingga selesai <span class="text-red-500">*</span>
                        </label>

                        <div class="flex items-center gap-3 mb-3 mt-3 relative">
                            <div class="flex items-center rounded-lg border border-gray-300 overflow-hidden chip-container-1">
                                <div class="flex items-center bg-gray-600 text-white text-sm px-3 py-1.5 rounded-l-lg chip-text-1">
                                    Selesai otomatis
                                </div>
                                <button type="button" class="px-2 text-gray-500 hover:text-gray-700 dropdown-btn-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>

                            <span class="text-sm text-gray-700" id="labelText">Selesai otomatis pada:</span>

                            <div class="flex items-center rounded-lg border border-gray-300 overflow-hidden chip-container-2" id="dropdownChip">
                                <div class="flex items-center bg-gray-600 text-white text-sm px-3 py-1.5 rounded-l-lg chip-text-2">
                                    1 hari dari sekarang
                                </div>
                                <button type="button" class="px-2 text-gray-500 hover:text-gray-700 dropdown-btn-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="hidden items-center gap-2" id="dateInputContainer">
                                <div class="flex items-center rounded-lg border border-gray-300 overflow-hidden relative">
                                    <input type="date" name="due_date" id="customDeadline"
                                        class="bg-gray-600 text-white text-sm px-3 py-1.5 rounded-l-lg focus:outline-none border-0 pr-10">
                                    <button type="button" class="px-2 bg-white absolute right-0 h-full flex items-center justify-center" id="calendarBtn" style="z-index:5;">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="auto_due" id="autoDue" value="">
                    </div>

                    <style>
                        /* Custom Scrollbar */
                        .custom-scrollbar::-webkit-scrollbar {
                            width: 6px;
                        }
                        .custom-scrollbar::-webkit-scrollbar-track {
                            background: #f1f5f9;
                            border-radius: 10px;
                        }
                        .custom-scrollbar::-webkit-scrollbar-thumb {
                            background: #cbd5e1;
                            border-radius: 10px;
                        }
                        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                            background: #94a3b8;
                        }

                        /* CKEditor Link Style */
                        .ck-content a {
                            color: #2563eb;
                            text-decoration: underline;
                            font-weight: 500;
                        }
                        .ck-content a:hover {
                            color: #1d4ed8;
                        }

                        /* Date Input Style */
                        #customDeadline::-webkit-calendar-picker-indicator {
                            opacity: 0;
                            position: absolute;
                            right: 0;
                            width: 100%;
                            height: 100%;
                            cursor: pointer;
                            z-index: 10;
                        }
                        #customDeadline::-webkit-datetime-edit-text,
                        #customDeadline::-webkit-datetime-edit-month-field,
                        #customDeadline::-webkit-datetime-edit-day-field,
                        #customDeadline::-webkit-datetime-edit-year-field {
                            color: white;
                        }
                        #dateInputContainer:not(.hidden) {
                            display: flex;
                        }
                    </style>

                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            const dropdown1 = document.createElement("div");
                            dropdown1.className = "absolute bg-white border border-gray-300 rounded-lg shadow-md mt-1 w-[200px] hidden z-50 dropdown-menu-1";
                            dropdown1.innerHTML = `
                                <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer rounded-t-lg text-black" data-value="Selesai otomatis">Selesai otomatis</div>
                                <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer rounded-b-lg text-black" data-value="Atur tenggat waktu sendiri">Atur tenggat waktu sendiri</div>
                            `;

                            const dropdown2 = document.createElement("div");
                            dropdown2.className = "absolute bg-white border border-gray-300 rounded-lg shadow-md mt-1 w-[200px] hidden z-50 dropdown-menu-2";
                            dropdown2.innerHTML = `
                                <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer rounded-t-lg text-black" data-value="1 hari dari sekarang">1 hari dari sekarang</div>
                                <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-black" data-value="3 hari dari sekarang">3 hari dari sekarang</div>
                                <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer rounded-b-lg text-black" data-value="7 hari dari sekarang">7 hari dari sekarang</div>
                            `;

                            document.body.appendChild(dropdown1);
                            document.body.appendChild(dropdown2);

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

                            function toggleDropdown(dropdown, container) {
                                const rect = container.getBoundingClientRect();
                                dropdown.style.top = `${rect.bottom + window.scrollY + 5}px`;
                                dropdown.style.left = `${rect.left + window.scrollX}px`;
                                dropdown.classList.toggle("hidden");
                            }

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

                            dropdown1.querySelectorAll("div[data-value]").forEach(opt => {
                                opt.addEventListener("click", (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const value = opt.getAttribute("data-value");
                                    chipText1.textContent = value;
                                    dropdown1.classList.add("hidden");

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

                            dropdown2.querySelectorAll("div[data-value]").forEach(opt => {
                                opt.addEventListener("click", (e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const value = opt.getAttribute("data-value");
                                    chipText2.textContent = value;
                                    document.getElementById("autoDue").value = value;
                                    dropdown2.classList.add("hidden");
                                });
                            });

                            document.addEventListener("click", (e) => {
                                const isClickInsideDropdown1 = dropdown1.contains(e.target) || chipContainer1.contains(e.target);
                                const isClickInsideDropdown2 = dropdown2.contains(e.target) || chipContainer2.contains(e.target);

                                if (!isClickInsideDropdown1) {
                                    dropdown1.classList.add("hidden");
                                }
                                if (!isClickInsideDropdown2) {
                                    dropdown2.classList.add("hidden");
                                }
                            });

                            dropdown1.addEventListener("click", (e) => {
                                e.stopPropagation();
                            });

                            dropdown2.addEventListener("click", (e) => {
                                e.stopPropagation();
                            });
                        });
                    </script>

                    <!-- Tombol Submit -->
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" id="btnBatal"
                            class="border-2 border-blue-600 text-blue-600 bg-white px-6 py-2.5 text-sm font-semibold rounded-lg hover:bg-blue-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="bg-blue-600 text-white px-6 py-2.5 text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
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
        const btnBatalHeader = document.getElementById('btnBatalHeader');

        btnPopup.addEventListener('click', () => {
            popupForm.classList.remove('hidden');
            popupForm.classList.add('flex');
        });

        btnBatal.addEventListener('click', () => {
            popupForm.classList.add('hidden');
            popupForm.classList.remove('flex');
        });

        btnBatalHeader.addEventListener('click', () => {
            popupForm.classList.add('hidden');
            popupForm.classList.remove('flex');
        });

        // Tangkap data dari CKEditor saat form dikirim
        document.getElementById('pengumumanForm').addEventListener('submit', function(e) {
            const title = document.querySelector('input[name="title"]').value.trim();
            const editorData = window['catatan-editor_editor'].getData().trim();

            // → CEK JUDUL
            if (!title) {
                e.preventDefault();
                return Swal.fire("Kolom Belum Diisi", "Judul pengumuman wajib diisi.", "warning");
            }

            // → CEK DESKRIPSI
            if (!editorData || editorData === "<p><br></p>") {
                e.preventDefault();
                return Swal.fire("Kolom Belum Diisi", "Deskripsi pengumuman wajib diisi.", "warning");
            }

            // Masukkan ke input hidden agar terkirim ke backend
            document.getElementById('catatanInput').value = editorData;
        });
    </script>
@endsection
