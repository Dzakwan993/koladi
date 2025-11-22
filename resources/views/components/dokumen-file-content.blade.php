{{-- Konten File dan Komentar --}}
<div x-show="currentFile && !replyView.active  && isLoadingPermission === false" class="mt-4 sm:mt-6">
    {{-- Preview File --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Preview File</h3>

        {{-- Preview berdasarkan tipe file --}}
        {{-- Jenis File PDF --}}
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

        {{-- Jenis File Word --}}
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

        {{-- Jenis File Excel --}}
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

        {{-- Jenis File Powerpoint --}}
        <template x-if="currentFile.type === 'PowerPoint'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/powerpoint.svg') }}" alt="PowerPoint" class="w-16 h-16 mx-auto mb-4">
                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>

                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download PowerPoint
                </button>
            </div>
        </template>

        {{-- Jenis File Text --}}
        <template x-if="currentFile.type === 'Text'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/text-file.svg') }}" alt="Text" class="w-16 h-16 mx-auto mb-4">
                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>

                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download Text File
                </button>
            </div>
        </template>

        {{-- Jenis File Gambar --}}
        <template x-if="currentFile.type === 'Image'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">

                <img :src="currentFile.file_url"
                    alt="Image"
                    class="mx-auto rounded-lg shadow mb-4"
                    style="max-width: 100%; max-height: 180px; object-fit: contain;">

                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>

                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download Image
                </button>
            </div>
        </template>



        {{-- Jenis File Video --}}
        <template x-if="currentFile.type === 'Video'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <video controls class="max-h-96 mx-auto rounded-lg shadow mb-4">
                    <source :src="currentFile.file_url">
                </video>
                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>

                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download Video
                </button>
            </div>
        </template>

        {{-- Jenis File Audio --}}
        <template x-if="currentFile.type === 'Audio'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/audio.svg') }}" alt="Audio" class="w-16 h-16 mx-auto mb-4">

                <audio controls class="mx-auto mb-4 w-full">
                    <source :src="currentFile.file_url">
                </audio>

                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>

                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download Audio
                </button>
            </div>
        </template>

        {{-- Jenis File ZIP --}}
        <template x-if="currentFile.type === 'Zip'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/zip.svg') }}" alt="Zip" class="w-16 h-16 mx-auto mb-4">
                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>

                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download Archive
                </button>
            </div>
        </template>

        {{-- Jenis File code --}}
        <template x-if="currentFile.type === 'Code'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/code.svg') }}" alt="Code" class="w-16 h-16 mx-auto mb-4">
                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>

                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download Code File
                </button>
            </div>
        </template>

        {{-- Unknown File Type --}}
        <template x-if="currentFile.type === 'Unknown'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/file-unknown.svg') }}" alt="Unknown" class="w-16 h-16 mx-auto mb-4">
                <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>

                <button @click="downloadFile(currentFile)"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                    Download File
                </button>
            </div>
        </template>






    </div>

    {{-- Komentar Section --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6" x-data="documentCommentSection()">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Komentar</h3>

        <!-- Tambah Komentar -->
        <div class="mb-6">
            <label class="text-sm font-medium text-gray-700 mb-2 block">Tulis Komentar</label>
            <div class="flex items-start gap-3">
                <img src="https://i.pravatar.cc/40?img=11" alt="Avatar" class="rounded-full w-10 h-10">

                <!-- Container untuk editor komentar utama -->
                <div class="flex-1">
                    <div class="bg-white border border-gray-300 rounded-lg p-4">
                        <!-- Gunakan ID yang unik untuk editor komentar dokumen -->
                        <div id="document-main-comment-editor" class="min-h-[120px] bg-white"></div>
                        
                        <div class="flex justify-end gap-2 mt-4">
                            <button @click="destroyDocumentMainEditor()" 
                                class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded-lg hover:text-gray-800 transition">
                                Batal
                            </button>
                            <button @click="submitMainComment()" 
                                class="px-3 py-1 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Kirim
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                        <button @click="toggleReply(comment)"
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

                    <!-- FORM BALAS (inline) -->
                    <template x-if="replyView.active && replyView.parentComment?.id === comment.id">
                        <div class="mt-4 pl-6 border-l-2 border-gray-200">
                            <div class="bg-white rounded-lg p-4 border border-gray-200">
                                <h4 class="text-sm font-semibold text-gray-800 mb-2">Membalas
                                    <span x-text="comment.author.name"></span></h4>

                                <div class="border border-gray-300 rounded-lg overflow-hidden mb-3">
                                    <!-- container unik untuk reply editor -->
                                    <div :id="'document-reply-editor-' + comment.id" class="min-h-[100px] p-3 bg-white"></div>
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
                                        <img :src="reply.author.avatar" class="w-6 h-6 rounded-full">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-semibold text-gray-800" x-text="reply.author.name"></p>
                                                <span class="text-xs text-gray-500" x-text="formatCommentDate(reply.createdAt)"></span>
                                            </div>
                                            <div class="text-sm text-gray-700 mt-1" x-html="reply.content"></div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
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