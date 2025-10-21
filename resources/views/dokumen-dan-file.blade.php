@extends('layouts.app')

<style>
    /* Styling untuk CKEditor */
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

    /* Styling untuk konten HTML dari komentar */
    .prose {
        line-height: 1.6;
        max-height: none !important;
        overflow: visible !important;
    }

    .prose p {
        margin-bottom: 0.5em;
    }

    .prose ul,
    .prose ol {
        margin-left: 1.25em;
        margin-bottom: 0.5em;
    }

    .prose li {
        margin-bottom: 0.25em;
        word-wrap: break-word;
    }

    /* Container komentar individual - hilangkan batasan */
    .bg-gray-50.rounded-lg.p-4.border.border-gray-200 {
        max-height: none !important;
        overflow: visible !important;
    }

    /* Untuk daftar komentar, biarkan scroll tapi jangan potong konten */
    .space-y-4.max-h-96.overflow-y-auto {
        max-height: 500px !important;
        /* Tinggi yang lebih reasonable */
        overflow-y: auto !important;
    }

    /* Pastikan konten dalam komentar tidak terpotong */
    .space-y-4.max-h-96.overflow-y-auto .bg-gray-50 {
        max-height: none !important;
        overflow: visible !important;
    }

    /* Breadcrumb styling */
    .breadcrumb-item {
        display: flex;
        align-items: center;
    }


    /* Custom responsive utilities */
    @media (max-width: 576px) {
        .mobile-padding {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .mobile-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 768px) {
        .tablet-grid {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }

    @media (max-width: 992px) {
        .desktop-grid {
            grid-template-columns: repeat(4, 1fr) !important;
        }
    }
</style>

@section('title', 'Dokumen dan File')

@section('content')
    <div x-data="documentSearch()" x-init="$store.workspace = { selectedMenu: 'dokumen' }" class="bg-[#f3f6fc] min-h-screen">

        {{-- Workspace Navigation --}}
        @include('components.workspace-nav', ['active' => 'dokumen'])

        {{-- Modal Buat Folder --}}
        <div x-show="showCreateFolderModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showCreateFolderModal = false">
                {{-- Header Modal --}}
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800"
                        x-text="currentFolder ? 'Buat Sub Folder' : 'Buat Folder'"></h3>
                </div>

                {{-- Content Modal --}}
                <div class="px-6 py-4">
                    <p class="text-sm text-gray-600 mb-4"
                        x-text="currentFolder ? 'Masukkan nama sub folder' : 'Masukkan nama folder'"></p>
                    <input type="text" x-model="newFolderName"
                        :placeholder="currentFolder ? 'Nama sub folder' : 'Nama folder'"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition mb-4"
                        @keyup.enter="createFolder()">

                    {{-- Switch untuk Folder Rahasia --}}
                    <div class="flex items-center justify-between py-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Folder Rahasia</p>
                                <p class="text-xs text-gray-500">Hanya yang berhak dapat melihat</p>
                            </div>
                        </div>
                        <button type="button" @click="isSecretFolder = !isSecretFolder"
                            :class="isSecretFolder ? 'bg-blue-600' : 'bg-gray-200'"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <span class="sr-only">Folder Rahasia</span>
                            <span :class="isSecretFolder ? 'translate-x-5' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" />
                        </button>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button @click="showCreateFolderModal = false; newFolderName = ''; isSecretFolder = false"
                        class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button @click="createFolder()" :disabled="!newFolderName.trim()"
                        :class="!newFolderName.trim() ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                        class="px-4 py-2 text-sm text-white rounded-lg transition">
                        Simpan
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Pindah Berkas --}}
        <div x-show="showMoveDocumentsModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showMoveDocumentsModal = false">
                {{-- Header Modal --}}
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Pindah Berkas</h3>
                </div>

                {{-- Content Modal --}}
                <div class="px-6 py-4 space-y-4">
                    {{-- Lokasi Saat Ini --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Saat Ini</label>
                        <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                            </svg>
                            <span class="text-sm text-gray-600" x-text="getCurrentLocation()"></span>
                        </div>
                    </div>

                    {{-- Jumlah Berkas --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Berkas</label>
                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <span class="text-sm font-medium text-blue-700"
                                x-text="selectedDocuments.length + ' berkas dipilih'"></span>
                        </div>
                    </div>

                    {{-- Tujuan Workspace --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tujuan Workspace</label>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            {{-- Pilihan Workspace --}}
                            <template x-for="workspace in availableWorkspaces" :key="workspace.id">
                                <div>
                                    <button @click="selectedWorkspace = workspace"
                                        :class="selectedWorkspace?.id === workspace.id ? 'border-blue-500 bg-blue-50' :
                                            'border-gray-200 bg-white'"
                                        class="w-full p-3 border rounded-lg text-left hover:bg-gray-50 transition flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div :class="workspace.color"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-700" x-text="workspace.name"></p>
                                                <p class="text-xs text-gray-500" x-text="workspace.description"></p>
                                            </div>
                                        </div>
                                        <svg x-show="selectedWorkspace?.id === workspace.id" class="w-5 h-5 text-blue-600"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>

                                    {{-- Folder dalam workspace yang dipilih --}}
                                    <div x-show="selectedWorkspace?.id === workspace.id && workspace.folders.length > 0"
                                        class="ml-8 mt-2 space-y-1">
                                        <p class="text-xs text-gray-500 mb-2">Pilih folder:</p>
                                        <button @click="selectedFolder = null"
                                            :class="!selectedFolder ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'"
                                            class="w-full p-2 rounded text-xs flex items-center gap-2 transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 12h14M12 5l7 7-7 7" />
                                            </svg>
                                            Dokumen Utama (tanpa folder)
                                        </button>
                                        <template x-for="folder in workspace.folders" :key="folder.id">
                                            <button @click="selectedFolder = folder"
                                                :class="selectedFolder?.id === folder.id ? 'bg-blue-100 text-blue-700' :
                                                    'bg-gray-100 text-gray-700'"
                                                class="w-full p-2 rounded text-xs flex items-center gap-2 transition">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                                </svg>
                                                <span x-text="folder.name"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Footer Modal --}}
                <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button @click="showMoveDocumentsModal = false"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition">
                        Batal
                    </button>
                    <button @click="confirmMoveDocuments()" :disabled="!selectedWorkspace"
                        :class="!selectedWorkspace ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                        class="px-4 py-2 text-sm text-white rounded-lg transition">
                        Pindahkan
                    </button>
                </div>
            </div>
        </div>

        {{-- Konten Halaman --}}
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto">
            {{-- Container dengan border dan padding --}}
            <div
                class="border border-gray-200 rounded-lg bg-white p-4 sm:p-6 flex flex-col h-[calc(100vh-140px)] sm:h-[calc(100vh-160px)] lg:h-[calc(100vh-200px)]">

                {{-- Header dengan Pencarian dan Tombol Aksi --}}
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6 flex-shrink-0"
                    x-show="!currentFile"> {{-- Tombol Aksi --}}
                    <div class="flex flex-wrap gap-2 sm:gap-3" x-show="!selectMode">
                        <button @click="showCreateFolderModal = true"
                            class="bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg hover:bg-blue-700 flex items-center gap-1 sm:gap-2 transition text-xs sm:text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="font-medium text-sm"
                                x-text="currentFolder ? 'Buat Sub Folder' : 'Buat Folder'"></span>
                        </button>

                        <label
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 16v-8m0 0l-4 4m4-4l4 4m4 4a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h3l2-2h6a2 2 0 012 2v12z" />
                            </svg>
                            <span class="font-medium text-sm">Unggah File</span>
                            <input type="file" class="hidden" @change="uploadFileToFolder($event)">
                        </label>

                        <button @click="toggleSelectMode()"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M2 12l9 9L22 4" />
                            </svg>
                            <span class="font-medium text-sm">Pilih Berkas</span>
                        </button>
                    </div>

                    {{-- Tombol Batalkan Pilihan (muncul saat select mode) --}}
                    <div class="flex gap-3" x-show="selectMode">
                        <button @click="cancelSelection()"
                            class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 flex items-center gap-2 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span class="font-medium text-sm">Batalkan Pilihan</span>
                        </button>
                    </div>

                    {{-- Search Bar --}}
                    <div class="relative w-full sm:w-64 lg:w-80" x-show="!selectMode">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" x-model="searchQuery" @input="filterDocuments()"
                            :placeholder="currentFolder ? 'Cari dalam ' + currentFolder.name : 'Cari dokumen atau folder...'"
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    </div>

                    {{-- Spacer untuk menjaga layout saat Select Mode --}}
                    <div class="w-80" x-show="selectMode"></div>
                </div>

                {{-- Breadcrumb dan Info Folder --}}
                <div x-show="currentFolder" class="mb-4 sm:mb-6 flex-shrink-0">
                    {{-- Breadcrumb --}}
                    <div class="flex items-center gap-1 sm:gap-2 text-xs sm:text-sm text-gray-500 mb-2 flex-wrap">
                        <button @click="goToRoot()" class="text-gray-500 hover:text-gray-700 transition">
                            Dokumen
                        </button>
                        {{-- Breadcrumb items dari folder history --}}
                        <template x-for="(crumb, index) in breadcrumbs" :key="index">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">›</span>
                                <button @click="navigateToFolder(crumb)"
                                    class="text-gray-500 hover:text-gray-700 transition" x-text="crumb.name"></button>
                            </div>
                        </template>
                        <{{-- Current folder (bukan bagian dari breadcrumb yang bisa diklik) --}} <template x-if="currentFolder">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">›</span>
                                <span class="text-gray-700 font-medium" x-text="currentFolder.name"></span>
                            </div>
                            </template>
                    </div>
                    {{-- Header Folder --}}
                    <div class="bg-white border border-gray-200 rounded-lg p-3 sm:p-4">
                        {{-- Judul Folder dan Actions --}}
                        <div class="flex items-center justify-between mb-2 sm:mb-3">
                            <h2 class="text-lg font-semibold text-gray-800" x-text="currentFolder.name"></h2>
                            <div class="flex items-center gap-1">
                                <button @click="openEditFolder(currentFolder)"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="openDeleteFolder(currentFolder)"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Info Pembuat dan Diterima Oleh --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
                            {{-- Info Pembuat --}}
                            <div class="flex items-center gap-2">
                                <img :src="currentFolder.creatorAvatar || 'https://i.pravatar.cc/32?img=8'" alt="Profile"
                                    class="w-6 h-6 rounded-full">
                                <div>
                                    <p class="text-xs font-medium text-gray-700"
                                        x-text="currentFolder.creator || 'Admin'"></p>
                                    <p class="text-xs text-gray-500" x-text="formatDate(currentFolder.createdAt)"></p>
                                </div>
                            </div>

                            {{-- Diterima Oleh --}}
                            <div class="flex items-center gap-2">
                                <div class="text-right">
                                    <p class="text-xs font-medium text-gray-700">Diterima Oleh :</p>
                                </div>

                                {{-- Penerima --}}
                                <template x-for="recipient in currentFolder.recipients" :key="recipient.id">
                                    <div class="relative group">
                                        <img :src="recipient.avatar" :alt="recipient.name" class="w-6 h-6 rounded-full">
                                        <div
                                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block z-10">
                                            <div class="bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap"
                                                x-text="recipient.name"></div>
                                            <div
                                                class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800">
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Tombol Tambah --}}
                                <div>
                                    <button @click="openAddMemberModal = true"
                                        class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                                        <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Tambah Peserta -->
                <div x-show="openAddMemberModal" x-cloak
                    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4" x-transition>
                    <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b">
                            <h2 class="text-center font-bold text-lg text-gray-800">Tambah Peserta</h2>
                        </div>

                        <!-- Isi Modal -->
                        <div class="p-6 space-y-4">
                            <!-- Input Cari -->
                            <div class="relative">
                                <input type="text" placeholder="Cari anggota..."
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    x-model="searchMember">
                            </div>

                            <!-- Pilih Semua -->
                            <div class="flex items-center justify-between border-b pb-2">
                                <span class="font-medium text-gray-700 text-sm">Pilih Semua</span>
                                <input type="checkbox" x-model="selectAll" @change="toggleSelectAll">
                            </div>

                            <!-- List Anggota -->
                            <div class="space-y-3 max-h-60 overflow-y-auto">
                                <template x-for="(member, index) in filteredMembers()" :key="index">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <img :src="member.avatar" class="w-8 h-8 rounded-full" alt="">
                                            <span class="text-sm font-medium text-gray-700" x-text="member.name"></span>
                                        </div>
                                        <input type="checkbox" x-model="member.selected">
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="flex justify-end gap-3 p-4 border-t">
                            <button type="button" @click="openAddMemberModal = false"
                                class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">Batal</button>
                            <button type="button" @click="saveSelectedMembers()"
                                class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Simpan</button>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Folder -->
                <div x-show="showEditFolderModal" x-cloak
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showEditFolderModal = false">
                        {{-- Header Modal --}}
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Edit Folder</h3>
                        </div>

                        {{-- Content Modal --}}
                        <div class="px-6 py-4">
                            <p class="text-sm text-gray-600 mb-4">Masukkan nama folder</p>
                            <input type="text" x-model="editFolderName" placeholder="Nama folder"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition mb-4">

                            {{-- Switch untuk Folder Rahasia --}}
                            <div class="flex items-center justify-between py-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">Folder Rahasia</p>
                                        <p class="text-xs text-gray-500">Hanya yang berhak dapat melihat</p>
                                    </div>
                                </div>
                                <button type="button" @click="editIsSecretFolder = !editIsSecretFolder"
                                    :class="editIsSecretFolder ? 'bg-blue-600' : 'bg-gray-200'"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <span class="sr-only">Folder Rahasia</span>
                                    <span :class="editIsSecretFolder ? 'translate-x-5' : 'translate-x-0'"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" />
                                </button>
                            </div>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                            <button @click="showEditFolderModal = false"
                                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button @click="updateFolder()" :disabled="!editFolderName.trim()"
                                :class="!editFolderName.trim() ? 'bg-gray-300 cursor-not-allowed' :
                                    'bg-blue-600 hover:bg-blue-700'"
                                class="px-4 py-2 text-sm text-white rounded-lg transition">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Hapus Folder -->
                <div x-show="showDeleteFolderModal" x-cloak
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showDeleteFolderModal = false">
                        {{-- Header Modal --}}
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Hapus Folder</h3>
                        </div>

                        {{-- Content Modal --}}
                        <div class="px-6 py-6">
                            <div class="flex items-center justify-center mb-4">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-center text-gray-700 font-medium">Anda yakin ingin menghapus folder ini?</p>
                            <p class="text-center text-sm text-gray-500 mt-2"
                                x-text="'Folder: ' + (deletingFolder?.name || '')"></p>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                            <button @click="showDeleteFolderModal = false"
                                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button @click="confirmDeleteFolder()"
                                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Ya
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Grid Dokumen di Dalam Folder --}}
                <template x-if="currentFolder">
                    <div
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4 overflow-y-auto flex-1 pb-4">
                        {{-- Tampilkan hasil pencarian atau dokumen biasa --}}
                        <template x-for="document in getDisplayedDocuments()" :key="document.id">
                            <div @click="selectMode ? toggleDocumentSelection(document) : (document.type === 'Folder' ? openFolder(document) : openFile(document))"
                                :class="{
                                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(
                                        document.id),
                                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(
                                        document.id),
                                    'cursor-pointer': true
                                }"
                                class="flex flex-col items-center text-center p-2 sm:p-3 border rounded-lg transition relative">

                                <!-- Checkbox untuk select mode -->
                                <div x-show="selectMode" class="absolute top-1 right-1 sm:top-2 sm:right-2">
                                    <div :class="isDocumentSelected(document.id) ? 'bg-blue-600 border-blue-600' :
                                        'bg-white border-gray-300'"
                                        class="w-4 h-4 sm:w-5 sm:h-5 border-2 rounded flex items-center justify-center">
                                        <svg x-show="isDocumentSelected(document.id)"
                                            class="w-2 h-2 sm:w-3 sm:h-3 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>

                                <img :src="document.icon" :alt="document.type"
                                    class="w-8 h-8 sm:w-10 sm:h-10 mb-1 sm:mb-2">
                                <span class="text-xs font-medium text-gray-700 truncate w-full"
                                    x-text="document.name"></span>

                                <span x-show="document.type !== 'Folder'" class="text-xs text-gray-400 mt-0.5"
                                    x-text="document.type"></span>
                            </div>
                        </template>

                        {{-- Empty State --}}
                        <div x-show="getDisplayedDocuments().length === 0 && searchQuery.length > 0"
                            class="col-span-2 sm:col-span-3 md:col-span-4 lg:col-span-5 xl:col-span-6
            flex flex-col items-center justify-center py-6 sm:py-8 text-gray-500">
                            <div
                                class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 rounded-full flex items-center justify-center mb-2 sm:mb-3">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium mb-1">Tidak ada hasil ditemukan</p>
                            <p class="text-xs">Coba gunakan kata kunci lain</p>
                        </div>

                        <div x-show="getDisplayedDocuments().length === 0 && searchQuery.length === 0"
                            class="col-span-6 flex flex-col items-center justify-center py-8 text-gray-500">
                            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                </svg>
                            </div>
                            <p class="text-sm font-medium mb-1">Folder ini kosong</p>
                            <p class="text-xs">Tambahkan file atau folder baru</p>
                        </div>
                    </div>
                </template>

                {{-- Hasil Pencarian Info di Dalam Folder --}}
                <div x-show="searchQuery.length > 0 && !(selectMode && selectedDocuments.length > 0) && currentFolder"
                    class="mb-4 flex-shrink-0">
                    <p class="text-sm text-gray-600">
                        Menampilkan <span x-text="filteredDocuments.length"></span> hasil dari pencarian "<span
                            x-text="searchQuery"></span>" dalam <span x-text="getCurrentFolderPath()"></span>
                        <button @click="clearSearch()" class="text-blue-600 hover:text-blue-800 ml-2 text-sm">
                            Bersihkan pencarian
                        </button>
                    </p>
                </div>

                {{-- Header Pilihan (muncul saat select mode) --}}
                <div x-show="selectMode && selectedDocuments.length > 0"
                    class="mb-3 sm:mb-4 flex-shrink-0 bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0">
                        <div class="flex items-center gap-2 sm:gap-4">
                            <button @click="cancelSelection()"
                                class="flex items-center gap-2 text-gray-600 hover:text-gray-800 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="font-medium">Batal</span>
                            </button>
                            <div class="h-6 w-px bg-gray-300"></div>
                            <span class="text-sm text-gray-600">
                                <span x-text="selectedDocuments.length"></span> berkas dipilih
                            </span>
                        </div>
                        <button @click="showMoveDocumentsModal = true"
                            class="bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2 transition text-xs sm:text-sm mt-2 sm:mt-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span class="font-medium text-sm">Pindahkan</span>
                        </button>
                    </div>
                </div>

                {{-- Hasil Pencarian Info --}}
                <div x-show="searchQuery.length > 0 && !(selectMode && selectedDocuments.length > 0) && !currentFolder && !currentFile"
                    class="mb-4 flex-shrink-0">
                    <p class="text-sm text-gray-600">
                        Menampilkan <span x-text="filteredDocuments.length"></span> hasil dari pencarian "<span
                            x-text="searchQuery"></span>"
                        <button @click="clearSearch()" class="text-blue-600 hover:text-blue-800 ml-2 text-sm">
                            Bersihkan pencarian
                        </button>
                    </p>
                </div>

                {{-- Breadcrumb dan Info File --}}
                <div x-show="currentFile && !replyView.active" class="mb-6 flex-shrink-0"> {{-- Breadcrumb --}}
                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                        <button @click="goBackToFolder()"
                            class="text-gray-500 hover:text-gray-700 transition flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali
                        </button>
                        <span class="text-gray-400">|</span>
                        <button @click="goToRoot()" class="text-gray-500 hover:text-gray-700 transition">
                            Dokumen
                        </button>
                        {{-- Breadcrumb folder --}}
                        <template x-for="(crumb, index) in fileBreadcrumbs" :key="index">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">›</span>
                                <button @click="navigateToFolderFromFile(crumb)"
                                    class="text-gray-500 hover:text-gray-700 transition" x-text="crumb.name"></button>
                            </div>
                        </template>

                        {{-- Current file --}}
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400">›</span>
                            <span class="text-gray-700 font-medium" x-text="currentFile.name"></span>
                        </div>
                    </div>



                    {{-- Header File --}}
                    <div x-show="currentFile && !replyView.active" class="bg-white border border-gray-200 rounded-lg p-4">
                        {{-- Judul File dan Actions --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <img :src="currentFile.icon" :alt="currentFile.type" class="w-8 h-8">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800" x-text="currentFile.name"></h2>
                                    <p class="text-xs text-gray-500" x-text="currentFile.type + ' • ' + currentFile.size">
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="downloadFile(currentFile)"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </button>
                                <button @click="openEditFile(currentFile)"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="openDeleteFile(currentFile)"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Info Pembuat dan Diterima Oleh --}}
                        <div class="flex items-center justify-between">
                            {{-- Info Pembuat --}}
                            <div class="flex items-center gap-2">
                                <img :src="currentFile.creatorAvatar || 'https://i.pravatar.cc/32?img=8'" alt="Profile"
                                    class="w-6 h-6 rounded-full">
                                <div>
                                    <p class="text-xs font-medium text-gray-700" x-text="currentFile.creator || 'Admin'">
                                    </p>
                                    <p class="text-xs text-gray-500" x-text="formatDate(currentFile.createdAt)"></p>
                                </div>
                            </div>

                            {{-- Diterima Oleh --}}
                            <div class="flex items-center gap-2">
                                <div class="text-right">
                                    <p class="text-xs font-medium text-gray-700">Diterima Oleh :</p>
                                </div>

                                <template x-for="recipient in currentFile.recipients" :key="recipient.id">
                                    <div class="relative group">
                                        <img :src="recipient.avatar" :alt="recipient.name" class="w-6 h-6 rounded-full">
                                        <div
                                            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-1 hidden group-hover:block z-10">
                                            <div class="bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap"
                                                x-text="recipient.name"></div>
                                            <div
                                                class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-800">
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                {{-- Tombol Tambah --}}
                                <div>
                                    <button @click="openAddMemberModal = true"
                                        class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 transition">
                                        <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Konten File dan Komentar --}}
                    <div x-show="currentFile && !replyView.active" class="mt-4 sm:mt-6">
                        {{-- Preview File --}}
                        <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
                            <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4">Preview File</h3>

                            {{-- Preview berdasarkan tipe file --}}
                            <template x-if="currentFile.type === 'PDF'">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 sm:p-6 md:p-8 text-center">
                                    <img src="{{ asset('images/icons/pdf.svg') }}" alt="PDF"
                                        class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 mx-auto mb-3 sm:mb-4">
                                    <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>
                                    <button @click="downloadFile(currentFile)"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                        Download PDF
                                    </button>
                                </div>
                            </template>

                            <template x-if="currentFile.type === 'Word'">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                    <img src="{{ asset('images/icons/microsoft-word.svg') }}" alt="Word"
                                        class="w-16 h-16 mx-auto mb-4">
                                    <p class="text-sm text-gray-600 mb-4" x-text="currentFile.name"></p>
                                    <button @click="downloadFile(currentFile)"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                                        Download Document
                                    </button>
                                </div>
                            </template>

                            <template x-if="currentFile.type === 'Excel'">
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
                                    <img src="{{ asset('images/icons/excel.svg') }}" alt="Excel"
                                        class="w-16 h-16 mx-auto mb-4">
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
                                        class="mt-3 px-4 py-2text-blue-600 bg-white border border-blue-600 hover:bg-gray-50 text-sm rounded-lg">
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
                                                <img :src="comment.author.avatar" :alt="comment.author.name"
                                                    class="w-6 h-6 rounded-full">
                                                <p class="text-sm font-semibold text-gray-800"
                                                    x-text="comment.author.name"></p>
                                            </div>
                                            <span class="text-xs text-gray-500"
                                                x-text="formatCommentDate(comment.createdAt)"></span>
                                        </div>

                                        {{-- Konten Komentar dengan HTML --}}
                                        <div class="text-sm text-gray-700 prose prose-sm max-w-none mb-2"
                                            x-html="comment.content"></div>

                                        {{-- Tombol Balas dan Jumlah Balasan --}}
                                        <div class="flex items-center gap-4 mt-2">
                                            <button @click="openReplyView(comment)"
                                                class="flex items-center gap-1 text-xs text-gray-500 hover:text-blue-600 transition">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                                <span>balas</span>
                                            </button>

                                            {{-- Tampilkan jumlah balasan jika ada --}}
                                            <template x-if="comment.replies && comment.replies.length > 0">
                                                <span class="text-xs text-gray-500"
                                                    x-text="comment.replies.length + ' balasan'"></span>
                                            </template>
                                        </div>


                                    </div>
                                </template>

                                {{-- Empty State Komentar --}}
                                <div x-show="!currentFile.comments || currentFile.comments.length === 0"
                                    class="text-center py-8 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <p class="text-sm">Belum ada komentar</p>
                                    <p class="text-xs">Jadilah yang pertama berkomentar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                @include('components.balas-komentar')


                <!-- Modal Hapus File -->
                <div x-show="showDeleteFileModal" x-cloak
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showDeleteFileModal = false">
                        {{-- Header Modal --}}
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Hapus File</h3>
                        </div>

                        {{-- Content Modal --}}
                        <div class="px-6 py-6">
                            <div class="flex items-center justify-center mb-4">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                            </div>
                            <p class="text-center text-gray-700 font-medium">Anda yakin ingin menghapus file ini?</p>
                            <p class="text-center text-sm text-gray-500 mt-2"
                                x-text="'File: ' + (deletingFile?.name || '')"></p>
                            <p class="text-center text-xs text-gray-400 mt-1"
                                x-text="'Tipe: ' + (deletingFile?.type || '')"></p>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                            <button @click="showDeleteFileModal = false"
                                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button @click="confirmDeleteFile()"
                                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Ya, Hapus
                            </button>
                        </div>
                    </div>
                </div>



                <!-- Modal Edit File -->
                <div x-show="showEditFileModal" x-cloak
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showEditFileModal = false">
                        {{-- Header Modal --}}
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Edit File</h3>
                        </div>

                        {{-- Content Modal --}}
                        <div class="px-6 py-4">
                            {{-- Info File --}}
                            <div class="flex items-center gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
                                <img :src="editingFile?.icon" :alt="editingFile?.type" class="w-8 h-8">
                                <div>
                                    <p class="text-sm font-medium text-gray-700" x-text="editingFile?.name"></p>
                                    <p class="text-xs text-gray-500" x-text="editingFile?.type"></p>
                                </div>
                            </div>

                            {{-- Switch untuk File Rahasia --}}
                            <div class="flex items-center justify-between py-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700">File Rahasia</p>
                                        <p class="text-xs text-gray-500">Hanya yang berhak dapat melihat</p>
                                    </div>
                                </div>
                                <button type="button" @click="editFileIsSecret = !editFileIsSecret"
                                    :class="editFileIsSecret ? 'bg-blue-600' : 'bg-gray-200'"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <span class="sr-only">File Rahasia</span>
                                    <span :class="editFileIsSecret ? 'translate-x-5' : 'translate-x-0'"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" />
                                </button>
                            </div>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                            <button @click="showEditFileModal = false; editFileIsSecret = false"
                                class="px-4 py-2 text-smtext-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button @click="updateFile()"
                                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>



                {{-- Grid Dokumen Utama (Scrollable) --}}
                <template x-if="filteredDocuments.length > 0 && !currentFolder && !currentFile">
                    <div
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 overflow-y-auto flex-1 pb-4">
                        <template x-for="document in filteredDocuments" :key="document.id">
                            <div @click="selectMode ? toggleDocumentSelection(document) : (document.type === 'Folder' ? openFolder(document) : openFile(document))"
                                :class="{
                                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(
                                        document.id),
                                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(
                                        document.id),
                                    'cursor-pointer': true
                                }"
                                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                                <!-- Checkbox untuk select mode -->
                                <div x-show="selectMode" class="absolute top-2 right-2">
                                    <div :class="isDocumentSelected(document.id) ? 'bg-blue-600 border-blue-600' :
                                        'bg-white border-gray-300'"
                                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                                        <svg x-show="isDocumentSelected(document.id)" class="w-3 h-3 text-white"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>

                                <img :src="document.icon" :alt="document.type" class="w-14 h-14 mb-3">
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-gray-600 truncate w-full" x-text="document.name"></span>
                                    <template x-if="document.isSecret">
                                        <svg class="w-3 h-3 text-yellow-500 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </template>
                                </div>
                                <span class="text-xs text-gray-400 mt-1" x-text="document.type"></span>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Empty State untuk Pencarian --}}
                <template
                    x-if="filteredDocuments.length === 0 && searchQuery.length > 0 && !currentFolder && !currentFile">
                    <div class="flex-1 flex flex-col items-center justify-center text-gray-500">
                        <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        <p class="text-lg font-medium mb-2">Tidak ada hasil ditemukan</p>
                        <p class="text-sm">Coba gunakan kata kunci lain atau <button @click="clearSearch()"
                                class="text-blue-600 hover:text-blue-800">bersihkan pencarian</button></p>
                    </div>
                </template>

                {{-- Default View (ketika tidak ada pencarian dan tidak di dalam folder) --}}
                <template
                    x-if="filteredDocuments.length === 0 && searchQuery.length === 0 && !currentFolder && !currentFile">

                    <div
                        class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 lg:gap-6 overflow-y-auto flex-1 pb-4">
                        {{-- Folder --}}
                        <template x-for="folder in folders" :key="folder.id">
                            <div @click="selectMode ? toggleDocumentSelection(folder) : openFolder(folder)"
                                :class="{
                                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(
                                        folder.id),
                                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(
                                        folder.id),
                                    'cursor-pointer': true
                                }"
                                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                                <!-- Checkbox untuk select mode -->
                                <div x-show="selectMode" class="absolute top-2 right-2">
                                    <div :class="isDocumentSelected(folder.id) ? 'bg-blue-600 border-blue-600' :
                                        'bg-white border-gray-300'"
                                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                                        <svg x-show="isDocumentSelected(folder.id)" class="w-3 h-3 text-white"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>

                                <img src="{{ asset('images/icons/folder.svg') }}" alt="Folder" class="w-14 h-14 mb-3">
                                <div class="flex items-center gap-1">
                                    <span class="text-sm font-medium text-gray-700" x-text="folder.name"></span>
                                    <template x-if="folder.isSecret">
                                        <svg class="w-3 h-3 text-yellow-500 flex-shrink-0" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- File PDF --}}
                        <template x-for="(pdf, index) in pdfFiles" :key="pdf.id">
                            <div @click="selectMode ? toggleDocumentSelection(pdf) : openFile(pdf)"
                                :class="{
                                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(
                                        pdf.id),
                                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(pdf
                                        .id),
                                    'cursor-pointer': true
                                }"
                                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                                <!-- Checkbox untuk select mode -->
                                <div x-show="selectMode" class="absolute top-2 right-2">
                                    <div :class="isDocumentSelected(pdf.id) ? 'bg-blue-600 border-blue-600' :
                                        'bg-white border-gray-300'"
                                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                                        <svg x-show="isDocumentSelected(pdf.id)" class="w-3 h-3 text-white"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>

                                <img src="{{ asset('images/icons/pdf.svg') }}" alt="PDF" class="w-14 h-14 mb-3">
                                <span class="text-xs text-gray-600 truncate w-full" x-text="pdf.name"></span>
                            </div>
                        </template>

                        {{-- File Word --}}
                        <template x-for="(word, index) in wordFiles" :key="word.id">
                            <div @click="selectMode ? toggleDocumentSelection(word) : openFile(word)"
                                :class="{
                                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(
                                        word.id),
                                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(word
                                        .id),
                                    'cursor-pointer': true
                                }"
                                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                                <!-- Checkbox untuk select mode -->
                                <div x-show="selectMode" class="absolute top-2 right-2">
                                    <div :class="isDocumentSelected(word.id) ? 'bg-blue-600 border-blue-600' :
                                        'bg-white border-gray-300'"
                                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                                        <svg x-show="isDocumentSelected(word.id)" class="w-3 h-3 text-white"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>

                                <img src="{{ asset('images/icons/microsoft-word.svg') }}" alt="File"
                                    class="w-14 h-14 mb-3">
                                <span class="text-xs text-gray-600 truncate w-full" x-text="word.name"></span>
                            </div>
                        </template>

                        {{-- File Excel --}}
                        <template x-for="(excel, index) in excelFiles" :key="excel.id">
                            <div <div @click="selectMode ? toggleDocumentSelection(excel) : openFile(excel)"
                                :class="{
                                    'border-blue-500 bg-blue-50 ring-2 ring-blue-200': selectMode && isDocumentSelected(
                                        excel.id),
                                    'border-gray-200 bg-white hover:shadow-md': !selectMode || !isDocumentSelected(excel
                                        .id),
                                    'cursor-pointer': true
                                }"
                                class="flex flex-col items-center text-center p-4 border rounded-lg transition relative">
                                <!-- Checkbox untuk select mode -->
                                <div x-show="selectMode" class="absolute top-2 right-2">
                                    <div :class="isDocumentSelected(excel.id) ? 'bg-blue-600 border-blue-600' :
                                        'bg-white border-gray-300'"
                                        class="w-5 h-5 border-2 rounded flex items-center justify-center">
                                        <svg x-show="isDocumentSelected(excel.id)" class="w-3 h-3 text-white"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>

                                <img src="{{ asset('images/icons/excel.svg') }}" alt="Excel" class="w-14 h-14 mb-3">
                                <span class="text-xs text-gray-600 truncate w-full" x-text="excel.name"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function documentSearch() {
            return {
                // Search & Filter Properties
                searchQuery: '',
                filteredDocuments: [],

                // Modal Properties
                showCreateFolderModal: false,
                showMoveDocumentsModal: false,
                showEditFolderModal: false,
                showDeleteFolderModal: false,
                openAddMemberModal: false,

                // Folder Properties
                newFolderName: '',
                isSecretFolder: false,
                editFolderName: '',
                editIsSecretFolder: false,

                // file properties
                currentFile: null,

                // Selection Properties
                selectMode: false,
                selectedDocuments: [],

                // Workspace Properties
                selectedWorkspace: null,
                selectedFolder: null,
                currentFolder: null,
                folderHistory: [], // Untuk menyimpan history navigasi folder
                breadcrumbs: [], // Untuk breadcrumb navigation

                // Editing Properties
                editingFolder: null,
                deletingFolder: null,
                showDeleteFileModal: false,
                deletingFile: null,
                // Modal Properties (tambahkan ini di bagian yang sudah ada)
                showEditFileModal: false,
                editingFile: null,
                editFileIsSecret: false,

                // Member Properties
                searchMember: '',
                selectAll: false,



                // Data arrays dengan struktur hierarki
                folders: [{
                        id: 'folder-desain',
                        name: 'Desain',
                        type: 'Folder',
                        icon: '{{ asset('images/icons/folder.svg') }}',
                        isSecret: false,
                        creator: 'Admin User',
                        creatorAvatar: 'https://i.pravatar.cc/32?img=8',
                        createdAt: new Date().toISOString(),
                        recipients: [{
                                id: 1,
                                name: 'John Doe',
                                avatar: 'https://i.pravatar.cc/32?img=5'
                            },
                            {
                                id: 2,
                                name: 'Jane Smith',
                                avatar: 'https://i.pravatar.cc/32?img=6'
                            }
                        ],
                        subFolders: [{
                                id: 'folder-desain-login',
                                name: 'Desain Login',
                                type: 'Folder',
                                icon: '{{ asset('images/icons/folder.svg') }}',
                                isSecret: false,
                                creator: 'Designer',
                                creatorAvatar: 'https://i.pravatar.cc/32?img=9',
                                createdAt: new Date().toISOString(),
                                recipients: [{
                                    id: 1,
                                    name: 'John Doe',
                                    avatar: 'https://i.pravatar.cc/32?img=5'
                                }],
                                // Dan file di subfolder
                                files: [{
                                    id: 'file-desain-login-1',
                                    name: 'Dokumen_Dalam_Folder.pdf',
                                    type: 'PDF',
                                    icon: '{{ asset('images/icons/pdf.svg') }}',
                                    size: '2.4 MB',
                                    creator: 'Designer',
                                    creatorAvatar: 'https://i.pravatar.cc/32?img=9',
                                    createdAt: new Date().toISOString(),
                                    recipients: [{
                                        id: 1,
                                        name: 'John Doe',
                                        avatar: 'https://i.pravatar.cc/32?img=5'
                                    }],
                                    comments: [],
                                    isSecret: false
                                }]
                            },
                            {
                                id: 'folder-desain-dashboard',
                                name: 'Desain Dashboard',
                                type: 'Folder',
                                icon: '{{ asset('images/icons/folder.svg') }}',
                                isSecret: false,
                                subFolders: [],
                                files: []
                            }
                        ],
                        files: [{
                            id: 'file-desain-1',
                            name: 'Mockup_Utama.fig',
                            type: 'Design',
                            icon: '{{ asset('images/icons/file.svg') }}',
                            size: '5.2 MB',
                            isSecret: false
                        }]
                    },
                    {
                        id: 'folder-pelaksanaan',
                        name: 'Pelaksanaan',
                        type: 'Folder',
                        icon: '{{ asset('images/icons/folder.svg') }}',
                        isSecret: false,
                        subFolders: [],
                        files: []
                    },
                    {
                        id: 'folder-administrasi',
                        name: 'Administrasi',
                        type: 'Folder',
                        icon: '{{ asset('images/icons/folder.svg') }}',
                        isSecret: false,
                        subFolders: [],
                        files: []
                    }
                ],

                // Update data files untuk menyertakan folder reference
                pdfFiles: [
                    @for ($i = 0; $i < 15; $i++)
                        {
                            id: 'pdf-{{ $i }}',
                            name: 'Proposal_ProyekA.pdf',
                            type: 'PDF',
                            icon: '{{ asset('images/icons/pdf.svg') }}',
                            isSecret: false,
                            folder: null // File di root tidak punya folder
                        },
                    @endfor
                ],

                // File di dalam folder harus memiliki referensi folder
                files: [{
                    id: 'file-desain-login-1',
                    name: 'Dokumen_Dalam_Folder.pdf',
                    type: 'PDF',
                    icon: '{{ asset('images/icons/pdf.svg') }}',
                    size: '2.4 MB',
                    creator: 'Designer',
                    creatorAvatar: 'https://i.pravatar.cc/32?img=9',
                    createdAt: new Date().toISOString(),
                    recipients: [{
                        id: 1,
                        name: 'John Doe',
                        avatar: 'https://i.pravatar.cc/32?img=5'
                    }],
                    comments: [],
                    isSecret: false,
                    folder: {
                        id: 'folder-desain-login',
                        name: 'Desain Login'
                    }
                }],

                wordFiles: [
                    @for ($i = 0; $i < 15; $i++)
                        {
                            id: 'word-{{ $i }}',
                            name: 'TOR_TermsOfReference_ProyekA.docx',
                            type: 'Word',
                            icon: '{{ asset('images/icons/microsoft-word.svg') }}',
                            isSecret: false
                        },
                    @endfor
                ],

                excelFiles: [
                    @for ($i = 0; $i < 10; $i++)
                        {
                            id: 'excel-{{ $i }}',
                            name: 'Laporan_Keuangan.xlsx',
                            type: 'Excel',
                            icon: '{{ asset('images/icons/excel.svg') }}',
                            isSecret: false
                        },
                    @endfor
                ],
                // Members data untuk modal tambah peserta
                members: [{
                        id: 1,
                        name: 'John Doe',
                        avatar: 'https://i.pravatar.cc/32?img=5',
                        selected: false
                    },
                    {
                        id: 2,
                        name: 'Jane Smith',
                        avatar: 'https://i.pravatar.cc/32?img=6',
                        selected: false
                    },
                    {
                        id: 3,
                        name: 'Robert Johnson',
                        avatar: 'https://i.pravatar.cc/32?img=7',
                        selected: false
                    }
                ],

                // Available workspaces untuk pindah berkas
                availableWorkspaces: [{
                        id: 'tim-it',
                        name: 'TIM IT',
                        description: 'Divisi Teknologi Informasi',
                        color: 'bg-blue-500',
                        folders: [{
                                id: 'folder-it-1',
                                name: 'Dokumen Server'
                            },
                            {
                                id: 'folder-it-2',
                                name: 'Backup Database'
                            }
                        ]
                    },
                    {
                        id: 'proyek-koladi',
                        name: 'PROYEK KOLADI',
                        description: 'Project Management',
                        color: 'bg-green-500',
                        folders: [{
                            id: 'folder-proyek-1',
                            name: 'Dokumen Perencanaan'
                        }]
                    }
                ],

                // Computed property untuk semua dokumen
                get allDocuments() {
                    return [...this.folders, ...this.pdfFiles, ...this.wordFiles, ...this.excelFiles];
                },

                // Computed property untuk file breadcrumbs
                get fileBreadcrumbs() {
                    if (!this.currentFile || !this.currentFile.folderPath) return [];
                    return this.currentFile.folderPath;
                },

                // Computed property untuk filtered members
                filteredMembers() {
                    if (!this.searchMember.trim()) {
                        return this.members;
                    }
                    const query = this.searchMember.toLowerCase();
                    return this.members.filter(member =>
                        member.name.toLowerCase().includes(query)
                    );
                },

                // Computed property untuk dokumen di dalam folder saat ini
                get currentFolderDocuments() {
                    if (!this.currentFolder) return [];
                    return [...this.currentFolder.subFolders, ...this.currentFolder.files];
                },

                // Search Functions
                filterDocuments() {
                    if (this.searchQuery.trim() === '') {
                        this.filteredDocuments = [];
                        return;
                    }

                    const query = this.searchQuery.toLowerCase();

                    if (this.currentFolder) {
                        // Search within current folder
                        const folderResults = this.currentFolder.subFolders.filter(folder =>
                            folder.name.toLowerCase().includes(query)
                        );
                        const fileResults = this.currentFolder.files.filter(file =>
                            file.name.toLowerCase().includes(query) ||
                            file.type.toLowerCase().includes(query)
                        );
                        this.filteredDocuments = [...folderResults, ...fileResults];
                    } else {
                        // Search in all documents
                        this.filteredDocuments = this.allDocuments.filter(doc =>
                            doc.name.toLowerCase().includes(query) ||
                            doc.type.toLowerCase().includes(query)
                        );
                    }
                },

                clearSearch() {
                    this.searchQuery = '';
                    this.filteredDocuments = [];
                },

                // Folder Functions
                createFolder() {
                    if (!this.newFolderName.trim()) return;

                    const newFolder = {
                        id: 'folder-' + Date.now(),
                        name: this.newFolderName.trim(),
                        type: 'Folder',
                        icon: '{{ asset('images/icons/folder.svg') }}',
                        isSecret: this.isSecretFolder,
                        creator: 'Admin User',
                        creatorAvatar: 'https://i.pravatar.cc/32?img=8',
                        createdAt: new Date().toISOString(),
                        recipients: [],
                        subFolders: [],
                        files: []
                    };

                    if (this.currentFolder) {
                        // Buat sub folder di dalam folder saat ini
                        this.currentFolder.subFolders.push(newFolder);
                    } else {
                        // Buat folder di root
                        this.folders.push(newFolder);
                    }

                    // Close modal and reset
                    this.showCreateFolderModal = false;
                    this.newFolderName = '';
                    this.isSecretFolder = false;

                    console.log('Folder created:', newFolder.name, 'Secret:', newFolder.isSecret);
                },

                // File Upload Functions
                uploadFileToFolder(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Determine file type and icon
                    let fileType = 'File';
                    let icon = '{{ asset('images/icons/file.svg') }}';

                    if (file.name.toLowerCase().endsWith('.pdf')) {
                        fileType = 'PDF';
                        icon = '{{ asset('images/icons/pdf.svg') }}';
                    } else if (file.name.toLowerCase().endsWith('.docx') || file.name.toLowerCase().endsWith('.doc')) {
                        fileType = 'Word';
                        icon = '{{ asset('images/icons/microsoft-word.svg') }}';
                    } else if (file.name.toLowerCase().endsWith('.xlsx') || file.name.toLowerCase().endsWith('.xls')) {
                        fileType = 'Excel';
                        icon = '{{ asset('images/icons/excel.svg') }}';
                    }

                    const newFile = {
                        id: 'file-' + Date.now(),
                        name: file.name,
                        type: fileType,
                        icon: icon,
                        file: file,
                        size: this.formatFileSize(file.size),
                        creator: 'Admin User',
                        creatorAvatar: 'https://i.pravatar.cc/32?img=8',
                        createdAt: new Date().toISOString(),
                        recipients: [],
                        comments: [],
                        isSecret: false // Default non-rahasia
                    };

                    // Add to current folder's files jika sedang di dalam folder
                    if (this.currentFolder) {
                        this.currentFolder.files.push(newFile);
                    } else {
                        // Tambahkan ke array yang sesuai berdasarkan tipe file
                        if (fileType === 'PDF') {
                            this.pdfFiles.push(newFile);
                        } else if (fileType === 'Word') {
                            this.wordFiles.push(newFile);
                        } else if (fileType === 'Excel') {
                            this.excelFiles.push(newFile);
                        }
                    }

                    // Reset file input
                    event.target.value = '';

                    const location = this.currentFolder ? `folder "${this.currentFolder.name}"` : 'dokumen utama';
                    console.log('File uploaded:', file.name, 'to:', location);

                    // Tampilkan pesan sukses
                    alert(`File "${file.name}" berhasil diunggah ke ${location}`);
                },

                // Selection Functions
                toggleSelectMode() {
                    this.selectMode = !this.selectMode;
                    if (!this.selectMode) {
                        this.selectedDocuments = [];
                    }
                },

                toggleDocumentSelection(document) {
                    // Jika tidak dalam select mode, izinkan klik untuk folder dan file
                    if (!this.selectMode) {
                        return; // Biarkan event click normal berjalan
                    }

                    const index = this.selectedDocuments.findIndex(doc => doc.id === document.id);
                    if (index > -1) {
                        this.selectedDocuments.splice(index, 1);
                    } else {
                        this.selectedDocuments.push(document);
                    }
                },

                isDocumentSelected(documentId) {
                    return this.selectedDocuments.some(doc => doc.id === documentId);
                },

                cancelSelection() {
                    this.selectedDocuments = [];
                    this.selectMode = false;
                },

                // Workspace Functions untuk pindah dokumen
                confirmMoveDocuments() {
                    if (!this.selectedWorkspace) return;

                    const destination = this.selectedFolder ?
                        `${this.selectedWorkspace.name} - ${this.selectedFolder.name}` :
                        `${this.selectedWorkspace.name} (Dokumen Utama)`;

                    console.log('Memindahkan dokumen:', {
                        documents: this.selectedDocuments,
                        destination: destination,
                        workspace: this.selectedWorkspace,
                        folder: this.selectedFolder
                    });

                    // Hapus dokumen yang dipilih
                    this.selectedDocuments.forEach(selectedDoc => {
                        if (this.currentFolder) {
                            // Jika di dalam folder, hapus dari folder saat ini
                            const subFolderIndex = this.currentFolder.subFolders.findIndex(folder => folder.id ===
                                selectedDoc.id);
                            if (subFolderIndex > -1) {
                                this.currentFolder.subFolders.splice(subFolderIndex, 1);
                            }

                            const fileIndex = this.currentFolder.files.findIndex(file => file.id === selectedDoc
                                .id);
                            if (fileIndex > -1) {
                                this.currentFolder.files.splice(fileIndex, 1);
                            }
                        } else {
                            // Jika di halaman utama, hapus dari arrays utama
                            const folderIndex = this.folders.findIndex(folder => folder.id === selectedDoc.id);
                            if (folderIndex > -1) {
                                this.folders.splice(folderIndex, 1);
                            }

                            const pdfIndex = this.pdfFiles.findIndex(pdf => pdf.id === selectedDoc.id);
                            if (pdfIndex > -1) {
                                this.pdfFiles.splice(pdfIndex, 1);
                            }

                            const wordIndex = this.wordFiles.findIndex(word => word.id === selectedDoc.id);
                            if (wordIndex > -1) {
                                this.wordFiles.splice(wordIndex, 1);
                            }

                            const excelIndex = this.excelFiles.findIndex(excel => excel.id === selectedDoc.id);
                            if (excelIndex > -1) {
                                this.excelFiles.splice(excelIndex, 1);
                            }
                        }
                    });

                    // Tampilkan konfirmasi sukses
                    const locationInfo = this.currentFolder ? `dari "${this.getCurrentFolderPath()}" ` : '';
                    alert(`Berhasil memindahkan ${this.selectedDocuments.length} berkas ${locationInfo}ke ${destination}`);

                    // Reset dan tutup modal
                    this.showMoveDocumentsModal = false;
                    this.selectedWorkspace = null;
                    this.selectedFolder = null;
                    this.cancelSelection();
                },

                // GANTI fungsi-fungsi berikut di dalam script Alpine.js:

                // GANTI fungsi-fungsi berikut di dalam script Alpine.js:

                // Folder Navigation dengan breadcrumb
                openFolder(folder) {
                    // Sembunyikan currentFile ketika membuka folder
                    this.currentFile = null;

                    // Jika sedang di root, reset folderHistory
                    if (!this.currentFolder) {
                        this.folderHistory = [];
                    }
                    // Jika sedang di folder lain, tambahkan current folder ke history
                    else {
                        // Pastikan folder yang sama tidak ditambahkan dua kali
                        const isAlreadyInHistory = this.folderHistory.some(f => f.id === this.currentFolder.id);
                        if (!isAlreadyInHistory) {
                            this.folderHistory.push({
                                ...this.currentFolder
                            });
                        }
                    }

                    this.currentFolder = folder;
                    this.updateBreadcrumbs();
                },

                navigateToFolder(folder) {
                    // Cari index folder yang diklik di breadcrumb
                    const folderIndex = this.breadcrumbs.findIndex(f => f.id === folder.id);

                    if (folderIndex > -1) {
                        // Potong history sampai folder yang diklik
                        this.folderHistory = this.breadcrumbs.slice(0, folderIndex);
                        this.currentFolder = folder;
                        this.updateBreadcrumbs();
                    }
                },

                goToRoot() {
                    this.currentFolder = null;
                    this.folderHistory = [];
                    this.breadcrumbs = [];
                    this.currentFile = null;
                },

                updateBreadcrumbs() {
                    // Breadcrumbs hanya berisi folder history (path menuju current folder)
                    // JANGAN sertakan currentFolder di breadcrumbs
                    this.breadcrumbs = [...this.folderHistory];
                },

                getCurrentFolderPath() {
                    if (!this.currentFolder) return 'Dokumen';

                    const pathParts = ['Dokumen'];

                    // Tambahkan semua breadcrumb
                    if (this.breadcrumbs.length > 0) {
                        pathParts.push(...this.breadcrumbs.map(crumb => crumb.name));
                    }

                    // Tambahkan current folder
                    pathParts.push(this.currentFolder.name);

                    return pathParts.join(' > ');
                },

                getCurrentLocation() {
                    return this.getCurrentFolderPath();
                },


                // Fungsi untuk kembali ke folder dari file
                goBackToFolder() {
                    if (this.currentFile && this.currentFile.folder) {
                        this.currentFolder = this.currentFile.folder;
                        this.currentFile = null;
                        // Restore breadcrumbs dari file
                        this.breadcrumbs = this.fileBreadcrumbs;
                    } else {
                        this.goToRoot();
                    }
                },

                // Fungsi untuk navigasi ke folder dari breadcrumb file
                navigateToFolderFromFile(folder) {
                    this.currentFolder = folder;
                    this.currentFile = null;
                    // Update breadcrumbs berdasarkan folder yang dipilih
                    const folderIndex = this.fileBreadcrumbs.findIndex(f => f.id === folder.id);
                    if (folderIndex > -1) {
                        this.breadcrumbs = this.fileBreadcrumbs.slice(0, folderIndex);
                    }
                },

                // Utility Functions
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                // Member Functions
                toggleSelectAll() {
                    this.members.forEach(member => {
                        member.selected = this.selectAll;
                    });
                },

                saveSelectedMembers() {
                    const selectedMembers = this.members.filter(member => member.selected);
                    console.log('Selected members:', selectedMembers);

                    // Tambahkan anggota yang dipilih ke folder atau file saat ini
                    if (this.currentFolder) {
                        this.currentFolder.recipients = [...this.currentFolder.recipients, ...selectedMembers];
                    } else if (this.currentFile) {
                        this.currentFile.recipients = [...this.currentFile.recipients, ...selectedMembers];
                    }

                    // Reset dan tutup modal
                    this.openAddMemberModal = false;
                    this.searchMember = '';
                    this.selectAll = false;

                    // Tampilkan pesan sukses
                    alert(`Berhasil menambahkan ${selectedMembers.length} peserta`);
                },

                // Folder Edit Functions
                openEditFolder(folder) {
                    this.editingFolder = folder;
                    this.editFolderName = folder.name;
                    this.editIsSecretFolder = folder.isSecret || false;
                    this.showEditFolderModal = true;
                },

                updateFolder() {
                    if (!this.editFolderName.trim()) return;

                    // Update folder di array folders
                    const folderIndex = this.folders.findIndex(f => f.id === this.editingFolder.id);
                    if (folderIndex > -1) {
                        this.folders[folderIndex].name = this.editFolderName.trim();
                        this.folders[folderIndex].isSecret = this.editIsSecretFolder;
                    }

                    // Jika sedang membuka folder yang diedit, update juga currentFolder
                    if (this.currentFolder && this.currentFolder.id === this.editingFolder.id) {
                        this.currentFolder.name = this.editFolderName.trim();
                        this.currentFolder.isSecret = this.editIsSecretFolder;
                    }

                    // Update di breadcrumbs juga
                    const breadcrumbIndex = this.breadcrumbs.findIndex(f => f.id === this.editingFolder.id);
                    if (breadcrumbIndex > -1) {
                        this.breadcrumbs[breadcrumbIndex].name = this.editFolderName.trim();
                        this.breadcrumbs[breadcrumbIndex].isSecret = this.editIsSecretFolder;
                    }

                    console.log('Folder updated:', {
                        name: this.editFolderName,
                        isSecret: this.editIsSecretFolder
                    });

                    // Close modal and reset
                    this.showEditFolderModal = false;
                    this.editFolderName = '';
                    this.editIsSecretFolder = false;
                    this.editingFolder = null;
                },

                // Folder Delete Functions
                openDeleteFolder(folder) {
                    this.deletingFolder = folder;
                    this.showDeleteFolderModal = true;
                },

                confirmDeleteFolder() {
                    if (!this.deletingFolder) return;

                    // Hapus folder dari array folders
                    const folderIndex = this.folders.findIndex(f => f.id === this.deletingFolder.id);
                    if (folderIndex > -1) {
                        this.folders.splice(folderIndex, 1);
                    }

                    // Jika sedang membuka folder yang dihapus, kembali ke halaman utama
                    if (this.currentFolder && this.currentFolder.id === this.deletingFolder.id) {
                        this.goToRoot();
                    }

                    console.log('Folder deleted:', this.deletingFolder.name);

                    // Tampilkan pesan sukses
                    alert(`Folder "${this.deletingFolder.name}" berhasil dihapus`);

                    // Close modal and reset
                    this.showDeleteFolderModal = false;
                    this.deletingFolder = null;
                },

                // Fungsi untuk mendapatkan dokumen yang akan ditampilkan di dalam folder
                getDisplayedDocuments() {
                    if (this.searchQuery && this.filteredDocuments.length > 0) {
                        return this.filteredDocuments;
                    }
                    if (this.currentFolder) {
                        return [...this.currentFolder.subFolders, ...this.currentFolder.files];
                    }
                    return [];
                },

                // GANTI fungsi openFile dengan yang diperbaiki:
                openFile(file) {
                    // Sembunyikan currentFolder ketika membuka file
                    this.currentFolder = null;

                    // Simpan referensi folder asal file
                    const fileFolder = file.folder || this.currentFolder;

                    this.currentFile = {
                        ...file,
                        folder: fileFolder, // Simpan folder asal file
                        folderPath: [...this.breadcrumbs], // Simpan breadcrumb saat ini
                        creator: file.creator || 'Admin User',
                        creatorAvatar: file.creatorAvatar || 'https://i.pravatar.cc/32?img=8',
                        createdAt: file.createdAt || new Date().toISOString(),
                        size: file.size || this.formatFileSize(file.size || 1024 * 1024),
                        recipients: file.recipients || [{
                                id: 1,
                                name: 'John Doe',
                                avatar: 'https://i.pravatar.cc/32?img=5'
                            },
                            {
                                id: 2,
                                name: 'Jane Smith',
                                avatar: 'https://i.pravatar.cc/32?img=6'
                            }
                        ],
                        comments: file.comments || [{
                            id: 1,
                            author: {
                                name: 'Irfan',
                                avatar: 'https://i.pravatar.cc/32?img=9'
                            },
                            content: 'bagi bagi thr',
                            createdAt: new Date('2025-09-22T10:20:00').toISOString(),
                            replies: [{
                                id: 1,
                                author: {
                                    name: 'Farrel',
                                    avatar: 'https://i.pravatar.cc/32?img=10'
                                },
                                content: 'mana nht thr rya',
                                createdAt: new Date().toISOString()
                            }, {
                                id: 2,
                                author: {
                                    name: 'Farrel',
                                    avatar: 'https://i.pravatar.cc/32?img=10'
                                },
                                content: 'mana nht thr rya',
                                createdAt: new Date().toISOString()
                            }]
                        }]
                    };

                    // Simpan breadcrumbs untuk file
                    this.fileBreadcrumbs = [...this.breadcrumbs];
                },

                // Fungsi untuk menambah komentar
                addComment(file, content) {
                    if (!content.trim()) return;

                    const newComment = {
                        id: Date.now(),
                        author: {
                            name: 'Anda',
                            avatar: 'https://i.pravatar.cc/32?img=11'
                        },
                        content: content.trim(),
                        createdAt: new Date().toISOString(),
                        replies: [],
                        showReply: false
                    };

                    if (!file.comments) {
                        file.comments = [];
                    }

                    file.comments.unshift(newComment);

                    console.log('Komentar ditambahkan:', newComment);
                },

                // Fungsi untuk menampilkan form balasan
                showReplyForm(commentId) {
                    const comment = this.currentFile.comments.find(c => c.id === commentId);
                    if (comment) {
                        comment.showReply = !comment.showReply;
                    }
                },

                // Fungsi untuk menambah balasan
                addReply(commentId, content) {
                    if (!content.trim()) return;

                    const comment = this.currentFile.comments.find(c => c.id === commentId);
                    if (comment) {
                        const newReply = {
                            id: Date.now(),
                            author: {
                                name: 'Anda',
                                avatar: 'https://i.pravatar.cc/32?img=11'
                            },
                            content: content.trim(),
                            createdAt: new Date().toISOString()
                        };

                        if (!comment.replies) {
                            comment.replies = [];
                        }

                        comment.replies.push(newReply);
                        comment.showReply = false;

                        console.log('Balasan ditambahkan:', newReply);
                    }
                },

                // GANTI bagian akhir script Anda dengan ini:

                // Fungsi untuk download file
                downloadFile(file) {
                    console.log('Download file:', file.name);
                    // Implementasi download file sesuai kebutuhan
                    alert(`Mengunduh file: ${file.name}`);
                },

                // File Edit Functions
                openEditFile(file) {
                    this.editingFile = file;
                    this.editFileIsSecret = file.isSecret || false;
                    this.showEditFileModal = true;
                },

                updateFile() {
                    if (!this.editingFile) return;

                    // Update file di berbagai lokasi yang mungkin
                    this.updateFileInArrays(this.editingFile, this.editFileIsSecret);

                    // Jika sedang melihat file yang diedit, update juga currentFile
                    if (this.currentFile && this.currentFile.id === this.editingFile.id) {
                        this.currentFile.isSecret = this.editFileIsSecret;
                    }

                    console.log('File updated:', {
                        name: this.editingFile.name,
                        isSecret: this.editFileIsSecret
                    });

                    // Tampilkan pesan sukses
                    const status = this.editFileIsSecret ? 'rahasia' : 'biasa';
                    alert(`File "${this.editingFile.name}" berhasil diubah menjadi file ${status}`);

                    // Close modal and reset
                    this.showEditFileModal = false;
                    this.editFileIsSecret = false;
                    this.editingFile = null;
                },

                // Helper function untuk update file di semua array
                updateFileInArrays(file, isSecret) {
                    // Update di folders (jika file ada di dalam folder)
                    this.folders.forEach(folder => {
                        // Cek di files folder
                        const fileIndex = folder.files.findIndex(f => f.id === file.id);
                        if (fileIndex > -1) {
                            folder.files[fileIndex].isSecret = isSecret;
                        }

                        // Cek di subfolders
                        if (folder.subFolders && folder.subFolders.length > 0) {
                            folder.subFolders.forEach(subFolder => {
                                const subFileIndex = subFolder.files.findIndex(f => f.id === file.id);
                                if (subFileIndex > -1) {
                                    subFolder.files[subFileIndex].isSecret = isSecret;
                                }
                            });
                        }
                    });

                    // Update di pdfFiles
                    const pdfIndex = this.pdfFiles.findIndex(pdf => pdf.id === file.id);
                    if (pdfIndex > -1) {
                        this.pdfFiles[pdfIndex].isSecret = isSecret;
                    }

                    // Update di wordFiles
                    const wordIndex = this.wordFiles.findIndex(word => word.id === file.id);
                    if (wordIndex > -1) {
                        this.wordFiles[wordIndex].isSecret = isSecret;
                    }

                    // Update di excelFiles
                    const excelIndex = this.excelFiles.findIndex(excel => excel.id === file.id);
                    if (excelIndex > -1) {
                        this.excelFiles[excelIndex].isSecret = isSecret;
                    }
                },

                // Fungsi untuk delete file
                openDeleteFile(file) {
                    if (confirm(`Apakah Anda yakin ingin menghapus file "${file.name}"?`)) {
                        console.log('Delete file:', file.name);

                        // Hapus file dari array yang sesuai
                        if (this.currentFolder) {
                            // Hapus dari folder saat ini
                            const fileIndex = this.currentFolder.files.findIndex(f => f.id === file.id);
                            if (fileIndex > -1) {
                                this.currentFolder.files.splice(fileIndex, 1);
                            }
                        } else {
                            // Hapus dari arrays utama
                            const pdfIndex = this.pdfFiles.findIndex(pdf => pdf.id === file.id);
                            if (pdfIndex > -1) {
                                this.pdfFiles.splice(pdfIndex, 1);
                            }

                            const wordIndex = this.wordFiles.findIndex(word => word.id === file.id);
                            if (wordIndex > -1) {
                                this.wordFiles.splice(wordIndex, 1);
                            }

                            const excelIndex = this.excelFiles.findIndex(excel => excel.id === file.id);
                            if (excelIndex > -1) {
                                this.excelFiles.splice(excelIndex, 1);
                            }
                        }

                        this.currentFile = null;

                        // Tampilkan pesan sukses
                        alert(`File "${file.name}" berhasil dihapus`);
                    }
                },

                // Tambahkan fungsi-fungsi ini di dalam script Alpine.js:

                // File Delete Functions
                openDeleteFile(file) {
                    this.deletingFile = file;
                    this.showDeleteFileModal = true;
                },

                confirmDeleteFile() {
                    if (!this.deletingFile) return;

                    console.log('Delete file:', this.deletingFile.name);

                    // Hapus file dari array yang sesuai
                    if (this.currentFolder) {
                        // Hapus dari folder saat ini
                        const fileIndex = this.currentFolder.files.findIndex(f => f.id === this.deletingFile.id);
                        if (fileIndex > -1) {
                            this.currentFolder.files.splice(fileIndex, 1);
                        }
                    } else {
                        // Hapus dari arrays utama
                        const pdfIndex = this.pdfFiles.findIndex(pdf => pdf.id === this.deletingFile.id);
                        if (pdfIndex > -1) {
                            this.pdfFiles.splice(pdfIndex, 1);
                        }

                        const wordIndex = this.wordFiles.findIndex(word => word.id === this.deletingFile.id);
                        if (wordIndex > -1) {
                            this.wordFiles.splice(wordIndex, 1);
                        }

                        const excelIndex = this.excelFiles.findIndex(excel => excel.id === this.deletingFile.id);
                        if (excelIndex > -1) {
                            this.excelFiles.splice(excelIndex, 1);
                        }
                    }

                    // Jika sedang melihat file yang dihapus, kembali ke folder atau halaman utama
                    if (this.currentFile && this.currentFile.id === this.deletingFile.id) {
                        this.currentFile = null;

                        // Jika ada folder sebelumnya, kembali ke folder tersebut
                        if (this.deletingFile.folder) {
                            this.currentFolder = this.deletingFile.folder;
                        } else {
                            this.goToRoot();
                        }
                    }

                    // Tampilkan pesan sukses
                    alert(`File "${this.deletingFile.name}" berhasil dihapus`);

                    // Close modal and reset
                    this.showDeleteFileModal = false;
                    this.deletingFile = null;
                },


                // Tambahkan ke dalam object return di documentSearch() function
                clearCommentEditor() {
                    if (typeof clearCommentEditor === 'function') {
                        clearCommentEditor();
                    }
                },

                submitComment() {
                    if (typeof submitComment === 'function') {
                        submitComment();
                    }
                },

                // Tambahkan fungsi ini di dalam documentSearch() return object
                formatCommentDate(dateString) {
                    if (!dateString) return '';

                    const date = new Date(dateString);
                    const now = new Date();
                    const diffTime = Math.abs(now - date);
                    const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                    const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
                    const diffMinutes = Math.floor(diffTime / (1000 * 60));

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


                // Balasan Komentar Properties
                replyView: {
                    active: false,
                    parentComment: null,
                    replyContent: '',
                    currentFile: null
                },

                // Fungsi untuk membuka halaman balas komentar
                openReplyView(comment) {
                    this.replyView.active = true;
                    this.replyView.parentComment = comment;
                    this.replyView.replyContent = '';
                    this.replyView.currentFile = this.currentFile;
                },

                // Fungsi untuk kembali dari halaman balas komentar
                closeReplyView() {
                    this.replyView.active = false;
                    this.replyView.parentComment = null;
                    this.replyView.replyContent = '';
                    this.replyView.currentFile = null;
                },

                // Fungsi untuk submit balasan komentar
                submitReply() {
                    if (!this.replyView.replyContent.trim() || !this.replyView.parentComment) return;

                    const newReply = {
                        id: Date.now(),
                        author: {
                            name: 'Anda',
                            avatar: 'https://i.pravatar.cc/32?img=11'
                        },
                        content: this.replyView.replyContent.trim(),
                        createdAt: new Date().toISOString()
                    };

                    // Tambahkan balasan ke komentar
                    if (!this.replyView.parentComment.replies) {
                        this.replyView.parentComment.replies = [];
                    }

                    this.replyView.parentComment.replies.push(newReply);

                    console.log('Balasan ditambahkan:', newReply);

                    // Kembali ke halaman komentar
                    this.closeReplyView();
                },

            }
        }
    </script>

@endsection
