console.log("✅ dokumen-script.js loaded");

// ==========================================
// 🔥 HELPER: Reusable SweetAlert
// ==========================================
function showCustomSwal({
    icon,
    title,
    text,
    timer = 1000,
    showConfirmButton = false,
}) {
    if (!window.Swal) {
        console.warn("SweetAlert2 not loaded");
        return;
    }

    return Swal.fire({
        icon: icon,
        title: title,
        text: text,
        showConfirmButton: showConfirmButton,
        timer: showConfirmButton ? undefined : timer,
        timerProgressBar: !showConfirmButton,
        position: "center",
        toast: false,
        background: "#f7faff",
        color: "#2b2b2b",
        customClass: {
            popup: "swal-custom-popup",
            title: "swal-custom-title",
            htmlContainer: "swal-custom-text",
        },
        didOpen: (popup) => {
            popup.classList.add("swal-fade-in");
        },
        willClose: (popup) => {
            popup.classList.remove("swal-fade-in");
            popup.classList.add("swal-fade-out");
        },
    });
}

// ✅ TAMBAHKAN di bagian atas (sebelum export default)
window.initCompanyDocuments = function (folders, rootFiles, company) {
    const event = new CustomEvent("init-company-documents", {
        detail: { folders, rootFiles, company },
    });
    window.dispatchEvent(event);
};

window.initWorkspaceDocuments = function (folders, rootFiles, workspace) {
    const event = new CustomEvent("init-workspace-documents", {
        detail: { folders, rootFiles, workspace },
    });
    window.dispatchEvent(event);
};

// ===== DOKUMEN SEARCH FUNCTIONS =====
export default function documentSearch() {
    console.log("🚀 documentSearch() function LOADED");
    return {
        // State Properties
        searchQuery: "",
        ready: false,
        _submittingFolder: false, // ✅ TAMBAHKAN INI
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
        showDeleteMultipleModal: false,
        editFolderName: "",
        editIsSecretFolder: false,
        originalIsSecretFolder: null,
        currentFile: null,
        selectMode: false,
        selectedDocuments: [],
        selectedWorkspace: null,
        selectedFolder: null,
        currentModalFolder: null, // ✅ BARU - folder yang sedang dibuka di modal
        modalFolderHistory: [], // ✅ BARU - history navigasi folder di modal
        modalBreadcrumbs: [], // ✅ BARU - breadcrumb folder di modal
        loadingModalFolders: false, // ✅ BARU - loading state
        availableModalFolders: [], // ✅ BARU - daftar folder yang ditampilkan
        availableModalFiles: [], // ⬅️ TAMBAHKAN di data properties
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
        // ✅ TAMBAHAN: Property untuk track context
        currentContext: null, // 'workspace' atau 'company'
        currentWorkspaceId: null,
        currentCompanyId: null,
        currentWorkspace: null,
        currentCompany: null,
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
        loadingWorkspaces: false,
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
            const handlePopState = (event) => {
                console.log("=== POPSTATE TRIGGERED ===");
                console.log("event.state:", event.state);
                console.log("current URL:", window.location.href);
                console.log("history.length:", history.length);

                const url = new URL(window.location);
                const folderIdFromUrl = url.searchParams.get("folder");
                const fileIdFromUrl = url.searchParams.get("file");

                // ✅ FILE STATE
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
                        // ✅ PERBAIKAN SIMPLE: Langsung pakai getFolderPathFull
                        const folderId = event.state.folderId || file.folder_id;
                        let folderPath = [];

                        if (folderId) {
                            // getFolderPathFull(folderId) sudah return full path INCLUDING folderId itu sendiri
                            folderPath = this.getFolderPathFull(folderId);
                            console.log(
                                "📂 Rebuilt folderPath for file:",
                                folderPath.map((f) => f.name)
                            );
                        }

                        this.currentFile = {
                            ...file,
                            folder: file.folder || null,
                            folderPath: folderPath,
                        };

                        this.currentFileUploadedBy =
                            file.uploaded_by || file.uploader?.id || null;
                        this.currentFolder = null;
                        this.breadcrumbs = [];
                        this.folderHistory = [];
                        this.loadMembersFromAPI();

                        console.log("✅ Restored file via popstate:", fileId);
                        console.log(
                            "📂 File folderPath:",
                            this.currentFile.folderPath.map((f) => f.name)
                        );
                    } else {
                        this.currentFile = null;
                        this.currentFolder = null;
                    }
                    return;
                }

                // ✅ FOLDER STATE
                if (event.state && event.state.folderId) {
                    console.log(
                        "🔹 Detected folderId (popstate):",
                        event.state.folderId
                    );

                    const folder = this.folders.find(
                        (f) => String(f.id) === String(event.state.folderId)
                    );

                    if (folder) {
                        console.log("📂 Restoring folder:", folder.name);

                        // ✅ REBUILD breadcrumb path dari parent chain
                        if (folder.parent_id) {
                            const fullPath = this.getFolderPath(
                                folder.parent_id
                            );
                            this.folderHistory = fullPath;
                            console.log(
                                "🔄 Folder history rebuilt from popstate:",
                                this.folderHistory
                            );
                        } else {
                            this.folderHistory = [];
                        }

                        // Set current folder
                        this.currentFolder = folder;
                        this.currentFile = null; // ✅ Clear file
                        this.currentFolderCreatedBy =
                            folder.creator?.id || folder.creator_id || null;
                        this.currentFileUploadedBy = null; // ✅ Clear file uploader

                        // Update breadcrumbs
                        this.updateBreadcrumbs();

                        // Load members
                        this.loadMembersFromAPI();

                        console.log(
                            "✅ Restored folder via popstate with full path:",
                            folder.name
                        );
                        console.log("📂 Breadcrumbs:", this.breadcrumbs);
                    } else {
                        this.currentFolder = null;
                        this.currentFile = null;
                        this.updateBreadcrumbs();
                    }
                    return;
                }

                // ✅ ROOT STATE (no folder, no file)
                console.log("🔹 No state -> going root");
                this.currentFolder = null;
                this.currentFile = null; // ✅ Clear file
                this.folderHistory = [];
                this.currentFolderCreatedBy = null;
                this.currentFileUploadedBy = null;
                this.updateBreadcrumbs();
            };

            // ✅ TAMBAHAN: Listen untuk workspace documents
            window.addEventListener("init-workspace-documents", (e) => {
                this.currentContext = "workspace";
                this.currentWorkspace = e.detail.workspace;
                this.currentWorkspaceId = e.detail.workspace.id;
                this.currentCompany = null;
                this.currentCompanyId = null;

                this.initData(
                    e.detail.folders,
                    e.detail.rootFiles,
                    e.detail.workspace
                );
            });

            // ✅ TAMBAHAN: Listen untuk company documents
            window.addEventListener("init-company-documents", (e) => {
                this.currentContext = "company";
                this.currentCompany = e.detail.company;
                this.currentCompanyId = e.detail.company.id;
                this.currentWorkspace = null;
                this.currentWorkspaceId = null;

                this.initData(
                    e.detail.folders,
                    e.detail.rootFiles,
                    e.detail.company
                );
            });

            window.addEventListener("popstate", handlePopState);

            // pageshow untuk bfcache
            window.addEventListener("pageshow", (e) => {
                if (e.persisted) {
                    if (window.Swal) {
                        try {
                            Swal.close();
                        } catch (err) {
                            /* ignore */
                        }
                    }
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

            // 🔥 TAMBAHKAN LOGGING INI
            console.log("📍 setHistoryState called:");
            console.log("   - replace:", replace);
            console.log("   - stateObj:", stateObj);
            console.log("   - newUrl:", newUrl);
            console.log("   - current URL:", window.location.href);
            console.log("   - history.length BEFORE:", history.length);

            try {
                if (replace) {
                    history.replaceState(stateObj, "", newUrl);
                    console.log("   ✅ REPLACE executed");
                } else {
                    history.pushState(stateObj, "", newUrl);
                    console.log("   ✅ PUSH executed");
                }
                console.log("   - history.length AFTER:", history.length);
            } catch (e) {
                console.warn("history set error", e);
            }
        },

        // Function untuk inisialisasi data
        initData(foldersData, rootFilesData, context) {
            console.log("🚀 ========== initData START ==========");
            console.log("📂 foldersData:", foldersData);
            console.log("📄 rootFilesData:", rootFilesData);
            console.log("🔍 context:", context);
            console.log("📌 currentContext:", this.currentContext);

            // Simpan data dari backend
            this.backendFolders = foldersData;
            this.backendRootFiles = rootFilesData;

            // ✅ Set workspace atau company ID untuk fetch API
            if (this.currentContext === "workspace") {
                this.currentWorkspace = context;
                this.currentWorkspaceId = context.id;
            } else if (this.currentContext === "company") {
                this.currentCompany = context;
                this.currentCompanyId = context.id;
            }

            // Convert data Laravel Collection ke format Alpine
            this.processBackendData();

            console.log(
                "✅ initData selesai, folders count:",
                this.folders.length
            );

            this.$nextTick(() => {
                this.restoreFolderFromUrl();
            });
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

        // Tambahkan di bagian methods
        confirmDeleteMultiple() {
            if (this.selectedDocuments.length === 0) return;

            this.showDeleteMultipleModal = true;
        },

        async submitDeleteMultiple() {
            if (this.selectedDocuments.length === 0) return;

            // ✅ Loading dengan progress yang mirip upload
            Swal.fire({
                title: "Menghapus berkas...",
                html: `
            <div class="mb-4">
                <div class="text-sm text-gray-600 mb-2">
                    Sedang menghapus <span id="deleteProgress">0</span> dari ${this.selectedDocuments.length} berkas
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="deleteProgressBar" class="bg-red-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                background: "#f7faff",
                customClass: {
                    popup: "swal-custom-popup",
                },
            });

            try {
                const csrfToken = document.querySelector(
                    'meta[name="csrf-token"]'
                )?.content;

                // ✅ Tentukan endpoint berdasarkan context
                const endpoint =
                    this.currentContext === "company"
                        ? "/company-documents/delete-multiple"
                        : "/documents/delete-multiple";

                console.log("🗑️ Deleting from endpoint:", endpoint);

                const response = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        documents: this.selectedDocuments.map((doc) => ({
                            id: doc.id,
                            type: doc.type === "Folder" ? "folder" : "file",
                        })),
                    }),
                });

                // ✅ Simulasi progress (karena delete tidak bisa track real progress)
                let progress = 0;
                const interval = setInterval(() => {
                    progress += 10;
                    if (progress <= 90) {
                        document.getElementById(
                            "deleteProgressBar"
                        ).style.width = progress + "%";
                        const currentCount = Math.floor(
                            (progress / 100) * this.selectedDocuments.length
                        );
                        document.getElementById("deleteProgress").textContent =
                            currentCount;
                    }
                }, 100);

                const data = await response.json();
                clearInterval(interval);

                // ✅ Set progress ke 100%
                document.getElementById("deleteProgressBar").style.width =
                    "100%";
                document.getElementById("deleteProgress").textContent =
                    this.selectedDocuments.length;

                if (data.success) {
                    this.showDeleteMultipleModal = false;
                    this.selectedDocuments = [];
                    this.selectMode = false;

                    showCustomSwal({
                        icon: "success",
                        title: "Berhasil!",
                        text: data.message || "Berkas berhasil dihapus",
                        timer: 1000,
                        showConfirmButton: false,
                    });

                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showCustomSwal({
                        icon: "error",
                        title: "Gagal!",
                        text: data.message || "Gagal menghapus berkas",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("❌ Error deleting documents:", error);
                showCustomSwal({
                    icon: "error",
                    title: "Error!",
                    text: "Terjadi kesalahan saat menghapus berkas",
                    showConfirmButton: true,
                });
            }
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
                console.log("👤 Raw uploaded_by:", file.uploaded_by);
                console.log("👤 Raw uploader:", file.uploader);

                const originalName = file.name || file.file_name || null;
                const extractedName = file.file_url
                    ? file.file_url.split("/").pop()
                    : null;
                // Prioritaskan originalName bila ada, kalau gak ada pakai extractedName
                const displayName =
                    originalName || extractedName || "Unknown File";

                // Dapatkan type dari displayName (getFileType harus mampu menerima nama)
                const type = this.getFileType(displayName);

                const uploaderId =
                    file.uploaded_by || file.uploader?.id || null;

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
                    uploaded_by: uploaderId,
                    creator: {
                        // perhatikan properti uploader: kamu pakai full_name di data
                        name:
                            file.uploader?.full_name ||
                            file.uploader?.name ||
                            "Unknown",
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

            // ✅ PERBAIKAN: Pastikan semua breadcrumb punya data lengkap
            return this.currentFile.folderPath.map((crumb) => {
                const fullData = this.folders.find((f) => f.id === crumb.id);
                return fullData || crumb;
            });
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
            console.log("searchQuery:", this.searchQuery);
            console.log("searchQuery length:", this.searchQuery.length);

            // ✅ Kosongkan hasil jika query kosong
            if (this.searchQuery.trim() === "") {
                this.filteredDocuments = [];
                return;
            }

            // ✅ TAMBAHAN: Minimal 2 karakter baru filter
            if (this.searchQuery.trim().length < 2) {
                console.log("⚠️ Query terlalu pendek, minimal 2 karakter");
                this.filteredDocuments = [];
                return;
            }

            const query = this.searchQuery.toLowerCase();
            let documentsToSearch = [];

            if (this.currentFolder) {
                // ✅ Jika di dalam folder, hanya search isi folder tersebut
                console.log(
                    "🔍 Searching inside folder:",
                    this.currentFolder.name
                );

                // Gunakan data yang sudah ada di currentFolder
                documentsToSearch = [
                    ...this.currentFolder.subFolders,
                    ...this.currentFolder.files,
                ];

                console.log(
                    "📁 documentsToSearch dalam folder:",
                    documentsToSearch.length
                );
            } else {
                // ✅ Jika di root, search semua dokumen
                console.log("🏠 Searching in root");

                const allFolders = this.getAllFolders(this.folders);
                const allFiles = this.getAllFiles(this.folders);
                const rootFiles = this.allFiles || [];

                documentsToSearch = [...allFolders, ...allFiles, ...rootFiles];

                console.log("📁 allFolders:", allFolders.length);
                console.log("📄 allFiles:", allFiles.length);
                console.log("📄 rootFiles:", rootFiles.length);
            }

            console.log(
                "🔎 Total documentsToSearch:",
                documentsToSearch.length
            );

            // Filter berdasarkan nama atau tipe
            this.filteredDocuments = documentsToSearch.filter((doc) => {
                const matchName = doc.name.toLowerCase().includes(query);
                const matchType =
                    doc.type && doc.type.toLowerCase().includes(query);

                return matchName || matchType;
            });

            console.log(
                "✨ filteredDocuments result:",
                this.filteredDocuments.length
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

        // ==========================================
        // 🔥 FORM SUBMISSION HANDLERS
        // ==========================================

        // 1️⃣ Handle Upload File
        // 1️⃣ Handle Upload File (MULTIPLE SUPPORT)
// 1️⃣ Handle Upload File (MULTIPLE SUPPORT)
async handleFileUpload(event) {
    console.log("🚀 handleFileUpload called");

    // ✅ FIX: Ambil fileInput dari form, bukan dari event.target
    const form = event.target;
    const fileInput = form.querySelector('input[type="file"]');
    
    if (!fileInput || !fileInput.files) {
        console.error("❌ File input not found");
        return;
    }

    const files = Array.from(fileInput.files);

    if (files.length === 0) return;

    // ✅ Validasi semua file dulu
    const validFiles = [];
    for (const file of files) {
        const fileSizeMB = file.size / 1024 / 1024;
        const isVideo = file.type.startsWith("video/");

        if (isVideo && fileSizeMB > 100) {
            showCustomSwal({
                icon: "error",
                title: "File Terlalu Besar!",
                text: `Video "${file.name}" maksimal 100 MB (${fileSizeMB.toFixed(2)} MB).`,
                showConfirmButton: true,
            });
            continue;
        }

        if (!isVideo && fileSizeMB > 20) {
            showCustomSwal({
                icon: "error",
                title: "File Terlalu Besar!",
                text: `"${file.name}" maksimal 20 MB (${fileSizeMB.toFixed(2)} MB).`,
                showConfirmButton: true,
            });
            continue;
        }

        validFiles.push(file);
    }

    if (validFiles.length === 0) {
        fileInput.value = "";
        return;
    }

    // ✅ Show modal dengan list semua file
    const fileListHTML = validFiles.map((f, idx) => `
        <div class="flex items-center justify-between py-2 border-b">
            <span class="text-sm text-gray-700 truncate flex-1">${idx + 1}. ${f.name}</span>
            <div class="flex items-center gap-2">
                <span id="progress-${idx}" class="text-xs text-gray-500">0%</span>
                <div class="w-16 bg-gray-200 rounded-full h-1.5">
                    <div id="bar-${idx}" class="bg-blue-600 h-1.5 rounded-full transition-all" style="width: 0%"></div>
                </div>
            </div>
        </div>
    `).join('');

    Swal.fire({
        title: "Mengunggah File...",
        html: `
            <div class="text-left max-h-64 overflow-y-auto">
                ${fileListHTML}
            </div>
            <div class="mt-4 text-sm text-gray-600">
                <span id="currentFile">0</span> / ${validFiles.length} file selesai
            </div>
        `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        background: "#f7faff",
    });

    // ✅ Upload satu per satu
    for (let i = 0; i < validFiles.length; i++) {
        await this.uploadSingleDocumentFile(validFiles[i], i, validFiles.length);
    }

    fileInput.value = "";

    showCustomSwal({
        icon: "success",
        title: "Berhasil!",
        text: `${validFiles.length} file berhasil diunggah`,
        timer: 2000,
        showConfirmButton: false,
    });

    setTimeout(() => {
        window.location.reload();
    }, 2000);
},

// 2️⃣ Upload single DOCUMENT file (NAMA BARU)
async uploadSingleDocumentFile(file, index, totalFiles) {
    const formData = new FormData();
    formData.append("file", file);

    if (this.currentFolder) {
        formData.append("folder_id", this.currentFolder.id);
    }

    if (this.currentContext === "workspace") {
        formData.append("workspace_id", this.currentWorkspaceId);
    } else if (this.currentContext === "company") {
        formData.append("company_id", this.currentCompanyId);
    }

    const endpoint = this.currentContext === "company"
        ? "/company-documents/file"
        : "/file";

    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();

        xhr.upload.addEventListener("progress", (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                const barEl = document.getElementById(`bar-${index}`);
                const progressEl = document.getElementById(`progress-${index}`);
                if (barEl) barEl.style.width = percent + "%";
                if (progressEl) progressEl.textContent = percent + "%";
            }
        });

        xhr.onload = function () {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                if (data.success) {
                    const counterEl = document.getElementById("currentFile");
                    if (counterEl) counterEl.textContent = index + 1;
                    resolve(data);
                } else {
                    reject(new Error(data.message || "Upload failed"));
                }
            } else {
                reject(new Error(`HTTP ${xhr.status}`));
            }
        };

        xhr.onerror = () => reject(new Error("Network error"));

        xhr.open("POST", endpoint);
        xhr.setRequestHeader("X-CSRF-TOKEN", document.querySelector('meta[name="csrf-token"]').content);
        xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhr.send(formData);
    });
},

        // Tambahkan method baru (sekitar baris 800-900, setelah method lainnya)
        async loadAvailableWorkspaces() {
            console.log("🔍 loadAvailableWorkspaces called");
            console.log("📍 currentContext:", this.currentContext);

            this.loadingWorkspaces = true;
            this.selectedWorkspace = "";

            try {
                // ✅ PERBAIKAN: Pilih endpoint berdasarkan context
                const endpoint =
                    this.currentContext === "company"
                        ? "/company-documents/workspaces" // ⬅️ Company endpoint
                        : "/api/user/workspaces"; // ⬅️ Workspace endpoint

                console.log("🔗 Fetching from:", endpoint);

                const response = await fetch(endpoint);

                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error(
                        "❌ Response is not JSON:",
                        text.substring(0, 500)
                    );
                    throw new Error("Server returned HTML instead of JSON");
                }

                const data = await response.json();

                if (data.success) {
                    this.availableWorkspaces = data.workspaces;
                    console.log(
                        "✅ Available workspaces:",
                        this.availableWorkspaces.length
                    );
                } else {
                    throw new Error(
                        data.message || "Failed to load workspaces"
                    );
                }
            } catch (error) {
                console.error("❌ Error loading workspaces:", error);

                showCustomSwal({
                    icon: "error",
                    title: "Error!",
                    text: error.message,
                    showConfirmButton: true,
                });
            } finally {
                this.loadingWorkspaces = false;
            }
        },

        // ✅ Method untuk memilih workspace dan load root folders
        // ✅ Method untuk memilih workspace dan load root folders
        selectWorkspaceForMove(workspace) {
            console.log("🔥 selectWorkspaceForMove called");
            console.log("📦 workspace param:", workspace);

            if (!workspace) {
                console.warn("⚠️ No workspace provided");
                return;
            }

            console.log("📂 Workspace selected:", workspace.name);
            console.log("📂 Workspace ID:", workspace.id);

            // ✅ PENTING: Set selectedWorkspace sebagai ID (string), bukan object
            // Karena x-model bind ke ID dari <option :value="workspace.id">
            this.selectedWorkspace = workspace.id;

            console.log(
                "✅ this.selectedWorkspace set to:",
                this.selectedWorkspace
            );

            // Reset state
            this.selectedWorkspace = workspace.id;
            this.selectedFolder = null;
            this.currentModalFolder = null;
            this.modalFolderHistory = [];
            this.modalBreadcrumbs = [];

            // Load root folders dari workspace yang dipilih
            this.availableModalFolders = workspace.folders || [];
            this.availableModalFiles = workspace.files || []; // ⬅️ TAMBAHKAN INI

            console.log(
                "📁 Root folders loaded:",
                this.availableModalFolders.length
            );
        },

        // ✅ Method untuk masuk ke dalam folder di modal
        openModalFolder(folder) {
            console.log("📂 Opening modal folder:", folder.name);

            if (this.currentModalFolder) {
                this.modalFolderHistory.push(this.currentModalFolder);
            }

            this.currentModalFolder = folder;
            this.updateModalBreadcrumbs();

            // ✅ Load subfolders DAN files
            this.loadModalSubfolders(folder.id);
        },

        // ✅ Method untuk kembali ke folder sebelumnya
        goBackModalFolder() {
            console.log("🔙 Going back in modal folder navigation");

            if (this.modalFolderHistory.length > 0) {
                // Ambil folder terakhir dari history
                const previousFolder = this.modalFolderHistory.pop();
                this.currentModalFolder = previousFolder;

                // Update breadcrumbs
                this.updateModalBreadcrumbs();

                // Load subfolders dari folder sebelumnya
                this.loadModalSubfolders(previousFolder.id);
            } else {
                // ✅ PERBAIKAN: Kembali ke root dengan load ulang data
                this.goToModalRoot();
            }
        },

        // ✅ Method untuk kembali ke root workspace
        goToModalRoot() {
            console.log("🏠 Going to modal root");

            this.currentModalFolder = null;
            this.modalFolderHistory = [];
            this.modalBreadcrumbs = [];

            // ✅ PERBAIKAN: Load ulang root folders dari workspace yang dipilih
            if (this.selectedWorkspace) {
                const workspace = this.availableWorkspaces.find(
                    (w) => String(w.id) === String(this.selectedWorkspace)
                );

                if (workspace) {
                    this.availableModalFolders = workspace.folders || [];
                    this.availableModalFiles = workspace.files || [];

                    console.log(
                        "✅ Root folders reloaded:",
                        this.availableModalFolders.length
                    );
                    console.log(
                        "✅ Root files reloaded:",
                        this.availableModalFiles.length
                    );
                }
            }
        },

        // ✅ Method untuk update breadcrumbs di modal
        updateModalBreadcrumbs() {
            if (!this.currentModalFolder) {
                this.modalBreadcrumbs = [];
                return;
            }

            // Build breadcrumbs dari history + current
            this.modalBreadcrumbs = [
                ...this.modalFolderHistory,
                this.currentModalFolder,
            ];

            console.log(
                "📍 Modal breadcrumbs:",
                this.modalBreadcrumbs.map((f) => f.name)
            );
        },

        // ✅ Method untuk load subfolders dari API
        async loadModalSubfolders(folderId) {
            this.loadingModalFolders = true;

            try {
                const response = await fetch(
                    `/api/folders/${folderId}/subfolders`
                );
                const data = await response.json();

                if (data.success) {
                    this.availableModalFolders = data.folders || [];
                    this.availableModalFiles = data.files || []; // ⬅️ TAMBAHKAN INI

                    console.log(
                        "✅ Subfolders loaded:",
                        this.availableModalFolders.length
                    );
                    console.log(
                        "✅ Files loaded:",
                        this.availableModalFiles.length
                    );
                }
            } catch (error) {
                console.error("❌ Error loading subfolders:", error);
                this.availableModalFolders = [];
                this.availableModalFiles = []; // ⬅️ TAMBAHKAN INI
            } finally {
                this.loadingModalFolders = false;
            }
        },

        // ✅ Method untuk navigasi breadcrumb (klik breadcrumb tertentu)
        navigateToModalFolder(folder) {
            console.log("🔹 Navigating to modal folder:", folder.name);

            const folderIndex = this.modalBreadcrumbs.findIndex(
                (f) => f.id === folder.id
            );

            if (folderIndex > -1) {
                // Potong history sampai index folder yang diklik
                this.modalFolderHistory = this.modalBreadcrumbs.slice(
                    0,
                    folderIndex
                );
                this.currentModalFolder = folder;

                // Update breadcrumbs
                this.updateModalBreadcrumbs();

                // Load subfolders
                this.loadModalSubfolders(folder.id);
            } else if (
                folderIndex === -1 &&
                this.modalBreadcrumbs.length === 0
            ) {
                // ✅ PERBAIKAN: Jika breadcrumbs kosong, berarti kembali ke root
                this.goToModalRoot();
            }
        },

        // ✅ Method untuk pilih folder tujuan
        selectFolderDestination(folder) {
            this.selectedFolder = folder;
            console.log("✅ Folder destination selected:", folder.name);
        },

        // ✅ Method untuk clear pilihan folder (pindah ke root)
        clearFolderDestination() {
            this.selectedFolder = null;
            console.log("✅ Destination set to root");
        },

        // Tambahkan method untuk submit move documents
        async submitMoveDocuments() {
            console.log("🔥 submitMoveDocuments called");
            console.log("📍 Context:", this.currentContext);

            if (
                !this.selectedWorkspace ||
                this.selectedDocuments.length === 0
            ) {
                showCustomSwal({
                    icon: "warning",
                    title: "Peringatan!",
                    text: "Pilih workspace tujuan dan dokumen yang akan dipindahkan",
                    showConfirmButton: true,
                });
                return;
            }

            const workspaceId =
                typeof this.selectedWorkspace === "object"
                    ? this.selectedWorkspace.id
                    : this.selectedWorkspace;

            const targetWorkspace = this.availableWorkspaces.find(
                (w) => String(w.id) === String(workspaceId)
            );

            if (!targetWorkspace) {
                showCustomSwal({
                    icon: "error",
                    title: "Error!",
                    text: "Workspace tidak ditemukan. Silakan refresh halaman.",
                    showConfirmButton: true,
                });
                return;
            }

            let targetFolderId = null;

            if (this.selectedFolder) {
                targetFolderId = this.selectedFolder.id;
            } else if (this.currentModalFolder) {
                targetFolderId = this.currentModalFolder.id;
            }

            // ✅ VALIDASI: Cek jika pindah ke lokasi yang sama
            const currentWorkspaceId =
                this.currentWorkspaceId || this.currentWorkspace?.id;
            const currentFolderId = this.currentFolder?.id || null;

            if (
                String(currentWorkspaceId) === String(targetWorkspace.id) &&
                String(currentFolderId) === String(targetFolderId)
            ) {
                showCustomSwal({
                    icon: "warning",
                    title: "Tidak Dapat Memindahkan!",
                    text: "Dokumen sudah berada di lokasi tersebut.",
                    showConfirmButton: true,
                });
                return;
            }

            // Show loading
            showCustomSwal({
                title: "Memindahkan dokumen...",
                text: "Mohon tunggu sebentar",
                showConfirmButton: false,
            });

            if (window.Swal) Swal.showLoading();

            try {
                // ✅ PERBAIKAN: Pilih endpoint berdasarkan context
                const endpoint =
                    this.currentContext === "company"
                        ? "/company-documents/move" // ⬅️ Company endpoint
                        : "/documents/move"; // ⬅️ Workspace endpoint

                console.log("📤 Posting to:", endpoint);

                const csrfToken = document.querySelector(
                    'meta[name="csrf-token"]'
                )?.content;

                if (!csrfToken) {
                    throw new Error("CSRF token not found");
                }

                const payload = {
                    workspace_id: targetWorkspace.id,
                    folder_id: targetFolderId,
                    documents: this.selectedDocuments.map((doc) => ({
                        id: doc.id,
                        type: doc.type === "Folder" ? "folder" : "file",
                    })),
                };

                console.log(
                    "📤 Request payload:",
                    JSON.stringify(payload, null, 2)
                );

                const response = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify(payload),
                });

                const contentType = response.headers.get("content-type");

                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error(
                        "❌ Response is not JSON:",
                        text.substring(0, 500)
                    );
                    throw new Error("Server returned HTML instead of JSON");
                }

                const data = await response.json();

                if (data.success) {
                    this.showMoveDocumentsModal = false;
                    this.selectedWorkspace = null;
                    this.selectedFolder = null;
                    this.currentModalFolder = null;
                    this.modalFolderHistory = [];
                    this.modalBreadcrumbs = [];
                    this.selectedDocuments = [];
                    this.selectMode = false;

                    // ✅ Build message dengan info renamed
                    let alertText = data.message;

                    if (data.renamed_files && data.renamed_files.length > 0) {
                        alertText += "\n\nFile yang di-rename:";
                        data.renamed_files.forEach((file) => {
                            alertText += `\n• ${file.old_name} → ${file.new_name}`;
                        });
                    }

                    showCustomSwal({
                        icon: "success",
                        title: "Berhasil!",
                        text: "File berhasil dipindahkan",
                        html: alertText.replace(/\n/g, "<br>"), // ✅ Gunakan html untuk line break
                        timer: 4000, // ✅ Kasih waktu lebih lama untuk baca
                        showConfirmButton: true,
                    });

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    showCustomSwal({
                        icon: "error",
                        title: "Gagal!",
                        text: data.message || "Gagal memindahkan dokumen",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("❌ Error moving documents:", error);
                showCustomSwal({
                    icon: "error",
                    title: "Error!",
                    text:
                        error.message ||
                        "Terjadi kesalahan saat memindahkan dokumen",
                    showConfirmButton: true,
                });
            }
        },

        // 2️⃣ Handle Create Folder
        async handleCreateFolder(event) {
            console.log("🚀 handleCreateFolder called");

            // ✅ PREVENT DOUBLE SUBMIT
            if (this._submittingFolder) {
                console.warn("⚠️ Already submitting folder, blocked!");
                return;
            }

            this._submittingFolder = true;

            console.log("📍 Call stack:", new Error().stack);
            console.log("📍 currentCompanyId:", this.currentCompanyId);
            console.log("📍 currentContext:", this.currentContext);

            const form = event.target;
            const formData = new FormData(form);
            const url = form.action;

            showCustomSwal({
                title: "Membuat folder...",
                showConfirmButton: false,
            });

            if (window.Swal) Swal.showLoading();

            try {
                const response = await fetch(url, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                });

                const data = await response.json();
                console.log("✅ Create folder response:", data);

                if (data.success && data.redirect_url) {
                    this.showCreateFolderModal = false;
                    this.newFolderName = "";
                    this.isSecretFolder = false;

                    if (data.alert) {
                        showCustomSwal({
                            icon: data.alert.icon,
                            title: data.alert.title,
                            text: data.alert.text,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    }

                    setTimeout(() => {
                        window.location.replace(data.redirect_url);
                    }, 1000);
                } else {
                    showCustomSwal({
                        icon: "error",
                        title: "Error!",
                        text:
                            data.message ||
                            "Terjadi kesalahan saat membuat folder",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("❌ Create folder error:", error);
                showCustomSwal({
                    icon: "error",
                    title: "Error!",
                    text: "Terjadi kesalahan saat membuat folder",
                    showConfirmButton: true,
                });
            } finally {
                // ✅ RESET FLAG SETELAH 2 DETIK
                setTimeout(() => {
                    this._submittingFolder = false;
                }, 2000);
            }
        },

        // 3️⃣ Handle Update Folder
        async handleUpdateFolder(event) {
            console.log("🚀 handleUpdateFolder called");
            console.log("📍 history.length BEFORE update:", history.length);

            const form = event.target;
            const formData = new FormData(form);
            const url = form.action;

            // Loading
            showCustomSwal({
                title: "Memperbarui folder...",
                showConfirmButton: false,
            });

            if (window.Swal) Swal.showLoading();

            try {
                const response = await fetch(url, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                });

                const data = await response.json();
                console.log("✅ Update folder response:", data);

                if (data.success && data.redirect_url) {
                    // Reset modal state
                    this.showEditFolderModal = false;
                    this.editFolderName = "";
                    this.editIsSecretFolder = false;
                    this.editingFolder = null;

                    // Success alert
                    if (data.alert) {
                        showCustomSwal({
                            icon: data.alert.icon,
                            title: data.alert.title,
                            text: data.alert.text,
                            timer: 1700,
                            showConfirmButton: false,
                        });
                    }

                    // Redirect
                    setTimeout(() => {
                        console.log(
                            "📍 history.length BEFORE replace:",
                            history.length
                        );
                        window.location.replace(data.redirect_url);
                    }, 1000);
                } else {
                    showCustomSwal({
                        icon: "error",
                        title: "Error!",
                        text:
                            data.message ||
                            "Terjadi kesalahan saat memperbarui folder",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("❌ Update folder error:", error);

                showCustomSwal({
                    icon: "error",
                    title: "Error!",
                    text: "Terjadi kesalahan saat memperbarui folder",
                    showConfirmButton: true,
                });
            }
        },

        // 4️⃣ Handle Update File
        async handleUpdateFile(event) {
            console.log("🚀 handleUpdateFile called");

            const form = event.target;
            const formData = new FormData(form);
            const url = form.action;

            formData.append("_method", "PUT");

            // Show loading
            showCustomSwal({
                title: "Memperbarui file...",
                text: "Mohon tunggu sebentar",
                showConfirmButton: false,
                timer: undefined, // no timer for loading
            });

            if (window.Swal) {
                Swal.showLoading();
            }

            try {
                const response = await fetch(url, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                });

                const data = await response.json();

                console.log("✅ Update file response:", data);

                if (data.success && data.redirect_url) {
                    // Close modal
                    this.showEditFileModal = false;
                    this.editFileIsSecret = false;
                    this.editingFile = null;

                    // 🔥 Show success alert dengan helper
                    if (data.alert) {
                        showCustomSwal({
                            icon: data.alert.icon,
                            title: data.alert.title,
                            text: data.alert.text,
                            timer: 1700,
                            showConfirmButton: false,
                        });
                    }

                    // 🔥 Redirect dengan location.replace()
                    setTimeout(() => {
                        console.log(
                            "📍 history.length BEFORE replace:",
                            history.length
                        );
                        window.location.replace(data.redirect_url);
                    }, 1500);
                } else {
                    showCustomSwal({
                        icon: "error",
                        title: "Error!",
                        text:
                            data.message ||
                            "Terjadi kesalahan saat memperbarui file",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("❌ Update file error:", error);

                showCustomSwal({
                    icon: "error",
                    title: "Error!",
                    text: "Terjadi kesalahan saat memperbarui file",
                    showConfirmButton: true,
                });
            }
        },

        // 5️⃣ Handle Add Members
        async handleAddMembers(event) {
            console.log("🚀 handleAddMembers called");
            console.log(
                "📍 history.length BEFORE add members:",
                history.length
            );

            const form = event.target;
            const formData = new FormData(form);
            const url = form.action;

            // Loading
            showCustomSwal({
                title: "Menambahkan peserta...",
                showConfirmButton: false,
            });

            if (window.Swal) Swal.showLoading();

            try {
                const response = await fetch(url, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                });

                const data = await response.json();
                console.log("✅ Add members response:", data);

                if (data.success && data.redirect_url) {
                    // Reset modal state
                    this.openAddMemberModal = false;
                    this.searchMember = "";
                    this.selectAll = false;

                    // Success alert
                    if (data.alert) {
                        showCustomSwal({
                            icon: data.alert.icon,
                            title: data.alert.title,
                            text: data.alert.text,
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    }

                    // Redirect
                    setTimeout(() => {
                        console.log(
                            "📍 history.length BEFORE replace:",
                            history.length
                        );
                        window.location.replace(data.redirect_url);
                    }, 1500);
                } else {
                    showCustomSwal({
                        icon: "error",
                        title: "Error!",
                        text:
                            data.message ||
                            "Terjadi kesalahan saat menambahkan peserta",
                        showConfirmButton: true,
                    });
                }
            } catch (error) {
                console.error("❌ Add members error:", error);

                showCustomSwal({
                    icon: "error",
                    title: "Error!",
                    text: "Terjadi kesalahan saat menambahkan peserta",
                    showConfirmButton: true,
                });
            }
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
            console.log("✅ openFolder START, folder:", folder.name);
            console.log("📍 history.length BEFORE openFolder:", history.length);

            this.currentFile = null;
            this.currentFolderCreatedBy = null;
            this.currentFileUploadedBy = null;

            this.$nextTick(() => {
                this.currentFolderCreatedBy =
                    folder.creator?.id || folder.creator_id || null;

                const isFromRestore = this._restoring === true;

                console.log("📍 isFromRestore:", isFromRestore);

                this.setHistoryState(
                    { folderId: folder.id, folderName: folder.name },
                    isFromRestore
                );

                // ✅ PERBAIKAN: Simpan currentFolder LENGKAP ke folderHistory
                if (!isFromRestore) {
                    if (!this.currentFolder) {
                        this.folderHistory = [];
                    } else {
                        const isAlreadyInHistory = this.folderHistory.some(
                            (f) => f.id === this.currentFolder.id
                        );
                        if (!isAlreadyInHistory) {
                            // ✅ Simpan data LENGKAP, bukan copy minimal
                            this.folderHistory.push(this.currentFolder);
                        }
                    }
                }

                this.loadMembersFromAPI();
                this.currentFolder = folder;
                this.currentFile = null;
                this.updateBreadcrumbs();

                console.log(
                    "✅ openFolder END, history.length:",
                    history.length
                );
                console.log("📂 Current breadcrumbs:", this.breadcrumbs);
            });
        },

        navigateToFolder(folder) {
            console.log("🔹 navigateToFolder called:", folder.name);
            console.log("📍 Current breadcrumbs:", this.breadcrumbs);
            console.log("📦 Folder data from breadcrumb:", folder);

            const folderIndex = this.breadcrumbs.findIndex(
                (f) => f.id === folder.id
            );

            if (folderIndex > -1) {
                // Update folderHistory: ambil hanya sampai index folder yang diklik
                this.folderHistory = this.breadcrumbs.slice(0, folderIndex);

                console.log("📂 New folderHistory:", this.folderHistory);

                // ✅ PERBAIKAN: Ambil data folder LENGKAP dari this.folders
                const fullFolderData = this.folders.find(
                    (f) => f.id === folder.id
                );

                if (!fullFolderData) {
                    console.error(
                        "❌ Folder not found in this.folders:",
                        folder.id
                    );
                    return;
                }

                console.log("📦 Full folder data:", fullFolderData);

                // Set current folder dengan data LENGKAP
                this.currentFolder = fullFolderData;
                this.currentFile = null;
                this.currentFolderCreatedBy =
                    fullFolderData.creator?.id ||
                    fullFolderData.creator_id ||
                    null;
                this.currentFileUploadedBy = null;

                // Update breadcrumbs
                this.updateBreadcrumbs();

                // Update URL
                this.setHistoryState(
                    {
                        folderId: fullFolderData.id,
                        folderName: fullFolderData.name,
                    },
                    false
                );

                // Load members
                this.loadMembersFromAPI();

                console.log("✅ navigateToFolder done");
                console.log("📂 Current folder:", this.currentFolder);
                console.log("📅 Created at:", this.currentFolder.createdAt);
                console.log("👤 Creator:", this.currentFolder.creator);
            }
        },

        goToRoot() {
            console.log("🏠 goToRoot called");
            console.log("📍 Before - currentFolder:", this.currentFolder?.name);
            console.log("📍 Before - currentFile:", this.currentFile?.name);

            // Reset state
            this.currentFolder = null;
            this.currentFile = null; // ✅ Clear file
            this.folderHistory = [];
            this.currentFolderCreatedBy = null;
            this.currentFileUploadedBy = null;

            // Update breadcrumbs
            this.updateBreadcrumbs();

            // ✅ Update URL ke root (hapus query params)
            this.setHistoryState({}, false); // {} = no folder/file, false = push

            console.log("✅ goToRoot done");
            console.log("📍 After - currentFolder:", this.currentFolder);
            console.log("📍 After - currentFile:", this.currentFile);
            console.log("📍 history.length:", history.length);
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
            console.log(
                "🔥🔥🔥 ========== restoreFolderFromUrl START =========="
            );
            console.log("🕐 Time:", new Date().toISOString());
            console.log("📍 Current URL:", window.location.href);
            console.log("📂 Folders available:", this.folders.length);
            console.log("🚪 _restoring flag BEFORE:", this._restoring);

            if (this._restoring) {
                console.warn(
                    "⚠️ restoreFolderFromUrl already running, skipping..."
                );
                return;
            }

            this._restoring = true;
            console.log("🚪 _restoring flag SET TO:", this._restoring);

            const url = new URL(window.location);
            const folderIdFromUrl = url.searchParams.get("folder");
            const fileIdFromUrl = url.searchParams.get("file");

            console.log(
                "🔑 URL Params - folder:",
                folderIdFromUrl,
                "file:",
                fileIdFromUrl
            );

            // === HANDLE FILE ===
            if (folderIdFromUrl) {
                console.log("🔹 Found folder param:", folderIdFromUrl);

                const folder = this.folders.find(
                    (f) => String(f.id) === String(folderIdFromUrl)
                );

                if (folder) {
                    console.log("📂 Restoring folder:", folder.name);

                    // ✅ TAMBAHAN: Rebuild folder history dari parent chain
                    if (folder.parent_id) {
                        const fullPath = this.getFolderPath(folder.parent_id);
                        this.folderHistory = fullPath;
                        console.log(
                            "🔄 Folder history rebuilt:",
                            this.folderHistory
                        );
                    } else {
                        this.folderHistory = [];
                    }

                    // Set current folder
                    this.currentFolder = folder;
                    this.currentFolderCreatedBy =
                        folder.creator?.id || folder.creator_id || null;

                    // Update breadcrumbs dengan history yang sudah direbuild
                    this.updateBreadcrumbs();

                    this.$nextTick(() => {
                        this.loadMembersFromAPI();
                        this.ready = true;
                        this._restoring = false;
                        console.log(
                            "✅ Folder restored with full path, history.length:",
                            history.length
                        );
                    });
                } else {
                    console.warn("⚠️ Folder not found");
                    this.ready = true;
                    this._restoring = false;
                }
                return;
            }

            // === HANDLE FOLDER ===
            if (folderIdFromUrl) {
                console.log("🔹 Found folder param:", folderIdFromUrl);

                const folder = this.folders.find(
                    (f) => String(f.id) === String(folderIdFromUrl)
                );

                if (folder) {
                    console.log("📂 Restoring folder:", folder.name);

                    // 🔥 openFolder akan cek this._restoring dan pakai replace=true
                    this.openFolder(folder);

                    this.$nextTick(() => {
                        this.ready = true;
                        this._restoring = false; // ⬅️ RESET FLAG
                        console.log(
                            "✅ Folder restored, history.length:",
                            history.length
                        );
                    });
                } else {
                    console.warn("⚠️ Folder not found");
                    this.ready = true;
                    this._restoring = false; // ⬅️ RESET FLAG
                }
                return;
            }

            // === ROOT STATE ===
            console.log("🔹 No params -> going root");
            this.currentFolder = null;
            this.currentFile = null;
            this.currentFolderCreatedBy = null;
            this.currentFileUploadedBy = null;
            this.ready = true;

            this.$nextTick(() => {
                this._restoring = false; // ⬅️ RESET FLAG
                console.log(
                    "✅ Root restored, history.length:",
                    history.length
                );
            });
        },

        // Update fungsi updateBreadcrumbs agar bisa rebuild dari currentFolder
        updateBreadcrumbs() {
            if (!this.currentFolder) {
                this.breadcrumbs = [];
                return;
            }

            // ✅ PERBAIKAN: Rebuild breadcrumbs dengan data LENGKAP
            if (
                this.folderHistory.length === 0 &&
                this.currentFolder.parent_id
            ) {
                const fullPath = this.getFolderPathFull(
                    this.currentFolder.parent_id
                );
                this.breadcrumbs = fullPath;
                this.folderHistory = [...fullPath];
                console.log(
                    "🔄 Breadcrumbs rebuilt from parent_id:",
                    this.breadcrumbs
                );
            } else {
                // ✅ Pastikan folderHistory berisi data lengkap
                this.breadcrumbs = this.folderHistory.map((crumb) => {
                    const fullData = this.folders.find(
                        (f) => f.id === crumb.id
                    );
                    return fullData || crumb; // fallback ke crumb jika tidak ketemu
                });
            }

            console.log("📂 Final breadcrumbs:", this.breadcrumbs);
        },

        // Fungsi untuk mendapatkan full path dengan data LENGKAP
        getFolderPathFull(folderId) {
            console.log("🔍 getFolderPathFull called for:", folderId);

            const path = [];
            let currentId = folderId;

            while (currentId) {
                const folder = this.folders.find((f) => f.id === currentId);

                if (!folder) {
                    console.warn("⚠️ Folder not found for ID:", currentId);
                    break;
                }

                // ✅ Simpan SELURUH data folder, bukan hanya id, name, parent_id
                path.unshift(folder);

                currentId = folder.parent_id;
            }

            console.log("📂 Full folder path with complete data:", path);
            return path;
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
            console.log("🔹 navigateToFolderFromFile called:", folder.name);
            console.log("📦 Folder data from file breadcrumb:", folder);

            // ✅ PERBAIKAN: Ambil data LENGKAP dari this.folders
            const fullFolderData = this.folders.find((f) => f.id === folder.id);

            if (!fullFolderData) {
                console.error(
                    "❌ Folder not found in this.folders:",
                    folder.id
                );
                return;
            }

            console.log("📦 Full folder data:", fullFolderData);

            // Cari index folder di fileBreadcrumbs (folderPath dari file)
            const folderIndex = this.fileBreadcrumbs.findIndex(
                (f) => f.id === folder.id
            );

            if (folderIndex > -1) {
                // ✅ Rebuild folderHistory dengan data LENGKAP
                this.folderHistory = this.fileBreadcrumbs
                    .slice(0, folderIndex)
                    .map((crumb) => {
                        const fullData = this.folders.find(
                            (f) => f.id === crumb.id
                        );
                        return fullData || crumb;
                    });

                console.log(
                    "📂 New folderHistory from file:",
                    this.folderHistory
                );
            } else {
                this.folderHistory = [];
            }

            // ✅ PENTING: Clear file state
            this.currentFile = null;
            this.currentFileUploadedBy = null;

            // Set current folder dengan data LENGKAP
            this.currentFolder = fullFolderData;
            this.currentFolderCreatedBy =
                fullFolderData.creator?.id || fullFolderData.creator_id || null;

            // Update breadcrumbs
            this.updateBreadcrumbs();

            // Update URL
            this.setHistoryState(
                {
                    folderId: fullFolderData.id,
                    folderName: fullFolderData.name,
                },
                false
            );

            // Load members
            this.loadMembersFromAPI();

            console.log("✅ navigateToFolderFromFile done");
            console.log("📂 Current folder:", this.currentFolder);
            console.log("📄 Current file (should be null):", this.currentFile);
            console.log("📂 Breadcrumbs:", this.breadcrumbs);
        },

        openFile(file) {
            console.log("openFile dipanggil", file);
            console.log(
                "📂 Current breadcrumbs before open:",
                this.breadcrumbs
            );
            console.log("📁 Current folder before open:", this.currentFolder);

            // Simpan folder context sebelum clear
            const parentFolder = this.currentFolder;

            // ✅ Build folderPath dengan data LENGKAP
            let folderPath = [];
            if (parentFolder) {
                // ✅ PERBAIKAN: Ambil data lengkap dari this.folders, bukan dari breadcrumbs
                const fullBreadcrumbs = this.breadcrumbs.map((crumb) => {
                    const fullData = this.folders.find(
                        (f) => f.id === crumb.id
                    );
                    return fullData || crumb;
                });

                // Ambil data lengkap parentFolder juga
                const fullParentData =
                    this.folders.find((f) => f.id === parentFolder.id) ||
                    parentFolder;

                folderPath = [...fullBreadcrumbs, fullParentData];
            }

            console.log(
                "📂 Folder path for file (with full data):",
                folderPath
            );

            // Clear folder UI
            this.currentFolder = null;
            this.currentFolderCreatedBy = null;

            const fileFolder = file.folder || parentFolder || null;
            const folderId =
                (fileFolder && fileFolder.id) || file.folder_id || null;

            this.currentFile = {
                ...file,
                folder: fileFolder,
                folderPath: folderPath, // ✅ Gunakan folderPath dengan data lengkap
                creator: file.creator || this.getCurrentUser(),
                createdAt: file.createdAt || new Date().toISOString(),
                size:
                    file.size || this.formatFileSize(file.size || 1024 * 1024),
                recipients: file.recipients || this.getDefaultRecipients(),
                comments: file.comments || this.getDefaultComments(),
            };

            this.currentFileUploadedBy = file.uploaded_by;
            this.loadMembersFromAPI();

            // Push file state (file view) so back/forward works
            this.setHistoryState(
                { fileId: file.id, folderId: folderId },
                false
            );

            console.log(
                "📂 openFile selesai, file pushed to history:",
                file.id
            );
            console.log("📂 File folderPath:", this.currentFile.folderPath);
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
            console.log("🔍 getDisplayedDocuments called");
            console.log("📂 currentFolder:", this.currentFolder?.name);

            if (this.searchQuery && this.filteredDocuments.length > 0) {
                return this.filteredDocuments;
            }

            if (this.currentFolder) {
                console.log(
                    "📁 Showing subfolders:",
                    this.currentFolder.subFolders.length
                );
                console.log(
                    "📄 Showing files:",
                    this.currentFolder.files.length
                );

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

        // Fungsi untuk mendapatkan full path dari folder (dari root sampai folder target)
        getFolderPath(folderId) {
            return this.getFolderPathFull(folderId);
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
            console.log("🔄 loadMembersFromAPI called");
            console.log("📋 currentContext:", this.currentContext);
            console.log(
                "📋 currentFolderCreatedBy:",
                this.currentFolderCreatedBy
            );
            console.log(
                "📋 currentFileUploadedBy:",
                this.currentFileUploadedBy
            );

            this.isLoadingPermission = true;

            const params = new URLSearchParams({
                folder_created_by: this.currentFolderCreatedBy ?? "",
                file_uploaded_by: this.currentFileUploadedBy ?? "",
            });

            // ✅ Tentukan endpoint berdasarkan context
            let endpoint;
            if (this.currentContext === "workspace") {
                endpoint = `/workspaces/${this.currentWorkspaceId}/members?${params}`;
            } else if (this.currentContext === "company") {
                endpoint = `/company-documents/members?${params}`;
            } else {
                console.error("❌ Unknown context:", this.currentContext);
                this.isLoadingPermission = false;
                return;
            }

            console.log("🔗 Fetching:", endpoint);

            fetch(endpoint)
                .then(async (res) => {
                    console.log("✅ Response status:", res.status);
                    this.memberListAllowed = res.status === 200;
                    if (!this.memberListAllowed) {
                        console.warn("⚠️ memberListAllowed = false");
                        this.members = [];
                        return null;
                    }
                    return await res.json();
                })
                .then(async (data) => {
                    if (!data?.members) return;

                    // Fetch recipients
                    const docId =
                        this.currentFolder?.id || this.currentFile?.id;
                    const recipientsRes = await fetch(
                        `/documents/${docId}/recipients`
                    );
                    const recipientsData = await recipientsRes.json();
                    const selectedUserIds = recipientsData?.recipients || [];

                    this.members = data.members.map((m) => ({
                        ...m,
                        selected: selectedUserIds.includes(m.id),
                    }));

                    this.selectAll =
                        this.members.length > 0 &&
                        this.members.every((m) => m.selected);
                })
                .catch((error) => {
                    console.error("❌ Error loading members:", error);
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

function insertUploadImageButtonToToolbar(editor, commentId) {
    const toolbarEl = editor.ui.view.toolbar.element;
    const itemsContainer =
        toolbarEl.querySelector(".ck-toolbar__items") || toolbarEl;

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "ck ck-button";
    btn.title = "Upload Image";
    btn.innerHTML = `
        <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                <path d="M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 11a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM5 19l4.5-6 3.5 4.5 2.5-3L19 19H5z"/>
            </svg>
        </span>
    `;
    btn.style.marginLeft = "6px";
    btn.style.cursor = "pointer";

    btn.addEventListener("click", () => {
        const input = document.createElement("input");
        input.type = "file";
        input.accept = "image/*";
        input.click();

        input.addEventListener(
            "change",
            async (e) => {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append("upload", file);
                formData.append("attachable_id", commentId);
                formData.append("attachable_type", "App\\Models\\Comment");

                try {
                    const res = await fetch("/upload-image", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,
                        },
                        body: formData,
                    });

                    const data = await res.json();
                    if (res.ok && data.url) {
                        console.log("🔍 Uploading image:", data.url);

                        // ✅ PERBAIKAN: Insert image dengan format yang benar
                        editor.model.change((writer) => {
                            const imageElement = writer.createElement(
                                "imageBlock",
                                {
                                    src: data.url,
                                }
                            );

                            // Insert di posisi cursor
                            const insertPosition =
                                editor.model.document.selection.getFirstPosition();
                            editor.model.insertContent(
                                imageElement,
                                insertPosition
                            );

                            // ✅ Set default width 50% SETELAH insert
                            setTimeout(() => {
                                editor.model.change((writer) => {
                                    // Cari image yang baru saja di-insert
                                    const root =
                                        editor.model.document.getRoot();
                                    for (const item of root.getChildren()) {
                                        if (
                                            item.is("element", "imageBlock") &&
                                            item.getAttribute("src") ===
                                                data.url
                                        ) {
                                            // ✅ Gunakan attribute name yang BENAR
                                            writer.setAttribute(
                                                "width",
                                                "50%",
                                                item
                                            );
                                            console.log(
                                                "✅ Image auto-resized to 50%"
                                            );
                                            break;
                                        }
                                    }
                                });
                            }, 100);
                        });
                    }
                } catch (err) {
                    console.error("Image upload error:", err);
                }
            },
            { once: true }
        );
    });

    itemsContainer.appendChild(btn);
}

function insertUploadFileButtonToToolbar(editor, commentId) {
    const toolbarEl = editor.ui.view.toolbar.element;
    const itemsContainer =
        toolbarEl.querySelector(".ck-toolbar__items") || toolbarEl;

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "ck ck-button";
    btn.title = "Upload File";
    btn.innerHTML = `
        <span class="ck-button__label" aria-hidden="true" style="display:flex;align-items:center;gap:2px">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" width="20" height="20">
                <path fill="currentColor" d="M6 2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V8.83a2 2 0 0 0-.59-1.41l-3.83-3.83A2 2 0 0 0 10.17 3H6zm4 2 4 4H10V4z"/>
            </svg>
        </span>
    `;
    btn.style.marginLeft = "6px";
    btn.style.cursor = "pointer";

    btn.addEventListener("click", () => {
        const input = document.createElement("input");
        input.type = "file";
        input.accept =
            ".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip,.rar,.ppt,.pptx";
        input.multiple = true;
        input.click();

        input.addEventListener(
            "change",
            async (e) => {
                const files = Array.from(e.target.files);
                console.log("🔵 Files selected:", files.length);

                if (files.length === 0) return;

                // ✅ Process files sequentially
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    console.log(
                        `🔵 Processing file ${i + 1}/${files.length}:`,
                        file.name
                    );
                    await uploadSingleFile(editor, file, commentId);
                }

                console.log("✅ All files processed");
            },
            { once: true }
        );
    });

    itemsContainer.appendChild(btn);
}

// ✅ FUNGSI TERPISAH untuk upload 1 file (dengan async/await yang benar)
async function uploadSingleFile(editor, file, commentId) {
    console.log("🔵 START uploadSingleFile:", file.name);

    const formData = new FormData();
    formData.append("upload", file);
    formData.append("attachable_id", commentId);
    formData.append("attachable_type", "App\\Models\\Comment");

    let loadingParagraph = null;

    // ✅ Step 1: Tampilkan "Uploading..."
    console.log("🔵 Step 1: Creating loading paragraph");
    editor.model.change((writer) => {
        const currentPos = editor.model.document.selection.getFirstPosition();
        console.log("🔵 Current position:", currentPos.path);

        loadingParagraph = writer.createElement("paragraph");
        const loadingText = writer.createText(`Uploading ${file.name}...`);
        writer.append(loadingText, loadingParagraph);

        editor.model.insertContent(loadingParagraph, currentPos);
        console.log("🔵 Loading paragraph inserted");

        // ✅ Pindahkan cursor ke SETELAH loading paragraph
        const afterLoading = writer.createPositionAfter(loadingParagraph);
        writer.setSelection(afterLoading);
        console.log("🔵 Cursor moved to:", afterLoading.path);
    });

    // ✅ Step 2: Upload file
    console.log("🔵 Step 2: Starting fetch for:", file.name);
    try {
        const res = await fetch("/upload", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
            },
            body: formData,
        });

        console.log("🔵 Fetch completed. Status:", res.status);

        if (!res.ok) {
            throw new Error(`Upload failed: ${res.status}`);
        }

        const data = await res.json();
        console.log("🔵 Response data:", data);

        // ✅ Step 3: Replace "Uploading..." dengan link file
        if (data.url) {
            console.log("🔵 Step 3: Replacing loading text with file link");

            editor.model.change((writer) => {
                const root = editor.model.document.getRoot();

                // ✅ Cari paragraph dengan text "Uploading [filename]..."
                const uploadingText = `Uploading ${file.name}...`;
                console.log(
                    "🔵 Looking for text:",
                    JSON.stringify(uploadingText)
                );

                let foundParagraph = null;

                for (const child of root.getChildren()) {
                    if (child.is("element", "paragraph")) {
                        let textContent = "";
                        for (const textNode of child.getChildren()) {
                            if (textNode.is("$text")) {
                                textContent += textNode.data;
                            }
                        }

                        // ✅ EXACT MATCH atau ENDS WITH (untuk kasus gabung)
                        if (
                            textContent === uploadingText ||
                            textContent.endsWith(uploadingText)
                        ) {
                            foundParagraph = child;
                            console.log("✅ Found loading paragraph!");
                            console.log(
                                "   Full text:",
                                JSON.stringify(textContent)
                            );
                            break;
                        }
                    }
                }

                console.log("🔵 Found paragraph?", !!foundParagraph);

                if (foundParagraph) {
                    console.log("🔵 Replacing loading text with file link");

                    // ✅ Hapus SEMUA content dalam paragraph
                    const range = writer.createRangeIn(foundParagraph);
                    writer.remove(range);

                    // ✅ Tambahkan link file
                    const fileLink = writer.createText(file.name, {
                        linkHref: data.url,
                    });
                    writer.append(fileLink, foundParagraph);
                    console.log("🔵 File link appended:", file.name);

                    // ✅ CRITICAL: Buat paragraph KOSONG baru setelahnya
                    const emptyParagraph = writer.createElement("paragraph");
                    const root = editor.model.document.getRoot();
                    const afterCurrentParagraph =
                        writer.createPositionAfter(foundParagraph);
                    writer.insert(emptyParagraph, afterCurrentParagraph);

                    // ✅ Pindahkan cursor ke paragraph kosong baru
                    const positionInEmpty = writer.createPositionAt(
                        emptyParagraph,
                        0
                    );
                    writer.setSelection(positionInEmpty);

                    console.log(
                        "🔵 Created empty paragraph and moved cursor there"
                    );
                    console.log(
                        "🔵 New cursor position:",
                        positionInEmpty.path
                    );
                } else {
                    console.error(
                        "❌ Loading paragraph not found! Creating new one."
                    );

                    // ✅ Buat paragraph baru di akhir
                    const lastChild = root.getChild(root.childCount - 1);
                    const insertPos = lastChild
                        ? writer.createPositionAfter(lastChild)
                        : writer.createPositionAt(root, 0);

                    const newParagraph = writer.createElement("paragraph");
                    const fileLink = writer.createText(file.name, {
                        linkHref: data.url,
                    });
                    writer.append(fileLink, newParagraph);
                    editor.model.insertContent(newParagraph, insertPos);

                    // ✅ Buat paragraph kosong setelahnya
                    const emptyParagraph = writer.createElement("paragraph");
                    const afterNew = writer.createPositionAfter(newParagraph);
                    writer.insert(emptyParagraph, afterNew);

                    const positionInEmpty = writer.createPositionAt(
                        emptyParagraph,
                        0
                    );
                    writer.setSelection(positionInEmpty);

                    console.log(
                        "🔵 Created new paragraph at end with empty paragraph after"
                    );
                }
            });

            console.log("✅ File upload completed successfully:", file.name);
        } else {
            console.error("❌ No URL in response data");
        }
    } catch (err) {
        console.error("❌ Upload error for", file.name, ":", err);

        // ✅ Hapus loading paragraph jika error
        editor.model.change((writer) => {
            const root = editor.model.document.getRoot();
            const uploadingText = `Uploading ${file.name}...`;

            for (const child of root.getChildren()) {
                if (child.is("element", "paragraph")) {
                    let textContent = "";
                    for (const textNode of child.getChildren()) {
                        if (textNode.is("$text")) {
                            textContent += textNode.data;
                        }
                    }

                    if (
                        textContent === uploadingText ||
                        textContent.endsWith(uploadingText)
                    ) {
                        writer.remove(child);
                        console.log(
                            "🔵 Removed loading paragraph due to error"
                        );
                        break;
                    }
                }
            }
        });
    }

    console.log("🔵 END uploadSingleFile:", file.name);
    console.log("─────────────────────────────────────────");
}

// ===== DOKUMEN COMMENT SECTION =====
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

            this.$watch("currentFile", (newFile) => {
                if (newFile && newFile.id) {
                    this.loadCommentsForFile(newFile.id);
                }
            });
        },

        // ✅ TAMBAHKAN INI - Fungsi untuk reset main editor
        resetMainEditor() {
            const containerId = "document-main-comment-editor";

            // Destroy editor lama kalau ada
            if (window.documentEditors[containerId]) {
                this.destroyEditorForDocument(containerId);
            }

            // Recreate editor baru
            setTimeout(() => {
                this.createEditorForDocument(containerId, {
                    placeholder: "Ketik komentar Anda di sini...",
                });
            }, 100);
        },

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

        // ✅ FIXED: Submit komentar utama tanpa alert error yang tidak perlu
        async submitMainComment() {
            const content = this.getDocumentEditorData(
                "document-main-comment-editor"
            ).trim();

            if (!content) {
                return;
            }

            const commentId = this.generateUUID();

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
                        id: commentId,
                        content: content,
                        commentable_id: this.currentFile.id,
                        commentable_type: "App\\Models\\File",
                    }),
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    if (!this.currentFile.comments) {
                        this.currentFile.comments = [];
                    }
                    this.currentFile.comments.unshift(data.comment);

                    // ✅ Reset editor dengan reinitialize
                    this.resetMainEditor();

                    console.log("Komentar berhasil ditambahkan");
                }
            } catch (error) {
                console.error("Error submitting comment:", error);
            }
        },

        // ✅ FIXED: Submit reply tanpa alert
        async submitReplyFromEditor() {
            if (!this.replyView.parentComment) {
                return;
            }

            const parentId = this.replyView.parentComment.id;
            const content = this.getDocumentReplyEditorDataFor(parentId).trim();

            if (!content) {
                return;
            }

            const replyId = this.generateUUID();

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
                        id: replyId,
                        content: content,
                        commentable_id: this.currentFile.id,
                        commentable_type: "App\\Models\\File",
                        parent_comment_id: parentId,
                    }),
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    if (!this.replyView.parentComment.replies) {
                        this.replyView.parentComment.replies = [];
                    }
                    this.replyView.parentComment.replies.push(data.comment);

                    // ✅ Close reply view
                    this.closeReplyView();

                    console.log("Balasan berhasil ditambahkan");
                }
            } catch (error) {
                console.error("Error submitting reply:", error);
            }
        },

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

        async createEditorForDocument(containerId, options = {}) {
            const el = document.getElementById(containerId);
            if (!el) {
                console.warn("Editor container not found:", containerId);
                return null;
            }
            el.innerHTML = "";

            const baseConfig = {
                // ✅ Toolbar yang sama seperti sebelumnya
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
                        "uploadFile",
                    ],
                    shouldNotGroupWhenFull: true,
                },

                // ✅ Heading config
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

                // ✅ Image config dengan RESIZE
                image: {
                    resizeUnit: "%",
                    resizeOptions: [
                        {
                            name: "resizeImage:original",
                            value: null,
                            label: "Original",
                        },
                        { name: "resizeImage:25", value: "25", label: "25%" },
                        { name: "resizeImage:50", value: "50", label: "50%" },
                        { name: "resizeImage:75", value: "75", label: "75%" },
                        {
                            name: "resizeImage:100",
                            value: "100",
                            label: "100%",
                        },
                    ],
                    toolbar: [
                        "imageTextAlternative",
                        "toggleImageCaption",
                        "|",
                        "imageStyle:inline",
                        "imageStyle:block",
                        "imageStyle:side",
                        "|",
                        "resizeImage",
                    ],
                },

                // ✅ Upload config (untuk paste image atau drag-drop)
                simpleUpload: {
                    uploadUrl: "/upload-image",
                    withCredentials: true,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                },

                placeholder:
                    options.placeholder || "Ketik komentar Anda di sini...",
            };

            try {
                // ✅ Gunakan CKEDITOR.ClassicEditor (dari superbuild)
                const editor = await ClassicEditor.create(el, baseConfig);
                window.documentEditors[containerId] = editor;

                // ✅ DEBUG
                console.log(
                    "🔍 Has ImageResize:",
                    editor.plugins.has("ImageResize")
                );
                console.log(
                    "🔍 Available plugins:",
                    Array.from(editor.plugins)
                        .map((p) => p.constructor.name)
                        .filter((n) => n.includes("Image"))
                );

                // ✅ Tambahkan custom button SETELAH editor ready
                insertUploadImageButtonToToolbar(editor, this.generateUUID());
                insertUploadFileButtonToToolbar(editor, this.generateUUID());

                // ✅ Custom upload adapter untuk paste/drag-drop image
                editor.plugins.get("FileRepository").createUploadAdapter = (
                    loader
                ) => {
                    return {
                        upload: async () => {
                            const file = await loader.file;
                            const formData = new FormData();
                            formData.append("upload", file);

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

                // ✅ Listen perubahan data
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
                // Fallback ke textarea
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
                        console.log(`✅ Editor ${containerId} destroyed`);
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
``;
