@extends('layouts.app')

@section('title', 'Detail Pengumuman')

@section('content')
    @include('components.sweet-alert')
    @php
        \Carbon\Carbon::setLocale('id');
        // Format waktu seperti di pengumuman umum
        $user = Auth::user();
        $userTimezone = $user->timezone ?? config('app.timezone');

        // Parse dulu baru set timezone
        $pengumuman->display_created_at = \Carbon\Carbon::parse($pengumuman->created_at)
            ->setTimezone($userTimezone)
            ->translatedFormat('d M Y H:i');

        $pengumuman->display_relative_time = \Carbon\Carbon::parse($pengumuman->created_at)
            ->setTimezone($userTimezone)
            ->diffForHumans();
    @endphp

    <!-- Font Inter & CKEditor -->
    <link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/41.3.1/classic/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <div class="bg-[#f3f6fc] min-h-screen font-[Inter,sans-serif] text-black" x-data="commentSection">
        <!-- Tombol Kembali -->
        <div class="max-w-5xl mx-auto px-4 py-6">
            <div class="mb-6">
                <button onclick="window.location.href='{{ route('workspace.pengumuman', ['workspace' => $workspace->id]) }}'"
                    class="flex items-center gap-2 text-blue-600 hover:text-blue-800 transition-colors group bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg">
                    <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="font-semibold">Kembali ke Pengumuman</span>
                </button>
            </div>

            <!-- Card Pengumuman (Mengikuti struktur pengumuman umum) -->
            <div class="bg-white rounded-xl shadow-sm border border-blue-100 overflow-hidden">
                @php
                    $creator = $pengumuman->creator ?? null;
                    $avatarPath = $creator && $creator->avatar ? 'storage/' . $creator->avatar : null;
                    $hasAvatarFile = $avatarPath && file_exists(public_path($avatarPath));
                    $avatarUrl = $hasAvatarFile
                        ? asset($avatarPath)
                        : ($creator && $creator->full_name
                            ? 'https://ui-avatars.com/api/?name=' .
                                urlencode($creator->full_name) .
                                '&background=random&color=fff'
                            : asset('images/dk.jpg'));
                @endphp

                <!-- Header Pengumuman (Mengikuti pengumuman umum) -->
                <div class="p-6 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-white">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4 flex-1">
                            <img src="{{ $avatarUrl }}" alt="Avatar"
                                class="rounded-full w-12 h-12 object-cover border-2 border-white shadow-sm flex-shrink-0">

                            <div class="flex-1 min-w-0">
                                <h1 class="text-2xl font-bold text-blue-900 mb-2">
                                    {{ $pengumuman->title }}
                                </h1>

                                <div class="flex items-center gap-3 flex-wrap">
                                    <span class="text-sm font-semibold text-blue-700">
                                        {{ $pengumuman->creator->full_name ?? 'Tidak diketahui' }}
                                    </span>
                                    <span class="text-blue-400">•</span>
                                    <span class="text-sm text-blue-600">
                                        {{ $pengumuman->display_created_at }}
                                        <span class="text-gray-500">({{ $pengumuman->display_relative_time }})</span>
                                    </span>

                                    @if ($pengumuman->due_date)
                                        <span
                                            class="inline-flex items-center gap-1 bg-blue-600 text-white text-xs font-medium px-2.5 py-1 rounded-md">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ \Carbon\Carbon::parse($pengumuman->due_date)->translatedFormat('d M Y') }}
                                        </span>
                                    @endif

                                    @if ($pengumuman->is_private)
                                        <span
                                            class="inline-flex items-center gap-1 bg-red-600 text-white text-xs font-medium px-2.5 py-1 rounded-md">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            Rahasia
                                        </span>
                                    @endif
                                </div>

                                <!-- Info Workspace -->
                                <div class="mt-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Ruang Kerja : {{ $workspace->name }}
                                </div>
                            </div>
                        </div>

                        <!-- Menu Aksi -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" @click.away="open = false"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                </svg>
                            </button>

                            <div x-show="open" x-transition.scale.origin.top.right
                                class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg p-2 z-50">
                                @if ($pengumuman->created_by == Auth::id())
                                    <button onclick="openEditModal('{{ $pengumuman->id }}')"
                                        class="flex items-center gap-3 w-full px-3 py-2 hover:bg-gray-50 rounded-lg transition text-left">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span class="text-gray-700 font-medium">Edit</span>
                                    </button>

                                    <hr class="my-1 border-gray-200">

                                    <button onclick="deletePengumuman('{{ $pengumuman->id }}')"
                                        class="flex items-center gap-3 w-full px-3 py-2 hover:bg-red-50 rounded-lg transition text-left">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span class="text-red-600 font-medium">Hapus</span>
                                    </button>
                                @else
                                    <div class="px-3 py-2 text-sm text-gray-500">
                                        Hanya pembuat yang bisa mengedit/menghapus
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Konten Deskripsi -->
                <div class="p-6 border-b border-blue-100 bg-blue-50/30">
                    <div class="prose max-w-none">
                        <div class="ck-content text-gray-700 text-[15px] leading-relaxed deskripsi-pengumuman">
                            {!! $pengumuman->description !!}
                        </div>
                    </div>
                </div>

                <!-- Section Komentar -->
                <div class="p-6 bg-white">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        Komentar
                        <span class="text-sm font-normal text-gray-500">
                            ({{ $commentCount }} komentar utama, {{ $allCommentCount }} total)
                        </span>
                    </h3>

                    <!-- Input Komentar Utama -->
                    <div class="flex items-start gap-3 mb-6">
                        @php
                            $currentUser = Auth::user();
                            $currentAvatarPath =
                                $currentUser && $currentUser->avatar ? 'storage/' . $currentUser->avatar : null;
                            $currentHasAvatarFile = $currentAvatarPath && file_exists(public_path($currentAvatarPath));
                            $currentAvatarUrl = $currentHasAvatarFile
                                ? asset($currentAvatarPath)
                                : ($currentUser && $currentUser->full_name
                                    ? 'https://ui-avatars.com/api/?name=' .
                                        urlencode($currentUser->full_name) .
                                        '&background=random&color=fff'
                                    : asset('images/dk.jpg'));
                        @endphp

                        <img src="{{ $currentAvatarUrl }}" alt="Avatar"
                            class="rounded-full w-10 h-10 object-cover border border-gray-200 shadow-sm flex-shrink-0">

                        <div class="flex-1" x-data="{ active: false }" x-cloak>
                            <template x-if="!active">
                                <input type="text" placeholder="Tambahkan komentar..."
                                    @focus="active = true; $nextTick(() => initMainEditor('main-editor'))"
                                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm bg-white hover:border-gray-300 transition cursor-text">
                            </template>

                            <template x-if="active">
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                                    <div id="main-editor" class="min-h-[120px] bg-white"></div>

                                    <div class="flex justify-end gap-2 mt-4">
                                        <button @click="active = false; destroyMainEditor('main-editor')"
                                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                            Batal
                                        </button>
                                        <button @click="submitMain(); active = false;"
                                            class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-sm">
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
                                        <img :src="comment.author.avatar || '{{ $currentAvatarUrl }}'"
                                            class="rounded-full w-10 h-10 object-cover border border-gray-200 shadow-sm flex-shrink-0">
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <p class="text-sm font-semibold text-gray-800"
                                                    x-text="comment.author.name"></p>
                                                <span class="text-xs text-gray-500"
                                                    x-text="formatCommentDate(comment.createdAt)"></span>
                                            </div>

                                            <div class="text-sm text-gray-700 mt-1 comment-text" x-html="comment.content">
                                            </div>

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
                                                                <img :src="reply.author.avatar || '{{ $currentAvatarUrl }}'"
                                                                    class="rounded-full w-6 h-6 object-cover border border-gray-200 shadow-sm">
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
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p class="text-gray-500 font-medium">Belum ada komentar</p>
                            <p class="text-sm text-gray-400 mt-1">Jadilah yang pertama berkomentar</p>
                        </div>
                    </template>
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
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
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

                // Tambahkan tombol upload file/image
                insertUploadFileButtonToToolbar(editor, commentId);
                insertUploadImageButtonToToolbar(editor, commentId);

                // Styling default editor content
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

            // Generate UUID baru untuk reply
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

        // =========================
        // Alpine.js component untuk KOMENTAR
        // =========================
        document.addEventListener('alpine:init', () => {
            Alpine.data('commentSection', () => ({
                comments: [],
                replyView: {
                    active: false,
                    parentComment: null
                },

                async init() {
                    const pengumumanId = "{{ $pengumuman->id }}";
                    try {
                        const res = await fetch(`/comments/${pengumumanId}`, {
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
                    await initMainEditor('main-editor');
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
                                commentable_id: "{{ $pengumuman->id }}",
                                commentable_type: "App\\Models\\Pengumuman"
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
                    if (this.replyView.active && this.replyView.parentComment) destroyReplyEditorFor(
                        this.replyView.parentComment.id);
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

        // =========================
        // Event & cleanup
        // =========================
        window.addEventListener('submit-main-comment', () => {
            const root = document.querySelector('[x-data="commentSection"]');
            if (root && root.__x) {
                const data = root.__x.$data;
                if (typeof data.submitMain === 'function') data.submitMain();
            }
        });

        window.addEventListener('beforeunload', () => {
            Object.keys(editors).forEach(k => {
                try {
                    editors[k]?.destroy?.();
                } catch (e) {}
                delete editors[k];
            });
        });

        // =========================
        // Fungsi insert tombol upload untuk KOMENTAR
        // =========================
        function insertUploadImageButtonToToolbar(editor, commentId) {
            const toolbarEl = editor.ui.view.toolbar.element;
            const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ck ck-button';
            btn.title = 'Upload Image';
            btn.innerHTML =
                `
            <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
                ${imageIconSVG()}
            </span>
        `;
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

                    // Kirim commentId yang sudah di-generate
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
            btn.type = 'button';
            btn.className = 'ck ck-button';
            btn.title = 'Upload File';
            btn.innerHTML =
                ` <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">${fileIconSVG()}</span>`;
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

                    // Kirim commentId yang sudah di-generate
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
            // Ikon SVG untuk tombol Upload File
            function fileIconSVG() {
                return `
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20" height="20">
                <path fill="currentColor" d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2 4 4H10V4z"/>
                </svg>`;
            }
        }
    </script>

    <!-- CSS dan JavaScript tambahan -->
    <style>
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

        #editCustomDeadline::-webkit-datetime-edit-text,
        #editCustomDeadline::-webkit-datetime-edit-month-field,
        #editCustomDeadline::-webkit-datetime-edit-day-field,
        #editCustomDeadline::-webkit-datetime-edit-year-field {
            color: white;
        }

        #editDateInputContainer:not(.hidden) {
            display: flex;
        }

        .ck-content a {
            color: #2563eb !important;
            text-decoration: underline;
            cursor: pointer;
        }

        .ck-content a:hover {
            color: #1d4ed8 !important;
        }

        .comment-text a {
            color: #2563eb !important;
            text-decoration: underline;
            cursor: pointer;
        }

        .comment-text a:hover {
            color: #1d4ed8 !important;
        }

        .deskripsi-pengumuman a {
            color: #2563eb;
            text-decoration: underline;
            font-weight: 500;
        }

        .deskripsi-pengumuman a:hover {
            color: #1d4ed8;
        }

        /* Responsif */
        @media (max-width: 640px) {
            .bg-white .p-6 h1 {
                font-size: 1.25rem;
            }

            .flex.items-start.gap-4 img {
                width: 2.5rem;
                height: 2.5rem;
            }
        }

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

    <!-- POPUP EDIT (VERSI DIPERBAIKI) -->
    <div id="openEditModal"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-lg p-6 w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-bold mb-4 text-[#102a63] border-b pb-2">Edit Pengumuman</h2>

            <form
                action="{{ route('pengumuman.update', ['workspace' => $workspace->id, 'pengumuman' => $pengumuman->id]) }}"
                method="POST" class="space-y-5" id="pengumumanEditForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="pengumuman_id" id="editPengumumanId">
                <input type="hidden" id="editWorkspaceId" name="workspace_id" value="{{ $workspace->id }}">

                <!-- Judul -->
                <div>
                    <label class="block text-sm font-inter font-semibold text-black mb-1">
                        Judul Pengumuman <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="editTitle" placeholder="Masukkan judul pengumuman..."
                        class="w-full border border-[#6B7280] rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600 font-[Inter] text-[14px] placeholder:text-[#6B7280] pl-5" />
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-sm font-inter font-semibold text-black mb-1">
                        Deskripsi <span class="text-red-500">*</span>
                    </label>
                    <div id="edit-catatan-editor" class="border border-[#6B7280] rounded-lg bg-white min-h-[200px] p-3">
                    </div>
                    <input type="hidden" name="description" id="editCatatanInput">
                </div>

                <!-- Tenggat -->
                <div>
                    <label class="block font-medium text-[14px] mb-2 text-black font-[Inter]">
                        Tenggat Pengumuman hingga selesai
                    </label>
                    <div class="flex items-center gap-3 mb-3 relative">
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

                        <span class="text-[14px] text-black font-[Inter]" id="editLabelText">Selesai otomatis pada:</span>

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

                <!-- Rahasia dan Penerima -->
                <div>
                    <label class="block text-sm font-inter font-semibold text-black mb-1">
                        Apakah pengumuman ini rahasia untuk penerima saja?
                    </label>

                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_private" value="0">
                            <input type="checkbox" name="is_private" id="editSwitchRahasia" class="sr-only"
                                value="1">
                            <div id="editSwitchBg"
                                class="relative w-11 h-6 bg-gray-300 rounded-full transition-colors duration-300">
                                <span id="editSwitchCircle"
                                    class="absolute top-[2px] left-[2px] w-[20px] h-[20px] bg-white rounded-full transition-transform duration-300"></span>
                            </div>
                            <span class="ml-2 text-[#102a63] font-medium">Rahasia</span>
                        </label>
                    </div>

                    <!-- Container untuk penerima (akan ditampilkan jika rahasia) -->
                    <div id="editRecipientsContainer" class="mt-4 hidden">
                        <div x-data="editPengumumanMembers">
                            <label class="block text-sm font-inter font-semibold text-black mb-2">
                                Penerima Pengumuman <span class="text-red-500">*</span>
                            </label>

                            <div class="flex items-center mb-3">
                                <div class="flex -space-x-2" id="editSelectedMembersAvatars">
                                    <template x-for="member in selectedMembers" :key="member.id">
                                        <div
                                            class="w-10 h-10 rounded-full border-2 border-white shadow-sm overflow-hidden">
                                            <img :src="member.avatar" :alt="member.name" :title="member.name"
                                                class="w-full h-full object-cover">
                                        </div>
                                    </template>
                                </div>

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
                                x-transition x-cloak>
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
                    </div>
                </div>

                <!-- Tombol -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" id="editBtnBatal"
                        class="border border-blue-700 text-blue-600 bg-white px-6 py-2 text-sm rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" id="editSubmitBtn"
                        class="bg-blue-700 text-white px-6 py-2 text-sm rounded-lg hover:bg-blue-800 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="editSubmitText">Perbarui</span>
                        <span id="editLoadingSpinner" class="hidden ml-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Script untuk dropdown tenggat waktu di modal edit
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
                    document.getElementById("editAutoDue").value =
                        value; // ✅ simpan ke input hidden
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

    <!-- JavaScript untuk edit modal -->
    <script>
        let editEditor = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi CKEditor untuk modal edit dengan toolbar LENGKAP
            ClassicEditor
                .create(document.querySelector('#edit-catatan-editor'), {
                    toolbar: {
                        items: [
                            'undo', 'redo', '|',
                            'heading', '|',
                            'bold', 'italic', 'underline', 'strikethrough', '|',
                            'link', 'blockQuote', '|',
                            'bulletedList', 'numberedList', '|',
                            'insertTable'
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
                    placeholder: 'Masukkan deskripsi pengumuman...'
                })
                .then(editor => {
                    editEditor = editor;

                    // Tambahkan tombol upload file dan image ke toolbar
                    insertUploadFileButtonToEditToolbar(editor);
                    insertUploadImageButtonToEditToolbar(editor);

                    // Styling default editor content
                    editor.editing.view.change(writer => {
                        writer.setStyle('font-family', 'Inter, sans-serif', editor.editing.view.document
                            .getRoot());
                        writer.setStyle('font-size', '14px', editor.editing.view.document.getRoot());
                        writer.setStyle('color', '#000000', editor.editing.view.document.getRoot());
                    });
                })
                .catch(error => {
                    console.error('Error initializing CKEditor:', error);
                });

            // ==========================================
            // TOGGLE SWITCH RAHASIA
            // ==========================================
            const editSwitchRahasia = document.getElementById('editSwitchRahasia');
            const editSwitchBg = document.getElementById('editSwitchBg');
            const editSwitchCircle = document.getElementById('editSwitchCircle');
            const editRecipientsContainer = document.getElementById('editRecipientsContainer');

            if (editSwitchRahasia) {
                editSwitchRahasia.addEventListener('change', function() {
                    if (this.checked) {
                        editSwitchBg.classList.remove('bg-gray-300');
                        editSwitchBg.classList.add('bg-blue-600');
                        editSwitchCircle.style.transform = 'translateX(20px)';
                        editRecipientsContainer.classList.remove('hidden');
                    } else {
                        editSwitchBg.classList.remove('bg-blue-600');
                        editSwitchBg.classList.add('bg-gray-300');
                        editSwitchCircle.style.transform = 'translateX(0px)';
                        editRecipientsContainer.classList.add('hidden');
                    }
                });
            }

            // ==========================================
            // DROPDOWN TENGGAT WAKTU - EDIT MODAL
            // ==========================================
            setupEditDeadlineDropdowns();
        });

        function insertUploadImageButtonToEditToolbar(editor) {
            try {
                const toolbarEl = editor.ui.view.toolbar.element;
                const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ck ck-button';
                btn.title = 'Upload Image';
                btn.innerHTML = `
            <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                    <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 11a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM5 19l4.5-6 3.5 4.5 2.5-3L19 19H5z"/>
                </svg>
            </span>
        `;
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
                            } else {
                                alert('Upload gagal.');
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Terjadi kesalahan upload image.');
                        }
                    }, {
                        once: true
                    });
                });

                itemsContainer.appendChild(btn);
            } catch (err) {
                console.error('Insert upload image button error:', err);
            }
        }

        // ==========================================
        // FUNGSI UPLOAD FILE BUTTON
        // ==========================================
        function insertUploadFileButtonToEditToolbar(editor) {
            try {
                const toolbarEl = editor.ui.view.toolbar.element;
                const itemsContainer = toolbarEl.querySelector('.ck-toolbar__items') || toolbarEl;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ck ck-button';
                btn.title = 'Upload File';
                btn.innerHTML = `
            <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20" height="20">
                    <path fill="currentColor" d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2 4 4H10V4z"/>
                </svg>
            </span>
        `;
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
                            } else {
                                alert('Upload file gagal.');
                            }
                        } catch (err) {
                            console.error(err);
                            alert('Terjadi kesalahan upload file.');
                        }
                    }, {
                        once: true
                    });
                });

                itemsContainer.appendChild(btn);
            } catch (err) {
                console.error('Insert upload file button error:', err);
            }
        }

        function setupEditDeadlineDropdowns() {
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

            // Function to position and toggle dropdown
            function toggleDropdown(dropdown, container) {
                const rect = container.getBoundingClientRect();
                dropdown.style.top = `${rect.bottom + window.scrollY + 5}px`;
                dropdown.style.left = `${rect.left + window.scrollX}px`;
                dropdown.classList.toggle("hidden");
            }

            // Event listeners for buttons
            if (btn1) {
                btn1.addEventListener("click", (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdown2.classList.add("hidden");
                    toggleDropdown(dropdown1, chipContainer1);
                });
            }

            if (btn2) {
                btn2.addEventListener("click", (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdown1.classList.add("hidden");
                    toggleDropdown(dropdown2, chipContainer2);
                });
            }

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
                        dateInputContainer.style.display = "flex";
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
                    const value = opt.getAttribute("data-value");
                    chipText2.textContent = value;
                    document.getElementById("editAutoDue").value = value;
                    dropdown2.classList.add("hidden");
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener("click", (e) => {
                const isClickInsideDropdown1 = dropdown1.contains(e.target) || chipContainer1?.contains(e.target);
                const isClickInsideDropdown2 = dropdown2.contains(e.target) || chipContainer2?.contains(e.target);

                if (!isClickInsideDropdown1) {
                    dropdown1.classList.add("hidden");
                }
                if (!isClickInsideDropdown2) {
                    dropdown2.classList.add("hidden");
                }
            });

            // Prevent dropdown from closing when clicking inside
            dropdown1.addEventListener("click", (e) => e.stopPropagation());
            dropdown2.addEventListener("click", (e) => e.stopPropagation());

            // PERBAIKAN: Date picker trigger - HAPUS tombol calendar ganda
            if (customDeadline) {
                // Biarkan browser native date picker bekerja
                customDeadline.addEventListener('click', function() {
                    if (this.showPicker) {
                        this.showPicker();
                    }
                });
            }
        }

        // ==========================================
        // FUNGSI OPEN EDIT MODAL
        // ==========================================
        function openEditModal(pengumumanId) {
            console.log('Opening edit modal for ID:', pengumumanId);

            // Reset modal terlebih dahulu
            resetEditModal();

            // Tampilkan modal
            const modal = document.getElementById('openEditModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Set pengumuman ID
            document.getElementById('editPengumumanId').value = pengumumanId;

            // Ambil data pengumuman
            const workspaceId = window.location.pathname.split('/')[2];
            fetch(`/workspace/${workspaceId}/pengumuman/${pengumumanId}/edit-data`)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil data');
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
                    populateEditForm(data);
                })
                .catch(error => {
                    console.error('Fetch error (edit-data):', error);
                    closeEditModal();
                    Swal.fire('Error', 'Gagal mengambil data pengumuman', 'error');
                });
        }

        // ==========================================
        // RESET MODAL EDIT
        // ==========================================
        function resetEditModal() {
            // Reset form fields
            document.getElementById('editTitle').value = '';

            // Reset CKEditor
            if (editEditor) {
                editEditor.setData('');
            }

            // Reset switch
            const switchInput = document.getElementById('editSwitchRahasia');
            const switchBg = document.getElementById('editSwitchBg');
            const switchCircle = document.getElementById('editSwitchCircle');
            const recipientsContainer = document.getElementById('editRecipientsContainer');

            if (switchInput) {
                switchInput.checked = false;
                switchBg.classList.remove('bg-blue-600');
                switchBg.classList.add('bg-gray-300');
                switchCircle.style.transform = 'translateX(0px)';
                recipientsContainer.classList.add('hidden');
            }

            // Reset loading state
            const submitBtn = document.getElementById('editSubmitBtn');
            const submitText = document.getElementById('editSubmitText');
            const loadingSpinner = document.getElementById('editLoadingSpinner');

            if (submitBtn) {
                submitBtn.disabled = false;
                submitText.classList.remove('hidden');
                loadingSpinner.classList.add('hidden');
            }
        }

        // ==========================================
        // POPULATE FORM DENGAN DATA
        // ==========================================
        function populateEditForm(data) {
            console.log('Populating form with data:', data);

            // Judul
            document.getElementById('editTitle').value = data.title || '';

            // Deskripsi
            if (editEditor) {
                editEditor.setData(data.description || '');
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
                const dateInputContainer = document.getElementById('editDateInputContainer');
                dateInputContainer.classList.remove('hidden');
                dateInputContainer.style.display = 'flex';
                document.getElementById('editCustomDeadline').value = data.due_date;
                document.getElementById('editAutoDue').value = '';
            }

            // Privacy setting
            const switchInput = document.getElementById('editSwitchRahasia');
            const switchBg = document.getElementById('editSwitchBg');
            const switchCircle = document.getElementById('editSwitchCircle');
            const recipientsContainer = document.getElementById('editRecipientsContainer');

            if (data.is_private) {
                switchInput.checked = true;
                switchBg.classList.remove('bg-gray-300');
                switchBg.classList.add('bg-blue-600');
                switchCircle.style.transform = 'translateX(20px)';
                recipientsContainer.classList.remove('hidden');

                // Load recipients data
                if (window.editMembersComponent && data.recipients_data) {
                    window.editMembersComponent.selectedMembers = data.recipients_data;
                    window.editMembersComponent.tempSelectedMembers = [...data.recipients_data];
                }
            }
        }

        // ==========================================
        // CLOSE EDIT MODAL
        // ==========================================
        function closeEditModal() {
            const modal = document.getElementById('openEditModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // FORM SUBMIT HANDLER
        // ==========================================
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('pengumumanEditForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const submitBtn = document.getElementById('editSubmitBtn');
                    const submitText = document.getElementById('editSubmitText');
                    const loadingSpinner = document.getElementById('editLoadingSpinner');

                    // Disable button dan show loading
                    submitBtn.disabled = true;
                    submitText.classList.add('hidden');
                    loadingSpinner.classList.remove('hidden');

                    const pengumumanId = document.getElementById('editPengumumanId').value;
                    const formData = new FormData(this);

                    // Tambahkan description dari CKEditor
                    if (editEditor) {
                        formData.set('description', editEditor.getData());
                    }

                    // Validasi form
                    const title = document.getElementById('editTitle').value.trim();
                    const description = editEditor ? editEditor.getData().trim() : '';
                    const isPrivate = document.getElementById('editSwitchRahasia').checked;

                    if (!title) {
                        Swal.fire('Kolom Belum Diisi', 'Judul pengumuman wajib diisi.', 'warning');
                        resetSubmitButton();
                        return;
                    }

                    if (!description || description === '<p><br></p>') {
                        Swal.fire('Kolom Belum Diisi', 'Deskripsi pengumuman wajib diisi.', 'warning');
                        resetSubmitButton();
                        return;
                    }

                    const workspaceId = window.location.pathname.split('/')[2];

                    // Kirim request
                    fetch(`/workspace/${workspaceId}/pengumuman/${pengumumanId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                'X-HTTP-Method-Override': 'PUT'
                            },
                            body: formData
                        })
                        .then(async response => {
                            const text = await response.text();
                            try {
                                return JSON.parse(text);
                            } catch {
                                throw new Error('Server tidak mengembalikan JSON');
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
                                Swal.fire('Error', data.message || 'Terjadi kesalahan', 'error');
                                resetSubmitButton();
                            }
                        })
                        .catch(error => {
                            console.error('Submit error:', error);
                            Swal.fire('Error', 'Terjadi kesalahan saat memperbarui pengumuman',
                                'error');
                            resetSubmitButton();
                        });

                    function resetSubmitButton() {
                        submitBtn.disabled = false;
                        submitText.classList.remove('hidden');
                        loadingSpinner.classList.add('hidden');
                    }
                });
            }
        });

        // ==========================================
        // ALPINE.JS - EDIT MEMBERS COMPONENT
        // ==========================================
        document.addEventListener('alpine:init', () => {
            Alpine.data('editPengumumanMembers', () => ({
                showManageMembersModal: false,
                members: [],
                tempSelectedMembers: [],
                selectedMembers: [],
                search: '',

                init() {
                    console.log('🎯 Edit members component initialized');
                    this.loadMembers();
                    window.editMembersComponent = this;
                },

                async loadMembers() {
                    try {
                        const workspaceId = document.getElementById('editWorkspaceId')?.value;
                        if (!workspaceId) return;

                        const res = await fetch(
                            `/workspace/${workspaceId}/pengumuman/anggota/${workspaceId}`);
                        if (!res.ok) throw new Error('Gagal mengambil data anggota');

                        const data = await res.json();
                        this.members = Array.isArray(data) ? data : [];
                    } catch (err) {
                        console.error('Gagal memuat anggota:', err);
                        this.members = [];
                    }
                },

                toggleMember(member) {
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
                        m.name?.toLowerCase().includes(term) ||
                        m.email?.toLowerCase().includes(term)
                    );
                },

                applyMembers() {
                    this.selectedMembers = [...this.tempSelectedMembers];
                    this.closeModal();
                },

                openMemberModal() {
                    this.tempSelectedMembers = [...this.selectedMembers];
                    this.showManageMembersModal = true;
                    this.search = '';
                },

                closeModal() {
                    this.showManageMembersModal = false;
                    this.search = '';
                }
            }));
        });

        // ==========================================
        // EVENT LISTENER UNTUK TOMBOL BATAL
        // ==========================================
        document.addEventListener('DOMContentLoaded', function() {
            const btnBatal = document.getElementById('editBtnBatal');
            const btnBatalHeader = document.querySelector('#openEditModal .flex.items-center button');

            if (btnBatal) {
                btnBatal.addEventListener('click', closeEditModal);
            }

            if (btnBatalHeader) {
                btnBatalHeader.addEventListener('click', closeEditModal);
            }
        });

        // Fungsi delete
        function deletePengumuman(id) {
            const workspaceId = window.location.pathname.split('/')[2];
            Swal.fire({
                title: 'Hapus pengumuman?',
                text: 'Semua file & gambar terkait juga akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/workspace/${workspaceId}/pengumuman/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-HTTP-Method-Override': 'DELETE'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = `/workspace/${workspaceId}/pengumuman`;
                                });
                            } else {
                                Swal.fire('Gagal', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'Terjadi kesalahan saat menghapus pengumuman', 'error');
                        });
                }
            });
        }
    </script>
@endsection
