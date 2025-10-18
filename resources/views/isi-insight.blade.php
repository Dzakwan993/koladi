@extends('layouts.app')

@section('title', 'Isi Pengumuman')

@section('content')
    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js & CKEditor -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <div class="bg-[#e9effd] min-h-screen font-[Inter,sans-serif] text-black relative" x-data="commentSection">
        @include('components.workspace-nav')

        <div class="justify-center max-w-7xl pt-4 sm:pt-6 mx-4 sm:mx-6 md:mx-12 lg:mx-16 xl:mx-24 pb-6">
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-md p-4 sm:p-5 md:p-6">

                
                <!-- Card Pengumuman/Insight -->
                <div class="bg-[#dde5f4] rounded-lg sm:rounded-xl shadow-sm p-4 sm:p-5 mb-4 sm:mb-5 md:mb-6 transition">
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-3 sm:gap-0">
                        <!-- Konten Kiri -->
                        <div class="flex flex-col gap-2 sm:gap-3 w-full sm:w-auto">
                            <div>
                                <p class="text-sm sm:text-base text-[#102A63] font-semibold mb-0.5 sm:mb-1">Insight 2 orang tiap Senin pada 09.00 AM</p>
                                <p class="text-[#102A63] font-bold text-lg sm:text-xl">Lapor Progres!</p>
                            </div>
                            <div class="flex items-center">
                                <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-8 h-8 sm:w-9 sm:h-9">
                                <img src="https://i.pravatar.cc/40?img=8" alt="Avatar" class="rounded-full w-8 h-8 sm:w-9 sm:h-9 -ml-2 sm:-ml-3">
                            </div>
                        </div>

                        <!-- Konten Kanan: Titik 3 -->
                        <div class="flex items-center gap-3 self-start sm:self-auto">
                            <!-- Tombol Titik 3 -->
                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                <button @click="open = !open" @click.away="open = false"
                                    class="text-gray-600 hover:text-gray-800 focus:outline-none">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </button>

                                <!-- Popup menu -->
                                <div x-show="open" x-transition.scale.origin.top.right
                                    class="absolute right-0 mt-2 w-44 sm:w-48 bg-white border border-gray-200 rounded-xl sm:rounded-2xl shadow-xl p-3 sm:p-4 z-50"
                                    @click.away="open = false">

                                    <!-- Judul -->
                                    <h3 class="text-center font-semibold text-gray-800 mb-2 text-sm sm:text-base">Aksi</h3>
                                    <hr class="border-gray-300 mb-2 sm:mb-3">

                                    <!-- Tombol Edit -->
                                    <button @click="open = false; editComment()"
                                        class="flex items-center gap-2 sm:gap-3 w-full px-2 sm:px-3 py-1.5 sm:py-2 hover:bg-gray-100 rounded-lg transition">
                                        <img src="images/icons/Pencil.svg" alt="Edit" class="w-5 h-5 sm:w-6 sm:h-6">
                                        <span class="text-gray-700 text-sm sm:text-base font-medium">Edit</span>
                                    </button>

                                    <hr class="border-gray-300 my-2">

                                    <!-- Tombol Hapus -->
                                    <button @click="open = false; deleteComment()"
                                        class="flex items-center gap-2 sm:gap-3 w-full px-2 sm:px-3 py-1.5 sm:py-2 hover:bg-gray-100 rounded-lg transition">
                                        <img src="images/icons/Trash.svg" alt="Hapus" class="w-5 h-5 sm:w-6 sm:h-6">
                                        <span class="text-gray-700 text-sm sm:text-base font-medium">Hapus</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Komentar -->
                <div class="mt-4 sm:mt-5 md:mt-6">
                    <h3 class="text-base sm:text-lg font-bold text-black mb-3 sm:mb-4">Komentar</h3>

                    <!-- Input Komentar Utama (placeholder -> CKEditor) -->
                    <div class="flex items-start gap-2 sm:gap-3 mb-4 sm:mb-5 md:mb-6">
                        <img src="https://i.pravatar.cc/40?img=6" alt="Avatar" class="rounded-full w-9 h-9 sm:w-10 sm:h-10">

                        <!-- gunakan x-data lokal hanya untuk toggle active -->
                        <div class="flex-1" x-data="{ active: false }" x-cloak>
                            <template x-if="!active">
                                <input type="text" placeholder="Tambahkan komentar baru..."
                                    @focus="active = true; $nextTick(() => initMainEditor('main-editor'))"
                                    class="w-full px-3 sm:px-4 py-2 sm:py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:border-[#102a63] text-xs sm:text-sm font-medium bg-white cursor-text">
                            </template>

                            <template x-if="active">
                                <div class="bg-white border border-gray-300 rounded-lg p-3 sm:p-4 shadow-sm">
                                    <div id="main-editor" class="min-h-[120px] sm:min-h-[140px] bg-white"></div>

                                    <div class="flex justify-end gap-2 mt-3 sm:mt-4">
                                        <button @click="active = false; destroyMainEditor('main-editor')"
                                            class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-gray-600 border border-gray-300 rounded-lg hover:text-gray-800 transition">
                                            Batal
                                        </button>
                                        <!-- panggil Alpine method submitMain yg ada di component -->
                                        <button @click="$dispatch('submit-main-comment'); active = false;"
                                            class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                            Kirim
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Daftar Komentar -->
                    <template x-if="comments.length > 0">
                        <div class="space-y-3 sm:space-y-4">
                            <template x-for="(comment, index) in comments" :key="comment.id">
                                <div>
                                    <!-- Label Tanggal/Hari -->
                                    <template x-if="shouldShowDateLabel(index)">
                                        <div class="text-center py-2 mb-3 sm:mb-4">
                                            <span class="text-xs sm:text-sm text-gray-500 font-semibold" x-text="getDateLabel(comment.createdAt)"></span>
                                        </div>
                                    </template>
                                    
                                    <!-- Card Komentar -->
                                    <div class="bg-[#f8f9fa] rounded-lg sm:rounded-xl p-3 sm:p-4 border border-gray-200 shadow-sm">
                                        <div class="flex items-start gap-2 sm:gap-3">
                                            <img :src="comment.author.avatar" alt="" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex justify-between items-center gap-2">
                                                    <p class="text-sm sm:text-base font-bold text-gray-900 truncate" x-text="comment.author.name">
                                                    </p>
                                                    <span class="text-xs sm:text-sm text-gray-500 font-medium whitespace-nowrap"
                                                        x-text="formatCommentDate(comment.createdAt)"></span>
                                                </div>

                                                <div class="text-sm sm:text-base text-gray-700 mt-1 sm:mt-1.5 font-medium break-words" x-html="comment.content"></div>

                                                <!-- Tombol Balas -->
                                                <div class="flex items-center gap-3 sm:gap-4 mt-2">
                                                    <button @click="toggleReply(comment)"
                                                        class="flex items-center gap-1 text-xs sm:text-sm text-gray-600 hover:text-blue-600 transition font-semibold">
                                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                        </svg>
                                                        <span>balas</span>
                                                    </button>
                                                </div>

                                                <!-- FORM BALAS (inline, hanya 1 instance active pada satu waktu) -->
                                                <template x-if="replyView.active && replyView.parentComment?.id === comment.id">
                                                    <div class="mt-3 sm:mt-4 pl-4 sm:pl-6 border-l-2 border-gray-300">
                                                        <div class="bg-white rounded-lg p-3 sm:p-4 border border-gray-200 shadow-sm">
                                                            <h4 class="text-sm sm:text-base font-bold text-gray-800 mb-2">Membalas <span
                                                                    x-text="comment.author.name"></span></h4>

                                                            <div class="border border-gray-300 rounded-lg overflow-hidden mb-2 sm:mb-3">
                                                                <!-- container unik untuk reply editor -->
                                                                <div :id="'reply-editor-' + comment.id"
                                                                    class="min-h-[100px] sm:min-h-[120px] p-2 sm:p-3 bg-white"></div>
                                                            </div>

                                                            <div class="flex justify-end gap-2">
                                                                <button @click="closeReplyView()"
                                                                    class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold text-gray-600 hover:text-gray-800 transition border border-gray-300 rounded-lg">Batal</button>
                                                                <button @click="submitReplyFromEditor()"
                                                                    class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">Kirim</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>

                                                <!-- Balasan -->
                                                <template x-if="comment.replies && comment.replies.length > 0">
                                                    <div class="mt-3 pl-4 sm:pl-6 border-l-2 border-gray-300 space-y-2 sm:space-y-3">
                                                        <template x-for="reply in comment.replies" :key="reply.id">
                                                            <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                                                                <div class="flex items-start gap-2">
                                                                    <img :src="reply.author.avatar"
                                                                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-full">
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="flex items-center gap-2">
                                                                            <p class="text-sm sm:text-base font-bold text-gray-900 truncate"
                                                                                x-text="reply.author.name"></p>
                                                                            <span class="text-xs sm:text-sm text-gray-500 font-medium whitespace-nowrap"
                                                                                x-text="formatCommentDate(reply.createdAt)"></span>
                                                                        </div>
                                                                        <div class="text-sm sm:text-base text-gray-700 mt-1 font-medium break-words"
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
                                </div>
                            </template>
                        </div>
                    </template>

                    <template x-if="comments.length === 0">
                        <div class="text-center py-6 sm:py-8 text-gray-500 text-xs sm:text-sm font-medium">Belum ada komentar disini...</div>
                    </template>

                    

                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPT: CKEditor & Alpine --}}
    <script>
        /* -------------------------
               Helper CKEditor functions
               - all functions defined BEFORE alpine:init
               - editor instances managed by id
            ------------------------- */

        const editors = {}; // map id -> editor instance

        // create editor in containerId (string)
        async function createEditorFor(containerId, options = {}) {
            const el = document.getElementById(containerId);
            if (!el) {
                console.warn('createEditorFor: element not found', containerId);
                return null;
            }

            // clear existing content to avoid duplicates
            el.innerHTML = '';

            // default toolbar (safe — avoids plugins that might not exist in CDN build)
            const baseConfig = {
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
                placeholder: options.placeholder || ''
            };

            // try to create editor, fallback to textarea on error
            try {
                const editor = await ClassicEditor.create(el, baseConfig);
                editors[containerId] = editor;

                // safe: focus editor when created
                try {
                    editor.editing.view.focus();
                } catch (e) {}

                // wire change event for debug (and to keep Alpine in sync via dispatch)
                editor.model.document.on('change:data', () => {
                    const data = editor.getData();
                    // dispatch a custom event so Alpine can listen if needed
                    const ev = new CustomEvent('editor-change', {
                        detail: {
                            id: containerId,
                            data
                        }
                    });
                    window.dispatchEvent(ev);
                });

                return editor;
            } catch (err) {
                console.error('createEditorFor error for', containerId, err);
                // fallback to textarea
                el.innerHTML =
                    `<textarea id="${containerId}-fallback" class="w-full min-h-[140px] p-3 border border-gray-300 rounded-lg bg-white resize-none">${options.initial || ''}</textarea>`;
                return null;
            }
        }

        function destroyEditorFor(containerId) {
            const ed = editors[containerId];
            if (ed) {
                ed.destroy().then(() => {
                    delete editors[containerId];
                }).catch((e) => {
                    console.warn('destroyEditorFor error', containerId, e);
                    delete editors[containerId];
                });
            } else {
                // remove fallback textarea if existed
                const ta = document.getElementById(containerId + '-fallback');
                if (ta) ta.remove();
            }
        }

        function getEditorData(containerId) {
            const ed = editors[containerId];
            if (ed) return ed.getData();
            const ta = document.getElementById(containerId + '-fallback');
            return ta ? ta.value : '';
        }

        // helper to init main (top) editor
        function initMainEditor(containerId = 'main-editor') {
            return createEditorFor(containerId, {
                placeholder: 'Ketik komentar Anda di sini...'
            });
        }

        function destroyMainEditor(containerId = 'main-editor') {
            destroyEditorFor(containerId);
        }

        // helper to init reply editor for a specific comment id
        function initReplyEditorFor(commentId) {
            const containerId = 'reply-editor-' + commentId;
            return createEditorFor(containerId, {
                placeholder: 'Ketik balasan Anda di sini...'
            });
        }

        function destroyReplyEditorFor(commentId) {
            const containerId = 'reply-editor-' + commentId;
            destroyEditorFor(containerId);
        }

        function getReplyEditorDataFor(commentId) {
            return getEditorData('reply-editor-' + commentId);
        }

        // -------------------------
        // Alpine data (commentSection)
        // -------------------------
        document.addEventListener('alpine:init', () => {
            Alpine.data('commentSection', () => ({
                comments: [
                    // contoh awal
                    {
                        id: 1,
                        author: {
                            name: 'Aulia',
                            avatar: 'https://i.pravatar.cc/40?img=6'
                        },
                        content: 'Terima kasih informasinya, saya akan cek segera.',
                        createdAt: new Date(Date.now() - (1000 * 60 * 60 * 2)).toISOString(),
                        replies: [{
                            id: 11,
                            author: {
                                name: 'Budi',
                                avatar: 'https://i.pravatar.cc/40?img=6'
                            },
                            content: 'Ditunggu konfirmasinya ya.',
                            createdAt: new Date(Date.now() - (1000 * 60 * 30)).toISOString()
                        }]
                    },
                    {
                        id: 2,
                        author: {
                            name: 'Citra',
                            avatar: 'https://i.pravatar.cc/40?img=6'
                        },
                        content: 'Apakah ada perubahan jadwal terkait pengumuman ini?',
                        createdAt: new Date(Date.now() - (1000 * 60 * 60 * 24)).toISOString(),
                        replies: []
                    },
                    {
                        id: 3,
                        author: {
                            name: 'Dzakwan',
                            avatar: 'https://i.pravatar.cc/40?img=6'
                        },
                        content: 'Sudah dishare ke tim, mohon dicek semua.',
                        createdAt: new Date(Date.now() - (1000 * 60 * 60 * 48)).toISOString(),
                        replies: [{
                            id: 31,
                            author: {
                                name: 'Irfan',
                                avatar: 'https://i.pravatar.cc/40?img=6'
                            },
                            content: 'Siap, terima kasih.',
                            createdAt: new Date(Date.now() - (1000 * 60 * 60 * 10))
                                .toISOString()
                        }]
                    }
                ],
                // replyView untuk inline reply form
                replyView: {
                    active: false,
                    parentComment: null
                },

                /* toggle reply inline */
                toggleReply(comment) {
                    if (this.replyView.active && this.replyView.parentComment?.id === comment.id) {
                        this.closeReplyView();
                        return;
                    }
                    // close any previous reply editor
                    if (this.replyView.active && this.replyView.parentComment) {
                        destroyReplyEditorFor(this.replyView.parentComment.id);
                    }
                    this.replyView.active = true;
                    this.replyView.parentComment = comment;

                    // give DOM time to render the template, kemudian inisialisasi editor untuk that comment
                    setTimeout(() => {
                        initReplyEditorFor(comment.id);
                    }, 150);
                },

                closeReplyView() {
                    if (this.replyView.parentComment) {
                        destroyReplyEditorFor(this.replyView.parentComment.id);
                    }
                    this.replyView.active = false;
                    this.replyView.parentComment = null;
                },

                /* submit reply dari editor inline */
                submitReplyFromEditor() {
                    if (!this.replyView.parentComment) {
                        alert('Komentar induk tidak ditemukan');
                        return;
                    }
                    const parentId = this.replyView.parentComment.id;
                    const content = getReplyEditorDataFor(parentId).trim();
                    if (!content) {
                        alert('Komentar balasan tidak boleh kosong!');
                        return;
                    }

                    const newReply = {
                        id: Date.now(),
                        author: {
                            name: 'Anda',
                            avatar: '{{ asset('images/dk.jpg') }}'
                        },
                        content,
                        createdAt: new Date().toISOString()
                    };

                    // push ke parent comment
                    if (!this.replyView.parentComment.replies) this.replyView.parentComment.replies = [];
                    this.replyView.parentComment.replies.push(newReply);

                    // tutup & destroy editor
                    this.closeReplyView();
                },

                /* submit main (top) comment */
                submitMain() {
                    const content = getEditorData('main-editor').trim();
                    if (!content) {
                        alert('Komentar tidak boleh kosong!');
                        return;
                    }

                    this.comments.unshift({
                        id: Date.now(),
                        author: {
                            name: 'Anda',
                            avatar: '{{ asset('images/dk.jpg') }}'
                        },
                        content,
                        createdAt: new Date().toISOString(),
                        replies: []
                    });

                    // destroy main editor content
                    destroyMainEditor('main-editor');
                },

                /* helper tanggal */
                formatCommentDate(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    const now = new Date();
                    const diffMs = Math.abs(now - date);
                    const diffMinutes = Math.floor(diffMs / (1000 * 60));
                    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
                    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

                    if (diffMinutes < 1) return 'beberapa detik yang lalu';
                    if (diffMinutes < 60) return `${diffMinutes} menit yang lalu`;
                    if (diffHours < 24) return `${diffHours} jam yang lalu`;
                    if (diffDays < 7) return `${diffDays} hari yang lalu`;

                    return date.toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric'
                    });
                },

                /* helper untuk label tanggal separator */
                getDateLabel(dateString) {
                    if (!dateString) return '';
                    const date = new Date(dateString);
                    const today = new Date();
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    
                    // Reset jam ke 00:00 untuk perbandingan tanggal saja
                    const commentDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                    const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                    const yesterdayDate = new Date(yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate());
                    
                    if (commentDate.getTime() === todayDate.getTime()) {
                        return 'Hari Ini';
                    } else if (commentDate.getTime() === yesterdayDate.getTime()) {
                        return 'Kemarin';
                    } else {
                        return date.toLocaleDateString('id-ID', {
                            weekday: 'long',
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                },

                /* cek apakah perlu tampilkan label tanggal */
                shouldShowDateLabel(index) {
                    if (index === 0) return true; // selalu tampilkan untuk komentar pertama
                    
                    const currentComment = this.comments[index];
                    const previousComment = this.comments[index - 1];
                    
                    if (!currentComment?.createdAt || !previousComment?.createdAt) return false;
                    
                    const currentDate = new Date(currentComment.createdAt);
                    const previousDate = new Date(previousComment.createdAt);
                    
                    // Bandingkan hanya tanggal (ignore waktu)
                    const currentDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
                    const previousDay = new Date(previousDate.getFullYear(), previousDate.getMonth(), previousDate.getDate());
                    
                    return currentDay.getTime() !== previousDay.getTime();
                }
            }));
        });

        /* Event bridge: saat user klik tombol Kirim (top), kita terima dispatched event dan panggil alpine method */
        window.addEventListener('submit-main-comment', () => {
            // cari root alpine component
            const root = document.querySelector('[x-data="commentSection"]');
            if (root && root.__x) {
                const data = root.__x.$data;
                if (typeof data.submitMain === 'function') data.submitMain();
            }
        });

        // destroy all editors saat unload
        window.addEventListener('beforeunload', () => {
            // destroy known editor containers (main + any reply fallback ids)
            destroyMainEditor('main-editor');
            // best-effort: destroy any editors in editors map
            Object.keys(editors).forEach(id => destroyEditorFor(id));
        });
    </script>

@endsection