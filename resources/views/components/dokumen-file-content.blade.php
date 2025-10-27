{{-- Konten File dan Komentar --}}
<div x-show="currentFile && !replyView.active" class="mt-4 sm:mt-6">
    {{-- Preview File --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Preview File</h3>

        {{-- Preview berdasarkan tipe file --}}
        <template x-if="currentFile.type === 'PDF'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 sm:p-6 md:p-8 text-center">
                <img src="{{ asset('images/icons/pdf.svg') }}" alt="PDF" class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 mx-auto mb-3 sm:mb-4">
                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>
                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download PDF
                </button>
            </div>
        </template>

        <template x-if="currentFile.type === 'Word'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/microsoft-word.svg') }}" alt="Word" class="w-16 h-16 mx-auto mb-4">
                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>
                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download Document
                </button>
            </div>
        </template>

        <template x-if="currentFile.type === 'Excel'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/excel.svg') }}" alt="Excel" class="w-16 h-16 mx-auto mb-4">
                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>
                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download Spreadsheet
                </button>
            </div>
        </template>
    </div>

    {{-- Komentar Section --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Komentar</h3>

        <!-- Tambah Komentar -->
        <div class="mb-6">
            <label class="text-sm font-medium text-gray-700 mb-2 block">Tulis Komentar</label>
            <div class="border rounded-lg overflow-hidden">
                <textarea id="editor-komentar" name="komentar"></textarea>
            </div>
            <div class="flex justify-end gap-2 mt-2">
                <button type="button" @click="clearCommentEditor()"
                    class="mt-3 px-4 py-2 text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 text-sm rounded-lg">
                    Batal
                </button>
                <button type="button" @click="submitComment()"
                    class="mt-3 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">
                    Kirim
                </button>
            </div>
        </div>

        <!-- CKEditor Script -->
        <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
        <script>
            let commentEditor;

            document.addEventListener("DOMContentLoaded", () => {
                ClassicEditor
                    .create(document.querySelector('#editor-komentar'), {
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
                        }
                    })
                    .then(editor => {
                        commentEditor = editor;
                        console.log('CKEditor siap dipakai untuk komentar:', editor);
                    })
                    .catch(error => console.error(error));
            });

            // Fungsi untuk Alpine.js
            function clearCommentEditor() {
                if (commentEditor) {
                    commentEditor.setData('');
                }
            }

            function submitComment() {
                if (commentEditor) {
                    const content = commentEditor.getData();
                    if (content.trim()) {
                        // Panggil fungsi addComment dari Alpine.js
                        const alpineComponent = document.querySelector('[x-data]').__x.$data;
                        alpineComponent.addComment(alpineComponent.currentFile, content);
                        commentEditor.setData('');
                    }
                }
            }
        </script>

        {{-- Daftar Komentar --}}
        <div class="space-y-4">
            <template x-for="comment in currentFile.comments" :key="comment.id">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <img :src="comment.author.avatar" :alt="comment.author.name" class="w-6 h-6 rounded-full">
                            <p class="text-sm font-semibold text-gray-800" x-text="comment.author.name"></p>
                        </div>
                        <span class="text-xs text-gray-500" x-text="formatCommentDate(comment.createdAt)"></span>
                    </div>

                    {{-- Konten Komentar dengan HTML --}}
                    <div class="text-sm text-gray-700 prose prose-sm max-w-none mb-2" x-html="comment.content"></div>

                    {{-- Tombol Balas dan Jumlah Balasan --}}
                    <div class="flex items-center gap-4 mt-2">
                        <button @click="openReplyView(comment)"
                            class="flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            <span>balas</span>
                        </button>

                        {{-- Tampilkan jumlah balasan jika ada --}}
                        <template x-if="comment.replies && comment.replies.length > 0">
                            <span class="text-xs text-gray-500" x-text="comment.replies.length + ' balasan'"></span>
                        </template>
                    </div>
                </div>
            </template>

            {{-- Empty State Komentar --}}
            <div x-show="!currentFile.comments || currentFile.comments.length === 0" class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="text-sm">Belum ada komentar</p>
                <p class="text-xs">Jadilah yang pertama berkomentar</p>
            </div>
        </div>
    </div>
</div>