{{-- Modal Buat Folder --}}
<div x-show="showCreateFolderModal" x-cloak 
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showCreateFolderModal = false">
        {{-- Header Modal --}}
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800"
                x-text="currentFolder ? 'Buat Sub Folder' : 'Buat Folder'"></h3>
        </div>

        {{-- FORM: kirim langsung ke controller --}}
        <form method="POST" action="{{ route('folder.store') }}" @submit="showCreateFolderModal = false">
            @csrf
            <input type="hidden" name="workspace_id" :value="currentWorkspaceId">
            <input type="hidden" name="parent_id" :value="currentFolder ? currentFolder.id : null">

            {{-- Content Modal --}}
            <div class="px-6 py-4">
                <p class="text-sm text-gray-600 mb-4"
                    x-text="currentFolder ? 'Masukkan nama sub folder' : 'Masukkan nama folder'"></p>

                <input type="text" name="name"
                    x-model="newFolderName"
                    :placeholder="currentFolder ? 'Nama sub folder' : 'Nama folder'"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition mb-4"
                    required>

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

                    <input type="hidden" name="is_private" :value="isSecretFolder ? 1 : 0">
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
                <button type="button"
                    @click="showCreateFolderModal = false; newFolderName = ''; isSecretFolder = false"
                    class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                    Batal
                </button>

                <button type="submit"
                    :disabled="!newFolderName.trim()"
                    :class="!newFolderName.trim() ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                    class="px-4 py-2 text-sm text-white rounded-lg transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

   


        {{-- Modal Pindah Berkas --}}
        <div x-show="showMoveDocumentsModal" x-cloak 
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

                    <!-- FORM MULAI -->
                    <form method="POST" :action="`/folders/${editingFolder.id}/update`"
                        @click.outside="showEditFolderModal = false"
                        class="bg-white rounded-lg w-full max-w-md">

                        @csrf

                        {{-- Header Modal --}}
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Edit Folder</h3>
                        </div>

                        {{-- Content Modal --}}
                        <div class="px-6 py-4">

                            <p class="text-sm text-gray-600 mb-4">Masukkan nama folder</p>

                            <input type="text" name="name" x-model="editFolderName"
                                placeholder="Nama folder"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 
                                focus:ring-blue-500 focus:border-blue-500 transition mb-4">

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

                                <!-- Hidden for backend -->
                                <input type="hidden" name="is_private" :value="editIsSecretFolder ? 1 : 0">

                                <button type="button"
                                    @click="editIsSecretFolder = !editIsSecretFolder"
                                    :class="editIsSecretFolder ? 'bg-blue-600' : 'bg-gray-200'"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full 
                                    border-2 border-transparent transition-colors duration-200 ease-in-out 
                                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">

                                    <span class="sr-only">Folder Rahasia</span>

                                    <span :class="editIsSecretFolder ? 'translate-x-5' : 'translate-x-0'"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full 
                                        bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                                </button>

                            </div>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">

                            <button type="button"
                                @click="showEditFolderModal = false"
                                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 
                                rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>

                            <button type="submit"
                                :disabled="editFolderName.trim() === originalFolderName.trim() 
                                            && editIsSecretFolder === originalIsSecretFolder"
                                :class="(editFolderName.trim() === originalFolderName.trim() 
                                        && editIsSecretFolder === originalIsSecretFolder)
                                    ? 'bg-gray-300 cursor-not-allowed' 
                                    : 'bg-blue-600 hover:bg-blue-700'"
                                class="px-4 py-2 text-sm text-white rounded-lg transition">
                                Simpan
                            </button>


                        </div>

                    </form>
                    <!-- FORM END -->
                </div>


                <!-- Modal Hapus Folder -->
                <div x-show="showDeleteFolderModal" x-cloak
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">

                    <form x-ref="deleteFolderForm"
                        :action="`/folders/${deletingFolder.id}/delete`"
                        method="POST"
                        class="bg-white rounded-lg w-full max-w-md"
                        @click.outside="showDeleteFolderModal = false">

                        @csrf
                        @method('DELETE')

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

                            <p class="text-center text-gray-700 font-medium">
                                Anda yakin ingin menghapus folder 
                                <span class="font-bold" x-text="deletingFolder?.name || ''"></span>?
                            </p>
                            <p class="text-center text-gray-700 text-sm">(Semua file dan subfolder di dalam akan ikut terhapus)</p>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">

                            <button type="button"
                                @click="showDeleteFolderModal = false"
                                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>

                            <button type="button"
                                @click="
                                        showDeleteFolderModal = false;
                                        openConfirmModal(
                                            'Konfirmasi Hapus',
                                            'Apakah Anda benar-benar ingin menghapus folder ini?',
                                            () => { $refs.deleteFolderForm.submit(); }
                                        )"
                                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Ya, Hapus
                            </button>

                        </div>

                    </form>
                </div>






                 <!-- Modal Hapus File -->
                <div x-show="showDeleteFileModal" x-cloak
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">

                    <form x-ref="deleteFileForm"
                        :action="`/files/${deletingFile.id}/delete`" 
                        method="POST"
                        class="bg-white rounded-lg w-full max-w-md"
                        @click.outside="showDeleteFileModal = false">

                        @csrf
                        @method('DELETE')

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
                            <button type="button"
                                @click="showDeleteFileModal = false"
                                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>

                            <button type="button"
                                    @click="
                                        showDeleteFileModal = false;
                                        openConfirmModal(
                                            'Konfirmasi Hapus',
                                            'Apakah Anda benar-benar ingin menghapus file ini?',
                                            () => { $refs.deleteFileForm.submit(); }
                                        )
                                    "
                                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                Ya, Hapus
                            </button>
                        </div>

                    </form>
                </div>





                {{-- Modal Konfirmasi Universal --}}
                    <div x-show="showConfirmModal" x-cloak
                        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">

                        <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showConfirmModal = false">

                            {{-- Header --}}
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-800" x-text="confirmTitle"></h3>
                            </div>

                            {{-- Content --}}
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

                                <p class="text-center text-gray-700 font-medium text-sm" x-text="confirmMessage"></p>
                                
                            </div>

                            {{-- Footer --}}
                            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                                <button @click="showConfirmModal = false"
                                    class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                                    Batal
                                </button>

                                <button @click="runConfirmedAction()"
                                    class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                    Ya, Lanjutkan
                                </button>
                            </div>
                        </div>
                    </div>



                <!-- Modal Edit File -->
                <div x-show="showEditFileModal" x-cloak
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">

                    <form method="POST" :action="`/files/${editingFile.id}/update`"
                        class="bg-white rounded-lg w-full max-w-md"
                        @click.outside="showEditFileModal = false">
                        
                        @csrf
                        @method('PUT')

                        {{-- Header Modal --}}
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Edit File</h3>
                        </div>

                        {{-- Content Modal --}}
                        <div class="px-6 py-4">

                            <!-- PREVIEW GAMBAR / VIDEO -->
                            <template x-if="editingFile?.type === 'Image'">
                                <img :src="editingFile.file_url"
                                    class="w-full max-h-48 object-contain rounded mb-4 shadow">
                            </template>

                            <template x-if="editingFile?.type === 'Video'">
                                <video controls class="w-full max-h-48 rounded mb-4 shadow">
                                    <source :src="editingFile.file_url">
                                </video>
                            </template>

                            {{-- Info File (icon + nama) --}}
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

                                <input type="hidden" name="is_private" :value="editFileIsSecret ? 1 : 0">

                                <button type="button"
                                    @click="editFileIsSecret = !editFileIsSecret"
                                    :class="editFileIsSecret ? 'bg-blue-600' : 'bg-gray-200'"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition">
                                    <span :class="editFileIsSecret ? 'translate-x-5' : 'translate-x-0'"
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition" />
                                </button>
                            </div>
                        </div>

                        {{-- Footer Modal --}}
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                            <button type="button"
                                @click="showEditFileModal = false"
                                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>

                            <button type="submit"
                                :disabled="editFileIsSecret === originalIsSecretFile"
                                :class="editFileIsSecret === originalIsSecretFile
                                    ? 'bg-gray-300 cursor-not-allowed'
                                    : 'bg-blue-600 hover:bg-blue-700'"
                                class="px-4 py-2 text-sm text-white rounded-lg transition">
                                Simpan
                            </button>
                        </div>

                    </form>
                </div>
