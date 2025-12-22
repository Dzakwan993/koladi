<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\DocumentRecipient;
use App\Models\Folder;
use App\Models\File;
use Illuminate\Support\Str; // ⬅⬅ Tambahkan ini
use Illuminate\Http\Request;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\StoreFileRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\UserWorkspace;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DokumenController extends Controller
{
    public function index(Workspace $workspace)
    {
        $user = auth()->user();
        $userId = $user->id;

        $activeCompanyId = $workspace->company_id;

        // Cek role SuperAdmin perusahaan
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
        // GET FOLDERS
        // ================================
        $folders = Folder::where('workspace_id', $workspace->id)
            ->where($folderAccess)
            ->with([
                'creator:id,full_name,avatar', // ⬅️ Tambahkan avatar
                'documentRecipients.user:id,full_name,avatar', // ⬅️ Tambahkan avatar di recipients
                'files' => function ($query) use ($fileAccess) {
                    $query->where($fileAccess)
                        ->with(
                            'uploader:id,full_name,avatar', // ⬅️ Tambahkan avatar
                            'documentRecipients.user:id,full_name,avatar' // ⬅️ Tambahkan avatar
                        );
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
        // GET ROOT FILES
        // ================================
        $rootFiles = File::where('workspace_id', $workspace->id)
            ->where($fileAccess)
            ->with([
                'uploader:id,full_name,avatar', // ⬅️ Tambahkan avatar
                'documentRecipients' => function ($query) {
                    $query->where('status', true);
                },
                'documentRecipients.user:id,full_name,avatar'
            ])
            ->orderBy('uploaded_at', 'desc')
            ->get();

        // 🔹 DEBUG: log apa yang di-pass ke view
        \Log::info('DokumenController@index called', [
            'workspace_id' => $workspace->id,
            'user_id' => $userId,
            'folders_count' => $folders->count(),
            'rootFiles_count' => $rootFiles->count(),
            'session_alert' => session('alert'),
            'session_keys' => session()->all(),
        ]);

        // ✅ TAMBAHAN: Log untuk debug
        \Log::info('Root files with uploader:', [
            'count' => $rootFiles->count(),
            'sample' => $rootFiles->first() ? [
                'id' => $rootFiles->first()->id,
                'name' => $rootFiles->first()->name,
                'uploaded_by' => $rootFiles->first()->uploaded_by,
                'uploader' => $rootFiles->first()->uploader ? [
                    'id' => $rootFiles->first()->uploader->id,
                    'name' => $rootFiles->first()->uploader->full_name,
                ] : null
            ] : null
        ]);


        return view('dokumen-dan-file', compact('workspace', 'folders', 'rootFiles'));
    }





    public function store(StoreFolderRequest $request)
    {
        $validated = $request->validated();

        Folder::create([
            'workspace_id' => $validated['workspace_id'],
            'name' => $validated['name'],
            'is_private' => $validated['is_private'] ?? false,
            'created_by' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // 🔥 PERBAIKAN: Redirect dengan URL eksplisit
        $workspaceId = $validated['workspace_id'];
        $parentId = $validated['parent_id'] ?? null;

        $redirectUrl = route('dokumen-dan-file', ['workspace' => $workspaceId]);

        if ($parentId) {
            $redirectUrl .= '?folder=' . $parentId;
        }

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

    public function storeFile(StoreFileRequest $request)
    {
        $validated = $request->validated();
        $uploadedBy = auth()->id();
        $uploaded = $request->file('file');

        // --------- [1] Ambil nama awal ---------
        $originalName = pathinfo($uploaded->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $uploaded->getClientOriginalExtension();

        $workspaceId = $validated['workspace_id'];
        $folderId = $validated['folder_id'] ?? null;

        // --------- [2] Generate nama unik ---------
        $newName = $originalName;
        $counter = 1;

        while (
            File::where('workspace_id', $workspaceId)
                ->where('folder_id', $folderId)
                ->where('file_name', $newName . '.' . $extension)
                ->exists()
        ) {
            $newName = $originalName . '(' . $counter . ')';
            $counter++;
        }

        $finalName = $newName . '.' . $extension;

        // --------- [3] Simpan fisik dengan nama final ---------
        $path = $uploaded->storeAs(
            'files',
            $finalName,
            'public'
        );

        // --------- [4] Simpan ke database ---------
        $fileModel = File::create([
            'workspace_id' => $workspaceId,
            'folder_id' => $folderId,
            'uploaded_by' => $uploadedBy,
            'file_name' => $finalName,
            'file_path' => $path,
            'file_size' => $uploaded->getSize(),
            'file_type' => $extension,
            'file_url' => asset('storage/' . $path),
            'is_private' => false,
            'uploaded_at' => now(),
        ]);

        $workspaceId = $validated['workspace_id'];
        $folderId = $validated['folder_id'] ?? null;

        $redirectUrl = route('dokumen-dan-file', ['workspace' => $workspaceId]);

        if ($folderId) {
            $redirectUrl .= '?folder=' . $folderId;
        }

        // 🔥 SOLUSI: Return JSON dengan redirect URL
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

        // Perubahan nama
        if ($oldName !== $newName) {
            $messages[] = "Nama folder diperbarui.";
        }

        // Perubahan private/public
        if ($oldPrivate !== $newPrivate) {
            $messages[] = "Aturan privasi folder diperbarui.";

            // Jika dari true -> false, reset semua document_recipients
            if ($oldPrivate === true && $newPrivate === false) {
                $folder->documentRecipients()->update(['status' => false]);
            }
        }

        // Jika tidak ada perubahan sama sekali
        if (empty($messages)) {
            $messages[] = "Tidak ada perubahan pada folder.";
        }

        // Update data folder
        $folder->update([
            'name' => $newName,
            'is_private' => $newPrivate,
        ]);

        // 🔥 PERBAIKAN: Redirect dengan URL eksplisit
        $redirectUrl = route('dokumen-dan-file', ['workspace' => $folder->workspace_id]);
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

        // Perubahan nama
        if ($oldName !== $newName) {
            $messages[] = "Nama file diperbarui.";
        }

        // Perubahan private/public
        if ($oldPrivate !== $newPrivate) {
            $messages[] = "Aturan privasi file diperbarui.";

            // Jika dari true -> false, reset semua document_recipients
            if ($oldPrivate === true && $newPrivate === false) {
                $file->documentRecipients()->update(['status' => false]);
            }
        }

        // Jika tidak ada perubahan sama sekali
        if (empty($messages)) {
            $messages[] = "Tidak ada perubahan pada file.";
        }

        // Update data file
        $file->update([
            'name' => $newName,
            'is_private' => $newPrivate,
        ]);

        // 🔥 PERBAIKAN: Redirect dengan URL eksplisit
        $workspaceId = $file->workspace_id;
        $folderId = $file->folder_id;

        $redirectUrl = route('dokumen-dan-file', ['workspace' => $workspaceId]);

        if ($folderId) {
            $redirectUrl .= '?folder=' . $folderId;
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


    public function destroy($id)
    {
        $file = File::findOrFail($id);

        // Hapus file dari storage
        if ($file->path && Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        $file->delete();

        return redirect()->back()->with('alert', [
            'icon' => 'success',
            'title' => 'File berhasil dihapus!',
            'text' => 'Data file sudah dihapus dari sistem.',
        ])->with('alert_once', true);
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

    public function destroyFolder(Folder $folder)
    {
        $workspaceId = $folder->workspace_id;
        $parentId = $folder->parent_id; // bisa null

        // hapus folder (atau soft-delete)
        $folder->delete();

        // build redirect ke workspace + parent folder (jika ada)
        $url = route('dokumen-dan-file', ['workspace' => $workspaceId]);
        if ($parentId) {
            $url .= '?folder=' . $parentId;
        }

        return redirect($url)->with('alert', [
            'icon' => 'success',
            'title' => 'Folder dihapus',
            'text' => 'Folder berhasil dihapus.'
        ]);
    }

    private function deleteSubfolders($folder)
    {
        foreach ($folder->children as $sub) {

            // Hapus file di subfolder ini
            foreach ($sub->files as $file) {
                $file->delete();
            }

            // Rekursif untuk subfolder berikutnya
            $this->deleteSubfolders($sub);

            // Hapus foldernya
            $sub->delete();
        }
    }

    public function getWorkspaceMembers(Request $req, $workspaceId)
    {
        $user = Auth::user();
        $activeCompanyId = session('active_company_id');

        // ================================
        // 1. CEK AKSES ROLE COMPANY
        // ================================

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        $companyRole = $userCompany?->role?->name ?? 'Member';
        $companyHighRoles = ['SuperAdmin',];

        $isHighRole = in_array($companyRole, $companyHighRoles);

        // ================================
        // 2. BACA KONTEKS (folder/file)
        // ================================

        $folderCreatedBy = $req->folder_created_by ?: null;
        $fileUploadedBy = $req->file_uploaded_by ?: null;

        // Jika BUKAN role tinggi,
        // tetap izinkan kalau dia adalah pembuat folder / pengupload file
        if (!$isHighRole) {

            $userWorkspace = UserWorkspace::where('user_id', $user->id)
                ->where('workspace_id', $workspaceId)
                ->where('status_active', true)
                ->with('role')
                ->first();

            $workspaceRole = $userWorkspace?->role?->name ?? 'Member';

            // kalau workspace role juga bukan manager
            if ($workspaceRole !== 'SuperAdmin') {

                // tetapi jika dia pembuat folder atau uploader → IZINKAN
                if ($folderCreatedBy != $user->id && $fileUploadedBy != $user->id) {
                    return response()->json([
                        'error' => 'Anda tidak memiliki akses untuk melihat anggota workspace ini'
                    ], 403);
                }
            }
        }

        // ================================
        // 3. AMBIL SEMUA MEMBER
        // ================================

        $members = User::whereIn(
            'id',
            UserWorkspace::where('workspace_id', $workspaceId)->pluck('user_id')
        )->get();

        // ================================
        // 4. IZIN PILIH MEMBER
        // ================================

        $filtered = $members->map(function ($m) use ($folderCreatedBy, $fileUploadedBy, $isHighRole) {

            $canBeSelected = false;

            // Role tinggi → bisa pilih siapa pun
            if ($isHighRole) {
                $canBeSelected = true;
            }

            // pembuat folder → hanya dirinya yang bisa dipilih
            if ($folderCreatedBy && $m->id == $folderCreatedBy) {
                $canBeSelected = true;
            }

            // pengupload file → hanya dirinya yang bisa dipilih
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

        return response()->json([
            'members' => $filtered
        ]);
    }

    public function recipientsStore(Request $request)
    {
        $request->validate([
            'document_id' => 'required|uuid',
            'selected_members' => 'required|json',
        ]);

        $selectedMembers = json_decode($request->selected_members, true) ?? [];

        // Tandai semua yang ada di database tapi tidak dipilih => status false
        DocumentRecipient::where('document_id', $request->document_id)
            ->whereNotIn('user_id', $selectedMembers)
            ->update(['status' => false]);

        // Tambahkan atau update recipients
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

        // Cek apakah ada minimal 1 recipient aktif
        $hasActiveRecipients = DocumentRecipient::where('document_id', $request->document_id)
            ->where('status', true)
            ->exists();

        // Update kolom is_private di files atau folders
        if ($file = File::find($request->document_id)) {

            // Kalau ada recipient aktif → pastikan private
            if ($hasActiveRecipients) {
                $file->update(['is_private' => true]);
            }

            // Kalau tidak ada recipient aktif → JANGAN sentuh is_private
            // biarkan tetap seperti sebelumnya (true or false sesuai riwayat)

        } elseif ($folder = Folder::find($request->document_id)) {

            if ($hasActiveRecipients) {
                $folder->update(['is_private' => true]);
            }

            // Kalau kosong → tidak diubah
        }

        // ✅ Tentukan redirect URL berdasarkan context
        $redirectUrl = null;

        // ❌ SEBELUM (di recipientsStore)
        if ($file = File::find($request->document_id)) {
            $workspaceId = $file->workspace_id;
            $folderId = $file->folder_id;

            $redirectUrl = route('dokumen-dan-file', ['workspace' => $workspaceId]);

            if ($folderId) {
                $redirectUrl .= '?folder=' . $folderId;
            }
        } elseif ($folder = Folder::find($request->document_id)) {
            $workspaceId = $folder->workspace_id;

            $redirectUrl = route('dokumen-dan-file', ['workspace' => $workspaceId]);
            $redirectUrl .= '?folder=' . $folder->id;
        }

        // ✅ Return JSON response
        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
            'alert' => [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Penerima berkas berhasil diperbarui.',
            ]
        ]);
    }



    public function getRecipients($documentId)
    {
        // Ambil hanya user_id yang status = true
        $recipients = DocumentRecipient::where('document_id', $documentId)
            ->where('status', true)
            ->pluck('user_id'); // ambil array user_id

        return response()->json(['recipients' => $recipients]);
    }

    /**
     * Get workspaces yang bisa diakses user
     */
    public function getUserWorkspaces()
    {
        try {
            $user = Auth::user();
            $activeCompanyId = session('active_company_id');

            if (!$activeCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perusahaan aktif tidak ditemukan'
                ], 400);
            }

            $userCompany = $user->userCompanies()
                ->where('company_id', $activeCompanyId)
                ->with('role')
                ->first();

            $userRole = $userCompany?->role?->name ?? 'Member';

            if (in_array($userRole, ['SuperAdmin', 'Administrator', 'Admin', 'Manager'])) {
                $workspaces = Workspace::where('company_id', $activeCompanyId)
                    ->whereNull('deleted_at')
                    // ✅ HAPUS filter workspace saat ini
                    ->with([
                        'folders' => function ($query) use ($user) {
                            $query->where(function ($q) use ($user) {
                                $q->where('is_private', false)
                                    ->orWhere('created_by', $user->id)
                                    ->orWhereHas('documentRecipients', function ($qr) use ($user) {
                                        $qr->where('user_id', $user->id)
                                            ->where('status', true);
                                    });
                            });
                        },
                        'files' => function ($query) use ($user) {
                            $query->whereNull('folder_id') // ✅ Root files only
                                ->where(function ($q) use ($user) {
                                    $q->where('is_private', false)
                                        ->orWhere('uploaded_by', $user->id)
                                        ->orWhereHas('documentRecipients', function ($qr) use ($user) {
                                            $qr->where('user_id', $user->id)
                                                ->where('status', true);
                                        });
                                });
                        }
                    ])
                    ->get();
            } else {
                $workspaces = Workspace::where('company_id', $activeCompanyId)
                    ->whereNull('deleted_at')
                    ->whereHas('userWorkspaces', function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->where('status_active', true);
                    })
                    ->with([
                        'folders' => function ($query) use ($user) {
                            $query->where(function ($q) use ($user) {
                                $q->where('is_private', false)
                                    ->orWhere('created_by', $user->id)
                                    ->orWhereHas('documentRecipients', function ($qr) use ($user) {
                                        $qr->where('user_id', $user->id)
                                            ->where('status', true);
                                    });
                            });
                        },
                        'files' => function ($query) use ($user) {
                            $query->whereNull('folder_id')
                                ->where(function ($q) use ($user) {
                                    $q->where('is_private', false)
                                        ->orWhere('uploaded_by', $user->id)
                                        ->orWhereHas('documentRecipients', function ($qr) use ($user) {
                                            $qr->where('user_id', $user->id)
                                                ->where('status', true);
                                        });
                                });
                        }
                    ])
                    ->get();
            }

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

            return response()->json([
                'success' => true,
                'workspaces' => $formattedWorkspaces
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getUserWorkspaces: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat workspace',
            ], 500);
        }
    }

    /**
     * Get subfolders dari folder tertentu (untuk navigasi di modal)
     */
    public function getFolderSubfolders(Folder $folder)
    {
        try {
            $user = Auth::user();

            // ... permission check ...

            // ✅ Get subfolders
            $subfolders = Folder::where('parent_id', $folder->id)
                ->where(function ($query) use ($user) {
                    $query->where('is_private', false)
                        ->orWhere('created_by', $user->id)
                        ->orWhereHas('documentRecipients', function ($q) use ($user) {
                            $q->where('user_id', $user->id)->where('status', true);
                        });
                })
                ->orderBy('name')
                ->get(['id', 'name', 'is_private', 'created_by']);

            // ✅ Get files di folder ini
            $files = File::where('folder_id', $folder->id)
                ->where(function ($query) use ($user) {
                    $query->where('is_private', false)
                        ->orWhere('uploaded_by', $user->id)
                        ->orWhereHas('documentRecipients', function ($q) use ($user) {
                            $q->where('user_id', $user->id)->where('status', true);
                        });
                })
                ->orderBy('file_name')
                ->get(['id', 'file_name as name', 'is_private']);

            return response()->json([
                'success' => true,
                'folders' => $subfolders,
                'files' => $files, // ⬅️ TAMBAHKAN INI
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getFolderSubfolders: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat subfolder'
            ], 500);
        }
    }

    /**
     * Pindahkan dokumen ke workspace lain
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
                    'message' => 'Session perusahaan tidak ditemukan. Silakan login ulang.'
                ], 401);
            }

            // ✅ 1. CEK ROLE COMPANY
            $userCompany = $user->userCompanies()
                ->where('company_id', $activeCompanyId)
                ->with('role')
                ->first();

            $companyRole = $userCompany?->role?->name ?? 'Member';
            $isCompanySuperAdmin = ($companyRole === 'SuperAdmin');

            // ✅ 2. CEK AKSES KE WORKSPACE TUJUAN
            $targetWorkspace = Workspace::findOrFail($targetWorkspaceId);

            if ($targetWorkspace->company_id !== $activeCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workspace tujuan tidak termasuk dalam perusahaan aktif Anda'
                ], 403);
            }

            // ✅ CEK: Apakah user adalah creator workspace tujuan?
            $isTargetWorkspaceCreator = ($targetWorkspace->created_by === $userId);

            // ✅ CEK: Apakah user adalah member workspace tujuan?
            $userWorkspace = UserWorkspace::where('user_id', $userId)
                ->where('workspace_id', $targetWorkspaceId)
                ->where('status_active', true)
                ->with('role')
                ->first();

            $workspaceRole = $userWorkspace?->role?->name ?? null;
            $isWorkspaceManager = ($workspaceRole === 'Manager');

            // ✅ VALIDASI AKSES WORKSPACE TUJUAN
            // Boleh akses jika:
            // 1. SuperAdmin Company, ATAU
            // 2. Creator workspace tujuan, ATAU  
            // 3. Member aktif workspace tujuan
            $hasAccessToTargetWorkspace = $isCompanySuperAdmin ||
                $isTargetWorkspaceCreator ||
                $userWorkspace !== null;

            if (!$hasAccessToTargetWorkspace) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke workspace tujuan'
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
                        'message' => 'Folder tujuan tidak ditemukan atau tidak valid'
                    ], 400);
                }

                // ✅ Validasi akses ke folder private
                if ($targetFolder->is_private && $targetFolder->created_by !== $userId) {
                    $hasAccess = DocumentRecipient::where('document_id', $targetFolder->id)
                        ->where('user_id', $userId)
                        ->where('status', true)
                        ->exists();

                    if (!$hasAccess && !$isCompanySuperAdmin) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Anda tidak memiliki akses ke folder tujuan'
                        ], 403);
                    }
                }
            }

            $movedCount = 0;
            $errors = [];
            $renamedFiles = [];

            // ✅ 4. PROSES SETIAP DOKUMEN
            foreach ($request->documents as $doc) {
                try {
                    if ($doc['type'] === 'folder') {
                        $folder = Folder::find($doc['id']);

                        if (!$folder) {
                            $errors[] = "Folder dengan ID {$doc['id']} tidak ditemukan";
                            continue;
                        }

                        // ✅ CEK PERMISSION FOLDER
                        // Boleh pindahkan jika:
                        // 1. SuperAdmin Company, ATAU
                        // 2. Dia pembuat folder (created_by)
                        $canMove = $isCompanySuperAdmin ||
                            ($folder->created_by === $userId);

                        if (!$canMove) {
                            $errors[] = "Tidak dapat memindahkan folder '{$folder->name}' (Anda bukan pembuat folder ini)";
                            continue;
                        }

                        // ✅ Validasi: Cegah circular reference
                        if ($targetFolderId && $this->isDescendantOf($targetFolderId, $folder->id)) {
                            $errors[] = "Tidak dapat memindahkan folder '{$folder->name}' ke dalam subfolder-nya sendiri";
                            continue;
                        }

                        // Update workspace dan parent
                        $updateData = [
                            'workspace_id' => $targetWorkspaceId,
                            'parent_id' => $targetFolderId,
                        ];

                        if (\Schema::hasColumn('folders', 'company_id')) {
                            $updateData['company_id'] = $targetWorkspace->company_id;
                        }

                        $folder->update($updateData);

                        // Update semua files di dalam folder (recursive)
                        $this->updateFolderFilesRecursive($folder, $targetWorkspaceId, $targetWorkspace->company_id);

                        $movedCount++;
                        \Log::info("Folder moved successfully", ['folder_id' => $folder->id, 'name' => $folder->name]);

                    } else if ($doc['type'] === 'file') {
                        $file = File::find($doc['id']);

                        if (!$file) {
                            $errors[] = "File dengan ID {$doc['id']} tidak ditemukan";
                            continue;
                        }

                        // ✅ CEK PERMISSION FILE
                        // Boleh pindahkan jika:
                        // 1. SuperAdmin Company, ATAU
                        // 2. Dia yang upload file (uploaded_by)
                        $canMove = $isCompanySuperAdmin ||
                            ($file->uploaded_by === $userId);

                        if (!$canMove) {
                            $errors[] = "Tidak dapat memindahkan file '{$file->file_name}' (Anda bukan yang mengupload file ini)";
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

                        // Update workspace, folder, dan nama file
                        $updateData = [
                            'workspace_id' => $targetWorkspaceId,
                            'folder_id' => $targetFolderId,
                            'file_name' => $newFileName,
                        ];

                        if (\Schema::hasColumn('files', 'company_id')) {
                            $updateData['company_id'] = $targetWorkspace->company_id;
                        }

                        $file->update($updateData);

                        // Track jika file di-rename
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
                        \Log::info("File moved successfully", ['file_id' => $file->id, 'name' => $newFileName]);
                    }

                } catch (\Exception $e) {
                    \Log::error("Error moving document", [
                        'doc_id' => $doc['id'],
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
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

            if ($movedCount === $totalRequested) {
                $message = "Semua dokumen ({$movedCount}) berhasil dipindahkan";
            } else {
                $message = "{$movedCount} dari {$totalRequested} dokumen berhasil dipindahkan";
            }

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
            \Log::error('Validation error in moveDocuments', [
                'errors' => $e->errors()
            ]);

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

    private function updateFolderFilesRecursive($folder, $workspaceId, $companyId)
    {
        // Update files di folder ini
        $updateData = ['workspace_id' => $workspaceId];

        if (\Schema::hasColumn('files', 'company_id')) {
            $updateData['company_id'] = $companyId;
        }

        $folder->files()->update($updateData);

        // Recursive untuk subfolder
        foreach ($folder->children as $subfolder) {
            $subfolder->update([
                'workspace_id' => $workspaceId,
                'company_id' => \Schema::hasColumn('folders', 'company_id') ? $companyId : null,
            ]);

            $this->updateFolderFilesRecursive($subfolder, $workspaceId, $companyId);
        }
    }

    /**
     * Helper: Cek apakah targetFolder adalah descendant dari sourceFolder
     */
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


    /**
     * Helper: cek apakah user adalah SuperAdmin
     */
    private function isSuperAdmin($user)
    {
        $activeCompanyId = session('active_company_id');

        $userCompany = $user->userCompanies()
            ->where('company_id', $activeCompanyId)
            ->with('role')
            ->first();

        return $userCompany?->role?->name === 'SuperAdmin';
    }







}