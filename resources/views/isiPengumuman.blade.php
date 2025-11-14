@extends('layouts.app')

@section('title', 'Isi Pengumuman')

@section('content')
    @include('components.sweet-alert')
    @php
        \Carbon\Carbon::setLocale('id');
    @endphp

    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/styles.css">

    <!-- Alpine.js & CKEditor -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <div class="bg-[#e9effd] min-h-screen font-[Inter,sans-serif] text-black relative" x-data="commentSection">
        @include('components.workspace-nav')

        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="bg-white rounded-xl shadow-md p-6">

                <!-- === CARD PENGUMUMAN (TIDAK DIUBAH) === -->
                <div class="max-w-5xl mx-auto mt-6">
                    <div class="bg-[#e9effd] rounded-xl p-5 mb-6 shadow-md">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start gap-3">
                                <img src="{{ $pengumuman->creator->avatar_url ?? asset('images/dk.jpg') }}" alt="Avatar"
                                    class="rounded-full w-10 h-10 object-cover object-center border border-gray-200 shadow-sm bg-gray-100">
                                <div>
                                    <h1 class="text-xl font-semibold text-black mb-1">
                                        {{ $pengumuman->title }}
                                    </h1>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm text-black font-medium">
                                            {{ $pengumuman->creator->full_name ?? 'Tidak diketahui' }}
                                        </p>
                                        <span class="text-sm text-gray-600">
                                            •
                                            {{ \Carbon\Carbon::parse($pengumuman->created_at)->translatedFormat('d M Y - H:i') }}
                                        </span>
                                    </div>

                                    @if ($pengumuman->due_date)
                                        <span
                                            class="bg-[#102a63] text-white text-xs font-medium px-2 py-1 flex rounded-md items-center gap-1 w-[90px] h-[28px] mt-2">
                                            <img src="{{ asset('images/icons/clock.svg') }}" alt="Clock" class="w-5 h-5">
                                            {{ \Carbon\Carbon::parse($pengumuman->due_date)->translatedFormat('d M') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Tombol tiga titik -->
                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                <button @click="open = !open" @click.away="open = false"
                                    class="text-gray-600 hover:text-gray-800 focus:outline-none">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </button>

                                <div x-show="open" x-transition.scale.origin.top.right
                                    class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-2xl shadow-xl p-4 z-50">
                                    <h3 class="text-center font-semibold text-gray-800 mb-2">Aksi</h3>
                       

                                    @if ($pengumuman->created_by == Auth::id())
                                        <hr class="border-gray-300 mb-3">
                                        <button onclick="openEditModal('{{ $pengumuman->id }}')"
                                            class="flex items-center gap-3 w-full px-3 py-2 hover:bg-gray-100 rounded-lg transition">
                                            <img src="{{ asset('images/icons/Pencil.svg') }}" alt="Edit" class="w-6 h-6">
                                            <span class="text-gray-700 text-base">Edit</span>
                                        </button>
                                    @endif


                                    <hr class="border-gray-300 my-2">

                                    <button @click="open = false"
                                        class="flex items-center gap-3 w-full px-3 py-2 hover:bg-gray-100 rounded-lg transition">
                                        <img src="{{ asset('images/icons/Trash.svg') }}" alt="Hapus" class="w-6 h-6">
                                        <span class="text-gray-700 text-base">Hapus</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr class="border-gray-300 my-4">

                        <div class="ck-content text-black text-[15px] leading-relaxed deskripsi-pengumuman">
                            {!! $pengumuman->description !!}
                        </div>


                    </div>

                    <!-- Section Komentar -->
                    <div class="mt-6">
                        <h3 class="text-base font-semibold text-black mb-4">Komentar</h3>

                        <!-- Input Komentar Utama (placeholder -> CKEditor) -->
                        <div class="flex items-start gap-3 mb-6">
                            <img src="{{ $pengumuman->creator->avatar_url ?? asset('images/dk.jpg') }}" alt="Avatar"
                                class="rounded-full w-10 h-10 object-cover object-center border border-gray-200 shadow-sm bg-gray-100">

                            <!-- gunakan x-data lokal hanya untuk toggle active -->
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

                                            <!-- panggil Alpine method submitMain yg ada di component -->
                                            <button @click="submitMain(); active = false;"
                                                class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                                Kirim
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Daftar Komentar -->
                        <template x-if="comments.length > 0">
                            <div class="space-y-4">
                                <template x-for="comment in comments" :key="comment.id">
                                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                        <div class="flex items-start gap-3">
                                            <img :src="comment.author.avatar" alt=""
                                                class="rounded-full w-10 h-10 object-cover object-center border border-gray-200 shadow-sm bg-gray-100">
                                            <div class="flex-1">
                                                <div class="flex justify-between items-center">
                                                    <p class="text-sm font-semibold text-gray-800"
                                                        x-text="comment.author.name"></p>
                                                    <span class="text-xs text-gray-500"
                                                        x-text="formatCommentDate(comment.createdAt)"></span>
                                                </div>

                                                <div class="text-sm text-gray-700 mt-1 comment-text"
                                                    x-html="comment.content"></div>

                                                <!-- Tombol Balas -->
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

                                                <!-- FORM BALAS (inline) -->
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
                                                                    <img :src="reply.author.avatar"
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

                        <hr class="border-gray-200 my-6">
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <!-- <span>Pengumuman ini diterima oleh 3 anggota</span>
                                                                    <div class="flex -space-x-2">
                                                                        <img src="{{ asset('images/dk.jpg') }}" alt="Avatar"
                                                                            class="rounded-full w-8 h-8 border-2 border-white">
                                                                        <img src="{{ asset('images/dk.jpg') }}" alt="Avatar"
                                                                            class="rounded-full w-8 h-8 border-2 border-white">
                                                                        <img src="{{ asset('images/dk.jpg') }}" alt="Avatar"
                                                                            class="rounded-full w-8 h-8 border-2 border-white">
                                                                    </div> -->
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/ckeditor.js"></script>

    <script>
        const editors = {}; // key -> CKEditor instance

        // =========================
        // Generate UUID v4
        // =========================
        function generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = Math.random() * 16 | 0;
                const v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        // =========================
        // Fungsi membuat editor dengan upload
        // =========================
        async function createEditor(el, type = 'all', commentId = null) {
            try {
                const toolbarItems = [
                    'undo', 'redo', '|',
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'link', 'blockQuote', '|',
                    'bulletedList', 'numberedList', '|',
                    'insertTable', '|'
                ];

                const config = {
                    toolbar: { items: toolbarItems, shouldNotGroupWhenFull: true },
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraf', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                        ]
                    },
                    placeholder: el.dataset.placeholder || ''
                };

                const editor = await ClassicEditor.create(el, config);

                // Tambahkan tombol upload file/image berdasarkan type
                if (type === 'all') {
                    insertUploadFileButtonToToolbar(editor, commentId);
                    insertUploadImageButtonToToolbar(editor, commentId);
                } else if (type === 'image') {
                    insertUploadFileButtonToToolbar(editor, commentId);
                    insertUploadImageButtonToToolbar(editor, commentId);
                }

                // Styling default editor content
                editor.editing.view.change(writer => {
                    writer.setStyle('font-family', 'Inter, sans-serif', editor.editing.view.document.getRoot());
                    writer.setStyle('font-size', '14px', editor.editing.view.document.getRoot());
                    writer.setStyle('color', '#000000', editor.editing.view.document.getRoot());
                });

                return editor;
            } catch (err) {
                console.warn('CKEditor create failed, fallback to textarea for', el.id, err);
                el.innerHTML = `<textarea id="${el.id}-fallback" class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none"></textarea>`;
                return null;
            }
        }

        // =========================
        // Inisialisasi & Destroy Editor
        // =========================
        async function initMainEditor(id = 'main-editor') {
            const el = document.getElementById(id);
            if (!el) return;
            if (editors[id] !== undefined) return;

            // Generate UUID baru untuk komentar utama
            const newCommentId = generateUUID();
            window.currentMainCommentId = newCommentId;

            const inst = await createEditor(el, 'all', newCommentId);
            editors[id] = inst;
        }

        function destroyMainEditor(id = 'main-editor') {
            const inst = editors[id];
            if (inst) inst.destroy().catch(() => { });
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

            // Generate UUID baru untuk reply
            const newReplyId = generateUUID();
            window[`currentReplyId_${commentId}`] = newReplyId;

            const inst = await createEditor(el, 'all', newReplyId);
            editors[`reply-${commentId}`] = inst;
        }

        function destroyReplyEditorFor(commentId) {
            const key = `reply-${commentId}`;
            const inst = editors[key];
            if (inst) inst.destroy().catch(() => { });
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

        // =========================
        // Alpine.js component
        // =========================
        document.addEventListener('alpine:init', () => {
            Alpine.data('commentSection', () => ({
                comments: [],
                replyView: { active: false, parentComment: null },

                async init() {
                    const pengumumanId = "{{ $pengumuman->id }}";
                    try {
                        const res = await fetch(`/comments/${pengumumanId}`, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                        if (!res.ok) throw new Error('Gagal memuat komentar: ' + res.status);
                        const data = await res.json();
                        this.comments = data.comments || [];
                    } catch (e) {
                        console.error('Gagal memuat komentar:', e);
                        this.comments = [];
                    }
                    await initMainEditor('main-editor');
                },

                async submitMain() {
                    const content = getEditorDataSafe('main-editor').trim();
                    if (!content) { alert('Komentar tidak boleh kosong!'); return; }

                    // Gunakan UUID yang sudah di-generate saat init editor
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
                                id: preGeneratedId, // Kirim pre-generated ID
                                content,
                                commentable_id: "{{ $pengumuman->id }}",
                                commentable_type: "App\\Models\\Pengumuman"
                            })
                        });

                        if (!res.ok) { const text = await res.text(); console.error('Server error', res.status, text); alert('Gagal mengirim komentar. Refresh halaman.'); return; }

                        const data = await res.json();
                        if (data.success) {
                            this.comments.unshift(data.comment);
                            destroyMainEditor('main-editor');
                            await initMainEditor('main-editor');
                        } else { alert(data.message || 'Gagal menambahkan komentar.'); }
                    } catch (err) { console.error(err); alert('Gagal mengirim komentar (network).'); }
                },

                async submitReplyFromEditor() {
                    if (!this.replyView.parentComment) return;
                    const parent = this.replyView.parentComment;
                    const key = `reply-${parent.id}`;
                    const content = getEditorDataSafe(key).trim();
                    if (!content) { alert('Balasan tidak boleh kosong!'); return; }

                    // Gunakan UUID yang sudah di-generate saat init editor
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
                                id: preGeneratedId, // Kirim pre-generated ID
                                content,
                                commentable_id: "{{ $pengumuman->id }}",
                                commentable_type: "App\\Models\\Pengumuman",
                                parent_comment_id: parent.id
                            })
                        });

                        if (!res.ok) { const text = await res.text(); console.error('Server error', res.status, text); alert('Gagal mengirim balasan.'); return; }

                        const data = await res.json();
                        if (data.success) {
                            if (!parent.replies) parent.replies = [];
                            parent.replies.push(data.comment);
                            this.closeReplyView();
                        } else { alert(data.message || 'Gagal menambahkan balasan.'); }
                    } catch (err) { console.error(err); alert('Gagal mengirim balasan (network).'); }
                },

                toggleReply(comment) {
                    if (this.replyView.active && this.replyView.parentComment?.id === comment.id) { this.closeReplyView(); return; }
                    if (this.replyView.active && this.replyView.parentComment) destroyReplyEditorFor(this.replyView.parentComment.id);
                    this.replyView.active = true;
                    this.replyView.parentComment = comment;
                    setTimeout(() => initReplyEditorFor(comment.id), 150);
                },

                closeReplyView() {
                    if (this.replyView.parentComment) destroyReplyEditorFor(this.replyView.parentComment.id);
                    this.replyView.active = false;
                    this.replyView.parentComment = null;
                },

                formatCommentDate(dateString) {
                    if (!dateString) return '';
                    const d = new Date(dateString);
                    return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                }
            }));
        });

        // =========================
        // Event & cleanup
        // =========================
        window.addEventListener('submit-main-comment', () => {
            const root = document.querySelector('[x-data="commentSection"]');
            if (root && root.__x) { const data = root.__x.$data; if (typeof data.submitMain === 'function') data.submitMain(); }
        });

        window.addEventListener('beforeunload', () => {
            Object.keys(editors).forEach(k => { try { editors[k]?.destroy?.(); } catch (e) { } delete editors[k]; });
        });

        // =========================
        // Fungsi insert tombol upload
        // =========================
        function insertUploadImageButtonToToolbar(editor, commentId) {
            const toolbarEl = editor.ui.view.toolbar.element;
            const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

            const btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'ck ck-button'; btn.title = 'Upload Image';
            btn.innerHTML = `
                                                                                            <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
                                                                                                ${imageIconSVG()}
                                                                                            </span>
                                                                                        `;
            btn.style.marginLeft = '6px';
            btn.style.cursor = 'pointer';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            btn.addEventListener('click', () => {
                const input = document.createElement('input');
                input.type = 'file'; input.accept = 'image/*'; input.click();
                input.addEventListener('change', async (e) => {
                    const file = e.target.files[0]; if (!file) return;
                    const formData = new FormData();
                    formData.append('upload', file);

                    // Kirim commentId yang sudah di-generate
                    formData.append('attachable_id', commentId);
                    formData.append('attachable_type', 'App\\Models\\Comment');

                    try {
                        const res = await fetch('/upload-image', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            body: formData
                        });
                        const data = await res.json();
                        if (res.ok && data.url) {
                            editor.model.change(writer => {
                                const insertPos = editor.model.document.selection.getFirstPosition();
                                const imageElement = writer.createElement('imageBlock', { src: data.url });
                                editor.model.insertContent(imageElement, insertPos);
                            });
                        } else alert('Upload gagal.');
                    } catch (err) { console.error(err); alert('Terjadi kesalahan upload image.'); }
                }, { once: true });
            });

            itemsContainer.appendChild(btn);

            // Ikon SVG untuk tombol Upload Image
            function imageIconSVG() {
                return `
                                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                                                                    <path d="M21 19V5a2 2 0 0 0-2-2H5
                                                                        a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14
                                                                        a2 2 0 0 0 2-2zM8.5 11
                                                                        a1.5 1.5 0 1 1 0-3
                                                                        1.5 1.5 0 0 1 0 3zM5 19
                                                                        l4.5-6 3.5 4.5 2.5-3L19 19H5z"/>
                                                                </svg>
                                                            `;
            }
        }

        function insertUploadFileButtonToToolbar(editor, commentId) {
            const toolbarEl = editor.ui.view.toolbar.element;
            const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

            const btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'ck ck-button'; btn.title = 'Upload File';
            btn.innerHTML =
                ` <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">${fileIconSVG()}</span>`;
            btn.style.marginLeft = '6px'; btn.style.cursor = 'pointer';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            btn.addEventListener('click', () => {
                const input = document.createElement('input');
                input.type = 'file';
                input.accept = ".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar,.ppt,.pptx";
                input.click();

                input.addEventListener('change', async (e) => {
                    const file = e.target.files[0]; if (!file) return;
                    const formData = new FormData();
                    formData.append('upload', file);

                    // Kirim commentId yang sudah di-generate
                    formData.append('attachable_id', commentId);
                    formData.append('attachable_type', 'App\\Models\\Comment');

                    try {
                        const res = await fetch('/upload', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            body: formData
                        });
                        const data = await res.json();
                        if (res.ok && data.url) {
                            editor.model.change(writer => {
                                const insertPos = editor.model.document.selection.getFirstPosition();
                                const paragraph = writer.createElement('paragraph');
                                const textNode = writer.createText(file.name, { linkHref: data.url });
                                writer.append(textNode, paragraph);
                                editor.model.insertContent(paragraph, insertPos);
                            });
                        } else alert('Upload file gagal.');
                    } catch (err) { console.error(err); alert('Terjadi kesalahan upload file.'); }
                }, { once: true });
            });

            itemsContainer.appendChild(btn);
            // Ikon SVG untuk tombol Upload File
            function fileIconSVG() {
                return `
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20" height="20">
                                        <path fill="currentColor" d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2 4 4H10V4z"/>
                                        </svg>`;
            }
        }
    </script>

    <style>
        /* Untuk tampilan di CKEditor */
        .ck-content a {
            color: #2563eb !important;
            /* biru seperti di pengumuman */
            text-decoration: underline;
            cursor: pointer;
        }

        /* Saat hover (disentuh mouse) */
        .ck-content a:hover {
            color: #1d4ed8 !important;
            /* biru lebih tua pas hover */
            text-decoration: none;
        }

        /* Untuk tampilan hasil (misalnya di daftar komentar atau detail pengumuman) */
        .comment-content a,
        .description-content a {
            color: #2563eb !important;
            text-decoration: underline;
            cursor: pointer;
        }

        .comment-content a:hover,
        .description-content a:hover {
            color: #1d4ed8 !important;
            text-decoration: none;
        }
    </style>


    <style>
        .ck-content {
            white-space: pre-wrap !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }

        .deskripsi-pengumuman a {
            color: #2563eb;
            /* biru Tailwind */
            text-decoration: underline;
            font-weight: 500;
        }

        .deskripsi-pengumuman a:hover {
            color: #1d4ed8;
            /* biru lebih gelap saat hover */
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
    </style>

    <style>
        /* Responsif untuk Card Pengumuman */
        @media (max-width: 640px) {
            .bg-[#e9effd] .flex.items-start.justify-between {
                flex-direction: column;
                gap: 1rem;
            }

            .bg-[#e9effd] img.rounded-full {
                width: 3rem;
                height: 3rem;
            }

            .bg-[#e9effd] h1.text-xl {
                font-size: 1rem;
            }

            .bg-[#e9effd] .flex.items-center.gap-2 p,
            .bg-[#e9effd] .flex.items-center.gap-2 span {
                font-size: 0.75rem;
            }

            .bg-[#e9effd] span.bg-[#102a63] {
                width: 70px;
                height: 24px;
                font-size: 0.7rem;
            }
        }

        @media (max-width: 640px) {
            .flex.items-start.gap-3 img.rounded-full {
                width: 2.5rem;
                height: 2.5rem;
            }

            input[type="text"] {
                font-size: 0.75rem;
                padding: 0.4rem 0.5rem;
            }

            #main-editor,
            [id^="reply-editor-"] {
                min-height: 100px !important;
            }

            .flex.justify-end.gap-2 button {
                padding: 0.25rem 0.5rem;
                font-size: 0.7rem;
            }


        }
    </style>

    <!-- ini untuk edit -->

    <!-- POPUP EDIT -->
    <div id="openEditModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-lg p-6 w-full max-w-3xl">
            <h2 class="text-xl font-bold mb-4 text-[#102a63] border-b pb-2">Edit Pengumuman</h2>

            <form action="#" method="POST" class="space-y-5" id="pengumumanEditForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="pengumuman_id" id="editPengumumanId">
                <!-- ✅ TAMBAHKAN INPUT HIDDEN UNTUK WORKSPACE ID -->
                <input type="hidden" id="editWorkspaceId" name="workspace_id" value="{{ $workspace->id }}">

                <!-- Judul -->
                <div>
                    <label class="block text-sm font-inter font-semibold text-black mb-1">
                        Judul Pengumuman <span class="text-red-500">*</span>
                    </label>

                    <input type="text" name="title" id="editTitle" placeholder="Masukkan judul pengumuman..."
                        class="w-full border border-[#6B7280] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 font-[Inter] text-[14px] placeholder:text-[#6B7280] pl-5" />

                    <!-- Deskripsi -->
                    <div class="flex flex-col" x-data
                        x-init="createEditorFor('edit-catatan-editor', { placeholder: 'Masukkan catatan anda disini...' })">
                        <label class="block text-sm font-inter font-semibold text-black mb-1 mt-5">
                            Deskripsi <span class="text-red-500">*</span>
                        </label>

                        <!-- CKEditor Container -->
                        <div id="edit-catatan-editor"
                            class="border border-[#6B7280] rounded-lg bg-white min-h-[160px] p-2 font-[Inter] text-[14px] placeholder-[#6B7280]">
                        </div>
                        <input type="hidden" name="description" id="editCatatanInput">
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
                                            'insertTable', '|',
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
                                insertUploadImageButtonToToolbar(editor);

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

                        //fungsi untuk upload image
                        function insertUploadImageButtonToToolbar(editor) {
                            try {
                                const toolbarEl = editor.ui.view.toolbar.element;
                                const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'ck ck-button';
                                btn.title = 'Upload Image';
                                btn.innerHTML = `
                    <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
                        ${imageIconSVG()}
                    </span>
                `;

                                btn.style.marginLeft = '6px';
                                btn.style.padding = '4px 8px';
                                btn.style.borderRadius = '6px';
                                btn.style.background = 'transparent';
                                btn.style.border = '0';
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

                                        btn.innerHTML = '⏳';
                                        const formData = new FormData();
                                        formData.append('upload', file);

                                        // TAMBAHKAN attachable_id saat upload image
                                        const pengumumanId = document.getElementById('editPengumumanId')?.value;
                                        if (pengumumanId) {
                                            formData.append('attachable_id', pengumumanId);
                                        }

                                        try {
                                            const res = await fetch('/upload-image', {
                                                method: 'POST',
                                                headers: { 'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}' },
                                                body: formData
                                            });

                                            const data = await res.json();
                                            if (res.ok && data.url) {
                                                editor.model.change(writer => {
                                                    const insertPos = editor.model.document.selection.getFirstPosition();
                                                    const imageElement = writer.createElement('imageBlock', { src: data.url });
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
                                            btn.innerHTML = `
                                <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
                                    ${imageIconSVG()}
                                </span>
                            `;
                                        }
                                    }, { once: true });
                                });

                                itemsContainer.appendChild(btn);
                            } catch (err) {
                                console.error('Insert upload image button error:', err);
                            }
                            // Ikon SVG untuk tombol Upload Image
                            function imageIconSVG() {
                                return `
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                        <path d="M21 19V5a2 2 0 0 0-2-2H5
                            a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14
                            a2 2 0 0 0 2-2zM8.5 11
                            a1.5 1.5 0 1 1 0-3
                            1.5 1.5 0 0 1 0 3zM5 19
                            l4.5-6 3.5 4.5 2.5-3L19 19H5z"/>
                    </svg>
                `;
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
                                btn.innerHTML =
                                    ` <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">${fileIconSVG()}</span>`;
                                btn.style.marginLeft = '6px';
                                btn.style.padding = '4px 8px';
                                btn.style.borderRadius = '6px';
                                btn.style.background = 'transparent';
                                btn.style.border = '0';
                                btn.style.cursor = 'pointer';

                                // ✅ Ambil CSRF token dari meta tag (aman untuk Blade & eksternal JS)
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

                                        // TAMBAHKAN attachable_id saat upload
                                        const pengumumanId = document.getElementById('editPengumumanId')?.value;
                                        if (pengumumanId) {
                                            formData.append('attachable_id', pengumumanId);
                                        }

                                        try {
                                            const res = await fetch('/upload', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': csrfToken || '{{ csrf_token() }}'
                                                },
                                                body: formData
                                            });

                                            const data = await res.json();

                                            if (res.ok && data.url) {
                                                editor.model.change(writer => {
                                                    const insertPos = editor.model.document.selection.getFirstPosition();

                                                    // Tambahkan elemen paragraf dengan text berwarna biru & underline
                                                    const linkElement = writer.createElement('paragraph');
                                                    const textNode = writer.createText(file.name, { linkHref: data.url });
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
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20" height="20">
                    <path fill="currentColor" d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2 4 4H10V4z"/>
                </svg>`;
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
                                class="flex items-center rounded-lg border border-[#d0d7e2] overflow-hidden edit-chip-container-1">
                                <div
                                    class="flex items-center bg-[#6B7280] text-white text-[14px] font-[Inter] px-3 py-1.5 rounded-l-lg edit-chip-text-1">
                                    Selesai otomatis
                                </div>
                                <button type="button" class="px-2 text-gray-500 hover:text-gray-700 edit-dropdown-btn-1">
                                    <img src="/images/icons/down.svg" alt="down">
                                </button>
                            </div>

                            <span class="text-[14px] text-black font-[Inter]" id="editLabelText">Selesai otomatis
                                pada:</span>

                            <!-- Select chip 2 (Dropdown) -->
                            <div class="flex items-center rounded-lg border border-[#d0d7e2] overflow-hidden edit-chip-container-2"
                                id="editDropdownChip">
                                <div
                                    class="flex items-center bg-[#6B7280] text-white text-[14px] font-[Inter] px-3 py-1.5 rounded-l-lg edit-chip-text-2">
                                    1 hari dari sekarang
                                </div>
                                <button type="button" class="px-2 text-gray-500 hover:text-gray-700 edit-dropdown-btn-2">
                                    <img src="/images/icons/down.svg" alt="down">
                                </button>
                            </div>

                            <!-- Date Input (Hidden by default) -->
                            <div class="hidden items-center gap-2" id="editDateInputContainer">
                                <div class="flex items-center rounded-lg border border-[#d0d7e2] overflow-hidden relative">
                                    <input type="date" name="due_date" id="editCustomDeadline"
                                        class="bg-[#6B7280] text-white text-[14px] font-[Inter] px-3 py-1.5 rounded-l-lg focus:outline-none border-0 pr-10">
                                    <button type="button"
                                        class="px-2 bg-white absolute right-0 h-full flex items-center justify-center pointer-events-auto"
                                        id="editCalendarBtn" style="z-index: 5;">
                                        <img src="/images/icons/calendarAbu.svg" alt="calendar" class="w-5 h-5">
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="auto_due" id="editAutoDue" value="">
                    </div>

                    <style>
                        /* ini untuk tampilan link filenya di pengumuman */
                        .ck-content a {
                            color: #2563eb;
                            /* biru Tailwind (blue-600) */
                            text-decoration: underline;
                            font-weight: 500;
                        }

                        .ck-content a:hover {
                            color: #1d4ed8;
                            /* sedikit lebih gelap pas hover */
                        }

                        /* ini untuk style link file upload awal biru */
                        .ck-content a {
                            color: #007bff !important;
                            text-decoration: underline !important;
                        }

                        /* Styling untuk input date */
                        #editCustomDeadline::-webkit-calendar-picker-indicator {
                            opacity: 0;
                            position: absolute;
                            right: 0;
                            width: 100%;
                            height: 100%;
                            cursor: pointer;
                            z-index: 10;
                        }

                        /* Warna placeholder untuk browser yang support */
                        #editCustomDeadline::-webkit-datetime-edit-text {
                            color: white;
                        }

                        #editCustomDeadline::-webkit-datetime-edit-month-field,
                        #editCustomDeadline::-webkit-datetime-edit-day-field,
                        #editCustomDeadline::-webkit-datetime-edit-year-field {
                            color: white;
                        }

                        /* Fix display saat show */
                        #editDateInputContainer:not(.hidden) {
                            display: flex;
                        }
                    </style>

                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            // Dropdown 1 untuk edit
                            const dropdown1 = document.createElement("div");
                            dropdown1.className =
                                "absolute bg-white border border-gray-300 rounded-lg shadow-md mt-1 w-[200px] hidden z-50 edit-dropdown-menu-1";
                            dropdown1.innerHTML = `
                <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-t-lg text-black font-[Inter]" data-value="Selesai otomatis">Selesai otomatis</div>
                <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-b-lg text-black font-[Inter]" data-value="Atur tenggat waktu sendiri">Atur tenggat waktu sendiri</div>
            `;
                            // Dropdown 2 untuk edit
                            const dropdown2 = document.createElement("div");
                            dropdown2.className =
                                "absolute bg-white border border-gray-300 rounded-lg shadow-md mt-1 w-[200px] hidden z-50 edit-dropdown-menu-2";
                            dropdown2.innerHTML = `
                <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-t-lg text-black font-[Inter]" data-value="1 hari dari sekarang">1 hari dari sekarang</div>
                <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer text-black font-[Inter]" data-value="3 hari dari sekarang">3 hari dari sekarang</div>
                <div class="px-4 py-2 hover:bg-[#f1f5ff] cursor-pointer rounded-b-lg text-black font-[Inter]" data-value="7 hari dari sekarang">7 hari dari sekarang</div>
            `;

                            document.body.appendChild(dropdown1);
                            document.body.appendChild(dropdown2);

                            // Get elements untuk edit
                            const btn1 = document.querySelector(".edit-dropdown-btn-1");
                            const btn2 = document.querySelector(".edit-dropdown-btn-2");
                            const chipText1 = document.querySelector(".edit-chip-text-1");
                            const chipText2 = document.querySelector(".edit-chip-text-2");
                            const chipContainer1 = document.querySelector(".edit-chip-container-1");
                            const chipContainer2 = document.querySelector(".edit-chip-container-2");
                            const labelText = document.getElementById("editLabelText");
                            const dropdownChip = document.getElementById("editDropdownChip");
                            const dateInputContainer = document.getElementById("editDateInputContainer");
                            const customDeadline = document.getElementById("editCustomDeadline");
                            const calendarBtn = document.getElementById("editCalendarBtn");

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
                                    document.getElementById("editAutoDue").value = value; // ✅ simpan ke input hidden
                                    dropdown2.classList.add("hidden");
                                });
                            });

                            // Close dropdowns when clicking outside
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
                    <div x-data="editPengumumanMembers()" class="space-y-2">
                        <label class="block text-sm font-inter font-semibold text-black mb-1 mt-5 text-left">
                            Penerima Pengumuman <span class="text-red-500">*</span>
                        </label>

                        <div class="flex items-center">
                            <!-- Avatar list -->
                            <div class="flex -space-x-2" id="editSelectedMembersAvatars">
                                <template x-for="member in selectedMembers" :key="member.id">
                                    <div class="w-10 h-10 rounded-full border-2 border-white shadow-sm overflow-hidden">
                                        <img :src="member.avatar" :alt="member.name" :title="member.name"
                                            class="w-full h-full object-cover">
                                    </div>
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
                        <template x-for="id in selectedMembers.map(m => m.id)" :key="id">
                            <input type="hidden" name="recipients[]" :value="id">
                        </template>

                        <!-- Modal Pilih penerima -->
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
                                            <input type="checkbox" :value="member.id" @change="toggleMember(member)"
                                                :checked="isSelected(member.id)"
                                                :disabled="window.currentUser && member.id === window.currentUser.id"
                                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        </label>
                                    </template>

                                    <div x-show="filteredMembers.length === 0"
                                        class="text-center text-sm text-gray-500 py-6">
                                        Tidak ada anggota ditemukan
                                    </div>
                                </div>

                                <div class="p-4 border-t border-gray-200 flex justify-end gap-3 bg-gray-50 rounded-b-xl">
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

                    <!-- Rahasia -->
                    <div class="mt-3">
                        <label class="block text-sm font-inter font-semibold text-black mb-1">
                            Apakah pengumuman ini rahasia untuk penerima saja? <span class="text-red-500">*</span>
                        </label>

                        <label class="inline-flex items-center cursor-pointer">
                            <!-- Hidden untuk memastikan nilai 0 dikirim jika tidak dicentang -->
                            <input type="hidden" name="is_private" value="0">

                            <!-- Checkbox utama -->
                            <input type="checkbox" name="is_private" id="editSwitchRahasia" class="sr-only" value="1">
                            <div id="editSwitchBg"
                                class="relative w-11 h-6 bg-gray-300 rounded-full transition-colors duration-300">
                                <span id="editSwitchCircle"
                                    class="absolute top-[2px] left-[2px] w-[20px] h-[20px] bg-white rounded-full transition-transform duration-300"></span>
                            </div>
                            <span class="ml-2 text-[#102a63] font-medium">Rahasia</span>
                        </label>

                        <script>
                            const editSwitchRahasia = document.getElementById('editSwitchRahasia');
                            const editSwitchBg = document.getElementById('editSwitchBg');
                            const editSwitchCircle = document.getElementById('editSwitchCircle');

                            editSwitchRahasia.addEventListener('change', function () {
                                if (this.checked) {
                                    editSwitchBg.classList.remove('bg-gray-300');
                                    editSwitchBg.classList.add('bg-blue-600');
                                    editSwitchCircle.style.transform = 'translateX(20px)';
                                } else {
                                    editSwitchBg.classList.remove('bg-blue-600');
                                    editSwitchBg.classList.add('bg-gray-300');
                                    editSwitchCircle.style.transform = 'translateX(0px)';
                                }
                            });
                        </script>

                        <!-- Tombol Lebih Besar -->
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" id="editBtnBatal"
                                class="border border-blue-700 text-blue-600 bg-white px-8 py-2 text-[16px] rounded-lg hover:bg-red-50 transition">
                                Batal
                            </button>
                            <button type="submit" id="editSubmitBtn"
                                class="bg-blue-700 text-white px-8 py-2 text-[16px] rounded-lg hover:bg-blue-800 transition">
                                Perbarui
                            </button>
                        </div>
            </form>
        </div>
    </div>

    <script>
        window.currentUser = @json(auth()->user());
    </script>

    <script>
        // Variabel untuk mencegah multiple submit
        let isSubmittingEdit = false;

        // Fungsi untuk membuka modal edit
        function openEditModal(pengumumanId) {
            console.log('Opening edit modal for ID:', pengumumanId);

            // Tampilkan modal
            const modal = document.getElementById('openEditModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Set pengumuman ID
            document.getElementById('editPengumumanId').value = pengumumanId;

            // Reset form state terlebih dahulu
            resetEditForm();

            // Ambil data pengumuman dari server
            fetch(`/pengumuman/${pengumumanId}/edit-data`)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil data');
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    // Isi form dengan data yang diterima
                    populateEditForm(data);
                })
                .catch(error => {
                    console.error('Fetch error (edit-data):', error);
                    closeEditModal();  // kalau mau tetap ditutup
                });
        }

        // Fungsi reset form
        function resetEditForm() {
            document.getElementById('editTitle').value = '';

            // Reset CKEditor
            const editor = window['edit-catatan-editor_editor'];
            if (editor) {
                editor.setData('');
            }

            // Reset due date settings ke default
            document.querySelector('.edit-chip-text-1').textContent = 'Selesai otomatis';
            document.getElementById('editLabelText').textContent = 'Selesai otomatis pada:';
            document.getElementById('editDropdownChip').classList.remove('hidden');
            document.getElementById('editDateInputContainer').classList.add('hidden');
            document.querySelector('.edit-chip-text-2').textContent = '1 hari dari sekarang';
            document.getElementById('editAutoDue').value = '1 hari dari sekarang';
            document.getElementById('editCustomDeadline').value = '';

            // Reset privacy switch
            const switchInput = document.getElementById('editSwitchRahasia');
            const switchBg = document.getElementById('editSwitchBg');
            const switchCircle = document.getElementById('editSwitchCircle');

            switchInput.checked = false;
            switchBg.classList.remove('bg-blue-600');
            switchBg.classList.add('bg-gray-300');
            switchCircle.style.transform = 'translateX(0px)';

            // Reset recipients
            if (window.editMembersComponent) {
                window.editMembersComponent.selectedMembers = [];
                window.editMembersComponent.tempSelectedMembers = [];
            }

            // Reset submit state
            isSubmittingEdit = false;
        }

        // Fungsi untuk mengisi form dengan data
        function populateEditForm(data) {
            console.log('Populating form with data:', data);

            // Judul
            document.getElementById('editTitle').value = data.title || '';

            // Deskripsi - set ke CKEditor
            const editor = window['edit-catatan-editor_editor'];
            if (editor) {
                editor.setData(data.description || '');
            } else {
                // Jika editor belum siap, tunggu sebentar
                setTimeout(() => {
                    const editorRetry = window['edit-catatan-editor_editor'];
                    if (editorRetry) {
                        editorRetry.setData(data.description || '');
                    } else {
                        console.error('CKEditor not found');
                    }
                }, 500);
            }

            // Due date settings
            if (data.auto_due && data.auto_due !== '') {
                // Mode auto due
                document.querySelector('.edit-chip-text-1').textContent = 'Selesai otomatis';
                document.getElementById('editLabelText').textContent = 'Selesai otomatis pada:';
                document.getElementById('editDropdownChip').classList.remove('hidden');
                document.getElementById('editDateInputContainer').classList.add('hidden');
                document.querySelector('.edit-chip-text-2').textContent = data.auto_due;
                document.getElementById('editAutoDue').value = data.auto_due;
            } else if (data.due_date) {
                // Mode manual due date
                document.querySelector('.edit-chip-text-1').textContent = 'Atur tenggat waktu sendiri';
                document.getElementById('editLabelText').textContent = 'Tenggat :';
                document.getElementById('editDropdownChip').classList.add('hidden');
                document.getElementById('editDateInputContainer').classList.remove('hidden');
                document.getElementById('editCustomDeadline').value = data.due_date;
                document.getElementById('editAutoDue').value = '';
            } else {
                // Default mode
                document.querySelector('.edit-chip-text-1').textContent = 'Selesai otomatis';
                document.getElementById('editLabelText').textContent = 'Selesai otomatis pada:';
                document.getElementById('editDropdownChip').classList.remove('hidden');
                document.getElementById('editDateInputContainer').classList.add('hidden');
                document.querySelector('.edit-chip-text-2').textContent = '1 hari dari sekarang';
                document.getElementById('editAutoDue').value = '1 hari dari sekarang';
            }

            // Privacy setting
            const switchInput = document.getElementById('editSwitchRahasia');
            const switchBg = document.getElementById('editSwitchBg');
            const switchCircle = document.getElementById('editSwitchCircle');

            if (data.is_private) {
                switchInput.checked = true;
                switchBg.classList.remove('bg-gray-300');
                switchBg.classList.add('bg-blue-600');
                switchCircle.style.transform = 'translateX(20px)';
            } else {
                switchInput.checked = false;
                switchBg.classList.remove('bg-blue-600');
                switchBg.classList.add('bg-gray-300');
                switchCircle.style.transform = 'translateX(0px)';
            }

            // Recipients
            if (window.editMembersComponent && data.recipients_data) {
                window.editMembersComponent.selectedMembers = data.recipients_data;
                window.editMembersComponent.tempSelectedMembers = [...data.recipients_data];
                console.log('Set selected members:', data.recipients_data);
            }
        }

        // 🔵🔵🔵 BAGIAN PERBAIKAN ADA DI SINI 🔵🔵🔵
        // Event listener untuk form submit
        document.getElementById('pengumumanEditForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Cegah multiple submit
            if (isSubmittingEdit) {
                console.log('Submit already in progress, please wait...');
                return;
            }

            isSubmittingEdit = true;

            const pengumumanId = document.getElementById('editPengumumanId').value;

            // 🔥🔥 FIX CLICK 1X — sinkronkan CKEditor sebelum FormData dibuat
            if (window['edit-catatan-editor_editor']) {
                window['edit-catatan-editor_editor'].updateSourceElement();
            }
            // 🔥🔥 END FIX

            const formData = new FormData(this);

            // Validasi form
            const title = document.getElementById('editTitle').value.trim();
            const editor = window['edit-catatan-editor_editor'];
            const editorData = editor ? editor.getData().trim() : '';

            // Get selected members dari Alpine component
            const selectedMembers = window.editMembersComponent ?
                window.editMembersComponent.selectedMembers.map(m => m.id) : [];

            if (!title) {
                Swal.fire("Kolom Belum Diisi", "Judul pengumuman wajib diisi.", "warning");
                isSubmittingEdit = false;
                return;
            }

            if (!editorData || editorData === "<p><br></p>") {
                Swal.fire("Kolom Belum Diisi", "Deskripsi pengumuman wajib diisi.", "warning");
                isSubmittingEdit = false;
                return;
            }

            // Validasi penerima untuk pengumuman rahasia
            const isPrivate = document.getElementById('editSwitchRahasia').checked;
            if (isPrivate && (!selectedMembers || selectedMembers.length === 0)) {
                Swal.fire("Penerima Kosong", "Pilih minimal 1 penerima pengumuman.", "warning");
                isSubmittingEdit = false;
                return;
            }

            // Set deskripsi ke hidden input
            document.getElementById('editCatatanInput').value = editorData;

            // Tambahkan recipients ke formData
            selectedMembers.forEach(memberId => {
                formData.append('recipients[]', memberId);
            });

            // TAMBAHKAN attachable_id ke formData untuk attachments
            formData.append('attachable_id', pengumumanId);

            console.log('Submitting form with data:', {
                title: title,
                description: editorData.substring(0, 100) + '...',
                is_private: isPrivate,
                recipients: selectedMembers,
                attachable_id: pengumumanId
            });

            // 🟦 PERBAIKAN FETCH — TIDAK AKAN ERROR "<DOCTYPE>" LAGI
            fetch(`/pengumuman/${pengumumanId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: formData
            })
                .then(async response => {
                    // Jika server mengembalikan HTML → langsung lempar error
                    const text = await response.text();
                    try {
                        return JSON.parse(text);
                    } catch {
                        throw new Error("Server tidak mengembalikan JSON: " + text.slice(0, 120));
                    }
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            closeEditModal();
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire("Error", data.message || "Terjadi kesalahan", "error");
                    }
                })
                .catch(error => {
                    console.error('Submit error (update):', error);
                })
                .finally(() => {
                    isSubmittingEdit = false;
                });
        });


        // Fungsi untuk menutup modal edit
        function closeEditModal() {
            const modal = document.getElementById('openEditModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Reset submit state
            isSubmittingEdit = false;
        }

        // Event listener untuk tombol batal
        document.getElementById('editBtnBatal').addEventListener('click', closeEditModal);

        // Alpine.js component untuk edit members - VERSI DIPERBAIKI
        document.addEventListener('alpine:init', () => {
            Alpine.data('editPengumumanMembers', () => ({
                showManageMembersModal: false,
                members: [],
                tempSelectedMembers: [], // 🔹 Temp untuk centang sementara di modal
                selectedMembers: [], // 🔹 Member yang sudah di-Terapkan
                search: '',

                async init() {
                    console.log('🎯 Alpine edit members component initialized');
                    await this.loadMembers();
                    window.editMembersComponent = this;

                    // 🔹 Tambahkan pembuat pengumuman otomatis
                    if (window.currentUser) {
                        const creator = this.members.find(m => m.id === window.currentUser.id);
                        if (creator && !this.isSelected(creator.id)) {
                            this.selectedMembers.push(creator);
                            this.tempSelectedMembers.push(creator);
                        }
                    }
                },

                async loadMembers() {
                    try {
                        // ✅ DAPATKAN WORKSPACE ID DARI INPUT HIDDEN
                        const workspaceId = document.getElementById('editWorkspaceId')?.value;
                        console.log('🏢 Workspace ID untuk edit:', workspaceId);

                        if (!workspaceId) {
                            console.error('Workspace ID tidak ditemukan');
                            return;
                        }

                        const res = await fetch(`/pengumuman/anggota/${workspaceId}`);
                        if (!res.ok) throw new Error('Gagal mengambil data anggota');

                        this.members = await res.json();
                        console.log('✅ Data anggota edit berhasil di-load:', this.members.length, 'anggota');
                    } catch (err) {
                        console.error('Gagal memuat anggota:', err);
                        this.members = [];
                    }
                },

                toggleMember(member) {
                    // 🔹 Tidak bisa hapus diri sendiri
                    if (window.currentUser && member.id === window.currentUser.id) return;

                    const idx = this.tempSelectedMembers.findIndex(m => m.id === member.id);
                    if (idx === -1) {
                        this.tempSelectedMembers.push(member);
                    } else {
                        this.tempSelectedMembers.splice(idx, 1);
                    }
                },

                isSelected(id) {
                    return this.tempSelectedMembers.some(m => m.id === id);
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
                    // 🔹 Terapkan pilihan dari temp ke selected
                    this.selectedMembers = [...this.tempSelectedMembers];
                    this.closeModal();
                },

                openMemberModal() {
                    // 🔹 Saat buka modal, isi temp dengan yang sudah ada (biar centang tetap)
                    this.tempSelectedMembers = [...this.selectedMembers];
                    this.showManageMembersModal = true;
                },

                closeModal() {
                    this.showManageMembersModal = false;
                    this.search = '';
                }
            }));
        });
    </script>

@endsection