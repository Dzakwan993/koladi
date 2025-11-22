console.log('✅ dokumen-script.js loaded');

// ===== DOKUMEN SEARCH FUNCTIONS =====
export default function documentSearch() {
            return {
                // State Properties
                searchQuery: '',
                filteredDocuments: [],
                showCreateFolderModal: false,
                showMoveDocumentsModal: false,
                showEditFolderModal: false,
                showDeleteFolderModal: false,
                openAddMemberModal: false,
                currentWorkspaceId: null,
                currentWorkspace: null,
                newFolderName: '',
                isSecretFolder: false,
                editFolderName: '',
                editIsSecretFolder: false,
                originalIsSecretFolder: null,
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
                originalIsSecretFile: null,
                searchMember: '',
                selectAll: false,
                originalFolderName: '',
                showConfirmModal: false,
                confirmTitle: '',
                confirmMessage: '',
                onConfirmAction: null,

                // Data akan diisi dari backend/API
                folders: [],
                pdfFiles: [],
                wordFiles: [],
                excelFiles: [],
                members: [],
                availableWorkspaces: [],
                backendFolders: [],
                backendRootFiles: [],

                // Mengembalikan seluruh data file
                get allFiles() {
                    return [
                        ...this.pdfFiles,
                        ...this.wordFiles,
                        ...this.excelFiles,
                        ...this.powerPointFiles,
                        ...this.textFiles,
                        ...this.imageFiles,
                        ...this.zipFiles,
                        ...this.videoFiles,
                        ...this.audioFiles,
                        ...this.codeFiles,
                        ...this.unknownFiles,
                    ].filter(file => file.folder_id === null);
                },                

                // Function untuk inisialisasi data
                initData(foldersData, rootFilesData, workspace) {
                    console.log("DEBUG backend folders:", JSON.parse(JSON.stringify(foldersData)));
                    console.log("DEBUG root files:", JSON.parse(JSON.stringify(rootFilesData)));

                    // Simpan data dari backend
                    this.backendFolders = foldersData;
                    this.backendRootFiles = rootFilesData;
                    this.currentWorkspace = workspace;
                    this.currentWorkspaceId = workspace.id; // penting buat fetch()
                    
                    // Convert data Laravel Collection ke format yang diharapkan Alpine
                    this.processBackendData();
                },

                closeFile() {
                    this.currentFile = null;
                    // this.replyView.active = false;

                    // Update URL biar balik ke default tanpa reload
                    history.pushState({}, '', '#default');
                },

                getRootFolders() {
                return this.folders.filter(f => f.parent_id === null);
                },


                processBackendData() {
                    
                    // Process folders
                    this.folders = this.backendFolders.map(folder => ({
                        id: folder.id,
                        parent_id: folder.parent_id,  
                        name: folder.name,
                        type: 'Folder',
                        icon: this.getFolderIcon(),
                        isSecret: folder.is_private || false,
                        creator: {
                            name: folder.creator?.full_name || 'kiki',
                            avatar: folder.creator?.avatar || 'https://i.pravatar.cc/32?img=8'
                        },
                        createdAt: folder.created_at,
                        recipients: [],
                        subFolders: [], 
                        files: folder.files ? this.processFiles(folder.files) : [],
                        filesCount: folder.files_count || 0
                    }));

                    // Step 2: sambungkan folder-child
                    this.folders.forEach(folder => {
                        if (folder.parent_id) {
                            const parent = this.folders.find(f => f.id === folder.parent_id);
                            if (parent) {
                                parent.subFolders.push(folder);
                            }
                        }
                    });


                    // Process root files
                    this.processRootFiles();
                },

                processRootFiles() {
                    // Pastikan kita memproses file root melalui processFiles sehingga logic nama/tipe/icon konsisten
                    const processedFiles = this.processFiles(this.backendRootFiles || []);

                    // Group by type untuk kompatibilitas dengan kode existing
                    this.pdfFiles        = processedFiles.filter(f => f.type === 'PDF');
                    this.wordFiles       = processedFiles.filter(f => f.type === 'Word');
                    this.excelFiles      = processedFiles.filter(f => f.type === 'Excel');
                    this.powerPointFiles = processedFiles.filter(f => f.type === 'PowerPoint');
                    this.textFiles       = processedFiles.filter(f => f.type === 'Text');
                    this.imageFiles      = processedFiles.filter(f => f.type === 'Image');
                    this.zipFiles        = processedFiles.filter(f => f.type === 'Zip');
                    this.videoFiles      = processedFiles.filter(f => f.type === 'Video');
                    this.audioFiles      = processedFiles.filter(f => f.type === 'Audio');
                    this.codeFiles       = processedFiles.filter(f => f.type === 'Code');
                    this.unknownFiles    = processedFiles.filter(f => f.type === 'Unknown');
                },



                processFiles(files) {
                    return (files || []).map(file => {
                        // Ambil nama dari properti yang ada, atau ekstrak dari URL
                        console.log("Isi file URL kamu adlah:", file.file_url);
                        const originalName = file.name || file.file_name || null;
                        const extractedName = file.file_url
                            ? file.file_url.split('/').pop()
                            : null;
                        // Prioritaskan originalName bila ada, kalau gak ada pakai extractedName
                        const displayName = originalName || extractedName || 'Unknown File';

                        // Dapatkan type dari displayName (getFileType harus mampu menerima nama)
                        const type = this.getFileType(displayName);

                        return {
                            id: file.id,
                            folder_id: file.folder_id ?? null,   // ⬅⬅ TAMBAHKAN INI
                            // gunakan displayName agar di Blade x-text="file.name" muncul
                            name: displayName,
                            type: type,
                            icon: this.getFileIcon(type),
                            size: this.formatFileSize(file.file_size || 0),
                            file_url: file.file_url,   // ⬅⬅ WAJIB
                            creator: {
                                // perhatikan properti uploader: kamu pakai full_name di data
                                name: file.uploader?.full_name || file.uploader?.name || 'Zaki',
                                avatar: file.uploader?.avatar || 'https://i.pravatar.cc/32?img=8'
                            },
                            createdAt: file.created_at || file.uploaded_at,
                            isSecret: file.is_private || false,
                            comments: file.comments || [],
                            recipients: []
                        };
                    });
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
                    console.log('%c🔥 filterDocuments terpanggil!', 'color: orange');
                    console.log('search Query:', this.searchQuery);
                    if (this.searchQuery.trim() === '') {
                        this.filteredDocuments = [];
                        return;
                    }

                    const query = this.searchQuery.toLowerCase();

                    // Ambil semua folder (semua level)
                    const allFolders = this.getAllFolders(this.folders);

                    // Ambil semua file (semua level)
                    const allFiles = this.getAllFiles(this.folders);

                    // Ambil file di root (kalau ada)
                    const rootFiles = this.allFiles || [];

                    // Gabungkan semua dokumen
                    const allDocuments = [...allFolders, ...allFiles, ...rootFiles];

                    console.log('allDocuments:', allDocuments);

                     // Filter berdasarkan nama atau tipe
                    this.filteredDocuments = allDocuments.filter(doc =>
                        doc.name.toLowerCase().includes(query) ||
                        (doc.type && doc.type.toLowerCase().includes(query))
                    );
                },

                getAllFiles(folders){
                    let result = [];

                    // Safety check: pastikan 'folders' itu array
                    if (!Array.isArray(folders)) return result;

                    folders.forEach(folder => {
                        // Ambil semua file di folder ini
                        if (folder.files && folder.files.length > 0) {
                            result = result.concat(folder.files);
                        }

                        // Kalau ada subfolder, ambil file-nya juga secara rekursif
                        if (folder.subFolders && folder.subFolders.length > 0) {
                            result = result.concat(this.getAllFiles(folder.subFolders));
                        }
                    });

                    return result;
                },

                getAllFolders(folders){
                    let result = [];

                    // Safety check: kalau bukan array, return kosong
                    if (!Array.isArray(folders)) return result;

                    folders.forEach(folder => {
                        // Masukkan folder ini ke hasil
                        result.push(folder);

                        // Kalau folder punya subFolders, ambil juga semua isinya (rekursif)
                        if (folder.subFolders && folder.subFolders.length > 0) {
                            result = result.concat(this.getAllFolders(folder.subFolders));
                        }
                    });

                    return result;
                },

                clearSearch() {
                    this.searchQuery = '';
                    this.filteredDocuments = [];
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
                    console.log('openFolder dipanggil');
                    this.currentFile = null;

                    // update URL hash
                    history.pushState(
                        { folderName: folder.name },
                        '',
                        `#${folder.name}`
                    );

                    localStorage.setItem('lastFolder', folder.name);

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
                
                restoreFolderFromUrl() {
                    console.log('restoreFolder() dipanggil');

                    const hash = window.location.hash;
                    console.log('[2] hash sekarang:', hash);

                    let folderName = null;

                    // 1. Prioritaskan hash jika ada
                    if (hash && hash.length > 1) {
                        folderName = decodeURIComponent(hash.substring(1));
                        console.log('[3] Folder dari hash:', folderName);
                    }

                    // 2. Kalau hash kosong → ambil dari localStorage
                    if (!folderName) {
                        folderName = localStorage.getItem('lastFolder');
                        console.log('[3] Folder dari localStorage:', folderName);
                    }

                    // 3. Jika tetap tidak ada folderName → berhenti
                    if (!folderName) return;

                    // 4. Cari folder berdasarkan nama
                    const folder = this.folders.find(f => f.name === folderName);
                    if (folder) {
                        this.currentFolder = folder;
                        console.log('[4] currentFolder di-set ke:', this.currentFolder);

                        // pastikan URL juga sinkron
                        history.replaceState({}, '', `#${folderName}`);
                    }
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
                    console.log('Gw Pembuat', file.creator.name);
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

                    // 🔹 Tambahkan ke history supaya tombol "Back browser" bisa tahu state
                    console.log('📂 openFile jalan, file:', file.id);
                    history.pushState({ fileId: file.id }, '', `#file-${file.id}`);
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

                // Download file
                downloadFile(file) {
                    console.log('Fungsi downlaod dipanggil!');
                    console.log('Isi File_URL', file.file_url);
                    if (!file || !file.file_url) {
                        console.error("File URL tidak ditemukan");
                        return;
                    }

                    // Buka tab baru untuk preview
                    window.open(file.file_url, "_blank");
                },


                openEditFile(file) {
                    this.editingFile = file;
                    this.editFileIsSecret = file.isSecret || false;
                     this.originalIsSecretFile = file.isSecret || false;
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
                    this.deletingFile = file;
                    this.showDeleteFileModal = true;
                },

                openConfirmModal(title, message, action) {
                    this.confirmTitle = title;
                    this.confirmMessage = message;
                    this.onConfirmAction = action;
                    this.showConfirmModal = true;
                },
                
                runConfirmedAction() {
                    if (typeof this.onConfirmAction === 'function') {
                        this.onConfirmAction();
                    }
                    this.showConfirmModal = false;
                },


                // openDeleteFileModal(file) {
                //     this.deletingFile = file;
                //     this.showDeleteFileModal = true;
                // },

                // confirmDeleteFile() {
                //     if (!this.deletingFile) return;

                //     this.deleteFile(this.deletingFile);
                //     this.showSuccessMessage(`File "${this.deletingFile.name}" berhasil dihapus`);

                //     this.showDeleteFileModal = false;
                //     this.deletingFile = null;
                // },

                // Folder Operations
                openEditFolder(folder) {
                    this.editingFolder = folder;
                    this.editFolderName = folder.name;
                    this.originalFolderName = folder.name;
                    this.editIsSecretFolder = folder.isSecret || false;
                    this.originalIsSecretFolder = folder.isSecret || false;
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

                // confirmDeleteFolder() {
                //     if (!this.deletingFolder) return;

                //     this.deleteFolder(this.deletingFolder);
                //     this.showSuccessMessage(`Folder "${this.deletingFolder.name}" berhasil dihapus`);

                //     this.showDeleteFolderModal = false;
                //     this.deletingFolder = null;
                // },

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
                    console.log('isi folder', this.folders);
                    console.log('subFolders length =', this.currentFolder.subFolders.length);
                    console.log('isi subFolders =', JSON.parse(JSON.stringify(this.currentFolder.subFolders)));

                    if (this.searchQuery && this.filteredDocuments.length > 0) {
                        return this.filteredDocuments;
                    }
                    if (this.currentFolder) {
                        return [...this.currentFolder.subFolders, ...this.currentFolder.files];
                    }
                    return [];
                },

                getFileType(filename) {
                    const name = filename.toLowerCase();
                    if (name.endsWith('.pdf')) return 'PDF';
                    if (name.endsWith('.doc') || name.endsWith('.docx')) return 'Word';
                    if (name.endsWith('.xls') || name.endsWith('.xlsx') || name.endsWith('.csv')) return 'Excel';
                    if (name.endsWith('.ppt') || name.endsWith('.pptx')) return 'PowerPoint';
                    if (name.endsWith('.txt') || name.endsWith('.rtf') || name.endsWith('.odt')) return 'Text';
                    if (name.endsWith('.jpg') || name.endsWith('.jpeg') || name.endsWith('.png') || name.endsWith('.gif') || name.endsWith('.svg') || name.endsWith('.webp') || name.endsWith('.bmp')) return 'Image';
                    if (name.endsWith('.zip') || name.endsWith('.rar') || name.endsWith('.7z') || name.endsWith('.tar') || name.endsWith('.gz')) return 'Zip';
                    if (name.endsWith('.mp4') || name.endsWith('.mov') || name.endsWith('.avi') || name.endsWith('.mkv')) return 'Video';
                    if (name.endsWith('.mp3') || name.endsWith('.wav') || name.endsWith('.ogg')) return 'Audio';
                    if (name.endsWith('.js') || name.endsWith('.html') || name.endsWith('.css') || name.endsWith('.json') || name.endsWith('.xml') || name.endsWith('.php') || name.endsWith('.py')) return 'Code';

                    return 'Unknown'; // fallback
                },

                getFileIcon(fileType) {
                    const base = window.assetPath || '/'

                   const icons = {
                        'PDF': `${base}images/icons/pdf.svg`,
                        'Word': `${base}images/icons/microsoft-word.svg`,
                        'Excel': `${base}images/icons/excel.svg`,
                        'PowerPoint': `${base}images/icons/powerpoint.svg`,
                        'Text': `${base}images/icons/text-file.svg`,
                        'Image': `${base}images/icons/image.svg`,
                        'Zip': `${base}images/icons/zip.svg`,
                        'Video': `${base}images/icons/video.svg`,
                        'Audio': `${base}images/icons/audio.svg`,
                        'Code': `${base}images/icons/code.svg`,
                        'Unknown': `${base}images/icons/file-unknown.svg`,
                    };

                    return icons[fileType] || icons['Unknown'];
                },

                getFolderIcon() {
                    return `${window.assetPath}images/icons/folder.svg`;
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

// ===== DOKUMEN EDITOR FUNCTIONS =====
window.documentEditors = {};

// ===== DOKUMEN COMMENT SECTION =====
window.documentCommentSection = function() {
    return {
        replyView: {
            active: false,
            parentComment: null
        },

        init() {
            this.$nextTick(() => {
                setTimeout(() => {
                    this.createEditorForDocument('document-main-comment-editor', {
                        placeholder: 'Ketik komentar Anda di sini...'
                    });
                }, 300);
            });

            window.addEventListener('popstate', (event) => {
                if (event.state && event.state.fileId) {
                    // Kalau popstate punya fileId, tampilkan lagi file-nya
                    const file = this.getAllFiles().find(f => f.id === event.state.fileId);
                    if (file) this.currentFile = file;
                } else {
                    // Kalau tidak ada state (berarti balik ke default view)
                    this.currentFile = null;
                    this.replyView.active = false;
                }
            });

        },

    

        toggleReply(comment) {
            if (this.replyView.active && this.replyView.parentComment?.id === comment.id) {
                this.closeReplyView();
                return;
            }
            
            if (this.replyView.active && this.replyView.parentComment) {
                this.destroyReplyEditorForDocument(this.replyView.parentComment.id);
            }
            
            this.replyView.active = true;
            this.replyView.parentComment = comment;

            setTimeout(() => {
                this.initReplyEditorForDocument(comment.id);
            }, 150);
        },

        closeReplyView() {
            if (this.replyView.parentComment) {
                this.destroyReplyEditorForDocument(this.replyView.parentComment.id);
            }
            this.replyView.active = false;
            this.replyView.parentComment = null;
        },

        submitReplyFromEditor() {
            if (!this.replyView.parentComment) {
                alert('Komentar induk tidak ditemukan');
                return;
            }
            
            const parentId = this.replyView.parentComment.id;
            const content = this.getDocumentReplyEditorDataFor(parentId).trim();
            
            if (!content) {
                alert('Komentar balasan tidak boleh kosong!');
                return;
            }

            const alpineComponent = document.querySelector('[x-data]').__x.$data;
            alpineComponent.addReply(parentId, content);
            this.closeReplyView();
        },

        submitMainComment() {
            const content = this.getDocumentEditorData('document-main-comment-editor').trim();
            if (!content) {
                alert('Komentar tidak boleh kosong!');
                return;
            }

            const alpineComponent = document.querySelector('[x-data]').__x.$data;
            alpineComponent.addComment(alpineComponent.currentFile, content);

            const editor = window.documentEditors['document-main-comment-editor'];
            if (editor) editor.setData('');
        },

        // Editor Functions
        async createEditorForDocument(containerId, options = {}) {
            const el = document.getElementById(containerId);
            if (!el) {
                console.warn('Editor container not found:', containerId);
                return null;
            }

            el.innerHTML = '';

            const baseConfig = {
                toolbar: {
                    items: [
                        'undo', 'redo', '|',
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'link', 'blockQuote', '|',
                        'bulletedList', 'numberedList', '|',
                        'insertTable'
                    ],
                    shouldNotGroupWhenFull: true
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraf', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                    ]
                },
                placeholder: options.placeholder || ''
            };

            try {
                const editor = await ClassicEditor.create(el, baseConfig);
                window.documentEditors[containerId] = editor;

                editor.model.document.on('change:data', () => {
                    const data = editor.getData();
                    const ev = new CustomEvent('editor-change', {
                        detail: { id: containerId, data }
                    });
                    window.dispatchEvent(ev);
                });

                return editor;
            } catch (err) {
                console.error('Editor creation error:', err);
                el.innerHTML = `<textarea id="${containerId}-fallback" class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none">${options.initial || ''}</textarea>`;
                return null;
            }
        },

        destroyEditorForDocument(containerId) {
            const ed = window.documentEditors[containerId];
            if (ed) {
                ed.destroy().then(() => {
                    delete window.documentEditors[containerId];
                }).catch((e) => {
                    console.warn('Destroy editor error:', e);
                    delete window.documentEditors[containerId];
                });
            } else {
                const ta = document.getElementById(containerId + '-fallback');
                if (ta) ta.remove();
            }
        },

        getDocumentEditorData(containerId) {
            const ed = window.documentEditors[containerId];
            if (ed) return ed.getData();
            const ta = document.getElementById(containerId + '-fallback');
            return ta ? ta.value : '';
        },

        initReplyEditorForDocument(commentId) {
            const containerId = 'document-reply-editor-' + commentId;
            return this.createEditorForDocument(containerId, {
                placeholder: 'Ketik balasan Anda di sini...'
            });
        },

        destroyReplyEditorForDocument(commentId) {
            const containerId = 'document-reply-editor-' + commentId;
            this.destroyEditorForDocument(containerId);
        },

        getDocumentReplyEditorDataFor(commentId) {
            return this.getDocumentEditorData('document-reply-editor-' + commentId);
        },

        destroyDocumentMainEditor() {
            this.destroyEditorForDocument('document-main-comment-editor');
        }
    };
};

// Export functions untuk akses global
window.createEditorForDocument = window.documentCommentSection.prototype.createEditorForDocument;
window.destroyEditorForDocument = window.documentCommentSection.prototype.destroyEditorForDocument;
window.getDocumentEditorData = window.documentCommentSection.prototype.getDocumentEditorData;
window.initReplyEditorForDocument = window.documentCommentSection.prototype.initReplyEditorForDocument;
window.destroyReplyEditorForDocument = window.documentCommentSection.prototype.destroyReplyEditorForDocument;
window.getDocumentReplyEditorDataFor = window.documentCommentSection.prototype.getDocumentReplyEditorDataFor;