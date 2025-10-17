{{-- Styles untuk halaman balas komentar --}}
<style>
    /* Styling untuk CKEditor di halaman balas komentar */
    .ck-editor__editable {
        min-height: 100px;
        max-height: 200px;
        overflow-y: auto;
    }

    .ck.ck-editor {
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem;
        min-height: 150px;
    }

    .ck.ck-content {
        min-height: 120px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Styling untuk fallback textarea */
    #reply-textarea-fallback {
        resize: vertical;
        min-height: 120px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        font-size: 14px;
        line-height: 1.6;
    }

    #reply-textarea-fallback:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .ck.ck-toolbar {
        border: none !important;
        border-bottom: 1px solid #e5e7eb !important;
        background: #f9fafb !important;
    }

    .ck.ck-editor__editable:not(.ck-editor__nested-editable) {
        border: none !important;
    }
</style>

{{-- Halaman Balas Komentar --}}
<div x-show="replyView.active" class="m-6 flex-shrink-0" x-init="$nextTick(() => {
    setTimeout(() => {
        if (typeof initReplyEditor === 'function') {
            initReplyEditor();
        }
    }, 300);
})">
    {{-- Breadcrumb Balas Komentar --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <button @click="closeReplyView(); if (typeof clearReplyEditor === 'function') clearReplyEditor();"
            class="text-gray-500 hover:text-gray-700 transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </button>
        <span class="text-gray-400">|</span>
        
        {{-- Konteks File --}}
        <template x-if="replyView.context === 'file'">
            <button @click="goToRoot()" class="text-gray-500 hover:text-gray-700 transition">
                Dokumen
            </button>
        </template>
        
        {{-- Konteks Tugas --}}
        <template x-if="replyView.context === 'task'">
            <button @click="closeReplyView()" class="text-gray-500 hover:text-gray-700 transition">
                Tugas
            </button>
        </template>

        {{-- Navigasi File --}}
        <template x-if="replyView.context === 'file' && replyView.currentFile">
            <div class="flex items-center gap-2">
                <span class="text-gray-400">›</span>
                <button @click="closeReplyView()" class="text-gray-500 hover:text-gray-700 transition"
                    x-text="replyView.currentFile.name"></button>
            </div>
        </template>

        {{-- Navigasi Tugas --}}
        <template x-if="replyView.context === 'task' && replyView.currentTask">
            <div class="flex items-center gap-2">
                <span class="text-gray-400">›</span>
                <button @click="closeReplyView()" class="text-gray-500 hover:text-gray-700 transition"
                    x-text="replyView.currentTask.title"></button>
            </div>
        </template>

        <div class="flex items-center gap-2">
            <span class="text-gray-400">›</span>
            <span class="text-gray-700 font-medium">Balas Komentar</span>
        </div>
    </div>

    {{-- Header Halaman Balas --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Membalas komentar <span
                x-text="replyView.parentComment?.author?.name"></span> 
            <template x-if="replyView.context === 'file'">pada berkas berikut ini</template>
            <template x-if="replyView.context === 'task'">pada tugas berikut ini</template>
        </h3>

        {{-- Info File/Tugas --}}
        <div class="mb-6">
            <template x-if="replyView.context === 'file'">
                <p class="text-sm font-semibold text-gray-800 mb-2" x-text="replyView.currentFile?.name"></p>
            </template>
            <template x-if="replyView.context === 'task'">
                <p class="text-sm font-semibold text-gray-800 mb-2" x-text="replyView.currentTask?.title"></p>
            </template>
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <span x-text="replyView.parentComment?.author?.name"></span>
                <span>-</span>
                <span x-text="formatCommentDate(replyView.parentComment?.createdAt)"></span>
            </div>
        </div>

        {{-- Komentar yang Dibalas --}}
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <img :src="replyView.parentComment?.author?.avatar"
                    :alt="replyView.parentComment?.author?.name" class="w-6 h-6 rounded-full">
                <p class="text-sm font-semibold text-gray-800"
                    x-text="replyView.parentComment?.author?.name"></p>
                <span class="text-xs text-gray-500"
                    x-text="formatCommentDate(replyView.parentComment?.createdAt)"></span>
            </div>
            <div class="text-sm text-gray-700" x-text="replyView.parentComment?.content"></div>
        </div>

        {{-- Form Balasan --}}
        <div class="mb-8">
            <label class="text-sm font-medium text-gray-700 mb-2 block">Komentar</label>
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <div id="reply-editor-main"></div>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
            <button
                @click="closeReplyView(); if (typeof clearReplyEditor === 'function') clearReplyEditor();"
                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition border border-gray-300 rounded-lg">
                Batal
            </button>
            <button @click="if (typeof submitReplyFromEditor === 'function') submitReplyFromEditor();"
                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                Kirim Balasan
            </button>
        </div>

        {{-- Daftar Balasan yang Sudah Ada --}}
        <div x-show="replyView.parentComment?.replies && replyView.parentComment.replies.length > 0">
            <h4 class="text-md font-semibold text-gray-800 mb-4">Balasan</h4>
            <div class="space-y-3">
                <template x-for="reply in replyView.parentComment.replies" :key="reply.id">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <img :src="reply.author.avatar" :alt="reply.author.name"
                                    class="w-5 h-5 rounded-full">
                                <p class="text-sm font-semibold text-gray-800" x-text="reply.author.name">
                                </p>
                            </div>
                            <span class="text-xs text-gray-500"
                                x-text="formatCommentDate(reply.createdAt)"></span>
                        </div>
                        <div class="text-sm text-gray-700" x-text="reply.content"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

{{-- Script untuk CKEditor Balasan --}}
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    let replyEditor = null;
    let isReplyEditorInitialized = false;

    // Inisialisasi CKEditor untuk form balasan
    function initReplyEditor() {
        console.log('🔄 Inisialisasi CKEditor untuk balasan...');
        
        // Hancurkan editor yang sudah ada jika ada
        if (replyEditor) {
            console.log('🗑️ Menghancurkan editor sebelumnya...');
            replyEditor.destroy().then(() => {
                console.log('✅ Editor sebelumnya berhasil dihancurkan');
            }).catch(error => {
                console.error('❌ Error menghancurkan editor:', error);
            });
            replyEditor = null;
        }

        const editorElement = document.getElementById('reply-editor-main');
        
        if (!editorElement) {
            console.error('❌ Element #reply-editor-main tidak ditemukan');
            return;
        }

        // Kosongkan element terlebih dahulu
        editorElement.innerHTML = '';

        // Tunggu sebentar untuk memastikan DOM sudah siap
        setTimeout(() => {
            ClassicEditor
                .create(editorElement, {
                    toolbar: {
                        items: [
                            'undo', 'redo', '|',
                            'heading', '|',
                            'bold', 'italic', 'underline', 'strikethrough', '|',
                            'fontColor', 'fontBackgroundColor', '|',
                            'link', 'blockQuote', 'code', '|',
                            'bulletedList', 'numberedList', 'outdent', 'indent', '|',
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
                            },
                            {
                                model: 'heading3',
                                view: 'h3',
                                title: 'Heading 3',
                                class: 'ck-heading_heading3'
                            }
                        ]
                    },
                    fontColor: {
                        colors: [{
                                color: 'black',
                                label: 'Hitam'
                            },
                            {
                                color: 'red',
                                label: 'Merah'
                            },
                            {
                                color: 'blue',
                                label: 'Biru'
                            },
                            {
                                color: 'green',
                                label: 'Hijau'
                            },
                            {
                                color: 'orange',
                                label: 'Oranye'
                            },
                            {
                                color: 'purple',
                                label: 'Ungu'
                            }
                        ]
                    },
                    fontBackgroundColor: {
                        colors: [{
                                color: 'yellow',
                                label: 'Kuning'
                            },
                            {
                                color: 'lightgreen',
                                label: 'Hijau Muda'
                            },
                            {
                                color: 'lightblue',
                                label: 'Biru Muda'
                            },
                            {
                                color: 'pink',
                                label: 'Merah Muda'
                            },
                            {
                                color: 'gray',
                                label: 'Abu-abu'
                            }
                        ]
                    },
                    image: {
                        toolbar: [
                            'imageTextAlternative',
                            'imageStyle:inline',
                            'imageStyle:block',
                            'imageStyle:side'
                        ]
                    },
                    table: {
                        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
                    },
                    mediaEmbed: {
                        previewsInData: true
                    },
                    placeholder: 'Ketik balasan Anda di sini...'
                })
                .then(editor => {
                    replyEditor = editor;
                    isReplyEditorInitialized = true;
                    console.log('✅ CKEditor untuk balasan berhasil diinisialisasi');

                    // Event listener untuk perubahan konten
                    editor.model.document.on('change:data', () => {
                        const content = editor.getData();
                        console.log('📝 Konten editor berubah:', content.substring(0, 50) + '...');
                        
                        // Update ke state Alpine.js
                        updateAlpineReplyContent(content);
                    });

                    // Focus ke editor
                    editor.editing.view.focus();
                    
                })
                .catch(error => {
                    console.error('❌ Error inisialisasi CKEditor balasan:', error);
                    fallbackToTextarea();
                });
        }, 100);
    }

    // Update content ke Alpine.js state
    function updateAlpineReplyContent(content) {
        const alpineElement = document.querySelector('[x-data]');
        if (alpineElement && alpineElement.__x) {
            // Cek konteks dan update ke state yang sesuai
            const alpineComponent = alpineElement.__x.$data;
            if (alpineComponent.replyView) {
                alpineComponent.replyView.replyContent = content;
            } else if (alpineComponent.replyContent !== undefined) {
                // Untuk konteks tugas yang menggunakan variabel terpisah
                alpineComponent.replyContent = content;
            }
        }
    }

    // Fallback ke textarea biasa jika CKEditor gagal
    function fallbackToTextarea() {
        console.log('🔄 Menggunakan fallback textarea...');
        const editorElement = document.getElementById('reply-editor-main');
        if (editorElement) {
            editorElement.innerHTML = `
                <textarea 
                    id="reply-textarea-fallback"
                    style="width: 100%; min-height: 200px; padding: 12px; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-family: sans-serif;"
                    placeholder="Ketik balasan Anda di sini..."
                    oninput="updateReplyContentFromTextarea(this.value)"
                ></textarea>
            `;
        }
    }

    // Update content dari textarea fallback
    function updateReplyContentFromTextarea(content) {
        updateAlpineReplyContent(content);
    }

    // Fungsi untuk mendapatkan konten dari editor
    function getReplyContent() {
        if (replyEditor && isReplyEditorInitialized) {
            return replyEditor.getData();
        }
        
        // Fallback untuk textarea
        const textarea = document.getElementById('reply-textarea-fallback');
        if (textarea) {
            return textarea.value;
        }
        
        return '';
    }

    // Fungsi untuk mengosongkan editor
    function clearReplyEditor() {
        if (replyEditor && isReplyEditorInitialized) {
            replyEditor.setData('');
        }
        
        // Fallback untuk textarea
        const textarea = document.getElementById('reply-textarea-fallback');
        if (textarea) {
            textarea.value = '';
        }
    }

    // Fungsi untuk submit balasan - Support untuk kedua konteks
    function submitReplyFromEditor() {
        const content = getReplyContent().trim();
        console.log('📤 Mengirim balasan:', content);
        
        if (!content) {
            alert('❌ Komentar tidak boleh kosong');
            return;
        }

        const alpineElement = document.querySelector('[x-data]');
        if (alpineElement && alpineElement.__x) {
            const alpineComponent = alpineElement.__x.$data;
            
            // Submit berdasarkan konteks
            if (alpineComponent.replyView && alpineComponent.replyView.context === 'task') {
                // Konteks Tugas
                if (typeof alpineComponent.submitReply === 'function') {
                    alpineComponent.submitReply();
                } else {
                    console.error('❌ Fungsi submitReply tidak ditemukan untuk tugas');
                }
            } else {
                // Konteks File (default)
                if (typeof alpineComponent.submitReply === 'function') {
                    alpineComponent.submitReply();
                } else {
                    console.error('❌ Fungsi submitReply tidak ditemukan');
                }
            }
            
            // Clear editor setelah submit
            clearReplyEditor();
        } else {
            console.error('❌ Alpine.js component tidak ditemukan');
        }
    }

    // Cleanup function
    function destroyReplyEditor() {
        if (replyEditor) {
            replyEditor.destroy().then(() => {
                console.log('✅ Reply editor destroyed');
                replyEditor = null;
                isReplyEditorInitialized = false;
            }).catch(error => {
                console.error('❌ Error destroying reply editor:', error);
            });
        }
    }

    // Event listener untuk Alpine.js
    document.addEventListener('alpine:init', () => {
        console.log('🎯 Alpine.js initialized');
    });

    // Inisialisasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📄 DOM Content Loaded');
        
        // Observer untuk mendeteksi ketika reply view dibuka
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'x-show') {
                    const replySection = document.querySelector('[x-show="replyView.active"]');
                    if (replySection && replySection.style.display !== 'none') {
                        console.log('🎯 Reply section opened, initializing editor...');
                        setTimeout(() => {
                            initReplyEditor();
                        }, 500);
                    }
                }
            });
        });

        // Mulai observe
        const alpineElement = document.querySelector('[x-data]');
        if (alpineElement) {
            observer.observe(alpineElement, {
                attributes: true,
                attributeFilter: ['x-show']
            });
        }
    });

    // Cleanup saat halaman ditutup
    window.addEventListener('beforeunload', () => {
        destroyReplyEditor();
    });
</script>