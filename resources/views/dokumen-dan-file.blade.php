@extends('layouts.app')

@section('title', 'Dokumen dan File')

@section('content')
    <div x-data="documentSearch()" x-init="$store.workspace = { selectedMenu: 'dokumen' }" class="bg-[#f3f6fc] min-h-screen">

        {{-- Workspace Navigation --}}
        @include('components.workspace-nav', ['active' => 'dokumen'])

        {{-- All Modals --}}
        @include('components.dokumen-modal')

        {{-- Konten Halaman --}}
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto">
            {{-- Container dengan border dan padding --}}
            <div
                class="border border-gray-200 rounded-lg bg-white p-4 sm:p-6 flex flex-col h-[calc(100vh-140px)] sm:h-[calc(100vh-160px)] lg:h-[calc(100vh-200px)]">

                {{-- Header dengan Pencarian dan Tombol Aksi --}}
                @include('components.dokumen-header')

                {{-- Breadcrumb dan Info Folder --}}
                @include('components.dokumen-breadcrumb')

                {{-- Grid Dokumen di Dalam Folder --}}
                @include('components.dokumen-grid')

                {{-- Hasil Pencarian Info --}}
                @include('components.dokumen-search-info')

                {{-- Header Pilihan (muncul saat select mode) --}}
                @include('components.dokumen-selection-header')

                {{-- Breadcrumb dan Info File --}}
                @include('components.dokumen-file-header')

                {{-- Konten File dan Komentar --}}
                @include('components.dokumen-file-content')

                {{-- Halaman Balas Komentar --}}
                @include('components.balas-komentar')

                {{-- Grid Dokumen Utama (Scrollable) --}}
                @include('components.dokumen-main-grid')

                {{-- Default View (ketika tidak ada pencarian dan tidak di dalam folder) --}}
                @include('components.dokumen-default-view')

            </div>
        </div>
    </div>

    {{-- Include CSS and Scripts --}}
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

    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
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
