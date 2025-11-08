@extends('layouts.app')

@section('title', 'Isi Pengumuman')

@section('content')
    @include('components.sweet-alert')
    @php
        \Carbon\Carbon::setLocale('id');
    @endphp

    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                                <img src="{{ Auth::user()->avatar_url }}" alt="Avatar"
                                    class="rounded-full w-10 h-10 object-cover object-center border border-gray-200 shadow-sm bg-gray-100">
                                <div>
                                    <h1 class="text-xl font-semibold text-black mb-1">
                                        {{ $pengumuman->title }}
                                    </h1>
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm text-black font-medium">
                                            {{ $pengumuman->creator->full_name ?? 'Tidak diketahui' }}
                                        </p>
                                        <h3 class="text-base font-semibold text-black mb-4">
                                            Komentar ({{ $commentCount }}) {{-- hanya komentar utama --}}
                                        </h3>
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
                                    <hr class="border-gray-300 mb-3">

                                    <button @click="open = false"
                                        class="flex items-center gap-3 w-full px-3 py-2 hover:bg-gray-100 rounded-lg transition">
                                        <img src="{{ asset('images/icons/Pencil.svg') }}" alt="Edit" class="w-6 h-6">
                                        <span class="text-gray-700 text-base">Edit</span>
                                    </button>

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

                        <div class="text-black text-[15px] leading-relaxed">
                            {!! $pengumuman->description !!}
                        </div>
                    </div>

                    <!-- Section Komentar -->
                    <div class="mt-6">
                        <h3 class="text-base font-semibold text-black mb-4">Komentar</h3>

                        <!-- Input Komentar Utama (placeholder -> CKEditor) -->
                        <div class="flex items-start gap-3 mb-6">
                            <img src="{{ Auth::user()->avatar_url }}" alt="Avatar"
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

                                                <div class="text-sm text-gray-700 mt-1" x-html="comment.content"></div>

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
                                                                        <div class="text-sm text-gray-700 mt-1"
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
                            <span>Pengumuman ini diterima oleh 3 anggota</span>
                            <div class="flex -space-x-2">
                                <img src="{{ asset('images/dk.jpg') }}" alt="Avatar"
                                    class="rounded-full w-8 h-8 border-2 border-white">
                                <img src="{{ asset('images/dk.jpg') }}" alt="Avatar"
                                    class="rounded-full w-8 h-8 border-2 border-white">
                                <img src="{{ asset('images/dk.jpg') }}" alt="Avatar"
                                    class="rounded-full w-8 h-8 border-2 border-white">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================= --}}
    {{-- SCRIPT: komentar (perbaikan) --}}
    {{-- ========================= --}}
    <script>
        const editors = {}; // key -> CKEditor instance

        // create editor, fallback to textarea when error (so get data always possible)
        async function createEditor(el) {
            try {
                const editor = await ClassicEditor.create(el);
                return editor;
            } catch (err) {
                console.warn('CKEditor create failed, fallback to textarea for', el.id, err);
                // fallback: create a textarea inside the element
                el.innerHTML =
                    `<textarea id="${el.id}-fallback" class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none"></textarea>`;
                return null;
            }
        }

        async function initMainEditor(id = 'main-editor') {
            const el = document.getElementById(id);
            if (!el) return;
            if (editors[id] !== undefined) return; // already attempted
            const inst = await createEditor(el);
            editors[id] = inst; // either editor or null (fallback created)
        }

        function destroyMainEditor(id = 'main-editor') {
            const inst = editors[id];
            if (inst) {
                inst.destroy().catch(() => { });
            }
            delete editors[id];
            const ta = document.getElementById(id + '-fallback');
            if (ta) ta.remove();
            // restore empty div for future init
            const el = document.getElementById(id);
            if (el) el.innerHTML = '';
        }

        async function initReplyEditorFor(commentId) {
            const id = `reply-editor-${commentId}`;
            const el = document.getElementById(id);
            if (!el) return;
            if (editors[`reply-${commentId}`] !== undefined) return;
            const inst = await createEditor(el);
            editors[`reply-${commentId}`] = inst;
        }

        function destroyReplyEditorFor(commentId) {
            const key = `reply-${commentId}`;
            const inst = editors[key];
            if (inst) {
                inst.destroy().catch(() => { });
            }
            delete editors[key];
            const ta = document.getElementById(`reply-editor-${commentId}-fallback`);
            if (ta) ta.remove();
            const el = document.getElementById(`reply-editor-${commentId}`);
            if (el) el.innerHTML = '';
        }

        function getEditorDataSafe(key) {
            // key is 'main-editor' or `reply-${id}`
            const inst = editors[key];
            if (inst) return inst.getData();
            // fallback: check element or fallback textarea
            if (key === 'main-editor') {
                return document.getElementById('main-editor-fallback')?.value || '';
            }
            // reply-xxx
            if (key.startsWith('reply-')) {
                const id = key.replace('reply-', '');
                return document.getElementById(`reply-editor-${id}-fallback`)?.value || '';
            }
            return '';
        }

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
                    // init main editor
                    await initMainEditor('main-editor');
                },

                async submitMain() {
                    // read data either from CKEditor instance or fallback textarea
                    const content = (editors['main-editor'] ? (editors['main-editor'].getData?.() ||
                        '') : (document.getElementById('main-editor-fallback')?.value ||
                            '')).trim();
                    if (!content) {
                        alert('Komentar tidak boleh kosong!');
                        return;
                    }

                    try {
                        const res = await fetch(`{{ route('comments.store') }}`, {
                            method: 'POST',
                            credentials: 'same-origin', // penting untuk mengirim session cookie (CSRF)
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                content,
                                commentable_id: "{{ $pengumuman->id }}",
                                commentable_type: "App\\Models\\Pengumuman"
                            })
                        });

                        // jika response 419/401, server menolak -> cek session/CSRF
                        if (!res.ok) {
                            const text = await res.text();
                            console.error('Server error status', res.status, text);
                            alert(
                                'Gagal mengirim komentar. Silakan refresh halaman dan coba lagi.');
                            return;
                        }

                        const data = await res.json();
                        if (data.success) {
                            this.comments.unshift(data.comment);
                            // reset editor (destroy & re-init) so content cleared
                            destroyMainEditor('main-editor');
                            await initMainEditor('main-editor');
                        } else {
                            console.error('Server returned success:false', data);
                            alert(data.message || 'Gagal menambahkan komentar.');
                        }
                    } catch (err) {
                        console.error('Gagal mengirim komentar:', err);
                        alert('Gagal mengirim komentar (network).');
                    }
                },

                async submitReplyFromEditor() {
                    if (!this.replyView.parentComment) return;
                    const parent = this.replyView.parentComment;
                    const key = `reply-${parent.id}`;
                    const content = (editors[key] ? (editors[key].getData?.() || '') : (document
                        .getElementById(`reply-editor-${parent.id}-fallback`)?.value || ''))
                        .trim();
                    if (!content) {
                        alert('Balasan tidak boleh kosong!');
                        return;
                    }

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
                                content,
                                commentable_id: "{{ $pengumuman->id }}",
                                commentable_type: "App\\Models\\Pengumuman",
                                parent_comment_id: parent.id
                            })
                        });

                        if (!res.ok) {
                            const text = await res.text();
                            console.error('Server error status', res.status, text);
                            alert('Gagal mengirim balasan. Silakan refresh halaman dan coba lagi.');
                            return;
                        }

                        const data = await res.json();
                        if (data.success) {
                            if (!parent.replies) parent.replies = [];
                            parent.replies.push(data.comment);
                            this.closeReplyView();
                        } else {
                            console.error('Server returned success:false for reply', data);
                            alert(data.message || 'Gagal menambahkan balasan.');
                        }
                    } catch (err) {
                        console.error('Gagal mengirim balasan:', err);
                        alert('Gagal mengirim balasan (network).');
                    }
                },

                toggleReply(comment) {
                    if (this.replyView.active && this.replyView.parentComment?.id === comment.id) {
                        this.closeReplyView();
                        return;
                    }
                    if (this.replyView.active && this.replyView.parentComment) {
                        destroyReplyEditorFor(this.replyView.parentComment.id);
                    }
                    this.replyView.active = true;
                    this.replyView.parentComment = comment;
                    // delay agar DOM template muncul
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

        // bridge: tombol Kirim di UI memanggil event ini
        window.addEventListener('submit-main-comment', () => {
            const root = document.querySelector('[x-data="commentSection"]');
            if (root && root.__x) {
                const data = root.__x.$data;
                if (typeof data.submitMain === 'function') data.submitMain();
            }
        });

        // cleanup on unload
        window.addEventListener('beforeunload', () => {
            Object.keys(editors).forEach(k => {
                try {
                    editors[k]?.destroy?.();
                } catch (e) { }
                delete editors[k];
            });
        });
    </script>

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
@endsection