<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Folder;
use App\Models\File;
use App\Models\User;
use App\Models\DocumentRecipient;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreCompanyFolderRequest;
use App\Http\Requests\StoreCompanyFileRequest;
use App\Models\Workspace;

class CompanyDokumenController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;
        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            return redirect()->route('dashboard')->with('error', 'Tidak ada company aktif');
        }

        // ================================
        // CEK ROLE COMPANY
        // ================================
        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $companyRole = $userCompany?->role?->name ?? 'Member';
        $isSuperAdmin = ($companyRole === 'SuperAdmin');

        // ================================
        // ACCESS CONDITION FOR FOLDERS
        // ================================
        $folderAccess = function ($query) use ($userId, $isSuperAdmin) {
            $query->where('is_private', false);

            if ($isSuperAdmin) {
                $query->orWhere('is_private', true);
            }

            $query->orWhere(function ($q) use ($userId) {
                $q->where('is_private', true)
                    ->where('created_by', $userId);
            });

            $query->orWhere(function ($q) use ($userId) {
                $q->where('is_private', true)
                    ->whereHas('documentRecipients', function ($qr) use ($userId) {
                        $qr->where('user_id', $userId)
                            ->where('status', true);
                    });
            });
        };

        // ================================
        // ACCESS CONDITION FOR FILES
        // ================================
        $fileAccess = function ($query) use ($userId, $isSuperAdmin) {
            $query->where('is_private', false);

            if ($isSuperAdmin) {
                $query->orWhere('is_private', true);
            }

            $query->orWhere(function ($q) use ($userId) {
                $q->where('is_private', true)
                    ->where('uploaded_by', $userId);
            });

            $query->orWhere(function ($q) use ($userId) {
                $q->where('is_private', true)
                    ->whereHas('documentRecipients', function ($qr) use ($userId) {
                        $qr->where('user_id', $userId)
                            ->where('status', true);
                    });
            });
        };

        // ================================
        // GET FOLDERS (PER COMPANY)
        // ================================
        $folders = Folder::where('company_id', $activeCompanyId) // ← BEDANYA DI SINI
            ->whereNull('workspace_id') // ← PASTIKAN BUKAN MILIK WORKSPACE
            ->where($folderAccess)
            ->with([
                'creator',
                'documentRecipients',
                'files' => function ($query) use ($fileAccess) {
                    $query->where($fileAccess)
                        ->with('uploader', 'documentRecipients');
                }
            ])
            ->withCount([
                'files' => function ($query) use ($fileAccess) {
                    $query->where($fileAccess);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // ================================
        // GET ROOT FILES (PER COMPANY)
        // ================================
        $rootFiles = File::where('company_id', $activeCompanyId) // ← BEDANYA DI SINI
            ->whereNull('workspace_id') // ← PASTIKAN BUKAN MILIK WORKSPACE
            ->whereNull('folder_id')
            ->where($fileAccess)
            ->with(['uploader', 'documentRecipients'])
            ->orderBy('uploaded_at', 'desc')
            ->get();

        $company = Company::find($activeCompanyId);

        return view('company-dokumen-dan-file', compact('company', 'folders', 'rootFiles'));
    }

    public function deleteMultiple(Request $request)
    {
        $validated = $request->validate([
            'documents' => 'required|array',
            'documents.*.id' => 'required',
            'documents.*.type' => 'required|in:file,folder'
        ]);

        $deletedCount = 0;

        foreach ($validated['documents'] as $doc) {
            if ($doc['type'] === 'folder') {
                $folder = Folder::find($doc['id']);
                if ($folder) {
                    $folder->delete();
                    $deletedCount++;
                }
            } else {
                $file = File::find($doc['id']);
                if ($file) {
                    // ✅ CEK: Apakah ada file lain yang pakai path yang sama?
                    $otherFilesCount = File::where('file_path', $file->file_path)
                        ->where('id', '!=', $file->id)
                        ->count();

                    // ✅ Hanya hapus file fisik jika TIDAK ada file lain yang pakai
                    if ($otherFilesCount === 0 && Storage::disk('public')->exists($file->file_path)) {
                        Storage::disk('public')->delete($file->file_path);
                    }

                    $file->delete();
                    $deletedCount++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "$deletedCount berkas berhasil dihapus"
        ]);
    }

    public function storeFolder(StoreCompanyFolderRequest $request)
    {
        \Log::info('📝 storeFolder request data:', $request->all());

        $validated = $request->validated();

        \Log::info('✅ Validated data:', $validated);

        // ✅ HANYA 1x CREATE
        Folder::create([
            'company_id' => $validated['company_id'],
            'workspace_id' => null,
            'name' => $validated['name'],
            'is_private' => $validated['is_private'] ?? false,
            'created_by' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $redirectUrl = route('company-documents.index');

        if ($validated['parent_id'] ?? null) {
            $redirectUrl .= '?folder=' . $validated['parent_id'];
        }

        \Log::info('🔀 Redirect URL generated:', ['url' => $redirectUrl]);

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'alert' => [
                'icon' => 'success',
                'title' => 'Folder berhasil dibuat!',
                'text' => 'Data folder baru sudah tersimpan.',
            ]
        ]);
    }

    public function storeFile(StoreCompanyFileRequest $request)
    {
        $validated = $request->validated();

        $uploaded = $request->file('file');
        $originalName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $uploaded->getClientOriginalExtension();

        $companyId = $validated['company_id'];
        $folderId = $validated['folder_id'] ?? null;

        // Generate nama unik
        $newName = $originalName;
        $counter = 1;

        while (
            File::where('company_id', $companyId)
                ->where('folder_id', $folderId)
                ->where('file_name', $newName . '.' . $extension)
                ->exists()
        ) {
            $newName = $originalName . '(' . $counter . ')';
            $counter++;
        }

        $finalName = $newName . '.' . $extension;

        // Simpan fisik
        $path = $uploaded->storeAs('files', $finalName, 'public');

        // Simpan ke database
        $fileModel = File::create([
            'company_id' => $companyId,
            'workspace_id' => null, // ← PENTING: NULL untuk company-level
            'folder_id' => $folderId,
            'uploaded_by' => auth()->id(),
            'file_name' => $finalName,
            'file_path' => $path,
            'file_size' => $uploaded->getSize(),
            'file_type' => $extension,
            'file_url' => asset('storage/' . $path),
            'is_private' => false,
            'uploaded_at' => now(),
        ]);

        $redirectUrl = route('company-documents.index');

        if ($folderId) {
            $redirectUrl .= '?folder=' . $folderId;
        }

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'alert' => [
                'icon' => 'success',
                'title' => 'File berhasil diunggah!',
                'text' => 'File ' . $fileModel->file_name . ' sudah tersimpan.',
            ]
        ]);
    }

    public function updateFolder(Request $request, $id)
    {
        $folder = Folder::findOrFail($id);

        $oldName = $folder->name;
        $oldPrivate = $folder->is_private;

        $newName = $request->name;
        $newPrivate = $request->is_private == 1;

        $messages = [];

        if ($oldName !== $newName) {
            $messages[] = "Nama folder diperbarui.";
        }

        if ($oldPrivate !== $newPrivate) {
            $messages[] = "Aturan privasi folder diperbarui.";

            if ($oldPrivate === true && $newPrivate === false) {
                $folder->documentRecipients()->update(['status' => false]);
            }
        }

        if (empty($messages)) {
            $messages[] = "Tidak ada perubahan pada folder.";
        }

        $folder->update([
            'name' => $newName,
            'is_private' => $newPrivate,
        ]);

        $redirectUrl = route('company-documents.index');
        $redirectUrl .= '?folder=' . $folder->id;

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'alert' => [
                'icon' => 'success',
                'title' => 'Folder berhasil diperbarui!',
                'text' => implode(" ", $messages),
            ]
        ]);
    }

    public function updateFile(Request $request, $id)
    {
        $file = File::findOrFail($id);

        $oldName = $file->name;
        $oldPrivate = $file->is_private;

        $newName = $request->name;
        $newPrivate = $request->is_private == 1;

        $messages = [];

        if ($oldName !== $newName) {
            $messages[] = "Nama file diperbarui.";
        }

        if ($oldPrivate !== $newPrivate) {
            $messages[] = "Aturan privasi file diperbarui.";

            if ($oldPrivate === true && $newPrivate === false) {
                $file->documentRecipients()->update(['status' => false]);
            }
        }

        if (empty($messages)) {
            $messages[] = "Tidak ada perubahan pada file.";
        }

        $file->update([
            'name' => $newName,
            'is_private' => $newPrivate,
        ]);

        $redirectUrl = route('company-documents.index');

        if ($file->folder_id) {
            $redirectUrl .= '?folder=' . $file->folder_id;
        }

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'alert' => [
                'icon' => 'success',
                'title' => 'File berhasil diperbarui!',
                'text' => implode(" ", $messages),
            ]
        ]);
    }

    public function destroyFile($id)
    {
        $file = File::findOrFail($id);

        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        return redirect()->back()->with('alert', [
            'icon' => 'success',
            'title' => 'File berhasil dihapus!',
            'text' => 'Data file sudah dihapus dari sistem.',
        ])->with('alert_once', true);
    }

    public function destroyFolder(Folder $folder)
    {
        $companyId = $folder->company_id;
        $parentId = $folder->parent_id;

        $folder->delete();

        $url = route('company-documents.index');
        if ($parentId) {
            $url .= '?folder=' . $parentId;
        }

        return redirect($url)->with('alert', [
            'icon' => 'success',
            'title' => 'Folder dihapus',
            'text' => 'Folder berhasil dihapus.'
        ]);
    }

    public function getCompanyMembers(Request $req)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $companyRole = $userCompany?->role?->name ?? 'Member';
        $isHighRole = in_array($companyRole, ['SuperAdmin', 'Admin']);

        $folderCreatedBy = $req->folder_created_by ?: null;
        $fileUploadedBy = $req->file_uploaded_by ?: null;

        if (!$isHighRole) {
            if ($folderCreatedBy != $user->id && $fileUploadedBy != $user->id) {
                return response()->json([
                    'error' => 'Anda tidak memiliki akses untuk melihat anggota company ini'
                ], 403);
            }
        }

        $members = User::whereHas('userCompanies', function ($q) use ($activeCompanyId) {
            $q->where('company_id', $activeCompanyId);
        })->get();

        $filtered = $members->map(function ($m) use ($folderCreatedBy, $fileUploadedBy, $isHighRole, $user) {
            $canBeSelected = false;

            if ($isHighRole) {
                $canBeSelected = true;
            }

            if ($folderCreatedBy && $m->id == $folderCreatedBy) {
                $canBeSelected = true;
            }

            if ($fileUploadedBy && $m->id == $fileUploadedBy) {
                $canBeSelected = true;
            }

            return [
                'id' => $m->id,
                'name' => $m->full_name ?? $m->name,
                'avatar' => $m->avatar ?? 'https://i.pravatar.cc/150?img=' . rand(1, 70),
                'can_be_selected' => $canBeSelected,
                'selected' => false,
            ];
        });

        return response()->json(['members' => $filtered]);
    }

    public function recipientsStore(Request $request)
    {
        $request->validate([
            'document_id' => 'required|uuid',
            'selected_members' => 'required|json',
        ]);

        $selectedMembers = json_decode($request->selected_members, true) ?? [];

        DocumentRecipient::where('document_id', $request->document_id)
            ->whereNotIn('user_id', $selectedMembers)
            ->update(['status' => false]);

        foreach ($selectedMembers as $userId) {
            DocumentRecipient::updateOrCreate(
                [
                    'document_id' => $request->document_id,
                    'user_id' => $userId
                ],
                [
                    'id' => Str::uuid(),
                    'status' => true
                ]
            );
        }

        $hasActiveRecipients = DocumentRecipient::where('document_id', $request->document_id)
            ->where('status', true)
            ->exists();

        if ($file = File::find($request->document_id)) {
            if ($hasActiveRecipients) {
                $file->update(['is_private' => true]);
            }
        } elseif ($folder = Folder::find($request->document_id)) {
            if ($hasActiveRecipients) {
                $folder->update(['is_private' => true]);
            }
        }

        return redirect()->back()->with('alert', [
            'icon' => 'success',
            'title' => 'Berhasil!',
            'text' => 'Penerima berkas berhasil diperbarui.',
        ])->with('alert_once', true);
    }

    public function getRecipients($documentId)
    {
        $recipients = DocumentRecipient::where('document_id', $documentId)
            ->where('status', true)
            ->pluck('user_id');

        return response()->json(['recipients' => $recipients]);
    }

    /**
     * Get daftar workspace yang tersedia untuk move
     */
    public function getAvailableWorkspaces()
    {
        try {
            // ✅ TAMBAHKAN LOGGING
            \Log::info('🔥 getAvailableWorkspaces called from company context');

            $user = Auth::user();
            $activeCompanyId = session('active_company_id');

            \Log::info('User ID:', ['user_id' => $user->id]);
            \Log::info('Active Company ID:', ['company_id' => $activeCompanyId]);

            if (!$activeCompanyId) {
                \Log::error('❌ No active company ID in session');

                return response()->json([
                    'success' => false,
                    'message' => 'Perusahaan aktif tidak ditemukan'
                ], 400);
            }

            // ✅ CEK ROLE: Hanya SuperAdmin dan Admin
            $userCompany = $user->userCompanies()
                ->where('company_id', $activeCompanyId)
                ->with('role')
                ->first();

            $companyRole = $userCompany?->role?->name ?? 'Member';

            \Log::info('User Company Role:', ['role' => $companyRole]);

            if (!in_array($companyRole, ['SuperAdmin', 'Admin'])) {
                \Log::warning('❌ User does not have permission');

                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk memindahkan dokumen company'
                ], 403);
            }

            // ✅ Ambil semua workspace di company ini
            $workspaces = Workspace::where('company_id', $activeCompanyId)
                ->whereNull('deleted_at')
                ->with([
                    'folders' => function ($query) {
                        $query->whereNull('parent_id') // ⬅️ TAMBAHKAN INI (hanya root folders)
                            ->orderBy('name');
                    },
                    'files' => function ($query) {
                        $query->whereNull('folder_id')
                            ->orderBy('file_name');
                    }
                ])
                ->orderBy('name')
                ->get();

            \Log::info('Workspaces found:', ['count' => $workspaces->count()]);

            $formattedWorkspaces = $workspaces->map(function ($workspace) {
                return [
                    'id' => $workspace->id,
                    'name' => $workspace->name,
                    'type' => $workspace->type,
                    'folders' => $workspace->folders->map(function ($folder) {
                        return [
                            'id' => $folder->id,
                            'name' => $folder->name,
                        ];
                    }),
                    'files' => $workspace->files->map(function ($file) {
                        return [
                            'id' => $file->id,
                            'name' => $file->file_name,
                        ];
                    })
                ];
            });

            \Log::info('✅ Response ready', ['workspace_count' => $formattedWorkspaces->count()]);

            // ✅ PASTIKAN RETURN JSON
            return response()->json([
                'success' => true,
                'workspaces' => $formattedWorkspaces
            ], 200, [], JSON_UNESCAPED_UNICODE); // ⬅️ Tambahkan JSON_UNESCAPED_UNICODE

        } catch (\Exception $e) {
            \Log::error('❌ Error in getAvailableWorkspaces:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat workspace: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pindahkan dokumen dari Company ke Workspace
     */
    public function moveDocuments(Request $request)
    {
        try {
            $request->validate([
                'workspace_id' => 'required|uuid|exists:workspaces,id',
                'folder_id' => 'nullable|uuid|exists:folders,id',
                'documents' => 'required|array|min:1',
                'documents.*.id' => 'required|uuid',
                'documents.*.type' => 'required|in:folder,file',
            ]);

            $user = Auth::user();
            $userId = $user->id;
            $targetWorkspaceId = $request->workspace_id;
            $targetFolderId = $request->folder_id;
            $activeCompanyId = session('active_company_id');

            if (!$activeCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session perusahaan tidak ditemukan'
                ], 401);
            }

            // ✅ 1. CEK ROLE: Hanya SuperAdmin dan Admin yang boleh
            $userCompany = $user->userCompanies()
                ->where('company_id', $activeCompanyId)
                ->with('role')
                ->first();

            $companyRole = $userCompany?->role?->name ?? 'Member';

            if (!in_array($companyRole, ['SuperAdmin', 'Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk memindahkan dokumen company'
                ], 403);
            }

            // ✅ 2. VALIDASI WORKSPACE TUJUAN
            $targetWorkspace = Workspace::findOrFail($targetWorkspaceId);

            if ($targetWorkspace->company_id !== $activeCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workspace tujuan tidak termasuk dalam company yang sama'
                ], 403);
            }

            // ✅ 3. VALIDASI FOLDER TUJUAN (jika ada)
            if ($targetFolderId) {
                $targetFolder = Folder::where('id', $targetFolderId)
                    ->where('workspace_id', $targetWorkspaceId)
                    ->first();

                if (!$targetFolder) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Folder tujuan tidak ditemukan'
                    ], 400);
                }
            }

            $movedCount = 0;
            $errors = [];
            $renamedFiles = [];

            // ✅ 4. PROSES SETIAP DOKUMEN
            foreach ($request->documents as $doc) {
                try {
                    if ($doc['type'] === 'folder') {
                        $folder = Folder::where('company_id', $activeCompanyId)
                            ->whereNull('workspace_id')
                            ->find($doc['id']);

                        if (!$folder) {
                            $errors[] = "Folder dengan ID {$doc['id']} tidak ditemukan";
                            continue;
                        }

                        // ✅ Validasi: Cegah circular reference
                        if ($targetFolderId && $this->isDescendantOf($targetFolderId, $folder->id)) {
                            $errors[] = "Tidak dapat memindahkan folder '{$folder->name}' ke dalam subfolder-nya sendiri";
                            continue;
                        }

                        // Update: Pindah dari company ke workspace
                        $folder->update([
                            'workspace_id' => $targetWorkspaceId,
                            'parent_id' => $targetFolderId,
                            'company_id' => $targetWorkspace->company_id, // tetap di company yang sama
                        ]);

                        // Update semua files di dalam folder
                        $this->updateFolderFilesRecursive($folder, $targetWorkspaceId, $targetWorkspace->company_id);

                        $movedCount++;
                        \Log::info("Folder moved from company to workspace", [
                            'folder_id' => $folder->id,
                            'name' => $folder->name,
                            'to_workspace' => $targetWorkspaceId
                        ]);

                    } else if ($doc['type'] === 'file') {
                        $file = File::where('company_id', $activeCompanyId)
                            ->whereNull('workspace_id')
                            ->find($doc['id']);

                        if (!$file) {
                            $errors[] = "File dengan ID {$doc['id']} tidak ditemukan";
                            continue;
                        }

                        // ✅ AUTO-RENAME JIKA DUPLIKAT
                        $originalFileName = $file->file_name;
                        $newFileName = $originalFileName;

                        $pathInfo = pathinfo($originalFileName);
                        $nameWithoutExt = $pathInfo['filename'];
                        $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';

                        $counter = 1;

                        while (
                            File::where('workspace_id', $targetWorkspaceId)
                                ->where('folder_id', $targetFolderId)
                                ->where('file_name', $newFileName)
                                ->where('id', '!=', $file->id)
                                ->exists()
                        ) {
                            $newFileName = $nameWithoutExt . '(' . $counter . ')' . $extension;
                            $counter++;
                        }

                        // Update: Pindah dari company ke workspace
                        $file->update([
                            'workspace_id' => $targetWorkspaceId,
                            'folder_id' => $targetFolderId,
                            'file_name' => $newFileName,
                            'company_id' => $targetWorkspace->company_id, // tetap di company yang sama
                        ]);

                        // Track jika di-rename
                        if ($newFileName !== $originalFileName) {
                            $renamedFiles[] = [
                                'old_name' => $originalFileName,
                                'new_name' => $newFileName
                            ];

                            \Log::info("File renamed during move", [
                                'file_id' => $file->id,
                                'old_name' => $originalFileName,
                                'new_name' => $newFileName
                            ]);
                        }

                        $movedCount++;
                        \Log::info("File moved from company to workspace", [
                            'file_id' => $file->id,
                            'name' => $newFileName,
                            'to_workspace' => $targetWorkspaceId
                        ]);
                    }

                } catch (\Exception $e) {
                    \Log::error("Error moving document", [
                        'doc_id' => $doc['id'],
                        'error' => $e->getMessage()
                    ]);
                    $errors[] = "Error memindahkan dokumen ID {$doc['id']}: " . $e->getMessage();
                }
            }

            // ✅ 5. RESPONSE
            if ($movedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada dokumen yang berhasil dipindahkan',
                    'errors' => $errors
                ], 400);
            }

            $totalRequested = count($request->documents);
            $renamedCount = count($renamedFiles);

            $message = $movedCount === $totalRequested
                ? "Semua dokumen ({$movedCount}) berhasil dipindahkan ke workspace"
                : "{$movedCount} dari {$totalRequested} dokumen berhasil dipindahkan ke workspace";

            if ($renamedCount > 0) {
                $message .= ". {$renamedCount} file di-rename otomatis karena nama duplikat.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'moved_count' => $movedCount,
                'total_requested' => $totalRequested,
                'renamed_count' => $renamedCount,
                'renamed_files' => $renamedFiles,
                'errors' => $errors
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Unexpected error in moveDocuments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ Helper methods (sama seperti DokumenController)
    private function updateFolderFilesRecursive($folder, $workspaceId, $companyId)
    {
        $updateData = ['workspace_id' => $workspaceId];

        if (\Schema::hasColumn('files', 'company_id')) {
            $updateData['company_id'] = $companyId;
        }

        $folder->files()->update($updateData);

        foreach ($folder->children as $subfolder) {
            $subfolder->update([
                'workspace_id' => $workspaceId,
                'company_id' => \Schema::hasColumn('folders', 'company_id') ? $companyId : null,
            ]);

            $this->updateFolderFilesRecursive($subfolder, $workspaceId, $companyId);
        }
    }

    private function isDescendantOf($targetFolderId, $sourceFolderId)
    {
        $currentFolder = Folder::find($targetFolderId);

        while ($currentFolder) {
            if ($currentFolder->id === $sourceFolderId) {
                return true;
            }
            $currentFolder = $currentFolder->parent;
        }

        return false;
    }
}