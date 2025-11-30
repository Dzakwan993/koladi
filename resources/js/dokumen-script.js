console.log("✅ dokumen-script.js loaded");

// ===== DOKUMEN SEARCH FUNCTIONS =====
export default function documentSearch() {
    return {
        // State Properties
        searchQuery: "",
        ready: false,
        filteredDocuments: [],
        showCreateFolderModal: false,
        showMoveDocumentsModal: false,
        showEditFolderModal: false,
        showDeleteFolderModal: false,
        openAddMemberModal: false,
        currentWorkspaceId: null,
        currentWorkspace: null,
        newFolderName: "",
        isSecretFolder: false,
        editFolderName: "",
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
        searchMember: "",
        selectAll: false,
        originalFolderName: "",
        showConfirmModal: false,
        confirmTitle: "",
        confirmMessage: "",
        onConfirmAction: null,
        currentFolderCreatedBy: null, // <–– TAMBAHAN
        currentFileUploadedBy: null, // <–– TAMBAHAN
        memberListAllowed: null, // <–– TAMBAHAN
        isLoadingPermission: false, // <–– TAMBAHAN

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
            ].filter((file) => file.folder_id === null);
        },

        init() {
            // Setup popstate untuk BACK / FORWARD
            const handlePopState = (event) => {
                console.log("=== POPSTATE TRIGGERED ===");
                console.log("event.state:", event.state);
                console.log("current URL:", window.location.href);
                console.log("currentFile sebelum:", this.currentFile);
                console.log("currentFolder sebelum:", this.currentFolder);

                // file state -> tampilkan file (JANGAN pertahankan breadcrumb/grid)
                if (event.state && event.state.fileId) {
                    console.log(
                        "🔹 Detected fileId (popstate):",
                        event.state.fileId
                    );
                    const fileId = event.state.fileId;
                    const allFiles = this.getAllFiles(this.folders);
                    const file = allFiles.find(
                        (f) => String(f.id) === String(fileId)
                    );
                    if (file) {
                        this.currentFile = {
                            ...file,
                            folder: null,
                            folderPath: [],
                        };
                        this.currentFileUploadedBy =
                            file.uploaded_by || file.uploader?.id || null;
                        this.currentFolder = null; // penting: jangan tampilkan folder UI
                        this.breadcrumbs = [];
                        this.loadMembersFromAPI();
                        console.log(
                            "✅ Restored file via popstate (ke file view):",
                            fileId
                        );
                    } else {
                        this.currentFile = null;
                        this.currentFolder = null;
                    }
                    return;
                }

                // folder state -> tampilkan folder
                if (event.state && event.state.folderId) {
                    console.log(
                        "🔹 Detected folderId (popstate):",
                        event.state.folderId
                    );
                    const folder = this.folders.find(
                        (f) => String(f.id) === String(event.state.folderId)
                    );
                    if (folder) {
                        this.currentFolder = folder;
                        this.currentFile = null;
                        this.updateBreadcrumbs();
                        this.loadMembersFromAPI();
                        console.log(
                            "✅ Restored folder via popstate:",
                            folder.name
                        );
                    } else {
                        this.currentFolder = null;
                        this.currentFile = null;
                        this.updateBreadcrumbs();
                    }
                    return;
                }

                // default -> root
                console.log("🔹 No state -> going root");
                this.currentFolder = null;
                this.currentFile = null;
                this.folderHistory = [];
                this.updateBreadcrumbs();
            };

            window.addEventListener("popstate", handlePopState);

            // pageshow untuk bfcache: jika halaman di-restore, pastikan modal swal ditutup bila perlu
            window.addEventListener("pageshow", (e) => {
                if (e.persisted) {
                    if (window.Swal) {
                        try {
                            Swal.close();
                        } catch (err) {
                            /* ignore */
                        }
                    }
                    // juga panggil restoreFolderFromUrl agar state disinkronkan ulang
                    this.restoreFolderFromUrl();
                }
            });
        },

        // helper central untuk mengatur history (push / replace)
        setHistoryState(stateObj = {}, replace = false) {
            const params = new URLSearchParams();
            if (stateObj.folderId) params.set("folder", stateObj.folderId);
            if (stateObj.fileId) params.set("file", stateObj.fileId);
            const newUrl = `${window.location.pathname}${
                params.toString() ? "?" + params.toString() : ""
            }`;
            try {
                if (replace) {
                    history.replaceState(stateObj, "", newUrl);
                } else {
                    history.pushState(stateObj, "", newUrl);
                }
            } catch (e) {
                console.warn("history set error", e);
            }
        },

        // Function untuk inisialisasi data
        initData(foldersData, rootFilesData, workspace) {
            console.log(
                "DEBUG backend folders:",
                JSON.parse(JSON.stringify(foldersData))
            );
            console.log(
                "DEBUG root files:",
                JSON.parse(JSON.stringify(rootFilesData))
            );

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
            // hiwtory.pushState({}, '', '#default');
        },

        getRootFolders() {
            return this.folders.filter((f) => f.parent_id === null);
        },

        processBackendData() {
            // Process folders
            this.folders = this.backendFolders.map((folder) => ({
                id: folder.id,
                parent_id: folder.parent_id,
                name: folder.name,
                type: "Folder",
                icon: this.getFolderIcon(),
                isSecret: folder.is_private || false,
                creator: {
                    id: folder.creator?.id || folder.creator_id || null,
                    name: folder.creator?.full_name || "kiki",
                    avatar:
                        folder.creator?.avatar ||
                        "https://i.pravatar.cc/32?img=8",
                },
                createdAt: folder.created_at,
                recipients: [],
                subFolders: [],
                files: folder.files ? this.processFiles(folder.files) : [],
                filesCount: folder.files_count || 0,
            }));

            // Step 2: sambungkan folder-child
            this.folders.forEach((folder) => {
                if (folder.parent_id) {
                    const parent = this.folders.find(
                        (f) => f.id === folder.parent_id
                    );
                    if (parent) {
                        parent.subFolders.push(folder);
                    }
                }
            });

            // Process root files
            this.processRootFiles();
        },

        processRootFiles() {
            const processedFiles = this.processFiles(
                this.backendRootFiles || []
            );

            // Group by type untuk kompatibilitas dengan kode existing
            this.pdfFiles = processedFiles.filter((f) => f.type === "PDF");
            this.wordFiles = processedFiles.filter((f) => f.type === "Word");
            this.excelFiles = processedFiles.filter((f) => f.type === "Excel");
            this.powerPointFiles = processedFiles.filter(
                (f) => f.type === "PowerPoint"
            );
            this.textFiles = processedFiles.filter((f) => f.type === "Text");
            this.imageFiles = processedFiles.filter((f) => f.type === "Image");
            this.zipFiles = processedFiles.filter((f) => f.type === "Zip");
            this.videoFiles = processedFiles.filter((f) => f.type === "Video");
            this.audioFiles = processedFiles.filter((f) => f.type === "Audio");
            this.codeFiles = processedFiles.filter((f) => f.type === "Code");
            this.unknownFiles = processedFiles.filter(
                (f) => f.type === "Unknown"
            );
        },

        processFiles(files) {
            return (files || []).map((file) => {
                // Ambil nama dari properti yang ada, atau ekstrak dari URL
                console.log("Isi file URL kamu adlah:", file.file_url);
                const originalName = file.name || file.file_name || null;
                const extractedName = file.file_url
                    ? file.file_url.split("/").pop()
                    : null;
                // Prioritaskan originalName bila ada, kalau gak ada pakai extractedName
                const displayName =
                    originalName || extractedName || "Unknown File";

                // Dapatkan type dari displayName (getFileType harus mampu menerima nama)
                const type = this.getFileType(displayName);

                return {
                    id: file.id,
                    folder_id: file.folder_id ?? null, // ⬅⬅ TAMBAHKAN INI
                    // gunakan displayName agar di Blade x-text="file.name" muncul
                    name: displayName,
                    type: type,
                    icon: this.getFileIcon(type),
                    size: this.formatFileSize(file.file_size || 0),
                    file_url: file.file_url, // ⬅⬅ WAJIB

                    // 🔥 Tambahkan ID uploader
                    uploaded_by: file.uploaded_by || file.uploader?.id || null,
                    creator: {
                        // perhatikan properti uploader: kamu pakai full_name di data
                        name:
                            file.uploader?.full_name ||
                            file.uploader?.name ||
                            "Zaki",
                        avatar:
                            file.uploader?.avatar ||
                            "https://i.pravatar.cc/32?img=8",
                    },
                    createdAt: file.created_at || file.uploaded_at,
                    isSecret: file.is_private || false,
                    comments: file.comments || [],
                    recipients: [],
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
            return this.members.filter((member) =>
                member.name.toLowerCase().includes(query)
            );
        },

        get currentFolderDocuments() {
            if (!this.currentFolder) return [];
            return [
                ...this.currentFolder.subFolders,
                ...this.currentFolder.files,
            ];
        },

        // Search Functions
        filterDocuments() {
            console.log("%c🔥 filterDocuments terpanggil!", "color: orange");
            console.log("search Query:", this.searchQuery);
            if (this.searchQuery.trim() === "") {
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

            console.log("allDocuments:", allDocuments);

            // Filter berdasarkan nama atau tipe
            this.filteredDocuments = allDocuments.filter(
                (doc) =>
                    doc.name.toLowerCase().includes(query) ||
                    (doc.type && doc.type.toLowerCase().includes(query))
            );
        },

        getAllFiles(folders) {
            let result = [];

            // Safety check: pastikan 'folders' itu array
            if (!Array.isArray(folders)) return result;

            folders.forEach((folder) => {
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

        getAllFolders(folders) {
            let result = [];

            // Safety check: kalau bukan array, return kosong
            if (!Array.isArray(folders)) return result;

            folders.forEach((folder) => {
                // Masukkan folder ini ke hasil
                result.push(folder);

                // Kalau folder punya subFolders, ambil juga semua isinya (rekursif)
                if (folder.subFolders && folder.subFolders.length > 0) {
                    result = result.concat(
                        this.getAllFolders(folder.subFolders)
                    );
                }
            });

            return result;
        },

        clearSearch() {
            this.searchQuery = "";
            this.filteredDocuments = [];
        },

        // File Upload Functions
        uploadFileToFolder(event) {
            const file = event.target.files[0];
            if (!file) return;

            const fileType = this.getFileType(file.name);
            const icon = this.getFileIcon(fileType);

            const newFile = {
                id: "file-" + Date.now(),
                name: file.name,
                type: fileType,
                icon: icon,
                file: file,
                size: this.formatFileSize(file.size),
                creator: this.getCurrentUser(),
                createdAt: new Date().toISOString(),
                recipients: [],
                comments: [],
                isSecret: false,
            };

            if (this.currentFolder) {
                this.currentFolder.files.push(newFile);
            } else {
                this.addFileToCollection(newFile, fileType);
            }

            event.target.value = "";
            this.showSuccessMessage(`File "${file.name}" berhasil diunggah`);
        },

        // Selection Functions
        toggleSelectMode() {
            this.selectMode = !this.selectMode;
            if (!this.selectMode) this.selectedDocuments = [];
        },

        toggleDocumentSelection(document) {
            if (!this.selectMode) return;

            const index = this.selectedDocuments.findIndex(
                (doc) => doc.id === document.id
            );
            if (index > -1) {
                this.selectedDocuments.splice(index, 1);
            } else {
                this.selectedDocuments.push(document);
            }
        },

        isDocumentSelected(documentId) {
            return this.selectedDocuments.some((doc) => doc.id === documentId);
        },

        cancelSelection() {
            this.selectedDocuments = [];
            this.selectMode = false;
        },

        // Workspace Functions
        confirmMoveDocuments() {
            if (!this.selectedWorkspace) return;

            const destination = this.selectedFolder
                ? `${this.selectedWorkspace.name} - ${this.selectedFolder.name}`
                : `${this.selectedWorkspace.name} (Dokumen Utama)`;

            this.moveSelectedDocuments();
            this.showSuccessMessage(
                `Berhasil memindahkan ${this.selectedDocuments.length} berkas ke ${destination}`
            );

            this.showMoveDocumentsModal = false;
            this.selectedWorkspace = null;
            this.selectedFolder = null;
            this.cancelSelection();
        },

        // Folder Navigation
        openFolder(folder) {
            console.log("openFolder dipanggil", folder);
            this.currentFile = null;
            this.currentFolderCreatedBy = null;
            this.currentFileUploadedBy = null;

            this.$nextTick(() => {
                this.currentFolderCreatedBy =
                    folder.creator?.id || folder.creator_id || null;

                // push new folder state
                this.setHistoryState(
                    { folderId: folder.id, folderName: folder.name },
                    false
                );

                if (!this.currentFolder) {
                    this.folderHistory = [];
                } else {
                    const isAlreadyInHistory = this.folderHistory.some(
                        (f) => f.id === this.currentFolder.id
                    );
                    if (!isAlreadyInHistory) {
                        this.folderHistory.push({ ...this.currentFolder });
                    }
                }

                this.loadMembersFromAPI();
                this.currentFolder = folder;
                this.currentFile = null;
                this.updateBreadcrumbs();
            });
        },

        navigateToFolder(folder) {
            const folderIndex = this.breadcrumbs.findIndex(
                (f) => f.id === folder.id
            );
            if (folderIndex > -1) {
                this.folderHistory = this.breadcrumbs.slice(0, folderIndex);
                this.currentFolder = folder;
                this.updateBreadcrumbs();
            }
        },

        goToRoot() {
            // Restore URL ke root (hapus query string)
            history.pushState({}, "", window.location.pathname);
            this.currentFolder = null;
            this.folderHistory = [];
            this.currentFile = null;
            this.updateBreadcrumbs();
        },

        resetAllModals() {
            this.showEditFileModal = false;
            this.showDeleteFileModal = false;
            this.showCreateFolderModal = false;
            this.showMoveDocumentsModal = false;
            this.showEditFolderModal = false;
            this.showDeleteFolderModal = false;
            this.openAddMemberModal = false;
            this.showConfirmModal = false;
        },

        restoreFolderFromUrl() {
            console.log("restoreFolderFromUrl dipanggil");

            const url = new URL(window.location);
            const folderIdFromUrl = url.searchParams.get("folder");
            const fileIdFromUrl = url.searchParams.get("file");

            // jika ada file param -> tampilkan file saja (jangan set currentFolder)
            if (fileIdFromUrl) {
                console.log("🔹 Found file param in URL:", fileIdFromUrl);
                const allFiles = this.getAllFiles(this.folders);
                const file = allFiles.find(
                    (f) => String(f.id) === String(fileIdFromUrl)
                );
                if (file) {
                    this.currentFile = {
                        ...file,
                        folder: null,
                        folderPath: [],
                    };
                    this.currentFileUploadedBy =
                        file.uploaded_by || file.uploader?.id || null;
                    this.currentFolder = null;
                    this.breadcrumbs = [];
                    this.loadMembersFromAPI();
                    // replace history so this page load does NOT create duplicate entry
                    this.setHistoryState(
                        { fileId: file.id, folderId: folderIdFromUrl || null },
                        true
                    );
                    console.log(
                        "✅ Restored file from URL (replace state):",
                        fileIdFromUrl
                    );
                } else {
                    this.currentFile = null;
                    // no file found -> fallback to folder handling
                    if (folderIdFromUrl) {
                        const folder = this.folders.find(
                            (f) => String(f.id) === String(folderIdFromUrl)
                        );
                        if (folder) {
                            this.currentFolder = folder;
                            this.currentFile = null;
                            this.updateBreadcrumbs();
                            this.setHistoryState({ folderId: folder.id }, true);
                            console.log(
                                "✅ Fallback: restored folder from URL (replace state):",
                                folder.id
                            );
                        } else {
                            this.currentFolder = null;
                            this.setHistoryState({}, true);
                        }
                    } else {
                        this.currentFolder = null;
                        this.setHistoryState({}, true);
                    }
                }
                return;
            }

            // jika tidak ada file param -> fallback folder param
            if (folderIdFromUrl) {
                const folder = this.folders.find(
                    (f) => String(f.id) === String(folderIdFromUrl)
                );
                if (folder) {
                    this.currentFolder = folder;
                    this.currentFile = null;
                    this.updateBreadcrumbs();
                    this.setHistoryState({ folderId: folder.id }, true); // normalize history entry
                    console.log(
                        "✅ Folder restored dari URL ?folder=",
                        folderIdFromUrl,
                        folder.name
                    );
                } else {
                    this.currentFolder = null;
                    this.setHistoryState({}, true);
                }
                return;
            }

            // tidak ada param -> normal state (root)
            this.currentFolder = null;
            this.currentFile = null;
            this.setHistoryState({}, true);
        },

        updateBreadcrumbs() {
            this.breadcrumbs = [...this.folderHistory];
        },

        getCurrentFolderPath() {
            if (!this.currentFolder) return "Dokumen";

            const pathParts = ["Dokumen"];
            if (this.breadcrumbs.length > 0) {
                pathParts.push(...this.breadcrumbs.map((crumb) => crumb.name));
            }
            pathParts.push(this.currentFolder.name);

            return pathParts.join(" > ");
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
            const folderIndex = this.fileBreadcrumbs.findIndex(
                (f) => f.id === folder.id
            );
            if (folderIndex > -1) {
                this.breadcrumbs = this.fileBreadcrumbs.slice(0, folderIndex);
            }
        },

        openFile(file) {
            console.log("openFile dipanggil", file);
            // always clear folder UI when opening file
            this.currentFolder = null;
            this.currentFolderCreatedBy = null;

            const fileFolder = file.folder || null;
            const folderId =
                (fileFolder && fileFolder.id) || file.folder_id || null;

            this.currentFile = {
                ...file,
                folder: fileFolder || null,
                folderPath: [...this.breadcrumbs],
                creator: file.creator || this.getCurrentUser(),
                createdAt: file.createdAt || new Date().toISOString(),
                size:
                    file.size || this.formatFileSize(file.size || 1024 * 1024),
                recipients: file.recipients || this.getDefaultRecipients(),
                comments: file.comments || this.getDefaultComments(),
            };

            this.currentFileUploadedBy = file.uploaded_by;
            this.loadMembersFromAPI();

            // push file state (file view) so back/forward works
            this.setHistoryState(
                { fileId: file.id, folderId: folderId },
                false
            );

            console.log(
                "📂 openFile selesai, file pushed to history:",
                file.id
            );
        },

        // Comment Functions
        addComment(file, content) {
            if (!content.trim()) return;

            const newComment = {
                id: Date.now(),
                author: this.getCurrentUser(),
                content: content.trim(),
                createdAt: new Date().toISOString(),
                replies: [],
            };

            if (!file.comments) file.comments = [];
            file.comments.unshift(newComment);
        },

        showReplyForm(commentId) {
            const comment = this.currentFile.comments.find(
                (c) => c.id === commentId
            );
            if (comment) {
                comment.showReply = !comment.showReply;
            }
        },

        addReply(commentId, content) {
            if (!content.trim()) return;

            const comment = this.currentFile.comments.find(
                (c) => c.id === commentId
            );
            if (comment) {
                const newReply = {
                    id: Date.now(),
                    author: this.getCurrentUser(),
                    content: content.trim(),
                    createdAt: new Date().toISOString(),
                };

                if (!comment.replies) comment.replies = [];
                comment.replies.push(newReply);
                comment.showReply = false;
            }
        },

        // Download file
        downloadFile(file) {
            console.log("Fungsi downlaod dipanggil!");
            console.log("Isi File_URL", file.file_url);
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

            if (
                this.currentFile &&
                this.currentFile.id === this.editingFile.id
            ) {
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
            if (typeof this.onConfirmAction === "function") {
                this.onConfirmAction();
            }
            this.showConfirmModal = false;
        },

        // openDeleteFileModal(file) {
        // this.deletingFile = file;
        // this.showDeleteFileModal = true;
        // },

        // confirmDeleteFile() {
        // if (!this.deletingFile) return;

        // this.deleteFile(this.deletingFile);
        // this.showSuccessMessage(`File "${this.deletingFile.name}" berhasil dihapus`);

        // this.showDeleteFileModal = false;
        // this.deletingFile = null;
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

            this.updateFolderInArrays(
                this.editingFolder,
                this.editFolderName,
                this.editIsSecretFolder
            );

            this.showEditFolderModal = false;
            this.editFolderName = "";
            this.editIsSecretFolder = false;
            this.editingFolder = null;
        },

        openDeleteFolder(folder) {
            this.deletingFolder = folder;
            this.showDeleteFolderModal = true;
        },

        // confirmDeleteFolder() {
        // if (!this.deletingFolder) return;

        // this.deleteFolder(this.deletingFolder);
        // this.showSuccessMessage(`Folder "${this.deletingFolder.name}" berhasil dihapus`);

        // this.showDeleteFolderModal = false;
        // this.deletingFolder = null;
        // },

        // Member Functions
        toggleSelectAll() {
            this.filteredMembers().forEach(
                (m) => (m.selected = this.selectAll)
            );
        },

        // Watch members agar otomatis update selectAll
        watchMembersSelected() {
            this.selectAll =
                this.filteredMembers().length > 0 &&
                this.filteredMembers().every((m) => m.selected);
        },
        // saveSelectedMembers() {
        // const selectedMembers = this.members.filter(member => member.selected);

        // if (this.currentFolder) {
        // this.currentFolder.recipients = [...this.currentFolder.recipients, ...selectedMembers];
        // } else if (this.currentFile) {
        // this.currentFile.recipients = [...this.currentFile.recipients, ...selectedMembers];
        // }

        // this.openAddMemberModal = false;
        // this.searchMember = '';
        // this.selectAll = false;
        // this.showSuccessMessage(`Berhasil menambahkan ${selectedMembers.length} peserta`);
        // },

        // Utility Functions
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString("id-ID", {
                day: "numeric",
                month: "long",
                year: "numeric",
            });
        },

        formatFileSize(bytes) {
            if (bytes === 0) return "0 Bytes";
            const k = 1024;
            const sizes = ["Bytes", "KB", "MB", "GB"];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (
                parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
            );
        },

        formatCommentDate(dateString) {
            if (!dateString) return "";
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
            const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
            const diffMinutes = Math.floor(diffTime / (1000 * 60));

            if (diffMinutes < 1) return "beberapa detik yang lalu";
            if (diffMinutes < 60) return `${diffMinutes} menit yang lalu`;
            if (diffHours < 24) return `${diffHours} jam yang lalu`;
            if (diffDays < 7) return `${diffDays} hari yang lalu`;

            return date.toLocaleDateString("id-ID", {
                day: "numeric",
                month: "short",
                year: "numeric",
            });
        },

        // Helper Functions
        getDisplayedDocuments() {
            console.log("isi folder", this.folders);
            console.log(
                "subFolders length =",
                this.currentFolder.subFolders.length
            );
            console.log(
                "isi subFolders =",
                JSON.parse(JSON.stringify(this.currentFolder.subFolders))
            );

            if (this.searchQuery && this.filteredDocuments.length > 0) {
                return this.filteredDocuments;
            }
            if (this.currentFolder) {
                return [
                    ...this.currentFolder.subFolders,
                    ...this.currentFolder.files,
                ];
            }
            return [];
        },

        getFileType(filename) {
            const name = filename.toLowerCase();
            if (name.endsWith(".pdf")) return "PDF";
            if (name.endsWith(".doc") || name.endsWith(".docx")) return "Word";
            if (
                name.endsWith(".xls") ||
                name.endsWith(".xlsx") ||
                name.endsWith(".csv")
            )
                return "Excel";
            if (name.endsWith(".ppt") || name.endsWith(".pptx"))
                return "PowerPoint";
            if (
                name.endsWith(".txt") ||
                name.endsWith(".rtf") ||
                name.endsWith(".odt")
            )
                return "Text";
            if (
                name.endsWith(".jpg") ||
                name.endsWith(".jpeg") ||
                name.endsWith(".png") ||
                name.endsWith(".gif") ||
                name.endsWith(".svg") ||
                name.endsWith(".webp") ||
                name.endsWith(".bmp")
            )
                return "Image";
            if (
                name.endsWith(".zip") ||
                name.endsWith(".rar") ||
                name.endsWith(".7z") ||
                name.endsWith(".tar") ||
                name.endsWith(".gz")
            )
                return "Zip";
            if (
                name.endsWith(".mp4") ||
                name.endsWith(".mov") ||
                name.endsWith(".avi") ||
                name.endsWith(".mkv")
            )
                return "Video";
            if (
                name.endsWith(".mp3") ||
                name.endsWith(".wav") ||
                name.endsWith(".ogg")
            )
                return "Audio";
            if (
                name.endsWith(".js") ||
                name.endsWith(".html") ||
                name.endsWith(".css") ||
                name.endsWith(".json") ||
                name.endsWith(".xml") ||
                name.endsWith(".php") ||
                name.endsWith(".py")
            )
                return "Code";

            return "Unknown"; // fallback
        },

        getFileIcon(fileType) {
            const base = window.assetPath || "/";

            const icons = {
                PDF: `${base}images/icons/pdf.svg`,
                Word: `${base}images/icons/microsoft-word.svg`,
                Excel: `${base}images/icons/excel.svg`,
                PowerPoint: `${base}images/icons/powerpoint.svg`,
                Text: `${base}images/icons/text-file.svg`,
                Image: `${base}images/icons/image.svg`,
                Zip: `${base}images/icons/zip.svg`,
                Video: `${base}images/icons/video.svg`,
                Audio: `${base}images/icons/audio.svg`,
                Code: `${base}images/icons/code.svg`,
                Unknown: `${base}images/icons/file-unknown.svg`,
            };

            return icons[fileType] || icons["Unknown"];
        },

        getFolderIcon() {
            return `${window.assetPath}images/icons/folder.svg`;
        },

        getCurrentUser() {
            // Ganti dengan data user dari backend
            return {
                name: "Admin User",
                avatar: "https://i.pravatar.cc/32?img=8",
            };
        },

        getDefaultRecipients() {
            // Ganti dengan data default dari backend
            return [
                {
                    id: 1,
                    name: "John Doe",
                    avatar: "https://i.pravatar.cc/32?img=5",
                },
                {
                    id: 2,
                    name: "Jane Smith",
                    avatar: "https://i.pravatar.cc/32?img=6",
                },
            ];
        },

        getDefaultComments() {
            // Ganti dengan data default dari backend
            return [
                {
                    id: 1,
                    author: {
                        name: "Irfan",
                        avatar: "https://i.pravatar.cc/32?img=9",
                    },
                    content: "bagi bagi thr",
                    createdAt: new Date("2025-09-22T10:20:00").toISOString(),
                    replies: [
                        {
                            id: 1,
                            author: {
                                name: "Farrel",
                                avatar: "https://i.pravatar.cc/32?img=10",
                            },
                            content: "mana nht thr rya",
                            createdAt: new Date().toISOString(),
                        },
                    ],
                },
            ];
        },

        addFileToCollection(file, fileType) {
            const collections = {
                PDF: this.pdfFiles,
                Word: this.wordFiles,
                Excel: this.excelFiles,
            };
            const collection = collections[fileType];
            if (collection) collection.push(file);
        },

        loadMembersFromAPI() {
            this.isLoadingPermission = true;

            const params = new URLSearchParams({
                folder_created_by: this.currentFolderCreatedBy ?? "",
                file_uploaded_by: this.currentFileUploadedBy ?? "",
            });

            fetch(`/workspaces/${this.currentWorkspaceId}/members?${params}`)
                .then(async (res) => {
                    this.memberListAllowed = res.status === 200;
                    if (!this.memberListAllowed) {
                        this.members = [];
                        return null;
                    }
                    return await res.json();
                })
                .then(async (data) => {
                    if (!data?.members) return;

                    // Fetch recipients status=true dari backend
                    const docId =
                        this.currentFolder?.id || this.currentFile?.id;
                    const recipientsRes = await fetch(
                        `/documents/${docId}/recipients`
                    );
                    const recipientsData = await recipientsRes.json();
                    const selectedUserIds = recipientsData?.recipients || [];

                    // Tandai member yang sudah status=true
                    this.members = data.members.map((m) => ({
                        ...m,
                        selected: selectedUserIds.includes(m.id),
                    }));

                    // Centang "Pilih Semua" jika semua member tercentang
                    this.selectAll =
                        this.members.length > 0 &&
                        this.members.every((m) => m.selected);
                })
                .catch(() => {
                    this.memberListAllowed = false;
                    this.members = [];
                })
                .finally(() => {
                    this.isLoadingPermission = false;
                });
        },

        watchMembersSelected() {
            this.selectAll =
                this.members.length > 0 &&
                this.members.every((m) => m.selected);
        },

        updateFileInArrays(file, isSecret) {
            // Update file in all possible locations
            [
                this.folders,
                this.pdfFiles,
                this.wordFiles,
                this.excelFiles,
            ].forEach((collection) => {
                this.updateFileInCollection(collection, file.id, isSecret);
            });
        },

        updateFileInCollection(collection, fileId, isSecret) {
            if (Array.isArray(collection)) {
                collection.forEach((item) => {
                    if (item.id === fileId) {
                        item.isSecret = isSecret;
                    }
                    if (item.files) {
                        this.updateFileInCollection(
                            item.files,
                            fileId,
                            isSecret
                        );
                    }
                    if (item.subFolders) {
                        this.updateFileInCollection(
                            item.subFolders,
                            fileId,
                            isSecret
                        );
                    }
                });
            }
        },

        updateFolderInArrays(folder, newName, isSecret) {
            const folderIndex = this.folders.findIndex(
                (f) => f.id === folder.id
            );
            if (folderIndex > -1) {
                this.folders[folderIndex].name = newName.trim();
                this.folders[folderIndex].isSecret = isSecret;
            }

            if (this.currentFolder && this.currentFolder.id === folder.id) {
                this.currentFolder.name = newName.trim();
                this.currentFolder.isSecret = isSecret;
            }

            const breadcrumbIndex = this.breadcrumbs.findIndex(
                (f) => f.id === folder.id
            );
            if (breadcrumbIndex > -1) {
                this.breadcrumbs[breadcrumbIndex].name = newName.trim();
                this.breadcrumbs[breadcrumbIndex].isSecret = isSecret;
            }
        },

        deleteFile(file) {
            if (this.currentFolder) {
                const fileIndex = this.currentFolder.files.findIndex(
                    (f) => f.id === file.id
                );
                if (fileIndex > -1)
                    this.currentFolder.files.splice(fileIndex, 1);
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
            const index = collection.findIndex((item) => item.id === fileId);
            if (index > -1) collection.splice(index, 1);
        },

        deleteFolder(folder) {
            const folderIndex = this.folders.findIndex(
                (f) => f.id === folder.id
            );
            if (folderIndex > -1) this.folders.splice(folderIndex, 1);

            if (this.currentFolder && this.currentFolder.id === folder.id) {
                this.goToRoot();
            }
        },

        moveSelectedDocuments() {
            this.selectedDocuments.forEach((selectedDoc) => {
                if (this.currentFolder) {
                    this.removeFromCurrentFolder(selectedDoc);
                } else {
                    this.removeFromRoot(selectedDoc);
                }
            });
        },

        removeFromCurrentFolder(doc) {
            const subFolderIndex = this.currentFolder.subFolders.findIndex(
                (folder) => folder.id === doc.id
            );
            if (subFolderIndex > -1)
                this.currentFolder.subFolders.splice(subFolderIndex, 1);

            const fileIndex = this.currentFolder.files.findIndex(
                (file) => file.id === doc.id
            );
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
            replyContent: "",
            currentFile: null,
        },

        openReplyView(comment) {
            this.replyView.active = true;
            this.replyView.parentComment = comment;
            this.replyView.replyContent = "";
            this.replyView.currentFile = this.currentFile;
        },

        closeReplyView() {
            this.replyView.active = false;
            this.replyView.parentComment = null;
            this.replyView.replyContent = "";
            this.replyView.currentFile = null;
        },

        submitReply() {
            if (
                !this.replyView.replyContent.trim() ||
                !this.replyView.parentComment
            )
                return;

            const newReply = {
                id: Date.now(),
                author: this.getCurrentUser(),
                content: this.replyView.replyContent.trim(),
                createdAt: new Date().toISOString(),
            };

            if (!this.replyView.parentComment.replies) {
                this.replyView.parentComment.replies = [];
            }

            this.replyView.parentComment.replies.push(newReply);
            this.closeReplyView();
        },
    };
}

// ===== DOKUMEN COMMENT SECTION =====
// ===== TAMBAHKAN DI BAGIAN documentCommentSection =====
window.documentCommentSection = function () {
    return {
        replyView: {
            active: false,
            parentComment: null,
        },

        init() {
            this.resetAllModals();

            this.$watch(
                () => this.members.map((m) => m.selected),
                () => {
                    this.watchMembersSelected();
                },
                { deep: true }
            );

            this.$nextTick(() => {
                setTimeout(() => {
                    this.createEditorForDocument(
                        "document-main-comment-editor",
                        {
                            placeholder: "Ketik komentar Anda di sini...",
                        }
                    );
                }, 300);
            });

            window.addEventListener("popstate", (event) => {
                if (event.state && event.state.fileId) {
                    const file = this.getAllFiles().find(
                        (f) => f.id === event.state.fileId
                    );
                    if (file) this.currentFile = file;
                } else {
                    this.currentFile = null;
                    this.replyView.active = false;
                }
            });

            // 🔥 TAMBAHAN: Load komentar saat file dibuka
            this.$watch("currentFile", (newFile) => {
                if (newFile && newFile.id) {
                    this.loadCommentsForFile(newFile.id);
                }
            });
        },

        // 🔥 FUNGSI BARU: Load komentar dari backend
        async loadCommentsForFile(fileId) {
            try {
                const response = await fetch(`/documents/${fileId}/comments`);
                const data = await response.json();

                if (data.comments) {
                    this.currentFile.comments = data.comments;
                }
            } catch (error) {
                console.error("Error loading comments:", error);
            }
        },

        // 🔥 FUNGSI BARU: Submit komentar utama dengan UUID generation
        async submitMainComment() {
            const content = this.getDocumentEditorData(
                "document-main-comment-editor"
            ).trim();
            if (!content) {
                alert("Komentar tidak boleh kosong!");
                return;
            }

            const commentId = this.generateUUID(); // ✅ Generate UUID di frontend

            try {
                const response = await fetch("/documents/comments", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: JSON.stringify({
                        id: commentId, // ✅ Kirim UUID ke backend
                        content: content,
                        commentable_id: this.currentFile.id,
                        commentable_type: "App\\Models\\File",
                    }),
                });

                // 🔥 TAMBAHKAN LOGGING INI
                console.log("Response status:", response.status);
                const text = await response.text();
                console.log("Response text:", text);

                const data = await response.json();

                if (data.success) {
                    // Tambahkan komentar baru ke array
                    if (!this.currentFile.comments) {
                        this.currentFile.comments = [];
                    }
                    this.currentFile.comments.unshift(data.comment);

                    // Reset editor
                    const editor =
                        window.documentEditors["document-main-comment-editor"];
                    if (editor) editor.setData("");

                    alert("Komentar berhasil ditambahkan!");
                } else {
                    alert("Gagal menambahkan komentar");
                }
            } catch (error) {
                console.error("Error submitting comment:", error);
                alert("Terjadi kesalahan saat mengirim komentar");
            }
        },

        // 🔥 FUNGSI BARU: Submit reply dengan UUID generation
        async submitReplyFromEditor() {
            if (!this.replyView.parentComment) {
                alert("Komentar induk tidak ditemukan");
                return;
            }

            const parentId = this.replyView.parentComment.id;
            const content = this.getDocumentReplyEditorDataFor(parentId).trim();

            if (!content) {
                alert("Komentar balasan tidak boleh kosong!");
                return;
            }

            const replyId = this.generateUUID(); // ✅ Generate UUID di frontend

            try {
                const response = await fetch("/documents/comments", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                    body: JSON.stringify({
                        id: replyId, // ✅ Kirim UUID ke backend
                        content: content,
                        commentable_id: this.currentFile.id,
                        commentable_type: "App\\Models\\File",
                        parent_comment_id: parentId,
                    }),
                });

                const data = await response.json();

                if (data.success) {
                    // Tambahkan reply ke parent comment
                    if (!this.replyView.parentComment.replies) {
                        this.replyView.parentComment.replies = [];
                    }
                    this.replyView.parentComment.replies.push(data.comment);

                    this.closeReplyView();
                    alert("Balasan berhasil ditambahkan!");
                } else {
                    alert("Gagal menambahkan balasan");
                }
            } catch (error) {
                console.error("Error submitting reply:", error);
                alert("Terjadi kesalahan saat mengirim balasan");
            }
        },

        // 🔥 FUNGSI BARU: Generate UUID v4
        generateUUID() {
            return "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(
                /[xy]/g,
                function (c) {
                    const r = (Math.random() * 16) | 0;
                    const v = c === "x" ? r : (r & 0x3) | 0x8;
                    return v.toString(16);
                }
            );
        },

        toggleReply(comment) {
            if (
                this.replyView.active &&
                this.replyView.parentComment?.id === comment.id
            ) {
                this.closeReplyView();
                return;
            }

            if (this.replyView.active && this.replyView.parentComment) {
                this.destroyReplyEditorForDocument(
                    this.replyView.parentComment.id
                );
            }

            this.replyView.active = true;
            this.replyView.parentComment = comment;

            setTimeout(() => {
                this.initReplyEditorForDocument(comment.id);
            }, 150);
        },

        closeReplyView() {
            if (this.replyView.parentComment) {
                this.destroyReplyEditorForDocument(
                    this.replyView.parentComment.id
                );
            }
            this.replyView.active = false;
            this.replyView.parentComment = null;
        },

        // Editor Functions (sisanya sama seperti kode asli...)
        async createEditorForDocument(containerId, options = {}) {
            const el = document.getElementById(containerId);
            if (!el) {
                console.warn("Editor container not found:", containerId);
                return null;
            }

            el.innerHTML = "";

            // 🔥 PENTING: Tambahkan config untuk upload file/image
            const baseConfig = {
                toolbar: {
                    items: [
                        "undo",
                        "redo",
                        "|",
                        "heading",
                        "|",
                        "bold",
                        "italic",
                        "underline",
                        "strikethrough",
                        "|",
                        "link",
                        "blockQuote",
                        "|",
                        "bulletedList",
                        "numberedList",
                        "|",
                        "insertTable",
                        "|",
                        "imageUpload",
                        "uploadFile", // ✅ Tambahkan button upload
                    ],
                    shouldNotGroupWhenFull: true,
                },
                heading: {
                    options: [
                        {
                            model: "paragraph",
                            title: "Paragraf",
                            class: "ck-heading_paragraph",
                        },
                        {
                            model: "heading1",
                            view: "h1",
                            title: "Heading 1",
                            class: "ck-heading_heading1",
                        },
                        {
                            model: "heading2",
                            view: "h2",
                            title: "Heading 2",
                            class: "ck-heading_heading2",
                        },
                    ],
                },
                // 🔥 Konfigurasi upload image
                simpleUpload: {
                    uploadUrl: "/upload-image",
                    withCredentials: true,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                },
                // 🔥 Konfigurasi upload file
                fileUpload: {
                    uploadUrl: "/upload",
                    withCredentials: true,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                },
                placeholder: options.placeholder || "",
            };

            try {
                const editor = await ClassicEditor.create(el, baseConfig);
                window.documentEditors[containerId] = editor;

                // 🔥 PENTING: Tambahkan commentable_id ke setiap upload
                editor.plugins.get("FileRepository").createUploadAdapter = (
                    loader
                ) => {
                    return {
                        upload: async () => {
                            const file = await loader.file;
                            const formData = new FormData();
                            formData.append("upload", file);

                            // ✅ Kirim commentable info
                            const commentId = this.generateUUID();
                            formData.append(
                                "attachable_type",
                                "App\\Models\\Comment"
                            );
                            formData.append("attachable_id", commentId);

                            const response = await fetch("/upload-image", {
                                method: "POST",
                                body: formData,
                                headers: {
                                    "X-CSRF-TOKEN": document.querySelector(
                                        'meta[name="csrf-token"]'
                                    ).content,
                                },
                            });

                            const data = await response.json();
                            return { default: data.url };
                        },
                    };
                };

                editor.model.document.on("change:data", () => {
                    const data = editor.getData();
                    const ev = new CustomEvent("editor-change", {
                        detail: { id: containerId, data },
                    });
                    window.dispatchEvent(ev);
                });

                return editor;
            } catch (err) {
                console.error("Editor creation error:", err);
                el.innerHTML = `<textarea id="${containerId}-fallback" class="w-full min-h-[120px] p-3 border border-gray-300 rounded-lg bg-white resize-none">${
                    options.initial || ""
                }</textarea>`;
                return null;
            }
        },

        destroyEditorForDocument(containerId) {
            const ed = window.documentEditors[containerId];
            if (ed) {
                ed.destroy()
                    .then(() => {
                        delete window.documentEditors[containerId];
                    })
                    .catch((e) => {
                        console.warn("Destroy editor error:", e);
                        delete window.documentEditors[containerId];
                    });
            } else {
                const ta = document.getElementById(containerId + "-fallback");
                if (ta) ta.remove();
            }
        },

        getDocumentEditorData(containerId) {
            const ed = window.documentEditors[containerId];
            if (ed) return ed.getData();
            const ta = document.getElementById(containerId + "-fallback");
            return ta ? ta.value : "";
        },

        initReplyEditorForDocument(commentId) {
            const containerId = "document-reply-editor-" + commentId;
            return this.createEditorForDocument(containerId, {
                placeholder: "Ketik balasan Anda di sini...",
            });
        },

        destroyReplyEditorForDocument(commentId) {
            const containerId = "document-reply-editor-" + commentId;
            this.destroyEditorForDocument(containerId);
        },

        getDocumentReplyEditorDataFor(commentId) {
            return this.getDocumentEditorData(
                "document-reply-editor-" + commentId
            );
        },

        destroyDocumentMainEditor() {
            this.destroyEditorForDocument("document-main-comment-editor");
        },
    };
};

// ===== DOKUMEN EDITOR FUNCTIONS =====
window.documentEditors = {};

// Export functions untuk akses global
window.createEditorForDocument =
    window.documentCommentSection.prototype.createEditorForDocument;
window.destroyEditorForDocument =
    window.documentCommentSection.prototype.destroyEditorForDocument;
window.getDocumentEditorData =
    window.documentCommentSection.prototype.getDocumentEditorData;
window.initReplyEditorForDocument =
    window.documentCommentSection.prototype.initReplyEditorForDocument;
window.destroyReplyEditorForDocument =
    window.documentCommentSection.prototype.destroyReplyEditorForDocument;
window.getDocumentReplyEditorDataFor =
    window.documentCommentSection.prototype.getDocumentReplyEditorDataFor;
