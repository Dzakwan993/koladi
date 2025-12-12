{{-- resources/views/components/company-dokumen-modal.blade.php --}}

{{-- 1️⃣ CREATE FOLDER MODAL --}}
<div x-show="showCreateFolderModal" x-cloak
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showCreateFolderModal = false">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800" x-text="currentFolder ? 'Buat Sub Folder' : 'Buat Folder'">
            </h3>
        </div>

        {{-- ✅ COMPANY ROUTE --}}
        <form method="POST" action="{{ route('company-documents.folder.store') }}"
            @submit.prevent="handleCreateFolder($event)">
            @csrf
            <input type="hidden" name="company_id" :value="currentCompanyId || '{{ $company->id }}'">
            <input type="hidden" name="parent_id" :value="currentFolder ? currentFolder.id : null">

            <div class="px-6 py-4">
                <p class="text-sm text-gray-600 mb-4"
                    x-text="currentFolder ? 'Masukkan nama sub folder' : 'Masukkan nama folder'"></p>
                <input type="text" name="name" x-model="newFolderName"
                    :placeholder="currentFolder ? 'Nama sub folder' : 'Nama folder'"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition mb-4"
                    required>

                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <span :class="isSecretFolder ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out" />
                    </button>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                <button type="button" @click="showCreateFolderModal = false; newFolderName = ''; isSecretFolder = false"
                    class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button type="submit" :disabled="!newFolderName.trim()"
                    :class="!newFolderName.trim() ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                    class="px-4 py-2 text-sm text-white rounded-lg transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- 2️⃣ EDIT FOLDER MODAL --}}
<div x-show="showEditFolderModal" x-cloak
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    {{-- ✅ COMPANY ROUTE --}}
    <form method="POST" :action="`/company-documents/folders/${editingFolder.id}/update`"
        @submit.prevent="handleUpdateFolder($event)" @click.outside="showEditFolderModal = false"
        class="bg-white rounded-lg w-full max-w-md">
        @csrf
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Edit Folder</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-sm text-gray-600 mb-4">Masukkan nama folder</p>
            <input type="text" name="name" x-model="editFolderName" placeholder="Nama folder"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition mb-4">
            <div class="flex items-center justify-between py-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Folder Rahasia</p>
                        <p class="text-xs text-gray-500">Hanya yang berhak dapat melihat</p>
                    </div>
                </div>
                <input type="hidden" name="is_private" :value="editIsSecretFolder ? 1 : 0">
                <button type="button" @click="editIsSecretFolder = !editIsSecretFolder"
                    :class="editIsSecretFolder ? 'bg-blue-600' : 'bg-gray-200'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <span :class="editIsSecretFolder ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
                </button>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button type="button" @click="showEditFolderModal = false"
                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">Batal</button>
            <button type="submit"
                :disabled="editFolderName.trim() === originalFolderName.trim() && editIsSecretFolder === originalIsSecretFolder"
                :class="(editFolderName.trim() === originalFolderName.trim() && editIsSecretFolder === originalIsSecretFolder) ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                class="px-4 py-2 text-sm text-white rounded-lg transition">Simpan</button>
        </div>
    </form>
</div>

{{-- 3️⃣ DELETE FOLDER MODAL --}}
<div x-show="showDeleteFolderModal" x-cloak
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    {{-- ✅ COMPANY ROUTE --}}
    <form x-ref="deleteFolderForm" :action="`/company-documents/folders/${deletingFolder.id}/delete`" method="POST"
        class="bg-white rounded-lg w-full max-w-md" @click.outside="showDeleteFolderModal = false">
        @csrf
        @method('DELETE')
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Hapus Folder</h3>
        </div>
        <div class="px-6 py-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
            </div>
            <p class="text-center text-gray-700 font-medium">
                Anda yakin ingin menghapus folder <span class="font-bold" x-text="deletingFolder?.name || ''"></span>?
            </p>
            <p class="text-center text-gray-700 text-sm">(Semua file dan subfolder di dalam akan ikut terhapus)</p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button type="button" @click="showDeleteFolderModal = false"
                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">Batal</button>
            <button type="button"
                @click="showDeleteFolderModal = false; openConfirmModal('Konfirmasi Hapus', 'Apakah Anda benar-benar ingin menghapus folder ini?', () => { $refs.deleteFolderForm.submit(); })"
                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">Ya,
                Hapus</button>
        </div>
    </form>
</div>

{{-- 8️⃣ MOVE DOCUMENTS MODAL (Company → Workspace) --}}
<div x-show="showMoveDocumentsModal" x-cloak @movemodal-open.window="
         console.log('🔥 movemodal-open event received (company context)');
         console.log('📋 currentContext:', currentContext);
         loadAvailableWorkspaces();
     " class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-2xl max-h-[90vh] flex flex-col"
        @click.outside="showMoveDocumentsModal = false">

        {{-- Header Modal --}}
        <div class="px-6 py-4 border-b border-gray-200 flex-shrink-0">
            <h3 class="text-lg font-semibold text-gray-800">Pindahkan Dokumen ke Workspace</h3>
        </div>

        {{-- Content Modal --}}
        <div class="px-6 py-4 space-y-4 overflow-y-auto flex-1">

            {{-- Info Dokumen Terpilih --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-blue-900">
                            <span x-text="selectedDocuments.length"></span> dokumen akan dipindahkan
                        </p>
                        <p class="text-xs text-blue-700 mt-1">
                            Dari: <span class="font-medium">Dokumen Company</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Loading State --}}
            <div x-show="loadingWorkspaces" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-3"></div>
                <p class="text-sm text-gray-600">Memuat workspace...</p>
            </div>

            {{-- Workspace & Folder Selection --}}
            <div x-show="!loadingWorkspaces" class="space-y-4">

                {{-- ========== WORKSPACE DROPDOWN ========== --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Workspace Tujuan <span class="text-red-500">*</span>
                    </label>

                    {{-- Empty State --}}
                    <div x-show="availableWorkspaces.length === 0"
                        class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                        </svg>
                        <p class="text-sm text-gray-600">Tidak ada workspace tersedia</p>
                    </div>

                    {{-- Dropdown Select --}}
                    <div x-show="availableWorkspaces.length > 0" class="relative">
                        <select x-model="selectedWorkspace"
                            @change="selectWorkspaceForMove(availableWorkspaces.find(w => w.id === selectedWorkspace))"
                            class="w-full px-4 py-3 pr-10 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 transition text-sm"
                            style="appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: none;">
                            <option value="" disabled selected>-- Pilih Workspace --</option>
                            <template x-for="workspace in availableWorkspaces" :key="workspace.id">
                                <option :value="workspace.id" x-text="`${workspace.name} (${workspace.type})`"></option>
                            </template>
                        </select>
                        {{-- Dropdown Arrow --}}
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- ========== FOLDER NAVIGATION (jika workspace dipilih) ========== --}}
                <div x-show="selectedWorkspace" x-transition class="space-y-3">

                    <label class="block text-sm font-medium text-gray-700">
                        Pilih Lokasi Tujuan
                    </label>

                    {{-- Breadcrumb Navigation --}}
                    <div x-show="currentModalFolder"
                        class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg border border-gray-200">

                        {{-- Breadcrumbs --}}
                        <div class="flex items-center gap-1 overflow-x-auto flex-1 text-sm">
                            <button @click="goToModalRoot()"
                                class="px-2 py-1 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded transition whitespace-nowrap">
                                Folder Utama
                            </button>

                            <template x-for="(crumb, index) in modalBreadcrumbs" :key="crumb.id">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    <button @click="navigateToModalFolder(crumb)"
                                        :class="index === modalBreadcrumbs.length - 1 ? 'text-blue-600 font-medium' : 'text-gray-600 hover:text-blue-600'"
                                        class="px-2 py-1 hover:bg-blue-50 rounded transition whitespace-nowrap"
                                        x-text="crumb.name">
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Loading Folders --}}
                    <div x-show="loadingModalFolders" class="text-center py-6">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto mb-2"></div>
                        <p class="text-xs text-gray-600">Memuat folder...</p>
                    </div>

                    {{-- Folder & File List --}}
                    <div x-show="!loadingModalFolders && (availableModalFolders.length > 0 || availableModalFiles.length > 0)"
                        class="space-y-2 max-h-[300px] overflow-y-auto border border-gray-200 rounded-lg p-3">

                        {{-- Folders --}}
                        <template x-for="folder in availableModalFolders" :key="folder.id">
                            <button @click="openModalFolder(folder)"
                                class="w-full p-2.5 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 text-left transition-all duration-150 flex items-center gap-2">
                                <svg class="w-4 h-4 flex-shrink-0 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                </svg>
                                <span class="text-sm font-medium truncate" x-text="folder.name"></span>
                            </button>
                        </template>

                        {{-- Files (read-only, tidak bisa diklik) --}}
                        <template x-for="file in availableModalFiles" :key="file.id">
                            <div
                                class="w-full p-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-500 text-left flex items-center gap-2 cursor-not-allowed">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm truncate" x-text="file.name || file.file_name"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Empty State --}}
                    <div x-show="!loadingModalFolders && availableModalFolders.length === 0 && availableModalFiles.length === 0"
                        class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                        </svg>
                        <p class="text-sm text-gray-600">Tidak ada folder di lokasi ini</p>
                    </div>

                </div>

            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center gap-3 flex-shrink-0">
            <button @click="showMoveDocumentsModal = false"
                class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                Batal
            </button>

            <button @click="submitMoveDocuments()" :disabled="!selectedWorkspace || selectedDocuments.length === 0"
                :class="(!selectedWorkspace || selectedDocuments.length === 0) ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                class="px-5 py-2.5 text-sm font-medium text-white rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
                <span>Pindahkan ke Workspace</span>
            </button>
        </div>
    </div>
</div>

{{-- 4️⃣ EDIT FILE MODAL --}}
<div x-show="showEditFileModal" x-cloak
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    {{-- ✅ COMPANY ROUTE --}}
    <form method="POST" :action="`/company-documents/files/${editingFile.id}/update`"
        @submit.prevent="handleUpdateFile($event)" class="bg-white rounded-lg w-full max-w-md"
        @click.outside="showEditFileModal = false">
        @csrf
        @method('PUT')
        {{-- Same content as workspace modal --}}
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Edit File</h3>
        </div>
        <div class="px-6 py-4">
            <template x-if="editingFile?.type === 'Image'">
                <img :src="editingFile.file_url" class="w-full max-h-48 object-contain rounded mb-4 shadow">
            </template>
            <template x-if="editingFile?.type === 'Video'">
                <video controls class="w-full max-h-48 rounded mb-4 shadow">
                    <source :src="editingFile.file_url">
                </video>
            </template>
            <div class="flex items-center gap-3 mb-4 p-3 bg-gray-50 rounded-lg">
                <img :src="editingFile?.icon" :alt="editingFile?.type" class="w-8 h-8">
                <div>
                    <p class="text-sm font-medium text-gray-700" x-text="editingFile?.name"></p>
                    <p class="text-xs text-gray-500" x-text="editingFile?.type"></p>
                </div>
            </div>
            <div class="flex items-center justify-between py-2">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <button type="button" @click="editFileIsSecret = !editFileIsSecret"
                    :class="editFileIsSecret ? 'bg-blue-600' : 'bg-gray-200'"
                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition">
                    <span :class="editFileIsSecret ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition" />
                </button>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button type="button" @click="showEditFileModal = false"
                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">Batal</button>
            <button type="submit" :disabled="editFileIsSecret === originalIsSecretFile"
                :class="editFileIsSecret === originalIsSecretFile ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                class="px-4 py-2 text-sm text-white rounded-lg transition">Simpan</button>
        </div>
    </form>
</div>

{{-- 5️⃣ DELETE FILE MODAL --}}
<div x-show="showDeleteFileModal" x-cloak
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    {{-- ✅ COMPANY ROUTE --}}
    <form x-ref="deleteFileForm" :action="`/company-documents/files/${deletingFile.id}/delete`" method="POST"
        class="bg-white rounded-lg w-full max-w-md" @click.outside="showDeleteFileModal = false">
        @csrf
        @method('DELETE')
        {{-- Same content as workspace modal --}}
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Hapus File</h3>
        </div>
        <div class="px-6 py-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
            </div>
            <p class="text-center text-gray-700 font-medium">Anda yakin ingin menghapus file ini?</p>
            <p class="text-center text-sm text-gray-500 mt-2" x-text="'File: ' + (deletingFile?.name || '')"></p>
            <p class="text-center text-xs text-gray-400 mt-1" x-text="'Tipe: ' + (deletingFile?.type || '')"></p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button type="button" @click="showDeleteFileModal = false"
                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">Batal</button>
            <button type="button"
                @click="showDeleteFileModal = false; openConfirmModal('Konfirmasi Hapus', 'Apakah Anda benar-benar ingin menghapus file ini?', () => { $refs.deleteFileForm.submit(); })"
                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">Ya,
                Hapus</button>
        </div>
    </form>
</div>

{{-- 6️⃣ ADD MEMBERS MODAL --}}
<div x-show="openAddMemberModal" x-cloak
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 p-4" x-transition>
    <div class="bg-white rounded-xl w-full max-w-sm shadow-2xl">
        {{-- ✅ COMPANY ROUTE --}}
        <form method="POST" action="{{ route('company-documents.recipients.store') }}"
            @submit.prevent="handleAddMembers($event)">
            @csrf
            <input type="hidden" name="document_id"
                x-bind:value="currentFolder ? currentFolder.id : (currentFile ? currentFile.id : '')">
            <input type="hidden" name="selected_members"
                x-bind:value="JSON.stringify(members.filter(m => m.selected).map(m => m.id))">
            {{-- Same content as workspace modal --}}
            <div class="px-6 py-4 border-b">
                <h2 class="text-center font-bold text-lg text-gray-800">Tambah Peserta</h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="relative">
                    <input type="text" placeholder="Cari anggota..."
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        x-model="searchMember">
                </div>
                <div class="flex items-center justify-between border-b pb-2">
                    <span class="font-medium text-gray-700 text-sm">Pilih Semua</span>
                    <input type="checkbox" x-model="selectAll" @change="toggleSelectAll">
                </div>
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
            <div class="flex justify-end gap-3 p-4 border-t">
                <button type="button" @click="openAddMemberModal = false"
                    class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">Batal</button>
                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- 7️⃣ CONFIRMATION MODAL (Sama untuk workspace & company) --}}
<div x-show="showConfirmModal" x-cloak
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg w-full max-w-md" @click.outside="showConfirmModal = false">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800" x-text="confirmTitle"></h3>
        </div>
        <div class="px-6 py-6">
            <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
            </div>
            <p class="text-center text-gray-700 font-medium text-sm" x-text="confirmMessage"></p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button @click="showConfirmModal = false"
                class="px-4 py-2 text-sm text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-gray-50 transition">Batal</button>
            <button @click="runConfirmedAction()"
                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">Ya,
                Lanjutkan</button>
        </div>
    </div>
</div>