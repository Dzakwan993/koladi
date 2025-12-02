@extends('layouts.app')

@section('title', 'Detail Jadwal')

@section('content')

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <div class="bg-[#e9effd] min-h-screen font-[Inter,sans-serif] text-black relative" x-data="commentSection">
        <div class="min-h-screen flex justify-center items-start pt-10 bg-[#f3f6fc] px-4">
            <div class="bg-white rounded-[8px] shadow-xl p-6 md:p-8 w-full max-w-3xl flex flex-col gap-6">

                <!-- Header dengan Tombol Kembali -->
                <header class="flex justify-between items-start">
                    <div class="flex items-center gap-4 flex-1">
                        <!-- ✅ Tombol Kembali -->
                        <a href="{{ route('jadwal-umum') }}"
                            class="flex items-center justify-center w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </a>

                        <div class="bg-[#2563eb] rounded-lg p-2">
                            <img src="{{ asset('images/icons/Calendar.svg') }}" alt="Calendar Icon" class="h-8 w-8">
                        </div>
                        <div class="flex-1">
                            <h1 class="font-bold text-xl text-black">{{ $event->title }}</h1>
                            <p class="text-sm font-semibold text-[16px] text-[#6B7280]">
                                Dibuat oleh {{ $event->creator->full_name }} pada
                                {{ \Carbon\Carbon::parse($event->created_at)->translatedFormat('l, d M Y') }}
                            </p>
                        </div>
                    </div>

                    @if ($isCreator)
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="text-[#6B7280] hover:text-gray-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-transition x-cloak
                                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10">
                                <a href="{{ route('jadwal-umum.edit', ['id' => $event->id]) }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Jadwal
                                </a>

                                <button type="button" onclick="confirmDelete()"
                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus Jadwal
                                </button>

                                <form id="deleteForm" action="{{ route('jadwal-umum.destroy', ['id' => $event->id]) }}"
                                    method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
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
                            @php
                                $startDate = \Carbon\Carbon::parse($event->start_datetime);
                                $endDate = \Carbon\Carbon::parse($event->end_datetime);
                                $isMultiDay = $startDate->format('Y-m-d') !== $endDate->format('Y-m-d');
                            @endphp

                            <p class="font-medium text-[14px] text-[#6B7280]">
                                @if ($isMultiDay)
                                    {{ $startDate->translatedFormat('l, d M Y, H:i') }} -
                                    {{ $endDate->translatedFormat('l, d M Y, H:i') }}
                                @else
                                    {{ $startDate->translatedFormat('l, d M Y') }},
                                    {{ $startDate->format('H:i') }} - {{ $endDate->format('H:i') }}
                                @endif

                                @if ($event->recurrence)
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
                                @foreach ($event->participants as $participant)
                                    <div class="relative group">
                                        <img src="{{ $participant->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($participant->user->full_name) . '&background=3B82F6&color=fff&bold=true&size=128' }}"
                                            alt="{{ $participant->user->full_name }}"
                                            title="{{ $participant->user->full_name }}"
                                            class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 hover:border-blue-500 transition cursor-pointer">

                                        <div
                                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-800 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                            {{ $participant->user->full_name }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- ✅ MODE RAPAT: ONLINE / OFFLINE -->
                    <div class="flex items-start gap-4">
                        @if ($event->is_online_meeting)
                            <!-- ========== ONLINE MEETING ========== -->
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-video text-blue-600 text-lg"></i>
                            </div>
                            <div class="flex-1" x-data="{ openPopup: false }">
                                <div class="flex items-center gap-2 mb-2">
                                    <h2 class="font-semibold text-black text-[16px] mt-2">Rapat Online</h2>
                                </div>

                                @if ($event->meeting_link)
                                    <button @click="openPopup = true"
                                        class="mt-2 bg-[#2563eb] text-white font-semibold py-2.5 px-5 rounded-lg text-sm hover:bg-blue-700 transition-colors flex items-center gap-2 shadow-md hover:shadow-lg">
                                        <i class="fas fa-video"></i>
                                        <span>Gabung Rapat</span>
                                    </button>

                                    <!-- POPUP Konfirmasi Gabung Rapat -->
                                    <div x-show="openPopup" x-transition x-cloak
                                        class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
                                        <div @click.away="openPopup = false"
                                            class="bg-[#f3f6fc] rounded-2xl shadow-lg p-8 w-full max-w-sm text-center">

                                            <img src="{{ asset('images/icons/teamimage.svg') }}" alt="Ilustrasi rapat"
                                                class="w-48 mx-auto mb-6">

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
                                @else
                                    <p class="text-sm text-gray-500 mt-2">Link rapat belum tersedia</p>
                                @endif
                            </div>
                        @else
                            <!-- ========== OFFLINE MEETING ========== -->
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-map-marker-alt text-red-600 text-lg"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h2 class="font-semibold text-black text-[16px] mt-2">Rapat Offline</h2>
                                </div>

                                {{-- ✅ TAMPILKAN LOKASI JIKA ADA - SIMPLE VERSION --}}
                                @if ($event->location)
                                    <div class="mt-3 flex items-center gap-2 text-gray-700">
                                        <i class="fas fa-map-pin text-red-500"></i>
                                        <span class="text-sm font-medium">{{ $event->location }}</span>
                                    </div>
                                @else
                                    {{-- ✅ JIKA LOKASI KOSONG --}}
                                    <div class="mt-3 flex items-center gap-2 text-gray-500">
                                        <i class="fas fa-info-circle"></i>
                                        <span class="text-sm">Lokasi rapat belum ditentukan</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Catatan -->
                    @if ($event->description)
                        <div class="flex items-start gap-4">
                            <img src="{{ asset('images/icons/Edit.svg') }}" alt="Icon Catatan" class="w-5 h-5 mt-1">
                            <div>
                                <h2 class="font-semibold text-black text-[16px]">Catatan</h2>
                                <div class="prose max-w-none mt-1 text-[#6B7280] font-medium text-[14px] deskripsi-jadwal">
                                    {!! $event->description !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Section Komentar (SAMA SEPERTI SEBELUMNYA) -->
                <div class="mt-6">
                    <h3 class="text-base font-semibold text-black mb-4">Komentar</h3>

                    @php
                        $user = Auth::user();
                        $avatarPath = $user->avatar ? 'storage/' . $user->avatar : null;
                        $hasAvatarFile = $avatarPath && file_exists(public_path($avatarPath));
                        $avatarUrl = $hasAvatarFile
                            ? asset($avatarPath)
                            : ($user->full_name
                                ? 'https://ui-avatars.com/api/?name=' .
                                    urlencode($user->full_name) .
                                    '&background=random&color=fff'
                                : asset('images/dk.jpg'));
                    @endphp

                    <div class="flex items-start gap-3 mb-6">
                        <img src="{{ $avatarUrl }}" alt="Avatar"
                            class="rounded-full w-10 h-10 object-cover object-center border border-gray-200 shadow-sm bg-gray-100">

                        <div class="flex-1" x-data="{ active: false }" x-cloak>
                            <template x-if="!active">
                                <input type="text" placeholder="Tambahkan komentar baru..."
                                    @focus="active = true; $nextTick(() => initMainEditor('main-editor'))"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#102a63] text-sm bg-white cursor-text">
                            </template>

                            <template x-if="active">
                                <div class="bg-white border border-gray-300 rounded-lg p-4">
                                    <div id="main-editor" class="min-h-[140px] bg-white"></div>

                                    <div class="flex justify-end gap-2 mt-4">
                                        <button @click="active = false; destroyMainEditor('main-editor')"
                                            class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded-lg hover:text-gray-800 transition">
                                            Batal
                                        </button>

                                        <button @click="submitMain(); active = false;"
                                            class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                            Kirim
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Daftar Komentar (SAMA) -->
                    <template x-if="comments.length > 0">
                        <div class="space-y-4">
                            <template x-for="comment in comments" :key="comment.id">
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-start gap-3">
                                        <img x-bind:src="comment.author.avatar"
                                            class="rounded-full w-10 h-10 object-cover object-center border border-gray-200 shadow-sm bg-gray-100">
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <p class="text-sm font-semibold text-gray-800"
                                                    x-text="comment.author.name"></p>
                                                <span class="text-xs text-gray-500"
                                                    x-text="formatCommentDate(comment.createdAt)"></span>
                                            </div>

                                            <div class="text-sm text-gray-700 mt-1 comment-text" x-html="comment.content">
                                            </div>

                                            <div class="flex items-center gap-4 mt-2">
                                                <button @click="toggleReply(comment)"
                                                    class="flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                    </svg>
                                                    <span>balas</span>
                                                </button>
                                            </div>


                                            <!-- FORM BALAS -->
                                            <template
                                                x-if="replyView.active && replyView.parentComment?.id === comment.id">
                                                <div class="mt-4 pl-6 border-l-2 border-gray-200">
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <h4 class="text-sm font-semibold text-gray-800 mb-2">Membalas
                                                            <span x-text="comment.author.name"></span>
                                                        </h4>

                                                        <div
                                                            class="border border-gray-300 rounded-lg overflow-hidden mb-3">
                                                            <div :id="'reply-editor-' + comment.id"
                                                                class="min-h-[120px] p-3 bg-white"></div>
                                                        </div>

                                                        <div class="flex justify-end gap-2">
                                                            <button @click="closeReplyView()"
                                                                class="px-3 py-1 text-sm text-gray-600 hover:text-gray-800 transition border border-gray-300 rounded-lg">Batal</button>
                                                            <button @click="submitReplyFromEditor()"
                                                                class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">Kirim</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Balasan -->
                                            <template x-if="comment.replies && comment.replies.length > 0">
                                                <div class="mt-3 pl-6 border-l-2 border-gray-200 space-y-3">
                                                    <template x-for="reply in comment.replies" :key="reply.id">
                                                        <div class="bg-white rounded-lg p-3 border border-gray-200">
                                                            <div class="flex items-start gap-2">
                                                                <img x-bind:src="reply.author.avatar"
                                                                    class="rounded-full w-6 h-6 object-cover object-center border border-gray-200 shadow-sm bg-gray-100">
                                                                <div>
                                                                    <div class="flex items-center gap-2">
                                                                        <p class="text-sm font-semibold text-gray-800"
                                                                            x-text="reply.author.name"></p>
                                                                        <span class="text-xs text-gray-500"
                                                                            x-text="formatCommentDate(reply.createdAt)"></span>
                                                                    </div>
                                                                    <div class="text-sm text-gray-700 mt-1 comment-text"
                                                                        x-html="reply.content"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="comments.length === 0">
                        <div class="text-center py-8 text-gray-500 text-sm">Belum ada komentar disini...</div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>

    <script>
        const editors = {};

        function generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        async function createEditor(el, type = 'all', commentId = null) {
            try {
                const toolbarItems = [
                    'undo', 'redo', '|',
                    'heading', '|',
                    'bold', 'italic', '|',
                    'link', 'blockQuote', '|',
                    'bulletedList', 'numberedList', '|',
                    'insertTable'
                ];

                const config = {
                    toolbar: {
                        items: toolbarItems,
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
                    placeholder: el.dataset.placeholder || ''
                };

                const editor = await ClassicEditor.create(el, config);

                if (type === 'all') {
                    insertUploadFileButtonToToolbar(editor, commentId);
                    insertUploadImageButtonToToolbar(editor, commentId);
                }

                editor.editing.view.change(writer => {
                    writer.setStyle('font-family', 'Inter, sans-serif', editor.editing.view.document.getRoot());
                    writer.setStyle('font-size', '14px', editor.editing.view.document.getRoot());
                    writer.setStyle('color', '#000000', editor.editing.view.document.getRoot());
                });

                return editor;
            } catch (err) {
                console.warn('CKEditor create failed, fallback to textarea for', el.id, err);
                el.innerHTML =
                    `<textarea id="${el.id}-fallback" class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none"></textarea>`;
                return null;
            }
        }

        async function initMainEditor(id = 'main-editor') {
            const el = document.getElementById(id);
            if (!el) return;
            if (editors[id] !== undefined) return;

            const newCommentId = generateUUID();
            window.currentMainCommentId = newCommentId;

            const inst = await createEditor(el, 'all', newCommentId);
            editors[id] = inst;
        }

        function destroyMainEditor(id = 'main-editor') {
            const inst = editors[id];
            if (inst) inst.destroy().catch(() => {});
            delete editors[id];
            const ta = document.getElementById(id + '-fallback');
            if (ta) ta.remove();
            const el = document.getElementById(id);
            if (el) el.innerHTML = '';
            window.currentMainCommentId = null;
        }

        async function initReplyEditorFor(commentId) {
            const id = `reply-editor-${commentId}`;
            const el = document.getElementById(id);
            if (!el) return;
            if (editors[`reply-${commentId}`] !== undefined) return;

            const newReplyId = generateUUID();
            window[`currentReplyId_${commentId}`] = newReplyId;

            const inst = await createEditor(el, 'all', newReplyId);
            editors[`reply-${commentId}`] = inst;
        }

        function destroyReplyEditorFor(commentId) {
            const key = `reply-${commentId}`;
            const inst = editors[key];
            if (inst) inst.destroy().catch(() => {});
            delete editors[key];
            const ta = document.getElementById(`reply-editor-${commentId}-fallback`);
            if (ta) ta.remove();
            const el = document.getElementById(`reply-editor-${commentId}`);
            if (el) el.innerHTML = '';
            delete window[`currentReplyId_${commentId}`];
        }

        function getEditorDataSafe(key) {
            const inst = editors[key];
            if (inst) return inst.getData();
            if (key === 'main-editor') return document.getElementById('main-editor-fallback')?.value || '';
            if (key.startsWith('reply-')) {
                const id = key.replace('reply-', '');
                return document.getElementById(`reply-editor-${id}-fallback`)?.value || '';
            }
            return '';
        }

        function insertUploadFileButtonToToolbar(editor, commentId) {
            const toolbarEl = editor.ui.view.toolbar.element;
            const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ck ck-button';
            btn.title = 'Upload File';
            btn.innerHTML = `<span class="ck-button__label" style="display:flex;align-items:center;gap:2px">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20" height="20">
                    <path fill="currentColor" d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2 4 4H10V4z"/>
                </svg>
            </span>`;
            btn.style.marginLeft = '6px';
            btn.style.cursor = 'pointer';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            btn.addEventListener('click', () => {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = ".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar,.ppt,.pptx";
                input.click();

                input.addEventListener('change', async (e) => {
                    const file = e.target.files[0];
                    if (!file) return;
                    const formData = new FormData();
                    formData.append('upload', file);
                    formData.append('attachable_id', commentId);
                    formData.append('attachable_type', 'App\\Models\\Comment');

                    try {
                        const res = await fetch('/upload', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: formData
                        });
                        const data = await res.json();
                        if (res.ok && data.url) {
                            editor.model.change(writer => {
                                const insertPos = editor.model.document.selection
                                    .getFirstPosition();
                                const paragraph = writer.createElement('paragraph');
                                const textNode = writer.createText(file.name, {
                                    linkHref: data.url
                                });
                                writer.append(textNode, paragraph);
                                editor.model.insertContent(paragraph, insertPos);
                            });
                        } else alert('Upload file gagal.');
                    } catch (err) {
                        console.error(err);
                        alert('Terjadi kesalahan upload file.');
                    }
                }, {
                    once: true
                });
            });

            itemsContainer.appendChild(btn);
        }

        function insertUploadImageButtonToToolbar(editor, commentId) {
            const toolbarEl = editor.ui.view.toolbar.element;
            const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ck ck-button';
            btn.title = 'Upload Image';
            btn.innerHTML = `<span class="ck-button__label" style="display:flex;align-items:center;gap:2px">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 11a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM5 19l4.5-6 3.5 4.5 2.5-3L19 19H5z"/>
                </svg>
            </span>`;
            btn.style.marginLeft = '6px';
            btn.style.cursor = 'pointer';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            btn.addEventListener('click', () => {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
                input.click();
                input.addEventListener('change', async (e) => {
                    const file = e.target.files[0];
                    if (!file) return;
                    const formData = new FormData();
                    formData.append('upload', file);
                    formData.append('attachable_id', commentId);
                    formData.append('attachable_type', 'App\\Models\\Comment');

                    try {
                        const res = await fetch('/upload-image', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: formData
                        });
                        const data = await res.json();
                        if (res.ok && data.url) {
                            editor.model.change(writer => {
                                const insertPos = editor.model.document.selection
                                    .getFirstPosition();
                                const imageElement = writer.createElement('imageBlock', {
                                    src: data.url
                                });
                                editor.model.insertContent(imageElement, insertPos);
                            });
                        } else alert('Upload gagal.');
                    } catch (err) {
                        console.error(err);
                        alert('Terjadi kesalahan upload image.');
                    }
                }, {
                    once: true
                });
            });

            itemsContainer.appendChild(btn);
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('commentSection', () => ({
                comments: [],
                replyView: {
                    active: false,
                    parentComment: null
                },

                async init() {
                    const eventId = "{{ $event->id }}";
                    try {
                        const res = await fetch(`/comments/${eventId}`, {
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        if (!res.ok) throw new Error('Gagal memuat komentar: ' + res.status);
                        const data = await res.json();
                        this.comments = data.comments || [];
                    } catch (e) {
                        console.error('Gagal memuat komentar:', e);
                        this.comments = [];
                    }
                },

                async submitMain() {
                    const content = getEditorDataSafe('main-editor').trim();
                    if (!content) {
                        alert('Komentar tidak boleh kosong!');
                        return;
                    }

                    const preGeneratedId = window.currentMainCommentId;

                    try {
                        const res = await fetch(`{{ route('comments.store') }}`, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                id: preGeneratedId,
                                content,
                                commentable_id: "{{ $event->id }}",
                                commentable_type: "App\\Models\\CalendarEvent"
                            })
                        });

                        if (!res.ok) {
                            const text = await res.text();
                            console.error('Server error', res.status, text);
                            alert('Gagal mengirim komentar. Refresh halaman.');
                            return;
                        }

                        const data = await res.json();
                        if (data.success) {
                            this.comments.unshift(data.comment);
                            destroyMainEditor('main-editor');
                            await initMainEditor('main-editor');
                        } else {
                            alert(data.message || 'Gagal menambahkan komentar.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Gagal mengirim komentar (network).');
                    }
                },

                async submitReplyFromEditor() {
                    if (!this.replyView.parentComment) return;
                    const parent = this.replyView.parentComment;
                    const key = `reply-${parent.id}`;
                    const content = getEditorDataSafe(key).trim();
                    if (!content) {
                        alert('Balasan tidak boleh kosong!');
                        return;
                    }

                    const preGeneratedId = window[`currentReplyId_${parent.id}`];

                    try {
                        const res = await fetch(`{{ route('comments.store') }}`, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                id: preGeneratedId,
                                content,
                                commentable_id: "{{ $event->id }}",
                                commentable_type: "App\\Models\\CalendarEvent",
                                parent_comment_id: parent.id
                            })
                        });

                        if (!res.ok) {
                            const text = await res.text();
                            console.error('Server error', res.status, text);
                            alert('Gagal mengirim balasan.');
                            return;
                        }

                        const data = await res.json();
                        if (data.success) {
                            if (!parent.replies) parent.replies = [];
                            parent.replies.push(data.comment);
                            this.closeReplyView();
                        } else {
                            alert(data.message || 'Gagal menambahkan balasan.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Gagal mengirim balasan (network).');
                    }
                },

                toggleReply(comment) {
                    if (this.replyView.active && this.replyView.parentComment?.id === comment.id) {
                        this.closeReplyView();
                        return;
                    }
                    if (this.replyView.active && this.replyView.parentComment)
                        destroyReplyEditorFor(this.replyView.parentComment.id);
                    this.replyView.active = true;
                    this.replyView.parentComment = comment;
                    setTimeout(() => initReplyEditorFor(comment.id), 150);
                },

                closeReplyView() {
                    if (this.replyView.parentComment) destroyReplyEditorFor(this.replyView.parentComment
                        .id);
                    this.replyView.active = false;
                    this.replyView.parentComment = null;
                },

                formatCommentDate(dateString) {
                    if (!dateString) return '';
                    const d = new Date(dateString);
                    return d.toLocaleString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            }));
        });

        window.addEventListener('beforeunload', () => {
            Object.keys(editors).forEach(k => {
                try {
                    editors[k]?.destroy?.();
                } catch (e) {}
                delete editors[k];
            });
        });

        function confirmDelete() {
            Swal.fire({
                title: 'Hapus Jadwal?',
                text: "Jadwal yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteForm').submit();
                }
            });
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .ck-content a {
            color: #2563eb !important;
            text-decoration: underline;
            cursor: pointer;
        }

        .ck-content a:hover {
            color: #1d4ed8 !important;
            text-decoration: none;
        }

        .comment-text a {
            color: #2563eb !important;
            text-decoration: underline;
            cursor: pointer;
        }

        .comment-text a:hover {
            color: #1d4ed8 !important;
            text-decoration: none;
        }

        .deskripsi-jadwal a {
            color: #2563eb;
            text-decoration: underline;
            font-weight: 500;
        }

        .deskripsi-jadwal a:hover {
            color: #1d4ed8;
        }

        .prose ul {
            list-style-type: disc;
            padding-left: 1.5rem;
        }

        .prose ol {
            list-style-type: decimal;
            padding-left: 1.5rem;
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
