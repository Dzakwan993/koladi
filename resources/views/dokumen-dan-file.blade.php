@extends('layouts.app')

@section('title', 'Dokumen dan File')

@section('content')
<div x-data="documentSearch()" 
         x-init="$store.workspace = { selectedMenu: 'dokumen' };
            // Inisialisasi data dari backend
            initData(@js($folders), @js($rootFiles));"  class="bg-[#f3f6fc] min-h-screen">

        {{-- Workspace Navigation --}}
        @include('components.workspace-nav', ['active' => 'dokumen'])

        {{-- All Modals --}}
        @include('components.dokumen-modal')

        {{-- Konten Halaman --}}
        <div class="px-4 sm:px-6 lg:px-8 py-4 sm:py-6 max-w-7xl mx-auto">
            <div class="border border-gray-200 rounded-lg bg-white p-4 sm:p-6 flex flex-col h-[calc(100vh-140px)] sm:h-[calc(100vh-160px)] lg:h-[calc(100vh-200px)]">

                {{-- Komponen-komponen --}}
                @include('components.dokumen-header')
                @include('components.dokumen-breadcrumb')
                @include('components.dokumen-grid')
                @include('components.dokumen-search-info')
                @include('components.dokumen-selection-header')
                @include('components.dokumen-file-header')
                @include('components.dokumen-file-content')
                @include('components.balas-komentar')
                @include('components.dokumen-main-grid')
                @include('components.dokumen-default-view')

            </div>
        </div>
    </div>

    {{-- Alpine Component Script (Sederhana) --}}
    <script>
        function documentSearch() {
            return {
                // State Properties
                searchQuery: '',
                filteredDocuments: [],
                showCreateFolderModal: false,
                showMoveDocumentsModal: false,
                showEditFolderModal: false,
                showDeleteFolderModal: false,
                openAddMemberModal: false,
                newFolderName: '',
                isSecretFolder: false,
                editFolderName: '',
                editIsSecretFolder: false,
                currentFile: null,
                selectMode: false,
                selectedDocuments: [],
                selectedWorkspace: null,
                selectedFolder: null,
                currentFolder: null,
                folderHistory: [],
                breadcrumbs: [],
                editingFolder: null,
                deletingFolder: null,
                showDeleteFileModal: false,
                deletingFile: null,
                showEditFileModal: false,
                editingFile: null,
                editFileIsSecret: false,
                searchMember: '',
                selectAll: false,

                // Data akan diisi dari backend/API
                folders: [],
                pdfFiles: [],
                wordFiles: [],
                excelFiles: [],
                members: [],
                availableWorkspaces: [],
                backendFolders: [],
                backendRootFiles: [],

                // Computed Properties
                get allDocuments() {
                    return [...this.backendFolders, ...this.backendRootFiles];
                },

                // Function untuk inisialisasi data
                initData(foldersData, rootFilesData) {
                    // Simpan data dari backend
                    this.backendFolders = foldersData;
                    this.backendRootFiles = rootFilesData;
                    
                    // Convert data Laravel Collection ke format yang diharapkan Alpine
                    this.processBackendData();
                },

                processBackendData() {
                    // Process folders
                    this.folders = this.backendFolders.map(folder => ({
                        id: folder.id,
                        name: folder.name,
                        type: 'Folder',
                        icon: this.getFolderIcon(),
                        isSecret: folder.is_private || false,
                        creator: {
                            name: folder.creator?.name || 'Unknown',
                            avatar: folder.creator?.avatar || 'https://i.pravatar.cc/32?img=8'
                        },
                        createdAt: folder.created_at,
                        recipients: [],
                        subFolders: [], // Anda perlu menyesuaikan jika ada nested folders
                        files: folder.files ? this.processFiles(folder.files) : [],
                        filesCount: folder.files_count || 0
                    }));

                    // Process root files
                    this.processRootFiles();
                },

                processFiles(files) {
                    return files.map(file => ({
                        id: file.id,
                        name: file.name || file.file_name,
                        type: this.getFileType(file.name || file.file_name),
                        icon: this.getFileIcon(this.getFileType(file.name || file.file_name)),
                        size: this.formatFileSize(file.size || 0),
                        creator: {
                            name: file.uploader?.name || 'Unknown',
                            avatar: file.uploader?.avatar || 'https://i.pravatar.cc/32?img=8'
                        },
                        createdAt: file.created_at || file.uploaded_at,
                        isSecret: file.is_private || false,
                        // Tambahan properti untuk kompatibilitas
                        comments: file.comments || [],
                        recipients: []
                    }));
                },

                processRootFiles() {
                    const processedFiles = this.processFiles(this.backendRootFiles);
                    
                    // Group by type untuk kompatibilitas dengan kode existing
                    this.pdfFiles = processedFiles.filter(file => file.type === 'PDF');
                    this.wordFiles = processedFiles.filter(file => file.type === 'Word');
                    this.excelFiles = processedFiles.filter(file => file.type === 'Excel');
                },

                get fileBreadcrumbs() {
                    if (!this.currentFile || !this.currentFile.folderPath) return [];
                    return this.currentFile.folderPath;
                },

                filteredMembers() {
                    if (!this.searchMember.trim()) return this.members;
                    const query = this.searchMember.toLowerCase();
                    return this.members.filter(member => member.name.toLowerCase().includes(query));
                },

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
                        const folderResults = this.currentFolder.subFolders.filter(folder =>
                            folder.name.toLowerCase().includes(query)
                        );
                        const fileResults = this.currentFolder.files.filter(file =>
                            file.name.toLowerCase().includes(query) || file.type.toLowerCase().includes(query)
                        );
                        this.filteredDocuments = [...folderResults, ...fileResults];
                    } else {
                        this.filteredDocuments = this.allDocuments.filter(doc =>
                            doc.name.toLowerCase().includes(query) || doc.type.toLowerCase().includes(query)
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
                        icon: this.getFolderIcon(),
                        isSecret: this.isSecretFolder,
                        creator: this.getCurrentUser(),
                        createdAt: new Date().toISOString(),
                        recipients: [],
                        subFolders: [],
                        files: []
                    };

                    if (this.currentFolder) {
                        this.currentFolder.subFolders.push(newFolder);
                    } else {
                        this.folders.push(newFolder);
                    }

                    this.showCreateFolderModal = false;
                    this.newFolderName = '';
                    this.isSecretFolder = false;
                },

                // File Upload Functions
                uploadFileToFolder(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const fileType = this.getFileType(file.name);
                    const icon = this.getFileIcon(fileType);

                    const newFile = {
                        id: 'file-' + Date.now(),
                        name: file.name,
                        type: fileType,
                        icon: icon,
                        file: file,
                        size: this.formatFileSize(file.size),
                        creator: this.getCurrentUser(),
                        createdAt: new Date().toISOString(),
                        recipients: [],
                        comments: [],
                        isSecret: false
                    };

                    if (this.currentFolder) {
                        this.currentFolder.files.push(newFile);
                    } else {
                        this.addFileToCollection(newFile, fileType);
                    }

                    event.target.value = '';
                    this.showSuccessMessage(`File "${file.name}" berhasil diunggah`);
                },

                // Selection Functions
                toggleSelectMode() {
                    this.selectMode = !this.selectMode;
                    if (!this.selectMode) this.selectedDocuments = [];
                },

                toggleDocumentSelection(document) {
                    if (!this.selectMode) return;

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

                // Workspace Functions
                confirmMoveDocuments() {
                    if (!this.selectedWorkspace) return;

                    const destination = this.selectedFolder ?
                        `${this.selectedWorkspace.name} - ${this.selectedFolder.name}` :
                        `${this.selectedWorkspace.name} (Dokumen Utama)`;

                    this.moveSelectedDocuments();
                    this.showSuccessMessage(`Berhasil memindahkan ${this.selectedDocuments.length} berkas ke ${destination}`);

                    this.showMoveDocumentsModal = false;
                    this.selectedWorkspace = null;
                    this.selectedFolder = null;
                    this.cancelSelection();
                },

                // Folder Navigation
                openFolder(folder) {
                    this.currentFile = null;

                    if (!this.currentFolder) {
                        this.folderHistory = [];
                    } else {
                        const isAlreadyInHistory = this.folderHistory.some(f => f.id === this.currentFolder.id);
                        if (!isAlreadyInHistory) {
                            this.folderHistory.push({ ...this.currentFolder });
                        }
                    }

                    this.currentFolder = folder;
                    this.updateBreadcrumbs();
                },

                navigateToFolder(folder) {
                    const folderIndex = this.breadcrumbs.findIndex(f => f.id === folder.id);
                    if (folderIndex > -1) {
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
                    this.breadcrumbs = [...this.folderHistory];
                },

                getCurrentFolderPath() {
                    if (!this.currentFolder) return 'Dokumen';

                    const pathParts = ['Dokumen'];
                    if (this.breadcrumbs.length > 0) {
                        pathParts.push(...this.breadcrumbs.map(crumb => crumb.name));
                    }
                    pathParts.push(this.currentFolder.name);

                    return pathParts.join(' > ');
                },

                getCurrentLocation() {
                    return this.getCurrentFolderPath();
                },

                // File Navigation
                goBackToFolder() {
                    if (this.currentFile && this.currentFile.folder) {
                        this.currentFolder = this.currentFile.folder;
                        this.currentFile = null;
                        this.breadcrumbs = this.fileBreadcrumbs;
                    } else {
                        this.goToRoot();
                    }
                },

                navigateToFolderFromFile(folder) {
                    this.currentFolder = folder;
                    this.currentFile = null;
                    const folderIndex = this.fileBreadcrumbs.findIndex(f => f.id === folder.id);
                    if (folderIndex > -1) {
                        this.breadcrumbs = this.fileBreadcrumbs.slice(0, folderIndex);
                    }
                },

                openFile(file) {
                    this.currentFolder = null;
                    const fileFolder = file.folder || this.currentFolder;

                    this.currentFile = {
                        ...file,
                        folder: fileFolder,
                        folderPath: [...this.breadcrumbs],
                        creator: file.creator || this.getCurrentUser(),
                        createdAt: file.createdAt || new Date().toISOString(),
                        size: file.size || this.formatFileSize(file.size || 1024 * 1024),
                        recipients: file.recipients || this.getDefaultRecipients(),
                        comments: file.comments || this.getDefaultComments()
                    };

                    this.fileBreadcrumbs = [...this.breadcrumbs];
                },

                // Comment Functions
                addComment(file, content) {
                    if (!content.trim()) return;

                    const newComment = {
                        id: Date.now(),
                        author: this.getCurrentUser(),
                        content: content.trim(),
                        createdAt: new Date().toISOString(),
                        replies: []
                    };

                    if (!file.comments) file.comments = [];
                    file.comments.unshift(newComment);
                },

                showReplyForm(commentId) {
                    const comment = this.currentFile.comments.find(c => c.id === commentId);
                    if (comment) {
                        comment.showReply = !comment.showReply;
                    }
                },

                addReply(commentId, content) {
                    if (!content.trim()) return;

                    const comment = this.currentFile.comments.find(c => c.id === commentId);
                    if (comment) {
                        const newReply = {
                            id: Date.now(),
                            author: this.getCurrentUser(),
                            content: content.trim(),
                            createdAt: new Date().toISOString()
                        };

                        if (!comment.replies) comment.replies = [];
                        comment.replies.push(newReply);
                        comment.showReply = false;
                    }
                },

                // File Operations
                downloadFile(file) {
                    console.log('Download file:', file.name);
                    // Implement download logic
                },

                openEditFile(file) {
                    this.editingFile = file;
                    this.editFileIsSecret = file.isSecret || false;
                    this.showEditFileModal = true;
                },

                updateFile() {
                    if (!this.editingFile) return;

                    this.updateFileInArrays(this.editingFile, this.editFileIsSecret);

                    if (this.currentFile && this.currentFile.id === this.editingFile.id) {
                        this.currentFile.isSecret = this.editFileIsSecret;
                    }

                    this.showEditFileModal = false;
                    this.editFileIsSecret = false;
                    this.editingFile = null;
                },

                openDeleteFile(file) {
                    if (confirm(`Apakah Anda yakin ingin menghapus file "${file.name}"?`)) {
                        this.deleteFile(file);
                        this.showSuccessMessage(`File "${file.name}" berhasil dihapus`);
                    }
                },

                openDeleteFileModal(file) {
                    this.deletingFile = file;
                    this.showDeleteFileModal = true;
                },

                confirmDeleteFile() {
                    if (!this.deletingFile) return;

                    this.deleteFile(this.deletingFile);
                    this.showSuccessMessage(`File "${this.deletingFile.name}" berhasil dihapus`);

                    this.showDeleteFileModal = false;
                    this.deletingFile = null;
                },

                // Folder Operations
                openEditFolder(folder) {
                    this.editingFolder = folder;
                    this.editFolderName = folder.name;
                    this.editIsSecretFolder = folder.isSecret || false;
                    this.showEditFolderModal = true;
                },

                updateFolder() {
                    if (!this.editFolderName.trim()) return;

                    this.updateFolderInArrays(this.editingFolder, this.editFolderName, this.editIsSecretFolder);

                    this.showEditFolderModal = false;
                    this.editFolderName = '';
                    this.editIsSecretFolder = false;
                    this.editingFolder = null;
                },

                openDeleteFolder(folder) {
                    this.deletingFolder = folder;
                    this.showDeleteFolderModal = true;
                },

                confirmDeleteFolder() {
                    if (!this.deletingFolder) return;

                    this.deleteFolder(this.deletingFolder);
                    this.showSuccessMessage(`Folder "${this.deletingFolder.name}" berhasil dihapus`);

                    this.showDeleteFolderModal = false;
                    this.deletingFolder = null;
                },

                // Member Functions
                toggleSelectAll() {
                    this.members.forEach(member => {
                        member.selected = this.selectAll;
                    });
                },

                saveSelectedMembers() {
                    const selectedMembers = this.members.filter(member => member.selected);
                    
                    if (this.currentFolder) {
                        this.currentFolder.recipients = [...this.currentFolder.recipients, ...selectedMembers];
                    } else if (this.currentFile) {
                        this.currentFile.recipients = [...this.currentFile.recipients, ...selectedMembers];
                    }

                    this.openAddMemberModal = false;
                    this.searchMember = '';
                    this.selectAll = false;
                    this.showSuccessMessage(`Berhasil menambahkan ${selectedMembers.length} peserta`);
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

                // Helper Functions
                getDisplayedDocuments() {
                    if (this.searchQuery && this.filteredDocuments.length > 0) {
                        return this.filteredDocuments;
                    }
                    if (this.currentFolder) {
                        return [...this.currentFolder.subFolders, ...this.currentFolder.files];
                    }
                    return [];
                },

                getFileType(filename) {
                    if (filename.toLowerCase().endsWith('.pdf')) return 'PDF';
                    if (filename.toLowerCase().endsWith('.docx') || filename.toLowerCase().endsWith('.doc')) return 'Word';
                    if (filename.toLowerCase().endsWith('.xlsx') || filename.toLowerCase().endsWith('.xls')) return 'Excel';
                    return 'File';
                },

                getFileIcon(fileType) {
                    const icons = {
                        'PDF': '{{ asset('images/icons/pdf.svg') }}',
                        'Word': '{{ asset('images/icons/microsoft-word.svg') }}',
                        'Excel': '{{ asset('images/icons/excel.svg') }}',
                        'File': '{{ asset('images/icons/file.svg') }}'
                    };
                    return icons[fileType] || icons['File'];
                },

                getFolderIcon() {
                    return '{{ asset('images/icons/folder.svg') }}';
                },

                getCurrentUser() {
                    // Ganti dengan data user dari backend
                    return {
                        name: 'Admin User',
                        avatar: 'https://i.pravatar.cc/32?img=8'
                    };
                },

                getDefaultRecipients() {
                    // Ganti dengan data default dari backend
                    return [
                        { id: 1, name: 'John Doe', avatar: 'https://i.pravatar.cc/32?img=5' },
                        { id: 2, name: 'Jane Smith', avatar: 'https://i.pravatar.cc/32?img=6' }
                    ];
                },

                getDefaultComments() {
                    // Ganti dengan data default dari backend
                    return [{
                        id: 1,
                        author: { name: 'Irfan', avatar: 'https://i.pravatar.cc/32?img=9' },
                        content: 'bagi bagi thr',
                        createdAt: new Date('2025-09-22T10:20:00').toISOString(),
                        replies: [
                            {
                                id: 1,
                                author: { name: 'Farrel', avatar: 'https://i.pravatar.cc/32?img=10' },
                                content: 'mana nht thr rya',
                                createdAt: new Date().toISOString()
                            }
                        ]
                    }];
                },

                addFileToCollection(file, fileType) {
                    const collections = {
                        'PDF': this.pdfFiles,
                        'Word': this.wordFiles,
                        'Excel': this.excelFiles
                    };
                    const collection = collections[fileType];
                    if (collection) collection.push(file);
                },

                updateFileInArrays(file, isSecret) {
                    // Update file in all possible locations
                    [this.folders, this.pdfFiles, this.wordFiles, this.excelFiles].forEach(collection => {
                        this.updateFileInCollection(collection, file.id, isSecret);
                    });
                },

                updateFileInCollection(collection, fileId, isSecret) {
                    if (Array.isArray(collection)) {
                        collection.forEach(item => {
                            if (item.id === fileId) {
                                item.isSecret = isSecret;
                            }
                            if (item.files) {
                                this.updateFileInCollection(item.files, fileId, isSecret);
                            }
                            if (item.subFolders) {
                                this.updateFileInCollection(item.subFolders, fileId, isSecret);
                            }
                        });
                    }
                },

                updateFolderInArrays(folder, newName, isSecret) {
                    const folderIndex = this.folders.findIndex(f => f.id === folder.id);
                    if (folderIndex > -1) {
                        this.folders[folderIndex].name = newName.trim();
                        this.folders[folderIndex].isSecret = isSecret;
                    }

                    if (this.currentFolder && this.currentFolder.id === folder.id) {
                        this.currentFolder.name = newName.trim();
                        this.currentFolder.isSecret = isSecret;
                    }

                    const breadcrumbIndex = this.breadcrumbs.findIndex(f => f.id === folder.id);
                    if (breadcrumbIndex > -1) {
                        this.breadcrumbs[breadcrumbIndex].name = newName.trim();
                        this.breadcrumbs[breadcrumbIndex].isSecret = isSecret;
                    }
                },

                deleteFile(file) {
                    if (this.currentFolder) {
                        const fileIndex = this.currentFolder.files.findIndex(f => f.id === file.id);
                        if (fileIndex > -1) this.currentFolder.files.splice(fileIndex, 1);
                    } else {
                        this.removeFileFromCollection(this.pdfFiles, file.id);
                        this.removeFileFromCollection(this.wordFiles, file.id);
                        this.removeFileFromCollection(this.excelFiles, file.id);
                    }

                    if (this.currentFile && this.currentFile.id === file.id) {
                        this.currentFile = null;
                        if (file.folder) {
                            this.currentFolder = file.folder;
                        } else {
                            this.goToRoot();
                        }
                    }
                },

                removeFileFromCollection(collection, fileId) {
                    const index = collection.findIndex(item => item.id === fileId);
                    if (index > -1) collection.splice(index, 1);
                },

                deleteFolder(folder) {
                    const folderIndex = this.folders.findIndex(f => f.id === folder.id);
                    if (folderIndex > -1) this.folders.splice(folderIndex, 1);

                    if (this.currentFolder && this.currentFolder.id === folder.id) {
                        this.goToRoot();
                    }
                },

                moveSelectedDocuments() {
                    this.selectedDocuments.forEach(selectedDoc => {
                        if (this.currentFolder) {
                            this.removeFromCurrentFolder(selectedDoc);
                        } else {
                            this.removeFromRoot(selectedDoc);
                        }
                    });
                },

                removeFromCurrentFolder(doc) {
                    const subFolderIndex = this.currentFolder.subFolders.findIndex(folder => folder.id === doc.id);
                    if (subFolderIndex > -1) this.currentFolder.subFolders.splice(subFolderIndex, 1);

                    const fileIndex = this.currentFolder.files.findIndex(file => file.id === doc.id);
                    if (fileIndex > -1) this.currentFolder.files.splice(fileIndex, 1);
                },

                removeFromRoot(doc) {
                    this.removeFileFromCollection(this.folders, doc.id);
                    this.removeFileFromCollection(this.pdfFiles, doc.id);
                    this.removeFileFromCollection(this.wordFiles, doc.id);
                    this.removeFileFromCollection(this.excelFiles, doc.id);
                },

                showSuccessMessage(message) {
                    alert(message); // Bisa diganti dengan notifikasi yang lebih baik
                },

                // Reply View Functions
                replyView: {
                    active: false,
                    parentComment: null,
                    replyContent: '',
                    currentFile: null
                },

                openReplyView(comment) {
                    this.replyView.active = true;
                    this.replyView.parentComment = comment;
                    this.replyView.replyContent = '';
                    this.replyView.currentFile = this.currentFile;
                },

                closeReplyView() {
                    this.replyView.active = false;
                    this.replyView.parentComment = null;
                    this.replyView.replyContent = '';
                    this.replyView.currentFile = null;
                },

                submitReply() {
                    if (!this.replyView.replyContent.trim() || !this.replyView.parentComment) return;

                    const newReply = {
                        id: Date.now(),
                        author: this.getCurrentUser(),
                        content: this.replyView.replyContent.trim(),
                        createdAt: new Date().toISOString()
                    };

                    if (!this.replyView.parentComment.replies) {
                        this.replyView.parentComment.replies = [];
                    }

                    this.replyView.parentComment.replies.push(newReply);
                    this.closeReplyView();
                }
            };
        }
    </script>

    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    @vite('resources/js/dokumen-script.js')
@endsection