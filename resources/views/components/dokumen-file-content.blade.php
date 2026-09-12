{{-- Konten File dan Komentar --}}
<div x-show="currentFile && !replyView.active  && isLoadingPermission === false" class="mt-4 sm:mt-6">
    {{-- Preview File --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
        <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Preview File</h3>

        {{-- Preview berdasarkan tipe file --}}
        {{-- Jenis File PDF --}}
        <template x-if="currentFile && currentFile.type === 'PDF'">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/icons/pdf.svg') }}" alt="PDF" class="w-6 h-6">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs sm:max-w-md" x-text="currentFile?.name"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="window.open('/documents/' + currentFile.id + '/preview', '_blank')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Buka Tab Baru
                        </button>
                        <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download PDF
                        </button>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-100 shadow-inner">
                    <iframe :src="'/documents/' + currentFile.id + '/preview'" class="w-full h-[650px] border-0" frameborder="0"></iframe>
                </div>
            </div>
        </template>

        {{-- Jenis File Word --}}
        <template x-if="currentFile && currentFile.type === 'Word'">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/icons/microsoft-word.svg') }}" alt="Word" class="w-6 h-6">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs sm:max-w-md" x-text="currentFile?.name"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="window.open('https://docs.google.com/viewer?url=' + encodeURIComponent(currentFile.file_url.startsWith('http') ? currentFile.file_url : (window.location.origin + '/documents/' + currentFile.id + '/preview')) + '&embedded=true', '_blank')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Buka di Google Docs
                        </button>
                        <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download Document
                        </button>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 shadow-inner">
                    <iframe :src="'https://docs.google.com/viewer?url=' + encodeURIComponent(currentFile.file_url.startsWith('http') ? currentFile.file_url : (window.location.origin + '/documents/' + currentFile.id + '/preview')) + '&embedded=true'"
                        class="w-full h-[650px] border-0" frameborder="0">
                    </iframe>
                </div>
            </div>
        </template>

        {{-- Jenis File Excel --}}
        <template x-if="currentFile && currentFile.type === 'Excel'">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/icons/excel.svg') }}" alt="Excel" class="w-6 h-6">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs sm:max-w-md" x-text="currentFile?.name"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="window.open('https://docs.google.com/viewer?url=' + encodeURIComponent(currentFile.file_url.startsWith('http') ? currentFile.file_url : (window.location.origin + '/documents/' + currentFile.id + '/preview')) + '&embedded=true', '_blank')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Buka di Google Docs
                        </button>
                        <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download Spreadsheet
                        </button>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 shadow-inner">
                    <iframe :src="'https://docs.google.com/viewer?url=' + encodeURIComponent(currentFile.file_url.startsWith('http') ? currentFile.file_url : (window.location.origin + '/documents/' + currentFile.id + '/preview')) + '&embedded=true'"
                        class="w-full h-[650px] border-0" frameborder="0">
                    </iframe>
                </div>
            </div>
        </template>

        {{-- Jenis File Powerpoint --}}
        <template x-if="currentFile && currentFile.type === 'PowerPoint'">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/icons/powerpoint.svg') }}" alt="PowerPoint" class="w-6 h-6">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs sm:max-w-md" x-text="currentFile?.name"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="window.open('https://docs.google.com/viewer?url=' + encodeURIComponent(currentFile.file_url.startsWith('http') ? currentFile.file_url : (window.location.origin + '/documents/' + currentFile.id + '/preview')) + '&embedded=true', '_blank')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Buka di Google Docs
                        </button>
                        <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download PowerPoint
                        </button>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-gray-50 shadow-inner">
                    <iframe :src="'https://docs.google.com/viewer?url=' + encodeURIComponent(currentFile.file_url.startsWith('http') ? currentFile.file_url : (window.location.origin + '/documents/' + currentFile.id + '/preview')) + '&embedded=true'"
                        class="w-full h-[650px] border-0" frameborder="0">
                    </iframe>
                </div>
            </div>
        </template>

        {{-- Jenis File Text --}}
        <template x-if="currentFile && currentFile.type === 'Text'">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/icons/text-file.svg') }}" alt="Text" class="w-6 h-6">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs sm:max-w-md" x-text="currentFile?.name"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="window.open('/documents/' + currentFile.id + '/preview', '_blank')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Buka Tab Baru
                        </button>
                        <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download Text File
                        </button>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-inner">
                    <iframe :src="'/documents/' + currentFile.id + '/preview'" class="w-full h-[500px] border-0" frameborder="0"></iframe>
                </div>
            </div>
        </template>

        {{-- Jenis File Gambar --}}
        <template x-if="currentFile && currentFile.type === 'Image'">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/icons/image.svg') }}" alt="Image" class="w-6 h-6">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs sm:max-w-md" x-text="currentFile?.name"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="window.open('/documents/' + currentFile.id + '/preview', '_blank')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Buka Gambar Penuh
                        </button>
                        <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download Image
                        </button>
                    </div>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center justify-center min-h-[300px]">
                    <img :src="'/documents/' + currentFile.id + '/preview'" alt="Preview"
                        class="max-h-[550px] max-w-full rounded-lg shadow-sm object-contain">
                </div>
            </div>
        </template>

        {{-- Jenis File Video --}}
        <template x-if="currentFile && currentFile.type === 'Video'">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/icons/video.svg') }}" alt="Video" class="w-6 h-6">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs sm:max-w-md" x-text="currentFile?.name"></span>
                    </div>
                    <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Download Video
                    </button>
                </div>
                <div class="bg-black rounded-lg p-2 flex items-center justify-center">
                    <video controls class="max-h-[550px] max-w-full rounded">
                        <source :src="'/documents/' + currentFile.id + '/preview'">
                    </video>
                </div>
            </div>
        </template>

        {{-- Jenis File Audio --}}
        <template x-if="currentFile && currentFile.type === 'Audio'">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/icons/audio.svg') }}" alt="Audio" class="w-6 h-6">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs sm:max-w-md" x-text="currentFile?.name"></span>
                    </div>
                    <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Download Audio
                    </button>
                </div>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                    <img src="{{ asset('images/icons/audio.svg') }}" alt="Audio" class="w-16 h-16 mx-auto mb-4">
                    <audio controls class="w-full max-w-xl mx-auto">
                        <source :src="'/documents/' + currentFile.id + '/preview'">
                    </audio>
                </div>
            </div>
        </template>

        {{-- Jenis File ZIP --}}
        <template x-if="currentFile && currentFile.type === 'Zip'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/zip.svg') }}" alt="Zip" class="w-16 h-16 mx-auto mb-4">
                <p class="text-base font-semibold text-gray-700 mb-1" x-text="currentFile?.name"></p>
                <p class="text-xs text-gray-500 mb-4" x-text="(currentFile?.type || '') + ' • ' + (currentFile?.size || '')"></p>
                <p class="text-xs text-gray-400 mb-5">Berkas arsip (ZIP/RAR) tidak dapat dipratinjau di browser. Silakan unduh berkas untuk mengekstrak isinya.</p>
                <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Download Archive
                </button>
            </div>
        </template>

        {{-- Jenis File Code --}}
        <template x-if="currentFile && currentFile.type === 'Code'">
            <div class="space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/icons/code.svg') }}" alt="Code" class="w-6 h-6">
                        <span class="text-sm font-medium text-gray-800 truncate max-w-xs sm:max-w-md" x-text="currentFile?.name"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="window.open('/documents/' + currentFile.id + '/preview', '_blank')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Buka Tab Baru
                        </button>
                        <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                            Download Code File
                        </button>
                    </div>
                </div>
                <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-inner">
                    <iframe :src="'/documents/' + currentFile.id + '/preview'" class="w-full h-[500px] border-0" frameborder="0"></iframe>
                </div>
            </div>
        </template>

        {{-- Unknown File Type --}}
        <template x-if="currentFile && currentFile.type === 'Unknown'">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                <img src="{{ asset('images/icons/file-unknown.svg') }}" alt="Unknown" class="w-16 h-16 mx-auto mb-4">
                <p class="text-base font-semibold text-gray-700 mb-1" x-text="currentFile?.name"></p>
                <p class="text-xs text-gray-500 mb-4" x-text="(currentFile?.type || '') + ' • ' + (currentFile?.size || '')"></p>
                <p class="text-xs text-gray-400 mb-5">Tipe berkas ini tidak dapat dipratinjau langsung di browser. Silakan unduh untuk membukanya.</p>
                <button @click="window.location.href = '/documents/' + currentFile.id + '/download'"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 transition font-medium text-sm shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Download File
                </button>
            </div>
        </template>
    </div>

    {{-- Komentar Section --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6" x-data="documentCommentSection()">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Komentar</h3>

        {{-- Tambah Komentar --}}
        <div class="mb-6">
            <label class="text-sm font-medium text-gray-700 mb-2 block">Tulis Komentar</label>
            <div class="flex items-start gap-3">
                @php
                    $user = auth()->user();
                    $avatar = $user->avatar;

                    if (!$avatar) {
                        $avatarUrl =
                            'https://ui-avatars.com/api/?name=' .
                            urlencode($user->full_name) .
                            '&background=random&color=fff';
                    } elseif (str_starts_with($avatar, 'http')) {
                        $avatarUrl = $avatar;
                    } else {
                        $avatarUrl = asset('storage/' . $avatar);
                    }
                @endphp

                <img src="{{ $avatarUrl }}" alt="Avatar" class="rounded-full w-10 h-10">

                <div class="flex-1">
                    <div class="bg-white border border-gray-300 rounded-lg p-4">
                        <div id="document-main-comment-editor" class="min-h-[120px] bg-white"></div>

                        <div class="flex justify-end gap-2 mt-4">
                            <button @click="resetMainEditor()"
                                class="px-3 py-1 text-sm text-gray-600 border border-gray-300 rounded-lg hover:text-gray-800 transition">
                                Batal
                            </button>
                            {{-- ✅ PASTIKAN INI MEMANGGIL submitMainComment() --}}
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
            <template x-for="comment in (currentFile?.comments || [])" :key="comment.id">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
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
                                    <span x-text="comment.author.name"></span>
                                </h4>

                                <div class="border border-gray-300 rounded-lg overflow-hidden mb-3">
                                    <!-- container unik untuk reply editor -->
                                    <div :id="'document-reply-editor-' + comment.id"
                                        class="min-h-[100px] p-3 bg-white">
                                    </div>
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
                                                <p class="text-sm font-semibold text-gray-800"
                                                    x-text="reply.author.name"></p>
                                                <span class="text-xs text-gray-500"
                                                    x-text="formatCommentDate(reply.createdAt)"></span>
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
            <div x-show="!currentFile?.comments?.length" class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <p class="text-sm">Belum ada komentar</p>
                <p class="text-xs">Jadilah yang pertama berkomentar</p>
            </div>
        </div>
    </div>
</div>
